<?php

 

class Checkin_Model_UPSEdi extends Zend_Custom

{

	public $ForwarderRecord = array();

   	public $Forwarders	= array();

	

	public function generateEDI($data){

	    $this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);

	   try{ 

	     $select = $this->_db->select()

                                        ->from(array('BT'=>SHIPMENT_BARCODE),array('COUNT(1) AS QUANT','SUM(BT.weight) as Totalweight',SHIPMENT_ID))

                                        ->joinleft(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array('user_id'))

                                        ->where(BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")")

                                        ->group("BT.shipment_id"); //print_r($select->__toString());die;

		  $result = $this->getAdapter()->fetchAll($select);//print_r($result);die;

		  

			$segmentcount = -1;

			$TotalEdiData = '';

			$filename = date('dmy')."_".$this->Forwarders['IFD_number'];

			for($i=0;$i<count($result);$i++){

				$pakegedatas = $this->recordByParcel($data,$result[$i][SHIPMENT_ID]);

				$this->RecordData = $pakegedatas[0];

		  		$this->ForwarderRecord = $this->ForwarderDetail();

				$TotalEdiData .=  $this->CreateEdiUPS($pakegedatas[0],$result[$i]); 

			

				$segmentcount = -1;

				foreach($pakegedatas as $packege){

					$TotalEdiData .=  $this->pakageInformation($packege);

					$segmentcount++;

				}

				$TotalEdiData .=  $this->UPSEdiFooter($packege,$segmentcount);

			}

			$headerData = $this->UPSEdiHeader($result);

			$TotalEdiData = $headerData.$TotalEdiData;

		   

		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); }

		 

		
		 
		 return array('EdiData'=>$TotalEdiData,'EdiFilename'=>$filename);

	}

	

	public function recordByParcel($data,$shipment_id){

	   $select = $this->_db->select()

								->from(array('BT'=>SHIPMENT_BARCODE),array('*'))

								->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE))

								->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,CITY,ZIPCODE,PHONE,EMAIL,

								 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id','rec_state',ADDRESS,STREET2))

								->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,'first_name','last_name','company_name','address1','city','postalcode','phoneno'))

								->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))

								->joininner(array('CCT' =>COUNTRIES),'CCT.'.COUNTRY_ID.'=AT.'.COUNTRY_ID.'',array('cncode as shipperCNCode','country_name as shipperCountry'))

								->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))

								->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).") AND BT.shipment_id='".$shipment_id."'"); //print_r($select->__toString());die;

			$results = $this->getAdapter()->fetchAll($select);

		return $results;	

	}

	

	/**

	 * Generate DPD EDI Header

	 * Function : DPDEdiHeader()

	 * Function Generate DPD EDI Header

	 **/

	public function UPSEdiHeader($result) {

	$ups_booknum = $this->ForwarderRecord['tracking_start'];

	$ups_pagenum = $this->ForwarderRecord['last_tracking'];

	

	$BookNumValue = $this->ForwarderRecord['tracking_start'];

	$PageNumValue = $this->ForwarderRecord['last_tracking'] + 1;

	

	if($PageNumValue > 999) {

		$BookNum = ($this->ForwarderRecord['tracking_start'] == '5936143') ? '5936144' : (($this->ForwarderRecord['tracking_start'] == '5936144') ? '5936143' : $this->ForwarderRecord['tracking_start']);

		$PageNum = '001';

	}

	else {

		$BookNum = $BookNumValue;

		$PageNum = $PageNumValue;

	}

	$trail = '';

	if(strlen($PageNum) < 3) {

		for($b=0;$b<(3 - strlen($PageNum));$b++) {

			$trail .= 0;

		}

		$PageNum = $trail.$PageNum;

	}

	

	// Update Book Number and Page Number

	$this->_db->update(FORWARDERS,array('tracking_start'=>$BookNum,'last_tracking'=>$PageNum),"forwarder_id IN(17,18,19)");

	

	

	// Header Segment

	$VersionNumber 			= "0200";

	$DataSource 			= "94";// Before 07 change(03-08-2013)

	$MailboxID 				= commonfunction::paddingRight(" ",24);

	$ClientSoftwareVersion  = commonfunction::paddingRight("V4R2",10);

	$PickupDate 			= date('Ymd');

	

	$PLDSequenceNumber 	  	= $BookNum.$PageNum;

	$leadzero = '';

	if(strlen($PLDSequenceNumber) < 15) {

		for($p=0;$p<(15 - strlen($PLDSequenceNumber));$p++) {

			$leadzero .= 0;

		}

		$PLDSequenceNumber = $leadzero.$PLDSequenceNumber;

	}

	

	$IncrementalPLDCode   	= "00";

	$SoftwareVendorCode   	= "000";

	$NumShipperSegsInFile 	= "000000001";

	

	// Total Length of Header Segment should be 77

	$HeaderInfo = $VersionNumber.$DataSource.$MailboxID.$ClientSoftwareVersion.$PickupDate.$PLDSequenceNumber.$IncrementalPLDCode.$SoftwareVendorCode.$NumShipperSegsInFile;

	

	/**

	 * SHIPPER/BOOK/PAGE INFORMATION SEGMENT - *AA

	 **/

	$SegmentIdentifierAA 	= "*AA";

	

	// Find Forwarder Account Number

	//$upsConfigRow = $this->getUpsConfiguration($result);

	$account_code1 = $this->ForwarderRecord['contract_number'];

	$account_code2 = $this->ForwarderRecord['sub_contract_number'];

	$ShipperNumber = $account_code1.$account_code2;

	

	$SenderShipperNumber = commonfunction::paddingRight($ShipperNumber,10);

	$ShipperCountry 	= "NL";

	$ShipperEIN 		= commonfunction::paddingRight(" ",15);

	$CalculatedRatesInd = commonfunction::paddingRight(" ",1);

	

	// A8 : NumShipmentsInPage

	$totalParcel = count($result);

	$NumShipmentsInPage = commonfunction::paddingleft($totalParcel,6,'0');

	

	// Total Length of SHIPPER/BOOK/PAGE INFORMATION SEGMENT should be 47

	$ShipperSegment = $SegmentIdentifierAA.$SenderShipperNumber.$ShipperCountry.$ShipperEIN.$CalculatedRatesInd.$BookNum.$PageNum.$NumShipmentsInPage;

	

	 return $HeaderInfo.$ShipperSegment;

	}

	/**

	 * Generate EDI For UPS

	 * Function : CreateEdiUPS()

	 * Function Generate  EDI for UPS forwarder

	 **/

	public function CreateEdiUPS($res_br,$totalRec){

	 	$AllSegment = '';

		$parcelBarcode	 = strtoupper($res_br["barcode"]);

		$res_br[WEIGHT] = ($res_br[WEIGHT]<1)?1:$res_br[WEIGHT]; // For round to 1kg for less then 1 kg weight

		$totalRec['Totalweight'] = ($totalRec['Totalweight']<1)?1:$totalRec['Totalweight'];



		$roundedparcelWeight	 = round(commonfunction::stringReplace(',','.',trim($res_br[WEIGHT])));

		$roundedtotalWeight	 = round(commonfunction::stringReplace(',','.',trim($totalRec['Totalweight'])));

		$parcelWeight	 = $roundedparcelWeight * 10;

		$totalWeight	 = $roundedtotalWeight * 10;

		$rec_name		 = trim(commonfunction::utf8Decode($res_br[RECEIVER]));

		$rec_contact	 = trim(commonfunction::utf8Decode($res_br[CONTACT]));

		$rec_street		 = trim(commonfunction::utf8Decode($res_br[STREET]));

		$rec_nr		     = trim(commonfunction::utf8Decode($res_br[STREETNR]));

		$rec_add	     = trim(commonfunction::utf8Decode($res_br[ADDRESS]));

		$address1		 = $rec_street.$rec_nr.$rec_add;

		$address2		 = trim(commonfunction::utf8Decode($res_br[STREET2]));

		$rec_plz		 = trim($res_br[ZIPCODE]);

		if($res_br[COUNTRY_ID]==7){

		   $rec_plz = preg_replace("/[^0-9]+/", "", $rec_plz);

		}



		$rec_plz		 = commonfunction::stringReplace(array(' ','+','(',')','-','/'),array('','','','','',''),$rec_plz);

		$rec_city		 = trim(commonfunction::utf8Decode(str_replace("'",'',$res_br[CITY])));

		$rec_country	 = trim($res_br["cncode"]);

        $rec_statecode   =  ($res_br[COUNTRY_ID]==7 || $res_br[COUNTRY_ID]==63)?$res_br["rec_state"]:' ';  // state code for us and canada

		$rec_tel   		 = (!empty($res_br[PHONE])) ? trim(commonfunction::stringReplace(array(' ','+','(',')','-','/'),array('','','','','',''),$res_br[PHONE])) : '';

		$rec_tel	 	 = commonfunction::stringReplace(' ','',$rec_tel);

		$goods_type		 = (!empty($res_br["goods_id"])) ? trim($res_br["goods_id"]) : "parts";

		

	    

		if($this->ForwarderRecord['contract_number']=='AX3'){

			  $senderaddress = array('NIKE EUROPEAN OP NETHERLANDS B.V.','NIKE EUROPEAN OP NETHERLANDS B.V.','COLOSSEUM 1','HILVERSUM','1213 

','','NETHERLANDS');

		}

		

		$customerCompany = trim(commonfunction::utf8Decode($res_br["company_name"]));

		$customerAddress = trim(commonfunction::utf8Decode($res_br["address1"]));

		$customerCity    = trim(commonfunction::utf8Decode($res_br["city"]));

		$customerZipcode = trim(commonfunction::utf8Decode($res_br["postalcode"]));

		$customerZipcode = commonfunction::stringReplace(' ','',$customerZipcode);

		$customerCountry = trim(commonfunction::utf8Decode($res_br["shipperCNCode"]));

		$customerCountry = 'NL';

		$customerPhone   = (!empty($res_br["phoneno"])) ? trim(commonfunction::stringReplace(array(' ','+','(',')','-','/'),array('','','','','',''),$res_br["phoneno"])) : '0748800700';

		$customerPhone	 = commonfunction::stringReplace(' ','',$customerPhone);

		

		//$forwardname = $this->forwarderName($res_br[FORWARDER_ID]);

		

		/**

		 * SHIPMENT INFORMATION SEGMENT - *BA

		 **/

		$SegmentIdentifierBA 	= "*BA";

		$ShipmentNumber 		= (strlen($parcelBarcode)<35) ? commonfunction::paddingRight($parcelBarcode,35) : substr($parcelBarcode,0,35);

		$PackageCount 			= commonfunction::paddingleft($totalRec['QUANT'],5,'0');

		$ShipmentActualWeight 	= "+".commonfunction::paddingleft($totalWeight,16,'0');

		$AveragePkgWeightInd 	= commonfunction::paddingRight(" ",1);

		$ShipmentDimWeight 		= "+0000000000000000";

		$UOMWeight 				= "KGS";

		$UPSServiceType 		= trim($this->ForwarderRecord['service_type']);

		$ShipmentChgType 		= "PRE";

		$PaymentMediaTypeCode 	= "10";

		$SenderName 			= (strlen($res_br['company_name'])<35) ? commonfunction::paddingRight($res_br['company_name'],35) : substr($res_br['company_name'],0,35);

		$DocInd 				= "3";

		$UOMDim 				= "CM";

		$CurrencyCode 			= "EUR";

		$NumPackagesInShipment  = commonfunction::paddingleft($totalRec['QUANT'],6,'0');

		

		// Total Length of SHIPMENT INFORMATION SEGMENT should be 135

		$AllSegment .= $SegmentIdentifierBA.$ShipmentNumber.$PackageCount.$ShipmentActualWeight.$AveragePkgWeightInd.$ShipmentDimWeight.$UOMWeight.$UPSServiceType.$ShipmentChgType.$PaymentMediaTypeCode.$SenderName.$DocInd.$UOMDim.$CurrencyCode.$NumPackagesInShipment;

		 

		 

		/**

		 * ADDRESS INFORMATION SEGMENT - *CA

		 **/

		//Pickup From Address

		$SegmentIdentifierCA = "*CA";

		$AddressQualifier = "06";

		$AttnName 		  = commonfunction::paddingRight(" ",35);

		$CompanyName 	  = (strlen($customerCompany)<35) ? commonfunction::paddingRight($customerCompany,35) : substr($customerCompany,0,35);

		$Address1 		  = (strlen($customerAddress)<35) ? commonfunction::paddingRight($customerAddress,35) : substr($customerAddress,0,35);

		$Address2 		  = commonfunction::paddingRight(" ",35);

		$Address3 		  = commonfunction::paddingRight(" ",35);

		$City 			  = (strlen($customerCity)<30) ? commonfunction::paddingRight($customerCity,30) : substr($customerCity,0,30);

		$StateProv		  = commonfunction::paddingRight(" ",5);

		$PostalCode 	  = (strlen($customerZipcode)<9) ? commonfunction::paddingRight($customerZipcode,9) 	: substr($customerZipcode,0,9);

		$Country		  = (strlen($customerCountry)<2) ? commonfunction::paddingRight($customerCountry,2)  : substr($customerCountry,0,2);

		$PhoneNumber	  = (strlen($customerPhone)<15)  ? commonfunction::paddingRight($customerPhone,15) 	: substr($customerPhone,0,15);

		$FaxInd 		  = commonfunction::paddingRight(" ",1);

		$FaxNumber 		  = commonfunction::paddingRight(" ",15);

		$UPSAccountNumber = commonfunction::paddingRight(" ",10);

		$TaxID 			  = commonfunction::paddingRight(" ",15);

		

		// Total Length of ADDRESS INFORMATION SEGMENT for Shipper information should be 282

		$AllSegment .= $SegmentIdentifierCA.$AddressQualifier.$AttnName.$CompanyName.$Address1.$Address2.$Address3.$City.$StateProv.$PostalCode.$Country.$PhoneNumber.$FaxInd.$FaxNumber.$UPSAccountNumber.$TaxID;

		

		// Deliver to Address

		$SegmentIdentifierCA18 = "*CA";

		$AddressQualifier18 = "18";

		$AttnName18 		= (strlen($rec_contact)<35) ? commonfunction::paddingRight($rec_contact,35) : substr($rec_contact,0,35);

		$CompanyName18 	    = (strlen($rec_name)<35) ? commonfunction::paddingRight($rec_name,35) : substr($rec_name,0,35);

		$Address118 		= (strlen($address1)<35) ? commonfunction::paddingRight($address1,35) : substr($address1,0,35);

		$Address218 		= (strlen($address2)<35) ? commonfunction::paddingRight($address2,35) : substr($address2,0,35);

		$Address318 		= commonfunction::paddingRight(" ",35);

		$City18 			= (strlen($rec_city)<30) ? commonfunction::paddingRight($rec_city,30) : substr($rec_city,0,30);

		$StateProv18		= commonfunction::paddingRight($rec_statecode,5);

		$PostalCode18 	    = (strlen($rec_plz)<9) 	 ? commonfunction::paddingRight($rec_plz,9) 		: substr($rec_plz,0,9);

		$Country18		    = (strlen($rec_country)<2) ? commonfunction::paddingRight($rec_country,2)  : substr($rec_country,0,2);

		$PhoneNumber18	    = (strlen($rec_tel)<15) 	 ? commonfunction::paddingRight($rec_tel,15) 	: substr($rec_tel,0,15);

		$FaxInd18 		    = commonfunction::paddingRight(" ",1);

		$FaxNumber18 		= commonfunction::paddingRight(" ",15);

		$UPSAccountNumber18 = commonfunction::paddingRight(" ",10);

		$TaxID18		    = commonfunction::paddingRight(" ",15);

		

		// Total Length of ADDRESS INFORMATION SEGMENT for Receiver information should be 282

		$AllSegment .= $SegmentIdentifierCA18.$AddressQualifier18.$AttnName18.$CompanyName18.$Address118.$Address218.$Address318.$City18.$StateProv18.$PostalCode18.$Country18.$PhoneNumber18.$FaxInd18.$FaxNumber18.$UPSAccountNumber18.$TaxID18;

		 

		/**

		 * STANDARD INTERNATIONAL SHIPMENT SEGMENT - *IA

		 * This segment is required for all international shipments including US to PR.

		 * For domestic shipments outside US, PR, CA, and VI, this segment is optional.

		 **/

		$SegmentIdentifierIA     = "*IA";

		

		// Find Waybill/BrokerageIDNo (shipment ID number) from shipment_barcode table

		$shipper_id = $this->ForwarderRecord['contract_number']." ".$this->ForwarderRecord['sub_contract_number'];

		$shp_no  = $this->calculateshipment($res_br["barcode"],$shipper_id);

		$shipmentIDnumber = trim(commonfunction::stringReplace(' ','',$shp_no)); //echo "<pre>";print_r($shipmentIDnumber);echo "</pre>";die;

		

		$WaybillBrokerageIDNo	 = commonfunction::paddingRight($shipmentIDnumber,11);	

		$WaybillPrintInd 		 = 1;

		$DescriptionOfGoods 	 = commonfunction::paddingRight($goods_type,50);

		$ShipmentGCCN 			 = commonfunction::paddingRight(" ",11);

		$BrokerCode 			 = commonfunction::paddingRight(" ",3);

		$ShipmentCommodityOrigin = commonfunction::paddingRight(" ",2);

		$CertOfOriginCode 		 = commonfunction::paddingRight(" ",5," ");

		

		// Total Length of STANDARD INTERNATIONAL SHIPMENT SEGMENT should be 86

		$AllSegment .= $SegmentIdentifierIA.$WaybillBrokerageIDNo.$WaybillPrintInd.$DescriptionOfGoods.$ShipmentGCCN.$BrokerCode.$ShipmentCommodityOrigin.$CertOfOriginCode;	

		

		 

		 return $AllSegment;									

	}

       

    public function pakageInformation($res_br){

        $AllSegment = '';

		$parcelBarcode	 = strtoupper($res_br["barcode"]);

		$res_br[WEIGHT] = ($res_br[WEIGHT]<1)?1:$res_br[WEIGHT];

		$roundedWeight	 = round(commonfunction::stringReplace(',','.',trim($res_br[WEIGHT])));

		$parcelWeight	 = $roundedWeight * 10;

		$rec_name		 = trim(commonfunction::utf8Decode($res_br[RECEIVER]));

		$rec_contact	 = trim(commonfunction::utf8Decode($res_br[CONTACT]));

		$rec_street		 = trim(commonfunction::utf8Decode($res_br[STREET]));

		$rec_nr		     = trim(commonfunction::utf8Decode($res_br[STREETNR]));

		$rec_add	     = trim(commonfunction::utf8Decode($res_br[ADDRESS]));

		$address1		 = $rec_street.$rec_nr.$rec_add;

		$address2		 = trim(commonfunction::utf8Decode($res_br[STREET2]));

		$rec_plz		 = trim($res_br[ZIPCODE]);

		$rec_plz		 = commonfunction::stringReplace(' ','',$rec_plz);

		$rec_city		 = trim(commonfunction::utf8Decode($res_br[CITY]));

		$rec_country	 = trim($res_br["cncode"]);

		$rec_tel		 = commonfunction::stringReplace(array(' ','+','(',')','-'),array('','','','',''),$res_br[PHONE]);

		$goods_type		 = (!empty($res_br["goods_id"])) ? trim($res_br["goods_id"]) : "parts";

           /**

		 * PACKAGE INFORMATION SEGMENT - *PA

		 **/

		$SegmentIdentifierPA 	= "*PA";

		$PackageTrackingNumber 	= (strlen($parcelBarcode)<35) ? commonfunction::paddingRight($parcelBarcode,35," ") : substr($parcelBarcode,0,35);

		$PackageTrackingNumber  = strtoupper($PackageTrackingNumber);

		$PackagingType 			= "02";



		if(strlen($parcelWeight) < 7) {

			$before = 0;

			$wegdiff = 7-strlen($parcelWeight);

			for($k=1;$k<$wegdiff;$k++) {

				$before .= 0;

			}

			$PackageActualWeight  = "+".$before.$parcelWeight;

		}

		else {

			$PackageActualWeight  = "+".$parcelWeight;

		}



		$DeliverToAttnName 		= commonfunction::paddingRight(" ",35," ");

		$DeliverToPhoneNumber 	= commonfunction::paddingRight(" ",15);

		$MerchandiseDescription = commonfunction::paddingRight(" ",35);

		$VoidInd 				= commonfunction::paddingRight(" ",1);

		$PkgPublishedDimWt 		= "+0000000";

		$Length 				= "+00000000";

		$Width 					= "+00000000";

		$Height 				= "+00000000";



		// Total Length of PACKAGE INFORMATION SEGMENT should be 169

		$AllSegment .= $SegmentIdentifierPA.$PackageTrackingNumber.$PackagingType.$PackageActualWeight.$DeliverToAttnName.$DeliverToPhoneNumber.$MerchandiseDescription.$VoidInd.$PkgPublishedDimWt.$Length.$Width.$Height;



		 return $AllSegment;

        }

       

    public function UPSEdiFooter($res_br,$segmentcount){

		/**

		 * SHIPMENT FOOTER SEGMENT - *SA

		 **/

		$SegmentIdentifierSA     = "*SA";

		//$TotalSegmentsInShipment = "000006";

		if($segmentcount>0){

		  $TotalSegmentsInShipment = commonfunction::paddingleft((6+$segmentcount), 6, '0');

		}else{

		  $TotalSegmentsInShipment = "000006";

		}



		 // Total Length of SHIPMENT FOOTER SEGMENT should be 9

		 $AllSegment = '';

		 $AllSegment .= $SegmentIdentifierSA.$TotalSegmentsInShipment;

		 return $AllSegment;

      }

	  

	  public function calculateshipment($trackno,$shippmentid) {

		$conversion = array(0  => 3,1  => 4,2  => 7,3  => 8,4  => 9,5  => 'B',6  => 'C',7  => 'D',8  => 'F',9  => 'G',10 => 'H',11 => 'J',12 => 'K',13 => 'L',14 => 'M',

							15 => 'N',16 => 'P',17 => 'Q',18 => 'R',19 => 'S',20 => 'T',21 => 'V',22 => 'W',23 => 'X',24 => 'Y',25 => 'Z');

		

		$trackno = commonfunction::stringReplace(' ', '', $trackno);

		$trackno = commonfunction::sub_string($trackno, -8);

		$trackno = commonfunction::sub_string($trackno, 0, -1);

		$pos1 =  $trackno/pow(26,4);

		$pos1 = floor($pos1);

		$pos2 = ($trackno - ($pos1*pow(26,4)))/pow(26,3);

		$pos2 = floor($pos2);

		$pos3 = ($trackno -($pos1*pow(26,4))-($pos2*pow(26,3)))/pow(26,2);

		$pos3 = floor($pos3);

		$pos4 = ($trackno - ($pos1*pow(26,4))- ($pos2*pow(26,3))-($pos3*pow(26,2)) )/26;

		$pos4 = floor($pos4);

		$pos5 = ($trackno-($pos1*pow(26,4))-($pos2*pow(26,3))-($pos3*pow(26,2))-($pos4*26));

		$pos5 = floor($pos5);

		$shipperno = $shippmentid.' '.$conversion[$pos1].$conversion[$pos2].$conversion[$pos3].$conversion[$pos4].$conversion[$pos5];	

		$shipperno = commonfunction::stringReplace(' ', '', $shipperno);	

		$shipperno1 = commonfunction::sub_string($shipperno, 0, 4);	

		$shipperno2 = commonfunction::sub_string($shipperno, 4, 4);	

		$shipperno3 = commonfunction::sub_string($shipperno, 8);

		return    $shipperno1.' '.$shipperno2.' '.$shipperno3;

	}



}



