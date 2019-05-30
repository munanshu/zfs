<?php

class settings_Model_Settings extends Zend_Custom

{

  public function ListForwarders(){

      $select = $this->_db->select()

	  							 ->from(array('FT'=>FORWARDERS),array('*'))

								 ->order("forwarder_id ASC");

	  return $this->getAdapter()->fetchAll($select);							 

   }

   

   public function ListLanguage(){

       $whare = (isset($this->getData['language_id'])) ? 'language_id='.Zend_Encript_Encription:: decode($this->getData['language_id']) : '1';

		   $select = $this->_db->select()

			   ->from(array('LG'=>LANGUAGE),array('*'))

			 ->where($whare)

			 ->order("language_name ASC");

        // echo $select->__tostring(); die;

  	  return $this->getAdapter()->fetchAll($select);							 

   }

   /**

	* get parcelshops created from system

	* Function : paketshop()

	* This function fetch all the parcels shp created in our system

	* Date of creation 30/03/2015

	**/ 	

	 public function packetshop($data){

			$where = 'AND 1';

			if(isset($data['id']) > 0){

			  $where = " AND PP.id='".$data['id']."'";

			}	  

			$select = $this->_db->select()                

				  ->from(array('PP' => PARCELSHOP),array('*'))

				  ->joininner(array('CT'=>COUNTRIES),"CT.country_id=PP.country_id",array('country_name as country'))

				  ->where("PP.is_delete ='0'".$where);

		   // echo $select->__tostring(); die;

		   $result = $this->getAdapter()->fetchAll($select); 

		    return $result;

	}

	/**

	 * Get Continent List Array

	 * Function : continentArray()

	 * return array of continent id , name

	 * */

	public function continentArray(){

		$data = array(0=>'Select Continent');

		foreach($this->getContinentList() as $value){

			$data[$value['continent_id']]= $value['continent_name'];

		}

		return $data;

	}

   

   public function ListServices(){

      $completeArray = array();

	  $servicelist = $this->getAllServices('0');

	  

	  foreach($servicelist as $key => $service){

	   $addserviceList = $this->getAllServices($servicelist[$key][SERVICE_ID]);

	   $servicelist[$key]['child'] = $addserviceList;

		

	  }

	  return $servicelist;

   }

   public function updateService(){  

	try{

	  $where = 'service_id ='.Zend_Encript_Encription:: decode($this->getData['id']);

	  $this->UpdateInToTable(SERVICES,array($this->getData),$where);

	  }catch (Exception $e) {

		 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	  }

 }

 

 public function addService(){ 

 try{

  if(isset($this->getData['mode']) && $this->getData['mode'] == 'addsub'){

     $this->getData['parent_service'] = Zend_Encript_Encription:: decode($this->getData['id']);

  }else{

   $this->getData['parent_service'] = '0';

  }

   $this->insertInToTable(SERVICES,array($this->getData));

  }catch (Exception $e) {

     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

  }

 }

   /**

  * Fetch country detail 

  * Function : countryDetail()

  * fatch country detail with continent and city name

  * */ 

 public function countryDetail(){

		$select = $this->_db->select()

							   ->from(array('CO'=>COUNTRIES),array('*'))

							   ->joinleft(array('CT'=>CONTINENTS), "CT.continent_id=CO.continent_id", array('continent_name'))

							   ->joinleft(array('CC'=>COUNTRYPORT), 'CC.country_id=CO.country_id', array('TotalPort' => 'COUNT(CC.port_id)'))

							   ->group('CO.country_id')

							   ->order('country_name ASC');

	   $results = $this->getAdapter()->fetchAll($select);

	   return $results;

 }

 	/**

	* Function : addcountrydetail()

    * This function is for show all Country list Detail

	* Date of creation 03/08/2016

    **/ 

		 

	public function addcountrydetail($data)	{

		$this->getData['cncode'] = strtoupper($this->getData['cncode']);

		$this->getData['cncode3'] = strtoupper($this->getData['cncode3']);

		$result = $this->insertInToTable(COUNTRIES,array($this->getData));

		return ($result) ? true : false;

	}

 /**

  * Fetch Continent List

  * Function : getContinentList()

  * Fetch forwarder name of forwader id

  * */

  public function getContinentList(){

	   $select = $this->_db->select()->from(CONTINENTS, array('*'))

				->order("continent_name ASC");

	   return  $this->getAdapter()->fetchAll($select);

  }

	/**

	* Update Print Option

	* Function : UpdatePrintOption()

	* Update Print Option of Loged In User

	**/

    public function UpdatePrintOption($printOption){

		try {

		  $update = $this->_db->update(USERS_SETTINGS,array('label_position'=>$printOption['label_position']),'user_id='.$this->Useconfig['user_id']);

		   $logicSeesion = new Zend_Session_Namespace('logicSeesion');

		   $logicSeesion->userconfig['label_position'] = $printOption['label_position'];

		 return ($update) ? true : false;

		} catch (Exception $e) {

		  die('Something is wrong: ' . $e->getMessage());

		}

	  }

  /**

  * Add WeightScallers Deatil

  * Function : AddWeightScalerDetail()

  * This function is to add Weight Scallers

  * Date of creation 03/11/2016

  **/

   public function AddWeightScalerDetail($data){

		if($data['submit']){

			if($data['serial_no']!='' && $data['machine_url']!=''){

			$this->_db->insert(WEIGHT_SCALER_INFO,array('serial_no'=>$data['serial_no'],'machine_url'=>$data['machine_url'],'machine_name'=>$data['machine_name'],'access_by'=>$this->Useconfig['user_id'],'access_date'=>new Zend_Db_Expr('NOW()'),'access_ip'=>commonfunction::loggedinIP()));

		   }

	   }

     return true;

   }

	/**

	* Fetch WeightScallers Deatil

	* Function : getWeightScallers()

	* This function is to fetch Weight Scallers

	* Date of creation 03/11/2016

	**/

  public function getWeightScallers($data,$coditon=false){

   $where = "is_delete='0'";

   if($coditon){

      $where .= "  AND serial_no='".$coditon."'";

   }

    $select = $this->_db->select()

              ->from(WEIGHT_SCALER_INFO,array('*'))

              ->where($where);

     $result = $this->getAdapter()->fetchAll($select); 

     return $result;

   }

   

	/**

	 * Email Notification Type

	 * Function : notificationtype()

	 * Fetch All the notification Type of Loggedin user

	 **/

	public function notificationtype(){     

			if($this->Useconfig['level_id']==4 || $this->Useconfig['level_id']==6){

					$where = "depot_display='1' AND templatecategory_id!=4";

			}elseif($this->Useconfig['level_id']==5){

					$where = "customer_display='1' AND templatecategory_id!=4";
					//$where = 'customer_display=?';

			}else{
					$where = "admin_display='1'";
					//$where = 'admin_display=?';

			}

			$select = $this->_db->select()

									->from(MAIL_NOTIFY_TYPES,array('*'))

									->where($where)

									->where("notification_staus='1'")

									->order('notification_name ASC');

			//echo $select->__tostring();die;

			$result = $this->getAdapter()->fetchAll($select);

		return $result; 

	}

	/**

	* Fetch Email Template

	* Function : fetchemailtemplate()

	* Fetch Email Template according to condition

	**/

	 public function fetchemailtemplate($Data){

  

         $notification_id=(!empty($Data['notification_id']))?$Data['notification_id']:null;

		   $user_id = (!empty($Data['user_id']))?$Data['user_id']:$this->Useconfig['user_id'];

		   $country_id = (!empty($Data['country_id']))?$Data['country_id']:0;

		   if(isset($Data['Notifictype']) && $Data['Notifictype']==2){

			$where = "".ADMIN_ID."='".$user_id."' AND ".PARENT_ID."='".$this->Useconfig['user_id']."' 

				 AND ".COUNTRY_ID."='".$country_id."' AND ".NOTIFICATION_ID." = '".$notification_id."'";

		   }else{

			$where = "".ADMIN_ID."='".$user_id."' AND ".COUNTRY_ID."='".$country_id."' 

				AND ".NOTIFICATION_ID." = '".$notification_id."'";

			}

		   $select = $this->_db->select()

				 ->from(MAIL_MANAGER,array('*'))

				 ->where($where);

		   $result = $this->getAdapter()->fetchAll($select);

		   foreach($result as $emaildata){

			  $emailnotification = $emaildata;

		   }

		   if(!empty($emailnotification)){

			  return $emailnotification;

			  }

	}

	/**

	* Function : getdynamicfield()

	* Find The Notification Dynamic Field 

	**/

	public function getdynamicfield($notification_id){

			$select = $this->_db->select()

										->from(MAIL_DYNAMIC_FIELD,array('*'));

	   //echo $select->__tostring();die;

			$result = $this->getAdapter()->fetchAll($select);

			$notification_id = (!empty($notification_id))?",".$notification_id.",":",".'0'.",";

			$Field = array();

			for($i=0;$i<count($result);$i++){

					$key =  commonfunction::substr_in_array($notification_id,$result[$i]);

					if(!empty($key)){

						$Field[] = $result[$i]['fieldname'];

				   }

			}

			return $Field;

	 }

   

	/**

	* Function : getcustomerlist()

	* Get The list of dynamic field for select box 

	**/

    public function getdyanmicfielddata($data){

	   $dataArray = array(''=>'Select Field');

	   foreach($data as $value){

		$dataArray[$value]= $value;

	   }

	 return $dataArray;

   }

	/**

	* Add or Update Email Template

	* Function : AddUpdateEmailTemplate()

	* Add and Update Email templete for email notification

	**/

	public function UpdateEmailTemplate($Data){

			$user_id = (!empty($Data['user_id']))?$Data['user_id']:$this->Useconfig['user_id'];

			$country_id = (!empty($Data['country_id']))?$Data['country_id']:0;

			$result = $this->fetchemailtemplate($Data);

		if(isset($Data['Notifictype']) && $Data['Notifictype']==2){

				$where = "".ADMIN_ID."='".$user_id."' AND ".PARENT_ID."='".$this->Useconfig['user_id']."' 

									AND ".COUNTRY_ID."='".$country_id."' AND ".NOTIFICATION_ID." = '".$Data['notification_id']."'";

				$AddUpdatearray = array(ADMIN_ID=>$user_id,PARENT_ID=>$this->Useconfig['user_id'],'modify_date'=>date("Y-m-d h:i:s"),

																COUNTRY_ID=>$country_id,MAIL_SUBJECT=>addslashes($Data['subject']),

																MAIL_TEXT=>$Data['FCKeditor1'],NOTIFICATION_ID=>$Data['notification_id']);

		}else{

				$where = "".ADMIN_ID."='".$user_id."' AND ".COUNTRY_ID."='".$country_id."' 

								AND ".NOTIFICATION_ID." = '".$Data['notification_id']."'";

				$AddUpdatearray = array(ADMIN_ID=>$user_id,COUNTRY_ID=>$country_id,MAIL_SUBJECT=>addslashes($Data['subject']),'modify_date'=>new Zend_Db_Expr('NOW()'),

																MAIL_TEXT=>$Data['FCKeditor1'],NOTIFICATION_ID=>$Data['notification_id']);

		}

		if(count($result)>0 && !empty($Data['emailtemplae'])){

			$this->_db->update(MAIL_MANAGER,$AddUpdatearray,$where);

			return 'Email Template Updated Successfully';

		}elseif(count($result)==0 && !empty($Data['emailtemplae'])){

			$this->_db->insert(MAIL_MANAGER,$AddUpdatearray);

			return 'Email Template Saved Successfully';

		}

	}

	

	public function Vehiclelistdetail($data=''){

	   $where = 1;

	   if(isset($data) && $data!=''){

		 $where .= " AND VC.vehicle_id='".Zend_Encript_Encription:: decode($data)."'";

	   }

	   $select = $this->_db->select()                

		  ->from(array('VC' => VEHICLE_SETTINGS),array('*'))

		   ->where($where)

		   ->order("VC.vehicle_name");

		  // echo $select->__tostring();die;

		  $result = $this->getAdapter()->fetchAll($select); 

	   //echo "<pre>"; print_r($result); die;

		  return $result;

	 }

	/**

	* Function : AddVehicleSetting($data)

	* This function is for to add vehicle setting

	* Date of creation 11/11/2016

	**/ 

	 public function AddVehicleSetting($data){

	   $result = $this->_db->insert(VEHICLE_SETTINGS,array('vehicle_name'=>$data['vehicle_name'],'vehicle_number'=>$data['vehicle_number'],'vehicle_distance'=>$data['vehicle_distance'],'created_by'=>$this->Useconfig['user_id'],'created_date'=>new Zend_Db_Expr('NOW()'),'created_ip'=>commonfunction::loggedinIP()));  

	   return ($result) ? true : false;

	  }

	/**

	* Function : EditVehicleSetting($data)

	* This function is for to edit vehicle setting

	* Date of creation 11/11/2016

	**/ 

 public function EditVehicleSetting($data){

   $update = $this->_db->update(VEHICLE_SETTINGS,array('vehicle_name'=>$data['vehicle_name'],'vehicle_distance'=>$data['vehicle_distance'],'vehicle_number'=>$data['vehicle_number'],'updated_by'=>$this->Useconfig['user_id'],'updated_ip'=>commonfunction::loggedinIP(),'updated_date'=>new Zend_Db_Expr('NOW()')),'vehicle_id='.Zend_Encript_Encription:: decode($data['id']));

   return ($update) ? true : false;  

  }

  

	/**

	* add new packet shop

	* Function : addPaketShop()

	* This function create a new packet shop

	* Date of creation 26/10/2016

	**/ 	

	 public function addpacketshop($data){

			 	$dataArr =  array('country_id'=>$data['country_id'],'shope_name'=>$data['shope_name'],'postal_code'=>$data['postal_code'],'city'=>$data['city'],'street'=>$data['street'],'streetno'=>$data['streetno'],'address'=>$data['address'],'latitude'=>$data['latitude'],'longitude'=>$data['longitude'],'create_date'=>new Zend_Db_Expr('NOW()'),'create_by'=>$this->Useconfig['user_id'],'created_ip'=>commonfunction::loggedinIP());

					$dataArr['shop_id'] = date('myhs');

					$dataArr['workinghours'] = json_encode($this->daysInArray($data));

					$this->_db->insert(PARCELSHOP,array_filter($dataArr));

		}

	/**

	* add new packet shop

	* Function : daysInArray()

	* This function create an array to json data

	* Date of creation 26/10/2016

	**/ 	

	 public function daysInArray($data){

			$workingdetails = array();

			$days =array('0'=>'Sunday','1'=>'Monday','2'=>'Tuesday','3'=>'Wednesday','4'=>'Thursday','5'=>'Friday','6'=>'Saturday');

				

			for($i=0;$i <count($days);$i++){

				$workingdetails[$days[$i]]['open'] = $data['start_time_'.$days[$i]];

				$workingdetails[$days[$i]]['close'] = $data['end_time_'.$days[$i]];

			}

				 return $workingdetails;

	  }

	  	/**

	* edit new packet shop

	* Function : daysInJson($data)()

	* This function create Json to array data

	* Date of creation 27/10/2016

	**/ 	

	 public function daysInJson($resetdata = array()){

			$workingdetails = json_decode($resetdata['workinghours']);

			$days =array('0'=>'Sunday','1'=>'Monday','2'=>'Tuesday','3'=>'Wednesday','4'=>'Thursday','5'=>'Friday','6'=>'Saturday');

			unset($resetdata['workinghours']);

			for($i=0;$i <count($days);$i++){

				if(isset($workingdetails->$days[$i]->open)){

				$resetdata['start_time_'.$days[$i]] = $workingdetails->$days[$i]->open;

				}

				if(isset($workingdetails->$days[$i]->close)){

				$resetdata['end_time_'.$days[$i]] = $workingdetails->$days[$i]->close;

				}

				

			}

			return $resetdata;

	  }

	  

		/**

		* add new packet shop

		* Function : updatePaketShop()

		* This function create a new packet shop

		* Date of creation 26/10/2016

		**/ 	

		 public function updatepacketshop($data){

					$dataArr =  array('country_id'=>$data['country_id'],'shope_name'=>$data['shope_name'],'postal_code'=>$data['postal_code'],'city'=>$data['city'],'street'=>$data['street'],'streetno'=>$data['streetno'],'address'=>$data['address'],'latitude'=>$data['latitude'],'longitude'=>$data['longitude'],'updated_date' => new Zend_Db_Expr('NOW()'),'updated_by' => $this->Useconfig['user_id'],'updated_ip' => commonfunction::loggedinIP());

						$dataArr['workinghours'] = json_encode($this->daysInArray($data));

						$this->_db->update(PARCELSHOP,$dataArr,"id='".$data['id']."'"); 

		 }

		 

		 /**

		* Delete packet shop

		* Function : deletePaketShop()

		* This function is to delete packet shop

		* Date of creation 27/10/2016

		**/ 	

		 public function deletepacketshop($data){

					$result =	$this->_db->update(PARCELSHOP,array('is_delete' =>'1'),"id='".$data['id']."'"); 

					return ($result) ? true : false;

		 }

		 

		 			 	/**

         * Function : AddPort($data)

         * This function is for to add vehicle setting

		 * Date of creation 11/11/2016

         **/ 

		 

	public function AddPort($data){

			$result = $this->_db->insert(COUNTRYPORT,array('country_id'=>$data['country_id'],'port_name'=>$data['port_name'],'port_code'=>$data['port_code'],'added_by'=>$this->Useconfig['user_id'],'added_date'=>new Zend_Db_Expr('NOW()'),'added_ip'=>commonfunction::loggedinIP()));	 

			return ($result) ? true : false;

		}

	/* Function : upadtecountrydetail($data)

    * This function is for update country Detail

	* Date of creation 03/11/2016

    **/ 

		 

	public function upadtecountrydetail($data)	{

		$this->getData['cncode'] = strtoupper($this->getData['cncode']);

		$this->getData['cncode3'] = strtoupper($this->getData['cncode3']);

		$result = $this->UpdateInToTable(COUNTRIES,array($this->getData),'country_id='.$this->getData['id']);

		return ($result) ? true : false;

	}

	/* Function : portlist($data)

    * This function is for displaylist of port based on country

	* Date of creation 26/11/2016

    **/ 

	public function portlist($data)

	{

			$where = 1;

			if(isset($data['id']) && $data['id']!='' && isset($data['port_id']) && $data['port_id'] !=''){

			  $where .= " AND CC.country_id='".$data['id']."' AND CC.port_id='".$data['port_id']."'";

			}elseif(isset($data['id']) && $data['id']!=''){

			  $where .= " AND CC.country_id='".$data['id']."'";

			}

				  

			$select = $this->_db->select()                

				  ->from(array('CC'=>COUNTRYPORT),array('*'))

				  ->joinleft(array('CT'=>COUNTRIES),"CT.country_id=CC.country_id",array('CT.country_name'))

				  ->where($where)

				  ->order("CC.port_name");

				  //echo $select->__tostring();die;

			$result = $this->getAdapter()->fetchAll($select); 

			return $result;

		

	}

	public function UpdatePort()

	{ 	try{

		$this->getData['modify_by'] = $this->Useconfig['user_id'];

		$this->getData['modify_ip'] = commonfunction::loggedinIP();

		$this->getData['modify_date'] = '';



		$this->UpdateInToTable(COUNTRYPORT,array($this->getData),"port_id ='".$this->getData['port_id']."'");

		}catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

	}

	public function getBlockIp()

	{

		$select = $this->_db->select()                

				  ->from(array('BI'=>BLOCKIP),array('*'))

				  ->where('is_delete !=?', '1')

				  ->order("BI.created_date DESC");

				  //echo $select->__tostring();die;

			return $this->getAdapter()->fetchAll($select);

	}

	

	public function addBlockIp($data)

	{	$data['created_by'] = $this->Useconfig['user_id'];

		$data['created_ip'] = commonfunction::loggedinIP();

		$data['created_date'] = '';

		$this->insertInToTable(BLOCKIP,array($data));

	} 

	 public function updateLanguage(){  

		 try{

		  $where = 'language_id ='.Zend_Encript_Encription:: decode($this->getData['id']);

		  $this->UpdateInToTable(LANGUAGE,array($this->getData),$where);

		  }catch (Exception $e) {

			 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		  }

	}

 

  public function addLanguage(){ 

     try{

	   $this->insertInToTable(LANGUAGE,array($this->getData));

	  }catch (Exception $e) {

		 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	  }

	 }

	/**

  * List of forwarder service which has status = 1

  * Function : getAllServices()

  * params 'parent, status, service_id'

  * This function get list of forwarder service with status true

  * 21/12/2016

  **/

 public function getAllServices($parentservice = '',$status = '',$id=''){

    try{

	  $where = ($parentservice != '') ? 'SV.parent_service="'.$parentservice.'"' : '1';

	  $status = ($status != '') ? 'SV.status="'.$status.'"' : '1';

	  $id = ($id != '') ? 'SV.service_id="'.$id.'"' : '1';

		 $select = $this->_db->select()

					->from(array('SV'=>SERVICES), array('*'))

					->where($status)

					->where($where)

					->where($id)

					->order(array('service_id ASC'));

		

	   }catch(Exception $e){

	  		$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	}

    return  $this->getAdapter()->fetchAll($select);

 }

	/**

	* Fetch forwarder country data

	* Function : forwarderCountry()

	* Fetch forwarder country data based on country id 

	* Date : 07/12/2016

	* */

    public function forwarderCountry($forwarder_id) {

        try {

            $select = $this->_db->select()

                    ->from(array('RCS'=>ROUTING_COUNTRY_SERVICE), array('CT.country_name','CT.country_id'))

                    ->joininner(array('CT'=>COUNTRIES), 'RCS.country_id = CT.country_id',array())

                    ->where('RCS.forwarder_id =?', $forwarder_id)

					 ->group('RCS.country_id');

					 //echo $select->__tostring();die;

            $result = $this->getAdapter()->fetchAll($select);

        } catch (Exception $e){

            $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

        }

        return $result;

    }



  /**

  * Fetch Aditional Services

  * Function : CountryConfigServiceList()

  * Fetch All the Country Config Service List from parcel Services.

  * Date : 12/12/2016

  **/

 public function CountryConfigServiceList($countryId,$forwarderId){

   $select = $this->_db->select()

          ->from(ROUTING_COUNTRY_SERVICE,array(SERVICE_ID))

          ->where('country_id ='.$countryId)

          ->where('forwarder_id ='.$forwarderId);

          //echo $select->__tostring();die;

   $result = $this->getAdapter()->fetchAll($select); 

   return $oneDimensionalArray = array_map('current', $result);

 }

 

	/**

	* Function : forwarderservice()

	* Params : NULL

	* forwarderservice 

	* */

    public function forwarderservice($data){

  $servicelist = $this->getAllServices('0','1');

  $checkBox = array();

  $serviceID = array();

  if (isset($data['token2']) && $data['token2'] != '') {

    $serviceID = $this->CountryConfigServiceList($data['token2'],$data['token1']);

   }

  

  foreach($servicelist as $key => $service){

   if(in_array($servicelist[$key][SERVICE_ID],$serviceID)){

       $checkBox[$key] = array('id' => $servicelist[$key][SERVICE_ID],'label'=>$servicelist[$key]['service_name'],'value'=>'checked = checked');

   }else{

    $checkBox[$key] = array('id' => $servicelist[$key][SERVICE_ID],'label'=>$servicelist[$key]['service_name'],'value'=>'');

   }

   $addserviceList = $this->getAllServices($servicelist[$key][SERVICE_ID],'1'); 

   $checkboxid = '';

    foreach($addserviceList as $subkey=>$addservice){

                if (commonfunction :: inArray($addservice['service_id'],$serviceID)){

                    $checkBox[$key]['child'][$subkey] = array('id' => $addservice['service_id'],'label'=>$addservice['service_name'],'value'=>'checked = checked');

                }else{

     $checkBox[$key]['child'][$subkey] = array('id' => $addservice['service_id'],'label'=>$addservice['service_name'],'value'=>'');

    }

               if ($checkBox[$key]['value'] == '') {

                    $checkBox[$key]['child'][$subkey]['disabled'] = 'disabled';

                }else{

     $checkBox[$key]['child'][$subkey]['disabled'] = '';

    }

                $checkboxid .= $addservice['service_id'] . ',';

    $checkBox[$key]['onclick'] = "enablecheckbox('".$service[SERVICE_ID]."','".$checkboxid."')";

            }

    

  }

         return $checkBox;

 }

 

  /**

  * Update forwarder service

  * Function : UpdateForwarderService()

  * Update Service code and add service in parcel_routing_countryservices table.

  * Date : 15/12/2016

  **/

  public function UpdateForwarderService(){

		 try{

		  $forwarder_id = Zend_Encript_Encription:: decode($this->getData['token1']);

		  $deleteResult = $this->_db->delete(ROUTING_FORWARDER_COUNTRY,'forwarder_id="'.$forwarder_id.'"');

		   if(isset($this->getData['country_id']) && count($this->getData['country_id']) > 0){

			foreach($this->getData['country_id'] as $requestdata){

			   $this->_db->insert(ROUTING_FORWARDER_COUNTRY, array('forwarder_id'=>$forwarder_id,'country_id'=>$requestdata));

			}

		   }

			return true;

		  }catch(Exception $e){

            $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

        }

 }

 /**

  * Forwarder Add country

  * Function : forwarderaddcountry()

  * This function add the country  for the forwarder

  **/

 public function forwarderaddcountry($data){

    try{

     $select = $this->_db->select()

								->from(ROUTING_FORWARDER_COUNTRY,array('*'))

								->where("".COUNTRY_ID."='".$data[COUNTRY_ID]."' AND ".FORWARDER_ID."=".$data['token1']."");

    $result = $this->getAdapter()->fetchAll($select); 

  if(!$result){

    $insert = $this->_db->insert(ROUNTING_FORWARDER_COUNTRY, array(COUNTRY_ID=>$data[COUNTRY_ID],FORWARDER_ID=>$data['token1']));

    return ($insert) ? true : false;

  }

   }catch (Exception $e){

            $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

        }

 }

 

 /**

  * List of Forwarder country

  * Function : getForwarderCountry()

  * This function get list of forwarder country

  * 20/12/2016

  **/

 public function getForwarderCountry($data=''){

    try{

     $resArr = array();

     $select = $this->_db->select()

        ->from(array('RFS'=>ROUTING_FORWARDER_COUNTRY),array('country_id'))

        ->joininner(array('CT'=>COUNTRIES), 'RFS.country_id = CT.country_id',array('CONCAT(CT.cncode,"--",CT.country_name) as country_name'))

        ->where("".FORWARDER_ID."=".$data."")

        ->order("CT.country_name");

    return $this->getAdapter()->fetchAll($select); 

   }catch (Exception $e){

            $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

        }

 }

 

  public function getTransitDetail()

 {

  $where = 'service_id ='.Zend_Encript_Encription:: decode($this->getData['id']);

  $select = $this->_db->select()

      ->from(TRANSIT_DETAIL,array('*'))

      ->where($where);

  return $this->getAdapter()->fetchAll($select); 

 }

 public function UpdateLocalInfoTransitTime()

 {  try{

  $insertData = array();

  $decodeCountryId = Zend_Encript_Encription:: decode($this->getData['id']);

  $where = 'service_id ='.$decodeCountryId;

   $this->_db->delete(TRANSIT_DETAIL,$where);

   $insertData['service_id'] =  $decodeCountryId;

      foreach($this->getData['country_id'] as $key=>$value){

    $insertData['country_id'] = Zend_Encript_Encription:: decode($value);

    $insertData['transit_days'] =  $this->getData['transit_days'][$key];

    $insertData['transit_desc'] =  $this->getData['transit_desc'][$key];

    if($insertData['transit_days']!='' || $insertData['transit_desc'] !=''){

      $return[] = $this->insertInToTable(TRANSIT_DETAIL,array($insertData));

      

    }

   }

   return true;

  }catch (Exception $e) {

     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

  }

 }

 	/**

	 * get Country Setting Detail 

	 * Function : getCountrySettingDetail()

	 * Date : 14/01/2017

     **/	

	public function getCountrySettingDetail($data){

		try{

			   $select = $this->_db->select()                

								  ->from(array(COUNTRY_SETTING),array('*'))

								  ->where("country_id='".$data."'");

			$result = $this->getAdapter()->fetchRow($select); 

		    return ($result) ? $result : array();

		   }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

	}

	

	/**

	 * setting for country  

	 * Function : addeditcountrysetting()

	 * Date : 14/01/2017

     **/	

	public function addeditcountrysetting()

	{ 	try{

			$this->getData['country_id'] = Zend_Encript_Encription:: decode($this->getData['country_id']);

			$previous = $this->getCountrySettingDetail($this->getData['country_id']);

			if(isset($previous['country_id']) && $previous['country_id']>0)

			{

				$return = $this->UpdateInToTable(COUNTRY_SETTING,array($this->getData),'country_id="'.$this->getData['country_id'].'"');

			}else{

				$return = $this->insertInToTable(COUNTRY_SETTING,array($this->getData));



			}

			return ($return) ? true : false;

		}catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

	}

	/**

	 * Tab access validation in setting module  

	 * Function : tabNavigation()

	 * Date : 01/02/2017

     **/		

	public function tabNavigation(){

		$tabhtml = '';

		if($this->Useconfig['level_id'] == 1){

			$tabArray = array('Print Setting'=>'printsetting','Country'=>'generalsetting','Weight Scaler'=>'weightscallers','Email Notification'=>'emailnotification','Packetshop'=>'packetshop','Vehicle Setting'=>'vehiclesetting','Block Ip List'=>'blockiplist','Language'=>'changelanguage');

		}else{

			$tabArray = array('Print Setting'=>'printsetting','Email Notification'=>'emailnotification','Language'=>'changelanguage');

		}

		foreach($tabArray as $key=>$value){

			if($value == $this->getData['action']){

				$tabhtml .= '<li class="active"> <a href="'.BASE_URL.'/Settings/'.$value.'" data-toggle="">'.$key.'</a> </li>';

			}else{

				$tabhtml .= '<li> <a href="'.BASE_URL.'/Settings/'.$value.'" data-toggle="">'.$key.'</a> </li>';

			}

		}

		return $tabhtml;

	}

	

	/**

	 * Get Forwarder FTP etail 

	 * Function : ForwarderFTPDetail()

	 * Date : 07/03/2017

     **/		

	public function ForwarderFTPDetail($data){

		try{

			   $select = $this->_db->select()                

								  ->from(array(FORWARDERS_FTP_DETAIL),array('*'))

								  ->where("forwarder_id='".$data."'");

			$result = $this->getAdapter()->fetchRow($select); 

		    return ($result) ? $result : array();

		   }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

	}

	

	/**

	 * update Forwarder Detail  

	 * Function : updateForwarderDetail()

	 * Date : 07/03/2017

     **/	

	public function updateForwarderDetail()

	{ 	try{

			$this->getData['forwarder_id'] = Zend_Encript_Encription:: decode($this->getData['forwarder_id']);



				$return = $this->UpdateInToTable(FORWARDERS,array($this->getData),'forwarder_id="'.$this->getData['forwarder_id'].'"');

				$ftpDatail = $this->ForwarderFTPDetail($this->getData['forwarder_id']);

				if(count($ftpDatail)>0){

					$return = $this->UpdateInToTable(FORWARDERS_FTP_DETAIL,array($this->getData),'forwarder_id="'.$this->getData['forwarder_id'].'"');

				}

			return ($return) ? true : false;

		}catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

	}

	

	/**

	* Update Language Option

	* Function : UpdateLanguageOption()

	* Update Language Option of Loged In User

	**/

    public function UpdateLanguageOption(){

		try {

		  $update = $this->_db->update(USERS_SETTINGS,array('language_id'=>$this->getData['language_id']),'user_id='.$this->Useconfig['user_id']);

		   $logicSeesion = new Zend_Session_Namespace('logicSeesion');

		   $logicSeesion->userconfig['language_id'] = $this->getData['language_id'];
		    $logicSeesion->translationArray = $this->getTranslations($logicSeesion->userconfig['language_id']);
		 return ($update) ? true : false;

		}catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

	  }

	  public function RemoveEmptyFields($FetchedFields,$formObj)
	  {
	  	
	  	$AdditionalFields = Array
				(
				    'manifest_customer_number', 
				    'tracking_length', 
				    'shipping_depot_no',
				    'delis_user_id', 
				    'version_number', 
				    'SAP_number',
				    'class_of_service',
				    'contract_number',
				    'sub_contract_number',
				    'service_type',
				    'service_indicator',
				    'service_icon',
				    'service_code_kp', 
				    'service_code_np', 
				    'primary_socket',
				    'primary_port',
				    'secondry_socket',
				    'secondry_port',
				    'barcode_prefix',
				    'IFD_number', 
				    'tracenr_status', 
				    'forwarder_icon', 
				    'contract_user_id', 
				);

 		foreach ($AdditionalFields as $key => $field) {
 			if(isset($FetchedFields[$field]) && empty($FetchedFields[$field]))
 				$formObj->removeElement($field);
 		}
 		return $formObj;

	  }

}



