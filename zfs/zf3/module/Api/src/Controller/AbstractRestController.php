<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Json\Json;
use Api\Response\JsonResponse; 

class AbstractRestController extends AbstractActionController
{

    protected $model;
    protected $apiParams;
    protected $ApiauthService;
    protected $UserService;
    protected $CustomerService;
    private $ServiceManager;

    public function setModel(ModelInterface $model)
    {
        $this->model = $model;
    }

    public function setParams()
    {
        $httpMethod = $this->getRequest()->getMethod();

        switch ($httpMethod) {
            case 'GET':
                $this->apiParams = $this->getRequest()->getQuery();
                break;

            case 'POST':
                $this->apiParams = $this->getRequest()->getContent();
                $this->apiParams = Json::decode($this->apiParams,true);
                break;

            default:
                $this->throwError(500,'method not allowed');
                break;
        }

    }

    public function setDataToIndex()
    {
        $data = array();

        if(!empty($this->apiParams)){

            $data['id'] = (isset($this->apiParams['id']) && !empty($this->apiParams['id']) )?  $this->apiParams['id'] : null ;
            $data['limit'] = (isset($this->apiParams['limit']) && !empty($this->apiParams['limit']) )?  $this->apiParams['limit'] : null ;
            $data['page'] = (isset($this->apiParams['page']) && !empty($this->apiParams['page']) )?  $this->apiParams['page'] : null ;
            $data['firstname'] = (isset($this->apiParams['firstname']) && !empty($this->apiParams['firstname']) )?  $this->apiParams['firstname'] : null ;
            $data['lastname'] = (isset($this->apiParams['lastname']) && !empty($this->apiParams['lastname']) )?  $this->apiParams['lastname'] : null ;
            $data['code'] = (isset($this->apiParams['code']) && !empty($this->apiParams['code']) )?  $this->apiParams['code'] : null ;
            $data['email'] = (isset($this->apiParams['email']) && !empty($this->apiParams['email']) )?  $this->apiParams['email'] : null ;
            $data['mobile'] = (isset($this->apiParams['mobile']) && !empty($this->apiParams['mobile']) )?  $this->apiParams['mobile'] : null ;
            $data['addressline1'] = (isset($this->apiParams['addressline1']) && !empty($this->apiParams['addressline1']) )?  $this->apiParams['addressline1'] : null ;
            $data['pincode'] = (isset($this->apiParams['pincode']) && !empty($this->apiParams['pincode']) )?  $this->apiParams['pincode'] : null ;
            $data['addressline2'] = (isset($this->apiParams['addressline2']) && !empty($this->apiParams['addressline2']) )?  $this->apiParams['addressline2'] : null ;

            $this->ApiParamData = $data;
        }

    }
     
    public function onDispatch(MvcEvent $e)
    {
        try {

            $this->setParams();
            $this->setServiceLocator($e);
            $this->getApiauthService();
            $this->setDataToIndex();
            parent::onDispatch($e);

        } catch (RestException $e) {
            return $this->notFound([
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {

            return $this->throwError(404,$e->getMessage());

        }

    }


    protected function ok($data = null,$code=200)
    {

        $response = new JsonResponse();
        $response->setStatusCode($code);
        $data = array('response'=>array('status'=>$code,'data'=>$data));
        $response->setContent($data);
        $response->SendResponse();
    }

    

    public function notFoundAction($data = null)
    {
        $this->throwError(404,'Invalid Request');
    }

    protected function throwError(int $code,$msg=null)
    {
        $response = new JsonResponse();
        $response->setStatusCode($code);
        $data = array('errors'=>array('status'=>$code,'message'=>$msg));
        $response->setContent($data);
        $response->SendResponse();
    }

    public function setServiceLocator($e)
    {
        $this->ServiceManager = $e->getApplication()->getServiceManager();
    }

    public function getServiceLocator()
    {
        return $this->ServiceManager;
    }

    public function getApiauthService()
    {

        $this->ApiauthService = $this->getServiceLocator()->get('Admin\Service\AuthService');
        $this->ApiauthService->getApiParams = $this->apiParams;

    }

    public function getUserService()
    {
        $this->UserService = $this->getServiceLocator()->get('Api\ServiceFactory\User');
    }

    public function getBearerToken()
    {
    
        $data = $this->getRequest()->getHeader('Authorization');
        if($data){
            $data = $data->getFieldValue();
            if(preg_match('/Bearer .*/', $data,$matches) !== false){
                if($matches){
                    $data = str_replace('Bearer ', '', $data);
                    return $data;
                }
            }
            else return false;
            
        }else return false;

    }

    public function getCustomerService()
    {
       $this->CustomerService = $this->getServiceLocator()->get('Api\CustomerMapperFactory');
       $this->CustomerService->ServiceParams = $this->ApiParamData;
    }

    public function IsTokenValid($token='')
    {
         $token = $this->getBearerToken();

        if($token){

            $this->getApiauthService();
            $result = $this->ApiauthService->matchToken($token);

            if(!isset($result['error']) && (isset($result['data']) && !empty($result['data'])) ){

                    return true; 

                }else $this->throwError(404,$result['message']);

        }else $this->throwError(404,'Invalid Token');
    }

}