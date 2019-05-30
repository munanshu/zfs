<?php 
//ini_set('display_errors','1');
class CheckinajaxController extends Zend_Controller_Action
{

    public $ModelObj = null;

    private $Request = array();

    public function init()
    {
       try{	
			$this->_helper->layout()->disableLayout();
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Application_Model_Checkinajax();
			$this->ModelObj->getData  = $this->Request;
			$this->view->Request  = $this->Request;
	 }catch(Exception $e){
	    echo $e->getMessage();die;
	 }
    }

    public function indexAction()
    {
        // action body
    }

    public function scandetailsAction()
    {
        $response = $this->ModelObj->getScanDetails();
        echo $response;die; 
    }

    public function checkinAction()
    {
	    $response = $this->ModelObj->getParcelCheckin();
    }

    public function updateweightAction()
    {
	    $response = $this->ModelObj->UpdateWeight();
    }

    public function emergencycheckinAction()
    {
	   $response = $this->ModelObj->EmergencyCheckin();
    }

    public function emergencynewlabelAction()
    {
	   $response = $this->ModelObj->EmergencyCHeckinWithNewLabel();
    }

    public function scaninfoAction()
    {
        // action body
    }

    public function forwarderattribAction()
    {
       $this->_helper->layout->setLayout('popup');
	   if($this->_request->isPost() && isset($this->Request['pickup_address'])){
	         $this->ModelObj->AddSystematicPickupAddress();
			 echo '<script>parent.jQuery.fancybox.close();</script>';exit;
	   }
	   if(isset($this->Request['forwarder_id']) && $this->Request['forwarder_id']==48){
	      $this->view->pickupdetails = $this->ModelObj->systematicPickups();
	   }
    }

    public function weightscalerAction()
    {
        $this->_helper->layout->setLayout('popup');
		global $objSession;
		if($this->_request->isPost() && $this->Request['weight_scale']!=''){
		   $objSession->weight_scale = $this->Request['weight_scale'];
		   echo '<script type="text/javascript">parent.window.location.reload();
					 parent.jQuery.fancybox.close();</script>';
			   exit();
		}elseif($this->_request->isPost() && $this->Request['weight_scale']==''){
		   unset($objSession->weight_scale);
		   echo '<script type="text/javascript">parent.window.location.reload();
					 parent.jQuery.fancybox.close();</script>';
			   exit();
		}
		$this->view->scalelist = $this->ModelObj->getWeightScaleList();
    }
	
	public function printlabelAction(){ 
	    $this->ModelObj->getPrintlabelLink(); 
	}
	public function changepriceAction(){
	    $this->ModelObj->ChangeCustomerPrice(); 
	}
	public function referencecheckinAction(){
	    $this->ModelObj->ReferenceCheckIN(); 
	}
}









