<?php
class Application_Model_RussianPostLabel extends Zend_custom{
	  public function CreateRussianPostLabel($shipmentObj,$newbarcode=true){ 
	  	 $checkdigit = $this->RussianPostCheckDigit($shipmentObj->RecordData[TRACENR]);
		 $barcode_prefix = ($shipmentObj->RecordData['addservice_id']==115 || $shipmentObj->RecordData['addservice_id']==145) ? $shipmentObj->RecordData['forwarder_detail']['barcode_prefix'] : $shipmentObj->RecordData['forwarder_detail']['service_indicator'];
		 $shipmentObj->RecordData[BARCODE] = $barcode_prefix.$shipmentObj->RecordData[TRACENR].$checkdigit.'RU';
		 $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
		 
		 $currency = ($shipmentObj->RecordData['currency']!='')?$shipmentObj->RecordData['currency']:'EUR';
		 if($shipmentObj->RecordData['addservice_id']==7 || $shipmentObj->RecordData['addservice_id']==7){
			$cod_price = ($shipmentObj->RecordData['cod_price']>0)?$shipmentObj->RecordData['cod_price']:$shipmentObj->RecordData['shipment_worth'];
			$shipmentObj->RecordData['cod_price'] = ($currency=='RUB')?$shipmentObj->RecordData['cod_price']:commonfunction::convertCurrency($shipmentObj->RecordData['cod_price'], $currency,'RUB');
			$shipmentObj->RecordData['shipment_worth'] = ($currency=='EUR')?$shipmentObj->RecordData['cod_price']:commonfunction::convertCurrency($cod_price, $currency,'EUR');
		}else{
		   $shipmentObj->RecordData['shipment_worth'] = ($currency=='EUR')?$shipmentObj->RecordData['shipment_worth']:commonfunction::convertCurrency($shipmentObj->RecordData['shipment_worth'], $currency,'EUR');
		}		
		 $shipmentObj->RecordData['chunk_barcode'] = commonfunction::String_chunk($shipmentObj->RecordData[BARCODE],1,' ');
	  }
	/**
	 * @Function : RussianPostCheckDigit()
	 * @Description : this function assemble generate Checkdigit
     */
	public function RussianPostCheckDigit($base_val)
	{
	   $weightingFactors = array(8,6,4,2,3,5,9,7);
	   $sum=0;
	   for ($i=0; $i<strlen($base_val); $i++)
	   { 
		  $sum = $sum + (substr($base_val,$i,1)*$weightingFactors[$i]);
	   }
	   $remainder = ($sum%11);
	   return ($remainder==0) ? 5 : (($remainder==1) ? 0 : (11-$remainder));
	}
}