<?php
class Application_Model_WwplLabel extends Zend_custom{
	  public function CreateWwplLabel($shipmentObj,$newbarcode=true){
	      $shipmentObj->RecordData[BARCODE] = $shipmentObj->RecordData['forwarder_detail']['barcode_prefix'].$shipmentObj->RecordData[TRACENR];
		  $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
	  }
}