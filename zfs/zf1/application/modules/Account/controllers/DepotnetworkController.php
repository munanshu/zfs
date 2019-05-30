<?php
	/**

     * Controll the Depot Network Routing (For Depot) module

     * @Auth : SJM softech Pvt. Ltd.

     * @Created Date: 4-9-17th-September-2017

     * @Description : Controll the functionality related to Depot Network Routing

     **/

class Account_DepotnetworkController extends Zend_Controller_Action

{

	public $ModelObj;

	public $formObj;

	public $Request = array();

	

    

    public function init()

    { 

		$this->_helper->layout->setLayout('main'); 

		try{

			$this->ModelObj = new Account_Model_Depotnetworkmanager();

			$this->Request = $this->_request->getParams();

			$this->ModelObj->getData = $this->Request;

			$this->formObj = new Account_Form_Account();
		}

		catch(Exception $e){

		  print_r($e->getMessage());die; 

		}

    }


    public function addeditdepotnetroutingAction()
    {
       		
      global $objSession;		
	  $depot_id = Zend_Encript_Encription:: decode($this->Request['token']);
	        // $this->_helper->layout->setLayout('popup');
	    	$this->countries = $this->ModelObj->getCountryList();
	    	// $this->forwarders = $this->ModelObj->getForwarderList();
	    	$this->services = $this->ModelObj->getAllServices();
	    	$ScheduledRouteModel = new Planner_Model_Scheduleroute();	
	    	$this->scheduled_routings = $ScheduledRouteModel->RouteSettingList();
	    	
	    	$this->formObj->listCountry = commonfunction::scalarToAssociative($this->countries,array(COUNTRY_ID,'country_code_name'));

	    	$this->formObj->listScheduledRoutings = commonfunction::scalarToAssociative($this->scheduled_routings,array('route_id','routename'));

	    	$this->formObj->listServices = commonfunction::scalarToAssociative($this->services,array('service_id','service_name'));
	    	$isedit = ($this->Request['currentMode'] == 'edit')? false: true;
	    	$this->formObj->addDepotRoutingForm($isedit);

	    	if(isset($this->Request['token']) && is_numeric($depot_id) ){

	    	if($this->Request['currentMode'] == 'edit')
	    		$ExistingDNRouting = $this->ModelObj->getExistingRoutings($depot_id);



	    	if($this->getRequest()->IsPost()){
	    		
	    		if($this->formObj->IsValid($this->Request)){

	    			if( !is_array($res = $this->ModelObj->validatePostcodes())){

	    			
	    			if(isset($this->Request['currentMode']) && $this->Request['currentMode'] == 'edit')
	    				$result = $this->ModelObj->EditDepotNetworkRouting();
	    			else $result = $this->ModelObj->AddDepotNetworkRouting(); 

	    			if( !empty(array_filter($result['success'])) )

	    				$objSession->successMsg = implode("<br>",array_filter($result['success']));
	    			
	    			if( !empty(array_filter($result['error'])) )

	    				$objSession->errorMsg = implode("<br>",array_filter($result['error']));
	    			  
	    			
	    			if( empty(array_filter($result['error'])) ){

	    				$this->_redirect(BASE_URL.'/Depotnetwork/alldepotnetworkrouting/'.$this->Request['token']);
	    			}
	    			else $this->formObj->populate($this->Request);
	    				 	 

	    			}
	    			else { 
	    				$objSession->errorMsg = implode('<br>', $res);
	    				$this->formObj->populate($this->Request);
	    			}
	    		}
	    		 	
	    	}else $this->formObj->populate($this->Request);

	    	if($this->Request['currentMode'] == 'edit'){
	    		if($ExistingDNRouting['status'] ==1){

	    		if($this->getRequest()->IsPost()){
	    			$this->formObj->populate($this->Request);
	    		}
	    		else $this->formObj->populate($ExistingDNRouting['data']);

	    		$this->Request['currentMode'] = 'edit';
	    		$this->formObj->getElement('submit')->setLabel('Edit');
    			$this->view->title = 'Edit Depot Network Routing';
    		  }
	    	}else $this->view->title = 'Add Depot Network Routing';

	    }else $objSession->errorMsg = 'Invalid Token given';

	    	$this->view->dn_form = $this->formObj;
	    	$this->view->Request = $this->Request;
	    	$this->view->token = $this->Request['token'];
    
    }

	
    public function alldepotnetworkroutingAction()
    {
    	$res = $this->ModelObj->getAllDepotRouting();
    	$this->view->records = $res;
    	$this->view->title = (count($res)>0)? 'Edit' :'Add new';
    	$this->view->token = $this->Request['token'];
    }
	

	public function deletedepotnetroutingAction()
	{	
		global $objSession;
		
			if(isset($this->Request['currentMode']) && $this->Request['currentMode']=='delete')
			$res = $this->ModelObj->DeleteDepotRouting();
			
			if($res['status'])
				 $objSession->successMsg = $res['message'];
			else $objSession->errorMsg = $res['message'];
		$this->_redirect(BASE_URL.'/Depotnetwork/alldepotnetworkrouting/'.$this->Request['token']);
	}


}