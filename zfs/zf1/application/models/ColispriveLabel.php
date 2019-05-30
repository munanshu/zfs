<?php
class Application_Model_ColispriveLabel extends Zend_custom{
	 public $colisprivedata = array();
	  public function CreateColispriveLabel($shipmentObj,$newbarcode=true){
			   $this->colisprivedata = $shipmentObj->RecordData['forwarder_detail'];
		       $shipmentObj->RecordData['route'] = $this->getPostcodecoveredArea($shipmentObj);
				if($newbarcode){
					if(trim($shipmentObj->RecordData['route']['pc_covered'])!='O'){						  
					   $shipmentObj->RecordData[TRACENR] = $this->getColisimoProfileTracking($shipmentObj);
					}	
				}
		 $this->colissimoBarcodeFormation($shipmentObj);
	  }
	  
	 
     public function colissimoBarcodeFormation($shipmentObj){
			$serviceCode =  $this->colisprivedata['barcode_prefix'];
			$rec_zipcode    = $shipmentObj->RecordData[ZIPCODE];
			
			if(!empty($shipmentObj->RecordData['route']) && isset($shipmentObj->RecordData['route']['pc_covered']) && trim($shipmentObj->RecordData['route']['pc_covered'])=='O'){
				 $shipmentObj->RecordData['local_barcode'] = $this->colisprivedata['contract_number'].str_pad($shipmentObj->RecordData[TRACENR],10,'0',STR_PAD_LEFT).substr($shipmentObj->RecordData[ZIPCODE],0,5);
				$shipmentObj->RecordData[BARCODE] = $this->colisprivedata['contract_number'].str_pad($shipmentObj->RecordData[TRACENR],10,'0',STR_PAD_LEFT).substr($shipmentObj->RecordData[ZIPCODE],0,5);
				$shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
			}elseif(!empty($shipmentObj->RecordData['route']) && isset($shipmentObj->RecordData['route']['pc_covered']) && trim($shipmentObj->RecordData['route']['pc_covered'])=='F' && ($shipmentObj->RecordData['addservice_id']==7 || $shipmentObj->RecordData['addservice_id']==146)){
			     $shipmentObj->RecordData['local_barcode'] = $this->colisprivedata['contract_number'].str_pad($shipmentObj->RecordData[TRACENR],10,'0',STR_PAD_LEFT).substr($shipmentObj->RecordData[ZIPCODE],0,5);
			     $shipmentObj->RecordData[BARCODE] = $this->colisprivedata['contract_number'].str_pad($shipmentObj->RecordData[TRACENR],10,'0',STR_PAD_LEFT).substr($shipmentObj->RecordData[ZIPCODE],0,5);
				 $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
				 $shipmentObj->RecordData['route']['branch_code'] = 99;
				 $shipmentObj->RecordData['route']['round_number'] = 9999;
			}else{
			    $checkDigit = $this->getCollispriveCheckDigit($shipmentObj->RecordData[TRACENR]);
				$shipmentObj->RecordData[BARCODE] = $serviceCode.$shipmentObj->RecordData[TRACENR].$checkDigit;
				$shipmentObj->RecordData['print_barcode'] = $serviceCode.'  '.chunk_split($shipmentObj->RecordData[TRACENR],5,'  ').'  '.$checkDigit;
				$shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
				
			    //Reroute Barcode
				$sortcode  = 1;
				
				$shipmentObj->RecordData['CustomerNo'] = $this->colisprivedata['customer_number'];
				$weightindeca   = str_pad(($shipmentObj->RecordData[WEIGHT] / 0.01),4,'0',STR_PAD_LEFT);
				$reserveedzone 	  = '00';
				$machinablesort   = '0';
				$deliveryPayment  = (($shipmentObj->RecordData['addservice_id']==7 || $shipmentObj->RecordData['addservice_id']==146) && $shipmentObj->RecordData['cod_price']>0)?1:'0';
				$controlllink     = substr($shipmentObj->RecordData[TRACENR],-1);
				
				$combineData = $shipmentObj->RecordData['CustomerNo'].$weightindeca.$reserveedzone.$machinablesort.$deliveryPayment.$controlllink;
				$checkDigitRoute = $this->getCollispriveCheckDigit($combineData);
				
				$shipmentObj->RecordData[REROUTE_BARCODE] = $serviceCode.$sortcode.$rec_zipcode.$combineData.$checkDigitRoute;
				$shipmentObj->RecordData['print_reroute'] = $serviceCode.' '.$sortcode.' '.$rec_zipcode.'  '.$shipmentObj->RecordData['CustomerNo'].'  '.$weightindeca.'  '.$reserveedzone.$machinablesort.$deliveryPayment.$controlllink.$checkDigitRoute;
			}
			return true;
		} 
	  /**
	 * @Function : getColissiomoCheckDigit()
	 * @Description : this function calculate check digit for colissimo forwarder on mod 10 algorithm
     */	
	 public function getCollispriveCheckDigit($number){
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
	 
	 public function getPostcodecoveredArea($shipmentObj){
	      $select = $this->masterdb->select()
	   					->from(array('CR'=>COLISPRIVE_ROUTE),array('*'))
						->where("CR.postcode='".$shipmentObj->RecordData['rec_zipcode']."'");//echo $select->__toString();die;
		$coveredarea = $this->masterdb->fetchRow($select);
		return $coveredarea;
	 }
	 
	 public function getColisimoProfileTracking($shipmentObj){
	     $select = $this->_db->select()
	   					->from(array('FT'=>FORWARDER_COLISPRIVE),array('*'))
						->where("forwarder_id=16");
		 $forwarderdata = $this->getAdapter()->fetchRow($select);
		 $tracking_number =  $forwarderdata['last_tracking'] + 1;
		 $this->_db->update(FORWARDER_COLISPRIVE,array('last_tracking'=>$tracking_number),"forwarder_id=16");
		 $shipmentObj->RecordData['local_barcode'] = $this->colisprivedata['contract_number'].str_pad($shipmentObj->RecordData[TRACENR],10,'0',STR_PAD_LEFT).substr($shipmentObj->RecordData[ZIPCODE],0,5);
		 return str_pad($tracking_number,10,'0',STR_PAD_LEFT);
	 }	
		
		
	
}