<?php
class Account_Model_Senderaddress extends Account_Model_Accountmanager
{
	/**
     * All the Function Related to Customer serder address
      @Auth : SJM Softech Pvt. Ltd
      @Create Date : 18-Jan-2017
      @Description : This module Consists All the methods which manage the Customer Sender Address
     * */

    public function __construct()
    {   
		parent::__construct();
		
	}
	
	public function getSenderAddressList($data=array()){ 
		
		$UserId = Zend_Encript_Encription:: decode($data['token']);
		
		try{
			if($UserId>0){
				$where = " USA.user_id=".$UserId;
				
				if((isset($data['address'])) && (!empty($data['address']))){
					
					$AddressId = Zend_Encript_Encription :: decode($data['address']);
					
					$where .= " AND USA.address_id=".$AddressId;
				}
					
				$select = $this->_db->select()
						->from(array('USA'=>USER_SENDER_ADDRESS), array('*'))
						->joininner(array('CT'=>COUNTRIES) ,' CT.country_id=USA.country_id',array('country_name'))
						->where($where);
						//echo $select->__tostring(); die;
				$AddressArr = $this->getAdapter()->fetchAll($select);
				$AddressCountries = array();
				foreach($AddressArr as $address){
					$AddressCountries[$address['address_id']] = $this->getSenderAddressCountry(array('addressID'=>$address['address_id'])); 
				}
				return array('Addresses'=>$AddressArr,'AddressCountry'=>$AddressCountries);
			}
			else{return array();}
		}
		catch(Exception $e){
			die('Something is wrong: ' . $e->getMessage());
		}
	}
	
	
	public function getSenderAddressCountry($data=array()){
	
		$addressID = (!empty($data['addressID'])) ? trim($data['addressID']) : 0;
		if($addressID>0){
			$select = $this->_db->select()
						->from(array('SAC'=>SENDER_ADDRESS_COUNTRIES),array('*'))
						->joininner(array('CT'=>COUNTRIES),'CT.country_id=SAC.country_id',array('CName'=>'CT.country_name','CID'=>'CT.country_id'))
						->where('SAC.address_id =?',$addressID);  //print_r($select->__toString());die;
			return $this->getAdapter()->fetchAll($select);
		}
		else return array();
	}
	
		
	public function addsenderaddress($data=array()){
		
		$uploadFiles 	= array('logo');
		$uploadedFiles 	= $this->uploadFiles($uploadFiles);
		
		$data['logo']  	= $uploadedFiles['logo'];
		
		$data['user_id'] 	= Zend_Encript_Encription:: decode($data['token']);
		$data['added_by'] 	= $this->Useconfig['user_id'];
		$data['added_ip']	= commonfunction::loggedinIP();
		
		$data['api_code']	= $this->SenderApiCode($data['user_id']);
		
		$AddressId = $this->insertInToTable(USER_SENDER_ADDRESS,array($data));
		
		if($data['set_default']=='1'){
			$where = "user_id=".$data['user_id']." AND address_id !=".$AddressId;
			$Updatedata['set_default'] = '0';
			$this->UpdateInToTable(USER_SENDER_ADDRESS,array($Updatedata),$where);
		}
		
		if($AddressId>0){
			$countryData['address_id'] = $AddressId;
			$countryData['country_id'] = $data['country_id'];
			$this->insertInToTable(SENDER_ADDRESS_COUNTRIES,array($countryData));
		}
		return $AddressId;
	}
	
	
	public function updatesenderaddress($data=array()){
		
		$uploadFiles 	= array('logo');
		$uploadedFiles 	= $this->uploadFiles($uploadFiles);
		
		if((!empty($uploadedFiles['logo'])) || $data['removeflag']==1){
			$data['logo']  	= $uploadedFiles['logo'];
		}
		
		$data['user_id'] 		= Zend_Encript_Encription:: decode($data['token']);
		$data['updated_date']	= new Zend_Db_Expr('NOW()');
		$data['updated_by'] 	= $this->Useconfig['user_id'];
		$data['updated_ip']		= commonfunction::loggedinIP();
		
		$addressId 	= Zend_Encript_Encription::decode($data['address']);
		$where = "address_id=".$addressId;
		
		//get sender address api code
		$select = $this->_db->select()
						->from(array('USA'=>USER_SENDER_ADDRESS), array('*'))
						->where($where);
						//echo $select->__tostring(); die;
				$AddressArr = $this->getAdapter()->fetchRow($select);
		
		$data['api_code']=(!empty($AddressArr['api_code']))?$AddressArr['api_code']:$this->SenderApiCode($data['user_id']);
		
		$this->UpdateInToTable(USER_SENDER_ADDRESS,array($data),$where);
		
		$where .= " AND country_id=".$AddressArr['country_id'];
		
		// update sender address country list
		if($data['country_id']!=$AddressArr['country_id']){
			
			$this->UpdateInToTable(SENDER_ADDRESS_COUNTRIES,array($data),$where);
		}
		elseif($data['country_id']==$AddressArr['country_id']){	
			$select = $this->_db->select()
						->from(array('USA'=>SENDER_ADDRESS_COUNTRIES), array('*'))
						->where($where);
				$CountryArr = $this->getAdapter()->fetchAll($select);
				
			if(count($CountryArr)==0){
				$countryData['address_id'] = $addressId;
				$countryData['country_id'] = $data['country_id'];
				$this->insertInToTable(SENDER_ADDRESS_COUNTRIES,array($countryData));
			}
		}
		return true;
	}
	
	
	
	/*
	 * Return Unique Sender Code for API OR Import Shipment
	 */
	public function SenderApiCode($UserId){
		$senderCode = $this->RandomDigitSenderAddress();
		
		$where = "user_id=".$UserId." AND api_code=".$senderCode;
		
		$select = $this->_db->select()
						->from(array('USA'=>USER_SENDER_ADDRESS), array('*'))
						->where($where);
						//echo $select->__tostring(); die;
				$AddressArr = $this->getAdapter()->fetchAll($select);
		if(count($AddressArr)==0){
			return $senderCode;
		}else{
			$this->SenderApiCode($UserId);
		}	
	}
	
	
	/*
	 * Get 3 Digit Random Number
	 */
	public function RandomDigitSenderAddress(){
		$length = 5;
		$random= "";
		srand((double)microtime()*1000000);
		
		$data = "1234567890";
		for($i= 0; $i < $length; $i++){
		  $random .= substr($data, (rand()%(strlen($data))), 1);
		}
		return $random;
	}
	
	
	public function addsenderaddresscountry($data=array()){
		
		//$data['user_id'] 	= Zend_Encript_Encription:: decode($data['token']);
		$data['address_id'] = Zend_Encript_Encription:: decode($data['address']);
		
		if((isset($data['country_id'])) && (count($data['country_id'])>0)){
			$countryArr = $data['country_id'];
			
			$this->_db->delete(SENDER_ADDRESS_COUNTRIES,"address_id=".$data['address_id']);
			
			foreach($countryArr as $country){
				$data['country_id'] = $country;
				
				$this->insertInToTable(SENDER_ADDRESS_COUNTRIES,array($data));
			}
		}
		elseif((isset($data['country'])) && (count($data['country'])>0)){
			$countryArr = $data['country'];
			
			foreach($countryArr as $country){
				$this->_db->delete(SENDER_ADDRESS_COUNTRIES,"address_id=".$data['address_id']." AND country_id=".$country);
			
			}	
		}
		return true;
	}
	
	
	public function updatedefaultcountry(){
		
		$userId = Zend_Encript_Encription:: decode($this->getData['token']);
		$addressId = Zend_Encript_Encription:: decode($this->getData['address']);
		
		$country = commonfunction::implod_array($this->getData['set_default']);
		$updateddata['set_default'] = '1';
		
		$where = "address_id=".$addressId." AND country_id IN(".$country.")";
		
		$this->UpdateInToTable(SENDER_ADDRESS_COUNTRIES,array($updateddata),$where);
		
		return true;
	}
	
	public function deletesenderaddress(){
		$AddressId = Zend_Encript_Encription:: decode($this->getData['address']);
		
		if($this->_db->delete(USER_SENDER_ADDRESS,"address_id=".$AddressId)){
			$this->_db->delete(SENDER_ADDRESS_COUNTRIES,"address_id=".$AddressId);
		}	
		return true;
	}
	
	
	public function countryaddresslist(){
			
		 $user_id = Zend_Encript_Encription::decode($this->getData['token']);
		  
		  $select  = $this->_db->select()
		  		->from(array('USACT'=>SENDER_ADDRESS_COUNTRIES),array('name'=>'USAT.name','street'=>'USAT.street','streetaddress'=>'USAT.streetaddress','postalcode'=>'USAT.postalcode','set_default'=>'USAT.set_default','CName'=>'CT.country_name'))
				->joininner(array('CT'=>COUNTRIES),'USACT.country_id=CT.country_id')
				->joininner(array('USAT'=>USER_SENDER_ADDRESS),'USAT.address_id=USACT.address_id')
				->where('CT.country_id =?',$this->getData['country'])
				->where("USAT.user_id='".$user_id."'");
					//echo $select->__tostring(); die;	
			$result = $this->getAdapter()->fetchAll($select); 
		return $result;
	}
	
	
	public function removeaddressdefaultcountry(){
		
		$userId = Zend_Encript_Encription::decode($this->getData['token']);
		$updateddata['set_default'] = '0';
		
		$where = "country_id=".$this->getData['country']." AND user_id=".$userId;
		
		$this->UpdateInToTable(SENDER_ADDRESS_COUNTRIES,array($updateddata),$where);
		return true;
	}
}