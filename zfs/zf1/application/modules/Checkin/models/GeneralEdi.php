<?php

class Checkin_Model_GeneralEdi extends Zend_Custom
{
   public $ForwarderRecord = array();
   public $Forwarders	= array();
    
	public function generateEDI($data){
	  $this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);
	  try{
	   $select = $this->_db->select()
							->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
							->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE))
							->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,ADDRESS,CITY,ZIPCODE,STREET2,PHONE,EMAIL,
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,'user_id'))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	    $ediBody = '';
	    $ediBody .= $this->EdiHeader(count($results));
        $counter = 1; 
		foreach($results as $result){
		    $this->RecordData = $result;
		  	$this->ForwarderRecord = $this->ForwarderDetail();
            $ediBody .= $this->EDIBody($result);
			$counter++;
		}
	   $ediBody .= $this->Forwarders['forwarder_name']."|700|".$this->Forwarders['IFD_number']."|\r\n";
	   $fileName =  $this->Forwarders['forwarder_name']."_".$this->Forwarders['IFD_number']."_D".date('Ymd')."T".date('his');
	   return array('EdiData'=>$ediBody,'EdiFilename'=>$fileName);
	}
	public function EdiHeader($total) {
		//Line Number 1
		$FileCreationDate = date('Ymd');
		$FileCreationTime = date('His');
		$consecutive_no   = $this->Forwarders['IFD_number'];
		
	   $TopRecords = "".$this->Forwarders['forwarder_name']."|100|".$FileCreationDate."|".$FileCreationTime."|".$total."|".$consecutive_no."|\r\n";
	   return $TopRecords;
	}
	/**
	*Generate EDI For All
	*Function : EDIBody()
	*Function Generate EDI for All forwarder
	**/
	public function EDIBody($data){
	    //Parcel Detail
		$PARCEL_DATA = '';
		$parcelnumber = $data[BARCODE];
		$weight		  = $data[WEIGHT];
		$servicedetail = $this->getServiceDetails($data['service_id']);
		$createdatetime = $data[CREATE_DATE];
		$reference = commonfunction::utf8Decode($data[REFERENCE]);
		$numofparcel = 1;
	    
		$PARCEL_DATA .= "".$this->Forwarders['forwarder_name']."|200|".$parcelnumber."|".$weight."|".$servicedetail['internal_code']."|".$createdatetime."|".$reference."|".$numofparcel."|\r\n";
		//Sender Detail
		 $SenderInfo  = $this->ForwarderRecord['SenderAddress'];

		$SenderName1          = $SenderInfo[0];
		$SenderName2          = $SenderInfo[1];
		$SenderStreet         = $SenderInfo[2];
		$SenderCountry        = $SenderInfo[5];
		$SenderPostcode       = $SenderInfo[4];
		$SenderCity           = $SenderInfo[3];
		
		$PARCEL_DATA .= "".$this->Forwarders['forwarder_name']."|300|".$parcelnumber."|".$SenderName1."|".$SenderName2."|".$SenderStreet."|".$SenderCountry."|".$SenderPostcode."|".$SenderCity."|\r\n";
		
		// Receiver Data
		$RecipientName1              = commonfunction::utf8Decode($data[RECEIVER]);
		$RecipientName2              = commonfunction::utf8Decode($data[CONTACT]);
		$RecipientStreet             = commonfunction::utf8Decode($data[STREET]);
		$RecipientHouseNo            = commonfunction::utf8Decode($data[STREETNR]);
		$RecipientCountry            = $data['iso_code'];
		$RecipientPostcode           = $data[ZIPCODE];
		$RecipientCity               = commonfunction::utf8Decode($data[CITY]);
		$RecipientTelephone          = $data[PHONE];
		$RecipientEmail              = $data[EMAIL];
		$RecipientComment            = $reference;
		
		$PARCEL_DATA .= "".$this->Forwarders['forwarder_name']."|500|".$parcelnumber."|".$RecipientName1."|".$RecipientName2."|".$RecipientStreet."|".$RecipientHouseNo."|".$RecipientCountry."|".$RecipientPostcode."|".$RecipientCity."|".$RecipientTelephone."|".$RecipientEmail."|".$RecipientComment."|\r\n";
		
		return $PARCEL_DATA;									
	}

}

