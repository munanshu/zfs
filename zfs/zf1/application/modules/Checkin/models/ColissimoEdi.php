<?php

class Checkin_Model_ColissimoEdi extends Zend_Custom
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
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,'user_id','phoneno'))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")");
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
		// echo "<pre>"; print_r($data);die;

	    $ediBody = '';
	    $date     = date('Ymd');
		$time     = date('Hi');
		$fileName = $this->Forwarders['customer_number'].'.'.$date.'.'.$time;
		$ediBody .= $this->ColissimoEdiHeader($fileName,$date,$time);
		$counter =1;
		foreach($results as $result){
		    $this->RecordData = $result;
		  	$this->ForwarderRecord = $this->ForwarderDetail();
            $ediBody .= $this->ColissimoEdiBody($result, $counter);
			$counter++;
		}
	   
	   return array('EdiData'=>$ediBody,'EdiFilename'=>$fileName);
	}
	
	/**
	 * Generate Colissimo EDI Header
	 * Function : ColissimoEdiHeader()
	 * Function Generate Collisimo EDI Header
	 **/
	public function ColissimoEdiHeader($filename,$date,$time) {
	     $TopRecords = '';
		 $recordtype = 'BBB001;';
		 $friegtidentifier = '3181;';
		 $customer_number   = $this->Forwarders['customer_number'].';';
		 $shippingdate      = $date.$time.';';
		 $flightissuedate   = $date.$time.';';
		 $fileformatversion = '02.00;';
		 $injectionhub =   $this->Forwarders['depot_number'].';';
		 $corporatename =   'Parcel.nl BV;';
	     $TopRecords .= $recordtype.$friegtidentifier.$customer_number.$shippingdate.$flightissuedate.$fileformatversion.$injectionhub.$corporatename."\r\n";
	   return $TopRecords;
	}
	
	/**
	*Generate EDI For Colissimo
	*Function : ColissimoEdiBody()
	*Function Generate Generate EDI for Colissimo forwarder
	**/
	public function ColissimoEdiBody($data,$counter){
		   $PARCEL_DATA = '';
		   $customer_number 		= $this->Forwarders['customer_number'];
		   $product_cde     		= $this->Forwarders['service_type'];
		   
		   $recordtype = 'DDD001;';
		   $productcode      = $product_cde.';';
		   $parceltrackingid = $data[TRACENR].';';
		   $parcelweight     = ($data[WEIGHT] * 1000).';';
		   $deliverypostalcode = $data[ZIPCODE].';';
		   $cod 			 = '0;';
		   $codcurency       = 'EUR;';
		   $insurance        = '0;';
		   $insurencecurr    = 'EUR;';
		   $saturdaydelivery = 'O;';
		   $nonmactflag      = 'N;';
		  
		   $PARCEL_DATA  .= $recordtype.$productcode.$parceltrackingid.$parcelweight.$deliverypostalcode.$cod.$codcurency.$insurance.$insurencecurr.$saturdaydelivery.$nonmactflag;
		   
		  
		   $identity           = '``M '.$data[CONTACT].';';
		   $companyname        = $data[RECEIVER].';';
		   
		   	if($data[CONTACT]!=''){
				$identity      = '``M '.commonfunction::sub_string($data[CONTACT],0,31).';';
				$companyname   = $data[RECEIVER].';';
			}else{
				$identity      = '``M '.commonfunction::sub_string($data[RECEIVER],0,31).';';
				$companyname   = ';';
			}
		   
		   $firstaddressline   = ';';
		   $secondaddressline  = commonfunction::sub_string(trim($data[ADDRESS].' '.$data[STREET2]),0,35).';';
		   $thirdaddressline   = commonfunction::sub_string($data[STREET].' '.$data[STREETNR],0,35).';';
		   
		   $fourthaddressline  = ';';
		   $addresspostalcode  = $data[ZIPCODE].';';
		   $addresstown        = $data[CITY].';';
		   $shipperreference   = ';';
		   $door1   			= ';';
		   $door2   			= ';';
		   $interphone   		= ';';
		   $comment   			= ';';
		   $routingcode   		= ';';//$data[SHIPMENT_REROUTE_BARCODE].';';
		   $addresscountrycode  = $this->RecordData['rec_cncode'].';';
		   $levelofregis  		=   ';'; 
		   $acknowledgement  	=   'N;'; 
		   $parceltype		 	=   'NON;'; 
		   $frankotext 		 	=   'N;'; 
		   $addresscolisimo	 	=   'Colissimo;';
		   $phone = (!empty($data[PHONE]))?$data[PHONE]:$data['phoneno'];
		   $telephone	 		=  $phone.';'; 
		   
		   $email_address = (!empty($data[EMAIL]))?$data[EMAIL]:$data['email'];
		   
		   $email	 			=   $email_address.';'; 
		   $cellphone	 		=   ';'; 
		   $pickuolocation	 	=   ';'; 
		   $promotioncode	 	=   ';'; 
		   $addressnotificationtype =   ';'; 
		  
		 $PARCEL_DATA  .= $identity.$companyname.$firstaddressline.$secondaddressline.$thirdaddressline.$fourthaddressline.$addresspostalcode.$addresstown.$shipperreference.$door1.$door2.$interphone.$comment.$routingcode.$addresscountrycode.$levelofregis.$acknowledgement.$parceltype.$frankotext.$addresscolisimo.$telephone.$email.$cellphone.$pickuolocation.$promotioncode.$promotioncode.$addressnotificationtype."\r\n";
		return $PARCEL_DATA;									
	}

}

