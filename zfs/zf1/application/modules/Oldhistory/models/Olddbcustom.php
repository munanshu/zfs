<?php //ini_set('display_errors', 1);

/**
* 
*/
class OlddbCustom  
{
	public $oldDb = NULL; 
	public $getData = NULL; 
	public $Useconfig = NULL; 
	protected $olddbConfig = NULL; 
	protected $oldDbYears = NULL; 
	private $olddbAdapter = NULL; 
	public $CurrentYear;


	public function __construct($Request)
	{	

		$this->setdbConfig($Request); 
		$this->setdbAdapter();
		$this->setDb();
		$this->setUserSession();
		$this->setSystemSession();
	}

	public function setSystemSession()
	{
		global $objSession;
		$objSession = new Zend_Session_Namespace('SystemSession');
	}

	public function setUserSession()
	{
		$logicSeesion = new Zend_Session_Namespace('logicSeesion');
		$this->Useconfig = $logicSeesion->userconfig;
	}

	public function setDb()
	{
		$this->oldDb = Zend_Registry::get("oldDb");
	}

	public function setdbConfig($Request='')
	{	
		$this->olddbConfig = new Zend_Config_Ini(ROOT_PATH.'/application/configs/oldb.ini',APPLICATION_ENV);
		$this->oldDbYears = $this->olddbConfig;
		$this->setRequestedDbYear($Request);
		
	}

	public function setRequestedDbYear($Request=array())
	{	
		$year = (isset($Request['year']) && !empty($Request['year']))? $Request['year'] : date('Y') ;
		$this->olddbConfig =  isset($this->olddbConfig->resources->{'oldb'.$year})? $this->olddbConfig->resources->{'oldb'.$year}: $this->olddbConfig->resources->{'oldb'.date('Y')} ;
	}

	public function setdbAdapter()
	{	
		 $this->olddbAdapter = Zend_Db::factory($this->olddbConfig->adapter, array(
            'host'     => $this->olddbConfig->params->hostname,
            'username' => $this->olddbConfig->params->username,
            'password' => $this->olddbConfig->params->password,
            'dbname'   => $this->olddbConfig->params->dbname
         )); 
		Zend_Registry::set('oldDb', $this->olddbAdapter);
	}

	public function getOldDbYears()
	{
		$this->CurrentYear = date('Y');
		 if(count($this->oldDbYears->resources)>0){

			 foreach ($this->oldDbYears->resources as $key => $value) {
			 	$years[] = $value->params->year;
			 }
		 	
		 }else $years = array($this->CurrentYear);
		 return $years;
		# code...
	}

	// public function getInvoiceyeardata()
	// {
	// 	$select = $this->oldDb->select()->from(array('INV'=>INVOICE),array('DISTINCT(YEAR(INV.create_date)) as year'));
	// 	$res = $this->oldDb->fetchAll($select);

	// 	return $this->manageYears($res);
	// 	// print_r($years);die;
	// }

	// public function manageYears($res)
	// {
	// 	foreach ($res as $key => $value) {
	// 		if($value['year'] < '2017' && $value['year'] > 0)
	// 		$years[] = $value['year'];
	// 	}
	// 	 rsort($years);
	// 	return $years;
	// }
	// public function getShipmentyeardata()
	// {
	// 	$select = $this->oldDb->select()->from(array('ST'=>SHIPMENT),array('DISTINCT(YEAR(ST.create_date)) as year'));
	// 	$res = $this->oldDb->fetchAll($select);
	// 	 return $this->manageYears($res);
	// }

	// public function getEdiyeardata()
	// {
	// 	 $select = $this->oldDb->select()->from(array('SE'=>SHIPMENT_EDI),array(' DISTINCT(YEAR(SE.create_date)) as year'));
	// 	 $res = $this->oldDb->fetchAll($select);

	// 	 return $this->manageYears($res);
	// }















	/* Functions Also Available in new Db start */ 

	 public function GetTrackingLog($barcode_id){
	    try{
			$select = $this->oldDb->select()
									->from(array('PT'=>PARCEL_TRACKING),array(BARCODE_ID,'status_date','added_date'))
									->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=PT.barcode_id",array('received_by','delivery_date'))
									->joininner(array('SL'=>STATUS_LIST),"SL.error_id=PT.status_id",array('error_type','error_desc'))
									->joinleft(array('SM'=>STATUS_MASTER),"SM.master_id=SL.master_id",array('status_name'))
									->where("PT.barcode_id='".$barcode_id."'")
									->order("status_date DESC");
									//print_r($select->__toString());die;
			return $this->oldDb->fetchAll($select);
		}catch(Exception $e){
		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  return array();
		}
	} 

	public function ParcelOldAddress($barcode_id){
	   try{
			$select = $this->oldDb->select()
									->from(array('AM'=>WRONG_ADDRESS_MODIFICATION),array('*'))
									->where("AM.barcode_id='".$barcode_id."'");
									//print_r($select->__toString());die;
			return $this->oldDb->fetchAll($select);
		}catch(Exception $e){
		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  return array();
		}
	}

	public function GetEventHistories($barcode_id){
	    try{
			$select = $this->oldDb->select()
									->from(array('EH'=>SHIPMENT_EVENT_HISTORIES),array('*'))
									/*->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=PT.barcode_id",array('received_by','delivery_date'))
									->joininner(array('SL'=>STATUS_LIST),"SL.error_id=PT.status_id",array('error_type','error_desc'))
									->joinleft(array('SM'=>STATUS_MASTER),"SM.master_id=SL.master_id",array('status_name'))*/
									->where("EH.barcode_id='".$barcode_id."'");
									//print_r($select->__toString());die;
			return $this->oldDb->fetchAll($select);
		}catch(Exception $e){
		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  return array();
		}
	}
	

	public function getDepotList(){
	    try{
	    $select = $this->oldDb->select()
						  ->from(array('UT'=>USERS),array('*'))
						  ->joininner(array('UD'=>USERS_DETAILS),"UT.user_id=UD.user_id",array('user_id','company_name','postalcode','city'))
						  ->where('UT.user_status=?', '1')
						  ->where('UT.delete_status=?', '0')
						  ->where('UT.level_id=?', 4);
		 switch($this->Useconfig['level_id']){
		    case 4:
			  $select->where('UD.user_id=?',$this->Useconfig['user_id']);
			break;
			case 6:
			$depot_id = $this->getDepotID($this->Useconfig['user_id']);
			$select->where('UD.user_id=?', $depot_id);
			break;
		}
		
		 $select->order("UD.company_name ASC");				  
		return $this->oldDb->fetchAll($select);
	  }catch(Exception $e){$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		 return array();
		}
	}
	/**
     * Return depot id of customer
     * @param  $user_id| user id of requested customer
     * @return depot id of customer as integer
     */
   public function getDepotID($user_id){
         $select = $this->oldDb->select()
						  ->from(array('UD'=>USERS_DETAILS),array('parent_id'))
						  ->where('UD.user_id=?', $user_id);
		 $result = $this->oldDb->fetchRow($select);
		return $result['parent_id'];
   }

   public function getForwarderList(){
	    $select = $this->oldDb->select()
						  ->from(array('FT'=>FORWARDERS),array('forwarder_name','forwarder_id'))
						  ->order("FT.forwarder_name ASC");
		return $this->oldDb->fetchAll($select);
	}

   public function ParcelCurrentStatus($data){
	    if($data['delivery_status']=='1'){
		    return array('Type'=>1,'Color'=>'green','Message'=>('<b>Delivered</b>:'.commonfunction::TimeFormat($data['delivery_date']).'<br><b>Received By</b>:'.$data['received_by']),'TransitTime'=>'','Icon'=>'<i class="fa fa-check fa-2x"></i>','status_code'=>'','status_date'=>'','status_desc'=>'');
		}
		$select = $this->oldDb->select()
								->from(array('PT'=>PARCEL_TRACKING),array(BARCODE_ID,'status_date'))
								->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=PT.barcode_id",array('received_by','delivery_date'))
								->joininner(array('SL'=>STATUS_LIST),"SL.error_id=PT.status_id",array('error_type','error_desc'))
								->joinleft(array('SM'=>STATUS_MASTER),"SM.master_id=SL.master_id",array('status_name'))
								->where('PT.barcode_id=?',$data['barcode_id'])
								->order("status_date DESC")
								->limit(1);
								//print_r($select->__toString());die;
		$result = $this->oldDb->fetchRow($select);
		if($result){
		   switch($result['error_type']){
		      case '0':
			     return array('Type'=>0,'Color'=>'red','Message'=>$result['error_desc'],'TransitTime'=>'2017-09-02 09:90:09','Icon'=>'<i class="fa fa-exclamation-triangle fa-2x"></i>','status_code'=>$result['error_desc'],'status_date'=>$result['status_date'],'status_desc'=>$result['error_desc']);
			  break;
			  case '1':
			    return array('Type'=>1,'Color'=>'green','Message'=>($result['status_name'].' '.$result['delivery_date'].' '.$result['received_by']),'TransitTime'=>'2017-09-02 09:90:09','Icon'=>'<i class="fa fa-check fa-2x"></i>','status_code'=>$result['error_desc'],'status_date'=>$result['status_date'],'status_desc'=>$result['error_desc']);
			  break;
			  case '2':
			    return array('Type'=>2,'Color'=>'orange','Message'=>$result['error_desc'],'TransitTime'=>'2017-09-02 09:90:09','Icon'=>'<i class="fa fa-exclamation-circle fa-2x"></i>','status_code'=>$result['error_desc'],'status_date'=>$result['status_date'],'status_desc'=>$result['error_desc']);
			  break;
		   }
		}else{
		   return array('Type'=>1,'Color'=>'#0000000','Message'=>'In-Transit','TransitTime'=>'','Icon'=>'<i class="fa fa-truck fa-2x"></i>','status_code'=>'','status_date'=>date('Y-m-d H:i:s'),'status_desc'=>'In-Transit');
		 } 
	}

	public function getCustomerList(){  
	     $select = $this->oldDb->select()
						  ->from(array('UT'=>USERS),array('*'))
						  ->joininner(array('UD'=>USERS_DETAILS),"UT.user_id=UD.user_id",array('user_id','company_name','postalcode','city'))
						  ->where('UT.user_status=?', '1')
						  ->where('UT.delete_status=?', '0')
						  ->where('UT.level_id=?', 5);
		 switch($this->Useconfig['level_id']){
		    case 4:
			  $select->where('UD.parent_id=?',$this->Useconfig['user_id']);
			break;
			case 5:
			   $select->where('UD.user_id=?',$this->Useconfig['user_id']);
			break;
			case 6:
			$depot_id = $this->getDepotID($this->Useconfig['user_id']);
			$select->where('UD.parent_id=?', $depot_id);
			break;
			case 10:
			$parent_id = $this->getDepotID($this->Useconfig['user_id']);
			$select->where('UD.user_id=?', $parent_id);
			break;
		}
		
		 $select->order("UD.company_name ASC");				  
		return $this->oldDb->fetchAll($select);
	}


	

	/* Functions Also Available in new Db end */ 


}

