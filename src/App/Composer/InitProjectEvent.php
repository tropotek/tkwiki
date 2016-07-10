<?php
namespace App\Composer;

use Composer\Script\Event;
use Tk\Config;
use Tk\Db\Pdo;
use Tk\Util\SqlMigrate;

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
        self::init($event, true);
    }

    /**
     * @param Event $event
     */
    static function postUpdate(Event $event)
    {
        self::init($event, false);
    }



    /**
     * @param Event $event
     */
    static function init(Event $event, $isInstall = false)
    {
        $isCleanInstall = false;
        try {
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
            $configInFile = $sitePath . '/src/config/config.php.in';
            $configFile = $sitePath . '/src/config/config.php';
            $htInFile = $sitePath . '/.htaccess.in';
            $htFile = $sitePath . '/.htaccess';

            if (@is_file($configInFile)) {
                $configContents = file_get_contents($configInFile);

                if ($isInstall && @is_file($configFile)) {
                    $v = $io-> askConfirmation(self::warning('NOTICE: Are you sure you want to remove the existing installation data [N]: '), false);
                    if ($v) {
                        try {
                            include $configFile;
                            $config = Config::getInstance();
                            $db = Pdo::getInstance($config['db.name'], $config->getGroup('db'));
                            $db->dropAllTables(true);
                        } catch (\Exception $e) {}
                        @unlink($configFile);
                        @unlink($htFile);
                    } else {
                        return;
                    }
                }

                if (!@is_file($configFile)) {
                    $io->write(self::green('Setup new config.php'));
                    $input = self::userInput($io);
                    foreach ($input as $k => $v) {
                        $configContents = self::setConfigValue($k, self::quote($v), $configContents);
                    }
                } else {

                    $io->write(self::green('Update existing config.php'));
                    $configContents = file_get_contents($configFile);
                }
                // Set dev/debug mode
                if ($composer->getPackage()->isDev()) {
                    $configContents = self::setConfigValue('debug', 'true', $configContents);
                }
                $io->write(self::green('Saving config.php'));
                file_put_contents($configFile, $configContents);
            }

            if (!@is_file($htFile) && @is_file($htInFile)) {
                $io->write(self::green('Setup new .htaccess file'));
                copy($htInFile, $htFile);
                $path = '/';
                if (preg_match('/(.+)\/public_html\/(.*)/', $sitePath, $regs)) {
                    $user = basename($regs[1]);
                    $path = '/~' . $user . '/' . $regs[2] . '/';
                }
                $path = trim($io->ask(self::bold('What is the site base URL path [' . $path . ']: '), $path));
                if (!$path) $path = '/';
                $io->write(self::green('Saving new .htaccess file'));
                $buf = file_get_contents($htFile);
                $buf = str_replace('RewriteBase /', 'RewriteBase ' . $path, $buf);
                file_put_contents($htFile, $buf);
            }

            if (!is_dir($sitePath . '/data')) {
                $io->write(self::green('Creating: Site data directory `/data`'));
                mkdir($sitePath . '/data', 0755, true);
            }

            // Migrate the SQL db
            $io->write(self::green('Migrate the Database'));

            include $configFile;
            $config = Config::getInstance();
            $db = Pdo::getInstance($config['db.name'], $config->getGroup('db'));
            $migrate = new SqlMigrate($db, $config->getSitePath());
            $migrate->setTmpPath($config->getTempPath());
            $files = $migrate->migrate($config->getSrcPath() . '/config/sql');
            foreach ($files as $f) {
                $io->write(self::green('  ' . $f));
            }

            // TODO Prompt for new admin user password and update DB
            // TODO This could be considered unsecure and may need to be removed in favor of an email address only?
            // TODO ----------------------------------------------------------------------------------------
            $sql = sprintf('SELECT * FROM %s a, %s b WHERE  b.role_id = 1 AND b.user_id = a.id', $db->quoteParameter('user'), $db->quoteParameter('user_role'));
            $r = $db->query($sql);
            if (!$r || !$r->rowCount()) {
                $p = $io->ask(self::bold('Please create a new `admin` user password: '), 'admin');
                $hashed = \App\Factory::hashPassword($p);
                $sql = sprintf('UPDATE %s SET password = %s WHERE id = 1', $db->quoteParameter('user'), $db->quote($hashed));

                $r = $db->exec($sql);
                if ($r === false) {
                    print_r($db->errorInfo());
                    $io->write(self::red('Error updating admin user password.'));
                } else {
                    $io->write(self::green('Administrator password updated.'));
                }
            }

        } catch (\Exception $e) {
            $io->write(self::red($e->getMessage()));
            error_log($e->__toString());
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
        $dbTypes = ['mysql', 'pgsql', 'sqlite'];
        $io->write('<options=bold>');
        $i = $io->select('Select the DB type [mysql]: ', $dbTypes, 0);
        $io->write('</>');
        $config['db.type'] = $dbTypes[$i];

        $config['db.host'] = $io->ask(self::bold('Set the DB hostname [localhost]: '), 'localhost');
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

    static function green($str) { return '<fg=green>'.$str.'</>'; }

    static function warning($str) { return '<fg=red;options=bold>'.$str.'</>'; }

    static function red($str) { return '<fg=white;bg=red>'.$str.'</>'; }

    static function quote($str) { return '\''.$str.'\''; }

// IO Examples
//$output->writeln('<fg=green>foo</>');
//$output->writeln('<fg=black;bg=cyan>foo</>');
//$output->writeln('<bg=yellow;options=bold>foo</>');

}