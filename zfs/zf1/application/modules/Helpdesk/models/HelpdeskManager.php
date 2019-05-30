<?php

class Helpdesk_Model_HelpdeskManager extends Zend_Custom

{



   /**

	 * Fetching All Languages

	 * Function : systemlanguage()

	 * Date of creation 06/01/2017

   **/

	public function systemlanguage($data=array()){

		$select = $this->_db->select()

							->from(LANGUAGE,array('*'))

							->order(array('language_name ASC'));

		$result = $this->getAdapter()->fetchAll($select);

		return $result; 

	}



	

	public function addedquestiondata($data){

	  $select = $this->_db->select()

	           ->from(array('HQ'=>HELPDESK_QUESTION), array('*'))

			   ->joinInner(array('HQL'=>HELPDESK_QUESTION_LANGUAGE), "HQ.question_id= HQL.question_id", array('*'))

	   		   ->where("HQL.question_id=".$data['question_id']);

	  $result = $this->getAdapter()->fetchAll($select);

	  return $result;		   

	}

	

	    

	public function allquestionrecord(){

	  $select= $this->_db->select()

	           ->from(array('HQ'=>HELPDESK_QUESTION), array('*'))

			   ->joinInner(array('HQL'=>HELPDESK_QUESTION_LANGUAGE),'HQ.question_id=HQL.question_id', array('question_type'=>'question_type'))

			   ->where("HQL.language_id=3 AND HQ.delete_status='0'"); //echo $select->__tostring(); die;

	  $result= $this->getAdapter()->fetchAll($select); 

	  $finalresult = array();

      foreach($result as $listvalue){

	    $company_name = $this->getCompanyname($listvalue['operators']);

	    $steps=$this->allsteps($listvalue['question_id']);

		$finalresult[]=array('question_id'=>$listvalue['question_id'], 'question_type'=>$listvalue['question_type'], 'operators'=>$listvalue['operators'], 'steps'=>$steps, 'company_name'=>$company_name);

	  }

	  return $finalresult;

	}

	 

    

	public function getCompanyname($operators){

	  for($i=0; $i<count($operators); $i++){

		  $operator = commonfunction::explode_string($operators);

	   for($j=0; $j<count($operator); $j++){

		$single_operator = $operator[$j];

		$company_name[]=$this->getCustomerDetails($single_operator);

	   }	

	 } 

	 return $company_name;

	}

	

	

	public function allsteps($question_id){

	  $step= '';

	  if($this->Useconfig['level_id']=='5'){

	    $step = "AND step_auth='1'";

	  }

	  $questionid= (isset($question_id))? $question_id : '';

	  $select= $this->_db->select()

	           ->from(array(HELPDESK_QUESTION_DETAILS), array('*'))

			   ->where("question_id='".$questionid."'".$step);	   

	  $result= $this->getAdapter()->fetchAll($select);

	  return $result;

	}

	

	

	public function showsteps(){

	  $step= '';

	  if($this->Useconfig['level_id']=='5'){

	    $step = "AND step_auth='1'";

	  }

	  $select= $this->_db->select()

	           ->from(array(HELPDESK_QUESTION_DETAILS), array('*'))

			   ->where("question_id='".$this->getData['question_id']."'".$step);

	  $result= $this->getAdapter()->fetchAll($select);

	  return $result;

	}

	

	

	public function getparentstep($question_id,$steps,$parent_step,$last=false){

	  if($last){

	    $where = "step<='".$steps."'";

	  }

	  else{

	    $where = "step<'".$steps."'";

	  }

	  $select = $this->_db->select()

	          ->from(array(HELPDESK_QUESTION_DETAILS), array('*'))

			  ->where("question_id=".$question_id)

			  ->where($where);

	  $results = $this->getAdapter()->fetchAll($select);

	  $string = '';

	  foreach($results as $step){

	   $selected = '';

	   if($step['step']==$parent_step){

	     $selected = 'selected="selected"';

	   }

	   $string .= '<option value="'.$step['step'].','.$step['steps'].'" '.$selected.'>'.$step['steps'].'</option>';

	  }

	   return $string;

    }

	

	

	public function addnewquestion(){

	  $operators = commonfunction::implod_array($this->getData['operators']);

	  $create_by = $this->Useconfig['user_id'];

	  $create_ip = commonfunction::loggedinIP();

	  $question_eng = $this->getData['question']['1'];

	  

	  $data=array('question_type'=>$question_eng, 'operators'=>$operators, 'create_date'=>'', 'create_by'=>$create_by, 'create_ip'=>$create_ip);

	  $this->insertInToTable(HELPDESK_QUESTION, array($data));

	  

	  $lastinserted_id =  $this->_db->lastInsertId();

	  

	  $question_type = $this->getData['question'];

	  foreach($question_type as $key=> $question){

	   $data= array('question_type'=>$question, 'question_id'=>$lastinserted_id, 'language_id'=>$key);

	   $this->insertInToTable(HELPDESK_QUESTION_LANGUAGE, array($data));

	  }

	  

	  return $lastinserted_id;

	}

    

	

	public function updatequestions($data){

	  try{

		  $operators= commonfunction::implod_array($data['operators']);

		  $this->UpdateInToTable(HELPDESK_QUESTION, array(array('operators'=>$operators)), "question_id=".$data['question_id']);

		  $question_type = $this->getData['question'];

		  foreach($question_type as $key=>$questions){

			$select = $this->_db->select()

					->from(array(HELPDESK_QUESTION_LANGUAGE), array('COUNT(1) as CNT'))

					->where("question_id='".$data['question_id']."' AND language_id='".$key."'");

			$result = $this->getAdapter()->fetchRow($select);

			if($result['CNT']>0){

			  $this->UpdateInToTable(HELPDESK_QUESTION_LANGUAGE, array(array('question_type'=>$questions)), "question_id='".$data['question_id']."' AND language_id='".$key."'");

			}

			else{

			  $this->insertInToTable(HELPDESK_QUESTION_LANGUAGE, array(array('question_type'=>$questions,'question_id'=>$data['question_id'],'language_id'=>$key)));

			}

		 } 

	  }

	  

	  catch (Exception $e) {

      $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

     }

	

	}



    public function steplist(){

	 $select = $this->_db->select()

	           ->from(array(HELPDESK_STEPS), array('*'));

	 $result= $this->getAdapter()->fetchAll($select);

	 return $result;

	}

    

	

	public function addstepandgetnewrow($data){

	   $steps = commonfunction::explode_string($data['step']);

	   $parent_step = commonfunction::explode_string($data['parent_step']);

	   $insert_data= array('question_id'=>$data['question_id'], 'step'=>$steps['0'], 'steps'=>$steps['1'], 'parent_step'=>$parent_step['0'], 'step_name'=>$data['step_name'], 'instruction'=>$data['instruction'], 'documents_uploade'=>$data['document_upload'], 'parent_status'=>$data['parent_status'], 'step_auth'=>$data['step_auth'], 'count'=>$data['count']-1);

	   $this->insertInToTable(HELPDESK_QUESTION_DETAILS, array($insert_data));

	  

	   $select = $this->_db->select()

		                  ->from(array(HELPDESK_QUESTION_DETAILS),array('parent_step','step_name','steps','parent_status'))

						  ->where("question_id=".$data['question_id'])

						  ->where("step<='".$data['count']."'");

						  //echo $select->__tostring(); die;

		$result =$this->getAdapter()->fetchAll($select);	

		foreach($result as $results){

		$parent_step .= '<option value="'.$data['step'].'">'.$results['steps'].'</option>';

		}

		$value = '';

		$value .= '<tr id="row'.($data['count']+1).'">';

		$value .= '<td><select id="step'.($data['count']+1).'" name="step'.($data['count']+1).'" class="inputfield">

						 <option value="0">--Select Step--</option>';

		 $steplist= $this->steplist();

		 foreach($steplist as $stepdata){

		 $value .= '<option value="'.$stepdata['value'].'">'.$stepdata['steplist'].'</option>';

		 }		 

		$value .= '</select></td>';

		$value .= '<td><input type="text" class="inputfield" name="steps_name'.($data['count']+1).'" id="steps_name'.($data['count']+1).'"></td>';

		$value .= '<td><textarea cols="20" rows="3" name="instruction'.($data['count']+1).'" id="instruction'.($data['count']+1).'"></textarea></td>';

		$value .= '<td><input type="checkbox" name="document_upload'.($data['count']+1).'">Documents Required</td>';

		$value .= '<td><select name="parent_step'.($data['count']+1).'" class="inputfield" id="parent_step'.($data['count']+1).'">';

		$value .= $parent_step;

		$value .= '</select>';

		$value .= '<input type="radio" name="status'.($data['count']+1).'" id="status'.($data['count']+1).'"  value="1"/>Yes';

		$value .= '<input type="radio" name="status'.($data['count']+1).'" id="status'.($data['count']+1).'" value="0" />No';

		$value .= '</td>';

		$value .= '<td><input type="radio" name="step_auth'.($data['count']+1).'" id="step_auth'.($data['count']+1).'" value="1">Customer';

		$value .= '<input type="radio" name="step_auth'.($data['count']+1).'" id="step_auth'.($data['count']+1).'" value="0">Operator</td>';

		$value .= '<td><i class="fa fa-plus-square" onclick="add_more('.($data['count']+1).')"></i></td>';

		

		return $value;

	}

    

	public function updatesteps($data){

	  $steps = commonfunction::explode_string($data['step']);

	  $parent_step = commonfunction::explode_string($data['parent_step']);

	  $update_data = array('step'=>$steps['0'], 'steps'=>$steps['1'], 'parent_step'=>$parent_step['0'], 'step_name'=>$data['step_name'], 'instruction'=>$data['instruction'], 'documents_uploade'=>$data['document_upload'], 'parent_status'=>$data['parent_status'], 'step_auth'=>$data['step_auth']);

	  $this->UpdateInToTable(HELPDESK_QUESTION_DETAILS, array($update_data), "question_id='".$data['question_id']."' AND count='".$data['count']."'");

	}

	

	

	public function allheldeskstatus(){

	  $select = $this->_db->select()

	           ->from(array('HS'=>HELPDESK_STATUS), array('*'))

			   ->joinleft(array('MNT'=>MAIL_NOTIFY_TYPES), "HS.".NOTIFICATION_ID." = MNT.".NOTIFICATION_ID."", array('notification_name'))

				->where("HS.delete_status='0'"); 

	  $result= $this->getAdapter()->fetchAll($select);

	  return $result;

	}

	

    /**

     * Insert new heldesk status

	 * Function : addnewnotification()

     * @param helpdeskname;

	 * Date of creation 18/01/2017

  */

    public function addnewhelpdeskstatus(){

	  $response = $this->insertInToTable(HELPDESK_STATUS, array(array('helpdesk_status_name'=>$this->getData['helpdeskname'], 'created_by'=>$this->Useconfig['user_id'], 'created_ip'=>commonfunction::loggedinIP())));

    return $response;

	}

	

   /**

	 * Fetching email template on behalf of notifcaion id

	 * Function : fetchemailtemplate()

	 * Date of creation 11/01/2017

    **/

	public function fetchemailtemplate($data=array()){

	  $select = $this->_db->select()

	          ->from(array(MAIL_MANAGER), array('*'))

			  ->where("".NOTIFICATION_ID."='".$data[NOTIFICATION_ID]."'");

	  $result = $this->getAdapter()->fetchAll($select);

	  return $result;

	}

	

	/**

	 * Getting helpdesk information

	 * Function : helpdeskinformation()

	 * Date of creation 31/01/2017

    **/

	public function ticketinformation(){

	  $filter_data = '1';

	  if(isset($this->getData['ticket_barcode']) && !empty($this->getData['ticket_barcode'])){

	    $filter_data .= " AND (HT.ticket_no='".$this->getData['ticket_barcode']."' || SBC.barcode='".$this->getData['ticket_barcode']."')";

	  }

	  if(isset($this->getData['country']) && !empty($this->getData['country'])){

	    $filter_data .= " AND ST.country_id='".$this->getData['country']."'";

	  }

	  if(isset($this->getData['status']) && !empty($this->getData['status'])){

	    $filter_data .= " AND HT.is_status='".$this->getData['status']."'";

	  }

	  if(isset($this->getData['customers']) && !empty($this->getData['customers'])){

	    $filter_data .= " AND HT.user_id='".$this->getData['customers']."'";

	  }

	  if(isset($this->getData['forwarders']) && !empty($this->getData['forwarders'])){

	    $filter_data .= " AND SBC.forwarder_id='".$this->getData['forwarders']."'";

	  }

	  if(!empty($this->getData['from_date']) && !empty($this->getData['to_date'])){

	    $filter_data .= " AND DATE(HT.create_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";

	  }

	  if(isset($this->getData['id']) && !empty($this->getData['id']) && $this->getData['id']==1){

	    $filter_data .= " AND HT.is_status!=8 AND HT.added_from='1'";

	  }

	  if(isset($this->getData['id']) && !empty($this->getData['id']) && $this->getData['id']==2){

	    $filter_data .= " AND HT.is_status=8";

	  }

	  if(empty($this->getData['ticket_barcode']) && empty($this->getData['country']) && empty($this->getData['status']) && empty($this->getData['customers']) && empty($this->getData['forwarders']) && empty($this->getData['from_date']) && empty($this->getData['to_date']) && empty($this->getData['to_date']) && empty($this->getData['id'])){

	  

		  if($this->Useconfig['level_id']==5 || $this->Useconfig['level_id']==10){

		    $filter_data .= " AND HT.is_status!=8 AND HT.delete_status='0'";

		  }

		  else{

		    $filter_data .= " AND HT.operator_read_status='0' AND HS.operator_notify='1' AND HT.delete_status='0' AND HT.added_from='1'";

		  }

	  

	  } 

	  $userdata = $this->LevelClause();

	

	  $OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'HT.create_date','DESC');

	  $select = $this->_db->select()

	          ->from(array('HT'=>HELPDESK_TICKET), array('COUNT(1) AS CNT'))

			  ->joinInner(array('HTD'=>HELPDESK_TICKET_DETAIL), 'HT.helpdesk_token=HTD.helpdesk_token', array(''))

			  ->joinInner(array('HQS'=>HELPDESK_QUESTION), 'HT.question_id=HQS.question_id', array(''))

			  ->joinInner(array('AT'=>USERS_DETAILS), 'HT.user_id=AT.user_id', array(''))

			  ->joinleft(array('US'=>USERS_SETTINGS), 'US.user_id=AT.user_id', array(''))

			  ->joinInner(array('SBC'=>SHIPMENT_BARCODE), 'HT.barcode_id=SBC.barcode_id', array(''))

			  ->joinInner(array('ST'=>SHIPMENT), 'ST.shipment_id=SBC.shipment_id', array(''))

			  ->joinInner(array('HS'=>HELPDESK_STATUS), 'HT.is_status=HS.helpdesk_status_id', array(''))

			  ->where("HT.delete_status='0'")

			  ->where($filter_data.$userdata)

			  ->group('HT.ticket_no');

	  $total = $this->getAdapter()->fetchAll($select);

	  $count = count($total);

   

	  $select = $this->_db->select()

	          ->from(array('HT'=>HELPDESK_TICKET), array('*'))

			  ->joinInner(array('HTD'=>HELPDESK_TICKET_DETAIL), 'HT.helpdesk_token=HTD.helpdesk_token', array('messages'))

			  ->joinInner(array('HQS'=>HELPDESK_QUESTION), 'HT.question_id=HQS.question_id', array('question_id','question_type'))

			  ->joinInner(array('SBC'=>SHIPMENT_BARCODE), 'HT.barcode_id=SBC.barcode_id', array('tracenr_barcode'))

			  ->joinInner(array('ST'=>SHIPMENT), 'ST.shipment_id=SBC.shipment_id', array('rec_name'))

			  ->joinInner(array('AT'=>USERS_DETAILS), 'HT.user_id=AT.user_id', array('user_id','company_name','customer_name'))

			  ->joinleft(array('US'=>USERS_SETTINGS), 'US.user_id=AT.user_id', array('logo'))

			  ->joinInner(array('HS'=>HELPDESK_STATUS), 'HS.helpdesk_status_id=HT.is_status', array('') )

			  ->where("HT.delete_status='0'")

			  ->where($filter_data.$userdata)

			  ->group('HT.ticket_no')

			  ->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])

              ->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);

	  $result = $this->getAdapter()->fetchAll($select);

	  $final_result = array();

	  foreach($result as $data){

	   $customer = $this->getCustomerDetails($data['user_id']);

	   $date_difference = commonfunction::getDatedifference($data['create_date']);

	   $final_result[] = array('helpdesk_token'=>$data['helpdesk_token'], 'logo'=>$data['logo'], 'ticket_no'=>$data['ticket_no'], 'question_category'=>$data['question_category'], 'company_name'=>$data['company_name'], 'rec_name'=>$data['rec_name'], 'barcode_id'=>$data['barcode_id'], 'tracenr_barcode'=>$data['tracenr_barcode'], 'create_date'=>$data['create_date'], 'question_type'=>$data['question_type'], 'messages'=>$data['messages'], 'date_difference'=>$date_difference, 'forward_to'=>$data['forward_to'],'is_status'=>$data['is_status'], 'customer'=>$customer,'Total'=>$count);

	  }

	  return $final_result;

	}

	

	public function getForwardDetail($ticketDetail){

		if(commonfunction::inArray($this->Useconfig['user_id'],commonfunction::explode_string($ticketDetail['forward_to']))){

		   $select = $this->_db->select()

                        ->from(array('HTD'=>HELPDESK_TICKET_DETAIL),array('*'))

						->joininner(array('HT'=>HELPDESK_TICKET),"HT.helpdesk_token=HTD.helpdesk_token",array())

                        ->joinleft(array('AT'=>USERS_DETAILS),"AT.user_id=HTD.forward_id",array('company_name'))

                        ->where("((HTD.forward_id='".$this->Useconfig['level_id']."' AND HTD.activity_id=4) OR (HTD.activity_by='".$this->Useconfig['user_id']."'  AND SHD.activity_id=5))")

						->where("HT.ticket_no='".$ticketDetail['ticket_no']."' AND HTD.helpdesk_token='".$ticketDetail['helpdesk_token']."'")

						->where("SHD.activity_id IN(4,5)"); 

      	  $results = $this->getAdapter()->fetchAll($select);

		  $string = '';

		  foreach($results as $forwads){

			 if($this->Useconfig['user_id']==$forwads['forward_id']){

			   $string .= '<b>Operator : </b>'.$forwads['company_name'].'<br>';

			   $string .= '<b>Send : </b>'.strip_tags($forwads['messages']).'<br>';

			 }elseif($this->Useconfig['user_id']==$forwads['activity_by']){

			   $string .= '<b>Reply : </b>'.strip_tags($forwads['messages']);

			 }

		  }	

		}else{

		    $select = $this->_db->select()

                        ->from(array('HTD'=>HELPDESK_TICKET_DETAIL),array('*'))

						->joininner(array('HT'=>HELPDESK_TICKET),"HT.helpdesk_token=HTD.helpdesk_token",array())

                        ->joinleft(array('AT'=>USERS_DETAILS),"AT.user_id=HTD.forward_id",array('company_name'))

                        ->where("HTD.activity_id IN(4,5)")

						->where("HT.ticket_no='".$ticketDetail['ticket_no']."' AND HTD.helpdesk_token='".$ticketDetail['helpdesk_token']."'"); 

      		 $results = $this->getAdapter()->fetchAll($select);

			$string = '';

		   foreach($results as $forwads){

		       $string3 = '';

			 if($forwads['activity_id']==4){

			   $string1 = '<b>Operator : </b>'.$forwads['company_name'].'<br>';

			   $string2 = '<b>Send : </b>'.strip_tags($forwads['messages']).'<br>';

			 }elseif($forwads['activity_id']==5){

			   $string3 = '<b>Reply : </b>'.strip_tags($forwads['messages']).'<br>';

			 } 

			

		}

		$url = "'".BASE_URL."/Helpdesk/viewforwarddetail?ticket_no=".$ticketDetail['ticket_no']."'";

		$string = (isset($string1) && isset($string2) && isset($string3) && $string1.$string2.$string3!='')?$string1.$string2.$string3.'<a href="javascript:void(0)" onclick="openbox('.$url.');">[Detail]</a>':'';

	 }

	 return $string;

   }

   

   

   public function viewForwarderDetail(){

     $select = $this->_db->select()

                        ->from(array('HTD'=>HELPDESK_TICKET_DETAIL),array('*'))

						->joininner(array('HT'=>HELPDESK_TICKET),"HT.helpdesk_token=HTD.helpdesk_token",array())

                        ->joinleft(array('AT'=>USERS_DETAILS),"AT.user_id=HTD.forward_id",array('company_name'))

                        ->where("HTD.activity_id IN(4,5)")

						->where("HT.ticket_no=".$this->getData['ticket_no']);

      $result = $this->getAdapter()->fetchAll($select);

	  return $result;

   }

   

   

	/**

	 * List all status 

	 * Function : getstatuslist()

	 * Date of creation 11/01/2017

    **/

	public function getstatuslist($status_id = false){

	  $where = '';

	  if(isset($status_id) && $status_id!=''){

	    $where = " AND HS.helpdesk_status_id='".$status_id."'"; 

	  }

	  $select = $this->_db->select()

	          ->from(array('HS'=>HELPDESK_STATUS), array('*'))

			  ->joinleft(array('MNT'=>MAIL_NOTIFY_TYPES), "HS.".NOTIFICATION_ID." = MNT.".NOTIFICATION_ID."", array('notification_name'))

			  ->where("delete_status='0'".$where); //echo $select->__tostring(); die;

	  $result = $this->getAdapter()->fetchAll($select);

	  return $result; 

	}

	

	public function getNotificationlist(){

	 $select = $this->_db->select()

           ->from(array('MNT'=>MAIL_NOTIFY_TYPES), array('MNT.notification_id','MNT.notification_name','MNT.templatecategory_id'))

		   ->where("MNT.notification_staus='1' AND MNT.templatecategory_id=2")

		   ->order("MNT.notification_name ASC"); //echo $select->__toString(); die;

     $result = $this->getAdapter()->fetchAll($select);

	 return $result;

	

	}

	

	

	public function addnewnotification($data){

	$catId = (isset($data)) ? $data : 0;

    $lastInserted_id = $this->insertInToTable(MAIL_NOTIFY_TYPES,array(array('notification_name'=>$this->getData['notification_name'], 'notification_staus'=>'1','admin_display'=>'1', 'templatecategory_id'=>$catId)));

	return $lastInserted_id;

  }

  

  

  public function updatehelpdeskstatus($notificationId){

    $response = $this->UpdateInToTable(HELPDESK_STATUS, array(array('helpdesk_status_name'=>$this->getData['helpdskstatusname'], 'notification_id'=>$notificationId, 'modify_by'=>$this->Useconfig['user_id'], 'modify_date'=>'', 'modify_ip'=>commonfunction::loggedinIP())), "helpdesk_status_id=".Zend_Encript_Encription:: decode($this->getData['status_id']));

 return $response;

  }

	

    /**

	 * Fetching list of FAQ's

	 * Function : faqdetails()

	 * Date of creation 27/01/2017

    **/



    public function faqdetails(){

	  $select = $this->_db->select()

	          ->from(array(HELPDESK_FAQ_DETAILS), array('*'))

			  ->where("delete_status='0'");

	  $result = $this->getAdapter()->fetchAll($select);

	  return $result;

	}

	

	 /**

	 * Adding new FAQ's question and helpdesk question

	 * Function : faqdetails()

	 * Date of creation 27/01/2017

    **/

	public function addnewfaqquestion(){

	  $operators= commonfunction::implod_array($this->getData['operatortype']);

	  $this->insertInToTable(HELPDESK_FAQ_DETAILS, array(array('operators'=>$operators, 'question'=>$this->getData['question'], 'answer'=>$this->getData['answer'], 'question_type'=>$this->getData['que_type'], 'create_date'=>'', 'created_by'=>$this->Useconfig['user_id'], 'created_ip'=>commonfunction::loggedinIP())));

	  return;

	}

    

	/**

	 * Fetching data of existing FAQ's question by question id

	 * Function : updatequedetails()

	 * @param que_id;

	 * Date of creation 28/01/2017

    **/

	public function updatequedetails(){

	 $faq_que_id = (isset($this->getData['que_id']))?Zend_Encript_Encription:: decode($this->getData['que_id']):'';

	 $select = $this->_db->select()

	         ->from(array(HELPDESK_FAQ_DETAILS), array('*'))

			 ->where("faq_id=".$faq_que_id);

	 $result = $this->getAdapter()->fetchAll($select);

	 return $result;

	}

	

	/**

	 * Update FAQ's and Helpdesk question, answer

	 * Function : updatefaqquestion()

	 * Date of creation 28/01/2017

    **/

	public function updatefaqquestion(){

	 $opertaors= commonfunction::implod_array($this->getData['operators']);

	 $this->UpdateInToTable(HELPDESK_FAQ_DETAILS, array(array('operators'=>$opertaors,'question'=>$this->getData['question'], 'answer'=>$this->getData['answer'], 'modify_date'=>'', 'modify_by'=>$this->Useconfig['user_id'], 'modify_ip'=>commonfunction::loggedinIP())), "faq_id=".Zend_Encript_Encription:: decode($this->getData['que_id']));

	 return;

	}

	

	/**

	 * Update ticket read status based on level_id

	 * Function : updateTicketreadstatus()

	 * Date of creation 06/02/2017

    **/

	public function updateTicketreadstatus(){

	  if($this->Useconfig['level_id']==5){

	    $update = $this->UpdateInToTable(HELPDESK_TICKET,array(array('customer_read_status'=>1)), "helpdesk_token=".Zend_Encript_Encription::decode($this->getData['helpdesk_token']));

	  }

	  else{

	    $update = $this->UpdateInToTable(HELPDESK_TICKET,array(array('read_status'=>1)), "helpdesk_token=".Zend_Encript_Encription::decode($this->getData['helpdesk_token']));

	  }

	  return $update;

	}

	

	

	public function updateReadStatus($forward_to){

	  if($this->Useconfig['level_id']==5){

	    $this->_db->update(HELPDESK_TICKET_DETAIL,array('activity_read'=>'1'),"helpdesk_token=".Zend_Encript_Encription::decode($this->getData['helpdesk_token'])." AND activity_id=2");

	  }

	  elseif(in_array($this->Useconfig['user_id'],explode(',',$forward_to))){

	    $this->_db->update(HELPDESK_TICKET_DETAIL,array('activity_read'=>'1'),"helpdesk_token=".Zend_Encript_Encription::decode($this->getData['helpdesk_token'])." AND activity_id=4 AND forward_id=".$this->Useconfig['user_id']);

	  }

	  else{

	    $this->_db->update(HELPDESK_TICKET_DETAIL,array('activity_read'=>'1'),"helpdesk_token=".Zend_Encript_Encription::decode($this->getData['helpdesk_token'])." AND activity_id IN(3,5)");

	  }

	

	}

	

	

    /**

	 * Update ticket status

	 * Function : changeTicketstatus()

	 * Date of creation 06/02/2017

    **/

	public function changeTicketstatus(){

	  $respone = $this->UpdateInToTable(HELPDESK_TICKET, array(array('is_status'=>$this->getData['status_id'])), "helpdesk_token=".Zend_Encript_Encription::decode($this->getData['helpdesk_token']));

	  return $respone;

	}

	

	/**

	 * get ticket information

	 * Function : GetTicketinfo()

	 * @param helpdesk_token,ticket_no;

	 * Date of creation 08/02/2017

    **/

	public function GetTicketinfo(){

	 $select = $this->_db->select()

	         ->from(array('HT'=>HELPDESK_TICKET), array('*'))

			 ->joinLeft(array('PT'=>PARCEL_TRACKING),"HT.barcode_id=PT.barcode_id", array('dpd_error_url'))

			 ->joinLeft(array('SL'=>STATUS_LIST),"SL.error_id=PT.status_id",array('master_id','error_status'))

			 ->joinInner(array('HTD'=>HELPDESK_TICKET_DETAIL), "HT.helpdesk_token=HTD.helpdesk_token", array('messages'))

			 ->joinInner(array('HQ'=>HELPDESK_QUESTION),"HQ.question_id=HT.question_id",array('question_type'))

			 ->where("HT.helpdesk_token='".Zend_Encript_Encription:: decode($this->getData['helpdesk_token'])."' AND ticket_no='".Zend_Encript_Encription:: decode($this->getData['ticket_no'])."'")

			 ->order("PT.status_date DESC"); //echo $select->__toString(); die;

	  $result= $this->getAdapter()->fetchRow($select);

	  return $result;

	}

	

	/**

	 * get parcel information

	 * Function : parceldetails()

	 * @param barcode_id;

	 * Date of creation 08/02/2017

    **/

	public function parceldetails($decode=true){

	  $select = $this->_db->select()

	          ->from(array('SBC'=>SHIPMENT_BARCODE), array('*'))

			  ->joinInner(array('ST'=>SHIPMENT), "SBC.shipment_id=ST.shipment_id", array('create_date','rec_name','rec_street','rec_streetnr','rec_street2','rec_address','rec_zipcode','rec_city','rec_phone','rec_email','email_notification','user_id','country_id','addservice_id','senderaddress_id','goods_id'))

			  ->joinInner(array('SBD'=>SHIPMENT_BARCODE_DETAIL), "SBD.barcode_id=SBC.barcode_id", array('checkin_date'))

			  ->joinInner(array('FWD'=>FORWARDERS), "SBC.forwarder_id=FWD.forwarder_id", array('forwarder_name'))

			  ->joinLeft(array('AT'=>USERS_DETAILS), "ST.user_id=AT.user_id", array('company_name'))

			  ->where('SBC.'.TRACENR.'!=""')

			  ->where("SBC.".BARCODE_ID."='".commonfunction::trimString( $this->getData['barcode_id'])."'");  //echo $select->__toString();

	  $result = $this->getAdapter()->fetchRow($select);

	  return $result;	  

	}

	

	/**

	 * This function is for getting previous reply data

	 * Function : previousReply()

	 * @param ticket_no;

	 * Date of creation 08/02/2017

    **/

	public function previousReply($ticket_no){

	  $select = $this->_db->select()

	          ->from(array('HT'=>HELPDESK_TICKET), array('*'))

			  ->joinInner(array('HTD'=>HELPDESK_TICKET_DETAIL), "HT.helpdesk_token=HTD.helpdesk_token", array('messages','uploded_file'))

	  	      ->joinInner(array('HQD'=>HELPDESK_QUESTION_DETAILS), "HT.question_id=HQD.question_id AND HQD.step=HTD.step_id", array('steps','step_name'))

			  ->joinInner(array('AT'=>USERS_DETAILS), "HTD.activity_by=AT.user_id", array('AT.parent_id'))

			  ->joinLeft(array('US'=>USERS_SETTINGS), "AT.user_id=US.user_id", array('logo'))

			  ->joinLeft(array('PT'=>USERS_DETAILS), "PT.user_id=AT.parent_id", array())

			  ->joinLeft(array('AS'=>USERS_SETTINGS), "AS.user_id=PT.user_id", array('logo AS depot_logo'))

			  

			  ->where("HTD.activity_id IN(2,3) AND HT.ticket_no='".$ticket_no."'");

	  $result = $this->getAdapter()->fetchAll($select);

	  return $result;

	}

    

	

	public function nextstepforReply($que_id, $current_step){

	  $step_auth ='';

	  if($this->Useconfig['level_id']==5){

        $step_auth = " AND step_auth='1'";	  

	  }

	  $select = $this->_db->select()

	          ->from(array('HQD'=>HELPDESK_QUESTION_DETAILS), array('*'))

			  ->joinInner(array('AT'=>USERS_DETAILS), "AT.user_id=".$this->Useconfig['user_id']."", array('AT.parent_id'))

			  ->joinInner(array('US'=>USERS_SETTINGS), "US.user_id=AT.user_id", array('logo'))

			  ->joinLeft(array('PT'=>USERS_DETAILS), "PT.user_id=AT.parent_id", array())

			  ->joinLeft(array('AS'=>USERS_SETTINGS), "AS.user_id=PT.user_id", array('logo AS depot_logo'))

			  ->where("HQD.question_id='".$que_id."' AND HQD.parent_step='".$current_step."'".$step_auth)

			  ->order("step ASC");

	  $result = $this->getAdapter()->fetchAll($select);

	  return $result;

	}

	

	

	public function previousreplyCustomstep($helpdesk_token){

	  $select = $this->_db->select()

	          ->from(array('HTD'=>HELPDESK_TICKET_DETAIL), array('*'))

			  ->joinInner(array('AT'=>USERS_DETAILS), "HTD.activity_by=AT.user_id", array('company_name','customer_name','user_id','parent_id'))

			  ->joinleft(array('US'=>USERS_SETTINGS), "AT.user_id=US.user_id", array('logo'))

			  ->joinleft(array('PT'=>USERS_DETAILS), "PT.user_id=AT.parent_id", array())

			  ->joinleft(array('AS'=>USERS_SETTINGS), "AS.user_id=PT.user_id", array('logo AS depot_logo'))

			  ->where("HTD.activity_id IN(10,11) AND HTD.helpdesk_token=".$helpdesk_token);

	  $result = $this->getAdapter()->fetchAll($select);

	  return $result;

	}

	

	public function ticketreply(){

	  $filename = $this->ImportFile();

	  if($this->getData['document_upload']==1 && empty($filename)){

	  $error_msg = "Step Required Kindly Upload document";

	  return $error_msg;

	  }

	  $activity_id = ($this->Useconfig['level_id']==5)? '3' : '2';

	  

	  $this->_db->insert(HELPDESK_TICKET_DETAIL, array('helpdesk_token'=>Zend_Encript_Encription:: decode($this->getData['helpdesk_token']),'activity_id'=>$activity_id,'step_id'=>$this->getData['step'], 'messages'=>$this->getData['description'],'uploded_file'=>$filename,'activity_date'=>new Zend_Db_Expr('NOW()'),'activity_by'=>$this->Useconfig['user_id'],'activity_ip'=>commonfunction::loggedinIP()));

	     

	  $this->UpdateInToTable(HELPDESK_TICKET, array(array('current_activity'=>$activity_id,'current_step'=>$this->getData['step'],'current_step_status'=>$this->getData['step_status'],'is_status'=>$this->getData['status'])), "helpdesk_token=".Zend_Encript_Encription:: decode($this->getData['helpdesk_token'])." AND ticket_no=".Zend_Encript_Encription:: decode($this->getData['ticket_no'])."");

	  $this->sendemailToforwarder();

	  return;

	}

	

	/**

	 * This function uploads save image in uploads directory and 

	   return file name 

	 * Function : ImportFile()

	 * Date of creation 14/02/2017

    **/

	public function ImportFile($upload_dir='help_desk'){

	    $upload = new Zend_File_Transfer();

		$file_info = $upload->getFileInfo();

		$file_name = $file_info['document_file']['name'];

		if(!empty($file_name)){

	      $datetime = date('Y_m_d_H_i_s');

          $Filename = ROOT_PATH.'/public/'. $upload_dir . '/' . $datetime .''.$file_name;

          $upload->addFilter('Rename', array('target' => $Filename, 'overwrite' => true));

          $upload->receive();

          return $datetime.''.$file_name;

		}

	}

	

	/**

	 * This function sends email to forwarder

	 * Function : previousReply()

	 * @param forwarder_notify,forwarder_email;

	 * Date of creation 15/02/2017

    **/

	public function sendemailToforwarder(){

	 if(isset($this->getData['forwarder_notify']) && $this->getData['forwarder_notify']==1 && !empty($this->getData['forwarder_email'])){

	   $parceldetails = $this->parceldetails(false);

	   $parcel_number = $parceldetails[TRACENR_BARCODE];

	   $mailOBj = new Zend_Custom_MailManager();

	   $email_text = $this->getData['description'];

	   $email_text .= '<br>Parcel number : '.$parcel_number;

	   $mailOBj->emailData['SenderEmail'] = 'info@dpost.be';

	   $mailOBj->emailData['SenderName']    = 'NOREPLY';

	   $mailOBj->emailData['Subject'] = 'Help';

	   $mailOBj->emailData['MailBody'] = $email_text;

	   $mailOBj->emailData['ReceiverEmail'] = commonfunction::trimString($this->getData['forwarder_email']);

	   $mailOBj->emailData['ReceiverName'] = '';

       $mailOBj->emailData['user_id'] = $this->Useconfig['user_id'];

	   $mailOBj->emailData['notification_id'] = 0;

       $mailOBj->Send();

	 }

	 return true;

	}

	

	

	public function updateHelpdeskread(){

	 if($this->Useconfig['level_id']!=5){

	   $update = $this->UpdateInToTable(HELPDESK_TICKET, array(array('customer_new_read_status'=>'0','operator_read_status'=>'1')), "helpdesk_token='".Zend_Encript_Encription:: decode($this->getData['helpdesk_token'])."'");

	 }

	 if($this->Useconfig['level_id']==5 || $this->Useconfig['level_id']==10 ){

	  $update = $this->UpdateInToTable(HELPDESK_TICKET, array(array('customer_new_read_status'=>'1','operator_read_status'=>'0')), "helpdesk_token='".Zend_Encript_Encription:: decode($this->getData['helpdesk_token'])."'");

	 }

	 return $update;

	}

	

	public function internalreply(){

	 $this->_db->insert(HELPDESK_TICKET_DETAIL, array('helpdesk_token'=>Zend_Encript_Encription:: decode($this->getData['helpdesk_token']),'activity_id'=>5,'messages'=>$this->getData['description'],'activity_date'=>new Zend_Db_Expr('NOW()'),'activity_by'=>$this->Useconfig['user_id'],'activity_ip'=>commonfunction::loggedinIP()));

	 

	 $this->UpdateInToTable(HELPDESK_TICKET, array(array('current_activity'=>5)), "helpdesk_token=".Zend_Encript_Encription:: decode($this->getData['helpdesk_token'])." AND ticket_no=".Zend_Encript_Encription:: decode($this->getData['ticket_no'])."");

	}

	

	

	public function Ticketreplyuncompleted(){

		 if($this->Useconfig['level_id']==5){

		   $current_step = Zend_Encript_Encription:: decode($this->getData['helpdesk_token'])."0";

		 }

		 else{

		   $current_step = Zend_Encript_Encription:: decode($this->getData['helpdesk_token'])."4";

		 }

		  $activity_id = ($this->Useconfig['level_id']==5)? '10' : '11';

		  $this->_db->insert(HELPDESK_TICKET_DETAIL, array('helpdesk_token'=>Zend_Encript_Encription:: decode($this->getData['helpdesk_token']),'activity_id'=>$activity_id,'step_id'=>$current_step,'messages'=>$this->getData['customdescription'],'activity_date'=>new Zend_Db_Expr('NOW()'),'activity_by'=>$this->Useconfig['user_id'],'activity_ip'=>commonfunction::loggedinIP()));

	}

	

	public function forwardmessage(){ 

	 $forward_to = ($this->getData['forward_to']!='')? Zend_Encript_Encription:: decode($this->getData['forward_to']).','.$this->getData['operators'] : $this->getData['operators'];

	 $this->UpdateInToTable(HELPDESK_TICKET, array(array('forward_to'=>$forward_to,'current_activity'=>4)), "helpdesk_token=".Zend_Encript_Encription:: decode($this->getData['helpdesk_token'])."");

	 

	 $this->_db->insert(HELPDESK_TICKET_DETAIL, array('helpdesk_token'=>Zend_Encript_Encription:: decode($this->getData['helpdesk_token']),'forward_id'=>$this->getData['operators'],'activity_id'=>4,'messages'=>$this->getData['message'],'activity_date'=>new Zend_Db_Expr('NOW()'),'activity_by'=>$this->Useconfig['user_id'],'activity_ip'=>commonfunction::loggedinIP()));

	}

	

	public function addNewTicket(){

	   if($this->getData['question_id']>0){

	    try{

		 $this->_db->insert(HELPDESK_TICKET,array(

		 													  'ticket_no'=>$this->ticketnumber(),

															  'user_id'=>$this->getData['user_id'],

															  'barcode_id'=>$this->getData['barcode_id'],

															  'question_id'=>$this->getData['question_id'],

															  'current_step'=>0,

															  'current_activity'=>1,

															  'is_status' =>1,

															  'create_date' => new Zend_Db_Expr('NOW()') 

																));

			$tocken_id = $this->getAdapter()->lastInsertId();

			$this->_db->insert(HELPDESK_TICKET_DETAIL, array('helpdesk_token'=>$tocken_id,

															 'activity_id'=>1,

															 'messages'=>$this->getData['question'],

															 'activity_date'=>new Zend_Db_Expr('NOW()'),

															 'activity_by'=>$this->Useconfig['user_id'],

															 'activity_ip'=>commonfunction::loggedinIP()));	

		}catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());}												

	   }

	}

	

	public function ticketnumber(){

        $length= 10;

        $random = "";

		srand((double)microtime()*1000000000);

		$data = "1234567890";

		for($i= 0; $i < $length; $i++) {

			 $random .= substr($data, (rand()%(strlen($data))), 1); 

		 }

       return $random +1;

  }

  

    public function checkAddedTicket(){

	     $select = $this->_db->select()

								  ->from(array('HT'=>HELPDESK_TICKET), array('COUNT(1) AS CNT'))

								  /*->joinInner(array('HTD'=>HELPDESK_TICKET_DETAIL), 'HT.helpdesk_token=HTD.helpdesk_token', array('messages'))

								  ->joinInner(array('HQS'=>HELPDESK_QUESTION), 'HT.question_id=HQS.question_id', array('question_id','question_type'))

								  ->joinInner(array('AT'=>USERS_DETAILS), 'HT.user_id=AT.user_id', array('user_id','company_name','customer_name'))

								  ->joinInner(array('US'=>USERS_SETTINGS), 'US.user_id=AT.user_id', array('logo'))*/

								  ->where("HT.barcode_id='".$this->getData['barcode_id']."' AND HT.question_id='".$this->getData['question_id']."'"); 

								  //echo $select->__toString(); die;

	  $result = $this->getAdapter()->fetchRow($select);

	  if($result['CNT']==0){

	      echo '0';die;

	  }else{

	     echo '<td colspan="2">Ticket already added for this question: <a href="'.BASE_URL.'/Helpdesk/reply">Go to Reply</a></td>';die;

	  }

	}

}

?>