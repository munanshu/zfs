<?php
class Planner_SchedulerouteController extends Zend_Controller_Action
{
    public $Request = array();

    public $ModelObj = null;

    public function init()
    {
       try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Planner_Model_Scheduleroute();
			$this->formObj = new Planner_Form_Planner();
			$this->ModelObj->getData  = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
			$this->_helper->layout->setLayout('main');
	  }catch(Exception $e){
	    echo $e->getMessage();die;
	  }
    }
	/**
	 * Get Driver Route schedule
	 * Function : routeAction()
	 * Date : 18/01/2017
	 **/
    public function routeAction()
    {  
       if(!empty($this->Request['Export'])){
	     $this->modelObj->ScheduleRouteExport();
	   }
	   $this->view->driver_list = $this->ModelObj->getDriverList();
	   $this->view->scheduleroute = $this->ModelObj->DriverRouteSchedule();
    }
	/**
	 * Get Driver Route schedule
	 * Function : assignrouteAction(()
	 * Date : 19/01/2017
	 **/
	public function assignrouteAction(){
	    if($this->_request->isPost() && $this->Request['Copy']=='Copy Last week Schedule'){
		   $this->view->copy_schedule = $this->ModelObj->getLastScheduleDate();//array('2015-10-05','2015-10-06','2015-10-07','2015-10-08','2015-10-09');
		}
		if($this->_request->isPost() && !empty($this->Request['submit'])){
		   $success = $this->ModelObj->AssignScheduleToDriver();
		   if($success){
		      $this->_redirect($this->_request->getControllerName() . '/route');
		   }
		}
	   $this->view->routesettinglist = $this->ModelObj->routesettinglist();
	   $this->view->driver_list = $this->ModelObj->getDriverList();
	}
	/**
	 * Get route setting list
	 * Function : routesettinglistAction(()
	 * Date : 19/01/2017
	 **/
	public function routesettinglistAction(){
		$this->view->routesettinglist = $this->ModelObj->RouteSettingList();
	}
	/**
	 * add edit route setting 
	 * Function : addeditroutesettingAction()
	 * Date : 19/01/2017
	 **/
	public function addeditroutesettingAction(){
	    $this->_helper->layout->setLayout('popup');
		   global $objSession;
		   $this->formObj->routeSettingForm();
		   if(isset($this->Request['mode']) && $this->Request['mode'] == 'add'){
			  $this->view->title = 'Add New Route';
			  if($this->_request->isPost() && !empty($this->Request['submit'])){
			 if($this->formObj->isValid($this->Request)){
			  $result=$this->ModelObj->addeditRouteSetting();
			  if($result){
				$objSession->successMsg = "Route Setting Added Successfully !!";
			  }else{
			    $objSession->successMsg = "Something is wrong !!";
			  }
			  echo '<script type="text/javascript">parent.window.location.reload();
				  parent.jQuery.fancybox.close();</script>';
				  exit(); 
			 }else{
			  $this->formObj->populate($this->Request);
			 }
			}
			  
		   }elseif($this->Request['mode'] == 'edit' && isset($this->Request['id'])){
			$this->view->title = 'Update Route Detail';
			$this->formObj->routeSettingForm()->submit->setLabel('Update Route');
			if($this->_request->isPost() && !empty($this->Request['submit'])){
			 if($this->formObj->isValid($this->Request)){
			  $result=$this->ModelObj->addeditRouteSetting();
			  if($result){
				$objSession->successMsg = "Route Setting Updated Successfully !!";
			  }else{
			    $objSession->successMsg = "Something is wrong !!";
			  }
			  echo '<script type="text/javascript">parent.window.location.reload();
				  parent.jQuery.fancybox.close();</script>';
				  exit(); 
			 }else{
			  $this->formObj->populate($this->Request);
			 }
			}else{
			 $fatchRowData = $this->ModelObj->RouteSettingList();
			 $this->formObj->populate($fatchRowData[0]);
			}
		   }
		   $this->view->plannerForm = $this->formObj;
	}
	/* get Driver schedule List
	 * function viewscheduleAction() 
     * Date : 20/01/2017
     */
	  public function viewscheduleAction(){
		  $this->_helper->layout->setLayout('popup');
		  $this->view->routesettinglist = $this->ModelObj->getDriverRouteList();
	  }	
  public function getdetailsdataAction(){
       $this->_helper->layout->disableLayout();
       $this->view->Planedsumm = $this->ModelObj->getSummeryRoute();
  }
  
  public function checkconflicttimeAction(){
       $this->view->Planedsumm = $this->ModelObj->checkConflict();
  }
}

