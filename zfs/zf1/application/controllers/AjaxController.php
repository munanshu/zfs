<?php
class AjaxController extends Zend_Controller_Action
{
	public $ModelObj = NULL;
	private $Request = array();
    public function init()
    {
        /* Initialize action controller here */
		try{	
			$this->_helper->layout()->disableLayout();
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Application_Model_Ajax();
			$this->ModelObj->getData  = $this->Request;
	 }catch(Exception $e){
	    echo $e->getMessage();die;
	 }
		
    }

    public function indexAction()
    {
        // action body
    }
	/**
     * Action to handle country chnage action from add or edit shipment.
	 * @Name: getcontinentAction()
     * @param  null| No parameter required
     * @return continent ID of requested country in text format
     */	
	public function getcontinentAction(){ 
	    $country_detail = $this->ModelObj->getCountryDetail($this->Request[COUNTRY_ID],1);
		$forwarder_setting = $this->ModelObj->getfrwarderSettings();
	  	echo json_encode(array('continent_id'=>$country_detail['continent_id'],'postcode_length'=>$country_detail['postcode_length'],'postcode_validate'=>($country_detail['postcode_validate']=='')?1:$country_detail['postcode_validate'],'house_number'=>$forwarder_setting['house_number']));die; 
	}
	/**
     * Action to get city name and street by Postalcode
	 * @Name: getcityAction()
     * @param  null| No parameter required
     * @return city name and street as HTML textbox or dropdown
     */	
	public function getcityAction(){
	    $citylist = $this->ModelObj->getCity();
	    echo $citylist;die;
	}
	/**
     * Action to get service list from for add shipment form
	 * @Name: getserviceAction()
     * @param  null| all data comes in post form
     * @return list of Services in string form
     */	
	public function getserviceAction(){ 
	    $services = $this->ModelObj->getServiceList();
	    echo $services;die;
	}
	public function getaddserviceAction(){
	    $addservices = $this->ModelObj->getAddServiceList();
	    echo $addservices;die;
	}
	
	public function searchaddressAction(){
	   $address = $this->ModelObj->getAddressbook();
	   echo json_encode($address);die;
	}
	public function getstreetAction(){
	    $streets = $this->ModelObj->getStreetList();
	   	echo json_encode($streets);die;
	}
	public function weightclassserviceAction(){
	    $weightclassser = $this->ModelObj->getWeightClassService(); //echo "<pre>";print_r($weightclassser);die;
	   	echo $weightclassser;die;
	}
	public function modifyextraheadAction(){
	   $modify = $this->ModelObj->ModifyInvoiceExtrahead(); //echo "<pre>";print_r($weightclassser);die;
	   	echo 'Modify Successfully';die;
	
	}
	public function changestatusAction(){
     $status = $this->ModelObj->getChangeStatus($this->Request);
     echo $status;die;
	}
  public function deleterecordAction(){
	  $status = $this->ModelObj->deleterecord($this->Request);
	  echo $status;die;
  }
  
  public function senderaddressAction(){
      $senderadd = new Application_Model_Senderaddress();
	  $address = $senderadd->getSenderaddress($this->Request);
	  echo $address;die; 
  }
}

