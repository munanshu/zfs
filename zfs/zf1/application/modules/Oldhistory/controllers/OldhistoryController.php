<?php
/**

 * Controll the Account module

 * @Auth : SJM softech Pvt. Ltd.

 * @Created Date: 03rd-October-2016

 * @Description : Controll the functionality related to Account

 **/

class Oldhistory_OldhistoryController extends Zend_Controller_Action

{

	public $Request = array();

    public $ModelObj = null;

	public $formObj  = NULL;

	

    

     public function init()

    { 
			$this->Request = $this->_request->getParams();

			$this->ModelObj = new Oldhistory_Model_Parcelhistorymanager($this->Request);
			$this->CheckinModelObj = new Checkin_Model_CheckinManager();
			
			$this->ParcelModelObj = new Oldhistory_Model_Oldparceltracking($this->Request,$this->CheckinModelObj);

			

			$this->view->CheckinModelObj = $this->CheckinModelObj;

			$this->ModelObj->getData  = $this->Request;

			$this->ParcelModelObj->getData  = $this->Request;

			$this->CheckinModelObj->getData  = $this->Request;
			
			if(isset($this->Request['tockenno']) && $this->Request['tockenno']!=''){
				 
				$barcode_id=trim(Zend_Encript_Encription::decode($this->Request['tockenno']));
					
				if ( is_numeric($barcode_id))
				{
					$barcode_id = $barcode_id;
				}
					  
				$this->ParcelModelObj->getData[BARCODE_ID] = $barcode_id;
				 
			}

			$this->view->ModelObj = $this->ModelObj;

			$this->view->Request = $this->Request;

			$this->_helper->layout->setLayout('main');

    }

	public function getoldparceldetailsAction()
	{	
		if($this->getRequest()->isPost() && isset($this->Request['search_barcode'])){

         
         	$this->view->parceldetails = $this->ModelObj->getParcelDetails();



         	if(!empty($this->view->parceldetails)){
				 $this->view->errolist = $this->CheckinModelObj->getErrorList($this->view->parceldetails['forwarder_id']);
				 $this->view->tracking_log = $this->ModelObj->GetTrackingLog($this->view->parceldetails['barcode_id']);
				 $this->view->old_address = $this->ModelObj->ParcelOldAddress($this->view->parceldetails['barcode_id']);
				 $this->view->eventhistories = $this->ModelObj->GetEventHistories($this->view->parceldetails['barcode_id']);
	     	}else {
	     		global $objSession;
	     		$objSession->errorMsg = 'Sorry No Parcel Found ';
	     	}
		}

		

	} 

	var querystring = "var1=1&bar2=";
	data : ,

	public function parceltrackingAction()
	{	
		$this->_helper->layout->setLayout('popup');
		$this->view->parcelinfo = $this->ParcelModelObj->parcelinformation();
		  
		// $this->_helper->viewRenderer->renderBySpec('getoldedihistory', array( 'module' => 'oldhistory' ,'controller' => 'edihistory')); 

		$this->view->setScriptPath(APPLICATION_PATH.'/views/scripts/parceltracking');
		$this->_helper->viewRenderer('tracking', null, true);
		 
	}



}