<?php
	/**

     * Controll the Term (For Terms & Condition) module

     * @Auth : SJM softech Pvt. Ltd.

     * @Created Date: 28th-December-2016

     * @Description : Controll the functionality related to Account terms & condition

     **/

class Account_TermsController extends Zend_Controller_Action

{

	public $TermObj;

	public $formObj;

	public $Request = array();

	

   /**

	* Initialize action controller

	* Function : init()

	* Auto create,loads and call the default object of model ,form and layout

	**/

    public function init()

    { 

		$this->_helper->layout->setLayout('main'); 

		try{

			$this->TermObj = new Account_Model_Terms();

			$this->Request = $this->_request->getParams();

			$this->TermObj->getData = $this->Request;

			$this->formObj = new Account_Form_Account();

			

			$this->view->token = (isset($this->Request['token'])) ? $this->Request['token'] : '';

		

		}

		catch(Exception $e){

		  print_r($e->getMessage());die; 

		}

    }

	

	

	public function termconditionAction(){

		global $objSession;

		$termData = $this->TermObj->gettermscondition();

		$this->view->terminfo = $termData;

		

		if($this->getRequest()->isPost()){

			

			if($this->TermObj->addtermcondition()){

				$objSession->successMsg = "Depot term & conditions added successfully!!";

				($this->Useconfig['level_id']==4) ? $this->_redirect('Account/myprofile') : $this->_redirect('Account/depotmanager');

			}

			else{

				$objSession->errorMsg = "There is some error, Please try again !";

				$this->_redirect($this->_request->getControllerName().'/termcondition?token='.$this->Request['token']);

			}

		}

	}

	

	

	

	public function depotnotificationAction(){

		global $objSession;

		$DataArr = $this->TermObj->getdepotnotification();

		$this->view->NotifyData = $DataArr;

		

		if($this->getRequest()->isPost()){

			

			if($this->TermObj->adddepotnotification()){

				$objSession->successMsg = "Depot notification added successfully!!";

				($this->Useconfig['level_id']==4) ? $this->_redirect('Account/myprofile') : $this->_redirect('Account/depotmanager');

			}

			else{

				$objSession->errorMsg = "There is some error, Please try again !";

				$this->_redirect($this->_request->getControllerName().'/depotnotification?token='.$this->Request['token']);

			}

		}

	}

}