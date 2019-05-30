<?php
class Application_Model_GLSNLLabel extends Application_Model_Socketprocessing{
	 
	  public function CreateGLSNLLabel($shipmentObj,$newbarcode=true){  
	        $this->SocketForwarder  =  $shipmentObj->RecordData['forwarder_detail'];
			$tracking_customer_no = $this->SocketForwarder['customer_number'].$shipmentObj->RecordData[TRACENR];
			$check_digit = $this->GlsNlCheckDigit($tracking_customer_no);
			$shipmentObj->RecordData[BARCODE] = $this->SocketForwarder['customer_number'].$shipmentObj->RecordData[TRACENR].$check_digit;
			$shipmentObj->RecordData['tracenr_barcode'] = $shipmentObj->RecordData[BARCODE];

			//Set final barcode
			$this->GlsSocketProcessing($shipmentObj);
			$this->GlsNL2DBarcodePrinting($shipmentObj);
	  }
	  
	  public function GlsSocketProcessing($shipmentObj){
			$shipmentObj->RecordData['OnetimeRef'] = $this->OneTimeReference($shipmentObj->RecordData[QUANTITY],$shipmentObj->RecordData[BARCODE]);
		    $this->SocketData = $shipmentObj->RecordData;
		    $socketdata = $this->SocketReturn(); 
			$shipmentObj->RecordData[REROUTE_BARCODE] = $socketdata['Reroute'];
			$shipmentObj->RecordData['local_barcode'] = isset($socketdata['SocketLabel']['T600'])?$socketdata['SocketLabel']['T600']:'';
			$shipmentObj->RecordData['SocketResult'] = $socketdata['SocketLabel'];
			// echo "<pre>"; print_r($shipmentObj->RecordData); die;
		}
		
		public function GlsNL2DBarcodePrinting($shipmentObj){
		  if(isset($shipmentObj->RecordData['SocketResult']['T8902']) && isset($shipmentObj->RecordData['SocketResult']['T8903'])){
		   $GLSNLbarcode = $this->Create2Dbarcode($shipmentObj->RecordData['SocketResult']['T8902'],$shipmentObj->RecordData['SocketResult']['T8903'],$shipmentObj->RecordData[TRACENR]);
		   }
		   $shipmentObj->RecordData['PrimaryBarcode']	 = $GLSNLbarcode['Pri'];
		   $shipmentObj->RecordData['SecondryBarcode']	 = $GLSNLbarcode['Sec'];//echo "<pre>";print_r($shipmentObj->RecordData);die;
		   
		   $serviceInfo = $this->getServiceDetails($shipmentObj->RecordData[ADDSERVICE_ID]);
		  if(in_array($serviceInfo['internal_code'],array('E',21,22,23,24,25,26,'E21','E22','E23','E24','E25','E26'))){
		       $shipmentObj->RecordData['ServiceText']	 = 'EXPRESS-Service';
		  }elseif(in_array($serviceInfo['internal_code'],array('L','L2'))){
		      $shipmentObj->RecordData['ServiceText']	 = 'Parcelletter';
		  }
		   
		  // $this->SetLogo();
		  return true;
		}
		public function GlsNlCheckDigit($number) {

	   $shipment_factor = 0;

	   $muliply = 0;

	  for($i=0;$i<strlen($number);$i++){

		$factor  = ($i%2==0)?'3':'1';

		$muliply = $muliply+$number[$i]*$factor;

	  }

	  $uppernumber = ceil($muliply/10)*10;

	  $check_digit = $uppernumber-($muliply);

	  return $check_digit;

	}
	
	   
}