<?php
namespace Api;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\InputFilter;
use Api\Response\JsonResponse;
use Api\Controller\AuthController;
use Api\Model\UserTable;
use Api\Mapper\CustomerMapper;

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


    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $router = $e->getApplication()->getServiceManager()->get('router');
        $request = $e->getApplication()->getServiceManager()->get('request');
        // $MatchedRouteName = $router->match($request)->getMatchedRouteName();
        // echo "<pre>"; 
        // print_r($router->match($request));die;

        $routeMatch = $router->match($request);
        
        if(!$routeMatch)
            return;
        
        $controllerName = $routeMatch->getParams()['__NAMESPACE__'];

        $module = explode('\\', $controllerName)[0];

        if( ucfirst($module) != 'Api' )
            return;

         // if( ($pos =strpos($MatchedRouteName, 'api')) != 0 )
         //    return;

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'returnErrorResponce'], 0);
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, [$this, 'returnErrorResponce'], 0);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, function(MvcEvent $e) {

            $routeMatch = $e->getRouteMatch();
            // echo "<pre>"; print_r($routeMatch);die;
            if (!$controller = $routeMatch->getParam('controller', false)) {
                return;
            }

            if (!$action = $routeMatch->getParam('action', false)) {
                return;
            }


            $this->checkContentType($e);

            return $this->ValidateParams($e,$controller,$action);


        }, 0);
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                    'Api\Model\User' => function($sm){

                        $adapter = $sm->get('Zend\Db\Adapter\Adapter');

                        return new UserTable($adapter);
                    },

                    'Api\Mapper\CustomerMapper' =>function($sm)
                    {
                        $adapter = $sm->get('Zend\Db\Adapter\Adapter');

                        return new CustomerMapper($adapter);
                    }
                )
        );
    }

    public function ValidateParams($e,$controller,$action)
    {   
        
        $request = $e->getRequest();
        $httpMethod = $request->getMethod();
        $this->config = $e->getApplication()->getServiceManager()->get('config');
        $response = new JsonResponse();
        // echo "a";die;

         if (!empty($this->config['api']['inputFilters'][$controller][$action])
            && $inputFiltersConfig = $this->config['api']['inputFilters'][$controller][$action]) {
                
                $factory = new InputFilter\Factory();
        // echo $httpMethod;die;
                $inputFilter = $factory->createInputFilter($inputFiltersConfig);

            
                switch ($httpMethod) {
                    case 'GET':
                        $inputFilter->setData($request->getQuery());
                        break;
                    case 'POST':
                        $jsonConverted = $response->isJson($request->getContent());
                        if( is_array($jsonConverted) && isset($jsonConverted['error']))
                            return $response->setContent([
                                'errors' => array('status'=>123,'message'=>$jsonConverted['message'])
                            ]);
                        $inputFilter->setData($jsonConverted);
                        break;
                }
                //validate
                if (!$inputFilter->isValid()) {
                    $errors = [];
                    foreach ($inputFilter->getMessages() as $param => $messages) {
                            // print_r($inputFilter);
                        $errors[] = "$param: ".implode(', ', $messages)."\n";
                    }
                    return $response->setContent([
                        'errors' => array('status'=>123,'message'=>$errors)
                    ]);
                }

            }

    }
    public function checkContentType($e)
    {   
        
        $request = $e->getRequest();
        $httpMethod = $request->getMethod();
        $CONTENT_TYPE = $e->getRequest()->getServer('CONTENT_TYPE');
            $response = new JsonResponse();

            if( $httpMethod == "POST" && strtolower($CONTENT_TYPE) != 'application/json'){

                $response->setContent(array('error'=>array('code'=>412,'message'=>'Invalid content-type')));
                $e->stopPropagation(true);
                $e->setResponse($response);
                $response->SendResponse();
            }
    }


    public function returnErrorResponce(MvcEvent $e)
    {

        $response = new JsonResponse();
        $response->setStatusCode(500);
        if ( $error = $e->getError() ) {
            $exception = $e->getParam('exception');
            $exceptionJson = [];
            if ($exception) {
                // echo $exception->getMessage();die;
                $exceptionMessage = in_array($exception->getCode(), array(0))? 'Invalid Request' :$exception->getMessage(); 
                $exceptionJson = [
                    'message' => $exceptionMessage,
                ];

            }
            $errors = [
                'message'   => 'An error occurred during execution; please try again later.',
                'error'     => $error,
                'exception' => $exceptionJson,
            ];
            if ($error == 'error-router-no-match') {
                $errors['message'] = 'Resource not found';
            }
            $response->setContent($errors);
        }
        $e->stopPropagation(true);
        $e->setResponse($response);
        $response->SendResponse();
    }

}
