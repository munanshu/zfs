<?php 
ini_set("memory_limit", "-1");
set_time_limit(0);
//ini_set('display_errors','1');
class Settings_RoutingController extends Zend_Controller_Action

{



    public $Request = array();



    public $ModelObj = null;



    public function init()

    {

        try{	

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new settings_Model_Routing();

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



    public function routinglistAction()

    {

       $this->view->records = $this->ModelObj->getRoutings();

	   $this->view->countrylist =  $this->ModelObj->getCountryList();

	   $this->view->depotlist =  $this->ModelObj->getDepotList();

	   $this->view->forwarders = $this->ModelObj->getForwarderList();

	   $this->view->servicelist =  $this->ModelObj->getCustomServiceList();

    }



    public function weightclassesAction()

    {

        $this->view->records = $this->ModelObj->getweightClass();

		$this->view->countrylist =  $this->ModelObj->getCountryList();

	    $this->view->depotlist =  $this->ModelObj->getDepotList();

		$this->view->servicelist =  $this->ModelObj->getCustomServiceList();

    }



    public function addweightclassAction()

    {

         $this->_helper->layout->setLayout('popup');

		 if($this->_request->isPost()){

		   		$this->ModelObj->CreateWeightClass();

				echo '<script type="text/javascript">parent.window.location.reload();parent.jQuery.fancybox.close();</script>';exit();

		 }

		 $this->view->countrylist =  $this->ModelObj->getCountryList();

		 $this->view->servicelist =  $this->ModelObj->getAllServicesList();

		 $this->view->totalclass = array();//$this->ModelObj->getweightClass();

    }



    public function addroutingAction()

    {

       // $this->_helper->layout->setLayout('popup');

		if($this->_request->isPost()){

		     //echo "<pre>";print_r($this->Request);die;

		     $this->ModelObj->AddRouting();

			 //echo '<script type="text/javascript">parent.window.location.reload();parent.jQuery.fancybox.close();

			 $this->_redirect($this->_request->getControllerName().'/routinglist');

		}

		

		$this->view->countrylist =  $this->ModelObj->getCountryList();

    }



    public function editroutingAction()

    {

       $this->_helper->layout->setLayout('popup');

	   if($this->_request->isPost()){

		     $this->ModelObj->EditRouting();

			 echo '<script type="text/javascript">window.top.location.href = "'.BASE_URL.'/'.$this->_request->getControllerName().'/routinglist";parent.jQuery.fancybox.close();</script>';exit();

		}

	   $this->view->record = $this->ModelObj->getRoutingByRoutingID();

	   $this->view->forwarders = $this->ModelObj->getForwarderList();

    }



    public function customerpriceAction()

    {

       $this->_helper->layout->setLayout('popup');

	   if($this->_request->isPost()){

		     $this->ModelObj->UpdateCustomer();

			 echo '<script type="text/javascript">parent.window.location.reload();parent.jQuery.fancybox.close();</script>';exit();

		}

	   $this->view->record = $this->ModelObj->getRoutingByRoutingID();

    }



    public function specialpriceAction()

    {

        $this->_helper->layout->setLayout('popup');

		if($this->_request->isPost() && isset($this->Request['btnsubmit'])){

		     $this->ModelObj->UpdateSpecial();

			 echo '<script type="text/javascript">parent.window.location.reload();parent.jQuery.fancybox.close();</script>';exit();

		}

		if(isset($this->Request['user_id'])){

		  $this->view->specialprice = $this->ModelObj->getSpecialPrices();

		}

		$this->view->specialpricecustomer = $this->ModelObj->getSpecialPriceCustomers();

		$this->view->record = $this->ModelObj->getRoutingByRoutingID();

    }

	

	public function deleteweightclassAction(){

	     $this->ModelObj->deleteclass();

		 exit();

	}

	public function deleteweightroutingAction(){

	    $this->ModelObj->deleterouting();

		 exit();  

	}

	public function updatepriceAction(){

		if(isset($this->Request['update'])){ 

		   $this->ModelObj->UpdateCustomer(); 

		   // $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());

		}

	    if(isset($this->Request['country_id']) && isset($this->Request['service_id'])){

		   $this->view->serviceweightClass = $this->ModelObj->serviceweightClass(); 

		}	

	   $this->view->countrylist =  $this->ModelObj->getCountryList();

	   $this->view->servicelist =  $this->ModelObj->getAllServicesList();

	}

	public function updatepricespecialAction(){

	    if(isset($this->Request['update'])){ 

		   $this->ModelObj->UpdateSpecial(); 

		}

	    if(isset($this->Request['country_id']) && isset($this->Request['service_id']) && isset($this->Request['user_id'])){

		   $this->view->serviceweightClass = $this->ModelObj->servicespecialPrice(); 

		}	

	   $this->view->specialpricecustomer = $this->ModelObj->getSpecialPriceCustomers();	

	   $this->view->countrylist =  $this->ModelObj->getCountryList();

	   $this->view->servicelist =  $this->ModelObj->getAllServicesList();

	}

	

	public function importAction(){

	  /*if($this->_request->isPost() && isset($this->Request['import_routing'])){

	      $this->ModelObj->ImportDepotRouting();        

	  

	  

	  }

	  $this->view->servicelist =  $this->ModelObj->getAllServicesList();  */ 

	  if($this->_request->isPost() && isset($this->Request['import_exp'])){

	       $this->ModelObj->ImportExportRouting();       

	  }  

	  $this->view->depotlist =  $this->ModelObj->getDepotList();

	  $this->view->specialpricecustomer = $this->ModelObj->getSpecialPriceCustomers();	

	  

	  

	}

}































