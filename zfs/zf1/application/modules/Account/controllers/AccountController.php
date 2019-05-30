<?php
/**

 * Controll the Account module

 * @Auth : SJM softech Pvt. Ltd.

 * @Created Date: 03rd-October-2016

 * @Description : Controll the functionality related to Account

 **/

class Account_AccountController extends Zend_Controller_Action

{

	public $AccountObj;

	public $formObj;

	public $Request = array();

	public $countries = array();

	public $languages = array();

	public $depotlists = array();

	public $previousPage = '';

	

   /**

	* Initialize action controller

	* Function : init()

	* Auto create,loads and call the default object of model ,form and layout

	**/

    public function init()

    { 

		$this->_helper->layout->setLayout('main'); 

		try{

			$this->AccountObj = new Account_Model_Accountmanager();

			$this->Request = $this->_request->getParams();

			$this->AccountObj->getData = $this->Request;

			$this->formObj = new Account_Form_Account();

			if(!empty($this->Request['token'])){

				$this->formObj->mode = 'edit';

			}

			$this->countries = $this->AccountObj->getCountryList();

			$this->languages = $this->AccountObj->AdminLanguage();

			$this->depotlists = $this->AccountObj->getDepotList();

			$hubusers = $this->AccountObj->Gethubuserlist();

		    $this->view->Request = $this->Request;

			$this->formObj->listCountry = commonfunction::scalarToAssociative($this->countries,array(COUNTRY_ID,'country_code_name'));

			$this->formObj->languageList = commonfunction::scalarToAssociative($this->languages,array(LANGUAGE_ID, LANGUAGE_NAME));

			$this->formObj->listDepot = commonfunction::scalarToAssociative($this->depotlists,array(ADMIN_ID, COMPANY_NAME));

			$this->formObj->listHubuser = commonfunction::scalarToAssociative($hubusers,array(ADMIN_ID, COMPANY_NAME));

			$this->formObj->sessionValue = $this->AccountObj->Useconfig;

		    $this->view->token = (isset($this->Request['token'])) ? $this->Request['token'] : '';

			$this->view->ModelObj = $this->AccountObj;

		}

		catch(Exception $e){

		  print_r($e->getMessage());die; 

		}

    }



    public function indexAction()

    {

		// action body

    }

	

	

   /**

	* administrator action

	* Function : administratorAction()

	* Description : view administrator list

	**/

	public function myprofileAction()

    {

		$this->view->filteruser = (isset($this->Request['username'])) ? $this->Request['username'] :'' ;

		$this->view->filtercompany = (isset($this->Request['company'])) ? $this->Request['company'] :'' ;

		$this->view->filterpostcode = (isset($this->Request['postcode'])) ? $this->Request['postcode'] :'' ;

		

		$record = $this->AccountObj->administratorList($this->Request);

		$this->view->records = $record;

	  /*$this->view->customerlist = array();

	    $this->view->countrylist = array();*/

    }

	

	public function addadministratorAction(){

		//echo 'add details !';//die;

	}

	

	

   /**

	* depotmanager action

	* Function : depotmanagerAction()

	* Description : view all depot users list or filter depot user details

	**/

	public function depotmanagerAction(){

		

		$this->view->filteruser = (isset($this->Request['username'])) ? $this->Request['username'] :'' ;

		$this->view->filtercompany = (isset($this->Request['company'])) ? $this->Request['company'] :'' ;

		$this->view->filterpostcode = (isset($this->Request['postcode'])) ? $this->Request['postcode'] :'' ;

		$this->view->filterdepot = (isset($this->Request['filterdepot'])) ? $this->Request['filterdepot'] :'' ;

		

		$record = $this->AccountObj->depotList($this->Request);

		$this->view->records = $record;

		$this->view->depots =  $this->AccountObj->getDepotList();	// depot list

	}

	

	

	/**

	* addeditdepot action

	* Function : addeditdepotAction()

	* Description : view depot add or edit form and add/update depot user details

	**/

	public function depotformAction(){	

		global $objSession;

		if(!empty($this->Request['token'])){

			$this->formObj->mode = 'edit';

			$record = $this->AccountObj->depotList(array('user_id'=>Zend_Encript_Encription::decode($this->Request['token'])));

			$record2 = $this->AccountObj->getInvoiceBankDetail(array('user_id'=>Zend_Encript_Encription::decode($this->Request['token'])));

			$record[0] = array_merge($record[0],$record2);

			$this->formObj->populate($record);

			$this->formObj->adminLogo = $record[0]['logo'];

			$this->formObj->invoiceLogo = $record[0]['invoice_logo'];

		}

		

		foreach($this->countries as $index=>$country){

			if($country['currency']!=''){

				$currencyArr[$country['country_id']] = $country['currency'];

			}

		}

		$this->formObj->CurrencyList = $currencyArr;

		

		$this->formObj->invoiceSeries = str_pad($this->AccountObj->getNextInvoiceSeries(),4,0,STR_PAD_LEFT);

		$this->formObj->LevelId  = '4';

		$this->formObj->parentId  = $this->AccountObj->Useconfig['user_id'];

		

		$this->view->addeditdeopt = $this->formObj->DepotForm();

		$this->view->depotaction = "Add";

		if($this->getRequest()->isPost()){

		   if($this->formObj->isValid($this->getRequest()->getPost())){

				$DataArr = $this->Request;

				//echo"<pre>";print_r($this->Request);die;

				if((!empty($this->Request['token'])) && (!empty($this->Request['modevalue']))){

					if($this->AccountObj->editUser($DataArr)){

					//$this->sendAccountEmail();

					$this->_redirect($this->_request->getControllerName() . '/settings?token='.$this->Request['token']);

						//$objSession->successMsg = "Record Updated successfully!!";

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}

				else{

					if($result = $this->AccountObj->addNewUser($DataArr)){

						//$this->sendAccountEmail();

						$this->_redirect($this->_request->getControllerName() . '/settings?token='.Zend_Encript_Encription::encode($result));

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}

				$this->_redirect($this->_request->getControllerName().'/depotmanager');

		   }

		   else{

		   		$objSession->errorMsg = "Please fill all mandatory fields!";

		   		$this->formObj->populate($this->getRequest()->getPost());

		   		$this->formObj;

		   }

		}

		elseif((isset($this->Request['token'])) && (!empty($this->Request['token']))){

			$this->view->depotaction = "Edit";

			$this->formObj->populate($record[0]);

			$this->formObj;

		}

	}

	

	

   /**

	* customer action

	* Function : customerAction()

	* Description : view all customers list or filter customer details

	**/

	public function customerAction(){

		

		$this->view->filteruser = (isset($this->Request['username'])) ? $this->Request['username'] :'' ;

		$this->view->filtercompany = (isset($this->Request['company'])) ? $this->Request['company'] :'' ;

		$this->view->filterpostcode = (isset($this->Request['postcode'])) ? $this->Request['postcode'] :'' ;

		$this->view->filterdepot = (isset($this->Request['filterdepot'])) ? $this->Request['filterdepot'] :'' ;

		

		$record = $this->AccountObj->customerList($this->Request);

		$this->view->records = 	$record;

		$this->view->depots  =  $this->AccountObj->getDepotList();	// depot list

		$this->view->customerList = $this->AccountObj->getCustomerList();

	}

	

   /**

	* customeroperator action

	* Function : customeroperatorAction()

	* Description : view all customer operators list or filter customer operator details

	**/

	public function customeroperatorAction(){

		

		$this->view->filteruser = (isset($this->Request['username'])) ? $this->Request['username'] :'' ;

		$this->view->filtercompany = (isset($this->Request['company'])) ? $this->Request['company'] :'' ;

		$this->view->filterpostcode = (isset($this->Request['postcode'])) ? $this->Request['postcode'] :'' ;

		

		$record = $this->AccountObj->customeroperatorList($this->Request);

		$this->view->records = $record;

	}

	

   /**

	* operator action

	* Function : operatorAction()

	* Description : view all operators list or filter operator details

	**/

	public function operatorAction(){



		$this->view->filteruser = (isset($this->Request['username'])) ? $this->Request['username'] :'' ;

		$this->view->filtercompany = (isset($this->Request['company'])) ? $this->Request['company'] :'' ;

		$this->view->filterpostcode = (isset($this->Request['postcode'])) ? $this->Request['postcode'] :'' ;

		

		$record = $this->AccountObj->operatorList($this->Request);

		$this->view->records = $record;

	}

	

   /**

	* hubuser action

	* Function : hubuserAction()

	* Description : view all HubUser list or filter hubuser list

	**/

	public function hubuserAction(){

		$this->view->filteruser = (isset($this->Request['username'])) ? $this->Request['username'] :'' ;

		$this->view->filtercompany = (isset($this->Request['company'])) ? $this->Request['company'] :'' ;

		$this->view->filterpostcode = (isset($this->Request['postcode'])) ? $this->Request['postcode'] :'' ;

		

		$record = $this->AccountObj->hubuserList($this->Request);

		$this->view->records = $record;

	}

	

   /**

	* huboperator action

	* Function : huboperatorAction()

	* Description : view all HubOperator or filter HubOperator list

	**/

	public function huboperatorAction(){

		

		$this->view->filteruser = (isset($this->Request['username'])) ? $this->Request['username'] :'' ;

		$this->view->filtercompany = (isset($this->Request['company'])) ? $this->Request['company'] :'' ;

		$this->view->filterpostcode = (isset($this->Request['postcode'])) ? $this->Request['postcode'] :'' ;

		

		$record = $this->AccountObj->hubOperatorList($this->Request);

		$this->view->records = $record;

	}

	

   

	

   /**

	* isuseravailable action

	* Function : isuseravailableAction()

	* Description : check username existance for user

	**/

	public function isuseravailableAction(){

		$data = $this->getRequest()->getPost();

		//echo"<pre>";print_r($data);die;

		$record = $this->AccountObj->userNameAvailability($data);

		echo $record;

		exit();

	}

	

   /**

	* emailvalidation action

	* Function : emailvalidationAction()

	* Description : check email and emailinvoice existance for user

	**/

	public function emailvalidationAction(){	

		$data = $this->getRequest()->getPost();

		print_r($data); die;

		//validate Email 

		if(!empty($this['Mode']) && !empty($data['email']))

		{   

		 	if($data['Mode'] =='invoiceemail'){

			   $value = $data['email'];

			   $field = 'invoice_email';	

			}

			else{

			   $value = $data['email'];

			   $field = 'email';

			}

			if($data['formaction']==1){

			   $action = 1;

			}

			else{

			   $action = 0;

			}			

		   $availableemail=$this->AccountObj->validateEmail($data['token'],$value,$field,$action);

		   echo $availableemail;exit();

		}

  	}

	

	

	/**

	* addnewcustomer action

	* Function : addnewcustomerAction()

	* Description : view customer addform and add new customer details

	**/

	public function customerformAction(){	

		global $objSession;

		if(!empty($this->Request['token'])){

			$this->formObj->mode = 'edit';

			$record = $this->AccountObj->customerList(array('user_id'=>$this->Request['token']));

			$record1 = $this->AccountObj->getForwardersGLSNLDetail(array('user_id'=>$this->Request['token']));

			$this->formObj->adminLogo = $record['Records'][0]['logo'];

		}

		

		$this->formObj->LevelId='5';

		$this->view->addeditcustomer = $this->formObj->CustomerForm();

		$this->view->customeraction = "Add";

		

		if($this->getRequest()->isPost()){

			

		   if($this->formObj->isValid($this->getRequest()->getPost())){

				//echo"<pre>";print_r( $this->Request);die;

				$DataArr = $this->Request;

				if((!empty($this->Request['token'])) && (!empty($this->Request['modevalue']))){

					

					if($this->AccountObj->editUser($DataArr)){

						$this->_redirect($this->_request->getControllerName() . '/settings?token='.$this->Request['token']);

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}

				else{

					if($result = $this->AccountObj->addNewUser($DataArr)){

						$this->_redirect($this->_request->getControllerName() . '/settings?token='.Zend_Encript_Encription::encode($result));

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

					

				}

		   }

		   else{

		   		$objSession->errorMsg = "Please fill all mandatory fields!";

		   		$this->formObj->populate($this->Request);

		   		$this->formObj;

				

				

		   }

		}

		elseif((isset($this->Request['token'])) && (!empty($this->Request['token']))){

			$this->view->customeraction = "Edit";

			//echo "<pre>";print_r($record[0]); die;

			$alldata = array_merge($record['Records'][0] , $record1);

			$this->formObj->populate($alldata);

			$this->formObj;

		}

	}

	

	

	public function operatorformAction(){

		global $objSession;

		if(!empty($this->Request['token'])){

			$this->formObj->mode = 'edit';

			$record = $this->AccountObj->operatorList(array('user_id'=>Zend_Encript_Encription :: decode($this->Request['token'])));

			$this->formObj->adminLogo = $record[0]['logo'];

		}

		$this->formObj->LevelId='6';

		

		$this->view->addeditoperator = $this->formObj->OperatorForm();

		$this->view->operatoraction = "Add";

		

		if($this->getRequest()->isPost()){

			//echo"<pre>";print_r($this->getRequest()->getPost());die;

		   if($this->formObj->isValid($this->getRequest()->getPost())){

				

				$DataArr = $this->Request;

				if((!empty($this->Request['token'])) && (!empty($this->Request['modevalue']))){

					

					if($this->AccountObj->editUser($DataArr)){

						$objSession->successMsg = "Record Updated successfully!!";

						$this->_redirect($this->_request->getControllerName() . '/operator');

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}

				else{

					if($this->AccountObj->addNewUser($DataArr)){

						$objSession->successMsg = "Record added successfully!!";

						$this->_redirect($this->_request->getControllerName() . '/operator');

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}	

		   }

		   else{

		   		$objSession->errorMsg = "Please fill all mandatory fields!";

		   		$this->formObj->populate($this->getRequest()->getPost());

		   		$this->formObj;

		   }

		}

		elseif((isset($this->Request['token'])) && (!empty($this->Request['token']))){

			$this->view->operatoraction = "Edit";

			$this->formObj->populate($record[0]);

			$this->formObj;

		}

	}

	

	

	public function customeroperatorformAction(){

		global $objSession;

		$customerlists = $this->AccountObj->getCustomerList();

		$this->formObj->customerlist = commonfunction::scalarToAssociative($customerlists,array(ADMIN_ID, COMPANY_NAME));

		$this->formObj->LevelId='10';

		if((isset($this->Request['token'])) && (!empty($this->Request['token']))){

		$record = $this->AccountObj->customeroperatorList(array('user_id'=>Zend_Encript_Encription::decode($this->Request['token'])));

		$this->formObj->adminLogo = $record[0]['logo'];

		}

		$this->view->addeditcustomeroperator = $this->formObj->CustomerOperatorForm();

		

		if($this->getRequest()->isPost()){

			//echo"<pre>";print_r($this->Request);die;

			$DataArr = $this->Request;

		   if($this->formObj->isValid($this->getRequest()->getPost())){

				if((!empty($this->Request['token'])) && (!empty($this->Request['submit']))){

					if($this->AccountObj->editUser($DataArr)){

						$objSession->successMsg = "Record Updated successfully!!";

						$this->_redirect($this->_request->getControllerName().'/customeroperator');

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}else{

					if($this->AccountObj->addNewUser($DataArr)){

						$objSession->successMsg = "Record added successfully!!";

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}	

		   }

		   else{

		   		$objSession->errorMsg = "Please fill all mandatory fields!";

		   		$this->formObj->populate($this->getRequest()->getPost());

		   		$this->formObj;

		   }

		}

		elseif((isset($this->Request['token'])) && (!empty($this->Request['token']))){

			$this->view->customeroperatoraction = "Edit";

			$this->formObj->populate($record[0]);

			$this->formObj;

		}

	}

	

	

	public function hubuserformAction(){

		global $objSession;

		if(!empty($this->Request['token'])){

			$this->formObj->mode = 'edit';

		}

		

		$this->formObj->LevelId='7';

		$this->view->addedithubuser = $this->formObj->HubuserForm();

		$this->view->hubuseraction = "Add";

		

		if($this->getRequest()->isPost()){

			

		   if($this->formObj->isValid($this->getRequest()->getPost())){

				//echo"<pre>";print_r($this->getRequest()->getPost());die;

				$DataArr = $this->Request;

				if((!empty($this->Request['token'])) && (!empty($this->Request['modevalue']))){

					

					if($this->AccountObj->editUser($DataArr)){

						$objSession->successMsg = "Record Updated successfully!!";

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}

				else{

					if($this->AccountObj->addNewUser($DataArr)){

						$objSession->successMsg = "Record added successfully!!";

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}	

		   }

		   else{

		   		$objSession->errorMsg = "Please fill all mandatory fields!";

		   		$this->formObj->populate($this->getRequest()->getPost());

		   		$this->formObj;

		   }

		}

		elseif((isset($this->Request['token'])) && (!empty($this->Request['token']))){

			$this->view->hubuseraction = "Edit";

			$record = $this->AccountObj->hubuserList(array('user_id'=>Zend_Encript_Encription:: decode($this->Request['token'])));

			$this->formObj->populate($record[0]);

			$this->formObj;

		}

	}

	

	

	public function huboperatorformAction(){

		global $objSession;

		$this->formObj->LevelId='8';

		if(!empty($this->Request['id'])){

			$this->formObj->mode = 'edit';

		}

		$this->view->addedithuboperator = $this->formObj->HubOperatorForm();

		$this->view->huboperatoraction = "Add";

		

		if($this->getRequest()->isPost()){

		  

		  if($this->formObj->isValid($this->getRequest()->getPost())){

				//echo"<pre>";print_r($this->getRequest()->getPost());die;

				$DataArr = $this->Request;

				if((!empty($this->Request['id'])) && (!empty($this->Request['modevalue']))){

					if($this->AccountObj->editUser($DataArr)){

						$objSession->successMsg = "Record Updated successfully!!";

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}

				else{

					

					if($this->AccountObj->addNewUser($DataArr)){

						$objSession->successMsg = "Record added successfully!!";

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}

		   }

		   else{

		   		$objSession->errorMsg = "Please fill all mandatory fields!";

		   		$this->formObj->populate($this->getRequest()->getPost());

		   		$this->formObj;

		   }

		}

		elseif((isset($this->Request['id'])) && (!empty($this->Request['id']))){

			$this->view->huboperatoraction = "Edit";

			$record = $this->AccountObj->hubOperatorList(array('user_id'=>Zend_Encript_Encription:: decode($this->Request['id'])));

			$this->formObj->populate($record[0]);

			$this->formObj;

		}

	

	}

	

	public function driversettingsAction(){

		$record = $this->AccountObj->driverlist($this->Request);

		//echo"<pre>";print_r($record);die;

		$this->view->records = 	$record;

	}

	

	

	public function driverformAction(){

		global $objSession;

		$this->formObj->LevelId='9';

		if($this->AccountObj->Useconfig['level_id'] == 4){

		   $this->formObj->parentId= $this->AccountObj->Useconfig['user_id'];

		}elseif($this->AccountObj->Useconfig['level_id'] == 6){

		   $this->formObj->parentId= $this->AccountObj->Useconfig['parent_id'];

		}



		$this->view->addeditdriver = $this->formObj->driverForm();

		$this->view->driveraction = "Add";

		if($this->getRequest()->isPost()){

			

		   if($this->formObj->isValid($this->getRequest()->getPost())){

				$DataArr = $this->Request;

				if((!empty($this->Request['token'])) && (!empty($this->Request['modevalue']))){

					

					if($this->AccountObj->editdriver($DataArr)){

						$objSession->successMsg = "Record updated successfully!!";

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}

				else{

					if($this->AccountObj->addnewdriver($DataArr)){

						$objSession->successMsg = "Record added successfully!!";

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}

				$this->_redirect($this->_request->getControllerName().'/driversettings');	

		   }

		   else{

		   		$objSession->errorMsg = "Please fill all mandatory fields!";

		   		$this->formObj->populate($this->getRequest()->getPost());

		   		$this->formObj;

		   }

		}

		elseif((isset($this->Request['token'])) && (!empty($this->Request['token']))){

			$this->view->driveraction = "Edit";

			$record = $this->AccountObj->driverlist(array('driver_id'=>Zend_Encript_Encription::decode($this->Request['token'])));

			$this->formObj->populate($record[0]);

			$this->formObj;

		}

	

	}

	

	public function changepasswordAction(){

			$this->formObj->changepasswordForm();

			if($this->_request->isPost() && $this->formObj->isValid($this->Request)){

				

				if($this->AccountObj->changepassword($this->Request)){

					echo '<script type="text/javascript">history.go(-2);</script>';

					exit();

				}

				else{

					$this->formObj->populate($this->Request);

					$this->formObj;

				}

			}else{

		   		$this->formObj->populate($this->getRequest()->getPost());

		   		$this->formObj;

		    }

		$this->view->changepassword = $this->formObj;

	}

	

	

	public function deleteAction(){		//print_r($this->Request);die;

		global $objSession;

		$url = $_SERVER['HTTP_REFERER'];

		$previousPage = substr($url,strrpos($url,'/'));

		$data=array();

		if(isset($this->Request['level'])){

			$record = $this->AccountObj->driverlist(array('driver_id'=>Zend_Encript_Encription:: decode($this->Request['token'])));

			$data['user_id'] = $record[0]['user_id'];

			$data['driver_id'] = $record[0]['driver_id'];

		}

		else{

			$data['user_id'] = Zend_Encript_Encription:: decode($this->Request['token']);

		}	

		

		if($data['user_id']>0){

			$this->AccountObj->deleteUser($data);

			$objSession->successMsg = "Record deleted successfully!!";

		}

		$this->_redirect($this->_request->getControllerName().$previousPage);

	}

	

	

	public function driverconfigAction(){

		global $objSession;

		$this->_helper->layout->setLayout('popup');

		$DriverId = Zend_Encript_Encription :: decode($this->Request['token']);

		$record = $this->AccountObj->driverlist(array('driver_id'=>$DriverId));

		

		$this->formObj->drivername = $record[0]['driver_name'];

		$this->view->driverconfig = $this->formObj->drivercofigForm();

		

		if($this->getRequest()->isPost()){	//print_r($this->Request);die;

			$this->Request['user'] = $record[0]['user_id'];

			$this->AccountObj->editdriver($this->Request);

			

			$objSession->successMsg = "Record updated successfully!!";

			

			echo '<script type="text/javascript">parent.window.location.reload();

			 parent.jQuery.fancybox.close();</script>';

			 exit();

		}

		

		$this->formObj->populate($record[0]);

		$this->formObj;

	}

	

	

	public function settingsAction(){

		global $objSession;

		$record = $this->AccountObj->UserDetails(array('user_id'=>Zend_Encript_Encription::decode($this->Request['token'])));

		

		$this->formObj->LevelId = $record['level_id'];

		

		$this->view->Userlevel = $record['level_id'];

		

		$this->formObj->companyName=$record['company_name'];

		

		if($record['level_id']==5){

			$User = 'customer';

			

			$this->view->settingData = $this->formObj->CustomerSettingForm();

			

			$ccbccemailArr = $this->AccountObj->getUserCcBccSetting(array('user_id'=>Zend_Encript_Encription:: decode($this->Request['token'])));

			if(count($ccbccemailArr)>0){

				$record['cc_email'] = '';

				$record['bcc_email'] = '';

				

				foreach($ccbccemailArr as $emaildata){

					if($emaildata['type']==1){

						$record['cc_email'] .= $emaildata['email'].PHP_EOL;

					}else{

						$record['bcc_email'] .= $emaildata['email'].PHP_EOL;

					}

				}

			}

		}

		else{

			$this->formObj->WarehouseList = $this->AccountObj->GetwmsCompanyList();

			

			$User = 'depotmanager';

			$record = $this->AccountObj->GetDepotSettings(array('user_id'=>Zend_Encript_Encription:: decode($this->Request['token'])));

			$this->view->settingData = $this->formObj->DepotSettingForm();

		}

		

		if($this->getRequest()->isPost()){

		

			if($this->AccountObj->UpdateUserSetting($this->Request)){

				

				if($record['level_id']==4){

				   $this->AccountObj->UpdateInvoiceSetting($this->Request);

				}

				

				if($this->Request['level_id']==5){

					$this->AccountObj->UpdateUserCcBccSetting($this->Request);

				}

				$objSession->successMsg = "Record updated successfully!!";

				$this->_redirect($this->_request->getControllerName().'/'.$User);

			}

		}

		

		$this->formObj->populate($record);

		$this->formObj;

	}

	

	public function pickupschedularAction(){

		global $objSession;

		$this->_helper->layout->setLayout('popup');

		

		$UserId = Zend_Encript_Encription :: decode($this->Request['token']);

		$record = $this->AccountObj->getSchedulePickupOfUser($UserId);

		

		$record['name'] 		= (empty($record['name'])) ? $record['uname'] : $record['name'];

		$record['street1'] 		= (empty($record['street1'])) ? $record['ustreet1'] : $record['street1'];

		$record['street2'] 		= (empty($record['street2'])) ? $record['ustreet2'] : $record['street2'];

		$record['postalcode'] 	= (empty($record['postalcode'])) ? $record['upostalcode'] : $record['postalcode'];

		$record['city'] 		= (empty($record['city'])) ? $record['ucity'] : $record['city'];

		$record['phoneno'] 		= (empty($record['phoneno'])) ? $record['uphoneno'] : $record['phoneno'];

		$record['country'] 		= (empty($record['country'])) ? $record['ucountry'] : $record['country'];

		

		$this->view->pickupschedular = $this->formObj->PickupSchedularForm();

		

		if($this->getRequest()->isPost()){	

			 

			 $this->Request['user_id'] = $UserId;

			 $this->AccountObj->addschedulepickup($this->Request);

			 

			 $objSession->successMsg = "Record updated successfully!!";

			 echo '<script type="text/javascript">parent.window.location.reload();

			 parent.jQuery.fancybox.close();</script>';

			 exit();

		}

		

		$this->formObj->populate($record);

		$this->formObj;

	}

}