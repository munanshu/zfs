<?php 
class Application_Model_Ajax extends Zend_Custom{
   /**
     * Return city and street of given postalcode
     * @param  $adaptor|$post main db adaptor and post data from ajax
     * @return city list as HTML dropdown or textbox
     */
	public function getCity(){ 
		$this->getData[ZIPCODE] = $this->ValidateZipcode($this->getData[ZIPCODE],$this->getData[COUNTRY_ID]);
		$select = $this->masterdb->select()
						  ->from(array('WC'=>CITIES),array('*'))
						  ->where('WC.country_id=?',trim($this->getData[COUNTRY_ID]))
						  ->where('WC.postcode=?',trim($this->getData[ZIPCODE]))
						  ->group('WC.city');
		$cityLists = $this->masterdb->fetchAll($select);	
		$cityArr =array();
		$streetArr = array();
		foreach($cityLists as $cityadata){
		  $cityArr[] = $cityadata['city'];
		  $streetArr[] = $cityadata['street'];
		}
		//print_r($cityArr);
		//print_r($streetArr);		
		$city = '';
		$street = '';
		$Cti = 0;
		$Str = 0;
		if($this->getData[COUNTRY_ID]==9){
		    if(count($streetArr)>1){
			   $street .= "<select name=\"rec_street\" id=\"rec_street\" class=\"inputfield\">";
		       foreach($streetArr as $streetdata){
			     $checked = (isset($this->getData[STREET]) && $streetdata==trim($this->getData[STREET]))?'selected="selected"':''; 
			  	 $street .= "<option value=\"".$streetdata."\" ".$checked.">".$streetdata."</option>";
		      }
			   $street  .= "</select>";
			   $Str = 1;
			}
			 else{
			   $streetname = (!empty($this->getData[STREET]))?$this->getData[STREET]:$streetArr[0];
			   $street = '<input type="text" name="rec_street" id="rec_street" class="mandatory ui-autocomplete-input inputfield" placeholder="Street" onkeyup="fieldvalidate(this.value,"rec_street");" value="'.$streetname.'" autocomplete="off">';
			   $Str = 1;
			}
		}
		else{
		      $streetname = (!empty($this->getData[STREET]))?$this->getData[STREET]:'';
			   $street = '<input type="text" name="rec_street" id="rec_street" class="mandatory ui-autocomplete-input inputfield" placeholder="Street" onkeyup="fieldvalidate(this.value,"rec_street");" value="'.$streetname.'" autocomplete="off">';
			   $Str = 1;
		}
		
		if($this->getData[COUNTRY_ID]==15 || $this->getData[COUNTRY_ID]==8 || $this->getData[COUNTRY_ID]==6 || $this->getData[COUNTRY_ID]==218){
		    if(count($cityArr)>1){
			   $city = "<select name=\"rec_city\" id=\"rec_city\" class=\"inputfield\">";
		       foreach($cityArr as $citydata){
			     $checked = (isset($this->getData[CITY]) && $citydata==trim($this->getData[CITY]))?'selected="selected"':''; 
			  	 $city .= "<option value=\"".$citydata."\" ".$checked.">".$citydata."</option>";
		      }
			   $city  .= "</select>";
			   $Cti = 1;
			}
			 else{
			   $city_name = (!empty($this->getData[CITY]))?$this->getData[CITY]:$cityArr[0];
			   $city = '<input type="text" name="rec_city" id="rec_city" class="mandatory ui-autocomplete-input inputfield" placeholder="City" onkeyup="fieldvalidate(this.value,"rec_city");" value="'.$city_name.'" autocomplete="off">';
			   $Cti = 1;
			}
		}else{
		       $city_name = (!empty($this->getData[CITY]))?$this->getData[CITY]:$cityArr[0];
			   $city = '<input type="text" name="rec_city" id="rec_city" class="mandatory ui-autocomplete-input inputfield" placeholder="City" onkeyup="fieldvalidate(this.value,"rec_city");" value="'.$city_name.'" autocomplete="off">';
			   $Cti = 1;
		}
		return json_encode(array('Str'=>$Str,'Cti'=>$Cti,'Street'=>$street,'City'=>$city));
	}
	
	public function getServiceList(){
		   $userdetails = $this->getCustomerDetails($this->getData[ADMIN_ID]);
		   $this->getData[PARENT_ID] = $userdetails[PARENT_ID];
		   $services = $this->getRoutingID();
		   $customerRouting = $this->getCustomerRouting($this->getData[ADMIN_ID],$this->getData[COUNTRY_ID]);
		   // echo ""; print_r($services);die;
		   $ReturnSTr = '';
		   $selected_forwarder = 0;
		   $originalforwarder = 0;
		   $weightLimit = array(110=>50,111=>30,112=>300,114=>700,128=>1000);
		   if($services){
		       foreach($services as $service){
			    $displaynon = (isset($this->getData['selected_service']) && isset($this->getData['selected_addservice']) && $this->getData['selected_addservice']>0 && $this->getData['selected_service']==$service[SERVICE_ID])?'':'style="display:none"';
				$checked = (isset($this->getData['selected_service']) && $this->getData['selected_service']==$service[SERVICE_ID])?'checked="checked"':'';
				$special_price = $this->getSpecialPrice($service,$this->getData[ADMIN_ID]);
				$price = ($service[SERVICE_ID]!=6 && $service[SERVICE_ID]!=5 && $userdetails['show_price']=='1')?(' &euro;'.commonfunction::numberformat((($special_price>0)?$special_price:$service['customer_price']),2).''):'';
				$selected = ($this->getData['selected_service']>0  && ($this->getData['selected_service']==$service[SERVICE_ID]) && $this->getData['selected_addservice']<1)?' selectedbox':'';
				$serviceinformation = $this->serviceInfo($service);
				$ReturnSTr .= '<div class="radiotextbox">';
				$ReturnSTr .= '<div class="sideaction">';
				$ReturnSTr .= '<div class="sideicon"><img src="'.IMAGE_LINK.(($service['signature']=='1')?'/sign.png':'/sign_x.png').'" title="Signature"></div>';
				$ReturnSTr .= '<div class="sideicon"><img src="'.IMAGE_LINK.(($service['tracking']=='1')?'/Barcode-Scanner.png':'/Barcode-Scanner_x.png').'"  title="Tracking"></div></div>';
				$ReturnSTr .= '<div class="radiobutton'.$selected.'" id="radiobox_'.$service[SERVICE_ID].'">';
				$forwarder_id = (!empty($customerRouting) && isset($customerRouting[$service[SERVICE_ID]]) && $customerRouting[$service[SERVICE_ID]]>0)?$customerRouting[$service[SERVICE_ID]]:$service[FORWARDER_ID];
				if(isset($this->getData['selected_service']) && $this->getData['selected_service']==$service[SERVICE_ID]){
				    $selected_forwarder = $forwarder_id;
		   			$originalforwarder = $service[FORWARDER_ID];
				}
				$ReturnSTr .= '<input type="radio" name="service_id" id="service_id_'.$service[SERVICE_ID].'" value="'.$service[SERVICE_ID].'"  class="imgradio"  onclick="onclickservice('.$service[SERVICE_ID].','.$forwarder_id.','.$service[FORWARDER_ID].')" '.$checked.' />';
				$ReturnSTr .= '<label for="service_id_'.$service[SERVICE_ID].'"><img src="'.SERVICE_ICON.$service['service_icon'].'" class="img-responsive"/></label>';
				$ReturnSTr .= '<p>'.$service['service_name'].' <a class="tooltip"><img src="'.SERVICE_ICON.'info.png"/><b class="tooltiptext">'.$service['description'].$serviceinformation.'</b></a></p>';
				$ReturnSTr .= '<p><i>'.$this->expectedDeliveryTime(array('Service'=>$service[SERVICE_ID],'Country'=>$this->getData[COUNTRY_ID],'Zipcode'=>$this->getData[ZIPCODE])).'</i>';
				$ReturnSTr .= '<span>'.$price.'</span></p>';
				$ReturnSTr .= '</div><div class="clearfix"></div>';
				$addservices = $this->getRoutingID($service[SERVICE_ID]);
				if(!empty($addservices)){
					$ReturnSTr .= '<div class="subradio" id="addservicediv_'.$service[SERVICE_ID].'" '.$displaynon.'>';
					
				    foreach($addservices as $addservice){
					    if(in_array($addservice[SERVICE_ID],array(101,102,103,151,152,153)) && date('l')!='Friday'){
						  continue;
						}
						if(in_array($addservice[SERVICE_ID],array(110,111,112,114,128)) ){
					       if($this->getData[WEIGHT]>$weightLimit[$addservice[SERVICE_ID]]){
						      continue;
						   }
						}
						$addserviceinfo = $this->serviceInfo($addservice);
					    $addchecked = (isset($this->getData['selected_addservice']) && $this->getData['selected_addservice']==$addservice[SERVICE_ID])?'checked="checked"':'';
						$addspecial_price = $this->getSpecialPrice($addservice,$this->getData[ADMIN_ID]);
					    $addprice = ($userdetails['show_price']=='1')?(' &euro;'.commonfunction::numberformat((($addspecial_price>0)?$addspecial_price:$addservice['customer_price']),2).' '):'';
						$addselected = ($this->getData['selected_addservice']>0  && ($this->getData['selected_addservice']==$addservice[SERVICE_ID]))?' selectedbox':'';
						$ReturnSTr .= '<div class="radiobutton2'.$addselected.'" id="radiobox_'.$addservice[SERVICE_ID].'">';
						$ReturnSTr .= '<div class="sideaction" style="top:0px; right:0px">';
						
						$ReturnSTr .= '<div class="sideicon"><img src="'.IMAGE_LINK.(($addservice['signature']=='1')?'/sign.png':'/sign_x.png').'" title="Signature"></div>';
						$ReturnSTr .= '<div class="sideicon"><img src="'.IMAGE_LINK.(($addservice['tracking']=='1')?'/Barcode-Scanner.png':'/Barcode-Scanner_x.png').'"  title="Tracking"></div></div>';
						
						$addforwarder_id = (!empty($customerRouting) && isset($customerRouting[$addservice[SERVICE_ID]]) && $customerRouting[$addservice[SERVICE_ID]]>0)?$customerRouting[$addservice[SERVICE_ID]]:$addservice[FORWARDER_ID];
						if(isset($this->getData['selected_addservice']) && $this->getData['selected_addservice']>0 && $this->getData['selected_addservice']==$addservice[SERVICE_ID]){
							$selected_forwarder = $addforwarder_id;
							$originalforwarder = $addservice[FORWARDER_ID];
						}
						$ReturnSTr .= '<input type="radio" name="addservice_id" id="addservice_id_'.$addservice[SERVICE_ID].'" value="'.$addservice[SERVICE_ID].'"  class="imgradio2"  onclick="onclickaddservice('.$addservice[SERVICE_ID].','.$addforwarder_id.','.$addservice[FORWARDER_ID].')" '.$addchecked.'/>';
						
						
						$ReturnSTr .= '<label for="addservice_id_'.$addservice[SERVICE_ID].'"><img src="'.SERVICE_ICON.$addservice['service_icon'].'" class="img-responsive"/></label>';
						
						$ReturnSTr .= '<p>'.$addservice['service_name'].' <a class="tooltip"><img src="'.SERVICE_ICON.'info.png"/><b class="tooltiptext">'.$addservice['description'].$addserviceinfo.'</b></a></p>';
						$ReturnSTr .= '<p><i>'.$this->expectedDeliveryTime(array('Service'=>$addservice[SERVICE_ID],'Country'=>$this->getData[COUNTRY_ID],'Zipcode'=>$this->getData[ZIPCODE])).'</i> <span>'.$addprice.' </span></p></div>';
					 }	
					  	
					 $ReturnSTr .= '</div>';
				}
				$ReturnSTr .= '<div class="clearfix"></div></div>';
			}
		} 
		   
		   $seafreightSearvices = $this->getSeaFreightService();
		   if($seafreightSearvices){
		       $displaynon = (isset($this->getData['selected_service']) && isset($this->getData['selected_addservice']) && $this->getData['selected_addservice']>0 && $this->getData['selected_service']==139)?'':'style="display:none"';
				$checked = (isset($this->getData['selected_service']) && $this->getData['selected_service']==139)?'checked="checked"':'';
				//$price = ($service[SERVICE_ID]!=6 && $service[SERVICE_ID]!=5)?(' &euro;'.commonfunction::numberformat($service['customer_price'],2).''):'';
				$selected = ($this->getData['selected_service']>0  && ($this->getData['selected_service']==139) && $this->getData['selected_addservice']<1)?' selectedbox':'';
				//$serviceinformation = $this->serviceInfo($service);
				$ReturnSTr .= '<div class="radiotextbox">';
				$ReturnSTr .= '<div class="sideaction">';
				$ReturnSTr .= '<div class="sideicon"><img src="'.IMAGE_LINK.'/sign_x.png" title="Signature"></div>';
				$ReturnSTr .= '<div class="sideicon"><img src="'.IMAGE_LINK.'/Barcode-Scanner_x.png"  title="Tracking"></div></div>';
				$ReturnSTr .= '<div class="radiobutton'.$selected.'" id="radiobox_139">';
				//$forwarder_id = (!empty($customerRouting) && isset($customerRouting[$service[SERVICE_ID]]) && $customerRouting[$service[SERVICE_ID]]>0)?$customerRouting[$service[SERVICE_ID]]:$service[FORWARDER_ID];
				$ReturnSTr .= '<input type="radio" name="service_id" id="service_id_139" value="139"  class="imgradio"  onclick="onclickservice(139,22,22)" '.$checked.' />';
				$ReturnSTr .= '<label for="service_id_139"><img src="'.SERVICE_ICON.'SeaFreight.png" class="img-responsive"/></label>';
				$ReturnSTr .= '<p>Sea Freight<a class="tooltip"><img src="'.SERVICE_ICON.'info.png"/><b class="tooltiptext">'.$seafreightSearvices['remark'].'</b></a></p>';
				$ReturnSTr .= '<p><i></i>';
				$ReturnSTr .= '<span></span></p>';
				$ReturnSTr .= '</div><div class="clearfix"></div>';
		   }
		   
		 return json_encode(array('success'=>(($ReturnSTr!='')?1:0),'Services'=>$ReturnSTr,'forwarder_id'=>$selected_forwarder,'original_forwarder'=>$originalforwarder));	

	}
	public function serviceInfo($data){
	     $service_desc = ($data['transit_desc']!='')?$data['transit_desc']:$data['gen_desc'];
		 return $service_desc;
	}
		
	public function getAddressbook(){
	     $select = $this->_db->select()
									->from(array('AB' =>ADDRESS_BOOK),array('name','contact','street','street_no','address','street2','postalcode','city','phone','email','country_id'))
									->where("user_id=?",$this->getData['user_id'])
									->where("(name LIKE '".$this->getData['name_startsWith']."%' OR customer_number LIKE '".$this->getData['name_startsWith']."%')")
									->order("name ASC")
									->limit(15);
		 $addresbook =  $this->getAdapter()->fetchAll($select);	//return $select->__toString();
		 $returnArr = array();
		 if(!empty($addresbook)){
		    foreach($addresbook as $address){
			   $returnArr[] = array('Name'=>$address['name'],'FullAdd'=>commonfunction::implod_array($address,'^'));
			}
		 }else{
		     $returnArr[] = array('Name'=>'No record!','FullAdd'=>'No record!');
		 }//return $addresbook;	
		 return $returnArr;									
	}
	
	public function getStreetList(){
	    $select = $this->masterdb->select()
									->from(array('CT' =>CITIES),array('street'))
									->where("country_id=?",$this->getData['country_id'])
									->where("postcode=?",$this->getData['rec_zipcode'])
									->where("street LIKE '".$this->getData['name_startsWith']."%'")
									->order("street ASC")->group("street")
									->limit(15);
		 $streetlists =  $this->masterdb->fetchAll($select);	//return $select->__toString();
		 $returnArr = array();
		 if(!empty($streetlists)){
		    foreach($streetlists as $streetlist){
			   $returnArr[] = $streetlist['street'];
			}
		 }else{
		     $returnArr[] = $this->getData['name_startsWith'];
		 }//return $addresbook;	
		 return $returnArr;	
	}
	
	public function getWeightClassService(){
	     $where = '';
		 if($this->getData['service']>0){
		    $where = " AND WC.service_id='".$this->getData['service']."'";
		 }
		 $select = $this->_db->select()
	   					->from(array('WC'=>ROUTING_WEIGHT_CLASS),array('*','GROUP_CONCAT(service_id) AS all_service'))
						->where("WC.country_id='".$this->getData['country_id']."' AND WC.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'".$where)
						->group("WC.min_weight")
						->group("WC.max_weight")
						->order("WC.min_weight ASC");
						// echo $select->__toString();die;
		$weightclasses =  $this->getAdapter()->fetchAll($select);
		$returnString = '';
		$returnString .= '<div class="table-header"><table width="100%" cellpadding="0" cellspacing="0"><thead>';
		$returnString .= '<tr><th class="header-cell wd1">Services</th>';
		foreach($weightclasses as $weightclass){
		  $minweight[]   = $weightclass['min_weight'];
		  $maxweight[]   = $weightclass['max_weight'];
		  $allservices[]   = commonfunction::explode_string($weightclass['all_service'],',');
		  $returnString .= '<th class="header-cell wd1">'.$weightclass['min_weight'].' - to - '.$weightclass['max_weight'].'</th>';
		}
		// echo "<pre>";print_r($returnString);die;
	   $returnString .= '</tr></thead></table></div><div class="table-body"><table width="100%" cellpadding="0" cellspacing="0"><tbody>';
	   $select = $this->_db->select()
	   					->from(array('WC'=>ROUTING_WEIGHT_CLASS),array('*'))
						->joininner(array('CT'=>COUNTRIES),"CT.country_id=WC.country_id",array('country_name'))
						->joininner(array('ST'=>SERVICES),"ST.service_id=WC.service_id",array('service_name'))
						->joinleft(array('PSV'=>SERVICES),"PSV.service_id=ST.parent_service", array('service_name AS parent_name'))
						->where("WC.country_id='".$this->getData['country_id']."' AND WC.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'".$where)
						->group("WC.service_id")
						->order(new Zend_Db_Expr("CASE WHEN ST.parent_service=0 THEN ST.service_id ELSE ST.parent_service END"))
						->order("ST.service_id")
						->order("WC.min_weight ASC");
						//echo $select->__toString();
		$serviceLists =  $this->getAdapter()->fetchAll($select);	
		
		foreach($serviceLists as $servicekey=>$serviceList){
		   //$returnString .= '<div class="row" style="background-color: '.(isset($serviceList['parent_name'])?'#ffecb3':'aquamarine;').'">';
		   $parent_service = isset($serviceList['parent_name'])?'-<b>'.$serviceList['parent_name'].'</b>':'';
		   $returnString .= '<tr style="background-color: '.(isset($serviceList['parent_name'])?'#ffecb3':'aquamarine;').'"><td class="body-cell wd1">'.$serviceList['service_name'].$parent_service.'</td>';
		   foreach($minweight as $key=>$weightbox){
		        $returnString .= '<td class="body-cell wd1">';
				if(commonfunction::inArray($serviceList['service_id'],$allservices[$key])){
				$addedRouting = $this->getAlreadyAddedROuting($weightbox,$maxweight[$key],$serviceList['service_id']); 
				$returnString .= '<input type="text" name="routingdata['.$weightbox.'-'.$maxweight[$key].']['.$servicekey.'][price]" class="inputfield" style="width: 74px;" placeholder="Price" value="'.(isset($addedRouting['depot_price'])?$addedRouting['depot_price']:'').'">&nbsp;';
				$returnString .= '<input type="hidden" name="routingdata['.$weightbox.'-'.$maxweight[$key].']['.$servicekey.'][routing_id]" value="'.(isset($addedRouting['routing_id'])?$addedRouting['routing_id']:'').'">&nbsp;';
				$returnString .= '<input type="hidden" name="routingdata['.$weightbox.'-'.$maxweight[$key].']['.$servicekey.'][service_id]" value="'.$serviceList['service_id'].'">&nbsp;';
				$returnString .= '<select class="inputfield" style="width: 50%;" id="forwarder_id" name="routingdata['.$weightbox.'-'.$maxweight[$key].']['.$servicekey.'][forwarder_id]">';
				$returnString .= '<option value="">--Select--</option>';
				$forwarderList =  $this->getForwarderCountry(); 
				foreach($forwarderList as $forwarder){ 
				   $selected = (isset($addedRouting['forwarder_id']) && $addedRouting['forwarder_id']==$forwarder['forwarder_id'])?'selected="selected"':'';
				   $returnString .= '<option value="'.$forwarder['forwarder_id'].'" '.$selected.'>'.$forwarder['forwarder_name'].'</option>';
				}
				//$returnString .= '</td>';
			  }
			 $returnString .= '</td>';
				
		   }
		   $returnString .= '</tr>';
		}
		$returnString .= '</tbody> </table></div>';
		
		
		return $returnString;
	}
	
	
	public function getAlreadyAddedROuting($min_weight,$max_weight,$service_id){
	   $select = $this->_db->select()
	   					->from(array('RT'=>ROUTING),array('depot_price','forwarder_id','routing_id'))
						->where("RT.country_id='".$this->getData['country_id']."' AND RT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'")
						->where("RT.min_weight=".$min_weight." AND RT.max_weight=".$max_weight." AND RT.service_id='".$service_id."' AND RT.special_routing='0'");
						//echo $select->__toString();die;
		return  $this->getAdapter()->fetchRow($select);
	}
	
	public function getWeightClassService_local(){
	     $select = $this->_db->select()
	   					->from(array('WC'=>ROUTING_WEIGHT_CLASS),array('*'))
						->where("WC.country_id='".$this->getData['country_id']."'")
						->group("WC.min_weight")
						->group("WC.max_weight")
						->order("WC.min_weight ASC");
		$weightclasses =  $this->getAdapter()->fetchAll($select);
		$returnString = '';
		$returnString .= '<div class="header-row row">';
		$returnString .= '<span class="cell">Services</span>';
		foreach($weightclasses as $weightclass){
		  $minweight[]   = $weightclass['min_weight'];
		  $maxweight[]   = $weightclass['max_weight'];
		  $returnString .= '<span class="cell">'.$weightclass['min_weight'].' - to - '.$weightclass['max_weight'].'</span>';
		}
	   $returnString .= '</div>';
	   $select = $this->_db->select()
	   					->from(array('WC'=>ROUTING_WEIGHT_CLASS),array('*'))
						->joininner(array('CT'=>COUNTRIES),"CT.country_id=WC.country_id",array('country_name'))
						->joininner(array('ST'=>SERVICES),"ST.service_id=WC.service_id",array('service_name'))
						->joinleft(array('PSV'=>SERVICES),"PSV.service_id=ST.parent_service", array('service_name AS parent_name'))
						->where("WC.country_id='".$this->getData['country_id']."'")
						->group("WC.service_id")
						->order("WC.min_weight ASC")->order("ST.service_id ASC")->order("ST.parent_service ASC");
		$serviceLists =  $this->getAdapter()->fetchAll($select);	
		
		foreach($serviceLists as $servicekey=>$serviceList){
		   $returnString .= '<div class="row">';
		   $parent_service = isset($serviceList['parent_name'])?'-<b>'.$serviceList['parent_name'].'</b>':'';
		   $returnString .= '<span class="cell" data-label="Service">'.$serviceList['service_name'].$parent_service.'</span>';
		   foreach($minweight as $key=>$weightbox){
		        $dissabled = ($serviceList['parent_name']!='')?'disabled="disabled"':'';
				$returnString .= '<span class="cell" data-label="Pricestep">';
				$returnString .= '<input type="text" name="routingdata['.$weightbox.'-'.$maxweight[$key].']['.$servicekey.'][price]" id="price_'.$key.'_'.$serviceList['service_id'].'" class="inputfield" style="width: 74px;" placeholder="Price" '.$dissabled.' onkeyup="enableSubservice(this.value,'.$key.','.$serviceList['service_id'].')" onblur="enableSubservice(this.value,'.$key.','.$serviceList['service_id'].')">&nbsp;';
				$returnString .= '<input type="hidden" name="routingdata['.$weightbox.'-'.$maxweight[$key].']['.$servicekey.'][service_id]" value="'.$serviceList['service_id'].'">&nbsp;';
				$returnString .= '<select class="inputfield" style="width: 150px;" name="routingdata['.$weightbox.'-'.$maxweight[$key].']['.$servicekey.'][forwarder_id]" id="forwarder_'.$key.'_'.$serviceList['service_id'].'" '.$dissabled.'>'; 
				$returnString .= '<option value="">--Select Forwarder--</option>';
				$forwarderList =  $this->getForwarderList(); 
				foreach($forwarderList as $forwarder){ 
				  $returnString .= '<option value="'.$forwarder['forwarder_id'].'">'.$forwarder['forwarder_name'].'</option>';
				}
				$returnString .= '</select></span>';
				
		   }
		   $returnString .= '</div>';
		}
		
		
		return $returnString;
	}
	
	public function ModifyInvoiceExtrahead(){//print_r($this->getData);die;
	   if($this->getData['mode']=='Delete'){
	   		$this->_db->delete(INVOICE_EXTRA_HEAD,"invoiceextra_id='".$this->getData['invoiceextra_id']."'");
	   }else{
	   		$this->_db->update(INVOICE_EXTRA_HEAD,array('quantity'=>$this->getData['added_quantity'],'description'=>$this->getData['added_description'],'price'=>$this->getData['added_price'],'btw_class'=>$this->getData['added_btw_class']),"invoiceextra_id='".$this->getData['invoiceextra_id']."'");
	   }
	}
	/**
	* Function : addcounstatus()
	* This function is for country change status
	* Date of creation 25/07/2016
	**/ 
	public function getChangeStatus($data)
	{ 
	try {
		if($data['condi_value'] > 0) {
		   $update = $this->_db->update($data['tablename'],array($data['column']=>$data['column_value']),$data['condi_column'].'='.$data['condi_value']);
		   return ($update) ? true : false;
		}
	} catch (Exception $e) {
		die('Something went wrong: ' . $e->getMessage());
		}
	}

 /**
 * Delete table record
 * Function : deleterecord()
 * This function is to delete table record
 * Date of creation 24/11/2016
 **/  
  public function deleterecord($data)
  { 
   try{
		$update = $this->_db->update($data['tablename'],array($data['column']=>$data['column_value'],'deleted_date'=>new Zend_Db_Expr('NOW()'),'deleted_by'=>$this->Useconfig['user_id'],'deleted_ip'=>commonfunction::loggedinIP()),$data['condi_column'].' = '.$data['condi_value']);
		return ($update) ? true : false;
    }catch (Exception $e) {
             $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
        } 
  }
  
  public function getfrwarderSettings(){
        $forwarder_id = (isset($this->getData['forwarder_id']))?$this->getData['forwarder_id']:0;
		$select = $this->_db->select()
	   					->from(array('FS'=>FORWARDER_SETTINGS),array('*'))
						->where("FS.country_id='".$this->getData['country_id']."' AND FS.forwarder_id='".$forwarder_id."'");
						//echo $select->__toString();
		$forwarderSetting =  $this->getAdapter()->fetchRow($select);
		if(empty($forwarderSetting)){
		  $select = $this->_db->select()
	   					->from(array('FS'=>FORWARDER_SETTINGS),array('*'))
						->where("FS.country_id='".$this->getData['country_id']."' AND FS.forwarder_id=0");
						//echo $select->__toString();
		 $forwarderSetting =  $this->getAdapter()->fetchRow($select);
		}
		return $forwarderSetting;	
  }
  
  
}

