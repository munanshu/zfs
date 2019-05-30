<?php

class Checkin_Model_HamacherEdi extends Zend_Custom
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
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id','length','width','height'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,'user_id','company_name','first_name','last_name','address1','address2'))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")")
							->order("BT.shipment_id ASC"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	    	foreach($results as $result)
			{
				$array_shipment[]  = $result['shipment_id'];
			}
			$shipmentcounts = array_count_values($array_shipment);
			
			$TotalEdiData = '';
			$fileName = 'FD'.$this->Forwarders['contract_number'].date("YmdHis");
			$counter = 1; 
			$start              = '@@PHBORD128 0128003500107 PARCEL1 HAMAGRO';
			$end  ="\r\n". '@@PT';
			$header=$this->AddHeader();
			$tot_actual_weight=0;
			$prev_shipment="";
			$EdiData= array('0'=>'','1'=>'');
			$ShipmentNo=0;
			$F_EdiData = '';
			$H_EdiData = '';
		foreach($results as $result){
		    $this->RecordData = $result;
		  	$this->ForwarderRecord = $this->ForwarderDetail();
			if($result['shipment_id']!=$prev_shipment)
			{
				$ShipmentNo++;
				$TotalEdiData.=$EdiData[0].$F_EdiData.$H_EdiData.$EdiData[1];
				$F_EdiData="";		
				$H_EdiData="";			
				$EdiData = $this->CreateEdiHamacher($result,$ShipmentNo);
				 
				$shipment_id = $result['shipment_id'];
				$count_shipment =  $shipmentcounts[$shipment_id];
				$F_EdiData.=$this->F_record_EdiHamacher($result,$ShipmentNo,$count_shipment);
				$counter ++;	
			}
		  	$H_EdiData.=$this->H_record_EdiHamacher($result,$ShipmentNo);			
			$actual_weight=0;
			$actual_weight=$this->actual_weight($result);
			$tot_actual_weight=$tot_actual_weight+$actual_weight;		
			$prev_shipment = $result['shipment_id'];
		}
		$TotalEdiData.=$EdiData[0].$F_EdiData.$H_EdiData.$EdiData[1];
		$shipment_summary=$this->shipment_summary($ShipmentNo,$counter,$tot_actual_weight);
		$record  = $start.$header.$TotalEdiData.$shipment_summary.$end;
	   $fileName ='hamacher_'.mktime().'_'. $this->Forwarders['IFD_number'];
	   return array('EdiData'=>$record,'EdiFilename'=>$fileName);
	}
	 public function CreateEdiHamacher($data,$ShipmentNo=1)
	{ 
		$brecordRow="";
		$crecordRow="";
		$drecordRow="";
		$erecordRow="";
		$frecordRow="";
		$IrecordRow="";
		$jrecordRow="";
		$hrecordRow="";
		  // A record Waybill header record
        ///   B record   Consignor address record - part 1
		    $senderData = $this->ForwarderRecord['SenderAddress'];
			$Brecord                    ='B';
			$sequentialwaybill_positon  = str_pad($ShipmentNo,3,0,STR_PAD_LEFT);
			$consignorname_one          = str_pad( strtoupper( $senderData[0]),35);                        // 35 digit mandotry
			$consignorname_two          = str_pad( strtoupper($senderData[1]), 35);                                        // 35 optional	 	
			$consigner_street           = str_pad(strtoupper($senderData[2]),35);      //Consignor street and street number optional  35 digit
			$consiner_country_code      = str_pad($senderData[5],3);  	
			//digit //optional
			$sender_postcode            = str_pad($senderData[4],9);                   //  Postcode sender 9 digit optional	 
			$freedomesile_code_pickup   = str_pad('', 3);
			$freedomesile_code_indicator = str_pad('',1);
			$brecordRow        = "\r\n".$Brecord.$sequentialwaybill_positon.$consignorname_one.$consignorname_two.$consigner_street.$consiner_country_code.$sender_postcode.$freedomesile_code_pickup.$freedomesile_code_pickup.$freedomesile_code_indicator;
			//  C record Consignor address record - part 2
			
			$crecord                        =  'C';
			$sequentialwaybill_posc         = str_pad($ShipmentNo,3,0,STR_PAD_LEFT);             //mandotary  3 digit
			$consignertown                  = str_pad(strtoupper($senderData[3]), 35);               // mandotary  35 digit
			$calculation_country_indicator  = str_pad(strtoupper($senderData[5]),3);                           //Calculation country    indicator/consignor optional 3 digit
			$calculation_postcode_indicator = str_pad(substr($sender_postcode,0,9),9);                     //  9 digit optional
			$calculation_town               = str_pad(strtoupper($senderData[3]),35);                      // sender town   35 digit optional
			$customer_no                    = str_pad('',17);                     // sender product value  17 digit
			$consignment                    = str_pad('000500000',9);                      // 9 digit optional Product value of  consignment
			$currency_product               = str_pad($data['currency'],3);                       // currency product value 3 digit
			$freedomesile                   = str_pad('',13);                      // free domesile 13 digit
			
			$crecordRow   =  "\r\n".$crecord.$sequentialwaybill_posc.$consignertown.$calculation_country_indicator.$calculation_postcode_indicator.$calculation_town.$customer_no.$consignment.$currency_product.$freedomesile;
			///  D record       Consignee Address Record part 1
			
			$Drecord                         = 'D';                               // mandatory
			$sequentialwaybill_posD          = str_pad($ShipmentNo,3,0,STR_PAD_LEFT);                 //Sequential waybill posit$receiver_name_oneion mandatory
			$receiver_name_one               = str_pad(strtoupper($data['rec_name']),35);                    //    Consignee name 1  mandotry   35 digit
			$receiver_name_two               = str_pad(strtoupper($data['rec_contact']) ,35);                    //    Consignee name 2    35 digit optional
			$receiver_city_dist              = str_pad(strtoupper($data['rec_city']),35);                    //    Consignee city district      35 digit optional
			$freedomesille_derecord          = str_pad('',19);                    // 19 digit
			
			$drecordRow  = "\r\n".$Drecord.$sequentialwaybill_posD.$receiver_name_one.$receiver_name_two.$receiver_city_dist.$freedomesille_derecord;
			//  E  Consignee address record      - Part 2
			
			$Erecord                             = 'E';
			$sequentialwaybill_posE              = str_pad($ShipmentNo,3,0,STR_PAD_LEFT);                   // Sequential waybill position \\mandatory
			$receive_street                      = str_pad(strtoupper($data['rec_street']).strtoupper($data['rec_streetnr']).$data['rec_street2'],35);                  // Consignee street  and street number \\mandatory
			$receive_country_code                = str_pad($data['cncode'],3);                  // Country code  Consignee  //optional 
			$receiver_postcode                   = str_pad($data['rec_zipcode'],9);                  // Consignee postcode \\mandatory
			$receiver_town                       = str_pad(strtoupper($data['rec_city']),35);                 //Receiving town \\mandatory
			$receiver_postal_area                = str_pad('' ,3);                   // Consignee postal area  //optional
			$receiver_matchcode_lastname         = str_pad('',10);                // Consignee matchcode  \\mandatory
			$receiver_cust_no                    = str_pad('',17);                     // Consignee customer number
			$original_sender_depot_id            = str_pad('',10);                     // Original sending depot ID at receiving partner //optional
			$freedomesileE                      =  str_pad('', 2);
			
			$erecordRow   = "\r\n".$Erecord.$sequentialwaybill_posE.$receive_street.$receive_country_code.$receiver_postcode.$receiver_town.$receiver_postal_area.$receiver_matchcode_lastname.$receiver_cust_no.$original_sender_depot_id.$freedomesileE;
			
			$actual_weight=$this->actual_weight($data);
			
			///I record
			$Irecord                           =   str_pad('I',1);
			$sequentilwaybill_numberI          =   str_pad($ShipmentNo,3,0,STR_PAD_LEFT);
			$consignment_number                =   substr(str_pad($data['tracenr'], 16),0,16); // 16 digit mandotary // With waybills ex HUB (only System Alliance and SystemPlus), always contains the original consignment number of initial sending depot.	     	
			$actualconsignment_weight      =  str_pad( number_format((float)$actual_weight, 2, '.', ''), 5,0,STR_PAD_LEFT);  
			//Actual consignment gross weight in kg mandatory 5 digit
			
			// $chargebleconsignment_weight       =   str_pad(round($actual_weight), 5,0,STR_PAD_LEFT); 
			$chargebleconsignment_weight   =  str_pad( number_format((float)$actual_weight, 2, '.', ''), 5,0,STR_PAD_LEFT); 
			
			
			//    chargeble consignment weight in kg optional 5 digit
			$cubicmeter = ($data['length'] * $data['width'] * $data['height'])/1000;
			$cubic_decemeter                   =   str_pad($cubicmeter, 5,'0',STR_PAD_LEFT);                   // cubicdecemeter 5 digit
			$loadingmeter                      =   str_pad('000', 3);                    // 3 digit
			$numberofaddweight                 =   str_pad('', 2);                     //        Number of additional loading aids (FP, GP) optional
			$packgingtype_ofaddwet             =   str_pad('', 2);                      //  packeging  of additional loading aids (FP, GP) optional
			$deliveryutermforwarder            =   str_pad('6', 2);                       // mandotary   2 digit
			$deliveryutermwaybill              =   str_pad('6', 2);                        //  mandatory  2 digit
		   
		   $additional_code 				 = ($data['service_id']==1)?'01':'';
		   $rec_phone		 				 = ($data['service_id']==1)?$data['rec_phone']:'';
           $textcodeone                       =   str_pad($additional_code, 2);             // optional  2digit
           $additional_textone               =   str_pad($rec_phone, 30);                           //optional  30digit
           $textcodetwo                       =   str_pad('', 2);                         // optional  2digit
           $additional_textwo                =   str_pad('', 30);                        //  optional  30digit
           $consignmentnumber                =   str_pad('', 16);                        // optional  16digit
           $ordertype                         =   str_pad('', 1);                         ///  1 digit
           $deliverynote_data                  =   str_pad('',1);                         // 1 digit
		   			
     $IrecordRow    = "\r\n".$Irecord.$sequentilwaybill_numberI.$consignment_number.$actualconsignment_weight.$chargebleconsignment_weight.$cubic_decemeter.$loadingmeter.$numberofaddweight.$packgingtype_ofaddwet.$deliveryutermforwarder.$deliveryutermwaybill.$textcodeone.$additional_textone.$textcodetwo.$additional_textwo.$consignmentnumber.$ordertype.$deliverynote_data ;
      
	   ///   J Record  /
	      $jrecord                          = str_pad('J',1);                                          // mandotary
          $waybillnumberJ                   = str_pad($ShipmentNo,3,0,STR_PAD_LEFT);                            // mandotary
		  $additional_text = $data['length'].' X '.$data['width'].' X '.$data['height'].' CM';
          $additionaltextjone               = str_pad(strtoupper($additional_text),62);     // mandotary
          $additionaltexttwo                = str_pad(strtoupper($data['rec_street2']),62);                              // optional
          $jrecordRow                       ="\r\n".$jrecord.$waybillnumberJ.$additionaltextjone.$additionaltexttwo;
  		  $result[] = $brecordRow.$crecordRow.$drecordRow.$erecordRow;
		  $result[]=$IrecordRow.$jrecordRow;
  		  return $result;
	}

///H record edi function 
     public function H_record_EdiHamacher($data,$ShipmentNo=1)
	  {
	          // H record      
          $hrecord                         = 'H';                                            // mandotary
          $sequentilawaybill_posH          = str_pad($ShipmentNo, 3,0,STR_PAD_LEFT);              // mandotary
          $barcodequalifier                = str_pad(substr($data['barcode'], 0, 3), 3);                   // optional   	
          $barcodeno_one                   = str_pad($data['barcode'], 35);         // mandotary
          $barcodeno_two                   = str_pad('', 35);                             // depends
          $barcodeno_three                 = str_pad('', 35);                            // optional
          $freedomesileHreco               = str_pad('', 16);                               // mandotary
  		  	
  $hrecordRow   = "\r\n".$hrecord.$sequentilawaybill_posH.$barcodequalifier.$barcodeno_one.$barcodeno_two.$barcodeno_three.$freedomesileHreco;
	return $hrecordRow;
	}

	public function F_record_EdiHamacher($data,$ShipmentNo=1,$tot_shipment=1)
	{		   	
		$actual_weight=$this->actual_weight($data);
		$actual_weight=number_format((float)$actual_weight, 2, '.', '');
		
	///    F record    Consignment Position       Record
			   $Frecord                           = 'F';     //mandatory
			   $sequentialwaybill_posF            = str_pad($ShipmentNo, 3,0,STR_PAD_LEFT);         // mandatory  3 digit
			   $number_of_packeges                = str_pad($tot_shipment, 4,0,STR_PAD_LEFT); //quantity           // mandatory Number of packages 4 digit	   	
			   
			   $package_type                      = str_pad($this->GetUnitCode($data['addservice_id']),2);             // Packaging type  2 digit	   	
			   $number_of_package                 = str_pad($tot_shipment, 4,0,STR_PAD_LEFT);            //Packaging type on/in   Pallets   optional  4 digit	   	
			   $packaging_type_pallet             = str_pad('', 2);            // Packaging type on/in  Pallets 2 digit  optional
			   
			   $content_of_goods                  = str_pad('STANDARD TRADING GOO', 20);            // Content of goods  20 digit  mandotary  	
			   $code_number                       = str_pad('', 20);            // Code and number  20 digit   optional
			   
			   
			   if($tot_shipment>0)
			   {
			   $weight=$actual_weight*$tot_shipment;
			   }
			   else
			   {
			   $weight=0; 
			   }
			   
			   $actual_weight                     = str_pad($weight, 5,0,STR_PAD_LEFT);             // Actual weight    optional    5 digit	    	
			   $chargeble_weight                  = str_pad($weight, 5,0,STR_PAD_LEFT);             // Chargeable weight   5 igit  	
			   $number_of_package_two             = str_pad('', 4);
			   $packaging_type                    = str_pad('', 2);
			   $number_of_package_oninpallet      = str_pad('', 4);
			   $packaging_type_on_inpallet        = str_pad('', 2);
			   $content_ofgoods_two               = str_pad('', 20);
			   $code_and_number                   = str_pad('', 20);
			   $actualweighjt                     = str_pad('', 5);
			   $chargebleweight                   = str_pad('', 5);
				
		  $frecordRow      = "\r\n".$Frecord.$sequentialwaybill_posF.$number_of_packeges.$package_type.$number_of_package.$packaging_type_pallet.$content_of_goods.$code_number.$actual_weight.$chargeble_weight.$number_of_package_two.$packaging_type.$number_of_package_oninpallet.$packaging_type_on_inpallet.$content_ofgoods_two.$code_and_number.$actualweighjt.$chargebleweight;
				
	return $frecordRow;
	}
	/* function find edi details of Hamacher forwarder
         * ediDetail()
         * */ 
              
	 public function AddHeader()
	  {			  	
				  //$edidata = $this->ediDetail();
				  $Arecord                   = str_pad('A',1);
				  $constant                  = str_pad('000',3);
				  $waybillnumber             = str_pad($this->Forwarders['IFD_number'],18);
				  $waybilldate               = str_pad(date('dmY'), 8);                         // mondatory 8 digit
				  
				  $transporttype             = str_pad('L', 1);                                 // mondatory 2 digit
				  $sequentialvehiclenumber   = str_pad('', 2);                                // optional  2 digit
				  $sendingDepotId            = str_pad('', 10);    //'30203080EC' ;                       //mondatory   10 digit
				  $freightOperatorname       = str_pad('',13);                                 //freight operator name  13 digit
				  $postcodefreightoperator   = str_pad('',9);                          //postcode freightoperator 9 digit
				 $freightoperatorplace      = str_pad('',12);                                     // mandotry 12 digit
				  $loadingunitno_one         = str_pad('',15);                                   // mandotry  15 digit //wagon/wab no
				  $loadingunitno_two         = str_pad('',15);                                  // mandotry  15 digit  //wagon/wab no
				 $leadsealno_one            = str_pad('',10);                                   // mandotry  10 digit  loading unit1
				 $leadsealno_two            = str_pad('',10);                                    // mandotry  10 digit  loading unit2
				 $releasesixa                = str_pad('6',1);
				  
			   $Arowrecord        ="\r\n".$Arecord.$constant.$waybillnumber.$waybilldate. $transporttype.$sequentialvehiclenumber.$sendingDepotId.$freightOperatorname.$postcodefreightoperator.$freightoperatorplace.$loadingunitno_one.$loadingunitno_two.$leadsealno_one.$leadsealno_two.$releasesixa;
				
			  // $Arowrecord  ="\r\n".$Arecord.$constant.$waybillnumber.$waybilldate. $loadingunitno_one;
			   return $Arowrecord;
	  
	  }
	  
	  
	 public function shipment_summary($no_of_shipment=0,$tot_qty=0,$tot_wt=0)
	  {		  	
			  //           L  Record
			  $lrecord                      = 'L';
			  $constantL                    = '999';
			  $totalnumber_consignment      =  str_pad($no_of_shipment, 5,0,STR_PAD_LEFT);
			  $total_num_ofpackeges         =  str_pad($tot_qty, 5,0,STR_PAD_LEFT);
			  $actual_grossweight           =  str_pad($tot_wt, 5,0,STR_PAD_LEFT);
			  $totalconsigneecost_taxable   =  str_pad('000000000', 9);             // mandotary
			  $totalconsineecost_nontaxable =  str_pad('000000000', 9);              // mandotary
			  $totalconsignercod            =  str_pad('000000000', 9);             // mandotary
			  $totalcustom                  =  str_pad('000000000', 9);                 // mandotary
			  $totalEUtax                   =  str_pad('000000000', 9);                 // mandotary
			  $number_ofSB                  =  str_pad('000', 3);                       // mandotary
			  $number_ofGP                  =  str_pad('004', 3);                        // mandotary
			  $number_ofFP                  =  str_pad('000', 3);                       // mandotary
			  $number_ofCC                  =  str_pad('000', 3);                   // mandotary
			  $number_ofAD                  =  str_pad('000', 3);                    // mandotary
			  $number_ofBD                  =  str_pad('000', 3);                    // mandotary
			  $number_ofCD                  =  str_pad('000', 3);                    // mandotary
			  $number_ofFP_additional       =  str_pad('000', 3);                    // mandotary
			  $number_ofGP_additional       =  str_pad('000', 3);                   // mandotary
			  $clearing_indicator           =  str_pad('N', 1);                      // mandotary
			  $freedomesileL                =  str_pad('', 36);                     // mandotary
	   $lrecordRow    = "\r\n".$lrecord.$constantL.$totalnumber_consignment.$total_num_ofpackeges.$actual_grossweight.$totalconsigneecost_taxable.$totalconsineecost_nontaxable.$totalconsignercod.$totalcustom.$totalEUtax.$number_ofSB.$number_ofGP.$number_ofFP.$number_ofCC.$number_ofAD.$number_ofBD.$number_ofCD.$number_ofFP_additional.$number_ofGP_additional.$clearing_indicator.$freedomesileL;
				
			  // W record
			  $wrecord                          = str_pad('W', 1);
			  $swapbodes_unitone                = str_pad('', 15);    // Swap bodies units) No. 1 mandatory
			  $swapbodes_unittwo                = str_pad('', 15);  //  Swap bodies (loading units) No. 2 optional
			  $leading_sealno_one               = str_pad('', 10);   // Lead seal number swap body  (loading unit) 1 mandatory 10
			  $leading_sealnotwo                = str_pad('', 10);   //  Lead seal number swap body  (loading unit) 2 mandatory 10		  	
			  $loadingswap                      = str_pad('', 1);   // Lead seal condition swap body (loading unit) 1* mandatory
			  $leadswapcon_swaptwo              = str_pad('', 1);      // Lead seal condition swap body   (loading unit) 2* mandatory 1
			  $addswap_body                     = str_pad('', 30);        // Any additional swap body   text 1 optional 30
			  $addinal_swapbodytwo              = str_pad('', 30);         // Any additional swap body  text 2 optional 30
			  $swapbody_one                     = str_pad('', 2);          //
			  $swapbody_two                     = str_pad('', 2);       // Condition code** optional 2
			  $forwarding_code                  = str_pad('VM', 2);          // Forwarding agent code*** mandatory 2
			  $freedomesileW                    = str_pad('', 8);             //  Free domicile 8
			  $relesesix                        = str_pad('6', 1);            // mandatory  1 digit
			  
	  $wrecordRow   = "\r\n".$wrecord.$swapbodes_unitone.$swapbodes_unittwo.$leading_sealnotwo.$leading_sealnotwo.$loadingswap.$leadswapcon_swaptwo.$addswap_body.$addinal_swapbodytwo.$swapbody_one.$swapbody_two.$forwarding_code.$freedomesileW.$relesesix;
		 
		$shipment_summary= $lrecordRow.$wrecordRow;
		return $shipment_summary;
		
	  } 
	 
	 public function GetUnitCode($service_id="")
	 {		   			
		if($service_id!="")
		{	
		//$addservice_id = ($addservice_id>0)?$addservice_id:110;
			  $select = $this->masterdb->select()
						  ->from(array('FS'=>FREIGHT_SUBSERVICE),array('unit_code'))
						  ->where("FS.addservice_id='".$service_id."'");
			 $result = $this->masterdb->fetchRow($select);		
				//print_r($select->__toString());die;
				switch($result['unit_code']){
				   case 'CO':
					   return 'KT';
					break;
					case 'MP':
					   return 'HP';
					break;
					case 'PL':
					   return 'EP';
					break;
					default:
					   return 'EP';
					break;
				}
	   }
	  else
	  {
			return '';
	  }
	}
	
	public function actual_weight($data)
	{
		$actual_weight=0;
		$volumetrick_weight=($data['length']*$data['width']*$data['height']*0.15/1000);
		if($data['weight']>$volumetrick_weight)
		{
			$actual_weight=$data['weight'];
		}
		else
		{
			$actual_weight=$volumetrick_weight;
		}
	
		return $actual_weight;
	}
}

