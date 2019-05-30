<?php

class Hubcheckin_HubcheckinController extends Zend_Controller_Action
{

    public $Request = array();
    public function init()
    {
        try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Hubcheckin_Model_Hubcheckin();
			$this->ModelObj->getData  = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
			$this->_helper->layout->setLayout('main');
	 }catch(Exception $e){
	    echo $e->getMessage();die;
	 }
    }

    public function indexAction()
    {
        // action body
    }

    public function forwardermanifestAction()
    {
        // action body
    }

    public function batchchekinAction()
    {
        // action body
    }

    public function checkinAction()
    {
        // action body
    }

    public function singlescanAction()
    {
        // action body
    }


}









