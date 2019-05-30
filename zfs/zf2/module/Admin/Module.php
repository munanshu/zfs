<?php
namespace Admin;

use Admin\Model\Entity\Admin;
use Admin\Model\AdminTable;
use Admin\Model\DbAdminMapper;
use Zend\ModuleManager\ModuleManager;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Admin\View\Helper\DbTablehelper;
use Admin\View\Helper\WidgetHelper;
use Admin\View\Helper\MsgTemplateHelper;


class Module
{   
    // public function onBootstrap(MvcEvent $e)
    // {
    //     $eventManager        = $e->getApplication()->getEventManager();
    //     $moduleRouteListener = new ModuleRouteListener();
    //     $moduleRouteListener->attach($eventManager);
    // //      $eventManager        = $e->getApplication()->getEventManager();
    // // $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
    // //     $controller      = $e->getTarget();
    // //     $controllerClass = get_class($controller);
    // //     $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
    // //     $config          = $e->getApplication()->getServiceManager()->get('config');
    // //     if (isset($config['module_layouts'][$moduleNamespace])) {
    // //         $controller->layout($config['module_layouts'][$moduleNamespace]);
    // //     }
    // // }, 100);
    // // $moduleRouteListener = new ModuleRouteListener();
    // // $moduleRouteListener->attach($eventManager);
    // }
    
    // public function init(ModuleManager $mm)
    // {
    //     $mm->getEventManager()->getSharedManager()->attach(__NAMESPACE__,
    //     'dispatch', function($e) {
            


    //         $e->getTarget()->layout('admin/layout');
    //     $auth = new AuthenticationService();
    //     $storage = $auth->getStorage();
        // print_r($storage->read());die;

        // if (!$auth->hasIdentity()) {
        //     // echo "Fdf";die;
        //     // $e->getRouteMatch()
        //     //         ->setParam('controller', 'Admin\Controller\Dashboard')
        //     //         ->setParam('action', 'index'); 
        // // print_r($auth->getIdentity());die;
        //     return $this->redirect()->toRoute('admin/default',array('controller'=>'dashboard','action'=>'index'));
        // }




        // });



        // $redirect = $this->params()->fromQuery('redirect','');
        
        // echo "gdf"; die;
        // print_r($redirect);die;
        
        // $request  = $this->getRequest();
        // $request->setUri($redirect);

        // if ($routeToBeMatched = $this->getServiceLocator()->get('Router')->match($request)) {
        //     // handle if redirect route = current route
        //         $currentRouteMatchName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
            
        //     if ($routeToBeMatched->getMatchedRouteName() != $currentRouteMatchName) {
                
        //         return $this->redirect()->toRoute($redirect);
            
        //     }
        // }
        // return $this->redirect()->toUrl('/user');



        
    // }

// public function init(ModuleManager $mm)
//     {
//         $mm->getEventManager()->getSharedManager()->attach(__NAMESPACE__,
//         'dispatch', function($e) {
//             $e->getTarget()->layout('admin/layout');
//         });
//     }


    public function init(ModuleManager $moduleManager) {
        $moduleName = $moduleManager->getEvent()->getModuleName();
        if ($moduleName == 'Admin') {
            $events = $moduleManager->getEventManager();
            $sharedEvents = $events->getSharedManager();
            // This define modules need Login
            $sharedEvents->attach(array(__NAMESPACE__, 'Admin', 'Account'), 'dispatch', array($this, 'initAuth'), 100);
            
            $moduleManager->getEventManager()->getSharedManager()->attach(__NAMESPACE__,
                'dispatch', function($e) {
                    $controller = $e->getRouteMatch()->getParam('controller');
                    if($controller != "Admin\Controller\Index")
                        $e->getTarget()->layout('admin/layout');
            });

        }
    }

public function initAuth(MvcEvent $e) {

    //This get router strings
    $routerMatch = $e->getRouteMatch();
    $module = $routerMatch->getMatchedRouteName();
    $controller = $routerMatch->getParam('controller');
    $action = $routerMatch->getParam('action');
    // print_r($controller);die;
    //This get Authenticate Class
     $auth = new AuthenticationService();
    //     $storage = $auth->getStorage();
        // print_r($storage->read());die;

        if ($auth->hasIdentity()) {
            // print_r($auth->getIdentity());die;
            if($controller == 'Admin\Controller\Index')
                { 
                    $controller = $e->getTarget();
                 return $controller->redirect()->toRoute('admin/default',array('controller'=>'dashboard','action'=>'index'));
                }
            else return;
        }
        else {
                if($controller != 'Admin\Controller\Index'){
                    $controller = $e->getTarget();
                    
                    $flashMessenger = $e->getApplication()->getServiceManager()->get('ControllerPluginManager')->get('flashMessenger');
                    
                     $flashMessenger->addMessage(array('type'=>'loginfirst','msg'=>'You need to login first'));   
                    return $controller->redirect()->toRoute('admin/default');
            }
        }

    // This redirect all. but is login interface not 
    // if ($controller != 'Admin\Controller\Login'  && !$auth->isLoggedIn()) {
    //     $controller = $e->getTarget();

    //     return $controller->redirect()->toRoute('admin',array('controller'=>'login','action' => 'index'));
    // }


    // if ($auth->isLoggedIn()) {

    //     $viewModel = $e->getViewModel();
    //     $viewModel->userIdentity = $auth->getIdentity();
    // }
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

     public function getServiceConfig() {
        return array(

            'invokables' => array(

                'cdbMapper' => 'Admin\Model\CommonDbMapper',
                'AdminMaster' => 'Admin\Controller\AppController',

                ),
            'factories' => array(
               
               'Admin\Model\AdminTable' =>   function($sm) {
                 $tableGateway = $sm->get('AdminTableGateway');
                $table = new AdminTable($tableGateway);
               return $table;
            },
                'AdminTableGateway' => function ($sm) {
                 $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                 $resultSetPrototype = new ResultSet();
                 $resultSetPrototype->setArrayObjectPrototype(new Admin());
                 return new TableGateway('admin', $dbAdapter, null, $resultSetPrototype);
             }, 

             'DbAdminMapper' => function ($sm){
                $dbadminmapper = new DbAdminMapper($sm->get('cdbMapper'));
                return $dbadminmapper;
             } 
                
            ),
        );
    }


    public function getViewHelperConfig()
    {
        return array(

                'factories' => array(

                        'DbTableHelper' => function($sm){
                                // $db = $sm->get('Zend\Db\Adapter\Adapter');
                                $helper = new DbTablehelper();
                                return $helper;

                            },
                        'WidgetHelper' => function($sm){
                            $helper = new WidgetHelper();
                            return $helper;
                        },
                        'MsgTemplateHelper' => function($sm){
                            $helper = new MsgTemplateHelper();
                            return $helper;
                        }  

                    ) 


            );

    }


     

}
