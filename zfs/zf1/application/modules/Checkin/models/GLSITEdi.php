<?php

class Checkin_Model_GLSITEdi extends Zend_Custom
{
   public $ForwarderRecord = array();
   public $Forwarders	= array();
   public $EdiShipmentApi;
    
	public function generateEDI($data){
		// print_r($data);die;
	  $this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);
	  try{
	   $select = $this->_db->select()
							->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
							->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE))
							->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,ADDRESS,CITY,ZIPCODE,STREET2,PHONE,EMAIL,
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,'user_id'))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")");
	    $results = $this->getAdapter()->fetchAll($select);
						// echo "<pre>";print_r($results);die;
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	    $ediBody = '';
		foreach($results as $result){
		    $this->RecordData = $result;
		  	$this->ForwarderRecord = $this->ForwarderDetail();
	    	@$this->EdiShipmentApi->RecordData = $result;
	    	$this->EdiShipmentApi->RecordData['forwarder_detail'] = $this->ForwarderRecord;
	    	// echo "<pre>"; print_r($this->EdiShipmentApi);die;
	    	$this->SendGLSManifestApiRequest($this->EdiShipmentApi);
            $ediBody .= $this->GLSITEDIBody($result);
		}
		$fileName = "GLS_IT_".$this->Forwarders['IFD_number']."_D".date('Ymd')."T".date('his');
	   return array('EdiData'=>$ediBody,'EdiFilename'=>$fileName);
	}
	/**
	*Generate EDI For GLSIT
	*Function : GLSITEDIBody()
	*Function Generate EDI for GLSIT forwarder
	**/
	public function GLSITEDIBody($data){
	    $PARCEL_DATA = '';
		//Parcel Detail
		$parcelnumber = $data[BARCODE];
		$weight		  = $data[WEIGHT];
		//$servicedetail = $this->getServiceDetails($data[SERVICE_ID]);
		//$service	  = $servicedetail['internal_code'];
		$createdatetime = $data[CREATE_DATE];
		$reference = commonfunction::utf8Decode($data[REFERENCE]);
		$numofparcel = 1;
		
		//Sender Detail
		 $SenderInfo  = $this->ForwarderRecord['SenderAddress'];

		$SenderName1          = $SenderInfo[0];
		$SenderName2          = $SenderInfo[1];
		$SenderStreet         = $SenderInfo[2];
		$SenderCountry        = $SenderInfo[5];
		$SenderPostcode       = $SenderInfo[4];
		$SenderCity           = $SenderInfo[3]; 
		
		$sender_data = $this->getCustomerDetails($data['parent_id']);
		$uniueSender = $sender_data['company_name'];
		//$uniueSender = 'GLSIT';
		if($data['parent_id']==3792){
		  $uniueSenderCus =  $SenderInfo[0];
		}else{
		   $uniueSenderCus =  $uniueSender;
		}
		$PARCEL_DATA .= $this->Forwarders['customer_number']."|".$uniueSender."|".$uniueSenderCus."|300|".$parcelnumber."|".$SenderName1."|".$SenderName2."|".$SenderStreet."|".$SenderCountry."|".$SenderPostcode."|".$SenderCity."|";
		
		// Receiver Data
		$RecipientName1              = commonfunction::utf8Decode($data[RECEIVER]);
		$RecipientName2              = commonfunction::utf8Decode($data[CONTACT]);
		$RecipientStreet             = commonfunction::utf8Decode($data[STREET]);
		$RecipientHouseNo            = commonfunction::utf8Decode($data[STREETNR]);
		$RecipientCountry            = $data['iso_code'];
		$RecipientPostcode           = $data[ZIPCODE];
		$RecipientCity               = commonfunction::utf8Decode($data[CITY]);
		$RecipientTelephone          = $data[PHONE];
		$RecipientEmail              = $data[EMAIL];
		$RecipientComment            = $reference;
		
		$PARCEL_DATA .= "GLSIT|500|".$parcelnumber."|".$RecipientName1."|".$RecipientName2."|".$RecipientStreet."|".$RecipientHouseNo."|".$RecipientCountry."|".$RecipientPostcode."|".$RecipientCity."|".$RecipientTelephone."|".$RecipientEmail."|".$RecipientComment."|".commonfunction::stringReplace('.',',',commonfunction::numberformat($weight,2))."\r\n";
		
		
		if($data[ADDSERVICE_ID]==7 || $data[ADDSERVICE_ID]==146) {
			$servicetag  		= "COD";
			$CODAmount          = $data['cod_price'];
			$currency	     	= ($data['currency']!='')?$data['currency']:'EUR'; 
		}
	
		return $PARCEL_DATA;									
	}

	public function SendGLSManifestApiRequest($shipment)
	{

		$GlsITLabelObj = new Application_Model_GLSITLabel();
		
		if(!empty($shipment)){

				$barcodeId = $shipment->RecordData['barcode_id'];
				$xml = $GlsITLabelObj->PrepareApiXmlData($shipment);
				$xml = str_replace(array("AddParcel","XMLInfoParcel"), array('CloseWorkDay','XMLCloseInfoParcel'), $xml);
				$GlsITLabelObj->xml_string = $xml;	
				echo $xml;die;
				$response[$barcodeId] = $GlsITLabelObj->SendApiCWDRequest('CloseWorkDay');
				$is_Updated = ($response[$barcodeId]['status'] == 1)? 1 : 0 ;
				$message = ($response[$barcodeId]['status'] == 1)? $response[$barcodeId]['message'] : $response[$barcodeId]['response']->Body->CloseWorkDayResponse->CloseWorkDayResult->DescrizioneErrore ;
					$ResponseData = array(
						'barcode_id' => $barcodeId,
						'forwarder_id' => $shipment->RecordData['forwarder_detail']['forwarder_id'],
						'user_id' => $shipment->RecordData['user_id'],
						'is_updated' => $is_Updated,
						'message' => $message,
					);	
					$this->SaveManifestApiResponse($ResponseData);

		}

	}


	public function SaveManifestApiResponse($data='')
	{
		try {
			
			$CreatedByDetails = commonfunction::createdByDetails($this->Useconfig['user_id']);
			$ResponseData = array_merge($data,$CreatedByDetails);
			$res = $this->_db->insert(MANIFEST_API_REQUESTS,$ResponseData);
			return $res? true : false;

		} catch (Exception $e) {
			return false;
		}

	}


}

