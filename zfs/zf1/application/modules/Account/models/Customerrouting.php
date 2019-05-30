<?php
class Account_Model_Customerrouting extends Account_Model_Accountmanager
{
	/**
     * All the Function Related to Customer Routing
      @Auth : SJM Softech Pvt. Ltd
      @Create Date : 13rd-Feb-2017
      @Description : This module Consists All the methods which manage the Customer Routing
     * */

    public function __construct()
    {   
		parent::__construct();
		
	}
	
	public function CustomerRoutingList(){
		
	    $UserId = Zend_Encript_Encription::decode($this->getData['token']);
		
		$forwarderId = (isset($this->getData['newforwarder'])) ? $this->getData['newforwarder'] : (isset($this->getData['forwarder_id'])) ? $this->getData['forwarder_id'] : '';
				
		$countryId = (isset($this->getData['country_id'])) ? "URT.country_id=".$this->getData['country_id'] : '1';
		$ForwarderId = (!empty($forwarderId)) ? "URT.forwarder_id=".$forwarderId : '1';
		$records = array();
		$select = $this->_db->select()
				 ->from(array('URT'=>CUSTOMER_ROUTING),array('*'))
				 ->joinleft(array('CT'=>COUNTRIES),'CT.country_id=URT.country_id',array('cncode','country_name'=>'country_name'))
				 ->joinleft(array('ST'=>SERVICES),'ST.service_id=URT.service_id',array('service_name'=>'service_name'))
				 ->joinleft(array('FT'=>FORWARDERS),'FT.forwarder_id=URT.forwarder_id',array('forwarder_name'=>'forwarder_name'))
				 
				 ->where("URT.user_id=".$UserId)
				 ->where($countryId)
				 ->where($ForwarderId)
				 ->group("forwarder_id")
				 ->group("country_id")
				 ->order('URT.id');	//echo $select->__tostring();die;
		return $this->getAdapter()->fetchAll($select);
	}
	
	
	public function addcustomerrouting($data=array()){
		return $this->insertInToTable(CUSTOMER_ROUTING,$data);
	}
	
	
	public function getforwarderSelectedService($data=array()){
		$select = $this->_db->select()
				 ->from(array('SV'=>SERVICES),array('service_id','service_name'))
				 ->joininner(array('URT'=>CUSTOMER_ROUTING),'URT.service_id=SV.service_id',array(''))
				 ->joinleft(array('PSV'=>SERVICES),"PSV.service_id=SV.parent_service", array('service_name AS parent_name'))
				 
				 ->where("URT.user_id=".$data['user_id'])
				 ->where("URT.country_id=".$data['country_id'])
				 ->where("URT.forwarder_id=".$data['forwarder_id'])
				 ->where("SV.status='1'")
				 ->order(new Zend_Db_Expr("CASE WHEN SV.parent_service=0 THEN SV.service_id ELSE SV.parent_service END"))
				 ->order('SV.service_id');	//echo $select->__tostring();die;
		$services =   $this->getAdapter()->fetchAll($select);
		
		$FinalServiceArr = array();
		foreach($services as $service){
			$service['service_name'] =  $service['service_name'].(($service['parent_name']!='')?' -'.$service['parent_name']:'');
			$FinalServiceArr[] = $service;
		}	//echo"<pre>";print_r($FinalServiceArr);die;
		return $FinalServiceArr;	
		
	}
	
	
	public function updaterouting(){
		
		$this->getData['user_id'] = Zend_Encript_Encription::decode($this->getData['token']);
		
		$oldroutingData = $this->CustomerRoutingList();
		
		$OldServices = $this->getCountryRoutingServices();
		
		$i=0;	$dataArr = array();
		$newservices = array();
		foreach($this->getData['service_id'] as $newData){
			$newservices[] = $newData;
			if(!in_array($newData,$OldServices)){
				$dataArr[$i]['user_id'] 	= $this->getData['user_id'];
				$dataArr[$i]['country_id'] 	= $this->getData['country_id'];
				$dataArr[$i]['forwarder_id']= $this->getData['newforwarder'];
				$dataArr[$i]['service_id'] 	= $newData;
				$i++;
			}
		}
		
		if($this->getData['oldforwarder']==$this->getData['newforwarder']){
			if(count($dataArr)>0){
				$this->insertInToTable(CUSTOMER_ROUTING,$dataArr);
			}
			if(count($newservices)>0){
				$AllId = commonfunction :: implod_array($newservices);
				$this->_db->delete(CUSTOMER_ROUTING,"user_id=".$this->getData['user_id']." AND country_id=".$this->getData['country_id']." AND forwarder_id=".$this->getData['newforwarder']." AND service_id NOT IN(".$AllId.")");
			}
		}
		else{
			$this->_db->delete(CUSTOMER_ROUTING,"user_id=".$this->getData['user_id']." AND country_id=".$this->getData['country_id']." AND forwarder_id=".$this->getData['oldforwarder']);
			
			$i=0;	$dataArr = array();
			$OldServices = $this->getCountryRoutingServices();
			
			foreach($this->getData['service_id'] as $newData){
				if(!in_array($newData,$OldServices)){
					$dataArr[$i]['user_id'] 	= $this->getData['user_id'];
					$dataArr[$i]['country_id'] 	= $this->getData['country_id'];
					$dataArr[$i]['forwarder_id']= $this->getData['newforwarder'];
					$dataArr[$i]['service_id'] 	= $newData;
					$i++;
				}
			}
			if(count($dataArr)>0){
				$this->insertInToTable(CUSTOMER_ROUTING,$dataArr);
			}
		}
		return;
	}
	
	
	public function deleterouting(){
		 $UserId = Zend_Encript_Encription::decode($this->getData['token']);
		
		$this->_db->delete(CUSTOMER_ROUTING,"user_id=".$UserId." AND country_id=".$this->getData['Country']." AND forwarder_id=".$this->getData['Forwarder']);
		return;
	}
	
	
	public function getCountryRoutingServices(){

		$select = $this->_db->select()
				 ->from(array('URT'=>CUSTOMER_ROUTING),array('*'))
				 ->where("URT.user_id=".Zend_Encript_Encription::decode($this->getData['token'])." AND country_id=".$this->getData['country_id'])
				 ->order('URT.id');	//echo $select->__tostring();die;
		$ServiceData = $this->getAdapter()->fetchAll($select);
		$AllassignedServices = array();
		foreach($ServiceData as $value){
			$AllassignedServices[$value['service_id']] = $value['service_id'];
		}
		return $AllassignedServices;
	}
	
}