<?php
class ChatsystemController extends Zend_Controller_Action
{
	public $ModelObj = null;
    private $Request = array();
    
	public function init()
    {
      		$this->_helper->layout->setLayout('popup');
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Application_Model_Shopapi();
			$this->ModelObj->getData  = $this->Request;
			$this->view->Request = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
    }

    public function indexAction()
    {
        // action body
    }
	
	
	public function chatAction(){
	     
	}
}

