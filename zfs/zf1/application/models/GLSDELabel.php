<?php
class Application_Model_GLSDELabel extends Application_Model_Socketprocessing{
      public $GLSNLdata = array();
	  public function CreateGLSDELabel($shipmentObj,$newbarcode=true){
	    	$this->SocketForwarder  =  $shipmentObj->RecordData['forwarder_detail'];
			$servicetype = $this->SocketForwarder['service_type'];
			$tracking_customer_no = $this->SocketForwarder['shipping_depot_no'].$servicetype.$shipmentObj->RecordData[TRACENR];
			$check_digit = $this->GlsDECheckDigit($tracking_customer_no);
			$shipmentObj->RecordData[BARCODE] = $tracking_customer_no.$check_digit;
			$shipmentObj->RecordData['tracenr_barcode'] = $shipmentObj->RecordData[BARCODE];
			//Set final barcode
			$this->GlsDESocketProcessing($shipmentObj);
			$this->GlsDE2DBarcodePrinting($shipmentObj);
			if($shipmentObj->RecordData[REROUTE_BARCODE]==''){
					$this->_db->update(SHIPMENT,array('forwarder_id'=>22,'wrong_parcel'=>'1'),"shipment_id=".$shipmentObj->RecordData[SHIPMENT_ID]."");
					$shipmentObj->RecordData[FORWARDER_ID]	= 22;
					$shipmentObj->RecordData['forwarder_detail'] = $shipmentObj->ForwarderDetail();
					$shipmentObj->GenerateBarcodeData(true);
					return true;
			} 
	  }
	  
	   public function GlsDESocketProcessing($shipmentObj){
			$shipmentObj->RecordData['OnetimeRef'] = $this->OneTimeReference($shipmentObj->RecordData[QUANTITY],$shipmentObj->RecordData[BARCODE]);
		    $this->SocketData = $shipmentObj->RecordData;
		    $socketdata = $this->SocketReturnDE(); 
			$shipmentObj->RecordData[REROUTE_BARCODE] = $socketdata['Reroute'];
			$shipmentObj->RecordData['local_barcode'] = isset($socketdata['SocketLabel']['T600'])?$socketdata['SocketLabel']['T600']:'';
			$shipmentObj->RecordData['SocketResult'] = $socketdata['SocketLabel'];
		}
		
	  public function GlsDE2DBarcodePrinting($shipmentObj){
		   $GLSNLbarcode = $this->Create2Dbarcode($shipmentObj->RecordData['SocketResult']['T8902'],$shipmentObj->RecordData['SocketResult']['T8903'],$shipmentObj->RecordData[TRACENR]);
		   $shipmentObj->RecordData['PrimaryBarcode']	 = $GLSNLbarcode['Pri'];
		   $shipmentObj->RecordData['SecondryBarcode']	 = $GLSNLbarcode['Sec'];//echo "<pre>";print_r($shipmentObj->RecordData);die;
		   
		   /*if($this->getInternalCode($this->RecordData[SERVICE_ID])=='L' && $this->RecordData[SHIPMENT_WEIGHT]<=2){
		       $this->RecordData['ServiceText']	 = 'Parcelletter';
		   }*/
		   
		  // $this->SetLogo();
		  return true;
		}
		
	public function GlsDECheckDigit($number) {
	   $multiply = 0;
	   $revnumber =  strrev($number);
	  for($i=0;$i<strlen($revnumber);$i++){
		$factor  = ($i%2==0)?'3':'1';
		$multiply = $multiply+($revnumber[$i]*$factor);
	  }
	  $multiply = $multiply + 1;
	  $mode = $multiply%10;
	   if($mode==0){
	     $check_digit = $mode;
	   }else{
	     $check_digit = (10-$mode);
	   }
	  return $check_digit;
	}
	
	public function OneTimeReferenceDE($quantity,$barcode){

		$Totalshipmet = $quantity;

		if($quantity<=9){

		$Totalshipmet = '0'.$quantity;

		}

	    if($quantity==1){

			$multicoli = 'S';

		}else{

			$multicoli = 'M';

		}

	  return '99'.substr($barcode,8,5).$Totalshipmet.$multicoli;

    }
	
	public function OldGlsBarcodeFormation($shipmentObj){
		    $shipmentObj->RecordData[COUNTRY_CODE] = $shipmentObj->RecordData['rec_cncode'];//Reciver cncode
			$shipmentObj->RecordData[SERVICE_NAME] = '';
			$shipmentObj->RecordData['SAP_customer_no'] = '2760116420';
			$shipmentObj->RecordData['unique_contact_id'] = '2760024479';
			$shipmentObj->RecordData[SHIPMENT_BARCODE] = $shipmentObj->RecordData[SHIPMENT_TRACENR];
			//$glsData = $this->countryDetail($this->RecordData[COUNTRY_ID]);
			$shipmentObj->RecordData['oldlabel'] = true;
			$CountryISOCode = $glsData['iso_code'];
			$totalUnitOfConsignment = str_pad($shipmentObj->RecordData[QUANTITY],3,'0',STR_PAD_LEFT);
			$unitSequenceNumberConsignment = str_pad(Bootstrap::$LabelObj->ParcelCount,3,'0',STR_PAD_LEFT);
			$consignmentNumber = "";
			$shipment_weight = $shipmentObj->RecordData[WEIGHT] / 100;
			
			$serviceAndAdditionalServiceInformation = '';$this->serviceAndAdditionalServiceInformationOFGlsDE();
			$product_code = 'AA';
			
			$Barcode = "A|".$shipmentObj->RecordData['SAP_customer_no']."|".
				       $shipmentObj->RecordData['unique_contact_id']."|".$product_code."|".
				       $CountryISOCode ."|".substr($shipmentObj->RecordData[SHIPMENT_ZIPCODE],0,7)."|".
				       substr($totalUnitOfConsignment,0,3)."|".substr($unitSequenceNumberConsignment,0,3)."|".
				   	   $consignmentNumber."|".substr($this->remove_accent($this->RecordData[SHIPMENT_NAME]),0,20)."|".
				       substr($this->remove_accent($shipmentObj->RecordData[SHIPMENT_CONTACT]),0,20)."|".substr($this->remove_accent($this->RecordData[SHIPMENT_STREET2]),0,20)."|".
					   substr($this->remove_accent($shipmentObj->RecordData[SHIPMENT_STREET]),0,20)."|".substr($this->remove_accent($this->RecordData[SHIPMENT_STREETNR]),0,5)."|".
				       substr($this->remove_accent($shipmentObj->RecordData[SHIPMENT_CITY]),0,20)."|".substr($this->RecordData[SHIPMENT_PHONE],0,20)."|".
				       substr($this->RecordData[TRACENR],0,20)."|". substr(27248,0,22)."|".
				       substr($shipment_weight,0,5)."|".$serviceAndAdditionalServiceInformation;
			
			$barcodeAfterPadding = str_pad($Barcode,303,' ',STR_PAD_RIGHT)."|";
			
			//2D code
			$bc = new Zend_Barcode_2DBARCODE_DataMatrix();
			$FILEPATH = GLS_LABEL_LINK."img/";
			
			$CODE = NULL;
			$CODE       = substr($barcodeAfterPadding, 0 ,304);
			$IMAGE_NAME = $this->RecordData[SHIPMENT_TRACENR].'.png';
			
			$this->RecordData[SHIPMENT_REROUTE_BARCODE] = $CODE; //Set final barcode
		   
			$this->RecordData['img_url'] = $FILEPATH.$IMAGE_NAME;
			
			$bc->setBGColor("WHITE");
			$bc->setBarColor("BLACK");
			$bc->setRotation("0");
			$bc->setImageType("PNG", 40 );
			$bc->setQuiteZone("10");
			$bc->setEncoding("AUTO");
			$bc->setFormat("64");
			$bc->setTilde("Y");
			$bc->setModuleSize("3");
			$bc->setFilePath($FILEPATH);
			$bc->paint($CODE,$IMAGE_NAME);
			
			/*$logo = $this->ParcelLogo($this->RecordData[ADMIN_ID],array($this->RecordData['senderaddress_id']));
			
			$this->RecordData['CustomerLogo']	= LOGO_UPLODE_LINK.'/'.$logo['CustLogo'][0];
			$this->RecordData['LogoType']		= $logo['CustLogo'][1];
			if($logo['CustLogo'][0]==''){
			  $this->RecordData['CustomerLogo']	= LOGO_UPLODE_LINK.'/'.$logo['DepotLogo'][0];
			  $this->RecordData['LogoType']		= $logo['DepotLogo'][1];
			}
			$this->RecordData['DepotLogo']	= LOGO_UPLODE_LINK.'/'.$logo['DepotLogo'][0];
			$this->RecordData['DepotLogoType'] = $logo['DepotLogo'][1];*/
			
			return;
		}
}