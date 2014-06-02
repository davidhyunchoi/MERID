<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver as AnnotationDriver;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $serviceManager = $e->getApplication()->getServiceManager();
        $chain = $serviceManager->get('doctrine.driver.odm_default');
        $chain->addDriver(AnnotationDriver::create(__DIR__ . '/src/' . __NAMESPACE__ . '/Document'), 'Application\Document');
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
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
    
    public function getServiceConfig(){
        return array(
            'factories' => array(
                'application.mappermanager' => function($sm){
                    return new Mapper\Manager($sm);
                },
                'application.email' => function($sm){
                    $config = $sm->get("Config");
                    $config = isset($config['email'])? $config['email']: array();
                    return new Service\Email($config);
                },
            )
        );
    }
}
