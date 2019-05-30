<?php



class settings_Model_Forwarderinvoicecheckin extends Zend_Custom

{

    public $CSVData = array(); 

    public function CheckforwarderInvoice(){

			$file_name = commonfunction::ImportFile('forwarder_invoice_checkin','csv',$this->getData['forwarder_id']);

			$this->getTrackingandWeightPossition();

			$this->readfiles($file_name);

			$this->CheckParcelDatabase();

			

			

	}

	public function readfiles($Csvfile){

	    if (($handle = fopen($Csvfile, "r")) !== FALSE) {

			$counter = 1;

			while (($data = fgetcsv($handle, 1000, ';', ' ')) !== FALSE) {

			  if($counter>1){ 

			     

				$this->CSVData[] = array('weight'=>trim(preg_replace('/\s+/', ' ', str_replace(array('"',','), array('','.'),$data[$this->getData['weightpos']]))),'tracenr_barcode'=>trim(preg_replace('/\s+/', ' ', str_replace('"', '',$data[$this->getData['trackingpos']]))));

			  }

			  $counter++;

		 }

		}  //echo "<pre>";print_r($this->getData);die;

		 return;

	}

	

	public function CheckParcelDatabase(){

	    $CsvExport = 'ParcelNumber;Depot;Status'."\r\n";

		foreach($this->CSVData as $record){

		    $select = $this->_db->select()

									->from(array('BT' =>SHIPMENT_BARCODE),array('*'))

									->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('rec_reference'))

									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('rec_name'))

									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("AT.company_name",'customer_number','user_id'))

									->joininner(array('PAT' =>USERS_DETAILS),"PAT.user_id=AT.parent_id",array("AT.company_name AS depot_company"))

									->where("tracenr_barcode='".$record['tracenr_barcode']."'");

									//print_r($select->__toString());die;

			$recorddata =  $this->getAdapter()->fetchRow($select);	

			if(!empty($recorddata)){

			   if($recorddata['delete_status']=='1'){

			      $CsvExport .= $record['tracenr_barcode'].';'.$recorddata['depot_company'].';Parcel Is delected'."\r\n";

			   }elseif($recorddata['checkin_status']=='0'){

			     $CsvExport .= $record['tracenr_barcode'].';'.$recorddata['depot_company'].';Parcel Not Checked-in'."\r\n";

			   }else{

			     $CsvExport .= $record['tracenr_barcode'].';'.$recorddata['depot_company'].';Parcel Checked-in'."\r\n";

			   }

			}else{

			   $CsvExport .= $record['tracenr_barcode'].';NA;Not in Database'."\r\n";

			}					

		} 

		commonfunction::ExportCsv($CsvExport,'ParcelInDatabase','csv'); 

	}

	

	public function ParcelNotinDatabase(){

	    $CsvExport = 'ParcelNumber;Depot;Status'."\r\n";

		foreach($this->CSVData as $record){

		    $select = $this->_db->select()

									->from(array('BT' =>SHIPMENT_BARCODE),array('tracenr_barcode'))

									->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('rec_reference'))

									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('rec_name'))

									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("AT.company_name",'customer_number','user_id'))

									->joininner(array('PAT' =>USERS_DETAILS),"PAT.user_id=AT.parent_id",array("AT.company_name AS depot_company"))

									->where("tracenr_barcode='".$record['tracenr_barcode']."'");

									//print_r($select->__toString());die;

			$recorddata =  $this->getAdapter()->fetchRow($select);	

			if(empty($recorddata)){

			    $CsvExport .= $record['tracenr_barcode'].';NA;Parcel Not In Database'."\r\n";

			}				

		} 

		commonfunction::ExportCsv($CsvExport,'ParcelInDatabase','csv'); 

	}

	

	public function getTrackingandWeightPossition(){

	     switch($this->getData['forwarder_id']){

		     case 1:

			 case 2:

			 case 3:

			    $this->getData['trackingpos'] = 3;

				$this->getData['weightpos'] = 11; 

			 break;

			 default:

			 $this->getData['trackingpos'] = 3;

			 $this->getData['weightpos'] = 13; 

		 } 

	}

}



