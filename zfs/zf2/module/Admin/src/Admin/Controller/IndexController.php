<?php

namespace Admin\Controller;

use Admin\Form\AdminForm;
use Admin\Model\Entity\Admin;
use Admin\Model\CommonDbMapper as DbMapper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;


class IndexController extends AppController
{

    public function indexAction()
    {	
    	$messages = "";
    	$adminform = new AdminForm();
    	$request = $this->getRequest();
    	if($request->isPost()){
    		$data = $request->getPost();
    		$inputfilter = new Admin();
    		$adminform->setInputFilter($inputfilter->getInputFilter());
    		$adminform->setData($data);

    		$sm = $this->getServiceLocator();
    		$DbAdapter = $sm->get( 'Zend\Db\Adapter\Adapter' );

    		if($adminform->isValid()){

    		 	$authform_data = $adminform->getData();
                $config = $sm->get('config');
                $salt = $config['static_salt'];

                // $dynamicSalt = '';
                // for ($i = 0; $i < 50; $i++) {
                //     $dynamicSalt .= chr(rand(33, 126));
                // }

                // $dynamicSalt = $dynamicSalt;
                // echo sha1('zf2')."<br>";
                // echo md5(sha1('zf2').$authform_data['password']);die;
                // print_r($salt);die;
    			// $forminputpass = md5($authform_data['password']);
    			
    			$authAdapter = new  AuthAdapter($DbAdapter,
    				'admin',
    				'username',
    				'password', 
    				"MD5(CONCAT('$salt', ?))"
    				);
    			 $authAdapter->setIdentity($authform_data['username'])->setCredential($authform_data['password']);
  				 $auth = new AuthenticationService();
                 $select = $authAdapter->getDbSelect();
                 $select->where('status = 1 AND email_confirmed = 1');
                 // $select->where('');
  				 $result = $auth->authenticate($authAdapter);

                 // $res = $authAdapter->getResultRowObject();
  				  // print_r($result);die;
  				 switch ($result->getCode()) {

			    case Result::FAILURE_IDENTITY_NOT_FOUND:
			        $messages = array('status' => 0,'message'=>'This identity does not exists ');
			        break;

			    case Result::FAILURE_CREDENTIAL_INVALID:
			        /** do stuff for invalid credential **/
                    $messages = array('status' => 0,'message'=>'password is not valid ');

			        break;

			    case Result::SUCCESS:
			        /** do stuff for successful authentication **/
						$storage = $auth->getStorage();
						$storage->write($authAdapter->getResultRowObject(
						null,
						'password'
           		 	));
			       	 // print_r($storage->read());die;
             		 //  $userInfo = Zend_Auth::getInstance()->getStorage()->read();
              		 // print_r($userInfo);die;

                     $this->flashMessenger()->addMessage(array('type'=>'dashboard','msg'=>'welcome my frnd welcome'));   
                     // print_r($this->flashMessenger()->getMessages());die;
					return $this->redirect()->toRoute('admin/default',array('controller'=>'dashboard'));
			        break;

			    default:
			        /** do stuff for other failure **/
			        break;
				}


				// foreach ($result->getMessages() as $key => $value) {

				// 	$messages[] = $value;

				// }
    //             print_r($messages);die;


    		}	
    		else {
	    		$messages = $adminform->getMessages();
    		}
    	
    	}

				// print_r($messages);die;
        $this->layout('layout/default');    
        // $view->setTemplate('main/layout'); 
        return new ViewModel(array('form'=>$adminform,'messages'=>$messages));
    }


    public function PracticeAction()
    {
            $tgw = $this->getServiceLocator()->get('DbAdminMapper');
            print_r($tgw) ;die;

    }

     


}

