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
     * Constructor.
     *
     * @param Dispatcher $dispatcher
     * @param Resolver $resolver
     * @throws \Tk\Exception
     */
    public function __construct(Dispatcher $dispatcher, Resolver $resolver)
    {
        parent::__construct($dispatcher, $resolver);

        // Init the plugins
        $this->getConfig()->getPluginFactory();

        // Initiate the email gateway
        $this->getConfig()->getEmailGateway();

        $this->init();
    }

    /**
     * init Application front controller
     *
     * NOTE: I have tried to add the handlers in the order they are fired but
     * this is only a rough estimate as some handlers have multiple subscribed events
     * Use this as more of a guide as to the order of the events that are fired (see comments)
     *
     * @throws \Tk\Exception
     */
    public function init()
    {
        $logger = $this->getConfig()->getLog();
        /** @var \Tk\Request $request */
        $request = $this->getConfig()->getRequest();

        // Tk Listeners
        $this->getDispatcher()->addSubscriber(new \Tk\Listener\StartupHandler($logger, $request, $this->getConfig()->getSession()));
        //$matcher = new \Tk\Routing\UrlMatcher($this->getConfig()->get('site.routes'));
        $matcher = new \Tk\Routing\StaticMatcher($this->getConfig()->getSitePath() . dirname($this->getConfig()->get('template.public')), '\App\Controller\StaticPage::doDefault');
        $this->getDispatcher()->addSubscriber(new \Tk\Listener\RouteListener($matcher));
        $matcher = new \Tk\Routing\UrlMatcher($this->getConfig()->get('site.routes'));
        $this->dispatcher->addSubscriber(new \Tk\Listener\RouteListener($matcher));
        $this->getDispatcher()->addSubscriber(new \Tk\Listener\PageHandler($this->getDispatcher()));
        $this->getDispatcher()->addSubscriber(new \Tk\Listener\ResponseHandler($this->getConfig()->getDomModifier()));

        // Exception Handling
        $this->getDispatcher()->addSubscriber(new \Tk\Listener\LogExceptionListener($logger));
        if (preg_match('|^/ajax/.+|', $request->getUri()->getRelativePath())) { // If ajax request
            $this->getDispatcher()->addSubscriber(new \Tk\Listener\JsonExceptionListener($this->getConfig()->isDebug()));
        } else {
            $this->getDispatcher()->addSubscriber(new \Tk\Listener\ExceptionListener($this->getConfig()->isDebug()));
        }
        if (!$this->getConfig()->isDebug()) {
            $this->getDispatcher()->addSubscriber(new \Tk\Listener\ExceptionEmailListener($this->getConfig()->getEmailGateway(), $logger,
                $this->getConfig()->get('site.email'), $this->getConfig()->get('site.title')));
        }

        $sh = new \Tk\Listener\ShutdownHandler($logger, $this->getConfig()->getScriptTime());
        $sh->setPageBytes($this->getConfig()->getDomFilterPageBytes());
        $this->getDispatcher()->addSubscriber($sh);

        // App Listeners
//        $this->getDispatcher()->addSubscriber(new \App\Listener\AjaxAuthHandler());
//        $this->getDispatcher()->addSubscriber(new \App\Listener\MasqueradeHandler());
//        $this->getDispatcher()->addSubscriber(new \App\Listener\ActionPanelHandler());
        $this->getDispatcher()->addSubscriber(new \App\Listener\PageInitHandler());
        $this->getDispatcher()->addSubscriber(new \App\Listener\AuthHandler());
        $this->dispatcher->addSubscriber(new \App\Listener\WikiHandler());


        
        // ----------------------------------------------------------------------------------------
//        // (kernel.init)
//        $this->dispatcher->addSubscriber(new \Tk\Listener\StartupHandler($logger, $request, $this->config->getSession()));
//
//        // (kernel.request)
//        $matcher = new \Tk\Routing\StaticMatcher($this->config->getSitePath().$this->config->get('template.path'), '\App\Controller\StaticPage::doDefault');
//        $this->dispatcher->addSubscriber(new \Tk\Listener\RouteListener($matcher));
//        $matcher = new \Tk\Routing\UrlMatcher($this->config['site.routes']);
//        $this->dispatcher->addSubscriber(new \Tk\Listener\RouteListener($matcher));
//        $this->dispatcher->addSubscriber(new \Tk\Listener\ResponseHandler(Factory::getDomModifier()));
//
//        $this->getDispatcher()->addSubscriber(new \Tk\Listener\LogExceptionListener($logger, $this->getConfig()->isDebug()));
//        if (preg_match('|^/ajax/.+|', $request->getUri()->getRelativePath())) { // If ajax request
//            $this->getDispatcher()->addSubscriber(new \Tk\Listener\JsonExceptionListener($this->getConfig()->isDebug()));
//        } else {
//            $this->getDispatcher()->addSubscriber(new \Tk\Listener\ExceptionListener($this->getConfig()->isDebug()));
//        }
//
//        // (kernel.terminate)
//        $sh = new \Tk\Listener\ShutdownHandler($logger, $this->config->getScriptTime());
//        $sh->setPageBytes($this->getConfig()->getDomFilterPageBytes());
//        $this->dispatcher->addSubscriber($sh);
//
//        // Add your own handlers here
//        $this->dispatcher->addSubscriber(new \App\Listener\AuthHandler());
//        $this->dispatcher->addSubscriber(new \App\Listener\WikiHandler());

    }


    /**
     * @return \App\Config
     */
    public function getConfig()
    {
        return \App\Config::getInstance();
    }
    
}