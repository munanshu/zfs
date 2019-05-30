<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\ModuleManager;
use Admin\Mapper\UserMapper;
use Admin\Helper\MessagesHelper;
use Admin\Service\AuthService;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventInterface;
use Admin\Mapper\ModuleMapper;
use Admin\Mapper\MediaMapper;
use Admin\Mapper\CategoryMapper;

class Module
{
    const VERSION = '3.0.2';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
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

    //  public function onBootstrap(MvcEvent $e)
    // {
    //     $eventManager = $e->getApplication()->getEventManager();

    //     $eventManager->attach(MvcEvent::EVENT_ROUTE, function(MvcEvent $e) {

    //         $routeMatch = $e->getRouteMatch();
    //         // echo "<pre>"; print_r($routeMatch);die;

    //     }, 0);

    //       // $app = $e->getApplication();
    //       // $evt = $app->getEventManager();

    // }

    public function onBootstrap (MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
         // $ModuleManager = new \Zend\ModuleManager\ModuleManager();
        $ModuleName = $event->getApplication()->getServiceManager()->get('ModuleManager')->getEvent()->getModuleName();
        $router = $event->getApplication()->getServiceManager()->get('router');
        $request = $event->getApplication()->getServiceManager()->get('request');
        $routeMatch = $router->match($request);
        
        if(!$routeMatch)
            return;
        
        $controllerName = $routeMatch->getParams()['__NAMESPACE__'];

        $module = explode('\\', $controllerName)[0];
        // echo "<pre>";
         // print_r($router->match($request)->getParams());
        // die;

        if( ucfirst($module) != 'Admin' )
            return;

            // echo $ModuleName;

        // if( ($pos =strpos($MatchedRouteName, 'api')) == 0 )
        //     return;

         
        // if($MatchedRouteName == 'admin'){

            $event->getApplication()->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'ManipulateAcl'),100);
            // $eventManager->getSharedManager()->attach('*', MvcEvent::EVENT_DISPATCH, array($this, 'ManipulateAcl'),100);


            $eventManager->getSharedManager()->attach('*', MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), -100);
            // $eventManager->getSharedManager()->attach('*', MvcEvent::EVENT_ROUTE, array($this, 'initAuth'), -100);
            $eventManager->getSharedManager()->attach('*', MvcEvent::EVENT_RENDER_ERROR, array(
                $this,
                'onDispatchError'
            ), - 100);
            
        // }
         // $this->ManipulateAcl($event);

    }


    public function ManipulateAcl(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
            // echo "<pre>sffsd"; print_r($routeMatch);die;

         $ViewModel = $event->getViewModel();
         $systemGlobals = new \Zend\Custom\Systemvars();
         $ViewModel->setVariable('systemGlobals',$systemGlobals);
         


         $RestrictionService = $event->getApplication()->getServiceManager()->get('Admin\Service\RestrictionService');
         $Acl = $RestrictionService->initAcl();
         $this->CheckModulePermission($Acl,$event);


         $ViewModel->setVariable('AdminAcl',$Acl);

    }

    public function CheckModulePermission($Acl,$event)
    {

       $Params =  $event->getRouteMatch()->getParams();
       $Target = $event->getTarget();
       $Controller =  $Params['controller'];
       $RouteName = $event->getRouteMatch()->getMatchedRouteName(); 
       @$Params['controller'] =  strtolower(end( explode("\\", $Params['controller'])))   ;
       $Params['controller'] =  str_replace("controller", "", $Params['controller'] )   ;
       $Params['MatchedRoute'] = $RouteName;
       $Session = new \Zend\Session\Container('CurrentUser');

       $AuthService = $event->getApplication()->getServiceManager()->get('Admin\Service\AuthService'); 
       $IsLoggedIn = $AuthService->IsLoggedIn($event,$Controller,false);

       // echo "<pre>"; print_r($IsLoggedIn);
        // die;

       if( isset($IsLoggedIn['error']) || $IsLoggedIn==false)
            return false;

       $UserRole = $Session->data['userRole'];

       $UserId =  \Admin\Helper\MessagesHelper::DeCryptIdentity($Session->data['user_id'],'userIdentity');
       $Params['UserId'] = $UserId;
       $ModulesMapper = $event->getApplication()->getServiceManager()->get('ModuleMapper'); 


       $ResourseName = $ModulesMapper->getCurrentResourceNamebyParams($Params);


       if(!empty($ResourseName)){
        // echo "<pre>"; print_r($Params);die;

            if(!$Acl->isAllowed( $UserRole , $ResourseName->module_name ,'view')){
             $flashMessenger = $event->getApplication()->getServiceManager()->get('ControllerPluginManager')->get('flashMessenger');
                    
                     $flashMessenger->setNamespace('error')->addMessage('You do not have permission to see this resource');

                     $url = $event->getRouter ()->assemble ( array (
                            "controller" => "Dashboard" ,'action'=>'error'
                            ), array (
                                    'name' => 'admin/default' 
                            ) );

                    $response = $event->getResponse();
                    $response->setHeaders( $response->getHeaders()->addHeaderLine( 'Location', $url ) );
                    $response->setStatusCode( 302 );
                    $response->sendHeaders();
                    exit ();

                     // $Target->redirect()->toRoute('admin/default',array('controller'=>'Dashboard','action'=>'error'));
            }

       } 

    }

    public function onDispatchError(MvcEvent $event){
        $target = $event->getTarget();
        $routeMatch = $event->getRouteMatch();
        echo "<pre>"; print_r($routeMatch);
        // die;

        echo "sdfsdf" , 
        $exception = $event->getParam('exception');
        echo $exception->getMessage();
        echo $exception->getLine();
        die;
        // $serviceLocator = $event->getApplication()->getServiceLocator();
        // Do what ever you want to check the user's identity
        $url = $event->getRouter ()->assemble ( array (
                            "controller" => "index" 
                    ), array (
                            'name' => 'admin' 
                    ) );

        $response = $event->getResponse();
        $response->setHeaders( $response->getHeaders()->addHeaderLine( 'Location', $url ) );
        $response->setStatusCode( 302 );
        $response->sendHeaders ();
        exit ();
    }
 


    public function init(ModuleManager $modulemanager)
    {
        
        $module = $modulemanager->getEvent()->getModuleName();

        if($module == "Admin"){
            $EventManager = $modulemanager->getEventManager();

            $EventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this,'onDispatchError'), 100);

            $SharedManager = $EventManager->getSharedManager();
             
            $SharedManager->attach(__NAMESPACE__,'dispatch',function($e){

                $routeMatch = $e->getRouteMatch();
                $controller = $routeMatch->getParam('controller');

                if($controller == "Admin\Controller\IndexController")    
                    $e->getTarget()->layout('admin/login');
                else $e->getTarget()->layout('admin/layout');

            });


           $SharedManager->attach(__NAMESPACE__,'dispatch', array($this, 'initAuth')  );

        }

    }

     

    public function initAuth(MvcEvent $e)
    {

        $routeMatch = $e->getRouteMatch();
        // echo "DFsfsdf"; print_r($routeMatch);die;
        $controller = $routeMatch->getParam('controller');
        $AuthService = $e->getApplication()->getServiceManager()->get('Admin\Service\AuthService');
        $data = $AuthService->IsLoggedIn($e , $controller);
        if(!is_array($data) && $data)
            return true;
        else if(is_array($data) && isset($data['type']) && $data['type'] == 0)
            return $e->getTarget()->redirect()->toRoute('admin/default',array('controller'=>'dashboard','action'=>'index'));
         elseif(is_array($data) && isset($data['type']) && $data['type'] == 1)
          return $e->getTarget()->redirect()->toRoute('admin');
    }
    

    public function getServiceConfig()
    {
        return array(
            'factories' => array(

                'UserMapper' => function($sm){
                    $adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new UserMapper($adapter);
                },

                'ModuleMapper' => function($sm){
                    $adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new ModuleMapper($adapter);  
                },
                'CategoryMapper' => function($sm){
                    $adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new CategoryMapper($adapter);
                },
                'MediaMapper' => function($sm){
                    $adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new MediaMapper($adapter);
                }
            )
        );

    }

    public function getViewHelperConfig()
    {
        return array('factories' => array(
            'MessagesHelper' => function ($sm)
            {
                $adapter = $sm->get('Zend\Db\Adapter\Adapter');
                return new MessagesHelper($adapter);
            }
        ));
    }

    


}