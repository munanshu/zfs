<?php
class Application_Model_HamacherLabel extends Zend_custom{
	   public $ForwarderDetails =  array();
	  public function CreateHamacherLabel($shipmentObj,$newbarcode=true){ 
	    if($newbarcode){
	     $numberforcheckDigit = '34040'.$shipmentObj->RecordData[TRACENR];
	  	 $checkdigit = $this->checkdijit_hamacher($numberforcheckDigit);
		 $shipmentObj->RecordData[BARCODE] = $shipmentObj->RecordData['forwarder_detail']['barcode_prefix'].$shipmentObj->RecordData[TRACENR].$checkdigit;
		 $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];	
		} 
		 $shipmentObj->RecordData['Additional_Service_name'] = $this->ServiceName($shipmentObj->RecordData['addservice_id']);	
	  }
	public function checkdijit_hamacher($number){ 
  	 $j=0;
	$oddadd = 0;
	for($i=0;$i<strlen($number);$i++){
		  $numberweight = ($j%2==0)?($number[$i]*3):($number[$i]*1);
		  $oddadd = $oddadd + $numberweight;
		  $j++;
	   }
	   $modevalue = $oddadd % 10;
	   if($modevalue==0){
	      return $modevalue;
	   }
	   $checkdijit = 10-$modevalue;
	   return $checkdijit;   
  }
}