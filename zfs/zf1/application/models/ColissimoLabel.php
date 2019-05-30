<?php
class Application_Model_ColissimoLabel extends Zend_Custom{
       public function CreateColissimoLabel($shipmentObj,$newbarcode=true){
		    $serviceCode =  $shipmentObj->RecordData['forwarder_detail']['service_type'];
			
			$checkDigit = $this->getColissiomoCheckDigit($shipmentObj->RecordData[TRACENR]);
			$shipmentObj->RecordData[BARCODE] = $serviceCode.$shipmentObj->RecordData[TRACENR].$checkDigit;
			$shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
			
			$shipmentObj->RecordData['print_barcode'] = $serviceCode.'  '.commonfunction::String_chunk($shipmentObj->RecordData[TRACENR],5,'  ').'  '.$checkDigit;
			
			//Reroute Barcode
			$shipmentObj->RecordData['CustomerNo'] = $shipmentObj->RecordDat['forwarder_detail']['customer_number'];
			
			$rec_zipcode    = $shipmentObj->RecordData[ZIPCODE];
			$weightindeca   = commonfunction::paddingleft(($shipmentObj->RecordData[WEIGHT] / 0.01),4,'0');
			$valoreninsurence = '00';
			$machinable       = '0';
			$reservedfield    = '0';
			$controlllink     = commonfunction::sub_string($shipmentObj->RecordData[TRACENR],-1);
			
			$combineData = $shipmentObj->RecordData['CustomerNo'].$weightindeca.$valoreninsurence.$machinable.$reservedfield.$controlllink;
			$checkDigitRoute = $this->getColissiomoCheckDigit($combineData);
			
			$shipmentObj->RecordData[REROUTE_BARCODE] = $serviceCode.'1'.$rec_zipcode.$combineData.$checkDigitRoute;
			$shipmentObj->RecordData['print_reroute'] = $serviceCode.'1  '.$rec_zipcode.'  '.$shipmentObj->RecordData['CustomerNo'].'  '.$weightindeca.'  '.$valoreninsurence.$machinable.$reservedfield.$controlllink.$checkDigitRoute;
			
			return true;
		
	   }
	   /**
	 * @Function : getColissiomoCheckDigit()
	 * @Description : this function calculate check digit for colissimo forwarder on mod 10 algorithm
     */	
	 public function getColissiomoCheckDigit($number){
	    $number = strrev($number);
		$evensum = 0;
		$oddnsum = 0;
		 for($i=0;$i<strlen($number);$i++){
			 if($i%2==0){
				 $evensum = $evensum + $number[$i];
			 }else{
				 $oddnsum = $oddnsum + $number[$i];
			 }
		  }
		 $evensummult =  $evensum * 3;
		 $finalnumber =  $evensummult + $oddnsum;
		 $mode10 = $finalnumber % 10;
		 if($mode10==0){
		   $checkdigit = $mode10;
		 }else{
		   $checkdigit = 10 - $mode10;
		 }
		 return $checkdigit;
	 }
   
   
}