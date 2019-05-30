<?php
ini_set('display_errors','1');
class Seafreight_SeafreightController extends Zend_Controller_Action
{
	public $ModelObj = NULL;
    private $Request = array();
	
    public function init()
    {
        try{	
			$this->_helper->layout->setLayout('main');
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Seafreight_Model_Searfreight();
			$this->ModelObj->getData  = $this->Request;
			$this->view->Request = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
	 }catch(Exception $e){
	    echo $e->getMessage();die;
	 }	
    }

    public function indexAction()
    {
        // action body
    }

    public function routingAction()
    {
         $this->view->records =  $this->ModelObj->getRoutingList();
		 $this->view->countrylist =  $this->ModelObj->getCountryList();
	     $this->view->depotlist =  $this->ModelObj->getDepotList();
    }
	
	public function addroutingAction(){
	    if($this->_request->isPost() && isset($this->Request['add_routing'])){
		   $this->ModelObj->AddRouting();
		   $this->_redirect($this->_request->getControllerName().'/routing');
		}
		$this->view->countrylist =  $this->ModelObj->getCountryList();  
		$this->view->portlist =  $this->ModelObj->getPortList();  
	}
	
	public function editroutingAction(){
	     if($this->_request->isPost() && isset($this->Request['update_routing'])){
		   $this->ModelObj->EditRouting();
		   $this->_redirect($this->_request->getControllerName().'/routing');
		}
	     $this->view->records =  $this->ModelObj->getRoutingDetail();
	     $this->view->portlist =  $this->ModelObj->getPortList();
	}
	
	public function customerpriceAction(){
	  $this->_helper->layout->setLayout('popup');
	  if($this->_request->isPost() && isset($this->Request['update_price'])){
	  	$this->ModelObj->UpdatePrice(1);
		echo '<script type="text/javascript">parent.window.location.reload();parent.jQuery.fancybox.close();</script>';exit();
	  }
	  $this->view->records =  $this->ModelObj->getRoutingDetail();
	}
	
	public function specialpriceAction(){
	   $this->_helper->layout->setLayout('popup');
	    if($this->_request->isPost() && isset($this->Request['update_price'])){
			$this->ModelObj->UpdatePrice(2);
			echo '<script type="text/javascript">parent.window.location.reload();parent.jQuery.fancybox.close();</script>';exit();
	  	}
	   $this->view->records =  $this->ModelObj->getRoutingDetail();
	   $this->view->specialpricecustomer =  $this->ModelObj->getSpecialPriceCustomers();
	}


}



