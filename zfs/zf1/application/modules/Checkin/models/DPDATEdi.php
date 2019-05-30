<?php
class Checkin_Model_DPDATEdi extends Zend_Custom
{
   public $ForwarderRecord = array();
   public $Forwarders	= array();
	public $_DateCreated = NULL;
	public $_CreatedTime = NULL;
   public function generateEDI($data){
       $this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);
	   $this->_DateCreated = date('Ymd');
	   $this->_CreatedTime = date('His');
	   $TotalEdiData = '';
	  try{
	   $select = $this->_db->select()
							->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
							->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE))
							->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,CITY,ZIPCODE,PHONE,EMAIL,STREET2,ADDRESS,
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		$TotalEdiData .= $this->DPDATEdiHeader();
		foreach($results as $result){
		    $this->RecordData = $result;
			$this->ForwarderRecord = $this->ForwarderDetail();
			$TotalEdiData .=  $this->DPDATEdiBody($result);
		}
		
		$TotalEdiData .="999|**EOF**|";
	    $filename = "A".$this->_DateCreated.".".$this->Forwarders['depot_number'].".".$this->Forwarders['customer_number'].".".str_pad($this->Forwarders['IFD_number'],2,'0',STR_PAD_LEFT);
		return array('EdiData'=>$TotalEdiData,'EdiFilename'=>$filename);
   }
   	/**
	 * Generate DPD EDI Header
	 * Function : DPDEdiHeader()
	 * Function Generate DPD EDI Header
	 **/
	public function DPDATEdiHeader() {
		//Line Number 1
		$Feldtrenner 		=	'|';	 
		$Listtrenner 		=	'~';	 
		$Versionsnummer		=	'32';	 
		 $TopRecords = '';
		 $TopRecords .=  $Feldtrenner.'|'.$Listtrenner.'|'.$Versionsnummer.'|'."\r\n";
		  	
		$recordType 		= '110';
		$create_date 		= date('Ymdhi');
		$DespatchDepot    	= $this->Forwarders['depot_number'];
		$customer_number 	= $this->Forwarders['customer_number'];
		$customer_name		= 'Maparexx';
		$customer_email		= 'klantenservice@parcel.nl';
		$feedback_mode		= '1';
	   
	    $TopRecords .=  $recordType.'|'.$create_date.'|'.$DespatchDepot.'|'.$customer_number.'|'.$customer_name.'|'.$customer_email.'|'.$feedback_mode.'|'."\r\n"; 
	   
	   return $TopRecords;
	}
	
	/**
	*Generate EDI For DPD
	*Function : DPDEdiBody()
	*Function Generate Generate EDI for DPD forwarder
	**/
	public function DPDATEdiBody($data){
		$PARCEL_DATA = '';
		$recordType 		= '212';
		$ordertype 			= 'DPD';
		$ServiceCode    	= ($data['weight']<=3)?'327':'328';
		$order_date		    = date('Ymd');
		$tracking_number	= substr($data[TRACENR_BARCODE],0,14);
		$reference_number	= substr(utf8_decode($data[REFERENCE]),0,20);
		$weight				= $data['weight']*1000;		//  Weight in gram
		$receiver_name		= substr(utf8_decode($data[RECEIVER]),0,35);
		$receiver_contact	= substr(utf8_decode($data[CONTACT]),0,35);
		$receiver3			= '';
		$receiver4			= substr($data[STREET2].' '.$data[ADDRESS],0,35);
		$rec_street 		= substr(utf8_decode($data[STREET]).' '.$data[STREETNR],0,35);
		$rec_country		= $this->RecordData['rec_cncode']; 
		$rec_zipcode		= substr($data[ZIPCODE],0,9);
		$rec_address		= '';
		$rec_city			= substr(utf8_decode($data[CITY]),0,25);
		$rec_region			= '';
		$phonenumber		= preg_replace("/[^0-9]+/", "",$data[PHONE]);
		$rec_phone			= ($phonenumber!='')?'+'.$phonenumber:'';
		$rec_email			= $data[EMAIL];
		$remark				= utf8_decode($data[REFERENCE]);
		$DPDAtt1			= '';
		$DPDAtt2			= '';
		$DPDAtt3			= ''; 
		$DPDAtt4			= '';
		$DPDAtt5			= '';
		
		$PARCEL_DATA .= $recordType.'|'.$ordertype.'|'.$ServiceCode.'|'.$order_date.'|'.$tracking_number.'|'.$reference_number.'|'.$weight.'|'.$receiver_name.'|'.$receiver_contact.'|'.$receiver3.'|'.$receiver4.'|'.$rec_street.'|'.$rec_country.'|'.$rec_zipcode.'|'.$rec_city.'|'.$rec_region.'|'.$rec_phone.'|'.$rec_email.'|'.$remark.'|'.$DPDAtt1.'|'.$DPDAtt2.'|'.$DPDAtt3.'|'.$DPDAtt4.'|'.$DPDAtt5.'|'."\r\n"; 
	
		return $PARCEL_DATA;									
	}
}

