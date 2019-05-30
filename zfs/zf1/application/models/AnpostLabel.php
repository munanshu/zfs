<?php
class Application_Model_AnpostLabel extends Zend_custom{
	public function CreateAnpostLabel($shipmentObj,$newbarcode=true){  
	     $this->DPDdata  =  $shipmentObj->RecordData['forwarder_detail'];
		 $shipmentObj->RecordData[BARCODE] = $shipmentObj->RecordData['forwarder_detail']['barcode_prefix'].$shipmentObj->RecordData[TRACENR].$shipmentObj->RecordData['forwarder_detail']['service_indicator'];
		 $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
		 return true;
	}
	
}