<?php
class Application_Model_MondialRelayLabel extends Zend_custom{
    public $MRdata = array();
	public function CreateMondialRelayLabel($shipmentObj,$newbarcode=true){  
	 $this->MRdata = $shipmentObj->RecordData['forwarder_detail']; 	 
     $marchant =  $this->MRdata['service_type'];
	 $trackingNumber  =  $shipmentObj->RecordData[TRACENR];
	 $parcelsequence  = str_pad($shipmentObj->RecordData['parcelcount'],2,'0',STR_PAD_LEFT);
	 $numberofparcel  = str_pad($shipmentObj->RecordData[QUANTITY],2,'0',STR_PAD_LEFT);
	 //$shipmentObj->RecordData['Country_code'] = $objLabel->countryCode($objLabel->RecordData[COUNTRY_ID]);
	 
	 $firstcdigitno  = $marchant.$trackingNumber.$parcelsequence.$numberofparcel;
	 $controldigit = $this->controlDigit($firstcdigitno);
	 if($shipmentObj->RecordData['addservice_id']==124 || $shipmentObj->RecordData['addservice_id']==148){
	    $shipmentObj->RecordData['label_validation'] = date('d/m/Y', strtotime("+90 days"));
	 	$agency = $this->getAgencyNumberReturn($shipmentObj);
	 }else{
	    $shipmentObj->RecordData['label_validation'] = date('d/m/Y');
	    $agency = $this->getAgencyNumber($shipmentObj);
	  }
	 //$agency = $this->getAgencyNumber($objLabel);
	  
	 $shipmentObj->RecordData['AgencyName'] = $agency['agency_name'];
	 $shipmentObj->RecordData['AgencyNumber'] = substr($agency['agency_code'],0,4);
	 $shipmentObj->RecordData['ShuttleCode'] = $agency['shuttle_code'];
	 $shipmentObj->RecordData['GroupCode'] = $agency['group_code'];
	 $shipmentObj->RecordData['RunNumber'] = $agency['pr_number'];
	 
	 $modeof_delivery = array('DOC'=>'Colissimo home delivery','HOM'=>'Home delivery','LD1'=>'Single man home delivery','LDS'=>'Special home delivery','LCC'=>'Merchant delivery','24R'=>'24 Point Relais®','24L'=>'24 Point Relais® XL','24X'=>'24 Point Relais® XXL','DRI'=>'Colis Drive');
	 
	 if($shipmentObj->RecordData['addservice_id']==124 || $shipmentObj->RecordData['addservice_id']==148){
	     $shipmentObj->RecordData['MOD'] =  'LCC';
	 }elseif($shipmentObj->RecordData[QUANTITY]>1){
	 	$shipmentObj->RecordData['MOD'] =  '24L';
	 }else{
	 	$shipmentObj->RecordData['MOD'] =  '24R';
	 }
	 
	 if($shipmentObj->RecordData['addservice_id'] ==7 || $shipmentObj->RecordData['addservice_id'] ==146){
	    $shipmentObj->RecordData['PaymentMode'] =  'COD';
	 }else{ 
	   $shipmentObj->RecordData['PaymentMode'] =  'FRANCO';
	 }
	
	 if($shipmentObj->RecordData['addservice_id']==124 || $shipmentObj->RecordData['addservice_id']==148){
	     $servicecode   = 2;
	 }elseif($shipmentObj->RecordData[SERVICE_ID]==1 || $shipmentObj->RecordData[SERVICE_ID]==2){
	    $servicecode   = 0;
	 }else{
	    $servicecode   = 3;
	 }
	 
	 $runnumber     = '67865';
	  
	 $secondcdigitno  =  $shipmentObj->RecordData['AgencyNumber'].$servicecode.$shipmentObj->RecordData['RunNumber'];
	 $controldigit2  =  $this->controlDigit($secondcdigitno);
	 
	 $shipmentObj->RecordData[BARCODE] =  $firstcdigitno.$controldigit.$secondcdigitno.$controldigit2;
	 $shipmentObj->RecordData[TRACENR_BARCODE] =  $firstcdigitno.$controldigit.$secondcdigitno.$controldigit2;
		 return true;
	}
	public function controlDigit($number){
        $multiple = 2;
		$addstep = 0;
		$totallength = strlen($number)- 1;
      for($i=$totallength;$i>=0;$i--){
          if($multiple==8){
		       $multiple = 2;
		  }
		  $addstep =  $addstep + ($number[$i] * $multiple);
		  $multiple++;	    
	  }
	  $stepnumbermode = $addstep % 11;
	  $controllDigit =  11-$stepnumbermode;
	  if($controllDigit==10 || $controllDigit==11){
	      return '0';
	   }
	  return $controllDigit;
  }
  public function getAgencyNumber($shipmentObj){
       $select = $this->_db->select()
	   						->from(array('PS'=>SHIPMENT_PARCELPOINT),array('*'))
							->where("PS.shipment_id='".$shipmentObj->RecordData['shipment_id']."' AND PS.parcel_shop!=''");
							//print_r($select->__toString());die;
	    $locations = $this->getAdapter()->fetchRow($select);
		if(!empty($locations)){
		  $shopsdetail =  json_decode($locations['parcel_shop']);
		  $result['agency_name']  = $shopsdetail->agency_name;
		  $result['agency_code']  = $shopsdetail->agency_code;
		  $result['shuttle_code']  = $shopsdetail->shuttle_code;
		  $result['group_code']  = $shopsdetail->group_code;
		  $result['pr_number']  = $shopsdetail->pr_number;
		  $result['street']  = $shopsdetail->street;
		  $shipmentObj->RecordData['shopdetail'] = array($shopsdetail->company,$shopsdetail->street,$shopsdetail->zipCode,$shopsdetail->city,$shipmentObj->RecordData['rec_cncode']);
		}
		if(empty($locations) || $result['agency_code']==''){
         $select = $this->masterdb->select()
	  							 ->from(array('MRNPR'=>MR_NEARESTPR),array(''))
								 ->joininner(array('MRRT'=>MR_ROUTING),"MRNPR.pr_number=MRRT.pr_number",array('*'))
								 ->joinleft(array('MRAN'=>MR_AGENCY),"SUBSTRING(MRRT.agency_code,1,4)=MRAN.agency_number",array('agency_name'))
								  ->where("MRNPR.zipcode='".$shipmentObj->RecordData[ZIPCODE]."' AND MRNPR.pr_country='".$shipmentObj->RecordData['rec_cncode']."'  AND MRRT.pr_country_code='".$shipmentObj->RecordData['rec_cncode']."' AND MRRT.group_code!='' AND MRRT.shuttle_code!=''")
								 ->order("MRNPR.distance ASC")
								 ->limit(1);
								 //echo $select->__toString();die;
      $result = $this->masterdb->fetchRow($select);
	  $shipmentObj->RecordData['shopdetail'] = array($result['pr_name'],$result['street'],$result['pr_zipcode'],$result['pr_city'],$shipmentObj->RecordData['rec_cncode']);
	}
	return $result;
  }
  public function getAgencyNumberReturn($shipmentObj){
  			$select = $this->masterdb->select()
									 ->from(array('MRNPR'=>MR_SORTING),array('*'))
									 ->joininner(array('AN'=>MR_AGENCY),"AN.agency_number=MRNPR.agency_number",array('agency_name'))
									  ->where("MRNPR.zipcode=1 AND MRNPR.destination_type='DT' AND MRNPR.delivery_mode='LCC' AND MRNPR.country_code='".$shipmentObj->RecordData['rec_cncode']."'");
									  
										 //echo $select->__toString();die;
		$result = $this->masterdb->fetchRow($select);
		  $shopdetail['agency_name']  = $result['agency_name'];
		  $shopdetail['agency_code']  = $result['agency_number'];
		  $shopdetail['shuttle_code']  = $result['shuttle_code'];
		  $shopdetail['group_code']  = $result['group_code'];
		  $shopdetail['pr_number']  = $result['direction'];
		return $shopdetail;
  }
}