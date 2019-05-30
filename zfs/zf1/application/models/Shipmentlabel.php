<?php
abstract class Application_Model_Shipmentlabel extends Zend_Custom
{
    // array containing all the extended classes
    private $_exts = array();
    public $_this;
	public $shipment_id = array();
	public $shipment_type = 1;
	public $bulkshipment = array();
	public $trackingdetail = array();
	public function __construct(){
	  parent::__construct();
	  $_this = $this;
	  global $objSession;
	}
	
    public function CreateLabel(){

    	
	      
	     $shipmentdetails = $this->getShipmentDetails();
		echo "<pre>sdf"; print_r($shipmentdetails);die;
		 global $labelObj,$objSession;
		 $labelObj->SetlabelFormat();
		 $this->_insertbrcode = true;
		 $formData = $this->getData;
		 if(isset($formData['print_position']) && $formData['print_position']>0){
		 	 $labelObj->AddPage();
		     $labelObj->ParcelCount =  $labelObj->ParcelCount + $formData['print_position'];
		 }
		 foreach($shipmentdetails as $shipments){ 
		      unset($this->RecordData);
			  $this->RecordData = $shipments;
			  $ReferenceNo =  $this->SystemGeneratedReference();
			  $this->RecordData[LABEL_DATE] = ($this->RecordData['shipment_type']!=4 && $this->RecordData['shipment_type']!=10)?commonfunction::DateNow():'0000-00-00 00:00:00';
			  $this->getParcelPrice();
			  $file = 'PDF_'.time().'_'.$this->RecordData[SHIPMENT_ID].'.pdf';
			  $this->RecordData['forwarder_detail'] = $this->ForwarderDetail();
			  for($q=1;$q<=$this->RecordData[QUANTITY];$q++){ 
			    if($this->RecordData[SERVICE_ID]==139){
				   $this->RecordData['length'] = $formData['sea_length'][($q-1)];
				   $this->RecordData['width'] = $formData['sea_width'][($q-1)];
				   $this->RecordData['height'] = $formData['sea_height'][($q-1)]; 
				}
			    $labelObj->ParcelCount =  $labelObj->ParcelCount + 1;
				$this->RecordData['parcelcount'] = $q;
				$this->RecordData['ShipmentCount'] = $q.'/'.$this->RecordData[QUANTITY];
				unset($this->RecordData['tracenr']);
				unset($this->RecordData['tracenr_barcode']);
				unset($this->RecordData['barcode']);
				unset($this->RecordData['reroute_barcode']);
				$this->RecordData[REFERENCE] = $this->multireference($ReferenceNo,$q);
			     $this->GenerateBarcodeData(true);
		 	// echo "hgfgh";die;
			  // echo "<pre>"; print_r($this->RecordData);die;
				 $labelObj->outputparam = $this->RecordData;
				 $labelObj->labelAllForwarder();
				 $this->trackingdetail['ParcelNumber'.$q] = 'PARCEL NUMBER :'.$this->RecordData[TRACENR_BARCODE];
				 $this->trackingdetail['TrackingURL'.$q]  = 'TRACKING URL: '.BASE_URL.'/Parceltracking/tracking/tockenno/'.Zend_Encript_Encription::encode($this->RecordData[BARCODE_ID]); 
				 $this->AutoAssignedPlanner(); 
			  } 
			  $this->AutocheckinReturn();
			  if(in_array($this->RecordData['shipment_type'],array(6,7,8,9,11,12,13,14,17))){
			     $shopObj = new Application_Model_Shopapi();
				 $shopObj->updateOrders($this->RecordData);
			  }
			}  

			// echo $this->RecordData['shipment_type'];die;
			  switch($this->RecordData['shipment_type']){
			     case 1:
				 break;
				 case 2:
				 case 6:
				 case 7:
				 case 8:
				 case 9:
				 case 11:
				 case 12:
				 case 13:
				 case 14:
				   if($formData['shipment_mode']=='Print'){
				    ob_end_clean();
			    	$labelObj->Output($file,'D');
			   		$labelObj->PopUpLabel();
				  }	
				 break;
				case 55:
				case 4:
				case 10:
			       $labelObj->Output(API_SAVE_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/pdf/'.$file,'F');
			       // echo "yaha";
			       // die;
			       return array('Success'=>array('SuccessMessage' => 'MESSAGE: Shipment has created successfully.....',
                        'Forwarder'=>'FORWARDER: '.$this->RecordData['forwarder_detail']['forwarder_name'],
                        'TrackingDetails'=>$this->trackingdetail,
                        'LabelURL'=>'LABEL URL: '.API_OPEN_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/pdf/'.$file
                       ));
					if($this->getData['labeltype']=='PDF'){
						//$return['Success']['Label'] = base64_encode(file_get_contents($this->_ApiSaveLink[$this->RecordData[FORWARDER_ID]].$file));
				   }
				 break;
				 default:
				   
				   $labelObj->AutoPrint(true);
			   	   $labelObj->_filePath = PRINT_OPEN_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file;
			       $labelObj->Output(PRINT_SAVE_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file,'F'); 
				   // echo "or niche";
			    //    die;
			       $objSession->AddSipmentLabel = $labelObj->_filePath;
			  }
		  
	}
	
	public function EditPrintLabel($oldRecord,$quantity_diff){ 
	     $Shipmentrecord = $this->getshipmentBarcodeDetail();
		 global $labelObj,$objSession;
		 $labelObj->SetlabelFormat();
		   $file = 'PDF_'.time().'_'.$this->RecordData[SHIPMENT_ID].'.pdf';
		   $q = 1;
		   $this->_insertbrcode = false;
		   $formData = $this->getData;
		   foreach($Shipmentrecord as $shipmentRecord){
		        $labelObj->ParcelCount =  $labelObj->ParcelCount + 1;
		        $this->RecordData  = $shipmentRecord;
				$ReferenceNo =  $this->SystemGeneratedReference();
				$this->RecordData[REFERENCE] = $this->multireference($ReferenceNo,$q);
				$this->getParcelPrice();
				$this->RecordData['forwarder_detail'] = $this->ForwarderDetail();
				$this->RecordData['parcelcount'] = $q;
				$this->RecordData['ShipmentCount'] = $q.'/'.$this->RecordData[QUANTITY];
				$this->InsertBarcodeEdited($this->RecordData['barcode_id']);
				
				if($oldRecord[FORWARDER_ID]==$this->RecordData[FORWARDER_ID]){
				  
					if($this->RecordData['forwarder_id']==4 || $this->RecordData['forwarder_id']==5 || $this->RecordData['forwarder_id']==6 ||($this->RecordData['forwarder_id']==24 && strlen($this->RecordData['barcode'])>15)){
					   $this->GenerateBarcodeData(true);
					}else{
						$this->GenerateBarcodeData(false);
					}
					$this->updateBarcodeAfterEdit();
				}else{
				    unset($this->RecordData['tracenr']);
					$this->GenerateBarcodeData(true);
				}
				 $this->trackingdetail['ParcelNumber'.$q] = 'PARCEL NUMBER :'.$this->RecordData[TRACENR_BARCODE];
				 $this->trackingdetail['TrackingURL'.$q]  = 'TRACKING URL: '.BASE_URL.'/Parceltracking/tracking/tockenno/'.Zend_Encript_Encription::encode($this->RecordData[BARCODE_ID]); 
				$labelObj->outputparam = $this->RecordData;
				$labelObj->labelAllForwarder();
				$q++;
		   }
		   $newlabel = $q;
		   for($i=1;$i<=$quantity_diff;$i++){
		       $this->_insertbrcode = true;
			   $this->RecordData[REFERENCE] = $this->multireference($shipmentRecord['rec_reference'],$newlabel);
		       $this->RecordData['forwarder_detail'] = $this->ForwarderDetail();
		       $this->RecordData['forwarder_detail'] = $this->ForwarderDetail();
			   $this->getParcelPrice();
			   $this->GenerateBarcodeData(true);
			   $this->trackingdetail['ParcelNumber'.$newlabel] = 'PARCEL NUMBER :'.$this->RecordData[TRACENR_BARCODE];
			   $this->trackingdetail['TrackingURL'.$newlabel]  = 'TRACKING URL: '.BASE_URL.'/Parceltracking/tracking/tockenno/'.Zend_Encript_Encription::encode($this->RecordData[BARCODE_ID]); 
			   $labelObj->outputparam = $this->RecordData;
			   $labelObj->labelAllForwarder();
			   $newlabel++;
		   }
		  if(isset($formData['API_EDI'])){
		      $labelObj->Output(PRINT_SAVE_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file,'F');
			   $return = array('Success'=>array('SuccessMessage' => 'MESSAGE: Shipment has Edited successfully.....',
					'Forwarder'=>'    FORWARDER: '.$this->RecordData['forwarder_detail']['forwarder_name'],
					'TrackingDetails'=>$this->trackingdetail,
					'LabelURL'=>'    LABEL URL: '.PRINT_OPEN_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file
				 ));
				 if($formData['labeltype']=='PDF'){
						$return['Success']['Label'] = base64_encode(file_get_contents(PRINT_SAVE_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file));
				 }
				return $return; 	
		  }else{
		      $labelObj->AutoPrint(true);
			  $labelObj->_filePath = PRINT_OPEN_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file;
			  $labelObj->Output(PRINT_SAVE_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file,'F'); 
			  $objSession->AddSipmentLabel = $labelObj->_filePath;
		  }
	}
	
	public function GenerateBarcodeData($new_tracking=true){ 
	     if($new_tracking && $this->RecordData['forwarder_detail']['tracenr_status']=='1'){
		    $this->RecordData['tracenr'] = $this->getUniqueTracking();
		 }

		 // echo $this->RecordData['forwarder_id'];die;
	     switch($this->RecordData['forwarder_id']){
			      case 1:
				  case 2:
				  case 3:
				  case 26:
				  case 32:
				  case 38:
				  case 54:
				     $this->CreateDPDLabel($this,$new_tracking);
				  break;
				  case 55:
				  case 4:
				  	 $this->RecordData['SOCKET'] = $new_tracking;
				  	 $this->CreateGLSDELabel($this,$new_tracking);
				  break;
				  case 5:
				  	  $this->RecordData['SOCKET'] = $new_tracking;
				  	  $this->CreateGLSFreightLabel($this,$new_tracking);
				  break;
				  case 6:
				  	// echo "sfdsdf";die;
				     $this->RecordData['SOCKET'] = $new_tracking;
				  	 $this->CreateGLSNLLabel($this,$new_tracking);
				  break;
				  case 7:
				     $this->CreateBpostLabel($this,$new_tracking);
				  break;
				  case 9:
				     $this->CreateExpressLabel($this,$new_tracking);
				  break;
				  case 11:
				     $this->CreatePostatLabel($this,$new_tracking);
				  break;
				  case 14:
				     $this->CreatePostnlLabel($this,$new_tracking);
				  break;
				  case 15:
				     $this->CreateCODLabel($this,$new_tracking);
				  break;
				   case 16:
				     $colisprive = new Application_Model_ColispriveLabel();
					 $colisprive->CreateColispriveLabel($this,$new_tracking);
				  break;
				  case 17:
				  case 18:
				  case 19:
				     $this->CreateUPSLabel($this,$new_tracking);
				  break;
				   case 20:
				     $this->CreateColissimoLabel($this,$new_tracking);
				  break;
				  case 22:
				    $this->CreateParcelnlLabel($this,$new_tracking);
				  break;
				  case 23:
				    $this->CreateLDELabel($this,$new_tracking);
				  break;
				  case 10:
				  case 24:
				    $this->CreateDHLLabel($this,$new_tracking);
				  break;
				  case 21:
				  case 25:
				    $this->CreateDHLGlobalLabel($this,$new_tracking);
				  break;
				  case 27:
				    $this->CreateMondialRelayLabel($this,$new_tracking);
				  break;
				  case 28:
				    $this->CreateAnpostLabel($this,$new_tracking);
				  break;
				  case 29:
				  case 40:
				  case 52:
				    $this->RecordData['API'] = $new_tracking;
					$this->CreateYodelLabel($this,$new_tracking);
				  break;
				 case 30:
				   $this->CreateCorreosLabel($this,$new_tracking);
				 break;
				 case 31:
				    $this->RecordData['API'] = $new_tracking;
				    $norsk = new Application_Model_Norsk();
					$norsk->CreateNorskLabel($this,$new_tracking);
				 break;
				 case 33:
				   $this->CreateWwplLabel($this,$new_tracking);
				 break;
				  case 34:
					$this->CreateGLSITLabel($this,$new_tracking);
				  break;
				 case 36:
				   $this->CreateRDPAGLabel($this,$new_tracking);
				 break;
				 case 37:
				   $this->CreateBRTLabel($this,$new_tracking);
				 break;
				 case 41:
				 	$this->RecordData['API'] = $new_tracking;
				    $this->CreateFadelloLabel($this,$new_tracking);
				 break;
				 case 42:
				     $this->CreateDeburenLabel($this,$new_tracking);
				 break;
				 case 43:
				     $this->CreateOmnivaLabel($this,$new_tracking);
				 break;
				 case 44:
				     $this->CreateAramexLabel($this,$new_tracking);
				 break;
				 case 45:
				    $this->CreateESCorreosLabel($this,$new_tracking);
				  break;
				  case 46:
				    $this->CreateRussianPostLabel($this,$new_tracking);
				  break;
				  case 48:
				    $this->CreateSystematicLabel($this,$new_tracking);
				  break;
				  case 49:
				    $this->CreateRswisspostLabel($this,$new_tracking);
				  break; 
				  case 50:
				    $this->CreateHamacherLabel($this,$new_tracking);
				  break;
				  case 51:
				    $this->CreateUrgentSwisspostLabel($this,$new_tracking);
				  break; 
				  case 53:
				    $this->CreateDCPostalLabel($this,$new_tracking);
				  break;
				  case 64:
				  	$this->RecordData['API'] = $new_tracking;
				    $this->CreateBizCourierLabel($this,$new_tracking);
				  break;
			  
		}
				  	// echo "sfdsdf";die;

		if($new_tracking){
			$this->insertUpdateBarcode(); 
		}
		return true;
	}
	
	public function PrintAllLabel($new_tracking=false){ 
	     $shipmentdetails = $this->getShipmentDetailsForPrint();
	     // echo "<pre>"; print_r($shipmentdetails);die;
		 global $labelObj,$objSession;
		 $labelObj->SetlabelFormat();
		 $file = 'PDF_'.time().'_'.date('Y_m_d').'.pdf';
		 $shipment_id = 0;
		 $countparcel = 1;
		 foreach($shipmentdetails as $shipments){
			 unset($this->RecordData);
			 $this->RecordData = $shipments;
			 if($this->RecordData['service_id']==139){
			     $this->getSeaFreightDimention();
			 }
			 $this->RecordData['forwarder_detail'] = $this->ForwarderDetail();
			  $labelObj->ParcelCount =  $labelObj->ParcelCount + 1;
			  if($this->RecordData[SHIPMENT_ID]!=$shipment_id){
				  $shipment_id = $this->RecordData[SHIPMENT_ID];
				  $countparcel=1;
			  }
			  $this->updatePrintStatus($this->RecordData['barcode_id']);
			  $this->RecordData['parcelcount'] = $countparcel;
			  $this->RecordData['ShipmentCount'] = $countparcel.'/'.$this->RecordData[QUANTITY];
				$this->GenerateBarcodeData(false);
				$labelObj->outputparam = $this->RecordData;
				$labelObj->labelAllForwarder();
			  $countparcel++;
		       
		}
		ob_end_clean();
		$labelObj->Output($file,'D');
		$labelObj->PopUpLabel();
		return true;
	}
    public function getShipmentDetails(){
	    $where = $this->LevelClause();
	    if(!empty($this->bulkshipment) && $this->bulkshipment>0){
		    $select = $this->_db->select()
									->from(array('ST'=>SHIPMENT),array('*'))
									->joinleft(array('BT' =>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array())
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array())
									->where('ISNULL(BT.barcode)'.$where);
			  //$select->where('ISNULL(Barcode.'.SHIPMENT_BARCODE.')');						
			  $select->order("ST.shipment_id DESC");
			  $select->limit($this->bulkshipment,0);
		}else{
		   $select = $this->_db->select()
								->from(array('ST'=>SHIPMENT),array('*'))
								->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array('parent_id'))
								->where("shipment_id IN ('".implode("','",$this->shipment_id)."')".$where);
		 }				  
						  
			// print_r($select->__toString());die;
		return $this->getAdapter()->fetchAll($select);
	}
	public function getShipmentDetailsForPrint(){
	     $where = $this->LevelClause();
		  if(!empty($this->bulkshipment) && $this->bulkshipment>0){
		  	 $this->getData['user_id'] = isset($this->getData['user_id'])?Zend_Encript_Encription::decode($this->getData['user_id']):'';
			 $this->getData['parent_id'] = isset($this->getData['parent_id'])?Zend_Encript_Encription::decode($this->getData['parent_id']):'';
			 $where .= commonfunction::filters($this->getData);
			 if(isset($this->getData['from_date']) && isset($this->getData['to_date']) &&  $this->getData['from_date']!='' && $this->getData['to_date']!=''){
				$where .=  " AND DATE(ST.create_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";
			 }
		     $select = $this->_db->select()
										->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
										->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('*'))
										->joininner(array('ST' =>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('user_id','forwarder_id','original_forwarder','country_id','addservice_id','service_attribute','senderaddress_id','quantity','rec_name','rec_contact','rec_street','rec_streetnr','rec_address','rec_street2','rec_zipcode','rec_city','rec_phone','rec_email','rec_state','length','width','height','goods_id','goods_description','shipment_worth','cod_price','currency','create_date','terminal_id','ship_mode','wrong_parcel'))
										->joinleft(array('RB' =>SHIPMENT_BARCODE_REROUTE),"RB.barcode_id=BT.barcode_id",array('*'))
										->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array('parent_id'))
										->where("ST.delete_status='0' AND BT.delete_status='0'".$where)
										->order("ST.shipment_id DESC")
										->limit($this->bulkshipment,0);
		  }else{
			 $select = $this->_db->select()
										->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
										->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('*'))
										->joininner(array('ST' =>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('user_id','forwarder_id','original_forwarder','country_id','addservice_id','service_attribute','senderaddress_id','quantity','rec_name','rec_contact','rec_street','rec_streetnr','rec_address','rec_street2','rec_zipcode','rec_city','rec_phone','rec_email','rec_state','length','width','height','goods_id','goods_description','shipment_worth','cod_price','currency','create_date','terminal_id','ship_mode','wrong_parcel'))
										->joinleft(array('RB' =>SHIPMENT_BARCODE_REROUTE),"RB.barcode_id=BT.barcode_id",array('*'))
										->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array('parent_id'));
			$select->where("ST.delete_status='0' AND BT.delete_status='0'  AND ST.shipment_id IN ('".implode("','",$this->getData['shipment_id'])."')".$where);
			$select->order("ST.shipment_id ASC");
		}
		//print_r($select->__toString());die;
		   return $this->getAdapter()->fetchAll($select);
									
	}
	public function getshipmentBarcodeDetail(){
		     $select = $this->_db->select()
										->from(array('BT'=>SHIPMENT_BARCODE),array('*'))
										->joininner(array('ST' =>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('*'))
										->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('*','ST.rec_reference'))
										->where("ST.shipment_id IN ('".implode("','",$this->shipment_id)."')");
		   return $this->getAdapter()->fetchAll($select);
	}
	public function getDPDRouteInfo(){
	    $select = $this->_db->select()
						->from(DPDROUTEINFO,array('BarcodeID','DPDService','Indentification','OSort','DSort','Version','ServiceCode','DestinationDepot','Country'))
						->where("shipment_id='".$this->RecordData[SHIPMENT_ID]."'");
	   	$result  = $this->getAdapter()->fetchRow($select);
		if(empty($result) || (isset($this->RecordData['saturday_delivery']) && $this->RecordData['saturday_delivery']=='1')){
		    return $this->CalculateDPDRoutes();
		}
		foreach($result as $key=>$routesvalue){ 
		    $this->RecordData[$key] = $routesvalue;
		}
	}
	public function CalculateDPDRoutes($update=false){
	    try{
		 $RouteObj = new Zend_Dpdroute_Routeclass();
		 $RouteObj->inputdata['depot_number']    = $this->RecordData['forwarder_detail']['depot_number'];
		 $RouteObj->inputdata[ZIPCODE] 			 = $this->RecordData[ZIPCODE];
		 $RouteObj->inputdata[STREET] 			 = $this->RecordData[STREET];
		 $RouteObj->inputdata[CITY] 			 = $this->RecordData[CITY];
		 $RouteObj->inputdata['ServiceCode']     = ($this->RecordData[WEIGHT]<=3)?$this->RecordData['forwarder_detail']['service_code_kp']:$this->RecordData['forwarder_detail']['service_code_np'];
		 $RouteObj->inputdata['CountryCode'] 	 = $this->RecordData['rec_cncode'];
		 $RouteObj->inputdata[FORWARDER_ID] 	 = $this->RecordData[FORWARDER_ID];
		}catch(Exception $e){
		   echo $e->getMessage();die;
		}
		$routesinfo = $RouteObj->RouteInformation();
		if(!is_array($routesinfo)){
		   return $routesinfo;
		}
		foreach($routesinfo as $key=>$routesvalue){ 
		    $this->RecordData[$key] = $routesvalue;
		}
		
		if($update){
			$this->_db->update(DPDROUTEINFO,array('DPDService'=>$this->RecordData['DPDService'],'Indentification'=>$this->RecordData['Indentification'],'ServiceInfo'=>$this->RecordData['ServiceInfo'],'OSort'=>$this->RecordData['OSort'],'DSort'=>$this->RecordData['DSort'],'GroupingPriority'=>$this->RecordData['GroupingPriority'],'BarcodeID'=>$this->RecordData['BarcodeID'],'DestinationDepot'=>$this->RecordData['DestinationDepot'],'Version'=>$this->RecordData['Version'],'ServiceCode'=>$this->RecordData['ServiceCode'],'Country'=>$this->RecordData['Country']),"shipment_id='".$this->RecordData[SHIPMENT_ID]."'");
		}else{
		  $this->_db->insert(DPDROUTEINFO,array_filter(array('shipment_id'=>$this->RecordData[SHIPMENT_ID],'forwarder_id'=>$this->RecordData[FORWARDER_ID],'DPDService'=>$this->RecordData['DPDService'],'Indentification'=>$this->RecordData['Indentification'],'ServiceInfo'=>$this->RecordData['ServiceInfo'],'OSort'=>$this->RecordData['OSort'],'DSort'=>$this->RecordData['DSort'],'GroupingPriority'=>$this->RecordData['GroupingPriority'],'BarcodeID'=>$this->RecordData['BarcodeID'],'DestinationDepot'=>$this->RecordData['DestinationDepot'],'Version'=>$this->RecordData['Version'],'ServiceCode'=>$this->RecordData['ServiceCode'],'Country'=>$this->RecordData['Country'])));
		}
		
	}
	
	public function insertUpdateBarcode(){

		// echo "<pre>"; print_r($this->RecordData);die;

	     try{
					 if($this->_insertbrcode){ 
			$this->_db->insert(SHIPMENT_BARCODE,
				 array_filter(array(SHIPMENT_ID   	     => $this->RecordData[SHIPMENT_ID],
									TRACENR    			 => $this->RecordData[TRACENR],
									TRACENR_BARCODE      => $this->RecordData[TRACENR_BARCODE],
									BARCODE    			 => $this->RecordData[BARCODE],
									LOCAL_BARCODE    	 => isset($this->RecordData[LOCAL_BARCODE])?$this->RecordData[LOCAL_BARCODE]:'',
									REFERENCE_BARCODE    => isset($this->RecordData[REFERENCE_BARCODE])?$this->RecordData[REFERENCE_BARCODE]:$this->getShipmentSeriesno($this->RecordData[TRACENR]),
									FORWARDER_ID	     => $this->RecordData[FORWARDER_ID],
									WEIGHT     			 => $this->RecordData[WEIGHT],
									SERVICE_ID           => $this->RecordData[SERVICE_ID],
									DEPOT_PRICE	     	 => $this->RecordData[DEPOT_PRICE],
									CUSTOMER_PRICE	     => $this->RecordData[CUSTOMER_PRICE],
									COD_PRICE	    	 => $this->RecordData[COD_PRICE],
									LABEL_STATUS 		=>(isset($this->RecordData[LABEL_DATE]) && $this->RecordData[LABEL_DATE]!='0000-00-00 00:00:00')?'1':'0')));
					$this->RecordData[BARCODE_ID] = $this->getAdapter()->lastInsertId();
					$this->_db->insert(SHIPMENT_BARCODE_REROUTE,array_filter(array(BARCODE_ID		=>	$this->RecordData[BARCODE_ID],
					REROUTE_BARCODE  =>	isset($this->RecordData[REROUTE_BARCODE])?commonfunction::addslashesing($this->RecordData[REROUTE_BARCODE]):'')));
					$this->_db->insert(SHIPMENT_BARCODE_DETAIL,array_filter(array(BARCODE_ID		=>	$this->RecordData[BARCODE_ID],
					LABEL_DATE 		=>	(isset($this->RecordData[LABEL_DATE]) && $this->RecordData[LABEL_DATE]!='0000-00-00 00:00:00')?new Zend_Db_Expr('NOW()'):'0000-00-00 00:00:00',
					REFERENCE		     => $this->RecordData[REFERENCE])));
					if($this->RecordData[SERVICE_ID]==139){
					$this->seafreightdimension(1);
					}																			  
					}else{
					$this->_db->update(SHIPMENT_BARCODE,array(TRACENR    		 => $this->RecordData[TRACENR],
						TRACENR_BARCODE      => $this->RecordData[TRACENR_BARCODE],
						BARCODE    			 => $this->RecordData[BARCODE],
						LOCAL_BARCODE    	 => $this->RecordData[LOCAL_BARCODE],
						FORWARDER_ID	     => $this->RecordData[FORWARDER_ID],
						WEIGHT     			 => $this->RecordData[WEIGHT],
						SERVICE_ID           => $this->RecordData[SERVICE_ID],
						DEPOT_PRICE	     	 => $this->RecordData[DEPOT_PRICE],
						CUSTOMER_PRICE	     => $this->RecordData[CUSTOMER_PRICE]),"barcode_id='".$this->RecordData[BARCODE_ID]."'");
//$this->RecordData[BARCODE_ID] = $this->getAdapter()->lastInsertId();
$this->_db->update(SHIPMENT_BARCODE_REROUTE,array(REROUTE_BARCODE  =>	commonfunction::addslashesing($this->RecordData[REROUTE_BARCODE])),"barcode_id='".$this->RecordData[BARCODE_ID]."'");
$this->_db->update(SHIPMENT_BARCODE_DETAIL,array(REFERENCE	 => $this->RecordData[REFERENCE]),"barcode_id='".$this->RecordData[BARCODE_ID]."'");
		}
	  }catch(Exception $e){
	     echo $e->getMessage();die;
	     return $this->GenerateBarcodeData(true);
	  }																																			
	}
	public function updateBarcodeAfterEdit(){
	    try{
			$this->_db->update(SHIPMENT_BARCODE,array(TRACENR    		 => $this->RecordData[TRACENR],
													TRACENR_BARCODE      => $this->RecordData[TRACENR_BARCODE],
													BARCODE    			 => $this->RecordData[BARCODE],
													LOCAL_BARCODE    	 => $this->RecordData[LOCAL_BARCODE],
													FORWARDER_ID	     => $this->RecordData[FORWARDER_ID],
													WEIGHT     			 => $this->RecordData[WEIGHT],
													SERVICE_ID           => $this->RecordData[SERVICE_ID],
													DEPOT_PRICE	     	 => $this->RecordData[DEPOT_PRICE],
													CUSTOMER_PRICE	     => $this->RecordData[CUSTOMER_PRICE]),"barcode_id='".$this->RecordData[BARCODE_ID]."'");
				$this->_db->update(SHIPMENT_BARCODE_REROUTE,array(REROUTE_BARCODE  =>	commonfunction::addslashesing($this->RecordData[REROUTE_BARCODE])),"barcode_id='".$this->RecordData[BARCODE_ID]."'");
				$this->_db->update(SHIPMENT_BARCODE_DETAIL,array(REFERENCE	 => $this->RecordData[REFERENCE]),"barcode_id='".$this->RecordData[BARCODE_ID]."'");
	  }catch(Exception $e){
	     echo $e->getMessage();die;
	  }	
	}
	/**
	*Reference Generated by system
	*Function : SystemGeneratedReference()
	*Generate Rendom Reference of Parcel
	**/
	public function SystemGeneratedReference(){
		  if($this->RecordData[REFERENCE]==''){
			$record = $this->getCustomerDetails($this->RecordData['user_id']);
			$FName      = commonfunction::sub_string(commonfunction::uppercase($record['name']),0,3);
			$Rec_Name  = commonfunction::sub_string(commonfunction::uppercase($this->RecordData[RECEIVER]),0,3);
			$ParcelNo  = commonfunction::paddingleft($this->RecordData[SHIPMENT_ID],4,'0',STR_PAD_LEFT);
			 return commonfunction::stringReplace(' ','',$FName.$ParcelNo.$Rec_Name);
		 }else{
			 return $this->RecordData[REFERENCE];
		 }	
	}
	public function multireference($reference,$count){
	  return ($count==1)?$reference:$reference.'-'.($count-1);
	}
	
	public function AutocheckinReturn(){
      if(($this->RecordData['addservice_id']==124 || $this->RecordData['addservice_id']==148) && $this->RecordData[FORWARDER_ID]==14){
		  $select = $this->_db->select()
								   ->from(SHIPMENT_BARCODE,array('barcode_id'))
								   ->where("shipment_id='".$this->RecordData[SHIPMENT_ID]."' AND checkin_status='0'");
			$shipments = $this->getAdapter()->fetchAll($select);
		    foreach($shipments as $shipment){ 
			   $this->CheckIN($shipment['barcode_id'],9);
		   }
	  }
   }
   
   public function seafreightdimension($insert){
      if($insert==1){
	     $this->_db->insert(SEAFREIGHT_DIMENSION,
		 					array_filter(array(BARCODE_ID =>$this->RecordData[BARCODE_ID],
								 "length"	 =>$this->RecordData['length'],
								 "width"	 =>$this->RecordData['width'],
								 "height"	 =>$this->RecordData['height'])));
	  }
	  if($insert==2){
	    $this->_db->update(SEAFREIGHT_DIMENSION,
		 					array("length"	 =>$this->RecordData['length'],
								 "width"	 =>$this->RecordData['width'],
								 "height"	 =>$this->RecordData['height']),
								 "barcode_id='".$this->RecordData[BARCODE_ID]."'");
	  }
   
   }
   public function getSeaFreightDimention(){
       $select = $this->_db->select()
								   ->from(SEAFREIGHT_DIMENSION,array('*'))
								   ->where("barcode_id='".$this->RecordData[BARCODE_ID]."'");
	   $dimention = $this->getAdapter()->fetchRow($select);
	   if(!empty($dimention)){
	      $this->RecordData['length'] = $dimention['length'];
		  $this->RecordData['width'] = $dimention['width'];
		  $this->RecordData['height'] = $dimention['height'];
	   }
	   return true;
   }
   public function updatePrintStatus($barcode_id){
        $this->_db->update(SHIPMENT_BARCODE,array(LABEL_STATUS=> '1'),"barcode_id='".$barcode_id."'");
		$this->_db->update(SHIPMENT_BARCODE_DETAIL,array(LABEL_DATE=>new Zend_Db_Expr('NOW()')),"barcode_id='".$barcode_id."'");
   }
   public function InsertBarcodeEdited($barcode_id){
          $select = $this->_db->select()
									->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id','tracenr','tracenr_barcode','barcode'))
									->joininner(array('RB'=>SHIPMENT_BARCODE_REROUTE),"RB.barcode_id=BT.barcode_id",array('reroute_barcode'))
									->where("BT.barcode_id='".$barcode_id."'");
		  $OldbarcodeData =  $this->getAdapter()->fetchRow($select);
		  try{
		  $this->_db->insert(SHIPMENT_BARCODE_EDITED,array_filter(array(
		 											BARCODE_ID    		 => $OldbarcodeData[BARCODE_ID],
		 											TRACENR    		 	 => $OldbarcodeData[TRACENR],
													TRACENR_BARCODE      => $OldbarcodeData[TRACENR_BARCODE],
													BARCODE    			 => $OldbarcodeData[BARCODE],
													REROUTE_BARCODE	     => $OldbarcodeData[REROUTE_BARCODE],
													'action_by'		 => $this->Useconfig['user_id'],
													'action_date'		 =>	new Zend_Db_Expr('NOW()'),
													'action_ip'		 => commonfunction::loggedinIP()))); 
		}catch(Exception $e){
		    $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		}											 
   }
   
   public function AutoAssignedPlanner(){
       $plannerobj = new Planner_Model_Planner();
	   $plannerobj->checkALreadyAssign($this->RecordData,1);
   }
   
    public function addExtandable($object)
    {
        $this->_exts[]=$object;
    }

    public function __get($varname)
    {
        foreach($this->_exts as $ext)
        {
            if(property_exists($ext,$varname))
            return $ext->$varname;
        }
    }

    public function __call($method,$args)
    {
        foreach($this->_exts as $ext)
        {
            if(method_exists($ext,$method))
            return call_user_func_array(array($ext,$method),$args);
        }
    }
	
	
	


}