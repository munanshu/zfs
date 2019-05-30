<?php

class Planner_Model_Delivery extends Zend_Custom
{
	public function getscheduledelivery() {
	   try {
			   $filterString =  $this->LevelClause();
				if (isset($this->getData['filter_customer']) && $this->getData['filter_customer'] != '') {
					$filterString = ' AND SCL.user_id=' . Zend_Encript_Encription:: decode($this->getData['filter_customer']);
				}
				if (isset($this->getData['filter_barcode']) && $this->getData['filter_barcode'] != '') {
					$filterString .=" AND BT.barcode='" . $this->getData['filter_barcode'] . "'";
				}
				if (isset($this->getData['filter_postcode']) && $this->getData['filter_postcode'] != '') {
					$zipCode = substr($this->getData['filter_postcode'], 0, 4);
					$filterString .=" AND SUBSTRING(SCL.rec_zipcode,1,4)like'" . $zipCode . "%'";
				}
			$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'BD.checkin_date','DESC');
            $select = $this->_db->select()
						    ->from(array('BT' => SHIPMENT_BARCODE), array('BT.barcode'))
							->joininner(array('BD' => SHIPMENT_BARCODE_DETAIL), 'BD.barcode_id = BT.barcode_id', array('DATE(BD.checkin_date) AS checkin_date','shipment_type'=>new Zend_Db_Expr('1')))
						    ->joininner(array('SCL' => SHIPMENT_BARCODE_LOG), 'BT.barcode_id = SCL.barcode_id', array('BT.weight','BT.barcode','BT.barcode_id','SCL.expected_delivery AS delivery_date'))
							->joininner(array('AT' => USERS_DETAILS), 'SCL.user_id = AT.user_id', array('company_name','AT.user_id'))
							->joininner(array('PT' => USERS_DETAILS), 'PT.user_id = AT.parent_id', array('company_name AS depot_name'))
							->joininner(array('CT' => COUNTRIES), 'SCL.country_id=CT.country_id',
									array('delivery_address' => "CONCAT(SCL.rec_name,'^',SCL.rec_contact,'^',CONCAT(SCL.rec_street,' ',SCL.rec_streetnr),'^',SCL.rec_address,'^',CONCAT(SCL.rec_zipcode,' ',SCL.rec_city),'^',CT.country_name)"))
							->where("BT.checkin_status='1' AND BT.forwarder_id=22 AND BT.delivery_status='0' AND SCL.driver_id=0 AND SCL.expected_delivery<= CURDATE()")
							->where('BD.checkin_date > NOW() - INTERVAL 6 MONTH'.$filterString)
							->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
							->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
							//print_r($select->__tostring());die;
				return $this->getAdapter()->fetchAll($select);
		}catch (Exception $e){
            $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
			return array();
        }

    }
	
	public function getEventReturnParcel($assigned=false) {
	   try {
				$filterString =  $this->LevelClause();
				if (isset($this->getData['filter_customer']) && $this->getData['filter_customer'] != '') {
					$filterString = ' AND ST.user_id=' . Zend_Encript_Encription:: decode($this->getData['filter_customer']);
				}
				if (isset($this->getData['filter_barcode']) && $this->getData['filter_barcode'] != '') {
					$filterString .=" AND BT.barcode='" . $this->getData['filter_barcode'] . "'";
				}
				if (isset($this->getData['filter_postcode']) && $this->getData['filter_postcode'] != '') {
					$zipCode = substr($this->getData['filter_postcode'], 0, 4);
					$filterString .=" AND SUBSTRING(ST.rec_zipcode,1,4)like'" . $zipCode . "%'";
				}
				if (isset($this->getData['filter_driver']) && $this->getData['filter_driver'] != '') {
					$filterString .=" AND SCL.driver_id='" . Zend_Encript_Encription:: decode($this->getData['filter_driver']) . "'";
				}
			if($assigned){
			   $filterString  .= " AND SCL.driver_id >0 AND SCL.delivery_status='0' AND DATE(SCL.assign_date) = CURDATE()";
			}else{
			   $filterString  .= " AND SCL.driver_id=0 AND SCL.delivery_status='0'";
			}
			$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'DATE(event_date)','DESC');
			$user_id = $this->LevelClause();
            $select = $this->_db->select()
							->from(array('BT' => SHIPMENT_BARCODE), array('BT.weight','BT.barcode','BT.barcode_id'))
							->joininner(array('ST' => SHIPMENT_BARCODE_LOG), 'ST.barcode_id = BT.barcode_id', array('ST.user_id'))
                            ->joininner(array('SCL' => SHIPMENT_EVENT_HISTORIES), 'SCL.barcode_id = BT.barcode_id ',array('event_date AS checkin_date','SCL.event_action','DATE(SCL.assign_date) AS assign_date','SCL.delivery_date','shipment_type'=>new Zend_Db_Expr('2'),'SCL.expected_delivery_date AS delivery_date'))
							->joininner(array('AT' => USERS_DETAILS), 'ST.user_id = AT.user_id', array('company_name','AT.user_id'))
							->joininner(array('AT1'=>USERS_DETAILS),'AT1.'.ADMIN_ID.'=AT.'.PARENT_ID,array('AT1.company_name AS depot_name'))
							->joininner(array('CT' => COUNTRIES), 'ST.country_id=CT.country_id',array('delivery_address' => "CONCAT(ST.rec_name,'^',ST.rec_contact,'^',CONCAT(ST.rec_street,' ',ST.rec_streetnr),'^',ST.rec_address,'^',CONCAT(ST.rec_zipcode,' ',ST.rec_city),'^',CT.country_name)"))
							->joinleft(array('DDT' => DRIVER_DETAIL_TABLE), 'DDT.driver_id=' . 'SCL.driver_id', array('DDT.driver_name'))
							->where("SCL.delivery_date <= CURDATE() AND SCL.event_date > NOW() - INTERVAL 6 MONTH  AND SCL.delivery_status='0'".$filterString)
							->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
							->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
						//print_r($select->__tostring());die;
				return $this->getAdapter()->fetchAll($select);
		}catch (Exception $e){ 
            $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
        }

    }
	
	public function getBarcodeCount($barcodeID){
	            $select = $this->_db->select()
						   ->from(array('PSD'=>PLANNER_SCHEDULE_DELIVERY), array('COUNT'=>'count(*)'))
							->where('PSD.barcode_id=?',$barcodeID );
				return $this->getAdapter()->fetchRow($select);
	}
	public function setDateTimeDelivery() {
	   try {	
				$barcodeID= Zend_Encript_Encription:: decode($this->getData['token']);
				 //print_r($this->getData);die;
				 $expected_deliverydate = $this->getData['delivery_date'].' '.$this->getData['delivery_time'];
				 switch($this->getData['shipment_type']){
				    case 1:
					   $this->_db->update(SHIPMENT_BARCODE_LOG,array('expected_delivery'=>$expected_deliverydate),"barcode_id='".$barcodeID."'");
					break;
					case 2:
					  $this->_db->update(SHIPMENT_EVENT_HISTORIES,array('expected_delivery_date'=>$expected_deliverydate),"barcode_id='".$barcodeID."'");
					break;
				 }
				return true;
		}catch (Exception $e){
            $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
            return false;
		}
    }

	public function assignToDriver(){
	  try {
	      $dataArray['driver_id'] = Zend_Encript_Encription:: decode($this->getData['driver_id']);
		 foreach($this->getData['barcode_id'] as $value){
		        $expData = commonfunction::explode_string($value,'&');
				$barcodeid = $expData['0'];
				$dataArray['shipment_type'] = $expData['1'];
				$dataArray['user_id'] = $expData['2'];
				$dataArray['delivery_date'] = $expData['3'];
				$dataArray['delivery_time'] = $expData['4'];
				switch($dataArray['shipment_type']){
				    case 1:
					   $this->_db->update(SHIPMENT_BARCODE_LOG,array('driver_id'=> $dataArray['driver_id'],'assign_date'=>new Zend_Db_Expr('CURDATE()')),"barcode_id='".$barcodeid."'");
					break;
					case 2:
					  $this->_db->update(SHIPMENT_EVENT_HISTORIES,array('driver_id'=> $dataArray['driver_id'],'assign_date'=>new Zend_Db_Expr('CURDATE()')),"barcode_id='".$barcodeid."'");
					break;
				 }
		 }
		 return true;
	  }catch (Exception $e){
            $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
          return false;
	  }
	}

	
	public function getassigndelivery() {
	   try {
			$filterString =  $this->LevelClause();
				if (isset($this->getData['filter_customer']) && $this->getData['filter_customer'] != '') {
					$filterString = ' AND SCL.user_id=' . Zend_Encript_Encription:: decode($this->getData['filter_customer']);
				}
				if (isset($this->getData['filter_barcode']) && $this->getData['filter_barcode'] != '') {
					$filterString .=" AND BT.barcode='" . $this->getData['filter_barcode'] . "'";
				}
				if (isset($this->getData['filter_driver']) && $this->getData['filter_driver'] != '') {
					$filterString .=" AND SCL.driver_id='" . Zend_Encript_Encription:: decode($this->getData['filter_driver']) . "'";
				}
			$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'BD.checkin_date','DESC');
            $select = $this->_db->select()
						    ->from(array('BT' => SHIPMENT_BARCODE), array('BT.barcode','BT.weight'))
							->joininner(array('BD' => SHIPMENT_BARCODE_DETAIL), 'BD.barcode_id = BT.barcode_id', array('DATE(BD.checkin_date) AS checkin_date','shipment_type'=>new Zend_Db_Expr('1')))
						    ->joininner(array('SCL' => SHIPMENT_BARCODE_LOG), 'BT.barcode_id = SCL.barcode_id', array('BT.weight','BT.barcode','BT.barcode_id','SCL.expected_delivery AS delivery_date','SCL.assign_date'))
							->joininner(array('AT' => USERS_DETAILS), 'SCL.user_id = AT.user_id', array('company_name','AT.user_id'))
							->joininner(array('PT' => USERS_DETAILS), 'PT.user_id = AT.parent_id', array('company_name AS depot_name'))
							->joininner(array('CT' => COUNTRIES), 'SCL.country_id=CT.country_id',
									array('delivery_address' => "CONCAT(SCL.rec_name,'^',SCL.rec_contact,'^',CONCAT(SCL.rec_street,' ',SCL.rec_streetnr),'^',SCL.rec_address,'^',CONCAT(SCL.rec_zipcode,' ',SCL.rec_city),'^',CT.country_name)"))
							->joininner(array('DT' => DRIVER_DETAIL_TABLE), 'DT.driver_id = SCL.driver_id', array('driver_name'))		
							->where("BT.checkin_status='1' AND BT.forwarder_id=22 AND BT.delivery_status='0' AND SCL.driver_id>0")
							->where('BD.checkin_date > NOW() - INTERVAL 6 MONTH'.$filterString)
							->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
							->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
							//print_r($select->__tostring());die;
				return $this->getAdapter()->fetchAll($select);
		}catch (Exception $e){
            $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
        }

    }
	
	public function driverManifest(){
	  // print_r($this->getData);die;
	  global $labelObj,$objSession;
	   $ManifestPdfObj = new Zend_Labelclass_ManifestPdf('P','mm','a6');
	   $Directory = DRIVER_MANIFEST_SAVE.date('Y-m');
		if(!is_dir($Directory)){
			mkdir($Directory);
			chmod($Directory, 0777);
		}
		
	   foreach($this->getData['barcode_id'] as $value){
	       $dataArr =array();
		   $expData = commonfunction::explode_string($value,'&');
	       switch($expData[1]){
		      case 1:
			     $manifestData   = $this->getData['manifest_data'][$expData[0]];
				 $manifestrecord = commonfunction::explode_string($manifestData,'@');
				
			  break;
			  case 2:
			  break;
		   }
		   $dataArr['Total_Quantity'] = 1;
		   $dataArr['Total_Weight'] = $manifestrecord[3];
		   $dataArr['barcode'] = $manifestrecord[6];
		   $dataArr['addservice_id'] = 0;//$manifestrecord[6]; 
		   $dataArr['expected_delivery_date'] = $manifestrecord[4];
		   $dataArr['senderAddress'] =  commonfunction::explode_string($manifestrecord[2],'@');
		   $customerAddress = $this->getCustomerDetails($manifestrecord[5]);
		   $dataArr['senderAddress'] = array($customerAddress['company_name'],$customerAddress['address1'],$customerAddress['postalcode'],$customerAddress['city'],$customerAddress['country_name']);
		   $depotAddress = $this->getCustomerDetails($customerAddress['parent_id']);
		   $ManifestPdfObj->outputparam['depotAddress'] = array($depotAddress['company_name'],$depotAddress['address1'],$depotAddress['postalcode'],$depotAddress['city'],$depotAddress['country_name']);
		   $driverDetail = $this->driverdetails(Zend_Encript_Encription:: decode($this->getData['filter_driver']));
		   $ManifestPdfObj->outputparam['ManifestData'][] = $dataArr;
	   }
	   $ManifestPdfObj->outputparam['driverAddress'] = array($driverDetail['driver_name'],'Truck','42342324',5345345345,4343534534);
	   $ManifestPdfObj->DriverDeliverManifest();
	   $file_name = 'Driver_Deliver_Manifest_'.date('Ymd_His').'.pdf';
	   $ManifestPdfObj->Output($Directory.'/'.$file_name,'F');
	   $objSession->DriverManifest = DRIVER_MANIFEST_OPEN.date('Y-m').'/'.$file_name;
	}
	
	public function checkTodaysAssign(){
	      $select = $this->_db->select()
						    ->from(array('BT' => SHIPMENT_BARCODE), array('BT.barcode_id'))
						    ->joininner(array('SCL' => SHIPMENT_BARCODE_LOG), 'BT.barcode_id = SCL.barcode_id', array('assign_date','driver_id','shipment_type'=>new Zend_Db_Expr('1'),'current_date'=>new Zend_Db_Expr('CURDATE()')))
							->where("BT.barcode='".$this->getData['barcode']."'");
							//print_r($select->__tostring());die;
		$result =  $this->getAdapter()->fetchRow($select);
		if(empty($result)){
		   $select = $this->_db->select()
						    ->from(array('BT' => SHIPMENT_BARCODE), array('BT.barcode_id'))
						    ->joininner(array('SCL' =>SHIPMENT_EVENT_HISTORIES), 'BT.barcode_id = SCL.barcode_id', array('assign_date','driver_id','shipment_type'=>new Zend_Db_Expr('1'),'current_date'=>new Zend_Db_Expr('CURDATE()')))
							->where("BT.barcode='".$this->getData['barcode']."'");
							//print_r($select->__tostring());die;
		   $result =  $this->getAdapter()->fetchRow($select);
		}
		if(!empty($result)){
		     if($result['driver_id']>0 && $result['assign_date']==$result['current_date']){
			      $data  = array('Status'=>'Y','message'=>'Parcel Found!','shipment_type'=>$result['shipment_type'],'barcode_id'=>$result['barcode_id']);
			 }else{
			     $data  = array('Status'=>'E','message'=>'Parcel not not assigned to driver today for delivery!');
			 }
		}else{
		   $data  = array('Status'=>'N','message'=>'No Record Found!');
		}
		echo json_encode($data);die;
	}
	
	public function ParcelDelvered(){
	       global $objSession;
	       $checkObj = new Application_Model_Checkinajax();
		   $parcel_details = $checkObj->getShipmentDataByBarcodeID($this->getData['barcode_id']);
		   $status_date = date('Y-m-d h:i:s',strtotime($this->getData['date'].' '.$this->getData['time']));
	     if($this->getData['status_id']==1){
		    $status_id = $this->getDeliveredStatus($parcel_details['forwarder_id']);
			switch($this->getData['shipment_type']){
				   case 1:
				        $this->_db->update(SHIPMENT_BARCODE,array('delivery_status'=>'1','error_status'=>'0'),"barcode_id='".$this->getData['barcode_id']."'");
						$this->_db->update(SHIPMENT_BARCODE_DETAIL,array('delivery_date'=>$status_date,'received_by'=>$this->getData['received_by']),"barcode_id='".$this->getData['barcode_id']."'");
				   break;
				   case 2:
				        $this->_db->update(SHIPMENT_EVENT_HISTORIES,array('delivery_status'=>'1','delivery_date'=>$status_date,'received_by'=>$this->getData['received_by']),"barcode_id='".$this->getData['barcode_id']."'");
				   break;
				}
		 }else{
		    switch($this->getData['shipment_type']){
				   case 1:
						$this->_db->update(SHIPMENT_BARCODE,array('error_status'=>'1'),"barcode_id='".$this->getData['barcode_id']."'");
				   break;
				   case 2:
				        $this->_db->update(SHIPMENT_EVENT_HISTORIES,array('error_status'=>'1'),"barcode_id='".$this->getData['barcode_id']."'");
				   break;
			} 
			$status_id = $this->getDeliveredStatus($parcel_details['forwarder_id'],$this->getData['status_id']); 
		 }
		 $this->_db->insert(PARCEL_TRACKING,array_filter(array('barcode_id'=>$this->getData['barcode_id'],'status_id'=>$status_id,'status_date'=>$status_date,'added_date'=>new Zend_Db_Expr('NOW()'),'added_by'=>1,'status_location'=>$parcel_details['rec_city'],'country_id'=>$parcel_details['country_id'])));
		  $objSession->successMsg = "Record Updated successfully !!";
		  echo "Success";die;
	}
	
	public function getDeliveredStatus($forwarder_id,$error_id=0){
	    $where = '';
		if($error_id>0){
		  $where = " AND SL.master_id='".$error_id."'";
		}else{
		  $where = ' AND SL.error_type=1';
		}
	    $select = $this->_db->select()
						    ->from(array('SL' => STATUS_LIST), array('error_id'))
							->where("SL.forwarder_id='".$forwarder_id."'".$where)
							->limit(1);
							//print_r($select->__tostring());die;
		   $result =  $this->getAdapter()->fetchRow($select);
		   return isset($result['error_id'])?$result['error_id']:0;
	}
	
	public function masterError(){
	    $select = $this->_db->select()
						    ->from(array('MS' => STATUS_MASTER), array('master_id','status_name'))
							->where("MS.master_id IN(1,2,13,9)");
							//print_r($select->__tostring());die;
		return   $this->getAdapter()->fetchAll($select);
	}

}

