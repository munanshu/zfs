<?php
class Application_Model_FadelloLabel extends Zend_custom{
	   	public $Fadello  = array();
		public $jsonData = array();
	  public function CreateFadelloLabel($shipmentObj,$newbarcode=true){ 
	        $this->Fadello  =  $shipmentObj->RecordData['forwarder_detail'];
			if($newbarcode){
			   $this->CreateBarcode($shipmentObj);
			 }  
	  }
	  public function CreateBarcode($shipmentObj){ 
	           $this->jsonData = array();
		        $senderAdd = $this->Fadello['SenderAddress'];
				$customeradd = $shipmentObj->getCustomerDetails($shipmentObj->RecordData[ADMIN_ID]);
				
				date_default_timezone_set('Europe/Amsterdam');
				$timestamp = date('H:i',time());
				$this->jsonData['Name'] 		= ($senderAdd[0]!='')?substr(trim($senderAdd[0]),0,100):'Parcel.nl';
				$this->jsonData['Phone'] 		= '0534617777';
				$this->jsonData['Note'] 		= '';
				$this->jsonData['Email'] 		= 'klantenservice@parcel.nl';
				$this->jsonData['ShipType'] 	= 'DC';
				if($this->Useconfig['fadello_pickup'] && $customeradd['cncode']=='NL'){
					$this->jsonData['PUname'] 		= $customeradd['company_name'];
					$this->jsonData['PUstreet'] 	= preg_replace('/[0-9]+/', '', $customeradd['address1']);
					$this->jsonData['PUpostalcode'] = $customeradd['postalcode'];
					$this->jsonData['PUhomeno'] 	= preg_replace('/\D/', '', $customeradd['address1']);
					$this->jsonData['PUhomenoAdd'] 	= '';
					$this->jsonData['PUcity'] 		= $customeradd['city'];
					$this->jsonData['PUcountry'] 	= 'NL';
					$this->jsonData['PUphone'] 		= $customeradd['phoneno'];
					$this->jsonData['PUemail'] 		= $customeradd['email'];
					
				}else{
					$this->jsonData['PUname'] 		= 'Parcel.nl';
					$this->jsonData['PUstreet'] 	= 'Euregioweg';
					$this->jsonData['PUpostalcode'] = '7532SN';
					$this->jsonData['PUhomeno'] 	= '332';
					$this->jsonData['PUhomenoAdd'] 	= '';
					$this->jsonData['PUcity'] 		= 'Enschede';
					$this->jsonData['PUcountry'] 	= 'NL';
					$this->jsonData['PUphone'] 		= '0534617777';
					$this->jsonData['PUemail'] 		= 'klantenservice@parcel.nl';
				}
				$pickdeliverydate = date('d-m-Y');
				if(date('l', strtotime(date('d-m-Y')))=='Sunday'){
				  $pickdeliverydate 		= date('d-m-Y', strtotime('tomorrow'));
				}elseif($timestamp>'14:00'){
				   $pickdeliverydate 		= (date('l', strtotime('tomorrow'))=='Sunday')?date('d-m-Y', strtotime('tomorrow + 1 day')):date('d-m-Y', strtotime('tomorrow'));
				}else{
				   $pickdeliverydate 		= date('d-m-Y');
				}
				$this->jsonData['PUdate'] 		= $pickdeliverydate;
				$this->jsonData['PUtime'] 		= '13:00-16:00';
				$this->jsonData['PUnote'] 		= '';
				$deliverydata = array();
				$street = $shipmentObj->RecordData[STREET];
				$streetN0 = $shipmentObj->RecordData[STREETNR];
				if($streetN0==''){
				  $streetN0 = preg_replace("/[^0-9]+/", "",substr($shipmentObj->RecordData[STREET],-5));
				  $street = str_replace($streetN0,'',$shipmentObj->RecordData[STREET]);
				}
				$strretAdd = preg_replace("/[^a-zA-Z]+/", "", $streetN0);
				$streetN0 = preg_replace("/[^0-9]/","",$streetN0);
				
				$deliverydata['DELname'] 		= $shipmentObj->RecordData[RECEIVER];
				$deliverydata['DELstreet'] 		= $street;
				$deliverydata['DELpostalcode'] 	= $shipmentObj->RecordData[ZIPCODE];
				$deliverydata['DELhomeno'] 		= $streetN0;
				$deliverydata['DELhomenoAdd'] 	= trim($strretAdd.' '.$shipmentObj->RecordData[STREET2].' '.$shipmentObj->RecordData[ADDRESS]);
				$deliverydata['DELcity'] 		= trim(str_replace('/','',$shipmentObj->RecordData[CITY]));
				$deliverydata['DELcountry'] 	= $shipmentObj->RecordData['rec_cncode'];
				$deliverydata['DELphone'] 		= $shipmentObj->RecordData[PHONE];
				$deliverydata['DELemail'] 		= $shipmentObj->RecordData[EMAIL];
				$deliverydata['DELdate'] 		=  $pickdeliverydate;
				$deliverydata['DELtime'] 		= '18:00-22:00';
				$deliverydata['DELAaantalColli'] = '1';
				$deliverydata['DELbarcodes'] 	= '';
				$deliverydata['CreateLabel'] 	= 'TRUE';

				$deliverydata['DELnote'] 		=  preg_replace('~[^a-zA-Z0-9]+~', '',$shipmentObj->RecordData[REFERENCE]);	
				$this->jsonData['Deliver'][] 		= $deliverydata;
				$this->getCreateLabelResponse($shipmentObj);
    	}
		
		public function getCreateLabelResponse($shipmentObj){
			 $URL = "https://api.fadello.nl/desktopmodules/fadello_retailAPI/API/v1/postOrder?apiID=".$this->Fadello['primary_port']."&apitoken=".$this->Fadello['primary_socket']."";
			 $labelcontent = $this->exceuteCurl($URL,json_encode($this->jsonData),$shipmentObj);
			 $json = json_decode($labelcontent);
			 if(trim($json->Status)=='OK'){
			     $shipmentObj->RecordData[BARCODE] 		= $json->TransDeliverID[0]->Barcode1;
				 $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
				 $shipmentObj->RecordData[TRACENR] 		= $json->TransDeliverID[0]->Barcode1;
				 $content  = array('Deliver1'=>$json->TransDeliverID[0]->Deliver1,'TransID'=>$json->TransID);
				 $this->storePDFData($shipmentObj,json_encode($content));
			 }else{
				$errorDecoded = json_decode($labelcontent);
				$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$shipmentObj->RecordData[SHIPMENT_ID].' '.$labelcontent);
				$shipmentObj->RecordData['ErrorMSG'] = $errorDecoded->Message;
				if($shipmentObj->RecordData['API']){ 
					$this->_db->update(SHIPMENT,array('forwarder_id'=>22,'wrong_parcel'=>'1'),"shipment_id=".$shipmentObj->RecordData[SHIPMENT_ID]."");
					$shipmentObj->RecordData[FORWARDER_ID]	= 22;
					$shipmentObj->RecordData['forwarder_detail'] = $shipmentObj->ForwarderDetail();
					$shipmentObj->GenerateBarcodeData(true);
					return true;
				}else{
				    $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$shipmentObj->RecordData[SHIPMENT_ID].' '.$json->Message);
					echo "F^".$json->Message;exit;
				}
			 }
		  return true; 
		}
		
		
		public function exceuteCurl($URL,$jsondata,$shipmentObj){
		 	$content = '';
			 try{   
				$ch = curl_init($URL);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: text/json'));
				curl_setopt($ch, CURLOPT_POSTFIELDS,$jsondata);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$content = curl_exec($ch); 
				curl_close($ch);
			}catch(Exception $e){  }
			
			return $content;
		}
	 public function storePDFData($shipmentObj,$pdfdata){
		$select = $this->_db->select()
									->from(YODEL_PDF,array('*'))
									->where("barcode='".$shipmentObj->RecordData[BARCODE]."'");
									//print_r($select->__toString());die;
		$record = $this->getAdapter()->fetchRow($select);
		if(empty($record)){
		   $this->_db->insert(YODEL_PDF,array_filter(array('barcode'=>$shipmentObj->RecordData[BARCODE],'pdf_contant'=>$pdfdata)));
		}else{
		   $this->_db->update(YODEL_PDF,array('pdf_contant'=>$pdfdata),"barcode='".$shipmentObj->RecordData[BARCODE]."'");
		}
		return; 
	  }	
}	  