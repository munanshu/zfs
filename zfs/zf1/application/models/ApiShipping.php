<?php

class Application_Model_ApiShipping extends Application_Model_Shipments
{
   public $PostData = array();
  
   public function UsernamePasswordValidation(){
       $error = '';
       if(empty($this->getData['username'])) {
           $error['Username'] = 'Please assign username!';
        }
       if(empty($this->getData['password'])) {
           $error['Password'] = 'Please assign password!';
       }
       if(!empty($error)) {
           return array('Error'=>array('ErrorMessage'=>commonfunction::implod_array($error,',')));
       }else{
	      $select = $this->_db->select()
						  ->from(array('UT'=>USERS),array('access_kay_valid','access_key'))
						  ->joininner(array('UD'=>USERS_DETAILS),"UT.user_id=UD.user_id",array('*'))
						  ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=UD.user_id",array('*'))
						  ->where('UT.username=?',addslashes($this->getData['username']))
						  ->where('UT.password=?',md5(addslashes($this->getData['password'])))
						  ->where('UT.user_status=?','1');
     	  $result = $this->getAdapter()->fetchRow($select);
						// print_r($result);die;
	    }	
		if(!empty($result))
		{
		   if($result['level_id']!=5 && $result['level_id']!=10){
			  return array('Error'=>array('ErrorMessage'=>'You have not privilege to create a shipment. Please contact to Parcel NL!'));
		   }
		   if($result['access_kay_valid']=='1' && $result['access_key']!=$this->getData['access_key']){
			  return array('Error'=>array('ErrorMessage'=>'Invalid Access Key!Please contact your administrator!'));
		   }
		   $logicSeesion = new Zend_Session_Namespace('logicSeesion');
		   $logicSeesion->userconfig =  $result;
		   $this->Useconfig = $result;
		   $this->getData[ADMIN_ID] = $result[ADMIN_ID];
		   $invoiceCheck = $this->InvoiceDueDateCheck();
		   if(!empty($invoiceCheck) && isset($invoiceCheck['Block']) && $invoiceCheck['Block']==1){
			  return array('Error'=>array('PrivillagesBlocked'=>strip_tags($invoiceCheck['ApiMessage'])));
			}
		   		   global $labelObj;
					$format = isset($logicSeesion->userconfig['label_position'])?$logicSeesion->userconfig['label_position']:'a6';
					if($format == 'a4' || $format == 'a1') {
						$labelObj = new Zend_Labelclass_PDFLabel('P','mm',$format);
					}
					elseif($format == 'a6') {
						$labelObj = new Zend_Labelclass_PDFLabel('P','mm','label_postat');
					}
			}else{
				return array('Error'=>array('ErrorMessage'=>'Couldn\'t find any users!'));
			}
	 return $error;	

  }
  
  public function setDataToIndex($data){


       $this->getData['username'] =  isset($data['username'])?trim($data['username']):'';
	   $this->getData['password']   = isset($data['password'])?trim($data['password']):'';
	   $this->getData['access_key']   = strtolower(trim(isset($data['access_key'])?$data['access_key']:''));
	   $this->getData['quantity'] = isset($data['quantity'])?trim($data['quantity']):0;
	   $this->getData['quantity'] = ($this->getData['quantity']>10)?10:$this->getData['quantity'];
	   $this->getData['weight']   = isset($data['weight'])?trim($data['weight']):0;
	   $this->getData['rec_name'] = isset($data['shipto'])?trim($data['shipto']):'';
	   $this->getData['rec_contact'] = isset($data['contact'])?trim($data['contact']):'';
	   $this->getData['rec_street'] = isset($data['street'])?trim($data['street']):'';
	   $this->getData['rec_streetnr'] = isset($data['streetno'])?trim($data['streetno']):'';
	   $this->getData['rec_address'] = isset($data['address1'])?trim($data['address1']):'';
	   $this->getData['rec_street2'] = isset($data['address2'])?trim($data['address2']):'';
	   $this->getData['rec_zipcode'] = isset($data['postalcode'])?trim($data['postalcode']):'';
	   $this->getData['countrycode'] = isset($data['countrycode'])?trim($data['countrycode']):0;
	   $contry_detail  = $this->getCountryDetail($this->getData['countrycode'], 2);
	   $this->getData['country_id']  = $contry_detail[COUNTRY_ID];
	   $this->getData['rec_city'] = isset($data['city'])?trim($data['city']):'';
	   $this->getData['rec_statecode'] = trim(isset($data['StateCode'])?$data['StateCode']:'');
	   $this->getData['rec_phone'] = isset($data['phone'])?trim($data['phone']):'';
	   $this->getData['rec_email'] = isset($data['email'])?trim($data['email']):'';
	   $this->getData['email_notification'] = (!empty($this->getData['rec_email']))?1:0;
	   $this->getData['rec_reference'] = isset($data['reference'])?trim($data['reference']):'';
	   $this->getData['shipment_worth'] = isset($data['price'])?trim($data['price']):0;
	   $this->getData['goods_id'] = isset($data['goods_type'])?trim($data['goods_type']):'';
	   $this->getData['goods_id'] = commonfunction::getGoodsType($this->getData['goods_id']);
	   $this->getData['goods_description'] = isset($data['goods_description'])?trim($data['goods_description']):'';
	   $this->getData['currency'] = trim(isset($data['currency'])?$data['currency']:'EUR');
	   $this->getData['servicecode'] = isset($data['servicecode'])?trim($data['servicecode']):'';
	   $serviceDetails = $this->getAPIService($this->getData['servicecode']);
	   //$this->getData['service_id'] = 1;$this->serviceIdbyNameInternalCode($this->getData['servicecode']);
	   //$this->getData['addservice_id'] = 0;
	   $this->getData['ActionCode'] = strtolower(trim(isset($data['ActionCode'])?$data['ActionCode']:'add'));
	   $this->getData['TrackingNumber'] = trim(isset($data['ParcelNumber'])?$data['ParcelNumber']:'');
	   if( isset($data['appdata']) && !empty($data['appdata'])){
	       $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	       $this->getData['app_reference'] = $alphabet[rand(0,25)].rand(0,9).date('hi');
	   }
	   $this->getData['labeltype'] = strtoupper((isset($data['labeltype'])?trim($data['labeltype']):''));
	   $this->getData['create_ip'] =  (isset($data['create_ip']))?trim($data['create_ip']):commonfunction::loggedinIP();
	   $this->getData['shipment_type'] = (isset($data['shipment_type'])) ? $data['shipment_type'] : 4;
	   //Sender
	   $this->getData['SenderCode'] 	= (isset($data['SenderCode'])) ? $data['SenderCode'] : 'C';
	   $this->getData['sname'] 			= (isset($data['sname'])) ? $data['sname'] : '';
	   $this->getData['saddress1'] 		= (isset($data['saddress1'])) ? $data['saddress1'] : '';
	   $this->getData['saddress2'] 		= (isset($data['saddress2'])) ? $data['saddress2'] : '';
	   $this->getData['szipcode'] 		= (isset($data['szipcode'])) ? $data['szipcode'] : '';
	   $this->getData['scity'] 			= (isset($data['scity'])) ? $data['scity'] : '';
	  // $this->getData['scountrycode'] 	= (isset($data['scountrycode'])) ? $data['scountrycode'] : '';
	   $this->getData['ParcelNumber'] 	= (isset($data['ParcelNumber'])) ? $data['ParcelNumber'] : '';
	   $this->getData['weightstandard'] 	= (isset($data['weightstandard'])) ? $data['weightstandard'] : '';	

	   $this->getData['order_ids'] 	= (isset($data['order_ids'])) ? $data['order_ids'] : '';	
  		// echo "<pre>"; print_r($this->getData);die;
	   if(strtoupper(trim($this->getData['weightstandard']))=='G'){
			$this->getData['weight'] = $this->getData['weight'] / 1000;
		}
	  $this->getData['orderinvoice'] = isset($data['orderinvoice'])?trim($data['orderinvoice']):'';   
	   if($this->getData['addservice_id']==7 || $this->getData['addservice_id']==146){
	    $this->getData['cod_price'] = $this->getData['shipment_worth'];
	  }
	   $this->PostData = $this->getData;
  }
  
  public function setDataMultishipment($data){
	   $this->getData['quantity'] = isset($data->quantity)?trim($data->quantity):0;
	   $this->getData['quantity'] = ($this->getData['quantity']>10)?10:$this->getData['quantity'];
	   $this->getData['weight']   = isset($data->weight)?trim($data->weight):0;
	   $this->getData['rec_name'] = isset($data->shipto)?trim($data->shipto):'';
	   $this->getData['rec_contact'] = isset($data->contact)?trim($data->contact):'';
	   $this->getData['rec_street'] = isset($data->street)?trim($data->street):'';
	   $this->getData['rec_streetnr'] = isset($data->streetno)?trim($data->streetno):'';
	   $this->getData['rec_address'] = isset($data->address1)?trim($data->address1):'';
	   $this->getData['rec_street2'] = isset($data->address2)?trim($data->address2):'';
	   $this->getData['rec_zipcode'] = isset($data->postalcode)?trim($data->postalcode):'';
	   $countrycode = isset($data->countrycode)?trim($data->countrycode):'';
	   $contry_detail  = $this->getCountryDetail($countrycode, 2);
	   $this->getData['country_id']  = $contry_detail[COUNTRY_ID];
	   $this->getData['rec_city'] = isset($data->city)?trim($data->city):'';
	   $this->getData['rec_statecode'] = isset($data->StateCode)?trim($data->StateCode):'';
	   $this->getData['rec_phone'] = isset($data->phone)?trim($data->phone):'';
	   $this->getData['rec_email'] = isset($data->email)?trim($data->email):'';
	   $this->getData['email_notification'] = ($this->getData['rec_email']!='')?1:0;
	   $this->getData['rec_reference'] = isset($data->reference)?trim($data->reference):'';
	   $this->getData['shipment_worth'] = isset($data->price)?$data->price:'';
	   $this->getData['goods_id'] = isset($data->price)?trim($data->goods_type):'';
	   $this->getData['goods_id'] = commonfunction::getGoodsType($data->goods_type);
	   $this->getData['goods_description'] = isset($data->goods_description)?trim($data->goods_description):'';
	   $this->getData['currency'] = isset($data->currency)?trim($data->currency):'EUR';
	   $this->getData['service_id'] = 1;//$this->serviceIdbyNameInternalCode(substr($data->servicecode'],0,1));
	   $this->getData['addservice_id'] = 0;
	   $this->getData['labeltype'] = isset($data->labeltype)?strtoupper(trim($data->labeltype)):'';
	   $this->getData['shipment_type'] = isset($data->shipment_type) ? $data->shipment_type: 4;
	   //Sender
	   $this->getData['SenderCode'] 	= (isset($data->SenderCode)) ? $data->SenderCode : '';
	   $this->getData['sname'] 			= (isset($data->sname)) ? $data->sname : '';
	   $this->getData['saddress1'] 		= (isset($data->saddress1)) ? $data->saddress1 : '';
	   $this->getData['saddress2'] 		= (isset($data->saddress2)) ? $data->saddress2 : '';
	   $this->getData['szipcode'] 		= (isset($data->szipcode)) ? $data->szipcode : '';
	   $this->getData['scity'] 			= (isset($data->scity)) ? $data->scity : '';
	   $this->getData['scountrycode'] 	= (isset($data->scountrycode)) ? $data->scountrycode : '';
	   
	   
  
  }
  
  public function checkSenderCode(){
       $senderAdd = new Application_Model_Senderaddress();
	   if((!isset($this->getData['SenderCode']) || $this->getData['SenderCode']=='') && isset($this->getData['szipcode']) && isset($this->getData['scountrycode'])){
			$this->getData['senderaddress_id']  = $senderAdd->createSenderAddress($this->getData);
	  }else{
	       $this->getData['senderaddress_id']  = $senderAdd->getAddressID($this->getData);
	  }	
  }
  
  public function AddApiShipment(){ 
      $this->checkSenderCode(); 
	  $this->checkRouting();
	  $label = $this->addShipment();
	  if(($this->getData['user_id']==2326 || $this->getData['user_id']==3553) && isset($label['Success'])){
		 $redata = $this->rowlabelData();
		 $label['Success']['RawLabelData'] = $redata; 
	  }
	  if($this->PostData['labeltype']=='PDF' && isset($label['Success'])){
	    $filenme = explode('pdf/',$label['Success']['LabelURL']);
	    $label['Success']['Label'] =  base64_encode(file_get_contents(API_SAVE_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/pdf/'.$filenme[1]));
	  }
	  return $label;
  }
  public function EditApiShipment(){
     $shipment_data = $this->getParcelData();
	 $this->getData['API_EDI'] = true;
	 $error = '';
	 if(empty($this->getData['ParcelNumber'])){
	    $error['EmptyParcelnumber'] = 'Please assign Parcel Number!';
	 }elseif(empty($shipment_data)){
	    $error['NoRecord'] = 'No Record found with given parcel number!';
	 }
	 if(!empty($error)) {
           return array('Error'=>array('ErrorMessage'=>commonfunction::implod_array($error,',')));
       }else{
	      $this->getData['shipment_id'] =  Zend_Encript_Encription::encode($shipment_data['shipment_id']);
		  $this->checkSenderCode(); 
		  $this->checkRouting();
		  $label = $this->editShipment($shipment_data);
		  return $label;
	  }
  }
  
  public function DeleteShipment(){
     $shipment_data = $this->getParcelData();
	 $this->getData['API_EDI'] = true;
	 $error = '';
	 if(empty($this->getData['ParcelNumber'])){
	    $error['EmptyParcelnumber'] = 'Please assign Parcel Number!';
	 }elseif(empty($shipment_data)){
	    $error['NoRecord'] = 'No Record found with given parcel number!';
	 }
	 if(!empty($error)) {
           return array('Error'=>array('ErrorMessage'=>commonfunction::implod_array($error,',')));
     }else{
	    $this->getData[SHIPMENT_ID][] = $shipment_data['shipment_id'];
	    $this->DeleteParcel();
		return array('Success'=>array('SuccessMessage' => 'MESSAGE: Shipment has been Deleted successfully.....'));
	 }
  }
  public function getParcelData(){
     try{
		$select = $this->_db->select()
									->from(array('ST'=>SHIPMENT),array('*'))
									->joininner(array('BT'=>SHIPMENT_BARCODE),"BT.shipment_id=ST.shipment_id" ,array())
									->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array('continent_id','postcode_length'))
									->where("BT.tracenr_barcode='".$this->getData['ParcelNumber']."' AND ST.delete_status='0' AND BT.delete_status='0' AND BT.checkin_status='0'");
		}catch(Exception $e){ echo $e->getMessage();die;}
		return $this->getAdapter()->fetchRow($select);	
  }
  
  public function getAPIService($internalcode){
      $service_ic = commonfunction::sub_string($internalcode,0,1);
	  $addservice_ic = commonfunction::sub_string($internalcode,1);
	  $this->getData['service_id'] = 0;
	  $this->getData['addservice_id'] = 0;
	  $select = $this->_db->select()
                ->from(array('SV'=>SERVICES), array('*'))
				->where("SV.internal_code='".$service_ic."'");
	  $servicedetails = $this->getAdapter()->fetchRow($select);	
	  if(!empty($servicedetails)){
	       if($servicedetails['parent_service']==0){
		      $this->getData['service_id'] 	= $servicedetails['service_id'];
		   }else{
		      $this->getData['service_id'] 	= $servicedetails['parent_service'];
			  $this->getData['addservice_id'] 	= $servicedetails['service_id'];
		   }
	  }
	  if(!empty($addservice_ic)){
		  $select = $this->_db->select()
					->from(array('SV'=>SERVICES), array('*'))
					->where("SV.internal_code='".$addservice_ic."'");
		  $addservicedetails = $this->getAdapter()->fetchRow($select);	
		  if(!empty($addservicedetails)){
			   $this->getData['addservice_id'] 	= ($addservicedetails['parent_service']>0)?$addservicedetails['service_id']:0;
		  }	
	 } 			
  }
  
  public function SenderCodeList(){
     try{ 
		$select = $this->_db->select()
							->from(array('SA'=>USER_SENDER_ADDRESS),array('*'))
							->joininner(array('CT'=>COUNTRIES),"CT.country_id=SA.country_id",array('CONCAT(CT.cncode,"-",CT.country_name) AS country'))
							->where("SA.user_id='".$this->getData[ADMIN_ID]."'");//print_r($select->__toString());die;
		}catch(Exception $e){ echo $e->getMessage();die;}
	   $senderAddress =  $this->getAdapter()->fetchAll($select); 
	   $finalAddress = array();
	   foreach($senderAddress as $key=>$Address){
	      $addressData = array();
		  $addressData['SenderCode'] = $Address['api_code'];
		  $addressData['SenderName'] = $Address['name'];
		  $addressData['SenderAddress'] = trim($Address['street'].' '.$Address['streetnumber'].' '.$Address['streetaddress']);
		  $addressData['SenderPostcode'] = $Address['postalcode'];
		  $addressData['SenderCity'] = $Address['city'];
		  $addressData['SenderCountry'] = $Address['country'];
		  $finalAddress['Code'.$key] = $addressData;
	   }
	   if(!empty($finalAddress)){
	     return array('SuccessMessage'=>'OK','Result'=>$finalAddress);
      }else{
        return array('SuccessMessage'=>'Error','Result'=>'There Is no Sender Address');
      }
  }
  
  public function ParcelStatus(){
     $where = '';
	if($this->getData['user_id']>0){
	   $where = " AND AT.user_id='".$this->getData[ADMIN_ID]."'";
	}
     $select = $this->_db->select()
                        ->from(array('BT'=>SHIPMENT_BARCODE), array('BT.weight','BT.tracenr_barcode','BT.local_barcode','BT.forwarder_id','BT.service_id','BT.delivery_status','BT.error_status'))
						->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('BD.checkin_ip','BD.checkin_by','BD.checkin_date','BD.delivery_date','BD.received_by','BD.rec_reference'))
						->joininner(array('ST'=>SHIPMENT),"BT.shipment_id=ST.shipment_id",array('ST.rec_name','ST.rec_street','ST.rec_streetnr',
		  			'ST.rec_address','ST.rec_street2','ST.rec_zipcode','ST.rec_city','ST.create_date','ST.forwarder_id as Sforwarder','ST.senderaddress_id','ST.create_ip','ST.create_by AS Createby','ST.addservice_id','ST.goods_id','ST.country_id'))
						->joininner(array('PL'=>PARCEL_TRACKING),"PL.barcode_id=BT.barcode_id",array('PL.status_date','PL.added_date '))
						->joininner(array('AT'=>USERS_DETAILS),"ST.user_id=AT.user_id",array('AT.company_name','AT.address1','AT.address2','AT.postalcode','AT.city as Ccity','AT.user_id'))
						->joininner(array('EL'=>STATUS_LIST),"EL.error_id=PL.status_id",array('EL.error_desc','EL.error_numeric'))
						->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array('CT.country_name'))
						->joininner(array('CT1'=>COUNTRIES),"CT1.country_id=AT.country_id",array('CT1.country_name AS Ccountry'))
						->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array('FT.forwarder_name'))
						->joininner(array('SV'=>SERVICES),"BT.service_id=SV.service_id",array('SV.service_name'))
                        ->where("BT.checkin_status='1' AND PL.added_date BETWEEN DATE_FORMAT(CURDATE() - INTERVAL 45 DAY, '%Y-%m-%d 09:00:00') AND DATE_FORMAT(CURDATE(), '%Y-%m-%d 09:00:00') ".$where);
						//print_r($select->__toString());die;
     $result = $this->getAdapter()->fetchAll($select);
	 if(!empty ($result)){
         foreach ($result as $key => $value) {
			   $this->RecordData = $value;
			   $addservice = '';
			   if($value['addservice_id']>0){
			      //$addservice = $this->serviceName($value['addservice_id']);
			   }
			 $forwarders = $this->ForwarderDetail();
			 $senderAddressArr = $forwarders['SenderAddress'];
             $data = array ();
             $data['SenderName'] 			= $senderAddressArr[0];
             $data['SenderStreet'] 			= $senderAddressArr[1];
             $data['SenderAddress2'] 		= '';
             $data['SenderPostalCode'] 		= $senderAddressArr[2];
             $data['SenderCity'] 			= $senderAddressArr[3];
             $data['SenderCountryName'] 	= $senderAddressArr[4];
			 $data['ReceiverName'] 			= $value['rec_name'];
			 $data['ReceiverStreet'] 		= $value['rec_street'];
			 $data['ReceiverStreetNo.'] 	= $value['rec_streetnr'];
			 $data['ReceiverAddress'] 		= $value['rec_address'].' '.$value['rec_street2'];
			 $data['ReceiverPostalCode.'] 	= $value['rec_zipcode'];
			 $data['ReceiverCity'] 			= $value['rec_city'];
			 $data['ReceiverCountry'] 		= $value['country_name'];
			 $data['CreateDate'] 			= ($value['create_date'] == '0000-00-00 00:00:00' ) ? '' : date('F d-Y, h:i', strtotime($value['create_date']));
			 $data['CheckInDate'] 			= ($value['checkin_date'] == '0000-00-00 00:00:00' ) ? '' : date('F d-Y, h:i', strtotime($value['checkin_date']));
			 $data['Weight'] 				= $value['weight'];
			 $data['Service'] 				= $value['service_name'];
			 $data['AdditionalService'] 	= $addservice;
			 $data['ReferenceNumber'] 		= $value['rec_reference'];
			 $data['Forwarder'] 			= $value['forwarder_name'];
			 $data['Barcode'] 				= $value['tracenr_barcode'];
			 //$data['CreatedBy'] 			= $this->CustomersName($value['Createby']);
			 $data['CreatedIP'] 			= $value['create_ip'];
			 //$data['Checked-InBy'] 			= $this->CustomersName($value['checkin_userid']);
			 $data['Checked-InIP'] 			= $value['checkin_ip'];
			 $data['ErrorName'] 			= $value['error_desc'];
			 $data['AssignDate'] 			= ($value['added_date'] == '0000-00-00 00:00:00' ) ? '' : date('F d-Y, h:i', strtotime($value['added_date']));
			 $data['DeliveryDate'] 			= ($value['delivery_date'] == '0000-00-00 00:00:00' ) ? '' : date('F d-Y, h:i', strtotime($value['delivery_date']));
			 $cod = "";
			 /*if($value['forwarder_id']==15 || $value['Sforwarder']==15 || $value['service_id']==7){
			   $codstatus = $this->CodPriceDetail($value['barcode']);
			   $cod = (($codstatus['status'] == 'Paid') && ($codstatus['customer_invoice_number'] > 0)) ? "'" . $codstatus['customer_invoice_number'] . "'" : $codstatus['status'];
               $cod = (!empty($cod)) ? trim($cod) : 'Unpaid';
			 }*/
			 $data['COD'] 					= $cod;
			 $data['Status'] 				= $value['delivery_status'];
             $finaldata['StatusDetail'.$key] 		= $data;
         }
         return array('SuccessMessage'=>'OK','DailyStatus'=>$finaldata);
     }else{
        return array('SuccessMessage'=>'Error','Error'=>'There Is no Parcel(s) Updates Today');
     }
  }
  
  public function CheckInShipmentList(){
  		$select = $this->_db->select()
							->from(array('ST' =>SHIPMENT),array('user_id','quantity','rec_name','rec_street','rec_streetnr','rec_address','rec_street2','rec_zipcode','rec_city','senderaddress_id','create_date','addservice_id','goods_id','country_id'))
							->joininner(array('BT' => SHIPMENT_BARCODE),'BT.'.SHIPMENT_ID. '=ST.'.SHIPMENT_ID,array(BARCODE,'tracenr_barcode','error_status','delivery_status','local_barcode','barcode','forwarder_id','weight'))
							->joininner(array('BD' => SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID. '=BT.'.BARCODE_ID,array(REFERENCE,'delivery_date','received_by','checkin_date',))
							->joininner(array('CT' => COUNTRIES), 'CT.' . COUNTRY_ID . '=ST.' . COUNTRY_ID , array('country_name'))
							->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array('FT.forwarder_name'))
						->joininner(array('SV'=>SERVICES),"BT.service_id=SV.service_id",array('SV.service_name'))
							->where('BT.'.CHECKIN_STATUS."= '1' AND BD.checkin_date>NOW() - INTERVAL 30 DAY")
							->where("ST.user_id='".$this->getData[ADMIN_ID]."'");
							//print_r($select->__toString());die;
			 $result = $this->getAdapter()->fetchAll($select);
			 $finaldata = array();
			 if(!empty ($result)){
				 foreach ($result as $key => $value) {
						 $data = array ();
						//get error details.....
						/*if($value['error_status']=='1'){
							$selecterror = $this->_db->select()
											->from(array('SBT' => SHIPMENT_BARCODE_TABLE),array('barcode'))
											->joininner(array('PET'=>'parcelerrorlogs'),'PET.'.SHIPMENT_BARCODE. '=SBT.'.SHIPMENT_BARCODE,array('error_id','dpd_error_url','timestamp','assigned_ip'))
											->joininner(array('ELT'=>'parcelerrorlists'),'ELT.error_id=PET.error_id',array('error_desc'))
											->order('PET.'.'assigned_date','DESC')
											->limit(1);
								$Errorresult = $this->getAdapter()->fetchRow($selecterror);
						}
					*/
					 $this->RecordData = $value;
					 $forwarders = $this->ForwarderDetail();
					 
					 $senderAddressArr = $forwarders['SenderAddress'];
					 
					 $data['ParcelNumber'] 		= $value['tracenr_barcode'];
					 $data['Weight'] 			= $value['weight'];
					 $data['Quanityt'] 			= $value['quantity'];
					 $data['Forwarder'] 		= $value['forwarder_name'];
					 $data['Service'] 			= $value['service_name'];
					 $data['DeatinaionCountry'] = $value['country_name'];
					 $data['Reference'] 		= $value['rec_reference'];
					 $data['ReceiverName'] 		= $value['rec_name'];
					 $data['Street'] 			= $value['rec_street'];
					 $data['HouseNumber']		= trim($value['rec_streetnr']);
					 $data['Address']			= ' ';
					 /*$data['PostCode'] 			= $value['rec_zipcode'];
					 $data['City'] 				= $value['rec_city'];
					 $data['SenderName'] 		= $senderAddressArr[0];
					 $data['SenderPostCode'] 	= $senderAddressArr[2];
					 $data['SenderCity'] 		= $senderAddressArr[3];
					 $data['SenderCountry'] 	= $senderAddressArr[4];
					 $data['CreateDate'] 			= ($value['create_date'] == '0000-00-00 00:00:00' ) ? '' : date('F d-Y, h:i', strtotime($value['create_date']));
					 $data['CheckInDate'] 			= ($value['checkin_date'] == '0000-00-00 00:00:00' ) ? '' : date('F d-Y, h:i', strtotime($value['checkin_date']));
					 if($value['error_status']=='1'){
					 	$data['ParcelError'] 	= $Errorresult['error_desc'];
						$data['ErrorDate'] 		= $Errorresult['timestamp'];
						
					 }
					 $data['LocalBarcode'] 	= $value['local_barcode'];
					 if($value['delivery_status']=='1'){
					 	$data['DeliveryDate'] 	= $value['delivery_date'];
						$data['ReceivedBy'] 	= $value['received_by'];
					 }*/
					
					 $finaldata['Parcel'.$key] = $data;
				 }
				  return array('SuccessMessage'=>'OK','ShipmentHistory'=>$finaldata);
			 }else{
				return array('SuccessMessage'=>'Error','Error'=>'There Is no History of Shipment(s)');
	 		  }
  }
  
  public function rowlabelData(){
		   $senderAdd = $this->RecordData['forwarder_detail']['SenderAddress'];
		   $dataArr = array();
		   $servicedetails = $this->getServiceDetails($this->RecordData['service_id'],1);
		   $addservicedetails = $this->getServiceDetails($this->RecordData['addservice_id'],1);
		   
		   $dataArr['ServiceName'] = $servicedetails['service_name'];
		   $dataArr['ServiceDescription'] = isset($addservicedetails['service_name'])?$addservicedetails['service_name']:'';
		   $dataArr['FromCompanyName'] = isset($senderAdd[0])?$senderAdd[0]:'';
		   $dataArr['FromAddressLine1'] = isset($senderAdd[2])?$senderAdd[2]:'';
		   $dataArr['FromAddressLine2'] = '';
		   $dataArr['FromTownCity'] = isset($senderAdd[3])?$senderAdd[3]:'';
		   $dataArr['FromArea'] = '';
		   $dataArr['FromPostcode'] = isset($senderAdd[4])?$senderAdd[4]:'';
		   $dataArr['ToCompanyName'] = $this->RecordData[RECEIVER];
		   $dataArr['ToAddressLine1'] = $this->RecordData[STREET]." ".$this->RecordData[STREETNR]." ".$this->RecordData[ADDRESS];
		   $dataArr['ToAddressLine2'] = $this->RecordData[STREET2];
		   $dataArr['ToTownCity'] = $this->RecordData[CITY];
		   $dataArr['ToArea'] = isset($this->RecordData['state_code'])?$this->RecordData['state_code']:'';
		   $dataArr['ToPostcode'] = $this->RecordData[ZIPCODE];
		   $dataArr['ToContactName'] = $this->RecordData[CONTACT];
		   $dataArr['ToContactTel'] = $this->RecordData[PHONE];
		   $dataArr['MeterNumber'] = '';
		   $dataArr['ServiceCode'] = $servicedetails['internal_code'];
		   $dataArr['DayCode'] = '';
		   $dataArr['TimeCode'] = '';
		   $dataArr['Reference']['Ref1'] = $this->RecordData[REFERENCE];
		   $dataArr['Reference']['Ref2'] = '';
		   $dataArr['ShipmentDate'] = date('Y/m/d',strtotime($this->RecordData[CREATE_DATE]));
		   $dataArr['ServiceCentreLocationName'] = '';
		   $dataArr['HubLocationName'] = 'Parcel.nl BV';
		   $dataArr['RoutingBarcode'] = '';
		   $dataArr['SpecialInstruction'] = '';
		   $select = $this->_db->select()
									->from(array('BT'=>SHIPMENT_BARCODE),array('barcode','weight','reference_barcode'))
									->where("BT.shipment_id='".$this->RecordData['shipment_id']."'")
									->order('BT.barcode_id ASC');//print_r($select->__toString());die;
		   $result = $this->getAdapter()->fetchAll($select);
		   $barcodearr = array();
		   foreach($result as $key=>$barcodedata){
		      $barcodearr['DataIdentifier'.$key] = '';
			  $barcodearr['RefBarcode'.$key] = $barcodedata['reference_barcode'];
			  $barcodearr['Barcode'.$key] = $barcodedata['reference_barcode'];
			  $barcodearr['HumanReadable'.$key] = $barcodedata[BARCODE];
			  $barcodearr['ReturnCode'.$key] = '';
			  $barcodearr['PackNumber'.$key] = ($key+1);
			  $barcodearr['PackageWeight'.$key] = $barcodedata[WEIGHT];
		   }
		   $dataArr['TrackingNumbers']['JDNumbers'] = $barcodearr;
		   
		   return $dataArr;
  }
  
  
  public function getAPIServiceCode(){
        $select = $this->_db->select()
									->from(array('SV'=>SERVICES), array('*'))
									->where("SV.status='1' AND SV.parent_service=0")
									->order("SV.service_name ASC");
									//print_r($select->__toString());die;
		$services =   $this->getAdapter()->fetchAll($select);
		$serviceInfo = array();
		foreach($services as $mainservice){
			$serviceArr = array();
			$serviceArr['ServiceCode'] = $mainservice['internal_code'];
			$serviceArr['ServiceName'] = $mainservice['service_name'];
			$serviceInfo['Service'.$mainservice['service_id']] = $serviceArr;
		   $select = $this->_db->select()
									->from(array('SV'=>SERVICES), array('*'))
									->where("SV.status='1' AND SV.parent_service='".$mainservice['service_id']."'")
									->order("SV.service_name ASC");
									
		   $addservices =   $this->getAdapter()->fetchAll($select);
			  if(!empty($addservices)){
				  foreach($addservices as $subservice){
					  $serviceArr = array();
					  $serviceArr['ServiceCode'] = $mainservice['internal_code'].$subservice['internal_code'];
					  $serviceArr['ServiceName'] = $subservice['service_name'];
					  $serviceInfo['Service'.$subservice['service_id']] = $serviceArr;
				  }
		   }
		}
	  return array('Success'=>array('SuccessMessage'=>'OK','Result'=>$serviceInfo));
   }
   
    public function getParcelshopData(){
     if(empty($this->getData['rec_zipcode'])) {
		 return array('Success'=>array('SuccessMessage'=>'Error','Result'=>'Please assign Valid Zipcode'));
	  }
	  if($this->getData['country_id']<=0) {
		 return array('Success'=>array('SuccessMessage'=>'Error','Result'=>'Please assign Valid Country Code'));
	  } 
     $object = new Application_Model_Common();
	 $locations = $object->getParcelshopList($this->getData['country_id'],$this->getData['rec_zipcode']);
	 if(!empty($locations)){
		 $ShopList = array();
		 foreach($locations as $key=>$location){
				$data = array();
				$company=(strrpos($location['company'],'|')>0)?substr_replace($location['company'],"Deburen",0,strrpos($location['company'],'|')+1):$location['company'];
		   		$company=(strpos($location['company'],'NL')===0)?substr($company,7):$company;
				$data['parcelShopId'] = $location['parcelShopId'];
				$data['company'] 	  = $company;
				$data['street']       = $location['street'];
				$data['houseNo']      = $location['houseNo'];
				$data['state'] 		  = $location['state'];
				$data['zipCode'] 	  = $location['zipCode'];
				$data['city']         = $location['city'];
				$data['distance']     = number_format($location['distance'],2);
				$data['fid'] 		  = $location['forwarder_id'];
				$data['shop_type'] 	  = $location['shop_type'];
				$data['parcel_shop'] 	  = json_encode($data);
				$data['latitude'] 	= $location['latitude'];
				$data['longitude'] 	= $location['longitude'];
				unset($data['fid']);
				unset($data['shop_type']);
				$ShopList['Parcelpoint'.$key] = $data;
		 }
		  return array('Success'=>array('SuccessMessage'=>'OK','Result'=>$ShopList));
	 }else{
	     return array('Success'=>array('SuccessMessage'=>'Error','Result'=>'Tthere is no any Parcelpoint for this country and Postalcode'));
	 }
  }
  
   public function getParcelStatus(){
   		 if(empty($this->getData['TrackingNumber'])){
		     return array('Success'=>array('SuccessMessage'=>'Error','Result'=>'Parcel number can not be blank!'));
		 }
		  $select = $this->_db->select()
								->from(array('ST' =>SHIPMENT),array(''))
								->joininner(array('BT' =>SHIPMENT_BARCODE),'BT.'.SHIPMENT_ID. '=ST.'.SHIPMENT_ID,array('*'))
								->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array('FT.forwarder_name'))
								->where("ST.user_id='".$this->getData[ADMIN_ID]."' AND BT.tracenr_barcode='".$this->PostData['TrackingNumber']."'");
								//print_r($select->__toString());die;
			$result = $this->getAdapter()->fetchRow($select);
		 $message = '';
		 $finaldata = array();
		 if(!empty($result)){
		    $data['shipstatus'] = (($result['delete_status']=='1')?0:($result['checkin_status']==1)?1:2);
			$data['forwarder'] = $result['forwarder_name'];
			$data['parcelnumber'] = $result['tracenr_barcode'];
			$data['trackingurl'] = BASE_URL."/Parceltracking/tracking/tockenno/".Zend_Encript_Encription::encode($result['barcode_id']);
			return array('Success'=>array('SuccessMessage'=>'OK','Result'=>$data));
		 }else{
		     return array('Success'=>array('SuccessMessage'=>'Error','Result'=>'Please assign valid parcel number'));
		 }
    }
	
	public function ApitrackingInfo(){
	    if(empty($this->getData['TrackingNumber'])){
		     return array('Success'=>array('SuccessMessage'=>'Error','Result'=>'Parcel number can not be blank!'));
		 }
		  $select = $this->_db->select()
								->from(array('ST' =>SHIPMENT),array(''))
								->joininner(array('BT' =>SHIPMENT_BARCODE),'BT.'.SHIPMENT_ID. '=ST.'.SHIPMENT_ID,array('barcode_id'))
								->where("ST.user_id='".$this->getData[ADMIN_ID]."' AND BT.tracenr_barcode='".$this->PostData['TrackingNumber']."'");
								//print_r($select->__toString());die;
		 $result = $this->getAdapter()->fetchRow($select);
		 if(!empty($result)){
		    $trackingObj = new Application_Model_Parceltracking();
			$trackingObj->getData['barcode_id'] = $result['barcode_id'];
			$trackingData = $trackingObj->parcelinformation();
		    if(!empty($trackingData)){
				$TrackArr = array();
				foreach($trackingData['Tracking'] as $key=>$locValue){
					$TrackArr['track'.$key]['location'] = $locValue['location'];
					$TrackArr['track'.$key]['status']	= (isset($locValue['status']))?$locValue['status']:'';
					$TrackArr['track'.$key]['updatedon']= (isset($locValue['updatedon']))?$locValue['updatedon']:'';
				}
				return array('Success'=>array('SuccessMessage'=>'OK','Result'=>$TrackArr));
			}else{
			   return array('Success'=>array('SuccessMessage'=>'Error','Result'=>'No Tracking Available!'));
			}
		 }else{
		     return array('Success'=>array('SuccessMessage'=>'Error','Result'=>'Please assign valid parcel number!'));
		 }
	}


	public function GetBulkShipments()
	{	
		$error = '';	
		$error =  isset($this->getData['order_ids'])? ( is_array($this->getData['order_ids']) ? ( !empty(  array_filter($this->getData['order_ids'])  ) ? ( commonfunction::is_multidim_array($this->getData['order_ids']) ? '"order_ids" should not be multidimensional array' : ( (ctype_digit(implode("", $this->getData['order_ids'])))  ? '' : 'Shipment ids should be in digits only' ) ) : 'No shipment id given' ) : 'Shipment ids should be given in non empty array format' ) : 'Shipment ids "order_ids" are not recieved';
		
		if(!empty($error))
			return array('Error'=>array('ErrorMessage'=> $error ));
		
		$in = "'".implode("','", $this->getData['order_ids'])."'";
		
		try {
			
			$select = $this->_db->select()->from(array('SH'=>SHIPMENT),array('SH.shipment_id','SH.rec_reference'))
			->joininner(array('BR'=>SHIPMENT_BARCODE),'SH.shipment_id = BR.shipment_id',array('BR.tracenr_barcode'))
			->where("SH.user_id='".$this->getData[ADMIN_ID]."' and SH.rec_reference IN($in)");
			// ->group("SH.rec_reference");

			$result = $this->getAdapter()->fetchAll($select);
			// echo "<pre>";print_r($result);die;

		} catch (Exception $e) {
			return array('Error'=>array('ErrorMessage'=> "Some Internal error occurred sorry for inconvenience" ));
		}

		  if(!empty($result) && commonfunction::is_multidim_array($result)){
				$KeyResult = array();

				foreach ($result as $key => $value) {
					if( in_array($value['rec_reference'], $this->getData['order_ids']) == true ){
						  
						$KeyResult[$value['rec_reference']]['rec_reference'][] = array('rec_reference'=>$value['rec_reference'],'shipment_id'=>$value['shipment_id'],'TrackingNumber'=>$value['tracenr_barcode']);  
					}
				}

				return array('Success'=>array('SuccessMessage'=>'OK','Result'=>$KeyResult));

		  }	else return array('Success'=>array('SuccessMessage'=>'OK','Result'=>$result)); 

	}


}

