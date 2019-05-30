<?php
//ini_set('display_errors','1');
class Checkin_EdimanagerController extends Zend_Controller_Action
{

    public $Request = array();

    public $ModelObj = null;

    public function init()
    {
       try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Checkin_Model_Edimanager();
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

    public function forwardermanifestAction()
    {
	   global $objSession,$labelObj;
	   if(isset($objSession->ManifestFile)){
		 $labelObj->_filePath = $objSession->ManifestFile; 
		 unset($objSession->ManifestFile);
		 $labelObj->printLabel();
	  }	
       if($this->_request->isPost() && (isset($this->Request['forwarder_manifest']) || isset($this->Request['hub_manifest']))){
	      $this->ModelObj->GenerateEDIAndManifest();
		  $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
	   }
	   $this->view->records  = $this->ModelObj->forwarderManifest();
	   $this->view->forwarder =  $this->ModelObj->getForwarderList();
	   $this->view->depotlist =  $this->ModelObj->getDepotList();
	   $this->view->hubList =  $this->ModelObj->getHubList();
    }

    public function forwardermanifestviewAction()
    {
	    global $objSession,$labelObj;
		$this->_helper->layout->setLayout('popup');
		 if($this->_request->isPost() && (isset($this->Request['forwarder_manifest']) || isset($this->Request['hub_manifest']))){
		     $this->ModelObj->GenerateEDIAndManifest();
			 //$this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
		 }
		$this->view->records  = $this->ModelObj->forwarderManifest(false);
		$this->view->depotlist =  $this->ModelObj->getDepotList();
		$this->view->hubList =  $this->ModelObj->getHubList();
		if(isset($objSession->ManifestFile)){
		 $labelObj->_filePath = $objSession->ManifestFile; 
		 unset($objSession->ManifestFile);
		 $labelObj->printLabel();
	  }	
    }

    public function edihistoryAction()
    {
         $this->view->records  = $this->ModelObj->ediHistory();
		$this->view->forwarder =  $this->ModelObj->getForwarderList();
    }
	
	public function ediupdownAction(){
		 switch($this->Request['mode']){
		    case 'Download':
			    $this->ModelObj->DownloadEDI();  
			break;
			case 'Upload':
				$this->ModelObj->ReUploadEDI();
				$this->_redirect($this->_request->getControllerName().'/edihistory');
			break;
		 }
	}
	public function mediatorforwarderAction(){
		  if($this->_request->isPost() && isset($this->Request['update']) && isset($this->Request['barcode_id'])){
		     $this->ModelObj->UpdateMediatorForwarder();
		  }
		  $this->view->records  = $this->ModelObj->getParcelListforMediatorForwarder();
		  $this->view->forwarder =  $this->ModelObj->getForwarderList();
		  $this->view->countryList = $this->ModelObj->getCountryList();
	}
	
	public function singlemediatorforAction(){
	   $this->_helper->layout->setLayout('popup');
	   if($this->Request['mediator_forwarder_id']>0 && $this->Request['barcode']!=''){
		   $this->ModelObj->getData = array('barcode_id'=>array($this->Request['barcode_id']),'bulk_forwarder_id'=>$this->Request['mediator_forwarder_id'],'bulk_mediator_barcodes'=>$this->Request['barcode']);
		   $this->ModelObj->UpdateMediatorForwarder();
			echo '1';die;
		}else{
		   echo '0';die;
		}
	}
	
	public function urgentletterAction(){
	    global $objSession,$labelObj;
		 if($this->_request->isPost() && (isset($this->Request['forwarder_manifest']) || isset($this->Request['hub_manifest']))){
		     $this->ModelObj->getData['urgentletter'] = true;
			 $this->ModelObj->GenerateEDIAndManifest();
			 //$this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
		 }
		$this->view->records  = $this->ModelObj->forwarderManifest(false);
		$this->view->depotlist =  $this->ModelObj->getDepotList();
		if(isset($objSession->ManifestFile)){
		 $labelObj->_filePath = $objSession->ManifestFile; 
		 unset($objSession->ManifestFile);
		 $labelObj->printLabel();
	  }	
	}
	public function specialbpostAction(){
	    global $objSession,$labelObj;
		 if($this->_request->isPost() && (isset($this->Request['forwarder_manifest']) || isset($this->Request['hub_manifest']))){
			 $this->ModelObj->GenerateEDIAndManifest();
			 //$this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
		 }
		$this->view->records  = $this->ModelObj->forwarderManifest(false);
		$this->view->depotlist =  $this->ModelObj->getDepotList();
		if(isset($objSession->ManifestFile)){
		 $labelObj->_filePath = $objSession->ManifestFile; 
		 unset($objSession->ManifestFile);
		 $labelObj->printLabel();
	  }	
	}
	
	public function specialdhlAction(){
	    global $objSession,$labelObj;
		 if($this->_request->isPost() && (isset($this->Request['forwarder_manifest']) || isset($this->Request['hub_manifest']))){
		     $this->ModelObj->getData['special_edi'] = 186;
			 $this->ModelObj->GenerateEDIAndManifest();
			 //$this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
		 }
		$this->view->records  = $this->ModelObj->forwarderManifest(false);
		$this->view->depotlist =  $this->ModelObj->getDepotList();
		if(isset($objSession->ManifestFile)){
		 $labelObj->_filePath = $objSession->ManifestFile; 
		 unset($objSession->ManifestFile);
		 $labelObj->printLabel();
	  }	
	}
	
	public function specialupsAction(){
	    global $objSession,$labelObj;
		$this->_helper->layout->setLayout('popup');
		 if($this->_request->isPost() && (isset($this->Request['forwarder_manifest']) || isset($this->Request['hub_manifest']))){
		     $this->ModelObj->GenerateEDIAndManifest();
			 //$this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
		 }
		$this->view->records  = $this->ModelObj->forwarderManifest(false);
		$this->view->depotlist =  $this->ModelObj->getDepotList();
		if(isset($objSession->ManifestFile)){
		 $labelObj->_filePath = $objSession->ManifestFile; 
		 unset($objSession->ManifestFile);
		 $labelObj->printLabel();
	  }	  
	}

}





