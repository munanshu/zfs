<?php

class Planner_DriverreportController extends Zend_Controller_Action
{

    public $Request = array();

    public $ModelObj = null;

    public function init()
    {
       try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Planner_Model_Driverreport();
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

    public function loginreportAction()
    {
        // action body
    }

    public function vehiclereportAction()
    {
        // action body
    }

    public function pickupreportAction()
    {
        // action body
    }

    public function deliveredreportAction()
    {
        // action body
    }

    public function leavereportAction()
    {
        // action body
    }


}









