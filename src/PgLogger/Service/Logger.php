<?php

namespace PgLogger\Service;

use Zend\Log\Logger as ZendLogger;
use Zend\Log\Filter\Priority;
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
     *
     */
    public function getLogger($debug = false, $logPhpErrors = true)
    {
        if(!$this->mailTransport instanceof MailTransport) {
            throw new InvalidArgumentException('Mail transport is not an instance of Zend\Mail\Transport\Sendmail');
        }

        //  set default recipient
        if(!(count($this->mailRecipients))) {
            $this->setMailRecipients(array('Patrick Groot' => 'pgroot@gmail.com'));
        }

        //  initiate the logger
        $logger = new parent();

        //  initiate the mail object
        $mail = new Message();
        $mail->addFrom('no-reply@skyradio.nl', 'SRG Logger');
        foreach($this->mailRecipients as $recipient => $email) {
            $mail->addTo($email, $recipient);
        }

        //  create the writers
        $writerSyslog = new Writer\Syslog(array('application' => $this->name));
        $writerMail = new Writer\Mail($mail, $this->mailTransport);

        //  add the filters and writers to the logger
        $logger->addWriter($writerMail->addFilter(new Priority(Logger::EMERG)));
        $logger->addWriter($writerSyslog->addFilter(new Priority(Logger::NOTICE)));

        //  log php environment errors
        if($logPhpErrors === true) {
            Logger::registerErrorHandler($logger);
        }

        //  return the logger
        return $logger;
    }


    /**
    *
    * Get the mail transport through DI
    *
    * @param Zend\Mail\Transport\Sendmail $mailTransport
    *
    */
    public function setMailTransport(MailTransport $mailTransport)
    {
        $this->mailTransport = $mailTransport;
        return $this;
    }


    /**
     *
     * Get the mail recipients through DI
     *
     * @param   array   $mailRecipients
     *
     */
    public function setMailRecipients($mailRecipients = array())
    {
        $this->mailRecipients = $mailRecipients;
        return $this;
    }

    /**
     *
     * Get the name used in log messages through DI
     *
     * @param   string   $name
     *
     */
    public function setName($name = 'NONAMESET')
    {
        $this->name = '[' . $name . ']';
        return $this;
    }
}
