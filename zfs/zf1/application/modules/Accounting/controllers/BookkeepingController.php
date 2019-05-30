<?php
/**

 * Controll the Account module

 * @Auth : SJM softech Pvt. Ltd.

 * @Created Date: 03rd-October-2016

 * @Description : Controll the functionality related to Account

 **/

class Accounting_BookkeepingController extends Zend_Controller_Action

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

    public function getbookkeepinglistAction()
    {	
    	 $this->view->data = $this->ModelObj->getInvArchiveData();
    }

	 public function getbookformAction()
	 {	
	 	$this->_helper->layout->disableLayout();
	 	$this->formObj->addBookForm();
	 	$this->view->title =  "Invoice";

	 	$form_type = $this->view->Request['FormType'];
	 	// echo $form_type;die;
	 	switch ($form_type) {

	 		case 'MEMORIAL':
	 	 		//  echo "For Now Only 'VERKOOPEN' and 'INKOOPEN' are working rest of them are in Development Mode"; exit();
	 	 		// break;

	 	 	case 'POSTBANK':
	 	 		//  echo "For Now Only 'VERKOOPEN' and 'INKOOPEN' are working rest of them are in Development Mode";exit();
	 	 		// break;

	 	 	case 'BANK':


	 	 		$this->formObj->credit_amount->setAttrib('onkeydown','removecolumn($(this).attr("name"))');
				$this->formObj->credit_amount->setAttrib('onDblclick','forceEnable($(this).attr("name"))');
				$this->formObj->invoice_number->setLabel('Booknumber:');
				$this->formObj->credit_amount->setLabel('Total Pay:');

				$this->formObj->debit_amount->setAttrib('onkeydown','removecolumn($(this).attr("name"))');
				$this->formObj->debit_amount->setAttrib('onDblclick','forceEnable($(this).attr("name"))');
				$this->formObj->debit_amount->setLabel('Total Recieve:');

				if($this->Request['mode'] == 'edit'){

	 	 			 $editdata = $this->ModelObj->getSingleInvoiceDetails();
	                 $this->formObj->populate($editdata);

	 	 			 if( empty($editdata['credit_amount']) && !empty($editdata['debit_amount'])){
	 	 			 	$this->formObj->credit_amount->setAttrib('disabled','disabled');
	 	 			 	$this->formObj->credit->setAttrib('disabled','disabled');
	 	 			 	// $this->formObj->removeElement('credit[]');
	 	 			 	
	 	 			 }

	 	 			 if( empty($editdata['debit_amount']) && !empty($editdata['credit_amount'])){
	 	 			 	$this->formObj->debit_amount->setAttrib('disabled','disabled');
	 	 			 	$this->formObj->debit->setAttrib('disabled','disabled');
	 	 			 	// $this->formObj->removeElement('debit[]');
	 	 			 	
	 	 			 }

	 	 			 $this->formObj->getElement('submit')->setLabel('Update');
	 	 		}
	 	 		
	 	 		$template = 'bank';
	 	 		break;	
	 	 	
	 	 	case 'INKOOPEN':
	 	 		if($this->Request['mode'] == 'edit'){

	 	 			 $editdata = $this->ModelObj->getSingleInvoiceDetails();
	 	 			 $this->formObj->getElement('submit')->setLabel('Update');
	                   $this->formObj->populate($editdata);
	 	 		}
	 	 		// echo "dfgdfgfd";die;
	 	 		 $this->formObj->removeElement('ledger_invoice_number');	
	 	 		 $this->formObj->removeElement('credit');	
	 	 		 $template = 'verkoopen';
	 	 		break;

	 	 	case 'VERKOOPEN':
	 	 		if($this->Request['mode'] == 'edit'){

	 	 			 $editdata = $this->ModelObj->getSingleInvoiceDetails();
	 	 			 $this->formObj->getElement('submit')->setLabel('Update');
	                   $this->formObj->populate($editdata);
	 	 		}	
	 	 		 $this->formObj->removeElement('ledger_invoice_number');	
	 	 		 $this->formObj->removeElement('debit');
	 	 		 $template = 'verkoopen';
	 	 		break;		

	 	 	default:
	 	 		 
	 	 		break;
	 	 } 


	 	 $this->view->BookkeepingForm = $this->formObj;
	 	 $this->render($template);
	 }

	 public function addeditinvoiceAction()
	 {	
	 	// echo "<pre>";
	 	// print_r($this->Request);die;

	 	$this->formObj->addBookForm();

	 	if($this->Request['mode']!='edit'){
	 	$form = $this->formAddConditions($this->formObj,$this->view->Request['FormType']);

	 	if($this->getRequest()->isPost()){
                   
                if($form->isValid($this->Request)){

                    $result = $this->ModelObj->SaveInvoice($this->view->Request);

                    // return $resp;
                     global $objSession; 

                     if($result['status'] ==1)
                        $objSession->successMsg = $result['message'];
                     else $objSession->errorMsg = $result['message']; 
                      
                     $resp = $result;
                                 

                }else{
                		$errors = $form->getMessages();
                		// print_r($errors);
                		foreach ($errors as $key => $value) {
                			$messagekey = key($value);
                			$message = $value[$messagekey]; 	
                			$messages[] = array('element'=>$key,'message'=> $message );
                		}

                	 $resp = array('status'=>0,'messages'=>$messages);
                }
            }
        }
        else { 

        		$this->view->title = 'Edit Invoice'; 
	            $form = $this->formAddConditions($this->formObj,$this->view->Request['FormType']);
	             if($this->getRequest()->isPost()){
	                if($form->isValid($this->Request)){
	                         // echo "valid";die;
	                    $result = $this->ModelObj->EditInvoice($this->Request);

	                     global $objSession; 

	                     if($result['status'])
	                        $objSession->successMsg = $result['message'];
	                     else $objSession->errorMsg = $result['message'];   
	                     
	                     $resp = $result;

	                }else{

	                    $errors = $form->getMessages();
                		// print_r($errors);
                		foreach ($errors as $key => $value) {
                			$messagekey = key($value);
                			$message = $value[$messagekey]; 	
                			$messages[] = array('element'=>$key,'message'=> $message );
                		}

                	 $resp = array('status'=>0,'messages'=>$messages);

	                }
	            }
	 	 }
	 	echo Commonfunction::jsonconvert($resp);die;
	 }

	 public function formAddConditions($form,$form_type)
	 {
	 	switch ($form_type) {
	 		
	 	 	case 'MEMORIAL':
	 	 		 
	 	 		// break;
	 	 	case 'POSTBANK':
	 	 		 
	 	 		// break;

	 	 	case 'BANK':
	 	 		 	
	 	 		 $form->getElement('customer_id')->setRequired(false);
	 	 		 $form->getElement('credit_amount')->setRequired(false);
	 	 		 $form->getElement('supplier_id')->setRequired(false);
	 	 		  $form->getElement('debit_amount')->setRequired(false);
	 	 		  $form->getElement('invoice_definition')->setRequired(false);
	 	 		break;	
	 	 	
	 	 	case 'INKOOPEN':
	 	 		 $form->getElement('customer_id')->setRequired(false);
	 	 		  $form->getElement('credit_amount')->setRequired(false);
	 	 		break;

	 	 	case 'VERKOOPEN':
	 	 		  $form->getElement('supplier_id')->setRequired(false);
	 	 		  $form->getElement('debit_amount')->setRequired(false);
	 	 		break;		

	 	 	default:
	 	 		 
	 	 		break;
	 	 } 

	 	 return $form;
	 }


}