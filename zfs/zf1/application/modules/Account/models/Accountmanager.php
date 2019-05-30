<?php



class Account_Model_Accountmanager extends Zend_Custom

{

	 /**

	 * administratorList() method, fetch administrator details.

	 * @access	public

	 * @param	Null

	 * @return	array

	 */

    public $mailOBj = NULL;

	public function administratorList($data=array()){

		

		$filterCustomerCompany=(!empty($data['company']))?"UD.company_name LIKE '" . $data['company'] . "%'" :'1';

		

		$filterCustomerName = (!empty($data['username']))? "UT.username LIKE '" . $data['username'] . "%'" : '1';

		

		$filterPostcode = (!empty($data['postcode']))? " UD.postalcode LIKE '" . $data['postcode'] . "%'" : '1';

			

		$select = $this->_db->select()

					->from(array('UT'=>USERS), array('user_id','username','password_text','user_status'))

					->joininner(array('UD'=>USERS_DETAILS), 'UT.user_id=UD.user_id', array('company_name','first_name','last_name','email',''))

					->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id', array('*'))

					->joininner(array('CT'=>COUNTRIES) ,' CT.country_id=UD.country_id',array('country_name'))

					->where('UT.user_id='.$this->Useconfig['user_id'])

					->where('UT.delete_status=?', "0")

					->where($filterCustomerCompany)

					->where($filterCustomerName)

				    ->where($filterPostcode)

					->order('UT.level_id');	//echo $select->__tostring(); //die;

			$result = $this->getAdapter()->fetchAll($select);

			//echo"<pre>";print_r($result);die;

			return $result;

	}

	

	

	/**

	 * depotList() method, fetch depot users details.

	 * @access	public

	 * @param	Null

	 * @return	array

	 */

	public function depotList($data=array()){ 

		try{

			$filterCustomerCompany=(!empty($data['company']))?"UD.company_name LIKE '". $data['company'] . "%'":'1';

			

			$filterCustomerName = (!empty($data['username']))? "UT.username LIKE '". $data['username'] . "%'" : '1';

			

			$filterPostcode = (!empty($data['postcode']))? " UD.postalcode LIKE '". $data['postcode'] . "%'" : '1';

			$filterdepot = (!empty($data['filterdepot']))? " UT.user_id LIKE '". $data['filterdepot'] . "%'" : '1';

			$userId = (!empty($data['user_id']))? " UT.user_id=".$data['user_id'] : '1';

			$lavelClause = '';

			 if($this->Useconfig['level_id']==1){

			     $lavelClause = " AND UD.parent_id=1";

			 }else{

			     $lavelClause = " AND UD.parent_id='".$this->Useconfig['user_id']."'";

			 }  

			

				

			$select = $this->_db->select()

					->from(array('UT'=>USERS), array(ADMIN_ID,'username','password_text','user_status'))

					->joininner(array('UD'=>USERS_DETAILS),'UT.user_id=UD.user_id', array('*'))

					->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id',array('*'))

					->joininner(array('CT'=>COUNTRIES) ,' CT.country_id=UD.country_id',array('country_id','country_name'))

					->where('UT.level_id=4')

					->where("UT.delete_status='0'".$lavelClause)

					->where($filterCustomerCompany)

					->where($filterCustomerName)

				    ->where($filterPostcode)

					->where($filterdepot)

					->where($userId)

					->order('UD.company_name');	//echo $select->__tostring(); die;

			$result = $this->getAdapter()->fetchAll($select);

			return $result;

		}

		catch(Exception $e){

			die('Something is wrong: ' . $e->getMessage());

		}	

	}

	

	

	/**

	 * customerList() method, fetch customers details.

	 * @access	public

	 * @param	Null

	 * @return	array

	 */

	public function customerList($data=array()){

		try{

			$filterCustomerCompany=(!empty($data['company']))?"AT.company_name LIKE '" . $data['company'] . "%'":'1';

			$filterCustomerName = (!empty($data['username']))? "UT.username LIKE '" . $data['username'] . "%'" : '1';

			$filterPostcode = (!empty($data['postcode']))? " AT.postalcode LIKE '" . $data['postcode'] . "%'" : '1';

			$filterdepot = (!empty($data['filterdepot']))? " AT.parent_id LIKE '". $data['filterdepot'] . "%'" : '1';

			$userId = (!empty($data['user_id']))? " UT.user_id=".Zend_Encript_Encription::decode($data['user_id']) : '1';

			

			if($this->Useconfig['level_id']==5){

				$userId = " UT.user_id=".$this->Useconfig['user_id'];

			}



			$levelCalause = $this->LevelClause();

			$OrderLimit = commonfunction::OdrderByAndLimit($data,'AT.company_name','ASC');

			

			$select = $this->_db->select()

					->from(array('UT'=>USERS), array('COUNT(1) AS CNT'))

					->joininner(array('AT'=>USERS_DETAILS), 'UT.user_id=AT.user_id', array('*'))

					->joininner(array('Depot'=>USERS_DETAILS), 'Depot.user_id=AT.parent_id', array('user_depot'=>'Depot.'.COMPANY_NAME))

					->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id',array('*'))

					->joininner(array('CT'=>COUNTRIES) ,' CT.country_id=AT.country_id',array('country_name'))

					->where('UT.level_id=5')

					->where("UT.delete_status='0'".$levelCalause)

					->where($filterCustomerCompany)

					->where($filterCustomerName)

				    ->where($filterPostcode)

					->where($filterdepot)

					->where($userId);

			$count = $this->getAdapter()->fetchRow($select);

						

			$select = $this->_db->select()

					->from(array('UT'=>USERS), array('user_id','username','password_text','user_status'))

					->joininner(array('AT'=>USERS_DETAILS), 'UT.user_id=AT.user_id', array('*'))

					->joininner(array('Depot'=>USERS_DETAILS), 'Depot.user_id=AT.parent_id', array('user_depot'=>'Depot.'.COMPANY_NAME))

					->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id',array('*'))

					->joininner(array('CT'=>COUNTRIES) ,' CT.country_id=AT.country_id',array('country_name'))

					->where('UT.level_id=5')

					->where("UT.delete_status='0'".$levelCalause)

					->where($filterCustomerCompany)

					->where($filterCustomerName)

				    ->where($filterPostcode)

					->where($filterdepot)

					->where($userId)
					->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
					->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);

			$result = $this->getAdapter()->fetchAll($select);

						//echo "<pre>"; print_r($result); die;	

			return array('Total'=>$count['CNT'],'Records'=>$result);

		

		}

		catch(Exception $e){

			//die('Something is wrong: ' . $e->getMessage());

		}

	}

	

	

	public function customeroperatorList($data){

		try{

			$filterCustomerCompany=(!empty($data['company']))?"UD.company_name LIKE '" . $data['company'] . "%'":'1';

			$filterCustomerName = (!empty($data['username']))? "UT.username LIKE '" . $data['username'] . "%'" : '1';

			$filterPostcode = (!empty($data['postcode']))? " UD.postalcode LIKE '" . $data['postcode'] . "%'" : '1';

			$userId = (!empty($data['user_id']))? " UT.user_id=".$data['user_id'] : '1';

			

			$where = '';

			switch($this->Useconfig['level_id']){

			   case 4:

			       $where = " AND UD1.parent_id='".$this->Useconfig['user_id']."'";

			   break;

			   case 5:

			      $where = " AND UD.parent_id='".$this->Useconfig['user_id']."'";

			   break;

			   case 6:

			      $where = " AND UD.parent_id='".$this->Useconfig['parent_id']."'";

			   break;

			   case 10:

			      $where = " AND UD.user_id='".$this->Useconfig['user_id']."'";

			   break;

			}

			

			$select = $this->_db->select()

					->from(array('UT'=>USERS), array('user_id','username','password_text','user_status'))

					->joininner(array('UD'=>USERS_DETAILS), 'UT.user_id=UD.user_id', array('*'))

					->joininner(array('UD1'=>USERS_DETAILS), 'UD1.user_id=UD.parent_id', array(''))

					->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id',array('*'))

					->joininner(array('CT'=>COUNTRIES) ,' CT.country_id=UD.country_id',array('country_name'))

					->where('UT.level_id=10'.$where)

					->where('UT.delete_status=?',"0")

					->where($filterCustomerCompany)

					->where($filterCustomerName)

				    ->where($filterPostcode)

					->where($userId)

					->order('UD.company_name');	//echo $select->__tostring(); die;

			$result = $this->getAdapter()->fetchAll($select);

			//echo"<pre>";print_r($result);die;

			return $result;

		}

		catch(Exception $e){

			die('Something is wrong: ' . $e->getMessage());

		}

	

	}

	

	/**

	 * operatorList() method, fetch operators details.

	 * @access	public

	 * @param	Null

	 * @return	array

	 */

	public function operatorList($data=array()){

		try{

			$filterCustomerCompany=(!empty($data['company']))?"UD.company_name LIKE '" . $data['company'] . "%'":'1';

			$filterCustomerName = (!empty($data['username']))? "UT.username LIKE '" . $data['username'] . "%'" : '1';

			$filterPostcode = (!empty($data['postcode']))? " UD.postalcode LIKE '" . $data['postcode'] . "%'" : '1';

			$userId = (!empty($data['user_id']))? " UT.user_id=".$data['user_id'] : '1';

			

			$levelClause = '';

			if($this->Useconfig['level_id']==1){

			    $levelClause  = " AND UT.level_id=6";

			}elseif($this->Useconfig['level_id']==4){

			 $levelClause  = " AND UD.parent_id= '".$this->Useconfig['user_id']."'";

			}else{

			  $levelClause  = " AND UD.user_id= '".$this->Useconfig['user_id']."'";

			}		

			$select = $this->_db->select()

					->from(array('UT'=>USERS), array('user_id','username','password_text','user_status'))

					->joininner(array('UD'=>USERS_DETAILS), 'UT.user_id=UD.user_id', array('*'))

					->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id',array('*'))

					->joininner(array('CT'=>COUNTRIES) ,' CT.country_id=UD.country_id',array('country_name'))

					->where('UT.level_id=6')

					->where("UT.delete_status='0'".$levelClause)

					->where($filterCustomerCompany)

					->where($filterCustomerName)

				    ->where($filterPostcode)

					->where($userId)

					->order('UD.company_name');	//echo $select->__tostring(); die;

			$result = $this->getAdapter()->fetchAll($select);

			//echo"<pre>";print_r($result);die;

			return $result;

		}

		catch(Exception $e){

			die('Something is wrong: ' . $e->getMessage());

		}

	}

	

	/**

	 * hubuserList() method, fetch HubUsers details.

	 * @access	public

	 * @param	Null

	 * @return	array

	 */

	public function hubuserList($data=array()){

		try{

			$filterCustomerCompany=(!empty($data['company']))?"UD.company_name LIKE '" . $data['company'] . "%'":'1';

			$filterCustomerName = (!empty($data['username']))? "UT.username LIKE '" . $data['username'] . "%'" : '1';

			$filterPostcode = (!empty($data['postcode']))? " UD.postalcode LIKE '" . $data['postcode'] . "%'" : '1';

				

			$select = $this->_db->select()

					->from(array('UT'=>USERS), array('user_id','username','password_text','user_status'))

					->joininner(array('UD'=>USERS_DETAILS), 'UT.user_id=UD.user_id', array('company_name','first_name','last_name','email',''))

					->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id',array('*'))

					->joininner(array('CT'=>COUNTRIES) ,' CT.country_id=UD.country_id',array('country_name'))

					->where('UT.level_id=7')

					->where('UT.delete_status=?',"0")

					->where($filterCustomerCompany)

					->where($filterCustomerName)

				    ->where($filterPostcode)

					->order('UD.company_name');	//echo $select->__tostring(); //die;

			$result = $this->getAdapter()->fetchAll($select);

			//echo"<pre>";print_r($result);die;

			return $result;

		}

		catch(Exception $e){

			die('Something is wrong: ' . $e->getMessage());

		}

	}

	

	/**

	 * hubOperatorList() method, fetch HubOperators details.

	 * @access	public

	 * @param	Null

	 * @return	array

	 */

	public function hubOperatorList($data=array()){

		try{

			

			$filterCustomerCompany=(!empty($data['company']))?"UD.company_name LIKE '" . $data['company'] . "%'":'1';

			$filterCustomerName = (!empty($data['username']))? "UT.username LIKE '" . $data['username'] . "%'" : '1';

			$filterPostcode = (!empty($data['postcode']))? " UD.postalcode LIKE '" . $data['postcode'] . "%'" : '1';

			$userId = (!empty($data['user_id']))? " UT.user_id=".$data['user_id'] : '1';

				

			$select = $this->_db->select()

					->from(array('UT'=>USERS), array('user_id','username','password_text','user_status'))

					->joininner(array('UD'=>USERS_DETAILS), 'UT.user_id=UD.user_id', array('company_name','first_name','last_name','email',''))

					->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id',array('*'))

					->joininner(array('CT'=>COUNTRIES) ,' CT.country_id=UD.country_id',array('country_name'))

					->where('UT.level_id=8')

					->where('UT.delete_status=?',"0")

					->where($filterCustomerCompany)

					->where($filterCustomerName)

				    ->where($filterPostcode)

					->where($userId)

					->order('UD.company_name');	//echo $select->__tostring(); //die;

			$result = $this->getAdapter()->fetchAll($select);

			//echo"<pre>";print_r($result);die;

			return $result;

		

		} 

		catch(Exception $e){

			die('Something is wrong: ' . $e->getMessage());

		}

	}

	

	

	public function addNewUser($data){

		$uploadFiles 	= array('logo','invoice_logo');

	    $uploadedFiles 	= $this->uploadFiles($uploadFiles);

		

		$data['logo']  	= $uploadedFiles['logo'];

		$data['invoice_logo'] = isset($uploadedFiles['invoice_logo'])?$uploadedFiles['invoice_logo']:'';

		$data['password_text']= $data['password'];

		$data['password'] = md5($data['password']);

		$data['create_date']= new Zend_Db_Expr('NOW()');

		$data['create_by'] = $this->Useconfig['user_id'];

		$data['create_ip'] = commonfunction::loggedinIP();

		

		$data['user_id'] = $this->insertInToTable(USERS,array($data));

		if($data['user_id']>0){

			$this->insertInToTable(USERS_DETAILS,array($data));

			$this->insertInToTable(USERS_SETTINGS,array($data));

			if(isset($data['level_id']) && $data['level_id']==4){

			     $this->isBankDetail($data);

			}elseif(isset($data['level_id']) && $data['level_id']==5){

				$this->addEditForwarderGLSNL($data['user_id']);



			}

			$DefaultPrivileges = $this->UserDefaultPrivilege($data['level_id']);

			if(count($DefaultPrivileges)>0){

				$PrivilegeArr = array();

				foreach($DefaultPrivileges as $key=>$levelPrivilege){

					$PrivilegeArr[$key]['user_id'] = $data['user_id'];

					$PrivilegeArr[$key]['module_id'] = $levelPrivilege['module_id'];

				}

				

				$this->insertInToTable(USERS_PRIVILLAGE,$PrivilegeArr);

			}

		   $this->addUpdateUserMail($data['user_id']);

		}

		return $data['user_id'];

	}

	

	

	/**

	 * userNameAvailability() method, find username existance in system for user.

	 * @access	public

	 * @param	$data, array hold user details

	 * @return	integer

	 */

	public function userNameAvailability($data){

			

	   $admin_id = ($data['formaction']==1) ? "user_id!=".$data['token'] : 1;

	    $select = $this->_db->select()

	             ->from(USERS,array('COUNT(1) AS CNT'))

				 ->where("username='".$data['username']."'"); 

	   $result = $this->getAdapter()->fetchRow($select);

	   if ($result['CNT']>=1){

          return 0;

       } 

	   else {

	      return 1;

       }

	}

	

	

	/**

	 * uploadFiles() method, used to upload files.

	 * @access	public

	 * @param	$uploadArr, array hold field name

	 * @return	array

	 */

	public function uploadFiles($uploadArr){

		$fileinfo = NULL;

		        $adapter = new Zend_File_Transfer_Adapter_Http();

		$files = $adapter->getFileInfo();

		for($i=0;$i<=count($uploadArr);$i++){

			foreach($files as $key=>$value){

			    if($key==$uploadArr[$i]){

				    $time = time();

					$fileName = $files[$key]['name'];

					if($fileName!=''){

					   $adapter->addFilter('Rename',array('target' => LOGO_UPLODE_LINK . '/' .$time.'.'.$fileName,'overwrite' => true));

					   $adapter->receive($fileName);

					   //Zend_FPDF_Image_Support::imageValidation(LOGO_UPLODE_LINK . '/' .$time.'.'.$fileName);

					   $fileinfo[$key] = $time.'.'.$fileName;

					}

					unset($files[$key]);

				}

			}

		}

		return $fileinfo;

	}

	

	

	public function getNextInvoiceSeries(){	

	   $select = $this->_db->select()

	             ->from(array('US'=>USERS_SETTINGS),array('MAX(invoice_series) as invoice_series'))

				 ->joininner(array('UD'=>USERS_DETAILS),' UD.user_id=US.user_id',array(''))

				 ->where('UD.parent_id=1');		//echo $select->__tostring(); die;

	   $result = $this->getAdapter()->fetchRow($select);

	   //print_r($result['invoice_series']+1);die;

	   return $result['invoice_series']+1;

	}

	

	

	public function editUser($data=array()){

	

		$uploadFiles 	= array('logo','invoice_logo');

	    $uploadedFiles 	= $this->uploadFiles($uploadFiles);

		unset($data['logo']);

		unset($data['invoice_logo']);

		if(isset($uploadedFiles['logo']) && $uploadedFiles['logo']!=''){

			$data['logo']  	= $uploadedFiles['logo'];

		}

		if(isset($uploadedFiles['invoice_logo']) && $uploadedFiles['invoice_logo']!=''){

			$data['invoice_logo']  	= $uploadedFiles['invoice_logo'];

		}

		$data['update_date']= new Zend_Db_Expr('NOW()');

		$data['update_by']	= $this->Useconfig['user_id'];	// update by session loginid

		$data['update_ip']	= commonfunction::loggedinIP();

		$userId = Zend_Encript_Encription::decode($data['token']);

		if(isset($data['level_id']) && $data['level_id']==4 && isset($data['token'])){

		$data['user_id'] = $userId;

		  $this->isBankDetail($data);

		}

		if(isset($data['level_id']) && $data['level_id']==5 && isset($data['token'])){

				$this->addEditForwarderGLSNL($userId);

				$update_Notify = $this->emailDetail($userId);

		 		if($update_Notify['update_notify'] == 1){

				      $this->addUpdateUserMail($userId);

				}

		}else{

				$this->addUpdateUserMail($userId);

		}

		$this->UpdateInToTable(USERS,array($data),'user_id='.$userId);

		$this->UpdateInToTable(USERS_DETAILS,array($data),'user_id='.$userId);

		$this->UpdateInToTable(USERS_SETTINGS,array($data),'user_id='.$userId);

		return true;

		//echo"<pre>";print_r($data);die;

	}

	

	

	public function driverlist($data=array()){

		$userId = (!empty($data['driver_id'])) ? " DDT.driver_id=".$data['driver_id'] : '1';

		$accesslevel = $this->LevelAsDepots();	

		$select = $this->_db->select()

	            ->from(array('DDT'=>DRIVER_DETAIL_TABLE),array('*'))
				//->joininner(array('UD'=>USERS),'UD.user_id=DDT.user_id',array(''))
				 ->joininner(array('AT'=>USERS_DETAILS),'AT.user_id=DDT.parent_id',array('AT.company_name'))
				 ->where("DDT.delete_status='0'")
				 ->where('DDT.level_id=9'.$accesslevel)

				 ->where($userId);		//echo $select->__tostring(); die;

	   $result = $this->getAdapter()->fetchAll($select);

	   

	   return $result;

	}

	

	public function addnewdriver($data=array()){	//echo"<pre>";print_r($data);die;

	  // $data['license_issue_date'] = date('Y:m:d',strtotime($data['license_issue_date']));

	  // $data['license_expiry_date'] = date('Y:m:d',strtotime($data['license_expiry_date']));

	  

	   $data['create_date'] = new Zend_Db_Expr('NOW()');

	   $data['create_ip']	= commonfunction::loggedinIP();

		

	   $data['password_text'] = $data['password'];

	   $data['password'] = md5($data['password']);

	   $data['user_id'] = $this->insertInToTable(USERS,array($data));

	   if(!empty($data['user_id'])){

	   	 $this->insertInToTable(USERS_DETAILS,array($data));

	     $this->insertInToTable(DRIVER_DETAIL_TABLE,array($data));

		 

		 return true;

	   }

	   return false;

	}

	

	public function editdriver($data=array()){

		

		$data['update_date']= new Zend_Db_Expr('NOW()');

		$data['update_by']	= $this->Useconfig['user_id'];	

		$data['update_ip']	= commonfunction::loggedinIP();

		

		$DriverId = Zend_Encript_Encription:: decode($data['token']);

		$this->UpdateInToTable(DRIVER_DETAIL_TABLE,array($data),'driver_id='.$DriverId);

		$this->UpdateInToTable(USERS_DETAILS,array($data),'user_id='.$data['user_id']);

		

		if(isset($data['user_name'])){

			$this->UpdateInToTable(USERS,array($data),'user_id='.$data['user_id']);

		}

		return true;

	}

	

	public function changepassword($data=array()){

		global $objSession;

		$data['token'] = Zend_Encript_Encription::decode($data['token']);

		$data['update_date']= new Zend_Db_Expr('NOW()');

		$data['update_by']	= $this->Useconfig['user_id'];	

		$data['update_ip']	= commonfunction::loggedinIP();

		$select = $this->_db->select()

	             		->from(USERS,array('*'))

						->where('user_id='.$data['token']);

	    $record = $this->getAdapter()->fetchRow($select);

		//print_r($record); die;

		if($record['password'] == md5($data['oldpassword'])){

			if($data['password'] == $data['retypepass']){

				$data['password_text'] = $data['password'];

				$data['password'] = md5($data['password']);

				$this->UpdateInToTable(USERS,array($data),'user_id='.$data['token']);

				$this->UpdateInToTable(USERS_DETAILS,array($data),'user_id='.$data['token']);

				$objSession->successMsg = "Password updated successfully!!";

				return true;

			}else{

				$objSession->errorMsg = "Password and confirm password not match !";

				return false;

			}

		}else{

			$objSession->errorMsg = "Password and old password not match !";

			return false;

		}

	}

	

	public function deleteUser($data){ 

		

		$data['delete_date']= new Zend_Db_Expr('NOW()');

		$data['delete_by']	= $this->Useconfig['user_id'];	

		$data['delete_ip']	= commonfunction::loggedinIP();

		$data['delete_status'] = '1';

		

		$this->UpdateInToTable(USERS,array($data),'user_id='.$data['user_id']);

		$this->UpdateInToTable(USERS_DETAILS,array($data),'user_id='.$data['user_id']);

		

		if(isset($data['driver_id'])){

			$this->UpdateInToTable(DRIVER_DETAIL_TABLE,array($data),'driver_id='.$data['driver_id']);

		}

		return true;

	}

	

	//13th jan 2017

	public function UserDetails($data=array()){	//echo"<pre>";print_r($data);die;

		try{

			if($data['user_id']>0){

				$userId = " UT.user_id=".$data['user_id'];

					

				$select = $this->_db->select()

						->from(array('UT'=>USERS), array('user_id','username','password_text','user_status'))

						->joininner(array('UD'=>USERS_DETAILS), 'UT.user_id=UD.user_id', array('*'))

						->joininner(array('US'=>USERS_SETTINGS), 'UT.user_id=US.user_id',array('*'))
						
						->joininner(array('CT'=>COUNTRIES) ,' CT.country_id=UD.country_id',array('country_name'))
						->joinleft(array('IS'=>INVOICE_SETTING) ,'IS.user_id=UT.user_id',array('*'))
						->where('UT.delete_status=?',"0")

						->where($userId)

						->order('UD.company_name');

						//echo $select->__tostring(); die;

				$result = $this->getAdapter()->fetchRow($select);

				return $result;

			}

			else{return array();}

		

		}

		catch(Exception $e){

			die('Something is wrong: ' . $e->getMessage());

		}

	}

	

	public function GetDepotSettings($data=array()){

		try{

			if($data['user_id']>0){

				

				$where = " UD.user_id=".$data['user_id'];

					

				$select = $this->_db->select()

						->from(array('UD'=>USERS_DETAILS), array('*'))

						->joininner(array('US'=>USERS_SETTINGS),'UD.user_id=US.user_id',array('invoice_type','payment_days','is_hub'))

						->joinleft(array('IST'=>INVOICE_SETTING),'UD.user_id=IST.user_id', array('*'))

						->where($where);

						//echo $select->__tostring(); die;

				$result = $this->getAdapter()->fetchRow($select);

				return $result;

			}

			else{return array();}

		}

		catch(Exception $e){

			die('Something is wrong: ' . $e->getMessage());

		}

	}

	

	public function UpdateUserSetting($data=array()){

		

		$this->UpdateInToTable(USERS_SETTINGS,array($data),'user_id='.Zend_Encript_Encription::decode($data['token']));

		return true;

	}

	

	public function UpdateInvoiceSetting($data=array()){

		

		$data['user_id']=Zend_Encript_Encription:: decode($data['token']);

		$resultData = $this->GetDepotSettings($data);

		

		if(!empty($resultData['invoicesetting_id'])){



			$data['updated_date']= new Zend_Db_Expr('NOW()');

			$data['updated_by']	 = $this->Useconfig['user_id'];

			$data['updated_ip']	 = commonfunction::loggedinIP();	

			

		$result =	$this->UpdateInToTable(INVOICE_SETTING,array($data),'invoicesetting_id='.$resultData['invoicesetting_id']);



		}

		else{

			$data['created_by']	 = $this->Useconfig['user_id'];

			$data['created_ip']	 = commonfunction::loggedinIP();

			

		$result =	$this->insertInToTable(INVOICE_SETTING,array($data));

		}

		return true;

	}

	

	

	public function getUserCcBccSetting($data=array()){

		try{

			if($data['user_id']>0){

				

				$where = " UE.user_id=".$data['user_id'];

					

				$select = $this->_db->select()

						->from(array('UE'=>CC_BCC_EMAIL), array('*'))

						->where($where);

						//echo $select->__tostring(); die;

				$result = $this->getAdapter()->fetchAll($select);

				return $result;

			}

			else{return array();}

		}

		catch(Exception $e){

			die('Something is wrong: ' . $e->getMessage());

		}

	}

	

	

	public function UpdateUserCcBccSetting($data=array()){

		

		$UserId = Zend_Encript_Encription:: decode($data['token']);

		

		$this->_db->delete(CC_BCC_EMAIL,"user_id=".$UserId);

		

		if(!empty($data['cc_email'])){

			

			$CCemailArr = commonfunction::explode_string($data['cc_email'],PHP_EOL);	

			

			$CCDataArr['user_id'] = $UserId;

			$CCDataArr['type'] = '1'; 

			

			foreach($CCemailArr as $email){

				$CCDataArr['email'] = $email;

				$this->insertInToTable(CC_BCC_EMAIL,array($CCDataArr));

			}

		}

		

		if(!empty($data['bcc_email'])){

		

			$BCCemailArr = commonfunction::explode_string($data['bcc_email'],PHP_EOL);

			

			$BCCDataArr['user_id'] 	= $UserId;

			$BCCDataArr['type'] 	= '2';

			

			foreach($BCCemailArr as $bccemail){

			

				$BCCDataArr['email'] = $bccemail;

				$this->insertInToTable(CC_BCC_EMAIL,array($BCCDataArr));

			}

		}

		return true;

	}

	

	

	public function getSchedulePickupOfUser($user_id){

	   $select = $this->_db->select()

	           ->from(array('UD'=>USERS_DETAILS),array('uname'=>'company_name','ustreet1'=>'address1','ustreet2'=>'address2','upostalcode'=>'postalcode','ucity'=>'city','uphoneno'=>'phoneno'))

				 ->joinleft(array('SPT'=>USERS_SCHEDULE_PICKUP),'SPT.'.ADMIN_ID .'= UD.'.ADMIN_ID,array('*'))

				  ->joinleft(array('CT'=>COUNTRIES),'CT.'.COUNTRY_ID.'='.'UD.'.COUNTRY_ID,array('ucountry'=>'country_name'))

				 ->where('UD.user_id=?',$user_id); 

				 //echo $select->__tostring();die;

	   $result = $this->getAdapter()->fetchRow($select);

	   return $result;

	}

	

	

	public function addschedulepickup($data=array()){

		

		$recordArr = $this->getSchedulePickupOfUser($data['user_id']);

		

		if(!empty($recordArr['id'])){

			$data['updated_date']	= new Zend_Db_Expr('NOW()');

			$data['updated_by']	 	= $this->Useconfig['user_id'];

			$data['updated_ip']	 	= commonfunction::loggedinIP();	

			

			$this->UpdateInToTable(USERS_SCHEDULE_PICKUP,array($data),'id='.$recordArr['id']);

		}

		else{

			$data['created_by']	 = $this->Useconfig['user_id'];

			$data['created_ip']	 = commonfunction::loggedinIP();

			

			$this->insertInToTable(USERS_SCHEDULE_PICKUP,array($data));

		}

		return true;

	}

	

	

	public function UserDefaultPrivilege($levelId=NULL){

		try{

			if($levelId>3){

				$Where = " DPT.level_id=".$levelId;

					

				$select = $this->_db->select()

						->from(array('DPT'=>DEFAULT_PRIVILLAGE), array('*'))

						->where($Where);

						//echo $select->__tostring(); die;

				$result = $this->getAdapter()->fetchAll($select);

				return $result;

			}

			else{return array();}

		}

		catch(Exception $e){

			die('Something is wrong: ' . $e->getMessage());

		}

	}

	

	

	public function GetUserLevel($userId){

		try{

	    $select = $this->_db->select()

                ->from(array('UD'=>USERS_DETAILS), array('UD.level_id'))

				->joininner(array('UL'=>USERS_LEVEL), 'UL.level_id=UD.level_id', array('levelName'=>'UL.name'))

				->where("UD.user_id=".$userId);//print_r($select->__toString());die;

       }catch(Exception $e){

	     echo $e->getMessage();die;

	   }

	   $userdetails =  $this->getAdapter()->fetchRow($select);

	   return $userdetails;

	}

	

	

	// get warehouse company List from api

	public function GetwmsCompanyList(){

	

		$apiArr['username'] = 'administrator' ;

		$apiArr['password'] = 'Simethinf';

		$apiArr['action']   = 'companylist';

	

		$curl_response = $this->Wmscurl_method($apiArr);

		$DataArr = json_decode($curl_response);

		$CompanyArr = array();

		$companyList = array();

		if((isset($DataArr->message)) && (count($DataArr->message)>0)){

			foreach($DataArr->message as $key=>$value){

				$CompanyArr[$key]['comp_id'] 	= $value->comp_id;

				$CompanyArr[$key]['comp_name'] 	= $value->comp_name;

			}

			

			$companyList = commonfunction::scalarToAssociative($CompanyArr,array('comp_id','comp_name'));

		}

		return $companyList;

	}

	

	public function Wmscurl_method($data)

	{	

		

		$url = 'http://warehouse.dpost.be/api/warehouse/apirequest/requestData';

			

		//Prepare data for posting. That is, urlencode data 

		$post_str = '';

		foreach($data as $key=>$val) {

			if(is_array($val)){

			$val2='';

			 	foreach($val as $key1=>$val1){

					$val2.=$key1.'='.$val1.',';

					}

					$val2 = substr($val2, 0, -1);

					$post_str .= $key.'='.urlencode($val2).'&';

			}

			else{

				$post_str .= $key.'='.urlencode($val).'&';

			}

		}

		

		$post_str = substr($post_str, 0, -1);

		

		//Initialize cURL and connect to the remote URL

		$ch = curl_init();

		//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");

		curl_setopt($ch, CURLOPT_URL, $url);

		

		//Instruct cURL to do a regular HTTP POST

		curl_setopt($ch, CURLOPT_POST, TRUE);

		//Specify the data which is to be posted

		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);

		//Tell curl_exec to return the response output as a string

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// Execute the cURL session

		$response = curl_exec($ch );

		//Close cURL session and file

		curl_close($ch );

		

		return $response;

	}			

	public function isBankDetail($data){

	   $select = $this->_db->select()

	             ->from(INVOICE_BANK_DETAIL,array('COUNT(1) as CNT'))

				 ->where('user_id='.$data['user_id']);

				 //print_r($select->__toString());die;

	   $record = $this->getAdapter()->fetchRow($select);

	   if($record['CNT']>0){

		 $result = $this->UpdateInToTable(INVOICE_BANK_DETAIL,array($data),'user_id='.$data['user_id']);

		}else{

		 $result = $this->insertInToTable(INVOICE_BANK_DETAIL,array($data));

			}

	   return $result;

	}

	

	public function sendAccountEmail()

	{

	

	return '';

	}

	

	

	public function getInvoiceBankDetail($userId){

		   $record = array();

		   $select = $this->_db->select()

	             ->from(INVOICE_BANK_DETAIL,array('*'))

				 ->where('user_id='.$userId['user_id']);

				 //print_r($select->__toString());die;

	   $record = $this->getAdapter()->fetchRow($select);

	   return $record;

	}

	

	public function emailDetail($userid){

		try{				   

			   $select = $this->_db->select()

								->from(array('UR'=>USERS),array('UR.password_text','UR.username'))

							  ->joininner(array('AT'=>USERS_DETAILS),"UR.user_id=AT.user_id",array("CONCAT(AT.first_name,' ',AT.middle_name,' ',AT.last_name) AS user_name",'AT.email','AT.user_id','AT.country_id','AT.parent_id','AT.company_name','AT.user_id',"CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name) AS customer_address"))

							  ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=AT.user_id",array('logo','update_notify'))

							  ->joininner(array('CT'=>COUNTRIES),'AT.country_id=CT.country_id',array(''))

							  ->where('UR.user_id='.$userid);

		  $record = $this->getAdapter()->fetchRow($select);

		  return $record;

			}catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

	}	

	 

	 public function addUpdateUserMail($userid){	

	  try{

			  $Template = array();

			  $this->mailOBj = new Zend_Custom_MailManager();

			  $record = $this->emailDetail($userid);

			  $returnArray = array($record['user_name'],nl2br(str_replace('^',"\n",str_replace('^^','^',$record['customer_address']))),$record['country_id'],$record['username'],$record['password_text'],BASE_URL,$record['email'],BASE_URL,'',$record['logo']);

			 $this->mailOBj->emailData['Dynamic'] = $returnArray;

			 $this->mailOBj->emailData[ADMIN_ID] = $record['user_id'];

			 $this->mailOBj->emailData[COUNTRY_ID]  = $record['country_id'];

			 $this->mailOBj->emailData['notification_id']  = 1;	 

			 $senderDetail = $this->emailDetail($record['parent_id']);

			 $this->mailOBj->emailData[PARENT_ID]  = $record['parent_id'];

			 $this->mailOBj->emailData['SenderEmail'] = $senderDetail['email'];

			 $this->mailOBj->emailData['SenderName']    = $senderDetail['user_name'];

			 $this->mailOBj->emailData['ReceiverEmail']  =$record['email'];

			 $this->mailOBj->emailData['ReceiverName']  = $record['user_name'];	

			 $this->mailOBj->emailData['BCCEmail']  = '';

			 $Template = $this->mailOBj->FindTemplate(1);

			 $Field =  $this->getDynamicField(1);

			 $this->mailOBj->emailData['MailBody'] = commonfunction::stringReplace($Field,$this->mailOBj->emailData['Dynamic'],$Template['email_text']);

			 $this->mailOBj->emailData['Subject'] = commonfunction::utf8Decode(commonfunction::strip_slashes(commonfunction::stringReplace($Field,$this->mailOBj->emailData['Dynamic'],$Template['subject'])));

			   $this->mailOBj->Send(); 

		 }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

	  }

	  

		public function addEditForwarderGLSNL($userId)

		{	$dataArray = array();

			$select = $this->_db->select()

						 ->from(GLSNL_SETTINGS,array('COUNT(1) as CNT'))

						 ->where('user_id='.$userId);

						 //print_r($select->__toString());die;

			   $record = $this->getAdapter()->fetchRow($select);

			   //echo "<pre>"; print_r($this->getData); die;

			   if(isset($this->getData['glscustomer_no']) && isset($this->getData['glsdepot_no']) &&$this->getData['glscustomer_no']!='' && $this->getData['glsdepot_no']!='')

			   {

			   	   $this->UpdateInToTable(USERS_SETTINGS,array(array('gls_pickup'=>1)),'user_id='.$userId);

			   

			   }else{

			   			$this->UpdateInToTable(USERS_SETTINGS,array(array('gls_pickup'=>0)),'user_id='.$userId);

			   

			   }

			   $dataArray['customer_number'] = $this->getData['glscustomer_no'];

			   $dataArray['depot_number'] = $this->getData['glsdepot_no'];

			   $dataArray['gls_email_id'] = $this->getData['gls_emailid'];

			   if($record['CNT']>0){

				 	$result = $this->UpdateInToTable(GLSNL_SETTINGS,array($dataArray),'user_id='.$userId);

				}else{

					$dataArray['user_id'] = $userId;

					$dataArray['tracking_start'] = 1;

					$dataArray['tracking_end'] = 99999;

					$dataArray['last_tracking'] = 1;

					$dataArray['tracking_length'] = 5;

					$result = $this->insertInToTable(GLSNL_SETTINGS,array($dataArray));

				}

			   return true;	

		}

	public function getForwardersGLSNLDetail($userId){

		   $record = array();

		   $select = $this->_db->select()

	             ->from(GLSNL_SETTINGS,array('*'))

				 ->where('user_id='.Zend_Encript_Encription::decode($userId['user_id']));

				 //print_r($select->__toString());die;

	   $selectdata = $this->getAdapter()->fetchRow($select);

		 $record['glscustomer_no'] =  isset($selectdata['customer_number'])?$selectdata['customer_number']:'';

		 $record['glsdepot_no'] = isset($selectdata['depot_number'])?$selectdata['depot_number']:'';

		 $record['gls_emailid'] = isset($selectdata['gls_email_id'])?$selectdata['gls_email_id']:'';

		 //echo "<pre>"; print_r($record); die;

	   return $record;

	}
	 public function isRountingAvailable($userId)
		 {    
		  $select = $this->_db->select()
						   ->from(CUSTOMER_ROUTING,array('COUNT(1) as CNT'))
						   ->where("user_id='".$userId."'");
		 $record = $this->getAdapter()->fetchRow($select);
			return  ($record['CNT']>0)?'yes':'No';
		 }

	

	

}



