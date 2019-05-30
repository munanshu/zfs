<?php

class Accounting_Model_Settingmanager extends Zend_Custom

{	

	public function tabNavigation(){

		$tabhtml = '';

		if($this->Useconfig['level_id'] == 1){

			$tabArray = array('Accounting Head'=>'getaccountheadlist','Accounting Group'=>'getaccountgrouplist','Accounting Class'=>'getaccountclasslist','Accounting Bank'=>'getaccountbanklist','Accounting Function'=>'getaccountfunctionlist');

		}else{

			$tabArray = array('Accounting Head'=>'getaccountheadlist','Accounting Bank'=>'getaccountbanklist','Accounting Function'=>'getaccountfunctionlist');

		}

		foreach($tabArray as $key=>$value){

			if($value == $this->getData['action']){

				$tabhtml .= '<li class="active"> <a href="'.BASE_URL.'/Accountsetting/'.$value.'" data-toggle="">'.$key.'</a> </li>';

			}else{

				$tabhtml .= '<li> <a href="'.BASE_URL.'/Accountsetting/'.$value.'" data-toggle="">'.$key.'</a> </li>';

			}

		}

		return $tabhtml;

	}

	public function AccountingHeadsDetails()
	{	
		// echo "FSD";die;
		$select = $this->_db->select()->from(array('AH'=>AccountingHead),array('AH.head_id','CONCAT(AH.head_code,",",AH.head_description) as AccountDesc','AH.btwrate_id','AH.status','AH.function_id','AH.class_id','AH.group_id'))
			->joinleft(array('AF'=>AccountingFunction),'AH.function_id = AF.function_id',array('AF.description as function_name'))
			->joinleft(array('AC'=>AccountingClass),'AH.class_id = AC.class_id',array('AC.class_name'))
			->joinleft(array('AG'=>AccountingGroup),'AH.group_id = AG.group_id',array('AG.group_name',new Zend_Db_Expr('CASE when AG.sub_group_id !=0 then (SELECT AG1.group_name from parcel_accounting_groups as AG1 where AG1.group_id = AG.sub_group_id ) END as sub_group_name')))
			->order('AH.head_description');
		// echo $select->__tostring();die;
		$result = $this->getAdapter()->fetchAll($select);
		return $result;
		// echo "<pre>";	
		// print_r($result);die;
	}

	public function getselectboxList($table,$fields,$ordertype='ASC')
	{		 
		$select = $this->_db->select()->from(array('AF'=>$table),array('AF.'.$fields[0],'AF.'.$fields[1]))
					->order('AF.'.$fields[0].' '.$ordertype);
		$result = $this->getAdapter()->fetchAll($select);	
		return $result;
	}

	public function getselectboxListbyClass($table,$fields,$ordertype='ASC',$class_id='')
	{	
		$where = "1";
		if(isset($class_id) && !empty($class_id))
			$where = "AF.class_id='$class_id'";

		$select = $this->_db->select()->from(array('AF'=>$table),array('AF.'.$fields[0],'AF.'.$fields[1]))
			->where($where)
					->order('AF.'.$fields[0].' '.$ordertype);
		$result = $this->getAdapter()->fetchAll($select);	
		return $result;
	}

	public function getbtwratelist()
	{	
		$select = $this->_db->select()
		->from( array('BR'=>AccountingBtwRates) , array('BR.btwrate_id'))
		->joinleft(array('BRT'=>AccountingBtwRateTypes),'BR.btwrate_type=BRT.btwrate_type_id',array(' CONCAT(BRT.btwrate_type_name," rate ",BR.btw_rate," %") as btwrates_desc'));	
		$result = $this->getAdapter()->fetchAll($select);
		return $result;
	}

	public function getbtwrateforinvoice()
	{
		$select = $this->_db->select()
		->from( array('BR'=>AccountingBtwRates) , array('BR.btw_rate'))
		->joinleft(array('BRT'=>AccountingBtwRateTypes),'BR.btwrate_type=BRT.btwrate_type_id',array(' CONCAT(BRT.btwrate_type_name," rate ",BR.btw_rate," %") as btwrates_desc'));	
		$result = $this->getAdapter()->fetchAll($select);
		return $result;
	}

	public function getaccountgroupbyclass()
	{	
		$class_id = $this->getData['classID'];
		$select = $this->_db->select()
		->from(array('AG'=>AccountingGroup),array('AG.group_id','AG.group_name'))
		->where('AG.class_id='.$class_id);
		 $result = $this->getAdapter()->fetchAll($select);
		return $result;
	}

	public function getHeadDetails()
	{	
		$head_id = $this->getData['id'];
		$select = $this->_db->select()->from(array('AH'=>AccountingHead),array('AH.head_id','AH.head_code','AH.head_description','AH.btwrate_id','AH.class_id','AH.group_id','AH.function_id'))
			->where("AH.head_id='$head_id'");
		$result = $this->getAdapter()->fetchAll($select);
		return $result;	
	}

	public function addHeadDetails($data)
	{	
		try{
		
		$formdata = $this->getData;
		$createdbydetails = commonfunction::createdByDetails($this->Useconfig['level_id']);
		$newdata = array_merge($createdbydetails,$formdata);
		// print_r($newdata);die;

   		$insid = $this->insertInToTable(AccountingHead,array($newdata));
   		return $insid;
	  }catch (Exception $e) {

	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	  }

	  return false;
		  
	}

	 public function EditHeadDetail($data){

	 	$chkdup = $this->checkduplicate(AccountingHead,$this->getData['head_id'],'head_id','head_code',$this->getData['head_code']);
        
        if($chkdup['count']>0)	 
        	return array('status'=>false,'message'=>$chkdup['fieldname']." ".$chkdup['fieldval']." Already exists in database");

	 	$modified_byDetails = commonfunction::modifiedByDetails($this->Useconfig['level_id']);
	 	$newdata = array_merge($modified_byDetails,$data);
	 	// print_r($newdata);die;

	 	$where = 'head_id ='.$data['head_id'];

	 	$update = $this->UpdateInToTable(AccountingHead,array($newdata),$where);
	 	return ($update) ? array('status'=>true,'message'=>'Accounting Head edited Successfully') : array('status'=>false,'message'=>'Some Internal Error Occured');  

  	}

  public function AccountingClassDetails()
	{	
		// echo "FSD";die;
		$select = $this->_db->select()->from(array('AC'=>AccountingClass),array('AC.class_id','AC.class_name','AC.description','AC.action_type','AC.status'))
			->order('AC.class_name');
		// echo $select->__tostring();die;
		$result = $this->getAdapter()->fetchAll($select);
		return $result;
		// echo "<pre>";	
		// print_r($result);die;
	}

	public function getclassactivitytypes()
	{
		$select = $this->_db->select()->from(array('AC'=>AccountingClass),array('AC.action_type as activity_type_id',new Zend_Db_Expr('CASE when AC.action_type=1 then "Credit" else "Debit" END as activity_type ')));
		// echo $select->__tostring();die;
		$result = $this->getAdapter()->fetchAll($select);
		// return $result;
		echo "<pre>";	
		print_r($result);die;
	}


	public function addClassDetails($data)
	{	
		try{
		
		$formdata = $this->getData;
		$createdbydetails = commonfunction::createdByDetails($this->Useconfig['level_id']);
		$newdata = array_merge($createdbydetails,$formdata);
		// print_r($newdata);die;

   		$insid = $this->insertInToTable(AccountingClass,array($newdata));
   		return $insid;
	  }catch (Exception $e) {

	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	  }

	  return false;
		  
	}

	public function getClassDetails()
	{	
		$class_id = $this->getData['id'];
		$select = $this->_db->select()->from(array('AC'=>AccountingClass),array('AC.class_id','AC.class_name','AC.description','AC.action_type as activity_type'))
			->where("AC.class_id='$class_id'");
		$result = $this->getAdapter()->fetchAll($select);
		return $result;	
	}

	public function EditClassDetails($data){

		$chkdup = $this->checkduplicate(AccountingClass,$this->getData['class_id'],'class_id','class_name',$this->getData['class_name']);
        
        if($chkdup['count']>0)	 
        	return array('status'=>false,'message'=>$chkdup['fieldname']." ".$chkdup['fieldval']." Already exists in database");

		$data['action_type'] = $data['activity_type'];
	 	$modified_byDetails = commonfunction::modifiedByDetails($this->Useconfig['level_id']);
	 	$newdata = array_merge($modified_byDetails,$data);
	 	// print_r($newdata);die;

	 	$where = 'class_id ='.$data['class_id'];

	 	$update = $this->UpdateInToTable(AccountingClass,array($newdata),$where);
	 	return ($update) ? array('status'=>true,'message'=>'Accounting Class edited Successfully') : array('status'=>false,'message'=>'Some Internal Error Occured');  

  	}

  	public function checkduplicate($table,$id,$field_name1,$field_name2,$field_val)
  	{
  		$select = $this->_db->select()->from(array('TB'=>$table),array(new Zend_Db_Expr("COUNT(TB.$field_name1) as count") ) )
  		->where("TB.$field_name1 !='$id' AND TB.$field_name2='$field_val'");

  		// echo $select->__tostring(); die;
  		$result = $this->getAdapter()->fetchAll($select);
  		return array( 'count'=> $result[0]['count'],'fieldname'=>$field_name2,'fieldval'=>$field_val) ;

  	}


  	public function AccountingGroupDetails()
	{	
		// echo "FSD";die;
		$select = $this->_db->select()->from(array('AG'=>AccountingGroup),array('AG.group_id','AG.group_name','AG.description',new Zend_Db_Expr('CASE when AG.sub_group_id!=0 then (select AG1.group_name from '.AccountingGroup.' as AG1 where AG1.group_id=AG.sub_group_id) END as sub_group_name'),'AG.class_id','AG.status'))
		->joinleft(array('AC'=>AccountingClass),'AG.class_id=AC.class_id',array('AC.class_name'))
			->order('AG.group_name');
		// echo $select->__tostring();die;
		$result = $this->getAdapter()->fetchAll($select);
		return $result;
		// echo "<pre>";	
		// print_r($result);die;
	}

	public function addGroupDetails($data)
	{	
		try{
		
		$formdata = $this->getData;
		$createdbydetails = commonfunction::createdByDetails($this->Useconfig['level_id']);
		$newdata = array_merge($createdbydetails,$formdata);
		// print_r($newdata);die;

   		$insid = $this->insertInToTable(AccountingGroup,array($newdata));
   		return $insid;
	  }catch (Exception $e) {

	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	  }

	  return false;
		  
	}
	public function getSingleGroupDetails()
	{	
		$group_id = $this->getData['id'];
		$select = $this->_db->select()->from(array('AG'=>AccountingGroup),array('AG.group_id','AG.group_name','AG.description','AG.sub_group_id','AG.class_id'))
			->where("AG.group_id='$group_id'");
		$result = $this->getAdapter()->fetchAll($select);
		return $result;	
	}

	public function EditGroupDetail($data){

		$chkdup = $this->checkduplicate(AccountingGroup,$this->getData['group_id'],'group_id','group_name',$this->getData['group_name']);
        
        if($chkdup['count']>0)	 
        	return array('status'=>false,'message'=>$chkdup['fieldname']." ".$chkdup['fieldval']." Already exists in database");

		 
	 	$modified_byDetails = commonfunction::modifiedByDetails($this->Useconfig['level_id']);
	 	$newdata = array_merge($modified_byDetails,$data);
	 	 

	 	$where = 'group_id ='.$data['group_id'];

	 	$update = $this->UpdateInToTable(AccountingGroup,array($newdata),$where);
	 	return ($update) ? array('status'=>true,'message'=>'Accounting Group edited Successfully') : array('status'=>false,'message'=>'Some Internal Error Occured');  

  	}


  	public function AccountingFunctionDetails()
	{	
		// echo "FSD";die;
		$select = $this->_db->select()->from(array('AF'=>AccountingFunction),array('AF.function_id','AF.description','AF.status'))
			 
			->order('AF.description');
		// echo $select->__tostring();die;
		$result = $this->getAdapter()->fetchAll($select);
		return $result;
		// echo "<pre>";	
		// print_r($result);die;
	}

	public function addFunctionDetails($data)
	{	
		try{
		
		$formdata = $this->getData;
		$createdbydetails = commonfunction::createdByDetails($this->Useconfig['level_id']);
		$newdata = array_merge($createdbydetails,$formdata);
		// print_r($newdata);die;

   		$insid = $this->insertInToTable(AccountingFunction,array($newdata));
   		return $insid;
	  }catch (Exception $e) {

	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	  }

	  return false;
		  
	}

	public function getSingleFunctionDetails()
	{	
		$function_id = $this->getData['id'];
		$select = $this->_db->select()->from(array('AF'=>AccountingFunction),array('AF.function_id','AF.description'))
			->where("AF.function_id='$function_id'");
		$result = $this->getAdapter()->fetchAll($select);
		return $result;	
	}

	public function EditFunctionDetails($data){

		$chkdup = $this->checkduplicate(AccountingFunction,$this->getData['function_id'],'function_id','description',$this->getData['description']);
        
        if($chkdup['count']>0)	 
        	return array('status'=>false,'message'=>$chkdup['fieldname']." ".$chkdup['fieldval']." Already exists in database");

		 
	 	$modified_byDetails = commonfunction::modifiedByDetails($this->Useconfig['level_id']);
	 	$newdata = array_merge($modified_byDetails,$data);
	 	 

	 	$where = 'function_id ='.$data['function_id'];

	 	$update = $this->UpdateInToTable(AccountingFunction,array($newdata),$where);
	 	return ($update) ? array('status'=>true,'message'=>'Accounting Function edited Successfully') : array('status'=>false,'message'=>'Some Internal Error Occured');  

  	}

  	public function AccountingBtwRatesDetails()
  	{
  		$select = $this->_db->select()->from(array('BTW'=>AccountingBtwRates),array('BTW.btwrate_id','BTW.effective_date','BTW.btw_rate','BTW.status'))
  		->joinleft(array('BTWRT'=>AccountingBtwRateTypes),'BTW.btwrate_type = BTWRT.btwrate_type_id',array('CONCAT(BTWRT.btwrate_type_name," rate") as rate_type'))
  		->order('BTW.btwrate_id DESC');
  		$result = $this->getAdapter()->fetchAll($select);
  		return $result;		
  // 		echo "<pre>";	
		// print_r($result);die;
  	}

  	public function addBtwRateDetails($data)
	{	
		try{
		
		$formdata = $this->getData;
		$createdbydetails = commonfunction::createdByDetails($this->Useconfig['level_id']);
		$newdata = array_merge($createdbydetails,$formdata);
		// print_r($newdata);die;

   		$insid = $this->insertInToTable(AccountingBtwRates,array($newdata));
   		return $insid;
	  }catch (Exception $e) {

	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	  }

	  return false;
		  
	}

	public function getSingleBtwrateDetails()
	{	
		$btwrate_id = $this->getData['id'];
		 
		$select = $this->_db->select()->from(array('BTW'=>AccountingBtwRates),array('BTW.btwrate_id','BTW.effective_date','BTW.btw_rate','BTW.status','BTW.btwrate_type'))
  		
			->where("BTW.btwrate_id='$btwrate_id'");
		$result = $this->getAdapter()->fetchAll($select);
		return $result;	
	}

	public function EditBtwrateDetail($data){

		 
	 	$modified_byDetails = commonfunction::modifiedByDetails($this->Useconfig['level_id']);
	 	$newdata = array_merge($modified_byDetails,$data);
	 	 

	 	$where = 'btwrate_id ='.$data['btwrate_id'];

	 	$update = $this->UpdateInToTable(AccountingBtwRates,array($newdata),$where);
	 	return ($update) ? array('status'=>true,'message'=>'Btw Rates edited Successfully') : array('status'=>false,'message'=>'Some Internal Error Occured');  

  	}

	public function getInvoiceHeads()
	{	ini_set('display_errors',1);
		$select = $this->_db->select()->from(array('AH'=>AccountingHead),array('AH.head_code','AH.head_id'))
			->joinleft(array('AF'=>AccountingFunction),'AF.function_id=AH.function_id',array('AF.description'))
			->where('AH.head_id IN(2,3,5,6,17,47)');
			// echo $select->__tostring();die;
		$result = $this->getAdapter()->fetchAll($select);
		return $result; 	
	}


 public function getInvArchiveData()
 {	

 	$InvHeads = $this->getInvoiceHeads();
 	 
 	 foreach ($InvHeads as $key => $value) {
 	 	$InvHeads[$key]['invoices'] = $this->getHeadInvoices($value['head_id']);
 	 }
 	 return $InvHeads;
 }

 public function getHeadInvoices($head_id)
 {	//return $head_id;
 	$select = $this->_db->select()->from(array('AI'=>AccountingInvoice),array('AI.created_date','AI.invoice_date','AI.ledger_head','AI.invoice_id','AI.invoice_number'))
 		->joinleft(array('ASP'=>AccountingSuppliers),'ASP.supplier_id=AI.supplier_id',array('ASP.company_name as supplier'))
 		->joinleft(array('AUD'=>USERS_DETAILS),'AUD.user_id=AI.customer_id',array('AUD.company_name as customer'))
 		->where("AI.ledger_head='$head_id'")
 		->order('AI.invoice_date desc');
 		// return $select->__tostring();	
 		$result = $this->getAdapter()->fetchAll($select);
 	$data = array();
 	foreach ($result as $key => $value) {

 			$year = date('Y', strtotime($value['invoice_date']));
 			$month = date('F', strtotime($value['invoice_date']));

 			$data[$year][$month][] = array($value['ledger_head'],$value['supplier'],$value['customer'],date( 'Y-m-d',strtotime($value['invoice_date'])), $value['invoice_id'],$value['invoice_number']);
 		}	
 	return $data;
 	# code...
 }

 public function getAccountingSuppliers()
 {
 	
 	$where = "1";
 	

 	if(isset($this->getData['company_name'])){
 		
 		if(preg_match('/[a-zA-Z0-9]/', $this->getData['company_name']) == false)
 			{
 				global $objSession;$objSession->errorMsg = "Search Content Should be AlphaNumeric only";
 			}
 		else $where.= " AND ASP.company_name LIKE '{$this->getData['company_name']}%'";	
 	}

 	$selectall = $this->_db->select()->from(array('ASP'=>AccountingSuppliers),array(new Zend_Db_Expr('COUNT(ASP.supplier_id) as totalsuppliers')))
 		->where($where);
 		// echo $selectall->__tostring();die;
 	$total = $this->getAdapter()->fetchAll($selectall);
 		
 	$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'ASP.company_name','ASC');
 	// print_r($OrderLimit);die;
 	$selectlimit = $this->_db->select()->from(array('ASP'=>AccountingSuppliers),array('ASP.supplier_id','ASP.company_name','ASP.contact_name','ASP.postalcode','ASP.city','CONCAT(ASP.street," ",ASP.street_no," ",ASP.address) as supplier_address','ASP.phoneno','ASP.email','ASP.status','ASP.bank_account','ASP.account_holder','ASP.btw_no','ASP.kvk_no','ASP.creditcard_no','ASP.credit_period'))
 		->joinleft(array('AC'=>COUNTRIES),'ASP.country_id=AC.country_id',array('AC.country_name'))
 		->where($where)
 		->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
 		->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
 		// echo $selectlimit->__tostring();die;
 		$result = $this->getAdapter()->fetchAll($selectlimit);

 		return array('total'=>$total[0]['totalsuppliers'],'data'=>$result);
 		// echo "<pre>";
 		// print_r($result);die;
 }

 public function getuniqsuppliers()
 {
 	$select = $this->_db->select()->from(array('ASP'=>AccountingSuppliers),array('DISTINCT(ASP.company_name)'))->order('ASP.company_name ASC');
 	// echo $select->__tostring();die;
 	return $this->getAdapter()->fetchAll($select);
 }


 public function addSupplierDetails($data)
	{	
		try{
		
		$formdata = $this->getData;
		$createdbydetails = commonfunction::createdByDetails($this->Useconfig['level_id']);
		$newdata = array_merge($createdbydetails,$formdata);
		// print_r($newdata);die;

   		$insid = $this->insertInToTable(AccountingSuppliers,array($newdata));
   		return $insid;
	  }catch (Exception $e) {

	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	  }

	  return false;
		  
	}

	public function getSingleSupplierDetails()
	{	
		$supplier_id = Zend_Encript_Encription:: decode($this->getData['token']);
		  
		$selectlimit = $this->_db->select()->from(array('ASP'=>AccountingSuppliers),array('ASP.supplier_id','ASP.company_name','ASP.contact_name','ASP.country_id','ASP.postalcode','ASP.city','ASP.address','ASP.phoneno','ASP.email','ASP.status','ASP.bank_account','ASP.account_holder','ASP.btw_no','ASP.kvk_no','ASP.creditcard_no','ASP.credit_period'))
 		 
 		->where("ASP.supplier_id='$supplier_id'");
		$result = $this->getAdapter()->fetchAll($selectlimit);
		return $result;	
	}

	public function EditSupplierDetail($data){

		 
	 	$modified_byDetails = commonfunction::modifiedByDetails($this->Useconfig['level_id']);
	 	$newdata = array_merge($modified_byDetails,$data);
	 	 

	 	$where = 'supplier_id ='.$data['supplier_id'];

	 	$update = $this->UpdateInToTable(AccountingSuppliers,array($newdata),$where);
	 	return ($update) ? array('status'=>true,'message'=>'Supplier edited Successfully') : array('status'=>false,'message'=>'Some Internal Error Occured');  

  	}


  	public function getaccountheadlist()
  	{
  		$select = $this->_db->select()->from(array('AH'=>AccountingHead),array('AH.head_id','CONCAT(AH.head_code," : ",AH.head_description) as description'))
  		->order('AH.head_description asc');
  		$result = $this->getAdapter()->fetchAll($select);
  		return $result;
  	}

  	public function SaveInvoice($data)
  	{	
  		// echo "<pre>"; print_r($data);die;
  		 $insertArr = array('ledger_head'=>$data['ledger_head'],'invoice_number'=>$data['invoice_number'],'invoice_date'=>$data['invoice_date'],'credit_amount'=>$data['credit_amount'],'debit_amount'=>$data['debit_amount'],'invoice_definition'=>$data['invoice_definition'],'supplier_id'=>$data['supplier_id'],'supplier_id'=>$data['supplier_id'],'customer_id'=>$data['customer_id']);
	  	
	  	$createdbydetails = commonfunction::createdByDetails($this->Useconfig['level_id']);	
	  	$newdata = array_merge($createdbydetails,$insertArr);
	  	$invoiceid = $this->insertInToTable(AccountingInvoice,array($newdata));
   		  

	   
	   
	  foreach($data['definition'] as $key=>$details){
	   if($data['definition'][$key]!='' || $data['ledger'][$key]!=''){
	    $dataArr = array('invoice_id'=>$invoiceid,'definition'=>$details,'ledger_id'=>$data['ledger_id'][$key],'btw'=>$data['btw'][$key],'debit'=>$data['debit'][$key],'credit'=>$data['credit'][$key],'booknumber'=>$data['baucharnumber'][$key]);

		    if(isset($this->getData['ledger_invoice_number'])){
		    	$dataArr['ledger_invoice_number'] = $this->getData['ledger_invoice_number'][$key];
		    }

		    if(isset($this->getData['memorial_id'])){
		    	$dataArr['memorial_id'] = $this->getData['memorial_id'][$key];
		    }

			$this->insertInToTable(AccountingInvoiceDetails,array($dataArr));
		}
	  }

	  if($invoiceid)
	  	$resp = array('status'=>1,'message'=>'Invoice Added Successfully');
	  	else $resp = array('status'=>0,'message'=>'Some Internal error occured');

	  	return $resp;
  	}


  	public function getSingleInvoiceDetails()
  	{
  		$select  = $this->_db->select()
		->from(array('AI'=>AccountingInvoice),array('*'))
		->where("invoice_id='".$this->getData['invoice_id']."'");
		 $result = $this->getAdapter()->fetchRow($select);
		 $select  = $this->_db->select()
		 ->from(array('AID'=>AccountingInvoiceDetails),array('*'))
		 ->where("invoice_id='".$this->getData['invoice_id']."'");
		

		 $invoicedetails = $this->getAdapter()->fetchAll($select);

			foreach ($invoicedetails as $key => $value) {
						foreach ($value as $k => $val) {
								if($k=='invoice_id') continue;

								$res[$k][] = $val;

							}	
					}		

		 return  array_merge($result,$res);
  	}



  	public function EditInvoice($data)
  	{	
  		 $insertArr = array('ledger_head'=>$data['ledger_head'],'invoice_number'=>$data['invoice_number'],'invoice_date'=>$data['invoice_date'],'credit_amount'=>$data['credit_amount'],'debit_amount'=>$data['debit_amount'],'invoice_definition'=>$data['invoice_definition'],'supplier_id'=>$data['supplier_id'],'supplier_id'=>$data['supplier_id'],'customer_id'=>$data['customer_id']);
	  	
	  	$createdbydetails = commonfunction::modifiedByDetails($this->Useconfig['level_id']);	
	  	$newdata = array_merge($createdbydetails,$insertArr);
   		$invoiceid = $data['invoice_id'];

	  	$where = 'invoice_id="'.$invoiceid.'"';

	  	$res = $this->UpdateInToTable(AccountingInvoice,array($newdata),$where);

	  	 $this->_db->delete(AccountingInvoiceDetails,"invoice_id='$invoiceid'");
	   // print_r($data);die;
	  foreach($data['definition'] as $key=>$details){
	   if($data['definition'][$key]!='' || $data['ledger'][$key]!=''){
	    $dataArr = array('invoice_id'=>$invoiceid,'definition'=>$details,'ledger_id'=>$data['ledger_id'][$key],'btw'=>$data['btw'][$key],'debit'=>$data['debit'][$key],'credit'=>$data['credit'][$key],'booknumber'=>$data['baucharnumber'][$key]);
	    
		    if(isset($this->getData['ledger_invoice_number'])){
		    	$dataArr['ledger_invoice_number'] = $this->getData['ledger_invoice_number'][$key];
		    }

		    if(isset($this->getData['memorial_id'])){
		    	$dataArr['memorial_id'] = $this->getData['memorial_id'][$key];
		    }

		$this->insertInToTable(AccountingInvoiceDetails,array($dataArr));
		}
	  }

	  if($invoiceid)
	  	$resp = array('status'=>1,'message'=>'Invoice Edited Successfully');
	  	else $resp = array('status'=>0,'message'=>'Some Internal error occured');

	  	return $resp;
  	}







}



