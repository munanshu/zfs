<?php

class Checkin_Model_BRTEdi extends Zend_Custom
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
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	    $ediBody = '';
		$fileName = 'FNVAB00R_'.date('Ymd').'_'.commonfunction::paddingleft($this->Forwarders['IFD_number'],3,0);
        $counter = 1; 
		$ediBody .= $this->BRTEdiHeader($fileName);
		foreach($results as $result){
		    $this->RecordData = $result;
		  	$this->ForwarderRecord = $this->ForwarderDetail();
            $ediBody .= $this->BRTEdiBody($result, $counter);
			$counter++;
		}

	   return array('EdiData'=>$ediBody,'EdiFilename'=>$fileName);
	}
	
	/**
	 * Generate BRT EDI Header
	 * Function : BRTEdiHeader()
	 * Function Generate BRT EDI Header
	 **/
	public function BRTEdiHeader($filename) {
	  $separator = ';';
	  $HEADER_DATA = '';
	  $HEADER_DATA  .= 'VABRMN'.$separator;
	  $HEADER_DATA  .= 'VABRSD'.$separator;
	  $HEADER_DATA  .= 'VABIND'.$separator;
	  $HEADER_DATA  .= 'VABLOD'.$separator;
	  $HEADER_DATA  .= 'VABCAD'.$separator;
	  $HEADER_DATA  .= 'VABPRD'.$separator;
	  $HEADER_DATA  .= 'VABNOT'.$separator;
	  $HEADER_DATA  .= 'VABNT2'.$separator;
	  $HEADER_DATA  .= 'VABNCL'.$separator;
	  $HEADER_DATA  .= 'VABPKB'.$separator;
	  $HEADER_DATA  .= 'VABNSP'.$separator;
	  $HEADER_DATA  .= 'VABCBO'.$separator;
	  $HEADER_DATA  .= 'VABCAS'.$separator;
	  $HEADER_DATA  .= 'VABTIC'.$separator;
	  $HEADER_DATA  .= 'VABVCA'.$separator;
	  $HEADER_DATA  .= 'VABFFD'.$separator;
	  $HEADER_DATA  .= 'VATNOT_B'.$separator;
	  $HEADER_DATA  .= 'VABLNP'.$separator;
	  $HEADER_DATA  .= 'VABTSP'.$separator;
	  $HEADER_DATA  .= 'VABCCM'.$separator;
	  $HEADER_DATA  .= 'VABCTR'.$separator;
	  $HEADER_DATA  .= 'VATNOT_S'.$separator;
	  $HEADER_DATA  .= 'VATNOT_I'.$separator;
	  $HEADER_DATA  .= 'VATNOT_E'.$separator;
	
	   return $HEADER_DATA."\n";
	}

	/**
	*Generate EDI For BRT
	*Function : BRTEdiBody()
	*Function Generate Generate EDI for BRT forwarder
	**/
	public function BRTEdiBody($data,$counter){
	        $sender_data = $this->getCustomerDetails($data[ADMIN_ID]);
			$separator = ';';
			$PARCEL_DATA = '';
			
			$customer_ref = commonfunction::sub_string($data[TRACENR],0,15);
			$VABRMN = $customer_ref.$separator;//Mumeric Customer Reference
			$VABRSD = commonfunction::sub_string($data[RECEIVER],0,35).$separator;//COMPANY NAME
			$VABIND = commonfunction::sub_string($data[STREET].' '.$data[STREETNR],0,35).$separator;//ADDRESS
			$VABLOD = commonfunction::sub_string($data[CITY],0,35).$separator;//AREA/city
			$VABCAD = commonfunction::sub_string($data[ZIPCODE],0,9).$separator;//POSTAL CODE
			
			$routingdata = $this->getRouting($data);
			
			$VABPRD = commonfunction::sub_string($routingdata['CPCPRV'],0,2).$separator;//MUNICIPALITY
			$VABNOT = commonfunction::sub_string('',0,35).$separator;//DESCRIPTION/NOTES
			$VABNT2 = commonfunction::sub_string('',0,35).$separator;//DESCRIPTION/ADDITIONAL NOTES
			$VABNCL = commonfunction::sub_string($data[QUANTITY],0,5).$separator;//NUMBER OF PACKS
			
			$VABPKB = commonfunction::sub_string(commonfunction::stringReplace('.',',',$data[WEIGHT]),0,7).$separator;//ENTERED WEIGHT (KG)
			$VABNSP = commonfunction::sub_string($data['tracenr'],0,7).$separator;//NUMERICAL SENDER REF
			$VABCBO = commonfunction::sub_string('1',0,2).$separator;//DELIVERY NOTE CODE
			
			$cod_amt = '';
			$cod_cur = '';
			if(($data[SERVICE_ID]==7 || $data[SERVICE_ID]==146) && $data['cod_price']>0){ 
			  $cod_cur = ($data['currency']!='')?$data['currency']:'EUR'; 
			  $cod_amt = ($data['cod_price']>0)?$data['cod_price']:'0.00'; 
			  $VABCBO = commonfunction::sub_string('4',0,2).$separator;//DELIVERY NOTE CODE
			}
			$VABCAS = commonfunction::sub_string(commonfunction::stringReplace('.',',',$cod_amt),0,13).$separator;//C.O.D. SUM
			$VABTIC = commonfunction::sub_string('',0,2).$separator;//TYPE OF C.O.D. PAYMENT
			$VABVCA = commonfunction::sub_string($cod_cur,0,3).$separator;//C.O.D. SUM CURRENCY
			
			//$service_type = 'C';
			 $service_type = 'D';
			 
			$data['DepartureDepot'] = 107;
			$depotdescription = $this->getDepotDescription($data,$routingdata);
			
			$senderclientcode = '1071071';
			//$pricingcode = 0 ;
			$pricingcode = 100 ;
			$phone = strlen($data[PHONE])>5?$data[PHONE]:$sender_data['phoneno'];
			$email = ($data[EMAIL]!='')?$data[EMAIL]:$sender_data['email'];
			
			$VABFFD = commonfunction::sub_string('',0,1).$separator;//DEPOSITED TILL CALLED FOR
			$VATNOT_B = commonfunction::sub_string($phone,0,15).$separator;//The field contains the telephone number of the consigneee
			$VABLNP = commonfunction::sub_string($data['DepartureDepot'],0,3).$separator;//OPERATIVE DEPARTURE POINT 
			$VABTSP = commonfunction::sub_string($service_type,0,1).$separator;//SERVICE TYPE
			$VABCCM = commonfunction::sub_string($senderclientcode,0,7).$separator;//SENDER CLIENT CODE
			$VABCTR = commonfunction::sub_string($pricingcode,0,3).$separator;//PRICING CONDITION CODE
			
			$VATTRC_S = commonfunction::sub_string($phone,0,15).$separator;//Phone number to which sending alert SMS
			$VATTRC_I = commonfunction::sub_string($email,0,50).$separator;//The field contains the e-mail address
			$VATNOT_E = commonfunction::sub_string($data[BARCODE],0,18);//UNIQUE PARCEL BARCODE

		   $PARCEL_DATA = $VABRMN.$VABRSD.$VABIND.$VABLOD.$VABCAD.$VABPRD.$VABNOT.$VABNT2.$VABNCL.$VABPKB.$VABNSP.$VABCBO.$VABCAS.$VABTIC.$VABVCA.$VABFFD.$VATNOT_B.$VABLNP.$VABTSP.$VABCCM.$VABCTR.$VATTRC_S.$VATTRC_I.$VATNOT_E."\n";
		return $PARCEL_DATA;									

	}

    public function getRouting($data){
	    $select = $this->masterdb->select()
                        ->from(array(BRT_ROUTING), array('*'))
                        ->where("CPCVER=46 AND (CPCNAR='".$this->RecordData['rec_cncode']."' OR CPCNAR='') AND CPCCAP='".commonfunction::onlynumbers($data[ZIPCODE])."'");
  						 //echo $select->__tostring();die;
   		$result = $this->masterdb->fetchRow($select);
		if($data['weight']<$result['CPCLKS']){
		   $result['CPCZNC'] = $result['CPCZOS'];
		}elseif($data['weight']>$result['CPCLKS'] && $data['weight']<=$result['CPCLKG']){
		   $result['CPCZNC'] = $result['CPCZNC'];
		}
		if($data['weight']<$result['CPCLKS']){
		   $result['CPCLNA'] = $result['CPCLOS'];
		}elseif($data['weight']>$result['CPCLKS'] && $data['weight']<=$result['CPCLKG']){
		   $result['CPCLNA'] = $result['CPCLNA'];
		}
		return $result;
	}
	
	public function getDepotDescription($data,$routingdata){
	    $select = $this->masterdb->select()
                        ->from(array(BRT_TERMINAL), array('*'))
                        ->where("CAEEPA='A' AND CAETFA='".$routingdata['CPCLNA']."'");
  						 //echo $select->__tostring();die;
   		$result = $this->masterdb->fetchRow($select);
		return $result;
	}

}

