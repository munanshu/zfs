<?php

class Checkin_Model_DHLEdi extends Zend_Custom
{
	public $ForwarderRecord = array();
    public $Forwarders	= array();
	public $inputData = array();
	public function generateEDI($data){ 
	  $this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);
	  $this->inputData = $data;
	  try{
	   $select = $this->_db->select()
							->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
							->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE))
							->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,ADDRESS,CITY,ZIPCODE,PHONE,EMAIL,STREET2,ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id','service_attribute'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	   $ediBody = '';
	   $counter = 0;
	   $TotalEdiData = '';
	   foreach($results as $result){
	      $counter++;
	      $this->RecordData = $result;
		  $this->ForwarderRecord = $this->ForwarderDetail();//echo "<pre>";print_r($this->ForwarderRecord);die;
		  $ediBody .= $this->DHLEdiBody($result,$counter);
	   }
		$Participation_of_customer = $this->ForwarderRecord['service_indicator'];
		$productcode 	= '01';
		$order_number = $this->ForwarderRecord['customer_number'].$productcode.$Participation_of_customer;
		$filename = $this->ForwarderRecord['customer_number']."_".$Participation_of_customer."_".$productcode."_".commonfunction::paddingleft($this->ForwarderRecord['IFD_number'],3,0)."_".date("Ymd");
		$TotalEdiData .= $this->DHLEdiHeader($filename);
		$TotalEdiData .=  $ediBody;
		$TotalEdiData .= $this->DHLEDIFooter($counter);
		//print_r( array('EdiData'=>$TotalEdiData,'EdiFilename'=>$filename));die;
	   return array('EdiData'=>$TotalEdiData,'EdiFilename'=>$filename);
	}
	
	/**
	 * Generate DHL EDI Header
	 * Function : DHLEdiHeader()
	 * Function Generate DHL EDI Header
	 **/
	public function DHLEdiHeader($filename) {
	     $customer_number 	= $this->ForwarderRecord['customer_number'];
		 $icr_number 		=  $this->ForwarderRecord['IFD_number'];
		 $sender_of_data 	=  $customer_number;
		 $TopRecords = "787|12|100|".$icr_number."|".date("Ymd")."|".date("hi")."|".$customer_number."|DPAG-EDICC|VLSSOFT|".$filename."|".date("Ymdhms")."|1|\r\n";
		$transpost_order_reference = 1;
		$participation 			= $this->ForwarderRecord['service_indicator'];
		$basenumber 			= $this->ForwarderRecord['contract_number'];
		$productcode = 'V01';
		$order_number 				= $customer_number.$productcode.$participation;
		$acceptance = '03';
		$placeof_posting = '6108';
		$customer_reference = '';
		$reference_number = '';
		$orderCode = '';
		$transpost_ordertext = '';
		$invoice_text = '';
		$reservedfor_internal = '';
 		$reservedfor_internal1 = '';
 		$TopRecords .= "787|12|200|".$transpost_order_reference."|".$icr_number."|".date("Ymd")."|".date("his")."|".$customer_number."||".$productcode."|".$participation."|".$acceptance."|".$placeof_posting."||||||||\r\n";
 		//code 210 Customer Data
 		$customerdata 		= $this->getCustomerDetails(188);
 		//$this->RecordData['SenderAddress'] =  array($sender[0],'Warschauerstr 8','48455 Bad Bentheim','Germany');
		if(isset($this->inputData['special_edi']) && $customer_number=='6216630230'){
			$cus_refrenceNumber 	= $customer_number;
			$cumpany_name 		= 'maparexx GbR';
			$cus_address1	 	= 'Mülheimer Straße 26';
			$cus_address2 		= '';
			$cus_city 			= 'Troisdorf';
			$cus_postalcode 	= '53840';
			$cus_countrycode 	= 'DE';
			$cus_cus_name 		= substr('Frank Breuer',0,17);
			$cus_phone 			= '02241-973180';
			$cus_email 			= 'parcel@maparexx.de';
		}else{
		    $cus_refrenceNumber 	= $customer_number;
			$cumpany_name 		= $customerdata['company_name'];
			$cus_address1	 	= 'Warschauerstr 8';
			$cus_address2 		= '';
			$cus_city 			= 'Bad Bentheim';
			$cus_postalcode 	= '48455';
			$cus_countrycode 	= 'DE';
			$cus_cus_name 		= '';
			$cus_phone 			= '05924-8357';
			$cus_email 			= 'info@logicparcel.net';
		}
		   /* $cus_refrenceNumber 	= $customer_number;
			$cumpany_name 		= $this->ForwarderRecord['SenderAddress'][0];
			$cus_cus_name 		= $this->ForwarderRecord['SenderAddress'][1];
			$cus_address1	 	= $this->ForwarderRecord['SenderAddress'][2];
			$cus_address2 		= '';
			$cus_city 			= $this->ForwarderRecord['SenderAddress'][3];
			$cus_postalcode 	= $this->ForwarderRecord['SenderAddress'][4];
			$cus_countrycode 	= $this->ForwarderRecord['SenderAddress'][5];
			
 		if(isset($this->ForwarderRecord['separate_tracking']) && $this->ForwarderRecord['separate_tracking']==1){
			$cus_phone 			= '02241-973180';
			$cus_email 			= 'parcel@maparexx.de';
   		}else{
			$cus_phone 			= '05924-8357';
			$cus_email 			= 'info@logicparcel.net';
	}*/
		   

	    $TopRecords .= "787|12|210|".$cus_refrenceNumber."|".$cumpany_name."|||".$cus_address1."||".$cus_address2."|".$cus_city."|".$cus_postalcode."|".$cus_countrycode."|".$cus_cus_name."||".$cus_phone."|||".$cus_email."||||||||\r\n";

	   

	   return $TopRecords;

	}
	
	public function DHLEdiBody($data,$counter){
		   $customer_number 		= $this->ForwarderRecord['customer_number'];
		  //Code 300 Shipment Data 
		  	$no_parcels = '1';
			$no_pallets = '1';
			if(commonfunction::sub_string(trim($data[REROUTE_BARCODE]),18,3)!='0000' && commonfunction::sub_string(trim($data[REROUTE_BARCODE]),21,3)!='000'){
			    $productkey = '1689'; 

			}else{
				$productkey = '60031';
			}	

		   $PARCEL_DATA = "787|12|300|".$counter."|".$data[BARCODE]."|".$data['weight']."||1||1|".$data[REFERENCE]."|||||||".$data[REROUTE_BARCODE]."|".$productkey."||||||\r\n";
		   //Code 310 Recipient Data
		    $country_code = $this->RecordData['rec_cncode'];
			$rec_name 		= commonfunction::sub_string(commonfunction::utf8Decode($data[RECEIVER]),0,35);
			$rec_contact		= commonfunction::utf8Decode($data[CONTACT]);
			$rec_street	 	= commonfunction::sub_string(commonfunction::utf8Decode(commonfunction::stringReplace('ß','ss',$data[STREET])),0,35);
			$rec_streetnr 	= commonfunction::sub_string($data[STREETNR],0,5);
			$rec_address 	= commonfunction::utf8Decode(commonfunction::stringReplace('ß','ss',$data[ADDRESS]));
			$rec_street2	= commonfunction::utf8Decode(commonfunction::stringReplace('ß','ss',$data[STREET2]));
			$rec_city 			= commonfunction::sub_string(commonfunction::utf8Decode($data[CITY]),0,35);
			$rec_zipcode 	= commonfunction::sub_string($data[ZIPCODE],0,17);
			$rec_phone 			= $data[PHONE];
			$rec_email 			= $data[EMAIL];
			$refrenceNumber		= $data[REFERENCE];

		   $PARCEL_DATA .= "787|12|310|".$counter."|".$refrenceNumber."|".$rec_name."|".$rec_contact."||".$rec_street."|".$rec_streetnr."|".commonfunction::sub_string(trim($rec_address.' '.$rec_street2),0,35)."|".$rec_city."|".$rec_zipcode."|".$country_code."||".commonfunction::sub_string($rec_contact,0,35)."|".$rec_phone."|||".$rec_email."|||||||||||||||||||||||||||||\r\n";

		   //Code 330 Service Data(Conditional)

		   if($data['service_id']==7 || $data['service_id']==146){
			   $service_code = 'ZL01';
			   $service_attribute = 'N';
			   $codAmount = $data['cod_price'];
			   $currency  = ($data['currency']!='')?$data['currency']:'EUR';
			   $PARCEL_DATA .= "787|12|330|".$counter."|".$service_code."|".$service_attribute."|".$codAmount."|".$currency."|||||||||\r\n";

		  }
		  //Code 400
		    $packeging_code = 'PK';
			$waight  = $data['weight'];
			$barcode  = $data[BARCODE];
		    $PARCEL_DATA .= "787|12|400|".$counter."|1|".$packeging_code."|".$waight."|||||".$barcode."|".$data[REFERENCE]."|\r\n";
		return $PARCEL_DATA;									

	}
	
	public function DHLEDIFooter($counter){
	     //Code 500  
		 $PARCEL_DATA = "787|12|500|1|".(($counter * 3) + 3)."|\r\n";
	     $icr_number 		=  $this->ForwarderRecord['IFD_number'];
		 //Code 600
		 $PARCEL_DATA .= "787|12|600|".$icr_number."|1|\r\n"; 
		return $PARCEL_DATA;     

	}
	
	
	
	public function generateDHLGlobalEDI($data){ 
	  $this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);
	  $this->inputData = $data;
	  try{
	   $select = $this->_db->select()
							->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
							->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE))
							->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,ADDRESS,CITY,ZIPCODE,PHONE,EMAIL,STREET2,ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id','service_attribute'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	   $ediBody = '';
	   $counter = 0;
	   $TotalEdiData = '';
	   foreach($results as $result){
	       $counter++;
	      $this->RecordData = $result;
		  $this->ForwarderRecord = $this->ForwarderDetail();//echo "<pre>";print_r($this->ForwarderRecord);die;
		  $ediBody .= $this->DHLWltpacketEdiBody($result,$counter);
	   }
		$Participation_of_customer = '53';
		$productcode 	= $this->ForwarderRecord['service_indicator'];
		$order_number = $this->ForwarderRecord['customer_number'].$productcode.$Participation_of_customer;
		$filename = $this->ForwarderRecord['customer_number']."_".$productcode."_".$Participation_of_customer."_".commonfunction::paddingleft($this->ForwarderRecord['IFD_number'],3,0)."_".date("Ymd");
		$TotalEdiData .= $this->DHLWltpacketEdiHeader($filename);
		$TotalEdiData .=  $ediBody;
		$TotalEdiData .= $this->DHLWltpacketFooter($counter);
		
	   return array('EdiData'=>$TotalEdiData,'EdiFilename'=>$filename);
	}
	/**
	 * Generate DHL Weltpacket EDI Header
	 * Function : DHLWltpacketEdiHeader()
	 * Function Generate DHL EDI Header
	 **/
	public function DHLWltpacketEdiHeader($filename) {
	     $customer_number 	= $this->ForwarderRecord['customer_number'];
		 $icr_number 		=  $this->ForwarderRecord['IFD_number'];
		 $sender_of_data 	=  $customer_number;
		 $TopRecords = "787|12|100|".$icr_number."|".date("Ymd")."|".date("hi")."|".$customer_number."|DPAG-EDICC|VLSSOFT|".$filename."|".date("Ymdhms")."|1|\r\n";
		$transpost_order_reference = 1;
		$participation 			= $this->ForwarderRecord['service_indicator'];
		$basenumber 			= $this->ForwarderRecord['contract_number'];
		$productcode = 'V53';
		$order_number 				= $customer_number.$productcode.$participation;
		$acceptance = '03';
		$placeof_posting = '6108';
		$customer_reference = '';
		$reference_number = '';
		$orderCode = '';
		$transpost_ordertext = '';
		$invoice_text = '';
		$reservedfor_internal = '';
 		$reservedfor_internal1 = '';
 		$TopRecords .= "787|12|200|".$transpost_order_reference."|".$icr_number."|".date("Ymd")."|".date("his")."|".$customer_number."||".$productcode."|".$participation."|".$acceptance."|".$placeof_posting."||||||||\r\n";
 		//code 210 Customer Data
 		$customerdata 		= $this->getCustomerDetails(188);
		    $cus_refrenceNumber = $customer_number;
			$cumpany_name 		= $customerdata['company_name'];
			$cus_address1	 	= 'Warschauerstr 8';
			$cus_address2 		= '';
			$cus_city 			= 'Bad Bentheim';
			$cus_postalcode 	= '48455';
			$cus_countrycode 	= 'DE';
			$cus_cus_name 		= '';
			$cus_phone 			= '05924-8357';
			$cus_email 			= 'info@logicparcel.net';

	    $TopRecords .= "787|12|210|".$cus_refrenceNumber."|".$cumpany_name."|||".$cus_address1."||".$cus_address2."|".$cus_city."|".$cus_postalcode."|".$cus_countrycode."|".$cus_cus_name."||".$cus_phone."|||".$cus_email."||||||||\r\n";

	   

	   return $TopRecords;

	}
	
	public function DHLWltpacketEdiBody($data,$counter){
		   $customer_number 		= $this->ForwarderRecord['customer_number'];
		  //Code 300 Shipment Data 
		  	$no_parcels = '1';
			$no_pallets = '1';
			if(commonfunction::sub_string(trim($data[REROUTE_BARCODE]),18,3)!='0000' && commonfunction::sub_string(trim($data[REROUTE_BARCODE]),21,3)!='000'){
			    $productkey = '1689'; 

			}else{
				$productkey = '60031';
			}	

		   $PARCEL_DATA = "787|12|300|".$counter."|".$data[BARCODE]."|".$data['weight']."||1||1|".$data[REFERENCE]."|||||||".$data[REROUTE_BARCODE]."|".$productkey."||||||\r\n";
		   //Code 310 Recipient Data
		    $country_code = $this->RecordData['rec_cncode'];
			$rec_name 		= commonfunction::sub_string(commonfunction::utf8Decode($data[RECEIVER]),0,35);
			$rec_contact		= commonfunction::utf8Decode($data[CONTACT]);
			$rec_street	 	= commonfunction::sub_string(commonfunction::utf8Decode(commonfunction::stringReplace('ß','ss',$data[STREET])),0,35);
			$rec_streetnr 	= commonfunction::sub_string($data[STREETNR],0,5);
			$rec_address 	= commonfunction::utf8Decode(commonfunction::stringReplace('ß','ss',$data[ADDRESS]));
			$rec_street2	= commonfunction::utf8Decode(commonfunction::stringReplace('ß','ss',$data[STREET2]));
			$rec_city 			= commonfunction::sub_string(commonfunction::utf8Decode($data[CITY]),0,35);
			$rec_zipcode 	= commonfunction::sub_string($data[ZIPCODE],0,17);
			$rec_phone 			= $data[PHONE];
			$rec_email 			= $data[EMAIL];
			$refrenceNumber		= $data[REFERENCE];

		   $PARCEL_DATA .= "787|12|310|".$counter."|".$refrenceNumber."|".$rec_name."|".$rec_contact."||".$rec_street."|".$rec_streetnr."|".commonfunction::sub_string($rec_address.' '.$rec_street2,0,35)."|".$rec_city."|".$rec_zipcode."|".$country_code."||".commonfunction::sub_string($rec_contact,0,35)."|".$rec_phone."|||".$rec_email."|||||||||||||||||||||||||||||\r\n";

		   //Code 330 Service Data(Conditional)

		   if($data['service_id']==7 || $data['service_id']==146){
			   $service_code = 'ZL01';
			   $service_attribute = 'N';
			   $codAmount = $data['cod_price'];
			   $currency  = ($data['currency']!='')?$data['currency']:'EUR';
			   $PARCEL_DATA .= "787|12|330|".$counter."|".$service_code."|".$service_attribute."|".$codAmount."|".$currency."|||||||||\r\n";

		  }
		  //Code 400
		    $packeging_code = 'PK';
			$waight  = $data['weight'];
			$barcode  = $data[BARCODE];
		    $PARCEL_DATA .= "787|12|400|".$counter."|1|".$packeging_code."|".$waight."|||||".$barcode."|".$data[REFERENCE]."|\r\n";
		return $PARCEL_DATA;									

	}
	
	public function DHLWltpacketFooter($counter){
	     //Code 500  
		 $PARCEL_DATA = "787|12|500|1|".(($counter * 3) + 3)."|\r\n";
	     $icr_number 		=  $this->ForwarderRecord['IFD_number'];
		 //Code 600
		 $PARCEL_DATA .= "787|12|600|".$icr_number."|1|\r\n"; 
		return $PARCEL_DATA;     

	}
	
}

