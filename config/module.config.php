<?php
/**
 * PgLogger Configuration
 */

use Zend\Log\Logger;

return array(
    'service_manager' => array(
        'factories' => array(
            'PgLogger\EventManager\LogEvents' => function ($sm) {
                $service = new \PgLogger\EventManager\LogEvents($sm->get('PgLogger\Service\Logger'));
                return $service;
            },
            'PgLogger\Service\Logger' => function ($sm) {
                $config = $sm->get('Config');
                $service = new \PgLogger\Service\Logger();
                $service->setConfig($config['logger']);
                return $service->getLogger();
            },
        ),
    ),
);