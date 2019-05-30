<?php
class Application_Model_BizCourierLabel extends Zend_custom{

	private $api_base = "http://www.bizcourier.eu/pegasus_cloud_app/service_01/";
	private $api_uri = "shipmentCreation_v2.php?wsdl";
	private $HttpHeaders = array();
	private $ApiData = array();
	private $client = array();
	 

	public function ConnectApi()
	{	
   		$this->client = new SoapClient( $this->api_base.$this->api_uri , array('trace' => 1));
	}


	  public function CreateBizCourierLabel($shipmentObj,$newbarcode=true){ 

      	if($newbarcode){
                    // echo "$newbarcode";die;	

	  		$this->ConnectApi();

	  		if(is_object($this->client) &&  property_exists($this->client, 'sdl') )
	  		$this->PrepareApiData($shipmentObj);
	  	

		  	if($shipmentObj->RecordData['quantity']>0){

	            $result['Shipment'] = $this->SendApiLabelRequest();
	          // echo "<pre>";  print_r($result['Shipment']);die;
	            if($result['Shipment']['status']==1){

	                  $response = $this->SaveApiLabelResponse($shipmentObj,$result['Shipment']['response']);

	                  return true;

	            }
	            else { 
	                $this->UpdateForwarder($shipmentObj);
	               $resp = $result['Shipment'];
	              return true;
	            }
	                     
	        }

		 
		} 
      	else $this->MakeLabeldata($shipmentObj);

	 	

	  }

	  public function MakeLabeldata($shipmentObj){
	      $select = $this->_db->select()
									->from(YODEL_PDF,array('*'))
									->where("barcode='".$shipmentObj->RecordData[BARCODE]."'");
									//print_r($select->__toString());die;
		  $record = $this->getAdapter()->fetchRow($select);
		  if(!empty($record)){
		       $decodeddata = json_decode($record['pdf_contant'],true);

			   $shipmentObj->RecordData['BARCODE_READABLE'] = $decodeddata['Barcode'];
			   $shipmentObj->RecordData['pdf_data'] = $decodeddata;
		  }
	   }

	  public function UpdateForwarder($shipmentObj){
      
      	$this->_db->update(SHIPMENT,array('forwarder_id'=>34,'wrong_parcel'=>'1'),"shipment_id=".$shipmentObj->RecordData[SHIPMENT_ID]."");
      }

	  public function SaveApiLabelResponse($shipmentObj='',$response='')
	  {


	 	 $Barcode = $response->Voucher;	

          $shipmentObj->RecordData[BARCODE] = $Barcode;
          $shipmentObj->RecordData['BARCODE_READABLE'] = $shipmentObj->RecordData[BARCODE];
          $shipmentObj->RecordData[REROUTE_BARCODE] = $shipmentObj->RecordData[BARCODE];
          $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
          $shipmentObj->RecordData[TRACENR] = $shipmentObj->RecordData[BARCODE];

          $Barcode_Text = "*$Barcode*";

		  $pdfdata = array(
		 			'Voucher' => 	$Barcode,
		 			'Name' => 	$this->ApiData['R_Name'] ,
		 			'Address' => $this->ApiData['R_Address'],
		 			'PC' => 	$this->ApiData['R_PC'],
		 			'Area' => 	$this->ApiData['R_Area'],
		 			'Telephone' => 	$this->ApiData['R_Phone1'],
		 			'Email' => 	$this->ApiData['R_Email'],
		 			'SenderName' => 'demouser2',
		 			'SenderAddress' => 	'Demo Address',
		 			'SenderPC' => 	17273,
		 			'SenderArea' => 'Demo Area'  ,
		 			'SenderTelephone' => 	'9090909090909',
		 			'SenderEmail' => 	'',
		 			'Weight' => $shipmentObj->RecordData['weight'],
		 			'Length' => $shipmentObj->RecordData['length'],
		 			'Width' => 	$shipmentObj->RecordData['width'],
		 			'Height' => $shipmentObj->RecordData['height'],
		 			'VolumetricWeight' => 	$shipmentObj->RecordData['weight'],
		 			'DestinationCountry' => 	$this->ApiData['R_Area_Code'],
		 			'Barcode' => 	$Barcode,
		 			'Barcode_Text' => 	wordwrap($Barcode_Text,1,"   ",true),
		 		);
		  // echo "<pre>"; print_r($pdfdata);die;

      	  $shipmentObj->RecordData['pdf_data'] =  $pdfdata;
          $jsonEncoded = json_encode($pdfdata);
          $YodelLabel = new Application_Model_YodelLabel();
          $res = $YodelLabel->storeLabelData($shipmentObj,$jsonEncoded);
           
	 	return true;
	 	    	
	  }

	   

	  public function SendApiLabelRequest()
	  {
	  	if(!empty($this->ApiData)){

	  			try {
	  				
   					$res = $this->client->__soapCall("newShipment", $this->ApiData);

	  			} catch (Exception $e) {
                  $res = array('status'=>0,'message'=>'Couldnt Send Request');
	  			}



	  			if(is_soap_fault($res))
                  $resp = array('status'=>0,'message'=>'Couldnt Send Request');
                 else {
                      
                      if( property_exists($res, 'Error') && $res->Error == 0 ){

                      	if( property_exists($res, 'Voucher') &&  !empty( $res->Voucher ) ){


                        	$resp = array('status'=>1,'message'=>'Parcel Added Successfully','response'=>$res);
                        }
                        else $resp = array('status'=>0,'message'=>'Some Internal Error Occurred Sorry for inconvienience','response'=>$res);
                      }

                      else $resp = array('status'=>0,'message'=>'Some Internal Error Occurred Sorry for inconvienience','response'=>$res);

                 } 

	  			 return $resp;

	  	}



	  }

	 
	  public function ExchangeValue($value='')
	  {
	  	return empty($value) ? '-' : $value;
	  }
	   

	  public function PrepareApiData($shipmentObj='')
	  {		

    	$this->ApiData = array(
   
            'Code'=>'687',
            'CRM'=>'1610',
            'User'=>'demouser',
            'Pass'=>'!demouser!',
            'R_Name'=> $this->ExchangeValue($shipmentObj->RecordData['rec_name']) ,
            'R_Address'=>$this->ExchangeValue($shipmentObj->RecordData['rec_street'].' '.$shipmentObj->RecordData['rec_address']),
            'R_Area_Code'=> $this->ExchangeValue($shipmentObj->RecordData['rec_cncode']) ,
            'R_Area'=> $this->ExchangeValue($shipmentObj->RecordData['rec_street'].' '.$shipmentObj->RecordData['rec_streetnr'].' '.$shipmentObj->RecordData['rec_street2']) ,
            'R_PC'=> $this->ExchangeValue($shipmentObj->RecordData['rec_zipcode']),
            'R_Phone1'=>$this->ExchangeValue($shipmentObj->RecordData['rec_phone']),
            'R_Email'=>$this->ExchangeValue($shipmentObj->RecordData['rec_email']),
            'Relative1'=>'yyyyyyyy',
            'Relative2'=>'zzzzzzzz',
		); 
		  // echo "<pre>"; print_r($this->ApiData);die;
 			
	  }


}