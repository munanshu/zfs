<?php
class Account_Model_Terms extends Zend_Custom
{
	/**
     * All the Function Related to Account's terms & condition
      @Auth : SJM Softech Pvt. Ltd
      @Create Date : 3rd-October-2016
      @Description : This module Consists All the methods which manage the Terms & Condition
     * */

    public function __construct()
    {   
		parent::__construct();
	}
	
	public function gettermscondition(){
		try{
			$userId = Zend_Encript_Encription:: decode($this->getData['token']);
			if($userId>0){
				
				$where = " UD.user_id=".$userId;
					
				$select = $this->_db->select()
						->from(array('UD'=>USERS_DETAILS), array('company_name'))
						->joinleft(array('TC'=>TERMS_CONDITION), 'UD.user_id=TC.user_id', array('*'))
						->where($where);
						//echo $select->__tostring(); die;
				$result = $this->getAdapter()->fetchRow($select);
				return $result;
			}
			else{return array();}
		}
		catch(Exception $e){
			die('Something is wrong: ' . $e->getMessage());
		}
	}
	
	
	
	public function addtermcondition(){
		
		$this->getData['user_id']=Zend_Encript_Encription:: decode($this->getData['token']);
		
		$resultData = $this->gettermscondition(); 
		
		if(!empty($resultData['term_id'])){
			
			$this->getData['update_date']= new Zend_Db_Expr('NOW()');
			$this->getData['update_by']	 = $this->Useconfig['user_id'];
			$this->getData['update_ip']	 = commonfunction::loggedinIP();	
			
			$this->UpdateInToTable(TERMS_CONDITION,array($this->getData),'term_id='.$resultData['term_id']);return true;
			
		}else{
			
			$this->getData['create_by']	 = $this->Useconfig['user_id'];
			$this->getData['create_ip']	 = commonfunction::loggedinIP();
			
			return ($this->insertInToTable(TERMS_CONDITION,array($this->getData))) ? true : false;
		}
	}
	
	
	public function getdepotnotification(){
		try{
			$userId = Zend_Encript_Encription:: decode($this->getData['token']);
			if($userId>0){
				
				$where = " UD.user_id=".$userId;
					
				$select = $this->_db->select()
						->from(array('UD'=>USERS_DETAILS), array('company_name'))
						->joinleft(array('DN'=>DEPOT_NOTIFICATION), 'UD.user_id=DN.user_id', array('*'))
						->where($where);
						//echo $select->__tostring(); die;
				$result = $this->getAdapter()->fetchRow($select);
				return $result;
			}
			else{return array();}
		}
		catch(Exception $e){
			die('Something is wrong: ' . $e->getMessage());
		}
	}
	
	
	public function adddepotnotification(){
		
		$this->getData['user_id']=Zend_Encript_Encription:: decode($this->getData['token']);
		
		$resultData = $this->getdepotnotification(); 
		
		if(!empty($resultData[NOTIFICATION_ID])){
			
			$this->getData['update_date']= new Zend_Db_Expr('NOW()');
			$this->getData['update_by']	 = $this->Useconfig['user_id'];
			$this->getData['update_ip']	 = commonfunction::loggedinIP();	
			
			$this->UpdateInToTable(DEPOT_NOTIFICATION,array($this->getData),NOTIFICATION_ID.'='.$resultData[NOTIFICATION_ID]);
			return true;
			
		}else{
			
			$this->getData['create_by']	 = $this->Useconfig['user_id'];
			$this->getData['create_ip']	 = commonfunction::loggedinIP();
			
			return ($this->insertInToTable(DEPOT_NOTIFICATION,array($this->getData))) ? true : false;
		}
		
	}

}