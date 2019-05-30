<?php
 
class Checkin_Model_PostatEdi extends Zend_Custom
{
	public $ForwarderRecord = array();
    public $Forwarders	= array();
	public function generateEDI($data){ 
	  $this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);
	  try{
	   $select = $this->_db->select()
							->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
							->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE))
							->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,CITY,ZIPCODE,PHONE,EMAIL,
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id','service_attribute','cod_price',STREET2, ADDRESS))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	   $ediBody = '';
	   foreach($results as $result){
	      $this->RecordData = $result;
		  $this->ForwarderRecord = $this->ForwarderDetail();
		  $ediBody .= $this->postatEdiBody($result);
	   }
	   $header = $this->postatEdiHeader();
	   $ediBody = $header.$ediBody;
		$filename = "0021113016-".date("Ymd").date("His").'-'.str_pad($this->ForwarderRecord['IFD_number'],3,'0',STR_PAD_LEFT);
	   return array('EdiData'=>$ediBody,'EdiFilename'=>$filename);
	}
	/**
	 * Generate PostAt EDI Header
	 * Function : postatEdiHeader()
	 * Function Generate Postat EDI Header
	 **/
	public function postatEdiHeader() {
		$field1				= '01;';
		$DebitorPayer 		= '0021113016;';
		$Sender				= 'Maillog Richter & Weiner Ges.m.b.H.;';
		$CreateDate			= date("Y-m-d").'T'.date("H:i:s").';';
		$SendingDate		= date("Y-m-d").'T'.date("H:i:s").';';
		$avisoversion		= '3;';
		$avisosystem		= 'PVS 3;';
		$itcontactname		= 'Herbert Rienesl;';
		$itcontactteleno	= '02236312105;';
		$itcontactmail		= 'h.rienesl@maillog.at';
		
		$TopRecords 		= $field1.$DebitorPayer.$Sender.$CreateDate.$SendingDate.$avisoversion.$avisosystem.$itcontactname.$itcontactteleno.$itcontactmail."\r\n";
		
		$sender_address = $this->ForwarderRecord['SenderAddress'];
		
		$FieldNo2			= '02;';
		$ShipperName1		= 'Parcel.NL BV;';
		$ShipperName2		= 'Parcel.NL BV;';
		$ShipperName3		= commonfunction::stringReplace(array('\r','\n',' ','  '),array('','','',''),trim($sender_address[1])).';';
		$ShipperName4		= ';';
		$ShipperCountry		= 'AT;';
		$ShipperPostalCode 	= '2340;';
		$ShipperCity		= 'Mödling;';
		$ShipperRegion		= ';';
		$ShipperStreet		= 'Postfach;';
		$ShipperAdditionalStreet= ';';
		$ShipperStreetNr	= '330;';
		$ShipperTelephoneNr = 'h.rienesl@maillog.at;';
		$ShipperMail		= '';
		
		$TopRecords .= $FieldNo2.$ShipperName1.$ShipperName2.$ShipperName3.$ShipperName4.$ShipperCountry.$ShipperPostalCode.$ShipperCity.$ShipperRegion.$ShipperStreet.$ShipperAdditionalStreet.$ShipperStreetNr.$ShipperTelephoneNr.$ShipperMail."\r\n";	   
		   return $TopRecords;
	}
	
	public function postatEdiBody($data){
	    //$sender_address_arr = $this->ForwarderRecord['SenderAddress'];
		//$depotname = $this->DepotsName($data[ADMIN_ID]);
		//array_unshift($sender_address_arr['SenderAddress'], $this->CustomersName($data[ADMIN_ID]).' - '.$depotname['Depot_Company']);
		//$sender_address = $sender_address_arr['SenderAddress'];
		//$customerAddress = $this->userAddress($data['user_id']);
	
		
	//print_r($PARCEL_DATA);die;
	/******************************* Return Address ********************************/
	    $PARCEL_DATA = '';
		
		$FieldNo3				= '03;';
		$ShipmentNr				= substr($data['barcode'],0,40).';';
		$ReturnDebitor			= ';';
		$ReturnName1			= ';';
		$ReturnName2			= ';';
		$ReturnName3			= ';';
		$ReturnName4			= ';';
		$ReturnCountry			= ';';
		$ReturnPostalCode		= ';';
		$ReturnCity				= ';';
		$ReturnRegion			= ';';
		$ReturnStreet			= ';';
		$ReturnAdditionalStreet	= ';';
		$ReturnStreetNr			= ';';
		$ReturnTelephoneNr		= ';';
		$ReturnMail				= ';';
/******************************* Receiver  Address ********************************/	
        //$countrycode = $this->countryCode($data['country_id']);
			
		$ConsigneeName1			= $data[RECEIVER].';';
		$ConsigneeName2			= $data[CONTACT].';';
		$ConsigneeName3			= ';';
		$ConsigneeName4			= ';';
		$ConsigneeCountry		= commonfunction::sub_string($this->RecordData['rec_cncode'],0,2).';';
		$ConsigneePostalCode	= $data[ZIPCODE].';';
		$ConsigneeCity			= $data[CITY].';';
		$ConsigneeRegion		= ';';
		$ConsigneeStreet		= $data[STREET].';';
		$ConsigneeAdditionalStreet= $data[ADDRESS].$data[STREET2].';';
		$ConsigneeStreetNr		= $data[STREETNR].';';
		$ConsigneeTelephoneNr	= $data[PHONE].';';
		$ConsigneeMail			= $data[EMAIL].';';
		$ShpRefNr				= commonfunction::sub_string($data[REFERENCE],0,40).';';
		$AlternativeRefNr		= commonfunction::sub_string($data[REFERENCE],0,40).';';
		$DeliveryRemark			= 'ParcelPakket Delivery';
		
		$PARCEL_DATA .= $FieldNo3.$ShipmentNr.$ReturnDebitor.$ReturnName1.$ReturnName2.$ReturnName3.$ReturnName4.$ReturnCountry.$ReturnPostalCode.$ReturnCity.$ReturnRegion.$ReturnStreet.$ReturnAdditionalStreet.$ReturnStreetNr.$ReturnTelephoneNr.$ReturnMail.$ConsigneeName1.$ConsigneeName2.$ConsigneeName3.$ConsigneeName4.$ConsigneeCountry.$ConsigneePostalCode.$ConsigneeCity.$ConsigneeRegion.$ConsigneeStreet.$ConsigneeAdditionalStreet.$ConsigneeStreetNr.$ConsigneeTelephoneNr.$ConsigneeMail.$ShpRefNr.$AlternativeRefNr.$DeliveryRemark."\r\n";
		
		
		
		/*$select = $this->masterdb->select()
			          ->from(POSTAT_PRODUCTS,array(POSTAT_PRODUCT_ID,POSTAT_PRODUCT_NAME,POSTAT_PRODUCT_ABBR))
					  ->where(POSTAT_PRODUCT_ID.'=?','08')
					  ->where('id=?','3'); echo $select->__tostring();die;
		$result = $this->masterdb->fetchRow($select);*/
		$select = $this->masterdb->select()
			          ->from(POSTAT_PRODUCTS,array('*'))
					  ->where('product_id'.'=?','08')
					  ->where('id=?','3');
					//  echo $select->__tostring();die;
		$result = $this->masterdb->fetchRow($select);
		$product_id   = (!empty($result['product_id']))?$result['product_id']:'0';
		
		$FieldNo4	 		 = '04;';
		//$ParcelNumber = $Barcode.";";
		$Identcode    		= substr($data['barcode'],0,22).';';
		$Weight 			= number_format($data['weight'],3).';';
		$ColliRefNr	       = ';';
		if($data['weight']<=31.5){
			$ParcelType	  = 'C';
		}else{
			$ParcelType	  = 'P';
		}
		
		$PARCEL_DATA .= $FieldNo4.$Identcode.$Weight.$ColliRefNr.$ParcelType."\r\n";
		
		
		/*$select = $this->masterdb->select()
			          ->from(POSTAT_PRODUCTS,array('*'))
					  ->where('product_id'.'=?','08')
					  ->where('id=?','3');
					//  echo $select->__tostring();die;
		$result = $this->masterdb->fetchRow($select);*/
		$FieldNo5	   		= '05;';
		$ProductCode	   	= substr($result['product_id_new'],0,2);
		$PARCEL_DATA .= $FieldNo5.$ProductCode."\r\n";
		
		
		if($data['addservice_id']==7 || $data['addservice_id']==146){
			   $where = "ST.id=2"; 
			}else{
			   $where = "ST.id=0"; 
			}
		$select = $this->masterdb->select()
			          ->from(array('ST'=>POSTAT_SUB_SERVICES),array('additional_service'=>'name','ocr'=>'OCR','postat_symbol'=>'symbol','postat_verbal'=>'verbal','add_product_id'))
					  ->where($where);
			//echo $select->__tostring();die;
		$result = $this->masterdb->fetchRow($select);
		
		$FieldNo6	   		= '06;';
		$FeatureCode	   	= substr(str_pad($result['add_product_id'],3,0,STR_PAD_LEFT),0,3).';';
		$Value	   			= $data[REFERENCE].';';
		$Amount	   			= number_format($data['cod_price'],2).';';
		$Currency	   		= ($data['currency']!='')?$data['currency'].';':'EUR;';
		$CodAccountHolder	= 'Letterproduction;';
		$IBAN	   			= 'AT093502200005012612;';
		$BIC	   			= 'RVSAAT2S022;';
		$CodPaymentReason	= $data[REFERENCE].';';
		$EMail	   			= 'h.rienesl@maillog.at;';
		$PhoneNumber	   	= '02236312105';
		$PARCEL_DATA .= $FieldNo6.$FeatureCode.$Value.$Amount.$Currency.$CodAccountHolder.$IBAN.$BIC.$CodPaymentReason.$EMail.$PhoneNumber."\r\n";
		return $PARCEL_DATA;									
	}

}

