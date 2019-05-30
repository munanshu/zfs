<?php //ini_set('display_errors', 1);


require_once 'Olddbcustom.php';

class Oldhistory_Model_Parcelhistorymanager extends OlddbCustom

{	
	public $RecordData;
	 
	public function __construct($Request)
	{	
		parent::__construct($Request);
		$this->CheckinModelObj = new Checkin_Model_CheckinManager();
	}



	public function getParcelDetails(){  
		 
	     $barcode_id = '';
		 $lavelfilter = $this->CheckinModelObj->LevelClause();
		 $Records = array();
		 try{
		 $select = $this->oldDb->select()
									   ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id'))
									   ->where("(barcode='".$this->getData['search_barcode']."' OR tracenr_barcode='".$this->getData['search_barcode']."')");
			$result = $this->oldDb->fetchRow($select);
		if(empty($result)){
			$select = $this->oldDb->select()
								   ->from(array('EC'=>EMERGENCY_CHECKIN),array('barcode_id'))
								   ->where("old_barcode='".$this->getData['search_barcode']."'");
			$result = $this->oldDb->fetchRow($select);

		}
		if(empty($result)){
		       $select = $this->oldDb->select()
						  ->from(array('BE'=>SHIPMENT_BARCODE_EDITED),array('barcode_id'))
						  ->where("BE.barcode='".$this->getData['search_barcode']."'");
			  $result = $this->oldDb->fetchRow($select);
		 }
		 if(empty($result)){
		        $select = $this->oldDb->select()
									   ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id'))
									   ->where("(tracenr='".$this->getData['search_barcode']."')");
			   $result = $this->oldDb->fetchRow($select);
		 }

		if(!empty($result)){
		     $select = $this->oldDb->select()
	   							   ->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
								   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BT.barcode_id=BD.barcode_id",array('rec_reference','checkin_date','checkin_ip','checkin_by','driver_id','assigned_date','pickup_date','depot_invoice_number','customer_invoice_number','edi_date','manifest_number','delivery_date','received_by'))
								   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('country_id','senderaddress_id','addservice_id','original_forwarder','rec_name','rec_contact','rec_street','rec_streetnr','rec_address','rec_street2','rec_city','rec_zipcode','rec_email','rec_phone','email_notification','create_date','create_by','create_ip','quantity','goods_id'))
								   ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array('user_id','company_name','parent_id'))
								   ->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array('forwarder_name'))
								   ->joininner(array('SR'=>SERVICES),"SR.service_id=BT.service_id",array('service_name'))
								   ->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array('country_name','continent_id'))
								   ->joininner(array('SM'=>SHIPMENT_TYPE),"SM.status_id=ST.shipment_type",array('shipment_mode'))
								   // ->where(" YEAR(ST.create_date)='".$this->getData['year']."' AND BT.barcode_id=".$result['barcode_id']."".$lavelfilter);
								   ->where(" BT.barcode_id=".$result['barcode_id']."".$lavelfilter);
								   // print_r($select->__toString());die;
			$Records = $this->oldDb->fetchRow($select);
			
		}
	 
	 }catch(Exception $e){
	      $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());die;
	   }

	   
		   if(!empty($Records)){
				   $this->RecordData = $Records;
				   $this->CheckinModelObj->RecordData = $this->RecordData;
				    $this->RecordData['forwarder_details'] = $this->CheckinModelObj->ForwarderDetail();
		   }
	   return  $this->RecordData;	
	}

}



