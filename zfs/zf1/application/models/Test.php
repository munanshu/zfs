<?php 

class Application_Model_Test extends Zend_custom
{
    
    
	public function sendEmailforYesterday(){
		try{
		    $select = $this->_db->select()
									->from(array('BT' =>SHIPMENT_BARCODE),array('barcode_id'))
									->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array(''))
									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(''))
									->where("BT.checkin_status='1' AND BT.delete_status='0' AND ST.delete_status='0' AND ST.rec_email!='' AND ST.email_notification='1' AND DATE(BD.checkin_date)='2017-04-03'");
		   $records =  $this->getAdapter()->fetchAll($select);
		    global $EmailObj;
		   foreach($records as $record){
		      $this->gerData =  $record;
			  $EmailObj->emailData = $this->getEmailData($trecord['barcode_id']);
		      echo "<pre>";print_r($EmailObj->emailData);die;
		   }
		   //$EmailObj->emailData = $this->getEmailData($this->getData['barcode_id']);
		    //$EmailObj->checkinMail();
		   echo "<pre>";print_r($records);die;
		}catch(Exception $e){
		  echo $e->getMessage() ;die;
		}
	}
}