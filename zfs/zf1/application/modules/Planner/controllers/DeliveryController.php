<?php
class Planner_DeliveryController extends Zend_Controller_Action
{
    public $Request = array();

    public $ModelObj = null;
    public function init()
    {
       try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Planner_Model_Delivery();
			$this->formObj = new Planner_Form_Planner();
			$this->ModelObj->getData  = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
			$this->_helper->layout->setLayout('main');
	  }catch(Exception $e){
	    echo $e->getMessage();die;
	  }
    }

	public function scheduledeliveryAction() {
		global $objSession;
		if($this->_request->isPost() && isset($this->Request['driver_id']) && !empty($this->Request['driver_id'])){
		$result = $this->ModelObj->assignToDriver();
			  if($result){
				$objSession->successMsg = "Driver Assignment Successfully !!";
			  }else{
			    $objSession->errorMsg = "Something is wrong !!";
			  }
			$this->_redirect($this->_request->getControllerName().'/scheduledelivery');
		}
		$RecordArr = array();
        $scheduleDelivery = $this->ModelObj->getscheduledelivery();
		$RecordArr = array_merge($RecordArr,$scheduleDelivery);
	    $eventreturn = $this->ModelObj->getEventReturnParcel();
		$RecordArr = array_merge($RecordArr,$eventreturn);
		$delivery_date =array();
		foreach($RecordArr as $key => $row) { 
				$delivery_date[$key]  = $row['delivery_date'];
           } 
		array_multisort($delivery_date, SORT_ASC, $RecordArr); 
		$this->view->records = $RecordArr;
		
		$driverlist = new Planner_Model_Scheduleroute();
        $this->view->driver_list = $driverlist->getDriverList();
		$this->view->customerList = $this->ModelObj->getCustomerList();
	}

	public function setdatetimeAction() {
		global $objSession;
		$this->_helper->layout->setLayout('popup');
		if($this->_request->isPost() && isset($this->Request['submit'])){
			  $result = $this->ModelObj->setDateTimeDelivery();
			  if($result){
				$objSession->successMsg = "Date Time Upadated Successfully !!";
			  }else{
			    $objSession->errorMsg = "Something is wrong !!";
			  }
			  echo '<script type="text/javascript">parent.window.location.reload();
				  parent.jQuery.fancybox.close();</script>';
				  exit(); 
		
		}
	}
	
	public function assigneddeliveryAction(){
		global $objSession,$labelObj;
		  if(isset($objSession->DriverManifest)){
			 $labelObj->_filePath = $objSession->DriverManifest; 
			 unset($objSession->DriverManifest);
			 $labelObj->printLabel();
		  }
		if(isset($this->Request['barcode_id']) && !empty($this->Request['barcode_id'])){
		      if(isset($this->Request['driver_id']) && $this->Request['driver_id']>0){
					 $result = $this->ModelObj->assignToDriver();
			  }elseif(isset($this->Request['driver_manifest'])){
			  		$result = $this->ModelObj->driverManifest();
					$this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
			  }
			  if($result){
				$objSession->successMsg = "Driver Assignment Successfully !!";
			  }else{
			    $objSession->errorMsg = "Something is wrong !!";
			  }
			$this->_redirect($this->_request->getControllerName().'/assigneddelivery');
		}
	    $RecordArr = array();
        $scheduleDelivery = $this->ModelObj->getassigndelivery();
		$RecordArr = array_merge($RecordArr,$scheduleDelivery);
	    $eventreturn = $this->ModelObj->getEventReturnParcel(true);
		$RecordArr = array_merge($RecordArr,$eventreturn);
		$delivery_date =array();
		foreach($RecordArr as $key => $row) { 
				$delivery_date[$key]  = $row['delivery_date'];
           } 
		array_multisort($delivery_date, SORT_ASC, $RecordArr); 
		$this->view->records = $RecordArr;
		$driverlist = new Planner_Model_Scheduleroute();
        $this->view->driver_list = $driverlist->getDriverList();
		//echo "<pre>";print_r($this->ModelObj->getCustomerList()); die;
		$this->view->customerList = $this->ModelObj->getCustomerList();
	}
	public function deliveryscanAction(){
	    if($this->_request->isPost() && isset($this->Request['mode'])){
		       $this->ModelObj->ParcelDelvered();      
			   print_r($this->Request);die;
		}
		$this->view->mastererror = $this->ModelObj->masterError();
	}
	public function checkscanAction(){
	     $this->ModelObj->checkTodaysAssign();      
	}
}

