<?php
//ini_set('display_errors','1');
class Account_NewsletterController extends Zend_Controller_Action

{

	public $NewsletterObj;

	public $Request = array();

	public $mode;

	

	/* Initialize action controller here */

    public function init()

    {

		$this->_helper->layout->setLayout('main'); 

		try{

			$this->NewsletterObj = new Account_Model_Newsletter();

			

			$this->Request = $this->_request->getParams();

			

			$this->mode = (!empty($this->Request['token'])) ? 'edit' : 'add';

			

			$this->NewsletterObj->getData = $this->Request;

			

			$this->view->token = (isset($this->Request['token'])) ? $this->Request['token'] : '';

			

		}

		catch(Exception $e){

		  print_r($e->getMessage());die; 

		}

        

    }





    public function indexAction()

    {

        $this->view->templates = $this->NewsletterObj->GetAlltemplates();

		//echo"<pre>";print_r($this->view->templates);die;

    }





    public function newsletterAction()

    {

		global $objSession;

        $this->view->AlluserType = array('Select','All Depot','All customer','All Depot & Customer','Selected Depot','Selected Customer','All Customer Of Selected Depot');

		

		$DepotList = $this->NewsletterObj->getDepotList();

		$this->view->AllDepotList = commonfunction::scalarToAssociative($DepotList,array(ADMIN_ID, COMPANY_NAME));

		

		$CustomerList = $this->NewsletterObj->getCustomerList();

		

		$this->view->AllCustomerList = commonfunction::scalarToAssociative($CustomerList,array(ADMIN_ID, COMPANY_NAME));

		

		$this->view->Mode = $this->mode;

		

		$Record = array();

		

		if((isset($this->Request['token'])) && (!empty($this->Request['token']))){

			

			$templateId = Zend_Encript_Encription:: decode($this->Request['token']);

			$Record = $this->NewsletterObj->GetAlltemplates($templateId);

		}



		$TemplateData = (count($Record)>0) ? $Record[0] : $Record;

		

		$this->view->Name = (isset($TemplateData['template_name'])) ? $TemplateData['template_name'] : '';

		$this->view->Subject = (isset($TemplateData['template_subject'])) ? $TemplateData['template_subject'] : '';

		$this->view->Message = (isset($TemplateData['template_message'])) ? $TemplateData['template_message'] : '';

		

		if($this->getRequest()->isPost()){

			

			if($this->mode=='edit'){

				$templateId = $this->NewsletterObj->UpdateTemplate();

				$objSession->successMsg = "Template updated successfully!!";

			}else{

				$templateId = $this->NewsletterObj->AddTemplate();

				$objSession->successMsg = "Template added successfully!!";

			}

			

			if((isset($this->Request['sendtemplate'])) && $this->Request['usertype']>0){

				$this->UpdateSendnewstemplate($templateId);

				$objSession->successMsg = "Add template ans mail send successfully!!";

			}

			$this->_redirect($this->_request->getControllerName());

		}

    }

	

	

	public function UpdateSendnewstemplate($templateId){

		

		$data = $this->Request;

		$data['template_id']= $templateId;

		$data['sender_id'] 	= $this->NewsletterObj->Useconfig['user_id'];

		

		switch($this->Request['usertype']) {

			

			case 1:

				//send to all depot only

				$data['receiver_list'] 	= $this->NewsletterObj->getAllDepotId();

				break;



			case 2:

				//send to all customer only

				$data['receiver_list'] = $this->NewsletterObj->getAllCustomerId();

				break;



			case 3:

				//send to all Depot & Customer

				$AllDepot = $this->NewsletterObj->getAllDepotId();

				$AllCustomer = $this->NewsletterObj->getAllCustomerId();

				$AllUsers = array_merge($AllDepot , $AllCustomer);

				

				$data['receiver_list'] = $AllUsers;

				break;



			case 4:

				//send to Selected Depot

				$data['receiver_list'] = $this->Request['depotId'];

				break;



			case 5:

				//send to Selected Customer

				$data['receiver_list'] = $this->Request['customerId'];

				break;



			case 6:

				//send to all Customer Of Selected Depot

				$data['receiver_list'] = $this->NewsletterObj->getAllCustomerOfDepot($this->Request['depot']);

				break;

		}

			//echo"<pre>";print_r($data);die;

		$this->NewsletterObj->updateSenderReceiverData($data);

		return;

	}



}







