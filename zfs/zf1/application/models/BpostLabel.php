<?php
class Application_Model_BpostLabel extends Zend_custom{
	  public function CreateBpostLabel($shipmentObj,$newbarcode=true){
	      $shipmentObj->RecordData[TRACENR] = commonfunction::paddingleft($shipmentObj->RecordData[TRACENR],15,0);
		  $level_id = '31';
		  
		  $contract_subcontract_no = $shipmentObj->RecordData['forwarder_detail']['contract_number'].$shipmentObj->RecordData['forwarder_detail']['sub_contract_number'];
		  $trackingbarcode_no = $level_id.$contract_subcontract_no.$shipmentObj->RecordData[TRACENR];
		  $check_digit = $this->bpost_check_digit($trackingbarcode_no);
		  $shipmentObj->RecordData[BARCODE] = $trackingbarcode_no.$check_digit;
		  $shipmentObj->RecordData['tracenr_barcode'] = $shipmentObj->RecordData[BARCODE];
	  }
	  /**
	  * Return check digit of bpost barcode
	  * Function : bpost_check_digit()
	  **/
	 public function bpost_check_digit($num){
		$checkDigit = bcmod($num,97);
		if($checkDigit==0) {
			$checkDigit = 97;
		}
		return commonfunction::paddingleft($checkDigit,2,0,STR_PAD_LEFT);
	}
}