<?php
class Settings_StatuscodeController extends Zend_Controller_Action
{

     public $Request = array();
     public $ModelObj = null;
	 public $formObj  = NULL;
	
     /**
	 * Auto load NameSpace and create objects 
	 * Function : init()
	 * Auto call and loads the default namespace and create object of model and form
	 **/
     public function init()
     { 
		try{
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new settings_Model_Statuscode();
			$this->formObj = new settings_Form_Settings();
			$this->ModelObj->getData  = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
			$this->view->Request  = $this->Request;
			$this->_helper->layout->setLayout('main');
	   }catch(Exception $e){
	    echo $e->getMessage();
	   }
     }

    /**
         * Show Master Listing 
         * Function : codelistAction()
         * Here we can add master Status
    **/
	public function codelistAction(){
	     $this->view->errors = $this->ModelObj->getmastererror();
    }
      /**
         * set master status code 
         * Function : changrerrorid()
		 * Date : 26/12/2016
         * Here we update master status error type
	  **/
		 
		public function changrerroridAction(){
			print_r($this->ModelObj->setStatusType());
			exit;
        }    
    /**
         * add edit status 
         * Function : addeditstatuscodeAction()
		 * Date : 27/12/2016
         * Here we can add and edit Status Code
    **/	 
     public function addeditstatuscodeAction(){
	    $this->_helper->layout->setLayout('popup');
		   global $objSession;
		   $this->formObj->addeditstatuscodeForm();
		   if($this->Request['mode'] == 'add' && !isset($this->Request['token'])){
			  $this->view->title = 'Add Status Code';
			  $this->formObj->addeditstatuscodeForm()->code_numeric->setValue($this->ModelObj->getNewStatusCode());
			  if($this->_request->isPost() && !empty($this->Request['submit'])){
			 if($this->formObj->isValid($this->Request)){
			  $result=$this->ModelObj->AddEditStatusCode();
			  if($result){
				$objSession->successMsg = "Status Code Added Successfully !!";
			  }else{
			    $objSession->successMsg = "Something is wrong !!";
			  }
			  echo '<script type="text/javascript">parent.window.location.reload();
				  parent.jQuery.fancybox.close();</script>';
				  exit(); 
			 }else{
			  $this->formObj->populate($this->Request);
			 }
			}
			  
		   }elseif($this->Request['mode'] == 'edit' && isset($this->Request['token'])){
			$this->view->title = 'Edit Status Code';
			$this->formObj->addeditstatuscodeForm()->submit->setLabel('Update Status Code');
			if($this->_request->isPost() && !empty($this->Request['submit'])){
			 if($this->formObj->isValid($this->Request)){
			  $result=$this->ModelObj->AddEditStatusCode();
			  if($result){
				$objSession->successMsg = "Status Code Updated Successfully !!";
			  }else{
			    $objSession->successMsg = "Something is wrong !!";
			  }
			  echo '<script type="text/javascript">parent.window.location.reload();
				  parent.jQuery.fancybox.close();</script>';
				  exit(); 
			 }else{
			  $this->formObj->populate($this->Request);
			 }
			}else{
			 $fatchRowData = $this->ModelObj->getmastererror();
			 $this->formObj->populate($fatchRowData[0]);
			}
		   }
		   $this->view->addeditstatuscode = $this->formObj;
     }
	 
	 /**
	 * Show forwarder status code list
	 * Function : forwarderstatuslistAction()
	 * Date : 29/12/2016
     **/
	public function forwarderstatuslistAction(){
	     $this->view->forwarderstatusList = $this->ModelObj->getForwarderStatusCodeList();
		  $this->view->forwarders = $this->ModelObj->getForwarderList();
	     $this->view->masterstatuses = $this->ModelObj->getStatusMasterList();
    }
	
	/**
	 * Associate master status with forwarder status
	 * Function : associateforwarderAction()
	 * Date : 29/12/2016
     **/
	public function associateforwarderAction(){
		$this->_helper->layout->setLayout('popup');
		global $objSession;
		   $this->formObj->associateforwarderForm($this->Request);
		   if($this->Request['mode'] == 'associeateforwarder' && isset($this->Request['token'])){
		  
			if($this->_request->isPost() && !empty($this->Request['submit'])){
			 if($this->formObj->isValid($this->Request)){
			  $result = $this->ModelObj->UpdateAssociateForwarderCode();
			  if($result){
				$objSession->successMsg = "Associate Forwarder Status Code Updated Successfully !!";
			  }else{
			    $objSession->successMsg = "Something is wrong !!";
			  }
			  echo '<script type="text/javascript">parent.window.location.reload();
				  parent.jQuery.fancybox.close();</script>';
				  exit(); 
			 }else{
			  $this->formObj->populate($this->Request);
			 }
			}else{
			 $populateData['error_id'] = array_map('current',$this->ModelObj->getSelectedForwarderStatus());
			 $this->formObj->populate($populateData);
			}
		   }
	      $this->view->associateforwarder =  $this->formObj;
    }
  
   /**
         * add edit status 
         * Function : addeditstatuscodeAction()
		 * Date : 27/12/2016
         * Here we can add and edit Status Code
    **/	 
     public function addeditforwarderstatusAction(){
	    $this->_helper->layout->setLayout('popup');
		   global $objSession;
		   $this->formObj->addeditForwarderErrorForm();
		   if($this->Request['mode'] == 'add' && !isset($this->Request['token'])){
			  $this->view->title = 'Add Forwarder Status Code';
			  if($this->_request->isPost() && !empty($this->Request['submit'])){
				 if($this->formObj->isValid($this->Request)){
				  $result=$this->ModelObj->AddEditForwarderStatusCode();
				  if($result){
					$objSession->successMsg = "Forwarder Status Code Added Successfully !!";
				  }else{
					$objSession->successMsg = "Something is wrong !!";
				  }
				  echo '<script type="text/javascript">parent.window.location.reload();
					  parent.jQuery.fancybox.close();</script>';
					  exit(); 
				 }else{
				  $this->formObj->populate($this->Request);
				 }
			}
			  
		   }elseif($this->Request['mode'] == 'edit' && isset($this->Request['token'])){
			$this->view->title = 'Edit Forwarder Status Code';
			$this->formObj->addeditForwarderErrorForm()->submit->setLabel('Update Status Code');
			if($this->_request->isPost() && !empty($this->Request['submit'])){
			 if($this->formObj->isValid($this->Request)){
			  $result=$this->ModelObj->AddEditForwarderStatusCode();
			  if($result){
				$objSession->successMsg = "Forwarder Status Code Updated Successfully !!";
			  }else{
			    $objSession->successMsg = "Something is wrong !!";
			  }
			  echo '<script type="text/javascript">parent.window.location.reload();
				  parent.jQuery.fancybox.close();</script>';
				  exit(); 
			 }else{
			  $this->formObj->populate($this->Request);
			 }
			}else{
			 $fatchRowData = $this->ModelObj->getForwarderStatusCodeList()['data'];
			 // $fatchRowData =  $fatchRowData['date'];
			 //echo "<pre>";print_r($fatchRowData); die;
			 $fatchRowData[0]['country_id'] = $fatchRowData[0]['forwarder_id'];
			 $this->formObj->populate($fatchRowData[0]);
			}
		   }
		   $this->view->addeditstatuscode = $this->formObj;
     }
  	/**
         * add edit status 
         * Function : addeditstatuscodeAction()
		 * Date : 27/12/2016
         * Here we can add and edit Status Code
    **/	 
     public function smssettingAction(){
		$this->view->forwarders = $this->ModelObj->getForwarderList();
		$this->view->errors = $this->ModelObj->getmastererror();
	 }
	/**
         * edit sms detail 
         * Function : editsmsdetailAction()
		 * Date : 15/03/2017
         * Here we can edit sms detail
    **/	 
     public function editsmsdetailAction(){
		global $objSession;
			$this->_helper->layout->setLayout('popup');
			if($this->_request->isPost() && !empty($this->Request['submit'])){
			  $result= $this->ModelObj->UpdateSMSDays();
			  if($result){
				$objSession->successMsg = "SMS Forwarder Days Updated Successfully !!";
			  }else{
			    $objSession->successMsg = "Something is wrong !!";
			  }
			  
			  echo '<script type="text/javascript">parent.window.location.reload();
				  parent.jQuery.fancybox.close();</script>';
				  exit(); 
			}
		$master_id = Zend_Encript_Encription:: decode($this->Request['token']);
		$this->view->SMSData = $this->ModelObj->getsmsDetail($master_id);
		$this->view->forwarders = $this->ModelObj->getForwarderList();
		$this->view->errors = $this->ModelObj->getmastererror();
	 }
  	/**
         * SMS Report
         * Function : smsreportAction()
		 * Date : 15/03/2016
         * Here we can display sms report
    **/	 
     public function smsreportAction(){
		$this->view->smsreport = $this->ModelObj->getsmslogdata();
	 }

	 public function allstatussmstextsAction()
	 {


	 }

	 public function addstatussmstextsAction($value='')
	 {

		$this->_helper->layout->setLayout('popup');
		$this->formObj->AddSmsTextForm();
		$this->formObj->setName('smstxtform');

		if(!empty($this->Request) && $this->_request->isPost()){
			global $objSession;
	 		$data = $this->ModelObj->AddMasterSmsTexts();
	 		if(isset($data['status']) && $data['status'] == 1)
	 			$objSession->successMsg = $data['message'];
	 		else $objSession->errorMsg = $data['message'];
	 		echo '<script type="text/javascript">parent.window.location.reload();
				  parent.jQuery.fancybox.close();</script>';
				  exit();
	 	}		

		$this->view->form = $this->formObj;
		$this->view->title = 'Add Edit Sms Texts';
	 	# code...
	 }

	 public function getmastersmstextsAction()
	 {
	 	$data = array();
	 	if(!empty($this->Request) && $this->_request->isPost()){

	 		$this->formObj->AddSmsTextForm();
	 		$data = $this->ModelObj->GetMasterSmsTexts();
	 		$data = !empty($data)? array('status'=>1,'data'=>$data):false;
	 	}
	 	$data = Zend_Json_Encoder::encode($data);
	 	echo $data;die;
	 }

}







