<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;


class Module
{
    const VERSION = '3.0.2';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

     public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $router = $e->getApplication()->getServiceManager()->get('router');
        $request = $e->getApplication()->getServiceManager()->get('request');
        // $MatchedRouteName = $router->match($request)->getMatchedRouteName();
        
        // echo "<pre>"; 
        // print_r($router->match($request)->getParams());

        $routeMatch = $router->match($request);
        
        if(!$routeMatch)
            return;

        
        $controllerName = $routeMatch->getParams()['__NAMESPACE__'];

        $module = explode('\\', $controllerName)[0];

        if( ucfirst($module) != 'Application' )
            return;

        $eventManager->attach(MvcEvent::EVENT_ROUTE, function(MvcEvent $e) {

            $routeMatch = $e->getRouteMatch();
            // echo "<pre>"; print_r($routeMatch);die;

        }, 0);
    }

}