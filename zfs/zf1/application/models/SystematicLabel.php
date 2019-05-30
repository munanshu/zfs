<?php
class Application_Model_SystematicLabel extends Zend_custom{
	  public function CreateSystematicLabel($shipmentObj,$newbarcode=true){ 
	  	 $postalcode =  commonfunction::paddingleft($shipmentObj->RecordData[ZIPCODE],7,0);
		 $shipmentObj->RecordData[BARCODE] = $shipmentObj->RecordData['forwarder_detail']['barcode_prefix'].$postalcode.$shipmentObj->RecordData[TRACENR];
		 $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];	
		 $shipmentObj->RecordData['addserviceName'] = $this->ServiceName($shipmentObj->RecordData['addservice_id']);	
	  }
}