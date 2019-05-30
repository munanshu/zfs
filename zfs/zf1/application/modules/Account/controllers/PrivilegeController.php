<?php
	/**

     * Controll the Senderaddress module

     * @Auth : SJM softech Pvt. Ltd.

     * @Created Date: 18th-January-2017

     * @Description : Controll the functionality related to Customer Senderaddress

     **/

class Account_PrivilegeController extends Zend_Controller_Action

{

	public $AccountPrivilegeObj;

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

			$this->AccountPrivilegeObj = new Account_Model_Privilege();

			$this->Request = $this->_request->getParams();

			

			$this->AccountPrivilegeObj->getData = $this->Request;

			

			$this->view->token = (isset($this->Request['token'])) ? $this->Request['token'] : '';

			

		}

		catch(Exception $e){

		  print_r($e->getMessage());die; 

		}

    }

	

	

	public function indexAction(){

		

		$this->view->privileges = $this->AccountPrivilegeObj->GetLevelnPrivileges();

		//echo"<pre>";print_r($this->view->privileges);die;

	}

	

	

	public function viewAction(){

		global $objSession;

		$LevelId = Zend_Encript_Encription::decode($this->Request['token']);

		

		$this->view->LevelName = $this->AccountPrivilegeObj->getLevelName($LevelId);

		

		$this->view->Allprivileges = $this->AccountPrivilegeObj->GetAllModules();

		

		$this->view->DefaultPrivilege = $this->AccountPrivilegeObj->LevelAllDefaultPrivilege($LevelId);

		

		if($this->getRequest()->isPost()){

			

			$this->AccountPrivilegeObj->UpdateDefaultPrivilege();

			$objSession->successMsg = "Privilege updated successfully!!";

			$this->_redirect($this->_request->getControllerName().'/view?token='.$this->Request["token"]);

		}

		//echo"<pre>";print_r($this->view->DefaultPrivilege);die;

	}

	

	

	public function userprivilegeAction(){

		global $objSession;

		$UserData = $this->AccountPrivilegeObj->GetUserLevel(Zend_Encript_Encription::decode($this->Request['token']));

		

		$level = $UserData['level_id'];

		$Records = $this->AccountPrivilegeObj->LevelPrivilege($level);

		

		$this->view->customerCompany = $this->AccountPrivilegeObj->CompanyName(Zend_Encript_Encription::decode($this->Request['token']));

		

		$this->view->LevelName = $UserData['levelName'];

		

		$this->view->LevelId = $UserData['level_id'];

		

		$this->view->DepotPrivileges = $this->AccountPrivilegeObj->CustomerDepotPrivilege();

		//echo"<pre>";print_r($this->view->DepotPrivileges);die;

		$this->view->UserPrivilege = $this->AccountPrivilegeObj->UserPrivilege();

		

		

		if($this->getRequest()->isPost()){

			

			$this->AccountPrivilegeObj->UpdateUserPrivilege();

			$objSession->successMsg = "User Privilege updated successfully!!";

			$this->_redirect($this->_request->getControllerName().'/userprivilege?token='.$this->Request["token"]);

		}

		

		$this->view->AllPrevileges = $Records;

	}

	

}







