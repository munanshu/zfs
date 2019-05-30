<?php

class Application_Model_Common extends Zend_Custom
{
    public function getParcelshopList($country_id, $zipcode){
	    $locations = array();
		$countryDetail = $this->getCountryDetail($country_id,1);
		$zipcode = $this->ValidateZipcode($zipcode,$country_id);
		$country_code = $countryDetail['cncode'];
		if($country_id==9){
		  $deburenLocation = $this->DeburenParcelpoints($country_code,$zipcode);
		  $locations = array_merge($deburenLocation,$locations);
		}
		if($country_id==6 || $country_id==8){
		    $mrLocation = $this->MondialRelayPoints($country_code,$zipcode);
			$locations = array_merge($mrLocation,$locations);
		}
		if($country_id==9 || $country_id==15){
		  $dpdLocation = $this->DPDParcelPoints($country_code,$zipcode);
		  $locations = array_merge($dpdLocation,$locations);
		  
		  $ownLocation = $this->OwnParcelpoints($country_code,$zipcode);
		  $locations = array_merge($ownLocation,$locations);
		}
		$distance = array();
		foreach($locations as $key => $row) { 
			$distance[$key]  = $row['distance'];
		} 
		array_multisort($distance, SORT_ASC, $locations);
		return $locations;
	}
	
	public function DPDParcelPoints($country_code,$zipcode){
	   try {
		$reference = array('delisId' => 'beero512', 'password' => 'l0307e','messageLanguage' =>'en_EN');
		$client = new SoapClient("https://public-ws-stage.dpd.com/services/LoginService/V2_0?wsdl");
		$result = $client->getAuth($reference);
		$orderClient = new SoapClient("https://public-ws-stage.dpd.com/services/ParcelShopFinderService/V5_0?wsdl", array('trace' => 1));
		$ArrOrderHeaderParam = Array
		(
			 "delisId" =>'beero512',  // Delis ID
			"messageLanguage" => "en_US", // Language of request and response
			"authToken" =>$result->return->authToken // Token provided by authentication above. $arrAuthResult['authToken']
		
		);

	// Authentication is done in the header and not in the function call.
	// Create (and set) new header with namespace, and parameters
	
	$sHeader = new SoapHeader('http://dpd.com/common/service/types/Authentication/2.0', 'authentication', $ArrOrderHeaderParam);
	$orderClient->__setSoapHeaders(array($sHeader));
	$arrOrderBodyParam = array(
		"zipCode" =>$zipcode, 
		"country"=>$country_code,
		"limit"=>20       // amount of shops to return
	);
	  $arrOrderResult = $orderClient->findParcelShops($arrOrderBodyParam);
	  } catch (Exception $e)  { }
	  $shopAddress = array();
	  if(!empty($arrOrderResult->parcelShop)){
		   foreach($arrOrderResult->parcelShop as $parcelshopes){
				$servicecode = 0;
				foreach($parcelshopes->services->service as $service){
				  if(isset($service->code) && $service->code==100){ 
				   	$servicecode = 100;
				  } 
				}
				if($servicecode==100){ 
					 $data['parcelShopId'] = $parcelshopes->parcelShopId;
					 $data['company'] = commonfunction::stringReplace("'",'',$parcelshopes->company);
					 $data['street'] = $parcelshopes->street;
					 $data['houseNo'] = $parcelshopes->houseNo;
					 $data['state'] = $parcelshopes->state;
					 $data['zipCode'] = $parcelshopes->zipCode;
					 $data['city'] = $parcelshopes->city;
					 $data['phone'] = $parcelshopes->phone;
					 $data['email'] = $parcelshopes->email;
					 $data['distance'] = $parcelshopes->distance;
					 $data['forwarder_id'] = 32;
					 $data['shop_type'] = 1;
					 $data['latitude'] 	= $parcelshopes->latitude;
					 $data['longitude'] 	= $parcelshopes->longitude;
					 $data['icon']   = IMAGE_LINK.'/D.png';
					 $shopAddress[] = $data;
			 } 
		   }
		}
		
	  return $shopAddress;
	}
	
	public function MondialRelayPoints($country_code,$zipcode){
	   $select = $this->masterdb->select()
	  							 ->from(array('MRNPR'=>MR_NEARESTPR),array('*'))
								 ->joininner(array('MRRT'=>MR_ROUTING),"MRNPR.pr_number=MRRT.pr_number",array('*'))
								 ->joinleft(array('MRAN'=>MR_AGENCY),"SUBSTRING(MRRT.agency_code,1,4)=MRAN.agency_number",array('agency_name'))
								  ->where("MRNPR.zipcode='".$zipcode."' AND MRNPR.pr_country='".$country_code."' AND MRRT.pr_country_code='".$country_code."' AND MRRT.group_code!='' AND MRRT.shuttle_code!=''")
								 ->order("MRNPR.distance ASC")
								 ->limit(10);
								 //echo $select->__toString();die;
      $result = $this->masterdb->fetchAll($select);
	  $relaypoint = array();
	 $shopAddress = array();
	 foreach($result as $locations){
		$relaypoint['parcelShopId'] = $locations['pr_number'];
		$relaypoint['company'] = str_replace("'",'',$locations['pr_name']);
		$relaypoint['agency_name'] = $locations['agency_name'];
		$relaypoint['agency_code'] = $locations['agency_code'];
		$relaypoint['shuttle_code'] = $locations['shuttle_code'];
		$relaypoint['group_code'] = $locations['group_code'];
		$relaypoint['pr_number'] = $locations['pr_number'];
		$relaypoint['zipCode'] =$locations['pr_zipcode'];
		$relaypoint['city'] = str_replace("'",'',$locations['pr_city']);
		$relaypoint['street'] = str_replace("'",'',$locations['street']);
		$relaypoint['distance'] = $locations['distance']>0?($locations['distance']/1000):0;
	    //$latitudelangitude = $this->getlatiLogiOfpostalcode($locations['pr_zipcode'],$countrycode);
		$latitudelangitude = $this->getLatLong($country_code,$locations['pr_zipcode']);
		$relaypoint['latitude'] 	= $latitudelangitude['latitude'];
		$relaypoint['longitude'] 	= $latitudelangitude['longitude'];
		$relaypoint['forwarder_id'] = 27;
		$relaypoint['shop_type'] = 2;
		$relaypoint['icon']   = IMAGE_LINK.'/M.png';
		 $shopAddress[] = $relaypoint;
	 }
	 return $shopAddress;
	}

	public function DeburenParcelpoints($country_code,$zipcode){
	   try{	 
		 $latlong = $this->getLatLong($country_code,$zipcode);
		 $client = new SoapClient("https://mijnburen.deburen.nl/soap/pakketservice/service?wsdl", array('soap_version' => SOAP_1_2));
		//Auth Data
		$private_key = 'tapepH7DA5Up';
		$public_key = microtime();
		$site_id    = 7;
		$hash       = hash('sha256',$public_key.$site_id.$private_key);
		//Create Data
		$data = (object)array('auth'=>(object)array('site_id'=>'7','hash'=>$hash,'public_key'=>$public_key));
		$data->auth->site_id = $site_id;
		$data->auth->hash = $hash;
		$data->auth->public_key = $public_key;
		//print_r($data);die;
		// call Method
		$locations = array();
		$shopAddress = array();
		
		$responses = $client->getLocations($data);
		foreach($responses->locations as $response){	
		   $distance = $this->distance($latlong['latitude'], $latlong['longitude'], $response->lat, $response->lng);
		   if($distance<30){
				$locations['parcelShopId'] = $response->location_id;
				$locations['company'] = $response->title;
				$locations['street'] = $response->address;
				$locations['houseNo'] = $response->address_nr;
				$locations['state'] = '';
				$locations['zipCode'] = $response->zipcode;
				$locations['city'] = $response->city;
				$locations['phone'] = '';
				$locations['email'] = '';
				$locations['distance'] = $distance;
				$locations['longitude'] = $response->lng;
				$locations['latitude'] = $response->lat;
				$locations['forwarder_id'] = 42;
				$locations['shop_type'] = 3;
				$locations['icon']   = IMAGE_LINK.'/B.png';
				$shopAddress[] = $locations;
		   }
		}
		//print_r($shopAddress);die;
		return $shopAddress;
	  }catch(Exception $e){
	     return array();
	   }	
	}
	
	public function OwnParcelpoints($country_code,$zipcode){
	    $latlong = $this->getLatLong($country_code,$zipcode);
		$select = $this->_db->select()
	  							 ->from(array('PS'=>PARCELSHOP),array('*',"(((acos(sin((".$latlong['latitude']."*pi()/180)) * sin((`Latitude`*pi()/180))+cos((".$latlong['latitude']."*pi()/180)) * cos((`Latitude`*pi()/180)) * cos(((".$latlong['longitude']."- `Longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) AS distance"))
								 ->having("distance<15")
								 ->limit(5);
								 //echo $select->__toString();die;
        $results = $this->getAdapter()->fetchAll($select);
		$shopAddress = array();
		foreach($results as $result){	
			$locations['parcelShopId'] = $result['shop_id'];
			$locations['company'] = $result['shope_name'];
			$locations['street'] =  $result['street'];
			$locations['houseNo'] = $result['streetno'];
			$locations['state'] = '';
			$locations['zipCode'] = $result['postal_code'];
			$locations['city'] 	 = $result['city'];
			$locations['phone'] = '';
			$locations['email'] = '';
			$locations['distance'] = $result['distance'];
			$locations['longitude'] = $result['longitude'];
			$locations['latitude'] = $result['latitude'];
			$locations['forwarder_id'] = 22;
			$locations['shop_type'] = 4;
			$locations['icon']   = IMAGE_LINK.'/P.png';
			$shopAddress[] = $locations;
		}	
		return $shopAddress;
	}
	
	public function distance($lat1, $lon1, $lat2, $lon2, $unit='K') {
		  $theta = $lon1 - $lon2;
		  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		  $dist = acos($dist);
		  $dist = rad2deg($dist);
		  $miles = $dist * 60 * 1.1515;
		  $unit = strtoupper($unit);
		
		  if ($unit == "K") {
			return ($miles * 1.609344);
		  } else if ($unit == "N") {
			  return ($miles * 0.8684);
			} else {
				return $miles;
			  }
		}
		
		
	public function PerformaInvoice($data){
	    
	}
	
	public function shipmenthistoryExport($exportData){
	    $_nxtcol   = "\t";
	    $_nxtrow  = "\n";
	    $Header = '';
	   	if($this->Useconfig['level_id']==1){
		  $Header .= "\"Depot Name \"".$_nxtcol;
		}
		if($this->Useconfig['user_id']==407) {
			$Header .= "\"Customer Number \"".$_nxtcol;
		}
		$Header .= "\"Customer Name \"".$_nxtcol.
					"\"Street\"".$_nxtcol.
					"\"Address2 \"".$_nxtcol.
					"\"PostalCode \"".$_nxtcol.
					"\"City\"".$_nxtcol.
					"\"Country Name \"".$_nxtcol.
					"\"Receiver Name \"".$_nxtcol.
					"\"Street\"".$_nxtcol.
					"\"Street No. \"".$_nxtcol.
					"\"Address\"".$_nxtcol.
					"\"PostalCode. \"".$_nxtcol.
					"\"City \"".$_nxtcol.
					"\"Country \"".$_nxtcol.
					"\"Phone Number \"".$_nxtcol.
					"\"Weight \"".$_nxtcol.
					"\"Service\"".$_nxtcol.
					"\"Additional Service\"".$_nxtcol.
					"\"Reference Number \"".$_nxtcol.
					"\"Forwarder\"".$_nxtcol.
					"\"Barcode\"".$_nxtcol.
					"\"Local Barcode\"".$_nxtcol.
					"\"Create Date\"".$_nxtcol.
					"\"Created By \"".$_nxtcol;
		
		if($this->Useconfig['level_id']!=5){ $Header .= "\"Created IP \"".$_nxtcol; }		
		$Header .= "\"CheckIn Date \"".$_nxtcol;
		if($this->Useconfig['level_id']!=5){ $Header .= "\"Checked-In IP \"".$_nxtcol; }
		$Header .= "\"Error Code \"".$_nxtcol.
					"\"Error Name \"".$_nxtcol.
					"\"Error Date-Time \"".$_nxtcol.
					"\"Delivery Date \"".$_nxtcol.
					"\"Received By \"".$_nxtcol.
					"\"Current Status \"".$_nxtcol.
					"\"Status Date \"".$_nxtcol.
					"\"COD \"".$_nxtcol.
					"\"Event Date \"".$_nxtcol.
					"\"Event Action \"".$_nxtcol.
					"\"Event Reason \"".$_nxtcol;
	   	if($this->Useconfig['level_id']<7){ 
		$Header .= "\"Shipping Price \"".$_nxtcol; }
		$Header .= "\"SMS Date \"".$_nxtcol;
		$Header .= "\"Added IP \"".$_nxtcol;
		$Header .= "\n";
	   foreach($exportData['Records']  as $RecordExport){
	        $this->RecordData = $RecordExport;
			$forwarderDetail = $this->ForwarderDetail();
			if($this->Useconfig['level_id']==1){
			  $depotdetails = $this->getCustomerDetails($RecordExport['user_id']);
			  $Header .= "\"" . str_replace("\"", "\"\"", utf8_decode($depotdetails['parent_company'])) . "\"" . $_nxtcol;
			}
			if($this->Useconfig['user_id']==407) {
				$Header .= "\"" . str_replace( "\"", "\"\"",utf8_decode($RecordExport['customer_number'])) . "\"" . $_nxtcol;
			}
			$Header .= "\"" . str_replace("\"", "\"\"", utf8_decode($forwarderDetail['SenderAddress'][0])) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace("\"", "\"\"", utf8_decode($forwarderDetail['SenderAddress'][2])) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace("\"", "\"\"", '') . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace("\"", "\"\"", $forwarderDetail['SenderAddress'][4]) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace("\"", "\"\"", utf8_decode($forwarderDetail['SenderAddress'][3])) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace("\"", "\"\"", utf8_decode($forwarderDetail['SenderAddress'][6])) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",utf8_decode($RecordExport[RECEIVER])) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",utf8_decode($RecordExport['rec_street'])) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$RecordExport['rec_streetnr'] ) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",utf8_decode($RecordExport['rec_address']).' '.utf8_decode($RecordExport['rec_street2'])) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$RecordExport['rec_zipcode'] ) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",utf8_decode($RecordExport['rec_city'])) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",utf8_decode($RecordExport['country_name'])) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$RecordExport[PHONE] ) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$RecordExport[WEIGHT] ) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$RecordExport['service_name']) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",($RecordExport[ADDSERVICE_ID]>0)?$this->ServiceName($RecordExport[ADDSERVICE_ID]):'') . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",utf8_decode($RecordExport[REFERENCE])) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$RecordExport['forwarder_name']) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"","'".$RecordExport[TRACENR_BARCODE]."'" ) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$RecordExport['local_barcode']) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",($RecordExport['create_date'] == '0000-00-00 00:00:00' ) ? '' : date('F d-Y, h:i a',strtotime($RecordExport['create_date']))) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$RecordExport['company_name']) . "\"" . $_nxtcol;
			
			if($this->Useconfig['level_id']!=5){ $Header .= "\"" . str_replace( "\"", "\"\"",$RecordExport['create_ip']) . "\"" . $_nxtcol; }
			$Header .= "\"" . str_replace( "\"", "\"\"",($RecordExport['checkin_date'] == '0000-00-00 00:00:00' ) ? '' : date('F d-Y, h:i a',strtotime($RecordExport['checkin_date']))) . "\"" . $_nxtcol;
			if($this->Useconfig['level_id']!=5){ $Header .= "\"" . str_replace( "\"", "\"\"",$RecordExport['checkin_ip']) . "\"" . $_nxtcol; }
			$currentStatus = $this->ParcelCurrentStatus($RecordExport);
			$Header .= "\"" . str_replace( "\"", "\"\"",$currentStatus['status_code']) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$currentStatus['status_desc']) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$currentStatus['status_date']) . "\"".$_nxtcol;
			$deliveryInfo = '';
			$receivedPers = '';
			if($RecordExport['delivery_status'] == '1') {
				$deliveryInfo = ($RecordExport['delivery_date'] == '0000-00-00 00:00:00' ) ? '' : date('F d-Y, h:i a',strtotime($RecordExport['delivery_date']));
				$receivedPers = $RecordExport['received_by'];
			}
			$Header .= "\"" . str_replace( "\"", "\"\"",$deliveryInfo) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$receivedPers) . "\"" . $_nxtcol;
				$currentStatus = utf8_decode($currentStatus['status_desc']);
				$firstdigits = substr($receivedPers,0,2);
				$phi = strpos($receivedPers, "|");
				if($phi!== false){
					$lastdigit = substr(trim($receivedPers),-1,1);
					if(is_numeric($lastdigit)){
						$currentStatus = "Parcel delivered to neighbour";
					}
				}
				elseif($firstdigits=='PS'){
					$currentStatus= "Parcel delivered to Parcelstation";
				}
				elseif($firstdigits=='AZ'){
					$currentStatus= "Parcel delivered to neighbour";
				}
			$statusDate = (isset($currentStatus['status_date']) && $currentStatus['status_date'] != '0000-00-00 00:00:00') ? date('F d-Y, h:i a',strtotime($currentStatus['status_date'])) : '';
			$Header .= "\"" . str_replace( "\"", "\"\"",$currentStatus) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$statusDate) . "\"" . $_nxtcol;
			$cod = "";
			if($RecordExport['addservice_id'] == 7 || $RecordExport['addservice_id'] == 146 || $RecordExport['addservice_id'] == 141 || $RecordExport['addservice_id'] == 157) {
			    $codrec = $this->CodPriceDetail($RecordExport[BARCODE_ID]);
				$cod = (($codrec['status']=='Paid') && ($codrec['customer_invoice_number'] > 0)) ? "'".$codrec['customer_invoice_number']."'" : $codrec['status'];
				$cod = (!empty($cod)) ? trim($cod) : 'Unpaid';
			}
			$Header .= "\"" . str_replace( "\"", "\"\"",$cod) . "\"" . $_nxtcol;
			$parcelEvent = array();
			$eventDate = (isset($parcelEvent['date']) && $parcelEvent['date'] != '0000-00-00 00:00:00') ? date('F d-Y, h:i a',strtotime($parcelEvent['date'])) : '';
			$eventAction = (isset($parcelEvent['action_name'])) ? $parcelEvent['action_name'] : '';
			$eventReason = (isset($parcelEvent['eventInfo'])) ? $parcelEvent['eventInfo'] : '';
			$Header .= "\"" . str_replace( "\"", "\"\"",$eventDate) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$eventAction) . "\"" . $_nxtcol;
			$Header .= "\"" . str_replace( "\"", "\"\"",$eventReason) . "\"" . $_nxtcol;			
			if($this->Useconfig['level_id']<4){ 
			$Header .= "\"" . str_replace( "\"", "\"\"",str_replace('.',',',number_format($RecordExport['depot_price'],2))) . "\"" . $_nxtcol; 
			}
			if($this->Useconfig['level_id']==5 || $this->Useconfig['level_id']==4 || $this->Useconfig['level_id']==6){ 
			$Header .= "\"" . str_replace( "\"", "\"\"",str_replace('.',',',number_format($RecordExport['customer_price'],2))) . "\"" . $_nxtcol; 
			}
			$smsinfo = array();//$this->SMSInfo($RecordExport['barcode_id']);
			$Header .= "\"" . str_replace( "\"", "\"\"",(isset($smsinfo['sms_date']) && $smsinfo['sms_date'] != '0000-00-00 00:00:00')?date('F d-Y, h:i a',strtotime($smsinfo['sms_date'])):'') . "\"" . $_nxtcol; 
			$Header .= "\"" . str_replace( "\"", "\"\"",$RecordExport['create_ip']) . "\"" . $_nxtcol; 
			$Header .="\n";
	   }//print_r($Header );die;
	   commonfunction::ExportCsv($Header,'Shipment_History','xls');
	}
	
	public function ReferenceShipment($shipmentObj){
        global $objSession;
		$shipmentObj->getData['user_id'] =  Zend_Encript_Encription::decode($shipmentObj->getData['user_id']);
		$select = $this->_db->select()
							->from(array("BT"=>SHIPMENT_BARCODE),array("barcode"))
							->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array(''))
							->where("BD.rec_reference='".$shipmentObj->getData['rec_reference']."' AND BT.forwarder_id=22"); //echo $select; exit;
        $result= $this->getAdapter()->fetchRow($select);
		if(empty($result)){
		   $select = $this->_db->select()
									->from(array("AT"=>USERS_DETAILS),array("*"))
									->where("AT.user_id='".$shipmentObj->getData['user_id']."'"); //echo $select; exit;
            $usrdetail= $this->getAdapter()->fetchRow($select);
			$shipmentObj->getData[COUNTRY_ID] = $usrdetail[COUNTRY_ID];
			$shipmentObj->getData[FORWARDER_ID] = 22;
			$shipmentObj->getData['original_forwarder'] = 22;
			$shipmentObj->getData[WEIGHT] = 1;
			$shipmentObj->getData[SERVICE_ID] = 1;
			$shipmentObj->getData['quantity'] = 1;
			$shipmentObj->getData[RECEIVER] = $usrdetail['company_name'];
			$shipmentObj->getData[CONTACT] = trim($usrdetail['first_name'].' '.$usrdetail['middle_name'].' '.$usrdetail['last_name']);
			$shipmentObj->getData[STREET] = $usrdetail['address1'];
			$shipmentObj->getData[ADDRESS] = $usrdetail['address2'];
			$shipmentObj->getData[ZIPCODE] = $usrdetail['postalcode'];
			$shipmentObj->getData[CITY] = $usrdetail['city'];
			$shipmentObj->getData[PHONE] = $usrdetail['phoneno'];
			$shipmentObj->getData[EMAIL] = $usrdetail['email'];
			$shipmentObj->getData['email_notification'] = '1';
			$shipmentObj->getData['rec_reference'] = $shipmentObj->getData['rec_reference'];
			$shipmentObj->getData['shipment_type'] = 5;
			$shipmentObj->addShipment();
		}else{
		   $objSession->errorMsg = 'Reference Already Exist In Databse';
		}
	}
	
	public function portlist($data){
	    $select = $this->_db->select()
	   						->from(array('CP'=>COUNTRYPORT),array('*'))
							->where("CP.country_id='".$data['country_id']."'");
							//print_r($select->__toString());die;
	   return $this->getAdapter()->fetchAll($select);  
	}
	
	public function CodPriceDetail($barcode_id){
	   $select = $this->_db->select()
						->from(INVOICE_COD,array('*'))
						->where("barcode_id='".$barcode_id."'");
	   $result  = $this->getAdapter()->fetchRow($select);
	   return $result;					
	}		
}

