<?php
class AdminloginController extends Zend_Controller_Action
{

    public $Model = null;

    public $params = null;

    public function init()
    {
        $this->Model = new Application_Model_Adminlogin();
		$this->params = $this->_request->getParams();
	  
    }

    public function indexAction()
    { //echo "HI";die;
	  global $objSession;
	   try{	
	        $auth = Zend_Auth::getInstance();
	        if($auth->hasIdentity()){ 
			   $this->_redirect('Dashboard');
			}
		
		//$obj->test();
	 }catch(Exception $e){
	    echo $e->getMessage();die;
	 }	
	
        // action body
		if($this->getRequest()->isPost()){
                $authAdapter = new Zend_Auth_Adapter_DbTable($this->Model->getAdapter(),USERS,'username','password');
                $authAdapter->setIdentity($this->params['admin_userid'])
                        	->setCredential(md5($this->params['admin_password']));
                $result = $auth->authenticate($authAdapter);
				if ($result->isValid()) {
					$this->Model->setRememberMe($this->params);
				    $logicSeesion = new Zend_Session_Namespace('logicSeesion');
					$logicSeesion->userconfig = $this->Model->getUserconfigs($authAdapter->getResultRowObject()->user_id);
					if(isset($logicSeesion->userconfig['language_id']) && !empty($logicSeesion->userconfig['language_id'])){
							$logicSeesion->translationArray = $this->Model->getTranslations($logicSeesion->userconfig['language_id']);
					}else{
							$logicSeesion->translationArray = commonfunction::getTranslationArray();
					}
					//Zend_Session::namespaceUnset('logicSeesion');
					if(isset($this->params['url'])){	
						 $this->_redirect(urldecode($this->params['url']));
					}else{
						 $this->_redirect('Dashboard');
					}
					
               }else{
			       Zend_Auth::getInstance()->clearIdentity();
				   $objSession->errorMsg = "Username or password is invalid.";
			   }
	    	}
    }

    public function logoutAction()
    {
        $auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		Zend_Session::namespaceUnset('logicSeesion');
		$this->_redirect(BASE_URL);
    }
	
	public function fronttrackingAction()
    { 
        global $objSession;
	  if($this->getRequest()->isPost()){
		 if(isset($this->params['barcode']) && !empty($this->params['barcode']) && isset($this->params['submit']) && $this->params['submit']=='Track Parcel' )
		 { 
		 $result = $this->Model->getTraceParcel($this->params['barcode']);
		if(!empty($result['barcode_id'])) {
		 echo '<script type="text/javascript">window.open("'.BASE_URL.'/Parceltracking/tracking/tockenno/'.Zend_Encript_Encription::encode($result['barcode_id']).'");                            window.parent.location.href = "'.BASE_URL.'"; </script>';
		 die;
		 }else {
		    $objSession->infoMsg = "Invalid Tracking";
			$this->_redirect('/');
		}
	   }
	  }
   }
    public function b2ctrackAction() {
		 if(isset($this->params['barcode']) && !empty($this->params['barcode']))
		 { 
		   $result = $this->Model->getTraceParcel($this->params['barcode']);
			if(!empty($result['barcode_id'])) {
			   $ipadd = (!empty($this->params['accessfrom']))?"/accessfrom/".$this->params['accessfrom']:'';
			   $this->_redirect(BASE_URL.'/Parceltracking/tracking/tockenno/'.Zend_Encript_Encription::encode($result['barcode_id']).$ipadd);	
			 }else {
			   $this->_redirect(BASE_URL.'/Parceltracking/tracking/tockenno/'.base64_encode(1).$ipadd);
			}
	   }
	}
	
	public function checkuseridAction() {
   		$user_data = $this->Model->checkusernamedeport($this->params);
	    if($user_data['parent_id'] != "") {
		  echo $user_data['parent_id']; exit;
	    }else{
		 exit;
	    }
	  }
     public function getdeportmassageAction(){
    	$this->_helper->layout->setLayout('popup');
	    $print_message = '';
		$data = $this->Model->checkusernamedeport($this->params);
		if (isset($data['message']) && !empty($data['message'])) {
			$print_message = '<table border="1" width="100%" height="100%"><tr><td>';
			$print_message.= $data['message'];
			$print_message.='</td></tr></table>';
			echo $print_message;
			exit;
		} else {
			$error_flag = "No terms and Condition to this Depot User.";
			echo $error_flag;
			exit;
		}
   }
  
     public function forgetpasswordAction(){
		 $this->_helper->layout->setLayout('layout');
		  global $objSession;
		  if($this->getRequest()->isPost() && isset($this->params['email']) && !empty($this->params['email'])){
			 $result = $this->Model->getTextPassword($this->params);
			if($result) {
			 	$objSession->infoMsg = "Your watchword has been Send to your Email Address.";
			 }else {
			 	$objSession->infoMsg = "Your entered email is not available.";
			} 
		  }
 	 }

}



