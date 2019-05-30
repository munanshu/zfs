<?php 
class Invoice_InvoicemanagerController extends Zend_Controller_Action
{

    public $InvoiceData = array();

    public $Request = array();

    public function init()
    {
        try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Invoice_Model_Invoicemanager();
			$this->ModelObj->getData  = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
			$this->_helper->layout->setLayout('main');
			$this->view->Request = $this->Request;
	 }catch(Exception $e){
	    echo $e->getMessage();die;
	 }	
    }

    public function syncparcelinvoiceAction()
    {
    	// echo "sdfsdf";die;
    	$this->ModelObj->invoicesync(); die;

    }

    public function invoiceAction()
    {
        global $objSession,$labelObj;
		  if(isset($objSession->Invoicefile)){//print($objSession->Invoicefile);die;
		     $labelObj->_filePath = $objSession->Invoicefile; 
		     unset($objSession->Invoicefile);
	         $labelObj->printLabel();
		  }
		if($this->_request->isPost()){
		   if(isset($this->Request['export'])){
		      $this->ModelObj->ExportInvoice();
		   }
		   if(isset($this->Request['unlist_invoice'])){
		     $this->ModelObj->UnlistInvoice();
			 $objSession->successMSG = 'Invoice Unlisted successfully!';
			 $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());
		   }
		   
		}  
		$invoiceData = $this->ModelObj->getInvoice(); 
		switch($this->ModelObj->Useconfig['level_id']){
		   case 1:
		   case 11:
		   	$this->view->userlist =  $this->ModelObj->getDepotList();
			$this->view->filtertext = 'Depot';
		   break;
		   case 4:
		   case 6:
		   	$this->view->userlist =  $this->ModelObj->getCustomerList();
			$this->view->filtertext = 'Customer';
		   break;
		}
		$paginator = Zend_Paginator::factory($invoiceData);
        $currentPage = isset($this->Request['page']) ? (int) htmlentities($this->Request['page']) : 1;
        $paginator->setCurrentPageNumber($currentPage);
        $itemsPerPage = isset($this->Request['count']) ? (int) htmlentities($this->Request['count']) : 100;
        $paginator->setItemCountPerPage($itemsPerPage);
        $this->view->page = $paginator->getPages();
        $this->view->records = $paginator;
		
    }

    public function createinvoiceAction()
    { 
		if(!empty($this->Request['invoice_id']) || !empty($this->Request['invoice_ids'])){ 
		   $this->ModelObj->GenerateInvoice();
		   $this->_redirect($this->_request->getControllerName()."/invoice");
		}else{
		   $this->_redirect($this->_request->getControllerName()."/invoice");
		}
    }
	public function addextraheadAction(){
		$this->_helper->layout->setLayout('popup');
		if($this->_request->isPost()){
		   $this->ModelObj->AddExtraHeadInvoice();
		   echo '<script>parent.location.reload();parent.jQuery.fancybox.close();</script>';exit;
		}
		$this->view->totalrecord = $this->ModelObj->ExtraInvoices();
		$this->view->btwclass = $this->ModelObj->getBTWClasses();
	}
	
	public function invoicelistAction(){
	   
	  if($this->_request->isPost() && isset($this->Request['export_financial'])){
	       $this->ModelObj->ExportFinancialInvoice();
	  }
	   $this->view->invoiceList = $this->ModelObj->InvoiceList();
	   switch($this->ModelObj->Useconfig['level_id']){
		   case 1:
		   case 11:
		   	$this->view->depotlist =  $this->ModelObj->getDepotList();
		   break;
		   case 4:
		   case 6:
		   	$this->view->depotlist =  $this->ModelObj->getCustomerList();
		   break;
		}
	}
	public function editinvoiceAction(){
	   if($this->_request->isPost()){ 
		   $this->ModelObj->updateInvoice();
		   $this->_redirect($this->_request->getControllerName().'/invoicelist');
		}
	   $this->view->invoiceList = $this->ModelObj->SingleInvoiceRecord($this->Request['invoice_number']);
	}
	
	public function viewpriceAction(){	
	   $this->_helper->layout->setLayout('popup');
	   $this->view->records = $this->ModelObj->getPricelistData();
	}
	public function updatepriceAction(){
		$this->ModelObj->updatePrice();
	}
	public function myinvoiceAction(){
	   $this->view->invoiceList = $this->ModelObj->InvoiceList();
	}
	public function codamountdetailAction(){
	    $this->view->invoiceList = $this->ModelObj->getcodparcellist();
	}
}





