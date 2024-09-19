<?php
namespace App\Console;

use App\Db\Content;
use App\Db\Secret;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bs\Console\Console;

class SecretUp extends Console
{

    protected function configure(): void
    {
        $this->setName('secretUp')
            ->setAliases(['su'])
            ->setDescription('Run once to update content secret ids to hashs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $updated = 0;
        $rows = Content::findAll();
        foreach ($rows as $content) {
            $html = $content->html;

            preg_match_all('/ (wk-secret="([0-9]+)") /', $html, $regs);
            foreach ($regs[2] ?? [] AS $id) {
                $secret = Secret::find(intval($id));
                $old = sprintf('wk-secret="%s"', $id);
                $new = '';
                if ($secret instanceof Secret) {
                    $new = sprintf('data-secret-hash="%s"', $secret->hash);
                }
                $html = str_replace($old, $new, $html);
                $updated++;
            }
            $content->html = $html;
            $content->save();
        }

        if ($updated > 0) {
            $this->writeGreen("Updated $updated content pages");
        } else {
            $this->writeBlue('No secrets found');
        }
        return self::SUCCESS;
    }

}
