<?php
namespace App\Composer;

use Composer\Script\Event;

/**
 * Class InitProject
 *
 * Default initProject installer class for the framework V2
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class InitProjectEvent
{


    /**
     * @param Event $event
     */
    static function postInstall(Event $event)
    {
        self::init($event);

    }

    /**
     * @param Event $event
     */
    static function postUpdate(Event $event)
    {
        self::init($event);
    }

    /**
     * @param Event $event
     */
    static function init(Event $event)
    {
        $sitePath = $_SERVER['PWD'];
        $io = $event->getIO();
        $composer = $event->getComposer();

        $head = <<<STR
---------------------------------------------------------
|                    tkWiki V2.0                        |
|                   Author: Godar                       |
|                 (c) Tropotek 2016                     |
---------------------------------------------------------
STR;
        $io->write(self::bold($head));
        $configInPath = $sitePath.'/src/config/config.php.in';
        $configPath = $sitePath . '/src/config/config.php';
        if (@is_file($configInPath)) {
            $configContents = file_get_contents($configInPath);
            if (!@is_file($sitePath . '/src/config/config.php')) {
                $input = self::userInput($io);
                foreach ($input as $k => $v) {
                    $configContents = self::setConfigValue($k, self::quote($v), $configContents);
                }
            } else {
                $configContents = file_get_contents($configPath);
            }
            // Set dev/debug mode
            if ($composer->getPackage()->isDev()) {
                $configContents = self::setConfigValue('debug', 'true', $configContents);
            }
            file_put_contents($sitePath . '/src/config/config.php', $configContents);
        }

        if (!@is_file($sitePath.'/.htaccess') && @is_file($sitePath.'/.htaccess.in')) {
            copy($sitePath.'/.htaccess.in', $sitePath.'/.htaccess');
            $path = '/';
            if (preg_match('/(.+)\/public_html\/(.*)/', $sitePath, $regs)) {
                $user = basename($regs[1]);
                $path = '/~' . $user . '/' . $regs[2] . '/';
            }
            $path = trim($io->ask(self::bold('What is the site base URL path['.$path.']: '), $path));
            if (!$path) $path = '/';
            $io->write('Installing: `.htaccess` for front controller.');
            $buf = file_get_contents($sitePath.'/.htaccess');
            $buf = str_replace('RewriteBase /', 'RewriteBase ' . $path, $buf);
            file_put_contents($sitePath.'/.htaccess', $buf);
        }

        if (!is_dir($sitePath.'/data')) {
            $io->write('Creating: Site data directory `/data`.');
            mkdir($sitePath.'/data', 0755, true);
            // TODO: Test if dir writable by apache/user running the site ????
        }

        // Finally check if the DB is setup
        include $configPath;
        $config = \Tk\Config::getInstance();
        $db = \Tk\Db\Pdo::getInstance($config['db.name'], $config->getGroup('db'));
        if ($db) {
            if (!$db->tableExists('data')) {
                // migrate/install db
                $migrate = new \Tk\Util\Migrate($db, $config->getSitePath());
                $migrate->setTempPath($config->getTempPath());
                $migrate->migrate($config->getSrcPath().'/config/sql');

            }
        }
    }

    /**
     * @param Composer\IO\IOInterface $io
     * @return array
     */
    static function userInput($io)
    {
        $config = [];
        // Prompt for the database access
        $dbTypes = ['mysql (default)', 'pgsql', 'sqlite'];
        $io->write('<options=bold>');
        $i = $io->select('Select the DB type[mysql]: ', $dbTypes, 0);
        $io->write('</>');
        $config['db.type'] = $dbTypes[$i];

        $config['db.host'] = $io->ask(self::bold('Set the DB hostname[localhost]: '), 'localhost');
        $config['db.name'] = $io->askAndValidate(self::bold('Set the DB name: '), function ($data) { if (!$data) throw new \Exception('Please enter the DB name to use.');  return $data; });
        $config['db.user'] = $io->askAndValidate(self::bold('Set the DB user: '), function ($data) { if (!$data) throw new \Exception('Please enter the DB username.'); return $data; });
        $config['db.pass'] = $io->askAndValidate(self::bold('Set the DB password: '), function ($data) { if (!$data) throw new \Exception('Please enter the DB password.'); return $data; });

        return $config;
    }


    /**
     * updateConfig
     *
     * @param string $k
     * @param string $v
     * @param string $configContents
     * @return mixed
     */
    static function setConfigValue($k, $v, $configContents)
    {
        $reg = '/\$config\[[\'"]('.preg_quote($k, '/').')[\'"]\]\s=\s[\'"]?(.+)[\'"]?;/';
        return preg_replace($reg, '\$config[\'$1\'] = ' . $v . ';', $configContents);
    }

    static function bold($str) { return '<options=bold>'.$str.'</>'; }

    static function quote($str) { return '\''.$str.'\''; }

// IO Examples
//$output->writeln('<fg=green>foo</>');
//$output->writeln('<fg=black;bg=cyan>foo</>');
//$output->writeln('<bg=yellow;options=bold>foo</>');

}