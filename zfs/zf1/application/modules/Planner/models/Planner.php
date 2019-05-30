<?php

class Planner_Model_Planner extends Zend_Custom
{
	public function schedulePickup(){
	   $accesslevel = $this->LevelClause();
	   $this->_db->query("SET SESSION group_concat_max_len = 1000000");
	   $select = $this->_db->select()
		               ->from(array('ST'=>SHIPMENT), array('DATE(ST.create_date) AS create_date','GROUP_CONCAT(DISTINCT DATE(ST.create_date)) AS Alldate','IF(BT.barcode_id,1,1) AS parcel_type'))
					   ->joininner(array('BT'=>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array('SUM(BT.weight) AS total_weight','COUNT(1) AS total_quantity','GROUP_CONCAT(DISTINCT BT.barcode_id) AS barcode_id'))
					   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('manual_pickup_id'))
					   ->joininner(array('AT'=>USERS_DETAILS),"ST.user_id=AT.user_id",array('company_name','user_id'))
					   ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=AT.user_id",array(''))
					   ->joininner(array('SR'=>SERVICES),'SR.service_id=ST.service_id',array(''))
					   ->joinleft(array('ASP'=>USERS_SCHEDULE_PICKUP),'ASP.user_id=AT.user_id',array())
					   ->joininner(array('CT'=>COUNTRIES),'AT.country_id=CT.country_id',array(''))
					   ->joinleft(array('SST'=>SHIPMENT_SCHEDULE_TIME),'SST.barcode_id=BT.barcode_id AND DATE(SST.date_added) = CURDATE() AND SST.parcel_type=1',array(''))
					   ->joinleft(array('MPT'=>SHIPMENT_MANUAL_PICKUP),'MPT.manual_pickup_id=BD.manual_pickup_id',
					   array('pickup_address'=>"if((BD.manual_pickup_id>0),CONCAT(MPT.name,'^',MPT.street1,'^',MPT.street2,'^',MPT.zipcode,'^',MPT.city,'^',MPT.country),if((ASP.zipcode!='' && ASP.city!=''),CONCAT(ASP.name,'^',ASP.street1,'^',ASP.street2,'^',ASP.zipcode,'^',ASP.city,'^',ASP.country),CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name)))",
					   	  'pickup_date'=>"if((BD.manual_pickup_id>0 && MPT.pickup_date!='0000-00-00'),MPT.pickup_date,if(ASP.user_id>0 && (".commonfunction::lowercase(date('D'))."_start"."!='00:00:00' || default_time_start!='00:00:00'),CURDATE(),CURDATE()))",
					      'pickup_time'=>"if((BD.manual_pickup_id>0 && MPT.pickup_time!='00:00:00'),MPT.pickup_time,IF(SST.pickup_time!='00:00:00' && SST.pickup_time!='',SST.pickup_time,if(ASP.user_id>0 && ".commonfunction::lowercase(date('D'))."_start"."!='00:00:00',".commonfunction::lowercase(date('D'))."_start".",if(default_time_start!='00:00:00',default_time_start,'00:00:00'))))"))
					   ->where("BT.checkin_status='0' AND BT.pickup_status='0' AND BT.show_planner='1' AND BD.driver_id=0 AND BD.assigned_date < CURDATE() AND ST.shipment_type!=5")
					   ->where("US.gls_pickup = '0'".$accesslevel)
					   ->where("(ST.create_date > NOW() - INTERVAL 60 DAY OR (BD.manual_pickup_id>0 AND DATE(MPT.pickup_date) = CURDATE() OR (ASP.picked_by_driver='0' AND BD.manual_pickup_id>0)))")
					   ->group("DATE(ST.create_date)")
					   ->group(new Zend_Db_Expr('pickup_time'))
					   ->having("pickup_date <= CURDATE()")
					   ->order(new Zend_Db_Expr('pickup_time'))
					   ->order(new Zend_Db_Expr('pickup_date'));//print_r($select->__tostring());die;
		return  $this->getAdapter()->fetchAll($select);
	}
	
	public function MailschedulePickup(){
	    $accesslevel = $this->LevelClause();
		$this->_db->query("SET SESSION group_concat_max_len = 1000000");
	   $select = $this->_db->select()
		               ->from(array('MS'=>MAIL_POST), array('DATE(MS.create_date) AS create_date','GROUP_CONCAT(DISTINCT DATE(MS.create_date)) AS Alldate','IF(MS.mail_id,2,2) AS parcel_type','SUM(MS.weight_class) AS total_weight','SUM(MS.quantity) AS total_quantity','GROUP_CONCAT(DISTINCT MS.mailshipment_id) AS barcode_id','manual_pickup_id'))
					   ->joininner(array('AT'=>USERS_DETAILS),"MS.user_id=AT.user_id",array('company_name','user_id'))
					   ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=AT.user_id",array(''))
					   ->joinleft(array('ASP'=>USERS_SCHEDULE_PICKUP),'ASP.user_id=AT.user_id',array())
					   ->joininner(array('CT'=>COUNTRIES),'AT.country_id=CT.country_id',array(''))
					   ->joinleft(array('SST'=>SHIPMENT_SCHEDULE_TIME),'SST.barcode_id=MS.mailshipment_id AND DATE(SST.date_added) = CURDATE() AND SST.parcel_type=2',array(''))
					   ->joinleft(array('MPT'=>SHIPMENT_MANUAL_PICKUP),'MPT.manual_pickup_id=MS.manual_pickup_id',
					   array('pickup_address'=>"if((MS.manual_pickup_id>0),CONCAT(MPT.name,'^',MPT.street1,'^',MPT.street2,'^',MPT.zipcode,'^',MPT.city,'^',MPT.country),if((ASP.zipcode!='' && ASP.city!=''),CONCAT(ASP.name,'^',ASP.street1,'^',ASP.street2,'^',ASP.zipcode,'^',ASP.city,'^',ASP.country),CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name)))",
					   	  'pickup_date'=>"if((MS.manual_pickup_id>0 && MPT.pickup_date!='0000-00-00'),MPT.pickup_date,if(ASP.user_id>0 && (".commonfunction::lowercase(date('D'))."_start"."!='00:00:00' || default_time_start!='00:00:00'),CURDATE(),CURDATE()))",
					      'pickup_time'=>"if((MS.manual_pickup_id>0 && MPT.pickup_time!='00:00:00'),MPT.pickup_time,IF(SST.pickup_time!='00:00:00' && SST.pickup_time!='',SST.pickup_time,if(ASP.user_id>0 && ".commonfunction::lowercase(date('D'))."_start"."!='00:00:00',".commonfunction::lowercase(date('D'))."_start".",if(default_time_start!='00:00:00',default_time_start,'00:00:00'))))"))
					   ->where("MS.checkin_status='0' AND MS.pickup_status='0' AND MS.show_planner='1' AND MS.driver_id=0 AND MS.assigned_date < CURDATE()".$accesslevel)
					   ->where("(MS.create_date > NOW() - INTERVAL 60 DAY OR (MS.manual_pickup_id>0 AND DATE(MPT.pickup_date) = CURDATE() OR (ASP.picked_by_driver='0' AND MS.manual_pickup_id>0)))")
					   ->group("DATE(MS.create_date)")
					   ->group(new Zend_Db_Expr('pickup_time'))
					   ->having("pickup_date <= CURDATE()")
					   ->order(new Zend_Db_Expr('pickup_time'))
					   ->order(new Zend_Db_Expr('pickup_date'));//print_r($select->__tostring());die;
		return  $this->getAdapter()->fetchAll($select);
	}
	
	public function DailyschedulePickup($recordArr){
	   $accesslevel = $this->LevelClause();
	   $userds = $this->getuserIds($recordArr);
	   if(!empty($userds)){
	      $accesslevel .= " AND AT.user_id NOT IN(".commonfunction::implod_array($userds,',').")";
	   }
	   $select = $this->_db->select()
		               ->from(array('AT'=>USERS_DETAILS),  array ('company_name','user_id','GROUP_CONCAT(DISTINCT AT.user_id) AS barcode_id','if(AT.user_id,3,3) as parcel_type','CURDATE() as create_date','if(AT.user_id,0,0) as manual_pickup_id','total_weight'=>new Zend_Db_Expr('1'),'total_quantity'=>new Zend_Db_Expr('1')))
					    ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=AT.user_id",array(''))
					    ->joininner(array('CT'=>COUNTRIES),'AT.country_id=CT.country_id',array(''))
						->joinleft(array('SST'=>SHIPMENT_SCHEDULE_TIME),'SST.barcode_id=AT.user_id AND DATE(SST.date_added) = CURDATE() AND SST.parcel_type=3',array(''))
					    ->joinleft(array('SPT'=>USERS_SCHEDULE_PICKUP),'SPT.user_id=AT.'.ADMIN_ID,array('pickup_address'=>"if(SPT.user_id>0 && SPT.name!='',CONCAT(SPT.name,'^',SPT.street1,'^',SPT.street2,'^',SPT.zipcode,'^',SPT.city,'^',SPT.country),CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name))",
					    'pickup_date'=>"if(SPT.user_id>0 && (".commonfunction::lowercase(date('D'))."_start"."!='00:00:00' || default_time_start!='00-00-00'),CURDATE(),CURDATE())",
					    'pickup_time'=>"if(SST.pickup_time!='00:00:00' && SST.pickup_time!='',SST.pickup_time,if(SPT.user_id>0 && ".commonfunction::lowercase(date('D'))."_start"."!='00:00:00',".commonfunction::lowercase(date('D'))."_start".",if(default_time_start!='00:00:00',default_time_start,'00:00:00')))"))
					   ->where("(SPT.daily_pickup_status='1' OR (SPT.pickwithoutparcel='1' AND ".commonfunction::lowercase(date('D'))."_start"."!='00:00:00'))".$accesslevel)
					   ->where("SPT.picked_by_driver='1' AND US.planner_status='1' AND (SELECT COUNT(1) AS CNT FROM ".DRIVER_HISTORY." DHT WHERE AT.user_id=DHT.user_id AND  DATE(DHT.assign_date)=CURDATE())<=0")
					   ->having("pickup_date <= CURDATE()")
					   ->order(new Zend_Db_Expr('pickup_time'))
					   ->order(new Zend_Db_Expr('pickup_date'));
				//echo $select->__tostring();die;
		   return  $this->getAdapter()->fetchAll($select);
	
	}
	
	public function ManualschedulePickup($recordArr){
	    $accesslevel = $this->LevelClause();
		$userds = $this->getuserIds($recordArr);
		if(!empty($userds)){
		  $accesslevel .= " AND AT.user_id NOT IN(".commonfunction::implod_array($userds,',').")";
		}
		$select = $this->_db->select()
		               ->from(array('ST'=>SHIPMENT_MANUAL_PICKUP),  array('manual_weight as total_weight','manual_quantity as total_quantity','create_date','if(manual_pickup_id,4,4) as parcel_type','manual_pickup_id','manual_pickup_id AS barcode_id'))
					   ->joininner(array('AT'=>USERS_DETAILS),'ST.'.ADMIN_ID.' = '.'AT.'.ADMIN_ID,array('company_name','user_id'))
					   ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=AT.user_id",array(''))
					   ->joininner(array('CT'=>COUNTRIES),'AT.country_id=CT.country_id',array(''))
					   ->joinleft(array('SST'=>SHIPMENT_SCHEDULE_TIME),'SST.barcode_id=ST.manual_pickup_id AND DATE(SST.date_added) = CURDATE() AND SST.parcel_type=4',array(''))
					   ->joinleft(array('SPT'=>USERS_SCHEDULE_PICKUP),'SPT.user_id=AT.'.ADMIN_ID,array('pickup_address'=>"if(CONCAT(ST.name,'^',ST.zipcode,'^',ST.city)!='^^^',CONCAT(ST.name,'^',ST.street1,'^',ST.street2,'^',ST.zipcode,'^',ST.city,'^',ST.country),if(SPT.user_id>0 && SPT.name!='',CONCAT(SPT.name,'^',SPT.street1,'^',SPT.street2,'^',SPT.zipcode,'^',SPT.city,'^',SPT.country),CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name)))",
					     'pickup_date'=>"if((ST.pickup_date!='0000-00-00'),ST.pickup_date,if(SPT.user_id>0 && (".commonfunction::lowercase(date('D'))."_start"."!='00:00:00' || default_time_start!='00-00-00'),CURDATE(),CURDATE()))",
					      'pickup_time'=>"if(SST.pickup_time!='00:00:00' && SST.pickup_time!='',SST.pickup_time,if(SPT.user_id>0 && ".commonfunction::lowercase(date('D'))."_start"."!='00:00:00',".commonfunction::lowercase(date('D'))."_start".",if(default_time_start!='00:00:00',default_time_start,'00:00:00')))"))
					   ->joinleft(array('DH'=>DRIVER_HISTORY),"DH.barcode_id=ST.manual_pickup_id AND DH.parcel_type=4 AND DH.pickup_date=CURDATE()",array(''))	  
					   ->where("ST.pickup_date >= CURDATE() AND US.planner_status='1' AND (SELECT COUNT(1) AS CNT FROM ".DRIVER_HISTORY." DHT WHERE AT.user_id=DHT.user_id AND  DATE(DHT.assign_date)=CURDATE())<=0 AND manual_type=1".$accesslevel)	  
					   ->having("pickup_date <= CURDATE()")
					   ->order(new Zend_Db_Expr('pickup_time'))
					   ->order(new Zend_Db_Expr('pickup_date')); //print_r($select->__tostring());die;
		return $this->getAdapter()->fetchAll($select);    
	
	}
	
	public function NonlistedScheduleParcel($assigned=false){
	   $accesslevel = $this->LevelAsDepots();
		if($assigned){
		  $accesslevel .= " AND assigned_date=CURDATE() AND NLS.pickup_date=CURDATE()";
		}else{
		   $accesslevel .= " AND assigned_date < CURDATE() AND NLS.create_date > NOW() - INTERVAL 3 DAY AND NLS.pickup_date <=CURDATE()";
		}
     $select =  $this->_db->select()
							 ->from(array('NLS'=>PLANNER_NONLISTED_SCHEDULE),array ("CONCAT(NLS.name,'^',NLS.street1,'^',NLS.street2,'^',NLS.zipcode,'^',NLS.city,'^',NLS.country) AS pickup_address",'DATE(NLS.create_date) AS create_date','if(NLS.schedule_id,5,5) AS parcel_type','NLS.schedule_id as barcode_id','NLS.pickup_date','NLS.name as company_name','NLS.weight as total_weight','NLS.quantity as total_quantity','NLS.user_id','NLS.pickup_time','NLS.driver_id','NLS.assigned_date')) 
							 ->joininner(array('AT'=>USERS_DETAILS),'NLS.'.ADMIN_ID.' = '.'AT.'.ADMIN_ID,array('manual_pickup_id'=>new Zend_Db_Expr('0')))
							 ->joinleft(array('DDT'=>DRIVER_DETAIL_TABLE),'DDT.driver_id=NLS.driver_id',array('driver_name','phoneno as driverphone'))
							 ->joinleft(array('SST'=>SHIPMENT_SCHEDULE_TIME),'SST.barcode_id=NLS.schedule_id AND DATE(SST.date_added) = CURDATE() AND SST.parcel_type=5',array(''))
							 ->where("pickup_status='0'".$accesslevel); //print_r($select->__tostring());die;
      $nonlisted = $this->getAdapter()->fetchAll($select);
	   
	  return $nonlisted;
  }
	
	public function manualPickup(){
	    global $objSession,$translate;
		if($this->getData['history_id']==''){
			   $this->_db->insert(SHIPMENT_MANUAL_PICKUP,array_filter(array('user_id'=>$this->getData['user_id'],
													  'name'=>$this->getData['name'],
													  'street1'=>$this->getData['street1'],
													  'street2'=>$this->getData['street2'],
													  'zipcode'=>$this->getData['zipcode'],
													  'city'=>$this->getData['city'],
													  'country'=>$this->getData['country'],
													  'pickup_date'=>$this->getData['pickup_date'],
													  'pickup_time'=>$this->getData['pickup_time'],
													  'manual_weight'=>isset($this->getData['manual_weight'])?$this->getData['manual_weight']:'',
													  'manual_quantity'=>isset($this->getData['manual_quantity'])?$this->getData['manual_quantity']:'',
													  'manual_type'=>$this->getData['parcel_type'],
													  'create_date'=>new Zend_Db_Expr('NOW()'))));
				$manual_pickup_id = $this->getAdapter()->lastInsertId();
			   switch($this->getData['parcel_type']){
				 case 1:
				   $this->_db->update(SHIPMENT_BARCODE_DETAIL,array('manual_pickup_id'=>$manual_pickup_id),"barcode_id IN(".$this->getData['barcode_id'].")");
				 break;
				 case 2:
				  $this->_db->update(MAIL_POST,array('manual_pickup_id'=>$manual_pickup_id),"mailshipment_id IN(".$this->getData['barcode_id'].")");
				 break;
				 case 3:
				 break;
			   }
	   }
	   if(isset($this->getData['history_id']) && $this->getData['history_id']!=''){
	          $history_ids = commonfunction::explode_string($this->getData['history_id'],',');
	          foreach($history_ids as $history_id){
					$this->_db->update(DRIVER_HISTORY,array('pickup_date'=>$this->getData['pickup_date'],
															'pickup_time'=>$this->getData['pickup_time'],
															'name'=>$this->getData['name'],
															'street1'=>$this->getData['street1'],
															'street2'=>$this->getData['street2'],
															'city'=>$this->getData['city'],
															'zipcode'=>$this->getData['zipcode'],
															'country'=>$this->getData['country']),"history_id='".$history_id."'");
	  	   }
	   }
	   $objSession->successMsg = $translate->translate('Manual Pickup Added successfully!');
	}
	
	public function getScheduleBarcodeList($requestData){
	   switch($requestData[0]){
	    case 1:
		$select = $this->_db->select()
		               ->from(array('BT'=>SHIPMENT_BARCODE), array('weight','barcode','barcode_id'))
					    ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array(''))
					   ->where("BT.checkin_status='0' AND BT.pickup_status='0' AND BT.show_planner='1' AND BD.driver_id=0 AND BD.assigned_date < CURDATE() AND BT.barcode_id IN(".$requestData[1].")");//print_r($select->__tostring());die;
		 break;
		 case 2;
		 break;
		}
		return  $this->getAdapter()->fetchAll($select);
	}
	
	public function getDriverList(){
	    $where = '';
	   switch($this->Useconfig['level_id']){
		   case 4:
			  $where = " AND DT.parent_id='".$this->Useconfig['user_id']."'";
		   break;
		   case 6:
			  $parent_id = $this->getDepotID($this->Useconfig['user_id']);
			  $where = " AND DT.parent_id='".$parent_id."'";
		   break;
		   default:
		}
	  $select = $this->_db->select()
	  							->from(array('DT'=>DRIVER_DETAIL_TABLE),array('*'))
								->where("DT.delete_status='0' AND DT.account_status='1'".$where)
								->order("driver_name ASC");//print_r($select->__tostring());die;
	  return $this->getAdapter()->fetchAll($select);							
	}
	
	public function bulkAssignment(){
	   $datas = $this->getData['barcode_id'];
	   foreach($datas as $barcode_ids){
	           $requestData = commonfunction::explode_string($barcode_ids,'$');
			    $this->getData['barcode_id'] = commonfunction::explode_string($requestData[1],',');
				$this->getData['pickup_date'] = $requestData[3];
				$this->getData['pickup_time'] = $requestData[4];
				$this->getData['parcel_type'] = $requestData[0];
				$this->getData['user_id'] = $requestData[5];
				$address = commonfunction::explode_string($requestData[2],'^');
				$this->getData['name'] = $address[0];
				$this->getData['street1'] = $address[1];
				$this->getData['street2'] = $address[2];
				$this->getData['city'] = $address[4];
				$this->getData['zipcode'] = $address[3];
				$this->getData['country'] = $address[5]; //echo "<pre>";print_r($this->getData);die;
			    $this->assignedToDriver();
			  
			 //
	   }
	}
	
	public function assignedToDriver(){
	    global $objSession,$translate;
	   foreach($this->getData['barcode_id'] as $barcode_id){
	        $this->_db->insert(DRIVER_HISTORY,array_filter(array('barcode_id'=>$barcode_id,
													'driver_id'=>$this->getData['driver_id'],
													'assigned_by'=>$this->Useconfig['user_id'],
													'assign_date'=>new Zend_Db_Expr('CURDATE()'),
													'assign_time'=>new Zend_Db_Expr('CURTIME()'),
													'pickup_date'=>$this->getData['pickup_date'],
													'pickup_time'=>$this->getData['pickup_time'],
													'parcel_type'=>$this->getData['parcel_type'],
													'user_id'=>$this->getData['user_id'],
													'name'=>$this->getData['name'],
													'street1'=>$this->getData['street1'],
													'street2'=>$this->getData['street2'],
													'city'=>$this->getData['city'],
													'zipcode'=>$this->getData['zipcode'],
													'country'=>$this->getData['country'])));
	   }
	   switch($this->getData['parcel_type']){
	       case 1:
		    $this->_db->update(SHIPMENT_BARCODE_DETAIL,array('driver_id'=>$this->getData['driver_id'],'assigned_date'=>new Zend_Db_Expr('CURDATE()')),"barcode_id IN(".commonfunction::implod_array($this->getData['barcode_id'],',').")");
		   break;
		   case 2:
		     $this->_db->update(MAIL_POST,array('driver_id'=>$this->getData['driver_id'],'assigned_date'=>new Zend_Db_Expr('CURDATE()')),"mailshipment_id IN(".commonfunction::implod_array($this->getData['barcode_id'],',').")");
		   break;
		   case 5:
		      $this->_db->update(PLANNER_NONLISTED_SCHEDULE,array('driver_id'=>$this->getData['driver_id'],'assigned_date'=>new Zend_Db_Expr('CURDATE()')),"schedule_id IN(".commonfunction::implod_array($this->getData['barcode_id'],',').")");
		   break;
		   case 4:
		   break;
	   }
	   $objSession->successMsg = $translate->translate('Shipment Assiged Successfully!');
	}
	
	public function ReAssignDriver(){
	     global $objSession,$translate;
	     $requestData = commonfunction::explode_string($this->getData['pickup_detail'],'$');
		 $history_ids = commonfunction::explode_string($requestData[3],',');
		 foreach($history_ids as $history_id){
	        $this->_db->update(DRIVER_HISTORY,array('driver_id'=>$this->getData['driver_id']),"history_id='".$history_id."' AND user_id='".$requestData[2]."' AND parcel_type='".$requestData[0]."'");
	   }
	   switch($requestData[0]){
	       case 1:
		     $this->_db->update(SHIPMENT_BARCODE_DETAIL,array('driver_id'=>$this->getData['driver_id']),"barcode_id IN(".$requestData[1].")");
		   break;
		   case 2:
		     $this->_db->update(MAIL_POST,array('driver_id'=>$this->getData['driver_id']),"mailshipment_id IN(".$requestData[1].")");
		   break;
		   case 5:
		      $this->_db->update(PLANNER_NONLISTED_SCHEDULE,array('driver_id'=>$this->getData['driver_id']),"schedule_id IN(".$requestData[1].")");
		   break;
	   }
	   $objSession->successMsg = $translate->translate('Re-Assigned successfully!');
	   echo 'T';die;
	   
	}
	
	public function AssignedShipment(){
	     $this->_db->query("SET SESSION group_concat_max_len = 1000000");
	     $accesslevel = $this->LevelClause();
		 if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){
		     $accesslevel .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";
		 }
		 if(isset($this->getData['filterdriver_id']) && $this->getData['filterdriver_id']>0){
		     $accesslevel .= " AND DH.driver_id='".$this->getData['filterdriver_id']."'";
		 }
		 $select = $this->_db->select()
		               ->from(array('BT'=>SHIPMENT_BARCODE), array('SUM(BT.weight) AS total_weight','COUNT(1) AS total_quantity','GROUP_CONCAT(DISTINCT BT.barcode_id) AS barcode_id','IF(BT.barcode_id,1,1) AS parcel_type'))
					   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('manual_pickup_id'))
					   ->joininner(array('DH'=>DRIVER_HISTORY),"DH.barcode_id=BT.barcode_id AND DH.pickup_date = CURDATE() AND DH.parcel_type=1 AND DH.pickup_status='0'",array('pickup_date','pickup_time','parcel_type','name','street1','street2','zipcode','city','country','GROUP_CONCAT(DISTINCT DH.history_id) AS history_id'))
					   ->joininner(array('AT'=>USERS_DETAILS),"DH.user_id=AT.user_id",array('company_name','user_id'))
					   ->joininner(array('DD'=>DRIVER_DETAIL_TABLE),"DD.driver_id=BD.driver_id",array('driver_name'))
					   ->where("BT.checkin_status='0' AND BT.pickup_status='0'".$accesslevel)
					   ->group(array('DH.driver_id','BD.manual_pickup_id','BD.assigned_date','AT.user_id'))
					   ->group(new Zend_Db_Expr('DH.pickup_time'))
					   ->order(array('DH.pickup_date ASC','DH.pickup_time ASC'));//print_r($select->__tostring());die;
		return  $this->getAdapter()->fetchAll($select);
	}
	
	public function AssignedMailShipment(){
	    $accesslevel = $this->LevelClause();
		$this->_db->query("SET SESSION group_concat_max_len = 1000000");
		 if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){
		     $accesslevel .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";
		 }
		 if(isset($this->getData['filterdriver_id']) && $this->getData['filterdriver_id']>0){
		     $accesslevel .= " AND DH.driver_id='".$this->getData['filterdriver_id']."'";
		 }
		$select = $this->_db->select()
		               ->from(array('MS'=>MAIL_POST), array('total_weight'=>new Zend_Db_Expr('1'),'SUM(quantity) AS total_quantity','GROUP_CONCAT(DISTINCT MS.mailshipment_id) AS barcode_id','parcel_type'=>new Zend_Db_Expr('2'),'manual_pickup_id'))
					   ->joininner(array('DH'=>DRIVER_HISTORY),"DH.barcode_id=MS.mailshipment_id AND DH.pickup_date = CURDATE() AND DH.parcel_type=2 AND DH.pickup_status='0'",array('pickup_date','pickup_time','parcel_type','name','street1','street2','zipcode','city','country','GROUP_CONCAT(DISTINCT DH.history_id) AS history_id'))
					   ->joininner(array('AT'=>USERS_DETAILS),"DH.user_id=AT.user_id",array('company_name','user_id'))
					   ->joininner(array('DD'=>DRIVER_DETAIL_TABLE),"DD.driver_id=MS.driver_id",array('driver_name'))
					   ->where("MS.checkin_status='0' AND MS.pickup_status='0'".$accesslevel)
					   ->group(array('DH.driver_id','MS.manual_pickup_id','MS.assigned_date','AT.user_id'))
					   ->group(new Zend_Db_Expr('DH.pickup_time'))
					   ->order(array('DH.pickup_date ASC','DH.pickup_time ASC'));//print_r($select->__tostring());die;
		return  $this->getAdapter()->fetchAll($select);
	
	}
	
	public function AssignedDailyShipment($recordArr){
	    $accesslevel = $this->LevelClause();
		$this->_db->query("SET SESSION group_concat_max_len = 1000000");
		if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){
		     $accesslevel .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";
		 }
		 if(isset($this->getData['filterdriver_id']) && $this->getData['filterdriver_id']>0){
		     $accesslevel .= " AND DH.driver_id='".$this->getData['filterdriver_id']."'";
		 }
		 $user_ids = array();
		if(!empty($recordArr)){
		   foreach($recordArr as $users){
		      $user_ids  = $users['user_id'];
		   }
		   $accesslevel .= " AND AT.user_id NOT IN(".implode(',',$user_ids).")"; 
		} 
		$select = $this->_db->select()
		               ->from(array('AT'=>USERS_DETAILS), array('total_weight'=>new Zend_Db_Expr('1'),'total_quantity'=>new Zend_Db_Expr('1'),'AT.user_id AS barcode_id','parcel_type'=>new Zend_Db_Expr('3'),'manual_pickup_id'=>new Zend_Db_Expr('0'),'company_name','user_id'))
					   ->joininner(array('DH'=>DRIVER_HISTORY),"DH.barcode_id=AT.user_id AND AT.user_id=DH.user_id AND DH.pickup_date = CURDATE() AND DH.parcel_type=3 AND DH.pickup_status='0'",array('pickup_date','pickup_time','parcel_type','name','street1','street2','zipcode','city','country','GROUP_CONCAT(DISTINCT DH.history_id) AS history_id'))
					   ->joininner(array('DD'=>DRIVER_DETAIL_TABLE),"DD.driver_id=DH.driver_id",array('driver_name'))
					   ->where("DH.pickup_status='0'".$accesslevel)
					   ->group(array('AT.user_id'))
					   ->group(new Zend_Db_Expr('DH.pickup_time'))
					   ->order(array('DH.pickup_date ASC','DH.pickup_time ASC'));//print_r($select->__tostring());die;
		return  $this->getAdapter()->fetchAll($select);
	
	}
	
	public function AssignedManualPickup(){ 
	    $accesslevel = $this->LevelClause();
		$this->_db->query("SET SESSION group_concat_max_len = 1000000");
		if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){
		     $accesslevel .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";
		 }
		 if(isset($this->getData['filterdriver_id']) && $this->getData['filterdriver_id']>0){
		     $accesslevel .= " AND DH.driver_id='".$this->getData['filterdriver_id']."'";
		 }
		try{
		$select = $this->_db->select()
		               ->from(array('MP'=>SHIPMENT_MANUAL_PICKUP), array('manual_weight as total_weight','manual_quantity as total_quantity','MP.manual_pickup_id AS barcode_id','parcel_type'=>new Zend_Db_Expr('4'),'manual_pickup_id'))
					   ->joininner(array('DH'=>DRIVER_HISTORY),"DH.barcode_id=MP.manual_pickup_id AND DH.pickup_date = CURDATE() AND DH.parcel_type=4 AND DH.pickup_status='0'",array('pickup_date','pickup_time','parcel_type','name','street1','street2','zipcode','city','country','GROUP_CONCAT(DISTINCT DH.history_id) AS history_id'))
					   ->joininner(array('DD'=>DRIVER_DETAIL_TABLE),"DD.driver_id=DH.driver_id",array('driver_name'))
					   ->joininner(array('AT'=>USERS_DETAILS),"DH.user_id=AT.user_id",array('company_name','user_id'))
					   ->where("DH.pickup_status='0'".$accesslevel)
					   ->group(array('DH.driver_id','MP.manual_pickup_id','DH.assign_date','MP.user_id'))
					   ->group(new Zend_Db_Expr('DH.pickup_time'))
					   ->order(array('DH.pickup_date ASC','DH.pickup_time ASC'));//print_r($select->__tostring());die;
		return  $this->getAdapter()->fetchAll($select);
		}catch(Exception $e){
		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  return array();
		}
	
	}
	
	public function AssignedNonlistedSchedule(){
	   $accesslevel = $this->LevelAsDepots();
	   if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){
		     $accesslevel .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";
		 }
		 if(isset($this->getData['filterdriver_id']) && $this->getData['filterdriver_id']>0){
		     $accesslevel .= " AND DH.driver_id='".$this->getData['filterdriver_id']."'";
		 }
      $select =  $this->_db->select()
							 ->from(array('NLS'=>PLANNER_NONLISTED_SCHEDULE),array ('DATE(NLS.create_date) AS create_date','if(NLS.schedule_id,5,5) AS parcel_type','NLS.schedule_id as barcode_id','NLS.pickup_date','NLS.name as company_name','NLS.weight as total_weight','NLS.quantity as total_quantity','NLS.user_id','NLS.pickup_time','NLS.driver_id','NLS.assigned_date')) 
							 ->joininner(array('AT'=>USERS_DETAILS),'NLS.'.ADMIN_ID.' = '.'AT.'.ADMIN_ID,array('manual_pickup_id'=>new Zend_Db_Expr('0')))
							 ->joininner(array('DDT'=>DRIVER_DETAIL_TABLE),'DDT.driver_id=NLS.driver_id',array('driver_name','phoneno as driverphone'))
							 ->joininner(array('DH'=>DRIVER_HISTORY),"DH.barcode_id=NLS.schedule_id AND DH.pickup_date = CURDATE() AND DH.parcel_type=5 AND DH.pickup_status='0'",array('DH.pickup_date','DH.pickup_time','DH.name','DH.street1','DH.street2','DH.zipcode','DH.city','DH.country','DH.history_id AS history_id'))
							 ->joinleft(array('SST'=>SHIPMENT_SCHEDULE_TIME),'SST.barcode_id=NLS.schedule_id AND DATE(SST.date_added) = CURDATE() AND SST.parcel_type=5',array(''))
							 ->where("NLS.pickup_status='0' AND assigned_date=CURDATE() AND NLS.pickup_date=CURDATE()".$accesslevel); //print_r($select->__tostring());die;
      $nonlisted = $this->getAdapter()->fetchAll($select);
	   
	  return $nonlisted;
  }
	
	public function AddNonListedcustomer(){
		 global $objSession;
		   $select =  $this->_db->select()
								 ->from(array('NLS'=>PLANNER_NONLISTED_SCHEDULE),array ("COUNT(1) AS CNT")) 
								 ->where("name='".$this->getData['name']."' AND street1='".$this->getData['street1']."' AND city='".$this->getData['city']."' AND zipcode='".$this->getData['zipcode']."' AND DATE(create_date) = CURDATE() AND user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'"); //print_r($select->__tostring());die;
		  $nonlisted = $this->getAdapter()->fetchRow($select);
			  if($nonlisted['CNT']<=0){
				 $this->_db->insert(PLANNER_NONLISTED_SCHEDULE,array_filter(
				 												array('name'=>$this->getData['name'],
																'user_id'=>Zend_Encript_Encription::decode($this->getData['user_id']),
																'street1'=>$this->getData['street1'],
																'street2'=>$this->getData['street2'],
																'city'=>$this->getData['city'],
																'zipcode'=>$this->getData['zipcode'],
																'country'=>$this->getData['country'],
																'create_date'=>new Zend_Db_Expr('NOW()'),
																'pickup_date'=>(($this->getData['pickup_date']!='')?$this->getData['pickup_date']:new Zend_Db_Expr('CURDATE()')),
																'pickup_time'=>(($this->getData['pickup_time']!='')?$this->getData['pickup_time']:new Zend_Db_Expr('TIME()')),
																'weight'=>$this->getData['weight'],
																'quantity'=>$this->getData['quantity'])));
			$objSession->successMsg = 'Non_listed Record Address Successfully';													
		 }else{
		   $objSession->errorMsg = 'Same Record Already Added!';						
		 }
		 return true;
	}
	
	
	public function DriverManifest(){
	 
	   $datas = $this->getData['barcode_id'];
	   $ManifestPdfObj = new Zend_Labelclass_ManifestPdf('P','mm','a6');
	   $Directory = DRIVER_MANIFEST_SAVE.date('Y-m');
		if(!is_dir($Directory)){
			mkdir($Directory);
			chmod($Directory, 0777);
		} 
	   foreach($datas as $barcode_ids){
	           $requestData = commonfunction::explode_string($barcode_ids,'$');
			    $this->getData['barcode_id'] = commonfunction::explode_string($requestData[1],',');
				$this->getData['pickup_date'] = $requestData[3];
				$this->getData['pickup_time'] = $requestData[4];
				$this->getData['parcel_type'] = $requestData[0];
				$this->getData['user_id'] = $requestData[5];
				$this->getData['weight'] = $requestData[6];
				$this->getData['quantity'] = $requestData[7];
				$address = commonfunction::explode_string($requestData[2],'^');
				$this->getData['pickupAddress'] = array_filter(commonfunction::explode_string($requestData[2],'^'));
				$customerAddress = $this->getCustomerDetails($this->getData['user_id']);
				$depotAddress = $this->getCustomerDetails($customerAddress['parent_id']);
				$this->getData['senderAddress'] = array($customerAddress['company_name'],$customerAddress['address1'],$customerAddress['postalcode'],$customerAddress['city'],$customerAddress['country_name']);
				$driverDetail = $this->driverdetails($this->getData['filterdriver_id']);
				$ManifestPdfObj->outputparam['driverAddress'] = array($driverDetail['driver_name'],'Truck','42342324',5345345345,4343534534);//array($driverDetail['driver_name'],$driverDetail['type_of_vehicle'],$driverDetail['license_number'],$driverDetail['phoneno']);
				$ManifestPdfObj->outputparam['depotAddress'] = array($depotAddress['company_name'],$depotAddress['address1'],$depotAddress['postalcode'],$depotAddress['city'],$depotAddress['country_name']);
				$ManifestPdfObj->outputparam['ManifestData'][] = $this->getData;
	   }
	   $ManifestPdfObj->DriverManifest();
	   $file_name = 'Driver_Manifest_'.date('Ymd_His').'.pdf';
	   $ManifestPdfObj->Output($Directory.'/'.$file_name,'F');
	    echo json_encode(array('Status'=>'Y','Manifest'=>DRIVER_MANIFEST_OPEN.date('Y-m').'/'.$file_name,'message'=>'Manifest Printed'));die;
	   //echo DRIVER_MANIFEST_OPEN.date('Y-m').'/'.$file_name;die;
	}
	
	public function getuserIds($records){
	   $userids =array();
	   foreach($records as $record){
	      $userids[] = $record['user_id'];
	   }
	   return $userids;
	}
	
	public function PickedupHistory(){
	     $accesslevel = $this->LevelClause();
		 if(isset($this->getData['pickup_date'])){
			  $accesslevel .= " AND DATE(DPH.pickup_datetime)='".$this->getData['pickup_date']."'"; 
		  }else{
		      $accesslevel .= " AND DATE(DPH.pickup_datetime)=CURDATE()"; 
		  }
		 $select = $this->_db->select()
                    ->from(array("DPH" => DRIVER_PICKEDUP_HISTORY))
                    ->joinInner(array("DD" =>DRIVER_DETAIL_TABLE),"DD.driver_Id = DPH.driver_id", array("DD.driver_name"))
                    ->joinInner(array("AT" => USERS_DETAILS),"AT.user_id = DD.user_id", array("AT.company_name"))
                    ->where('1'.$accesslevel);//print_r($select->__tostring());die;
	    return $this->getAdapter()->fetchAll($select);				
	}
	
   public function FailedPickup(){
       $accesslevel = $this->LevelClause();
	  $select = $this->_db->select()
	             ->from(array('DHT'=>DRIVER_HISTORY),array('total_shipment'=>'COUNT(DHT.barcode_id)','assign_date'))
				 ->joininner(array('BT'=>SHIPMENT_BARCODE),'BT.barcode_id=DHT.barcode_id AND DHT.parcel_type=1',array('total_weight'=>'SUM(BT.weight)','total_quantity'=>'COUNT(1)'))
				 ->joininner(array('ST'=>SHIPMENT),'ST.shipment_id=BT.shipment_id',array('shipment_id'=>'shipment_id'))
				 ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id AND AT.user_id=DHT.user_id",array('user_id','company_name'))
				 ->joininner(array('DDT'=>DRIVER_DETAIL_TABLE),'DDT.driver_id=DHT.driver_id',array('driver_name'))
				 ->where('DHT.assign_date > CURDATE() - INTERVAL 15 DAY' )
				 ->where("DHT.pickup_status!=1 AND BT.pickup_status!='1'".$accesslevel)
				 ->group('ST.user_id')
				 ->group('DHT.assign_date')
				 ->group('DHT.driver_id');
	   //echo $select->assemble();die;
	   $result = $this->getAdapter()->fetchAll($select);
	   return $result;
   }
   public function getAllDriverList(){
        $accesslevel = $this->LevelAsDepots();
		 $select = $this->_db->select()
	             ->from(array('DD'=>DRIVER_DETAIL_TABLE),array('*'))
				 ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=DD.parent_id",array('company_name'))
				 ->where("DD.delete_status='0'".$accesslevel);
	   //echo $select->assemble();die;
	   $result = $this->getAdapter()->fetchAll($select);
	   return $result;
   }
   
    public function checkALreadyAssign($data,$type=1){
        $select = $this->_db->select()
                    ->from(array("DH"=>DRIVER_HISTORY),array('*'))
                    ->where("DH.user_id='".$data['user_id']."' AND DH.assign_date = CURDATE() AND DH.pickup_date = CURDATE() AND DH.pickup_status=0")
					->limit(1);//print_r($select->__tostring());die;
	    $assignedparcel =  $this->getAdapter()->fetchRow($select);
		if(!empty($assignedparcel)){
		    $this->_db->insert(DRIVER_HISTORY,array_filter(array('barcode_id'=>$data['barcode_id'],
													'driver_id'=>$assignedparcel['driver_id'],
													'assigned_by'=>$this->Useconfig['user_id'],
													'assign_date'=>new Zend_Db_Expr('CURDATE()'),
													'assign_time'=>new Zend_Db_Expr('CURTIME()'),
													'pickup_date'=>$assignedparcel['pickup_date'],
													'pickup_time'=>$assignedparcel['pickup_time'],
													'parcel_type'=>1,
													'user_id'=>$data['user_id'],
													'name'=>$assignedparcel['name'],
													'street1'=>$assignedparcel['street1'],
													'street2'=>$assignedparcel['street2'],
													'city'=>$assignedparcel['city'],
													'zipcode'=>$assignedparcel['zipcode'],
													'country'=>$assignedparcel['country'])));
		 switch($type){
		   case 1:											
		      $this->_db->update(SHIPMENT_BARCODE_DETAIL,array('driver_id'=>$assignedparcel['driver_id'],'assigned_date'=>new Zend_Db_Expr('CURDATE()')),"barcode_id='".$data['barcode_id']."'");
			 break;
			}
		}
		
		return true;
   }	
}

