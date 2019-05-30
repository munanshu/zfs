<?php
class Helpdesk_HelpdeskController extends Zend_Controller_Action

{

	

	public $Request = array();

    public $ModelObj = NULL;

     /**

	 * Auto load NameSpace and create objects 

	 * Function : init()

	 * Auto call and loads the default namespace and create object of model and form

	 **/ 

     public function init()

     { 

		try{

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new Helpdesk_Model_HelpdeskManager();

			$this->ModelObj->getData  = $this->Request;

			$this->view->ModelObj = $this->ModelObj;

			$this->view->Request  = $this->Request;

			$this->_helper->layout->setLayout('main');

	   }catch(Exception $e){

	    echo $e->getMessage();

	   }

     }

  

    /**

	* Action for listing ticket details

	* Function : ticketlistAction()

	* Date of creation 31/01/2017

	**/  

	public function ticketlistAction(){

	  $this->view->session_data = $this->ModelObj->Useconfig;

	  $this->view->ticketdetails = $this->ModelObj->ticketinformation();

	  $this->view->countries = $this->ModelObj->getCountryList();

	  $this->view->helpdeskstatus = $this->ModelObj->getstatuslist(); 

	  $this->view->allcustomers = $this->ModelObj->getCustomerList();

	  $this->view->forwarders = $this->ModelObj->getForwarderList();

     }

	 

    /**

	* Action for summary ticket

	* Function : summmaryticketsAction

	* Date of creation 31/01/2017

	**/ 

	public function summmaryticketsAction(){

	  $this->view->session_data = $this->ModelObj->Useconfig;

	  $this->view->ticketdetails = $this->ModelObj->ticketinformation();

	  $this->view->helpdeskstatus = $this->ModelObj->getstatuslist();

	} 

	

	/**

	* Action for helpdesk setting

	* Function : summmaryticketsAction

	* Date of creation 31/01/2017

	**/

    public function helpdesksettingAction(){

	  $this->view->allquestion = $this->ModelObj->allquestionrecord();

    } 

	

	/**

	* Action for add new question

	* Function : addnewquestionAction()

	* Date of creation 06/01/2017

	**/

	public function addnewquestionAction(){

	  try{

		   global $objSession;

		   $this->view->languages = $this->ModelObj->systemlanguage();

		   $this->view->operatortype = $this->ModelObj->operatortype();

		   if($this->_request->isPost()){

			   if(count($this->Request['question'])>0 && !empty ($this->Request['operators'])){

				  $question_id=$this->ModelObj->addnewquestion();

				  $objSession->successMsg = "New question added successfully";

				  $this->_redirect($this->_request->getControllerName().'/setstep?question_id='.$question_id);

			   } 

			   else{

				   $objSession->errorMsg = "Please enter question and select Operator!";

			   }

		  }

	  }

	  catch (Exception $e) {

      $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

     }

	 

   } 

	

   /**

	* Action for update existing questions and operators

	* Function : updatequestionAction()

	* Date of creation 06/01/2017

	**/

   public function updatequestionAction(){

    try{

		 global $objSession;

		 $this->view->languages = $this->ModelObj->systemlanguage();

		 $this->view->operatortype = $this->ModelObj->operatortype(); 

		 $this->view->addedquestions = $this->ModelObj->addedquestiondata($this->Request);

		 if($this->_request->isPost()){

		   if(!empty($this->Request['operators']) && count($this->Request['question'])>0){

			  $this->ModelObj->updatequestions($this->Request);	

			  $objSession->successMsg = "Record Updated Successfully";

			  $this->_redirect($this->_request->getControllerName().'/helpdesksetting');  

		   }

		   else{

			  $objSession->errorMsg = "Please enter question and select Operator!";

		   }

		}

	 }

	  

	 catch (Exception $e) {

     $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

    }

	 

   }

	

   /**

	* Action for adding step

	* Function : setstepAction()

	* Date of creation 06/01/2017

	**/

   public function setstepAction(){

     $this->view->getdata = $this->Request;

	 $this->view->steplistdata = $this->ModelObj->steplist();

	 $this->view->steps = $this->ModelObj->showsteps();

   }	

	

   /**

	* Action for adding steps and getting new row 

	* Function : addstepsAction()

	* Date of creation 06/01/2017

	**/

   public function addstepsAction(){

	  echo $this->ModelObj->addstepandgetnewrow($this->Request); exit;

	}

	

	/**

	* Action for update steps/existing step 

	* Function : addstepsAction()

	* Date of creation 06/01/2017

	**/

	public function editstepsAction(){

	  echo $this->ModelObj->updatesteps($this->Request); exit;

	}

	

	/**

	* Action for fetching all helpdesk status 

	* Function : helpdeskstatusAction()

	* Date of creation 09/01/2017

	**/

	public function helpdeskstatusAction(){

      $this->view->helpdeskstatus = $this->ModelObj->allheldeskstatus(); 

   }

   

   /**

	* Action for adding new helpdesk status 

	* Function : addhelpdeskstatusAction()

	* Date of creation 18/01/2017

	**/

   public function addhelpdeskstatusAction(){

	 try{

	  global $objSession;

	  $this->_helper->layout->setLayout('popup');

	  if($this->_request->isPost() && $this->Request['addnewhelpdesk']=='Add New Helpdesk'){

	    if(!empty($this->Request['helpdeskname'])){

		  $this->ModelObj->addnewhelpdeskstatus();

		  $objSession->successMsg = "New helpdesk status has been added Successfully";

		  echo '<script type="text/javascript">parent.window.location.reload();

          parent.jQuery.fancybox.close();</script>';

          exit();

		  $this->_redirect($this->_request->getControllerName().'/helpdeskstatus');

		}

		else{

		   $objSession->errorMsg = "Please enter new status and then submit!";

		   echo '<script type="text/javascript">parent.window.location.reload();

           parent.jQuery.fancybox.close();</script>';

           exit();

		   $this->_redirect($this->_request->getControllerName().'/helpdeskstatus');

		}

	  }

	 }

	 catch (Exception $e) {

     $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

    }

   }

   

   /**

	* Action for fetching mail template

	* Function : heldeskstatusmailtemplateAction()

	* Date of creation 10/01/2017

	**/

   public function emailtemplateAction(){

     $this->_helper->layout->setLayout('popup'); 

	 $this->view->emailtemplate = $this->ModelObj->fetchemailtemplate(array('notification_id'=>$this->Request['notification_id'])); 

	 $this->view->templatename = ($this->Request['notification_name']!='') ? $this->Request['notification_name'] : '';

   }

   

   /**

	* Action for edit helpdesk status

	* Function : edithelpdeskstatusAction()

	* Date of creation 11/01/2017

	**/

   /**

 * Action for edit helpdesk status

 * Function : edithelpdeskstatusAction()

 * Date of creation 11/01/2017

 **/

   public function edithelpdeskstatusAction(){

   	global $objSession;

	   try{

	   $this->_helper->layout->setLayout('popup');

	   $status_id = Zend_Encript_Encription:: decode($this->Request['status_id']);

	   $this->view->statuslist = $this->ModelObj->getstatuslist($status_id);

	   $this->view->notificationlist = $this->ModelObj->getNotificationlist();

	   if($this->_request->isPost() && $this->Request['updatestatus']=='Update Status'){

	   if($this->Request['notification_name']!=''){

		 $notificationid = $this->ModelObj->addnewnotification($templatecategory_id=2);

	   }  

	   $notificationId = (isset($notificationid))?$notificationid:$this->Request['notificationid'];

		if($this->ModelObj->updatehelpdeskstatus($notificationId)){

		 $objSession->successMsg = "Helpdesk Status has been Updated Successfully";

		 echo '<script type="text/javascript">parent.window.location.reload();

		 parent.jQuery.fancybox.close();</script>';

		 exit();

		 $this->_redirect($this->_request->getControllerName().'/helpdeskstatus');

		}

		else{

		$objSession->errorMsg = "There is some problem!";

		echo '<script type="text/javascript">parent.window.location.reload();

		parent.jQuery.fancybox.close();</script>';

		exit();

		$this->_redirect($this->_request->getControllerName().'/helpdeskstatus');

		}

	  }

	 }catch (Exception $e) {

		 $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

 

   }

  

   /**

	* Action for getiing FAQ's List

	* Function : faqdetailAction()

	* Date of creation 27/01/2017

	**/

   public function faqdetailAction(){

     $this->view->faqquestion = $this->ModelObj->faqdetails();

   

   }

   

   /**

	* Action for adding FAQ's Question

	* Function : addfaqAction()

	* Date of creation 27/01/2017

	**/

   public function addfaqAction(){

    try{

		global $objSession;

		 $this->view->opertortype = $this->ModelObj->operatortype();

		 if($this->_request->isPost()){

			 $this->ModelObj->addnewfaqquestion();

			 $objSession->successMsg = "New FAQ's question has been added Successfully";

		     $this->_redirect($this->_request->getControllerName().'/faqdetail');

		   }

	 }

	 catch (Exception $e) {

     $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

    }

   }

   

  /**

	* Action for updating FAQ's Question

	* Function : updatefaqAction()

	* Date of creation 28/01/2017

	**/

   public function updatefaqAction(){

    try{

	  global $objSession;

       $this->view->quedetails = $this->ModelObj->updatequedetails();

	   $this->view->opertortype = $this->ModelObj->operatortype();

	   if($this->_request->isPost()){

	    $this->ModelObj->updatefaqquestion();

	    $objSession->successMsg = "Updated Successfully";

		$this->_redirect($this->_request->getControllerName().'/faqdetail');

	   }

	 }

	 catch (Exception $e) {

     $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

    }

	

   }

   

   /**

	* Action for change ticket Status

	* Function : changeticketstatusAction()

	* Date of creation 06/02/2017

	**/

   public function changeticketstatusAction(){

    try{

       global $objSession;

	   $this->ModelObj->updateTicketreadstatus();

	   $this->ModelObj->changeTicketstatus(); 

	   $objSession->successMsg = "Ticket Status Updated Successfully";

       exit;

	 }

	 catch (Exception $e) {

     $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

    }

   }

   

   /**

	* Action for change ticket Status

	* Function : changeticketstatusAction()

	* Date of creation 06/02/2017

	**/

    public function viewmessageAction(){

	  $this->view->ticketdeatil = $this->ModelObj->GetTicketinfo();

	  $this->ModelObj->updateTicketreadstatus();

	  $this->ModelObj->getData['barcode_id'] = $this->view->ticketdeatil['barcode_id'];

      $this->view->parceldeatil = $this->ModelObj->parceldetails(false);

	  $this->ModelObj->RecordData['forwarder_id'] = $this->view->parceldeatil['forwarder_id'];

	  $this->ModelObj->RecordData['user_id'] = $this->view->parceldeatil['user_id'];

	  $this->ModelObj->RecordData['senderaddress_id'] = $this->view->parceldeatil['senderaddress_id'];

	  $this->ModelObj->RecordData['addservice_id'] = $this->view->parceldeatil['addservice_id'];

	  $this->ModelObj->RecordData['country_id'] = $this->view->parceldeatil['country_id'];

	  $this->ModelObj->RecordData['goods_id'] = $this->view->parceldeatil['goods_id'];

	  $this->view->forwarderData = $this->ModelObj->ForwarderDetail();

	  $this->view->previousstep = $this->ModelObj->previousReply($this->view->ticketdeatil['ticket_no']);

	  $this->view->previouscustom = $this->ModelObj->previousreplyCustomstep($this->view->ticketdeatil['helpdesk_token']);

    }

	

  public function replyformAction(){

	 try{

	  global $objSession;

	  if($this->_request->isPost() && isset($this->Request['reply']) && $this->Request['reply']=='Reply'){

	     $error_msg = $this->ModelObj->ticketreply();

	     $objSession->errorMsg = $error_msg;

		 $this->ModelObj->updateHelpdeskread();

		 $objSession->successMsg = "Reply Submitted Successfully";

		 $this->_redirect($this->_request->getControllerName().'/replyform?mode=replyform&helpdesk_token='.$this->Request['helpdesk_token'].'&ticket_no='.$this->Request['ticket_no']);

	  }

	  

	  if($this->_request->isPost() && isset($this->Request['internalreply']) && $this->Request['internalreply']=='Reply'){

	   $this->ModelObj->internalreply();

	   $this->ModelObj->updateHelpdeskread();

	   $objSession->successMsg = "Internal Reply Submitted Successfully";

	   $this->_redirect($this->_request->getControllerName().'/replyform?mode=replyform&helpdesk_token='.$this->Request['helpdesk_token'].'&ticket_no='.$this->Request['ticket_no']);

	  }

	  

	  if($this->_request->isPost() && isset($this->Request['askquestion']) && $this->Request['askquestion']=='Reply' || isset($this->Request['askquestion']) && $this->Request['askquestion']=='AskQuestion'){

	   $this->ModelObj->Ticketreplyuncompleted();

	   $this->ModelObj->updateHelpdeskread();

	   $this->_redirect($this->_request->getControllerName().'/replyform?mode=replyform&helpdesk_token='.$this->Request['helpdesk_token'].'&ticket_no='.$this->Request['ticket_no']);

	  }

	  

	  $this->view->sess_data = $this->ModelObj->Useconfig;

	  $this->view->ticketdetail = $this->ModelObj->GetTicketinfo();

	  $this->ModelObj->getData['barcode_id'] = $this->view->ticketdetail['barcode_id'];

	  $this->ModelObj->getData['question_id'] = $this->view->ticketdetail['question_id'];

	  $this->view->parceldetail = $this->ModelObj->parceldetails(false);

	  $this->ModelObj->updateTicketreadstatus();

	  $this->ModelObj->updateReadStatus($this->view->ticketdetail['forward_to']);

	  $this->view->previousstep = $this->ModelObj->previousReply($this->view->ticketdetail['ticket_no']);

	  $this->view->nextsteps = $this->ModelObj->nextstepforReply($this->view->ticketdetail['question_id'],$this->view->ticketdetail['current_step']);

	  $this->view->helpdeskstatus = $this->ModelObj->getstatuslist();

	  $this->view->previouscustom = $this->ModelObj->previousreplyCustomstep($this->view->ticketdetail['helpdesk_token']);

	  $this->view->steps = $this->ModelObj->showsteps();

	  } catch (Exception $e) {

      $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

     }

   }

   

   

   public function viewforwarddetailAction(){

     $this->_helper->layout->setLayout('popup');

     $this->view->forwarderDetail = $this->ModelObj->viewForwarderDetail();

   }

    

   public function forwardmessageAction(){

    $this->_helper->layout->setLayout('popup');

    $this->view->operators = $this->ModelObj->operatortype();

	if($this->_request->isPost() && $this->Request['forwardmessage']=='Submit'){

	 $this->ModelObj->forwardmessage();

	 $objSession->successMsg = "Saved Successfully";

	 echo '<script type="text/javascript">parent.window.location.reload();

	 parent.jQuery.fancybox.close();</script>';

	 exit();

	}

   }

   

   public function addticketAction(){

      $this->_helper->layout->setLayout('popup');

	   if($this->_request->isPost()){

	        global $objSession;

	        $this->ModelObj->addNewTicket();

			 $objSession->successMsg = "Ticket Added successfully!!";

			echo '<script type="text/javascript">parent.window.location.reload(); parent.jQuery.fancybox.close();</script>';exit;

	   }

	   $this->view->parcelinfo = $this->ModelObj->ParcelDetails();

	   $this->view->allquestion = $this->ModelObj->allquestionrecord();

   }

   public function checkticketAction(){

	  $this->view->allquestion = $this->ModelObj->checkAddedTicket();

   }

    

}

?>