<?php
class Application_Model_RDPAGLabel extends Zend_custom{
	   public $ForwarderDetails =  array();
	  public function CreateRDPAGLabel($shipmentObj,$newbarcode=true){ 
	  	 $checkdigit = $this->GenMOD11($shipmentObj->RecordData[TRACENR]);
		 $shipmentObj->RecordData[BARCODE] = 'RC'.$shipmentObj->RecordData[TRACENR].$checkdigit.'DE';
		 $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];		
	  }
	/**
	 * @Function : GenMOD11()
	 * @Description : this function assemble generate Checkdigit
     */
	public function GenMOD11($base_val)
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