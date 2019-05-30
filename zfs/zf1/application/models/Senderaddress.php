<?php

class Application_Model_Senderaddress extends Zend_Custom
{
    public function getSenderaddress($data){ 
	      $select = $this->_db->select()
									->from(array('SA' =>USER_SENDER_ADDRESS),array('*'))
									->joininner(array('AC' =>SENDER_ADDRESS_COUNTRIES),"SA.address_id=AC.address_id",array(''))
									->where("SA.user_id=?",$data['user_id'])
									->where("AC.country_id=?",$data['country_id']);//echo $select->__toString();die;
		 $senderaddresses =  $this->getAdapter()->fetchAll($select);
		 $option .= '<option value="C">Default Address</option>';
		 $option .= '<option value="B">No Address</option>';
		 foreach($senderaddresses as $senderaddress){
		   $selected = (isset($data['senderaddress_id']) && $data['senderaddress_id']==$senderaddress['address_id'])?'selected="selected"':'';
		   $address = array_filter(array($senderaddress['name'],$senderaddress['street'].' '.$senderaddress['streetnumber'],$senderaddress['streetaddress'],$senderaddress['postalcode'].' '.$senderaddress['city']));
		   $option .= '<option value="'.$senderaddress['address_id'].'" '.$selected.'>'.commonfunction::implod_array($address,', ').'</option>';  
		 }
		 return $option;
	}
	
	public function createSenderAddress($data){
	  		$contry_detail  = $this->getCountryDetail(trim($data['scountrycode']), 2);
			$this->_db->insert(USER_SENDER_ADDRESS,array_filter(array('user_id'=>$data['user_id'],'name'=>$data['sname'],'street'=>$data['saddress1'],'streetaddress'=>$data['saddress2'],'postalcode'=>$data['szipcode'],'city'=>$data['scity'],'country_id'=>$contry_detail[COUNTRY_ID])));
			return  $this->getAdapter()->lastInsertId();
	}
	public function getAddressID($data){
	   if($data['SenderCode']=='B'){
	      return 'B';
	   }elseif($data['SenderCode']=='C' || $data['SenderCode']==''){
	      return 'C';
	   }else{
	       $select = $this->_db->select()
									->from(array('SA' =>USER_SENDER_ADDRESS),array('address_id'))
									->joininner(array('AC' =>SENDER_ADDRESS_COUNTRIES),"SA.address_id=AC.address_id",array(''))
									->where("SA.user_id='".$data['user_id']."'")
									->where("SA.api_code='".$data['SenderCode']."'")
									->where("AC.country_id='".$data['country_id']."'");//echo $select->__toString();die;
		  $senderaddresses =  $this->getAdapter()->fetchRow($select);
		  if(!empty($senderaddresses)){
			  return  $senderaddresses['address_id'];
		  }else{
		      return 'C';
		  }
	   }
	}

}

