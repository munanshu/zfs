<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\LoginForm;
use Admin\Service\UserService;
use Admin\Filter\AbstractFilter;

 
class IndexController extends MasterController
{
	protected $UserService;	
	protected $AuthService;	

	public function __construct($UserService='',$AuthService='')
	{
		$this->UserService = $UserService;
		$this->AuthService = $AuthService;
	}

	public function indexAction()
	{
		$request = $this->getRequest();
		$this->form = new LoginForm();

		if($request->isPost()) {

			$inputfilter = new AbstractFilter();
			$inputfilter->setAuthFilter();
			$this->form->setInputFilter($inputfilter);
			$this->form->setData($request->getPost());
			if($this->form->isValid()){

				$params = $this->form->getData();
				$result = $this->AuthService->authenticate($params['username'],$params['password']);
					
				if(isset($result['error']))
					$this->flashMessenger()->setnamespace('error')->addMessage($result['message']);
				else {
					
					if(isset($result['data'])){

						$userId = $result['data']['user_id'];
						$result['data']['user_id'] = $this->EncryptIdentity($result['data']['user_id'],'userIdentity');
						$result['data']['email'] = $this->EncryptIdentity($result['data']['email'],'userIdentityEmail');
						$result['data']['username'] = $this->EncryptIdentity($result['data']['username'],'userIdentityUsername');
						
						$UserRole = $this->UserService->getUserRole($userId,true);  

						$result['data']['userRole'] = $UserRole;
						$this->AuthService->LoginUser($result['data']);
						$this->flashMessenger()->setnamespace('success')->addMessage('You have logged in successfully');

						$this->redirect()->toRoute('admin/default',array('controller'=>'dashboard'));
					}
					else $this->flashMessenger()->setnamespace('error')->addMessage($result['message']);

				}

			}else  $messages = $this->form->getMessages();

			if(!empty($messages)){

					foreach ($messages as $key => $value) {
						$message = $key." - ".current($value);
						$this->flashMessenger()->setnamespace('error')->addMessage($message);
					}
				}


		}

		return new ViewModel(array('form'=>$this->form));
	}

	public function logoutAction()
	{
		$this->AuthService->logoutUser();
		return $this->redirect()->toRoute('admin/default');
	}


}