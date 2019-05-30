<?php
class settings_Model_Statuscode extends Zend_Custom
{
     /**
         * get master status code list 
         * Function : getmastererror()
		 * Date : 29/12/2016
         * Here we get master status list
    **/	 
	 public  function getmastererror(){
		try{	$where_filter =1;
				$where = '';
			   if(isset($this->getData['token']) && $this->getData['token'] != ""){
					  $where_filter = "SM.master_id ='".Zend_Encript_Encription:: decode($this->getData['token'])."'";
				}
			    if(isset($this->getData['error_code'])){
					if($this->getData['error_code'] == "1"){ 
					  $where.= " AND SM.status ='1'";
					}elseif($this->getData['error_code'] == "0"){
					  $where.= " AND SM.status ='0'";
					}elseif($this->getData['error_code'] == "2"){
					  $where.= " AND SM.delete_status ='1'";
					}
				}else{
					$where.= ' AND SM.delete_status="0"';
				}
				if(isset($this->getData['error_type']) && $this->getData['error_type'] != "all" &&  $this->getData['error_type'] != ""){
					  $where.= " AND SM.error_type ='".$this->getData['error_type']."'";
				}
			$select = $this->_db->select()
							->from(array('SM'=>STATUS_MASTER),array('SM.*'))
							->joinleft(array('MNT' =>MAIL_NOTIFY_TYPES),'MNT.notification_id=SM.notification_id AND MNT.templatecategory_id =3',array('MNT.notification_name'))
							->where($where_filter.$where)
							->order('SM.status_name ASC');			
			//echo $select->__tostring(); die;				
			$result =  $this->getAdapter()->fetchAll($select);
			return $result;}catch (Exception $e) {
					 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
			}
	  
	  }
  /**
	 * set master status code 
	 * Function : setMasterstatus()
	 * Date : 26/12/2016
	 * Here we update master status error type
  **/	  
	public function setStatusType(){
	  try{
			$this->getData['modify_by'] = $this->Useconfig['user_id'];
			$this->getData['modify_ip'] = commonfunction::loggedinIP();
			$this->getData['modify_date'] = '';
		if(isset($this->getData['table']) && $this->getData['table'] == 'master'){
				$where = 'master_id ='.$this->getData['errorID'];
				return ($this->UpdateInToTable(STATUS_MASTER,array($this->getData),$where)) ? TRUE : FALSE;
		}elseif(isset($this->getData['table']) && $this->getData['table'] == 'list'){
				$where = 'error_id ='.$this->getData['errorID'];
				return ($this->UpdateInToTable(STATUS_LIST,array($this->getData),$where)) ? TRUE : FALSE;
		}else{
				return false;
			
		}
	  }catch (Exception $e) {
					 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	  }
   }
  
     /**
	 * add edit status 
	 * Function : addeditstatuscode()
	 * Date : 27/12/2016
	 * Here we can add and edit Status Code
    **/	 
    public function AddEditStatusCode(){
		try{
			if(isset($this->getData['emailstatus'])){
					if($this->getData['emailstatus'] == '0' && isset($this->getData['new_notification_name']) && $this->getData['new_notification_name'] != ''){
						$inserted = $this->_db->insert(MAIL_NOTIFY_TYPES,array('notification_name' => $this->getData['new_notification_name'],'notification_staus' => $this->getData['notification_sta'],'admin_display'=>'1','templatecategory_id'=>3));
						$this->getData['notification_id'] = ($inserted)?$this->_db->lastInsertId():'0';
					}
				}else{
					unset($this->getData['notification_id']);
				}
			if(isset($this->getData['mode']) && $this->getData['mode'] == 'add'){
				return ($this->insertInToTable(STATUS_MASTER,array($this->getData))) ? TRUE : FALSE;
			}else{
				$where = 'master_id ='.Zend_Encript_Encription:: decode($this->getData['token']);
				$this->getData['modify_by'] = $this->Useconfig['user_id'];
				$this->getData['modify_ip'] = commonfunction::loggedinIP();
				$this->getData['modify_date'] = '';
				return ($this->UpdateInToTable(STATUS_MASTER,array($this->getData),$where)) ? TRUE : FALSE;
			}
		}catch (Exception $e) {
				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		}
       }
     /**
	 * Get notification list 
	 * Function : getNotifications()
	 * Date : 28/12/2016
	 * Here we get notification list 
    **/	  
	public function getNotifications($data=array()){
		$where_filter = "1 ";
		 if(!empty($data['templatecategory_id'])){
			 $where_filter = "NT.templatecategory_id ='3'";
		 }
		$select = $this->_db->select()
					->from(array('NT'=>MAIL_NOTIFY_TYPES),array('ID'=>"NT.notification_id",'Name'=>'NT.notification_name','category_id'=>'NT.templatecategory_id'))
					 ->where('NT.notification_staus ="1" AND '.$where_filter)
					->order('NT.notification_name ASC');
						//echo $select->__tostring();die;
		return $this->getAdapter()->fetchAll($select);
	}
     /**
	 * Get notification list 
	 * Function : getNewStatusCode()
	 * Date : 28/12/2016
	 * Here we get new status code
    **/	
	  public function getNewStatusCode()
	  {
		$select = $this->_db->select() 
				 ->from(array('MS'=>STATUS_MASTER),array("MS.code_numeric"))
				 ->order('master_id DESC')
				 ->limit('1');
				 //echo $select->__tostring();die;
		 $result =  $this->getAdapter()->fetchrow($select);
		 $start     = substr($result['code_numeric'],0,2);
							 $newCode   = (substr($result['code_numeric'],2)+1); 
							 $newStatus = (strlen($newCode)<3) ? $start.'0'.$newCode : $start.$newCode; 
		 return $newStatus;
	  }
     /**
	 * Get Forwarder Status Code List
	 * Function : getForwarderStatusCodeList()
	 * Date : 28/12/2016
    **/	
	  public function getForwarderStatusCodeList($data = array())
	  {		$where = '1';
			if(isset($data['mode']) && $data['mode'] = 'associeateforwarder'){
				$where = 'PEL.error_status = "1" AND PEL.delete_status = "0"';
			}elseif(isset($this->getData['mode']) && $this->getData['mode'] = 'edit'){
				$where = 'PEL.error_id ='.Zend_Encript_Encription:: decode($this->getData['token']);
			}
			if(isset($this->getData['error_code'])){
				if($this->getData['error_code'] == "1"){ 
				  $where.= " AND PEL.error_status ='1'";
				}elseif($this->getData['error_code'] == "0"){
				  $where.= " AND PEL.error_status ='0'";
				}elseif($this->getData['error_code'] == "2"){
				  $where.= " AND PEL.delete_status ='1'";
				}
			}else{
				$where.= ' AND PEL.delete_status="0"';
			}
			if(isset($this->getData['error_type']) && $this->getData['error_type'] != "all" &&  $this->getData['error_type'] != ""){
				  $where.= " AND PEL.error_type ='".$this->getData['error_type']."'";
			}
			if(isset($this->getData['forwarder_id']) && !empty($this->getData['forwarder_id']) ){
				  $where.= " AND PEL.forwarder_id ='".$this->getData['forwarder_id']."'";
			}
			if(isset($this->getData['master_id']) && !empty($this->getData['master_id']) ){
				  $where.= " AND PEL.master_id ='".$this->getData['master_id']."'";
			}
			// print_r($where);die;

			$select = $this->_db->select()
					->from(array('PEL' =>STATUS_LIST),array('PEL.*','concat(FT.forwarder_name," -- ",PEL.error_desc) as associateForwarderDeatil'))
					->joininner(array('FT' =>FORWARDERS),'FT.forwarder_id = PEL.forwarder_id',array('FT.forwarder_name'))
					->joinleft(array('MS' =>STATUS_MASTER),'MS.master_id = PEL.master_id',array('MS.status_name as masterStatus'))
					->where($where);

			$total = $this->getAdapter()->fetchAll($select);
			$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'PEL.forwarder_id','ASC');
			
			 $selectlimited = $this->_db->select()
					->from(array('PEL' =>STATUS_LIST),array('PEL.*','concat(FT.forwarder_name," -- ",PEL.error_desc) as associateForwarderDeatil'))
					->joininner(array('FT' =>FORWARDERS),'FT.forwarder_id = PEL.forwarder_id',array('FT.forwarder_name'))
					->joinleft(array('MS' =>STATUS_MASTER),'MS.master_id = PEL.master_id',array('MS.status_name as masterStatus'))
					->where($where)
					->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
					->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);

		  	$result = $this->getAdapter()->fetchAll($selectlimited);			

		  	return array('Total'=>count($total),'data'=>$result); 
			echo "<pre>";
			// echo count($total)."--".count($result);
			echo $select->__tostring();
			// print_r($OrderLimit);

			die;		
					// echo $where;die;
					// echo $select->__tostring();die;						
            // return $this->getAdapter()->fetchAll($select);
	  }

	  public function getStatusMasterList()
	{
		$select = $this->_db->select()->from(array('SM'=>STATUS_MASTER),array('status_name','master_id'))
				->order('SM.status_name ASC');
		return $this->getAdapter()->fetchAll($select);
				
	}
	  
	 /**
	 * Get selected Forwarder Status Code List
	 * Function : getSelectedForwarderStatus()
	 * Date : 28/12/2016
    **/	
	  public function getSelectedForwarderStatus()
	  {		$where = '1';
			if(isset($this->getData['mode']) && $this->getData['mode'] = 'associeateForwarder' && isset($this->getData['token'])){
				$where = 'PEL.error_status = "1" AND delete_status = "0" AND PEL.master_id = "'.Zend_Encript_Encription:: decode($this->getData['token']).'"';
			}
			$select = $this->_db->select()
					->from(array('PEL' =>STATUS_LIST),array('error_id'))
					->where($where);	
				//echo $select->__tostring();die;					
            return $this->getAdapter()->fetchAll($select);
	  }
	 /**
	 * Update Associate Forwarder Code
	 * Function : UpdateAssociateForwarderCode()
	 * Date : 28/12/2016
    **/	
	public function UpdateAssociateForwarderCode(){
		try{
			$wheredata = $this->getData['error_id'];
			$this->getData['master_id'] = Zend_Encript_Encription:: decode($this->getData['token']);
			$this->getData['modify_by'] = $this->Useconfig['user_id'];
			$this->getData['modify_ip'] = commonfunction::loggedinIP();
			$this->getData['modify_date'] = '';
			//echo "<pre>"; print_r($this->getData); die;
			unset($this->getData['error_id']);
			foreach($wheredata as $where){
				$update = $this->UpdateInToTable(STATUS_LIST,array($this->getData),'error_id="'.$where.'"');
			}
			return ($update)?true : false;
		}catch(Exception $e) {
				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		}
	}
     /**
	 * add edit forwarder status 
	 * Function : AddEditForwarderStatusCode()
	 * Date : 10/03/2017
	 * Here we can add and edit forwarder Status Code
    **/	 
    public function AddEditForwarderStatusCode(){
		try{
			if(isset($this->getData['mode']) && $this->getData['mode'] == 'add'){
				$this->getData['added_by'] = $this->Useconfig['user_id'];
				$this->getData['added_ip'] = commonfunction::loggedinIP();
				$this->getData['forwarder_id'] = $this->getData['country_id'];
				return ($this->insertInToTable(STATUS_LIST,array($this->getData))) ? TRUE : FALSE;
			}elseif(isset($this->getData['mode']) && $this->getData['mode'] == 'edit'){
				$where = 'error_id ='.Zend_Encript_Encription:: decode($this->getData['token']);
				$this->getData['modify_by'] = $this->Useconfig['user_id'];
				$this->getData['modify_ip'] = commonfunction::loggedinIP();
				$this->getData['modify_date'] = '';
				$this->getData['forwarder_id'] = $this->getData['country_id'];
				return ($this->UpdateInToTable(STATUS_LIST,array($this->getData),$where)) ? TRUE : FALSE;
			}
		}catch (Exception $e) {
				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		}
       }
	   
     /**
	 * get sms Detail by master_id
	 * Function : getsmsDetail()
	 * Date : 10/03/2017
	 * Here we can add and edit forwarder Status Code
    **/	 
    public function getsmsDetail($master_id){
		try{
			$select = $this->_db->select()
					->from(array('SSS' =>STATUS_SMS_SETTING),array('*'))
					->joininner(array('FT' =>FORWARDERS),'FT.forwarder_id = SSS.forwarder_id',array('FT.forwarder_name'))					
					->where('SSS.master_id='.$master_id);	
				//echo $select->__tostring();die;					
            return $this->getAdapter()->fetchAll($select);
		}catch (Exception $e) {
				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		}
       }

	 public function UpdateSMSDays()
	 {  
		 try{
		  $insertData = array();
		  $decodeCountryId = Zend_Encript_Encription:: decode($this->getData['token']);
		  $where = 'master_id ='.$decodeCountryId;
		   $this->_db->delete(STATUS_SMS_SETTING,$where);
		   $insertData['master_id'] =  $decodeCountryId;
			  foreach($this->getData['forwarder_id'] as $key=>$value){
			$insertData['forwarder_id'] =  $this->getData['forwarder_id'][$key];
			$insertData['sms_days'] =  $this->getData['sms_days'][$key];
			if($insertData['forwarder_id']!='' || $insertData['sms_days'] !=''){
			  $return[] = $this->insertInToTable(STATUS_SMS_SETTING,array($insertData));
			  
			}
		   }

		   return true;
		  }catch (Exception $e) {
			 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  }
	 }
	 
		public function getsmslogdata(){
		 try{
			$select = $this->_db->select()
				->from(array('SSL'=>STATUS_SMSLOG),array('SSL.*'))
				->joininner(array('SB'=>SHIPMENT_BARCODE),'SB.barcode_id=SSL.barcode_id',array('SB.'.BARCODE_ID,'SB.'.TRACENR_BARCODE))
				->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=SB.shipment_id",'')
				->joininner(array('CT'=>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID,array('CT.'.COUNTRY_NAME))
				->joininner(array('FT'=>FORWARDERS),'FT.'.FORWARDER_ID.'=SB.'.FORWARDER_ID,array('FT.forwarder_name'))
				->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array('AT.company_name'))
				->joinleft(array('SSC' =>STATUS_SMSCODE),'SSC.error_code=SSL.response_code',array('description'));
				//echo $select->__tostring();die;	
		   $result =  $this->getAdapter()->fetchAll($select);
		   return $result;
		  }catch (Exception $e) {
			 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  }
	   }	  	
}

?>