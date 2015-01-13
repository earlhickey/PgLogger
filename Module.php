<?php

namespace PgLogger;

use Zend\ModuleManager\Feature;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements Feature\AutoloaderProviderInterface, Feature\ConfigProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $sharedManager = $eventManager->getSharedManager();
        $sm = $e->getApplication()->getServiceManager();

        // attach LogEvents with priority 100 to execute early
        $sharedManager->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) use ($sm) {
           $controller = $e->getTarget();
           $controller->getEventManager()->attachAggregate($sm->get('PgLogger\EventManager\LogEvents'));
        }, 100);


        $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error', function($e) use ($sm) {
            if ($e->getParam('exception')){
                $sm->get('PgLogger\Service\Logger')->crit($e->getParam('exception'));
            }
        }, 100);

    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

}
