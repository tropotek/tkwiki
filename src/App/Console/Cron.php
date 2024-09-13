<?php
namespace App\Console;

use App\Db\Page;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Console\Console;
use Tk\Db;

/**
 * Cron job to be run nightly
 *
 * # run Nightly site cron job
 *   * /5  *  *   *   *      php /home/user/public_html/bin/cmd cron > /dev/null 2>&1
 *
 */
class Cron extends Console
{
    use LockableTrait;

    protected function configure(): void
    {
        $path = getcwd();
        $this->setName('cron')
            ->setDescription('The site cron script. crontab line: */1 *  * * *   ' . $path . '/bin/cmd cron > /dev/null 2>&1');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            return self::SUCCESS;
        }

        // re-index wiki pages to identify orphaned pages that are not linked

        Db::execute("TRUNCATE links");
        $pages = Page::findAll();
        foreach ($pages as $page) {
            Page::indexPage($page);
        }

        $this->writeComment('Completed!!!');

        $this->release();   // release lock
        return self::SUCCESS;
    }

}
