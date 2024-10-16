<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Config;

class TestData extends \Bs\Console\TestDataInterface
{

    protected function configure(): void
    {
        $this->setName('testData')
            ->setAliases(['td'])
            ->addOption('clear', 'c', InputOption::VALUE_NONE, 'Clear all test data')
            ->setDescription('Fill the database with test data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!Config::isDev()) {
            $this->writeError('Error: Only run this command in a dev environment.');
            return self::FAILURE;
        }



        return self::SUCCESS;
    }

}
