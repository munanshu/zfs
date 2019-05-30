<?php
class Application_Model_UrgentSwisspostLabel extends Zend_custom{
	  public function CreateUrgentSwisspostLabel($shipmentObj,$newbarcode=true){ 
	  	 $checkdigit = $this->checkdigit_UrgentSwiss($shipmentObj->RecordData[TRACENR]);
		 $barcode_suffix = 'CH';
		 if($shipmentObj->RecordData[COUNTRY_ID]==17){
		    $barcode_suffix = '';
		 }
		 $shipmentObj->RecordData[BARCODE] = $shipmentObj->RecordData['forwarder_detail']['barcode_prefix'].$shipmentObj->RecordData['forwarder_detail']['customer_number'].$shipmentObj->RecordData[TRACENR].$checkdigit.$barcode_suffix; 
		 $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
		 $this->generateBarcode($shipmentObj);	
	  }
	public function checkdigit_UrgentSwiss($base_val)
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
	
	public function generateBarcode($shipmentObj){
	     $country_detail = $this->getCountryDetail($shipmentObj->RecordData[COUNTRY_ID],1);
		 $shipmentObj->RecordData['continent_id'] = 3;//$country_detail['continent_id'];
		 $isocode = $country_detail['iso_code'];
	     $printabledata = '';
		if(isset($shipmentObj->RecordData[REROUTE_BARCODE]) && $shipmentObj->RecordData[REROUTE_BARCODE]!=''){
		    $printabledata = $shipmentObj->RecordData[REROUTE_BARCODE];
		}else{
			$groupunit = 80;
			$datamatrixtype = 31;
			$invoice_ref = substr($shipmentObj->RecordData[BARCODE],2,8);
			$ordernumber = "000000";
			$reservePost = 0;
			$customerno = "40383355";
			$productcode = "001";
		    $printabledata = $isocode.$groupunit.$datamatrixtype.$invoice_ref.$ordernumber.$reservePost.$customerno.$productcode;
		}
		$DataMatrix = new Zend_Barcode_2DBARCODE_DataMatrix();
		$IMAGE_NAME_S = 'Urgent_'.$shipmentObj->RecordData[TRACENR].'.png';
		$FILEPATH = PRINT_SAVE_LABEL.$shipmentObj->RecordData['forwarder_detail']['forwarder_name']."/img/";//print_r($printabledata);die;
		$DataMatrix->setBGColor("WHITE");
		$DataMatrix->setBarColor("BLACK");
		$DataMatrix->setRotation("0");
		$DataMatrix->setImageType("PNG", 40 );
		$DataMatrix->setQuiteZone("7");
		$DataMatrix->setEncoding("AUTO");
		$DataMatrix->setFormat("4");
		$DataMatrix->setTilde("Y");
		$DataMatrix->setModuleSize("5");
		$DataMatrix->setFilePath($FILEPATH);
		$DataMatrix->paint($printabledata,$IMAGE_NAME_S);
		$shipmentObj->RecordData['RDFilepatch'] = PRINT_OPEN_LABEL.$shipmentObj->RecordData['forwarder_detail']['forwarder_name'].'/img/'.$IMAGE_NAME_S;
		return;
	 }
}