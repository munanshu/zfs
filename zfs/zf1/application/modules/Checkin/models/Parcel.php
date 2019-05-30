<?php

class Checkin_Model_Parcel extends Zend_Custom
{
    
    
	public function UpdateParcelDelivery(){
	   
	try {
			// echo "<pre>"; print_r($this->getData);
			
			// echo $delivery_date_time;
			// die;

		$select = $this->_db->select()->from(array('SB'=>SHIPMENT_BARCODE),array('barcode_id','tracenr','tracenr_barcode','barcode','delivery_status'))
		->joininner(array('ST'=>SHIPMENT),'ST.shipment_id=SB.shipment_id',array('ST.country_id','ST.rec_city'))	
		->where("tracenr='".$this->getData['barcode']."' OR tracenr_barcode='".$this->getData['barcode']."'");
		$result = $this->getAdapter()->fetchRow($select);

		// echo "<pre>";  
		if(!empty($result)){

			$modify_details = array('delivery_status'=>1);
			$where = "barcode_id='".$result['barcode_id']."'";

			$upd = $this->_db->update(SHIPMENT_BARCODE,$modify_details,$where);
			$date = new Zend_Db_Expr('NOW()');	
			$delivery_date_time = date('Y-m-d h:i A', strtotime($this->getData['date_time']." ".$this->getData['time']));
			// echo $delivery_date_time;die;
			   $barcode_details = array(
			   		'delivery_date'=> $delivery_date_time,
			   		'received_by'=> $this->getData['rec_name'],
			   		'current_status'=> 1,
			   	);		
			   $upd1 = $this->_db->update(SHIPMENT_BARCODE_DETAIL,$barcode_details,$where);
			   if($upd1){

				   $TrackingLogs = array(
				   		'barcode_id'=>$result['barcode_id'],
				   		'status_id'=>$this->getData['status'],
				   		'status_date'=>$date,
				   		'status_location'=>$result['rec_city'],
				   		'country_id'=>$result['country_id'],
				   		'added_date'=>$date,
				   		'added_by'=>$this->Useconfig['user_id'],
				   	);
				   $ins = $this->_db->insert(PARCEL_TRACKING,array_filter($TrackingLogs));
			   }
			if($upd1){
				$resp = array('status'=>1,'message'=>'Updated Successfully');
			}
			else $resp = array('status'=>0,'message'=>'Some Internal error Occurred');
				 // $resp = array('status'=>2,'message'=>'Delivery Already Updated');
		}	
		else $resp = array('status'=>0,'message'=>'No Tracking code found');

		} catch (Exception $e) {
			$resp = array('status'=>0,'message'=>$e->getMessage());	
		}	

		return $resp; 

	}
	 
	public function UploadAdditionalDoc()
	{
		$barcode_id = Zend_Encript_Encription::decode($this->getData['bid']);

		try {
			
			$select = $this->_db->select()
			->from(array('AD'=>ADDITIONAL_DOCUMENTS),array('count(AD.barcode_id) as entries'))
			->where('AD.barcode_id=?',$barcode_id);
			$result =  $this->getAdapter()->fetchRow($select);
			// print_r($result);die;
			if($result['entries'] <=0){

					$select = $this->_db->select()
					->from(array('BD'=>SHIPMENT_BARCODE),array('BD.barcode_id'))
					->joininner(array('SBD'=>SHIPMENT_BARCODE_DETAIL),'SBD.barcode_id=BD.barcode_id',array('SBD.label_date'))
					->joininner(array('ST'=>SHIPMENT),'ST.shipment_id=BD.shipment_id',array('ST.rec_name','ST.create_date'))
					->where('BD.barcode_id=?',$barcode_id);

					// echo $select->__toString();die;
					$result =  $this->getAdapter()->fetchRow($select);
						// print_r($result);die;

					if(!empty($result)){
						$allowed_formats = array('docx','doc','txt','csv','xlsx','pdf');
						$upload = new Zend_File_Transfer();
						$file = $upload->getFileInfo()['filedata'];
						$ext = pathinfo($file['name'] , PATHINFO_EXTENSION);

							// echo $result['create_date'];die;
						if(!isset($result['create_date'])){
							$resp = array('status'=>0,'message'=>'parcel date is invalid');
							return $resp;
						}elseif($result['create_date'] == '0000-00-00 00:00:00' || empty($result['create_date'])){
							$resp = array('status'=>0,'message'=>'parcel date is invalid');
							return $resp;
							
						}

						if(in_array($ext, $allowed_formats )){
							$year = date("Y", strtotime($result['create_date']));
							$month = date("M", strtotime($result['create_date']));
							$filepath = $year."_".$month."/".$result['rec_name']."_".time()."_".$file['name'];
							$fullfilepath = ADDITIONAL_DOC_SAVE.$filepath;
							$filedir = dirname($fullfilepath);
							if(!is_dir($filedir)){
								mkdir($filedir,0755,true);	
							}

							// echo $fullfilepath;die;
							$upload->addFilter('Rename', array('target' =>$fullfilepath, 'overwrite' =>true));
							$upload->receive($file['name']);

							if(file_exists($fullfilepath)){
								$InsData = array(
										'barcode_id' => $barcode_id,
										'upload_file'=> $filepath,
										'uploaded_ip' => commonfunction::loggedinIP(),
										'upload_date' => commonfunction::DateNow(),
										'uploaded_by' => $this->Useconfig['user_id'],
									);
								$res = $this->_db->insert(ADDITIONAL_DOCUMENTS,array_filter($InsData));
								if($res)
									$resp = array('status'=>1,'message'=>'document uploaded successfully','filepath'=>$fullfilepath);
								else $resp = array('status'=>0,'message'=>'some internal error occurred');
							}
							else $resp = array('status'=>0,'message'=>'sorry document can not be uploaded');

							
						}else $resp = array('status'=>0,'message'=>'only '.implode(',',$allowed_formats).' are allowed');

					}else $resp = array('status'=>0,'message'=>'entry not found');

			}else $resp = array('status'=>0,'message'=>'Please delete previously uploaded document first');


			
		} catch (Exception $e) {
			$resp = array('status'=>1,'message'=>$e->getMessage());	
		}

		return $resp;
	}

	public function deleteAdditionalDoc()
	 {
	 	$document_id = Zend_Encript_Encription::decode($this->getData['doc_id']);
	 	try {
	 		// $Data = array(
	 		// 		'is_removed'=>1
	 		// 	);
	 		// $modify_details = commonfunction::modifiedByDetails($this->Useconfig['user_id']);
	 		// $updData = array_merge($Data,$modify_details);
	 		$where = "document_id=".$document_id;
	 		// $res = $this->_db->update(ADDITIONAL_DOCUMENTS,$updData,$where);
	 		$res = $this->_db->delete(ADDITIONAL_DOCUMENTS,$where);
	 		if($res)
	 			$resp = array('status'=>1,'message'=>'deleted successfully');
	 		else $resp = array('status'=>0,'message'=>'some internal error occurred');
	 	} catch (Exception $e) {
	 		$resp = array('status'=>0,'message'=>$e->getMessage());	
	 	}
		return $resp;
	 	 
	 }

	public function AddDocDownload()
	{
	 	$document_id = Zend_Encript_Encription::decode($this->getData['doc_id']);
		try {
	 		 
	 		$where = "document_id=".$document_id;
	 		$select = $this->_db->select()
			->from(array('AD'=>ADDITIONAL_DOCUMENTS),array('AD.upload_file')) 
			->where($where);
			$res = $this->getAdapter()->fetchRow($select);

	 		if($res){

	 			header('Content-Type: '.filetype(ADDITIONAL_DOC_SAVE.$res['upload_file']).'');
			    header('Content-Disposition: attachment; filename="'.basename($res['upload_file']).'"');
			    readfile(ADDITIONAL_DOC_SAVE.$res['upload_file']);
			    // echo ADDITIONAL_DOC_SAVE.$res['upload_file'];
			    die;
	 			// $resp = array('status'=>1,'message'=>'deleted successfully');
	 		}
	 		else $resp = array('status'=>0,'message'=>'some internal error occurred');
	 	} catch (Exception $e) {
	 		$resp = array('status'=>0,'message'=>$e->getMessage());	
	 	}
	 	return $resp;
	}  
	
}

