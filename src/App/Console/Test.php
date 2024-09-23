<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bs\Console\Console;
use Tk\Config;

class Test extends Console
{

    protected function configure(): void
    {
        $this->setName('test')
            ->setDescription('This is a test script');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!Config::isDev()) {
            $this->writeError('Error: Only run this command in a dev environment.');
            return self::FAILURE;
        }

        $output->writeln('Complete!!!');
        return self::SUCCESS;
    }



}
