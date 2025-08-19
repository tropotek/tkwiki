<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bs\Console\Console;
use Tk\Config;
use Tk\Db;
use Tk\Encrypt;

class Test extends Console
{

    protected function configure(): void
    {
        $this->setName('test')
            //->addArgument('qrImage', InputArgument::REQUIRED, 'A valid file path')
            ->setDescription('This is a test script');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!Config::isDev()) {
            $this->writeError('Error: Only run this command in a dev environment.');
            return self::FAILURE;
        }

        $rows = DB::query('SELECT * FROM secret');

        $oldEnc = new Encrypt(Config::getValue('system.encrypt.old'));
        $newEnc = new Encrypt(Config::getValue('system.encrypt'));
        foreach ($rows as $row) {
            $row->url = $oldEnc->basicDecrypt($row->url);
            $row->username = $oldEnc->basicDecrypt($row->username);
            $row->password = $oldEnc->basicDecrypt($row->password);
            $row->otp = $oldEnc->basicDecrypt($row->otp);
            $row->keys = $oldEnc->basicDecrypt($row->keys);
            $row->notes = $oldEnc->basicDecrypt($row->notes);
            vd($row);
            $row->url = $newEnc->safeEncrypt($row->url);
            $row->username = $newEnc->safeEncrypt($row->username);
            $row->password = $newEnc->safeEncrypt($row->password);
            $row->otp = $newEnc->safeEncrypt($row->otp);
            $row->keys = $newEnc->safeEncrypt($row->keys);
            $row->notes = $newEnc->safeEncrypt($row->notes);
            vd($row);
            //DB::update('secret', 'secret_id', $row);
        }



        $output->writeln('Complete!!!');
        return self::SUCCESS;
    }
}
