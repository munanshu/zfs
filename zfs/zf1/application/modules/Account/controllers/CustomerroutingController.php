<?php
	/**

     * Controll the Customer  Routing module

     * @Auth : SJM softech Pvt. Ltd.

     * @Created Date: 13th-February-2017

     * @Description : Controll the functionality related to Customer Routing

     **/

class Account_CustomerroutingController extends Zend_Controller_Action

{

	public $CustomerRoutingObj;

	public $Request = array();

	public $countries = array();

	public $Forwarders= array();

	

   /**

	* Initialize action controller

	* Function : init()

	* Auto create,loads and call the default object of model ,form and layout

	**/

    public function init()

    { 

		$this->_helper->layout->setLayout('main'); 

		try{

			$this->CustomerRoutingObj = new Account_Model_Customerrouting();

			$this->Request = $this->_request->getParams();

			

			$this->CustomerRoutingObj->getData = $this->Request;

			

			$this->countries = $this->CustomerRoutingObj->getCountryList();

			$this->view->CountryList = commonfunction::scalarToAssociative($this->countries,array(COUNTRY_ID,'country_code_name'));

			

			$this->Forwarders = $this->CustomerRoutingObj->getForwarderList();

			$this->view->ForwarderList = commonfunction::scalarToAssociative($this->Forwarders,array('forwarder_id','forwarder_name'));

		

			$this->view->token = (isset($this->Request['token'])) ? $this->Request['token'] : '';

			

			$this->view->customerName = (isset($this->Request['token'])) ? $this->CustomerRoutingObj->CompanyName(Zend_Encript_Encription::decode($this->Request['token'])) : '' ;

		

		}

		catch(Exception $e){

		  print_r($e->getMessage());die; 

		}

    }

	

	

	public function customerroutingAction(){

		$UserId = Zend_Encript_Encription::decode($this->Request['token']);

		$Records = $this->CustomerRoutingObj->CustomerRoutingList();

		

		$RoutingServices = array();

		foreach($Records as $key=>$record){

			$RoutingServices[$key] = $this->CustomerRoutingObj->getforwarderSelectedService(array('user_id'=>$UserId,'country_id'=>$record['country_id'],'forwarder_id'=>$record['forwarder_id']));

		}

		

		$this->view->CountryForwarderService = $RoutingServices;

		$this->view->records = $Records;

		

	}

	

	

	public function addcustomerroutingAction(){

		global $objSession;

		$countryId = (isset($this->Request['country_id'])) ? $this->Request['country_id'] : 0 ;

		

		$countryData = ($countryId>0) ? $this->CustomerRoutingObj->getCountryDetail($countryId,1) : array();

		

		$this->view->SelectedCountry = (count($countryData)>0) ? $countryData['cncode'].'-'.$countryData['country_name'] :'';

		

		$CountryForwarders = ($countryId>0) ? $this->CustomerRoutingObj->getForwarderCountry() : array();

		$this->view->CountryForwarderList = (count($CountryForwarders)>0) ? commonfunction::scalarToAssociative($CountryForwarders,array('forwarder_id','forwarder_name')) : array();

		

		$Services = $this->CustomerRoutingObj->getCustomServiceList();

		$this->view->ServiceList = commonfunction::scalarToAssociative($Services,array('service_id','service_name'));

		

		

		if($this->getRequest()->isPost()){

			

			if(isset($this->Request['assignforwarder'])){	

				$routingData = array();

				

				$allServiceArr = $this->CustomerRoutingObj->getCountryRoutingServices();

				

				$UserId 	= Zend_Encript_Encription::decode($this->Request['token']);

				

				$i=0;

				if(count($this->Request['forwarder_id'])>0){

					foreach($this->Request['forwarder_id'] as $key=>$forwarder){

						if(count($this->Request['service_id'][$key])>0){

							foreach($this->Request['service_id'][$key] as $index=>$service){

								if(!in_array($service,$allServiceArr)){

									

									$allServiceArr[$service] = $service;

									

									$routingData[$i]['user_id'] = $UserId;

									$routingData[$i]['country_id'] = $this->Request['country_id'];;

									$routingData[$i]['forwarder_id'] = $forwarder;

									$routingData[$i]['service_id'] = $service;

									$i++;

								}

							}

						}

					}

				}

				if(count($routingData)>0){

					if($this->CustomerRoutingObj->addcustomerrouting($routingData)){

						$objSession->successMsg = "Customer routing added successfully!!";

					}

					else{

						$objSession->errorMsg = "There is some error, Please try again !";

					}

				}

				$this->_redirect($this->_request->getControllerName().'/customerrouting?token='.$this->Request['token']);

			}

		}

	}

	

	

	public function editroutingAction(){

		global $objSession;

		$this->_helper->layout->setLayout('popup');

		$RoutingServices = array();

		

		$Records = $this->CustomerRoutingObj->CustomerRoutingList();

		$this->view->RoutingData = $Records[0];

		

		$ServiceResult = $this->CustomerRoutingObj->getforwarderSelectedService(array('user_id'=>$Records[0]['user_id'],'country_id'=>$Records[0]['country_id'],'forwarder_id'=>$Records[0]['forwarder_id']));

		

		foreach($ServiceResult as $key=>$record){

			$RoutingServices[$record['service_id']] = $record['service_id'];

		}

		

		$CountryForwarders = ($this->Request['country_id']>0) ? $this->CustomerRoutingObj->getForwarderCountry() : array();

		

		$this->view->CountryForwarderList = (count($CountryForwarders)>0) ? commonfunction::scalarToAssociative($CountryForwarders,array('forwarder_id','forwarder_name')) : array();

		

		$Services = $this->CustomerRoutingObj->getCustomServiceList();

		$this->view->ServiceList = commonfunction::scalarToAssociative($Services,array('service_id','service_name'));

		

		$this->view->selectedService = $RoutingServices;

		

		if($this->getRequest()->isPost()){

			 

			 $this->CustomerRoutingObj->updaterouting();

			 $objSession->successMsg = "Routing updated successfully!!";

			 echo '<script type="text/javascript">parent.window.location.reload();

			 parent.jQuery.fancybox.close();</script>';

			 exit();

		}

	}

	

	

	public function deleteroutingAction(){

		global $objSession;

		$this->CustomerRoutingObj->deleterouting();

		$objSession->successMsg = "Routing deleted successfully!!";

		$this->_redirect($this->_request->getControllerName().'/customerrouting?token='.$this->Request['token']);

	}

	

}







