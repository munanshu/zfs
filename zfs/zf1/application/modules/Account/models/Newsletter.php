<?php

class Account_Model_Newsletter extends Account_Model_Accountmanager

{

	/**

     * All the Function Related to Newsletter templates

      @Auth : SJM Softech Pvt. Ltd

      @Create Date : 13-Feb-2017

      @Description : This module Consists All the methods which manage the Newsletters

     * */



    public function __construct()

    {   

		parent::__construct();

		

	}

	

	

	 public function GetAlltemplates($id=NULL){

		$where = ($this->Useconfig['level_id']==1) ? 1 : "NLT.user_id=".$this->Useconfig['user_id'];

		if($id>0){

			$where .=" AND NLT.template_id=".$id;

		}

		$select = $this->_db->select()

							->from(array('NLT'=>NEWSLETTER_TEMPLATES),array('*'))

							->where("NLT.delete_status='0' AND ".$where)

							->order('NLT.template_id'); //echo $select->__tostring();die;

		return $this->getAdapter()->fetchAll($select);

		

	}

	

	

	public function AddTemplate(){

		

		$this->getData['user_id'] = $this->Useconfig['user_id'];

		$this->getData['sender_id'] = $this->Useconfig['user_id'];

		$this->getData['created_ip'] = commonfunction::loggedinIP();

		

		$templateId = $this->insertInToTable(NEWSLETTER_TEMPLATES,array($this->getData));

		

		return $templateId;

	}

	

		

	public function UpdateTemplate(){

		

		$templateId = Zend_Encript_Encription:: decode($this->getData['token']);

		if($templateId>0){

			

			$where = "template_id=".$templateId;

			

			$this->getData['updated_by'] = $this->Useconfig['user_id'];

			$this->getData['updated_date'] = new Zend_Db_Expr('NOW()');

			$this->getData['updated_ip'] = commonfunction::loggedinIP();

			$this->UpdateInToTable(NEWSLETTER_TEMPLATES,array($this->getData),$where);

			

			return $templateId;

		}	

	}

	

	// fetch all depot list

	public function getAllDepotId(){

		$select = $this->_db->select()

				->from(array('UT'=>USERS),array('user_id'))

				->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id',array(''))

				->where('UT.level_id =4')

				->where('UT.delete_status=?', "0")

				->where('UT.user_status=?', "1")

				->where('US.newsletter_email_status=?', "1")

				->order('UT.user_id');	//echo $select->__tostring();die;

		$result = $this->getAdapter()->fetchAll($select);

		$array = array_map('current', $result);

		

		return $array;

	}

	

	

	// fetch all customer list

	public function getAllCustomerId(){

		

		if($this->Useconfig['level_id']==1){

           $filterCustomer = "1";

		}

		elseif($this->Useconfig['level_id']==4){

		   $filterCustomer = "UD.parent_id=".$this->Useconfig['user_id'];

		}

		elseif($this->Useconfig['level_id']==6){

		   $depot_id = $this->getDepotID($this->Useconfig['user_id']);

		   $filterCustomer = "UD.parent_id=".$depot_id;

		}

		

		$select = $this->_db->select()

				->from(array('UT'=>USERS),array('user_id'))

				->joininner(array('UD'=>USERS_DETAILS),'UT.user_id=UD.user_id', array(''))

				->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id',array(''))

				->where('UT.level_id = 5')

				->where('UT.delete_status=?', "0")

				->where('UT.user_status=?', "1")

				->where('US.newsletter_email_status=?', "1")

				->where($filterCustomer)

				->order('UT.user_id');

		$result = $this->getAdapter()->fetchAll($select);

		$array = array_map('current', $result);

		return $array;

	}

	

	

	public function getAllCustomerOfDepot($depotId){

	

		$select = $this->_db->select()

				->from(array('UT'=>USERS),array('user_id'))

				->joininner(array('UD'=>USERS_DETAILS),'UT.user_id=UD.user_id', array(''))

				->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id',array(''))

				->where('UT.level_id = 5')

				->where('UT.delete_status=?', "0")

				->where('UT.user_status=?', "1")

				->where('US.newsletter_email_status=?', "1")

				->where("UD.parent_id=".$depotId)

				->order('UT.user_id');

		$result = $this->getAdapter()->fetchAll($select);

		$array = array_map('current', $result);

		return $array;

	

	}

	

	

	public function sendmail($data){

	    $recieverData = $this->getRecieverNameEmail($data['receiver_list']);

		foreach($recieverData as $value){

			$this->mailOBj = new Zend_Custom_MailManager();

			 $this->mailOBj->emailData['SenderEmail'] = $value['senderemail'];

			 $this->mailOBj->emailData['SenderName']    = $value['sender'];

			 $this->mailOBj->emailData['ReceiverEmail']  =$value['email'];

			 $this->mailOBj->emailData['ReceiverName']  = $value['company_name'];	

			 $this->mailOBj->emailData['MailBody'] = $data['template_message'];;

			 $this->mailOBj->emailData['Subject'] = $data['template_subject'];

			 $this->mailOBj->Send(); 

		  	}

		$where = "template_id=".$data['template_id'];

		

		$updatedData['send_status'] = '1';

		$this->UpdateInToTable(NEWSLETTER_TEMPLATES,array($updatedData),$where);

		return;

	}

	

	

	public function updateSenderReceiverData($data){

		$ReceiverData = array();

		foreach($data['receiver_list'] as $key=>$value){

			$ReceiverData[$key]['template_id'] 	= $data['template_id'];

			$ReceiverData[$key]['sender_id'] 	= $data['sender_id'];

			$ReceiverData[$key]['receiver_id'] 	= $value;

		}

		

		if(count($ReceiverData)>0){

			$this->insertInToTable(NEWSLETTER_RECEIBERS,$ReceiverData);

			

			$this->sendmail($data);

		}

		return;

	}

	public function getRecieverNameEmail($userid){

		$name_email = array();

		foreach($userid as $key=>$val){

		$select = $this->_db->select()

				->from(array('UD'=>USERS_DETAILS),array('company_name','email'))
				->joininner(array('PUD'=>USERS_DETAILS),"PUD.user_id=UD.parent_id",array('company_name AS sender','email as senderemail'))
				->where('UD.user_id=?', $val);

		$name_email[$key] = $this->getAdapter()->fetchRow($select);

		}

		return $name_email;

	

	}	

}