<?php

namespace App\Listener;

use Psr\Log\LoggerInterface;
use Tk\EventDispatcher\SubscriberInterface;
use Tk\Event\ResponseEvent;

/**
 * Class ShutdownHandler
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ShutdownHandler implements SubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * @param LoggerInterface $logger
     */
    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ResponseEvent $event
     */
    public function onShutdown(ResponseEvent $event)
    {
        if ($this->logger) {
            $this->logger->info('------------------------------------------------');
            $this->logger->info('Load Time: ' . round(\App\FrontController::scriptDuration(), 4) . ' sec');
            $this->logger->info('Peek Mem:  ' . \Tk\File::bytes2String(memory_get_peak_usage(), 4));
            $this->logger->info('------------------------------------------------' . \PHP_EOL . \PHP_EOL);
        }
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(\Tk\Kernel\KernelEvents::TERMINATE => 'onShutdown');
    }

}