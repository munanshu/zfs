<?php

class Checkin_Model_ESCorreosEdi extends Zend_Custom
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
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,'user_id'))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	    $ediBody = '';
	    $fileName = 'FD'.$this->Forwarders['contract_number'].date("YmdHis");
        $counter = 1; 
		$total_records = count($results);
		foreach($results as $result){
		    $this->RecordData = $result;
		  	$this->ForwarderRecord = $this->ForwarderDetail();
            $ediBody .= $this->ESCorreosEDIBody($result, $counter, $total_records);
			$counter++;
		}

	   return array('EdiData'=>$ediBody,'EdiFilename'=>$fileName);
	}
	/**
	*Generate EDI For Bpost
	*Function : ESCorreosEDIBody()
	*Function Generate EDI for Correos forwarder
	**/
	public function ESCorreosEDIBody($data, $counter, $total_records){
        $sender_data = $this->getCustomerDetails($data[ADMIN_ID]);
		if($counter== 1 && $total_records==1){
			$position = 'U';	// If there is only one registration
		}
		elseif($counter==$total_records){
			$position = 'F';	//For the last registration
		}
		elseif($counter==1){
			$position = 'C';	//For the first registration
		}else{
        	$position = 'R'; //1 For the rest
		}

		//echo "<pre>";print_r($data); echo "</pre>"; die;
        $year_of_creation = '2012v001';  //2
        if ($data['service_id'] == 126) { //3
			  $product_code  = 'S0133';
        }else{
              $product_code  = 'S0132';
        }
		
        $franking_type 			 		= commonfunction::paddingRight("FP",2," ");'FP'; //4 to be changed
        $labeler_code 			 		= commonfunction::paddingRight($this->ForwarderRecord['contract_number'],4," ");
        $contract_number 		 		= commonfunction::paddingRight("",8," "); //6
        $client_number 			 		= commonfunction::paddingRight("",8," ");	//7
        $franking_machine_number 		= commonfunction::paddingRight("",8," ");	// 8 depend on $franking_type FM
        $amount_franked 		 		= commonfunction::paddingRight("",10," "); //9 depend on $franking_type FM
        $shipment_code 			 		= commonfunction::paddingRight($data['barcode'],23," ");	// 10 barcode
        $total_packages 				= '1';	                      	
        $package_number 		 		= '1';	                    	
		
		$manifest 				 		= 'MD'.$this->ForwarderRecord['contract_number'].'04'.date("YmdHis").'01';
        $manifest_number 		 		= commonfunction::paddingRight($manifest,24," ");// $data['manifest_number'];	// 13 to be passed in database
        $promotion_code 		 		= commonfunction::paddingRight("",10," ");	//14
        $reason_resending 		 		= commonfunction::paddingRight("",1," "); //15  1 to be determined

        //Addressee
        $name 							= commonfunction::sub_string(commonfunction::utf8Decode(trim($data[RECEIVER])),0,50);
		$name 							= commonfunction::paddingRight($name,50," "); //16
        $surname1 						= commonfunction::paddingRight("",50," "); // 17 not required
        $surname2 						= commonfunction::paddingRight("",50," "); //18 not required
        $taxpayer_identification_number = commonfunction::paddingRight("",15," "); // 19 to be determined
        $company 						= (trim($data[RECEIVER])=='')?commonfunction::paddingRight(commonfunction::sub_string(commonfunction::utf8Decode(trim($data[CONTACT])),50),50," "):commonfunction::paddingRight("",50," "); //20  to be determined
        $contact_person 				= commonfunction::sub_string(trim($data[CONTACT]),0,50);
		$contact_person 				= commonfunction::paddingRight($contact_person,50," ");	//21
        $street_type 					= commonfunction::paddingRight("",3," "); //22 to be determined
        $street_name 					= commonfunction::utf8Decode(trim($data[STREET])).' '.trim($data[STREET2]);
		$street_name 					= commonfunction::sub_string($street_name,0,50);
		$street_name 					= commonfunction::paddingRight(trim($street_name),50," "); //23
        $street_number 					= trim($data[STREETNR]);
		$street_number 					= commonfunction::paddingRight($street_number,5," ");	//24
        $main_door 						= commonfunction::paddingRight(commonfunction::sub_string($data[ADDRESS],0,5),5," "); //25 not required
        $block 							= commonfunction::paddingRight("",5," "); // 26not required
        $stairway 						= commonfunction::paddingRight("",5," "); //27 not required
        $floor 							= commonfunction::paddingRight("",5," "); //28 not required
        $door 							= commonfunction::paddingRight("",5," "); //29 not required
        $town 							= commonfunction::utf8Decode(trim(commonfunction::stringReplace(';','',$data[CITY])));
		$town 							= commonfunction::paddingRight($town,50," ");
        
		$str_province="";
		$post_code 						= commonfunction::onlynumbers(trim($data[ZIPCODE]));
		if($data['country_id']==192)
		{	
			$zip_code1  = commonfunction::paddingRight('',5," ");
			$zip_code2 	= commonfunction::paddingRight(commonfunction::sub_string($post_code,0,10),10," ");
			$Province 	= commonfunction::paddingRight('',40," ");
		}	
		else
		{	
		  $zip_code1     =  commonfunction::paddingleft(commonfunction::sub_string($post_code,-5,5),5,'0');
		  $zip_code2 	 =  commonfunction::paddingRight("",10," ");
		  $str_province  =  $this->GetProvince(commonfunction::sub_string($zip_code1,0,2));
		  $Province 	 =  commonfunction::paddingRight(commonfunction::sub_string($str_province,0,40),40," ");	
		}	
		
        $country 						= trim($data['cncode']);
		$country 						= commonfunction::paddingRight($country,2," ");	//34 2 digit iso code default ES
        $chosen_ofc 					= commonfunction::paddingRight("",7," "); //35 not required
        $destination_i_po_box 			= commonfunction::paddingRight("",1," "); //36 not required
        $destination_po_box 			= commonfunction::paddingRight("",6," "); //37 not required
        $contact_number 				= commonfunction::sub_string(commonfunction::onlynumbers(trim($data[PHONE])),-12);
		$contact_number 				= commonfunction::paddingRight($contact_number,12," "); 	//38
        $email 							= trim($data['rec_email']);
		$email 							= commonfunction::paddingRight($email,50," ");	//39
		
        //Shipment data
        $reference_number 				= trim($data[REFERENCE]);
		$reference_number 				= commonfunction::paddingRight(commonfunction::sub_string($reference_number,0,30),30," ");	//40
        $delivery_mode 					= ($data['service_id']==126 || $data['service_id']==149)?commonfunction::paddingRight("OR",2," "):commonfunction::paddingRight("ST",2," "); //41 to be changed	
        $weight 						= trim($data['weight']);
		$weight 						= commonfunction::paddingRight($weight*1000,5," ");	//42
        $length 						= commonfunction::paddingRight("",3," "); //43 not required
        $height 						= commonfunction::paddingRight("",3," "); //44 not required
        $width 							= commonfunction::paddingRight("",3," "); //45 not required
        $insurance 						= commonfunction::paddingRight("N",1," "); //46 not required
        $insuredvalue 					= commonfunction::paddingRight('',6," "); //47 not required, depend on $insurance
        if ($data['service_id'] == 7 || $data['service_id'] == 146) { 
            $cash_on_delivery 			= 'S'; //48
            $codAmount 					= trim($data['cod_price']);
			$codAmount 					= commonfunction::paddingRight($codAmount,6," ");	//49
			$type_of_cod 				= 'RC';
			$account_number 			= commonfunction::paddingRight("",20," ");
        }else{
            $cash_on_delivery 			= ''; //N
            $codAmount 					= commonfunction::paddingRight("",6," ");
			$type_of_cod 				= '  ';
			$account_number 			= commonfunction::paddingRight("",34," ");
        }
		
        $delivery 					= ' '; //52 not required
        $proof_of_delivery 			= '  '; //53 not required
        $pee_reference 				= commonfunction::paddingRight("",55," "); //54 not required
        $sender_pee_reference 		= commonfunction::paddingRight("",350," "); //55 not required
        $prior_delivery 			= commonfunction::paddingRight("",23," "); //56 not required
        $generate_return_delivery 	= " "; //57 not required
        $return_delivery_barcode 	= commonfunction::paddingRight("",23," "); //58 not required
        $return_delivery_expirydate = commonfunction::paddingRight("",8," "); //59 not required
        $return_delivery_allow_pkg 	= " "; //60 not required
        $return_delivery_code 		= commonfunction::paddingRight("",4," "); //61 not required
        $address_sms_number 		= commonfunction::paddingRight("",12," "); //62 not required
        $sender_sms_number 			= commonfunction::paddingRight("",12," "); //63 not required
        $sms_lang_sender 			= commonfunction::paddingRight("",1," "); //64 not required
        $sms_lang_addresse 			= commonfunction::paddingRight("",1," "); //65 not required
        $home_collection 			= commonfunction::paddingRight("",1," "); //66 not required
        $delivery_note_return 		= commonfunction::paddingRight("",1," "); //67 not required
        $satrday_delivery 			= commonfunction::paddingRight("",1," "); //68 not required
        $agreed_delivery 			= commonfunction::paddingRight("",8," "); //69 not required
        $prepaid_packaging 			= commonfunction::paddingRight("",1," "); // 70 not required
        $prepaid_packaging_code 	= commonfunction::paddingRight("",23," "); //71 not required
        $code_point_admission 		= commonfunction::paddingRight("",7," "); //72 not required
        $promotional_slogan 		= commonfunction::paddingRight("",80," "); //73 not required
        $envisaged_deposit_date 	= date('Ymd'); 	//74
        $observation1 				= commonfunction::paddingRight("",45," "); //75 not required
        $observation2 				= commonfunction::paddingRight("",45," "); //76 not required
        $return_instructions 		= commonfunction::paddingRight("",1," "); //77 not required

        //Custom data
        if($this->RecordData['goods_id']!='' || $this->RecordData['goods_id']=='Documents'){	//78
            $shipment_type = '1';
        }else if($this->RecordData['goods_id']=='Gifts'){
            $shipment_type = '3';
        }else if($this->RecordData['goods_id']=='commercial'){
            $shipment_type = '4';
        }else if($this->RecordData['goods_id']=='commercial goods'){
            $shipment_type = '2';
        }else{
            $shipment_type = '1';
        }
        $commercial_shipment = commonfunction::paddingRight("",1," ");	//79
        $invoice 			= commonfunction::paddingRight("",1," ");	//80
        $custom_dec_form 	= commonfunction::paddingRight("",1," "); 	//81
        $amount1 			= commonfunction::paddingRight("",3," "); //82 to be determined
        $description_goods1 = commonfunction::paddingRight("",3," "); //83 to be determined
        $net_weight1 		= commonfunction::paddingRight("",5," "); //84 to be determined
        $net_value1 		= commonfunction::paddingRight("",6," "); //85 to be determined
        $pricing_number1 	= commonfunction::paddingRight("",10," "); //86 not required
        $country_of_origin1 = commonfunction::paddingRight("",2," "); //87 not required
        $amount2 			= commonfunction::paddingRight("",3," "); //88 not required
        $description_goods2 = commonfunction::paddingRight("",3," "); //89 not required
        $net_weight2 		= commonfunction::paddingRight("",5," "); //90 not required
        $net_value2 		= commonfunction::paddingRight("",6," "); //91 not required
        $pricing_number2 	= commonfunction::paddingRight("",10," "); //92 not required
        $country_origin2 	= commonfunction::paddingRight("",2," "); //93 not required
        $amount3 			= commonfunction::paddingRight("",3," "); //94 not required
        $description_goods3 = commonfunction::paddingRight("",3," "); //95 not required
        $net_weight3 		= commonfunction::paddingRight("",5," "); //96 not required
        $net_value3 		= commonfunction::paddingRight("",6," "); //97 not required
        $pricing_number3 	= commonfunction::paddingRight("",10," "); //98 not required
        $country_origin3 	= commonfunction::paddingRight("",2," "); //99 not required
        $invoice_attached 	= " "; //100 not required
        $license_attached 	= " "; //101 not required
        $certificate_attached = " "; //102 not required

        //Details of sender
		$SenderInfo  = $this->ForwarderRecord['SenderAddress'];
		$customerName = ($SenderInfo[0]!='')?$SenderInfo[0]:$sender_data['company_name'];
		
		if($SenderInfo[0]=='' && $data[ADMIN_ID]==3121){
		  $customerName = 'Lidl Fotos';
		}
		
        $sender_name 			= 'PARCEL.NL';
		$sender_name 			= commonfunction::paddingRight($sender_name,50," ");	//103
        $sender_surname1 		= commonfunction::paddingRight("",50," "); //104 not required
        $sender_surname2 		= commonfunction::paddingRight("",50," "); //105 not required
        $sender_taxpayernumber 	= commonfunction::paddingRight("",15," "); //106 not required
        $sender_company 		= commonfunction::paddingRight($customerName,50," "); //107
        $sender_contact_person 	= commonfunction::paddingRight('PARCEL.NL',50," ");	//108
        $sender_street_type 	= commonfunction::paddingRight("",3," "); //109 not required
        $sender_street_name 	= commonfunction::paddingRight('C/29, nº 12 PARC LOGISTIC Polígono ZONA FRANCA',50," ");	//110
        $sender_street_number 	= commonfunction::paddingRight("",5," "); //111 not required
        $sender_maindoor 		= commonfunction::paddingRight("",5," "); //112 not required
        $sender_block 			= commonfunction::paddingRight("",5," "); //113 not required
        $sender_stairway 		= commonfunction::paddingRight("",5," "); //114 not required
        $sender_floor 			= commonfunction::paddingRight("",5," "); //115 not required
        $sender_door 			= commonfunction::paddingRight("",5," "); //116 not required
        $sender_town 			= commonfunction::paddingRight('CORREOS UAMCLI Paquetería',25," "); 	//117
        $sender_province 		= commonfunction::paddingRight('Barcelona',40," "); //118 not required 
		
		$sender_pc = commonfunction::paddingRight('08070',5," ");
		
        $sender_sendertelnumber = commonfunction::paddingRight(commonfunction::sub_string(commonfunction::onlynumbers($sender_data['phoneno']),-12),12," "); //120 not required
        $sender_email 			= commonfunction::paddingRight($sender_data['email'],50," "); //121 not required
        $sender_pobox 			= commonfunction::paddingRight("",6," "); //122 not required

        //control data of file
        $end_registration = 'E';	//123
		
        $tab = "\t";
		
        $PARCEL_DATA =  $position. "$tab" .$year_of_creation . "$tab" .$product_code. "$tab" .$franking_type. "$tab" .$labeler_code. "$tab" .$contract_number. "$tab" .
                        $client_number. "$tab" .$franking_machine_number. "$tab" .$amount_franked. "$tab" .$shipment_code. "$tab" .$total_packages. "$tab" .$package_number. "$tab" .
                        $manifest_number. "$tab" .$promotion_code. "$tab" .$reason_resending. "$tab" .
                        $name . "$tab" .$surname1. "$tab" .$surname2. "$tab" .$taxpayer_identification_number. "$tab" .$company. "$tab" .$contact_person. "$tab" .
                        $street_type. "$tab" .$street_name. "$tab" .$street_number. "$tab" .$main_door. "$tab" .$block. "$tab" .$stairway. "$tab" .$floor. "$tab" .$door."$tab"
                        .$town. "$tab" .$Province. "$tab" .$zip_code1. "$tab" .$zip_code2. "$tab" .$country. "$tab" .$chosen_ofc. "$tab" .$destination_i_po_box. "$tab" .$destination_po_box . "$tab" .
                        $contact_number. "$tab" .$email. "$tab" .
                        $reference_number . "$tab" .$delivery_mode . "$tab" .$weight. "$tab" .$length. "$tab" .$height. "$tab" .$width. "$tab" .$insurance . "$tab" .$insuredvalue. "$tab" .
                        $cash_on_delivery. "$tab" .$codAmount . "$tab" .$type_of_cod . "$tab" .$account_number . "$tab" .$delivery . "$tab" .$proof_of_delivery . "$tab" .
                        $pee_reference. "$tab" .$sender_pee_reference . "$tab" .$prior_delivery . "$tab" .$generate_return_delivery . "$tab" . $return_delivery_barcode. "$tab" .
                        $return_delivery_expirydate. "$tab" .$return_delivery_allow_pkg. "$tab" .$return_delivery_code. "$tab" .$address_sms_number . "$tab" .
                        $sender_sms_number . "$tab" .$sms_lang_sender. "$tab" .$sms_lang_addresse. "$tab" .$home_collection . "$tab" .$delivery_note_return. "$tab" .
                        $satrday_delivery. "$tab" .$agreed_delivery . "$tab" .$prepaid_packaging . "$tab" .$prepaid_packaging_code . "$tab" .$code_point_admission . "$tab" .
                        $promotional_slogan. "$tab" .$envisaged_deposit_date. "$tab" .$observation1 . "$tab" .$observation2 . "$tab" .$return_instructions . "$tab" .
                        $shipment_type. "$tab" .$commercial_shipment. "$tab" .$invoice. "$tab" .$custom_dec_form. "$tab" . $amount1 . "$tab" .$description_goods1 . "$tab" .$net_weight1. "$tab" .
                        $net_value1. "$tab" .$pricing_number1 . "$tab" .$country_of_origin1. "$tab" .$amount2 . "$tab" .$description_goods2. "$tab" .$net_weight2. "$tab" .
                        $net_value2 . "$tab" .$pricing_number2. "$tab" .$country_origin2 . "$tab" .$amount3 . "$tab" .$description_goods3 . "$tab" .$net_weight3 . "$tab" .
                        $net_value3. "$tab" .$pricing_number3. "$tab" .$country_origin3 . "$tab" .$invoice_attached . "$tab" .$license_attached. "$tab" .$certificate_attached. "$tab" .
                        $sender_name. "$tab" .$sender_surname1. "$tab" . $sender_surname2. "$tab" .$sender_taxpayernumber. "$tab" .$sender_company. "$tab" .
                        $sender_contact_person. "$tab" .$sender_street_type. "$tab" .$sender_street_name . "$tab" .$sender_street_number. "$tab" .
                        $sender_maindoor. "$tab" .$sender_block. "$tab" . $sender_stairway . "$tab" .$sender_floor . "$tab" .$sender_door. "$tab" .$sender_town. "$tab" .$sender_province. "$tab" .
                        $sender_pc . "$tab" .$sender_sendertelnumber. "$tab" .$sender_email. "$tab" .$sender_pobox. "$tab" .$end_registration . (($counter==$total_records)?'':"\r\n");


        return $PARCEL_DATA;
    }
	
	
	public function GetProvince($code="")
	{	
	    $select = $this->masterdb->select()
                                    ->from(array('PD'=>CORREOS_PROVINCE),array('province'))
                                    ->where("PD.post_code IN('".$code."')");
	  	$result = $this->masterdb->fetchRow($select);						   
	 	return $result['province'];	
	}

}

