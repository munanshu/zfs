<?php
class Application_Model_DHLLabel extends Zend_custom{
      public $DHLdata = array();
	  public function CreateDHLLabel($shipmentObj,$newbarcode=true){
	     
	      $this->DHLdata  =  $shipmentObj->RecordData['forwarder_detail'];
		  $this->RoutingCode($shipmentObj);
		  if($this->DHLdata['customer_number']=='6216630230'){
		      if($shipmentObj->RecordData['addservice_id']==124 || $shipmentObj->RecordData['addservice_id']==148){
			      $this->dhllicencePlat_retour($shipmentObj);  
			  }else{
			      $this->dhllicencePlat_long($shipmentObj);  
			  }
		  }else{
		       $this->dhllicencePlat($shipmentObj);
		  }
		  $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
	  }
	  public function RoutingCode($shipmentObj){  
      $applicationidentifier = $this->DHLdata['barcode_prefix'];
	  $customer_number = $this->DHLdata['customer_number'];
	  $participation = $this->DHLdata['service_indicator'];
	
	  $zipcodeCode = $shipmentObj->RecordData[ZIPCODE];
	  $Separator = "+";
	  $productCode = "99";
	  $daytime = "000";
	  
	  if($shipmentObj->RecordData['addservice_id']==7 || $shipmentObj->RecordData['addservice_id']==146){
	      $shipmentObj->RecordData['ProductFeature'] = 'C.O.D';
		  $feature = "901";
		  $productcode = '01';
	  }else{
	  	  $feature = "900";
		  $productcode = '00';
		  $shipmentObj->RecordData['ProductFeature'] = '';
	  }
	  if($shipmentObj->RecordData['addservice_id']==124 || $shipmentObj->RecordData['addservice_id']==148){
	      $feature = "933";
	  }
	  
	  $streetCode = $this->getStreetCode($shipmentObj->RecordData[STREET],$shipmentObj->RecordData[ZIPCODE]);
	  
       if(trim($shipmentObj->RecordData[STREETNR])!=''){
	     $houseNo = str_pad(substr(preg_replace("/[^0-9]+/", "",$shipmentObj->RecordData[STREETNR]),0,3),3,'0',STR_PAD_LEFT);
      }else{
	    $nrfromstreet = preg_replace("/[^0-9]+/", "",substr($shipmentObj->RecordData[STREET],-5)); 
		$houseNo =  str_pad($nrfromstreet,3,'0',STR_PAD_LEFT);
	  }
	  $countryCode = $shipmentObj->RecordData['rec_cncode'];
	  $shipmentObj->RecordData[REROUTE_BARCODE] =  $applicationidentifier.$shipmentObj->RecordData['rec_cncode'].$zipcodeCode.$Separator.$productCode.$daytime.$feature.$streetCode.$houseNo;
	  $shipmentObj->RecordData['Route']  	   = '('.$applicationidentifier.') '.$countryCode.' '.$zipcodeCode.' '.$Separator.' '.$productCode.' '.$daytime.' '.$feature.' '.$streetCode.' '.$houseNo; 
	  
	  //$billproduct = '01';
	   $billproduct = $this->DHLdata['service_type'];
	   $shipmentObj->RecordData['BillingNumber'] = $customer_number.$billproduct.$participation;
	  
   }
   
   	  public function dhllicencePlat($shipmentObj){
		 if(isset($shipmentObj->RecordData[BARCODE]) && substr($shipmentObj->RecordData[BARCODE],0,6)=='435616'){
			$basenumber = 435616;
		 }else{
		    $basenumber = $this->DHLdata['contract_number'];
		 }
		 
		 $numberforcdigit = $basenumber.str_pad($shipmentObj->RecordData[TRACENR],5,0,STR_PAD_LEFT);
		 $checkdijit = $this->checkdijitDHL($numberforcdigit);
		 
		 $shipmentObj->RecordData['LP'] = $basenumber.' '.$shipmentObj->RecordData[TRACENR].' '.$checkdijit;
		 $shipmentObj->RecordData[BARCODE] = $basenumber.$shipmentObj->RecordData[TRACENR].$checkdijit;
  }
  
  	  public function dhllicencePlat_long($shipmentObj){ 
		 $applicationidentifier = '00';
		 $reservesection = 3;
		 $basenumber = $this->DHLdata['contract_number'];
		 $numberforcdigit = $reservesection.$basenumber.$shipmentObj->RecordData[TRACENR];
		 $checkdijit = $this->checkdijitDHL_long($numberforcdigit);
		 
		 $shipmentObj->RecordData['BLP'] = '[C1'.$applicationidentifier.$reservesection.$basenumber.$shipmentObj->RecordData[TRACENR].$checkdijit;
		 $shipmentObj->RecordData['LP'] = '('.$applicationidentifier.') '.$reservesection.' '.$basenumber.' '.$shipmentObj->RecordData[TRACENR].' '.$checkdijit;
		 $shipmentObj->RecordData[BARCODE] = $applicationidentifier.$reservesection.$basenumber.$shipmentObj->RecordData[TRACENR].$checkdijit;
		 $shipmentObj->RecordData['LPIMG'] = $this->getBarcodeImage(str_replace(' ','',$shipmentObj->RecordData['LP']));
		  if($shipmentObj->RecordData['user_id']==3256){
		     //echo "<pre>";print_r($shipmentObj->RecordData['LP']);die;
		  }
     }
  	 
	 public function dhllicencePlat_retour($shipmentObj){
		 $applicationidentifier = '00';
		 $reservesection = 3;
		 $basenumber = $this->DHLdata['contract_number'];
		 $paernumber = $shipmentObj->RecordData[TRACENR];
		 
		 $numberforcdigit = $basenumber.str_pad($shipmentObj->RecordData[TRACENR],6,0,STR_PAD_LEFT);
		 $checkdijit = $this->checkdijitDHL($numberforcdigit);
		 
		 $shipmentObj->RecordData['LP'] = $basenumber.' '.$shipmentObj->RecordData[TRACENR].' '.$checkdijit;
		 $shipmentObj->RecordData[BARCODE] = $basenumber.$shipmentObj->RecordData[TRACENR].$checkdijit;
  }
  
     public function checkdijitDHL($number){ 
			$j=0;
			$oddadd = 0;
			$evenadd = 0;
			$reverse = strrev($number);
			for($i=0;$i<strlen($reverse);$i++){
				 if($i%2==0){ 
					$oddadd = $oddadd + $reverse[$i];
				 }
				 if($i%2==1){ 
					$evenadd = $evenadd + $reverse[$i];
				 }  
			 }
			 $totalno = ($oddadd*4) + ($evenadd*9);
			
			   $modevalue = $totalno % 10;
			   if($modevalue==0){
				  return $modevalue;
			   }
			   $checkdijit = 10-$modevalue;
			   return $checkdijit;    
     }
	 
	 public function checkdijitDHL_long($number){ 
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
  
	 public function CreateDHLGlobalLabel($shipmentObj,$newbarcode=true){
	      $this->DHLdata  =  $shipmentObj->RecordData['forwarder_detail'];
		  $this->RoutingCodeWeltpacket($shipmentObj);
		  if($shipmentObj->RecordData['addservice_id']==124 || $shipmentObj->RecordData['addservice_id']==148){
			      $this->dhllicencePlat_retour($shipmentObj);  
		   }
		  $this->dhllicencePlatweltpacket($shipmentObj);
		  $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
	 
		 $shipmentObj->RecordData['SenderPhone'] = '49 40 123456';
		 //$getContinent = $objLabel->getContinentId($objLabel->RecordData[COUNTRY_ID]);
		 //$objLabel->RecordData['continent_id'] = $getContinent['continent_id'];
		 //$this->DhlBarcodeFormation($objLabel);
  
	 }
	 
	 // DHL weltpacket Licence Plate
   public function dhllicencePlatweltpacket($shipmentObj){
	 $base_number = $this->DHLdata['contract_number'];
	
	 $numberforcdigit = $base_number.$shipmentObj->RecordData[TRACENR];
	 $checkdijit = $this->checkdigitweltpacket($numberforcdigit);
	 
	 $shipmentObj->RecordData['LP'] = $base_number.$shipmentObj->RecordData[TRACENR].$checkdijit;
	 $shipmentObj->RecordData['LP']  = substr($shipmentObj->RecordData['LP'],0,2).'.'.substr($shipmentObj->RecordData['LP'],2,5).'.'.substr($shipmentObj->RecordData['LP'],7,3).'.'.substr($shipmentObj->RecordData['LP'],10);
	 $shipmentObj->RecordData[BARCODE] = $base_number.$shipmentObj->RecordData[TRACENR].$checkdijit;
  }
	 
	 // DHL weltpacket  RoutingCode formation 
    public function RoutingCodeWeltpacket($shipmentObj){  
      $applicationidentifier = $this->DHLdata['barcode_prefix'];
	  $customer_number = $this->DHLdata['customer_number'];
	  $participation = $this->DHLdata['service_indicator'];
	 
	  $countryCode  = $shipmentObj->RecordData['rec_cncode'];
	  $zipcodeCode = $shipmentObj->RecordData[ZIPCODE];
	  
	  $Separator = "+";
	  $productCode = '01';
	   
	  $shipmentObj->RecordData['ProductFeature'] = '';
	  $basicproduct = $this->DHLdata['service_type'];
	  $daytime = "000";
	  $featurecode = '000';
		  
	  
	  $shipmentObj->RecordData[REROUTE_BARCODE] =  $applicationidentifier.$countryCode.$zipcodeCode.$Separator.$basicproduct.$daytime.$featurecode;
	  $shipmentObj->RecordData['Route']  	   = '('.$applicationidentifier.')'.$countryCode.$zipcodeCode.$Separator.$basicproduct.$daytime.$featurecode; 
	  $shipmentObj->RecordData['BillingNumber'] = $customer_number.$participation.$productCode;
	  
  }
  public function checkdigitweltpacket($number){
    	$reversenumber = strrev($number);
		$oddadd = 0;
      for($i=0;$i<strlen($reversenumber);$i=$i+2){
		  $oddadd = $oddadd + $reversenumber[$i];
	   }
	  $oddadd = $oddadd * 4;
	  $evennum = 0;
      for($i=1;$i<strlen($reversenumber);$i=$i+2){
		  $evennum = $evennum + $reversenumber[$i]; 
	   }
	  $evennum = $evennum * 9; 
	  $modevalue =  ($oddadd + $evennum) % 10;
	   if($modevalue==0){
	      return $modevalue;
	   }
	   $checkdijit = 10-$modevalue;
	   return $checkdijit; 
  } 
	  
	   public function getStreetCode($rec_street,$zipcode){
		$find = array('.',' ','-','Ž','”','„','á','ä','aße','š','ö','ü','ß');
		$replace=array('','','','ae','oe','ae','ss','ae','','ue','oe','ue','ss');
		$street = strtoupper(preg_replace('/[0-9]+/', '',str_replace($find,$replace,utf8_decode($rec_street))));
		 try {
		 $select = $this->masterdb->select()
								->from(array('SC'=>DHL_STREETCODE),array('*'))
								->where("`STR-PLZ`='".trim($zipcode)."' AND `STR-NAME-SORT` LIKE SUBSTRING('".$street."',1,LENGTH(  `STR-NAME-SORT` ))");
								//echo $select->__toString();die;
		 $result = $this->masterdb->fetchRow($select);
		 return str_pad(trim($result['STR-CODE']),3,'0',STR_PAD_LEFT);
		 }
		 catch (Exception $e) {
			return '000';
		 }
	  }
	  
	  public function getBarcodeImage($licenseplat){
        $filename = $licenseplat.'.png';
        $information =  array('filetype'=>'PNG','dpi'=>72,'scale'=>3,'rotation'=>0,'font_family'=>'Arial.ttf','font_size'=>8,'text'=>$licenseplat,'thickness'=>30,'start'=>'C','code'=>'BCGgs1128','path'=>PRINT_SAVE_LABEL.$this->DHLdata['forwarder_name'].'/img/'.$filename);
       $image  = new Zend_EAN128_ean_BCGgs1128($information);
	   return PRINT_OPEN_LABEL.$this->DHLdata['forwarder_name'].'/img/'.$filename;
   }
}