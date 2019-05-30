<?php

class Checkin_Model_ColispriveEdi extends Zend_Custom
{
   public $ForwarderRecord = array();
   public $Forwarders	= array();
    
	public function generateEDI($data){
	  $this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);
	  try{
	   $select = $this->_db->select()
							->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
							->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE))
							->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,ADDRESS,CITY,ZIPCODE,STREET2,PHONE,EMAIL,
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id','cod_price'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,'user_id','phoneno'))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	    $ediBody = '';
		$this->_DateCreated = date('Ymd');
		$this->_CreatedTime = date('Hi');
		$ediBody .= $this->ColispriveEdiHeader();
		$counter =1;
		foreach($results as $result){
		    $this->RecordData = $result;
		  	$this->ForwarderRecord = $this->ForwarderDetail();
            $ediBody .= $this->CollispriveEdiColissimo($result, $counter);
			$counter++;
		}
		$ediBody .=  $this->EDIFooter($counter);
	   $edifilefile  = "coldes".$this->Forwarders['contract_number']."_".$this->_DateCreated."_".$this->_CreatedTime;
	   return array('EdiData'=>$ediBody,'EdiFilename'=>$edifilefile);
	}
	
	public function ColispriveEdiHeader() {
	     $recordtype = 'DEBUT';
		 $friegtidentifier = '99';
		 $TopRecords ='';
		 $customer_number   = $this->Forwarders['contract_number'];
		 $fileformatversion = '0001';
		 $filler =  str_pad('',575,' ',STR_PAD_RIGHT);
	     $TopRecords .= $recordtype.$friegtidentifier.$customer_number.$fileformatversion.$filler."\r\n";
	   return $TopRecords;
	}
	
	/**
	*Generate EDI For Colissimo
	*Function : CreateEdiColissimo()
	*Function Generate Generate EDI for Colissimo forwarder
	**/
	public function CollispriveEdiColissimo($data,$counter){
	        $PARCEL_DATA = '';
		   $postcode_covered =  $this->getPostcodecoveredArea($data); 
		   //$recordtype = 'DDD001';
		   $account_id 		= $this->Forwarders['contract_number'];
		   $parceltrackingid = str_pad(substr($data['local_barcode'],2,10),10,' ',STR_PAD_LEFT);
		   $customer_number 		= str_pad(substr($data[REFERENCE],0,10),10,' ',STR_PAD_RIGHT);
		   $orderNumber = str_pad(substr($data[REFERENCE],0,10),10,' ',STR_PAD_RIGHT);
		   
		   $PARCEL_DATA  .= $account_id.$parceltrackingid.$customer_number.$orderNumber;
		  
		   $companyname        = strtoupper(str_pad(substr(utf8_decode($data[RECEIVER]),0,32),32,' ',STR_PAD_RIGHT));
		   $rec_address =  $data[STREET].' '.$data[STREETNR].' '.$data[ADDRESS];
		   $address1 = substr($rec_address,0,32);
		   $address2 = (trim(substr($rec_address,32,32))!='')?substr($rec_address,32,32):$data[STREET2];
		   $address3 = (trim(substr($rec_address,32,32))!='' && trim($data[STREET2])!='')?substr($data[STREET2],0,32):'';
		   
		   $firstaddressline   =  strtoupper(str_pad(substr(trim($address1),0,32),32,' ',STR_PAD_RIGHT));
		   $secondaddressline  = strtoupper(str_pad(substr(trim($address2),0,32),32,' ',STR_PAD_RIGHT));
		   $thirdaddressline   = strtoupper(str_pad(substr(trim($address3),0,32),32,' ',STR_PAD_RIGHT));
		   $fourthaddressline  = strtoupper(str_pad('',32,' ',STR_PAD_RIGHT));
		   $addresspostalcode  = str_pad($data[ZIPCODE],5,' ',STR_PAD_RIGHT);
		   $addresstown        = strtoupper(str_pad($data[CITY],25,' ',STR_PAD_RIGHT));
		   $phone = (!empty($data[PHONE]))?$data[PHONE]:$data['phoneno'];
		   if(substr($phone,0,4)=='0033'){
		      $phone = substr($phone,4);
		   }elseif(substr($phone,0,2)=='33'){
		      $phone = substr($phone,2);
		   }elseif(substr($phone,0,2)=='00'){
		      $phone = substr($phone,2);
		   }
		   
		   $telephone	 		=  str_pad(substr($phone,0,10),10,' ',STR_PAD_RIGHT); 
		   $dispatch_date =  date('dmy');
		   $weight  = str_pad(($data[WEIGHT]/ 0.01),5,0,STR_PAD_LEFT);
		   if(($data[ADDSERVICE_ID]==7 || $data[ADDSERVICE_ID]==146) && $data['cod_price']>0){
		      $cod =  str_pad(($data['cod_price']*100),6,0,STR_PAD_LEFT); 
		   }else{
		   	 $cod = '000000';
		   }
		   $colissimo = 0;
		   if($postcode_covered['pc_covered']=='O'){
			  $product_code = 'S';
		   }elseif($postcode_covered['pc_covered']=='F' &&  $data['service_id']==7){
		      $product_code = 'A';
		   }elseif(($postcode_covered['pc_covered']=='F' || !isset($postcode_covered['pc_covered'])) && $data['service_id']!=7){
		         $product_code = 'B';
				 switch($postcode_covered['branch_code']){
					case 98:
					  $product_code = 'V';
					break;
					case 97:
					  $product_code = 'L';
					break;
				  }
				 $colissimo = 1; 
			 
		   }else{
		        $product_code = 'S';
		   }
		   
		   $postcode_covered = 'O';
		   $valueofparcel = '000000';
		   $comment1 = str_pad('',32,' ',STR_PAD_RIGHT);
		   $comment2 = str_pad('',32,' ',STR_PAD_RIGHT);
		   $statezipinternational = str_pad('',30,' ',STR_PAD_RIGHT);
		   $barcode_number = str_pad('',40,' ',STR_PAD_RIGHT);
		   $phone1 = str_pad($phone,14,' ',STR_PAD_RIGHT); 
		   $phone2 = str_pad('',14,' ',STR_PAD_RIGHT);
		   $phone3 = str_pad('',14,' ',STR_PAD_RIGHT);
		   
			$colisimoprofile_parcelnumber = str_pad((($colissimo==1)?$data[BARCODE]:''),16,' ',STR_PAD_RIGHT);
			$filler1 = str_pad('',4,' ',STR_PAD_LEFT);
			$filler2 = str_pad('',1,' ',STR_PAD_LEFT);
			$colisimoprofile_reroute = str_pad((($colissimo==1)?$data[REROUTE_BARCODE]:''),29,' ',STR_PAD_RIGHT);
			$email_pers = str_pad($data[EMAIL],50,' ',STR_PAD_RIGHT);
			
			$filler3 = str_pad('',10,' ',STR_PAD_LEFT);
			$filler4 = str_pad('',1,' ',STR_PAD_LEFT);
			$filler5 = str_pad('',22,' ',STR_PAD_LEFT);
			$filler6 = str_pad('',22,' ',STR_PAD_LEFT);
			$filler7 = str_pad('',13,' ',STR_PAD_LEFT);
		  
		 $PARCEL_DATA  .= $companyname.$firstaddressline.$secondaddressline.$thirdaddressline.$fourthaddressline.$addresspostalcode.$addresstown.$telephone.$dispatch_date.$weight.$cod.$product_code.$valueofparcel.$comment1.$comment2.$statezipinternational.$barcode_number.$phone1.$phone2.$phone3.$colisimoprofile_parcelnumber.$filler1.$filler2.$colisimoprofile_reroute.$email_pers.$filler3.$filler4.$filler5.$filler6.$filler7."\r\n";
		return $PARCEL_DATA;									
	}
	
	public function EDIFooter($counter){
	     //Code 500 
		 $PARCEL_DATA = ''; 
		 $PARCEL_DATA .= str_pad("FIN",5,' ',STR_PAD_RIGHT);
		 $PARCEL_DATA .= str_pad($counter,5,0,STR_PAD_LEFT);
		 $PARCEL_DATA .= str_pad('',590,' ',STR_PAD_RIGHT);
		
		return $PARCEL_DATA;     
	}
   
    public function getPostcodecoveredArea($data){
	      $select = $this->masterdb->select()
	   					->from(array('CR'=>COLISPRIVE_ROUTE),array('*'))
						->where("CR.postcode='".$data['rec_zipcode']."'");
		   $coveredarea = $this->masterdb->fetchRow($select);
		return $coveredarea;
	 }

}

