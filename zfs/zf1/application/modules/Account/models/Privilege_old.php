<?php
class Account_Model_Privilege extends Account_Model_Accountmanager
{
	/**
     * All the Function Related to Users Privilege
      @Auth : SJM Softech Pvt. Ltd
      @Create Date : 2nd-February-2017
      @Description : This module Consists All the methods which manage the User's Privilege
     * */

    public function __construct()
    {   
		parent::__construct();
	}
	
	
	public function LevelPrivilege($levelId=NULL){
		
		try{
			if($levelId>3){
				$Where = " DPT.level_id=".$levelId;
					
				$select = $this->_db->select()
						->from(array('DPT'=>DEFAULT_PRIVILLAGE), array('*'))
						->joininner(array('MT'=>MODULES), 'MT.module_id=DPT.module_id', array('module_name'))
						->where($Where)
						->where('MT.module_parent=0')
						->order('MT.module_id');
						//echo $select->__tostring(); die;
				$MainModules = $this->getAdapter()->fetchAll($select);
				$privileg = array();
				foreach($MainModules as $key=>$module){
					
					$SubModule = $this->getsubmodule($module['module_id']);
					
					$privileg[$key]['module_id']   = $module['module_id'];
					$privileg[$key]['module_name'] = $module['module_name'];
					$privileg[$key]['submodule']   = $SubModule;
				}
				
				$customerextraModule = $this->CustomerCustomPrivilege(count($privileg));
				
				if(count($customerextraModule)>0){
					$privileg = $privileg + $customerextraModule;
				}
				//echo"<pre>";print_r($privileg);die;
				return $privileg;
			}
			else{return array();}
		}
		catch(Exception $e){
			die('Something is wrong: ' . $e->getMessage());
		}
	}
	
	public function getsubmodule($id){
		$select = $this->_db->select()
				->from(array('DPT'=>DEFAULT_PRIVILLAGE), array('*'))
				->joininner(array('MT'=>MODULES), 'MT.module_id=DPT.module_id', array('*'))
				->where('MT.module_parent='.$id)
				->where('DPT.level_id=5')
				->order('MT.module_id');
				//echo $select->__tostring(); die;
		$SubModules = $this->getAdapter()->fetchAll($select);
		return $SubModules;
	}
	
	
	public function CustomerCustomPrivilege($keycount){
		$userId = Zend_Encript_Encription::decode($this->getData['token']);
		$select = $this->_db->select()
					->from(array('UPT'=>USERS_PRIVILLAGE), array('*'))
					->joininner(array('MT'=>MODULES), 'MT.module_id=UPT.module_id', array('module_name'))
					->joininner(array('DPT'=>DEFAULT_PRIVILLAGE), 'UPT.module_id!=DPT.module_id', array(''))
					->where('UPT.user_id='.$userId)
					->where('MT.module_parent=0')
					->order('MT.module_id');
					//echo $select->__tostring(); die;
			$MainModules = $this->getAdapter()->fetchAll($select);
			
		$customeprivileg = array();
		foreach($MainModules as $key=>$module){
			
			$CustomeSubModule = $this->getCustomSubmodule($module['module_id'],$userId);
			
			$customeprivileg[$keycount]['module_id']   = $module['module_id'];
			$customeprivileg[$keycount]['module_name'] = $module['module_name'];
			$customeprivileg[$keycount]['submodule']   = $CustomeSubModule;
			$keycount+1;
		}
		return $customeprivileg;
	}
	
	
	public function getCustomSubmodule($id,$userId){
		$select = $this->_db->select()
				->from(array('UPT'=>USERS_PRIVILLAGE), array('*'))
				->joininner(array('MT'=>MODULES), 'MT.module_id=UPT.module_id', array('*'))
				->where('UPT.user_id='.$userId)
				->where('MT.module_parent='.$id)
				->order('MT.module_id');
				//echo $select->__tostring(); die;
		$CustomSubModules = $this->getAdapter()->fetchAll($select);
		return $CustomSubModules;
	}
	
	
	public function UpdateUserPrivilege(){
		
		$userId = Zend_Encript_Encription::decode($this->getData['token']);
		$deletemodule = array();
		$PrivilegeArr = array();
		$key = 0;
		
		
		$UpdatedModules = (($this->getData['privilegetype']==0) ? $this->getData['ModuleArr'] : (isset($this->getData['depotModuleArr']) ? $this->getData['depotModuleArr'] : $this->getData['ModuleArr']));
		
		//echo"<pre>";print_r($UpdatedModules);die;
		
		foreach($UpdatedModules as $moduleId){
			$deletemodule[] = $moduleId;
			$result = $this->checkprivilege(array('user_id'=>$userId,'module_id'=>$moduleId));
			
			if($result==0){
				$PrivilegeArr[$key]['user_id'] = $userId;
				$PrivilegeArr[$key]['module_id'] = $moduleId;
				$key++;
			}
		}
		
		if($this->insertInToTable(USERS_PRIVILLAGE,$PrivilegeArr)){
			if(count($deletemodule)>0){
				$AllId = commonfunction :: implod_array($deletemodule);
				$this->_db->delete(USERS_PRIVILLAGE,"user_id=".$userId." AND module_id NOT IN(".$AllId.")");
			}
			return true;
		}
	}
	
	
	public function checkprivilege($data=array()){
		
		$select = $this->_db->select()
				->from(array('UPT'=>USERS_PRIVILLAGE), array('*'))
				->where('UPT.module_id='.$data['module_id'])
				->where('UPT.user_id='.$data['user_id']);
				//echo $select->__tostring(); die;
		$result = $this->getAdapter()->fetchAll($select);
		return (count($result)>0) ? 1 : 0;
	}
	
	
	public function UserPrivilege(){
		$userId = Zend_Encript_Encription::decode($this->getData['token']);
	
		$select = $this->_db->select()
				->from(array('UPT'=>USERS_PRIVILLAGE), array('priv_id','module_id'))
				->where('UPT.user_id='.$userId);
				//echo $select->__tostring(); die;
		$result = $this->getAdapter()->fetchAll($select);
		
		$usermodule = array();
		if(count($result)>0){
			foreach($result as $value){
				$usermodule[$value['module_id']] = $value['module_id'];
			}
		}
		return $usermodule;
	}
	
	
	public function CustomerDepotPrivilege(){
		
		$userId = Zend_Encript_Encription::decode($this->getData['token']);
		
		$depotId = $this->getDepotID($userId);
		
		$select = $this->_db->select()
				->from(array('UPT'=>USERS_PRIVILLAGE), array('priv_id','user_id'))
				->joininner(array('MT'=>MODULES), 'MT.module_id=UPT.module_id', array('module_id','module_name','module_parent','module_level'))
				->where('UPT.user_id='.$depotId)
				->order('MT.module_id')
				->order('MT.module_parent');
				//echo $select->__tostring(); die;
		$result = $this->getAdapter()->fetchAll($select);
		//echo"<pre>";print_r($result);die;
		$depotPrivilege = array();
		if(count($result)>0){
			foreach($result as $value){
				if($value['module_parent']==0){
					$depotPrivilege['module'][$value['module_id']] = $value['module_name'];
				}
				else{
					$depotPrivilege['submodule'][$value['module_parent']][$value['module_id']] = $value['module_name'];
					
				}
			}
		}
		
		return $depotPrivilege;
		
	}

}