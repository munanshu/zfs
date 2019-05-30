<?php

class Shipmentmanager_ShipmentmanagerController extends Zend_Controller_Action

{



    public $Request = array();

    public function init()

    {

        try{	

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new Shipmentmanager_Model_Shipmentmanager();

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



    public function pickrequestAction()

    {	$recordArr = array();

		$parcelSchedule  = $this->ModelObj->getPickRequestList();

		$recordArr = array_merge($recordArr,$parcelSchedule);

		$manualSchedule    = $this->ModelObj->ManualschedulePickup($recordArr);

		$recordArr = array_merge($recordArr,$manualSchedule);

		$pickupdate =array();

		$pickuptime =array();

		$createdate =array();

		foreach($recordArr as $key => $row) { 

				$pickupdate[$key]  = $row['pickup_date'];

				$pickuptime[$key]  = $row['pickup_time'];

				$createdate[$key]  = $row['create_date'];

           } 

		array_multisort($pickuptime, SORT_ASC, $pickupdate, SORT_ASC,$createdate,SORT_DESC, $recordArr); 

        $this->view->PickRequestList = $recordArr;

		$paginator = Zend_Paginator::factory($recordArr);

        $currentPage = isset($this->Request['page']) ? (int) htmlentities($this->Request['page']) : 1;

        $paginator->setCurrentPageNumber($currentPage);

        $itemsPerPage = isset($this->Request['count']) ? (int) htmlentities($this->Request['count']) : 100;

        $paginator->setItemCountPerPage($itemsPerPage);

        $this->view->page = $paginator->getPages();

		$this->view->customerList = $this->ModelObj->getCustomerList();

    }

	

	

	public function returnshipmentAction()

    {	global $objSession; 

		if(isset($this->Request['mode']) && $this->Request['mode']=='delete'){

		 $result = $this->ModelObj->deleteReturnShipment();

		 if($result){

		 $objSession->successMsg = "Return Shipment Successfully Deleted!!";

		 }else{

			$objSession->errorMsg = "Something is wrong !!";

		 }

		 $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());

		}

        $this->view->ReturnShipmentList = $this->ModelObj->getReturnShipmentList();

    }

	

	public function customerscanAction()

    { 	
		global $objSession; 
	    if($this->Request['Mode']=='scan'){

			$this->ModelObj->customerScan();

		}elseif(isset($this->Request['batchcheckin']) && $this->Request['batchcheckin']=='CustomerScan'){
			 if(isset($this->Request['barcode_id']) && !empty($this->Request['barcode_id'])){
				$this->ModelObj->batchcustomerScan();
				$objSession->successMsg = "Parcel Checkin Successfully!!";
			 }else{
				$objSession->errorMsg = "Please check atleast one parcel !!";
			 }
		}

        $this->view->CustomerScanList = $this->ModelObj->getCustomerScanList();

		$this->view->countryList = $this->ModelObj->getCountryList();

		$this->view->customerList = $this->ModelObj->getCustomerList();

		$this->view->forwarderList = $this->ModelObj->getForwarderList();

    }

	

	public function returncheckinAction(){

		$this->_helper->layout->setLayout('popup');

		if($this->Request['Mode']=='view'){

			$this->view->returninfo =  $this->ModelObj->returnShipmentsDetail();

			$this->view->forwarderList = $this->ModelObj->getForwarderList();

		}else if($this->Request['Mode']=='checkin'){

			$this->ModelObj->checkinReturnShipments();

	   }

	}

	

	public function deliverytrackerAction(){

			$this->view->countryList = $this->ModelObj->getCountryList();

			$this->view->forwarderList = $this->ModelObj->getForwarderList();

			if($this->_request->isPost() && isset($this->Request['forwarder_id']) && isset($this->Request['country_id']) && isset($this->Request['postalcode'])

			&& $this->Request['forwarder_id']!='' && $this->Request['country_id']!='' && $this->Request['postalcode']!=''){

				if(isset($this->Request['mode']) && $this->Request['mode']=='addnew'){

					$this->view->TimeDetail =  $this->ModelObj->addTracker();

				}else{

			        $this->view->TimeDetail =  $this->ModelObj->getdeliverytime();

			   }

			}

	}



	public function defaultmanualpickupAction(){

		global $objSession; 

		$this->_helper->layout->setLayout('popup');

		if($this->_request->isPost() && isset($this->Request['submit']) && $this->Request['submit']!=''){

		   $result = $this->ModelObj->defaultManualPickup();

		   if($result){

		   $objSession->successMsg = "Manual Pickup Added Successfully !!";

		   }else{

			$objSession->errorMsg = "Something is wrong !!";

		   }

			echo '<script type="text/javascript">parent.window.location.reload();parent.jQuery.fancybox.close();</script>';exit();

		}

		if(isset($this->Request['user_id'])){

			$userid = Zend_Encript_Encription:: decode($this->Request['user_id']);

			$this->view->pickupDatail = $this->ModelObj->getCustomerDetails($userid);

		}

	}

}



