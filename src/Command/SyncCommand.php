<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\DataSet;
use App\Service\ApiClient;
use App\Service\ApiSyncService;
use DateTime;
use Doctrine\ORM\Exception\NotSupported;
use InvalidArgumentException;
use Monolog\Logger;
use ReflectionException;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsCommand(
    name: 'knmi:sync',
    description: 'KNMI sync command.',
    hidden: false
)]
class SyncCommand extends Command
{
    public const int BATCH_SIZE = 500;

    public function __construct(
        private readonly Logger $logger,
        private readonly ApiSyncService $syncService,
        readonly ApiClient $client,
    ) {
        parent::__construct();
    }

    private function getSyncOptionsAsString(): string
    {
        return implode(' | ', array_merge(['all'], array_column(DataSet::cases(), 'name')));
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'sync',
                's',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'What dataset(s) to sync (' . $this->getSyncOptionsAsString() . ')',
                ['all'],
                array_merge(['all'], array_column(DataSet::cases(), 'name'))
            )
            ->addOption(
                'weatherstations',
                'w',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'What weatherstation(s) to sync (empty or 0 means all)',
                [],
            )
            ->addOption(
                'from-date',
                'f',
                InputOption::VALUE_REQUIRED,
                'Start date to query for',
                (new DateTime("first day of last month"))->format('Y-m-d')
            )
            ->addOption(
                'to-date',
                't',
                InputOption::VALUE_REQUIRED,
                'End date to query for',
                (new DateTime("last day of last month"))->format('Y-m-d')
            )
            ->addOption(
                'incremental',
                'i',
                InputOption::VALUE_NONE,
                "Don't use --from-date and --to-date, use the last sync date instead (if available)"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $input->getOption('quiet')) {
            $this->logger->pushHandler(new ConsoleHandler($output));
        }

        if (! $this->syncService->schemaInSyncWithMetadata()) {
            $this->logger->error('Database is not in sync with metadata, update the tables first!');
            return self::FAILURE;
        }

        if ($this->processInput($input, $output) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $retVal = $this->syncService->sync($input, $output);
        if ($retVal !== self::SUCCESS) {
            return $retVal;
        }

        return self::SUCCESS;
    }

    private function processInput(InputInterface $input, OutputInterface $output): int
    {
        if (in_array('all', $input->getOption('sync')) || in_array('All', $input->getOption('sync'))) {
            $input->setOption('sync', array_column(DataSet::cases(), 'name'));
        } else {
            foreach ($input->getOption('sync') as $sync) {
                if (! in_array($sync, array_column(DataSet::cases(), 'name'))) {
                    (new SymfonyStyle($input, $output))->error("Invalid option, $sync is not in: " . $this->getSyncOptionsAsString());
                    return self::FAILURE;
                }
            }
        }

        foreach ($input->getOption('weatherstations') as $weatherstation) {
            if (! is_numeric($weatherstation) || $weatherstation < 0) {
                (new SymfonyStyle($input, $output))->error("Invalid weatherstation value ($weatherstation), must be numeric and > 0");
                return self::FAILURE;
            }
        }
        $input->setOption(
            'weatherstations',
            $input->getOption('weatherstations') === [] || in_array('0', $input->getOption('weatherstations'))
                ? 'ALL'
                : implode(':', $input->getOption('weatherstations'))
        );

        try {
            $fromDate = new \DateTime($input->getOption('from-date'));
        } catch (\Exception $e) {
            (new SymfonyStyle($input, $output))->error(
                "from-date string could not be parsed\n
                (see https://www.php.net/manual/en/datetime.formats.php#datetime.formats.relative for possible values)"
            );
            return self::FAILURE;
        }
        $input->setOption('from-date', $fromDate);

        try {
            $toDate = new \DateTime($input->getOption('to-date'));
        } catch (\Exception $e) {
            (new SymfonyStyle($input, $output))->error(
                "to-date string could not be parsed\n
                 (see https://www.php.net/manual/en/datetime.formats.php#datetime.formats.relative for possible values)"
            );
            return self::FAILURE;
        }
        $input->setOption('to-date', $toDate);

        if ($fromDate > $toDate) {
            (new SymfonyStyle($input, $output))->error("--from-date must be before or equal to --to-date");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
