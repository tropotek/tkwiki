<?php
namespace App;
use Tk\Db\Pdo;

/**
 * Class Factory
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Factory
{
    
    /**
     * Get Config object or array
     * 
     * @param string $sitePath
     * @param string $siteUrl
     * @return \Tk\Config
     */
    static public function getConfig($sitePath = '', $siteUrl = '')
    {
        return \Tk\Config::getInstance($sitePath, $siteUrl);
    }

    /**
     * @return \Tk\Request
     */
    static public function getRequest()
    {
        if (!self::getConfig()->getRequest()) {
            $obj = \Tk\Request::create();
            $obj->setAttribute('config', self::getConfig());;
            self::getConfig()->setRequest($obj);
        }
        return self::getConfig()->getRequest();
    }

    /**
     * @return \Tk\Cookie
     */
    static public function getCookie()
    {
        if (!self::getConfig()->getCookie()) {
            $obj = new \Tk\Cookie(self::getConfig()->getSiteUrl());
            self::getConfig()->setCookie($obj);
        }
        return self::getConfig()->getCookie();
    }

    /**
     * @return \Tk\Session
     */
    static public function getSession()
    {
        if (!self::getConfig()->getSession()) {
            $obj = new \Tk\Session(self::getConfig(), self::getRequest(), self::getCookie());
            //$obj->start(new \Tk\Session\Adapter\Database( self::getDb() ));
            $obj->start();
            self::getConfig()->setSession($obj);
        }
        return self::getConfig()->getSession();
    }

    /**
     * getDb
     * Ways to get the db after calling this method
     *
     *  - \App\Factory::getDb()                 // Application level call
     *  - \Tk\Config::getInstance()->getDb()    //
     *  - \Tk\Db\Pdo::getInstance()             //
     *
     * Note: If you are creating a base lib then the DB really should be sent in via a param or method.
     *
     * @param string $name
     * @return mixed|Pdo
     */
    static public function getDb($name = 'default')
    {
        $config = self::getConfig();
        if (!$config->getDb() && $config->has('db.type')) {
            try {
                $pdo = Pdo::getInstance($name, $config->getGroup('db'));
                $logger = $config->getLog();
//                if ($logger && $config->isDebug()) {
//                    $pdo->setOnLogListener(function ($entry) use ($logger) {
//                        $logger->debug('[' . round($entry['time'], 4) . 'sec] ' . $entry['query']);
//                    });
//                }
                $config->setDb($pdo);
            } catch (\Exception $e) {
                error_log('<p>' . $e->getMessage() . '</p>');
                exit;
            }
            self::getConfig()->setDb($pdo);
        }
        return self::getConfig()->getDb();
    }
    
    /**
     * get a dom Modifier object
     * 
     * @return \Dom\Modifier\Modifier
     */
    static public function getDomModifier()
    {
        if (!self::getConfig()->getDomModifier()) {
            $dm = new \Dom\Modifier\Modifier();
            $dm->add(new \Dom\Modifier\Filter\UrlPath(self::getConfig()->getSiteUrl()));
            $dm->add(new \Dom\Modifier\Filter\Less(self::getConfig()));
            $dm->add(new \Dom\Modifier\Filter\JsLast());
            $dm->add(new \App\Helper\UrlModifierFilter());
            self::getConfig()->setDomModifier($dm);
        }
        return self::getConfig()->getDomModifier();
    }

    /**
     * getDomLoader
     * 
     * @return \Dom\Loader
     */
    static public function getDomLoader()
    {   
        if (!self::getConfig()->getDomLoader()) {
            $dl = \Dom\Loader::getInstance()->setParams(self::getConfig()->all());
            $dl->addAdapter(new \Dom\Loader\Adapter\DefaultLoader());
            if (self::getConfig()->getTemplatePath()) {
                $dl->addAdapter(new \Dom\Loader\Adapter\ClassPath(self::getConfig()->getTemplatePath() . '/xtpl'));
            }
            self::getConfig()->setDomLoader($dl);
        }
        return self::getConfig()->getDomLoader();
    }

    /**
     * @return \App\FrontController
     */
    static public function getFrontController()
    {
        if (!self::getConfig()->getFrontController()) {
            $obj = new \App\FrontController(self::getEventDispatcher(), self::getControllerResolver(), self::getConfig());
            self::getConfig()->setFrontController($obj);
        }
        return self::getConfig()->getFrontController();
    }


    /**
     * get
     *
     * @return \Tk\EventDispatcher\EventDispatcher
     */
    static public function getEventDispatcher()
    {
        if (!self::getConfig()->getEventDispatcher()) {
            $obj = new \Tk\EventDispatcher\EventDispatcher(self::getConfig()->getLog());
            self::getConfig()->setEventDispatcher($obj);
        }
        return self::getConfig()->getEventDispatcher();
    }

    /**
     * get
     *
     * @return \Tk\Controller\ControllerResolver
     */
    static public function getControllerResolver()
    {
        if (!self::getConfig()->getControllerResolver()) {
            $obj = new \Tk\Controller\ControllerResolver(self::getConfig()->getLog());
            self::getConfig()->setControllerResolver($obj);
        }
        return self::getConfig()->getControllerResolver();
    }

    /**
     * @return Db\LockMap
     */
    static public function getLockMap()
    {
        $lm = \App\Db\LockMap::instance(self::getConfig()->getUser(), self::getDb());
        return $lm;
    }
    
    
    /**
     * get
     *
     * @return \Tk\Auth
     */
    static public function getAuth()
    {
        if (!self::getConfig()->getAuth()) {
            $obj = new \Tk\Auth(new \Tk\Auth\Storage\SessionStorage(self::getConfig()->getSession()));
            self::getConfig()->setAuth($obj);
        }
        return self::getConfig()->getAuth();
    }


    /**
     * A factory method to create an instances of an Auth adapters
     *
     *
     * @param string $class
     * @param array $submittedData
     * @return \Tk\Auth\Adapter\Iface
     * @throws \Tk\Auth\Exception
     */
    static public function getAuthAdapter($class, $submittedData = [])
    {
        $config = self::getConfig();
        /** @var \Tk\Auth\Adapter\Iface $adapter */
        $adapter = null;
        switch($class) {
            case '\Tk\Auth\Adapter\Config':
                $adapter = new $class($config['system.auth.username'], $config['system.auth.password']);
                break;
            case '\Tk\Auth\Adapter\Ldap':
                $adapter = new $class($config['system.auth.ldap.host'], $config['system.auth.ldap.baseDn'], $config['system.auth.ldap.filter'],
                    $config['system.auth.ldap.port'], $config['system.auth.ldap.tls']);
                break;
            case '\Tk\Auth\Adapter\DbTable':
                $adapter = new $class($config['db'], $config['system.auth.dbtable.tableName'],
                    $config['system.auth.dbtable.usernameColumn'], $config['system.auth.dbtable.passwordColumn'], 
                    $config['system.auth.dbtable.activeColumn']);
                $adapter->setHashCallback(array(__CLASS__, 'hashPassword'));
                break;
            case '\Tk\Auth\Adapter\Trapdoor':
                $adapter = new $class();
                break;
            default:
                throw new \Tk\Auth\Exception('Cannot locate adapter class: ' . $class);
        }
        // send the user submitted username and password to the adapter
        $adapter->replace($submittedData);
        return $adapter;
    }

    /**
     * @param $pwd
     * @param $user (optional)
     * @return string
     */
    static public function hashPassword($pwd, $user = null)
    {
        return hash('md5', $pwd);
    }
    
}