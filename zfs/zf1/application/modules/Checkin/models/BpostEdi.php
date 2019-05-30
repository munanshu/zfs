<?php

class Checkin_Model_BpostEdi extends Zend_Custom
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
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id'))
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
		  $ediBody .= $this->BpostEdiBody($result);
	   }
	   $sequential_number = commonfunction::paddingleft($this->ForwarderRecord['IFD_number']+1,5,0);
	   $ediData = $this->BpostEdiHeader($sequential_number);
	   $getEdiinfo = $ediData.$ediBody.'*END*';
	   $getEdiinfo .= commonfunction::paddingleft(count($results),8,0);
	   $fileName = $this->ForwarderRecord['customer_number'].'_'.$sequential_number;
	   return array('EdiData'=>$getEdiinfo,'EdiFilename'=>$fileName);
	}
	/**
	*Generate Bpost EDI Header
	*Function : BpostEdiHeader()
	*Function Generate Bpost EDI Header
	**/
	public function BpostEdiHeader($sequential_number){
		$line1 = commonfunction::paddingRight('*BPI_IN*',20," ").commonfunction::paddingRight($this->ForwarderRecord['customer_number'],8,"0").commonfunction::paddingRight($this->ForwarderRecord['version_number'],8," ").$sequential_number."\r\n";
		return $line1;
	}
	
	/**
	*Generate EDI For Bpost
	*Function : BpostEdiBody()
	*Function Generate EDI for Bpost forwarder
	**/
	public function BpostEdiBody($data){
		$identification_code = $this->ForwarderRecord['service_indicator'];  //Now service_indicator
		
		$content_type = '01';//$this->_Forwardersrec['content_type']; 
		
		$account_no   = commonfunction::sub_string(commonfunction::paddingRight($this->ForwarderRecord['customer_number'],8,"0"),0,8);
		
		$vas_code     = commonfunction::sub_string(commonfunction::paddingRight('',3," "),0,3);  //$this->_Forwardersrec['vas_code']
		
		$sender_data = $this->getCustomerDetails($data['user_id']);
		
		$sender_name = trim(commonfunction::utf8Decode($sender_data['name']));
					   
		$sender_name = commonfunction::sub_string(commonfunction::just_clean($sender_name),0,40);
		
		$sender_name = commonfunction::paddingRight($sender_name,40," ");
		
		$sender_department = commonfunction::paddingRight(" ",40," ");
		
		$sender_contact = commonfunction::sub_string(trim(commonfunction::utf8Decode(commonfunction::just_clean($sender_data['company_name']))),0,40);
		$sender_contact = commonfunction::paddingRight($sender_contact,40," ");
		
		$sender_place   = commonfunction::paddingRight(" ",40," ");
		
		$sender_street  = commonfunction::sub_string(trim(commonfunction::utf8Decode(commonfunction::just_clean($sender_data['address1']))),0,40);
		$sender_street 	= commonfunction::paddingRight($sender_street,40," ");
		
		$sender_house	= commonfunction::paddingRight(" ",8," ");
		
		$sender_box	   	= commonfunction::paddingRight(" ",8," ");
		
		$sender_zipcode = commonfunction::just_clean($sender_data['postalcode']);
		$sender_zipcode = commonfunction::sub_string(trim($sender_zipcode),0,8);
		$sender_zipcode = commonfunction::paddingRight($sender_zipcode,8," ");
		
		$sender_city    = commonfunction::just_clean($sender_data['city']);
		$sender_city    = commonfunction::sub_string(trim(commonfunction::utf8Decode($sender_city)),0,40);
		$sender_city	= commonfunction::paddingRight($sender_city,40," ");
		
		$sender_country = commonfunction::sub_string(trim(commonfunction::just_clean($sender_data['cncode'])),0,3);
		$sender_country	= commonfunction::paddingRight($sender_country,3," ");
		
		$sender_phone   = commonfunction::sub_string(trim($sender_data['phoneno']),0,20);
		$sender_phone	= commonfunction::paddingRight($sender_phone,20," ");
		
		$sender_mail    = commonfunction::sub_string(trim($sender_data['email']),0,50);
		$sender_mail	= commonfunction::paddingRight($sender_mail,50," ");
		
		$sender_mobile	= commonfunction::paddingRight(" ",20," ");
		
		$barcode         = commonfunction::paddingRight(commonfunction::sub_string($data['barcode'],0,40),40," ");
		
		
		$ReceiverName    = commonfunction::sub_string(trim(commonfunction::utf8Decode($data[RECEIVER])),0,40);
		$ReceiverName    = commonfunction::just_clean($ReceiverName);
		$ReceiverName    = commonfunction::paddingRight($ReceiverName,40," ");
		
		$ReceiverDept    = commonfunction::paddingRight(" ",40," ");
		
		$ReceiverContact = commonfunction::sub_string(trim(commonfunction::utf8Decode($data[CONTACT])),0,40);
		$ReceiverContact = commonfunction::just_clean($ReceiverContact);
		$ReceiverContact = commonfunction::paddingRight($ReceiverContact,80," ");
		
		$ReceiverStreet	 = commonfunction::sub_string(trim(commonfunction::utf8Decode($data[STREET])),0,40);
		$ReceiverStreet  = commonfunction::just_clean($ReceiverStreet);
		$ReceiverStreet	 = commonfunction::paddingRight($ReceiverStreet,40," ");
		
		$ReceiverHouse	 = commonfunction::sub_string(trim(commonfunction::utf8Decode($data[STREETNR])),0,16);
		$ReceiverHouse   = commonfunction::just_clean($ReceiverHouse);
		$ReceiverHouse	 = commonfunction::paddingRight($ReceiverHouse,16," ");
		
		$ReceiverZip     = commonfunction::sub_string(trim($data[ZIPCODE]),0,8);
		$ReceiverZip     = commonfunction::just_clean($ReceiverZip);
		$ReceiverZip     = commonfunction::paddingRight($ReceiverZip,8," ");
		
		$ReceiverCity    = commonfunction::sub_string(trim(commonfunction::utf8Decode($data[CITY])),0,40);
		$ReceiverCity    = commonfunction::just_clean($ReceiverCity);
		$ReceiverCity    = commonfunction::paddingRight($ReceiverCity,40," ");
		
		$ReceiverCountry = commonfunction::sub_string(trim($this->RecordData['rec_cncode']),0,3);
		$ReceiverCountry = commonfunction::just_clean($ReceiverCountry);
		$ReceiverCountry = commonfunction::paddingRight($ReceiverCountry,3," ");
		
		$ReceiverPhone	 = commonfunction::sub_string(trim($data[PHONE]),0,20);
		$ReceiverPhone   = commonfunction::just_clean($ReceiverPhone);
		$ReceiverPhone	 = commonfunction::paddingRight($ReceiverPhone,20," ");
		
		
		$ReceiverEmail	 = commonfunction::sub_string(trim($data[EMAIL]),0,50);
		$ReceiverEmail	 = commonfunction::paddingRight($ReceiverEmail,50," ");
		
		$ReceiverMobile	 =  commonfunction::paddingRight(" ",20," ");
		
		$weight = $data[WEIGHT]*1000;
		$weight = commonfunction::paddingleft($weight,7,"0");
		$EdiData = $identification_code.$content_type.$barcode.$account_no.$vas_code.$sender_name.$sender_department.$sender_contact.$sender_place.$sender_street.$sender_house.
		           $sender_box.$sender_zipcode.$sender_city.$sender_country.$sender_phone.$sender_mail.$sender_mobile.$ReceiverName.$ReceiverDept.$ReceiverContact.$ReceiverStreet.
				   $ReceiverHouse.$ReceiverZip.$ReceiverCity.$ReceiverCountry.$ReceiverPhone.$ReceiverEmail.$ReceiverMobile.$weight.'000'."\r\n";
		return $EdiData;
	}

}

