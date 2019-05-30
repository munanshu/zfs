<?php
 
class Checkin_Model_CheckinManager  extends Zend_Custom
{
    public function getParcelList(){
	   try{
	    $lavelfilter = $this->LevelClause();
	    $this->getData['user_id'] = isset($this->getData['user_id'])?Zend_Encript_Encription::decode($this->getData['user_id']):'';
		$lavelfilter .= commonfunction::filters($this->getData);
		if(isset($this->getData['shipment_type']) && $this->getData['shipment_type']>0){
		    $lavelfilter .=  " AND ST.shipment_type='".$this->getData['shipment_type']."'";
		}
		if(isset($this->getData['from_date']) && isset($this->getData['to_date']) &&  $this->getData['from_date']!='' && $this->getData['to_date']!=''){
		    $lavelfilter .=  " AND DATE(ST.create_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";
		}
		if(isset($this->getData['print_status']) && $this->getData['print_status']>0){
		    $lavelfilter .=  ($this->getData['print_status']==1)?" AND BD.label_date!='0000-00-00 00:00:00'":" AND BD.label_date='0000-00-00 00:00:00'";
		}
		
		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'DATE(BT.barcode_id)','DESC');
	   $select = $this->_db->select()
	   							   ->from(array('BT'=>SHIPMENT_BARCODE),array('COUNT(1) AS CNT'))
								   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BT.barcode_id=BD.barcode_id",array(''))
								   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(''))
								   ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array(''))
								   ->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array(''))
								   ->joininner(array('SR'=>SERVICES),"SR.service_id=BT.service_id",array(''))
								   ->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array(''))
								   ->where("BT.checkin_status='0' AND BT.delete_status='0'".$lavelfilter);
		$total = $this->getAdapter()->fetchRow($select);						   
								    
	   $select = $this->_db->select()
	   							   ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode','weight','barcode_id'))
								   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BT.barcode_id=BD.barcode_id",array('rec_reference','label_date'))
								   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('create_date','quantity'))
								   ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array('user_id'))
								   ->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array('forwarder_name'))
								   ->joininner(array('SR'=>SERVICES),"SR.service_id=BT.service_id",array('service_name'))
								   ->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array('country_name'))
								   ->where("BT.checkin_status='0' AND BT.delete_status='0'".$lavelfilter)
								   ->order("BT.barcode_id DESC")
								   ->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
								   ->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);//print_r($select->__toString());die;
		$result = $this->getAdapter()->fetchAll($select);
		return array('Total'=>$total['CNT'],'Records'=>$result);
		}catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  return array('Total'=>0,'Records'=>array());
		}						   
	}
	
	public function getfilters(){
	   try{
	   $lavelfilter = $this->LevelClause();
	   $select = $this->_db->select()
								->from(array('BT'=>SHIPMENT_BARCODE),array())
								->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array())
								->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array('AT.'.ADMIN_ID,'AT.'.COMPANY_NAME))
								->where("BT.checkin_status='0'".$lavelfilter)
								->group("AT.user_id")
								->order("AT.company_name");
								//print_r($select->__toString());die;
		$userdata = $this->getAdapter()->fetchAll($select);
		
		$select = $this->_db->select()
								->from(array('BT'=>SHIPMENT_BARCODE),array())
								->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array())
								->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array('country_id','CONCAT(CT.cncode,"-",CT.country_name) AS country_code_name'))
								->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array())
								->where("BT.checkin_status='0'".$lavelfilter)
								->group("CT.country_id")
								->order("CT.country_name");
								//print_r($select->__toString());die;
		$countrydata = $this->getAdapter()->fetchAll($select);
		
		$select = $this->_db->select()
								->from(array('BT'=>SHIPMENT_BARCODE),array())
								->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array())
								->joininner(array('FT' =>FORWARDERS),'FT.'.FORWARDER_ID.'=BT.'.FORWARDER_ID.'',array('FT.'.FORWARDER_ID,'FT.forwarder_name'))
								 ->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array())
								->where("BT.checkin_status='0'".$lavelfilter)
								->group("BT.forwarder_id")
								->order("FT.forwarder_name");
								//print_r($select->__toString());die;
		$fowarderdata = $this->getAdapter()->fetchAll($select);
		
		  return array('User'=>$userdata,'Country'=>$countrydata,'Forwarder'=>$fowarderdata);
		}catch(Exception $e){
		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  return array('User'=>array(),'Country'=>array(),'Forwarder'=>array());
		}
	}
	
	public function getParcelDetails(){ 
	     $barcode_id = '';
		 $lavelfilter = $this->LevelClause();
		 $Records = array();

		 // $last7 = substr($this->getData['search_barcode'], -7);
		 // $last7count = strlen(ltrim($last7,0));
		 // $first7 = substr(ltrim($this->getData['search_barcode'],0), 0,$last7count); 
		 // $last7upd = substr($this->getData['search_barcode'], -$last7count); 
		 // if($first7 == $last7upd)
		 // 	$this->getData['search_barcode'] = $last7;			 
		 // echo $last7."--".$first7;
		 // die;

    	// echo $this->getData['search_barcode'];die;

		 try{
		 $select = $this->_db->select()
									   ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id','forwarder_id'))
									   ->where("(barcode='".$this->getData['search_barcode']."' OR tracenr_barcode='".$this->getData['search_barcode']."')");
			$result = $this->getAdapter()->fetchRow($select);
		if(empty($result)){
			$select = $this->_db->select()
								   ->from(array('EC'=>EMERGENCY_CHECKIN),array('barcode_id'))
								   ->where("old_barcode='".$this->getData['search_barcode']."'");
			$result = $this->getAdapter()->fetchRow($select);

		}
		if(empty($result)){
		       $select = $this->_db->select()
						  ->from(array('BE'=>SHIPMENT_BARCODE_EDITED),array('barcode_id'))
						  ->where("BE.barcode='".$this->getData['search_barcode']."'");
			  $result = $this->getAdapter()->fetchRow($select);
		 }

		 if(isset($result['barcode_id']) && strlen($this->getData['search_barcode'])==7 ){
		 	$select = $this->_db->select()
									   ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id','forwarder_id'))
									   ->where("(barcode_id='".$result['barcode_id']."') AND forwarder_id=37");
			   $res = $this->getAdapter()->fetchRow($select);
			   if(!empty($res))
			   $result = array();
		 }

		 if(empty($result)){
		        $select = $this->_db->select()
									   ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id'))
									   ->where("(tracenr='".$this->getData['search_barcode']."')");
			   $result = $this->getAdapter()->fetchRow($select);
		 }
		if(!empty($result)){
		     $select = $this->_db->select()
	   							   ->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
								   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BT.barcode_id=BD.barcode_id",array('rec_reference','checkin_date','checkin_ip','checkin_by','driver_id','assigned_date','pickup_date','depot_invoice_number','customer_invoice_number','edi_date','manifest_number','delivery_date','received_by'))
								   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('country_id','senderaddress_id','addservice_id','original_forwarder','rec_name','rec_contact','rec_street','rec_streetnr','rec_address','rec_street2','rec_city','rec_zipcode','rec_email','rec_phone','email_notification','create_date','create_by','create_ip','quantity','goods_id'))
								   ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array('user_id','company_name','parent_id'))
								   ->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array('forwarder_name'))
								   ->joininner(array('SR'=>SERVICES),"SR.service_id=BT.service_id",array('service_name'))
								   ->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array('country_name','continent_id'))
								   ->joininner(array('SM'=>SHIPMENT_TYPE),"SM.status_id=ST.shipment_type",array('shipment_mode'))
								   ->joinleft(array('AD'=>ADDITIONAL_DOCUMENTS),"BD.barcode_id=AD.barcode_id",array('upload_file','AD.is_removed','AD.is_downloaded','AD.document_id'))
								   ->joinleft(array('DL'=>DRIVER_DELIVERY_LIST),"BD.barcode_id=DL.barcode_id",array('DL.signature','DL.assigned_date as deliveryassigneddate'))
								   ->where("BT.barcode_id=".$result['barcode_id']."".$lavelfilter);
								   // print_r($select->__toString());die;
			$Records = $this->getAdapter()->fetchRow($select);
		}
	 
	 }catch(Exception $e){

	 	// echo $e->getMessage();die;
	      $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	   }

		 // print_r($Records);die;

		   if(!empty($Records)){
				   $this->RecordData = $Records;
				    $this->RecordData['forwarder_details'] = $this->ForwarderDetail();
		   }
	   return  $this->RecordData;	
	}
	
	public function batchCheckin(){ 
	      global $EmailObj,$objSession;
	      $select = $this->_db->select()
	   							   ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id'))
								   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('email_notification'))
								   ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array(''))
								   ->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array(''))
								   ->joininner(array('SR'=>SERVICES),"SR.service_id=BT.service_id",array(''))
								   ->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array(''))
								   ->where("BT.checkin_status='0'");
		  if(isset($this->getData['shipment_mode1']) && $this->getData['shipment_mode1']!=''){
		    $lavelfilter = $this->LevelClause();
			$this->getData['user_id'] = isset($this->getData['user_id'])?Zend_Encript_Encription::decode($this->getData['user_id']):'';
			$lavelfilter .= commonfunction::filters($this->getData);
			if(isset($this->getData['shipment_type']) && $this->getData['shipment_type']>0){
				$lavelfilter .=  " AND ST.shipment_type='".$this->getData['shipment_type']."'";
			}
			if(isset($this->getData['from_date']) && isset($this->getData['to_date']) &&  $this->getData['from_date']!='' && $this->getData['to_date']!=''){
				$lavelfilter .=  " AND DATE(ST.create_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";
			}
			if(isset($this->getData['print_status']) && $this->getData['print_status']>0){
				$lavelfilter .=  ($this->getData['print_status']==1)?" AND BD.label_date!='0000-00-00 00:00:00'":" AND BD.label_date='0000-00-00 00:00:00'";
			}
			$select->where("1".$lavelfilter);
		  	$select->limit($this->getData['shipment_mode1'],0);
		  }else{
		    $select->where("barcode_id IN ('".commonfunction::implod_array($this->getData['barcode_id'],"','")."')");
		  }	
		  $select->order("BT.barcode_id DESC");				   
		
		$results = $this->getAdapter()->fetchAll($select);

		foreach($results as $result){
		     $this->CheckIN($result['barcode_id'],2);
			 if($result['email_notification']=='1'){
			    $this->getData['barcode_id'] = $result['barcode_id'];
				$EmailObj->emailData = $this->getEmailData();
				$EmailObj->checkinMail();
				// echo"<pre>";print_r($EmailObj->emailData);die;
			 }
		}
		$objSession->successMsg = 'Parcel(s) has been checked-in successfully!!';
	}
	
	public function CheckinCSV(){
	   global $EmailObj,$objSession;
	   $Filename = commonfunction::ImportFile('csv_checkin','csv',1);
	   $response = '';
	   if (($handle = fopen($Filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ';', ' ')) !== FALSE) {
			     $barcode = commonfunction::stringReplace(array("%","!"),array("25","25"),$data[0]);
					 $select = $this->_db->select()
								->from(array('BT'=>SHIPMENT_BARCODE),array(BARCODE,BARCODE_ID,CHECKIN_STATUS))
								->joinleft(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array('email_notification'))
								->where("BT.barcode='".$barcode."' AND DATE(ST.create_date)>=DATE_SUB(CURDATE(), INTERVAL 3 MONTH)");
						//print_r($select->__toString());die;
					   $result = $this->getAdapter()->fetchRow($select);
					   $csvdata = '';
					   if(!empty($result)){
						   if($result[CHECKIN_STATUS]=='1'){
							  $response .= $barcode.';Parcel Already Checked IN'."\n";
						   }
							$checkin = $this->CheckIN($result[BARCODE_ID],8);
							 if($result['email_notification']==1){
								 $this->getData['barcode_id'] = $result['barcode_id'];
								 $EmailObj->emailData = $this->getEmailData();
								 $EmailObj->checkinMail();
								$response .= $barcode.';Has Been Check In SuccessFully'."\n";
							 }
						}else{
						   $response .= $barcode.';No Record Found!'."\n";
						}
			}
		  }  
		  commonfunction::ExportCsv($response,'Response_Checkin','csv');
	}
	
	public function AddParcelError($data){
	    global $objSession;
	   $this->_db->insert(PARCEL_TRACKING,array_filter(array('barcode_id'=>$data['barcode_id'],'status_id'=>$this->getData['status_id'],'status_date'=>new Zend_Db_Expr('NOW()'),'added_date'=>new Zend_Db_Expr('NOW()'),'added_by'=>$this->Useconfig['user_id'])));
	   $objSession->successMsg = 'Error Assigned successfully!!';
	}
	
	public function AddEvent($data,$label_genrate=false){
	   global $objSession; 
	   if(!$label_genrate){
			 $this->gerData['eventComment'] = $this->getData['eventInfo'];
			 $this->gerData['eventInfo'] = ($this->getData['event_action_id']==1)?"Return to depot":'Return to Customer';
		   }
	     $this->_db->insert(SHIPMENT_EVENT_HISTORIES,array_filter(array('barcode_id'=>$data['barcode_id'],'event_action'=>$this->getData['event_action_id'],'event_userid'=>$this->Useconfig['user_id'],'eventInfo'=>$this->gerData['eventInfo'],'eventComment'=>$this->getData['eventComment'],'event_ip'=>commonfunction::loggedinIP())));
		   
		   $select = $this->_db->select()
			 					->from(array('BT'=>SHIPMENT_BARCODE),array('COUNT(1) AS CNT'))
								->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array())
								->where("BT.barcode_id='".$data['barcode_id']."' AND ST.addservice_id IN(7,146,141,157)");
		  $shipment_detail = $this->getAdapter()->fetchRow($select);
		  
		  if($shipment_detail['CNT']>0){
		       $select = $this->_db->select()
			 					->from(INVOICE_COD,array('COUNT(1) AS CNT'))
								->where("barcode_id='".$data['barcode_id']."'");
				$checktotal = $this->getAdapter()->fetchRow($select);
				if($checktotal['CNT']<=0){
		     		 $this->_db->insert(INVOICE_COD,array_filter(array('barcode_id'=>$data['barcode_id'],'user_id'=>$data['user_id'],'reasion'=>$this->getData['eventInfo'],'status'=>'Return')));
				}													
		  }
		//This Class caaled for create Event Label
		if($label_genrate){
			$eventobj = new EventScanning();
			$eventobj->eventReturnParcel($data);  
		}
	   $objSession->successMsg = 'Event Added successfully!!';
	}
	
	public function ParcelDoAction(){
	  switch($this->getData['doaction']){
	     case 1:
		   $this->RevertParcel();
		 break;
		 case 2:
		   $this->DeleteParcel();
		 break;
		 case 3:
		    $performa = new Application_Model_Performainvoice();
		    $performa->getCN23CP71Data($this->getData['barcode_id']);
		 break;
		 case 4:
		 	$performa = new Application_Model_Performainvoice();
		    $performa->Invoice(array('barcode_id'=>$this->getData['barcode_id']),true);
		  break;
	  }
	  echo json_encode(array('Status'=>'S','message'=>'Action Perform successfully!!'));
	}
	
	public function DeleteParcel(){
	       $select = $this->_db->select()
								->from(SHIPMENT_BARCODE,array('shipment_id','barcode_id'))
								->where("barcode_id='".$this->getData['barcode_id']."' AND depot_invoice_status='0' AND customer_invoice_status='0'");
								//print_r($select->__toString());die;
			$result = $this->getAdapter()->fetchRow($select);
			if(!empty($result)){
			   $this->_db->update(SHIPMENT_BARCODE,array('delete_status'=>1),"barcode_id='".$this->getData['barcode_id']."'");
			   $select = $this->_db->select()
								->from(SHIPMENT_BARCODE,array('COUNT(1) AS CNT'))
								->where("shipment_id='".$result['shipment_id']."' AND delete_status='0'");
								//print_r($select->__toString());die;
			  $moreparcel = $this->getAdapter()->fetchRow($select);
			  if($moreparcel['CNT']<=0){
			     $this->_db->update(SHIPMENT,array('delete_status'=>1),"shipment_id='".$result['shipment_id']."'"); 
			  }
				$result['deleted_by'] = $this->Useconfig['user_id'];
				$result['deleted_date'] = commonfunction::DateNow();
				$result['deleted_ip'] = commonfunction::loggedinIP();
				$this->insertInToTable(SHIPMENT_DELETED,array($result));
			}
			return true;	
	}
	public function RevertParcel(){
	       $select = $this->_db->select()
								->from(SHIPMENT_BARCODE,array('shipment_id'))
								->where("barcode_id='".$this->getData['barcode_id']."' AND depot_invoice_status='0' AND customer_invoice_status='0'");
								//print_r($select->__toString());die;
			$result = $this->getAdapter()->fetchRow($select);
			if(!empty($result)){
			   $this->_db->update(SHIPMENT_BARCODE,array('checkin_status'=>'0','edi_status'=>0,'hub_status'=>0),"barcode_id='".$this->getData['barcode_id']."'");
			   $this->_db->update(SHIPMENT_BARCODE_DETAIL,array('checkin_date'=>'0000-00-00 00:00:00','checkin_by'=>0,'checkin_ip'=>'','edi_date'=>'0000-00-00 00:00:00'),"barcode_id='".$this->getData['barcode_id']."'");
			}
			return true;
	}
	
	public function getDeletedInf($barcode_id){
			$select = $this->_db->select()
								   ->from(array('SD'=>SHIPMENT_DELETED),array('*'))
								    ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=SD.deleted_by",array('company_name'))
								   ->where("barcode_id='".$barcode_id."'");
			return $this->getAdapter()->fetchRow($select);
	}
	
	
}

