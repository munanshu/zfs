<?php

ini_set ( 'max_execution_time', 1200); 

class Shipmentmanager_Model_Shipmentmanager extends Zend_Custom

{

	public $CheckinajaxObj = NULL;



	public function getPickRequestList(){

		   $where = '';

	   $OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'create_date','DESC');

	   if(!empty($this->getData['filter_customer'])){

          $where.= " AND AT.user_id='".Zend_Encript_Encription:: decode($this->getData['filter_customer'])."'";

		}

	   if(!empty($this->getData['fromdate']) && !empty($this->getData['fromdate'])){

          $where.= " AND ST.create_date BETWEEN '".$this->getData['fromdate']."' AND '".$this->getData['todate']."'";

		}	

	   	   $accesslevel = $this->LevelClause();

	   $this->_db->query("SET SESSION group_concat_max_len = 1000000");

	   $select = $this->_db->select()

		               ->from(array('ST'=>SHIPMENT), array('DATE(ST.create_date) AS create_date','GROUP_CONCAT(DISTINCT DATE(ST.create_date)) AS Alldate','IF(BT.barcode_id,1,1) AS parcel_type'))

					   ->joininner(array('BT'=>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array('SUM(BT.weight) AS total_weight','COUNT(1) AS total_quantity','GROUP_CONCAT(DISTINCT BT.barcode_id) AS barcode_id'))

					   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('manual_pickup_id'))

					   ->joininner(array('AT'=>USERS_DETAILS),"ST.user_id=AT.user_id",array('company_name','user_id',"CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name) AS customer_address"))

					   ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=AT.user_id",array(''))

					   ->joininner(array('SR'=>SERVICES),'SR.service_id=ST.service_id',array(''))

					   ->joinleft(array('ASP'=>USERS_SCHEDULE_PICKUP),'ASP.user_id=AT.user_id',array())

					   ->joininner(array('CT'=>COUNTRIES),'AT.country_id=CT.country_id',array(''))

					   ->joinleft(array('SST'=>SHIPMENT_SCHEDULE_TIME),'SST.barcode_id=BT.barcode_id AND DATE(SST.date_added) = CURDATE() AND SST.parcel_type=1',array(''))

					   ->joinleft(array('MPT'=>SHIPMENT_MANUAL_PICKUP),'MPT.manual_pickup_id=BD.manual_pickup_id',

					   array('pickup_address'=>"if((BD.manual_pickup_id>0),CONCAT(MPT.name,'^',MPT.street1,'^',MPT.street2,'^',MPT.zipcode,'^',MPT.city,'^',MPT.country),if((ASP.zipcode!='' && ASP.city!=''),CONCAT(ASP.name,'^',ASP.street1,'^',ASP.street2,'^',ASP.zipcode,'^',ASP.city,'^',ASP.country),CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name)))",

					   	  'pickup_date'=>"if((BD.manual_pickup_id>0 && MPT.pickup_date!='0000-00-00'),MPT.pickup_date,if(ASP.user_id>0 && (".commonfunction::lowercase(date('D'))."_start"."!='00:00:00' || default_time_start!='00:00:00'),CURDATE(),CURDATE()))",

					      'pickup_time'=>"if((BD.manual_pickup_id>0 && MPT.pickup_time!='00:00:00'),MPT.pickup_time,IF(SST.pickup_time!='00:00:00' && SST.pickup_time!='',SST.pickup_time,if(ASP.user_id>0 && ".commonfunction::lowercase(date('D'))."_start"."!='00:00:00',".commonfunction::lowercase(date('D'))."_start".",if(default_time_start!='00-00-00',default_time_start,'00-00-00'))))"))

					   ->where("BT.checkin_status='0' AND BT.pickup_status='0' AND BT.show_planner='1' AND BD.driver_id=0 AND BD.assigned_date < CURDATE() AND ST.shipment_type!=5")

					   ->where("US.gls_pickup = '0'".$accesslevel.$where)

					   ->where("(ST.create_date > NOW() - INTERVAL 60 DAY OR (BD.manual_pickup_id>0 AND DATE(MPT.pickup_date) = CURDATE() OR (ASP.picked_by_driver='0' AND BD.manual_pickup_id>0)))")

					   ->group("DATE(ST.create_date)")

					   ->order(new Zend_Db_Expr('create_date')); //print_r($select->__tostring());die;

		return  $this->getAdapter()->fetchAll($select);

	}

	public function getReturnShipmentList(){

	    try{

	    $this->getData['user_id'] = isset($this->getData['user_id'])?Zend_Encript_Encription::decode($this->getData['user_id']):'';

		$this->getData['parent_id'] = isset($this->getData['parent_id'])?Zend_Encript_Encription::decode($this->getData['parent_id']):'';

		$where = $this->LevelClause();

		$where .= commonfunction::filters($this->getData);

								

	    $select = $this->_db->select()

									->from(array('ST'=>SHIPMENT),array('rec_name','rec_reference','create_date','rec_zipcode','quantity','addservice_id','shipment_id','weight'))

									->joinleft(array('BT' =>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array('COUNT(1) AS CNT'))

									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("AT.company_name"))

									->joininner(array('FT' =>FORWARDERS),"FT.forwarder_id=ST.forwarder_id",array("FT.forwarder_name"))

									->joininner(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array("CT.country_name"))

									->joininner(array('SV' =>SERVICES),"ST.service_id=SV.service_id",array("SV.service_name"))

									->where("ST.delete_status='0'  AND ST.shipment_type=16 AND ST.quantity>(SELECT COUNT(1) AS TCNT FROM ".SHIPMENT_BARCODE." AS BT1 WHERE BT1.shipment_id=ST.shipment_id) AND ST.addservice_id IN(148,124)".$where)

									->group("ST.shipment_id")
									->order("ST.create_date DESC"); 

									//print_r($select->__tostring());die;

		 $records =  $this->getAdapter()->fetchAll($select);								

		}catch(Exception $e){$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());}

		

		return $records;

	}

	     

	public function getCustomerScanList(){

		try{

			$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'BT.barcode_id','DESC');

			$filterparam = '';

			if(isset($this->getData['forwarder_filter']) && !empty($this->getData['forwarder_filter'])){

				$filterparam.= " AND BT.forwarder_id = '". Zend_Encript_Encription:: decode($this->getData['forwarder_filter'])."'";

			}

			if(isset($this->getData['filter_country']) && !empty($this->getData['filter_country'])){

				$filterparam.= " AND ST.country_id = '". Zend_Encript_Encription:: decode($this->getData['filter_country'])."'";

			}

			if(isset($this->getData['filter_customer']) && !empty($this->getData['filter_customer'])){

				$filterparam.= " AND AT.user_id = '". Zend_Encript_Encription:: decode($this->getData['filter_customer'])."'";

			}



	    $filterparam.= $this->LevelClause();

	   $select = $this->_db->select()

	   							   ->from(array('BT'=>SHIPMENT_BARCODE),array('COUNT(1) AS CNT'))

								   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BT.barcode_id=BD.barcode_id",array(''))

								   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(''))

								   ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array(''))

								   ->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array(''))

								   ->joininner(array('SR'=>SERVICES),"SR.service_id=BT.service_id",array(''))

								   ->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array(''))

								   ->joinleft(array('CSH'=>CUSTOMER_SCAN_HISTORY),"CSH.barcode_id = BD.barcode_id",array(''))

								   ->where("ST.email_notification='1' AND ISNULL(CSH.barcode_id) AND BT.checkin_status='0' AND AT.parent_id=188".$filterparam);

		$total = $this->getAdapter()->fetchRow($select);						   

		//print_r($total); die;						    

	   $select = $this->_db->select()

	   							   ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode','weight','barcode_id'))

								   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BT.barcode_id=BD.barcode_id",array('rec_reference','label_date'))

								   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('create_date','quantity'))

								   ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array('AT.user_id','AT.company_name'))

								   ->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array('forwarder_name'))

								   ->joininner(array('SR'=>SERVICES),"SR.service_id=BT.service_id",array('service_name'))

								   ->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array('country_name'))

								   ->joinleft(array('CSH'=>CUSTOMER_SCAN_HISTORY),"CSH.barcode_id = BD.barcode_id",array(''))

								   ->where("ST.email_notification='1' AND ISNULL(CSH.barcode_id) AND BT.checkin_status='0' AND AT.parent_id=188".$filterparam)

								   ->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])

								   ->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);//print_r($select->__toString());die;

			$result = $this->getAdapter()->fetchAll($select);

			return array('Total'=>$total['CNT'],'Records'=>$result);

		}catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		  return array('Total'=>0,'Records'=>array());

		}

    }

	

	public function returnShipmentsDetail(){

		try{

	     $select = $this->_db->select()

								->from(array('ST'=>SHIPMENT),array('*'))

								->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array('reciever_address' => "CONCAT(ST.rec_name,'^',ST.rec_contact,'^',CONCAT(ST.rec_street,' ',ST.rec_streetnr),'^',ST.rec_address,'^',CONCAT(ST.rec_zipcode,' ',ST.rec_city),'^',CT.country_name)",'country_name'))

								->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array('company_name','email','phoneno','parent_id'))

								->joininner(array('GS'=>SERVICES),"GS.service_id=ST.service_id",array('service_name'))

								->joinleft(array('AST'=>SERVICES),"AST.service_id=ST.addservice_id",array('addservice'=>'service_name'))

								->where("ST.shipment_id='".Zend_Encript_Encription:: decode($this->getData['shipment_id'])."'");  

		$this->RecordData = $this->getAdapter()->fetchRow($select);

		//$this->getData = $this->getData+$this->RecordData;
		$this->getParcelPrice();
		$parcelDetail = $this->ForwarderDetail(); 
		$this->RecordData['reciever_address']  = $this->RecordData['rec_name'].'^'.$this->RecordData['rec_contact'].'^'.$this->RecordData['rec_street'].' '.$this->RecordData['rec_streetnr'].'^'.$this->RecordData['rec_address'].$this->RecordData['rec_zipcode'].' '.$this->RecordData['rec_city'].' '.$parcelDetail['rec_country_name'];
		//echo "<pre>";print_r($parcelDetail);die;
		$this->RecordData['SenderAddress'] = $parcelDetail['SenderAddress'];

		

		return $this->RecordData;

		}catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		  return array();

		}

	}

	

	

	public function checkinReturnShipments(){

		try{ 

			global $objSession;

			$select = $this->_db->select()

								->from(array('BT'=>SHIPMENT_BARCODE),array('count(*) as CNT'))

								->where("BT.forwarder_id='".Zend_Encript_Encription:: decode($this->getData['forwarder_id'])."' AND tracenr_barcode = '".$this->getData['barcode']."'"); 		

			$existData = $this->getAdapter()->fetchRow($select);

 			if($existData['CNT'] > 0)

			{	

				echo 'F^Enter Detail is already exist, Please Change selected forwarder OR Barcode!';

			    exit;

				}else{

				$select = $this->_db->select()

								->from(array('ST'=>SHIPMENT),array('*'))

								->where("ST.shipment_id='".$this->getData['shipment_id']."'");

				$this->RecordData = $this->getAdapter()->fetchRow($select);

				$this->RecordData[FORWARDER_ID] = Zend_Encript_Encription:: decode($this->getData['forwarder_id']);

				$this->CheckinajaxObj = new Application_Model_Checkinajax();

				$this->CheckinajaxObj->RecordData = $this->RecordData;

				$this->RecordData[REFERENCE] = $this->CheckinajaxObj->SystemGeneratedReference();

				$this->RecordData[LABEL_DATE]= new Zend_Db_Expr('NOW()');

				$this->RecordData[TRACENR] = $this->getData[BARCODE];

				$this->RecordData[TRACENR_BARCODE] = $this->getData[BARCODE];

				$this->RecordData[BARCODE] = $this->getData[BARCODE];

				$this->RecordData[DEPOT_PRICE] = $this->getData[DEPOT_PRICE];

				$this->RecordData[CUSTOMER_PRICE] = $this->getData[CUSTOMER_PRICE];

				//$this->RecordData[CUSTOMER_PRICE] = 10;

				 $this->_db->insert(SHIPMENT_BARCODE,

							 array_filter(array(SHIPMENT_ID   	     => $this->RecordData[SHIPMENT_ID],

												TRACENR    			 => $this->RecordData[TRACENR],

												TRACENR_BARCODE      => $this->RecordData[TRACENR_BARCODE],

												BARCODE    			 => $this->RecordData[BARCODE],

												LOCAL_BARCODE    	 => isset($this->RecordData[LOCAL_BARCODE])?$this->RecordData[LOCAL_BARCODE]:'',

												REFERENCE_BARCODE    => isset($this->RecordData[REFERENCE_BARCODE])?$this->RecordData[REFERENCE_BARCODE]:$this->getShipmentSeriesno($this->RecordData[TRACENR]),

												FORWARDER_ID	     => $this->RecordData[FORWARDER_ID],

												WEIGHT     			 => $this->RecordData[WEIGHT],

												SERVICE_ID           => $this->RecordData[SERVICE_ID],

												DEPOT_PRICE	     	 => $this->RecordData[DEPOT_PRICE],

												CUSTOMER_PRICE	     => $this->RecordData[CUSTOMER_PRICE])));

				$this->RecordData[BARCODE_ID] = $this->getAdapter()->lastInsertId();

				$this->_db->insert(SHIPMENT_BARCODE_REROUTE,array_filter(array(BARCODE_ID		=>	$this->RecordData[BARCODE_ID],

																			   REROUTE_BARCODE  =>	isset($this->RecordData[REROUTE_BARCODE])?commonfunction::addslashesing($this->RecordData[REROUTE_BARCODE]):'')));

				$this->_db->insert(SHIPMENT_BARCODE_DETAIL,array_filter(array(BARCODE_ID		=>	$this->RecordData[BARCODE_ID],

																			  LABEL_DATE 		=>	(isset($this->RecordData[LABEL_DATE]) && $this->RecordData[LABEL_DATE]!='0000-00-00 00:00:00')?new Zend_Db_Expr('NOW()'):'0000-00-00 00:00:00',

																			  REFERENCE		     => $this->RecordData[REFERENCE],

																			  CHECKIN_TYPE		     => $this->RecordData[CHECKIN_TYPE])));

				$this->CheckinajaxObj->getData = $this->RecordData;
				$this->CheckinajaxObj->getData['checkin_type']  =  10;
				$this->CheckinajaxObj->getParcelCheckin(true);

				//$pdfLabelUrl =  $this->CheckinajaxObj->getPrintlabelLink(true);

				$objSession->successMsg = "Return Parcel Check-In Successfully !!";

				echo 'T'.'^Return Parcel Check-In Successfully';

			    exit;

			}

		}catch(Exception $e){ 

			$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

	}

	

	public function customerScan(){

			try{ 

				global $EmailObj;

				$dataArray = array();

				$select = $this->_db->select()

							   ->from(array('BT'=>SHIPMENT_BARCODE),array('*'))

							   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('email_notification'))

							   ->where("BT.barcode='".$this->getData['barcode']."'");

				$result = $this->getAdapter()->fetchRow($select);

				$select = $this->_db->select()

							   ->from(array('CSH'=>CUSTOMER_SCAN_HISTORY),array('count(*) as CNT'))

							   ->where("CSH.barcode_id='".$result[BARCODE_ID]."'");

				$exist = $this->getAdapter()->fetchRow($select);

				if($result['email_notification'] == '1'){

					if($exist['CNT'] == '0'){

					$this->getData['barcode_id'] = $result['barcode_id'];

					$EmailObj->emailData = $this->getEmailData();

					$EmailObj->checkinMail();

					$this->_db->insert(CUSTOMER_SCAN_HISTORY, array(BARCODE_ID =>$result[BARCODE_ID],'scan_date'=>new Zend_Db_Expr('NOW()'),'scan_by'=> $this->Useconfig['user_id'],'scan_ip' => commonfunction::loggedinIP()));

					echo 'T^Customer scan email successfully send !!'; exit;

				  }else{

					echo 'F^Customer scan email already send !!'; exit;

				  }

				}else{

					echo 'F^Email Notification Not Available'; exit;

				}

			}catch(Exception $e){ 

					$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

			}

		}
		
		public function batchcustomerScan(){
			try{ 
				global $EmailObj;
				$dataArray = array();
				$barcodeid = commonfunction::implod_array($this->getData['barcode_id'],',');
				$select = $this->_db->select()
							   ->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
							   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('email_notification'))
							   ->where("ST.email_notification = '1' AND BT.barcode_id IN(".$barcodeid.")");
							   //print_r($select->__tostring());die;
			$result = $this->getAdapter()->fetchAll($select);
			foreach($result as $data){
				$select = $this->_db->select()
							   ->from(array('CSH'=>CUSTOMER_SCAN_HISTORY),array('count(*) as CNT'))
							   ->where("CSH.barcode_id='".$data[BARCODE_ID]."'");
				$exist = $this->getAdapter()->fetchRow($select);
					if($exist['CNT'] == '0'){
					$this->getData['barcode_id'] = $data['barcode_id'];
					$EmailObj->emailData = $this->getEmailData();
					$EmailObj->checkinMail();
					$this->_db->insert(CUSTOMER_SCAN_HISTORY, array(BARCODE_ID =>$data[BARCODE_ID],'scan_date'=>new Zend_Db_Expr('NOW()'),'scan_by'=> $this->Useconfig['user_id'],'scan_ip' => commonfunction::loggedinIP()));
				}
			 }
				return true;
				
			}catch(Exception $e){ 
					$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
			}
		}

		

		

	public function getdeliverytime() {

		try{

        $this->getData['postalcode'] = commonfunction::stringReplace(" ", "", $this->getData['postalcode']);

		$this->getData['country_id'] = Zend_Encript_Encription:: decode($this->getData['country_id']);

		$this->getData['forwarder_id'] = Zend_Encript_Encription:: decode($this->getData['forwarder_id']);

        $select = $this->_db->select()

                        ->from(array('DT' =>DELIVERY_TRACKER), array('DT.delivery_id', 'DT.delivery_priority', 'DT.delivery_zipcode', 

						'DT.country_id', 'DT.delivery_time', 'DT.forwarder_id', new Zend_Db_Expr("(SELECT delivery_time FROM

            ".DELIVERY_TRACKER." WHERE country_id ='" . $this->getData['country_id'] . "' AND DT.delivery_zipcode= delivery_zipcode

            AND forwarder_id ='" . $this->getData['forwarder_id'] . "' ORDER BY delivery_id DESC LIMIT 1) as last_delivery")))

						->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id = DT.forwarder_id",array('FT.forwarder_name'))

						->joininner(array('CT'=>COUNTRIES),"CT.country_id = DT.country_id",array('CT.country_name'))

                        ->where("DT.country_id ='" . $this->getData['country_id'] . "'")

                        ->where("delivery_zipcode like '" . $this->getData['postalcode'] . "'")

                        ->where("DT.forwarder_id ='" . $this->getData['forwarder_id'] . "'")

                        ->group("TIME_FORMAT('delivery_time', '%H:%i')")

                        ->group("delivery_zipcode")

                        ->order("delivery_id DESC");

								//print_r($select->__toString());die;

        $result = $this->getAdapter()->fetchAll($select);

			$datastr = '';

	if(count($result)>0){

	$datastr.='<table width="100%" border="0" id="dataTableGridId"><thead><tr style="text-align: center;color: #ffffff;background: #484b68;"><th>Forwarder</th><th>Country</th><th>Postal code</th><th>Delivery Time</th><th>Last Delivery</th></tr>

						</thead>

						<tbody>';

						 foreach($result as $key=>$data){

						 $class = ($key%2==0)?'even':'odd';

						$datastr.='<tr class="'.$class.'">';

                        $datastr.='<td data-label="Forwarder">'.$data['forwarder_name'].'</td>'; 

						$datastr.='<td data-label="Country">'.$data[COUNTRY_NAME].'</td>';

						$datastr.='<td data-label="Postal code">'.$data['delivery_zipcode'].'</td>';

						$datastr.='<td data-label="Delivery Time">'.$data['delivery_time'].'</td>';

						$datastr.='<td data-label="Last Delivery">'.$data['last_delivery'].'</td></tr>';

					} } else{

						$datastr.='<tr><td data-label="No Data" colspan="7"><div class="nodatatxt" style="color:red">There are no record found!...</div></td></tr></tbody></table>^^F';

						}

	

        echo $datastr;

		exit;

	   }catch(Exception $e){ 

				$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	   }

    }

	

	public function addTracker()

	{

	 try{

		$this->getData['country_id'] = Zend_Encript_Encription:: decode($this->getData['country_id']);

		$this->getData['forwarder_id'] = Zend_Encript_Encription:: decode($this->getData['forwarder_id']);

		$this->getData['delivery_zipcode'] = $this->getData['postalcode'];

		$result = $this->insertInToTable(DELIVERY_TRACKER,array($this->getData));

		$message = ($result)?'Delivery Tracker Successfully Added..':'Something is wrong..';

		$datastr = '<tr><td data-label="No Data" colspan="7"><div class="nodatatxt" style="color:green">'.$message.'</div></td></tr></tbody></table>';

		echo $datastr;

		exit;

	   }catch(Exception $e){ 

				$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	   }

	}

	

	public function deleteReturnShipment(){

		 try{

			 $rerult = $this->_db->delete(SHIPMENT,SHIPMENT_ID.'='.$this->getData[SHIPMENT_ID]);

			 return $rerult;

		   }catch(Exception $e){ 

				$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	   }

	}



	public function defaultManualPickup(){

		 try{

			   $this->getData['user_id'] = Zend_Encript_Encription:: decode($this->getData['user_id']);

			   $select =  $this->_db->select()

									 ->from(array('SMP'=>SHIPMENT_MANUAL_PICKUP),array ('*')) 

									 ->where("SMP.user_id='".$this->getData['user_id']."' AND SMP.pickup_date='".$this->getData['pickup_date']."'"); 							

			   $dailypickups = $this->getAdapter()->fetchrow($select);

			  if(!empty($dailypickups)){

				$result = $this->UpdateInToTable(SHIPMENT_MANUAL_PICKUP,array($this->getData),"pickup_date=".$this->getData['pickup_date']." AND user_id=".$this->getData['user_id']);

			  }else{

			   $this->getData['create_date'] = new Zend_Db_Expr('NOW()');

			   $this->getData['manual_type'] = 1;

			   $result = $this->insertInToTable(SHIPMENT_MANUAL_PICKUP,array($this->getData));

			  }

			  return $result;

		   }catch(Exception $e){ 

				$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	   }	

	

	}

	public function ManualschedulePickup($recordArr){

	try{

	    $accesslevel = $this->LevelClause();

		$userds = $this->getuserIds($recordArr);

		if(!empty($userds)){

		  $accesslevel .= " AND AT.user_id NOT IN(".commonfunction::implod_array($userds,',').")";

		}

		if(!empty($this->getData['filter_customer'])){

          $accesslevel.= " AND AT.user_id='".Zend_Encript_Encription:: decode($this->getData['filter_customer'])."'";

		}

	   if(!empty($this->getData['fromdate']) && !empty($this->getData['fromdate'])){

          $accesslevel.= " AND DATE(ST.create_date) >= '".$this->getData['fromdate']."' AND DATE(ST.create_date) <= '".$this->getData['todate']."'";

		}

		

				$select = $this->_db->select()

		               ->from(array('ST'=>SHIPMENT_MANUAL_PICKUP),  array('manual_weight as total_weight','manual_quantity as total_quantity','create_date','if(manual_pickup_id,4,4) as parcel_type','manual_pickup_id','manual_pickup_id AS barcode_id'))

					   ->joininner(array('AT'=>USERS_DETAILS),"ST.user_id=AT.user_id",array('company_name','user_id',"CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name) AS customer_address"))

					   ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=AT.user_id",array(''))

					   ->joininner(array('CT'=>COUNTRIES),'AT.country_id=CT.country_id',array(''))

					   ->joinleft(array('SST'=>SHIPMENT_SCHEDULE_TIME),'SST.barcode_id=ST.manual_pickup_id AND DATE(SST.date_added) = CURDATE() AND SST.parcel_type=4',array(''))

					   ->joinleft(array('SPT'=>USERS_SCHEDULE_PICKUP),'SPT.user_id=AT.'.ADMIN_ID,array('pickup_address'=>"if(CONCAT(ST.name,'^',ST.zipcode,'^',ST.city)!='^^^',CONCAT(ST.name,'^',ST.street1,'^',ST.street2,'^',ST.zipcode,'^',ST.city,'^',ST.country),if(SPT.user_id>0 && SPT.name!='',CONCAT(SPT.name,'^',SPT.street1,'^',SPT.street2,'^',SPT.zipcode,'^',SPT.city,'^',SPT.country),CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name)))",

					     'pickup_date'=>"if((ST.pickup_date!='0000-00-00'),ST.pickup_date,if(SPT.user_id>0 && (".commonfunction::lowercase(date('D'))."_start"."!='00:00:00' || default_time_start!='00-00-00'),CURDATE(),CURDATE()))",

					      'pickup_time'=>"if(ST.pickup_time!='00:00:00' && ST.pickup_time!='',ST.pickup_time,if(SPT.user_id>0 && ".commonfunction::lowercase(date('D'))."_start"."!='00:00:00',".commonfunction::lowercase(date('D'))."_start".",if(default_time_start!='00-00-00',default_time_start,'00-00-00')))"))

					   ->joinleft(array('DH'=>DRIVER_HISTORY),"DH.barcode_id=ST.manual_pickup_id AND DH.parcel_type=4 AND DH.pickup_date=CURDATE()",array(''))	  

					   ->where("ST.pickup_date >= CURDATE() AND US.planner_status='1' AND (SELECT COUNT(1) AS CNT FROM ".DRIVER_HISTORY." DHT WHERE AT.user_id=DHT.user_id AND  DATE(DHT.assign_date)=CURDATE())<=0 AND manual_type=1".$accesslevel)	

					   ->order(new Zend_Db_Expr('pickup_time'))

					   ->order(new Zend_Db_Expr('pickup_date')); //print_r($select->__tostring());die;

		return $this->getAdapter()->fetchAll($select);      

			   }catch(Exception $e){ 

				$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	   }

	}

	public function getuserIds($records){

	   $userids =array();

	   foreach($records as $record){

	      $userids[] = $record['user_id'];

	   }

	   return $userids;

	}

}



