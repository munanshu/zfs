<?php ini_set('display_errors', 1);
class Application_Model_GLSITLabel extends Zend_custom{
	  
	private $api_base = "https://weblabeling.gls-italy.com/";
	private $api_uri = "ilsWebService.asmx?op=AddParcel";
	private $HttpHeaders = array();
	private $Bda;
	public  $xml_string;
	public  $xml_string_inner;
	public  $dom;




	  public function CreateGLSITLabel($shipmentObj,$newbarcode=true){ 

	  	$this->PrepareApiXmlData($shipmentObj);

	  	if($shipmentObj->RecordData['quantity']>0){

            $result['Shipment'] = $this->SendApiLabelRequest();
                // echo "<pre>";  print_r($result);die;
                    
            if($result['Shipment']['status']==1){

                  $response = $this->SaveApiLabelResponse($shipmentObj,$result['Shipment']['response']);

                  // print_r($response);die;
                  return true;

            }
            else { 
                $this->UpdateForwarder($shipmentObj);
               $resp = $result['Shipment'];
              return true;
            }
                     
        }

		 
	 	

	  }

	  public function UpdateForwarder($shipmentObj){
      
      	$this->_db->update(SHIPMENT,array('forwarder_id'=>34,'wrong_parcel'=>'1'),"shipment_id=".$shipmentObj->RecordData[SHIPMENT_ID]."");
      }

	  public function SetApiHeaders($action,$xml)
	  {
	  	$this->HttpHeaders = array(
				"Host: weblabeling.gls-italy.com",
				"Connection: Keep-Alive",
				"Content-type: text/xml;charset=\"utf-8\"",
				"SOAPAction: http://weblabeling.gls-italy.com/$action", 
				"Content-length: ".strlen($xml),
	  		);

	  }

	  public function SaveApiLabelResponse($shipmentObj='',$response='')
	  {

	  	 $Parcel = $response->InfoLabel->Parcel;

	  	 $RapportoPesoVolume = (isset($Parcel->RapportoPesoVolume) && !empty($Parcel->RapportoPesoVolume) && !is_object($Parcel->RapportoPesoVolume))? $Parcel->RapportoPesoVolume : '' ;

	  	 $NoteSpedizione = (isset($Parcel->NoteSpedizione) && !empty($Parcel->NoteSpedizione) && !is_object($Parcel->NoteSpedizione))? $Parcel->NoteSpedizione : '' ;

	  	 // $SiglaCSM = (isset($Parcel->SiglaCSM) && !empty($Parcel->SiglaCSM) && $Parcel->SiglaCSM != "!!")? $Parcel->SiglaCSM : 0 ;

	  	 // $CodiceZona = (isset($Parcel->CodiceZona) && !empty($Parcel->CodiceZona) && $Parcel->CodiceZona != "!!")? $Parcel->CodiceZona : 0 ;

	 	 $Barcode = (string)$Parcel->SiglaMittente.$Parcel->NumeroSpedizione.$Parcel->TotaleColli.$Parcel->TipoCollo.$Parcel->SiglaSedeDestino;	

	 	 $Barcode = $Parcel->DescrizioneTipoPorto == "ERRORE"? $this->Bda : $Barcode ;

          $shipmentObj->RecordData[BARCODE] = $Barcode;
          $shipmentObj->RecordData['BARCODE_READABLE'] = $shipmentObj->RecordData[BARCODE];
          $shipmentObj->RecordData[REROUTE_BARCODE] = $shipmentObj->RecordData[BARCODE];
          $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
          $shipmentObj->RecordData[TRACENR] = $shipmentObj->RecordData[BARCODE];

          $Barcode_Text = $Parcel->DescrizioneTipoPorto == "ERRORE"? $Barcode."               ".$Parcel->ProgressivoCollo : $Parcel->SiglaMittente." ".$Parcel->NumeroSpedizione." ".$Parcel->TotaleColli." ".$Parcel->TipoCollo." ".$Parcel->SiglaSedeDestino."       ".$Parcel->ProgressivoCollo;

		  $pdfdata = array(
		 			'SiglaMittente' => 	$Parcel->SiglaMittente,
		 			'NumeroSpedizione' => 	$Parcel->NumeroSpedizione,
		 			'TotaleColli' => 	$Parcel->TotaleColli,
		 			'TipoCollo' => 	$Parcel->TipoCollo,
		 			'SiglaSedeDestino' => 	$Parcel->SiglaSedeDestino,
		 			'DenominazioneMittente' => 	$Parcel->DenominazioneMittente,
		 			'DenominazioneDestinatario' => 	$Parcel->DenominazioneDestinatario,
		 			'IndirizzoDestinatario' => 	$Parcel->IndirizzoDestinatario,
		 			'CittaDestinatario' => 	$Parcel->CittaDestinatario,
		 			'ProvinciaDestinatario' => 	$Parcel->ProvinciaDestinatario,
		 			'DataSpedizione' => 	$Parcel->DataSpedizione,
		 			'DescrizioneSedeDestino' => 	$Parcel->DescrizioneSedeDestino,
		 			'PesoSpedizione' => 	$Parcel->PesoSpedizione,
		 			'DescrizioneTipoPorto' => 	$Parcel->DescrizioneTipoPorto,
		 			'SiglaCSM' => 	$Parcel->SiglaCSM,
		 			'DescrizioneCSM1' => 	$Parcel->DescrizioneCSM1,
		 			'DescrizioneCSM2' => 	$Parcel->DescrizioneCSM2,
		 			'RapportoPesoVolume' => 	$RapportoPesoVolume,
		 			'ProgressivoCollo' => 	$Parcel->ProgressivoCollo,
		 			'CodiceZona' => 	$Parcel->CodiceZona,
		 			'Barcode' => $Barcode,
		 			'Barcode_Text' => $Barcode_Text,
		 			'NoteSpedizione' => $NoteSpedizione
		 		);


      	  $shipmentObj->RecordData['pdf_data'] =  $pdfdata;
          $jsonEncoded = json_encode($pdfdata);
          $YodelLabel = new Application_Model_YodelLabel();
          $res = $YodelLabel->storeLabelData($shipmentObj,$jsonEncoded);
          // echo "<pre>";
          	
          // 	print_r($pdfdata);
          
          // die;

          // echo "<pre>";  
          // print_r($response); 
          // print_r($pdfdata); 
          // die;
           
	 	return true;
	 	    	
	  }

	  public function CurlRequest($url='',$method='',$headers='',$data='',$xml=false,$AuthData='')
	  { 
	    $method = strtoupper($method);
	    $headers = (isset($headers) && !empty($headers))?$headers:array();
	    $ch = curl_init();
	    $options = array(
	      CURLOPT_URL=>$url,
	      CURLOPT_RETURNTRANSFER=>true,
	      CURLOPT_HTTPHEADER=>$headers,
	      CURLOPT_SSL_VERIFYPEER=>false,
	      CURLOPT_SSL_VERIFYHOST=>false,
	      );
	    // $this->debug($options);
	    if($method =='GET'){
	      $requestUrl = (isset($data) && !empty($data))? $url."?".http_build_query($data):$url;
	      $options[CURLOPT_URL] = $requestUrl;
	      $options[CURLOPT_CUSTOMREQUEST] = $method;
	    }
	    if($method =='POST'){
	      $data = $xml?$data:http_build_query($data);
	      $options[CURLOPT_HTTPAUTH] = isset($AuthData) && !empty($AuthData) ? CURLAUTH_ANY :  false ;
	      $options[CURLOPT_USERPWD] = isset($AuthData) && !empty($AuthData) ? $AuthData['UserName'] ." : ".$AuthData['Password'] :  false ;
	      $options[CURLOPT_POSTFIELDS] = $data;
	      $options[CURLOPT_POST] = true;
	      $options[CURLOPT_CUSTOMREQUEST] = $method;
	      // echo "<pre>"; print_r($options[CURLOPT_POSTFIELDS]);die;
	    }
	    // $this->debug($options);die;
	    curl_setopt_array($ch, $options);
	    return curl_exec($ch);
	  }

	  public function SendApiLabelRequest()
	  {
	  	if(!empty($this->xml_string)){

	  		$this->SetApiHeaders('AddParcel',$this->xml_string);
			
	  		if(isset($this->HttpHeaders) && !empty($this->HttpHeaders)){
	  			try {
	  				
	  				$res = $this->CurlRequest($this->api_base.$this->api_uri,'POST',$this->HttpHeaders,$this->xml_string,true);
	  				// echo $res;

	  			} catch (Exception $e) {
                  $resp = array('status'=>0,'message'=>'Couldnt Send Request');
	  			}

	  			if(is_soap_fault($res))
                  $resp = array('status'=>0,'message'=>'Couldnt Send Request');
                 else {
                      
	  				$res = $this->ConvertXmlToArray($res);
                      if(isset($res->Body->AddParcelResponse->AddParcelResult->InfoLabel->Parcel->DescrizioneTipoPorto)){

                      	// if($res->Body->AddParcelResponse->AddParcelResult->InfoLabel->Parcel->DescrizioneTipoPorto != 'ERRORE' ){
                      	if( isset($res->Body->AddParcelResponse->AddParcelResult->InfoLabel) ){


                        	$resp = array('status'=>1,'message'=>'Parcel Added Successfully','response'=>$res->Body->AddParcelResponse->AddParcelResult);
                        }
                        else $resp = array('status'=>0,'message'=>'Some Internal Error Occurred Sorry for inconvienience','response'=>$res);
                      }

                      else $resp = array('status'=>0,'message'=>'Some Internal Error Occurred Sorry for inconvienience','response'=>$res);

                 } 

	  			 return $resp;

	  		}



	  	}



	  }

	public function ConvertXmlToArray($response='')
    { 

      $response = str_replace(array('soap:'), array(''), $response); 
      $p = simplexml_load_string($response);
      $p = json_encode($p);
      $p = json_decode($p);
      return $p;
    }

	  public function arrayToDomXml($arr='',$dom)
	  {

	  		// if(is_array($arr)){

	  		// 	foreach ($arr as $key => $value) {
	  		// 		if($key == 'attributes')
	  		// 			$dom->setAttribute()	
	  		// 		$dom->appendChild($dom->CreateElement($key,$value));

	  		// 	}

	  		// }	



	  }

	  public function PrepareApiXmlData($shipmentObj='')
	  {		
	  		$customer_detail =  $shipmentObj->RecordData['forwarder_detail']['CustomerAddress'];
        	$sender_detail =  $shipmentObj->RecordData['forwarder_detail']['SenderAddress'];
        	$reciever_info = $shipmentObj->RecordData;
        	$this->Bda = $shipmentObj->RecordData['forwarder_detail']['customer_number'].$shipmentObj->RecordData[TRACENR];
	  	// echo "<pre>";
	  	// print_r($customer_detail);
	  	// print_r($sender_detail);
	  	// print_r($shipmentObj->RecordData);

	  		$dom = new DOMDocument('1.0',"utf-8");
	  		$root = $dom->CreateElement('soap:Envelope');
	  		$root->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance'); 	
	  		$root->setAttribute('xmlns:xsd','http://www.w3.org/2001/XMLSchema');
	  		$root->setAttribute('xmlns:soap','http://schemas.xmlsoap.org/soap/envelope/');
	  		$dom->appendChild($root);
	  		$body = $dom->CreateElement('soap:Body');	
	  		$root->appendChild( $body ); 
	  		$AddParcel = $dom->CreateElement('AddParcel');	
	  		$AddParcel->setAttribute('xmlns','http://weblabeling.gls-italy.com/');
	  		$body->appendChild($AddParcel);	

	  		$XMLInfoParcel = $dom->CreateElement('XMLInfoParcel');	
	  		$AddParcel->appendChild($XMLInfoParcel);
	  		$XMLInfoParcel->appendChild($dom->CreateElement('FakeChild'));
	  		$dom->formatOutput = true;
	  		$this->xml_string = $dom->saveXML();
	  		 

	  		$Info = $dom->CreateElement('Info');

			  		$Info->appendChild($dom->CreateElement('SedeGls','bz'));
			  		$Info->appendChild($dom->CreateElement('CodiceClienteGls','12130'));
			  		$Info->appendChild($dom->CreateElement('PasswordClienteGls','BZ223SP'));
	  		$XMLInfoParcel->appendChild($Info);	

			  		$Parcel = $dom->CreateElement('Parcel');	
			  		$Parcel->appendChild($dom->CreateElement('CodiceContrattoGls','2523'));
			  		$Parcel->appendChild($dom->CreateElement('RagioneSociale',$sender_detail[0]));
			  		$Parcel->appendChild($dom->CreateElement('Indirizzo', $reciever_info['rec_street']." ".$reciever_info['rec_streetnr']." ".$reciever_info['rec_address']." ".$reciever_info['rec_street2']));
			  		$Parcel->appendChild($dom->CreateElement('Localita',$reciever_info['rec_city']));
			  		$Parcel->appendChild($dom->CreateElement('Zipcode',$reciever_info['rec_zipcode']));
			  		// $Parcel->appendChild($dom->CreateElement('Provincia','PC'));
			  		$Parcel->appendChild($dom->CreateElement('Colli','1'));
			  		$Parcel->appendChild($dom->CreateElement('PesoReale',$shipmentObj->RecordData['weight']));
			  		$Parcel->appendChild($dom->CreateElement('Bda',$this->Bda));
			  		$Parcel->appendChild($dom->CreateElement('ContatoreProgressivo','000000663'));
			  		$Parcel->appendChild($dom->CreateElement('GeneraPdf','4'));


	  		$Info->appendChild($Parcel);
			$dom->formatOutput = true;
	  		$this->xml_string_inner = $dom->saveXML();
			 

	  		$pos1 = strpos($this->xml_string_inner, '<Info');
	  		$pos2 = strpos($this->xml_string_inner, '</XMLInfoParcel>
    </AddParcel>
  </soap:Body>
</soap:Envelope>');
	  		 
	  		$substr = substr($this->xml_string_inner, $pos1,$pos2);
	  		$substr = str_replace("</XMLInfoParcel>
    </AddParcel>
  </soap:Body>
</soap:Envelope>", "", $substr);
	  		$substr = htmlentities($substr);

	  		$this->xml_string = str_replace("<FakeChild/>", $substr , $this->xml_string);

	  		return $this->xml_string;
 
	  }

	   public function SendApiCWDRequest()
	  {
	  	if(!empty($this->xml_string)){

	  		$this->SetApiHeaders('CloseWorkDay',$this->xml_string);
			
	  		if(isset($this->HttpHeaders) && !empty($this->HttpHeaders)){
	  			try {
	  				
	  				$res = $this->CurlRequest($this->api_base.$this->api_uri,'POST',$this->HttpHeaders,$this->xml_string,true);

	  			} catch (Exception $e) {
                  $resp = array('status'=>0,'message'=>'Couldnt Send Request');
	  			}

	  			if(is_soap_fault($res))
                  $resp = array('status'=>0,'message'=>'Couldnt Send Request');
                 else {
                      
	  				$res = $this->ConvertXmlToArray($res);
                      if(isset($res->Body->CloseWorkDayResponse->CloseWorkDayResult->DescrizioneErrore)){

                      	// if($res->Body->AddParcelResponse->AddParcelResult->InfoLabel->Parcel->DescrizioneTipoPorto != 'ERRORE' ){
                      	if( $res->Body->CloseWorkDayResponse->CloseWorkDayResult->DescrizioneErrore == 'OK' ){


                        	$resp = array('status'=>1,'message'=>'Shipment Closed Successfully','response'=>$res->Body->CloseWorkDayResponse->CloseWorkDayResult);
                        }
                        else $resp = array('status'=>0,'message'=>'Some Internal Error Occurred Sorry for inconvienience','response'=>$res);
                      }

                      else $resp = array('status'=>0,'message'=>'Some Internal Error Occurred Sorry for inconvienience','response'=>$res);

                 } 

	  			 return $resp;

	  		}



	  	}



	  }

}