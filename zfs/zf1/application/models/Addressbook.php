<?php 
class Application_Model_Addressbook extends Zend_Custom
{
       public function getTotalAddress(){
	    $where = '1';
	    $where .= $this->LevelClause();
		if(isset($this->getData['usertoken']) && $this->getData['usertoken']!=''){
		 $where .= " AND UA.user_id=".Zend_Encript_Encription:: decode($this->getData['usertoken']); 
		}
	 	$select = $this->_db->select()
		        ->from(array('UA'=>ADDRESS_BOOK), array('totalBook'=>'COUNT(*)','UA.user_id'))
				->joininner(array('AT'=>USERS_DETAILS),'UA.user_id=AT.user_id', array('AT.company_name'))
				->where($where)
				->group('UA.user_id')
				->order('AT.company_name');
	   $result = $this->getAdapter()->fetchAll($select);
	   return $result;
	 }
	 
	 public function getAddressList(){
	   try{
	    $where = '';
		if(isset($this->getData['country_id']) && $this->getData['country_id']>0){
		  $where = " AND UA.country_id='".$this->getData['country_id']."'";
		}
		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'UA.name','ASC');
		$select = $this->_db->select()
		        ->from(array('UA'=>ADDRESS_BOOK), array('COUNT(1) AS CNT'))
				->joininner(array('AT'=>USERS_DETAILS),'UA.user_id=AT.user_id', array(''))
				->where("UA.user_id='".Zend_Encript_Encription::decode($this->getData['tocken'])."'".$where)
				->order('UA.name');//print_r($select->__toString());die;
	   $count = $this->getAdapter()->fetchRow($select);
	   
	 	$select = $this->_db->select()
		        ->from(array('UA'=>ADDRESS_BOOK), array('*'))
				->joininner(array('AT'=>USERS_DETAILS),'UA.user_id=AT.user_id', array('AT.company_name'))
				->joininner(array('CT' =>COUNTRIES),"CT.country_id=UA.country_id",array("CT.country_name"))
				->where("UA.user_id='".Zend_Encript_Encription::decode($this->getData['tocken'])."'".$where)
				->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
				->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
	   $result = $this->getAdapter()->fetchAll($select);
	    return array('Total'=>$count['CNT'],'Records'=>$result);
		}catch(Exception $e){ echo $e->getMessage();die;$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		 return array('Total'=>0,'Records'=>array());
		}
	 }
	 
	 public function addnewaddressData(){
   				$this->insertInToTable(ADDRESS_BOOK, array(array('customer_number'=>$this->getData['customer_number'],'user_id'=>$this->Useconfig['user_id'],'country_id'=>$this->getData['country'],'name'=>$this->getData['name'],'contact'=>$this->getData['contact'],'street'=>$this->getData['street'],'street_no'=>$this->getData['street_no'],'address'=>$this->getData['address'],'street2'=>$this->getData['street2'],'city'=>$this->getData['city'],'postalcode'=>$this->getData['postal_code'],'phone'=>$this->getData['phone'],'email'=>$this->getData['email_address'])));
   return;
  }
  
  
  public function getDataByaddressid(){
	   $select = $this->_db->select()
			  ->from(array('AB'=>ADDRESS_BOOK), array('*'))
				->where('AB.addressbook_id='.Zend_Encript_Encription:: decode($this->getData['token']));
				 $result = $this->getAdapter()->fetchRow($select);
				 return $result;
  }
  
  public function updateaddressData(){
	   $result = $this->UpdateInToTable(ADDRESS_BOOK, array(array('customer_number'=>$this->getData['customer_number'],'user_id'=>$this->Useconfig['user_id'],'country_id'=>$this->getData['country'],'name'=>$this->getData['name'],'contact'=>$this->getData['contact'],'street'=>$this->getData['street'],'street_no'=>$this->getData['street_no'],'address'=>$this->getData['address'],'street2'=>$this->getData['street2'],'city'=>$this->getData['city'],'postalcode'=>$this->getData['postal_code'],'phone'=>$this->getData['phone'],'email'=>$this->getData['email_address'])),'addressbook_id='.Zend_Encript_Encription:: decode($this->getData['token']));
	  return $result;
	}
}
?>
