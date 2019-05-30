<?php
class Settings_ForwarderinvoicecheckinController extends Zend_Controller_Action

{

	public $Request = array();

    public $ModelObj = NULL;

    public function init()

    { 

       try{	

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new settings_Model_Forwarderinvoicecheckin();

			$this->ModelObj->getData  = $this->Request;

			$this->view->ModelObj = $this->ModelObj;

			$this->view->Request =  $this->Request;

			$this->_helper->layout->setLayout('main');

	  }catch(Exception $e){

	    echo $e->getMessage();die;

	  }

    }



    public function indexAction()

    {

        // action body

    }



    public function checkinvoiceAction()

    { 

        if($this->_request->isPost() && $this->Request['import']){

		    $this->ModelObj->CheckforwarderInvoice(); 

		}

		$this->view->forwarderlist =  $this->ModelObj->getForwarderList();

    }





}







