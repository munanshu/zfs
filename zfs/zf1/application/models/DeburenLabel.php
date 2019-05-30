<?php
class Application_Model_DeburenLabel extends Zend_custom{
	  public function CreateDeburenLabel($shipmentObj,$newbarcode=true){
	      $shipmentObj->RecordData[BARCODE] = $shipmentObj->RecordData['forwarder_detail']['barcode_prefix'].$shipmentObj->RecordData[TRACENR]; 
		  $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
		  $this->getAddressParcelpoint($shipmentObj); //echo "<pre>";print_r($shipmentObj->RecordData);die;
	  }
	  public function getAddressParcelpoint($shipmentObj){
	     $select = $this->_db->select()
	   						->from(array('PS'=>SHIPMENT_PARCELPOINT),array('*'))
							->where("PS.shipment_id='".$shipmentObj->RecordData['shipment_id']."' AND PS.parcel_shop!='' AND shop_type=3");
							//print_r($select->__toString());die;
	    $result = $this->getAdapter()->fetchRow($select);
	    if(empty($result)){
		   $comObj = new Application_Model_Common();
		    $locations = $comObj->DeburenParcelpoints($shipmentObj->RecordData['rec_cncode'],$shipmentObj->RecordData[ZIPCODE]);
		    $distance = array();
			foreach($locations as $key => $row) { 
				$distance[$key]  = $row['distance'];
			} 
			array_multisort($distance, SORT_ASC, $locations);
			$location = json_encode($locations[0]);
			$this->AddUpdateParcelpoint($shipmentObj,$location);
			$result = json_decode($location);
		}
		 $shipmentObj->RecordData['ParcelShop'] = array('locker_title'=>$result->company,'locker_address'=>$result->street,'locker_streetno'=>$result->houseNo,'locker_zipcode'=>$result->zipCode,'locker_city'=>$result->city,'shop_type'=>$result->shop_type);
	 } 
	 public function AddUpdateParcelpoint($shipmentObj,$locations){
	        $select = $this->_db->select()
	   						->from(array('PS'=>SHIPMENT_PARCELPOINT),array('*'))
							->where("PS.shipment_id='".$shipmentObj->RecordData['shipment_id']."' AND shop_type=3");
							//print_r($select->__toString());die;
	       $result = $this->getAdapter()->fetchRow($select);
		   if(!empty($result)){
		      $this->_db->update(SHIPMENT_PARCELPOINT,array('parcel_shop'=>json_encode($locations)),"shipment_id='".$shipmentObj->RecordData['shipment_id']."' AND shop_type=3");
		   }else{
		     $this->_db->insert(SHIPMENT_PARCELPOINT,array('shipment_id'=>$shipmentObj->RecordData['shipment_id'],'parcel_shop'=>json_encode($locations),'shop_type'=>3));
		   }
	 }	
	   
}