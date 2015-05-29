<?php

namespace PgLogger\EventManager;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Log\Logger;
use Zend\Http\PhpEnvironment\RemoteAddress;
use Zend\Console\Request as ConsoleRequest;

/**
 *
 * Event Logging
 *
 * @author pG
 * @package PgLogger\EventManager
 * @copyright 2015
 *
 */
class LogEvents implements ListenerAggregateInterface
{
    protected $handlers = array();
    protected $log;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * Attach the listener
     *
     * Priority -1000 makes logging execute very late.
     *
     * @param EventManagerInterface $events
     *
     */
    public function attach(EventManagerInterface $events)
    {
        $logger = $this->logger;

        $this->handlers[] = $events->attach('*', function(Event $e) use ($logger) {

            $priority = $e->getParam('priority', Logger::INFO);
            $message = $e->getParam('message', "[No Message Provided]");
            // extra's
            $remoteAddress = new RemoteAddress();
            $extras = array(
                'source' => get_class($e->getTarget()),
                'ip' => $remoteAddress->getIpAddress(),
                'session_id' => session_id()
            );

            $logger->log($priority, $message, $extras);

        }, -1000);

    }

    /**
     *
     * Detach all the attaches listeners
     *
     * @param EventManagerInterface $events
     *
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->handlers as $key => $handler) {
            $events->detach($handler);
            unset($this->handlers[$key]);
        }

        $this->handlers = array();
    }

}
