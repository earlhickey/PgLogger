<?php

namespace PgLogger\Service;

use Zend\Log\Logger as ZendLogger;
use Zend\Log\Filter\Priority as PriorityFilter;
use Zend\Log\Writer;

use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as MailTransport;

/**
 *
 * Logger Service Class
 *
 * @author pG
 * @package PgLogger\Service
 * @copyright 2015
 *
 */
class Logger extends ZendLogger
{
    protected $serviceManager;
    protected $mailTransport;
    protected $mailRecipients;

    /**
     *
     * Class that returns the initiated zend logger class
     *
     * @param bool $debug
     * @param bool $logPhpErrors
     * @return Zend\Log\Logger $logger
     *
     * Built-in Priorities:
     *   EMERG   = 0;  // Emergency: system is unusable
     *   ALERT   = 1;  // Alert: action must be taken immediately
     *   CRIT    = 2;  // Critical: critical conditions
     *   ERR     = 3;  // Error: error conditions
     *   WARN    = 4;  // Warning: warning conditions
     *   NOTICE  = 5;  // Notice: normal but significant condition
     *   INFO    = 6;  // Informational: informational messages
     *   DEBUG   = 7;  // Debug: debug messages
     */
    public function getLogger()
    {
        if(!isset($this->config)) {
            throw new \RuntimeException('Logger not properly configured');
        }

        if(!isset($this->config['appName'])) {
            throw new \RuntimeException('You must specify an app name');
        }

        //  initiate the logger
        $logger = new parent();

        // setup db logging
        if(isset($this->config['database']) && !is_null($this->config['database'])) {
            if((empty($this->config['database']['logger_table']))) {
                throw new \RuntimeException("You must specify a 'logger_table' config param");
            }

            $dbAdapter = $serviceManager->get($this->config['database']['db_adapter']);

            if(!$dbAdapter instanceof \Zend\Db\Adapter\Adapter) {
                throw new \RuntimeException("Failed to load database adapter for logger");
            }

            $tableMapping = array(
                    'timestamp' => 'event_date',
                    'priorityName' => 'priority',
                    'message' => 'event',
                    'extra' => array(
                        'source' => 'source',
                        'uri' => 'uri',
                        'ip'  => 'ip',
                        'session_id' => 'session_id'
                    )
            );

            $logWriter = new DbWriter($dbAdapter, $this->config['logger_table'], $tableMapping);

            $logWriter->addFilter($logFilter);
            $logger->addWriter($logWriter);
        }

        // setup email logging
        if(isset($this->config['email']) && !is_null($this->config['email'])) {

            //  initiate the mail object
            $mail = new Message();

            // set email subject
            $mail->setSubject($this->config['appName']);

            //  set email from
            if(!(count($this->config['email']['from']))) {
                throw new \RuntimeException("Logger email from not properly configured");
            }

            $mail->addFrom($this->config['email']['from']);

            //  set email recipient(s)
            if(!(count($this->config['email']['recipients']))) {
                throw new \RuntimeException("Logger email recipients not properly configured");
            }

            $mail->addTo($this->config['email']['recipients']);

            if(!$this->config['email']['transport'] instanceof MailTransport) {
                throw new \RuntimeException('Mail transport is not an instance of Zend\Mail\Transport\Sendmail');
            }

            // create writer
            $writerMail = new Writer\Mail($mail, $this->config['email']['transport']);

            if(!isset($this->config['email']['priority_filter'])) {
                throw new \RuntimeException("You must specify a email 'priority_filter' config param");
            }

            // create filter
            $filterMail = new PriorityFilter($this->config['email']['priority_filter']);

            // add filter and writer to the logger
            $logger->addWriter($writerMail->addFilter($filterMail));

        }

        // setup syslog logging
        if(isset($this->config['syslog']) && !is_null($this->config['syslog'])) {
            // create writer
            $writerSyslog = new Writer\Syslog(array('application' => $this->config['appName']));

            if(!isset($this->config['syslog']['priority_filter'])) {
                throw new \RuntimeException("You must specify a syslog 'priority_filter' config param");
            }

            // create filter
            $filterSyslog = new PriorityFilter($this->config['syslog']['priority_filter']);

            // add filter and writer to the logger
            $logger->addWriter($writerSyslog->addFilter($filterSyslog));
        }

        // setup file logging
        if(isset($this->config['file']) && !is_null($this->config['file'])) {
            if(!isset($this->config['file']['log_file'])) {
                throw new \RuntimeException("You must specify a file 'log_file' config param");
            }

            // create writer
            $writerStream = new Writer\Stream($this->config['file']['log_file']);

            if(!isset($this->config['file']['priority_filter'])) {
                throw new \RuntimeException("You must specify a file 'priority_filter' config param");
            }

            // create filter
            $filterStream = new PriorityFilter($this->config['file']['priority_filter']);

            // add filter and writer to the logger
            $logger->addWriter($writerStream->addFilter($filterStream));
        }

        Logger::registerErrorHandler($logger, true);
        Logger::registerExceptionHandler($logger);

        //  return the logger
        return $logger;
    }


    /**
     *
     * Get the config through DI
     *
     * @param   string   $config
     *
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

}
