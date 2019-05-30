<?php
class DashboardController extends Zend_Controller_Action
{
	public $ModelObj = null;
    private $Request = array();

    public function init()
    {
        /* Initialize action controller here */
		 try{	
			$this->_helper->layout->setLayout('dashboard');
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Application_Model_Dashboard();
			$this->ModelObj->getData  = $this->Request;
			$this->view->Request = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
	 }catch(Exception $e){
	    echo $e->getMessage();die;
	 }	
		
    }

    public function indexAction()
    {
        $this->view->addedParcel = $this->ModelObj->AddedParcel();
		$this->view->errorParcel = $this->ModelObj->ErrorParcel();
		if($this->ModelObj->Useconfig['level_id']==5){
		   $this->view->pickups = $this->ModelObj->PlannedPickup();
		   $this->view->transit = $this->ModelObj->TransitParcel();
		   $this->view->deliveredParcel = $this->ModelObj->DeliveredParcel();
		   $this->view->opentickets = $this->ModelObj->openTickets();
		   $this->view->api_orders = $this->ModelObj->shopAPiorders();
		}
		if($this->ModelObj->Useconfig['level_id']!=5){
		   $this->view->freight = $this->ModelObj->AddedFreight();
		   $this->view->express = $this->ModelObj->AddedExpress();
		   $this->view->Assignedpickup = $this->ModelObj->Assignedpickup();
		   $this->view->pickedup = $this->ModelObj->PickedUpDetails();
		   $this->view->driverpickupsummary = $this->ModelObj->DriverPickupSummary();
		}
		if($this->ModelObj->Useconfig['level_id']==4 || $this->ModelObj->Useconfig['level_id']==6 || $this->ModelObj->Useconfig['level_id']==5 || $this->ModelObj->Useconfig['level_id']==10){
			
		}
    }
	public function shopordersAction(){ 
	      $this->ModelObj->shopAPiorders();
	}
}

