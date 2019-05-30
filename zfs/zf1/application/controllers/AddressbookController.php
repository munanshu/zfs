<?php
//ini_set('display_errors','1');
class AddressbookController extends Zend_Controller_Action
{
    public $ModelObj = NULL;
    private $Request = array();
    public function init()
    {
        /* Initialize action controller here */ 
		try{	
			$this->_helper->layout->setLayout('main');
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Application_Model_Addressbook();
			$this->ModelObj->getData  = $this->Request;
			$this->view->Request = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
	 }catch(Exception $e){
	    echo $e->getMessage();die;
	 }	
    }

    public function indexAction()
    {
        // action body
    }

    public function addresslistAction()
    {
		 $this->view->address = $this->ModelObj->getTotalAddress();
		 $this->view->customerlist = $this->ModelObj->getCustomerList();
	     //$this->view->countrylist =  $this->ModelObj->getCountryList();
    }
	public function addressdetailAction(){
	     $this->view->records = $this->ModelObj->getAddressList();
	    $this->view->countrylist =  $this->ModelObj->getCountryList();
	}
	
	public function addnewaddressAction(){
	  global $objSession;
	  $this->view->countries  = $this->ModelObj->getCountryList();
	  if($this->_request->isPost() && $this->Request['submit']=='Save Profile'){
	   $this->ModelObj->addnewaddressData();
	  $objSession->successMsg = "Address Saved Successfully";
	  }
	 }
 
 
	 public function updateaddressAction(){
	  global $objSession;
	  $this->view->getalldata = $this->ModelObj->getDataByaddressid();
	  $this->view->countries  = $this->ModelObj->getCountryList();
	  if($this->_request->isPost() && $this->Request['submit']=='Update Profile'){
	   $this->ModelObj->updateaddressData();
	  $objSession->successMsg = "Address Updated Successfully";
	  }
	 }
}