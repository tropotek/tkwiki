<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestData extends \Tk\Console\Command\TestData
{

    protected function configure()
    {
        $this->setName('testData')
            ->setAliases(['td'])
            ->addOption('clear', 'c', InputOption::VALUE_NONE, 'Clear all test data')
            ->setDescription('Fill the database with test data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->getConfig()->isDebug()) {
            $this->writeError('Error: Only run this command in a debug environment.');
            return self::FAILURE;
        }



        return self::SUCCESS;
    }

}
