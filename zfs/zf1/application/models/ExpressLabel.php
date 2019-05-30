<?php
class Application_Model_ExpressLabel extends Zend_custom{
	  public function CreateExpressLabel($shipmentObj,$newbarcode=true){
	       $shipmentObj->RecordData[TRACENR] = commonfunction::paddingleft($shipmentObj->RecordData[TRACENR],10,0);
	       $shipmentObj->RecordData[BARCODE] =  'EXPNL'.$shipmentObj->RecordData[TRACENR];
		   $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
		   
		   $serviceDetail = $this->getServiceDetails($shipmentObj->RecordData['addservice_id'],1);
		   $serviceAttrib = $this->getServiceDetails($shipmentObj->RecordData['service_attribute'],1);
		   $shipmentObj->RecordData['addservice_name'] = isset($serviceDetail['service_name'])?$serviceDetail['service_name']:'';
		   
		   $shipmentObj->RecordData['service_attarib'] = isset($serviceDetail['service_name'])?$serviceDetail['service_name']:'';
	  }
}