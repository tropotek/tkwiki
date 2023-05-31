<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Tk\Console\Console;

class Password extends Console
{

    protected function configure()
    {
        $this->setName('password')
            ->setAliases(array('pwd'))
            ->addArgument('username', InputArgument::REQUIRED, 'A valid username.')
            ->addArgument('password', InputArgument::REQUIRED, 'A valid password for the user.')
            ->addArgument('institutionId', InputArgument::OPTIONAL, 'A valid institutionId if username is not unique.', null)
            //->addArgument('roleId', InputArgument::OPTIONAL, 'A valid institutionId if username is not unique.', 5)
            ->setDescription('Set a users new password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->getConfig()->isDebug()) {
            $this->writeError('Error: Only run this command in a debug environment.');
            return self::FAILURE;
        }

        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $user = \App\Db\UserMap::create()->findByUsername($username);
        if (!$user) {
            $this->writeError('Error: No valid user found.');
            return self::FAILURE;
        }

        $user->setPassword(\App\Db\User::hashPassword($password));
        $user->save();

        return self::SUCCESS;
    }

}
