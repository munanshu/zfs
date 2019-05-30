<?php
class Account_Model_Privilege extends Account_Model_Accountmanager
{
	/**
     * All the Function Related to All Privilege
      @Auth : SJM Softech Pvt. Ltd
      @Create Date : 2nd-February-2017
      @Description : This module Consists All the methods which manage the User's Privilege
     * */

    public function __construct()
    {   
		parent::__construct();
	}
	
	
	public function GetLevelnPrivileges($data=array()){
		
		$where = (!empty($data['level_id'])) ? ' AND UL.level_id ='.$data['level_id'] : " AND UL.level_id IN(4,5,6,7,10) ";
		$select = $this->_db->select()
							->from(array('UL'=>USERS_LEVEL),array('UL.level_id','levelName'=>'UL.name'))
							->where('UL.level_parent_id !=0'.$where)
							->order('UL.level_id'); //echo $select->__tostring();die;
		$userLevels = $this->getAdapter()->fetchAll($select);
		
		$privilegeMainModule = array();
		
		foreach($userLevels as $userLevel) {
			
			$privilegeMainModule[$userLevel['level_id']] = $this->getPrivilegeMainModule(array('LevelID'=>$userLevel['level_id'])); 
		}	
		//print_r($privilegeMainModule);die;
		return array('UserLevel'=>$userLevels,'LevelMainModule'=>$privilegeMainModule);	
	}
	
		
	public function getPrivilegeMainModule($data=array()) {
		$LevelID = (!empty($data['LevelID'])) ? trim($data['LevelID']) : '0';
		
		$select = $this->_db->select()
							->from(array('UDP'=>DEFAULT_PRIVILLAGE),array('*'))
							->joininner(array('MT'=>MODULES),'MT.module_id=UDP.module_id AND MT.module_parent=0',array('MT.module_name'))
							->where('UDP.level_id =?',$LevelID)
							->order('MT.module_id'); //echo $select->__tostring();die;
		return $this->getAdapter()->fetchAll($select);
	}
		
	
	public function LevelPrivilege($levelId=NULL){
		
		try{
			if($levelId>1){
				
				$MainModules = $this->getPrivilegeMainModule(array('LevelID'=>$levelId));
				
				$privileg = array();
				
				foreach($MainModules as $key=>$module){
					
					$SubModule = $this->getsubmodule($module['module_id'],$levelId);
					
					$privileg[$key]['module_id']   = $module['module_id'];
					$privileg[$key]['module_name'] = $module['module_name'];
					$privileg[$key]['submodule']   = $SubModule;
				}
				
				if($levelId==5){
					$customerextraModule = $this->CustomerCustomPrivilege(count($privileg));
					
					if(count($customerextraModule)>0){
						$privileg = $privileg + $customerextraModule;
					}
				}
				
				return $privileg;
			}
			else{return array();}
		}
		catch(Exception $e){
			die('Something is wrong: ' . $e->getMessage());
		}
	}
	
	
	public function getsubmodule($id,$levelId=NULL){
		$select = $this->_db->select()
				->from(array('DPT'=>DEFAULT_PRIVILLAGE), array('*'))
				->joininner(array('MT'=>MODULES), 'MT.module_id=DPT.module_id', array('*'))
				->where('MT.module_parent='.$id)
				->where('DPT.level_id='.$levelId)
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
		
		$UpdatedModules = ((isset($this->getData['privilegetype'])) && ($this->getData['privilegetype']==0) ? $this->getData['ModuleArr'] : (isset($this->getData['depotModuleArr']) ? $this->getData['depotModuleArr'] : (isset($this->getData['ModuleArr'])) ? $this->getData['ModuleArr'] : array()));
		
		if(count($UpdatedModules)>0){
			foreach($UpdatedModules as $moduleId){
				$deletemodule[] = $moduleId;
				$result = $this->checkUserprivilege(array('user_id'=>$userId,'module_id'=>$moduleId));
				
				if($result==0){
					$PrivilegeArr[$key]['user_id'] = $userId;
					$PrivilegeArr[$key]['module_id'] = $moduleId;
					$key++;
				}
			}
			$this->insertInToTable(USERS_PRIVILLAGE,$PrivilegeArr);
		}	
		
		if(count($deletemodule)>0){
			$AllId = commonfunction :: implod_array($deletemodule);
			$this->_db->delete(USERS_PRIVILLAGE,"user_id=".$userId." AND module_id NOT IN(".$AllId.")");
		}
		else{
			$this->_db->delete(USERS_PRIVILLAGE,"user_id=".$userId);
		}
		return true;
	}
	
	
	public function checkUserprivilege($data=array()){
		
		$select = $this->_db->select()
				->from(array('UPT'=>USERS_PRIVILLAGE), array('*'))
				->where('UPT.module_id='.$data['module_id'])
				->where('UPT.user_id='.$data['user_id']);
				//echo $select->__tostring(); die;
		$result = $this->getAdapter()->fetchAll($select);
		//echo"<pre>";print_r($result);die;
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
	
	public function getLevelName($LevelId=NULL){
		
	   $select = $this->_db->select()
				 ->from(array('ULT'=>USERS_LEVEL),array('LevelName'=>'ULT.name'))
				 ->where("level_id=".$LevelId);
				// echo $select->__toString();die;
	   $result = $this->getAdapter()->fetchRow($select);
	   return $result['LevelName'];
	}
	
	public function GetAllModules(){
		
		$select = $this->_db->select()
					->from(array('MT'=>MODULES),array('*'))
					->where('MT.module_parent=0')
					->order('MT.module_id'); //echo $select->__tostring();die;
		$MainModules = $this->getAdapter()->fetchAll($select);
		
		$privileg = array();
				
		foreach($MainModules as $key=>$module){
			
			$SubModule = $this->getAllsubmodule($module['module_id']);
			
			$privileg[$key]['module_id']   = $module['module_id'];
			$privileg[$key]['module_name'] = $module['module_name'];
			$privileg[$key]['submodule']   = $SubModule;
		}
		
		return $privileg;
	
	}
	
	public function getAllsubmodule($id){
		$select = $this->_db->select()
				->from(array('MT'=>MODULES), array('*'))
				->where('MT.module_parent='.$id)
				->order('MT.module_id');
				//echo $select->__tostring(); die;
		$SubModules = $this->getAdapter()->fetchAll($select);
		return $SubModules;
	}
	
	public function LevelAllDefaultPrivilege($LevelId=NULL){
		$DefaultModules = array();
		if(!empty($LevelId)){
			$select = $this->_db->select()
					->from(array('DPT'=>DEFAULT_PRIVILLAGE), array('*'))
					->joininner(array('MT'=>MODULES), 'MT.module_id=DPT.module_id', array(''))
					->where('DPT.level_id='.$LevelId)
					->order('MT.module_id');
					//echo $select->__tostring(); die;
			$Modules = $this->getAdapter()->fetchAll($select);
		}
		if(count($Modules)>0){
			foreach($Modules as $module){
				$DefaultModules[$module['module_id']] = $module['module_id'];
			}
		}
		return $DefaultModules;
	}
	
	
	public function UpdateDefaultPrivilege(){
		
		$LevelId = Zend_Encript_Encription::decode($this->getData['token']);
		$deletemodule = array();
		$PrivilegeArr = array();
		$key = 0;
		
		
		$UpdatedModules = $this->getData['ModuleArr'];
		
		foreach($UpdatedModules as $moduleId){
			$deletemodule[] = $moduleId;
			$result = $this->checkDefaultprivilege(array('level_id'=>$LevelId,'module_id'=>$moduleId));
			
			if($result==0){
				$PrivilegeArr[$key]['level_id'] = $LevelId;
				$PrivilegeArr[$key]['module_id'] = $moduleId;
				$key++;
			}
		}
		//echo"<pre>";print_r($deletemodule);die;
		$this->insertInToTable(DEFAULT_PRIVILLAGE,$PrivilegeArr);
		
		if(count($deletemodule)>0){
			$AllId = commonfunction :: implod_array($deletemodule);
			$this->_db->delete(DEFAULT_PRIVILLAGE,"level_id=".$LevelId." AND module_id NOT IN(".$AllId.")");
		}	
		return true;
		
	}
	
	
	public function checkDefaultprivilege($data=array()){
		
		$select = $this->_db->select()
				->from(array('UPT'=>DEFAULT_PRIVILLAGE), array('*'))
				->where('UPT.module_id='.$data['module_id'])
				->where('UPT.level_id='.$data['level_id']);
				//echo $select->__tostring(); die;
		$result = $this->getAdapter()->fetchAll($select);
		return (count($result)>0) ? 1 : 0;
	}
	

}