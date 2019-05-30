<?php
class Application_Model_YodelLabel extends Zend_custom{
	  public $ForwarderDetail = array();
	  public $xmlCreate = array();
	  public function CreateYodelLabel($shipmentObj,$newbarcode=true){
	  		// echo "<pre>";print_r($shipmentObj);die;
	  	
			$this->ForwarderDetail = $shipmentObj->RecordData['forwarder_detail'];
			if($newbarcode){
			  switch($shipmentObj->RecordData[FORWARDER_ID]){
				case 29:
					$this->createXMLYodel($shipmentObj);
					$this->getCreateLabelResponse($shipmentObj);	
			    break;
				case 40:
					$this->createXMLHermesuk($shipmentObj);
					$this->getLabelResponseHermesh($shipmentObj);	
			    break;
				case 52:
					$this->createXMLRposten($shipmentObj);
					$this->getLabelResponseRposten($shipmentObj);	
			    break;
			}
		  }else{
			  switch($shipmentObj->RecordData[FORWARDER_ID]){
				case 29:
					$this->MakeLabeldata($shipmentObj);
			    break;
			}
		  }	
		  
			return true;
    	
	  }
	  
	  public function getCreateLabelResponse($shipmentObj){

	  	$xml_data  = file_get_contents(PRINT_OPEN_LABEL.$this->ForwarderDetail['forwarder_name'].'/xml/CreateShipment.xml');
			// $authurl = "http://api.parcelhub.net/api/0.4/token";
			$authurl = "http://apitest.phservice.co.uk/0.4/token";
			// $URL = "http://api.parcelhub.net/api/0.4/Shipment?RequestedLabelSize=6&RequestedLabelFormat=PDF";
			$URL = "http://apitest.phservice.co.uk/0.4/Shipment?RequestedLabelSize=6&RequestedLabelFormat=PDF";
			// $authenticate = $this->CurlResponse($authurl,array('Content-Type: text/xml'),"grant_type=password&username=PNL001&password=pnl2894");
			$authenticate = $this->CurlResponse($authurl,array('Content-Type: text/xml'),"grant_type=password&username=TEST001&password=TEST001");
			
			$auth = json_decode($authenticate); 

			$content = $this->CurlResponse($URL,array('Content-Type: text/xml','Authorization: bearer '.$auth->access_token),$xml_data);

			 

			// echo "<pre>"; print_r($auth);die;




		   // $xml_data  = file_get_contents(PRINT_OPEN_LABEL.$this->ForwarderDetail['forwarder_name'].'/xml/CreateShipment.xml');
		   // $content = $this->CurlResponse('http://api.parcelhub.net/api/0.3/CreateShipment',array('Content-Type: text/xml'),$xml_data);
		try{	
			$dom = new DOMDocument;
			$dom->loadXML($content);			
		    $xml = simplexml_load_string($content);
			$json_encoded = json_encode($xml);
			$json = json_decode($json_encoded);
			
		if(!empty($dom->getElementsByTagName('Description')->item(0)->nodeValue)){
		       $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$shipmentObj->RecordData[SHIPMENT_ID].' '.$dom->getElementsByTagName('Description')->item(0)->nodeValue);
		       
				if($shipmentObj->RecordData['API']){ 
					$this->UpdateForwarder($shipmentObj,$dom->getElementsByTagName('Description')->item(0)->nodeValue);
					$shipmentObj->RecordData[FORWARDER_ID]	= 22;
					$shipmentObj->RecordData['forwarder_detail'] = $shipmentObj->ForwarderDetail();
					$shipmentObj->GenerateBarcodeData(true);
					return true;
					return true;
				}else{
					echo "F^There is some Error With XML Data. Please Try Again!";exit;
				}
			} 
		}catch(Exception $e){

			 
		      $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$shipmentObj->RecordData[SHIPMENT_ID].' '.$dom->getElementsByTagName('Description')->item(0)->nodeValue);
		}	
		$ReturnData = array();
		$findarr = array('(',')',' ');
		$replace = array('','','');

		// $labelmodel = $json->Packages->Package->ShippingInfo->Labels->Label->RawLabelData->YodelLabelModel;

		$labelmodel = $json->Packages->Package->PackageShippingInfo->Labels->Label->RawLabelData->YodelLabelModel;

			// echo "<pre>";print_r($json);die; 

		// $shipmentObj->RecordData[BARCODE] 		= str_replace($findarr,$replace,$json->Packages->Package->ShippingInfo->TrackingNumber);	

		$shipmentObj->RecordData[BARCODE] 		= str_replace($findarr,$replace,$json->Packages->Package->PackageShippingInfo->CourierTrackingNumber);

		// $shipmentObj->RecordData['BARCODE_READABLE'] 		= $json->Packages->Package->ShippingInfo->TrackingNumber;

		$shipmentObj->RecordData['BARCODE_READABLE'] 		= $json->Packages->Package->PackageShippingInfo->CourierTrackingNumber;
		$shipmentObj->RecordData[REROUTE_BARCODE] = $labelmodel->RoutingBarcode;
		$shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
		// $shipmentObj->RecordData[TRACENR] 		= $json->ShippingInfo->ShipmentServiceProviderId;
		$shipmentObj->RecordData[TRACENR] 		= $json->ServiceInfo->ServiceProviderId;
		 
			// echo "<pre>";print_r($shipmentObj);die;

		$pdfdata = array('ProductName'=>$labelmodel->ProductName,'ServiceDescription'=>$labelmodel->ServiceDescription,'ReturnCode'=>$labelmodel->TrackingNumbers->JDNumbers->ReturnCode,'ServiceCentreLocationName'=>$labelmodel->ServiceCentreLocationName,'HubLocationName'=>$labelmodel->HubLocationName,'MeterNumber'=>$labelmodel->MeterNumber);
		 $shipmentObj->RecordData['pdf_data'] =  $pdfdata;
		
		 if($shipmentObj->RecordData[BARCODE]==''){
		 
		 }
		$this->storeLabelData($shipmentObj,$json_encoded);
		 return true; 
		}
	  
	  public function CurlResponse($URL,$header=array(),$xml_data){
 			try{
				$ch = curl_init($URL);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$content = curl_exec($ch);
				if($this->Useconfig['user_id']==412){
				   //print_r($content);die;
				}
				curl_close($ch);
			}catch(Exception $e){ }	
			return $content;
	  }
	  
	  public function storeLabelData($shipmentObj,$pdfdata){
		$select = $this->_db->select()
									->from(YODEL_PDF,array('*'))
									->where("barcode='".$shipmentObj->RecordData[BARCODE]."'");
									//print_r($select->__toString());die;
		$record = $this->getAdapter()->fetchRow($select);
		if(empty($record)){
		   $this->_db->insert(YODEL_PDF,array_filter(array('barcode'=>$shipmentObj->RecordData[BARCODE],'pdf_contant'=>$pdfdata)));
		}else{
		   $this->_db->update(YODEL_PDF,array('pdf_contant'=>$pdfdata),"barcode='".$shipmentObj->RecordData[BARCODE]."'");
		}
		return; 
	  }		
		
		
	  public function createXMLYodel($shipmentObj){ 

	  		// echo "<pre>";print_r($shipmentObj); die;
				$dom = new DOMDocument ( '1.0', 'UTF-8' );
				$root = $dom->createElement ('Shipment');
				// $root->setAttribute ( 'xmlns', 'http://api.parcelhub.net/schemas/api/parcelhub-api-v0.4.xsd');
				$root->setAttribute ( 'xmlns', 'http://api.parcelhub.net/schemas/api/parcelhub-api-v0.4.xsd');
				$dom->appendChild ($root);
				//$this->xmlCreate['AuthenticationDetails']['Username'] 			= 'TEST001';
//				$this->xmlCreate['AuthenticationDetails']['Password'] 			= 'TEST001'; 
				
				// $this->xmlCreate['AuthenticationDetails']['Username'] 			= 'PNL001';
				// $this->xmlCreate['AuthenticationDetails']['Password'] 			= 'pnl2894';
				
				// $this->xmlCreate['RequestedLabelFormat']      		  			= 'PDF';
				// $this->xmlCreate['RequestedLabelSize']				  			= '6';
				
				$senderAdd = $this->ForwarderDetail['SenderAddress'];
				$customeradd = $this->getCustomerDetails($shipmentObj->RecordData[ADMIN_ID]);
				
				$this->xmlCreate['CollectionAddress']['ContactName'] = ($senderAdd[1]!='')?substr(trim($senderAdd[1]),0,35):'Parcel.nl';
				$this->xmlCreate['CollectionAddress']['CompanyName'] = ($senderAdd[0]!='')?substr(trim($senderAdd[0]),0,35):'Parcel.nl';
				$this->xmlCreate['CollectionAddress']['Email'] 		 = 'klantenservice@parcel.nl';
				$this->xmlCreate['CollectionAddress']['Phone'] 		 = '31748800700';
				$this->xmlCreate['CollectionAddress']['City'] 		 = 'Nottingham';
				$this->xmlCreate['CollectionAddress']['Address1']	 = 'Little Tennis Street Unit 1';
				// $this->xmlCreate['CollectionAddress']['Address2']	 = 'Little Tennis Street Unit 1';
				$this->xmlCreate['CollectionAddress']['Postcode'] 	 = 'NG2 4EU';
				$this->xmlCreate['CollectionAddress']['Country'] 	 = 'GB';
				$this->xmlCreate['CollectionAddress']['AddressType'] = 'Business';
				
				$this->xmlCreate['DeliveryAddress']['ContactName']   = !empty($shipmentObj->RecordData[CONTACT]) ? substr($shipmentObj->RecordData[CONTACT],0,35) : substr($shipmentObj->RecordData[RECEIVER],0,35);
				$this->xmlCreate['DeliveryAddress']['CompanyName']   = substr($shipmentObj->RecordData[RECEIVER],0,35);
				$this->xmlCreate['DeliveryAddress']['Email'] 		 = !empty($shipmentObj->RecordData[EMAIL])?$shipmentObj->RecordData[EMAIL]:$customeradd['email'];
				$this->xmlCreate['DeliveryAddress']['Phone'] 		 = preg_replace('/\s+/', '',!empty($shipmentObj->RecordData[PHONE])?$shipmentObj->RecordData[PHONE]:'31748800700');
				$this->xmlCreate['DeliveryAddress']['City'] 		 = $shipmentObj->RecordData[CITY];
				// $this->xmlCreate['DeliveryAddress']['Area'] 		 = substr($shipmentObj->RecordData[STREET],0,32);
				$address1 = trim($shipmentObj->RecordData[STREETNR].' '.$shipmentObj->RecordData[ADDRESS].' '.$shipmentObj->RecordData[STREET2]);
				 
				$this->xmlCreate['DeliveryAddress']['Address1']	 	 = substr((trim($address1)!='')?trim($address1):$shipmentObj->RecordData[STREET],0,35);
				$this->xmlCreate['DeliveryAddress']['Address2']	 	 = substr((trim($address1)!='')?trim($address1):$shipmentObj->RecordData[STREET],0,35);
				
				$postcodestring = preg_replace('/\s+/', '',preg_replace('/[^A-Za-z0-9 ]/', '',$shipmentObj->RecordData[ZIPCODE]));
				
				$this->xmlCreate['DeliveryAddress']['Postcode'] 	 = substr($postcodestring,0,-3).' '.substr($postcodestring,-3);
				$this->xmlCreate['DeliveryAddress']['Country'] 	 	 = $shipmentObj->RecordData['rec_cncode'];
				 if($shipmentObj->RecordData[SERVICE_ID]==1){
				  $this->xmlCreate['DeliveryAddress']['AddressType'] = 'Residential';
				 }else{
				  $this->xmlCreate['DeliveryAddress']['AddressType'] = 'Business';
			   }
				
				$this->xmlCreate['Reference1'] = $shipmentObj->RecordData[REFERENCE];
				$this->xmlCreate['Reference2'] = $shipmentObj->RecordData[REFERENCE];
				// <Enhancements />
  // <ShipmentTags />
				$this->xmlCreate['Enhancements'] = '' ;
				$this->xmlCreate['ShipmentTags'] = '';
				$this->xmlCreate['Packages']['Package']['Dimensions']['Length'] = 0;
				$this->xmlCreate['Packages']['Package']['Dimensions']['Width'] = 0;
				$this->xmlCreate['Packages']['Package']['Dimensions']['Height'] = 0;
				$this->xmlCreate['Packages']['Package']['Weight'] = $shipmentObj->RecordData[WEIGHT];
				
				$this->xmlCreate['Packages']['Package']['Value'] = 0;
				// $this->xmlCreate['Packages']['Package']['ValueCurrency'] = 'GBP';
				$this->xmlCreate['Packages']['Package']['Contents'] = $shipmentObj->RecordData[REFERENCE];
				
				if($shipmentObj->RecordData[SERVICE_ID]==7 || $shipmentObj->RecordData[SERVICE_ID]==146){
				   $this->xmlCreate['Packages']['Package']['Value'] = $shipmentObj->RecordData['cod_price'];
				   // $this->xmlCreate['Packages']['Package']['ValueCurrency'] = $shipmentObj->RecordData['currency'];
				   $this->xmlCreate['Packages']['Package']['Contents'] = $shipmentObj->RecordData['goods_id'];
				}
				
				// $this->xmlCreate['TypeOfShipment'] = 'Delivery';
				$this->xmlCreate['ContentsDescription'] = $shipmentObj->RecordData[REFERENCE];
				
			 if($shipmentObj->RecordData['shipment_worth']>0){
				$this->xmlCreate['CustomsInfo']['DescriptionOfGoods'] = ($shipmentObj->RecordData['goods_description']!='')?$shipmentObj->RecordData['goods_description']:'Commercial';
				$this->xmlCreate['CustomsInfo']['DeclaredValue'] = ($shipmentObj->RecordData['shipment_worth']>0)?round($shipmentObj->RecordData['shipment_worth']):0;
				$this->xmlCreate['CustomsInfo']['ExportReason'] = ($shipmentObj->RecordData['goods_id']!='')?$shipmentObj->RecordData['goods_id']:'Documents';
				$this->xmlCreate['CustomsInfo']['IsDutiable'] = 'false';
				$this->xmlCreate['CustomsInfo']['TermsOfTrade'] = 'DDU';
				$this->xmlCreate['CustomsInfo']['DeclaredCurrency'] = 'GBP'; 
			}	
			$first2digit 		= 	strtoupper(substr($postcodestring,0,2));
			$first3digit 		= 	strtoupper(substr($postcodestring,0,2));
			$first4digit 		= 	strtoupper(substr($postcodestring,0,2));
			$thirdfouthdigit 	= 	strtoupper(substr($postcodestring,0,2));
			$thirddigit 		= 	strtoupper(substr($postcodestring,0,2));
				
			 if($first2digit=='JE' || $first2digit=='GY'){	
			        $this->xmlCreate['ServiceInfo']['ServiceId'] = 56;
					$this->xmlCreate['ServiceInfo']['ServiceProviderId'] = 10;
					$this->xmlCreate['ServiceInfo']['ServiceCustomerUID'] = 1;
			  }elseif($first2digit=='BT'){	
			        $this->xmlCreate['ServiceInfo']['ServiceId'] = 164;
					$this->xmlCreate['ServiceInfo']['ServiceProviderId'] = 10;
					$this->xmlCreate['ServiceInfo']['ServiceCustomerUID'] = 6426;
			  }elseif($first2digit=='HS' || $first2digit=='IM' || $first3digit=='IOM' || $first4digit=='FK18' || $first4digit=='FK19' || $first2digit=='IV' || $first4digit=='KA27' || $first4digit=='KA28' || $first2digit=='KW' || ($first2digit=='PA' && $thirdfouthdigit>=20) || ($first2digit=='PH' && $thirdfouthdigit>=17) || ($first2digit=='PO' && $thirdfouthdigit>=30) || ($first2digit=='TR' && $thirdfouthdigit>=21) || $first2digit=='ZE'){	
			        $this->xmlCreate['ServiceInfo']['ServiceId'] = 224;
					$this->xmlCreate['ServiceInfo']['ServiceProviderId'] = 10;
					$this->xmlCreate['ServiceInfo']['ServiceCustomerUID'] = 6455;
			  }elseif($shipmentObj->RecordData['service_id']==1){	
					$this->xmlCreate['ServiceInfo']['ServiceId'] = 56;
					$this->xmlCreate['ServiceInfo']['ServiceProviderId'] = 10;
					$this->xmlCreate['ServiceInfo']['ServiceCustomerUID'] = 1;
			  }elseif($shipmentObj->RecordData['service_id']==2){
					$this->xmlCreate['ServiceInfo']['ServiceId'] = 251;
					$this->xmlCreate['ServiceInfo']['ServiceProviderId'] = 10;
					$this->xmlCreate['ServiceInfo']['ServiceCustomerUID'] = 6396;
			  }else{
			       $this->xmlCreate['ServiceInfo']['ServiceId'] = 251;
				   $this->xmlCreate['ServiceInfo']['ServiceProviderId'] = 10;
				   $this->xmlCreate['ServiceInfo']['ServiceCustomerUID'] = 6396;
			      
			  }	
				
				
				
				$this->createstructure($this->xmlCreate,$dom,$root);
				
				$saveData = $dom->save(PRINT_SAVE_LABEL.$this->ForwarderDetail['forwarder_name'].'/xml/CreateShipment.xml');
				// print_r($saveData);die;
				return PRINT_OPEN_LABEL.$this->ForwarderDetail['forwarder_name'].'/xml/CreateShipment.xml';	
    	}
	/**
	*Craete xml for create shipment
	*Function : createXML()
	*function create XML for create shipment at yodel
	**/
	 public function createXMLHermesuk($shipmentObj){ 

	 			// echo "<pre>"; print_r($shipmentObj);die;
				$dom = new DOMDocument ( '1.0', 'UTF-8' );
				$root = $dom->createElement ('Shipment');
				$root->setAttribute ( 'xmlns', 'http://api.parcelhub.net/schemas/api/parcelhub-api-v0.4.xsd');
				$dom->appendChild ($root);
				
				$senderAdd = $this->ForwarderDetail['SenderAddress'];
				$customeradd = $this->getCustomerDetails($shipmentObj->RecordData[ADMIN_ID]);
				
				$this->xmlCreate['CollectionAddress']['ContactName'] = ($senderAdd[1]!='')?substr(trim($senderAdd[1]),0,35):'Parcel.nl';
				$this->xmlCreate['CollectionAddress']['CompanyName'] = ($senderAdd[0]!='')?substr(trim($senderAdd[0]),0,35):'Parcel.nl';
				$this->xmlCreate['CollectionAddress']['Email'] 		 = 'klantenservice@parcel.nl';
				$this->xmlCreate['CollectionAddress']['Phone'] 		 = '31748800700';
				$this->xmlCreate['CollectionAddress']['City'] 		 = 'Nottingham';
				$this->xmlCreate['CollectionAddress']['Address1']	 = 'Little Tennis Street Unit 1';
				$this->xmlCreate['CollectionAddress']['Postcode'] 	 = 'NG2 4EU';
				$this->xmlCreate['CollectionAddress']['Country'] 	 = 'GB';
				$this->xmlCreate['CollectionAddress']['AddressType'] = 'Business';
				
				$this->xmlCreate['DeliveryAddress']['ContactName']   = !empty($shipmentObj->RecordData[CONTACT]) ? substr($shipmentObj->RecordData[CONTACT],0,35) : substr($shipmentObj->RecordData[RECEIVER],0,35);
				$this->xmlCreate['DeliveryAddress']['CompanyName']   = substr($shipmentObj->RecordData[RECEIVER],0,35);
				$this->xmlCreate['DeliveryAddress']['Email'] 		 = !empty($shipmentObj->RecordData[EMAIL])?$shipmentObj->RecordData[EMAIL]:$customeradd['email'];
				$this->xmlCreate['DeliveryAddress']['Phone'] 		 = preg_replace('/\s+/', '',!empty($shipmentObj->RecordData[PHONE])?$shipmentObj->RecordData[PHONE]:'31748800700');
				
				$address1 = trim($shipmentObj->RecordData[ADDRESS].' '.$shipmentObj->RecordData[STREET2]);
				$this->xmlCreate['DeliveryAddress']['Address1']	 	 = substr($shipmentObj->RecordData[STREET].' '.$shipmentObj->RecordData[STREETNR],0,32);
				
				if(trim($address1)!=''){
				 $this->xmlCreate['DeliveryAddress']['Address2']	 	 = substr(trim($address1),0,30);
				}
				$this->xmlCreate['DeliveryAddress']['City'] 		 = $shipmentObj->RecordData[CITY];
				
				$postcodestring = preg_replace('/\s+/', '',preg_replace('/[^A-Za-z0-9 ]/', '',$shipmentObj->RecordData[ZIPCODE]));
				$this->xmlCreate['DeliveryAddress']['Postcode'] 	 = substr($postcodestring,0,-3).' '.substr($postcodestring,-3);
				$this->xmlCreate['DeliveryAddress']['Country'] 	 	 = $shipmentObj->RecordData['rec_cncode'];
				
				if($shipmentObj->RecordData[SERVICE_ID]==1){
				  $this->xmlCreate['DeliveryAddress']['AddressType'] = 'Residential';
				 }else{
				  $this->xmlCreate['DeliveryAddress']['AddressType'] = 'Business';
			   }
			    $this->xmlCreate['Reference1'] = substr($shipmentObj->RecordData[REFERENCE],0,20);
				$this->xmlCreate['Reference2'] = substr($shipmentObj->RecordData[REFERENCE],0,20);
				
				$this->xmlCreate['ContentsDescription'] = ($shipmentObj->RecordData['goods_description']!='')?$shipmentObj->RecordData['goods_description']:$shipmentObj->RecordData[REFERENCE];
				
				$this->xmlCreate['Packages']['PackageType'] = 'Parcel';
				$this->xmlCreate['Packages']['Package']['Dimensions']['Length'] = 0;
				$this->xmlCreate['Packages']['Package']['Dimensions']['Width'] = 0;
				$this->xmlCreate['Packages']['Package']['Dimensions']['Height'] = 0;
				$this->xmlCreate['Packages']['Package']['Weight'] = $shipmentObj->RecordData[WEIGHT];
				
				$this->xmlCreate['Packages']['Package']['Value'] = ($shipmentObj->RecordData['shipment_worth']>0)?round($shipmentObj->RecordData['shipment_worth']):0;
				$this->xmlCreate['Packages']['Package']['Contents'] = ($shipmentObj->RecordData['goods_id']!='')?$shipmentObj->RecordData['goods_id']:'Documents';
				
				$this->xmlCreate['ServiceInfo']['ServiceId'] = 5011;
				$this->xmlCreate['ServiceInfo']['ServiceProviderId'] = 9;
				$this->xmlCreate['ServiceInfo']['ServiceCustomerUID'] = 2;
				   
				$this->createXmlstructure($this->xmlCreate,$dom,$root);
				
				$saveData = $dom->save(PRINT_SAVE_LABEL.$this->ForwarderDetail['forwarder_name'].'/xml/CreateShipment.xml');
				return PRINT_OPEN_LABEL.$this->ForwarderDetail['forwarder_name'].'/xml/CreateShipment.xml';		
    	}
		
	 
	 public function getLabelResponseHermesh($shipmentObj){
		    try{
			$xml_data  = file_get_contents(PRINT_OPEN_LABEL.$this->ForwarderDetail['forwarder_name'].'/xml/CreateShipment.xml');
			$authurl = "http://api.parcelhub.net/api/0.4/token";
			$URL = "http://api.parcelhub.net/api/0.4/Shipment?RequestedLabelSize=6&RequestedLabelFormat=PDF";
			$authenticate = $this->CurlResponse($authurl,array('Content-Type: text/xml'),"grant_type=password&username=PNL001&password=pnl2894");
			$auth = json_decode($authenticate); 
			
			$labelcontent = $this->CurlResponse($URL,array('Content-Type: text/xml','Authorization: bearer '.$auth->access_token),$xml_data);
			$xml = simplexml_load_string($labelcontent);
			$json_encoded = json_encode($xml);
			$json = json_decode($json_encoded);
		 if(isset($json->Message)){
		        $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$shipmentObj->RecordData[SHIPMENT_ID].' '.$json->Message);
				if($shipmentObj->RecordData['API']){ 
					$this->UpdateForwarder($shipmentObj,$json->Message);
					$shipmentObj->RecordData[FORWARDER_ID]	= 22;
					$shipmentObj->RecordData['forwarder_detail'] = $shipmentObj->ForwarderDetail();
					$shipmentObj->GenerateBarcodeData(true);
					return true;
				}else{
					echo "F^There is some Error With XML Data. Please Try Again!";exit;
				}
			}
		 }catch(Exception $e){
		     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$shipmentObj->RecordData[SHIPMENT_ID].' '.$e->getMessage());
		 }	
		$ReturnData = array();
		$findarr = array('(',')',' ');
		$replace = array('','','');
		
		$labelmodel = $json->Packages->Package->PackageShippingInfo->Labels->Label->RawLabelData->RawLabelDataModel->Packages->RawLabelPiece;
		//$labelmodel1 = $json->Packages->Package->PackageShippingInfo->Labels->Label->RawLabelData->HubEuropeRawLabelModel;
		
		$shipmentObj->RecordData[BARCODE] 		= str_replace($findarr,$replace,$labelmodel->Courier2Barcode1);
		$shipmentObj->RecordData[REROUTE_BARCODE] = $json->ShippingInfo->ParcelhubTrackingNumber;
		$shipmentObj->RecordData[TRACENR] 		= $shipmentObj->RecordData[BARCODE];
		$shipmentObj->RecordData[TRACENR_BARCODE] 		= $shipmentObj->RecordData[BARCODE];
		$shipmentObj->RecordData['ParcelhubShipmentId']    = $json->ParcelhubShipmentId;
		
		$labeldata = array('SortLevel1'=>$labelmodel->Courier2SortLevel1,'SortLevel2'=>(!is_object($labelmodel->Courier2SortLevel2)?$labelmodel->Courier2SortLevel2:''),'SortLevel3'=>(!is_object($labelmodel->Courier2SortLevel3)?$labelmodel->Courier2SortLevel3:''),'SortLevel4'=>(!is_object($labelmodel->Courier2SortLevel4)?$labelmodel->Courier2SortLevel4:''),'SortLevel5'=>(!is_object($labelmodel->Courier2SortLevel5)?$labelmodel->Courier2SortLevel5:''),'ReturnCode'=>(!is_object($labelmodel->ReturnCode)?$labelmodel->ReturnCode:''),'BarcodeHumanReadable'=>$labelmodel->Courier2Barcode1HumanReadable,'Entity1Key'=>$labelmodel->Entity1Description,'Entity2Key'=>$labelmodel->Entity2Description,'Entity1Value'=>$labelmodel->Entity1Value,'Entity2Value'=>$labelmodel->Entity2Value,'ClientName'=>(!is_object ($labelmodel->Courier1LabelCourierID))?$labelmodel->Courier1LabelCourierID:'Hermes','ParcelhubShipmentId'=>$shipmentObj->RecordData['ParcelhubShipmentId']);
		
		 $shipmentObj->RecordData['pdf_data'] = $labeldata;
		if($shipmentObj->RecordData[BARCODE]==''){
		  			$this->UpdateForwarder($shipmentObj,'Error');
					$shipmentObj->RecordData[FORWARDER_ID]	= 22;
					$shipmentObj->RecordData['forwarder_detail'] = $shipmentObj->ForwarderDetail();
					$shipmentObj->GenerateBarcodeData(true);
					return true;
		}
		$this->storeLabelData($shipmentObj,json_encode($labeldata));
		 return true; 
		   // echo "<pre>";print_r($json);die;
		}
		
	 public function createXMLRposten($shipmentObj){ 
				$dom = new DOMDocument ( '1.0', 'UTF-8' );
				$root = $dom->createElement ('Shipment');
				$root->setAttribute ( 'xmlns', 'http://api.parcelhub.net/schemas/api/parcelhub-api-v0.4.xsd');
				$dom->appendChild ($root);
				
				$senderAdd = $this->ForwarderDetail['SenderAddress'];
				$customeradd = $this->getCustomerDetails($shipmentObj->RecordData[ADMIN_ID]);
				
				$this->xmlCreate['CollectionAddress']['ContactName'] = ($senderAdd[1]!='')?substr(trim($senderAdd[1]),0,35):'Parcel.nl';
				$this->xmlCreate['CollectionAddress']['CompanyName'] = ($senderAdd[0]!='')?substr(trim($senderAdd[0]),0,35):'Parcel.nl';
				$this->xmlCreate['CollectionAddress']['Email'] 		 = 'klantenservice@parcel.nl';
				$this->xmlCreate['CollectionAddress']['Phone'] 		 = '31748800700';
				$this->xmlCreate['CollectionAddress']['City'] 		 = 'Nottingham';
				$this->xmlCreate['CollectionAddress']['Address1']	 = 'Little Tennis Street Unit 1';
				$this->xmlCreate['CollectionAddress']['Postcode'] 	 = 'NG2 4EU';
				$this->xmlCreate['CollectionAddress']['Country'] 	 = 'GB';
				$this->xmlCreate['CollectionAddress']['AddressType'] = 'Business';
				
				$this->xmlCreate['DeliveryAddress']['ContactName']   = !empty($shipmentObj->RecordData[CONTACT]) ? substr($shipmentObj->RecordData[CONTACT],0,35) : substr($shipmentObj->RecordData[RECEIVER],0,35);
				$this->xmlCreate['DeliveryAddress']['CompanyName']   = substr($shipmentObj->RecordData[RECEIVER],0,35);
				$this->xmlCreate['DeliveryAddress']['Email'] 		 = !empty($shipmentObj->RecordData[EMAIL])?$shipmentObj->RecordData[EMAIL]:$customeradd['email'];
				$this->xmlCreate['DeliveryAddress']['Phone'] 		 = preg_replace('/\s+/', '',!empty($shipmentObj->RecordData[PHONE])?$shipmentObj->RecordData[PHONE]:'31748800700');
				
				$address1 = trim($shipmentObj->RecordData[ADDRESS].' '.$shipmentObj->RecordData[STREET2]);
				$this->xmlCreate['DeliveryAddress']['Address1']	 	 = substr($shipmentObj->RecordData[STREET].' '.$shipmentObj->RecordData[STREETNR],0,32);
				
				if(trim($address1)!=''){
				 $this->xmlCreate['DeliveryAddress']['Address2']	 	 = substr(trim($address1),0,30);
				}
				$this->xmlCreate['DeliveryAddress']['City'] 		 = $shipmentObj->RecordData[CITY];
				
				$postcodestring = preg_replace('/\s+/', '',preg_replace('/[^A-Za-z0-9 ]/', '',$shipmentObj->RecordData[ZIPCODE]));
				$this->xmlCreate['DeliveryAddress']['Postcode'] 	 = substr($postcodestring,0,-3).' '.substr($postcodestring,-3);
				$this->xmlCreate['DeliveryAddress']['Country'] 	 	 = $shipmentObj->RecordData['rec_cncode'];
				
				if($shipmentObj->RecordData[SERVICE_ID]==1){
				  $this->xmlCreate['DeliveryAddress']['AddressType'] = 'Residential';
				 }else{
				  $this->xmlCreate['DeliveryAddress']['AddressType'] = 'Business';
			   }
			    $this->xmlCreate['Reference1'] = substr($shipmentObj->RecordData[REFERENCE],0,20);
				$this->xmlCreate['Reference2'] = substr($shipmentObj->RecordData[REFERENCE],0,20);
				
				$this->xmlCreate['ContentsDescription'] = ($shipmentObj->RecordData['goods_description']!='')?$shipmentObj->RecordData['goods_description']:$shipmentObj->RecordData[REFERENCE];
				
				$this->xmlCreate['Packages']['PackageType'] = 'Parcel';
				$this->xmlCreate['Packages']['Package']['Dimensions']['Length'] = 0;
				$this->xmlCreate['Packages']['Package']['Dimensions']['Width'] = 0;
				$this->xmlCreate['Packages']['Package']['Dimensions']['Height'] = 0;
				$this->xmlCreate['Packages']['Package']['Weight'] = $shipmentObj->RecordData[WEIGHT];
				
				$this->xmlCreate['Packages']['Package']['Value'] = ($shipmentObj->RecordData['shipment_worth']>0)?round($shipmentObj->RecordData['shipment_worth']):0;
				$this->xmlCreate['Packages']['Package']['Contents'] = ($shipmentObj->RecordData['goods_id']!='')?$shipmentObj->RecordData['goods_id']:'Documents';
				
				
				$this->xmlCreate['Packages']['Package']['PackageCustomsDeclaration']['ContentsDescription'] = ($shipmentObj->RecordData['goods_id']!='')?$shipmentObj->RecordData['goods_id']:'Documents';
				$this->xmlCreate['Packages']['Package']['PackageCustomsDeclaration']['Weight'] = $shipmentObj->RecordData[WEIGHT];
				$this->xmlCreate['Packages']['Package']['PackageCustomsDeclaration']['Value'] = ($shipmentObj->RecordData['shipment_worth']>0)?round($shipmentObj->RecordData['shipment_worth']):0;
				$this->xmlCreate['Packages']['Package']['PackageCustomsDeclaration']['CountryOfOrigin'] = 'GB';
				$this->xmlCreate['Packages']['Package']['PackageCustomsDeclaration']['HSTariffNumber'] = '9703000000';
				$this->xmlCreate['Packages']['Package']['PackageCustomsDeclaration']['Quantity'] = 1;
				
				
				$this->xmlCreate['ServiceInfo']['ServiceId'] = 25011;
				$this->xmlCreate['ServiceInfo']['ServiceProviderId'] = 31;
				$this->xmlCreate['ServiceInfo']['ServiceCustomerUID'] = 4;
				
				$this->xmlCreate['CustomsDeclarationInfo']['CategoryOfItem'] = 'Sold';
				$this->xmlCreate['CustomsDeclarationInfo']['CategoryOfItemExplanation'] = 'Sold '.($shipmentObj->RecordData['goods_id']!='')?$shipmentObj->RecordData['goods_id']:'Documents';
				$this->xmlCreate['CustomsDeclarationInfo']['TermsOfTrade']		= 'DutiesAndTaxesUnpaid';
				$this->xmlCreate['CustomsDeclarationInfo']['PostalCharges'] = ($shipmentObj->RecordData['shipment_worth']>0)?round($shipmentObj->RecordData['shipment_worth']):0;
				$this->xmlCreate['CustomsDeclarationInfo']['ImportersContactDetails'] = $this->xmlCreate['DeliveryAddress']['Phone'];
				   
				$this->createXmlstructure($this->xmlCreate,$dom,$root);
				$saveData = $dom->save(PRINT_SAVE_LABEL.$this->ForwarderDetail['forwarder_name'].'/xml/CreateShipment.xml');
				return PRINT_OPEN_LABEL.$this->ForwarderDetail['forwarder_name'].'/xml/CreateShipment.xml';		
    	}
		
	 public function getLabelResponseRposten($shipmentObj){
		   try{
			$xml_data  = file_get_contents(PRINT_OPEN_LABEL.$this->ForwarderDetail['forwarder_name'].'/xml/CreateShipment.xml');
			$authurl = "http://api.parcelhub.net/api/0.4/token";
			$URL = "http://api.parcelhub.net/api/0.4/Shipment?RequestedLabelSize=6&RequestedLabelFormat=PDF";
			$authenticate = $this->CurlResponse($authurl,array('Content-Type: text/xml'),"grant_type=password&username=PNL001&password=pnl2894");
			$auth = json_decode($authenticate); 
			
			$labelcontent = $this->CurlResponse($URL,array('Content-Type: text/xml','Authorization: bearer '.$auth->access_token),$xml_data);
			$xml = simplexml_load_string($labelcontent);
			$json_encoded = json_encode($xml);
			$json = json_decode($json_encoded);
		   //echo "<pre>";print_r($json);die;
		 if(isset($json->Message)){
		        $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$shipmentObj->RecordData[SHIPMENT_ID].' '.$json->Message);
				if($shipmentObj->RecordData['API']){ 
					$this->UpdateForwarder($shipmentObj,$json->Message);
					$shipmentObj->RecordData[FORWARDER_ID]	= 22;
					$shipmentObj->RecordData['forwarder_detail'] = $shipmentObj->ForwarderDetail();
					$shipmentObj->GenerateBarcodeData(true);
					return true;
				}else{
					echo "F^There is some Error With XML Data. Please Try Again!";exit;
				}
			}
		 }catch(Exception $e){
		     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$shipmentObj->RecordData[SHIPMENT_ID].' '.$e->getMessage());
		 }	
		$ReturnData = array();
		$findarr = array('(',')',' ');
		$replace = array('','','');
		
		$labelmodel = $json->Packages->Package->PackageShippingInfo->Labels->Label->RawLabelData->RawLabelDataModel->Packages->RawLabelPiece;
		$labelmodel1 = $json->Packages->Package->PackageShippingInfo->Labels->Label->RawLabelData->HubEuropeRawLabelModel;
		
		$shipmentObj->RecordData[BARCODE] 		= str_replace($findarr,$replace,$json->ShippingInfo->CourierTrackingNumber);
		$shipmentObj->RecordData[REROUTE_BARCODE] = $json->ShippingInfo->ParcelhubTrackingNumber;
		$shipmentObj->RecordData[TRACENR] 		= $shipmentObj->RecordData[BARCODE];
		$shipmentObj->RecordData[TRACENR_BARCODE] 		= $shipmentObj->RecordData[BARCODE];
		$shipmentObj->RecordData['ParcelhubShipmentId']    = $json->ParcelhubShipmentId;
		
		$labeldata = array('TermsOfTrade'=>$json->CustomsDeclarationInfo->TermsOfTrade,'ImportersContactDetails'=>$json->CustomsDeclarationInfo->ImportersContactDetails,'Tarif_number'=>'9703000000');
		$shipmentObj->RecordData['pdf_data'] = $labeldata;
		$shipmentObj->RecordData['country_name'] = $objLabel->RecordData['rec_country_code'];
		$shipmentObj->RecordData['origin_country'] = 'GB';
		$shipmentObj->RecordData['Tarif_number'] = '9703000000';
		
		if($shipmentObj->RecordData[BARCODE]==''){
			
		}
		$this->storeLabelData($shipmentObj,json_encode($labeldata));
		 return true; 
		}
	  
	  public function UpdateForwarder($shipmentObj,$message){
	    $this->_db->update(SHIPMENT,array('forwarder_id'=>22,'wrong_parcel'=>'1'),"shipment_id=".$shipmentObj->RecordData[SHIPMENT_ID]."");
	  }
		
		
	  public function createXmlstructure($struct, DOMDocument $dom, DOMElement $parent){
		   $struct = ( array ) $struct;

              foreach ( $struct as $key => $value ) {//print_r($key);
               if ($value === false) {
                $value = 0;
               } elseif ($value === true) {
                $value = 1;
               }

               if (ctype_digit ( ( string ) $key )) {
                $key = 'key_' . $key;
               }

               if (is_array ( $value ) || is_object ( $value )) {
                $element = $dom->createElement ($key);
                $this->createXmlstructure ( $value, $dom, $element );
               } else {
                $element = $dom->createElement ($key);
				if($key=='Value' || $key=='PostalCharges'){
					$element->setAttribute('Currency', "GBP");
				}
                $element->appendChild ( $dom->createTextNode ( $value ) );
               }
            $parent->appendChild ( $element );
   		 }
	}
	
	public function createstructure($struct, DOMDocument $dom, DOMElement $parent){
		   $struct = ( array ) $struct;

              foreach ( $struct as $key => $value ) {//print_r($key);
               if ($value === false) {
                $value = 0;
               } elseif ($value === true) {
                $value = 1;
               }

               if (ctype_digit ( ( string ) $key )) {
                $key = 'key_' . $key;
               }

               if (is_array ( $value ) || is_object ( $value )) {
                $element = $dom->createElement ($key);
                    /*if($key=='MailItem'){
                    $element->setAttribute('ItemId', $this->barcode[$key]);
                    }*/
                $this->createstructure ( $value, $dom, $element );
               } else {

                $element = $dom->createElement ($key);
                $element->appendChild ( $dom->createTextNode ( $value ) );
               }
            $parent->appendChild ( $element );
   		 }
	}
	public function MakeLabeldata($shipmentObj){
	      $select = $this->_db->select()
									->from(YODEL_PDF,array('*'))
									->where("barcode='".$shipmentObj->RecordData[BARCODE]."'");
									//print_r($select->__toString());die;
		  $record = $this->getAdapter()->fetchRow($select);
		  if(!empty($record)){
		       $decodeddata = json_decode($record['pdf_contant']);
			   $labelmodel = $decodeddata->Packages->Package->ShippingInfo->Labels->Label->RawLabelData->YodelLabelModel;
			   $shipmentObj->RecordData['BARCODE_READABLE'] 		= $decodeddata->Packages->Package->ShippingInfo->TrackingNumber;
			   $labeldata = array('ProductName'=>$labelmodel->ProductName,'ServiceDescription'=>$labelmodel->ServiceDescription,'ReturnCode'=>$labelmodel->TrackingNumbers->JDNumbers->ReturnCode,'ServiceCentreLocationName'=>$labelmodel->ServiceCentreLocationName,'HubLocationName'=>$labelmodel->HubLocationName,'MeterNumber'=>$labelmodel->MeterNumber);
			   $findarr = array('(',')',' ');
				$replace = array('','','');
			   $shipmentObj->RecordData['pdf_data'] = $labeldata;
		  }
	   }
}