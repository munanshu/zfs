<?php
class Application_Model_ParcelnlLabel extends Zend_custom{
	  public function CreateParcelnlLabel($shipmentObj,$newbarcode=true){
	     if($newbarcode){
	      $shipmentObj->RecordData[BARCODE] = $shipmentObj->RecordData['forwarder_detail']['barcode_prefix'].$shipmentObj->RecordData[TRACENR]; 
		  $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
		 } 
		  if($shipmentObj->RecordData['addservice_id']==126 || $shipmentObj->RecordData['addservice_id']==3 || $shipmentObj->RecordData['addservice_id']==149){
		    $this->getOwnShopAddress($shipmentObj);
		  }
		  if($shipmentObj->RecordData['service_id']==139){
		     $this->getTurminalName($shipmentObj);
		  }
	  }
	  
	  public function getOwnShopAddress($shipmentObj){
	 	$select = $this->_db->select()
	   						->from(array('PS'=>SHIPMENT_PARCELPOINT),array('*'))
							->where("PS.shipment_id='".$shipmentObj->RecordData['shipment_id']."' AND PS.parcel_shop!=''");
							//print_r($select->__toString());die;
	    $locations = $this->getAdapter()->fetchRow($select);
		if(!empty($locations)){
		  $locations =  json_decode($locations['parcel_shop']); 
		  $shipmentObj->RecordData['ParcelShop'] = array('shop_name'=>$locations->company,'address'=>$locations->street.' '.$locations->houseNo,'zipcode'=>$locations->zipCode,'city'=>$locations->city,'country'=>$shipmentObj->RecordData['rec_country_name']);
		  return;
		}else{ 
		    $countrycode = $shipmentObj->RecordData['rec_cncode'];
			$comobj  = new Application_Model_Common();
		    $locations = $comobj->OwnParcelpoints($countrycode,$shipmentObj->RecordData[ZIPCODE]);
			foreach($locations as $location){
			     $this->_db->insert(SHIPMENT_PARCELPOINT, array('shipment_id'=>$shipmentObj->RecordData['shipment_id'],'parcel_shop'=>json_encode($location)));
				 $shipmentObj->RecordData['ParcelShop'] = array('shop_name'=>$location['company'],'address'=>$location['street'].' '.$location['houseNo'],'zipcode'=>$shipmentObj->RecordData['rec_cncode'],'city'=>$location['city'],'country'=>$shipmentObj->RecordData['rec_country_name']);
				 //print_r($location);die;
	           return;
			 } 
		}
	}
	
	public function getTurminalName($shipmentObj){
	    $select = $this->_db->select()
	   						->from(array('CP'=>COUNTRYPORT),array('*'))
							->where("CP.port_id='".$shipmentObj->RecordData['terminal_id']."'");
							//print_r($select->__toString());die;
	    $terminal = $this->getAdapter()->fetchRow($select); 
		 $shipmentObj->RecordData['port_name'] = $terminal['port_name'];
	}
}