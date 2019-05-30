<?php

class Checkin_Model_SystematicEdi extends Zend_Custom
{
   public $ForwarderRecord = array();
   public $Forwarders	= array();
   public $manifestFile	= '';
    
	public function generateEDI($data){
	  $this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);
	  $this->manifestFile = $data['manifest_file'];
	  try{
	   $select = $this->_db->select()
							->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
							->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE,'checkin_date'))
							->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,ADDRESS,CITY,ZIPCODE,STREET2,PHONE,EMAIL,
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id','goods_description','length','width','height'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,'user_id'))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	    $ediBody = '';
        $counter = 1; 
		foreach($results as $result){
		    $this->RecordData = $result;
		  	$this->ForwarderRecord = $this->ForwarderDetail();
			$fileName = "CDS"."_D".$counter.date('Ymd')."T".date('his');
            $ediBody .= $this->SystematicEDIBody($result, $fileName,$counter);
			$counter++;
		}

	   return array('EdiData'=>$ediBody,'EdiFilename'=>$fileName);
	}
	/**
	*Generate EDI For Systematic
	*Function : SystematicEDIBody()
	*Function Generate EDI for Correos forwarder
	**/
	public function SystematicEDIBody($data, $fileName,$counter){
		
		$typeofpakage = $this->GetCDSDimensionName($data);
		//echo"<pre>";print_r($typeofpakage);die;
		//$ReultData= $this->SenderAddress(FORWARDER_CDS,array($data['senderaddress_id'],$data[ADMIN_ID],$data[COUNTRY_ID]));
		
		$senderAdd = $this->ForwarderRecord['SenderAddress'];
		
		$dom = new DOMDocument ( '1.0', 'UTF-8' );
		$root = $dom->createElement ('Shipment');
		$root->setAttribute ( 'create', 'true');
		$root->setAttribute ( 'update', 'false');
		$dom->appendChild ($root);
		$service="";
		if($data['cncode']=='IE' || $data['cncode']=='GB')
		{
		$service='E';
		}		
		elseif( $senderAdd[5]=='IE' || $senderAdd[5]=='GB')
		{
		$service='I';
		}
		else
		{
		$service='D';
		}
		
		$struct['Mode'] = 'R';
		$struct['Service'] =$service ;
		$struct['CD_code'] = 'D';
		$struct['Principal_number'] = 10349;
		$struct['Local_Customer_Number'] = 10349;
		$struct['Foreign_Customer_Number']= 10349;
		$struct['Waybill_Number'] 	='';		// optional
		$struct['Shipment_Date'] 	= date('Ymd',strtotime($data['checkin_date']));		// pickup date in format yyyymmdd
		$struct['Pickup_Date'] 		= date('Ymd',strtotime($data['checkin_date']));			// pickup date in format yyyymmdd
		$struct['Delivery_Date'] 	= '';		// delivery date in the format YYYYMMDD
		$struct['Handling_Instructions'] = ''; // optional
		$struct['Invoice_Reference'] 	= $data['barcode'];//$data['reference_no'];	//Reference that will be put on the invoice (your reference).
		
		$pickup_address = $this->GetPickupAddress($data);
		if(!empty($pickup_address)){
			$struct['Pickup_Name']		= $pickup_address['company_name'];	// pickup name
			$struct['Pickup_Address1']	= $pickup_address['street1'];	//	street+number
			$struct['Pickup_Address2']	= $pickup_address['street2'];;	// building+flor
			$struct['Pickup_City']		= $pickup_address['zipcode'].' '.$pickup_address['city'];	// zipcode n city of pickup address
			$struct['Pickup_Country_Code']= $pickup_address['country_id'];	//country code of pickup address
		}else{
			$struct['Pickup_Name']		= $senderAdd[0];	// pickup name
			$struct['Pickup_Address1']	= $senderAdd[2];	//	street+number
			$struct['Pickup_Address2']	= '';	// building+flor
			$struct['Pickup_City']		= $senderAdd[4].' '.$senderAdd[3];	// zipcode n city of pickup address
			$struct['Pickup_Country_Code']= $senderAdd[5];
		}
		$struct['Delivery_Name']		= $data['rec_name'];	// delivery name
		$struct['Delivery_Address1']	= $data['rec_street'].' '.$data['rec_streetnr'];	//
		$struct['Delivery_Address2']	= '';	//
		$struct['Delivery_City']		= $data['rec_zipcode'].' '.$data['rec_city'];	//
		$struct['Delivery_Country_Code'] = $data['cncode'];	//
		
		$struct['Pieces_1']			= $counter;	//
		$struct['Type_of_package_1'] 	= $typeofpakage;	//
		$struct['Pieces_2']			= '';	// optional
		$struct['Type_of_package_2']	= '';	// optional
		$struct['Pieces_3']			= '';	// optional
		$struct['Type_of_package_3']	= '';	// optional
		$struct['Goods_Description_1']	= $data['goods_description'];	// extra information concerning the shipment like a phone number,delivery contact or an extra reference
		$struct['Goods_Description_2']	=(!empty($data[PHONE]))?'Phone: '.$data[PHONE]:'';	//optional
		$struct['Goods_Description_3']	=(!empty($data[EMAIL]))?'Email: '.$data[EMAIL]:'';	//optional
		
		$struct['Gross_Weight']		= $data['weight'];	//The total gross weight of the shipment
		
		$xmlData = $this->_structValue ($struct,$dom,$root );
		$dom->save(EDI_SAVE.'/'.$fileName.'.xml');
		try{
		$ftp_sftp = new Zend_Custom_EdiUpload();
		$ftp_sftp->getForwarderFTP($data['forwarder_id']);
		$upload_status = $ftp_sftp->uploadeFtp('/out/'.$fileName.'.xml',EDI_SAVE.'/'.$fileName.'.xml',FTP_ASCII);
		$ftp_sftp->close();
		 
		 $this->_db->insert(SHIPMENT_EDI,array_filter(array('forwarder_id'=>$data['forwarder_id'],'edi_file_name'=>$fileName.'.xml','manifest_file_name'=>$this->manifestFile,'create_ip'=>commonfunction::loggedinIP(),'create_date'=>new Zend_Db_Expr('NOW()'),'upload_status'=>$upload_status)));
		 
	    $this->_db->update(FORWARDERS,array('IFD_number'=>new ZEND_DB_EXPR('IFD_number + 1')),"forwarder_id ='".$data['forwarder_id']."'"); 
	  }catch(Exception $e){
		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		}	
			return true;									
	}
	
	public function _structValue($struct,DOMDocument $dom,DOMElement $parent) {
			$struct = ( array ) $struct;
			foreach ( $struct as $key => $value ) {
				if ($value === false) {
					$value = 0;
				}
				elseif ($value === true) {
					$value = 1;
				}

               if (ctype_digit ( ( string ) $key )) {
                $key = 'key_' . $key;
               }
               if (is_array ( $value ) || is_object ( $value )) {
                $element = $dom->createElement ($key);
                    if($key=='MailItem'){
                    $element->setAttribute('ItemId', $this->ean_code[$key]);
                    }
                $this->_structValue ( $value, $dom, $element );
               } 
				else{
					$element = $dom->createElement ($key);
					$element->appendChild ( $dom->createTextNode ( $value ) );
				}
				$parent->appendChild ( $element );
			}
		}
		
	public function GetCDSDimensionName($data){
		
		 $select = $this->masterdb->select()
				  ->from(SYSTEMATIC_DIMENSION,array('name'))
				  ->where("length>=".$data['length']." AND ((length >= ".$data['length']." AND width  >= ".$data['width'].") OR (length >= ".$data['width']." AND width  >= ".$data['length'].")) AND height>=".$data['height']." AND weight>=".$data['weight'])
				  ->order('weight')
				  ->limit(1);
				 // print_r($select->__toString());die;
	    $Resutl = $this->masterdb->fetchRow($select);
	    return (isset($Resutl['name'])) ? $Resutl['name'] : '';
		
	}
	
	public function GetPickupAddress($data){
		 $select = $this->_db->select()
				  ->from(SYSTEMATIC_PICKUP,array('*'))
				  ->where("barcode_id='".$data['barcode_id']."'")
				  ->order('id DESC')
				  ->limit(1);
				  //print_r($select->__toString());die;
	    $Resutl = $this->getAdapter()->fetchRow($select);
	    return $Resutl;
	}
}

