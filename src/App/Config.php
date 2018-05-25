<?php
namespace App;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Config extends \Tk\Config
{

    /**
     * @return Db\LockMap
     */
    public function getLockMap()
    {
        $lm = \App\Db\LockMap::getInstance($this->getUser(), $this->getDb());
        return $lm;
    }



    /**
     * A factory method to create an instances of an Auth adapters
     *
     * @param string $class
     * @param array $submittedData
     * @return \Tk\Auth\Adapter\Iface
     * @throws \Tk\Auth\Exception
     */
    public function getAuthAdapter($class, $submittedData = array())
    {
        /** @var \Tk\Auth\Adapter\Iface $adapter */
        $adapter = null;
        switch($class) {
            case '\Tk\Auth\Adapter\Config':
                $adapter = new $class($this['system.auth.username'], $this['system.auth.password']);
                break;
            case '\Tk\Auth\Adapter\Ldap':
                $adapter = new $class($this['system.auth.ldap.host'], $this['system.auth.ldap.baseDn'], $this['system.auth.ldap.filter'],
                    $this['system.auth.ldap.port'], $this['system.auth.ldap.tls']);
                break;
            case '\Tk\Auth\Adapter\DbTable':
                $adapter = new $class($this['db'], $this['system.auth.dbtable.tableName'],
                    $this['system.auth.dbtable.usernameColumn'], $this['system.auth.dbtable.passwordColumn'],
                    $this['system.auth.dbtable.activeColumn']);
                $adapter->setHashCallback(array($this, 'hashPassword'));
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
     * hashPassword
     *
     * @param $pwd
     * @param \App\Db\User $user (optional)
     * @return string
     */
    public function hashPassword($pwd, $user = null)
    {
        $salt = '';
        if ($user && false) {    // TODO: Use salted password
            if (method_exists($user, 'getHash'))
                $salt = $user->getHash();
            else if ($user->hash)
                $salt = $user->hash;
        }
        return $this->hash($pwd, $salt);
    }

    /**
     * Hash a string using the system config set algorithm
     *
     * @link http://php.net/manual/en/function.hash.php
     * @param string $str
     * @param string $salt (optional)
     * @param string $algo Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..)
     *
     * @return string
     */
    public function hash($str, $salt = '', $algo = 'md5')
    {
        if ($salt) $str .= $salt;
        if ($this->get('hash.function'))
            $algo = $this->get('hash.function');
        return hash($algo, $str);
    }

    /**
     * Create a random password
     *
     * @param int $length
     * @return string
     */
    public function generatePassword($length = 8)
    {
        $chars = '234567890abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $i = 0;
        $password = '';
        while ($i <= $length) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
            $i++;
        }
        return $password;
    }

    /**
     * Create a new user
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string $name
     * @param bool $active
     * @return Db\User
     */
    public function createNewUser($username, $email, $password = '', $name = '', $active = true)
    {
        $user = new \App\Db\User();
        $user->username = $username;
        $user->name = $name;
        $user->email = $email;
        if ($password)
            $user->setNewPassword($password);
        $user->active = $active;
        $user->save();

        return $user;
    }




    /* ****************************************************************************************** */



    /**
     * getRequest
     *
     * @return \Tk\Request
     */
    public function getRequest()
    {
        if (!parent::getRequest()) {
            $obj = \Tk\Request::create();
            parent::setRequest($obj);
        }
        return parent::getRequest();
    }

    /**
     * getCookie
     *
     * @return \Tk\Cookie
     */
    public function getCookie()
    {
        if (!parent::getCookie()) {
            $obj = new \Tk\Cookie($this->getSiteUrl());
            parent::setCookie($obj);
        }
        return parent::getCookie();
    }

    /**
     * getSession
     *
     * @return \Tk\Session
     */
    public function getSession()
    {
        if (!parent::getSession()) {
            $adapter = $this->getSessionAdapter();
            $obj = \Tk\Session::getInstance($adapter, $this, $this->getRequest(), $this->getCookie());
            parent::setSession($obj);
        }
        return parent::getSession();
    }

    /**
     * getSessionAdapter
     *
     * @return \Tk\Session\Adapter\Iface|null
     */
    public function getSessionAdapter()
    {
        if (!$this->get('session.adapter')) {
            //$adapter = null;
            $adapter = new \Tk\Session\Adapter\Database($this->getDb(), new \Tk\Encrypt());
            $this->set('session.adapter', $adapter);
        }
        return $this->get('session.adapter');
    }

    /**
     * getFrontController
     *
     * @return \App\FrontController
     * @throws \Tk\Exception
     */
    public function getFrontController()
    {
        if (!$this->get('front.controller')) {
            $obj = new \App\FrontController($this->getEventDispatcher(), $this->getResolver());
            $this->set('front.controller', $obj);
        }
        return parent::get('front.controller');
    }

    /**
     * getEventDispatcher
     *
     * @return \Tk\Event\Dispatcher
     */
    public function getEventDispatcher()
    {
        if (!$this->get('event.dispatcher')) {
            $obj = new \Tk\Event\Dispatcher($this->getLog());
            $this->set('event.dispatcher', $obj);
        }
        return $this->get('event.dispatcher');
    }

    /**
     * getResolver
     *
     * @return \Tk\Controller\Resolver
     */
    public function getResolver()
    {
        if (!$this->get('resolver')) {
            $obj = new \Tk\Controller\PageResolver($this->getLog());
            //$obj = new \Tk\Controller\Resolver($this->getLog());
            $this->set('resolver', $obj);
        }
        return $this->get('resolver');
    }

    /**
     * Ways to get the db after calling this method
     *
     *  - \Uni\Config::getInstance()->getDb()       //
     *  - \Tk\Db\Pdo::getInstance()                //
     *
     * Note: If you are creating a base lib then the DB really should be sent in via a param or method.
     *
     * @param string $name
     * @return mixed|\Tk\Db\Pdo
     */
    public function getDb($name = 'db')
    {
        if (!$this->get('db') && $this->has($name.'.type')) {
            try {
                $pdo = \Tk\Db\Pdo::getInstance($name, $this->getGroup($name, true));
                $this->set('db', $pdo);
            } catch (\Exception $e) {
                error_log('<p>Config::getDb(): ' . $e->getMessage() . '</p>');
                exit;
            }
        }
        return $this->get('db');
    }

    /**
     * get a dom Modifier object
     *
     * @return \Dom\Modifier\Modifier
     * @throws \Tk\Exception
     */
    public function getDomModifier()
    {
        if (!$this->get('dom.modifier')) {
            $dm = new \Dom\Modifier\Modifier();
            $dm->add(new \Dom\Modifier\Filter\UrlPath($this->getSiteUrl()));
            $dm->add(new \Dom\Modifier\Filter\JsLast());
            if (class_exists('Dom\Modifier\Filter\Less')) {
                $less = $dm->add(new \Dom\Modifier\Filter\Less($this->getSitePath(), $this->getSiteUrl(), $this->getCachePath(),
                    array('siteUrl' => $this->getSiteUrl(), 'dataUrl' => $this->getDataUrl(), 'templateUrl' => $this->getTemplateUrl())));
                $less->setCompress(true);
            }
            if ($this->isDebug()) {
                $dm->add($this->getDomFilterPageBytes());
            }
            $this->set('dom.modifier', $dm);
        }
        return $this->get('dom.modifier');
    }

    /**
     * @return \Dom\Modifier\Filter\PageBytes
     */
    public function getDomFilterPageBytes()
    {
        if (!$this->get('dom.filter.page.bytes')) {
            $obj = new \Dom\Modifier\Filter\PageBytes($this->getSitePath());
            $this->set('dom.filter.page.bytes', $obj);
        }
        return $this->get('dom.filter.page.bytes');
    }

    /**
     * getDomLoader
     *
     * @return \Dom\Loader
     */
    public function getDomLoader()
    {
        if (!$this->get('dom.loader')) {
            $dl = \Dom\Loader::getInstance()->setParams($this->all());
            $dl->addAdapter(new \Dom\Loader\Adapter\DefaultLoader());
            /** @var \Tk\Controller\Iface $controller */
            $controller = $this->getRequest()->getAttribute('controller.object');
            if ($controller->getPage()) {
                $templatePath = dirname($controller->getPage()->getTemplatePath());
                $xtplPath = str_replace('{templatePath}', $templatePath, $this['template.xtpl.path']);
                $dl->addAdapter(new \Dom\Loader\Adapter\ClassPath($xtplPath, $this['template.xtpl.ext']));
            }
            $this->set('dom.loader', $dl);
        }
        return $this->get('dom.loader');
    }

    /**
     * getAuth
     *
     * @return \Tk\Auth
     */
    public function getAuth()
    {
        if (!$this->get('auth')) {
            $obj = new \Tk\Auth(new \Tk\Auth\Storage\SessionStorage($this->getSession()));
            $this->set('auth', $obj);
        }
        return $this->get('auth');
    }

    /**
     * getEmailGateway
     *
     * @return \Tk\Mail\Gateway
     */
    public function getEmailGateway()
    {
        if (!$this->get('email.gateway')) {
            $gateway = new \Tk\Mail\Gateway($this);
            $gateway->setDispatcher($this->getEventDispatcher());
            $this->set('email.gateway', $gateway);
        }
        return $this->get('email.gateway');
    }

    /**
     * getPluginFactory
     *
     * @return \Tk\Plugin\Factory
     */
    public function getPluginFactory()
    {
        if (!$this->get('plugin.factory')) {
            $this->set('plugin.factory', \Tk\Plugin\Factory::getInstance($this->getDb(), $this->getPluginPath(), $this->getEventDispatcher()));
        }
        return $this->get('plugin.factory');
    }



    /**
     * @return Db\User
     */
    public function getUser()
    {
        return $this->get('user');
    }

    /**
     * @param Db\User $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->set('user', $user);
        return $this;
    }



    //  -----------------------  Create methods  -----------------------


    /**
     * Create a page for the request
     *
     * @param \Tk\Controller\Iface $controller
     * @return \Tk\Controller\Page
     */
    public static function createPage($controller)
    {
        $page = new \Tk\Controller\Page();
        $page->setController($controller);
        if (!$controller->getPageTitle()) {     // Set a default page Title for the crumbs
            $controller->setPageTitle($controller->getDefaultTitle());
        }
        return $page;
    }

    /**
     * @param string $formId
     * @param string $method
     * @param string|null $action
     * @return \Tk\Form
     */
    public static function createForm($formId, $method = \Tk\Form::METHOD_POST, $action = null)
    {
        $form = \Tk\Form::create($formId, $method, $action);
        $form->addCss('form-horizontal');
        return $form;
    }

    /**
     * @param $form
     * @return \Tk\Form\Renderer\Dom
     */
    public static function createFormRenderer($form)
    {
        $obj = new \Tk\Form\Renderer\Dom($form);
        $obj->setFieldGroupClass(\Tk\Form\Renderer\FieldGroup::class);
        return $obj;
    }

    /**
     *
     * @param string $id
     * @param array $params
     * @param null|\Tk\Request $request
     * @param null|\Tk\Session $session
     * @return \Tk\Table
     */
    public static function createTable($id, $params = array(), $request = null, $session = null)
    {
        $form = \Tk\Table::create($id, $params, $request, $session);
        return $form;
    }

    /**
     * @param \Tk\Table $table
     * @return \Tk\Table\Renderer\Dom\Table
     */
    public static function createTableRenderer($table)
    {
        $obj = \Tk\Table\Renderer\Dom\Table::create($table);
        return $obj;
    }

    /**
     * @param string $title
     * @param string $icon
     * @param bool $withBack
     * @return \Tk\Ui\Admin\ActionPanel
     */
    public static function createActionPanel($title = 'Actions', $icon = 'fa fa-cogs', $withBack = true)
    {
        $ap = \Tk\Ui\Admin\ActionPanel::create($title, $icon);
        if ($withBack) {
            $ap->addButton(\Tk\Ui\Button::create('Back', 'javascript: window.history.back();', 'fa fa-arrow-left'))
                ->addCss('btn-default btn-once back');
        }
        return $ap;
    }

    /**
     * Helper Method
     * Make a default HTML template to create HTML emails
     * usage:
     *  $message->setBody($message->createHtmlTemplate($bodyStr));
     *
     * @param string $body
     * @param bool $showFooter
     * @return string
     * @todo: Probably not the best place for this..... Dependant on the App
     */
    public static function createMailTemplate($body, $showFooter = true)
    {
        $request = self::getInstance()->getRequest();
        $foot = '';
        if (!self::getInstance()->isCli() && $showFooter) {
            $foot .= sprintf('<i>Page:</i> <a href="%s">%s</a><br/>', $request->getUri()->toString(), $request->getUri()->toString());
            if ($request->getReferer()) {
                $foot .= sprintf('<i>Referer:</i> <span>%s</span><br/>', $request->getReferer()->toString());
            }
            $foot .= sprintf('<i>IP Address:</i> <span>%s</span><br/>', $request->getIp());
            $foot .= sprintf('<i>User Agent:</i> <span>%s</span>', $request->getUserAgent());
        }

        $defaultHtml = sprintf('
<html>
<head>
  <title>Email</title>

<style type="text/css">
body {
  font-family: arial,sans-serif;
  font-size: 80%%;
  padding: 5px;
  background-color: #FFF;
}
table {
  font-size: 0.9em;
}
th, td {
  vertical-align: top;
}
table {

}
th {
  text-align: left;
}
td {
  padding: 4px 5px;
}
.content {
  padding: 0px 0px 0px 20px;
}
p {
  margin: 0px 0px 10px 0px;
  padding: 0px;
}
</style>
</head>
<body>
  <div class="content">%s</div>
  <p>&#160;</p>
  <hr />
  <div class="footer">
    <p>
      %s
    </p>
  </div>
</body>
</html>', $body, $foot);

        return $defaultHtml;
    }





}