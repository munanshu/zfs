<?php
class Checkin_Model_DPDEdi extends Zend_Custom
{
   public $ForwarderRecord = array();
   public $Forwarders	= array();
	public $_DateCreated = NULL;
	public $_CreatedTime = NULL;
   public function generateEDI($data){
       $this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);
	   $this->_DateCreated = date('Ymd');
	   $this->_CreatedTime = date('His');
	   $TotalEdiData = '';
	  try{
	   $select = $this->_db->select()
							->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
							->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE))
							->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,CITY,ZIPCODE,PHONE,EMAIL,
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id','cod_price'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		$TotalEdiData .= $this->DPDEdiHeader();
		foreach($results as $result){
		    $this->RecordData = $result;
			$this->ForwarderRecord = $this->ForwarderDetail();
			$TotalEdiData .=  $this->DPDEdiBody($result);
		}
		
	  	$TotalEdiData .="#END;".$this->Forwarders['IFD_number'].";\r\n";
	    $filename = "MPSEXPDATA_".$this->Forwarders['delis_user_id']."_CUST_".$this->Forwarders['depot_number']."_D".$this->_DateCreated."T".$this->_CreatedTime;
		return array('EdiData'=>$TotalEdiData,'EdiFilename'=>$filename);
   }
   	/**
	 * Generate DPD EDI Header
	 * Function : DPDEdiHeader()
	 * Function Generate DPD EDI Header
	 **/
	public function DPDEdiHeader() {
		//Line Number 1
		$H1               = "#FILE;";
		$DELISUserID      = $this->Forwarders['delis_user_id'].";";
		$DespatchDepot    = $this->Forwarders['depot_number'].";";
		$FileCreationDate = $this->_DateCreated.";";
		$FileCreationTime = $this->_CreatedTime.";";
		$consecutive_no   = $this->Forwarders['IFD_number'].";";
		
		$TopRecords = $H1.$DELISUserID.$DespatchDepot.$FileCreationDate.$FileCreationTime.$consecutive_no."\r\n";
		
		// Line Number 2
		$Line1 = "#DEF;MPSEXP:HEADER;MPSID;MPSCOMP;MPSCOMPLBL;MPSCREF1;MPSCREF2;MPSCREF3;MPSCREF4;MPSCOUNT;MPSVOLUME;MPSWEIGHT;SDEPOT;SCUSTID;SCUSTSUBID;DELISUSR;SNAME1;SNAME2;SSTREET;SHOUSENO;SCOUNTRYN;SPOSTAL;SCITY;SCONTACT;SPHONE;SFAX;SEMAIL;SCOMMENT;SILN;CDATE;CTIME;CUSER;HARDWARE;RDEPOT;ESORT;RCUSTID;RNAME1;RNAME2;RSTREET;RHOUSENO;RCOUNTRYN;RSTATE;RPOSTAL;RCITY;RCONTACT;RPHONE;RFAX;REMAIL;RCOMMENT;RILN;MPSSERVICE;MPSSDATE;MPSSTIME;LATEPICKUP;UMVER;UMVERREF;PODMAN;\r\n";
		$Line2 = "#DEF;MPSEXP:PARCEL;MPSID;PARCELNO;CREF1;CREF2;CREF3;CREF4;DELISUSR;SERVICE;VOLUME;WEIGHT;HINSURE;HINSAMOUNT;HINSCURRENCY;HINSCONTENT;\r\n";
	    $Line3 = "#DEF;MPSEXP:COD;MPSID;PARCELNO;NAMOUNT;NCURR;NINKASSO;NPURPOSE;SBKCODE;SBKNAME;SACCOUNT;SACCNAME;IBAN;BIC;\r\n";
	    $Line4 = "#DEF;MPSEXP:PICKUP;PTYPE;MPSID;PNAME1;PNAME2;PSTREET;PHOUSENO;PCOUNTRYN;PPOSTAL;PCITY;PCONTACT;PPHONE;PFAX;PEMAIL;PILN;PDATE;PTOUR;PQUANTITY;PDAY;PFROMTIME1;PTOTIME1;PFROMTIME2;PTOTIME2;\r\n";
	    $Line5 = "#DEF;MPSEXP:INVOICE;MPSID;INAME1;INAME2;ISTREET;IHOUSENO;ICOUNTRYN;IPOSTAL;ICITY;ICONTACT;IPHONE;IFAX;IEMAIL;IILN;\r\n";
	    $Line6 = "#DEF;MPSEXP:PERS;MPSID;PERSDELIVERY;PERSFLOOR;PERSBUILDING;PERSDEPARTMENT;PERSNAME;PERSPHONE;PERSID;ODEPOT;ONAME1;ONAME2;OSTREET;OHOUSENO;OCOUNTRYN;OSTATE;OPOSTAL;OCITY;OPHONE;OEMAIL;OILN;\r\n";
	    $Line7 = "#DEF;MPSEXP:MSG;MPSID;MSGTYPE1;MSGVALUE1;MSGRULE1;MSGLANG1;MSGTYPE2;MSGVALUE2;MSGRULE2;MSGLANG2;MSGTYPE3;MSGVALUE3;MSGRULE3;MSGLANG3;MSGTYPE4;MSGVALUE4;MSGRULE4;MSGLANG4;MSGTYPE5;MSGVALUE5;MSGRULE5;MSGLANG5;\r\n";
	    $Line8 = "#DEF;MPSEXP:SWAP;MPSID;PARCELNO;PARCELNOBACK;SERVICEBACK;\r\n";
	    $Line9 = "#DEF;MPSEXP:INTER;MPSID;PARCELNO;PARCELTYPE;CAMOUNT;CURRENCY;CTERMS;CCONTENT;CTARIF;\r\n";
	    $Line10 = "#DEF;MPSEXP:DELIVERY;MPSID;PODINFO1;PODINFO2;PODINFO3;PODINFO4;PODINFO5;CUSTOMERINFO1;CUSTOMERINFO2;CUSTOMERINFO3;CUSTOMERINFO4;CUSTOMERINFO5;DELIVERYDAY;DELIVERYDATE_FROM;DELIVERYDATE_TO;TIMEFRAME_FROM;TIMEFRAME_TO;\r\n";
	   
	   $TopRecords .= $Line1.$Line2.$Line3.$Line4.$Line5.$Line6.$Line7.$Line8.$Line9.$Line10; //print_r($TopRecords);die;
	   
	   return $TopRecords;
	}
	
	/**
	*Generate EDI For DPD
	*Function : DPDEdiBody()
	*Function Generate Generate EDI for DPD forwarder
	**/
	public function DPDEdiBody($data){
		$PARCEL_DATA = '';
		$HardwareFlag = array("Adhoc"=>"A","B2C system"=>"B","DELISexpress"=>"C","depot software manual entry"=>"D","Interface system partner"=>"I","directly from customer"=>"K","express online system"=>"O","DELISprint"=>"P","myDELISprint"=>"M","scan software manual entry"=>"S");
		
		$ModifiedInstruction   = array("none"=>0,"zero-costmodified_instructions"=>1,"modified_instructions_with_charge"=>2);
		$CollectionRequestFlag = array("Pickup_order"=>1,"Pickup_service"=>2);
		
		$DateCreate 	  		= $this->_DateCreated;
		$CompleteDeliveryStatus = ($data['cod_price'] > 0 && ($data['addservice_id']==7 || $data['addservice_id']==146)) ? 2 : 1;
		$pickup_status 			= ($data['pickup_status'] = 1) ? $data['pickup_status'] : 1;
		$WeightParcle 			= round((($data[WEIGHT]*1000)/10));
	    $ServiceCode 			= ($data[WEIGHT] <= 3) ?$this->ForwarderRecord['service_code_kp'] : $this->ForwarderRecord['service_code_np'];
		
		$customerNum = (!empty($this->ForwarderRecord['manifest_customer_number']) && $this->ForwarderRecord['manifest_customer_number'] > 0) ? trim($this->ForwarderRecord['manifest_customer_number']) : '49497';
		
		// DPD Sender information
		$selectSender = $this->masterdb->select()
								->from(array('DP'=>DPD_DEPOTS),array('DP.*'))
								//->joininner(array('CT' =>COUNTRIES),'CT.cncode=DP.ISO_Alpha2CountryCode',array('CT.iso_code'))
								->where("DP.GeoPostDepotNumber=".$this->ForwarderRecord['depot_number']); //print_r($selectSender->__toString());die;
	    $SenderInfo = $this->masterdb->fetchRow($selectSender);//print_r($SenderInfo);die;
		
		// Consignment Entry Date & Time
		$EntryDate        = commonfunction::explode_string($data[CREATE_DATE]," ");
		$DateEntryConsign = commonfunction::stringReplace("-","",$EntryDate[0]);
		$TimeEntryConsign = commonfunction::stringReplace(":","",$EntryDate[1]);
	
		$H2               	  = "#DEF;";
		$ConsignmentHeader	  = "HEADER;";
		$ConsignmentNumber	  = "MPS".$data[REROUTE_BARCODE].$DateCreate.";";
		$CompleteDelivery 	  = $CompleteDeliveryStatus.";";
		$DeliveryLabel    	  = $pickup_status.";";
		$CustomerReferenceNo1 = commonfunction::utf8Decode($data[REFERENCE]).";";
		$CustomerReferenceNo2 = ";";
		$CustomerReferenceNo3 = ";";
		$CustomerReferenceNo4 = ";"; 
		$NumberOfParcles      = "1;"; //$quantity.";";
		$Volumn               = ";";
		$ShipmentWeight       = $WeightParcle.";";
		$SendingDepot         = $this->ForwarderRecord['depot_number'].";";
		$CustomerNumber1      = $customerNum.";"; //"49497;";
		$CustomerNumberSubID  = ";";
		$DELISUserID 		  = $this->ForwarderRecord['delis_user_id'].";";
		$SenderName1          = $SenderInfo["Name1"].";";
		$SenderName2          = $SenderInfo["Name2"].";";
		$SenderStreet         = $SenderInfo["Address1"]." ".$SenderInfo["Address2"].";";
		$SenderHouseNumber    = ";";
		$SenderCountry        = $SenderInfo["ISO_Alpha2CountryCode"].";";
		$SenderPostcode       = $SenderInfo["PostCode"].";";
		$SenderCity           = $SenderInfo["CityName"].";";
		$SenderContactPerson  = ";";
		$SenderTelephone      = $SenderInfo["Phone"].";";
		$SenderFaxNumber      = $SenderInfo["Fax"].";";
		$SenderEmail          = $SenderInfo["Mail"].";";
		$SenderComment        = ";";
		$SenderILNnumber      = ";";
		$ConsignmentEntryDate = $DateEntryConsign.";";
		$ConsignmentEntryTime = $TimeEntryConsign.";";
		$ConsignmentEntryUser = $data[ADMIN_ID].";";
		$HardwareFlag         = $HardwareFlag["directly from customer"].";";
		$RecipientDepot       = ";";//$this->ForwarderRecord['receipent_depot'].";";
		$ESort                = ";";
		
		$PARCEL_DATA .= $ConsignmentHeader.$ConsignmentNumber.$CompleteDelivery.$DeliveryLabel.$CustomerReferenceNo1.$CustomerReferenceNo2.$CustomerReferenceNo3.$CustomerReferenceNo4.$NumberOfParcles.$Volumn.$ShipmentWeight.$SendingDepot.$CustomerNumber1.$CustomerNumberSubID.$DELISUserID.$SenderName1.$SenderName2.$SenderStreet.$SenderHouseNumber.$SenderCountry.$SenderPostcode.$SenderCity.$SenderContactPerson.$SenderTelephone.$SenderFaxNumber.$SenderEmail.$SenderComment.$SenderILNnumber.$ConsignmentEntryDate.$ConsignmentEntryTime.$ConsignmentEntryUser.$HardwareFlag.$RecipientDepot.$ESort;
		
		// Receiver Data
		$CustomerNumbeOfTheConsignee = ";";
		$RecipientName1              = commonfunction::utf8Decode($data[RECEIVER]).";";
		$RecipientName2              = commonfunction::utf8Decode($data[CONTACT]).";";
		$RecipientStreet             = commonfunction::utf8Decode($data[STREET]).";";
		$RecipientHouseNo            = commonfunction::utf8Decode($data[STREETNR]).";";
		$RecipientCountry            = $data['iso_code'].";";
		$RecipientState              = ";";
		$RecipientPostcode           = $data[ZIPCODE].";";
		$RecipientCity               = commonfunction::utf8Decode($data[CITY]).";";
		$RecipientContactPerson      = commonfunction::utf8Decode($data[CONTACT]).";";
		$RecipientTelephone          = $data[PHONE].";";
		$RecipientFax                = ";";
		$RecipientEmail              = $data[EMAIL].";";
		$RecipientComment            = ";";//commonfunction::utf8Decode($data['note1']).";";
		$RecipientILNnumber          = ";";
		
		$PARCEL_DATA .= $CustomerNumbeOfTheConsignee.$RecipientName1.$RecipientName2.$RecipientStreet.$RecipientHouseNo.$RecipientCountry.$RecipientState.$RecipientPostcode.$RecipientCity.$RecipientContactPerson.$RecipientTelephone.$RecipientFax.$RecipientEmail.$RecipientComment.$RecipientILNnumber;
		
		$ShipmentType         = $ServiceCode.";";
		$ExpectedDeliveryDate = ";";
		$ExpectedDeliveryTime = ";";
		$LatePickUp           = "0;";
		$InstructionFlag 	  = $ModifiedInstruction["none"].";"; //Modified instruction flag(0=none/1=zero-cost modified instructions/2=modified instructions with charge),default value:0
		$MPSIDreference 	  = ";"; //MPSID reference (master number of the original shipment) 2)
		$ProofOfDelivery 	  = "0;"; //Manual creation of Proof of delivery (0 = no / 1 = yes), default value: 0
		
		$PARCEL_DATA .= $ShipmentType.$ExpectedDeliveryDate.$ExpectedDeliveryTime.$LatePickUp.$InstructionFlag.$MPSIDreference.$ProofOfDelivery."\r\n";
		
		$H3       					= "#DEF;";
		$ParcleRecordIdentification = "PARCEL;";
		$ConsignmentNumberParcel    = "MPS".$data[REROUTE_BARCODE].$DateCreate.";";
		$ParcleLabelNumber          = $data[REROUTE_BARCODE].";";
		
		$IncreasedInsuranceValue    = "0;"; //Increased insurance value (1 = yes / 0 = no)
		$IncreasedInsuranceValue1   = ";"; //Increased insurance value with 2 places after the decimal point without separators
		$CurrencyCode               = $data['currency'].";";
		$ParcleContents             = ";";
		
		$PARCEL_DATA .= $ParcleRecordIdentification.$ConsignmentNumberParcel.$ParcleLabelNumber.$CustomerReferenceNo1.$CustomerReferenceNo2.$CustomerReferenceNo3.$CustomerReferenceNo4.$DELISUserID.$ShipmentType.$Volumn.$ShipmentWeight.$IncreasedInsuranceValue.$IncreasedInsuranceValue1.$CurrencyCode.$ParcleContents."\r\n";
       if(trim($data[EMAIL])!=''){
		    $countrycode = $this->RecordData['rec_cncode'];
			$Hemail 		= "#DEF;";
			$emailhead		 = "MSG;";
			$emailcheck      = "MPS".$data[REROUTE_BARCODE].$DateCreate.";1;";
			$emailadd        = $data[EMAIL].";904;".$countrycode.";;;;;;;;;;;;;;;;;";
			$PARCEL_DATA .= $emailhead.$emailcheck.$emailadd."\r\n";
		}
		if(($data['addservice_id']==7 || $data['addservice_id']==146) && $data['cod_price'] > 0) {
			$H4      					= "#DEF;";
			$CODIdentification  		= "COD;";
			$CODAmount          		= commonfunction::stringReplace(".","",$data['cod_price']).";";
			$CollectionType     		= "0;"; 
			$IntendedUse        		= ";";
			$BankCode           		= ";";
			$BankName           		= ";";
			$BankAcountNumber   		= ";";
			$AccountHolder      		= ";";
			$InternationalAccountNumber = ";";
			$BankCode           		= ";";
			$PARCEL_DATA .= $CODIdentification.$ConsignmentNumber.$ParcleLabelNumber.$CODAmount.$CurrencyCode.$CollectionType.$IntendedUse.$BankCode.$BankName.$BankAcountNumber.$AccountHolder.$InternationalAccountNumber.$BankCode."\r\n";
		}
	
		return $PARCEL_DATA;									
	}
	
	public function EDIFooter(){
		$TotalEdiData .= "#END;". $this->Forwarders['IFD_number'].";\r\n";
	}  

}

