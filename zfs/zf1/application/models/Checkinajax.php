<?php



class Application_Model_Checkinajax extends Application_Model_Shipments

{
	public $extraParam = array();
    public function getScanDetails(){ 

		  try{
		  
		 $select = $this->_db->select()

						  ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id'))

						  ->joininner(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array(''))

						  ->where("BT.barcode='".$this->getData['barcode']."' AND DATE(ST.create_date)>=DATE_SUB(CURDATE(), INTERVAL 6 MONTH)");

			//print_r($select->__toString());die;

		 $check_barcode = $this->getAdapter()->fetchRow($select);

		 if(empty($check_barcode)){

		    $select = $this->_db->select()

						  ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id'))

						  ->joininner(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array(''))

						  ->where("BT.reference_barcode='".$this->getData['barcode']."' AND DATE(ST.create_date)>=DATE_SUB(CURDATE(), INTERVAL 6 MONTH)");

			//print_r($select->__toString());die;

		    $check_barcode = $this->getAdapter()->fetchRow($select);

		 }
		 if(empty($check_barcode)){
		       $select = $this->_db->select()
						  ->from(array('BE'=>SHIPMENT_BARCODE_EDITED),array('barcode_id'))
						  ->where("BE.barcode='".$this->getData['barcode']."' AND DATE(BE.action_date)>=DATE_SUB(CURDATE(), INTERVAL 6 MONTH)");
			  $check_barcode = $this->getAdapter()->fetchRow($select);
			  $this->extraParam['edit_print'] = (!empty($check_barcode))?1:0;			  
		 }

		 $result = array();

		 if(!empty($check_barcode)){

				 $select = $this->_db->select() 

								  ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id','barcode','reference_barcode','checkin_status','hub_status','customer_price','weight','cod_price','BT.service_id','BT.forwarder_id','BT.delete_status'))

								  ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID,array('checkin_date','pickup_date','checkin_by'))

								  ->joininner(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array(RECEIVER,'addservice_id','email_notification as EMAIL','ST.user_id','ST.rec_zipcode','ST.country_id','ST.email_notification','modify_date'))

								  ->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.user_id',array(COMPANY_NAME))

								  //->joinleft(array('CB'=>USERS_DETAILS),'CB.'.ADMIN_ID.'=BD.checkin_by',array(COMPANY_NAME.' AS checkin_by'))

								  ->joininner(array('CT'=>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID,array('country_name'))

								  ->joininner(array('FT'=>FORWARDERS),'FT.'.FORWARDER_ID.'=ST.'.FORWARDER_ID,array('forwarder_name'))

								  ->joininner(array('GT'=>SERVICES),'GT.'.SERVICE_ID.'=ST.'.SERVICE_ID,array('service_name'))

								  ->joinleft(array('HS'=>SHIPMENT_HUB),'HS.'.BARCODE_ID.'=BT.'.BARCODE_ID,array('hub_checkin_status','hub_edi','hub_checkin_by','hub_checkin_date','hub_edi_date'))

								  ->where("BT.barcode_id='".$check_barcode['barcode_id']."'");


			   $result = $this->getAdapter()->fetchRow($select);

			 }
				// echo "<pre>"; print_r($response);die;

		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); }

		 $response = array();


		 if(!empty($result)){

		     if($result['delete_status']=='1'){

			      $response = array('Status'=>'R','Response'=>'Parcel Deleted');

			 }elseif($result['hub_status']=='1' && $result['hub_checkin_status']=='1'){

			     $hub_by = $this->getCustomerDetails($result['hub_checkin_by']);

				 $response = array('Status'=>'R','Response'=>'Parcel Already HUB Scaned By '.$hub_by['company_name'].' On 

				 '.date('F - d Y',strtotime($result['hub_checkin_date'])));

			 }elseif($result['checkin_status']=='1' && $result['hub_status']=='0'){

			     $checkin_by = $this->getCustomerDetails($result['checkin_by']);

				 $response = array('Status'=>'R','Response'=>'Parcel Already Checked In By '.$checkin_by['company_name'].' On 

				 '.date('F - d Y',strtotime($result[CHECKIN_DATE])));

			 }else{

			     $response = array('Status'=>'G','Response'=>$this->ParcelinfoHTML($result),'Parcelprice'=>$result['customer_price'],'Weight'=>$result[WEIGHT]);

			 }			 

		 }else{

			 $response = array('Status'=>'R','Response'=>'No Record found!!');

		 }

		 

		 return json_encode($response);

	}

	

	

	public function ParcelinfoHTML($data){
	        global $translate;

			$this->getData['country_id'] = $data[COUNTRY_ID];

			$scale_data = $this->getActualWeight($data[WEIGHT]);

			$sclaerweight = ($scale_data['scaler_weight']<>0)?$scale_data['scaler_weight']:'N/A';

			$weight_difference = ($scale_data['Diff']<>0)?$scale_data['Diff']:'N/A';

			$print_label = ($data[REFERENCE_BARCODE]==$this->getData['barcode'] || ($data['pickup_date']!='0000-00-00 00:00:00'  && $data['modify_date']!='0000-00-00 00:00:00' && $data['modify_date']>$data['pickup_date']))?1:0;
			if(isset($this->extraParam['edit_print']) && $this->extraParam['edit_print']==1 && $print_label ==0){
			   $print_label = 1;
			}

			

			$forwarders = $this->getForwarderCountry();

			$emergencyforwarder = '';
			$emergencyforwarder .= '<div id="forwarder_div" style="display:none"><div class="col-sm-12 col-md-12">';

			foreach($forwarders as $forwarder){

			    //$selected = ($forwarder['forwarder_id']==$data['forwarder_id'])?'selected="selected"':'';

				$emergencyforwarder .= '<div class="col-sm-12 col-md-6"><div class="radiotextbox"><div class="radiobutton" id="radiobox_'.$forwarder['forwarder_id'].'"><input id="forwarder_id'.$forwarder['forwarder_id'].'" type="radio"  class="imgradio" name="forwarder_id" value="'.$forwarder['forwarder_id'].'" onclick="onclickemergencyforwarder('.$forwarder['forwarder_id'].')"/><label for="forwarder_id'.$forwarder['forwarder_id'].'"><img src="'.FORWARDER_ICON.$forwarder['forwarder_icon'].'" class="img-responsive"></label><p style="font-size:25px; color:#ffffff">'.$forwarder['forwarder_name'].'</p></div><div class="clearfix"></div></div></div>';

				

			}

			if(date('l')=='Friday' && ($data[COUNTRY_ID]==9 || $data[COUNTRY_ID]==6)){

			$emergencyforwarder .= '<div class="col-sm-12 col-md-6"><div class="radiotextbox"><div class="radiobutton" id="radiobox_SD"><input id="forwarder_id500" type="radio"  class="imgradio" name="forwarder_id" value="500" onclick="onclickemergencyforwarder(500)"/><label for="forwarder_id500"><img src="'.FORWARDER_ICON.'dpd.jpg" class="img-responsive"></label><p style="font-size:25px; color:#ffffff">Zaterdag</p></div><div class="clearfix"></div></div></div>';

			}

			$emergencyforwarder .= '</div></div>';

			

			$string = '';

			$string .= '<div class="col-sm-12 col-md-3" style="padding:5px 0px;">';

			$string .= '<form action="" method="post" onsubmit="return false;" name="checin_form" id="checkin_form" class="inputbox">';

			$string .= '<input type="hidden" name="barcode_id" id="barcode_id" value="'.$data[BARCODE_ID].'">';

			$string .= '<input type="hidden" name="service_id" id="service_id" value="'.$data[SERVICE_ID].'">';

			$string .= '<input type="hidden" name="addservice_id" id="addservice_id" value="'.$data[ADDSERVICE_ID].'">';

			$string .= '<input type="hidden" name="user_id" id="user_id" value="'.$data[ADMIN_ID].'">';

			$string .= '<input type="hidden" name="barcode" id="barcode" value="'.$data[BARCODE].'">';

			$string .= '<input type="hidden" name="scan_barcode" id="scan_barcode" value="'.$this->getData['barcode'].'">';

			$string .= '<input type="hidden" name="email_notification" id="email_notification" value="'.$data['email_notification'].'">';

			$string .= '<input type="hidden" name="scaler_weight" id="scaler_weight" value="'.$scale_data['scaler_weight'].'">';

			$string .= '<input type="hidden" name="system_weight" id="system_weight" value="'.$data['weight'].'">';

			$string .= '<input type="hidden" name="customer_price" id="customer_price" value="'.$data['customer_price'].'">';

			$string .= '<input type="hidden" name="weight_difference" id="weight_difference" value="'.$scale_data['Diff'].'">';

			$string .= '<input type="hidden" name="print_label" id="print_label" value="'.$print_label.'">';

			$string .= '<input type="hidden" name="emergency_forwarder" id="emergency_forwarder" value="0">';

			//Left

			$string .= '<a href="javascript:void(0)" onclick="keyboardaction(1)"><div class="col-md-12" style="text-align:center;background-color:#000000; color:#FFFFFF; padding:20px 10px; margin-bottom:13px; font-size:27px">';

			$string .= '<strong><span>'.$translate->translate('System Weight').'<br></span><span id="weight_show">'.$data[WEIGHT].'</span>&nbsp;<span>(F2)</span></strong>';

			$string .= '</div></a>';

			$string .= '<div class="col-md-12" style="text-align:center;background-color:#0066CC; color:#FFFFFF; padding:20px 10px; margin-bottom:13px;  font-size:27px">';

			$string .= '<strong> <span>'.$translate->translate('Scaler Weight').'</span><br>';

			$string .= '<span>'.$sclaerweight.'</span></strong>';

			$string .= '</div>';

			$string .= '<div class="col-md-12" style="text-align:center;background-color:#FF0000; color:#FFFFFF; padding:20px 10px; margin-bottom:13px;  font-size:30px">';

			$string .= '<strong> <span>'.$translate->translate('Difference').'</span><br>'.$weight_difference.'</strong></div></div>';

			//Center Part	

			$string .= '<div class="col-sm-12 col-md-6 col_paddingtop" style="font-size:21px">';

			$string .= ' <div id="ajax_loading" style="display:none"></div>';

			$string .= '<div id="checkin_process" style="display:none;color: green"></div>';

			$string .= '<table class="tbl_new">';

			$string .= '<tbody>';

			$string .= '<tr><th colspan="2">'.$translate->translate('Parcel Information').'</th></tr>';

			$string .= '<tr><td>'.$translate->translate('Country').'</td><td>'.$data['country_name'].'</td></tr>';

			$string .= '<tr><td>'.$translate->translate('Forwarder').'</td><td><span id="forwarder_text">'.$data['forwarder_name'].'</span>'.$emergencyforwarder.'</td></tr>';

			$string .= '<tr><td>'.$translate->translate('Service').'</td><td>'.$data['service_name'].'</td></tr>';

			$string .= '<tr><td>'.$translate->translate('Sub-Service').'</td><td>'.(($data['addservice_id']>0)?$this->ServiceName($data['addservice_id']):'N/A').'</td></tr>';

			$string .= '<tr><th colspan="2">'.$translate->translate('Receiver Information').'</th></tr>';

			$string .= '<tr><td>'.$translate->translate('Company/ Name').'</td><td>'.$data[RECEIVER].'</td></tr>';

			$string .= '</tbody></table>';

			$string .= '<div class="clear"></div>';

			$string .= '<div class="col-sm-12 col-md-5"><strong style="margin-top:4px">'.$translate->translate('Re-Barcode').'-:</strong></div>';

			$string .= '<div class="col-sm-12 col-md-7"><input name="rebarcode" id="rebarcode" class="inputfield" type="text" onkeypress="Rechecksubmit(event);"/></div>';

			$string .= '<div class="clear"></div></div>';

			//Right Part

			$string .= '<a href="javascript:void(0)" onclick="keyboardaction(2)"><div class="col-sm-12 col-md-3" style="padding:5px 0px;">';

			$string .= '<div class="col-md-12" style="text-align:center; background-color:#d22e50; color:#ffffff; border:2px solid #000000; padding:15px 10px; margin-bottom:13px; font-size:27px">';

			$string .= '<strong><span>'.$translate->translate('Shipping Price').'</span><br><span id="price_show">'.$data[CUSTOMER_PRICE].'</span> <span>(F9)</span></strong>';

			$string .= ' </div></a>';

			$string .= '<a href="javascript:void(0)" onclick="keyboardaction(3)"><div class="col-md-12" style="text-align:center;border:2px solid #0066CC;background-color:rgb(230,100,38); color:#ffffff; padding:15px 10px; margin-bottom:13px;  font-size:25px">';

			$string .= '<strong> <span>'.$translate->translate('Emergency Check-in').'</span><br><span>(F6)</span></strong>';

			$string .= '</div></a>';

			$string .= '<a href="javascript:void(0)" id="print_label" onclick="keyboardaction(4)"><div class="col-md-12" style="text-align:center;border:2px solid #ff0000; background-color:rgb(0, 150, 201);color:#ffffff; padding:15px 10px; margin-bottom:13px;  font-size:27px" id="print_area">';

			$string .= '<strong> <span>'.$translate->translate('Print Label').'</span><br><span>(Ctrl+P)</span></strong>';

			$string .= '</div></a></div>';

			$string .=$emergencyforwarder;

			

			

			return $string;

	}

	

	public function getActualWeight($sacnweight){

	     global $objSession;

	   if(!isset($objSession->scaller_machineno) && !isset($objSession->scaller_machineurl) && isset($objSession->weight_scale)){

		      $select = $this->_db->select()

									   ->from(WEIGHT_SCALER_INFO,array('*'))

									   ->where("serial_id='".$objSession->weight_scale."'")

									   ->limit(1);

			  $result = $this->getAdapter()->fetchRow($select);	

			  if(!empty($result)){

				  $objSession->scaller_machineno = $result['serial_no'];

				  $objSession->scaller_machineurl = $result['machine_url'];

			  }						   

		}

		if(!isset($objSession->weight_scale)){

		    unset($objSession->scaller_machineno);

			unset($objSession->scaller_machineurl);  

		}

		$difference = 0;

		$weight = 0;

	   if(isset($objSession->scaller_machineurl) && isset($objSession->scaller_machineno)) {	

		$data = commonfunction::file_contect($objSession->scaller_machineurl);

		$exploded = commonfunction::explode_string($data,':');

		

		$weight = commonfunction::sub_string(trim($exploded[1]),0,commonfunction::string_position(trim($exploded[1]),'k'));

		$serialno = commonfunction::sub_string(trim($exploded[2]),0,commonfunction::string_position(trim($exploded[2]),'<br />'));

		

		if(trim($serialno)==trim($objSession->scaller_machineno) && $weight>0.01){

		    $difference = commonfunction::numberformat($weight-$sacnweight,2);

			$difference = (($difference>0.01 || $difference<0.01) && $weight>0.01)?$difference:0;

		}

	  }

	  return array('scaler_weight'=>$weight,'Diff'=>$difference);

	}

	

	public function getParcelCheckin($return = false){

	   global $EmailObj;

	    switch($this->getData['checkin_type']){

		   case 1:

	       	  $this->CheckIN($this->getData['barcode_id'],$this->getData['checkin_type']);

	       break;

		   case 3:

	       	  $this->CheckIN($this->getData['barcode_id'],$this->getData['checkin_type']);

	       break;

		   case 5:

	       	  $this->CheckIN($this->getData['barcode_id'],$this->getData['checkin_type']);

	       break;

		   case 6:

	       	  $this->CheckIN($this->getData['barcode_id'],$this->getData['checkin_type']);

	       break;

		   default:

		   	 $this->CheckIN($this->getData['barcode_id'],$this->getData['checkin_type']);

		}

		if($this->getData['email_notification']=='1'){

		  	$EmailObj->emailData = $this->getEmailData($this->getData['barcode_id']);

			$EmailObj->checkinMail();

		}

		if($return){

	 	  return true;

	   }

	    echo json_encode(array('Status'=>'Y','message'=>'Parcel Checked-in successfully'));exit;

	}

	public function getShipmentDataByBarcodeID($barcode_id){

	     $select = $this->_db->select()

						  ->from(array('BT'=>SHIPMENT_BARCODE),array('*'))

						  ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID,array('rec_reference AS reference'))

						  ->joininner(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array('*','BT.forwarder_id'))

						  ->joinleft(array('RB' =>SHIPMENT_BARCODE_REROUTE),"RB.barcode_id=BT.barcode_id",array('*'))

						  ->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID,array('parent_id'))

						  ->where("BT.barcode_id='".$barcode_id."'");//print_r($select->__toString());die;

		return $this->getAdapter()->fetchRow($select);				  

	}

	public function UpdateWeight(){

	    $formData = $this->getData;

		$this->RecordData =  $this->getShipmentDataByBarcodeID($formData['barcode_id']);

		$systemData = $this->RecordData;

	    $change_weight = commonfunction::stringReplace(',','.',$formData['new_weight']);

		if($change_weight<=0){

	       echo json_encode(array('Status'=>'E','message'=>'Please enter valid weight!!','ActualWeight'=>$this->RecordData[WEIGHT]));exit;

	    }  

		 //$this->RecordData =  $this->getShipmentDataByBarcodeID($formData['barcode_id']);

		 $this->RecordData['rec_reference'] =  $this->RecordData['reference'];

	   if($this->RecordData[WEIGHT]<>$change_weight){

	      $service_id = ($this->RecordData[ADDSERVICE_ID]>0)?$this->RecordData[ADDSERVICE_ID]:$this->RecordData[SERVICE_ID];

		  $this->getData = $this->getData + array(WEIGHT=>$change_weight,COUNTRY_ID=>$this->RecordData[COUNTRY_ID],ADMIN_ID=>$this->RecordData[ADMIN_ID],ZIPCODE=>$this->RecordData[ZIPCODE],'service_id'=>$this->RecordData['service_id'],'addservice_id'=>$this->RecordData['addservice_id']);

		  $this->RecordData[WEIGHT] = $change_weight;

		  $forwardersPrice = $this->checkRouting(true);

		  if($forwardersPrice){

		      if(($this->RecordData[FORWARDER_ID]!=$forwardersPrice[FORWARDER_ID]) || (in_array($this->RecordData[FORWARDER_ID],array(1,2,3,23,26,32,54)) && (($change_weight<=3 && $systemData[WEIGHT]>3) || ($change_weight>3 && $systemData[WEIGHT]<=3)))){

			       $this->RecordData[FORWARDER_ID] = $forwardersPrice[FORWARDER_ID];

			       $label_patch = $this->EmergencyPrint();

				   $this->getParcelCheckin(true);

				   echo json_encode(array('Status'=>'L','Label'=>$label_patch,'message'=>'New label has Printed!!'));exit;

			  }else{

			    $this->_db->update(SHIPMENT_BARCODE,array(WEIGHT=>$change_weight,'depot_price'=>$forwardersPrice['depot_price'],'customer_price'=>$forwardersPrice['customer_price']),"barcode_id='".$formData['barcode_id']."'");

				if($this->getData['checkin_type']==10){

				   $this->getParcelCheckin(true);

				}

				echo json_encode(array('Status'=>'U','message'=>'Weight Updated!!','ActualWeight'=>$this->RecordData[WEIGHT]));exit;

			  }

		  }else{

		    echo json_encode(array('Status'=>'E','message'=>'No routing found for this weight!!','ActualWeight'=>$systemData[WEIGHT]));exit;

		  }      

	   }else{

	       echo json_encode(array('Status'=>'E','message'=>'Old Weight and new weight is same!!','ActualWeight'=>$this->RecordData[WEIGHT]));exit;

	   }

	   

	}

	public function EmergencyCheckin(){

		$tracenr = '';

		$barcode = '';

		$tracenr_barcode =  '';

		$reroute_barcode = '';

	   switch($this->getData['emergency_forwarder']){

	      case 1:

		  case 2:

		  case 3:

		  case 23:

		  case 26:

		  case 26:

		  case 32:

		  case 54:

		  

		  if(commonfunction::string_length($this->getData['emergency_barcode'])>25){

		        $tracenr = commonfunction::sub_string($this->getData['emergency_barcode'],13,10);

				$barcode = $this->getData['emergency_barcode'];

				$tracenr_barcode =  commonfunction::sub_string($this->getData['emergency_barcode'],9,14);

				$reroute_barcode = $tracenr_barcode;

		  }else{

		        $tracenr = commonfunction::sub_string($this->getData['emergency_barcode'],4,10);

				$barcode = $this->getData['emergency_barcode'];

				$tracenr_barcode = $this->getData['emergency_barcode'];

				$reroute_barcode = $this->getData['emergency_barcode'];

		  }

		  break;

		 default:

		  	    $tracenr = $this->getData['emergency_barcode'];

				$barcode = $this->getData['emergency_barcode'];

				$tracenr_barcode = $this->getData['emergency_barcode'];

				$reroute_barcode = $this->getData['emergency_barcode'];

	    }

		

		$select = $this->_db->select()

		   					   ->from(SHIPMENT_BARCODE,'COUNT(1) AS CNT')

							   ->where("forwarder_id='".$this->getData['emergency_forwarder']."'")

							   ->where("tracenr_barcode='".$reroute_barcode."'");//print_r($select->__toString()); die;

		 $result = $this->getAdapter()->fetchRow($select);

		 if($result['CNT']>0){

		    echo json_encode(array('Status'=>'E','message'=>'Barcode already exist'));exit;

		 }else{

		     try{

			   $this->SaveIntoEmergencyCheckin($this->getData['barcode_id']);

			   $this->_db->update(SHIPMENT_BARCODE,array(TRACENR    		 => $tracenr,

														 TRACENR_BARCODE     => $tracenr_barcode,

														 BARCODE    		 => $barcode,

														 FORWARDER_ID	     => $this->getData['emergency_forwarder']),

														 "barcode_id='".$this->getData['barcode_id']."'");

			  $this->_db->update(SHIPMENT_BARCODE_REROUTE,array(REROUTE_BARCODE  =>	$reroute_barcode),"barcode_id='".$this->getData[BARCODE_ID]."'");

			  $this->getParcelCheckin(true);

			  echo json_encode(array('Status'=>'S','message'=>'Parcel Checked-in Successfully!'));exit;

														 

			 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); 

			     echo json_encode(array('Status'=>'E','message'=>'Duplicate Barcode Entry!'));exit;

			 }

		 }

	}

	public function EmergencyCHeckinWithNewLabel(){

	    $this->RecordData =  $this->getShipmentDataByBarcodeID($this->getData['barcode_id']);

		$this->RecordData[FORWARDER_ID] =  $this->getData['emergency_forwarder'];

		if(isset($this->getData['saturday_delivery'])){

			$this->RecordData['saturday_delivery'] =  $this->getData['saturday_delivery'];

		}

		$this->SaveIntoEmergencyCheckin($this->getData['barcode_id']);

		$label_patch = $this->EmergencyPrint();

		$this->getParcelCheckin(true);

		echo json_encode(array('Status'=>'L','Label'=>$label_patch,'message'=>'New label has Printed!!'));exit;

	}

	

	public function EmergencyPrint(){

		   global $labelObj,$objSession;

		   $labelObj->SetlabelFormat();

	       $this->_insertbrcode = false;

		   $file = 'PDF_'.time().'_'.$this->RecordData[SHIPMENT_ID].'.pdf';

		   $labelObj->ParcelCount =  $labelObj->ParcelCount + 1;

			$this->RecordData['forwarder_detail'] = $this->ForwarderDetail();

			$this->RecordData['parcelcount'] = 1;

			$this->RecordData['ShipmentCount'] = '1/'.$this->RecordData[QUANTITY];

			unset($this->RecordData['tracenr']);

			unset($this->RecordData['tracenr_barcode']);

			unset($this->RecordData['barcode']);

			unset($this->RecordData['reroute_barcode']);

			$this->GenerateBarcodeData(true);

			$labelObj->outputparam = $this->RecordData;

			$labelObj->labelAllForwarder();

			$labelObj->AutoPrint(true);

		    $labelObj->_filePath = PRINT_OPEN_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file;

		    $labelObj->Output(PRINT_SAVE_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file,'F');

			return $labelObj->_filePath;

	}

	

	public function systematicPickups(){

	    $select = $this->_db->select()

		   					   ->from(SYSTEMATIC_PICKUP,'*')

							   ->where("barcode_id='".$this->getData['barcode_id']."'");//print_r($select->__toString()); die;

		 $result = $this->getAdapter()->fetchRow($select);

		 if(empty($result)){

		   $customer_details = $this->getCustomerDetails($this->getData['user_id']);

		   $result = array('company_name'=>$customer_details['company_name'],'street1'=>$customer_details['address1'],'street2'=>'','zipcode'=>$customer_details['postalcode'],'city'=>$customer_details['city'],'country_id'=>$customer_details['cncode'],'phone'=>$customer_details['phoneno']);

		 }

		 return $result;

	}

	public function AddSystematicPickupAddress(){

        $select = $this->_db->select()

		   					   ->from(SYSTEMATIC_PICKUP,'*')

							   ->where("barcode_id='".$this->getData['barcode_id']."'");//print_r($select->__toString()); die;

		 $result = $this->getAdapter()->fetchRow($select);

		 if(!empty($result)){

			$this->_db->update(SYSTEMATIC_PICKUP , array('company_name'=>$this->getData['company_name'],'street1'=>$this->getData['street1'],'street2'=>$this->getData['street2'],'zipcode'=>$this->getData['zipcode'],'city'=>$this->getData['city'],'country_id'=>$this->getData['country_id'],'phone'=>$this->getData['phone']),"barcode_id='".$this->getData['barcode_id']."'"); 

		}else{

		  	$this->_db->insert(SYSTEMATIC_PICKUP , array_filter(array('barcode_id'=>$this->getData['barcode_id'],'company_name'=>$this->getData['company_name'],'street1'=>$this->getData['street1'],'street2'=>$this->getData['street2'],'zipcode'=>$this->getData['zipcode'],'city'=>$this->getData['city'],'country_id'=>$this->getData['country_id'],'phone'=>$this->getData['phone'],'date'=>new Zend_Db_Expr('NOW()'))));

		}

	}

	

	public function getPrintlabelLink($return=false){

	    	global $labelObj,$objSession;

		   $this->RecordData =  $this->getShipmentDataByBarcodeID($this->getData['barcode_id']);

		   $labelObj->SetlabelFormat();

		   $file = 'PDF_'.time().'_'.$this->RecordData[SHIPMENT_ID].'.pdf';

		   $labelObj->ParcelCount =  $labelObj->ParcelCount + 1;

			$this->RecordData['forwarder_detail'] = $this->ForwarderDetail();

			$this->RecordData['parcelcount'] = 1;

			$this->RecordData['ShipmentCount'] = $this->getPosisionofParcel().'/'.$this->RecordData[QUANTITY];

			//$this->PrintAllLabel();echo json_encode(array('Status'=>'L','message'=>'New label has Printed!!'));exit;

			$this->GenerateBarcodeData(false);

			$labelObj->outputparam = $this->RecordData;

			$labelObj->labelAllForwarder();

			$labelObj->AutoPrint(true);

		    $labelObj->_filePath = PRINT_OPEN_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file;

		    $labelObj->Output(PRINT_SAVE_LABEL.$this->RecordData['forwarder_detail']['forwarder_name'].'/'.$file,'F');

			if($return){ return $labelObj->_filePath; }

			echo json_encode(array('Status'=>'L','Label'=>$labelObj->_filePath,'message'=>'New label has Printed!!'));exit;

	}

	

	 public function getPosisionofParcel(){

        $select = $this->_db->select()

							->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id'))

							->where("BT.shipment_id='".$this->RecordData[SHIPMENT_ID]."'")

							->order("BT.barcode_id ASC");

		  // print_r($select->__toString());die;

	   $result = $this->getAdapter()->fetchAll($select);

	   $count = 1;

	   foreach($result as $parcel){

	     if(trim($this->RecordData[BARCODE_ID])==trim($parcel[BARCODE_ID])){

		     return $count;

		  }

		  $count++;

	   }

	  return $count;

   }

   public function ChangeCustomerPrice(){

      $this->_db->update(SHIPMENT_BARCODE,array('customer_price'=>commonfunction::stringReplace(',','.',$this->getData['customer_price'])),"barcode_id='".$this->getData['barcode_id']."'");

	  echo json_encode(array('Status'=>'S','message'=>'Price Updated successfully!!'));exit;

   }

   

   public function ReferenceCheckIN(){  

        $select = $this->_db->select()

								->from(array('BT'=>SHIPMENT_BARCODE),array('barcode','barcode_id','checkin_status','weight'))

								->joinleft(array('BD'=>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID,array(''))

								->joinleft(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array('email_notification'))

								->where("BD.rec_reference='".$this->getData['barcode']."' AND DATE(ST.create_date)>=DATE_SUB(CURDATE(), INTERVAL 3 MONTH)");

						//print_r($select->__toString());die;

		  $result = $this->getAdapter()->fetchRow($select);//print_r($result);die;

		 if(!empty($result)){

		       if($result['checkin_status']=='0'){

			         $scale_data = $this->getActualWeight($result[WEIGHT]);

					 $this->getData = array('barcode_id'=>$result[BARCODE_ID],'checkin_type'=>10,'email_notification'=>$result['email_notification'],'new_weight'=>$scale_data['scaler_weight']);

					 if($scale_data['Diff']<>0 && $scale_data['scaler_weight']>0){

					  $this->UpdateWeight();

					 }else{

					    $response = $this->getPrintlabelLink(true);

						$this->getParcelCheckin(true);

					    echo json_encode(array('Status'=>'L','message'=>'Parcel Found','Label'=>$response));die; 

					 }

			   }else{

			     echo json_encode(array('Status'=>'E','message'=>'Parcel Already Checked-In'));die;

			   }

		 }else{

		   echo json_encode(array('Status'=>'E','message'=>'No Record Found'));die;

		 }

   }

   

   public function SaveIntoEmergencyCheckin($barcode_id){

       $select = $this->_db->select()

		   					   ->from(SHIPMENT_BARCODE,array('barcode','tracenr','weight','forwarder_id'))

							   ->where("barcode_id='".$barcode_id."'");//print_r($select->__toString()); die;

	   $barcodedata = $this->getAdapter()->fetchRow($select);

	   

      $select = $this->_db->select()

		   					   ->from(EMERGENCY_CHECKIN,array('COUNT(1) AS CNT'))

							   ->where("barcode_id='".$dataArr['barcode_id']."'");//print_r($select->__toString()); die;

		 $result = $this->getAdapter()->fetchRow($select);

		 if($result['CNT']>0){

		     $this->_db->update(EMERGENCY_CHECKIN,array('old_tracenr'=>$barcodedata['tracenr'],'old_barcode'=>$barcodedata['barcode'],'old_weight'=>$barcodedata['weight'],'old_forwarder'=>$barcodedata['forwarder_id']),"barcode_id='".$barcode_id."'");

		 }else{

		    $this->_db->insert(EMERGENCY_CHECKIN,array('barcode_id'=>$barcode_id,'old_tracenr'=>$barcodedata['tracenr'],'old_barcode'=>$barcodedata['barcode'],'old_weight'=>$barcodedata['weight'],'old_forwarder'=>$barcodedata['forwarder_id']));

		 }

		 return true;

   }

}



