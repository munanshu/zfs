<?php 
class Invoice_InvoicecodController extends Zend_Controller_Action
{

    public $InvoiceData = array();
    public $Request = array();

    public function init()
    {
        try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Invoice_Model_Invoicecod();
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

	public function codamountdetailAction(){
	    if($this->_request->isPost() && isset($this->Request['import'])){
	        $this->ModelObj->importCODfile();
	    }
		$this->view->invoiceList = $this->ModelObj->getcodparcellist();
		$this->view->depotlist =  $this->ModelObj->getCustomerList();
		$this->view->countrylist =  $this->ModelObj->getCountryList();
	}
	public function updatecodAction()
	{
	  
	  $this->ModelObj->UpdateCodPrice();
	}
}





