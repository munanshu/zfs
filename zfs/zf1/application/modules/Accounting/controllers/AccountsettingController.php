<?php
/**

 * Controll the Account module

 * @Auth : SJM softech Pvt. Ltd.

 * @Created Date: 03rd-October-2016

 * @Description : Controll the functionality related to Account

 **/

class Accounting_AccountsettingController extends Zend_Controller_Action

{

	public $Request = array();

    public $ModelObj = null;

	public $formObj  = NULL;

	

   /**

	* Initialize action controller

	* Function : init()

	* Auto create,loads and call the default object of model ,form and layout

	**/

    public function init()

    { 

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new Accounting_Model_Settingmanager();

			$this->formObj = new Accounting_Form_Heads($this->Request);

			$this->ModelObj->getData  = $this->Request;

			$this->view->ModelObj = $this->ModelObj;

			$this->view->Request = $this->Request;

			$this->_helper->layout->setLayout('main');

    }



    public function indexAction()

    {
    	 
		// action body

    }

    public function getaccountheadlistAction()
    {	
         $this->view->AccountingHeads = $this->ModelObj->AccountingHeadsDetails();

    } 


    public function addeditaccountingheadAction()

    {    
        $this->_helper->layout->setLayout('popup');
        // echo "<pre>";
        // print_r($this->ModelObj->getselectboxListbyClass(AccountingGroup,array('group_id','group_name'),'ASC',2) );die;
        $this->formObj->addHeadForm();
         // try {
        if($this->Request['mode'] == 'add'){
            $this->view->title = 'Add New Accounting Head';
                if($this->getRequest()->isPost()){
                   
                if($this->formObj->isValid($this->Request)){
                    $result = $this->ModelObj->addHeadDetails($this->Request);

                     global $objSession; 

                     if($result)
                        $objSession->successMsg = "Accounting Head Added successfully!!";
                     else $objSession->successMsg = "Some internal error occurred"; 
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{
                    $this->formObj->populate($this->Request);

                }
            }
        }else{
             $this->view->title = 'Edit Accounting Head'; 
            $this->formObj->getElement('submit')->setLabel('Update');
             if($this->getRequest()->isPost()){
                $this->formObj->getElement('head_code')->removeValidator('Db_NoRecordExists'); 
                if($this->formObj->isValid($this->Request)){
                         
                    $result = $this->ModelObj->EditHeadDetail($this->Request);

                     global $objSession; 

                     if($result['status'])
                        $objSession->successMsg = $result['message'];
                     else $objSession->errorMsg = $result['message'];   
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{

                    $this->formObj->populate($this->Request);

                }

            }
            else {  
                   $editdata = $this->ModelObj->getHeadDetails();
                   $this->formObj->__construct($editdata[0]);
                   $this->formObj->addHeadForm();
                   $this->formObj->getElement('submit')->setLabel('Update');
                   $this->formObj->populate($editdata[0]);
            } 
        } 
        $this->view->AccountingHeadForm =  $this->formObj;  

    }


    

    public function getaccountgroupbyclassAction()
    {   
        $classId = $this->Request['classID'];
        $AccountingGroups = $this->ModelObj->getaccountgroupbyclass($classID);
        if(count($AccountingGroups)>0){
            $resp = array('status'=>1,'data'=>$AccountingGroups);
        }
        else $resp = array('status'=>0,'message'=>"No group foun related to this class");

        echo  commonfunction::jsonconvert($resp);
        die;
    }

    public function getaccountgrouplistAction()
    {	
    	 $this->view->AccountingGroups = $this->ModelObj->AccountingGroupDetails();
    }

    public function addeditaccountinggroupAction()

    {    
        $this->_helper->layout->setLayout('popup');
        // echo "<pre>";
        // print_r($this->ModelObj->getselectboxListbyClass(AccountingGroup,array('group_id','group_name'),'ASC',2) );die;
        $this->formObj->addGroupForm();
         // try {
        if($this->Request['mode'] == 'add'){
            $this->view->title = 'Add New Accounting Group';
                if($this->getRequest()->isPost()){
                   
                if($this->formObj->isValid($this->Request)){
                    $result = $this->ModelObj->addGroupDetails($this->Request);

                     global $objSession; 

                     if($result)
                        $objSession->successMsg = "Accounting Group Added successfully!!";
                     else $objSession->errorMsg = "Some internal error occurred"; 
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{
                    $this->formObj->populate($this->Request);

                }
            }
        }else{
             $this->view->title = 'Edit Accounting Group'; 
            $this->formObj->getElement('submit')->setLabel('Update');
             if($this->getRequest()->isPost()){
                $this->formObj->getElement('group_name')->removeValidator('Db_NoRecordExists'); 
                if($this->formObj->isValid($this->Request)){
                         
                    $result = $this->ModelObj->EditGroupDetail($this->Request);

                     global $objSession; 

                     if($result['status'])
                        $objSession->successMsg = $result['message'];
                     else $objSession->errorMsg = $result['message'];   
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{

                    $this->formObj->populate($this->Request);

                }

            }
            else {  
                   $editdata = $this->ModelObj->getSingleGroupDetails();
                   $this->formObj->populate($editdata[0]);
            } 
        } 

        $this->view->AccountingGroupForm =  $this->formObj;  

    }



    public function getaccountclasslistAction()
    {	
    	$this->view->AccountingClass = $this->ModelObj->AccountingClassDetails();
    }


    public function addeditaccountingclassAction()

    {    
        $this->_helper->layout->setLayout('popup');
        // echo "<pre>";
        // print_r($this->ModelObj->getclassactivitytypes());die;
        $this->formObj->addClassForm();
         // try {
        if($this->Request['mode'] == 'add'){
            $this->view->title = 'Add New Accounting Class';
                if($this->getRequest()->isPost()){
                   
                if($this->formObj->isValid($this->Request)){
                    // print_r($this->Request);die;
                    $result = $this->ModelObj->addClassDetails($this->Request);

                     global $objSession; 

                     if($result)
                        $objSession->successMsg = "Accounting Class Added successfully!!";
                     else $objSession->errorMsg = "Some internal error occurred"; 
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{
                    $this->formObj->populate($this->Request);

                }
            }
        }else{
             $this->view->title = 'Edit Accounting Class'; 
            $this->formObj->getElement('submit')->setLabel('Update');
             if($this->getRequest()->isPost()){
                $this->formObj->getElement('class_name')->removeValidator('Db_NoRecordExists'); 
                if($this->formObj->isValid($this->Request)){

                    // print_r($this->Request);die;

                                             
                     $result = $this->ModelObj->EditClassDetails($this->Request);

                     global $objSession; 

                     if($result['status'])
                        $objSession->successMsg = $result['message'];
                     else $objSession->errorMsg = $result['message'];   
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{

                    $this->formObj->populate($this->Request);

                }

            }
            else {  
                   $editdata = $this->ModelObj->getClassDetails();
                   $this->formObj->populate($editdata[0]);
            } 
        } 
        $this->view->AccountingClassForm =  $this->formObj;  

    }



     public function getaccountbanklistAction()
    {	
    	// echo "aaa";die;
    	# code...
    } 

    public function getaccountfunctionlistAction()
    {	
    	 $this->view->AccountingFunction = $this->ModelObj->AccountingFunctionDetails();
    }

    public function addeditaccountingfunctionAction()

    {    
        $this->_helper->layout->setLayout('popup');
        // echo "<pre>";
        // print_r($this->ModelObj->getclassactivitytypes());die;
        $this->formObj->addFunctionForm();
         // try {
        if($this->Request['mode'] == 'add'){
            $this->view->title = 'Add New Accounting Function';
                if($this->getRequest()->isPost()){
                   
                if($this->formObj->isValid($this->Request)){
                    // print_r($this->Request);die;
                    $result = $this->ModelObj->addFunctionDetails($this->Request);

                     global $objSession; 

                     if($result)
                        $objSession->successMsg = "Accounting Function Added successfully!!";
                     else $objSession->errorMsg = "Some internal error occurred"; 
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{
                    $this->formObj->populate($this->Request);

                }
            }
        }else{
             $this->view->title = 'Edit Accounting Class'; 
            $this->formObj->getElement('submit')->setLabel('Update');
             if($this->getRequest()->isPost()){
                $this->formObj->getElement('description')->removeValidator('Db_NoRecordExists'); 
                if($this->formObj->isValid($this->Request)){

                    // print_r($this->Request);die;

                                             
                     $result = $this->ModelObj->EditFunctionDetails($this->Request);

                     global $objSession; 

                     if($result['status'])
                        $objSession->successMsg = $result['message'];
                     else $objSession->errorMsg = $result['message'];   
                            echo '<script type="text/javascript">parent.window.location.reload();

                                parent.jQuery.fancybox.close();</script>';

                                exit(); 

                }else{

                    $this->formObj->populate($this->Request);

                }

            }
            else {  
                   $editdata = $this->ModelObj->getSingleFunctionDetails();
                   $this->formObj->populate($editdata[0]);
            } 
        } 
        $this->view->AccountingFunctionForm =  $this->formObj;  

    }

    
	 

}