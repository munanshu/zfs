<?php

class Application_Model_Norsk extends Zend_Custom
{
   		public $jsonData = array();
		public function CreateNorskLabel($shipmentObj,$newbarcode=true){
	        $shipmentObj->RecordData['ship_day'] 	= date('D, M j');
		    $deliverydate = date('d-m-Y', strtotime('+ 4 weekday'));
			if($newbarcode){
			   $this->CreateBarcode($shipmentObj);
			}
			
		   $shipmentObj->RecordData['deliv_day'] 	= date('D, M j',strtotime($deliverydate));
 		   //$shipmentObj->RecordData['cncode'] 	= $objLabel->countryCode($objLabel->RecordData[COUNTRY_ID]);
		  // $shipmentObj->RecordData['country_name'] 	= $objLabel->countryName($objLabel->RecordData[COUNTRY_ID]);
		   //$objLabel->SetLogo();
		  /*$shipmentObj->RecordData[BARCODE] = $shipmentObj->RecordData['forwarder_detail']['barcode_prefix'].$shipmentObj->RecordData[TRACENR]; 
		  $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];*/
		  
	  }
	  
	  public function CreateBarcode($shipmentObj){ 
		        //$senderAdd = $objLabel->RecordData['SOURCE_ADDRESS']['SenderAddress'];
				//$customeradd = $objLabel->userAddress($objLabel->RecordData[ADMIN_ID]);
				
				//date_default_timezone_set('Europe/Amsterdam');
				$timestamp = date('H:i',time());
				$this->jsonData['Pieces'][] 	= array('Depth'=>10,'Height'=>10,'Width'=>10,'Weight'=>$shipmentObj->RecordData[WEIGHT],'NumberOfPieces'=>1);
				$this->jsonData['ReadyByDate'] 	= date('Y-m-d').'T17:19:37.510Z';
				$this->jsonData['Hawb'] 		= ($shipmentObj->RecordData['goods_id']!='')?$shipmentObj->RecordData['goods_id']:'Documents';
				$this->jsonData['Description'] 	= ($shipmentObj->RecordData['goods_description']!='')?substr($shipmentObj->RecordData['goods_description'],0,50):'Documents';
				$this->jsonData['Value']		= ($shipmentObj->RecordData['shipment_worth']!='' && $shipmentObj->RecordData['shipment_worth']>0)?round($shipmentObj->RecordData['shipment_worth']):"0.00001";
				$this->jsonData['Currency'] = 'USD';
				$this->jsonData['NonDox'] 	= true;
				$this->jsonData['DDP'] 	= true;
				$this->jsonData['Pallet'] = false;
				$this->jsonData['Requestor'] 		= array('Name'=>'Parcel.nl','PhoneNumber'=>'0684587459');
				$url_first = "https://maps.googleapis.com/maps/api/geocode/json?address=".commonfunction::StringReplace(array(' ','-','/'),'+',$shipmentObj->RecordData[ZIPCODE].' '.$shipmentObj->RecordData[CITY])."+".$shipmentObj->RecordData['rec_cncode']."+View&key=AIzaSyAxLgyM6pPLkMF8-jomEdxefVACkxf2nKU";
				
		      $datafirst = @file_get_contents($url_first);
		      $datafirstdecoded = json_decode($datafirst);
			  $divisionname = 'New York';
			  $divisioncode = 'NY';
			  $found = false;
			  if(!empty($datafirstdecoded)){ 
				foreach($datafirstdecoded->results as $firstdatas){
					 foreach( $firstdatas->address_components as  $firstdata){
						if(isset($firstdata->types[0]) && isset($firstdata->types[1]) && $firstdata->types[0]=='administrative_area_level_1' && $firstdata->types[1]=='political'){
						   $divisionname = $firstdata->long_name;
						   $divisioncode =  $firstdata->short_name;
						   $found = true;
						   break;
						}
					  }
					  if($found){
					    break;
					  }
				 }	  
			 }	  	
				$contact_name = ($shipmentObj->RecordData[CONTACT]!='')?$shipmentObj->RecordData[CONTACT]:$shipmentObj->RecordData[RECEIVER];
				$receiver = ($shipmentObj->RecordData[CONTACT]=='')?$shipmentObj->RecordData[RECEIVER]:$shipmentObj->RecordData[CONTACT];
				$address1 = $shipmentObj->RecordData[STREET].' '.$shipmentObj->RecordData[STREETNR];
				
				$this->jsonData['Consignee'] 		= array('ContactName'=>substr($contact_name,0,35),'Company'=>substr($receiver,0,35),'Address1'=>substr($address1,0,35),'Address2'=>substr(substr($address1,35).' '.$shipmentObj->RecordData[ADDRESS],0,35),'Address3'=>substr($shipmentObj->RecordData[STREET2],0,35),'City'=>substr($shipmentObj->RecordData[CITY],0,35),'Division'=>$divisionname,'Email'=>$shipmentObj->RecordData[EMAIL],'DivisionCode'=>$divisioncode,'PhoneNumber'=>$shipmentObj->RecordData[PHONE],'Fax'=>"",'Zipcode'=>substr(trim($shipmentObj->RecordData[ZIPCODE]),0,5),'MobileNumber'=>"",'CountryCode'=>$shipmentObj->RecordData['rec_cncode']);
				
				$this->jsonData['Service'] 		= array('Code'=>'USA','Enhancements'=>array(array('Code'=>'USA')),'Supplier'=>"",'Route'=>"");
				$this->jsonData['Picking'] 		=  array('Instructions'=>'This Booking is from Parcel.nl');
				$this->jsonData['LabelFormat'] 		=  "pdf";
				$this->getCreateLabelResponse($shipmentObj);
    	}
		
	  public function getCreateLabelResponse($shipmentObj){  
			date_default_timezone_set('Etc/GMT');
			$requestBody = json_encode($this->jsonData);
			$NorskAccessKeyId = "MI2UTF3KIBPDHEAB";
			$YourSecretAccessKeyID = "ZGXJ2OHM6V2GQDZOOMBXSWZCQZ3JNLJOQAUUB3SV73YMNG3E";
			$method = "POST";
			$contentMd5 = strtolower(md5($requestBody, false));
			$contentType = "application/json";
			$date =	gmdate('D, d M Y H:i:s').' GMT';
			$resource = "/api/Shipment";
			
			$StringToSign = $method."\n".$contentMd5."\n".$contentType."\n".$date."\n".$resource;
			$Signature = base64_encode(hash_hmac("sha1", utf8_encode($StringToSign), $YourSecretAccessKeyID, true));
			$Authorization = $NorskAccessKeyId.':'.$Signature;
			$host = "http://api.norsk-global.com";
			$url = $host.$resource;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: '.$contentType,
				'Content-Type: '.$contentType,
				'Authorization: '.$Authorization,
				'Date: '.$date));
			
			curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
			
			if (curl_errno($ch)) 
			{  
				$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$shipmentObj->RecordData[SHIPMENT_ID].' Error in response');
				if($shipmentObj->RecordData['API']){ 
					$this->UpdateForwarder($shipmentObj,'Error in response');
					$shipmentObj->RecordData[FORWARDER_ID]	= 22;
					$shipmentObj->RecordData['forwarder_detail'] = $shipmentObj->ForwarderDetail();
					$shipmentObj->GenerateBarcodeData(true);
					return true;
				}else{
					echo "F^There is some Error With XML Data. Please Try Again!";exit;
				}
			} 
			else 
			{
				$response = curl_exec($ch);
			}
			$responseData = json_decode($response);
			$shipmentObj->RecordData[BARCODE] 		= isset($responseData->Items[0]->ScanBarcode)?$responseData->Items[0]->ScanBarcode:'';
			$shipmentObj->RecordData[TRACENR_BARCODE]  = $shipmentObj->RecordData[BARCODE];
			$shipmentObj->RecordData[REROUTE_BARCODE] = isset($responseData->Barcode)?$responseData->Barcode:'';
			$shipmentObj->RecordData[TRACENR] 		= isset($responseData->NorskBarcode)?$responseData->NorskBarcode:'';
			$shipmentObj->RecordData['local_barcode']= isset($responseData->Items[0]->NorskBarcode)?$responseData->Items[0]->NorskBarcode:'';
			
			if($shipmentObj->RecordData[BARCODE]==''){
				$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$shipmentObj->RecordData[SHIPMENT_ID].' Barcode Blank');
				if($shipmentObj->RecordData['API']){ 
					$this->UpdateForwarderNorsk($shipmentObj,'Barcode Blank');
					$shipmentObj->RecordData[FORWARDER_ID]	= 22;
					$shipmentObj->RecordData['forwarder_detail'] = $shipmentObj->ForwarderDetail();
					$shipmentObj->GenerateBarcodeData(true);
					return true;
				}else{
					echo "F^There is some Error With XML Data. Please Try Again!";exit;
				}
			}
		  return true; 
		}
		
	 public function UpdateForwarderNorsk($shipmentObj,$message){
	    $this->_db->update(SHIPMENT,array('forwarder_id'=>22,'wrong_parcel'=>'1'),"shipment_id=".$shipmentObj->RecordData[SHIPMENT_ID]."");
	 }
	  

}

