<?php

class Checkin_Model_PostnlEdi extends Zend_Custom
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
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id','service_attribute'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	   $ediBody = '';
	   $sequence = 0;
	   foreach($results as $result){
	      $this->RecordData = $result;
		  $this->ForwarderRecord = $this->ForwarderDetail();
		  $ediBody .= $this->postnlEdiBody($result,$sequence);
		  $sequence++;
	   }
		$ediBody .= 'Z001 '.count($result)."\r\n";
		$ediBody .= 'Z002 '."\r\n";
		$ediBody .= 'Z999';
		
		$sequential_number = str_pad($this->Forwarders['IFD_number']+1,6,0,STR_PAD_LEFT);
	    $fileName = 'VM'.$sequential_number;
	   return array('EdiData'=>$ediBody,'EdiFilename'=>$fileName);
	}
	
	public function postnlEdiBody($record,$serialNumber){
		$sender_company = '';
		$sender_last_name = '';
		$partyCode       = $this->ForwarderRecord['class_of_service'];
		$customerNumber  = $this->ForwarderRecord['customer_number'];
		$forwarderAddress = explode("\n",$this->ForwarderRecord['depot_address']);
		$time_string = date_parse($record[CREATE_DATE]);

		$productCode = $this->postnlsubService($record['service_attribute']);
		$senderAddress = $this->getCustomerDetails($record['user_id']);

		$houseNumber  = substr($senderAddress['address1'],strrpos($senderAddress['address1'],' ')+1);

		$streetNumber = substr($senderAddress['address1'],0,strrpos($senderAddress['address1'],' '));

		

		if(!is_null($senderAddress['company_name'])){
			$sender_company = $senderAddress['company_name'];
		}else{
			$sender_last_name = $senderAddress['last_name'];
		}
		$parcelWeightGram = $record['weight']*1000;
		$A010 = 'A010 '.date('Ymd')."\r\n";
		$A011 = 'A011 '.date('His')."\r\n";
		$AO20 = 'A020 '.$this->ForwarderRecord['version_number']."\r\n";
		$A021 = 'A021 '.$this->ForwarderRecord['version_number']."\r\n";
		$A022 = 'A022 '.$this->ForwarderRecord['service_type']."\r\n";		
		$A030 = 'A030 '.iconv('UTF-8', 'ASCII//TRANSLIT',substr($partyCode,0,4))."\r\n";  
		$A040 = 'A040 '.iconv('UTF-8', 'ASCII//TRANSLIT',substr($partyCode.$customerNumber,0,12))."\r\n"; 		
		$A060 = 'A060 '.$time_string['year'].str_pad($time_string['month'],2,'0',STR_PAD_LEFT).str_pad($time_string['day'],2,'0',STR_PAD_LEFT)."\r\n";
		$A100 = 'A100 '.iconv('UTF-8', 'ASCII//TRANSLIT', substr($customerNumber,0,8))."\r\n";
		// Sender Information Block
		$A130 = 'A130 '.iconv('UTF-8', 'ASCII//TRANSLIT', substr($forwarderAddress[0],0,35))."\r\n";
		$A139 = 'A139 '.iconv('UTF-8', 'ASCII//TRANSLIT', commonfunction::onlynumbers($forwarderAddress[1]))."\r\n"; //Mandatory Field.Sender Street Number //As per feedback 
		$A140 = 'A140 '.iconv('UTF-8', 'ASCII//TRANSLIT', commonfunction::onlynumbers($forwarderAddress[1]))."\r\n"; //Mandatory Field.Sender House Number
	    $A150 = 'A150 '.iconv('UTF-8', 'ASCII//TRANSLIT', substr(str_replace(' ','',$forwarderAddress[3]),0,35))."\r\n"; //As per feedback 
		$A151 = 'A151 '.iconv('UTF-8', 'ASCII//TRANSLIT', substr($forwarderAddress[2],0,35))."\r\n"; //As per feedback
		$A220 = 'A220 '.iconv('UTF-8', 'ASCII//TRANSLIT', substr($forwarderAddress[4],0,2))."\r\n"; //Mandatory field.Sender's ISO country code. //As per feedback
		$A230 = 'A230 '.$this->ForwarderRecord['shipping_depot_no']."\r\n"; //Fixed.
		$A999 = 'A999'."\r\n";
		$tendering_lebel = ($serialNumber==0) ? $A010.$A011.$AO20.$A021.$A022.$A030.$A040.$A060.$A100.$A130.$A139.$A140.$A150.$A151.$A220.$A230.$A999 : "";

		

		$serialNumber++;
		//DISPATCH UNIT LEVEL (V SEGMENT)
		$V010 = 'V010 V'."\r\n";
		$V020 = 'V020 '.substr($record[BARCODE],0,17)."\r\n";
		$V021 = 'V021 '.substr($record[BARCODE],0,17)."\r\n";
		$V025 = ($record[REFERENCE]) ? 'V025 '.substr($record[REFERENCE],0,35)."\r\n" : ""; 
		$V040 = ($productCode) ? 'V040 '.substr($productCode,0,5)."\r\n" : $this->ForwarderRecord['contract_number']; //This is product code // As per feedback
		$V051 = 'V051 '.$this->ForwarderRecord['sub_contract_number']."\r\n"; //Is Fixed 
		$V060 = 'V060 1'."\r\n";//'VO60 '.substr($serialNumber,0,3)."\r\n";
		$V061 = 'V061 1'."\r\n";
		
		
		$V170 = 'V170 '.iconv('UTF-8', 'ASCII//TRANSLIT', substr($this->ForwarderRecord['SenderAddress'][0],0,35))."\r\n";
		$V179 = 'V179 '.substr($this->ForwarderRecord['SenderAddress'][2],0,35)."\r\n";
		$V180 = 'V180 '.iconv('UTF-8', 'ASCII//TRANSLIT', substr($houseNumber,0,5))."\r\n";
		$V190 = 'V190 '.iconv('UTF-8', 'ASCII//TRANSLIT', substr(str_replace(' ','',$this->ForwarderRecord['SenderAddress'][4]),0,35))."\r\n";
		$V200 = 'V200 '.substr($this->ForwarderRecord['SenderAddress'][5],0,2)."\r\n";
		$V999 = 'V999'."\r\n";
		$record = $tendering_lebel.$V010.$V020.$V021.$V025.$V040.$V051.$V060.$V061.$V170.$V179.$V180.$V190.$V200.$V999;

		return $record;

	}
	protected function postnlsubService($service_attribute) {
		 $servic_attribute = ($service_attribute!='')?$service_attribute:0;
		 $servicedetail = $this->getServiceDetails($servic_attribute);
		 $internalCode = isset($servicedetail['internal_code'])?$servicedetail['internal_code']:0;
		 $select = $this->masterdb->select()
								  ->from(array('PNSS'=>POSTNL_SUB_SERVICES),array('PNSS.product_code'))
								  ->where("PNSS.internal_code='".$servicedetail['internal_code']."' AND PNSS.internal_code>0");
		//echo $select->__tostring();die;
		$result = $this->masterdb->fetchRow($select);
		if(empty($result)){
		  $select = $this->masterdb->select()
								  ->from(array('PNSS'=>POSTNL_SUB_SERVICES),array('PNSS.product_code'))
								  ->where("PNSS.service='Standard'");//echo $select->__tostring();die;
		  $result = $this->masterdb->fetchRow($select);

		}
		return $result['product_code'];

	}

}

