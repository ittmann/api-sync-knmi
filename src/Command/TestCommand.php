<?php

namespace App\Command;

use App\Model\DataSet;
use App\Service\ApiClient;
use App\Service\SerializerService;
use DateTimeImmutable;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'knmi:test',
    description: 'KNMI test command.',
    hidden: false
)]
class TestCommand extends Command
{
    public function __construct(
        private ApiClient $apiClient,
        private Logger $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This is a command to test the application...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (! $input->getOption('quiet')) {
            $this->logger->pushHandler(new ConsoleHandler($output));
        }

        $this->logger->info("Starting test");

        $datestring = (new DateTimeImmutable('midnight -2 day'))->format('Ymd');
        $result = $this->apiClient->getWaarnemingen(DataSet::dagwaarneming, $datestring, $datestring, '290');
        $json = json_decode($result->getContent());
        $count = count($json);
        $io->success("Received $count item" . ($count === 1 ? '' : 's'));
        var_dump($json);
        $io->success("Finished!");

        return Command::SUCCESS;
    }
}
