<?php
 
class Invoice_Model_Depotduesinvoice  extends Zend_Custom
{
    public function getListDuesInvoice(){
	  try{
	       /*$customer_code = trim($data['search']); 
		   $customer_search=Class_Encryption::decode($data['customer']);
		if($customer_search!=''){
		   $where .= " AND US.user_id='".$customer_search."'";
		  }
		if($data['search']!=''){
		    $filterCustomernumber = (!empty($customer_code))? 'AND US' . '.' .'customer_code'  . " LIKE '%" . $customer_code . "%'" : ''; 
		 //$filterCustomernumber .= " AND (US.customer_code LIKE '%".$data['search']."%')";
		 }*/
		 
		//$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'AT.company_name','ASC');
		$select =$this->_db->select()  
			               ->from(array('IVS'=>INVOICE),array('AT.user_id','IVS.invoice_number','IVS.total_amount','AS.payment_days','AT.company_name','IVS.paid_amount','AT.customer_number'))
						   ->joininner(array('AT'=>USERS_DETAILS),'IVS.user_id=AT.user_id',array(''))
						   ->joininner(array('AS'=>USERS_SETTINGS),'IVS.user_id=AS.user_id',array(''))
						   ->where("AS.payment_days>0")
						   ->where("IVS.total_amount-IVS.paid_amount>1 AND IVS.is_list='0'")
						   ->where("DATE_ADD(IVS.invoice_date,INTERVAL(AS.payment_days) DAY)< CURDATE() AND AT.parent_id=1")
						   ->group('AT.company_name');//print_r($select->__toString());die;
	     $result = $this->getAdapter()->fetchAll($select);  
		}catch(Exception $e){
		  $_SESSION[ERROR_MSG] = $e->getMessage();
		} 
		 return $result;
	}
	
	public function paymentHistory(){
		  $select =$this->_db->select()  
							   ->from(array('IVS'=>INVOICE),array('IVS.*','AT.user_id','IVS.invoice_number','IVS.total_amount','AS.payment_days','AT.company_name','IVS.paid_amount','AT.customer_number'))
							   ->joininner(array('AT'=>USERS_DETAILS),'IVS.user_id=AT.user_id',array(''))
							   ->joininner(array('AS'=>USERS_SETTINGS),'IVS.user_id=AS.user_id',array(''))
							   ->where("AS.payment_days>0")
							   ->where("IVS.total_amount-IVS.paid_amount>1 AND IVS.is_list='0'")
							   ->where("DATE_ADD(IVS.invoice_date,INTERVAL(AS.payment_days) DAY)< CURDATE() AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'");//print_r($select->__toString());die;
			 $result = $this->getAdapter()->fetchAll($select);
			return $result;
	}
}


