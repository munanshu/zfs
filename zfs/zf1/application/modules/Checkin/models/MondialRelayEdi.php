<?php 
class Checkin_Model_MondialRelayEdi extends Zend_Custom
{
   public $ForwarderRecord = array();
   public $Forwarders	= array();
    
	public function generateMondialRelay($data){
	  $this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);
	  try{
	   $select = $this->_db->select()
							->from(array('BT'=>SHIPMENT_BARCODE),array('*','COUNT(1) AS CNT'))
							->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE))
							->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,ADDRESS,CITY,ZIPCODE,STREET2,PHONE,EMAIL,
							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,'currency',QUANTITY,'senderaddress_id','goods_id'))
							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,'user_id','phoneno'))
							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))
							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))
							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")")
							->group("BT.shipment_id"); //print_r($select->__toString());die;
	    $results = $this->getAdapter()->fetchAll($select);
		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); } 
		
	    $ediBody = '';
		$ediBody .= $this->MondialRelayEdiHeader(count($results));
		foreach($results as $result){
		    $this->RecordData = $result;
		  	$this->ForwarderRecord = $this->ForwarderDetail();
            if($result[SERVICE_ID]==124 || $result[SERVICE_ID]==148){
		     $ediBody .=  $this->MondialRelayReturnEdiBody($result);
		   }else{
		     $ediBody .=  $this->MondialRelayEdiBody($result);
		   }
		}
	   $fileName = "dpc.".date("Ymd").'.'.date("His");
	   return array('EdiData'=>$ediBody,'EdiFilename'=>$fileName);
	}
	
	/**
	 * Generate DPD EDI Header
	 * Function : MondialRelayEdiHeader()
	 * Function Generate Mondial EDI Header
	 **/
	public function MondialRelayEdiHeader($count) {
		$CODFIC			= 'A';												//Record Type
		$CODENR 		= '0';												//Recordng Code
		$SOCEMET		= commonfunction::paddingRight('NL',3,' ');				//Sending Company
		$SOCDEST		= commonfunction::paddingRight('MR',3,' ');					//receiving Company
		$SEQFIC			= commonfunction::paddingleft($this->Forwarders['IFD_number'],5,'0');	//file sequence
		$NBENR			= commonfunction::paddingleft(($count+1),7,'0');					// Number of record include file header
		$DTTRAN			= date('d.m.Y');									//Transfer Date
		$VERSION		= commonfunction::paddingRight('04.00',5,' ');				// Version Number
		$Blank			= commonfunction::paddingRight('',965,' ');					// Blanck Space
		$TopRecords 		= $CODFIC.$CODENR.$SOCEMET.$SOCDEST.$SEQFIC.$NBENR.$DTTRAN.$VERSION.$Blank."\r\n";
		
		return $TopRecords;
	}
	
	/**
	*Generate EDI For MondialRelay
	*Function : MondialRelayEdiBody()
	*Function Generate Generate EDI for MondialRelay forwarder
	**/
	public function MondialRelayEdiBody($data){
	    $PARCEL_DATA = '';
		$data['Country_code'] = $this->RecordData['rec_cncode'];
		$agency 			 = $this->getAgencyNumber($data);
		$marchant = $this->Forwarders['service_type'];
		$origin = $this->Forwarders['barcode_prefix'];
		
		
		if($data[SERVICE_ID]==1 || $data[SERVICE_ID]==2){
			$servicecode   = 0;
			$collectionServce = 1;
		 }else{
			$servicecode   = 3;
			$collectionServce = 2;
		 }
		$weight = $data[WEIGHT] * 1000;
		
		$Sender   = $this->ForwarderRecord['SenderAddress'];
		
		// File Information
	    $CODFIC			= 'A';									//file type
		$CODENR 		= '1';									// Recording COde
		$SSCODE			= '0';									//recording sub-code
		$MARQUE			= commonfunction::paddingRight('NL',2,' ');							// Brand Code given by Mondial Relay
		$NEXPE			= commonfunction::paddingRight($data[TRACENR],8,' ');		//Shipment Number
		$NBCOLIS		= commonfunction::paddingleft($data['CNT'],2,0);							//Number of parcel in the shipment
		$DISTRI			= commonfunction::paddingRight('D',1,' ');							//Distribution Mode
		$CDEST			= commonfunction::paddingRight($agency['pr_number'],8,' ');		//destination zipcode
		$TRANS			= commonfunction::paddingRight(commonfunction::sub_string($agency['agency_code'],0,4),4,' ');    // Agency Number
		$TOURNE			= commonfunction::paddingRight($agency['pr_number'],5,' ');					// Run number
		$TYPSER			= commonfunction::paddingRight($servicecode,1,' ');							//Service code
		if($data[QUANTITY]>1){
			$LIVMOD			= commonfunction::paddingRight('24L',3,' ');						// Mode of Delivery
		}else{
			$LIVMOD			= commonfunction::paddingRight('24R',3,' ');						// Mode of Delivery
		}
		$DATREM			= date('d.m.Y');											//preadvice date
		$SIGLE			= commonfunction::paddingRight('Mr',4,' ');						// Status
		//51 
		$PARCEL_DATA .= $CODFIC.$CODENR.$SSCODE.$MARQUE.$NEXPE.$NBCOLIS.$DISTRI.$CDEST.$TRANS.$TOURNE.$TYPSER.$LIVMOD.$DATREM.$SIGLE;
		
		//Receiver Information
		$firstTwo = (commonfunction::sub_string($data[PHONE],0,2)=='33')?commonfunction::sub_string($data[PHONE],2):$data[PHONE];
		$rec_phone = commonfunction::sub_string((!empty($data[PHONE]))?commonfunction::stringReplace(array('(0)',' '),array('0',''),$firstTwo):'748800700',0,17);
		
		$LVADR1			= commonfunction::paddingRight(commonfunction::sub_string(commonfunction::utf8Decode($data[RECEIVER]),0,28),28,' ');		//Receiver name
		$LVADR2			= commonfunction::paddingRight(commonfunction::sub_string(commonfunction::utf8Decode($data[CONTACT]),0,30),30,' ');    //receiver Contact
		$Libre			= commonfunction::paddingRight('',2,' ');							//  free space
		$LVADR3			= commonfunction::paddingRight(commonfunction::sub_string(commonfunction::utf8Decode($data[STREET].' '.$data[STREETNR]),0,30),30,' '); //receiver address 1
		$Libre1			= commonfunction::paddingRight('',2,' ');							//free space
		$LVADR4			= commonfunction::paddingRight(commonfunction::sub_string(commonfunction::utf8Decode($data[ADDRESS].' '.$data[STREET2]),0,30),30,' ');   //receiver address 2
		$Libre2			= commonfunction::paddingRight('',2,' ');				//free space
		$LVADR5			= commonfunction::paddingRight('',30,' ');				// Receiver address 3
		$Libre3			= commonfunction::paddingRight('',2,' ');					//free space
		$LVADR6			= commonfunction::paddingRight(commonfunction::utf8Decode($data[CITY]),26,' ');	// Receiver city
		$LVCPAY			= commonfunction::paddingRight($data['Country_code'],2,' ');	//receiver country Code
		$LVCPOS			= commonfunction::paddingRight($data[ZIPCODE],5,' ');	//Receiver Zipcode
		$LVXPOS			= commonfunction::paddingRight('',5,' ');						//Delivery Zipcode extension
		$LVTEL1			= commonfunction::paddingRight('+33'.$rec_phone,20,' ');	// Receiver Phone 1
		$LVTEL2			= commonfunction::paddingRight('',20,' ');						//Receiver Phone 2
		$LVEMAI			= commonfunction::paddingRight($data[EMAIL],70,' ');	//Receiver email
		$INSLIV1		= commonfunction::paddingRight($data[REFERENCE],31,' ');		// Delivery special information
		
		//335
		$PARCEL_DATA .= $LVADR1.$LVADR2.$Libre.$LVADR3.$Libre1.$LVADR4.$Libre2.$LVADR5.$Libre3.$LVADR6.$LVCPAY.$LVCPOS.$LVXPOS.$LVTEL1.$LVTEL2.$LVEMAI.$INSLIV1;

		//Shipment Information
		$INSLIV2		= commonfunction::paddingRight('',31,' ');								//Delivery special information line 2
		$Libre4			= commonfunction::paddingRight('',10,' ');								// Free space
		$POIDS			= commonfunction::paddingleft($weight,7,'0');							// Weight in grams
		$VOLU			= commonfunction::paddingRight('',7,' ');								// PArcel volumn in dl
		$LONG			= commonfunction::paddingRight('',3,' ');								//SHipment max length in cm?
		$ORIG			= commonfunction::paddingRight($origin,6,' ');    					// Marchant Given by Modial Relay
		$VENTE			= commonfunction::paddingRight('',7,' ');								//SHipment value in cents
		$DEVVTE			= commonfunction::paddingRight('',3,' ');								//Currency of shipment Value
		
		$cod_value = commonfunction::paddingleft(0,7,0);
		$cod_currency = '';
		if(($data[ADDSERVICE_ID]==141 || $data[ADDSERVICE_ID]==157) && $data['cod_price']>0){
		    $cod_value = commonfunction::paddingleft(($data['cod_price']*100),7,0);
			$cod_currency = 'EUR';
		}
		
		$CRT			= commonfunction::paddingRight($cod_value,7,' ');								//COD shipment value in cents
		$DEVCRT			= commonfunction::paddingRight($cod_currency,3,' ');								//Currency of shipment value
		$REFEXT			= commonfunction::paddingRight('',15,' ');								//Marchant shpment reference
		$REFCLI			= commonfunction::paddingRight(commonfunction::sub_string($data[REFERENCE],0,9),9,' ');	//Receiver reference				
		$DATFAC			= date('d.m.Y');												//Invoice date
		$DATCDE			= date('d.m.Y');												//Order Date
		$CALPHA			= commonfunction::paddingRight(commonfunction::sub_string($data[RECEIVER],0,5),5,' ');	//five character of receiver name
		$PRTMIS			= commonfunction::paddingRight('',5,' ');								//Blanck
		$COLLEC			= commonfunction::paddingRight('',1,' ');							//Blanck
		$TOPMDM			= commonfunction::paddingRight('O',1,' ');							//furniture assembly O or Not is N
		$TOPEMB			= commonfunction::paddingRight('01',2,' ');							//Packeging
		$QTLGAR			= commonfunction::paddingRight('01',2,' ');							//Quantiry of item lines
		
		$DATRDV			= date('d.m.Y');
		$CODCRNRDV		= commonfunction::paddingRight('',2,' ');
		$DEBCRNLIVANN	= commonfunction::paddingRight('',2,' ');
		$FINCRNLIVANN	= commonfunction::paddingRight('',2,' ');
		$Blank			= commonfunction::paddingRight('',10,' ');
		$TOPAVI			= commonfunction::paddingRight('O',1,' ');
		$TAXAFF			= commonfunction::paddingRight('',7,' ');
		$TAXCRT			= commonfunction::paddingRight('',7,' ');
		$Blank1			= commonfunction::paddingRight('',2,' ');
		$TOPKDO			= commonfunction::paddingRight('',3,' ');
		$TOTDIM			= commonfunction::paddingRight('',3,' '); 
		$Blank2			= commonfunction::paddingRight('',33,' ');
		$TOP_POSIT		= commonfunction::paddingRight('',7,' ');
		
		$DISCOL			= commonfunction::paddingRight('',1,' ');
		
		$CCOLL			= commonfunction::paddingRight('',8,' ');
		$AGPEC			= commonfunction::paddingRight('',4,' ');
		$TRNCOL			= commonfunction::paddingRight('',5,' ');
		$SERCOL			= commonfunction::paddingRight(2,1,' ');
		$COLMOD			= commonfunction::paddingRight('CCC',3,' ');
		$SIGLE1			= commonfunction::paddingRight('',4,' ');
		//256
		$PARCEL_DATA .= $INSLIV2.$Libre4.$POIDS.$VOLU.$LONG.$ORIG.$VENTE.$DEVVTE.$CRT.$DEVCRT.$REFEXT.$REFCLI.$DATFAC.$DATCDE.$CALPHA.$PRTMIS.$COLLEC.$TOPMDM.$TOPEMB.$QTLGAR.$DATRDV.$CODCRNRDV.$DEBCRNLIVANN.$FINCRNLIVANN.$Blank.$TOPAVI.$TAXAFF.$TAXCRT.$Blank1.$TOPKDO.$TOTDIM.$Blank2.$TOP_POSIT.$DISCOL.$CCOLL.$AGPEC.$TRNCOL.$SERCOL.$COLMOD.$SIGLE1;
		
		//Sender Information
		$EXADR1			= commonfunction::paddingRight('',28,' ');
		$EXADR2			= commonfunction::paddingRight('',30,' ');
		$Libres1		= commonfunction::paddingRight('',2,' ');
		$EXADR3			= commonfunction::paddingRight('',30,' ');
		$Libres2		= commonfunction::paddingRight('',2,' ');
		$EXADR4			= commonfunction::paddingRight('',30,' ');
		$Libres3		= commonfunction::paddingRight('',2,' ');
		$EXADR5			= commonfunction::paddingRight('',30,' ');  
		$Libres4		= commonfunction::paddingRight('',2,' ');
		$EXADR6			= commonfunction::paddingRight('',26,' ');
		$EXCPAY			= commonfunction::paddingRight('',2,' '); 
		$EXCPOS			= commonfunction::paddingRight('',5,' '); 
		$EXXCPO			= commonfunction::paddingRight('',5,' ');
		$EXNTEL			= commonfunction::paddingRight('',20,' ');
		$EXEMAI			= commonfunction::paddingRight('',70,' ');
		$LNGCOL			= commonfunction::paddingRight('',2,' ');
		$RECOL			= commonfunction::paddingRight('999999999',9,' ');
		//291
		$PARCEL_DATA .= $EXADR1.$EXADR2.$Libres1.$EXADR3.$Libres2.$EXADR4.$Libres3.$EXADR5.$Libres4.$EXADR6.$EXCPAY.$EXCPOS.$EXXCPO.$EXNTEL.$EXEMAI.$LNGCOL.$RECOL;
		
		//Delvery information
		$TASSU			= commonfunction::paddingRight('',1,' ');
		$LNGLIV			= commonfunction::paddingRight($data['Country_code'],2,' ');   
		$Blank1			= commonfunction::paddingRight('',57,' ');
		//67
		$PARCEL_DATA .= $TASSU.$LNGLIV.$Blank1."\r\n";
	   
		return $PARCEL_DATA;									
	}
	
	/**
	*Generate EDI For MondialRelay
	*Function : CreateEdiMondialRelayReturn()
	*Function Generate Generate EDI for MondialRelay forwarder
	**/
	public function MondialRelayReturnEdiBody($data){ 
	    $PARCEL_DATA = '';
	    $data['Country_code'] = $this->RecordData['rec_cncode'];
		$agency 			 = $this->getAgencyNumberReturn($data);	
		$marchant = $this->Forwarders['service_type'];
		$origin = $this->Forwarders['barcode_prefix'];
		
		$servicecode   = 2;
		$collectionServce = 2;
		
		$weight = $data[WEIGHT] * 1000;
		
		$Sender   = $this->ForwarderRecord['SenderAddress'];
		
		// File Information
	    $CODFIC			= 'A';									//file type
		$CODENR 		= '1';									// Recording COde
		$SSCODE			= '0';									//recording sub-code
		$MARQUE			= commonfunction::paddingRight('NL',2,' ');							// Brand Code given by Mondial Relay
		$NEXPE			= commonfunction::paddingRight($data[TRACENR],8,' ');		//Shipment Number
		$NBCOLIS		= commonfunction::paddingleft($data['CNT'],2,0);							//Number of parcel in the shipment
		$DISTRI			= commonfunction::paddingRight('D',1,' ');							//Distribution Mode
		$CDEST			= commonfunction::paddingRight('1480',8,' ');		//destination zipcode
		$TRANS			= commonfunction::paddingRight(commonfunction::sub_string($agency['agency_code'],0,4),4,' ');    // Agency Number
		$TOURNE			= commonfunction::paddingRight($agency['pr_number'],5,' ');					// Run number
		$TYPSER			= commonfunction::paddingRight($servicecode,1,' ');							//Service code
		
		$LIVMOD			= commonfunction::paddingRight('LCC',3,' ');
		
		$DATREM			= date('d.m.Y');											//preadvice date
		$SIGLE			= commonfunction::paddingRight('Mr',4,' ');						// Status
		//51 
		$PARCEL_DATA .= $CODFIC.$CODENR.$SSCODE.$MARQUE.$NEXPE.$NBCOLIS.$DISTRI.$CDEST.$TRANS.$TOURNE.$TYPSER.$LIVMOD.$DATREM.$SIGLE;
		
		//Receiver Information
		$rec_phone = (!empty($data[PHONE]))?$data[PHONE]:'748800700';
		$userdetail = $this->getCustomerDetails($data[ADMIN_ID]);
		$LVADR1			= commonfunction::paddingRight(commonfunction::utf8Decode($userdetail['company_name']),28,' ');		//Receiver name
		$LVADR2			= commonfunction::paddingRight('Parcel.nl',30,' ');    //receiver Contact
		$Libre			= commonfunction::paddingRight('',2,' ');							//  free space
		$LVADR3			= commonfunction::paddingRight('3 Square Fabelta',30,' '); //receiver address 1
		$Libre1			= commonfunction::paddingRight('',2,' ');							//free space
		$LVADR4			= commonfunction::paddingRight('',30,' ');   //receiver address 2
		$Libre2			= commonfunction::paddingRight('',2,' ');				//free space
		$LVADR5			= commonfunction::paddingRight('',30,' ');				// Receiver address 3
		$Libre3			= commonfunction::paddingRight('',2,' ');					//free space
		$LVADR6			= commonfunction::paddingRight('Tubeke Tubize',26,' ');	// Receiver city
		$LVCPAY			= commonfunction::paddingRight('BE',2,' ');	//receiver country Code
		$LVCPOS			= commonfunction::paddingRight('1480',5,' ');	//Receiver Zipcode
		$LVXPOS			= commonfunction::paddingRight('',5,' ');						//Delivery Zipcode extension
		$LVTEL1			= commonfunction::paddingRight('+33748800700',20,' ');	// Receiver Phone 1
		$LVTEL2			= commonfunction::paddingRight('',20,' ');						//Receiver Phone 2
		$LVEMAI			= commonfunction::paddingRight($userdetail['email'],70,' ');
		$INSLIV1		= commonfunction::paddingRight($data[REFERENCE],31,' ');		// Delivery special information
		//335
		$PARCEL_DATA .= $LVADR1.$LVADR2.$Libre.$LVADR3.$Libre1.$LVADR4.$Libre2.$LVADR5.$Libre3.$LVADR6.$LVCPAY.$LVCPOS.$LVXPOS.$LVTEL1.$LVTEL2.$LVEMAI.$INSLIV1;

		//Shipment Information
		$INSLIV2		= commonfunction::paddingRight('',31,' ');								//Delivery special information line 2
		$Libre4			= commonfunction::paddingRight('',10,' ');								// Free space
		$POIDS			= commonfunction::paddingleft($weight,7,'0');							// Weight in grams
		$VOLU			= commonfunction::paddingRight('',7,' ');								// PArcel volumn in dl
		$LONG			= commonfunction::paddingRight('',3,' ');								//SHipment max length in cm?
		$ORIG			= commonfunction::paddingRight($origin,6,' ');    					// Marchant Given by Modial Relay
		$VENTE			= commonfunction::paddingRight('',7,' ');								//SHipment value in cents
		$DEVVTE			= commonfunction::paddingRight('',3,' ');								//Currency of shipment Value
		
		$cod_value = commonfunction::paddingleft(0,7,0);
		
		$CRT			= commonfunction::paddingRight($cod_value,7,' ');								//COD shipment value in cents
		$DEVCRT			= commonfunction::paddingRight('',3,' ');								//Currency of shipment value
		$REFEXT			= commonfunction::paddingRight('',15,' ');								//Marchant shpment reference
		$REFCLI			= commonfunction::paddingRight(commonfunction::sub_string($data[REFERENCE],0,9),9,' ');	//Receiver reference				
		$DATFAC			= date('d.m.Y');												//Invoice date
		$DATCDE			= date('d.m.Y');												//Order Date
		$CALPHA			= commonfunction::paddingRight(commonfunction::sub_string($data[RECEIVER],0,5),5,' ');	//five character of receiver name
		$PRTMIS			= commonfunction::paddingRight('',5,' ');								//Blanck
		$COLLEC			= commonfunction::paddingRight('',1,' ');							//Blanck
		$TOPMDM			= commonfunction::paddingRight('O',1,' ');							//furniture assembly O or Not is N
		$TOPEMB			= commonfunction::paddingRight('01',2,' ');							//Packeging
		$QTLGAR			= commonfunction::paddingRight('01',2,' ');							//Quantiry of item lines
		
		$DATRDV			= date('d.m.Y');
		$CODCRNRDV		= commonfunction::paddingRight('',2,' ');
		$DEBCRNLIVANN	= commonfunction::paddingRight('',2,' ');
		$FINCRNLIVANN	= commonfunction::paddingRight('',2,' ');
		$Blank			= commonfunction::paddingRight('',10,' ');
		$TOPAVI			= commonfunction::paddingRight('O',1,' ');
		$TAXAFF			= commonfunction::paddingRight('',7,' ');
		$TAXCRT			= commonfunction::paddingRight('',7,' ');
		$Blank1			= commonfunction::paddingRight('',2,' ');
		$TOPKDO			= commonfunction::paddingRight('',3,' ');
		$TOTDIM			= commonfunction::paddingRight('',3,' '); 
		$Blank2			= commonfunction::paddingRight('',33,' ');
		$TOP_POSIT		= commonfunction::paddingRight('',7,' ');
		
		$collectionAddress = $this->getCollectionAddress($data);
		$DISCOL			= commonfunction::paddingRight('D',1,' ');
		$CCOLL			= commonfunction::paddingRight($data[ZIPCODE],8,' ');
		$AGPEC			= commonfunction::paddingRight(commonfunction::sub_string($collectionAddress['agency_number'],0,4),4,' ');
		$TRNCOL			= commonfunction::paddingRight($collectionAddress['direction'],5,' ');
		
		
		$SERCOL			= commonfunction::paddingRight(2,1,' ');
		$COLMOD			= commonfunction::paddingRight('CDS',3,' ');
		
		$SIGLE1			= commonfunction::paddingRight('',4,' ');
		//256
		$PARCEL_DATA .= $INSLIV2.$Libre4.$POIDS.$VOLU.$LONG.$ORIG.$VENTE.$DEVVTE.$CRT.$DEVCRT.$REFEXT.$REFCLI.$DATFAC.$DATCDE.$CALPHA.$PRTMIS.$COLLEC.$TOPMDM.$TOPEMB.$QTLGAR.$DATRDV.$CODCRNRDV.$DEBCRNLIVANN.$FINCRNLIVANN.$Blank.$TOPAVI.$TAXAFF.$TAXCRT.$Blank1.$TOPKDO.$TOTDIM.$Blank2.$TOP_POSIT.$DISCOL.$CCOLL.$AGPEC.$TRNCOL.$SERCOL.$COLMOD.$SIGLE1;
		
		//Sender Information
		$EXADR1			= commonfunction::paddingRight(commonfunction::sub_string(commonfunction::utf8Decode($data[RECEIVER]),0,28),28,' ');
		$EXADR2			= commonfunction::paddingRight(commonfunction::sub_string(commonfunction::utf8Decode($data[CONTACT]),0,30),30,' ');
		$Libres1		= commonfunction::paddingRight('',2,' ');
		$EXADR3			= commonfunction::paddingRight(commonfunction::sub_string(commonfunction::utf8Decode($data[STREET].' '.$data[STREETNR]),0,30),30,' ');
		$Libres2		= commonfunction::paddingRight('',2,' ');
		$EXADR4			= commonfunction::paddingRight(commonfunction::sub_string(commonfunction::utf8Decode($data[ADDRESS].' '.$data[STREET2]),0,30),30,' ');
		$Libres3		= commonfunction::paddingRight('',2,' ');
		$EXADR5			= commonfunction::paddingRight('',30,' ');  
		$Libres4		= commonfunction::paddingRight('',2,' ');
		$EXADR6			= commonfunction::paddingRight($data[CITY],26,' ');
		$EXCPAY			= commonfunction::paddingRight($data['Country_code'],2,' ');
		$EXCPOS			= commonfunction::paddingRight($data[ZIPCODE],5,' '); 
		$EXXCPO			= commonfunction::paddingRight('',5,' ');
		$EXNTEL			= commonfunction::paddingRight('+33748800700',20,' ');
		$EXEMAI			= commonfunction::paddingRight('',70,' ');
		$LNGCOL			= commonfunction::paddingRight('',2,' ');
		
		$RECOL			= commonfunction::paddingRight('',9,' ');
		//291
		$PARCEL_DATA .= $EXADR1.$EXADR2.$Libres1.$EXADR3.$Libres2.$EXADR4.$Libres3.$EXADR5.$Libres4.$EXADR6.$EXCPAY.$EXCPOS.$EXXCPO.$EXNTEL.$EXEMAI.$LNGCOL.$RECOL;
		
		//Delvery information
		$TASSU			= commonfunction::paddingRight('',1,' ');
		$LNGLIV			= commonfunction::paddingRight('FR',2,' ');   
		$Blank1			= commonfunction::paddingRight('',57,' ');
		//67
		
		$PARCEL_DATA .= $TASSU.$LNGLIV.$Blank1."\r\n";
	   
		return $PARCEL_DATA;									
	}
	
    
    public function getAgencyNumber($data){
	   $select = $this->_db->select()
	   						->from(array('PS'=>SHIPMENT_PARCELPOINT),array('*'))
							->where("PS.shipment_id='".$data['shipment_id']."' AND PS.parcel_shop!=''");
							//print_r($select->__toString());die;
	    $locations = $this->getAdapter()->fetchRow($select);
		if(!empty($locations)){
		  $shopsdetail =  json_decode($locations['parcel_shop']);
		  $result['company']  = $shopsdetail->company;
		  $result['zipCode']  = $shopsdetail->zipCode;
		  $result['city']  = $shopsdetail->city;
		  $result['agency_name']  = $shopsdetail->agency_name;
		  $result['agency_code']  = $shopsdetail->agency_code;
		  $result['shuttle_code']  = $shopsdetail->shuttle_code;
		  $result['group_code']  = $shopsdetail->group_code;
		  $result['pr_number']  = $shopsdetail->pr_number;
		  $result['street']  = $shopsdetail->street;
		}else{
         $select = $this->masterdb->select()
	  							 ->from(array('MRNPR'=>MR_NEARESTPR),array(''))
								 ->joininner(array('MRRT'=>MR_ROUTING),"MRNPR.pr_number=MRRT.pr_number",array('*'))
								 ->joinleft(array('MRAN'=>MR_AGENCY),"SUBSTRING(MRRT.agency_code,1,4)=MRAN.agency_number",array('agency_name'))
								  ->where("MRNPR.zipcode='".$data[ZIPCODE]."' AND MRNPR.pr_country='".$this->RecordData['rec_cncode']."'  AND MRRT.pr_country_code='".$this->RecordData['rec_cncode']."' AND MRRT.group_code!='' AND MRRT.shuttle_code!=''")
								 ->order("MRNPR.distance ASC")
								 ->limit(1);
								 //echo $select->__toString();die;
      $result = $this->masterdb->fetchRow($select);
	}
	return $result;
  }	
 
    public function getAgencyNumberReturn($data){
  			$select = $this->masterdb->select()
									->from(array('MRNPR'=>MR_SORTING),array('*'))
									 ->joininner(array('AN'=>MR_AGENCY),"AN.agency_number=MRNPR.agency_number",array('agency_name'))
									  ->where("MRNPR.zipcode=1 AND MRNPR.destination_type='DT' AND MRNPR.delivery_mode='LCC' AND MRNPR.country_code='BE'");
										 //echo $select->__toString();die;
		  $result = $this->masterdb->fetchRow($select);
		  $shopdetail['agency_name']  = $result['agency_name'];
		  $shopdetail['agency_code']  = $result['agency_number'];
		  $shopdetail['shuttle_code']  = $result['shuttle_code'];
		  $shopdetail['group_code']  = $result['group_code'];
		  $shopdetail['pr_number']  = $result['direction'];
		  $shopdetail['direction']  = $result['direction'];
		return $shopdetail;
  }
 
    public function getCollectionAddress($data){
       $select = $this->masterdb->select()
									 ->from(array('MRNPR'=>MR_SORTING),array('*'))
									 ->joininner(array('AN'=>MR_AGENCY),"AN.agency_number=MRNPR.agency_number",array('agency_name'))
									  ->where("MRNPR.zipcode='".$data[ZIPCODE]."'  AND MRNPR.delivery_mode='LCC' AND MRNPR.country_code='".$this->RecordData['rec_cncode']."'")
									  ->limit(1);
								 //echo $select->__toString();die;
      return $this->masterdb->fetchRow($select);
  }
}

