<?php

class Checkin_Model_RussianpostEdi extends Zend_Custom
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
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id','shipment_worth','goods_description'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,'user_id'))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	    $ediBody = '';
        $counter = 1; 
		$ediBody .= $this->EDiHeade();
		foreach($results as $result){
		    $this->RecordData = $result;
		  	$this->ForwarderRecord = $this->ForwarderDetail();
            $ediBody .= $this->RussianpostEDIBody($result, $counter);
			$counter++;
		}
		$fileName = "Russianpost_".$this->Forwarders['IFD_number']."_D".date('Ymd')."T".date('his');
	   return array('EdiData'=>$ediBody,'EdiFilename'=>$fileName);
	}
	public function EDiHeade(){
	    $_nxtcol   	 = "\t";
	   	$_nxtrow  	 = "\n";
		// EDI Header
		$ediContents = "\" Client \"".$_nxtcol.
						"\" No agreement \"".$_nxtcol.
						"\"Barcode \"".$_nxtcol.
						"\"sender_name \"".$_nxtcol.
						"\"receiver_name \"".$_nxtcol.
						"\"zip_code \"".$_nxtcol.
						"\"region \"".$_nxtcol.
						"\"district \"".$_nxtcol.
						"\"city \"".$_nxtcol.
						"\"street \"".$_nxtcol.
						"\"house \"".$_nxtcol.
						"\"building \"".$_nxtcol.
						"\"apartment \"".$_nxtcol.
						"\"mobile phone number \"".$_nxtcol.
						"\"tarif_euro \"".$_nxtcol.
						"\"tarif_eur_cents \"".$_nxtcol.
						"\"weight_of_the_parcel_kg \"".$_nxtcol.
						"\"weight_of_the_parcel_g \"".$_nxtcol.
						"\"type_of_service_code \"".$_nxtcol.
						"\"COD_amount_rur \"".$_nxtcol.
						"\"COD_amount_rur_kopeks \"".$_nxtcol.
						"\"no_of_the_product \"".$_nxtcol.
						"\"name_of_the_product \"".$_nxtcol.
						"\"quantity of identical items_of_product \"".$_nxtcol.
						"\"weight_of_the_product_kg \"".$_nxtcol.
						"\"weight_of_the_product_g \"".$_nxtcol.
						"\"product_value_eur \"".$_nxtcol.
						"\"product_value_eur_cents \"".$_nxtcol.
						"\"category \"".$_nxtcol.
						"\"comments \"".$_nxtcol.
						"\"invoice_id \"".$_nxtcol.
						"\"Country \"".$_nxtcol.
						"\"zip_sender \"".$_nxtcol.
						"\"region_sender \"".$_nxtcol.
						"\"district1 \"".$_nxtcol.
						"\"city_sender \"".$_nxtcol.
						"\"street_sender \"".$_nxtcol.
						"\"house_sender \"".$_nxtcol.
						"\"bldg_sender \"".$_nxtcol.
						"\"apartment_sender \"".$_nxtcol.
						"\"no of delivery lot \"".$_nxtcol.
						"\"type_delivery_code \"".$_nxtcol.$_nxtrow;
		return $ediContents;				
	}
	/**
	*Generate EDI For Russianpost
	*Function : RussianpostEDIBody()
	*Function Generate EDI for Russianpost forwarder
	**/
	public function RussianpostEDIBody($data, $counter){
	   	$_nxtcol   	 = "\t";
	   	$_nxtrow  	 = "\n";
		$tarifprice = $this->russianpost_tarifprice($data);
		
		$ServiceCode = ($data[ADDSERVICE_ID]==115 || $data[ADDSERVICE_ID]==145) ? '7' : '1';		// note 1-CJ and 7-RJ
		//price value
		$tarif = explode('.',$tarifprice);
		$int_tarif = $tarif[0];
		$cent_tarif = (isset($tarif[1])) ? $tarif[1] : 0;
		
		//price value
		$currency = ($data['currency']!='')?$data['currency']:'EUR';
		if($data['addservice_id']==7 || $data['addservice_id']==146){
			$data['cod_price'] = ($data['cod_price']>0)?$data['cod_price']:$data['shipment_worth'];
			$codPrice = ($currency=='RUB')?$data['cod_price']:$this->convertCurrency($data['cod_price'], $currency,'RUB');
			$bpost_price = ($currency=='EUR')?$data['cod_price']:$this->convertCurrency($data['cod_price'], $currency,'EUR');
		}else{
		   $bpost_price = ($currency=='EUR')?$data['shipment_worth']:$this->convertCurrency($data['shipment_worth'], $currency,'EUR');
		}
		
		$Bpostprice = explode('.',$bpost_price);
		$int_price = $Bpostprice[0];
		$cent_price = (isset($Bpostprice[1])) ? $Bpostprice[1] : 0;
		
		//COD Price
		$int_price_cod = '';
		$cent_price_cod = '';
		if($data['addservice_id']==7 || $data['addservice_id']==146){
			$codPrice = explode('.',$codPrice);
			$int_price_cod = $codPrice[0];
			$cent_price_cod = (isset($codPrice[1])) ? $codPrice[1] : 0;
		}
		
		//weight valuescaler_weight
		//$parcelweight = ($data['scaler_weight']>0)?explode('.',$data['scaler_weight']):explode('.',$data[SHIPMENT_WEIGHT]);
		$parcelweight = commonfunction::explode_string($data[WEIGHT],'.');
		$kg_weight = $parcelweight[0];
		$gm_weight = (isset($parcelweight[1])) ? $parcelweight[1] : 0;
		$gm_weight = commonfunction::paddingleft($gm_weight,3,0);
		$streetnr = commonfunction::explode_string(commonfunction::stringReplace(array('-','/',',','_'),'.',$data[STREETNR]),'.');
		$house = (isset($streetnr[0])) ? $streetnr[0] : $data[STREETNR];
		$building = (isset($streetnr[1])) ? $streetnr[1] : $data[ADDRESS];
		$apartment = (isset($streetnr[2])) ? $streetnr[2] : $data[STREET2];
		if($apartment==''){
		   $apartment = $building;
		   $building = '';
		}
		$contents = '';
		$_nxtcol  = "\t";
	   	$_nxtrow  = "\n";
		
		$contents  = "\"" . commonfunction::stringReplace("\"", "\"\"", 'maparexx')."\"" . $_nxtcol;	//1 Client
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", 'A040')."\"" . $_nxtcol;	//2 No agreement 
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", $data[BARCODE]) . "\"" . $_nxtcol;	//3 Barcode
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode(commonfunction::stringReplace('-','',$this->ForwarderRecord['SenderAddress'][0]))) . "\"" . $_nxtcol;	// 4 sender_name 

		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[RECEIVER])) . "\"" . $_nxtcol;	//5 receiver_name
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[ZIPCODE])) . "\"" . $_nxtcol;	//6 zip_code
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '') . "\"" . $_nxtcol;	//7 region
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '') . "\"" . $_nxtcol;	//8 district
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[CITY])) . "\"" . $_nxtcol;	//9 city
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[STREET])) . "\"" . $_nxtcol;	//10 street
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($house)) . "\"" . $_nxtcol;	//11 house
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($building)) . "\"" . $_nxtcol;	//12 building
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::onlynumbers("/[^0-9]/","",$apartment)) . "\"" . $_nxtcol;	//13 apartment
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data[PHONE])) . "\"" . $_nxtcol;	//14 mobile phone number
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", $int_tarif ) . "\"" . $_nxtcol;	//15 tarif_euro
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", $cent_tarif) . "\"" . $_nxtcol;	//16 tarif_eur_cents
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", $kg_weight) . "\"" . $_nxtcol;	//17 weight_of_the_parcel_kg
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", $gm_weight) . "\"" . $_nxtcol; // 18 weight_of_the_parcel_g
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", $ServiceCode ) . "\"" . $_nxtcol;	//19 type_of_service_code
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", $int_price_cod) . "\"" . $_nxtcol;	//20 COD_amount_rur
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", $cent_price_cod) . "\"" . $_nxtcol;	//21 COD_amount_rur_kopeks
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '1') . "\"" . $_nxtcol;	//22 no_of_the_product
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($data['goods_description'])) . "\"" . $_nxtcol;	//23 name_of_the_product
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '1') . "\"" . $_nxtcol;	//24 quantity of identical items_of_product 
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($kg_weight)) . "\"" . $_nxtcol;	//25 weight_of_the_product_kg
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($gm_weight)) . "\"" . $_nxtcol;	//26 weight_of_the_product_g
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", $int_price) . "\"" . $_nxtcol;	//27 product_value_eur
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", $cent_price) . "\"" . $_nxtcol;	//28 product_value_eur_cents
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($this->RecordData['goods_id'])) . "\"" . $_nxtcol;	//29 category
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '') . "\"" . $_nxtcol;	//30 comment
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '') . "\"" . $_nxtcol;	//31 invoice id
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($this->ForwarderRecord['SenderAddress'][6])) . "\"" . $_nxtcol;	//32 sender Country
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($this->ForwarderRecord['SenderAddress'][4])) . "\"" . $_nxtcol;	//33 sender_zipcode
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '') . "\"" . $_nxtcol;	//34 sender_region
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '') . "\"" . $_nxtcol;	//45 sender_district
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($this->ForwarderRecord['SenderAddress'][3])) . "\"" . $_nxtcol;	// 56 sender_city
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($this->ForwarderRecord['SenderAddress'][2])) . "\"" . $_nxtcol;	//37 sender_street (address1)
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", commonfunction::utf8Decode($this->ForwarderRecord['SenderAddress'][8])) . "\"" . $_nxtcol;	// 38 sender_house
		
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '') . "\"" . $_nxtcol;	//39 bldg_sender
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '') . "\"" . $_nxtcol;	//49 apartment_sender
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '21') . "\"" . $_nxtcol;	//41 no of delivery lot 
		$contents .= "\"" . commonfunction::stringReplace("\"", "\"\"", '1') . "\"" . $_nxtcol;	//42 type_delivery_code
		return $contents.$_nxtrow;									
	}
	
	public function russianpost_tarifprice($data){ //print_r($data);die;
   		
		 $type = ($data[ADDSERVICE_ID]==115 || $data[ADDSERVICE_ID]==145) ? '1' : '2';
		 $min_weight = floor($data[WEIGHT]);
		 $max_weight = ceil($data[WEIGHT]);
		
		if(($type==1 && $min_weight==2) || ($type==2 && $min_weight==20)){
			$weight = " min_weight=".$min_weight." AND max_weight=0";
		}elseif($min_weight==$max_weight){
		   $weight = " min_weight<".$min_weight." AND max_weight>=".$max_weight;
		  }
		  else{
		   $weight = " min_weight=".$min_weight." AND max_weight=".$max_weight;
		  }
		
		$select = $this->masterdb->select()
                ->from(RUSSIANPOST_TARIF, array('sum_euro'))
                ->where("type=" . $type . " AND ".$weight);
        //print_r($select->__toString());die;
        $result = $this->masterdb->fetchRow($select);
		return $result['sum_euro'];
   }
	
}

