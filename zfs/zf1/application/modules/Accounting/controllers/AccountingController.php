<?php
/**

 * Controll the Account module

 * @Auth : SJM softech Pvt. Ltd.

 * @Created Date: 03rd-October-2016

 * @Description : Controll the functionality related to Account

 **/

class Accounting_AccountingController extends Zend_Controller_Action

{

	 public $Request = array();

    public $ModelObj = null;

	public $formObj  = NULL;

	

    

    public function init()

    { 

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new Accounting_Model_Settingmanager();

			$this->formObj = new Accounting_Form_Heads($this->Request);

			$this->ModelObj->getData  = $this->Request;

			$this->view->ModelObj = $this->ModelObj;

			$this->view->Request = $this->Request;

			$this->_helper->layout->setLayout('main');

    }


    public function getbtwratesAction()
    {	
         $this->view->BtwRates = $this->ModelObj->AccountingBtwRatesDetails();

    } 


    public function addeditbtwratesAction()

    {    
        $this->_helper->layout->setLayout('popup');
        // echo "<pre>";
        // print_r($this->ModelObj->getselectboxListbyClass(AccountingGroup,array('group_id','group_name'),'ASC',2) );die;
        $this->formObj->addBtwRateForm();
         // try {
        if($this->Request['mode'] == 'add'){
            $this->view->title = 'Add New Btw Rate';
                if($this->getRequest()->isPost()){
                   
                if($this->formObj->isValid($this->Request)){
                    $result = $this->ModelObj->addBtwRateDetails($this->Request);

                     global $objSession; 

                     if($result)
                        $objSession->successMsg = "Btw Rate Added successfully!!";
                     else $objSession->errorMsg = "Some internal error occurred"; 
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{
                    $this->formObj->populate($this->Request);

                }
            }
        }else{
             $this->view->title = 'Edit Btw Rate'; 
            $this->formObj->getElement('submit')->setLabel('Update');
             if($this->getRequest()->isPost()){
                if($this->formObj->isValid($this->Request)){
                         
                    $result = $this->ModelObj->EditBtwrateDetail($this->Request);

                     global $objSession; 

                     if($result['status'])
                        $objSession->successMsg = $result['message'];
                     else $objSession->errorMsg = $result['message'];   
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{

                    $this->formObj->populate($this->Request);

                }

            }
            else {  
                   $editdata = $this->ModelObj->getSingleBtwrateDetails();
                   $this->formObj->populate($editdata[0]);
            } 
        } 
        $this->view->BtwRatesForm =  $this->formObj;  

    }

    public function testarchiveAction()
    {
        $this->view->data = $this->ModelObj->getInvArchiveData();

    }


    public function getsupplierlistAction()
    {
        $this->view->AccountingSuppliers = $this->ModelObj->getAccountingSuppliers();
        $this->view->SuppliersNames = $this->ModelObj->getuniqsuppliers();

    }

    public function addeditsupplierAction()
    {
        $this->_helper->layout->setLayout('popup');
        // echo "<pre>";
        // print_r($this->ModelObj->getselectboxListbyClass(AccountingGroup,array('group_id','group_name'),'ASC',2) );die;
        $this->formObj->addSupplierForm();
         // try {
        if($this->Request['mode'] == 'add'){
            $this->view->title = 'Add New Supplier';
                if($this->getRequest()->isPost()){
                   
                if($this->formObj->isValid($this->Request)){
                    $result = $this->ModelObj->addSupplierDetails($this->Request);

                     global $objSession; 

                     if($result)
                        $objSession->successMsg = "Supplier Added successfully!!";
                     else $objSession->errorMsg = "Some internal error occurred"; 
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{
                    $this->formObj->populate($this->Request);

                }
            }
        }else{
             $this->view->title = 'Edit Supplier'; 
            $this->formObj->getElement('submit')->setLabel('Update');
             if($this->getRequest()->isPost()){
                if($this->formObj->isValid($this->Request)){
                         
                    $result = $this->ModelObj->EditSupplierDetail($this->Request);

                     global $objSession; 

                     if($result['status'])
                        $objSession->successMsg = $result['message'];
                     else $objSession->errorMsg = $result['message'];   
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{

                    $this->formObj->populate($this->Request);

                }

            }
            else {  
                   $editdata = $this->ModelObj->getSingleSupplierDetails();
                   $this->formObj->populate($editdata[0]);
            } 
        } 
        $this->view->SupplierForm =  $this->formObj;  
    }
	 

}