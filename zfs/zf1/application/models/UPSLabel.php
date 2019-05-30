<?php
class Application_Model_UPSLabel extends Zend_custom{
	  public $UPSDetail = array();
	  public function CreateUPSLabel($shipmentObj,$newbarcode=true){
				$this->UPSDetail = $shipmentObj->RecordData['forwarder_detail'];
			//$SenderPhone = $this->userAddress($this->RecordData['user_id']);
			//$this->RecordData['SenderPhone'] = (!empty($SenderPhone['phoneno'])) ? trim($SenderPhone['phoneno']) : '074 8800700';
			/*if($this->RecordData['user_id']==3553){
			  $this->RecordData['SOURCE_ADDRESS']['SenderAddress'] = array('NIKE EUROPEAN OPERATIONS NL BV','MARGOT VAN DEN BURG','COLOSSEUM 1','HILVERSUM','1213NL','NL','NETHERLANDS');
			}*/
			$shipmentObj->RecordData['Ship_date'] 		= (!empty($shipmentObj->RecordData['create_date']))? $shipmentObj->RecordData['create_date'] 	: date('Y-m-d H:i:s');	
			$shipmentObj->RecordData['Billing_opt'] 	= 'P/P*';
			/*$upsdetail = $this->upsDetail($this->RecordData[FORWARDER_ID]);
			
			$this->RecordData[SERVICE_NAME] = $this->ServiceName($this->RecordData['service_id']);
			$this->RecordData[NOTE1] = $this->RecordData[NOTE1];
			$this->RecordData[NOTE2] = $this->RecordData[NOTE2];
			
			$cncode = $this->countryCode($this->RecordData['country_id']);*/
			$shipmentObj->RecordData['URC_code'] = $this->upsRouteInfo($shipmentObj->RecordData['rec_cncode'],$shipmentObj->RecordData[ZIPCODE]);
			$indicator = $this->UPSDetail['service_indicator'];
			$shipmentObj->RecordData['Service_icon'] = $this->UPSDetail['service_icon'];
			$class_of_service = $this->UPSDetail['class_of_service'];
			// main code of barcode creation
			if($newbarcode){
				$shipmentObj->RecordData[TRACENR] = $this->randomDigits();
			 }
			//call ups configuration table
			//$getUpsConfiguration = $this->getUpsConfiguration();
			$shipmentObj->RecordData['CONFIG_URC_CODE'] = $this->UPSDetail['version_number'];   //URC code now Version number
			$shipmentObj->RecordData['CONFIG_URC_DATE'] = $this->UPSDetail['SAP_number'];   // URC date in field SAP number
			$shipper_id = $this->UPSDetail['contract_number']." ".$this->UPSDetail['sub_contract_number'];  // Account 1 and 2 now became Contract and bub contract
			
			$Tracking = $this->UPSDetail['barcode_prefix']." ".$shipper_id." ".$indicator." ".$shipmentObj->RecordData[TRACENR]; //create upc tracking number without checkdigit
			$Tracking .= $this->generate_upc_checkdigit($Tracking);  //create upc tracking number with checkdigit
			
			$tracking1 = commonfunction::sub_string($Tracking, 0, 18);
		    $tracking2 = commonfunction::sub_string($Tracking, -4);
		    $shipmentObj->RecordData['tracking_no'] = commonfunction::sub_string($Tracking, 0, 18)." ".commonfunction::sub_string($Tracking, -4);
			$shipmentObj->RecordData[BARCODE] =  commonfunction::stringReplace(' ','',$shipmentObj->RecordData['tracking_no']);
			$shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
			
			$Tracking_code = commonfunction::stringReplace(" ","",$shipmentObj->RecordData['tracking_no']);
			$shipmentObj->RecordData['shp_no'] = $this->calculateshipment($Tracking_code,$shipper_id);
			
			// Maxicode string formatting
		    $maxi_track_no    = commonfunction::sub_string($Tracking_code,0,2).commonfunction::sub_string($Tracking_code,-8);
			
			$rec_country_detail = $this->getCountryDetail($shipmentObj->RecordData['country_id'],1);
			
			if(commonfunction::lowercase($rec_country_detail['country_name']) == 'netherlands') {
			  $application_id = '420';
			  //$new_zip = commonfunction::sub_string($rec_zip,0,4);
			  $potalbarcode   = $application_id.$shipmentObj->RecordData[ZIPCODE];
			}
			else {
			  $application_id = '421';
			 // $new_zip = $rec_zip;
			  $potalbarcode = $application_id.$rec_country_detail['iso_code'].$shipmentObj->RecordData[ZIPCODE];
			}
			
			$shipmentObj->RecordData['Parcel_barcode'] = $potalbarcode;
			
			$rec_zip = ($shipmentObj->RecordData['country_id']==116) ? commonfunction::stringReplace(array('I','S','-'),array('','',''),commonfunction::uppercase($shipmentObj->RecordData[ZIPCODE])) :$shipmentObj->RecordData[ZIPCODE];
			$rec_country_num_code = $rec_country_detail['iso_code'];
			//$class_of_service 	= $upsdetail['class_of_service'];
			$rec_add 			= commonfunction::remove_accent($shipmentObj->RecordData[ADDRESS]);
			$rec_city 			= commonfunction::remove_accent($shipmentObj->RecordData[CITY]);
			
			$maxicodeData['messageheader'] 	= '[)>~030';
			$maxicodeData['tdata'] 		    = '01~02996';
			$maxicodeData['postalcode']	    = commonfunction::uppercase($rec_zip).'~029';
			$maxicodeData['countrynumcode'] = commonfunction::uppercase($rec_country_num_code).'~029';
			$maxicodeData['serviceclass'] 	= commonfunction::uppercase($class_of_service).'~029';
			$maxicodeData['mtrackno'] 	    = commonfunction::uppercase($maxi_track_no).'~029';
			$maxicodeData['accountno'] 	    = commonfunction::stringReplace(' ', '', commonfunction::uppercase($shipper_id)).'~029';
			$maxicodeData['shipno'] 	    = ''.'~029';
			$maxicodeData['packweight'] 	= commonfunction::uppercase(round(commonfunction::stringReplace(',','.',trim($shipmentObj->RecordData[WEIGHT])))).'~029';
			$maxicodeData['streetAddress'] 	= commonfunction::sub_string(commonfunction::uppercase($rec_add),0,10).'~029';
			$maxicodeData['shiptocity'] 	= commonfunction::sub_string(commonfunction::uppercase($rec_city),0,20).'~029~030'; //echo $maxicodeData['shiptocity']."<br>";
			$maxicodeData['shiptostate'] 	= '~004';
			$maxicodeData['creation_date']  = commonfunction::julianDate($shipmentObj->RecordData['create_date']); //echo $maxicodeData['creation_date'];die;
			
			$maxicode = $maxicodeData['messageheader'].$maxicodeData['tdata'].$maxicodeData['postalcode'].$maxicodeData['countrynumcode'].$maxicodeData['serviceclass'].$maxicodeData['mtrackno'].'UPSN~029'.$maxicodeData['accountno'].$maxicodeData['creation_date'].'~029'.$maxicodeData['shipno'].'1/1~029'.$maxicodeData['packweight'].'N~029'.$maxicodeData['streetAddress'].$maxicodeData['shiptocity'].$maxicodeData['shiptostate']; 
			//////////////////////////////
			
			$shipmentObj->RecordData[REROUTE_BARCODE]	= $maxicode;			
			
			$bc = new Zend_Barcode_Maxicode_MaxiCode();
			$Path = PRINT_SAVE_LABEL.$this->UPSDetail['forwarder_name'].'/img/';
			$bc->setMode(3);
			$bc->setBGColor('WHITE');
			$bc->setBarColor('BLACK');
			$bc->setRotation(0);
			$bc->setImageType('PNG', 40 );
			$bc->setProcessTilde(TRUE);
			$bc->setResolution(600);
			$bc->setNumberOfSymbols(8);
			$bc->setSALayout('TB');
			$bc->setFilePath($Path);
			$bc->setQuiteZone('4px'); //print_r($maxicode);die;
			$bc->paint($maxicode,$Tracking_code.'.png');
			
			$imgurl = $Path.$Tracking_code.'.png';
			$shipmentObj->RecordData['Maxi_code'] 		= (!empty($imgurl)) ? $imgurl:'';
			//$shipmentObj->RecordData['customer_number']= $upsdetail['customer_number'];
			$shipmentObj->RecordData['Tracking_code'] 	= $Tracking_code;
			$this->RecordData['Service_name'] 	= $this->UPSDetail['forwarder_name'];
			return true;
    	
	  }
	 /**
	 * Ups Route Information
	 * Function : upsbarcode
	 * This function returns UPs Routes Information
	**/		
	public function upsRouteInfo($country_code,$postal_code){
			$select = $this->masterdb->select()
					  ->from(array('UR'=>UPS_ROUTECODES),array('urc_code'))
					  ->where('postal_high >=?', $postal_code)
					  ->where('postal_low <=?', $postal_code)
					  ->where('country_code =?' ,$country_code);
			$result = $this->masterdb->fetchRow($select);
			$urc_code = isset($result['urc_code'])?$result['urc_code']:'';
			return $urc_code;
	}
	public function randomDigits() {

		$length = 7;

		$random = "";

		srand((double)microtime()*1000000);

		//$data = "1234567890ABCDE123IJKLMN67QRSTUVWXYZ";

		$data = "1234567890";

		

		for($i= 0; $i < $length; $i++) {

		  $random .= substr($data, (rand()%(strlen($data))), 1);

		}

		return $random;

	}
	
	public function generate_upc_checkdigit($upc_code) {
		$odd_total  = 0;
		$even_total = 0;
		$bcode= $upc_code;
		$alphachar = array('A' => 2,'B' => 3,'C' => 4,'D' => 5,'E' => 6,'F' => 7,'G' => 8,'H' => 9,'I' => 0,'J' => 1,'K' => 2,'L' => 3,'M' => 4,'N' => 5,'O' => 6,'P' => 7,
						 'Q' => 8,'R' => 9,'S' => 0,'T' => 1,'U' => 2,'V' => 3,'W' => 4,'X' => 5,'Y' => 6,'Z' => 7);

		$upc_code = commonfunction::stringReplace(' ', '', $upc_code);
		$upc_code = commonfunction::sub_string($upc_code, -15);
		$len = commonfunction::string_length($upc_code);
		for ($index=0;$index < $len ;$index++){
			if(commonfunction::is_number($upc_code[$index])){	
			}
			else{
			   $trackno = commonfunction::stringReplace($upc_code[$index], $alphachar[$upc_code[$index]], $upc_code);
			}	
	    }
		$len = commonfunction::string_length($trackno);
		for($i=0; $i<$len; $i++)
		{
			if((($i+1)%2) == 0) {
				/* Sum even digits */
				$even_total += $trackno[$i];
			} else {
				/* Sum odd digits */
				$odd_total += $trackno[$i];
			}
		}
		$checksum = ($even_total*2)+ $odd_total;	
		$check_digit = $checksum % 10;
		/* If the result is not zero, subtract the result from ten. */
		return ($check_digit > 0) ? 10 - $check_digit : $check_digit;
	}
	
	public function calculateshipment($trackno,$shippmentid) {
		$conversion = array(0  => 3,1  => 4,2  => 7,3  => 8,4  => 9,5  => 'B',6  => 'C',7  => 'D',8  => 'F',9  => 'G',10 => 'H',11 => 'J',12 => 'K',13 => 'L',14 => 'M',
							15 => 'N',16 => 'P',17 => 'Q',18 => 'R',19 => 'S',20 => 'T',21 => 'V',22 => 'W',23 => 'X',24 => 'Y',25 => 'Z');
		
		$trackno = commonfunction::stringReplace(' ', '', $trackno);
		$trackno = commonfunction::sub_string($trackno, -8);
		$trackno = commonfunction::sub_string($trackno, 0, -1);
		$pos1 =  $trackno/pow(26,4);
		$pos1 = floor($pos1);
		$pos2 = ($trackno - ($pos1*pow(26,4)))/pow(26,3);
		$pos2 = floor($pos2);
		$pos3 = ($trackno -($pos1*pow(26,4))-($pos2*pow(26,3)))/pow(26,2);
		$pos3 = floor($pos3);
		$pos4 = ($trackno - ($pos1*pow(26,4))- ($pos2*pow(26,3))-($pos3*pow(26,2)) )/26;
		$pos4 = floor($pos4);
		$pos5 = ($trackno-($pos1*pow(26,4))-($pos2*pow(26,3))-($pos3*pow(26,2))-($pos4*26));
		$pos5 = floor($pos5);
		$shipperno = $shippmentid.' '.$conversion[$pos1].$conversion[$pos2].$conversion[$pos3].$conversion[$pos4].$conversion[$pos5];	
		$shipperno = commonfunction::stringReplace(' ', '', $shipperno);	
		$shipperno1 = commonfunction::sub_string($shipperno, 0, 4);	
		$shipperno2 = commonfunction::sub_string($shipperno, 4, 4);	
		$shipperno3 = commonfunction::sub_string($shipperno, 8);
		return    $shipperno1.' '.$shipperno2.' '.$shipperno3;
	}
}