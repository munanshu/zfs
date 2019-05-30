<?php
 

class Application_Model_Performainvoice extends Application_Model_Shipments
{

    public function Invoice($data,$link=false){

	      $where = "CT.continent_id !='2'";
	      $where = $where.$this->LevelClause();
		   $select = $this->_db->select()
                    ->from(array("BT" => SHIPMENT_BARCODE), array("forwarder_id",'SUM(BT.weight) AS weight','GROUP_CONCAT(tracenr_barcode) AS tracenr_barcode','service_id'))
                    ->joininner(array("ST" => SHIPMENT), "ST.shipment_id = BT.shipment_id",array("user_id",'shipment_id','goods_id','goods_description','shipment_worth','senderaddress_id','addservice_id','country_id','rec_name','rec_contact','rec_street','rec_streetnr','rec_street2','rec_address','SUM(quantity) AS quantity','rec_zipcode','rec_city','rec_phone',))
                    ->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("AT.parent_id"))
					->joininner(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array("cncode AS origin_country","CT.country_id","CT.continent_id")); 

		  if(isset($data['BulkProformaPrint']) && !empty($data['BulkProformaPrint']) && $data['BulkProformaPrint'] > 0 ){
		  		$select->where($where);	
		  		$select->limit($data['BulkProformaPrint'],0);	
		  }			
		  elseif(isset($data['shipment_id']) && count($data['shipment_id'])>0){
		     $select->where("ST.shipment_id IN('".commonfunction::implod_array($data['shipment_id'],"','")."') and ".$where);
		  }elseif(isset($data['barcode_id'])){
		     $select->where("BT.barcode_id='".$data['barcode_id']."' and ".$where);
		  }	
		  
		  // echo "<pre>",$select->__toString();die;
		  // echo "<pre>"; 
		  // print_r($data['shipment_id']);die;
		  // echo($select->__toString());die;	

		  $select->group("BT.shipment_id");
		  $performaData = array();	
          $data = $this->getAdapter()->fetchAll($select); 

		  // echo "<pre>"; print_r($data);die;	

          if(empty($data))
          	return false;

		  $file = 'Performa_invoice_'.date('Y-m_d_H_i_s').'.pdf';
		  $InvoicePdfObj = new Zend_Labelclass_Performainvoice('P','mm','a4');
		  ob_start();
		  foreach ($data as $key => $value) {

		  	  $this->RecordData = $value;
			  $performaData[$value['shipment_id']] = $this->ForwarderDetail();
		  	  
		  	  // echo "<pre>"; print_r($performaData);die;	

			  $performaData[$value['shipment_id']]['ShipmentData'] = $this->RecordData;
          	  $InvoicePdfObj->performaInvoice($performaData[$value['shipment_id']]);
		  }

 		  // echo "<pre>"; print_r($performaData); die;	
		  
		  if($link){
		    @$InvoicePdfObj->Output(PERFORMA_INVOICE_SAVE.$file,'F');
		    echo  PERFORMA_INVOICE_OPEN.$file;die;
		  }else{
		    ob_end_clean();
			@$InvoicePdfObj->Output($file,'D');
			$InvoicePdfObj->PopUpLabel();
		  }	
		  //$InvoicePdfObj->Output(PERFORMA_INVOICE_SAVE.'/'.$file,'F');
		  ///return PERFORMA_INVOICE_OPEN.'/'.$file;
	}
	public function getCN23CP71Data($barcode_id){ 
		   $select = $this->_db->select()
                    ->from(array("BT" => SHIPMENT_BARCODE), array("forwarder_id",'barcode_id','barcode','tracenr','tracenr_barcode','local_barcode','reference_barcode'))
					//->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array("checkin_date",'rec_reference'))
                    ->joininner(array("ST" => SHIPMENT), "ST.shipment_id = BT.shipment_id",array("user_id",'goods_id','goods_description','shipment_worth','senderaddress_id','addservice_id','country_id','rec_name','rec_contact','rec_street','rec_streetnr','rec_street2','rec_address','quantity','rec_zipcode','rec_city','rec_phone','create_date'))
                    ->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("user_id",'parent_id'))
					->joininner(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array("cncode AS origin_country",'continent_id','no_of_cn23'))
					->where("barcode_id='".$barcode_id."'"); 
		 $result = $this->getAdapter()->fetchRow($select);
		 global $labelObj;
		 $labelObj = new Zend_Labelclass_PDFLabel('P','mm','a4');
		 $labelObj->SetlabelFormat();
		 $file = 'PDF_'.time().'_'.date('Y_m_d').'.pdf';
		 $this->RecordData = $result;
		 $this->RecordData['forwarder_detail'] = $this->ForwarderDetail();
		 $labelObj->ParcelCount =  $labelObj->ParcelCount + 1;
		 $this->RecordData['parcelcount'] = 1;
		 $this->RecordData['ShipmentCount'] = $this->RecordData['parcelcount'].'/'.$this->RecordData[QUANTITY];
		 $this->RecordData['russianpost'] = 'printA4cn23'; 
		 $this->CreateRussianPostLabel($this,false);
		 //$this->GenerateBarcodeData(false);
		 $labelObj->outputparam = $this->RecordData;
		 $labelObj->labelAllForwarder();
		 $labelObj->AutoPrint(true);
		 $labelObj->_filePath = PRINT_OPEN_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file;
		 $labelObj->Output(PRINT_SAVE_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file,'F');
		 
		 echo $labelObj->_filePath;die;
	 }
}

