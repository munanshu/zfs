<?php 
class Application_Model_GLSFreightLabel extends Application_Model_Socketprocessing{
      public $GLSFdata = array();
	  public function CreateGLSFreightLabel($shipmentObj,$newbarcode=true){
	        $this->SocketForwarder  =  $shipmentObj->RecordData['forwarder_detail'];
			$shipmentObj->RecordData['service_unitcode'] =  $this->FreightSubservice($shipmentObj->RecordData['addservice_id']);
			$shipmentObj->RecordData['unique_reference'] =  $this->getUniueReferenceforFreight(commonfunction::Alphanumeric($shipmentObj->RecordData[REFERENCE]));
			$this->GlsFTSocketProcessing($shipmentObj);
			//$this->GlsFT2DBarcodePrinting($shipmentObj); 
	  }
	  
	  public function GlsFTSocketProcessing($shipmentObj){
		    $this->SocketData = $shipmentObj->RecordData;
		    $socketdata = $this->SocketReturnGFT(); 
			$shipmentObj->RecordData['SocketResult'] = $socketdata['SocketLabel'];
			$explodebarcode =  commonfunction::explode_string($socketdata['SocketLabel']['T979'],',');
			$shipmentObj->RecordData[REROUTE_BARCODE] = $socketdata['Reroute'];
			if(isset($explodebarcode[1]) && empty($socketdata['E000']) && trim($explodebarcode[1])!=''){
				$shipmentObj->RecordData[BARCODE] = $explodebarcode[1];
				$shipmentObj->RecordData['tracenr_barcode'] = $shipmentObj->RecordData[BARCODE];
				$shipmentObj->RecordData['tracenr'] = $shipmentObj->RecordData[BARCODE];
			}else{
				/*$shipmentObj->RecordData[BARCODE] = $shipmentObj->RecordData['tracenr'];
				$shipmentObj->RecordData['tracenr_barcode'] = $shipmentObj->RecordData[BARCODE];
				$shipmentObj->RecordData['tracenr'] = $shipmentObj->RecordData[BARCODE];*/
				 $this->_db->update(SHIPMENT,array('forwarder_id'=>22,'wrong_parcel'=>'1'),"shipment_id=".$shipmentObj->RecordData[SHIPMENT_ID]."");
				$shipmentObj->RecordData[FORWARDER_ID]	= 22;
				$shipmentObj->RecordData['forwarder_detail'] = $shipmentObj->ForwarderDetail();
				$shipmentObj->GenerateBarcodeData(true);
			}
		}
		
	  /*public function GlsFT2DBarcodePrinting($shipmentObj){
		   $GLSNLbarcode = $this->Create2Dbarcode($shipmentObj->RecordData['SocketResult']['T8902'],$shipmentObj->RecordData['SocketResult']['T8903'],$shipmentObj->RecordData[TRACENR]);
		   $shipmentObj->RecordData['PrimaryBarcode']	 = $GLSNLbarcode['Pri'];
		   $shipmentObj->RecordData['SecondryBarcode']	 = $GLSNLbarcode['Sec'];
		  return true;
		}*/
		public function FreightSubservice($addservice_id){
			  $addservice_id = ($addservice_id>0)?$addservice_id:110;
			  $select = $this->masterdb->select()
						  ->from(array('FS'=>FREIGHT_SUBSERVICE),array('unit_code'))
						  ->where("FS.addservice_id='".$addservice_id."'");
			 $result = $this->masterdb->fetchRow($select);		
			 return $result['unit_code'];	  
		}
		
		public function getUniueReferenceforFreight($reference){
			  $select = $this->_db->select()
									  ->from(array('BD'=>SHIPMENT_BARCODE_DETAIL),array('COUNT(1) AS CNT'))
									  ->where("BD.rec_reference='".$reference."'");
			  $result = $this->getAdapter()->fetchRow($select);
		  if($result['CNT']>0){
			$reference = commonfunction::sub_string($reference,0,9).commonfunction::sub_string(commonfunction::string_suffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,1);
			return $this->getUniueReferenceforFreight($reference);
		  }else{
			return $reference;
	  }
  }
}