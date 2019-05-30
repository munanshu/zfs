<?php

class Checkin_Model_AnpostEdi extends Zend_Custom
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
        $counter = 1; 
		$ediBody .= $this->EDIHeader();
		foreach($results as $result){
		    $this->RecordData = $result;
		  	$this->ForwarderRecord = $this->ForwarderDetail();
            $ediBody .= $this->AnpostEDIBody($result);
			$counter++;
		}
	   $fileName = "Anpost_".$this->Forwarders['IFD_number']."_D".date('Ymd')."T".date('his');
	   return array('EdiData'=>$ediBody,'EdiFilename'=>$fileName);
	}
	public function EDIHeader(){
	   $_nxtcol   	 = "\t";

	   	$_nxtrow  	 = "\n";
		// EDI Header
		$ediContents = "\"Parcel Number \"".$_nxtcol.

						"\"RefNr \"".$_nxtcol.

						"\"Name \"".$_nxtcol.

						"\"Name 2 \"".$_nxtcol.

						"\"Street \"".$_nxtcol.

						"\"House Number \"".$_nxtcol.

						"\"Address \"".$_nxtcol.

						"\"Street 2 \"".$_nxtcol.

						"\"Postcode \"".$_nxtcol.

						"\"City \"".$_nxtcol.

						"\"Country \"".$_nxtcol.

						"\"Weight \"".$_nxtcol.$_nxtrow;
		return $ediContents;				
	}
	/**
	*Generate EDI For Bpost
	*Function : AnpostEDIBody()
	*Function Generate EDI for Anpost forwarder
	**/
	public function AnpostEDIBody($data){
	    $contents = '';
		$_nxtcol  = "\t";
	   	$_nxtrow  = "\n";
		$contents  = "\"" . commonfunction::stringReplace("\"", "\"\"", $data[BARCODE]) . "\"" . $_nxtcol;
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[REFERENCE])) . "\"" . $_nxtcol;
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[RECEIVER])) . "\"" . $_nxtcol;
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[CONTACT])) . "\"" . $_nxtcol;
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[STREET])) . "\"" . $_nxtcol;
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[STREETNR])) . "\"" . $_nxtcol;
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[ADDRESS])) . "\"" . $_nxtcol;
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[STREET2])) . "\"" . $_nxtcol;
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[ZIPCODE])) . "\"" . $_nxtcol;
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[CITY])) . "\"" . $_nxtcol;
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($this->RecordData['rec_country_name'])) . "\"" . $_nxtcol;
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[WEIGHT])) . "\"" . $_nxtcol;
		return $contents.$_nxtrow;
	 }
	
}

