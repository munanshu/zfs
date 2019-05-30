<?php
class Application_Model_BRTLabel extends Zend_custom{
	  public $BRTDetail = array();
	  public function CreateBRTLabel($shipmentObj,$newbarcode=true){
			$this->BRTDetail = $shipmentObj->RecordData['forwarder_detail'];
			$shipmentObj->RecordData['country_code'] = $shipmentObj->RecordData['rec_cncode'];
			$shipmentObj->RecordData['Routing'] = $this->getBRTRouting($shipmentObj);
			
			$shipmentObj->RecordData['ArivalDepot'] = $shipmentObj->RecordData['Routing']['CPCLNA'];
			$shipmentObj->RecordData['WLNA'] = $shipmentObj->RecordData['Routing']['CPCLNA'];
			$shipmentObj->RecordData['WZNC'] = str_pad(substr($shipmentObj->RecordData['Routing']['CPCZNC'],0,2),2,0,STR_PAD_LEFT);
			$shipmentObj->RecordData['DeliveryZone'] = trim($shipmentObj->RecordData['Routing']['CPCZNC']);
			$shipmentObj->RecordData['DeliveryTimecode'] = $shipmentObj->RecordData['Routing']['CPCTTC'];
			
			$shipmentObj->RecordData['DepartureDepot'] = 107;
			$shipmentObj->RecordData['Terminal'] = $this->getDepotDescription($shipmentObj);
			//$parcelcount     = explode('/',$shipmentObj->RecordData['ShipmentCount']);
	 		$shipmentObj->RecordData['Series']  = '01';
			$barcoderaw = $shipmentObj->RecordData['DepartureDepot'].str_pad(substr($shipmentObj->RecordData['ArivalDepot'],0,3),3,0,STR_PAD_LEFT).$shipmentObj->RecordData['Series'].$shipmentObj->RecordData[TRACENR].str_pad(substr($shipmentObj->RecordData['DeliveryZone'],0,2),2,0,STR_PAD_LEFT);
			
			$checkdigit = $this->BRTcheckdigit($barcoderaw);
			$shipmentObj->RecordData[BARCODE] = $barcoderaw.$checkdigit;
			$shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
			$shipmentObj->RecordData['DeliveryService']  = 'E';
			return true;
    	
	  }
	  public function getBRTRouting($shipmentObj){
	    $select = $this->masterdb->select()
                        ->from(array(BRT_ROUTING), array('*'))
                        ->where("CPCVER=46 AND (CPCNAR='".$shipmentObj->RecordData['rec_cncode']."' OR CPCNAR='') AND CPCCAP='".preg_replace('/[^0-9+]/', '',$shipmentObj->RecordData[ZIPCODE])."'");
						//echo $select->__tostring();die;
   		$result = $this->masterdb->fetchRow($select);
		if($shipmentObj->RecordData['weight']<$result['CPCLKS']){
		   $result['CPCZNC'] = $result['CPCZOS'];
		}elseif($shipmentObj->RecordData['weight']>$result['CPCLKS'] && $shipmentObj->RecordData['weight']<=$result['CPCLKG']){
		   $result['CPCZNC'] = $result['CPCZNC'];
		}
		if($shipmentObj->RecordData['weight']<$result['CPCLKS']){
		   $result['CPCLNA'] = $result['CPCLOS'];
		}elseif($shipmentObj->RecordData['weight']>$result['CPCLKS'] && $shipmentObj->RecordData['weight']<=$result['CPCLKG']){
		   $result['CPCLNA'] = $result['CPCLNA'];
		}
		return $result;
	}
	
	public function getDepotDescription($shipmentObj){
	    $select = $this->masterdb->select()
                        ->from(array(BRT_TERMINAL), array('*'))
                        ->where("CAEEPA='A' AND CAETFA='".$shipmentObj->RecordData['Routing']['CPCLNA']."'");
  						 //echo $select->__tostring();die;
   		$result = $this->masterdb->fetchRow($select);
		return $result;
	}
	public function BRTcheckdigit($number){
       $str_rev = strrev($number);
	   $evensum = 0;
	   $oddsum = 0;
	   for($i=strlen($number);$i>=0;$i--){
	      $num =  ($i%2==0)?$number[$i]*2:'';
		 
		  $evensum = ($num>9)?($evensum + substr($num,0,1)+substr($num,1)):($evensum + $num); 
	   }
	   for($j=strlen($number);$j>=0;$j--){
	      $num1 =  ($j%2==0)?0:isset($number[$j]);
		  $oddsum = $oddsum + $num1;
	   }
	   $total = (10-($evensum + $oddsum)%10);
	   if($total==10){
	      return 0;
	   }else{
	      return $total;
	   }
   }
}