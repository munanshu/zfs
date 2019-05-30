<?php 

class Application_Model_Socketprocessing extends Zend_custom{
	public $SocketData = array();
	public $SocketForwarder = NULL;
	public $unicode = NULL;
	 public function SocketReturn(){
		  $this->SocketDataFormationGLSNL();
		 if($this->SocketData['SOCKET']){ 
			$socketResult =  $this->ConnectSocket();
		  }else{ 
			$socketResult =  $this->RerouteBarcodeData();
		  }
		  return $socketResult;
	}
	public function SocketDataFormationGLSNL(){
	      //Service Flag
		    $phoneno = ($this->SocketData[PHONE]!='' && $this->SocketData[PHONE]!='0')?'+'.commonfunction::onlynumbers($this->SocketData[PHONE]):''; 
		     $serviceInfo = $this->getServiceDetails($this->SocketData[ADDSERVICE_ID]);
			 $expressTAg = '';
			 if(in_array($serviceInfo['internal_code'],array('E',21,22,23,24,25,26,'E21','E22','E23','E24','E25','E26'))){
			    $Servicetag = 'EP';
				$expressTAg = '|T750:EXPRESS-Service|T752:'.$phoneno.''; 
			 }else{
			    $Servicetag = 'EBP';
				if($this->SocketData['country_id']==9) {
					$Servicetag = 'BP';
				}
			 }
		   	 if($this->SocketData[SERVICE_ID]=='1'){
			    $MainServicetag = 'P';
			 }elseif($this->SocketData[SERVICE_ID]=='2'){
			    $MainServicetag = 'B';
			 } 
			 if($this->SocketData[EMAIL]!=''){
			   $tag1393 = 1;
			 }else{
			   $tag1393 = '';
			 } 
		if($this->SocketData['senderaddress_id']=='B'){
		  $customerAddress = $this->SocketData['forwarder_detail']['CustomerAddress'];
		  $this->SocketData['forwarder_detail']['SenderAddress'] = array($customerAddress['company_name'],$customerAddress['name'],$customerAddress['address1'],$customerAddress['city'],$customerAddress['postalcode'],$customerAddress['cncode'],$customerAddress['country_name']);;
		}	 
		
	   $this->unicode = "\\\\\\\\\\GLS\\\\\\\\\\".
			   'T8904:'.commonfunction::paddingleft($this->SocketData['parcelcount'],3,'0').
			   "|".'T050:parcel.nl'.
			   "|".'T8905:'.commonfunction::paddingleft($this->SocketData[QUANTITY],3,'0').
			   "|".'T100:'.$this->SocketData['rec_cncode'].
			   "|".'T330:'.commonfunction::sub_string($this->SocketData[ZIPCODE],0,10).
			   "|".'T853:'.commonfunction::sub_string($this->SocketData[REFERENCE],0,20).
			   "|".'T530:'.commonfunction::stringReplace(".",",",commonfunction::sub_string($this->SocketData[WEIGHT],0,4)).
			   "|".'T800:'.commonfunction::sub_string('Afzender:',0,15).
			   "|".'T8914:'.commonfunction::sub_string($this->SocketForwarder['contract_number'],0,10).
			   "|".'T8915:'.commonfunction::sub_string($this->SocketForwarder['SAP_number'],0,10).
			   "|".'T810:'.commonfunction::sub_string($this->SocketData['forwarder_detail']['SenderAddress'][0],0,50).
			   "|".'T820:'.commonfunction::sub_string($this->SocketData['forwarder_detail']['SenderAddress'][2],0,50).
			   "|".'T821:'.commonfunction::sub_string($this->SocketData['forwarder_detail']['SenderAddress'][5],0,2).
			   "|".'T822:'.commonfunction::sub_string($this->SocketData['forwarder_detail']['SenderAddress'][4],0,10).
			   "|".'T823:'.commonfunction::sub_string($this->SocketData['forwarder_detail']['SenderAddress'][3],0,10).
			   "|".'T860:'.commonfunction::sub_string($this->SocketData[RECEIVER],0,30).
			   "|".'T861:'.commonfunction::sub_string($this->SocketData[CONTACT],0,30).
			   "|".'T862:'.commonfunction::sub_string($this->SocketData[STREET2],0,30).
			   "|".'T863:'.commonfunction::sub_string($this->SocketData[STREET].' '.$this->SocketData[STREETNR].' '.$this->SocketData[ADDRESS],0,30).
			   "|".'T864:'.commonfunction::sub_string($this->SocketData[CITY],0,30).
			   "|".'T882:'.
			   "|".'T1229:'.commonfunction::sub_string($this->SocketData[EMAIL],0,50).
			   "|".'T758:'.commonfunction::sub_string($phoneno,0,50).
			   "|".'T805:'.commonfunction::sub_string($this->SocketForwarder['customer_number'],0,8).
			   "|".'T206:'.commonfunction::sub_string($Servicetag,0,3).
			   "|".'T859:'.commonfunction::sub_string($MainServicetag,0,1).
			   "|".'T1393:'.commonfunction::sub_string($tag1393,0,1).
			   "|".'T620:'.commonfunction::sub_string($this->SocketData[BARCODE],0,14).
			   "|".'T854:'.commonfunction::sub_string($this->SocketData['OnetimeRef'],0,10).
			   "|".'T084:P'.
			   "|".'T090:Noprint'.
			   "|".'T8700:'.commonfunction::sub_string('NL'.$this->SocketForwarder['depot_number'],0,6).$expressTAg."|"."/////GLS/////";
	 }
	 
	public function ConnectSocket(){ 
			sleep(1);
	       	$socket = socket_create(AF_INET,SOCK_STREAM, SOL_TCP) or die("Could not create socket\n");
			$result = @socket_connect($socket,$this->SocketForwarder['primary_socket'],$this->SocketForwarder['primary_port']);
			if($result != 1){
				$result = @socket_connect($socket, $this->SocketForwarder['secondry_socket'], $this->SocketForwarder['secondry_socket']);
				if($result == ''){
					//$_SESSION[ERROR_MSG] = 'Could not connect with the socket';
				}

			}
		   $write_data = socket_write($socket,$this->unicode, strlen($this->unicode));
		   $fullResult='';
		   while($resp = socket_read($socket, 5000)) {
			   $fullResult .= $resp;
			   if (strpos($fullResult, "/////GLS/////") !== false) break;
			}
			socket_close($socket);
			$reroute = strstr($fullResult,"\\\\\GLS\\\\");
			$fullResult = explode('|',$fullResult);
			$val = array();
			for($i=1;$i< count($fullResult);$i++){ 
				if(strstr($fullResult[$i],':')) {
					$bre = explode(":",$fullResult[$i]);
					$val[$bre[0]] = $bre[1];
				}
			}
		  return array('Reroute'=>$reroute,'SocketLabel'=>$val);	
	}
	
	public function RerouteBarcodeData(){
		$fullResult = explode('|',$this->SocketData['reroute_barcode']);
		$val = array();
		for($i=1;$i< count($fullResult);$i++)
		{
			if(strstr($fullResult[$i],':')) { 
				$bre = explode(":",$fullResult[$i]);
				$val[$bre[0]] = $bre[1];
			}
		}
	  return array('Reroute'=>$this->SocketData['reroute_barcode'],'SocketLabel'=>$val);
	} 
	
	public function Create2Dbarcode($primary,$secondry,$tacenr){
	    	//2D code
			$DataMatrix = new Zend_Barcode_2DBARCODE_DataMatrix();
			$FILEPATH = PRINT_SAVE_LABEL.$this->SocketData['forwarder_detail']['forwarder_name'].'/img/';
			$CODE = NULL;
			$CODE1 = NULL;
			$CODE        = $primary;
			$findarr = array('¬','?');
			$replacearr = array("|","|");
			$CODE1 = commonfunction::stringReplace(array('¬','?'),'|',commonfunction::utf8Decode($secondry));
			$IMAGE_NAME_P = 'P_'.$tacenr.'.png';
			$IMAGE_NAME_S = 'S_'.$tacenr.'.png';
			$DataMatrix->setBGColor("WHITE");
			$DataMatrix->setBarColor("BLACK");
			$DataMatrix->setRotation("0");
			$DataMatrix->setImageType("PNG", 40 );
			$DataMatrix->setQuiteZone("10");
			$DataMatrix->setEncoding("AUTO");
			$DataMatrix->setFormat("36");
			$DataMatrix->setTilde("Y");
			$DataMatrix->setModuleSize("3");
			$DataMatrix->setFilePath($FILEPATH);
			$DataMatrix->paint($CODE,$IMAGE_NAME_P);
			$DataMatrix->paint($CODE1,$IMAGE_NAME_S);
			return array("Pri"=>$FILEPATH.$IMAGE_NAME_P,"Sec"=>$FILEPATH.$IMAGE_NAME_S);
	}
	
	public function SocketReturnDE(){
		  $this->SocketDataFormationGLSDE(); 
		 if($this->SocketData['SOCKET']){ 
			$socketResult =  $this->ConnectSocket();
		  }else{ 
			$socketResult =  $this->RerouteBarcodeData();
		  }
		  return $socketResult;
	}
	
    public function SocketDataFormationGLSDE(){
	      $weightTag = ($this->SocketData[WEIGHT]<3) ? '|T205:S' : '';
		// Create Socket Data Tag for COD Parcels
		$senderAddress = array();
		if($this->SocketData['senderaddress_id']=='B'){
		  $customerAddress = $this->SocketData['forwarder_detail']['CustomerAddress'];
		  $senderAddress = array($customerAddress['company_name'],$customerAddress['name'],$customerAddress['address1'],$customerAddress['city'],$customerAddress['postalcode'],$customerAddress['cncode'],$customerAddress['country_name']);;
		}else{
		   $senderAddress = $this->SocketData['forwarder_detail']['SenderAddress'];
		}
		if($this->SocketData['addservice_id']==7 || $this->SocketData['addservice_id']==146) {
			$this->unicode = "\\\\\\\\\\GLS\\\\\\\\\\".
		  					"T090:NOPRINT".
							"|T050:Versand".
							"|T051:V 3.5 Rev. 7".
							"|T100:".$this->SocketData['rec_cncode'].
							"|T8700:".commonfunction::sub_string(' DE '.$this->SocketForwarder['depot_number'],0,7).
							"|T203:C".$weightTag.
							"|T220:".commonfunction::numberformat($this->SocketData['cod_price'],2, ',', '').
							"|T221:".commonfunction::sub_string(commonfunction::uppercase($this->SocketData['currency']),0,3).
							"|T330:".commonfunction::sub_string($this->SocketData[ZIPCODE],0,10).
							"|T400:".commonfunction::sub_string($this->SocketData[BARCODE],0,12).
							"|T530:".commonfunction::stringReplace(".",",",commonfunction::sub_string($this->SocketData[WEIGHT],0,4)).
							"|T545:".commonfunction::sub_string(date('d.m.Y'),0,10).
							"|T750:CASH-SERVICE".
							"|T752:Verwendungszweck".
							"|T753:content of reason for transfer".
							"|T800:Absender".
							"|T805:".commonfunction::sub_string($this->SocketForwarder['customer_number'],0,8).
							"|T809:Company".
							"|T810:".commonfunction::remove_accent(commonfunction::sub_string($senderAddress[0],0,50)).
							"|T811:".commonfunction::remove_accent(commonfunction::sub_string($senderAddress[1],0,50)).
							"|T820:".commonfunction::remove_accent(commonfunction::sub_string($senderAddress[2],0,50)).
							"|T821:".commonfunction::sub_string($senderAddress[5],0,2).
							"|T822:".commonfunction::remove_accent(commonfunction::sub_string($senderAddress[4],0,10)).
							"|T823:".commonfunction::remove_accent(commonfunction::sub_string($senderAddress[3],0,10)).
							"|T851:KD-Nr.:".
							"|T852:10166".
							"|T853:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[REFERENCE],0,20)).
							"|T854:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[REFERENCE],0,20)).
							"|T859:Company".
							"|T860:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[RECEIVER],0,50)).
							"|T861:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[CONTACT],0,50)).
							"|T863:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[STREET].' '.$this->SocketData[STREETNR],0,50)).
							"|T864:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[CITY],0,50)).							
							"|T8904:".commonfunction::paddingleft($this->SocketData['parcelcount'],3,'0',STR_PAD_LEFT).
							"|T8905:".commonfunction::paddingleft($this->SocketData[QUANTITY],3,'0',STR_PAD_LEFT).
							"|T8914:".commonfunction::sub_string($this->SocketForwarder['contract_number'],0,10).
							"|T8915:".commonfunction::sub_string($this->SocketForwarder['SAP_number'],0,10)."|/////GLS/////";
		}
		else {
			 $Shipment_weight =  ($this->SocketData[WEIGHT]>0.1)?$this->SocketData[WEIGHT]:'0,1';	
			  $this->unicode = "\\\\\\\\\\GLS\\\\\\\\\\".
								"T090:NOPRINT".
								"|T050:Versandsoftwarename".
								"|T051:V 4711".
								"|T100:".$this->SocketData['rec_cncode'].$weightTag.
								"|T8700:".commonfunction::sub_string(' DE '.$this->SocketForwarder['depot_number'],0,7).
								"|T330:".commonfunction::sub_string($this->SocketData[ZIPCODE],0,10).
								"|T400:".commonfunction::sub_string($this->SocketData[BARCODE],0,12).
								"|T530:".commonfunction::stringReplace(".",",",commonfunction::sub_string($Shipment_weight,0,4)).
								"|T545:".commonfunction::sub_string(date('d.m.Y'),0,10).
								"|T8904:".commonfunction::paddingleft($this->SocketData['parcelcount'],3,'0',STR_PAD_LEFT).
								"|T8905:".commonfunction::paddingleft($this->SocketData[QUANTITY],3,'0',STR_PAD_LEFT).
								"|T800:Absender".
								"|T805:".commonfunction::sub_string($this->SocketForwarder['customer_number'],0,8).
								"|T810:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData['forwarder_detail']['SenderAddress'][0],0,50)).
								"|T811:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData['forwarder_detail']['SenderAddress'][1],0,50)).
								"|T820:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData['forwarder_detail']['SenderAddress'][2],0,50)).
								"|T821:".commonfunction::sub_string($this->SocketData['forwarder_detail']['SenderAddress'][5],0,2).
								"|T822:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData['forwarder_detail']['SenderAddress'][4],0,10)).
								"|T823:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData['forwarder_detail']['SenderAddress'][3],0,10)).
								"|T851:KD-Nr.:".
								"|T852:4711".
								"|T853:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[REFERENCE],0,20)).
								"|T854:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[REFERENCE],0,20)).
								"|T859:Company".
								"|T860:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[RECEIVER],0,50)).
								"|T861:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[CONTACT],0,50)).
								"|T863:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[STREET].' '.$this->SocketData[STREETNR],0,50)).
								"|T864:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[CITY],0,50)).
								"|T921:".commonfunction::remove_accent(commonfunction::sub_string($this->SocketData[REFERENCE],0,20)).
								"|T8914:".commonfunction::sub_string($this->SocketForwarder['contract_number'],0,10).
								"|T8915:".commonfunction::sub_string($this->SocketForwarder['SAP_number'],0,10)."|/////GLS/////"; 
			}
			$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$this->unicode);
	 }
	 
	 public function SocketReturnGFT(){
		  $this->SocketDataFormationGLSFT();
		 if($this->SocketData['SOCKET']){ 
			$socketResult =  $this->ConnectSocket();
		  }else{ 
			$socketResult =  $this->RerouteBarcodeData();
		  }
		  return $socketResult;
	}
	
    public function SocketDataFormationGLSFT(){
		$tage1267 = 'Y';
		//$additionalservicetag = $this->serviceUnitCode($objModel);
		$sendername = 'Parcel.nl';
		$senderaddress = 'Slachthuisweg 77';
		$sendercountry = 'NL';
		$senderpostalcode = '7556AX';
		$sendercity = 'Hengelo';
		$this->unicode = "\\\\\\\\\\GLS\\\\\\\\\\".
							"|T8904:".commonfunction::paddingleft($this->SocketData['parcelcount'],3,'0',STR_PAD_LEFT).
							"|T8905:".commonfunction::paddingleft($this->SocketData[QUANTITY],3,'0',STR_PAD_LEFT).
							"|T100:".$this->SocketData['rec_cncode'].
							"|T330:".commonfunction::sub_string($this->SocketData[ZIPCODE],0,10).
							"|T530:".str_replace(".",",",number_format(str_replace(",",".",$this->SocketData[WEIGHT]),1)).
							"|T800:Absender".
							"|T8914:".commonfunction::sub_string($this->SocketForwarder['contract_number'],0,10).
							"|T8915:".commonfunction::sub_string($this->SocketForwarder['SAP_number'],0,10).
							"|T810:".commonfunction::remove_accent(commonfunction::sub_string($sendername,0,50)).
							"|T820:".commonfunction::remove_accent(commonfunction::sub_string($senderaddress,0,50)).
							"|T821:".commonfunction::remove_accent(commonfunction::sub_string($sendercountry,0,2)).
							"|T822:".commonfunction::remove_accent(commonfunction::sub_string($senderpostalcode,0,10)).
							"|T823:".commonfunction::remove_accent(commonfunction::sub_string($sendercity,0,10)).
							"|T860:".commonfunction::sub_string($this->SocketData[RECEIVER],0,30).
							"|T759:".commonfunction::sub_string($this->SocketData[CONTACT],0,30).
							"|T861:".
							"|T863:".commonfunction::sub_string($this->SocketData[STREET].' '.$this->SocketData[STREETNR],0,30).
							"|T864:".commonfunction::sub_string($this->SocketData[CITY],0,30).
							"|T882:".commonfunction::sub_string($this->SocketData[STREETNR],0,8).
							"|T1229:".commonfunction::sub_string($this->SocketData[EMAIL],0,50).
							"|T758:".commonfunction::sub_string($this->SocketData[PHONE],0,15).
							"|T805:".commonfunction::sub_string($this->SocketForwarder['customer_number'],0,8).
							"|T854:".$this->SocketData['unique_reference'].
							"|T090:NOPRINT".
							"|T8700:".commonfunction::sub_string('NL'.$this->SocketForwarder['depot_number'],0,6).
							"|T1267:".$tage1267.
							"|TPT:".commonfunction::sub_string($this->SocketData['service_unitcode'],0,2)."|"."/////GLS/////"."\n";
	 }
	 
    public function OneTimeReference($quantity,$barcode){

		$Totalshipmet = $quantity;

		if($quantity<=9){

		$Totalshipmet = '0'.$quantity;

		}

	    if($quantity==1){

			$multicoli = 'S';

		}else{

			$multicoli = 'M';

		}

	  return '99'.substr($barcode,8,5).$Totalshipmet.$multicoli;

    }
}

