<?php

class Planner_DrivertrackingController extends Zend_Controller_Action
{

    public $Request = array();
    public function init()
    {
        try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Planner_Model_Drivertracking();
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
	
	public function gpstrackingAction(){
	    $this->_helper->layout->setLayout('popup'); 
		$this->view->driverlocation = $this->ModelObj->getDriverLocation();
	}
	
	public function drivertrackAction(){
	     $this->_helper->layout->setLayout('popup'); 
		 $recordArr = array();
		 $pickuplocations = $this->ModelObj->getDriverPickupLocaions();
		 foreach($pickuplocations as $pickuplocation){ 
		     $addedXY = $this->ModelObj->getLatLong($pickuplocation['cncode'],$pickuplocation['city']);
		     $lat[] = $addedXY['latitude'];
			 $lng[] = $addedXY['longitude'];
			 $location[] = $pickuplocation['zipcode'].' '.$pickuplocation['city'].' '.$pickuplocation['country'];
			 $driverName = $pickuplocation['driver_name'];
		   if($this->ModelObj->Useconfig['user_id']==$pickuplocation['user_id']){
		     $icons[] = IMAGE_LINK.'/home_icon.png';
			 break;
		   }else{
		     $icons[] = IMAGE_LINK.'/assigned.png';
		     continue;
		   }
		 }
		 $drivercurrent = $this->ModelObj->driverCurrentLocation();
		 $lat[] = $drivercurrent['lat'];
		 $lng[] = $drivercurrent['lng'];
		 $location[] = $driverName.' On the way!';
		 $icons[] = IMAGE_LINK.'/pickup.png';
		 //$this->view->pickuplocations = $pickuplocation;
		 $this->view->driverlocation = array('Lat'=>$lat,'Lng'=>$lng,'Status'=>$location,'Icons'=>$icons);
	}


}

