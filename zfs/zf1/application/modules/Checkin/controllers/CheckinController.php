<?php 
class Checkin_CheckinController extends Zend_Controller_Action
{

    public $Request = array();

    public $ModelObj = null;

    public $Setting_Model = null;

    public function init()
    {
       try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Checkin_Model_CheckinManager();
			$this->Setting_Model = new settings_Model_Statuscode();
			$this->formObj = new Checkin_Form_Parcel();
			$this->ModelObj->getData  = $this->Request;
			$this->Setting_Model->getData  = $this->Request;
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

    public function checkinAction()
    {
    }

    public function batchcheckinAction()
    {
       if((isset($this->Request['shipment_mode']) && $this->Request['shipment_mode']!='' && !empty($this->Request['barcode_id'])) || (isset($this->Request['shipment_mode1']) && $this->Request['shipment_mode1']!='')){
	         $this->ModelObj->batchCheckin();
			 $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName()); 
	   }
	   $this->view->records = $this->ModelObj->getParcelList();
	   $this->view->filters = $this->ModelObj->getfilters();
	   $this->view->servicelist = $this->ModelObj->getCustomServiceList();
	   $this->view->addedtype = $this->ModelObj->ParcelAddedType();
    }

    public function parceldetailAction()
    {   // $this->ModelObj->getData['search_barcode'] = '75560200627000';

    	// echo "<pre>"; print_r($this->getData);die;
    	
	   if(isset($this->Request['search_barcode'])){
         $this->view->parceldetails = $this->ModelObj->getParcelDetails();
		 if($this->_request->isPost() && isset($this->Request['assign_errors']) && isset($this->Request['status_id']) && $this->Request['status_id']>0){
		     $this->ModelObj->AddParcelError($this->view->parceldetails);
			 $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName()); 
		 }
		 if($this->_request->isPost() && isset($this->Request['event']) && isset($this->Request['event_action_id']) && $this->Request['event_action_id']>0){
		      $this->ModelObj->AddEvent($this->view->parceldetails);
			  $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName()); 
		 }
		 if(!empty($this->view->parceldetails)){
			 $this->view->errolist = $this->ModelObj->getErrorList($this->view->parceldetails['forwarder_id']);
			 $this->view->tracking_log = $this->ModelObj->GetTrackingLog($this->view->parceldetails['barcode_id']);
			 $this->view->old_address = $this->ModelObj->ParcelOldAddress($this->view->parceldetails['barcode_id']);
			 $this->view->eventhistories = $this->ModelObj->GetEventHistories($this->view->parceldetails['barcode_id']);
	     }
		}
	   //echo "<pre>";print_r( $this->view->parceldetails);die	;
    }
	
	public function csvcheckinAction(){
	     if($this->_request->isPost()){
		    $this->ModelObj->CheckinCSV();  
		 }
	}
	
	public function referencecheckinAction(){
	    
	}
	
	public function parceldooptionAction(){
	     $this->ModelObj->ParcelDoAction();die;   
	}

	 
	
	 


}







