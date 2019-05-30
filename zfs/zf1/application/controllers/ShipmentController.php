<?php
 
class ShipmentController extends Zend_Controller_Action
{

    public $ModelObj = null;

    private $Request = array();

    public function init()
    {
        /* Initialize action controller here */ 
		try{	
			$this->_helper->layout->setLayout('main');
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Application_Model_Shipments();
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

    public function addshipmentAction()
    {
//Zend_Encript_Encription::encode('Hello');
		 global $objSession,$labelObj;
		  if(isset($objSession->AddSipmentLabel)){
		     $labelObj->_filePath = $objSession->AddSipmentLabel; 
		     unset($objSession->AddSipmentLabel);
	         $labelObj->printLabel();
		  }
        // action body
		 if($this->_request->IsPost()){
		 	//$this->ModelObj->shipment_type = 0;
		    $this->ModelObj->addShipment();
			$this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());  
		 }
		 $this->view->customerlist = $this->ModelObj->getCustomerList();
	     $this->view->countrylist =  $this->ModelObj->getCountryList();
    }
	public function customerdeclarationAction()
    {
        $this->_helper->layout->setLayout('popup');
		 if($this->_request->IsPost() && empty($this->Request['shipment_id'])){
		    // echo "1";die;
		    $this->ModelObj->shipment_type = 0;
		    $this->ModelObj->addShipment();
			echo '<script type="text/javascript">window.top.location.href = "'.BASE_URL.'/Shipment/addshipment";parent.jQuery.fancybox.close();</script>';exit();
		 }
		 if($this->_request->IsPost() && isset($this->Request['shipment_id']) &&!empty($this->Request['shipment_id'])){
		    // echo "2";die;

		    $this->view->records = $this->ModelObj->getShipmentById(); 
		    $this->ModelObj->shipment_type = 0;
		    $this->ModelObj->editShipment($this->view->records);
			echo '<script type="text/javascript">window.top.location.href = "'.BASE_URL.'/Shipment/addshipment";parent.jQuery.fancybox.close();</script>';exit();
		 }
		 if(isset($this->Request['shipment_id']) &&!empty($this->Request['shipment_id'])){

		 	$this->view->Declaration =  $this->ModelObj->getShipmentById(); 
		 }

		 // echo "<pre>"; print_r($this->Request); die;
		 // action body
		 
		 $this->view->countrylist =  $this->ModelObj->getCountryList();
		 $this->view->goodslist   =  $this->ModelObj->getGoodsCategory();
		 if(isset($this->Request['addservice_id']) && ($this->Request['addservice_id']==126 || $this->Request['addservice_id']==149 || $this->Request['addservice_id']==142)){
		 	$commonObj =  new Application_Model_Common();
		    $this->view->parcelpointlist   =  $commonObj->getParcelshopList($this->Request['country_id'],$this->Request[ZIPCODE]);
		 }
		 if(isset($this->Request['service_id']) && $this->Request['service_id']==139){
		    $commonObj =  new Application_Model_Common();
		    $this->view->portlist   =  $commonObj->portlist($this->Request);
		 }
		  //$this->ModelObj->CountryInfomations();
		 
    }
	public function editshipmentAction()
    {
          
		 $this->view->records = $this->ModelObj->getShipmentById(); 
        // action body
		 if($this->_request->IsPost()){
		 	//$this->ModelObj->shipment_type = 0;
		    $this->ModelObj->editShipment($this->view->records);
			$this->_redirect($this->_request->getControllerName().'/showallparcel');  
		}
	   //$this->view->records = $this->ModelObj->getShipmentById();
	   $this->view->customerlist = $this->ModelObj->getCustomerList();
	   $this->view->countrylist =  $this->ModelObj->getCountryList();
    }

    public function newshipmentAction()
    {
         /*global $objSession,$labelObj;
		  if(isset($objSession->AddSipmentLabel)){
		     $labelObj->_filePath = $objSession->AddSipmentLabel; 
		     unset($objSession->AddSipmentLabel);
	         $labelObj->printLabel();
		  }
        // action body
		 if($this->_request->IsPost()){
		 	$this->ModelObj->shipment_type = 0;
		    $this->ModelObj->addShipment();
			$this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());  
		 }*/
		$this->view->records = $this->ModelObj->getNewShipment();
		$this->view->customerlist = $this->ModelObj->getCustomerList();
    }

    public function showallparcelAction()
    {  
		 global $objSession,$labelObj;
		  if(isset($objSession->AddSipmentLabel)){
		     $labelObj->_filePath = $objSession->AddSipmentLabel; 
		     unset($objSession->AddSipmentLabel);
	         $labelObj->printLabel();
		  }
        if($this->_request->isPost() && isset($this->Request['shipment_mode']) && isset($this->Request['shipment_id'])){
        	// echo "<pre>"; print_r($this->Request);die;
		   $result = $this->ModelObj->PrintAction();
		   if($result==false){
		   	global $objSession;
		   	$objSession->errorMsg = "No eligible for this continent";
		   }
		}elseif($this->_request->isPost() && ( isset($this->Request['BulkShipping']) || isset($this->Request['BulkPrint']) || isset($this->Request['BulkProformaPrint']) ) ){
		   $result = $this->ModelObj->BulkPrinting();
		   if($result==false){
		   	global $objSession;
		   	$objSession->errorMsg = "No eligible for this continent";
		   }

		}/*elseif($this->_request->isPost() && isset($this->Request['shipment_mode']) && !isset($this->Request['shipment_id'])){
		     $objSession->errorMsg = "Please Select row(s)";
		}*/
		$this->view->records = $this->ModelObj->getShowAllShipments();
		$this->view->countrylist =  $this->ModelObj->getCountryList();
	    $this->view->depotlist =  $this->ModelObj->getDepotList();
		$this->view->customerlist =  $this->ModelObj->getCustomerList();
		$this->view->forwarderlist =  $this->ModelObj->getForwarderList();
		$this->view->servicelist =  $this->ModelObj->getCustomServiceList();
		$this->view->addedtype = $this->ModelObj->ParcelAddedType();
    }

    public function deletedshipmentAction()
    {	

    	if(isset($this->Request['export'])){

    		 $this->view->csvrecords = $this->ModelObj->getdeletedshipment();

    		 $this->ModelObj->ExportDeletedShipments($this->view->csvrecords);	
    		 // echo "<pre>";
    		 die;
    	}

    	if(isset($this->Request['revertall']) || isset($this->Request['reverthis']) ){
			global $objSession;
    		
    		if((isset($this->Request['shipment_id']) && !empty($this->Request['shipment_id'])) ||  
    		   (isset($this->Request['reverthis']) && !empty($this->Request['reverthis'])) ){
    				$this->ModelObj->reverttoshipment($this->Request);
		 			$objSession->successMsg = "Parcel sucessfully added back to shipment";
		 			unset($this->Request['reverthis']);
		 			unset($this->Request['bar']);
    		}
		     else $objSession->errorMsg = "Please Select Record(s) to Revert";
		       			 
    	}

    	 $this->view->records = $this->ModelObj->getdeletedshipment();

    	 $this->view->countries = $this->ModelObj->getCountryList();
    	 $this->view->forwarders = $this->ModelObj->getForwarderList();
    	 $this->view->customers = $this->ModelObj->getCustomerList();
    }

    public function revertwithcheckinAction()
    {

    	$res = $this->ModelObj->revertparcelwithcheckin(); 
    	global $objSession;
    	if($resp['status'] == 1)
    		$objSession->successMsg = $resp['message'];
    	else $objSession->errorMsg = $resp['message'];
    	 $this->_redirect('Shipment/deletedshipment');
    }

    public function shipmenthistoryAction()
    {
        // action body
		if(isset($this->Request['export']) && $this->Request['export']!=''){
		      $this->ModelObj->ExportHistory();
		}
		$this->view->records = $this->ModelObj->getShipmentHistory();
		$this->view->countrylist =  $this->ModelObj->getCountryList();
	    $this->view->depotlist =  $this->ModelObj->getDepotList();
		$this->view->customerlist =  $this->ModelObj->getCustomerList();
		$this->view->forwarderlist =  $this->ModelObj->getForwarderList();
		$this->view->servicelist =  $this->ModelObj->getCustomServiceList();
		$this->view->addedtype = $this->ModelObj->ParcelAddedType();
    }

    public function viewshipmentAction()
    {
        // action body
		$this->_helper->layout->setLayout('popup');
		global $objSession,$labelObj;
		  if(isset($objSession->AddSipmentLabel)){
		     $labelObj->_filePath = $objSession->AddSipmentLabel; 
		     unset($objSession->AddSipmentLabel);
	         $labelObj->printLabel();
		  }
        if($this->_request->isPost() && isset($this->Request['shipment_mode']) && isset($this->Request['shipment_id'])){
		   $this->ModelObj->PrintAction();
		}elseif($this->_request->isPost() && (isset($this->Request['BulkShipping']) || isset($this->Request['BulkPrint']))){
		   $this->ModelObj->BulkPrinting();   
		}
		$this->view->records = $this->ModelObj->getShowAllShipments();
		$this->view->countrylist =  $this->ModelObj->getCountryList();
		$this->view->forwarderlist =  $this->ModelObj->getForwarderList();
    }

    public function importshipmentAction()
    {
        if($this->_request->isPost() && (!empty( $this->Request['import']) || !empty( $this->Request['importwithHeader']))){
		   $error_data = $this->ModelObj->importShipment(1);
		   $this->_redirect($this->_request->getControllerName().'/importlist'); 
		}
		$this->view->customerlist = $this->ModelObj->getCustomerList();
    }
	public function importlistAction(){ 
	     global $objSession;
		if($this->_request->isPost() && isset($this->Request['change'])){
		   if(isset($this->Request['shipment_id']) && !empty($this->Request['shipment_id'])){
		      $this->ModelObj->ChangeWeightAndService();
		   }else{
		     $objSession->errorMsg = "Please Select Record(s) to change Weight or service";
		   }
		   //print_r($this->Request);die;
		}
		 if($this->_request->isPost() && (isset($this->Request['shipment_mode']) && isset($this->Request['shipment_id'])) || isset($this->Request['shipment_mode1'])){ 
		   $this->ModelObj->importPrint();
		   $this->_redirect($this->_request->getControllerName().'/importlist'); 
		}
		$this->view->importlist = $this->ModelObj->getImportShipmentList();
		$this->view->countrylist =  $this->ModelObj->getCountryList();
		$this->view->depotlist =  $this->ModelObj->getDepotList();
		$this->view->customerlist =  $this->ModelObj->getCustomerList();
		$this->view->forwarderlist =  $this->ModelObj->getForwarderList();
		$this->view->serviceList =  $this->ModelObj->getCustomServiceList();
	}
    public function showaddbookAction()
    {
       $this->_helper->layout->setLayout('popup');
	   $this->view->records = $this->ModelObj->AddressBookList();
	   $this->view->countrylist =  $this->ModelObj->getCountryList();
    }
	public function shipmentvalidateAction(){

		// echo "<pre>"; print_r($this->Request);die;
	     $this->ModelObj->ErrorCheck(1);die;
	}
	public function importsampleAction(){
	    $csvheaders = $this->ModelObj->getImportSample();
		$header = array();
		foreach($csvheaders as $csvheader){
		  $header[] = $csvheader;
		}
		commonfunction::ExportCsv(commonfunction::implod_array(array_reverse($header),';'),'Import_sample');
	}
	public function importerrorAction(){
	     $this->view->records = $this->ModelObj->getImportErrorList();
	}
	
	public function referenceshipmentAction(){
	  global $objSession,$labelObj;
	  if(isset($objSession->AddSipmentLabel)){
		 $labelObj->_filePath = $objSession->AddSipmentLabel; 
		 unset($objSession->AddSipmentLabel);
		 $labelObj->printLabel();
	  }
	  if($this->_request->isPost() && isset($this->Request['rec_reference']) && isset($this->Request['user_id']) ){
	      $obj = new Application_Model_Common();
		  $obj->ReferenceShipment($this->ModelObj);
		  $this->_redirect($this->_request->getControllerName().'/referenceshipment'); 
	  }
	  $this->view->customerlist =  $this->ModelObj->getCustomerList();
	}
	
	public function refshipmentlistAction(){
	    global $objSession,$labelObj;
		  if(isset($objSession->AddSipmentLabel)){
		     $labelObj->_filePath = $objSession->AddSipmentLabel; 
		     unset($objSession->AddSipmentLabel);
	         $labelObj->printLabel();
		  }
        if($this->_request->isPost() && isset($this->Request['shipment_mode']) && isset($this->Request['shipment_id'])){
		   $this->ModelObj->PrintAction();
		}elseif($this->_request->isPost() && (isset($this->Request['BulkShipping']) || isset($this->Request['BulkPrint']))){
		   $this->ModelObj->BulkPrinting();   
		}/*elseif($this->_request->isPost() && isset($this->Request['shipment_mode']) && !isset($this->Request['shipment_id'])){
		     $objSession->errorMsg = "Please Select row(s)";
		}*/
		$this->view->records = $this->ModelObj->getShowAllShipments();
		$this->view->countrylist =  $this->ModelObj->getCountryList();
	    $this->view->depotlist =  $this->ModelObj->getDepotList();
		$this->view->customerlist =  $this->ModelObj->getCustomerList();
		$this->view->forwarderlist =  $this->ModelObj->getForwarderList();
		$this->view->servicelist =  $this->ModelObj->getCustomServiceList();
		$this->view->addedtype = $this->ModelObj->ParcelAddedType();
	}
}



















