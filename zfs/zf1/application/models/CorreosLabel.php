<?php
class Application_Model_CorreosLabel extends Zend_custom{
      public $Correosdata = array();
	  public function CreateCorreosLabel($shipmentObj,$newbarcode=true){
	        $this->Correosdata = $shipmentObj->RecordData['forwarder_detail'];
			$Postalcode = preg_replace("/[^0-9]/","",$shipmentObj->RecordData[ZIPCODE]);
			$Postalcode = str_pad($Postalcode, 5, '0', STR_PAD_LEFT);
			$Postalcode = substr($Postalcode,-5,5);
			$trackingNo = substr(str_pad($shipmentObj->RecordData[TRACENR], 7, '0', STR_PAD_LEFT),0,7);
			
			if($shipmentObj->RecordData['addservice_id']==126 || $shipmentObj->RecordData['addservice_id']==149){
				$shipmentObj->RecordData['product_type'] = 'PR';
			}elseif($shipmentObj->RecordData['addservice_id']==124 || $shipmentObj->RecordData['addservice_id']==148){
			    $shipmentObj->RecordData['product_type'] = 'DQ';
			}else{
				$shipmentObj->RecordData['product_type'] = 'PQ';
			}
			
			$shipmentObj->RecordData['client_code'] = $this->Correosdata['contract_number'];
			$shipmentObj->RecordData['label_generation'] = '04';
			$shipmentObj->RecordData['TrackingSPC'] = $trackingNo;
			$shipmentObj->RecordData['NoOfPackage'] = '01';
			$shipmentObj->RecordData['DesPostcodeSPC'] = $Postalcode;
			
			$shipmentObj->RecordData['BARCODENR'] = $shipmentObj->RecordData['product_type'] . $shipmentObj->RecordData['client_code'] . $shipmentObj->RecordData['label_generation'] . $shipmentObj->RecordData['TrackingSPC'] . $shipmentObj->RecordData['NoOfPackage'] . $shipmentObj->RecordData['DesPostcodeSPC'];
			$shipmentObj->RecordData['ControlCharacter'] = $this->CorreosBarcodeControlCharacter($shipmentObj->RecordData['BARCODENR']);
			
			$shipmentObj->RecordData['BARCODENR'] =  $shipmentObj->RecordData['BARCODENR'].$shipmentObj->RecordData['ControlCharacter'];
			$shipmentObj->RecordData[BARCODE] = $shipmentObj->RecordData['product_type'] . $shipmentObj->RecordData['client_code'] . $shipmentObj->RecordData['label_generation'] . $shipmentObj->RecordData['TrackingSPC'] . $shipmentObj->RecordData['NoOfPackage'] . $shipmentObj->RecordData['DesPostcodeSPC'] . $shipmentObj->RecordData['ControlCharacter'];
			$shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
			
			$shipmentObj->RecordData['DispatchCode'] = $shipmentObj->RecordData['product_type'] . $shipmentObj->RecordData['client_code'] . $shipmentObj->RecordData['label_generation'] . $shipmentObj->RecordData['TrackingSPC'];
			$shipmentObj->RecordData['ControlCharacter'] = $this->CorreosBarcodeControlCharacter($shipmentObj->RecordData['BARCODENR']);
			$shipmentObj->RecordData['DispatchCode'] =  $shipmentObj->RecordData['DispatchCode'].$shipmentObj->RecordData['ControlCharacter'];
	  }
	  
	  /** 
    *Correos Barcode Formation
    *Function : CreateCorreosLabel()
    *Function Set barcode and Other informations of ESCorreos label
    **/ 
    public function CreateESCorreosLabel($shipmentObj)
	{ 
		$this->Correosdata = $shipmentObj->RecordData['forwarder_detail'];
		$Postalcode = preg_replace("/[^0-9]/","",$shipmentObj->RecordData[ZIPCODE]);
		$Postalcode = substr($Postalcode,-5,5);
		$trackingNo = substr(str_pad($shipmentObj->RecordData[TRACENR], 7, '0', STR_PAD_LEFT),0,7);
       
	   if($shipmentObj->RecordData['country_id']==218){
	       $Postalcode = str_pad($Postalcode, 5, '0', STR_PAD_LEFT);
		   $Postalcode = substr($Postalcode,-5,5);
		}
		
		if($shipmentObj->RecordData['addservice_id']==126 || $shipmentObj->RecordData['addservice_id']==149)
		 {
		 	$shipmentObj->RecordData['product_type']='PR' ;
		 }elseif(($shipmentObj->RecordData['weight']*1000)<=500 && ($shipmentObj->RecordData['addservice_id']==115 || $shipmentObj->RecordData['addservice_id']==145)){
		    $shipmentObj->RecordData['product_type'] ='UR';
		 }else{
		    $shipmentObj->RecordData['product_type']='PQ' ;
		 }
		 
		if( $shipmentObj->RecordData['country_id']==34)
		 {
		 	$Postalcode=substr($Postalcode,2);
		 	$Postalcode='99'.$Postalcode;
		 }
		 elseif($shipmentObj->RecordData['country_id']==192)
		 {
		 	$Postalcode= substr(trim($Postalcode),0,4);
		 	$Postalcode='8'.$Postalcode;
		 }
		 
		 $shipmentObj->RecordData['client_code'] = $this->Correosdata['contract_number'];
         $shipmentObj->RecordData['label_generation'] = '04';
         $shipmentObj->RecordData['TrackingSPC'] = $trackingNo;
         $shipmentObj->RecordData['NoOfPackage'] = '01';
		 
		 $shipmentObj->RecordData['DesPostcodeSPC'] = substr($Postalcode,0,5);
    	
        $shipmentObj->RecordData['BARCODENR'] = $shipmentObj->RecordData['product_type'] . $shipmentObj->RecordData['client_code'] . $shipmentObj->RecordData['label_generation'] . $shipmentObj->RecordData['TrackingSPC'] . $shipmentObj->RecordData['NoOfPackage'] . $shipmentObj->RecordData['DesPostcodeSPC'];
        
	    $shipmentObj->RecordData['ControlCharacter'] = $this->CorreosBarcodeControlCharacter($shipmentObj->RecordData['BARCODENR']);		
		
        $shipmentObj->RecordData['BARCODENR'] =  $shipmentObj->RecordData['BARCODENR'].$shipmentObj->RecordData['ControlCharacter'];
        $shipmentObj->RecordData[BARCODE] = $shipmentObj->RecordData['product_type'] . $shipmentObj->RecordData['client_code'] . $shipmentObj->RecordData['label_generation'] . $shipmentObj->RecordData['TrackingSPC'] . $shipmentObj->RecordData['NoOfPackage'] . $shipmentObj->RecordData['DesPostcodeSPC'] . $shipmentObj->RecordData['ControlCharacter'];
		$shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
		$shipmentObj->RecordData['DispatchCode'] = $shipmentObj->RecordData['product_type'] . $shipmentObj->RecordData['client_code'] . $shipmentObj->RecordData['label_generation'] . $shipmentObj->RecordData['TrackingSPC'];
        $shipmentObj->RecordData['ControlCharacter'] = $this->CorreosBarcodeControlCharacter($shipmentObj->RecordData['BARCODENR']);
        $shipmentObj->RecordData['DispatchCode'] =  $shipmentObj->RecordData['DispatchCode'].$shipmentObj->RecordData['ControlCharacter'];
        return true;
    }
	  /**
    *Correos Barcode Control Character calculation
    *Function : CorreosBarcodeControlCharacter()
    *Function Set control character at Correos label
    **/
    public function CorreosBarcodeControlCharacter($correosBarcode){
        $correosBarcode = str_split($correosBarcode);
        $sum_asciivalues = 0;
        $asciivalue = 0;
        foreach ($correosBarcode as $correosBarcodevalue) {
            $asciivalue = ord($correosBarcodevalue);
            $sum_asciivalues = $sum_asciivalues + $asciivalue;
        }
        $identification_number = array(0 => "T", 1 => "R", 2 => "W", 3 => "A", 4 => "G", 5 => "M", 6 => "Y", 7 => "F", 8 => "P", 9 => "D", 10 => "X", 11 => "B",
            12 => "N", 13 => "J", 14 => "Z", 15 => "S", 16 => "Q", 17 => "V", 18 => "H", 19 => "L", 20 => "C", 21 => "K", 22 => "E");
        return $identification_number[$sum_asciivalues % 23];
    }
}