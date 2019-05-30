<?php
/**

 * Controll the Old History module

 * @Auth : SJM softech Pvt. Ltd.

 * @Created Date: 03rd-October-2016

 * @Description : Controll the functionality related to Old History

 **/

class Oldhistory_InvoicehistoryController extends Zend_Controller_Action

{

	 public $Request = array();

    public $ModelObj = null;

	public $formObj  = NULL;

	

    

    public function init()

    { 

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new Oldhistory_Model_Oldhistorymanager($this->Request);

			$this->formObj = new Oldhistory_Form_Historyforms();

			$this->ModelObj->getData  = $this->Request;

			$this->view->ModelObj = $this->ModelObj;

			$this->view->Request = $this->Request;

			$this->_helper->layout->setLayout('main');

    }

    public function getinvoicehistoryAction()
    {   

    	if(isset($this->Request['export_financial'])){
	       $this->ModelObj->ExportFinancialInvoice();
	  	} 

        $this->view->yearlyInvoiceDetails =  $this->ModelObj->getInvoicehistoryDetails();


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

     public function editinvoiceAction()
    {    
        $this->_helper->layout->setLayout('popup');
        // echo "<pre>";
        // print_r($this->ModelObj->getselectboxListbyClass(AccountingGroup,array('group_id','group_name'),'ASC',2) );die;
        $this->formObj->addInvoiceForm();
         // try {
         
             $this->view->title = 'Edit Invoice Info.'; 
            $this->formObj->getElement('submit')->setLabel('Update Invoice');
             if($this->getRequest()->isPost()){
                if($this->formObj->isValid($this->Request)){
                    $result = $this->ModelObj->updateInvoice();

                     global $objSession; 

                     if($result['status'])
                        $objSession->successMsg = $result['message'];
                     else $objSession->errorMsg = $result['message'];   
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{
                   
                   $editdata = $this->ModelObj->SingleInvoiceRecord($this->Request['invoice_number']);
        		   $this->view->InvoiceHistoryData = $editdata;  
                   $this->formObj->populate($editdata);

                }

            }
            else {  
                   $editdata = $this->ModelObj->SingleInvoiceRecord($this->Request['invoice_number']);
        		   $this->view->InvoiceHistoryData = $editdata;  
                   $this->formObj->populate($editdata);
            } 
          
        $this->view->InvoiceHistoryForm =  $this->formObj;  

     }
	 

}