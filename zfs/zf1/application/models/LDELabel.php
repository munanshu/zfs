<?php
class Application_Model_LDELabel extends Zend_custom{
       public $DPDDEdata = array();
	  public function CreateLDELabel($shipmentObj,$newbarcode=true){
			$this->DPDDEdata  =  $shipmentObj->RecordData['forwarder_detail'];
			$shipmentObj->getDPDRouteInfo();
			$shipmentObj->RecordData[REROUTE_BARCODE] = $this->DPDDEdata['depot_number'].$shipmentObj->RecordData[TRACENR];
			$shipmentObj->RecordData['tracenr_barcode'] = $this->DPDDEdata['depot_number'].$shipmentObj->RecordData[TRACENR];
			$this->DPDBarcodeFormation($shipmentObj);
			
			$objaztech = new Zend_Aztech_aztech();
			try{
			$barcodedata = $this->Generate2DBarcodeData($shipmentObj);
			$objaztech->createtAztechbarcode($barcodedata,PRINT_SAVE_LABEL.$this->DPDDEdata['forwarder_name'].'/img/'.$shipmentObj->RecordData[REROUTE_BARCODE].'.png');
			}catch(Exception $e){
			   echo $e->getMessage();die;
			}
			return true;
	  }
	  public function DPDBarcodeFormation($shipmentObj){
	     $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[REROUTE_BARCODE];
		 if($shipmentObj->RecordData['BarcodeID']==37){
			 $prifix = '%';
		 }else{
			 $prifix = '!';
		 }
		 $DestPostalcode  = commonfunction::paddingleft(substr($shipmentObj->RecordData[ZIPCODE],0,7),7,0);
		 $barcode = commonfunction::uppercase($DestPostalcode.$this->DPDDEdata['depot_number'].$shipmentObj->RecordData[TRACENR].$shipmentObj->RecordData['ServiceCode'].$shipmentObj->RecordData['Country']);
		 /*if(isset($shipmentObj->RecordData['saturday_delivery'])){
		 	$check_digit = $this->DPDDECheckDigit($barcode);
		 }else{
		   $check_digit = $this->CheckDigitMode36Parcelshop($barcode);
		 }*/
		 $check_digit = commonfunction::CheckDigitMode36Parcelshop($barcode);
		 
		 $shipmentObj->RecordData[BARCODE] = commonfunction::uppercase('25'.$DestPostalcode.$this->DPDDEdata['depot_number'].$shipmentObj->RecordData[TRACENR].$shipmentObj->RecordData['ServiceCode'].$shipmentObj->RecordData['Country'].$check_digit);
		 
		  $shipmentObj->RecordData['DesPostcodeSPC'] = commonfunction::GetSpaceBetweenString($DestPostalcode);
		  $shipmentObj->RecordData['TrackingSPC']    = commonfunction::GetSpaceBetweenString($shipmentObj->RecordData[TRACENR]);
		  
		  
		  $shipmentObj->RecordData['barcode_redable'] = commonfunction::uppercase($shipmentObj->RecordData['DesPostcodeSPC']." ".$this->DPDDEdata['depot_number']." ".$shipmentObj->RecordData['TrackingSPC']." ".$shipmentObj->RecordData['ServiceCode']." ".$shipmentObj->RecordData['Country']." ".$check_digit);
		  
		  $shipmentObj->RecordData['barcode_encodable'] = commonfunction::uppercase(urlencode($prifix).$DestPostalcode.$this->DPDDEdata['depot_number'].$shipmentObj->RecordData[TRACENR].$shipmentObj->RecordData['ServiceCode'].$shipmentObj->RecordData['Country']);
		  $shipmentObj->RecordData['TrackingCheck'] = $this->DPDDECheckDigit($shipmentObj->RecordData[REROUTE_BARCODE]);
		  //$this->RecordData['Destination'] = $this->countryCode($this->RecordData[COUNTRY_ID]).'-'.$this->RecordData['DestinationDepot'];
		  //$this->RecordData['ServiceCountry'] = $this->RecordData['serviceCode'].'-'.$this->countryCode($this->RecordData[COUNTRY_ID]).'-'.$this->RecordData[SHIPMENT_ZIPCODE];
		  $shipmentObj->RecordData['Destination'] = $shipmentObj->RecordData['rec_cncode'].'-'.$shipmentObj->RecordData['DestinationDepot'];
		  $shipmentObj->RecordData['ServiceCountry'] = $shipmentObj->RecordData['ServiceCode'].'-'.$shipmentObj->RecordData['rec_cncode'].'-'.commonfunction::uppercase($shipmentObj->RecordData[ZIPCODE]);
		  $shipmentObj->RecordData['OriginText']  = date('d/m/y h:i')." ".$this->DPDDEdata['version_number']." ".$this->DPDDEdata['depot_number']."/99 ";
		  return true;
	}
	
	
	  public function Generate2DBarcodeData($shipmentObj){ 
  
      $headers = '';
	  $separator = ';';
	  
	   $messageheader = '[)>30';
	   
	   $headers .= $messageheader;
	   
	   $D2DISO_Header  			= '01';
	   $D2DISO_Version 			= '02';
	   $D2DISO_DestZipCode 		= $shipmentObj->RecordData[ZIPCODE];
	   $D2DISO_DestCountryCode 	= $shipmentObj->RecordData['rec_cncode'];
	   $D2DISO_ServiceCode 		= $shipmentObj->RecordData['ServiceCode'];
	   $D2DISO_ParcelNumber 		= $shipmentObj->RecordData[REROUTE_BARCODE];
	   $D2DISO_SCAC 				= 'GEOP';
	   $D2DISO_CustAccNumber 	= '';  //Customer AC number or shipper 
	   $D2DISO_JDPickup 			= commonfunction::julianDate(date('Y-m-d'));
	   $ID2DISO_ConsCustRef1 	= $shipmentObj->RecordData[REFERENCE];
	   
	   $headers .= $D2DISO_Header.$separator.$D2DISO_Version.$separator.$D2DISO_DestZipCode.$separator.$D2DISO_DestCountryCode.$separator.$D2DISO_ServiceCode.$separator.$D2DISO_ParcelNumber.$separator.$D2DISO_SCAC.$separator.$D2DISO_CustAccNumber.$separator.$D2DISO_JDPickup.$separator.$ID2DISO_ConsCustRef1;
	   
	   $parcelcountexp = commonfunction::explode_string($shipmentObj->RecordData['ShipmentCount'],'/');
	   $D2DISO_RangInNumber 		= commonfunction::paddingleft($parcelcountexp[0],3,0).'/'.commonfunction::paddingleft($parcelcountexp[1],3,0);
	   $D2DISO_DeclaredWeight 	= round((($shipmentObj->RecordData[WEIGHT]*1000)/10));
	   $D2DISO_CrossMatch 		= 'N';
	   $D2DISO_RecStreet 		= $shipmentObj->RecordData[STREET];
	   $D2DISO_RecTown 			= $shipmentObj->RecordData[CITY];
	   $D2DISO_RecState 			= $shipmentObj->RecordData['rec_state'];
	   $D2DISO_RecCompName1 		= $shipmentObj->RecordData[RECEIVER];
	   
	   $headers .= $D2DISO_RangInNumber.$separator.$D2DISO_DeclaredWeight.$separator.$D2DISO_CrossMatch.$separator.$D2DISO_RecStreet.$separator.$D2DISO_RecTown.$separator.$D2DISO_RecState.$separator.$D2DISO_RecCompName1;
	   
	   $D2DSTD_Header2 		= '07';
	   $D2DSTD_FormatID 		= 'G02';
	   $D2DSTD_BarcodeOF 	= '0';
	   $D2DSTD_NoHandWoutData= '0';
	   $D2DSTD_RoutingNec 	= '0';
	    
		$headers .= $D2DSTD_Header2.$separator.$D2DSTD_FormatID.$separator.$D2DSTD_BarcodeOF.$separator.$D2DSTD_NoHandWoutData.$separator.$D2DSTD_RoutingNec;
	  
	   //Rceiver Information
	   $D2DSTD_RecComment 			= '';
	   $D2DSTD_RecCompName2 		= $shipmentObj->RecordData[RECEIVER];
	   $D2DSTD_RecContact 			= $shipmentObj->RecordData[CONTACT];
	   $D2DSTD_RecContactPho1 		= $shipmentObj->RecordData[PHONE];
	   $D2DSTD_RecContactPho2 		= '';
	   $D2DSTD_RecNotifMob 			= '';
	   $D2DSTD_RecNotifEMail 		= $shipmentObj->RecordData[EMAIL];
	   $D2DSTD_RecPropNum 			= $shipmentObj->RecordData[STREETNR];
	   $D2DSTD_RecAdd2 				= $shipmentObj->RecordData[ADDRESS];
	   $D2DSTD_RecAdd3 				= $shipmentObj->RecordData[STREET2];
	   $D2DSTD_NotifType 			= 'B';
	   $D2DSTD_TotalWeight 			= $shipmentObj->RecordData[WEIGHT].'KG';
	   
	   $headers .= $D2DSTD_RecComment.$separator.$D2DSTD_RecCompName2.$separator.$D2DSTD_RecContact.$separator.$D2DSTD_RecContactPho1.$separator.$D2DSTD_RecContactPho2.$separator.$D2DSTD_RecNotifMob.$separator.$D2DSTD_RecNotifEMail.$separator.$D2DSTD_RecPropNum.$separator.$D2DSTD_RecAdd2.$separator.$D2DSTD_RecAdd3.$separator.$D2DSTD_NotifType.$separator.$D2DSTD_TotalWeight;
	   
	   $senderAddress  = $this->DPDDEdata['SenderAddress'];
	   
	   $D2DSTD_NotifSenderComp 		= $senderAddress[0];
	   $D2DSTD_NotifSenderContact 	= $senderAddress[1];
	   $D2DSTD_SendParcelRef 		=  $shipmentObj->RecordData[REFERENCE];
	   $D2DSTD_RecParcelRef 			=  $shipmentObj->RecordData[REFERENCE];
	   $D2DSTD_ConsType 				= 'D';
	   $D2DSTD_ContDescr 			= $shipmentObj->RecordData['goods_description'];
	   $D2DSTD_ConsCustRef2 			= $shipmentObj->RecordData[REFERENCE];
	   $D2DSTD_LimitedQtyHaz 		= '0';
	   
	   $headers .= $D2DSTD_NotifSenderComp.$separator.$D2DSTD_NotifSenderContact.$separator.$D2DSTD_SendParcelRef.$separator.$D2DSTD_RecParcelRef.$separator.$D2DSTD_ConsType.$separator.$D2DSTD_ContDescr.$separator.$D2DSTD_ConsCustRef2.$separator.$D2DSTD_LimitedQtyHaz;
	   
	   $FormatEnvelopetrailer		= '';
	   
	   $headers .= $FormatEnvelopetrailer;
	   
		$D2DS01_Header		= '07';
		$D2DS01_FormatID		= 'S010';
		//Sender Information
		$D2DS01_SendCompName		= $senderAddress[0];
		$D2DS01_SendPhone		= '';   // Sender Phone number
		$D2DS01_Contact			= $senderAddress[1];
		$D2DS01_SendPropNum		= '';
		$D2DS01_SendStreet		= $senderAddress[2];
		$D2DS01_SendAddr2		= '';
		$D2DS01_SendTown			= $senderAddress[3];
		$D2DS01_SendZipCode		= $senderAddress[4];
		$D2DS01_SendCountryCode	= $senderAddress[5];
		
		$headers .= $D2DS01_Header.$separator.$D2DS01_FormatID.$separator.$D2DS01_SendCompName.$separator.$D2DS01_SendPhone.$separator.$D2DS01_Contact.$separator.$D2DS01_SendPropNum.$separator.$D2DS01_SendStreet.$separator.$D2DS01_SendAddr2.$separator.$D2DS01_SendTown.$separator.$D2DS01_SendZipCode.$separator.$D2DS01_SendCountryCode;
		
		
		$D2DS02_Header			= '07';
		$D2DS02_FormatID			= 'S020';
		$D2DS02_Curr				= 'EUR';
		$D2DS02_Amount			= $shipmentObj->RecordData['cod_price'];
		$D2DS02_CollectType		= '0';
		
		$headers .= $D2DS02_Header.$separator.$D2DS02_FormatID.$separator.$D2DS02_Curr.$separator.$D2DS02_Amount.$separator.$D2DS02_CollectType;
		
		$D2DS03_Header			= '07';
		$D2DS03_FormatID			= 'S030';
		$D2DS03_CompInformation	= '0';
		$D2DS03_RecVAT			= '';
		$D2DS03_SendVAT			= '';
		$D2DS03_ComBillRecName	= '';
		$D2DS03_ComBillRecPropNum= '';
		$D2DS03_ComBillRecStreet	= '';
		$D2DS03_ComBillRecCity	= '';
		$D2DS03_ComBillRecCountryCode= '';
		$D2DS03_ComBillRecZipCode= '';
		$D2DS03_ComBillRecContact= '';
		
		$headers .= $D2DS03_Header.$separator.$D2DS03_FormatID.$separator.$D2DS03_CompInformation.$separator.$D2DS03_RecVAT.$separator.$D2DS03_SendVAT.$separator.$D2DS03_ComBillRecName.$separator.$D2DS03_ComBillRecPropNum.$separator.$D2DS03_ComBillRecStreet.$separator.$D2DS03_ComBillRecCity.$separator.$D2DS03_ComBillRecCountryCode.$separator.$D2DS03_ComBillRecZipCode.$separator.$D2DS03_ComBillRecContact;
		//Receiver COntact
		$D2DS03_ComBillRecPhone	= '';
		$D2DS03_TotalValue		= '';
		$D2DS03_Currency			= '';
		$D2DS03_Incoterm			= '';
		$D2DS03_DestCountryReg	= '';
		$D2DS03_ArticleNumber	= '';
		
		$headers .= $D2DS03_ComBillRecPhone.$separator.$D2DS03_TotalValue.$separator.$D2DS03_Currency.$separator.$D2DS03_Incoterm.$separator.$D2DS03_DestCountryReg.$separator.$D2DS03_ArticleNumber;
		//Article Description
		$D2DS03_Art1_Desc	= '';
		$D2DS03_Art1_Qty	= '';
		$D2DS03_Art1_Weigth	= '';
		$D2DS03_Art1_Value	= '';
		$D2DS03_Art1_ComCode	= '';
		$D2DS03_Art1_OriginCountry	= '';
		
		$headers .= $D2DS03_Art1_Desc.$separator.$D2DS03_Art1_Qty.$separator.$D2DS03_Art1_Weigth.$separator.$D2DS03_Art1_Value.$separator.$D2DS03_Art1_ComCode.$separator.$D2DS03_Art1_OriginCountry;
		//Article description 2
		$D2DS03_Art2_Desc	= '';
		$D2DS03_Art2_Qty	= '';
		$D2DS03_Art2_Weigth	= '';
		$D2DS03_Art2_Value	= '';
		$D2DS03_Art2_ComCode	= '';
		$D2DS03_Art2_OriginCountry	= '';
		
		$headers .= $D2DS03_Art2_Desc.$separator.$D2DS03_Art2_Qty.$separator.$D2DS03_Art2_Weigth.$separator.$D2DS03_Art2_Value.$separator.$D2DS03_Art2_ComCode.$separator.$D2DS03_Art2_OriginCountry;
		//Article descrption3
		$D2DS03_Art3_Desc	= '';
		$D2DS03_Art3_Qty	= '';
		$D2DS03_Art3_Weigth	= '';
		$D2DS03_Art3_Value	= '';
		$D2DS03_Art3_ComCode	= '';
		$D2DS03_Art3_OriginCountry	= '';
		
		$headers .= $D2DS03_Art3_Desc.$separator.$D2DS03_Art3_Qty.$separator.$D2DS03_Art3_Weigth.$separator.$D2DS03_Art3_Value.$separator.$D2DS03_Art3_ComCode.$separator.$D2DS03_Art3_OriginCountry;
		//Article description 4
		$D2DS03_Art4_Desc	= '';
		$D2DS03_Art4_Qty	= '';
		$D2DS03_Art4_Weigth	= '';
		$D2DS03_Art4_Value	= '';
		$D2DS03_Art4_ComCode	= '';
		$D2DS03_Art4_OriginCountry	= '';
		
		$headers .= $D2DS03_Art4_Desc.$separator.$D2DS03_Art4_Qty.$separator.$D2DS03_Art4_Weigth.$separator.$D2DS03_Art4_Value.$separator.$D2DS03_Art4_ComCode.$separator.$D2DS03_Art4_OriginCountry;
		//Artcle description5
		$D2DS03_Art5_Desc	= '';
		$D2DS03_Art5_Qty	= '';
		$D2DS03_Art5_Weigth	= '';
		$D2DS03_Art5_Value	= '';
		$D2DS03_Art5_ComCode	= '';
		$D2DS03_Art5_OriginCountry	= '';
		
		$headers .= $D2DS03_Art5_Desc.$separator.$D2DS03_Art5_Qty.$separator.$D2DS03_Art5_Weigth.$separator.$D2DS03_Art5_Value.$separator.$D2DS03_Art5_ComCode.$separator.$D2DS03_Art5_OriginCountry;
		
		$D2DS05_Header		= '07';
		$D2DS05_FormatID		= 'D002010';
		$D2DS05_DELISUSR		= $this->DPDDEdata['delis_user_id'];
		$D2DS05_VOLUME		= '0';
		$D2DS05_MPSID		= '';
		$D2DS05_MPSCOMP		= '1';
		$D2DS05_MPSCOMPLBL	= '0';
		$D2DS05_PERSCOMPLETE	= '0';
		$D2DS05_PERSDELIVERY	= '1';
		$D2DS05_PERSFLOOR	= '1';
		$D2DS05_PERSBUILDUNG	= '1';
		$D2DS05_PERSDEPARTMENT	= '1';
		
		$headers .= $D2DS05_Header.$separator.$D2DS05_FormatID.$separator.$D2DS05_DELISUSR.$separator.$D2DS05_VOLUME.$separator.$D2DS05_MPSID.$separator.$D2DS05_MPSCOMP.$separator.$D2DS05_MPSCOMPLBL.$separator.$D2DS05_PERSCOMPLETE.$separator.$D2DS05_PERSDELIVERY.$separator.$D2DS05_PERSFLOOR.$separator.$D2DS05_PERSBUILDUNG.$separator.$D2DS05_PERSDEPARTMENT;
		//Department Delivery
		$D2DS05_PERSNAME		= '';
		$D2DS05_PERSPHONE		= '1';
		$D2DS05_PERSID	= '0';
		$D2DS05_ODEPOT	= '0';
		$D2DS05_ONAME1	= '1';
		$D2DS05_ONAME2	= '';
		$D2DS05_OSTREET	= '';
		$D2DS05_OHOUSENO	= '';
		$D2DS05_OCOUNTRYN= '';
		$D2DS05_OSTATE	= '';
		$D2DS05_OPOSTAL	= '';
		$D2DS05_OCITY	= '';
		$D2DS05_OPHONE	= '';
		$D2DS05_OEMAIL	= '';
		
		$headers .= $D2DS05_PERSNAME.$separator.$D2DS05_PERSPHONE.$separator.$D2DS05_PERSID.$separator.$D2DS05_ODEPOT.$separator.$D2DS05_ONAME1.$separator.$D2DS05_ONAME2.$separator.$D2DS05_OSTREET.$separator.$D2DS05_OHOUSENO.$separator.$D2DS05_OCOUNTRYN.$separator.$D2DS05_OSTATE.$separator.$D2DS05_OPOSTAL.$separator.$D2DS05_OCITY.$separator.$D2DS05_OPHONE.$separator.$D2DS05_OEMAIL;
		//Consignee
		$D2DS05_OILN	= '';
		$D2DS05_MSGCOMPLETE	= '0';
		//Notification type 1
		$D2DS05_MSGTYPE1	= '1';
		$D2DS05_MSGVALUE1 = '';
		$D2DS05_MSGRULE1	= '';
		$D2DS05_MSGLANG1	= 'NL';
		//notification Type 2
		$D2DS05_MSGTYPE2	= '2';
		$D2DS05_MSGTYPE2	= '';
		$D2DS05_MSGRULE2	= '2';
		$D2DS05_MSGLANG2	= 'NL';
		//notification Type 3
		$D2DS05_MSGTYPE3	= '2';
		$D2DS05_MSGVALUE3	= '';
		$D2DS05_MSGRULE3	= '2';
		$D2DS05_MSGLANG3	= 'NL';
		//notification Type 4
		$D2DS05_MSGTYPE4	= '2';
		$D2DS05_MSGVALUE4	= '';
		$D2DS05_MSGRULE4	= '2';
		$D2DS05_MSGLANG4	= 'NL';
		//notification Type 5
		$D2DS05_MSGTYPE5	= '2';
		$D2DS05_MSGVALUE5	= '';
		$D2DS05_MSGRULE5	= '2';
		$D2DS05_MSGLANG5	= 'NL';
		
		$headers .= $D2DS05_OILN.$separator.$D2DS05_MSGCOMPLETE.$separator.$D2DS05_MSGTYPE1.$separator.$D2DS05_MSGVALUE1.$separator.$D2DS05_MSGRULE1.$separator.$D2DS05_MSGLANG1.$separator.$D2DS05_MSGTYPE2.$separator.$D2DS05_MSGTYPE2.$separator.$D2DS05_MSGRULE2.$separator.$D2DS05_MSGLANG2.$separator.$D2DS05_MSGTYPE3.$separator.$D2DS05_MSGVALUE3.$separator.$D2DS05_MSGRULE3.$separator.$D2DS05_MSGLANG3.$separator.$D2DS05_MSGTYPE4.$separator.$D2DS05_MSGVALUE4.$separator.$D2DS05_MSGRULE4.$separator.$D2DS05_MSGLANG4;
		
		$D2DS05_SHIPINFOCOMPLETE	= '0';
		$D2DS05_ADDSERVICE	= '';
		$D2DS05_MSGNO	= '';
		$D2DS05_FUNCTION	= '';
		$D2DS05_PARAMETER	= '';
		$D2DS05_HAZDATACOMPLETE	= '0';
		$D2DS05_HAZPACKUNG	= '';
		$D2DS05_HAZZIELDEP	= '0512';    //Destyination depot
		$D2DS05_HAZVERSDEP	= '0144';	 //0419sending depot
		
		$headers .= $D2DS05_SHIPINFOCOMPLETE.$separator.$D2DS05_ADDSERVICE.$separator.$D2DS05_MSGNO.$separator.$D2DS05_FUNCTION.$separator.$D2DS05_PARAMETER.$separator.$D2DS05_HAZDATACOMPLETE.$separator.$D2DS05_HAZPACKUNG.$separator.$D2DS05_HAZZIELDEP.$separator.$D2DS05_HAZVERSDEP;
		//HAZ1 - hazardous substance1
		$D2DS05_HAZUNNR1		= '';
		$D2DS05_HAZKLASSE1	= '';
		$D2DS05_HAZKCODE1	= '';
		$D2DS05_HAZVGRUPPE1	= '';
		$D2DS05_HAZBEZ1		= '';
		$D2DS05_HAZNEBGEF1	= '';
		$D2DS05_HAZTBC1	= '';
		$D2DS05_HAZGEW1	= '';
		$D2DS05_HAZEXGEW1		= '';
		$D2DS05_HAZFAKTOR1		= '';
		$D2DS05_HAZNAGTEXT1		= '';
		
		$headers .= $D2DS05_HAZUNNR1.$separator.$D2DS05_HAZKLASSE1.$separator.$D2DS05_HAZKCODE1.$separator.$D2DS05_HAZVGRUPPE1.$separator.$D2DS05_HAZBEZ1.$separator.$D2DS05_HAZNEBGEF1.$separator.$D2DS05_HAZTBC1.$separator.$D2DS05_HAZGEW1.$separator.$D2DS05_HAZEXGEW1.$separator.$D2DS05_HAZFAKTOR1.$separator.$D2DS05_HAZNAGTEXT1;
		//HAZ2 - hazardous substance2
		$D2DS05_HAZUNNR2		= '';
		$D2DS05_HAZKLASSE2	= '';
		$D2DS05_HAZKCODE2	= '';
		$D2DS05_HAZVGRUPPE2	= '';
		$D2DS05_HAZBEZ2		= '';
		$D2DS05_HAZNEBGEF2	= '';
		$D2DS05_HAZTBC2		= '';
		$D2DS05_HAZGEW2		= '';
		$D2DS05_HAZEXGEW2	= '';
		$D2DS05_HAZFAKTOR2	= '';
		$D2DS05_HAZNAGTEXT2	= '';
		$D2DS05_RECEIVERZIPCODE8		= '';
		$D2DS05_RECEIVERZIPCODE11		= '';
		
		$headers .= $D2DS05_HAZUNNR2.$separator.$D2DS05_HAZKLASSE2.$separator.$D2DS05_HAZKCODE2.$separator.$D2DS05_HAZVGRUPPE2.$separator.$D2DS05_HAZBEZ2.$separator.$D2DS05_HAZNEBGEF2.$separator.$D2DS05_HAZTBC2.$separator.$D2DS05_HAZGEW2.$separator.$D2DS05_HAZEXGEW2.$separator.$D2DS05_HAZFAKTOR2.$separator.$D2DS05_HAZNAGTEXT2.$separator.$D2DS05_RECEIVERZIPCODE8.$separator.$D2DS05_RECEIVERZIPCODE11;
		
		
		return $headers;
  }
  
     /**
     * Check Didig Mode 36
     * Function : DPDDECheckDigit()
     * This Function Calculate Check Digit on Mode 36
     * */
     public function DPDDECheckDigit($string) {
        $char2Value = array(
            0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4,5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9,'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17,
            'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21, 'M' => 22, 'N' => 23, 'O' => 24, 'P' => 25,

            'Q' => 26, 'R' => 27, 'S' => 28, 'T' => 29,

            'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33,

            'Y' => 34, 'Z' => 35

        );

        $value2Char = array(

            0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4,

            5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9,

            10 => 'A', 11 => 'B', 12 => 'C', 13 => 'D',

            14 => 'E', 15 => 'F', 16 => 'G', 17 => 'H',

            18 => 'I', 19 => 'J', 20 => 'K', 21 => 'L',

            22 => 'M', 23 => 'N', 24 => 'O', 25 => 'P',

            26 => 'Q', 27 => 'R', 28 => 'S', 29 => 'T',

            30 => 'U', 31 => 'V', 32 => 'W', 33 => 'X',

            34 => 'Y', 35 => 'Z'

        );

        $mod = 36;

        $cd = $mod;



        for ($i = 0; $i < strlen($string); $i++) {



            $val = intval($char2Value[$string{$i}]);

            $cd = $cd + $val;



            if ($cd > $mod) {

                $cd = ($cd - $mod);

                $cd = ($cd * 2);

            } else {

                $cd = ($cd * 2);

                if ($cd > $mod) {

                    $cd = ($cd - ($mod + 1));

                }

            }

        }



        $cd = (($mod + 1) - $cd);

        if ($cd == $mod) {

            $cd = 0;

        }

        $checkDigit = $value2Char[$cd];



        return $checkDigit;

    }
}