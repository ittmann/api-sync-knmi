<?php

namespace App\Service;

use App\Command\SyncCommand;
use App\Entity\DayObservation;
use App\Entity\HourObservation;
use App\Model\DataSet;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Tools\SchemaValidator;
use Exception;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiSyncService
{
    private const DATABASE_BATCH_SIZE = 1000;
    private InputInterface $input;

    public function __construct(
        private readonly ApiClient $apiClient,
        private readonly SerializerService $serializerService,
        private readonly EntityManagerInterface $em,
        private readonly Logger $logger,
    ) {
        $em->getConnection()->getConfiguration()->setMiddlewares([new \Doctrine\DBAL\Logging\Middleware(new \Psr\Log\NullLogger())]);
    }

    /**
     * @return Command::SUCCESS|Command::FAILURE
     */
    public function sync(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $syncItems = $input->getOption('sync');

        foreach ($syncItems as $syncItem) {
            if ($this->syncItem(DataSet::{$syncItem}) !== Command::SUCCESS) {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @return Command::SUCCESS|Command::FAILURE
     */
    private function syncItem(DataSet $syncEnum): int
    {
        $this->logger->debug('Retrieving ' . $syncEnum->getClassname() . ' data');
        $fromDateString = $this->input->getOption('from-date')->format('Ymd');
        $toDateString = $this->input->getOption('to-date')->format('Ymd');

        if ($this->input->getOption('incremental')) {
            $this->logger->debug("incremental mode");

            $maxLast = $this->getMaxLast($syncEnum);
            if ($maxLast === null) {
                $this->logger->warning('Incremental mode was used, but no data found - using default --from-date as start');
            } else {
                $fromDateString = $maxLast;
            }
            $toDateString = (new DateTimeImmutable())->format('Ymd');
        }

        if ($syncEnum === DataSet::uurwaarneming) {
            // fetch observations for all available hours (see https://www.knmi.nl/kennis-en-datacentrum/achtergrond/data-ophalen-vanuit-een-script section about uurgegevens)
            $fromDateString = $fromDateString . '01';
            $toDateString = $toDateString . '24';
        }

        $this->logger->debug("'start': $fromDateString");
        $this->logger->debug("'end': $toDateString");
        $this->logger->debug("'stations': " . $this->input->getOption('weatherstations'));

        if (
            $this->processResponse(
                $syncEnum,
                $this->apiClient->getWaarnemingen($syncEnum, $fromDateString, $toDateString, $this->input->getOption('weatherstations'))
            ) !== Command::SUCCESS
        ) {
            return Command::FAILURE;
        }

        $this->em->clear();
        gc_collect_cycles();
        $this->logger->info('Memory Usage in MB: ' . memory_get_usage() / 1024 / 1024);

        return Command::SUCCESS;
    }

    /**
     * @return Command::SUCCESS|Command::FAILURE
     */
    private function processResponse(DataSet $syncEnum, ResponseInterface $response): int
    {
        $body = $response->getContent(false);

        if (!json_validate($body)) {
            if (str_contains($body, 'De query geeft te veel resultaten terug')) {
                $this->logger->error('De query geeft te veel resultaten terug. Pas uw parameters aan!');
                return Command::FAILURE;
            }
            throw new \Exception('API did not respond valid JSON: ' . $body);
        }

        $items = $this->serializerService->arrayFromJson($body);
        $this->logger->info('Retrieved ' . count($items) . ' ' . $syncEnum->name . ' entit' . (count($items) === 1 ? 'y' : 'ies'));

        $counter = 0;

        foreach ($items as $item) {
            $this->denormalizePersistAndFlush($item, $syncEnum->getFullyQualifiedClassname(), $syncEnum->name, ++$counter, count($items));
        }

        $this->em->clear();

        return Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $itemArray
     * @param class-string<object> $fullyQualifiedClassname
     * @param string $itemName
     * @param int $counter
     * @param int $total
     * @return void
     * @throws Exception
     */
    private function denormalizePersistAndFlush(array $itemArray, string $fullyQualifiedClassname, string $itemName, int $counter, int $total): void
    {
        try {
            $this->denormalizeAndPersist($itemArray, $fullyQualifiedClassname);
        } catch (Exception $e) {
            if (method_exists($e, 'getQuery')) {
                $this->logger->error('Error executing query: ' . $e->getQuery()->getSQL(), ['params' => $e->getQuery()->getParams()]);
            }
            $this->logger->error("Failed normalizing/persisting $itemName object", ['itemArray' => $itemArray]);
            throw new Exception("Failed normalizing/persisting $itemName object: " . $e->getMessage());
        }
        try {
            $this->flushEntitiesWhenBatchSizeIsReached($itemName, $counter, $total);
        } catch (Exception $e) {
            if (method_exists($e, 'getQuery')) {
                $this->logger->error('Error executing query: ' . $e->getQuery()->getSQL(), ['params' => $e->getQuery()->getParams()]);
            }
            throw new Exception("Failed normalizing/persisting $itemName object: " . $e->getMessage());
        }
    }

    /**
     * @template T of object
     * @param array<string, mixed> $itemArray
     * @param class-string<T> $className
     * @return object
     * @throws Exception
     */
    private function denormalizeAndPersist(array $itemArray, string $className): object
    {

        $serializer = new Serializer([new ObjectNormalizer()], []);
        $found = $this->tryToFindEntityForObjectArray($itemArray, $className);

        $context = [];
        if ($found) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $found;
        }

        $object = $serializer->denormalize($itemArray, $className, null, $context);

        if ($found === null) {
            try {
                $this->em->persist($object);
            } catch (ORMException $e) {
                throw new Exception('Error persisting object: ' . $e->getMessage());
            }
        }

        return $object;
    }

    /**
     * @template T of object
     * @param array<string, mixed> $array
     * @param class-string<T> $className
     * @return T|null
     * @throws ORMException
     * @throws \Doctrine\Persistence\Mapping\MappingException
     * @throws \ReflectionException
     */
    private function tryToFindEntityForObjectArray(array $array, string $className): ?object
    {
        $metadataFactory = $this->em->getMetadataFactory();
        $metadata = $metadataFactory->getMetadataFor($className);
        $id = [];
        foreach ($metadata->getIdentifier() as $idKey) {
            if (! array_key_exists($idKey, $array)) {
                $this->logger->debug("identifier key {$idKey} in array is missing!", ['classname' => $className, 'array' => $array]);
                return null;
            }
            if ($array[$idKey] === null) {
                $this->logger->debug("identifier key {$idKey} in array is null!", ['classname' => $className, 'array' => $array]);
                return null;
            }
            $id[$idKey] = $array[$idKey];
        }

        try {
            $found = $this->em->getRepository($className)->find($id);
        } catch (Exception $e) {
            throw new Exception("Error trying EntityManager::find " . $e->getMessage());
        }

        return $found;
    }

    private function flushEntitiesWhenBatchSizeIsReached(string $entityName, int $counter, int $total): void
    {
        if (($counter % self::DATABASE_BATCH_SIZE === 0) || $counter === $total) {
            $this->logger->info("Flushing $entityName $counter / $total");
            try {
                $this->em->flush();
            } catch (Exception $e) {
                if (method_exists($e, 'getQuery')) {
                    $this->logger->error('Error executing query: ' . $e->getQuery()->getSQL(), ['params' => $e->getQuery()->getParams()]);
                }
                throw new Exception($e->getMessage());
            }
            $this->em->clear();
        }
    }

    private function getMaxLast(DataSet $syncEnum): ?string
    {
        $qb = $this->em->getRepository($syncEnum->getFullyQualifiedClassname())->createQueryBuilder('entity')
            ->select('MAX(entity.date) as maxDate');
        $maxDateResult = $qb->getQuery()->getOneOrNullResult();

        if ($maxDateResult['maxDate'] === null) {
            return null;
        }

        return (new DateTimeImmutable($maxDateResult['maxDate']))->format('Ymd');
    }

    public function schemaInSyncWithMetadata(): bool
    {
        if (! (new SchemaValidator($this->em))->schemaInSyncWithMetadata()) {
            $this->logger->debug('Schema is not in sync with metadata');
            return false;
        }
        return true;
    }
}
