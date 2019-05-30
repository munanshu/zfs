<?php
class Planner_PlannerController extends Zend_Controller_Action
{

    public $Request = array();
    public function init()
    {
        try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Planner_Model_Planner();
			$this->ModelObj->getData  = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
			$this->view->Request = $this->Request;
			$this->_helper->layout->setLayout('main');
	 }catch(Exception $e){
	    echo $e->getMessage();die;
	 }
    }

    public function indexAction()
    {
        // action body
    }

    public function schedulepickupAction()
    {   
        $recordArr = array();
		if($this->_request->isPost() && isset($this->Request['driver_id']) && $this->Request['driver_id']>0){
		    $this->ModelObj->bulkAssignment();
			$this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
		}
		$parcelSchedule  = $this->ModelObj->schedulePickup();
		$recordArr = array_merge($recordArr,$parcelSchedule);
		$mailSchedule    = $this->ModelObj->MailschedulePickup();
		$recordArr = array_merge($recordArr,$mailSchedule);
		$dailySchedule    = $this->ModelObj->DailyschedulePickup($recordArr);
		$recordArr = array_merge($recordArr,$dailySchedule);
		$manualSchedule    = $this->ModelObj->ManualschedulePickup($recordArr);
		$recordArr = array_merge($recordArr,$manualSchedule);
		$nonlistedSchedule    = $this->ModelObj->NonlistedScheduleParcel();
		$recordArr = array_merge($recordArr,$nonlistedSchedule);
		$pickupdate =array();
		$pickuptime =array();
		$createdate =array();
		foreach($recordArr as $key => $row) { 
				$pickupdate[$key]  = $row['pickup_date'];
				$pickuptime[$key]  = $row['pickup_time'];
				$createdate[$key]  = $row['create_date'];
           } 
		array_multisort($pickuptime, SORT_ASC, $pickupdate, SORT_ASC,$createdate,SORT_DESC, $recordArr); 
		
		$this->view->schedulepickup = $recordArr;
		$this->view->driverList = $this->ModelObj->getDriverList();
    }

    public function schedulerouteAction()
    {
       
    }
   

    public function todaypickedAction()
    {
        $this->view->records = $this->ModelObj->PickedupHistory();
    }

    public function failedpickupshipmentAction()
    {
        $this->view->records = $this->ModelObj->FailedPickup();
    }

    public function assignedshipmentAction()
    {
		 $recordArr = array();
		 $Assignedshipment = $this->ModelObj->AssignedShipment();
		 $recordArr = array_merge($recordArr,$Assignedshipment);
		 $Assignedmailshipment = $this->ModelObj->AssignedMailShipment();
		 $recordArr = array_merge($recordArr,$Assignedmailshipment);
		 $Assigneddailypickup = $this->ModelObj->AssignedDailyShipment();
		 $recordArr = array_merge($recordArr,$Assigneddailypickup);
		 $Assignedmanualpickup = $this->ModelObj->AssignedManualPickup();
		 $recordArr = array_merge($recordArr,$Assignedmanualpickup);
		 $Assignednonlisted = $this->ModelObj->AssignedNonlistedSchedule(true);
		 $recordArr = array_merge($recordArr,$Assignednonlisted);
		 $pickupdate =array();
		 $pickuptime =array();
		 foreach($recordArr as $key => $row) { 
				$pickupdate[$key]  = $row['pickup_date'];
				$pickuptime[$key]  = $row['pickup_time'];
         } 
		 array_multisort($pickuptime, SORT_ASC, $pickupdate, SORT_ASC, $recordArr); 
		 $this->view->schedulepickup = $recordArr;
		 $this->view->driverList = $this->ModelObj->getDriverList();
		 $this->view->customerList = $this->ModelObj->getCustomerList();
    }

    public function driverlistAction()
    {
		$this->view->records = $this->ModelObj->getAllDriverList();
    }
	public function manualpickupAction(){
	  $this->_helper->layout->setLayout('popup');
	  if($this->_request->isPost() && isset($this->Request['manualpickup'])){
	      $this->ModelObj->manualPickup();
		  echo '<script type="text/javascript">parent.window.location.reload();parent.jQuery.fancybox.close();</script>';exit();
	  }
	  $data = commonfunction::explode_string(commonfunction::DeCompress($this->Request['pickup_detail']),'$');
	  $this->view->pickup_date = $data;
	  //
	}
	public function singleassignedAction(){
	   $this->_helper->layout->setLayout('popup');
	   $requestData = commonfunction::explode_string(commonfunction::DeCompress($this->Request['pickup_detail']),'$');
	   if($this->_request->isPost() && isset($this->Request['barcode_id']) && count($this->Request['barcode_id'])>0){
	        $this->ModelObj->assignedToDriver();
			echo '<script type="text/javascript">parent.window.location.reload();parent.jQuery.fancybox.close();</script>';exit();
	   }
	    $this->view->pickup_detail =$requestData;
	   $this->view->schedulepickup = $this->ModelObj->getScheduleBarcodeList($requestData);
	   $this->view->driverList = $this->ModelObj->getDriverList();
	}
	public function reassignAction(){
	   $this->ModelObj->ReAssignDriver();
	}
	
	public function nonlistedcustomerAction(){
	   $this->_helper->layout->setLayout('popup');
	   if($this->_request->isPost()){
	      $this->ModelObj->AddNonListedcustomer();
		  echo '<script type="text/javascript">parent.window.location.reload();parent.jQuery.fancybox.close();</script>';exit();
	   }
	   $this->view->depotlist = $this->ModelObj->getDepotList(); 
	}
	
	public function drivermanifestAction(){
	  
	   if(isset($this->Request['barcode_id'])){
	      $this->ModelObj->DriverManifest();
	   }else{
	     echo json_encode(array('Sataus'=>'N','Manifest'=>'','message'=>'Please Select any record to generate Manifest'));die;
	   }
	   
	}
	
	public function glsmailedparcelAction(){
	  
	}

}





















