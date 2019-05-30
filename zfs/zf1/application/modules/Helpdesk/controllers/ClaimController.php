<?php
//ini_set('display_errors','1');
class Helpdesk_ClaimController extends Zend_Controller_Action

{



     public $Request = array();

     public $ModelObj = null;

     public $HelpDeskMOdelObj = NULL;

	 

	

     /**

	 * Auto load NameSpace and create objects 

	 * Function : init()

	 * Auto call and loads the default namespace and create object of model and form

	 **/

	 

     public function init()

     { 

		try{

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new Helpdesk_Model_ClaimManager();

			$this->HelpDeskMOdelObj = new Helpdesk_Model_HelpdeskManager();

			$this->formObj = new Helpdesk_Form_Helpdesk();

			$this->ModelObj->getData  = $this->Request;
			
			$this->HelpDeskMOdelObj->getData  = $this->Request;

			$this->view->ModelObj = $this->ModelObj;

			$this->view->Request  = $this->Request;

			$this->_helper->layout->setLayout('main');

	   }catch(Exception $e){

	    echo $e->getMessage();

	   }

     }



    

	public function claimlistAction(){

	  $this->view->user_config = $this->ModelObj->Useconfig;

	  $this->view->claimdetails = $this->ModelObj->getclaimlist();

	  $this->view->claimstatus = $this->ModelObj->getclaimstatus();

	  $this->view->forwarders = $this->ModelObj->getForwarderList();

	  $this->view->countries = $this->ModelObj->getCountryList();

	  $this->view->customers = $this->ModelObj->getCustomerList();

	  $this->view->depot = $this->ModelObj->getDepotList();

    }

	

	public function mailonupdateclaimstatusAction(){

	  echo $this->ModelObj->updateclaimliststatus(); exit;

	}

	

	

	/**

	* Action for view all claims data

	* Function : viewclaimsAction()

	* Date of creation 23/02/2017

	**/

	public function viewclaimsAction(){

	global $objSession;

	 $this->view->Userconfig = $this->ModelObj->Useconfig;

	 $claimdetails = $this->ModelObj->getAllclaim();

	 $this->ModelObj->RecordData['forwarder_id'] = $claimdetails[0]['forwarder_id'];

	 $this->ModelObj->RecordData['user_id'] = $claimdetails[0]['user_id'];

	 $this->ModelObj->RecordData['senderaddress_id'] = $claimdetails[0]['senderaddress_id'];

	 $this->ModelObj->RecordData['addservice_id'] = $claimdetails[0]['addservice_id'];

	 $this->ModelObj->RecordData['country_id'] = $claimdetails[0]['country_id'];

	 $this->ModelObj->RecordData['goods_id'] = $claimdetails[0]['goods_id'];

	 $forwarder_detail = $this->ModelObj->ForwarderDetail();

     $this->view->forwarderData = $forwarder_detail;

	 $this->view->claimData = $claimdetails; 

	 $this->view->recCountry = $this->ModelObj->RecordData;

	 $this->view->ClaimStatus = $this->ModelObj->getclaimstatus(); 

	 if($this->_request->isPost()){

	   $this->ModelObj->updatClaimdata();

	   $this->ModelObj->UpdateClaimread();

	   $objSession->successMsg =  "Claim data updated successfully";

	   $this->_redirect($this->_request->getControllerName().'/claimlist');

	 }

	}

     	

    /**

	* Action for view all claims data

	* Function : viewclaimsAction()

	* Date of creation 23/02/2017

	**/

	public function replyclaimAction(){

	 global $objSession;

	  $claimdetails = $this->ModelObj->getAllclaim();

	  $this->ModelObj->RecordData['forwarder_id'] = $claimdetails[0]['forwarder_id'];

	  $this->ModelObj->RecordData['user_id'] = $claimdetails[0]['user_id'];

	  $this->ModelObj->RecordData['senderaddress_id'] = $claimdetails[0]['senderaddress_id'];

	  $this->ModelObj->RecordData['addservice_id'] = $claimdetails[0]['addservice_id'];

	  $this->ModelObj->RecordData['country_id'] = $claimdetails[0]['country_id'];

	  $this->ModelObj->RecordData['goods_id'] = $claimdetails[0]['goods_id'];

	  $forwarder_detail = $this->ModelObj->ForwarderDetail();

	  $this->view->replyDetail = $this->ModelObj->replyByoperator();

      $this->view->forwarderData = $forwarder_detail;

	  $this->view->claimData = $claimdetails; 

	  $this->view->recCountry = $this->ModelObj->RecordData;

	  $this->view->notifications = $this->ModelObj->NotificationList(2);
	  

	  if($this->_request->isPost() && !empty($this->Request['reply_message'])){

	    $this->ModelObj->addclaimreply();

		$this->ModelObj->UpdateClaimread();

	    $objSession->successMsg = "Reply from claim added and mail sent successfully";

	    $this->_redirect($this->_request->getControllerName().'/claimlist');

	  }

	}

	

			 

	public function claimsettingAction(){

	  $this->view->allquestions = $this->ModelObj->allclaimquestions();

	  

    }

	

	/**

	* Action for getting all claims

	* Function : claimstatusAction()

	* Date of creation 13/01/2017

	**/

	public function claimstatusAction(){

	  $this->view->claimstatus = $this->ModelObj->getclaimstatus();

	}

	

	/**

	* Action for adding claim

	* Function : addclaimAction()

	* Date of creation 05/04/2017

	**/

	public function addclaimAction(){

		try{

		    global $objSession;

			$this->_helper->layout->setLayout('popup');

			$this->view->claimQue = $this->ModelObj->GetAllclaimque();
			$this->view->shipmentData = $this->ModelObj->GetshipmentData();
			  

		   if($this->_request->isPost() && $this->Request['addClaim']=='Add Claim'){

		   	// echo "<pre>"; print_r($this->Request);die;
			  $claim_id = $this->ModelObj->addclaim();

			  $objSession->successMsg = "Claim added successfully!! Claim Id:- ".$claim_id;

			  echo '<script type="text/javascript">parent.window.location.reload();

              parent.jQuery.fancybox.close();</script>';

              exit();

		      $this->_redirect($this->_request->getControllerName().'/addclaim');

		   }

		}

		catch (Exception $e) {

          $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

        }

	}

	

	/**

	* Action for adding new claim question

	* Function : claimstatusAction()

	* Date of creation 18/01/2017

	**/

	public function addnewclaimquestionAction(){

	 try{

		 global $objSession;

		  $this->view->operatortype = $this->ModelObj->operatortype();

		  $sub_que['sub_question'][0]=0;

		  $sub_que['sub_question'][1]=1;

		  $this->view->allclaimquestiondata = $this->ModelObj->allclaimquestiondata($sub_que);

		  $this->view->claimquestion = $this->ModelObj->getclaimquestion();

		  if($this->_request->isPost()){

			if(!empty($this->Request['question']) && !empty($this->Request['question_type'])){

			  $question_id = $this->ModelObj->addnewclaimquestion();

			  $objSession->successMsg =  "New claim question added successfully";
			  $this->_redirect($this->_request->getControllerName().'/claimsetting');

			}

			else{

			  $objSession->errorMsg = "Pease enter question and select question type!";

			}

		  }

	  }

	  catch (Exception $e) {

      $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

     }



    }

	

	/**

     * Get claim question option

     * Function : getoptionclaimquestionbyidAction()

	 * Date of creation 20/01/2017

   */

	public function getoptionclaimquestionbyidAction(){

	   $result = $this->ModelObj->getclaimqueoptionsbyid();

	   $results['Claimquebyid'] = $result;

	   echo json_encode($results); exit;

	}

	

	/**

     * 

     * Function : claimquebyidoptionAction()

	 * Date of creation 20/01/2017

   */

	public function claimquebyoptionidAction(){

	  $result1 = $this->ModelObj->getquestionbyidoption();

	  $result2 = $this->ModelObj->getclaimquestion();

	  $result3 = $this->ModelObj->getquestionbyidoptionother();

	  $results['claimque'] = $result1;

	  $results['claimallque'] = $result2;

	  $results['claimotherqsss'] = $result3;

	  echo json_encode($results); exit;

	}

	/**

	* Action for update 

	* Function : editclaimstatusAction()

	* Date of creation 16/01/2017

	**/

	public function editclaimstatusAction(){

		 try{

			global $objSession;

			$this->_helper->layout->setLayout('popup');

			$this->view->getstatus = $this->ModelObj->getclaimstatus();

			$this->view->notificationlist = $this->ModelObj->notificationlist();

			if($this->_request->isPost() && $this->Request['editclaimstatus']=='Update Status'){

		   if($this->Request['newnotification']!=''){

			 $notificationid = $this->ModelObj->addnewnotification($templatecategory_id=1);

		   } 

		   $notificationId = (isset($notificationid))?$notificationid:$this->Request['notificationid'];

		   if($this->ModelObj->updateclaimstatus($notificationId)){

			$objSession->successMsg = "Claim Status has been Updated Successfully";

			echo '<script type="text/javascript">parent.window.location.reload();

				  parent.jQuery.fancybox.close();</script>';

				  exit();

			$this->_redirect($this->_request->getControllerName().'/claimstatus');

		   }

		   else{

			  $objSession->errorMsg = "There is some problem!";

		   echo '<script type="text/javascript">parent.window.location.reload();

				 parent.jQuery.fancybox.close();</script>';

				 exit();

		   $this->_redirect($this->_request->getControllerName().'/claimstatus');

			  }

			}

		   } catch (Exception $e) {

			  $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

			 }

		   

 }

	

	

	public function mailtemplateAction(){

	  $this->_helper->layout->setLayout('popup');

	  

	}

	

	/**

	* Action for update claim questions and operators

	* Function : updateclaimquestionAction()

	* Date of creation 13/01/2017

	**/

	public function updateclaimquestionAction(){

	  try{

	   global $objSession;

	   $this->_helper->layout->setLayout('popup');

	   $this->view->operatortype = $this->ModelObj->operatortype(); 

	   $this->view->updatedetails = $this->ModelObj->claimquebyquestionid();

	   if($this->_request->isPost() && isset($this->Request['updateque']) &&  $this->Request['updateque']=='Update Question'){

	     $this->ModelObj->updateclaimquestion();

		 $objSession->successMsg = "Record Updated Successfully";

		 echo '<script type="text/javascript">parent.window.location.reload();

         parent.jQuery.fancybox.close();</script>';

         exit();

		 $this->_redirect($this->_request->getControllerName().'/claimsetting');

	   }

	 }

	 catch (Exception $e) {

     $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

    }

	

	}

	

	/**

	* Action for adding new claim status

	* Function : addclaimstatusAction()

	* Date of creation 17/01/2017

	**/

	public function addclaimstatusAction(){

	 try{

	  global $objSession;

	  $this->_helper->layout->setLayout('popup');

	  if($this->_request->isPost() && $this->Request['editclaimstatus']=='Add new Status'){

	    if(!empty($this->Request['addnewclaimstatus'])){

		  $this->ModelObj->addclaimstatus();

		  $objSession->successMsg = "New claim status has been added Successfully";

		  echo '<script type="text/javascript">parent.window.location.reload();

          parent.jQuery.fancybox.close();</script>';

          exit();

		  $this->_redirect($this->_request->getControllerName().'/claimstatus');

		}

		else{

		   $objSession->errorMsg = "Please enter new status and then submit!";

		   echo '<script type="text/javascript">parent.window.location.reload();

           parent.jQuery.fancybox.close();</script>';

           exit();

		   $this->_redirect($this->_request->getControllerName().'/claimstatus');

		}

	  }

	 }

	 catch (Exception $e) {

     $this->ModelObj->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

    }

	

	}

	

	/**

	* Action for fetching mail template for claim

	* Function : emailtemplateAction()

	* @param notification_id

	* Date of creation 13/01/2017

	**/

	public function emailtemplateAction(){

	 $this->_helper->layout->setLayout('popup');

     $this->view->mailtemplate = $this->ModelObj->fetchtemailemplate();

	 $this->view->templatename = ($this->Request['notification_name']) ? $this->Request['notification_name'] : ''; 

   }

	

   public function changestatusAction(){

     $status = $this->ModelObj->getChangeStatus($this->Request);

     echo $status; exit;

   }

   	

	 // public function claimnotificationAction(){
  //     global $objSession;
  //     $this->_helper->layout->setLayout('popup');
  //     	if($this->_request->isPost() && isset($this->Request['submit'])){
      	
  //     		echo "<pre>";print_r($this->Request);die;

	 //      if(isset($this->Request['helpdesknotification']) && empty($this->Request['helpdesknotification'])){
	 //      	$objSession->errorMsg = "Notification can not be blank !";
		//   }elseif(isset($this->Request['token']) && !empty($this->Request['token'])){
		//   	$result = $this->ModelObj->AddClaimNotification();
		//   	if($result){
		//   		$objSession->successMsg = "Notification Added successfully!!";
		//   	}else{
		//   		$objSession->errorMsg = "Something is wrong !";
		//   	}
		//    }	
		  
		//   echo '<script type="text/javascript">parent.window.location.reload(); parent.jQuery.fancybox.close();</script>';exit;
		// }
  //   }

}

?>