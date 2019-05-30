<?php

require_once 'Olddbcustom.php';


class Oldhistory_Model_Oldparceltracking extends OlddbCustom
{	

	 public function __construct($Request,$checkinmodel)
	{	//echo "sfdf";die;
		parent::__construct($Request);
		$this->CheckinModelObj = $checkinmodel;

	}

    public function parcelinformation(){

    ini_set('display_errors', 1);	
	   					
	   			$select = $this->oldDb->select()
					->from(array('BT' =>SHIPMENT_BARCODE),array('*'))
					->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('rec_reference','checkin_date','driver_id','assigned_date','checkin_by','label_date','pickup_date','edi_date','delivery_date','received_by'))
					->joininner(array('BL'=>SHIPMENT),"BL.shipment_id=BT.shipment_id",array('country_id','user_id','rec_name','addservice_id','create_date','senderaddress_id','goods_id','create_by','rec_zipcode','rec_email','email_notification','rec_city','rec_street','rec_phone','rec_address','rec_streetnr','rec_contact','rec_street2'))
					->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=BL.user_id",array("AT.company_name",'AT.city AS sender_city','parent_id'))
					->joininner(array('CT' =>COUNTRIES),"CT.country_id=BL.country_id",array("CT.country_name"))
					->joininner(array('ACT' =>COUNTRIES),"ACT.country_id=AT.country_id",array("ACT.cncode AS sendercncode","ACT.country_name AS sender_country"))
					->joininner(array('FT' =>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array("FT.forwarder_name"))
					->joininner(array('ST' =>SERVICES),"ST.service_id=BT.service_id",array("ST.service_name"))
					->where("BT.barcode_id='".$this->getData['barcode_id']."'");
		$this->RecordData =   $this->oldDb->fetchRow($select);
		$this->CheckinModelObj->RecordData =  $this->RecordData;

		
		$this->CheckinModelObj->RecordData['forwarder_detail'] = $this->CheckinModelObj->ForwarderDetail();
		$this->RecordData =  $this->CheckinModelObj->RecordData;
		// echo "<pre>";print_r($this->RecordData);die;
									// print_r($this->RecordData);die;
		$this->getData = $this->RecordData;
		if($this->getData['error_status']=='1'){
		  $select = $this->oldDb->select()
								->from(array('PT'=>PARCEL_TRACKING),array('dpd_error_url'))
								->joininner(array('SL'=>STATUS_LIST),"SL.error_id=PT.status_id",array('master_id'))
								->where("PT.barcode_id='".$this->getData['barcode_id']."'")
								->order("status_date DESC")
								->limit(1);
								// print_r($select->__toString());die;
		 $result = $this->oldDb->fetchRow($select);
		 $this->getData['master_id'] = $result['master_id'];
		 $this->getData['dpd_error_url'] = $result['dpd_error_url'];
		}
		
		$this->getOurTracking();
		return $this->getData;
		//
	}
	
	public function getOurTracking(){ //echo "<pre>";print_r($this->getData);die;
	   $tracking_data = array();
	   $this->getData['scrapws'] = array();
	   $addedXY = $this->CheckinModelObj->getLatLong($this->getData['sendercncode'],$this->getData['sender_city']);
	   //Added
	   $tracking_data[] = array('location'=>$this->getData['sender_city'].'('.$this->getData['sendercncode'].')','status'=>'Parcel Added','updatedon'=>$this->getData['create_date'],'latitude'=>$addedXY['latitude'],'longitude'=>$addedXY['longitude'],'icon'=>'parcel_added.png');
	   //Label Generated 
	   if($this->getData['label_status']=='1' && $this->getData['label_date']!='0000-00-00 00:00:00'){
	     $tracking_data[] = array('location'=>$this->getData['sender_city'].'('.$this->getData['sendercncode'].')','status'=>'Label Generated','updatedon'=>$this->getData['label_date'],'latitude'=>$addedXY['latitude'],'longitude'=>$addedXY['longitude'],'icon'=>'parcel_added.png');
	   }
	  
	   //$userdetails = $this->CheckinModelObj->getCustomerDetails($this->getData['create_by']);
	   //assigned To driver
	   if($this->getData['driver_id']>0){
	      $tracking_data[] = array('location'=>$this->getData['sender_city'].'('.$this->getData['sendercncode'].')','status'=>'Assigned Driver for Pickup','updatedon'=>$this->getData['assigned_date'],'latitude'=>$addedXY['latitude'],'longitude'=>$addedXY['longitude'],'icon'=>'parcel_added.png');
	   }
	   //$tracking_data[] = array('location'=>$userdetails['city'].'('.$userdetails['cncode'].')','status'=>'Parcel Added','updatedon'=>$this->getData['create_date']);
	   
		
	   //Pickup by Driver
	   if($this->getData['pickup_status']=='1' && $this->getData['pickup_date']!='0000-00-00 00:00:00'){
	    	$tracking_data[] = array('location'=>$this->getData['sender_city'].'('.$this->getData['sendercncode'].')','status'=>'Picked Up','updatedon'=>$this->getData['pickup_date'],'latitude'=>$addedXY['latitude'],'longitude'=>$addedXY['longitude'],'icon'=>'parcel_added.png');
	    }
	   //Checkin
	    
	    if($this->getData['checkin_status']=='1'){
		    $checkin_detail = $this->CheckinModelObj->getCustomerDetails($this->getData['checkin_by']);
		// echo "<pre>"; print_r($checkin_detail);die;
			$addedXYcheckin = $this->CheckinModelObj->getLatLong($checkin_detail['cncode'],$checkin_detail['city']);
	    	$tracking_data[] = array('location'=>$checkin_detail['city'].'('.$checkin_detail['cncode'].')','status'=>'Depot Scan','updatedon'=>$this->getData['checkin_date'],'latitude'=>$addedXYcheckin['latitude'],'longitude'=>$addedXYcheckin['longitude'],'icon'=>'depot_scan.png');
		}
	   //Hub check-in
	    if($this->getData['hub_status']=='1' && isset($checkin_detail['hub_country'])){
		    $hubdetails = $this->getHubDetails($this->getData['barcode_id']);
			$addedXY = $this->CheckinModelObj->getLatLong($checkin_detail['hub_country'],$checkin_detail['hub_city']);
	    	$tracking_data[] = array('location'=>$hubdetails['hub_city'].'('.$hubdetails['hub_country'].')','status'=>'Arrival At hub','updatedon'=>$hubdetails['hub_date'],'latitude'=>$addedXY['latitude'],'longitude'=>$addedXY['longitude'],'icon'=>'other_scan.png');
			if($hubdetails['hub_checkin_status']=='1'){
				$addedXY = $this->CheckinModelObj->getLatLong($checkin_detail['hub_scan_country'],$checkin_detail['hub_scan_city']);
				$tracking_data[] = array('location'=>$hubdetails['hub_scan_city'].'('.$hubdetails['hub_scan_country'].')','status'=>'Departure From hub','updatedon'=>$hubdetails['hub_checkin_date'],'latitude'=>$addedXY['latitude'],'longitude'=>$addedXY['longitude'],'icon'=>'other_scan.png');
			}
		}
		$this->trackinglink();
		// echo "<pre>";print_r($this->getData);die;
		
		$this->getData['scrapws'] = $this->getScrapData();
		
	   //Electronic data Exchange
	    if($this->getData['edi_status']=='1' && empty($this->getData['scrapws'])){
		    //$checkin_detail = $this->CheckinModelObj->getCustomerDetails($this->getData['checkin_by']);
			///$addedXY = $this->CheckinModelObj->getLatLong($checkin_detail['cncode'],$checkin_detail['city']);
	    	$tracking_data[] = array('location'=>$checkin_detail['city'].'('.$checkin_detail['cncode'].')','status'=>'Transit To - '.$this->getData['rec_country_name'],'updatedon'=>$this->getData['checkin_date'],'latitude'=>$addedXYcheckin['latitude'],'longitude'=>$addedXYcheckin['longitude'],'icon'=>'imgicon8.png');
		}

		foreach($this->getData['scrapws'] as $key=>$scrappingData){
		   $addedXY = $this->CheckinModelObj->getLatLong($scrappingData['country'],$scrappingData['city']);
		   if($this->getData['error_status']==1){
		     $icon = ((count($this->getData['scrapws'])-1)==$key )?'not_assigned.png':'imgicon8.png';
		   }else{ 
		      $icon = ((count($this->getData['scrapws'])-1)==$key && $this->getData['delivery_status']==1)?'home_icon.png':'imgicon8.png';
		   }
		   $tracking_data[] = array('location'=>$scrappingData['city'].'('.$scrappingData['country'].')','status'=>$scrappingData['status'],'updatedon'=>$scrappingData['updateon'],'latitude'=>$addedXY['latitude'],'longitude'=>$addedXY['longitude'],'icon'=>$icon);   
		}
	   // echo "<pre>";print_r($tracking_data);die;
	   $this->getData['Tracking'] =  $tracking_data;
	}
	
	public function getHubDetails($barcode_id){
	   $select = $this->oldDb->select()
						  ->from(array('HS'=>SHIPMENT_HUB),array('*'))
						  ->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=HS.hub_userid",array('AT.city AS hub_city'))
						  ->joininner(array('CT' =>COUNTRIES),"CT.country_id=AT.country_id",array("CT.cncode AS hub_country"))
						  ->joinleft(array('SAT' =>USERS_DETAILS),"SAT.user_id=HS.hub_userid",array('SAT.city AS hub_scan_city'))
						  ->joinleft(array('SCT' =>COUNTRIES),"SCT.country_id=SAT.country_id",array("SCT.cncode AS hub_scan_country"))
						  ->where("HS.barcode_id='".$barcode_id."'");
	   return $this->oldDb->fetchRow($select);					  
	}
	
	public function trackinglink(){
	     switch($this->getData[FORWARDER_ID]){
	      case 1:
		  case 2:
		  case 3:
		  case 23:
		  case 26:
		  case 32:
		     // $this->getData['tracking_link']  =  "http://tracking.dpd.de/cgi-bin/delistrack?typ=2&lang=nl&pknr=05128833206182";
		    if($this->getData[COUNTRY_ID]==9){
				$this->getData['tracking_link']  =  "http://tracking.dpd.de/cgi-bin/delistrack?typ=2&lang=nl&pknr=".$this->getData[TRACENR_BARCODE]."";
			}
			elseif($this->getData[COUNTRY_ID]==15){
				$this->getData['tracking_link']  =  "http://tracking.dpd.de/cgi-bin/delistrack?typ=2&lang=de&pknr=".$this->getData[TRACENR_BARCODE]."";
			}elseif($this->getData[COUNTRY_ID]==8){
				$this->getData['tracking_link']  =  "http://tracking.dpd.de/cgi-bin/delistrack?typ=2&lang=fr&pknr=".$this->getData[TRACENR_BARCODE]."";
			}
			else{
				$this->getData['tracking_link']  =  "http://tracking.dpd.de/cgi-bin/delistrack?typ=2&lang=en&pknr=".$this->getData[TRACENR_BARCODE]."";
			}
		  break;
		    case 4:
			  $this->getData['tracking_link']  =  "https://gls-group.eu/DE/de/paketverfolgung?match=".$this->getData[TRACENR_BARCODE];
			break;
			case 5:
			 $this->getData['tracking_link']  =   $this->GLSFreightUrl($this->getData[TRACENR_BARCODE]);
			break;
			case 6:
			   if($this->getData['country_id']!=9){
			     $this->getData['tracking_link']  =  "https://gls-group.eu/EU/en/parcel-tracking?match=".$this->getData[TRACENR_BARCODE]."";
			   }
			   else{
			     $this->getData['tracking_link']  =  $this->GLSNLUrl($this->getData[TRACENR_BARCODE]); 
			   }
			  
			break;
			case 7:
			   $this->getData['tracking_link']  =  "http://www.depostlaposte.be/bpi/track_trace/find.php?search=s&lng=en&trackcode=".$this->getData[TRACENR_BARCODE]."";
			break;
			case 8:
			   $urlink  = "http://online.pannordic.com/pn_logistics/tracking/set_param_pub_tracking.jsp?id=".$this->getData[TRACENR_BARCODE]."";
			break;
			case 9:
			break;
			case 10:
			  $this->getData['tracking_link']  =   "https://sisyr.hlg.de/wps/portal/SISY/TRACKING?TrackID=".$this->getData[TRACENR_BARCODE]."&PLZ=".$this->getData[ZIPCODE]."";
			break;
			case 11:
			 $this->getData['tracking_link']  =   'http://www.post.at/sendungsverfolgung.php/details?pnum1='.$this->getData[TRACENR_BARCODE];
			break;
			case 12:
			  $this->getData['tracking_link']  =  "http://www.post.ch/swisspost-tracking?formattedParcelCodes=".$this->getData[TRACENR_BARCODE]."&p_language=de";
			break;
			case 13:
			  $this->getData['tracking_link']  =  "http://www.selektvracht.nl/track-and-trace.shtml?bcode=".$this->getData[TRACENR_BARCODE]."&submit=";
			break;
		
		case 14:
			 $this->getData['tracking_link']  =  "https://mijnpakket.postnl.nl/Claim?Barcode=".$this->getData[TRACENR_BARCODE]."&Postalcode=".$this->getData[ZIPCODE]."&vind-pakket=Zoek+mijn+pakket&Foreign=false";
			break;
			
		case 17:
			$this->getData['tracking_link']  =  "http://wwwapps.ups.com/etracking/tracking.cgi?TypeOfInquiryNumber=T&InquiryNumber1=".$this->getData[TRACENR_BARCODE]."&commit=Track!";
			break;
		case 18:
         	$this->getData['tracking_link']  =  "http://wwwapps.ups.com/etracking/tracking.cgi?TypeOfInquiryNumber=T&InquiryNumber1=".$this->getData[TRACENR_BARCODE]."&commit=Track!";
			break;
		case 19:
			 $this->getData['tracking_link']  =  "http://wwwapps.ups.com/etracking/tracking.cgi?TypeOfInquiryNumber=T&InquiryNumber1=".$this->getData[TRACENR_BARCODE]."&commit=Track!";
			break;
		case 20:
			$this->getData['tracking_link']  = 'http://www.colissimo.fr/portail_colissimo/suivre.do?colispart='.$this->getData[TRACENR_BARCODE];
		   break;
	  	case 22:
			 //$this->getData['tracking_link']  = BASE_URL."/Parceltracking/parcelnltracking?barcode_id=".Class_Encryption::encode($this->TrackingData['barcode_id']);
		   break;
		case 24:
			$lang = (trim($this->getData[COUNTRY_ID])==15)?'de':'en';
		   $this->getData['tracking_link']  = 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang='.$lang.'&idc='.$this->getData[TRACENR_BARCODE].'&rfn=&extendedSearch=true';
		   break;
		case 25:
			$lang = (trim($this->getData[COUNTRY_ID])==15)?'de':'en';
			$this->getData['tracking_link']  = 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang='.$lang.'&idc='.$this->getData[TRACENR_BARCODE].'&rfn=&extendedSearch=true';
		   break;
		  case 27:
			$this->getData['tracking_link']  = 'http://www.mondialrelay.be/fr-be/suivi-de-colis/?numeroExpedition='.$this->getData[TRACENR_BARCODE].'&codePostal='.$this->getData[ZIPCODE].'';
		   break;
		   case 28:
			$this->getData['tracking_link']  = 'https://track.anpost.ie/TrackStatus.aspx?item='.$this->getData[TRACENR_BARCODE].'&sender=';
		   break;
		  case 29:
			$this->getData['tracking_link']  = 'http://www.myyodel.co.uk/tracking?parcel_id='.$this->getData[TRACENR_BARCODE].'&postcode=';
		   break;
		   case 30:
			  $this->getData['tracking_link']  = "http://www.correos.es/ss/Satellite/site/aplicacion-1349167812834-herramientas_y_apps/detalle_app-num=".$this->getData[TRACENR_BARCODE]."-sidioma=es_ES";
			break; 
		  case 31:
			   $this->getData['tracking_link']  = 'https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1='.$this->getData[TRACENR_BARCODE];
		   break;
		  case 33:
				$this->getData['tracking_link']  = "http://aplicacionesweb.correos.es/localizadorenvios/track.asp?numero=".$this->getData[TRACENR_BARCODE]."&amp;idiomaCorreos=es_ES";
				//$urlink = "http://dpost.be/public/admin_images/wwpl_tracking.jpg";
			break;
		   case 34:
			 $customerno = ($this->getData['parent_id']=='3792')?'223':'2523';
			 $this->getData['tracking_link']  = "https://www.gls-Italy.com/index.php?option=com_gls&view=track_e_trace&mode=search&diretto=yes&locpartenza=BZ&numbda=".$this->getData[TRACENR_BARCODE]."&tiporicerca=numbda&codice=".$customerno."&cl=1";
			break;
			
		case 35:
		    $lang = ($this->getData[COUNTRY_ID]==15) ? 'de' : 'en';
			$this->getData['tracking_link']  = "https://portal.emea.hellmann.net/wps/portal/hps/publicttroad?hps.collino=".$this->getData[TRACENR_BARCODE]."&hps.lang=".$lang;
			break;
	  case 36:
	  case 49:
	  case 51:
	     if($this->getData[COUNTRY_ID]==17){
			 $this->getData['tracking_link']  = "https://service.post.ch/EasyTrack/submitParcelData.do?formattedParcelCodes=".$this->getData[TRACENR_BARCODE]."+&from_directentry=True&directSearch=false&p_language=en&VTI-GROUP=1&lang=de&service=ttb";
			}elseif($this->getData[COUNTRY_ID]==7){  //us
			   $this->getData['tracking_link']  = "https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1=".$this->getData[TRACENR_BARCODE]."";
			}elseif($this->getData[COUNTRY_ID]==19){  //sweden
			   $this->getData['tracking_link']  = "https://track.aftership.com/sweden-posten/".$this->getData[TRACENR_BARCODE]."";
			}elseif($this->getData[COUNTRY_ID]==8){  //France
			   $this->getData['tracking_link']  = "http://www.csuivi.courrier.laposte.fr/suivi/index?id=".$this->getData[TRACENR_BARCODE]."";
			}elseif($this->getData[COUNTRY_ID]==18){  //Norway
			   $this->getData['tracking_link']  = "http://sporing.posten.no/sporing.html?q=".$this->getData[TRACENR_BARCODE]."&lang=en";
			}elseif($this->getData[COUNTRY_ID]==78){  //Cyprus
			   $this->getData['tracking_link']  = "http://ips.cypruspost.gov.cy/ipswebtrack/IPSWeb_item_events.asp?itemid=".$this->getData[TRACENR_BARCODE]."&Submit=%CE%A5%CF%80%CE%BF%CE%B2%CE%BF%CE%BB%CE%AE+%2F+Submit";
			}elseif($this->getData[COUNTRY_ID]==231){  //Cruz Republic
			   $this->getData['tracking_link']  = "https://www.postaonline.cz/en/trackandtrace/-/zasilka/".$this->getData[TRACENR_BARCODE]."";
			}elseif($this->getData[COUNTRY_ID]==131){  //Kazakhstan Republic
			   $this->getData['tracking_link']  = "https://post.kz/#/track/".$this->getData[TRACENR_BARCODE]."#trackingResults";
			}elseif($this->getData[COUNTRY_ID]==151){  //Malta
			   $this->getData['tracking_link']  = "http://trackandtrace.maltapost.com/TrackAndTrace.asp";
			}elseif($this->getData[COUNTRY_ID]==172){  //Malta
			   $this->getData['tracking_link']  = "https://www.nzpost.co.nz/tools/tracking/item/".$this->getData[TRACENR_BARCODE]."";
			}else{
			  $this->getData['tracking_link']  = "http://trackitonline.ru/?tn=".$this->getData[TRACENR_BARCODE]."";
			}  
			break;
		 case 37:
		    $this->getData['tracking_link']  = "http://as777.brt.it/vas/sped_ricdocmit_load.hsm?docmit=".substr($this->getData['tracenr'],0,8)."&ksu=1071071&lang=en";
			/*$contents =@file_get_contents($src);
			if(strpos($contents,'Out for the delivery')!==false){
				$newdata =substr($contents,strpos($contents,'Out for the delivery')+20,130);
				$peredictdata = ($newdata!='')?strip_tags($newdata):'';
			}elseif(strpos($contents,'Expected delivery date')!==false){
			   $peredictdata = trim(strip_tags(preg_replace('/\s+/', ' ',substr($contents,strpos($contents,'Expected delivery date'),400))));
			}*/
		 break;	
		 case 39:
			$this->getData['tracking_link']  = "https://eschenker.dbschenker.com/nges-portal/public/en-US_US/#!/tracking/schenker-search?refNumber=".$this->getData[TRACENR_BARCODE]."";
			break;
		case 40:
			 $this->getData['tracking_link']  = "https://www.hermesworld.com/en/our-services/distribution/uk-distribution/parcel-tracking/?trackingNo=".$this->getData[TRACENR_BARCODE]."";
		   break;
		case 41:
			 $this->getData['tracking_link']  = "https://www.fadello.nl/livetracker?c=".$this->getData[TRACENR_BARCODE]."&pc=".$this->getData[ZIPCODE]."";
		   break;
		case 43:
			 $this->getData['tracking_link']  = "http://trackitonline.ru/?tn=".$this->getData[TRACENR_BARCODE]."";
		   break;
		case 45:
			$this->getData['tracking_link']  = "http://www.correos.es/ss/Satellite/site/aplicacion-1349167812834-herramientas_y_apps/detalle_app-num=".$this->getData[TRACENR_BARCODE]."-sidioma=es_ES";
		break;	 
		case 46:
			$this->getData['tracking_link']  = "https://www.pochta.ru/tracking#".$this->getData[TRACENR_BARCODE]."";
		break;
		case 47:
			$this->getData['tracking_link']  = "https://inpost.pl/en/help/track-parcel?parcel=".$this->getData[TRACENR_BARCODE]."";
		   break;
	    case 50:
		  $this->getData['tracking_link']  = "https://www.cim-online.de/webapp?Request=DIRECTLOGIN&ColliNr=".$this->getData[TRACENR_BARCODE]."&user=U8548PARNL&password=PaNL7532&design=AL&country=DE";
	   break; 
	    case 52:
			$this->getData['tracking_link']  = "http://trackitonline.ru/?tn=".$this->getData[TRACENR_BARCODE]."";
		   break;
	    case 54:
			$this->getData['tracking_link']  = "http://tracking.dpd.de/cgi-bin/delistrack?typ=2&lang=en&pknr=0147".$this->getData[TRACENR]."";
			break;
		case 56:
			 $lang = (trim($this->getData[COUNTRY_ID])==15)?'de':'en';
		   $this->getData['tracking_link']  = "http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=459804037599&rfn=&extendedSearch=true";

			break;   				
		  default:
		    $this->getData['tracking_link']  = "http://trackitonline.ru/?tn=".$this->getData[TRACENR_BARCODE]."";
	   }
	}
	
	public function getScrapData(){
	   $scrappingData =  array();
	   switch($this->getData[FORWARDER_ID]){
	      case 1:
		  case 2:
		  case 3:
		  case 26:
		  case 54:
		     $scrappingData =$this->DPDTracetrack($this->getData);
		  break;
		  case 4:
		     $scrappingData =$this->getGLSNLTrack($this->getData);
		   break;
		   case 6:
		     $scrappingData =$this->getGLSNLTrack($this->getData);
		   break;
		   case 7:
		     $scrappingData =$this->BpostTracking($this->getData);
		   break;
		   case 11:
		     $scrappingData =$this->PostatTracking($this->getData);
		   break;
		   case 17:
		   case 18:
		   case 19:
		   	 $scrappingData =$this->UPSparceltrack($this->getData);
		   break;
		   case 20:
		   	 $scrappingData =$this->Colissimotracking($this->getData);
		   break;
		   case 24:
		   case 25:
		   	 $scrappingData =$this->DHLtracking($this->getData);
		   break;
		   case 27:
		   	 $scrappingData =$this->MondialRelaytracking($this->getData);
		   break;
		   case 29:
		     $scrappingData =$this->Yodeltracking($this->getData);
		   break;
		   case 30: 
		   case 45:
		     $scrappingData =$this->CorreosTrackTrace($this->getData);
		   break;
		   case 32:
		     $scrappingData =$this->DPDPaketshopTracetrack($this->getData);
		   break;
		   case 34:
		     $scrappingData =$this->GlsITTrackTrace($this->getData);
		   break;
		   case 37:
		      $scrappingData = $this->BRTtracking($this->getData);
		     break;
		   case 40:
		     $scrappingData =$this->Hermestracking($this->getData);
		   break;
		   case 56:
		     $scrappingData =$this->GigaDPDTracking($this->getData);
		   break;
		  default:
		     $scrappingData = $this->generalTracking($this->getData);
	   }
	   return $scrappingData;
	}

	public function CutPartialString($string='',$marks=array())
	{
		if(!empty($marks)){

			$startpos =  strpos($string, $marks[0]);
	        $firstend = substr($string, $startpos);
	        $endpos =  strpos($firstend, $marks[1]);
	        return substr($string, $startpos , $endpos);
	
		}
		else return $string;

	}

	public function GigaDPDTracking($Parcelinfo='')
	{
		$countrydetail = $this->CheckinModelObj->getCountryDetail($this->getData['country_id']);
		// print_r($countrydetail);die;
		$html = commonfunction::file_contect($this->getData['tracking_link']);
		$requiredhtml = $this->CutPartialString($html,array('<div id="pieceEvents0"','<div id="recipientDetails0"'));
		$rows = array();
		$values = array();
		$dom = new DOMDocument;
        $dom->loadHtml($requiredhtml);
        $xpath = new DomXPath($dom);
        $nodes = $xpath->query('//table[@class="table table-hover"]/tbody')->item(0);
        $trs = $nodes->getElementsByTagName('tr');
        
        foreach ($trs as $key => $tr) {
        	$tds = $tr->getElementsByTagName('td');
        	if(empty( preg_replace("/--/", "", trim($tds->item(1)->nodeValue)) ) )
        		continue;	
        	$date = strip_tags(trim(explode("," , $tds->item(0)->nodeValue)[1]));
 			
        	$dateStarted = (array) \DateTime::createFromFormat('d.m.y H:i', $date); 
        	$values['updateon'] = date('Y-m-d H:i:s',strtotime($dateStarted['date']));

        	$location = str_replace("¶", "", commonfunction::remove_accent(strip_tags(trim($tds->item(1)->nodeValue)))) ;
        	$values['location'] = $location;
        	$values['status'] = commonfunction::remove_accent($tds->item(2)->nodeValue);
        	$values['country'] = $countrydetail['cncode'];
        	$values['city'] = $location;
        	
        	$rows[] = $values;

        }

       return $rows; 
		 
	}

	public function DPDTracetrack($Parcelinfo){ 
		// $this->getData['tracking_link'] = "http://tracking.dpd.de/cgi-bin/delistrack?typ=2&lang=nl&pknr=05128833408410";
		$html = commonfunction::file_contect($this->getData['tracking_link']);
		// echo "<pre>"; print_r($html);die;
		preg_match('/<table class="alternatingTable" width="100%">(.+?)<div style="float:left;"><div class="box"><div class="boxHead">/s', $html, $matches);
		$arrayMatch = array('<tr>','<tr >','<tr class="even">','<img src="/images/icon_arrow_red.gif" border="0">','/cgi-bin/');
		$arrayreplace = array('<tr>','<tr>','<tr>','<img src="'.BASE_URL.'/public/admin_images/icon_arrow_red.gif" border="0">','http://tracking.dpd.de/cgi-bin/');
		$finalDataArr = array();
		  if(!empty($matches[0])){ 
			$dataArr = commonfunction::explode_string(commonfunction::stringReplace($arrayMatch,$arrayreplace,$matches[0]),'<tr>');
			if(is_array($dataArr)){
				for($i=1;$i<count($dataArr);$i++){
				   $singlerecod = explode("</td>",$dataArr[$i]);
				   $Data = array();
				   $Data['updateon'] = date('Y-m-d H:i:s',strtotime(commonfunction::stringReplace(array('<td>','<br>'),array('',''),$singlerecod[0])));
				   $Data['location'] = commonfunction::stringReplace(array('<td>','<br>'),array('',''),$singlerecod[1]);
				   $locations  =  commonfunction::explode_string(preg_replace("/\s|&nbsp;/",'',$Data['location']),'</a>'); 
				   $country_city  = isset($locations[1])?commonfunction::explode_string($locations[1],'('):array();
				   $Data['city'] = isset($country_city[0])?$country_city[0]:'';
				   if($Data['city']==''){
				      continue;
				   }
				   $Data['country'] = commonfunction::stringReplace(')','',$country_city[1]);
				   $Data['status'] = commonfunction::stringReplace(array('<td>','<br>'),array('',''),$singlerecod[2]);
				   $extrainfo = commonfunction::explode_string(strip_tags(commonfunction::stringReplace(array('<td>','<br>'),array('',''),$singlerecod[3])),'&nbsp;');
				   if(trim($extrainfo[0])=='Parcel picked up by receiver at DPD ParcelShop' || trim($extrainfo[0])=='Pakket afgehaald bij Pickup pakketshop door ontvanger'){
					 $Data['status'] = $extrainfo[0];
					// $this->updateDelivred($Data, $data);
				   }
				   if(isset($data['scrap'])){
					  $Data['location'] = commonfunction::stringReplace(array('(TO)','(VR)','&nbsp;'),'',preg_replace('/[0-9]+/', '',strip_tags($Data['location'])));
				   }
				   $finalDataArr[] = $Data;
				   
				}
			}
		 }
 	return $finalDataArr;
  }
  
  
  public function DPDTATracetrack($data=array()){
   $html = @file_get_contents($url);
    preg_match('/<table class="alternatingTable" width="100%">(.+?)<div style="float:left;"><div class="box"><div class="boxHead">/s', $html, $matches);
    $arrayMatch = array('<tr>','<tr >','<tr class="even">','<img src="/images/icon_arrow_red.gif" border="0">','/cgi-bin/');
	$arrayreplace = array('<tr>','<tr>','<tr>','<img src="'.BASE_URL.'/public/admin_images/icon_arrow_red.gif" border="0">','http://tracking.dpd.de/cgi-bin/');
	$finalDataArr = array();
	  if(!empty($matches[0])){ 
		$dataArr = explode('<tr>',str_replace($arrayMatch,$arrayreplace,$matches[0]));
		if(is_array($dataArr)){
			for($i=1;$i<count($dataArr);$i++){
			   $singlerecod = explode("</td>",$dataArr[$i]);
			   $Data = array();
			   $Data['updateon'] = date('Y-m-d H:i',strtotime(str_replace(array('<td>','<br>','.'),array('','','-'),$singlerecod[0])));
			   $Data['location'] = str_replace(array('<td>','<br>'),array('',''),$singlerecod[1]);
			   $Data['status'] = str_replace(array('<td>','<br>'),array('',''),$singlerecod[2]);
			   $finalDataArr[] = $Data;
			}
		}
	 }    
	return $this->getOurTracking($data,$finalDataArr);
  }
  
   public function DPDPaketshopTracetrack($data=array()){
   $html = commonfunction::file_contect($data['tracking_link']);
	preg_match('#<table class="alternatingTable" width="100%">[^>]+>(.+?)</table>#ims',$html, $matches);
    $arrayMatch = array('<tr>','<tr >','<tr class="even">');
	$arrayreplace = array('<tr>','<tr>','<tr>');
	$finalDataArr = array();
	  if(!empty($matches[0])){ 
		$dataArr = commonfunction::explode_string(str_replace($arrayMatch,$arrayreplace,$matches[0]),'<tr>');
		if(is_array($dataArr)){
			for($i=1;$i<count($dataArr);$i++){
			   $singlerecod = commonfunction::explode_string($dataArr[$i],"</td>");
			   $Data = array();
			   $date_time = commonfunction::trimString(commonfunction::stringReplace(array('<td>','<br>'),array('',''),$singlerecod[0]));
			   
			   $explode_date = commonfunction::explode_string($date_time, ' ');
			  
			   $timestring = $explode_date[1];
			   
			   $datecheck = commonfunction::explode_string($explode_date[0],'.');
			   
			   if(!empty($datecheck[2]) && !empty($datecheck[1]) && !empty($datecheck[0]))
		       {
			     $date = $datecheck[2].'-'.$datecheck[1].'-'.$datecheck[0].' '.$timestring;
			   }
			   else{
			       $datecheck = commonfunction::explode_string($explode_date[0],'/');
				   $date = $datecheck[2].'-'.$datecheck[1].'-'.$datecheck[0].' '.$timestring;
			   }
			   $Data['updateon'] = date('Y-m-d H:i',strtotime($date));
			   $city_country = commonfunction::trimString(commonfunction::stringReplace(array('<td>','<br>'),array('',''),$singlerecod[1]));
			   $city_code = commonfunction::explode_string(strip_tags($city_country),'(');
			   $city = preg_replace('/\d/', '', commonfunction::trimString($city_code[0]));
			   $Data['city'] = commonfunction::stringReplace('&nbsp;', '', strip_tags($city));
			   $Data['country']= preg_replace('/[()]/','',(isset($city_code[1]))?$city_code[1]:'');
			   
			   $status = commonfunction::stringReplace(array('<td>','<br>'),array('',''),strip_tags($singlerecod[2]));
			   $status1 = preg_replace('/\d/', '', commonfunction::trimString($status));
			   $Data['status'] = commonfunction::stringReplace('&nbsp;', '', $status1);
			   if(!empty($Data['country'])){
			   $finalDataArr[] = $Data;
			   }
			    
			}
			
		}
	 }
	 return $finalDataArr;
  }
  
   public function getGLSNLTrack($data=array()){
  	$barcode = ($data['forwarder_id']==4)?commonfunction::sub_string($data['barcode'],0,-1):$data['barcode'];
	$reference = array(
    'Credentials' => array('UserName' => '2760171944', 'Password' => '2760171944'),
    'RefValue' =>$barcode,'Parameters'=>array('ParamCode'=>'LangCode','ParamValue'=>'EN'));
	$finalDataArr = array();
	if($data['country_id']==9 && $data['addservice_id']!=125){
	    $select = $this->oldDb->select()
								->from(array('PT'=>PARCEL_TRACKING),array('status_location','status_date'))
								->joinleft(array('CT' =>COUNTRIES),"CT.country_id=PT.country_id",array("CT.cncode"))
								->joininner(array('SL'=>STATUS_LIST),"SL.error_id=PT.status_id",array('error_type','error_desc'))
								//->joinleft(array('SM'=>STATUS_MASTER),"SM.master_id=SL.master_id",array('status_name'))
								->where("PT.barcode_id='".$data['barcode_id']."'")
								->order("status_date ASC");
								//print_r($select->__toString());die;
		$results = $this->oldDb->fetchAll($select);
		foreach($results as $result){
		  $detail['city'] = $result['status_location'];
		  $detail['country'] = ($result['cncode']!='')?$result['cncode']:'NL';
		  $detail['updateon'] = $result['status_date'];
		  $detail['status'] = $result['error_desc'];
		  $finalDataArr[] = $detail;
		}
	}else{
	try{
	$client = new SoapClient("http://www.gls-group.eu/276-I-PORTAL-WEBSERVICE/services/Tracking/wsdl/Tracking.wsdl");
	$result = $client->GetTuDetail($reference);
	 if($result->ExitCode->ErrorCode==0){
 	   if(count($result->History)>1){ //echo "<pre>";print_r($result->History);die;
		    foreach($result->History as $history){
			$Data['updateon'] = date('Y-m-d H:i',strtotime($history->Date->Year.'-'.$history->Date->Month.'-'.$history->Date->Day.' '.$history->Date->Hour.':'.$history->Date->Minut));
			$Data['city'] =  commonfunction::stringReplace(array('\2C'),array(','),$history->LocationName);
			$trackcountry = $this->CheckinModelObj->getCountryDetail($history->CountryName,3);
			$Data['country'] =  $trackcountry['cncode'];
			if(trim($history->LocationName)==''){
			      $countrydetail = $this->CheckinModelObj->getCountryDetail($data['country_id']);
			     if(strtoupper($countrydetail['country_name']) == strtoupper($history->CountryName)){
				     $Data['city'] =  $data['rec_city'];
				 }else{ continue;}
			}
			$Data['status'] =  str_replace('GLS','Parcel',$history->Desc);
			$finalDataArr[] = $Data;
		   }	
		 }else{
			$Data['updateon'] = date('d/m/Y H:i',strtotime($result->History->Date->Year.'-'.$result->History->Date->Month.'-'.$result->History->Date->Day.' '.$result->History->Date->Hour.':'.$result->History->Date->Minut));
			$Data['city'] =  commonfunction::stringReplace(array('\2C'),array(','),$result->History->LocationName);
			$Data['country'] =  $result->History->CountryName;
			$Data['status'] =  commonfunction::stringReplace('GLS','Parcel',$result->History->Desc);
			$finalDataArr[] = $Data;
	 
		} 
		$finalDataArr = array_reverse($finalDataArr);
	}
	}catch(Exception $e){ } 
	}
	//echo "<pre>";print_r($finalDataArr);die;
	return $finalDataArr;
  }
  
   public function  PostatTracking($parcelinfo=array()){
        $finalDataArr = array();
	    /*$select = $this->oldDb->select()
								->from(array('EL'=>'parcelerrorlogs'),array('*'))
								->joininner(array('ED'=>'parcelerrorlists'),"ED.error_id=EL.error_id",array('error_desc'))
								->joinleft(array('CA'=>'city_austria'),"CA.postcode=EL.postcode",array('city'))
								->where("barcode='".$data['barcode']."'")
								->order("errorlog_id ASC");
								//print_r($select->__toString());die;
	     $parceltrackdata =  $this->oldDb->fetchAll($select);
		 foreach($parceltrackdata as $parceltrack){
				$Data['updateon'] = date('d/m/Y H:i',strtotime($parceltrack['timestamp']));
				$Data['location'] =  $parceltrack['city'].'(AT)';
				$Data['status'] =  $parceltrack['error_desc'];
				$finalDataArr[] = $Data; 
		 }*/
		 try{
		  $select = $this->oldDb->select()
									->from(array('PT'=>PARCEL_TRACKING),array('status_location','status_date'))
									->joinleft(array('CT' =>COUNTRIES),"CT.country_id=PT.country_id",array("CT.cncode"))
									->joininner(array('SL'=>STATUS_LIST),"SL.error_id=PT.status_id",array('error_type','error_desc'))
									//->joinleft(array('SM'=>STATUS_MASTER),"SM.master_id=SL.master_id",array('status_name'))
									->where("PT.barcode_id='".$parcelinfo['barcode_id']."'")
									->order("status_date ASC");
									//print_r($select->__toString());die;
			$results = $this->oldDb->fetchAll($select);
		foreach($results as $result){
		  $detail['city'] = $result['status_location'];
		  $detail['country'] = $result['cncode'];
		  $detail['updateon'] = $result['status_date'];
		  $detail['status'] = $result['error_desc'];
		  $finalDataArr[] = $detail;
		}
		return $finalDataArr;
	  }catch(Exception $e){
		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  return array();
		}					
  }
     
  public function BpostTracking($data=true){
      $url = "http://www.post.be/bis/parcels/".$data['tracenr_barcode']."";
	  $trackinglist = simplexml_load_file($url);
	  $finalDataArr = array();
	  if(!empty($trackinglist->parcel->trackingBarcode)){
			   $i=0;
			 foreach($trackinglist->parcel->deliveryStatus->status as $status){
				 if($data['country_id']==9){
					 $lang_status =  $status->status->nl;
				 }else if($data['country_id']==8){
					 $lang_status =  $status->status->fr; 
				 }else if($data['country_id']==15){
					 $lang_status =  $status->status->de; 
				 }else{
					 $lang_status =  $status->status->en;  
				 }
					$Data = array();
					$Data['updateon'] = date('Y-m-d H:i',strtotime(utf8_encode($status->dateTime)));
					$Data['status'] = utf8_decode(utf8_encode($lang_status));
					if($i==0){
						$depotAdd = $this->CheckinModelObj->getCustomerDetails($data['parent_id']);
						$Data['city'] = $depotAdd['city'];
						$Data['country'] = $depotAdd['cncode'];
					}elseif($Data['status']=='Item delivered'){
						$Data['city'] = utf8_encode($data['rec_city']);
						$Data['country'] = $data['rec_cncode'];
					}else{
					    $trackcountry = $this->CheckinModelObj->getCountryDetail(utf8_encode($status->location),3);
						$Data['city'] = utf8_encode($status->location);
						$Data['country'] = $trackcountry['cncode'];
					}						
					$finalDataArr[] = $Data;
					$i++;
			 }
	  }
	  return $finalDataArr;
  }
  
  public function DHLtracking($data=array()){
        $lang = (trim($data[COUNTRY_ID])==15)?'de':'en';
		$html = commonfunction::file_contect('http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang='.$lang.'&idc='.$data[TRACENR_BARCODE].'&rfn=&extendedSearch=true');
		$countrydetail = $this->CheckinModelObj->getCountryDetail($data['country_id']);
		
		$explodediv = explode('<div class="panel-body panel-table">',$html);
		$finalDataArr = array();
	 
		if(!empty($explodediv[1])){ 
			 preg_match('#<table class="table table-hover">(.+?)</table>#ims', $explodediv[1], $matches);
			 $Explodetr = explode('<tr>',$matches[0]);
			  if(!empty($Explodetr)){
				 $previusLoc = '';
				 for($i=2;$i<count($Explodetr);$i++){
					$Explodetd = explode('<td>',commonfunction::stringReplace(array('--',' data-label="Datum/Uhrzeit"',' data-label="Ort"',' data-label="Status"','<td class="">','<td data-label="Date/Time">','<td data-label="City">'),array('','','','','<td>','<td>','<td>'),$Explodetr[$i]));
					if($data['forwarder_id']==25){
					    $datestring = trim(substr(trim(strip_tags($Explodetd[1])),5,8));
					    $timestring = commonfunction::sub_string(trim(strip_tags($Explodetd[1])),13,6);
					}else{
						$datestring = commonfunction::sub_string(trim(strip_tags($Explodetd[1])),4,8);
						$timestring = commonfunction::sub_string(trim(strip_tags($Explodetd[1])),12,6);
					}
					$datecheck = explode('.',$datestring);
					$date = $datecheck[2].'-'.$datecheck[1].'-'.$datecheck[0].' '.$timestring;
					if($date!=''){
					$Data['updateon'] = date('Y-m-d H:i',strtotime($date));
					$location = preg_replace('/\s+/', '',strip_tags($Explodetd[2]));
					if($location!=''){
					  $previusLoc = $location;
					}
					$city = ($i>3)?$data['rec_city']:'Bad Bentheim';
					$finallocation = ($previusLoc!='')?$previusLoc:$city;
					$Data['city'] =  $finallocation;
					$Data['country'] = $countrydetail['cncode'];
					$Data['status'] =  trim(strip_tags($Explodetd[3]));
					$finalDataArr[] = $Data; 
					}
				 }
			  }
		}
		
	 return $finalDataArr; 
  }
  
  	/**
	 * Scrapping of trace track page for forwarder Anpost
	 **/
	public function Anposttracking($url,$user_id,$data=array()){
		$finalDataArr = array();
		if(!empty($data['local_barcode'])){
		     $html = file_get_contents("http://track.anpost.ie/TrackStatus.aspx?item=".$data['local_barcode']."&sender=".$data['local_barcode']);
			 preg_match('/<tr class="clearHeader">(.+?)<div id="barcode" style="display:table-cell;" title="Track the number entered below">/s', $html, $matches);
			 if(!empty($matches[0])){
			   $findhtml = array('<tr class="statusRow">','<tr class="statusAlternate">','<td width="30%">','<td width="20%">','<td align="center" width="20%">','<br/>');
			   $replacehtml = array('<tr>','<tr>','<td>','<td>','<td>',' ');
			   $explodebytr = explode('<tr>',str_replace($findhtml,$replacehtml,$matches[0]));
			   if(!empty($explodebytr)){
				for($i=1; $i<count($explodebytr)-2;$i++){
					$Explodetd = explode('<td>',str_replace('<td align="center">','<td>',$explodebytr[$i]));
					$Data['updateon'] = date('Y-m-d H:i',strtotime(strip_tags($Explodetd[3])));
					$Data['location'] =  trim(strip_tags($Explodetd[2]));
					$Data['status'] =  ucfirst(strtolower(strip_tags($Explodetd[1])));
					$finalDataArr[] = $Data; 
				 }
			   }
			 }
		}
		else{
		$html = file_get_contents($url);
		preg_match('#Item History(.+?)</table>#ims',$html, $matches);
		if(!empty($matches[0])){
		    $search = array(' class="clearHeader"',' class="statusRow"',' class="statusAlternate"');
			$trexplode = explode('<tr>',str_replace($search,'',$matches[0]));
			if(!empty($trexplode)){
				 for($i=2;$i<count($trexplode);$i++){
				    $tdsearch = array(' width="30%"',' align="center" width="20%"',' align="center"');
					$td_explode = explode('<td>',str_replace($tdsearch,'',$trexplode[$i]));
					$explode_time = explode('<br />',$td_explode[3]);
					$Data['updateon'] = date('Y-m-d H:i',strtotime(strip_tags(str_replace('<br/>',' ',$td_explode[3]))));
					$explode_location = explode('<br />',$td_explode[2]);
					$Data['location'] =  trim(str_replace(array('PARCEL CENTRE','MAIL CENTRE'),'',$explode_location[0])).'(IE)';
					$Data['status'] = ucfirst(strtolower(strip_tags($td_explode[1])));
					$finalDataArr[] = $Data; 
				}
			 }
		}
	  }
	    return $this->getOurTracking($data,array_reverse($finalDataArr));
  }
  
  public function Yodeltracking($data=array()){
   		 $barcode = ($data['forwarder_id']==29)?substr($data['barcode'],1):$data['barcode'];
		 $xml_data = '<?xml version="1.0" encoding="utf-8" ?>
			<GetTrackingHistoryRequest>
			<Authentication>
			<AccountID>PNL001</AccountID>
			<AccessCode>bed39f23a7b344da8735bdc7a1379260</AccessCode>
			</Authentication>
			<TransactionMessageID>Parcelnl Transaction Message</TransactionMessageID>
			<SearchType>ByTrackingNumber</SearchType>
			<SearchTerm>'.$barcode.'</SearchTerm>
			</GetTrackingHistoryRequest>';
		$finalDataArr = array();
		 try{
			$ch = curl_init('https://trackapi.parcelhub.net/v1/trackingservice/gettrackinghistory');
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$content = curl_exec($ch);
			curl_close($ch);
			$xml = simplexml_load_string($content);
			$json_encoded = json_encode($xml);
			$json = json_decode($json_encoded);
			if(!empty($json)){
			   if(isset($json->Packages->Package->PackageTrackingEvents->TrackingEvent)){
				  $events = $json->Packages->Package->PackageTrackingEvents->TrackingEvent;
				  foreach($events as $event){
						$Data = array();
						if(!is_object ($event->EventLocation)){
							$Data['updateon'] = date('Y-m-d H:i',strtotime(commonfunction::stringReplace('T',' ',$event->EventTimestamp)));
							$findArr = array('service centre','hub','at','home delivery','(',')',"&#39;",'depot','yodel','spa','HUB');
							$Data['city'] =  commonfunction::first_upper(commonfunction::trimString(commonfunction::stringReplace($findArr,'',commonfunction::lowercase($event->EventLocation))));
							$Data['country'] = 'GB';
							$Data['status'] =  commonfunction::trimString($event->EventDescription);
							$finalDataArr[] = $Data;
					  }	
				  }
			   }
			}
		}catch(Exception $e){
	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	   }
		return array_reverse($finalDataArr);
  }
  
  public function Hermestracking($data=array()){ //echo "<pre>"; print_r($data); die;
   		 $barcode = ($data['forwarder_id']==29)?substr($data['barcode'],1):$data['barcode'];
		 $xml_data = '<?xml version="1.0" encoding="utf-8" ?>
			<GetTrackingHistoryRequest>
			<Authentication>
			<AccountID>PNL001</AccountID>
			<AccessCode>bed39f23a7b344da8735bdc7a1379260</AccessCode>
			</Authentication>
			<TransactionMessageID>Parcelnl Transaction Message</TransactionMessageID>
			<SearchType>ByTrackingNumber</SearchType>
			<SearchTerm>'.$barcode.'</SearchTerm>
			</GetTrackingHistoryRequest>';
		$finalDataArr = array();
		 try{
			$ch = curl_init('https://trackapi.parcelhub.net/v1/trackingservice/gettrackinghistory');
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$content = curl_exec($ch);
			curl_close($ch);
			$xml = simplexml_load_string($content);
			$json_encoded = json_encode($xml);
			$json = json_decode($json_encoded);
			if(!empty($json)){
			   if(isset($json->Packages->Package->PackageTrackingEvents->TrackingEvent)){
				  $events = $json->Packages->Package->PackageTrackingEvents->TrackingEvent;
				  foreach($events as $event){
						$Data = array();
						if($event->EventCategoryID!=7){
							$location = (!is_object($event->EventLocation))?$event->EventLocation:'92';
							$Data['updateon'] = date('Y-m-d H:i',strtotime(str_replace('T',' ',$event->EventTimestamp)));
							$findArr = array('service centre','hub','at','home delivery','(',')',"&#39;",'depot','yodel','spa','HUB');
							$location = ucfirst(trim(str_replace($findArr,'',$location)));
							$Data['city'] =  ((!ctype_digit($location) && $location!='')?$location:$data['rec_city']);
							$Data['country'] = 'GB';
							$Data['status'] =  trim($event->EventDescription);
							$finalDataArr[] = $Data;
					  }	
				  }
			   }
			}
		}catch(Exception $e){
	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	   }
	   return array_reverse($finalDataArr);
  }
 /**
	*Scrapping track of WWPL
	**/ 
  public function WWPLTracking($url,$data=array()){
		 $html = file_get_contents($url);
		 preg_match('#<table id="Table2" border="0" cellpadding="1" cellspacing="1" width="100%">(.+?)</table>#ims', $html, $matches);
		if(!empty($matches[0])){
			 $search_arr = array('<tr class="txtCabeceraTabla">','<tr class="txtCabeceraTabla" align="center">',"<td class='txtDescripcionTabla'>",'align="left"',"<td class='"); 
		  $replacearra = array('<tr>','<tr>','<td>',"align='left'",'<td>');
			 $Explodetr = explode('<tr>',str_replace($search_arr,$replacearra,$matches[0]));
			  if(!empty($Explodetr)){
				 for($i=1;$i<count($Explodetr);$i++){  
					$Explodetd = explode('<td>',$Explodetr[$i]);
					$Data['updateon'] = trim(preg_replace('/\s+/', '',strip_tags($Explodetd[1])));
					$status = trim(strip_tags($Explodetd[2]));
					$Data['status'] =  utf8_encode(trim(substr($status,strpos($status,'>')+1)));
					$finalDataArr[] = $Data; 
				 }
			  }
		} 
		return $this->getOurTracking($data,$finalDataArr);
  }
  /**
	*Scrapping track of Correos
	**/ 
  public function CorreosTrackTrace($data=array()){ 
      $html = file_get_contents("http://aplicacionesweb.correos.es/localizadorenvios/track.asp?numero=".$data['barcode']."&idiomaCorreos=en_GB");
	  preg_match('/<table id="Table2" border="0" cellpadding="1" cellspacing="1" width="100%">(.+?)<div class="txtNormal" style="width:581px;" align="left">/s', $html, $matches);
	 $finalDataArr = array();
	  if(!empty($matches[0])){
			$findhtml = array("<tr class='txtCabeceraTabla'>",'<tr class="txtCabeceraTabla" align="center">');
			$replacehtml = array('<tr>','<tr>');
			$explodebytr = commonfunction::explode_string(commonfunction::stringReplace($findhtml,$replacehtml,$matches[0]),'<tr>');
			
		    for($i=3; $i<count($explodebytr);$i++){
		        $Explodetd = explode('<td>',commonfunction::stringReplace(array('align="left"',"<td class='
					txtContenidoTabla'>","<td class='txtDescripcionTabla'>"),array('<td>','','<td>'),$explodebytr[$i]));
				$datecheck1 = commonfunction::trimString(strip_tags($Explodetd[1]));
				$datecheck = commonfunction::explode_string($datecheck1,'/');
			    $date = $datecheck[2].'-'.$datecheck[1].'-'.$datecheck[0];
			    $Data['updateon'] = $date;
				
				$Data['status'] =  utf8_encode(commonfunction::trimString(commonfunction::stringReplace('>','',strip_tags($Explodetd[2]))));
				if($data['forwarder_id']==45){
				$Data['city'] =  ($i<5 && commonfunction::trimString($Data['status'])!='In the delivery process' && commonfunction::trimString($Data['status'])!='Delivered')?"Barcelona":$data['rec_city'];
				if($i<5 && commonfunction::trimString($Data['status'])!='In the delivery process' && commonfunction::trimString($Data['status'])!='Delivered'){
					$Data['country'] = "ES";
				}else{
				 	$country_detail = $this->CheckinModelObj->getCountryDetail($data['country_id']);
					$Data['country'] = $country_detail['cncode'];
				}  
				}else{
				  $Data['city'] =  ($i<5 && commonfunction::trimString($Data['status'])!='In the delivery process' && commonfunction::trimString($Data['status'])!='Delivered')?"Madrid":$data['rec_city'];
				  if($i<5 && commonfunction::trimString($Data['status'])!='In the delivery process' && commonfunction::trimString($Data['status'])!='Delivered'){
				  	 $Data['country'] = "ES";
				  }else{
				      $country_detail = $this->CheckinModelObj->getCountryDetail($data['country_id']);
				  	  $Data['country'] = $country_detail['cncode'];
				  }
				}
				$finalDataArr[] = $Data; 
		  }
	  }
	  return $finalDataArr;
}
/**
	*Scrapping track of GlsIT
	**/
public function GlsITTrackTrace($data=array()){
	$finalDataArr = array();
	if($data['parent_id']==3792){
	  $url = "https://wwwdr.gls-italy.com/XML/get_xml_track.php?locpartenza=BZ&bda=".$data['barcode']."&CodCli=223";
	}else{
	   $url = "https://wwwdr.gls-italy.com/XML/get_xml_track.php?locpartenza=BZ&bda=".$data['barcode']."&CodCli=2523";      
	}
	if($url!=''){
	  $html = file_get_contents($url);
	  $html = str_replace(array('<![CDATA[',']]>'), array('|',''), $html);
	   if(!empty($html) && strlen($html)>100){
		$xml = simplexml_load_string($html, "SimpleXMLElement", LIBXML_NOCDATA);
		$json = json_encode($xml);
		$jsonarraydata = json_decode(str_replace('|','',$json),TRUE);
		if(!empty($jsonarraydata['SPEDIZIONE']['TRACKING'])){
			foreach($jsonarraydata['SPEDIZIONE']['TRACKING']['Data'] as $key=>$date){
					$exploded_date = explode('/',$date);
					$date = $exploded_date[2].'-'.$exploded_date[1].'-'.$exploded_date[0].' '.str_replace('.',':',$jsonarraydata['SPEDIZIONE']['TRACKING']['Ora'][$key]);
					$Data['updateon'] = date('Y-m-d H:i',strtotime($date));
					$findArr = array('HUB');
					$ReplaceArr = array('');
					$Data['city'] =  str_replace($findArr,$ReplaceArr,$jsonarraydata['SPEDIZIONE']['TRACKING']['Luogo'][$key]);
					$Data['country'] = 'IT';
					$Data['status'] =  $jsonarraydata['SPEDIZIONE']['TRACKING']['Stato'][$key];
					$finalDataArr[] = $Data;
			}
		}
	  }	
	 }	
	 return array_reverse($finalDataArr);
}
/**
* get Colissimo forwarder Tracking
**/
public function Colissimotracking($data=array()){
	$html = file_get_contents("http://www.laposte.fr/particulier/outils/suivre-vos-envois?code=".$data['barcode']."");
	preg_match('#<table class="table table-bordered table-bordered-inverse hidden-xs free-col-size" summary="">(.+?)</table>#ims', $html, $matches);
	 $finalDataArr = array();
	if(!empty($matches[0])){
		$Explodetr = explode('<tr>',str_replace("<tr class='last'>",'<tr>',$matches[0]));
		if(!empty($Explodetr)){
			 for($i=2;$i<count($Explodetr);$i++){  
				$Explodetd = explode('</td>',$Explodetr[$i]);
				$updateon = explode('/',trim(preg_replace('/\s+/', '',strip_tags($Explodetd[0]))));
				$Data['updateon'] = $updateon[2].'-'.$updateon[1].'-'.$updateon[0];
				$find = array('Centre Courrier 56','Plate-forme Ouest','Plate-forme Nord','Plate-forme Est','Plate-forme Rhône-Alpes','Centre Courrier 13','Centre Courrier 01','Centre Courrier 29','Agence Bouches-du-Rhône','Agence Paris','Centre Courrier 66','Bureau de Poste Perpignan','Bureau de Poste PerpignanHoraires et adresse','Plateforme Colis','Centre Courrier 02','Plateforme Colis');
				$replace = array('Mullhouse','Paris','Lille','Paris','Lyon','Toulon','Grenoble','Brittany','Bouches du rhone','Paris','Perpignan','Perpignan','Perpignan','Laon','Paris');
				$Data['city'] =  commonfunction::stringReplace($find,$replace,commonfunction::utf8Decode(trim(strip_tags($Explodetd[2]))));
				$Data['country'] =  'FR';
				$Data['status'] =  trim(strip_tags($Explodetd[1]));
				$finalDataArr[] = $Data; 
			 }
			 $finalDataArr = array_reverse($finalDataArr);
		  }
	}
	return $finalDataArr;
 }
 
public function RDPAGttracking($data=array()){
		$finalDataArr = array();
	if($data['country_id']==17 && $data['forwarder_id']==36){	
		$url = "https://service.post.ch/EasyTrack/submitParcelData.do?formattedParcelCodes=".$data['barcode']."+&from_directentry=True&directSearch=false&p_language=en&VTI-GROUP=1&lang=de&service=ttb";
		$html = file_get_contents($url);
		preg_match('#<table class="events_view fullview_tabledata" cellpadding="0" cellspacing="0">(.+?)</table>#ims',$html, $matches);
		if(!empty($matches[0])){
		    $search = array(' class=""',' class="row_fullview_tablerow_grey"');
			$trexplode = explode('<tr>',str_replace($search,'',$matches[0]));
			if(!empty($trexplode)){
				 for($i=1;$i<count($trexplode);$i++){
				    $tdsearch = array(' class="event_date"',' class="event_time"',' class="event_event"',' class="event_city"');
					$td_explode = explode('<td>',str_replace($tdsearch,'',$trexplode[$i]));
					$Data['updateon'] = trim(str_replace('.','/',substr(strip_tags($td_explode[1]),3))).' '.trim(strip_tags($td_explode[2]));
					$expl_location =  explode(' ',trim(strip_tags($td_explode[4])));
					$country = ($i==1)?'(DE)':'(CH)';
					$Data['location'] =  utf8_encode(str_replace('DEFRAA','Frankfurt',trim(!empty($expl_location[1])?$expl_location[1]:$expl_location[0]))).$country;
					if($Data['location'] =='Netherlands(DE)' || $Data['location'] =='Netherlands(CH)'){
					  $Data['location'] = 'Enschede(NL)';
					}
					$explode_status = explode('</span>',$td_explode[3]);
					$status = (!empty($explode_status[1]))?$explode_status[1]:$explode_status[0];
					$Data['status'] = trim(strip_tags($status));
					$finalDataArr[] = $Data; 
				}
				
			 }
		}
	 }elseif($data['country_id']==18 && $data['forwarder_id']!=52){
	    $content = json_decode(file_get_contents('https://tracking.bring.com/tracking.json?q='.$data['barcode'].''));
		$events = isset($content->consignmentSet[0]->packageSet[0]->eventSet)?$content->consignmentSet[0]->packageSet[0]->eventSet:array();
		foreach($events as $event){ //echo "<pre>";print_r($event);die;
				$Data  = array();
				$Data['updateon'] = date('Y-m-d H:i',strtotime($event->displayDate.' '.$event->displayTime));
				if(!empty($event->city)){
					$Data['location'] =  $event->city.'('.$event->countryCode.')';
				}else{
				   $Data['location'] =  $data['rec_city'].'('.$this->countryCode($data['country_id']).')';
				}
				$Data['status'] =  trim($event->status);
				if(isset($event->recipientSignature->name)){
				 $Data['receivedby'] =  trim($event->recipientSignature->name);
				}
				$finalDataArr[] = $Data; 
		}
		$finalDataArr = array_reverse($finalDataArr);
	 }	
	 elseif($data['country_id']==19){
	    $apireponse = file_get_contents('https://api2.postnord.com/rest/shipment/v1/trackandtrace/findByIdentifier.json?id='.$data['barcode'].'&locale=en&apikey=d98ea3662cd414b75c7d9bd1883c316c');
		$tracking = json_decode($apireponse);
		if(isset($tracking->TrackingInformationResponse->shipments) && !empty($tracking->TrackingInformationResponse->shipments)){
			$events = $tracking->TrackingInformationResponse->shipments[0]->items[0]->events;
			$status = $tracking->TrackingInformationResponse->shipments[0]->items[0]->status;
			foreach($events as $event){ 
					$Data  = array(); 
					$Data['updateon'] = date('Y-m-d H:i',strtotime($event->eventTime));
					if($event->location->locationId=='DE'){
						$Data['location'] =  'Frankfurt(Germany)';
					}elseif($event->location->locationId=='SEMMAA'){
						$Data['location'] =  'Malmo(Sweden)';
					}else{
					   $Data['location'] =  $event->location->city.'('.$event->location->country.')';
					}
					$Data['status'] =  trim($event->eventDescription);
					$finalDataArr[] = $Data; 
			}
			if($data['delivery_status']=='0' && $status=='DELIVERED'){
			  $this->updateDelivred(array('updateon'=>str_replace('T',' ',$tracking->TrackingInformationResponse->shipments[0]->items[0]->deliveryDate)), $data); 
			}
		}
	 }
	 elseif($data['country_id']==172){
	    $barcode= $data['barcode'];
				$trackdata = file_get_contents('http://api.nzpost.co.nz/tracking/track?license_key=3dc739f0-3114-0134-8d49-06c980f2668f&&user_ip_address=144.76.156.18&&tracking_code='.$barcode.'&&format=json');
		$trackdecode = json_decode($trackdata);
		$finalDataArr = array();
		if(!isset($trackdecode->$barcode->error_code)){
		   foreach($trackdecode->$barcode->events as $event){
					$Data['updateon'] = date('Y-m-d H:i',strtotime(str_replace('T',' ',substr($event->datetime,0,16))));
					$Data['location'] =  $data['rec_city'].'(NZ)';
					$Data['status'] =  $event->description;
					$finalDataArr[] = $Data;
					if($data['delivery_status']=='0' && $Data['status']=='Delivery Complete'){
					  $this->updateDelivred($Data, $data); 
					}
		   }
		}
	 }
	 else{
	    $select = $this->oldDb->select()
								->from(array('EL'=>'parcelerrorlogs'),array('*'))
								->joininner(array('ED'=>'parcelerrorlists'),"ED.error_id=EL.error_id",array('error_desc'))
								->joinleft(array('CT'=>'countries'),"CT.country_id=EL.country_id",array('cncode'))
								->where("barcode='".$data['barcode']."' AND EL.	postcode!=''")
								->order("timestamp ASC");
								//print_r($select->__toString());die;
	     $parceltrackdata =  $this->oldDb->fetchAll($select);
		 foreach($parceltrackdata as $parceltrack){
				$Data['updateon'] = date('Y-m-d H:i',strtotime($parceltrack['timestamp']));
				$Data['location'] =  $parceltrack['postcode'].'('.$parceltrack['cncode'].')';
				$Data['status'] =  $parceltrack['error_desc'];
				$finalDataArr[] = $Data; 
		 }
	 }
		return $this->getOurTracking($data,$finalDataArr);
  }
  
   public function BRTtracking($data=array()){
		  $finalDataArr=array();
		  $contents =file_get_contents("https://as777.brt.it/vas/sped_ricdocmit_load.hsm?docmit=".substr($data['tracenr'],0,8)."&ksu=1071071&lang=en");
		  preg_match('#<label id="diz_387" title="Evento">Evento</label>(.+?)</table>#ims', $contents, $matches);
		  $findhtml = array('<tr>','<tr class="riga_pari">','<td style="white-space: nowrap; width: 1%">','<td style="text-align: left; width: 35%">','<td style="text-align: left;">');
		  $replacehtml = array('<tr>','<tr>','<td>','<td>','<td>');
		  $explodebytd = explode('<td>',str_replace($findhtml,$replacehtml,$matches[0]));
			 $k=0;
			 for($j=1;$j<=(count($explodebytd)-1)/4;$j++){
			 $address =preg_replace('/\s+/', ' ',strip_tags($explodebytd[$k+3]));
			$finalDataArr[$j]['updateon']= date('Y-m-d H:i',strtotime(preg_replace('/\s+/', ' ',strip_tags($explodebytd[$k+1].' '.$explodebytd[$k+2]))));
			$finalDataArr[$j]['city']=substr($address,0,strpos($address,'('));
			$finalDataArr[$j]['country'] = 'IT';
			$finalDataArr[$j]['status']=preg_replace('/\s+/', ' ',strip_tags($explodebytd[$k+4]));
			$k=$k+4;
		   }
     return array_reverse($finalDataArr);
    }
 
  public function MondialRelaytracking($data=array()){
   try{
		include ROOT_PATH."/application/models/MondialRelayTracking.php";
		$MR_WebSiteId = "NLPARCEL";
		$MR_WebSiteKey = "UMs9tnJM";
		$client = new MondialRelayTracking("http://api.mondialrelay.com/Web_Services.asmx?WSDL", true);
		$client->soap_defencoding = 'utf-8';
		$params = array('Enseigne' =>$MR_WebSiteId,'Expedition'=>$data['tracenr'],'Langue' => "FR");
		$code = commonfunction::implod_array($params, "");
		$code .= $MR_WebSiteKey;
		$params["Security"] = commonfunction::uppercase(md5($code));
		$result = $client->call('WSI2_TracingColisDetaille',$params,'http://api.mondialrelay.com/','http://api.mondialrelay.com/WSI3_PointRelais_Recherche');
		$finalDataArr = array();
		if(!empty($result['WSI2_TracingColisDetailleResult']['Tracing']['ret_WSI2_sub_TracingColisDetaille'])){
			foreach($result['WSI2_TracingColisDetailleResult']['Tracing']['ret_WSI2_sub_TracingColisDetaille'] as $trackings){
				if(!empty($trackings['Date']) && !empty($trackings['Emplacement'])){
					$exploded_date = commonfunction::explode_string($trackings['Date'], '/');
					$date = $exploded_date[2].'-'.$exploded_date[1].'-'.$exploded_date[0].' '.$trackings['Heure'];
					$Data['updateon'] = date('Y-m-d H:i',strtotime($date));
					$Data['city'] =  commonfunction::trimString(commonfunction::stringReplace(array('HUB'),'',$trackings['Emplacement']));
					$Data['country'] = 'FR';
					$Data['status'] =  $trackings['Libelle'];
					$finalDataArr[] = $Data; 
				 }	
			}	
		}
	 }catch(Exception $e){
 		$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());		
     }
	 
     return $finalDataArr;
  }

/**
* get Deburan forwarder Tracking
**/
public function DeburanTracking($newdata=array()){
    $client = new SoapClient("https://mijnburen.deburen.nl/soap/pakketservice/service?wsdl", array('soap_version' => SOAP_1_2));
     //Auth Data
     $private_key = 'tapepH7DA5Up';
     $public_key = microtime();
     $site_id    = 7;
     $hash     = hash('sha256',$public_key.$site_id.$private_key);
     //Create Data
     $data = (object)array();
     $data->auth->site_id = $site_id;
     $data->auth->hash = $hash;
     $data->auth->public_key = $public_key;
     //$data->test_mode = 1;
     $lockerType=array();
     $i=0;
     
     $order = new OrderData();

     $order->external_id =3122152;
     $data->orders = array($order); 
     $response2 = $client->getOrderStatus($data);
     $finalDataArr = array();
     foreach($response2->orders as $key=>$val){
		 $location=$val->parcels[$key]->location_title;
		 $Data['updateon']= date('Y-m-d H:i',strtotime($val->parcels[$key]->date_modified));
		 $Data['status']=$val->parcels[$key]->status;
		 $Data['location'] =substr($location, 0, strpos($location, '|')).'(NL)';
		 $Data['barcode_receiver'] = $val->parcels[$key]->barcode_receiver;
		 $finalDataArr[] = $Data;
 }
 return $this->getOurTracking($newdata,$finalDataArr);
}

 public function OminvaTracking($data=array()){ 
       /*$url = 'http://post-tracking.com/'.$data['barcode'].'';
		$html = file_get_contents($url);
preg_match('#<table class="table table-bordered">(.+?)</table>#ims', $html, $matches);
$finalDataArr = array();
	if(!empty($matches[0])){ 
		$Explodetr = explode('<tr>',$matches[0]);
		if(!empty($Explodetr)){
		    $count = 1;
			 for($i=2;$i<count($Explodetr);$i++){  
				$Explodetd = explode('</td>',$Explodetr[$i]); 
				$Data['updateon'] = date('Y-m-d H:i',strtotime(trim(preg_replace('/\s+/', '',strip_tags($Explodetd[0])))));
				if($count==1){
				  $Data['location'] =  'frankfurt(DE)';
				}elseif($count==2){
				  $Data['location'] =  'Tallinn(Estonia)';
				}else{
				  $Data['location'] =  trim(strip_tags($Explodetd[2]));
				}
				$Data['status'] =  utf8_encode(trim(strip_tags($Explodetd[1])));
				$finalDataArr[] = $Data; 
				$count++;
			 }
		  }
	}*/
	$select = $this->oldDb->select()
								->from(array('EL'=>'parcelerrorlogs'),array('*'))
								->joininner(array('ED'=>'parcelerrorlists'),"ED.error_id=EL.error_id",array('error_desc'))
								->joinleft(array('CT'=>'countries'),"CT.country_id=EL.country_id",array('cncode'))
								->where("barcode='".$data['barcode']."' AND EL.	postcode!=''")
								->order("timestamp ASC");
								//print_r($select->__toString());die;
	     $parceltrackdata =  $this->oldDb->fetchAll($select);
		 foreach($parceltrackdata as $parceltrack){
				$Data['updateon'] = date('Y-m-d H:i',strtotime($parceltrack['timestamp']));
				$Data['location'] =  $parceltrack['postcode'].'('.$parceltrack['cncode'].')';
				$Data['status'] =  $parceltrack['error_desc'];
				$finalDataArr[] = $Data; 
		 }
		return $this->getOurTracking($data,$finalDataArr);
 }
 
 public function generalTracking($parcelinfo){
      $trackingData = array();
	 try{
	  $select = $this->oldDb->select()
								->from(array('PT'=>PARCEL_TRACKING),array('status_location','status_date'))
								->joinleft(array('CT' =>COUNTRIES),"CT.country_id=PT.country_id",array("CT.cncode"))
								->joininner(array('SL'=>STATUS_LIST),"SL.error_id=PT.status_id",array('error_type','error_desc'))
								//->joinleft(array('SM'=>STATUS_MASTER),"SM.master_id=SL.master_id",array('status_name'))
								->where("PT.barcode_id='".$parcelinfo['barcode_id']."'")
								->order("status_date ASC");
								//print_r($select->__toString());die;
		$results = $this->oldDb->fetchAll($select);
		foreach($results as $result){
		  $detail['city'] = $result['status_location'];
		  $detail['country'] = $result['cncode'];
		  $detail['updateon'] = $result['status_date'];
		  $detail['status'] = $result['error_desc'];
		  $trackingData[] = $detail;
		}
		return $trackingData;
	  }catch(Exception $e){
		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  return array();
		}	
 } 
 public function GLSFreightUrl($barcode){
	       $daraArr = array('&'=>'01','8'=>'11','I'=>'21','S'=>'31','='=>'02','9'=>'12','J'=>'22','T'=>'32','0'=>'03','A'=>'13','K'=>'23','U'=>'33','1'=>'04','B'=>'14','L'=>'24','V'=>'34','2'=>'05','C'=>'15','M'=>'25','W'=>'35','3'=>'06','D'=>'16','N'=>'26','X'=>'36','4'=>'07','E'=>'7','O'=>'27','Y'=>'37','5'=>'08','F'=>'18','P'=>'28','Z'=>'38','6'=>'09','G'=>'19','Q'=>'29','7'=>'10','H'=>'20','R'=>'30');
			$mainurl = "http://services-freight.gls-netherlands.com/pgm/wwwdspenvc.pgm?REQUEST=2413261933170226013331173021160228133015172426240128133131351602301524041005110405011527332632303702";
			$countrycode = "262401";
			$labling = "24131417240402";
			$val = '';
			 for ($i = 0; $i < strlen($barcode); $i++) {
                $val .= $daraArr[$barcode{$i}];
			}//print_r($val);die;
			$mainurl = $mainurl.$countrycode.$labling.$val;
		return $mainurl;
	
	 }
	 
	public function GLSNLUrl($barcode){//echo $tracenr.','.$customer_no.',';
	        $cNPDVerladerNummer =  substr($barcode,0,8);
			$cUwOrderNummer     = $barcode;  
			$nUwEncryptieCode   = "712";
			
			$nControleGetal  = $this->NPDInternetChecksum($cNPDVerladerNummer.$cUwOrderNummer.'Verlader');
			$nControleGetal += $nUwEncryptieCode;
			
			$cURL  = "http://services.gls-netherlands.com/tracking/ttlink.aspx?";
			$cURL .= "NVRL=".$cNPDVerladerNummer ;
			$cURL .= "&VREF=";
			$cURL .= "&NDOC=".$barcode;
			$cURL .= "&REDIRTO=Verlader";
			$cURL .= "&TAAL=NL";
			$cURL .= "&CHK=".$nControleGetal;
		return $cURL;
	  }	 
	public function NPDInternetChecksum($cData) {
	  $nPos = 1;
	  $nChk = '';
	  $nAsc = '';
	  for ($i = 0; $i < strlen($cData); $i++) {
	   // asci waarde bepalen
	   $nAsc = ord(substr($cData, $i, 1));
	   if ($nAsc >= 65 && $nAsc <= 90)
		$nAsc = $nAsc - 64;
	   elseif ($nAsc >= 48 && $nAsc <= 57)
		$nAsc = $nAsc - 21;
	   $nChk = $nChk + (($i + 1) * $nAsc);
	  }
	  return $nChk;
	 } 
	 
	 public function UPSparceltrack($data) { //print_r($shipmentdata);die;
		$finalDataArr = array();
		try
		{
		 $client = new SoapClient("http://dpost.be/script_manually/wsdl/Track_1.wsdl" , array('soap_version' => '1.0','trace' => 1));
		$usernameToken['Username'] = "logicparcel";
		$usernameToken['Password'] = "Let25Dance";
		$serviceAccessLicense['AccessLicenseNumber'] = "3CB775C3DD8A73B6";
		$upss['UsernameToken'] = $usernameToken;
		$upss['ServiceAccessToken'] = $serviceAccessLicense;
		$req['RequestOption'] = '15';
		$tref['CustomerContext'] = '';
		$req['TransactionReference'] = $tref;
		$request['Request'] = $req;
		$request['InquiryNumber'] = $data[TRACENR_BARCODE];
		$request['TrackingOption'] = '02';
		$header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0','UPSSecurity',$upss);
		$client->__setSoapHeaders($header);
		//get response
		$resp = $client->__soapCall('ProcessTrack' ,array($request));
			if($resp->Response->ResponseStatus->Code==1){
				if(!empty($resp->Shipment->Package->Activity) && count($resp->Shipment->Package->Activity)>1){
				   foreach($resp->Shipment->Package->Activity as $activities){
					   $Data = array();
						$Data['updateon'] = date('Y-m-d H:i',strtotime($activities->Date.$activities->Time));
						$Data['city'] =  isset($activities->ActivityLocation->Address->City)?$activities->ActivityLocation->Address->City:'';
						$Data['country'] =  $activities->ActivityLocation->Address->CountryCode;
						$Data['status'] =  str_replace('UPS','',$activities->Status->Description);
						/*if(isset($shipmentdata['scrap'])){
						  $cityname =  (($activities->ActivityLocation->Address->City=='APELDOORN')?'Amsterdam':($activities->ActivityLocation->Address->City=='KOELN (COLOGNE)'?'Frankfurt':($activities->ActivityLocation->Address->City=='TESSERA'?'Rome':$activities->ActivityLocation->Address->City)));
						  $Data['location'] =  $cityname.'('.$activities->ActivityLocation->Address->CountryCode.')';
						  if(strstr($activities->Status->Description,'Delivery') || strstr($activities->Status->Description,'Delivered')){
						     continue;
						  }
						}*/
						if(trim($Data['status'])!='Order Processed: Ready for'){
						  $finalDataArr[] = $Data; 
						}
					}
					$finalDataArr = array_reverse($finalDataArr);
				}else{
				        $Data = array();
						$Data['updateon'] = date('Y-m-d H:i',strtotime($resp->Shipment->Package->Activity->Date.$resp->Shipment->Package->Activity->Time));
						$Data['city'] =  isset($resp->Shipment->Package->Activity->ActivityLocation->Address->City)?$resp->Shipment->Package->Activity->ActivityLocation->Address->City:'';
						$Data['country'] =  $resp->Shipment->Package->Activity->ActivityLocation->Address->CountryCode;
						$Data['status'] =  str_replace('UPS','',$resp->Shipment->Package->Activity->Status->Description);
						$finalDataArr[] = $Data;
				}
			}
	}
  	catch(Exception $ex){} 
	return $finalDataArr;
   }
   
    public function parcelErrorModification($data){ 
	    global $objSession;
		if(trim($this->getData['rec_name'])==""){
			$objSession->errorMsg = 'Receiver name should not be blanck';
		}
		elseif(trim($this->getData['rec_street'])==""){
			$objSession->errorMsg = 'Street should not be blanck';
		}
		elseif(trim($this->getData['rec_zipcode'])==""){
			$objSession->errorMsg = 'Zipcode should not be blanck';
		}
		elseif(trim($this->getData['rec_city'])==""){
			$objSession->errorMsg = 'City should not be blanck';
		}
		elseif(trim($this->getData['rec_phone'])==""){
			$objSession->errorMsg = 'Phone Number should not be blanck';
		}
		elseif(strlen(trim($this->getData['rec_phone']))<7){
			$objSession->errorMsg = 'Phone Number should be correct';
		}
		else {
		    $formData = $this->getData;
		    $modifiedAddress = $this->ParcelOldAddress($this->getData['barcode_id']);
			if(empty($modifiedAddress)){
			     $logID = $this->insertInToTable(WRONG_ADDRESS_MODIFICATION,array($this->getData));
				 $updatearray = array(RECEIVER=>$this->getData['rec_name'],STREET=>$this->getData['rec_street'],
								   STREETNR=>$this->getData['rec_streetnr'],CONTACT=>$this->getData['rec_contact'],
								   ADDRESS=>$this->getData['rec_address'],STREET2=>$this->getData['rec_street2'],
								   ZIPCODE=>$this->getData['rec_zipcode'],CITY=>$this->getData['rec_city'],
								   PHONE=>$this->getData['rec_phone']);
								   
				$this->oldDb->update(SHIPMENT,array_filter($updatearray),"shipment_id='".$this->getData[SHIPMENT_ID]."'");
				$this->oldDb->update(SHIPMENT_BARCODE,array('error_status'=>'0'),"barcode_id='".$this->getData[BARCODE_ID]."'");
				$this->parcelinformation();
				if($this->getData['delivery_status']=='0' && $this->getData['forwarder_detail']['forwarder_email']!=''){
					$mailOBj = new Zend_Custom_MailManager();
					$email_text = '';
					$email_text .= '<table><tr><td colspan="2">Dear Forwarder. This is an automatic text from the Logicparcel server. For below here the changed and confirmed adres of the receiver. Please deliver again to the adres below: </td></tr>';
					$email_text .= '<tr><td colspan="2">&nbsp;</td></tr>';
					$email_text .= '<tr><td colspan="2"><b>Tracking Number : </b></td></tr>';
					$email_text .= '<tr><td><br></td><td>'.$this->getData[TRACENR_BARCODE].'</td></tr>';
					$email_text .= '<tr><td colspan="2"><b>Receiver Name : </b></td></tr>';
					$email_text .= '<tr><td><br></td><td>'.utf8_decode($this->getData['rec_name']).' '.utf8_decode($this->getData['rec_contact']).'</td></tr>';
					$email_text .= '<tr><td colspan="2"><b>Receiver Address Information : </b></td></tr>';
					$email_text .= '<tr><td><br></td><td>'.utf8_decode($this->getData['rec_street']).' '.$this->getData['rec_streetnr'].' '.utf8_decode($this->getData['rec_address']).'</td></tr>';
					$email_text .= '<tr><td><br></td><td>'.utf8_decode($this->getData['rec_street2']).'</td></tr>';
					$email_text .= '<tr><td><br></td><td>'.$this->getData['rec_zipcode'].' '.$this->getData['rec_city'].'</td></tr>';
					$email_text .= '<tr><td><br></td><td>'.$this->RecordData['rec_country_name'].'</td></tr>';
					$email_text .= '<tr><td><b>Phone no. : </td><td>'.$this->getData['rec_phone'].'</td></tr>';
					$email_text .= '<tr><td colspan="2"><b>Additional Information :</b></td></tr>';
					$email_text .= '<tr><td><br></td><td>'.utf8_decode($formData['additional_info']).'</td></tr>';
					$email_text .= '</table>';
					$mailOBj->emailData['SenderEmail'] = 'helpdesk@dpost.be';
					$mailOBj->emailData['SenderName']    = 'Parcelnl';
					$mailOBj->emailData['Subject'] = 'Parcel Updated Information';
					$mailOBj->emailData['MailBody'] = $email_text;
					//$mailOBj->_noReply   = 'helpdesk@dpost.be'; 
					//$this->emailData['SenderEmail']= 'helpdesk@dpost.be'; 
					$mailOBj->emailData['BCCEmail']  = array('logicparcel@sjmsoftech.com');
					
					$mailOBj->emailData['ReceiverEmail'] = trim($this->getData['forwarder_detail']['forwarder_email']); //'sanjeev.kumar@sjmsoftech.com';
					$mailOBj->emailData['ReceiverName'] = trim($this->getData['forwarder_detail']['forwarder_email']);
					$mailOBj->emailData['user_id'] = $this->getData['user_id'];
					$mailOBj->emailData['notification_id'] = 0;
					if($mailOBj->Send()) {
						$this->oldDb->update(WRONG_ADDRESS_MODIFICATION,array('email_sent_timestamp'=>new Zend_Db_Expr('NOW()')),"log_id='".$logID."'");
						$objSession->successMsg = "You parcel history will be show after some times.....!";
					}else{
					    if($mailOBj->Send()) {
							$this->oldDb->update(WRONG_ADDRESS_MODIFICATION,array('email_sent_timestamp'=>new Zend_Db_Expr('NOW()')),"log_id='".$logID."'");
						}
					}
				 return true;	
			  }	
			}
		}
	}

	public function oldParcelBarcodeId($tracno)
	{
		$select = $this->oldDb->select()
						  ->from(array('OP'=>OLDTRACKING_ENCRYPT),array('OP.barcode_id'))
						  ->where("OP.encryptkey='".$tracno."'");
	   return $this->oldDb->fetchRow($select);	

	}
	
	public function SpecialTrackingBarcodeid($barcode){
	    $select = $this->oldDb->select()
								->from(array('Barcode' =>SHIPMENT_BARCODE),array('barcode_id'))
								->where("Barcode.tracenr_barcode='".$barcode."'");
								//print_r($select->__toString());die;
		$result = $this->oldDb->fetchROW($select);
		
		if(empty($result)){
		   $select = $this->oldDb->select()
								->from(array('EC' =>EMERGENCY_CHECKIN),array('barcode_id'))
								->where("EC.old_barcode='".$barcode."'");
								//print_r($select->__toString());die;
		  $result = $this->oldDb->fetchROW($select);
		}
		return $result['barcode_id'];
	}	
}

