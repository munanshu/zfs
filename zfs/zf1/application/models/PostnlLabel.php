<?php
class Application_Model_PostnlLabel extends Zend_custom{
	  public $DetailsPostnl =  array();
	  public function CreatePostnlLabel($shipmentObj,$newbarcode=true){
			$this->DetailsPostnl  =  $shipmentObj->RecordData['forwarder_detail'];
			$shipmentObj->RecordData[BARCODE] =  $this->DetailsPostnl['barcode_prefix'].$this->DetailsPostnl['class_of_service'].$shipmentObj->RecordData[TRACENR] ;
			
			$shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
		
			$this->postnlProduct($shipmentObj);
			if($shipmentObj->RecordData['add_service'] == 3085){
				$shipmentObj->RecordData['PageNo'] = ($shipmentObj->RecordData[QUANTITY]>1)?'Item '.commonfunction::stringReplace('/',' of ',$shipmentObj->RecordData['ShipmentCount']) : '1 Item';
			}else{
				$shipmentObj->RecordData['PageNo'] = 'Collo '.commonfunction::stringReplace('/',' of ',$shipmentObj->RecordData['ShipmentCount']);
			}
	  }
	  public function postnlProduct($shipmentObj) {
	     $servic_attribute = ($shipmentObj->RecordData['service_attribute']!='')?$shipmentObj->RecordData['service_attribute']:0;
		 $servicedetail = $this->getServiceDetails($servic_attribute);
		 $internalCode = isset($servicedetail['internal_code'])?$servicedetail['internal_code']:0;
		 $select = $this->masterdb->select()
								  ->from(array('PNSS'=>POSTNL_SUB_SERVICES),array('PNSS.product_code'))
								  ->where("PNSS.internal_code='".$servicedetail['internal_code']."' AND PNSS.internal_code>0");
		//echo $select->__tostring();die;
		$result = $this->masterdb->fetchRow($select);

		$shipmentObj->RecordData['add_service'] = isset($result['product_code'])?$result['product_code']:'0';
		return true;
	}
}