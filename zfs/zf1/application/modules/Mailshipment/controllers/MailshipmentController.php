<?php
//ini_set('display_errors','1');
class Mailshipment_MailshipmentController extends Zend_Controller_Action
{

    public $Request = array();
    public function init()
    {
        try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Mailshipment_Model_Mailshipment();
			$this->ModelObj->getData  = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
			$this->_helper->layout->setLayout('main');
			$this->view->Request = $this->Request;
	 }catch(Exception $e){
	    echo $e->getMessage();die;
	 }
    }

    public function indexAction()
    {
        // action body
    }

    public function mailroutingAction()
    {
        $this->view->records = $this->ModelObj->getMailRoutingList();
		$this->view->countrylist = $this->ModelObj->getCountry();
		$this->view->depotlist =  $this->ModelObj->getDepotList();
		$this->view->weightclass = $this->ModelObj->getMailPostWeightClas();
    }
	public function addmailroutingAction(){
		$this->_helper->layout->setLayout('popup');
		if($this->_request->isPost() && isset($this->Request['submit'])){
			$this->ModelObj->AddMailRouting();
			echo '<script type="text/javascript">parent.window.location.reload();parent.jQuery.fancybox.close();</script>';exit();
		}
		$this->view->countrylist = $this->ModelObj->getCountry();
		$this->view->weightclass = $this->ModelObj->getMailPostWeightClas();
	}

    public function mailpostlistAction()
    {
          global $objSession,$labelObj;
		  if(isset($objSession->MailPostLabel)){
		     $labelObj->_filePath = $objSession->MailPostLabel; 
		     unset($objSession->MailPostLabel);
	         $labelObj->printLabel();
		  }
		  if($this->_request->isPost() && isset($this->Request['manifest_action']) && isset($this->Request['manifest_number'])){ 
		      $this->ModelObj->DoManifestaction();
			  $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
			  print_r($this->Request);die;
		  }
		$this->view->records = $this->ModelObj->getMailshipmentList();
		
    }

    public function addmailshipmentAction()
    {
        global $objSession,$labelObj;
		  if(isset($objSession->MailPostLabel)){
		     $labelObj->_filePath = $objSession->MailPostLabel; 
		     unset($objSession->MailPostLabel);
	         $labelObj->printLabel();
		  }
		if($this->_request->isPost() && isset($this->Request['add_mail_post']) && $this->Request['user_id']!=''){
		   $this->ModelObj->AddMailPost();
		   $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
		}elseif($this->_request->isPost() && isset($this->Request['add_mail_post']) && $this->Request['user_id']==''){
		   $objSession->errorMsg = 'Please select Customer';
		   $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
		}
		$this->view->countrylist = $this->ModelObj->getCountry();
		$this->view->weightclass = $this->ModelObj->getMailPostWeightClas();
		$this->view->customerlist = $this->ModelObj->getCustomerList();
    }
	public function editmailpostAction(){
		  global $objSession;
		if($this->_request->isPost() && isset($this->Request['update_mail_post']) && $this->Request['user_id']!=''){
		   $this->ModelObj->EditMailPost();
		   $this->_redirect($this->_request->getControllerName().'/mailpostlist');
		}elseif($this->_request->isPost() && isset($this->Request['update_mail_post']) && $this->Request['user_id']==''){
		   $objSession->errorMsg = 'Please select Customer';
		   //$this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
		}
		$this->view->mailPostDetails = $this->ModelObj->getMailPostDetails();
		$this->view->countrylist = $this->ModelObj->getCountry();
		$this->view->weightclass = $this->ModelObj->getMailPostWeightClas();
		$this->view->customerlist = $this->ModelObj->getCustomerList();
	}

    public function mailbarcodecheckinAction()
    {
        // action body
    }

    public function mailhistoryAction()
    {
       $this->view->records = $this->ModelObj->MailpostHistory();
	   $this->view->countrylist = $this->ModelObj->getCountry();
	   $this->view->weightclass = $this->ModelObj->getMailPostWeightClas();
	  $this->view->customerlist = $this->ModelObj->getCustomerList();
    }

    public function deletedmailshipmentAction()
    {
        $this->view->records = $this->ModelObj->MailpostDeleted();
    }
	public function routingupdateAction(){
	   $this->ModelObj->MailRoutingUpdate();
	}
	public function mailpriceAction(){
	  $this->ModelObj->MailRoutingPrice(); 
	}
	public function printmanifestAction(){
		if(isset($this->Request['manifest_number']) && isset($this->Request['actions']) && $this->Request['actions']=='Print'){
		    $this->ModelObj->PrintManifest();
		}elseif(isset($this->Request['manifest_number']) && isset($this->Request['actions']) && $this->Request['actions']=='Delete'){
		   $this->ModelObj->DeleteMailPost();
		   if(isset($this->Request['ajax'])){
		  	   echo json_encode(array('status'=>1,'message'=>'Deleted Successfully'));die;
			}else{
			   $this->_redirect($this->_request->getControllerName().'/mailpostlist');
			}
		}
	}
	
	public function checkinmanifestAction(){
	      //print_r($this->Request);die;
		  $this->ModelObj->MailCheckinData();
	}
	public function scanmaileditAction(){
	    $this->ModelObj->updatemailScaned();
	}
	public function mailcheckinAction(){
	    $this->ModelObj->checkinMailpost();
	}


}













