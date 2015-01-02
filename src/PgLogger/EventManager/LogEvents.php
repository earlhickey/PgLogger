<?php

namespace PgLogger\EventManager;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Log\Logger;

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


    public function __construct(Logger $log)
    {
        $this->log = $log;
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
        $functions = array('find', 'findOneBy', 'fetchRowsBy', 'fetchSelectBy', 'insert', 'update', 'delete');

        //  add the handles for all the functions
        foreach($functions as $function) {
            $this->handlers[] = $events->attach($function . '.pre', array($this, 'log'), -1000);
            $this->handlers[] = $events->attach($function . '.cache', array($this, 'log'), -1000);
            $this->handlers[] = $events->attach($function . '.post',array($this, 'log'), -1000);
        }
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


    /**
     *
     * Function that logs the event
     *
     * @param Event $e
     *
     */
    public function log($e)
    {
        if (is_array($e)) {
            $this->logArray($e);
        } elseif (is_object($e)) {
            if ($e instanceof Event) {
                $this->logEvent($e);
            } elseif ($e instanceof \Exception) {
                $this->logException($e);
            } else {
                $this->logObject($e);
            }
        } elseif (is_scalar($e)) {
            $this->logScalar($e);
        }
    }


    /**
     *
     * Function that logs an event
     *
     * @param Event $e
     *
     */
    public function logEvent(Event $e)
    {
        $params = $e->getParams();
        $class = get_class($e->getTarget());
        $event  = $e->getName();

        $p = '';
        foreach ($params as $key => $param) {
            if (is_array($param) || is_object($param)) {
                foreach ($param as $key => $value) {
                    if ($key == '__RESULT__') {
                        $value = count($value);
                    }
                    $p .= $key . ' => ' . (is_string($value) || is_numeric($value) ? $value : json_encode(serialize($value))) . ' | ';
                }
                $param = $p;
            } elseif ($param != '') {
                $p .= $key . ' => ' . $param . ' | ';
            }
        }

        $this->log->notice(sprintf('%s(%s): %s', $class, $event, $p));
    }

    /**
     *
     * Function that logs an exception
     *
     * @param Exception $e
     *
     */
    public function logException(\Exception $e)
    {
        $trace = $e->getTraceAsString();
        $i = 1;
        do {
            $messages[] = $i++ . ": " . $e->getMessage();
        } while ($e = $e->getPrevious());

        $log = "Exception:\n" . implode("\n", $messages);
        $log .= "\nTrace:\n" . $trace;

        $this->log->err($log);
    }

    /**
     *
     * Function that logs an object
     *
     * @param $e
     *
     */
    public function logObject($e)
    {
        $this->log->err(sprintf('object!'));
    }

    /**
     *
     * Function that logs an array
     *
     * @param $e
     *
     */
    public function logArray($e)
    {
        $this->log->info(sprintf('array!'));
    }

    /**
     *
     * Function that logs a scalar
     *
     * @param $e
     *
     */
    public function logScalar($e)
    {
        $this->log->info(sprintf($e));
    }
}
