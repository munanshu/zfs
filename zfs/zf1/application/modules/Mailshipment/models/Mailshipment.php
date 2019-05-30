<?php

class Mailshipment_Model_Mailshipment  extends Zend_Custom
{
	public function getCountry($country_id=0){
	  $where = '';
	  if($this->Useconfig['parent_id']==188 || $this->Useconfig['parent_id']==2772 || $this->Useconfig['parent_id']==145){
	     $where = " AND status='1'";
	  }
	  $select = $this->_db->select()
	  							  ->from(array('MC'=>MAIL_POST_COUNTRY),array('*'))
								  ->where("1".$where);
		if($country_id>0){
			$select->where('MC.id=?',$country_id);
			return $this->getAdapter()->fetchRow($select);
		}else{
			return $this->getAdapter()->fetchAll($select);
		}					  
	}
	public function getMailPostWeightClas($class_id=0){
	    $select = $this->_db->select()
	  							  ->from(array('MW'=>MAILPOST_WEIGHTCLASS),array('*','CONCAT(min_weight,"-",max_weight) AS Classes'));
		if($class_id>0){
			$select->where('MW.class_id=?',$class_id);
			return $this->getAdapter()->fetchRow($select);
		}else{						  
			return $this->getAdapter()->fetchAll($select);
		}
	}
	public function AddMailRouting(){
	   global $objSession;
	   foreach($this->getData['routing'] as $class_id=>$routing){
	   	 if($routing['depot_price']>0){
		 	 $select = $this->_db->select()
			 						 ->from(MAILPOST_ROUTINGS,array('COUNT(1) AS CNT'))
									 ->where("user_id='".$this->getData['user_id']."' AND country_id='".$this->getData['country_id']."' AND weight_class_id='".$class_id."'"); 
			 $checkValidate = $this->getAdapter()->fetchRow($select);
			 $country  = $this->getCountry($this->getData['country_id']);
			 $weightclass = $this->getMailPostWeightClas($class_id);
			 if($checkValidate['CNT']<=0 && $routing['depot_price']>0){
			 	$this->_db->insert(MAILPOST_ROUTINGS,array_filter(array('user_id'=>$this->getData['user_id'],'country_id'=>$this->getData['country_id'],'weight_class_id'=>$class_id, 'depot_price'=>$routing['depot_price'],'remark'=>$routing['remark'])));
		        $objSession->successMsg .= 'Weight Class Added '.$weightclass['Classes'].' for country '.$country['country_name'].'<br>';
			 }else{
			     $objSession->errorMsg .= 'Weight Class '.$weightclass['Classes'].' Already Exist or Depot price zero for country '.$country['country_name'].'<br>';
			 }
			 
		 }
	   }
	}
	
	public function getMailRoutingList(){
	  $where = $this->LevelAsDepots();
	  if(isset($this->getData['country_id']) && $this->getData['country_id']>0){
	      $where .= " AND CT.id='".$this->getData['country_id']."'";
	  }
	  if(isset($this->getData['weight_class_id']) && $this->getData['weight_class_id']>0){
	      $where .= " AND WC.class_id='".$this->getData['weight_class_id']."'";
	  }
	  if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){
	      $where .= " AND AT.user_id='".$this->getData['user_id']."'";
	  }
	  $select = $this->_db->select()
	  							->from(array('MR'=>MAILPOST_ROUTINGS),array('*'))
								->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=MR.user_id",array('company_name'))
								->joininner(array('CT'=>MAIL_POST_COUNTRY),"CT.id=MR.country_id",array('country_name'))
								->joininner(array('WC'=>MAILPOST_WEIGHTCLASS),"WC.class_id=MR.weight_class_id",array('min_weight','max_weight'))
								->where("MR.delete_status='0'".$where);//echo $select->__toString();die;
	  return $this->getAdapter()->fetchAll($select);							
								
	}
	public function MailRoutingUpdate(){
	  if($this->getData['mode']=='delete'){
	    $this->_db->update(MAILPOST_ROUTINGS,array('delete_status'=>1),"mail_routing_id='".$this->getData['mail_routing_id']."'");
		echo 'D';die;
	  }else{
	    switch($this->Useconfig['level_id']){
		   case 1:
		   case 11:
		    $update = array('depot_price'=>$this->getData['price'],'remark'=>$this->getData['remark']);
		   break;
		   case 4:
		   case 6:
		    $update = array('customer_price'=>$this->getData['price']);
		   break;
		}
		$this->_db->update(MAILPOST_ROUTINGS,$update,"mail_routing_id='".$this->getData['mail_routing_id']."'");
		echo 'U';die;
	  }
	}
	public function MailRoutingPrice(){
	   $this->getData[ADMIN_ID] = Zend_Encript_Encription::decode($this->getData[ADMIN_ID]);
	   $userdetail = $this->getCustomerDetails($this->getData[ADMIN_ID]);
	   $this->getData[PARENT_ID]= $userdetail['parent_id'];
	   $select = $this->_db->select()
	  							->from(array('MR'=>MAILPOST_ROUTINGS),array('*'))
								->where("MR.delete_status='0'")
								->where("MR.user_id='".$this->getData[PARENT_ID]."' AND MR.weight_class_id='".$this->getData['weight_class']."' AND MR.country_id='".$this->getData['country_id']."' AND MR.depot_price>0 AND MR.customer_price>0");//echo $select->__toString();die;
	  $routing_price =  $this->getAdapter()->fetchRow($select); 
	  if(!empty($routing_price)){
	     echo json_encode(array('status'=>1,'depot_price'=>($routing_price['depot_price'] * $this->getData['quantity']),'customer_price'=>($routing_price['customer_price'] * $this->getData['quantity']),'remark'=>$routing_price['remark']));die;
	  }else{
	     echo json_encode(array('status'=>0,'message'=>'No Price Found'));die;
	  }
	}
	public function AddMailPost(){
	  global $objSession;
	  $this->getData[ADMIN_ID] = Zend_Encript_Encription::decode($this->getData[ADMIN_ID]);
	  $mail_id = $this->mailID();
	  $manifest_number = commonfunction::mK_time().commonfunction::sub_string(commonfunction::randomDigits(), '0', '-2');
	  $count  = 0;
	  foreach($this->getData['country_id'] as $key=>$country_id){
	     if(isset($this->getData['customer_price'][$key]) && isset($this->getData['depot_price'][$key]) && $this->getData['customer_price'][$key]>0 && $this->getData['depot_price'][$key]>0){
			$insert_data = array();
			$insert_data['manifest_number'] = $manifest_number;
			$insert_data['mail_id'] = $mail_id;
			$insert_data['quantity'] = $this->getData['quantity'][$key];
			$insert_data['weight_class'] = $this->getData['weight_class'][$key];
			$insert_data['country_id'] = $country_id;
			$insert_data['customer_price'] = $this->getData['customer_price'][$key];
			$insert_data['depot_price'] = $this->getData['depot_price'][$key];
			$insert_data['user_id'] = $this->getData['user_id'];
			$insert_data['create_date'] = commonfunction::DateNow();
			$this->insertInToTable(MAIL_POST,array($insert_data));
			$count++;
		 }
		 $objSession->successMsg = 'Mail/post Added successfully';
	  }
	  if($count>0){
	  	$this->generateSingleManifest($manifest_number);
	  }
	 // echo "<pre>";print_r($this->getData);die;
	}
	public function EditMailPost(){  //echo "<pre>";print_r($this->getData);die;
	    global $objSession;
	  $this->getData[ADMIN_ID] = Zend_Encript_Encription::decode($this->getData[ADMIN_ID]);
	  $this->getData['mail_id'] = Zend_Encript_Encription::decode($this->getData['mail_id']);
	  //$mail_id = $this->mailID();
	  $manifest_number = $this->getData['manifest_number'];
	  $count  = 0;
	  foreach($this->getData['country_id'] as $key=>$country_id){
	     if(isset($this->getData['customer_price'][$key]) && isset($this->getData['depot_price'][$key]) && $this->getData['customer_price'][$key]>0 && $this->getData['depot_price'][$key]>0){
			$insert_data = array();
			$insert_data['manifest_number'] = $manifest_number;
			$insert_data['mail_id'] = $this->getData['mail_id'];
			$insert_data['quantity'] = $this->getData['quantity'][$key];
			$insert_data['weight_class'] = $this->getData['weight_class'][$key];
			$insert_data['country_id'] = $country_id;
			$insert_data['customer_price'] = $this->getData['customer_price'][$key];
			$insert_data['depot_price'] = $this->getData['depot_price'][$key];
			$insert_data['user_id'] = $this->getData['user_id'];
			$insert_data['create_date'] = commonfunction::DateNow();
			if(isset($this->getData['mailshipment_id'][$key])){
			    $this->_db->update(MAIL_POST,array('quantity'=>$this->getData['quantity'][$key],'weight_class'=>$this->getData['weight_class'][$key],'country_id'=>$country_id,'user_id'=>$this->getData['user_id']),"mailshipment_id='".$this->getData['mailshipment_id'][$key]."'");
			 }else{
				$this->insertInToTable(MAIL_POST,array($insert_data));
			}
			$count++;
		 }
	  }
	  if($count>0){
	  	$this->generateSingleManifest($manifest_number);
	  }
	}
	public function mailID(){
	  $select = $this->_db->select()
                        ->from(array('MP'=>MAIL_POST), array("mail_id"))
                        ->order("mailshipment_id DESC ")
                        ->limit("1");
        $result = $this->getAdapter()->fetchrow($select);
        return $result['mail_id']+1;
	}
	public function generateSingleManifest($manifest_number){
	    global $labelObj,$objSession;
		$manifestData = $this->getManifestData($manifest_number);
		$labelObj = new Zend_Labelclass_MailManifest('P','mm','a4');
		$labelObj->AddPage();
        $labelObj->MailManifest($manifestData);
        $filename = 'MAIL_MANIFEST_' . mktime() . ".pdf";
        $labelObj->Output(ROOT_PATH.'/label/MAIL_MANIFEST/' . $filename, 'F');
		$objSession->MailPostLabel =  BASE_URL.'/label/MAIL_MANIFEST/' . $filename;
	}
	
	public function PrintManifest(){
	     global $labelObj,$objSession;
		$labelObj = new Zend_Labelclass_MailManifest('P','mm','a4');
		$manifestNumbers = commonfunction::explode_string($this->getData['manifest_number']);
		foreach($manifestNumbers as $manifest_number){
		    $labelObj->AddPage();
			$manifestData = $this->getManifestData($manifest_number);
        	$labelObj->MailManifest($manifestData);
		}
        $filename = 'MAIL_MANIFEST_' . mktime() . ".pdf";
		if(isset($this->getData['ajax'])){
		   $labelObj->Output(ROOT_PATH.'/label/MAIL_MANIFEST/' . $filename, 'F');
		   //$objSession->MailPostLabel =  BASE_URL.'/label/MAIL_MANIFEST/' . $filename;
		   echo json_encode(array('status'=>1,'message'=>BASE_URL.'/label/MAIL_MANIFEST/' . $filename));die;
		}else{
		    $labelObj->Output(ROOT_PATH.'/label/MAIL_MANIFEST/' . $filename, 'F');
			$objSession->MailPostLabel =  BASE_URL.'/label/MAIL_MANIFEST/' . $filename;
		}
        
	}
	
	public function getManifestData($manifest_number){
	   $select = $this->_db->select()
                        ->from(array('MP' => MAIL_POST), array("*"))
                        ->joininner(array("MWC" => MAILPOST_WEIGHTCLASS), "MWC.class_id = MP.weight_class",array("(MWC.min_weight * MP.quantity) AS min_weight_quantity","(MWC.max_weight * MP.quantity) AS max_weight_quantity","CONCAT(MWC.min_weight,'-',MWC.max_weight) AS show_weight_class"))
                        ->joininner(array('CT' => MAIL_POST_COUNTRY), "CT.id=MP.country_id", array('country_name as mail_country_name'))
                        ->joininner(array('AT' => USERS_DETAILS), "AT." . ADMIN_ID . "=MP.user_id", array('email as customer_email', 'company_name as customer_company_name', 'first_name as customer_first_name', 'middle_name as customer_middle_name', 'last_name as customer_last_name', 'customer_name as customer_customer_name', 'address1 as customer_address1', 'address2 as customer_address2', 'city as customer_city', 'postalcode as customer_postalcode', 'phoneno as customer_phoneno'))
                        ->joininner(array('ATT' => USERS_DETAILS), "ATT." . ADMIN_ID . "=AT.parent_id", array('email as depot_email', 'company_name as depot_company_name', 'first_name as depot_first_name', 'middle_name as depot_middle_name', 'last_name as depot_last_name', 'customer_name as depot_customer_name', 'address1 as depot_address1', 'address2 as depot_address2', 'city as depot_city', 'postalcode as depot_postalcode', 'phoneno as depot_phoneno'))
						->joininner(array('ATT1' => USERS_SETTINGS), "ATT." . ADMIN_ID . "=ATT1.user_id",array('logo as depot_logo'))
                        ->where("MP.manifest_number='" . $manifest_number . "' AND MP.checkin_status='0'")
						->order("MP.mailshipment_id ASC"); //echo $select; exit;
        return $this->getAdapter()->fetchAll($select);
	}
	
	public function getMailshipmentList(){
	  $levelwhere = $this->LevelClause();
	  $select = $this->_db->select()
                        ->from(array("MS" => MAIL_POST), array('mail_id','mailshipment_id','manifest_number','create_date', "SUM(customer_price) as total_price", "SUM(quantity) as total_quantity","assigned_date","pickup_status"))
                        ->joininner(array("MWC" => MAILPOST_WEIGHTCLASS), "MWC.class_id = MS.weight_class",array("MWC.min_weight","MWC.max_weight"))
                        ->joininner(array('AT' => USERS_DETAILS), 'AT.' . ADMIN_ID . '=MS.user_id', array('company_name'))
                        ->where("MS.checkin_status='0'".$levelwhere)
                        ->group("MS.manifest_number")
						->order("create_date DESC");                      // echo $select; exit;
        return $this->getAdapter()->fetchAll($select);
	}
	
   public function getMailPostDetails(){
       $this->getData['mail_id'] = Zend_Encript_Encription::decode($this->getData['mail_id']);
	   $select = $this->_db->select()
                        ->from(array("MS" => MAIL_POST), array('*'))
                        ->where("MS.checkin_status='0' AND MS.mail_id='".$this->getData['mail_id']."'");//     echo $select; die;
        return $this->getAdapter()->fetchAll($select);
   }
   public function DeleteMailPost(){
        $manifestNumbers = commonfunction::explode_string($this->getData['manifest_number']);
		 global $objSession;
		foreach($manifestNumbers as $manifest_number){
			$select = $this->_db->select()
							->from(array("MS" => MAIL_POST), array('*'))
							->where("MS.manifest_number='" . $manifest_number . "'");                      // echo $select; exit;
			$mailshipments =  $this->getAdapter()->fetchAll($select);
			if(!empty($mailshipments)){
			  foreach($mailshipments as $mailshipment){ 
			     $mailshipment['deleted_by'] = $this->Useconfig['user_id'];
				 $mailshipment['deleted_date'] = commonfunction::DateNow();
				 $mailshipment['deleted_ip'] = commonfunction::loggedinIP();
			     $this->insertInToTable(MAIL_POST_DELETED,array($mailshipment));
			  }	 
				 $this->_db->delete(MAIL_POST,"checkin_status='0' AND manifest_number='" .$manifest_number. "'");
				 $objSession->successMsg =  'Mail/Post Deleted successfully!';
			}
		}
		return true;
   }
   public function MailCheckinData(){
        $manifestDatas = $this->getManifestData($this->getData['manifest_number']);
		global $translate;
	 if(!empty($manifestDatas)){	
		$count =1;
		$string = '';
		$string .= '<form id="Importshipmentform" class="inputbox" enctype="multipart/form-data" action="" method="post">';
		$string .= '<input type="hidden" name="manifest_number" id="manifest_number" value="'.$this->getData['manifest_number'].'">';
		$string .= '<table class="tbl_new">';
		$string .= '<tbody>';
		$string .= '<tr>';
		$string .= '<th>'.$translate->translate('Country').'</th>';
		$string .= '<th>'.$translate->translate('Weight Class').'</th>';
		$string .= '<th>'.$translate->translate('Quantity').'</th>';
		$string .= '<th>'.$translate->translate('Price').'</th>';
		$string .= '<th>'.$translate->translate('Action').'</th>';
		$string .= '</tr>';
		foreach($manifestDatas as $manifestData){
			$string .= '<tr id="row'.$count.'"><input type="hidden" name="country_id[]" id="country_id'.$count.'" value="'.$manifestData['country_id'].'"><input type="hidden" name="user_id" id="user_id" value="'.Zend_Encript_Encription::encode($manifestData['user_id']).'">';	
			$string .= '<td>'.$manifestData['mail_country_name'].'</td>';
			$string .= '<td><select name="weight_class" id="weight_class'.$count.'" onChange="getMailPrice(this.value)">';
			$weightClass = $this->getMailPostWeightClas(); 
			foreach($weightClass as $class){ 
				$string .= '<option value="'.$class['class_id'].'">'.$class['Classes'].'</option>'; 
			}
			$string .= '</select></td>';
			$string .= '<td><input type="text" name="quantity[]" id="quantity'.$count.'" class="inputfield" value="'.$manifestData['quantity'].'" onblur="getMailPrice('.$count.');" style="width:50%" value="'.$manifestData['depot_price'].'"><input type="hidden" name="depot_price[]" id="depot_price'.$count.'" value="'.$manifestData['depot_price'].'"><input type="hidden" name="customer_price[]" id="customer_price'.$count.'" value="'.$manifestData['customer_price'].'"></td>';
			$string .= '<td id="price'.$count.'">'.$manifestData['customer_price'].'</td>';
			$string .= '<td><i class="fa fa-floppy-o"  onclick="doaction(1,'.$count.','.$manifestData['mailshipment_id'].')"></i>&nbsp;|&nbsp;<i class="fa fa-times" onclick="doaction(0,'.$count.','.$manifestData['mailshipment_id'].')"></i></td>';
			$string .= '</tr>';				 
			$count++;
		}
		$string .= '<tr>';
			$string .= '<td colspan="5" style="text-align:center"><strong>Re-manifest</strong>';
			$string .= '<input type="text" name="remanifest" id="remanifest" class="inputfield" style="width:30%" onkeypress="recheckmanifest(event)"></td>';
			$string .= '</td>';
			$string .= '</tr>';
		$string .= '</tbody>';
		$string .= '</table>';
		$string .= '</form>	';
		echo json_encode(array('Status'=>1,'Response'=>$string,'message'=>''));die;
	 }else{
	    echo json_encode(array('Status'=>0,'message'=>'No Record Found'));die;
	 }	
   }
  
   public function updatemailScaned(){
	   if($this->getData['doaction']==1){
	        $this->_db->update(MAIL_POST,array('quantity'=>$this->getData['quantity'],'weight_class'=>$this->getData['weight_class'],'depot_price'=>$this->getData['depot_price'],'customer_price'=>$this->getData['customer_price']),"mailshipment_id='".$this->getData['mailshipment_id']."'");
		  echo json_encode(array('Status'=>'1','message'=>'Record Updated'));die;
	   }elseif($this->getData['doaction']==0){
	       $this->_db->delete(MAIL_POST,"mailshipment_id='".$this->getData['mailshipment_id']."'");
		  echo json_encode(array('Status'=>'2','message'=>'Record Deleted'));die;
	   }
	     echo json_encode(array('Status'=>'0','message'=>'Something went wrongplease try again!'));die;
   }
   
   public function checkinMailpost(){
      $this->_db->update(MAIL_POST,array('checkin_by'=>$this->Useconfig['user_id'],'checkin_status'=>'1','checkin_ip'=>commonfunction::loggedinIP(),'checkin_date'=>new Zend_Db_Expr('NOW()')),"manifest_number='".$this->getData['remanifest']."'");
		echo 'Check-in Successfully';die;
   }
   
   public function DoManifestaction(){
	   $this->getData['manifest_number'] = commonfunction::implod_array($this->getData['manifest_number'],',');
	  switch($this->getData['manifest_action']){
	     case 1:
		     
		      $this->PrintManifest();
		 break;
		 case 2:
		    //$this->_db->delete(MAIL_POST,"manifest_number='".commonfunction::implod_array($this->getData['manifest_number'],',')."'");
			$this->DeleteMailPost();
			//$objSession->successMsg =  'Mail/Post Deleted successfully!';
		 break;
	  }
   }
   
   public function MailpostHistory(){
      $where = $this->LevelClause();
	  if(isset($this->getData['country_id']) && $this->getData['country_id']>0){
	      $where .= " AND CT.id='".$this->getData['country_id']."'";
	  }
	  if(isset($this->getData['weight_class_id']) && $this->getData['weight_class_id']>0){
	      $where .= " AND MS.class_id='".$this->getData['weight_class_id']."'";
	  }
	  if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){
	      $where .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";
	  }
	  if(isset($this->getData['from_date']) && isset($this->getData['to_date']) && $this->getData['from_date']!='' && $this->getData['to_date']!=''){
	      $where .= " AND DATE(MS.checkin_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";
	  }
	  if(isset($this->getData['search_manifest']) && $this->getData['search_manifest']!=''){
	      $where .= " AND MS.manifest_number='".$this->getData['search_manifest']."'";
	  }
	  $OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'MS.checkin_date','DESC');
	  $select = $this->_db->select()
                        ->from(array("MS" => MAIL_POST), array('COUNT(1) AS CNT'))
                        ->joininner(array("MWC" => MAILPOST_WEIGHTCLASS), "MWC.class_id = MS.weight_class",array(""))
						->joininner(array('CT' => MAIL_POST_COUNTRY), "CT.id=MS.country_id", array(''))
                        ->joininner(array('AT' => USERS_DETAILS), 'AT.' . ADMIN_ID . '=MS.user_id', array(''))
                        ->where("MS.checkin_status='1'".$where)
						 ->group("MS.manifest_number");
       $total =  $this->getAdapter()->fetchAll($select);
		
	  $select = $this->_db->select()
                        ->from(array("MS" => MAIL_POST), array('mail_id','mailshipment_id','manifest_number','create_date', "SUM(customer_price) as total_price", "SUM(quantity) as total_quantity","assigned_date","pickup_status",'checkin_date'))
                        ->joininner(array("MWC" => MAILPOST_WEIGHTCLASS), "MWC.class_id = MS.weight_class",array("MWC.min_weight","MWC.max_weight"))
						->joininner(array('CT' => MAIL_POST_COUNTRY), "CT.id=MS.country_id", array('country_name as mail_country_name'))
                        ->joininner(array('AT' => USERS_DETAILS), 'AT.' . ADMIN_ID . '=MS.user_id', array('company_name'))
                        ->where("MS.checkin_status='1'".$where)
                        ->group("MS.manifest_number")
						->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
						->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);               //echo $select; exit;
        $result =  $this->getAdapter()->fetchAll($select);
	 return array('Total'=>count($total),'Record'=>$result);
   }
   
   public function MailpostDeleted(){
         $select = $this->_db->select()
                        ->from(array("MS" => MAIL_POST_DELETED), array('mail_id','mailshipment_id','manifest_number','create_date', "SUM(customer_price) as total_price", "SUM(quantity) as total_quantity","assigned_date","pickup_status",'delete_date'))
                        ->joininner(array("MWC" => MAILPOST_WEIGHTCLASS), "MWC.class_id = MS.weight_class",array("MWC.min_weight","MWC.max_weight"))
						->joininner(array('CT' => MAIL_POST_COUNTRY), "CT.id=MS.country_id", array('country_name as mail_country_name'))
                        ->joininner(array('AT' => USERS_DETAILS), 'AT.' . ADMIN_ID . '=MS.user_id', array('company_name'))
                        ->group("MS.manifest_number".$where)
						->order("MS.delete_date");              // echo $select; exit;
       return $this->getAdapter()->fetchAll($select);
   } 
}

