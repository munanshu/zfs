<?php
class Helpdesk_Model_ClaimManager extends Zend_Custom
{
   
   public function getclaimstatus(){
	  $where = '1';
      if(isset($this->getData['status_id']) && $this->getData['status_id']!=''){
	    $where .= " AND ".CLAIM_STATUS_ID."='".$this->getData['status_id']."'";
	  }
	  $select= $this->_db->select()
			   ->from(array('CS'=>CLAIM_STATUS), array('*'))
			   ->joinleft(array('MN'=>MAIL_NOTIFY_TYPES), 'MN.notification_id = CS.notification_id', array('notification_name'))
			   ->where("CS.delete_status='0' AND status='1'")
			   ->where($where);
	  $result= $this->getAdapter()->fetchAll($select);
	  return $result;   
   }
   
   
   public function allclaimquestions(){
     if($this->Useconfig['level_id']=='6'){
		   $select = $this->_db->select()
					 ->from(array('CQ'=>CLAIM_QUESTIONS), array('*'));
		   $result = $this->getAdapter()->fetchAll($select);
		   $question_id = array();
		   foreach($result as $operators){
			 $totaloperators = commonfunction::explode_string($operators['operators']);
			 if(commonfunction::inArray($this->Useconfig['user_id'], $totaloperators)){
			   $question_id[] = $operators['question_id'];
			 }
		   }
		   if(!empty($question_id)){
			 $userdata = "CQ.question_id IN(".commonfunction::implod_array($question_id).")";
		   }else{
			 $userdata = '0';
		   }
	   }
	   else if($this->Useconfig['level_id']=='5'){
	     $userdata = '0';
	   }else{
	     $userdata = '1';
	   }
       $select = $this->_db->select()
	            ->from(array('CQ'=>CLAIM_QUESTIONS), array('*'))
				->where($userdata)
				->order('CQ.create_date DESC');
	   $result = $this->getAdapter()->fetchAll($select);
	   $finalresult = array();
	   foreach($result as $data){
	    $operator_list = array();
	    $operator = commonfunction::explode_string($data['operators']);
	    foreach($operator as $operators){
		   $operator_list[] = $this->getCustomerDetails($operators);
		 }
		 $finalresult[] = array('question_id'=>$data['question_id'],'operators'=>$operator_list, 'question'=>$data['question'], 'status'=>$data['status']);
	   }
 	   return $finalresult;
   }
   
   public function GetAllclaimque(){
   	 $select = $this->_db->select()
                        ->from(array('CQ' =>CLAIM_QUESTIONS), array('*'))
                        ->where("CQ.status='Y'")
                        ->order('CQ.question_id ASC'); //echo $select->__toString(); die;
	$result = $this->getAdapter()->fetchAll($select);
	return $result;
   }
   
   
   public function GetshipmentData(){
	  $select = $this->_db->select()
	            ->from(array('SBC'=>SHIPMENT_BARCODE), array('tracenr_barcode'))
                ->joinInner(array('ST'=>SHIPMENT), "SBC.shipment_id=ST.shipment_id", array('user_id'))
				->where("SBC.barcode_id=".$this->getData['barcode_id']);
	   $result = $this->getAdapter()->fetchRow($select);
	   return $result;
   }
   /**
     * getting claimquestion options
     * @param question_id;
	 * Date of creation 20/01/2017
   */
   public function getclaimqueoptionsbyid(){
     $where = "question_id='".$this->getData['question_id']."'";
	 $select = $this->_db->select()
	         ->from(array('CQ'=>CLAIM_QUESTIONS), array('*'))
			 ->where("CQ.question_options!=''")
			 ->where("CQ.status='Y'")
			 ->where($where);
     $result = $this->getAdapter()->fetchAll($select);
	 return $result;
   }
   
   /**
     * getting question by id option
     * @param question_id,sub_question_option;
	 * Date of creation 20/01/2017
   */
   public function getquestionbyidoption(){
     $select = $this->_db->select()
	         ->from(array('CQ'=>CLAIM_QUESTIONS), array('*'))
			 ->where("CQ.sub_question='".$this->getData['question_id']."'")
			 ->where("CQ.sub_question_option='".$this->getData['sub_question_option']."'")
			 ->where("CQ.status='Y'"); 
	$result = $this->getAdapter()->fetchAll($select);
    return $result;
   }
   
   /**
     * 
     * @param question_id,sub_question_option;
	 * Date of creation 20/01/2017
   */
   public function getquestionbyidoptionother(){
     $select = $this->_db->select()
	         ->from(array('CQ'=>CLAIM_QUESTIONS), array('*'))
			 ->where("CQ.sub_question='".$this->getData['question_id']."'")
			 ->where("CQ.sub_question_option!='".$this->getData['sub_question_option']."'")
			 ->where("CQ.status='Y'"); 
	 $result = $this->getAdapter()->fetchAll($select);
     return $result;
   
   }
   
   public function allclaimquestiondata($sub_que){
     if(isset($sub_que['sub_question']) && $sub_que['sub_question'][0]=='0' && $sub_que['sub_question'][1]=='1'){
	   $where ="sub_question='".$sub_que['sub_question'][0]."' OR sub_question='".$sub_que['sub_question'][1]."' ";
	 }
	 else{
	   $where ='1';
	 }
     
	 $select = $this->_db->select()
	         ->from(array('CQ'=>CLAIM_QUESTIONS), array('*'))
			 ->where($where)
			 ->order("CQ.create_date DESC");
	$result = $this->getAdapter()->fetchAll($select);
    return $result; 
   }
   
   
   public function getclaimquestion(){
     $select = $this->_db->select()
	         ->from(array('CQ'=>CLAIM_QUESTIONS), array('*'))
			 ->where("CQ.status='Y'");
	 $result = $this->getAdapter()->fetchAll($select);
     return $result; 
   }
   
   /**
     * Return detail of existing claim question
     * @param question_id;
	 * Date of creation 12/01/2017
   */
   public function claimquebyquestionid(){
     $select = $this->_db->select()
	         ->from(array(CLAIM_QUESTIONS), array('*'))
			 ->where("question_id='".$this->getData['question_id']."'"); //echo $select->__tostring(); die;
	 $result = $this->getAdapter()->fetchAll($select);
     return $result;
   }
   
   /**
     * Add new claim question
     * @param question_id;
	 * Date of creation 25/01/2017
   */
   public function addnewclaimquestion(){
	if(isset($this->getData['opertaors']) && !empty($this->getData['opertaors'])){
	 $operators = commonfunction::implod_array($this->getData['opertaors']);
	}
	else{
	  $operators = '';
 	}
	if($this->getData['question_type']=='file'){
	  $file_upload = 1;
	}else{
	  $file_upload = 0;
	}
	$question_options ='';
	if($this->getData['question_type']=='select' || $this->getData['question_type']=='radio'){
	  foreach($this->getData['questionoption'] as $key=>$que_option){
		$que_value = $this->getData['questionvalue'][$key];
		$question_options .= $que_option.'|'.$que_value.';';
	  }
	}
	if(isset($this->getData['subquestion_a']) && $this->getData['subquestion_a']!=''){
	  $sub_question = isset($this->getData['subquestion_a'])?$this->getData['subquestion_a']:'';
	  $sub_que_option = isset($this->getData['subquestion_option_a'])?$this->getData['subquestion_option_a']:'';
	}else{
	  $sub_question = isset($this->getData['subquestion'])?$this->getData['subquestion']:'';
	  $sub_que_option = isset($this->getData['subquestion_option'])?$this->getData['subquestion_option']:'';
	}
	$lastinserted_id = $this->insertInToTable(CLAIM_QUESTIONS, array(array('sub_question'=>$sub_question,'sub_question_option'=>$sub_que_option,'question_type'=>$this->getData['question_type'],'question'=>$this->getData['question'],'question_options'=>$question_options,'operators'=>$operators,'file_upload'=>$file_upload,'status'=>$this->getData['claim_status'],'created_by'=>$this->Useconfig['user_id'],'created_ip'=>commonfunction::loggedinIP())));
	return $lastinserted_id;
   }
   
   /**
     * update detail of existing claim question
     * @param operators,claimquename,claim_status ;
	 * Date of creation 13/01/2017
   */
   public function updateclaimquestion(){
   try{
	 if(isset($this->getData['operators'])){
	  $opertaors = commonfunction::implod_array($this->getData['operators']);
	  $update = $this->UpdateInToTable(CLAIM_QUESTIONS, array(array('operators'=>$opertaors, 'question'=>$this->getData['claimquename'], 'status'=>$this->getData['claim_status'])), "question_id='".$this->getData['question_id']."'");
	 }
	 else{
	   $update = $this->UpdateInToTable(CLAIM_QUESTIONS, array(array('question'=>$this->getData['claimquename'], 'status'=>$this->getData['claim_status'])), "question_id='".$this->getData['question_id']."'");
	 }
	}
	catch (Exception $e) {
       $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
     } 
	 return $update;
   }
   
   /**
     * fetching email template for claim
     * @param notification_id ;
	 * Date of creation 13/01/2017
   */
   public function fetchtemailemplate(){
     $notification_id = Zend_Encript_Encription:: decode($this->getData['notification_id']);
     $select = $this->_db->select()
	         ->from(array(MAIL_MANAGER), array('*'))
			 ->where("notification_id='".$notification_id."'"); //echo $select->__tostring(); die;
	 $result = $this->getAdapter()->fetchAll($select);
	 return $result;
   }
  
  /**
     * fetching active notification list
	 * Function : notificationlist()
     * @param null;
	 * Date of creation 16/01/2017
  */
  public function notificationlist(){
  
     $select = $this->_db->select()
           ->from(array('MNT'=>MAIL_NOTIFY_TYPES), array('MNT.notification_id','MNT.notification_name','MNT.templatecategory_id'))
		   ->where("MNT.notification_staus='1' AND MNT.templatecategory_id=1")
		   ->order("MNT.notification_name ASC");
     $result = $this->getAdapter()->fetchAll($select);
	 return $result;
  }

  /**
     * Insert new notification
	 * Function : addnewnotification()
     * @param newnotification,templatecategory_id;
	 * Date of creation 16/01/2017
  */
  public function addnewnotification($data){
	$catId = (isset($data)) ? $data : 0;
    $lastInserted_id = $this->insertInToTable(MAIL_NOTIFY_TYPES,array(array('notification_name'=>$this->getData['newnotification'], 'notification_staus'=>'1','admin_display'=>'1', 'templatecategory_id'=>$catId)));
	return $lastInserted_id;
  }
  
  /**
     * Insert new claim status
	 * Function : addnewnotification()
     * @param addnewclaimstatus;
	 * Date of creation 16/01/2017
  */
  public function addclaimstatus(){
    $response = $this->insertInToTable(CLAIM_STATUS, array(array('claim_status_name'=>$this->getData['addnewclaimstatus'], 'created_by'=>$this->Useconfig['user_id'], 'created_ip'=>commonfunction::loggedinIP())));
    return $response;
  }
  
  /**
     * Update claim status 
	 * Function : updateclaimstatus()
	 * Date of creation 16/01/2017
  */
  public function updateclaimstatus($notificationId){
    $response = $this->UpdateInToTable(CLAIM_STATUS, array(array('claim_status_name'=>$this->getData['claimstatusname'], 'notification_id'=>$notificationId, 'modify_by'=>$this->Useconfig['user_id'], 'modify_date'=>'', 'modify_ip'=>commonfunction::loggedinIP())), "".CLAIM_STATUS_ID."=".$this->getData['status_id'])."";
 	return $response;
  }
  
  
  public function getclaimlist(){
    $filter_data = '1'; 

	if(isset($this->getData['claim_id']) && $this->getData['claim_id']!=''){
	  $filter_data .=" AND CT.claim_id=".$this->getData['claim_id']." || SBC.barcode=".$this->getData['claim_id'].""; 
	}
	if(isset($this->getData['country_id']) && $this->getData['country_id']!=''){
	  $filter_data .=" AND ST.country_id=".$this->getData['country_id'].""; 
	}
	if(isset($this->getData['claim_status']) && $this->getData['claim_status']!=''){
	  $filter_data .=" AND CT.claim_status_id=".$this->getData['claim_status'].""; 
	}
	if(isset($this->getData['customer_id']) && $this->getData['customer_id']!=''){
	  $filter_data .=" AND CT.user_id=".$this->getData['customer_id'].""; 
	}
	if(isset($this->getData['depot_id']) && $this->getData['depot_id']!=''){
	  $filter_data .=" AND AT.parent_id=".$this->getData['depot_id'].""; 
	}
	if(isset($this->getData['forwarder_id']) && $this->getData['forwarder_id']!=''){
	  $filter_data .=" AND SBC.forwarder_id=".$this->getData['forwarder_id']."";
	}
	if(!empty($this->getData['from_date']) && !empty($this->getData['to_date'])){
	  $filter_data .=" AND DATE(CT.created_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";
	}
	if(!empty($this->getData['reply_date'])){
	  $filter_data .=" AND DATE(CT.update_date) ='".$this->getData['reply_date']."'";
	}
	if(!empty($this->getData['claim_read_status']) && $this->getData['claim_read_status']==1){
	  if($this->Useconfig['level_id']==5 || $this->Useconfig['level_id']==10){
	    $filter_data .=" AND CT.customer_read_status='1'";
	  }else{
	    $filter_data .=" AND CT.operator_read_status='1'";
	  }
	}
	if(!empty($this->getData['claim_read_status']) && $this->getData['claim_read_status']==2){
	  if($this->Useconfig['level_id']==5 || $this->Useconfig['level_id']==10){
	     $filter_data .=" AND CT.customer_read_status='0'";
	  }else{
	     $filter_data .=" AND CT.operator_read_status='0'";
	  }
	}
	
	$user_level= $this->LevelClause();
	
	$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'CT.created_date','DESC');
	
	$select = $this->_db->select()
	        ->from(array('CT'=>CLAIM_TICKET),array('*'))
			->joinInner(array('AT'=>USERS_DETAILS),"CT.user_id=AT.user_id",array(''))
			->joinInner(array('US'=>USERS_SETTINGS), "AT.user_id=US.user_id", array(''))
			->joinInner(array('SBC'=>SHIPMENT_BARCODE), "CT.barcode_id=SBC.barcode_id", array(''))
			->joinInner(array('SBD'=>SHIPMENT_BARCODE_DETAIL), "SBD.barcode_id=SBC.barcode_id", array(''))
			->joinInner(array('ST'=>SHIPMENT), "SBC.shipment_id=ST.shipment_id", array(''))
			->joinInner(array('FW'=>FORWARDERS), "SBC.forwarder_id=FW.forwarder_id", array(''))
			->joinInner(array('SR'=>SERVICES), "SBC.service_id=SR.service_id", array(''))
			->joinInner(array('CS'=>CLAIM_STATUS), "CT.claim_status_id=CS.claim_status_id", array(''))
			->where($filter_data.$user_level)
			->group('CT.claim_id');
	$result = $this->getAdapter()->fetchAll($select);
	$count = count($result);
	
    $select = $this->_db->select()
	        ->from(array('CT'=>CLAIM_TICKET),array('*'))
			->joinInner(array('AT'=>USERS_DETAILS),"CT.user_id=AT.user_id",array('customer_name','company_name','email','user_id','parent_id'))
			->joinInner(array('US'=>USERS_SETTINGS), "AT.user_id=US.user_id", array('logo'))
			->joinInner(array('SBC'=>SHIPMENT_BARCODE), "CT.barcode_id=SBC.barcode_id", array('tracenr_barcode','weight'))
			->joinInner(array('SBD'=>SHIPMENT_BARCODE_DETAIL), "SBD.barcode_id=SBC.barcode_id", array('checkin_date'))
			->joinInner(array('ST'=>SHIPMENT), "SBC.shipment_id=ST.shipment_id", array('rec_name','rec_contact','rec_street','rec_streetnr','rec_address','rec_street2','rec_zipcode','rec_city','rec_phone','rec_email','country_id','addservice_id','senderaddress_id','goods_id','create_date','goods_description'))
			->joinInner(array('FW'=>FORWARDERS), "SBC.forwarder_id=FW.forwarder_id", array('forwarder_id','forwarder_name'))
			->joinInner(array('SR'=>SERVICES), "SBC.service_id=SR.service_id", array('service_name'))
			->joinInner(array('CS'=>CLAIM_STATUS), "CT.claim_status_id=CS.claim_status_id", array('claim_status_name'))
			->where($filter_data.$user_level)
			->group('claim_id')
			->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
            ->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
	$result = $this->getAdapter()->fetchAll($select); 
	$final_result = array();
	foreach($result as $data){
	$customer_info = $this->getCustomerDetails($data['parent_id']);
	$days_diff = commonfunction::getDatedifference($data['created_date']);
	$final_result[]= array('logo'=>$data['logo'],'id'=>$data['id'],'barcode_id'=>$data['barcode_id'],'claim_id'=>$data['claim_id'],'question'=>$data['question'],'answer'=>$data['answer'],'question_type'=>$data['question_type'],'file_status'=>$data['file_status'],'user_id'=>$data['user_id'],'company_name'=>$data['company_name'],'tracenr_barcode'=>$data['tracenr_barcode'],'weight'=>$data['weight'],'country_id'=>$data['country_id'],'addservice_id'=>$data['addservice_id'],'senderaddress_id'=>$data['senderaddress_id'],'goods_id'=>$data['goods_id'],'create_date'=>$data['create_date'],'goods_description'=>$data['goods_description'],'rec_name'=>$data['rec_name'],'rec_contact'=>$data['rec_contact'],'rec_street'=>$data['rec_street'],'rec_streetnr'=>$data['rec_streetnr'],'rec_address'=>$data['rec_address'],'rec_street2'=>$data['rec_street2'],'rec_zipcode'=>$data['rec_zipcode'],'rec_city'=>$data['rec_city'],'rec_phone'=>$data['rec_phone'],'rec_email'=>$data['rec_email'],'claim_status_id'=>$data['claim_status_id'],'created_date'=>$data['created_date'],'checkin_date'=>$data['checkin_date'],'update_date'=>$data['update_date'],'forwarder_id'=>$data['forwarder_id'],'forwarder_name'=>$data['forwarder_name'],'service_name'=>$data['service_name'],'claim_status_id'=>$data['claim_status_id'],'claim_status_name'=>$data['claim_status_name'],'days_diff'=>$days_diff,'customer_info'=>$customer_info,'Total'=>$count);
	}
	return $final_result;	
  }
  
  
  public function getAllclaim(){
    $filter_data = '1';
	
	if(isset($this->getData['claim_id']) && $this->getData['claim_id']!=''){
	  $filter_data .=" AND CT.claim_id=".$this->getData['claim_id']	; 
	}
	
	$user_level= $this->LevelClause();
	 
	$select = $this->_db->select()
			 ->from(array('CT'=>CLAIM_TICKET),array('*'))
			   ->joinInner(array('AT'=>USERS_DETAILS),"CT.user_id=AT.user_id",array('customer_name','company_name','email','user_id','parent_id'))
			   ->joinInner(array('US'=>USERS_SETTINGS), "AT.user_id=US.user_id", array('logo'))
			   ->joinInner(array('SBC'=>SHIPMENT_BARCODE), "CT.barcode_id=SBC.barcode_id", array('tracenr_barcode','weight'))
			   ->joinInner(array('SBD'=>SHIPMENT_BARCODE_DETAIL), "SBD.barcode_id=SBC.barcode_id", array('checkin_date'))
			   ->joinInner(array('ST'=>SHIPMENT), "SBC.shipment_id=ST.shipment_id", array('rec_name','rec_contact','rec_street','rec_streetnr','rec_address','rec_street2','rec_zipcode','rec_city','rec_phone','rec_email','country_id','addservice_id','senderaddress_id','goods_id','create_date','goods_description'))
			   ->joinInner(array('FW'=>FORWARDERS), "SBC.forwarder_id=FW.forwarder_id", array('forwarder_id','forwarder_name'))
			   ->joinInner(array('SR'=>SERVICES), "SBC.service_id=SR.service_id", array('service_name'))
			   ->joinInner(array('CS'=>CLAIM_STATUS), "CT.claim_status_id=CS.claim_status_id", array('claim_status_name'))
			   ->where($filter_data.$user_level);  //echo $select->__toString(); die;
	 $result = $this->getAdapter()->fetchAll($select);
	$final_result = array();
	foreach($result as $data){
	$customer_info = $this->getCustomerDetails($data['parent_id']);
	$days_diff = commonfunction::getDatedifference($data['created_date']);
	$final_result[]= array('logo'=>$data['logo'],'id'=>$data['id'],'barcode_id'=>$data['barcode_id'],'claim_id'=>$data['claim_id'],'question'=>$data['question'],'answer'=>$data['answer'],'question_type'=>$data['question_type'],'file_status'=>$data['file_status'],'user_id'=>$data['user_id'],'company_name'=>$data['company_name'],'tracenr_barcode'=>$data['tracenr_barcode'],'weight'=>$data['weight'],'country_id'=>$data['country_id'],'addservice_id'=>$data['addservice_id'],'senderaddress_id'=>$data['senderaddress_id'],'goods_id'=>$data['goods_id'],'create_date'=>$data['create_date'],'goods_description'=>$data['goods_description'],'rec_name'=>$data['rec_name'],'rec_contact'=>$data['rec_contact'],'rec_street'=>$data['rec_street'],'rec_streetnr'=>$data['rec_streetnr'],'rec_address'=>$data['rec_address'],'rec_street2'=>$data['rec_street2'],'rec_zipcode'=>$data['rec_zipcode'],'rec_city'=>$data['rec_city'],'rec_phone'=>$data['rec_phone'],'rec_email'=>$data['rec_email'],'claim_status_id'=>$data['claim_status_id'],'created_date'=>$data['created_date'],'checkin_date'=>$data['checkin_date'],'update_date'=>$data['update_date'],'forwarder_id'=>$data['forwarder_id'],'forwarder_name'=>$data['forwarder_name'],'service_name'=>$data['service_name'],'claim_status_id'=>$data['claim_status_id'],'claim_status_name'=>$data['claim_status_name'],'days_diff'=>$days_diff,'customer_info'=>$customer_info);
	}
	return $final_result;	
  }
     
  public function updateclaimliststatus(){
   $this->insertInToTable(CLAIM_TICKET_STATUS, array(array('claim_id'=>$this->getData['claim_id'],'claim_status_id'=>$this->getData['status_id'],'added_by'=>$this->Useconfig['user_id'],'added_ip'=>commonfunction::loggedinIP())));
   $this->UpdateInToTable(CLAIM_TICKET, array(array('claim_status_id'=>$this->getData['status_id'])), "claim_id=".$this->getData['claim_id']);
   $customer_details = $this->getCustomerDetails($this->getData['user_id']); 
   $this->mailonUpdateStatus($customer_details);
   return;
  }
  
  
  public function mailonUpdateStatus($customer_details){
   $status = $this->getclaimstatus($this->getData['status_id']);
   $mailOBj = new Zend_Custom_MailManager();
   $email_text = $customer_details['company_name'].' your claim has been '. $status[0]['claim_status_name'].' having Claim ID - '.$this->getData['claim_id'];
   $mailOBj->emailData['SenderEmail'] = 'info@dpost.be';
   $mailOBj->emailData['SenderName']    = 'NOREPLY';
   $mailOBj->emailData['Subject'] = $customer_details['company_name'].' claim has been '. $status[0]['claim_status_name'];                                
   $mailOBj->emailData['MailBody'] = $email_text;
   $mailOBj->emailData['ReceiverEmail'] = commonfunction::trimString($customer_details['email']); //$customer_details['email']
   $mailOBj->emailData['ReceiverName'] = '';
   $mailOBj->emailData['user_id'] = $this->Useconfig['user_id'];
   $mailOBj->emailData['notification_id'] = 0;
   $mailOBj->Send();
   return true;
  }
  
  /**
     * Update claim data 
	 * Function : updatClaimdata()
	 * Date of creation 27/02/2017
  */
  public function updatClaimdata(){
    $upload = new Zend_File_Transfer();
    $files = $upload->getFileInfo();
	foreach($files as $fileinfo){
		if($fileinfo['name']!=''){
		 $this->getData = array_merge($this->getData,$files);
		}
	}
	if(count($this->getData['quest_id'])>0){
	  foreach($this->getData['quest_id'] as $question_id){
	   if($this->getData['questionfield_'.$question_id]){
	     $this->reuploadclaimDocument($question_id);
	   }
	  }
	}
	
	if($this->Useconfig['level_id']==5){
	  foreach($this->getData['quest_id'] as $key=>$question_id){
	    if($this->getData['questionfield_'.$question_id]!=''){
		  $this->UpdateInToTable(CLAIM_TICKET, array(array('file_status'=>'0')), "id=".$question_id);
		}
	  }
	}
	
	if($this->Useconfig['level_id']!=5){
	  foreach($this->getData['delete_field'] as $delete_id){
	    $this->UpdateInToTable(CLAIM_TICKET, array(array('file_status'=>'1')), "id=".$delete_id);
	  }
	  $this->insertInToTable(CLAIM_TICKET_STATUS, array(array('claim_id'=>$this->getData['claim_id'],'claim_status_id'=>$this->getData['status'],'added_by'=>$this->Useconfig['user_id'],'added_ip'=>commonfunction::loggedinIP())));
	  
	  $this->UpdateInToTable(CLAIM_TICKET, array(array('claim_status_id'=>$this->getData['status'])), "claim_id=".$this->getData['claim_id']);
	  
	}
	return;
  }
  
  
  public function addclaim(){
    if (!empty($_FILES)) {
		$this->getData = array_merge($this->getData, $_FILES);
		$file_array = $this->uploadClaimfile();
	}
    $claim_id = $this->randomClaimnumber();
    foreach ($this->getData as $key => $data_value) { echo $data_value;
           if (strpos($key, 'questionfield') !== false) {
                $question_id = substr($key, strpos($key, "_") + 1);
				$select = $this->_db->select()
	                     ->from(array(CLAIM_QUESTIONS), array('*'))
			             ->where("question_id=".$question_id); //echo $select->__toString(); 
	            $question_detials = $this->getAdapter()->fetchRow($select);
                if ($question_detials['question_type'] == 'select' || $question_detials['question_type'] == 'radio') {
                    $question_options = commonfunction::explode_string($question_detials['question_options'], ";");
                    foreach ($question_options as $option_val) {
                        $question_options_values = commonfunction::explode_string($option_val, "|");
                        if ($option_val != '') {
                            if ($question_options_values[1] == $data_value) {
                                $answer = $question_options_values[0];
                            }
                        }
                    }
                } else if ($question_detials['question_type'] == 'file') {
                    $answer = $file_array['questionfield_'.$question_id];
                } else {
                    $answer = $data_value;
                }
				
				try{
					$lastinserted_id = $this->insertInToTable(CLAIM_TICKET, array(array('claim_id'=>$claim_id,'user_id'=>$this->getData['user_id'],'barcode_id'=>$this->getData['barcode_id'],'question_id'=>$question_detials['question_id'],'question'=>$question_detials['question'],'question_type'=>$question_detials['question_type'],'operators'=>$question_detials['operators'],'answer'=>$answer,'user_ip'=>commonfunction::loggedinIP())));
				   
				   if (isset($question_detials['question_type']) && $question_detials['question_type'] == 'file') {
				   		$this->insertInToTable(CLAIM_TICKET_DOCUMENT, array(array('claim_id'=>$lastinserted_id,'filename'=>$answer,'uploaded_by'=>$this->Useconfig['user_id'],'uploaded_ip'=>commonfunction::loggedinIP())));
				   }
				   
				}
				catch (Exception $e) {
                  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
               }
			   
			   
           }
    }

	$inserted_id = $this->insertInToTable(CLAIM_TICKET_STATUS, array(array('claim_id'=>$claim_id,'claim_status_id'=>'1','added_by'=>$this->Useconfig['user_id'],'added_ip'=>commonfunction::loggedinIP())));
	
	$select = $this->_db->select()
	        ->from(array('CT'=>CLAIM_TICKET), array('claim_id'))
			->where("CT.claim_id=".$inserted_id);
	$Claim_Id = $this->getAdapter()->fetchRow($select);
    
	if($Claim_Id!=''){
	  $customer_details = $this->getCustomerDetails($this->getData['user_id']);
	  $this->sendClaimemail($customer_details);
	}
	return $claim_id;
  
  }
  
  public function uploadClaimfile() {
	$upload = new Zend_File_Transfer();
	$file_info = $upload->getFileInfo();
	$filename = '';
	$fileNameArray = array();
	foreach ($file_info as $key => $files){ 
		$Filename = '';
		$FilePath = '';
		if (!empty($file_info[$key]['name'])) {
			$Filename = date('Y_m_d_H_i_s').$file_info[$key]['name'];
			$fileNameArray[$key] = $Filename;
			$Filepath = CLAIM_SAVE.''.$Filename;
			$upload->addFilter('Rename', array('target' =>$Filepath, 'overwrite' =>false));
			$upload->receive($file_info[$key]['name']);
			$fileinfo[$key] = $Filename;
			$filename .=$Filename;	
		}
	}
	return $fileNameArray;
		
  }
  
  public function randomClaimnumber(){
  	 $length = 10;
	 $random = "";
	 srand((double) microtime() * 1010102000);
	 $data = "1234567890";
	 for ($i = 0; $i < $length; $i++) {
		$random .= substr($data, (rand() % (strlen($data))), 1);
	 }
	 return $random + 1;
   }

  public function sendClaimemail($customer_details){
   try{
       $select = $this->_db->select()
	         ->from(array(MAIL_MANAGER), array('subject','email_text'))
			 ->where("notification_id=46");
	   $result = $this->getAdapter()->fetchRow($select);  
	   $email_text = commonfunction::stringReplace('[COMPANY]',$customer_details['company_name'],$result['email_text']); 
	   $mailOBj = new Zend_Custom_MailManager();
	   
	   $mailOBj->emailData['SenderEmail'] = 'info@dpost.be';
	   $mailOBj->emailData['SenderName']    = 'Parcel NL';
	   $mailOBj->emailData['Subject'] = (!empty($result['subject']))?$result['subject']:'New Claim Notification';                                 
	   $mailOBj->emailData['MailBody'] = $email_text;
	   $mailOBj->emailData['ReceiverEmail'] = commonfunction::trimString($customer_details['email']); //$customer_details['email']
	   $mailOBj->emailData['ReceiverName'] = '';
	   $mailOBj->emailData['user_id'] = $this->Useconfig['user_id'];
	   $mailOBj->emailData['notification_id'] = 0;
	   $mailOBj->Send();
	   }
	   catch (Exception $e) {
         $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
       }
	   return true;
  
  }

  /**
     * Upload claim document
	 * Function : reuploadclaimDocument()
	 * Date of creation 27/02/2017
  */
  public function reuploadclaimDocument($fileindex){
	$upload = new Zend_File_Transfer();
	$file_info = $upload->getFileInfo();
	if($file_info['questionfield_'.$fileindex]){
	  if($file_info['questionfield_'.$fileindex]['name']!=''){
	     $datetime = date('Y_m_d_H_i_s');
	     $Filename = $datetime.''.$file_info['questionfield_'.$fileindex]['name'];
	     $Filepath = CLAIM_SAVE.''.$Filename;
	     $upload->addFilter('Rename', array('target' =>$Filepath, 'overwrite' => true));
	     $upload->receive();
		 $added_status = $this->insertInToTable(CLAIM_TICKET_DOCUMENT, array(array('claim_id'=>$fileindex,'filename'=>$Filename,'uploaded_by'=>$this->Useconfig['user_id'],'uploaded_ip'=>commonfunction::loggedinIP())));
		 if($added_status){
		  $this->UpdateInToTable(CLAIM_TICKET, array(array('answer'=>$Filename)), "id=".$fileindex);
		 }
	  }  
	}
	return;
	
  }
  
  /**
     * update claimread status
	 * Function : UpdateClaimread()
	 * Date of creation 28/02/2017
  */
  public function UpdateClaimread(){
    if($this->Useconfig['level_id']!=5){
	  $this->_db->update(CLAIM_TICKET,array('customer_read_status'=>'0','operator_read_status'=>'1','update_date'=>new Zend_Db_Expr('NOW()')), "claim_id=".$this->getData['claim_id']);
	}
	if($this->Useconfig['level_id']==5){
	 $this->_db->update(CLAIM_TICKET,array('customer_read_status'=>'1','operator_read_status'=>'0','update_date'=>new Zend_Db_Expr('NOW()')), "claim_id=".$this->getData['claim_id']);
	}
	return;
  }

  public function replyByoperator(){
    $select = $this->_db->select()
	        ->from(array(CLAIM_TICKET_REPLY), array('*'))
			->where("claim_id=".$this->getData['claim_id'])
			->order("reply_date DESC"); //echo $select->__toString(); die;
    $result = $this->getAdapter()->fetchAll($select);
	return $result;
  }
  
  public function addclaimreply(){
    $this->insertInToTable(CLAIM_TICKET_REPLY, array(array('claim_id'=>$this->getData['claim_id'],'reply_message'=>$this->getData['reply_message'],'reply_by'=>$this->Useconfig['user_id'],'reply_ip'=>commonfunction::loggedinIP())));
	$customer_details = $this->getCustomerDetails(Zend_Encript_Encription::decode($this->getData['user_id']));
	// $this->sendemail($customer_details);
	return;
  }
  
  public function sendemail($customer_details){
   try{
	   $mailOBj = new Zend_Custom_MailManager();
	   $email_text = 'Reply for '. $customer_details['company_name'] .' claim having Claim ID - '.$this->getData['claim_id'].' : '.$this->getData['reply_message'];
	   $mailOBj->emailData['SenderEmail'] = 'info@dpost.be';
	   $mailOBj->emailData['SenderName']    = 'NOREPLY';
	   $mailOBj->emailData['Subject'] = 'Reply for '.$customer_details['company_name'].' claim having Claim ID - '.$this->getData['claim_id'];
	   $mailOBj->emailData['MailBody'] = $email_text;
	   $mailOBj->emailData['ReceiverEmail'] = commonfunction::trimString($customer_details['email']);  //$customer_details['email']
	   $mailOBj->emailData['ReceiverName'] = '';
	   $mailOBj->emailData['user_id'] = $this->Useconfig['user_id'];
	   $mailOBj->emailData['notification_id'] = 0;
	   $mailOBj->Send();
   }
   catch (Exception $e) {
         $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
   }
   return true;
  }
  
  
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

}
?>