<?php
namespace App;

use Tk\Event\Dispatcher;
use Tk\Controller\Resolver;



/**
 * Class FrontController
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class FrontController extends \Tk\Kernel\HttpKernel
{
    
    /**
     * @var \Tk\Config
     */
    protected $config = null;


    /**
     * Constructor.
     *
     * @param Dispatcher $dispatcher
     * @param Resolver $resolver
     * @param $config
     */
    public function __construct(Dispatcher $dispatcher, Resolver $resolver, $config)
    {
        parent::__construct($dispatcher, $resolver);
        $this->config = $config;

        // initialise Dom Loader
        \App\Factory::getDomLoader();

        // Init the plugins, has to be here so the configs are not loaded before the system config
        \App\Factory::getPluginFactory();

        // Initiate the email gateway
        \App\Factory::getEmailGateway();
        
        $this->init();
    }

    /**
     * init Application front controller
     * 
     * NOTE: I have tried to add the handlers in the order they are fired but 
     * this is only a rough estimate as some handlers have multiple subscribed events
     * Use this as more of a guide as to the order of the events that are fired (see comments)
     */
    public function init()
    {
        $logger = $this->config->getLog();
        /** @var \Tk\Request $request */
        $request = \App\Factory::getConfig()->getRequest();
        

        // (kernel.init)
        $this->dispatcher->addSubscriber(new \Tk\Listener\StartupHandler($logger, $request, $this->config->getSession()));
        
        // (kernel.request)
        $matcher = new \Tk\Routing\StaticMatcher($this->config->getSitePath().$this->config->get('template.path'), '\App\Controller\StaticPage::doDefault');
        $this->dispatcher->addSubscriber(new \Tk\Listener\RouteListener($matcher));
        $matcher = new \Tk\Routing\UrlMatcher($this->config['site.routes']);
        $this->dispatcher->addSubscriber(new \Tk\Listener\RouteListener($matcher));
        $this->dispatcher->addSubscriber(new \Tk\Listener\ResponseHandler(Factory::getDomModifier()));

        $this->getDispatcher()->addSubscriber(new \Tk\Listener\LogExceptionListener($logger, $this->getConfig()->isDebug()));
        if (preg_match('|^/ajax/.+|', $request->getUri()->getRelativePath())) { // If ajax request
            $this->getDispatcher()->addSubscriber(new \Tk\Listener\JsonExceptionListener($this->getConfig()->isDebug()));
        } else {
            $this->getDispatcher()->addSubscriber(new \Tk\Listener\ExceptionListener($this->getConfig()->isDebug()));
        }

        // (kernel.terminate)
        $sh = new \Tk\Listener\ShutdownHandler($logger, $this->config->getScriptTime());
        $sh->setPageBytes(\App\Factory::getDomFilterPageBytes());
        $this->dispatcher->addSubscriber($sh);

        // Add your own handlers here

        $this->dispatcher->addSubscriber(new \App\Listener\AuthHandler());
        $this->dispatcher->addSubscriber(new \App\Listener\WikiHandler());

    }

    



    /**
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return \App\Factory::getConfig();
    }

    
}