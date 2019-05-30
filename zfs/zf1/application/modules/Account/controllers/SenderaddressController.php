<?php
	/**

     * Controll the Senderaddress module

     * @Auth : SJM softech Pvt. Ltd.

     * @Created Date: 18th-January-2017

     * @Description : Controll the functionality related to Customer Senderaddress

     **/

class Account_SenderaddressController extends Zend_Controller_Action

{

	public $AccountSenderObj;

	public $formObj;

	public $Request = array();

	public $countries = array();

	public $languages = array();

	public $depotlists = array();

	public $previousPage = '';

	public $CustomerLists = array();

	

   /**

	* Initialize action controller

	* Function : init()

	* Auto create,loads and call the default object of model ,form and layout

	**/

    public function init()

    { 

		$this->_helper->layout->setLayout('main'); 

		try{

			$this->AccountSenderObj = new Account_Model_Senderaddress();

			$this->Request = $this->_request->getParams();

			

			$this->AccountSenderObj->getData = $this->Request;

			

			$this->formObj = new Account_Form_Account();

			

			$this->countries = $this->AccountSenderObj->getCountryList();

			$this->depotlists = $this->AccountSenderObj->getDepotList();

			$this->CustomerLists = $this->AccountSenderObj->getCustomerList();

			

			$this->formObj->listCountry = commonfunction::scalarToAssociative($this->countries,array(COUNTRY_ID,'country_code_name'));

			$this->formObj->listDepot = commonfunction::scalarToAssociative($this->depotlists,array(ADMIN_ID, COMPANY_NAME));

			$this->formObj->customerlist = commonfunction::scalarToAssociative($this->CustomerLists,array(ADMIN_ID, COMPANY_NAME));

			

			$this->view->token = (isset($this->Request['token'])) ? $this->Request['token'] : '';

			

			if(isset($this->Request['address'])){

				$addressArr = $this->AccountSenderObj->getSenderAddressList($this->Request);

				$this->formObj->mode = 'edit';

				$this->formObj->senderLogo = (!empty($addressArr['Addresses'][0]['logo'])) ? $addressArr['Addresses'][0]['logo'] : '';

			}

		}

		catch(Exception $e){

		  print_r($e->getMessage());die; 

		}

    }



	

	

	public function senderaddressAction(){

		$record = $this->AccountSenderObj->getSenderAddressList($this->Request);

		

		$UserDataArr = $this->AccountSenderObj->UserDetails(array('user_id'=>Zend_Encript_Encription::decode($this->Request['token'])));

		$this->view->CustomerName = $UserDataArr['company_name'];

		$this->view->records = 	$record;

	}

	

	

	public function senderaddressformAction(){

		global $objSession;

		

		$UserId = Zend_Encript_Encription :: decode($this->Request['token']);

		$this->view->senderaddress = $this->formObj->SenderAddressForm();

		

		$this->view->addressaction = "Add";

		

		if(isset($this->Request['address'])){

			$addressArr = $this->AccountSenderObj->getSenderAddressList($this->Request);

			$this->view->addressaction = "Edit";

			$this->view->senderLogo = $addressArr['Addresses'][0]['logo'];

			$this->view->address = $this->Request['address'];

			$this->formObj->populate($addressArr['Addresses'][0]);

			$this->formObj;

		}

		

		if($this->getRequest()->isPost()){	

			

			if($this->formObj->isValid($this->getRequest()->getPost())){ 

				//echo"<pre>";print_r($this->Request);die;

				if(isset($this->Request['address'])){

					$this->AccountSenderObj->updatesenderaddress($this->Request);

					$objSession->successMsg = "Record updated successfully!!";

				}

				else{

					if($this->AccountSenderObj->addsenderaddress($this->Request)){

						$objSession->successMsg = "Sender address added successfully!!";

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}

				$this->_redirect($this->_request->getControllerName().'/senderaddress?token='.$this->Request['token']);

			} 

			else{

				$objSession->errorMsg = "Please fill all mandatory fields!";

			}

		}

	}

	

	

	public function senderaddresscountryAction(){

		global $objSession;

		$this->_helper->layout->setLayout('popup');

		

		$this->view->countryList = commonfunction::scalarToAssociative($this->countries,array(COUNTRY_ID,COUNTRY_NAME));

		

		$AddressId = Zend_Encript_Encription:: decode($this->Request['address']);

		

		$SenderCountries = $this->AccountSenderObj->getSenderAddressCountry(array('addressID'=>$AddressId));	

		

		$this->view->sendercountryList = commonfunction::scalarToAssociative($SenderCountries,array(COUNTRY_ID,'CName'));

		

		if($this->getRequest()->isPost()){	

			

			if($this->formObj->isValid($this->getRequest()->getPost())){ 

				//echo"<pre>";print_r($this->Request);die;

				$this->AccountSenderObj->addsenderaddresscountry($this->Request);

				

				$objSession->successMsg = "Senderaddress country added successfully!!";

				

				echo '<script type="text/javascript">parent.window.location.reload();

				parent.jQuery.fancybox.close();</script>';

				exit();

			} 

		}

	}

	

	

	public function defaultcountryAction(){

		global $objSession;

		$this->_helper->layout->setLayout('popup');

		

		$AddressId = Zend_Encript_Encription:: decode($this->Request['address']);

		

		$this->view->AddressCountries = $this->AccountSenderObj->getSenderAddressCountry(array('addressID'=>$AddressId));	

		

		if($this->getRequest()->isPost()){	

			if((isset($this->Request['set_default']))){

				//echo"<pre>";print_r($this->Request);die;

				$this->AccountSenderObj->updatedefaultcountry();

				$objSession->successMsg = "Record updated successfully!!";

			}

			echo '<script type="text/javascript">parent.window.location.reload();

			parent.jQuery.fancybox.close();</script>';

			exit();

		}

	}

	

	

	public function deleteaddressAction(){

		global $objSession;

		if(!empty($this->Request['address'])){

			$this->AccountSenderObj->deletesenderaddress();

			$objSession->successMsg = "Record deleted successfully!!";

		}

		$this->_redirect($this->_request->getControllerName().'/senderaddress?token='.$this->Request['token']);

	}

	

	

	public function countryaddressAction(){

		global $objSession;

		$this->_helper->layout->setLayout('popup');

		

		$this->view->CountryaddressArr = $this->AccountSenderObj->countryaddresslist();	

		

		if($this->getRequest()->isPost()){	

			

			$this->AccountSenderObj->removeaddressdefaultcountry();

			

			$objSession->successMsg = "Default country updated successfully!!";

			

			echo '<script type="text/javascript">parent.window.location.reload();

			parent.jQuery.fancybox.close();</script>';

			exit();

		}

	}

}