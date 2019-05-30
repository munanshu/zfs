<?php 
ini_set('display_errors','1');
class Invoice_DepotduesinvoiceController extends Zend_Controller_Action
{

    public $InvoiceData = array();

    public $Request = array();

    public function init()
    {
        try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Invoice_Model_Depotduesinvoice();
			$this->ModelObj->getData  = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
			$this->_helper->layout->setLayout('main');
	 }catch(Exception $e){
	    echo $e->getMessage();die;
	 }	
    }

    public function indexAction()
    {
        // action body
    }
	public function allduesinvoiceAction(){
	    $this->view->records = $this->ModelObj->getListDuesInvoice();
		$this->view->userlist = $this->ModelObj->getDepotList();
	}
	
	public function paymenthistoryAction(){
	   $this->_helper->layout->setLayout('popup');
	   $this->view->records = $this->ModelObj->paymentHistory();
	}
	public function emailactionAction(){
	   echo "HI";die;
	}
}





