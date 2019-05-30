<?php
/*

 * CommonFunction of Logicparcel ERP System

 * @PHP version  : 5.6

 * @ZEND version : 1.12.20

 * @author       : SJM Softech Private Limited <contact@sjmsoftech.com>

 * @created on   : 30 Sep-2016

 * @link         : http://www.dpost.be

 */

ini_set("memory_limit", "-1");

class Zend_Customclass extends Zend_Db_Table_Abstract {
	public $Useconfig = NULL;
	public $logger = NULL;
	public $getData = array();
	public $forwarderdetails = array();
	public $RecordData	 = array();	
	public $masterdb	 = NULL;	
	public $_insertbrcode = TRUE;
	public $_logger = NULL;
	public $FS = array();
	public function __construct(){
		    $logicSeesion = new Zend_Session_Namespace('logicSeesion');
			$this->Useconfig = $logicSeesion->userconfig;
			$this->masterdb = Zend_Registry::get("masterdb");
			$writer = new Zend_Log_Writer_Stream(ROOT_PATH.'/log/error.log');
			$this->_logger = new Zend_Log($writer);
			parent::__construct();
	}
	public function getMenu(){ 
	    try{
		$select = $this->_db->select()
						  ->from(array('MT'=>MODULES),array('*'))
						  ->where('MT.module_parent=?',0);
		$mainmenu = $this->getAdapter()->fetchAll($select);				  
	  }catch(Exception $e){
	   
	  }
	  $AapplicationMenu = array();
	  foreach($mainmenu as $menues){
	     
	     $select = $this->_db->select()
						  ->from(array('MT'=>MODULES),array('*'))
						  ->where('MT.module_parent=?',$menues['module_id']);
		 $submenu = $this->getAdapter()->fetchAll($select);
		 $menues['level2'] = $submenu;
		 $AapplicationMenu[] = $menues; 
	  }
	   return $AapplicationMenu;
	   //echo "<pre>";print_r($AapplicationMenu);die;
	}
	public function getModuleName($controllerName,$actionName){
	    $select = $this->_db->select()
						  ->from(array('MT'=>MODULES),array('module_name'))
						  ->where('MT.module_controller=?',commonfunction::trimString($controllerName))
						  ->where('MT.module_action=?',commonfunction::trimString($actionName));//echo $select->__toString();die;
		 $modulename = $this->getAdapter()->fetchRow($select);
		 return $modulename['module_name'];
	}
	/**
     * Return list of countiries
     * @param  null| get on the basis of logged useris session
     * @return country list with full information in array
     */
	public function getCountryList(){
	    $select = $this->_db->select()
						  ->from(array('CT'=>COUNTRIES),array('*','CONCAT(CT.cncode,"-",CT.country_name) AS country_code_name'))
						  ->order("CT.country_name ASC");
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getForwarderList(){
	    $select = $this->_db->select()
						  ->from(array('FT'=>FORWARDERS),array('forwarder_name','forwarder_id'))
						  ->order("FT.forwarder_name ASC");
		return $this->getAdapter()->fetchAll($select);
	}
	/**
     * Return detail of a perticular country
     * @param  $cond_id,$check| get country detail of check 1 then country id and 2 then country code and 3 then country name
     * @return detail of country by id , name and code
     */
	public function getCountryDetail($cond_id,$check=1){ 
	    $select = $this->_db->select()
						  ->from(array('CT'=>COUNTRIES),array('*'));
		switch($check){
		    case 1:
			 $select->where('CT.country_id=?',$cond_id);
			break;
			case 2:
			  $select->where('CT.cncode=?',$cond_id);
			break;
			case 3:
			  $select->where('LOWER(CT.country_name)=?',$cond_id);
			break;
		  default:
		     $select->where('CT.country_id=?',$cond_id);
		  break;	
		}
        return $this->getAdapter()->fetchRow($select);
	}
	/**
     * Return list of customers according to lavel
     * @param  null| get on the basis of logged in lavel
     * @return customer list in array
     */
	public function getCustomerList(){  
	     $select = $this->_db->select()
						  ->from(array('UT'=>USERS),array('*'))
						  ->joininner(array('UD'=>USERS_DETAILS),"UT.user_id=UD.user_id",array('user_id','company_name','postalcode','city'))
						  ->where('UT.user_status=?', '1')
						  ->where('UT.delete_status=?', '0')
						  ->where('UT.level_id=?', 5);
		 switch($this->Useconfig['level_id']){
		    case 4:
			  $select->where('UD.parent_id=?',$this->Useconfig['user_id']);
			break;
			case 5:
			   $select->where('UD.user_id=?',$this->Useconfig['user_id']);
			break;
			case 6:
			$depot_id = $this->getDepotID($this->user_session['user_id']);
			$this->select->where->equalTo('UD.parent_id', $depot_id);
			break;
			case 10:
			$parent_id = $this->getDepotID($this->user_session['user_id']);
			$this->select->where->equalTo('UD.user_id', $parent_id);
			break;
		}
		
		 $select->order("UD.company_name ASC");				  
		return $this->getAdapter()->fetchAll($select);
	}
	/**
     * Return list of countiries
     * @param  null| get on the basis of logged useris session
     * @return country list with full information in array
     */
	public function getDepotList(){
	    $select = $this->_db->select()
						  ->from(array('UT'=>USERS),array('*'))
						  ->joininner(array('UD'=>USERS_DETAILS),"UT.user_id=UD.user_id",array('user_id','company_name','postalcode','city'))
						  ->where('UT.user_status=?', '1')
						  ->where('UT.delete_status=?', '0')
						  ->where('UT.level_id=?', 4);
		 switch($this->Useconfig['level_id']){
		    case 4:
			  $select->where('UD.user_id=?',$this->Useconfig['user_id']);
			break;
			case 6:
			$depot_id = $this->getDepotID($this->user_session['user_id']);
			$this->select->where->equalTo('UD.user_id', $depot_id);
			break;
		}
		
		 $select->order("UD.company_name ASC");				  
		return $this->getAdapter()->fetchAll($select);
	}
	/**
     * Return depot id of customer
     * @param  $user_id| user id of requested customer
     * @return depot id of customer as integer
     */
   public function getDepotID($user_id){
         $select = $this->_db->select()
						  ->from(array('UD'=>USERS_DETAILS),array('parent_id'))
						  ->where('UD.user_id=?', $user_id);
		 $result = $this->getAdapter()->fetchRow($select);
		return $result['parent_id'];
   }
   public function insertInToTable($tablename,$dataArr){
       try {
            $columns = $this->columnName($tablename);
            for ($i = 0; $i < count($dataArr); $i++) {
                foreach ($columns as $key => $value) {
                    if (array_key_exists($value, $dataArr[$i])) {
                        $isertArr[$value] = trim(preg_replace('/\s+/', ' ', str_replace('"', '', $dataArr[$i][$value])));
					    if($value=='create_date' || $value=='edit_date'){
					        $isertArr[$value] = new Zend_Db_Expr('NOW()');
					    }
                    }
                }
                $this->_db->insert($tablename, $isertArr);
            }

        } catch (Exception $e) {

           echo $e->getMessage(); die;

        }

        return $this->getAdapter()->lastInsertId(); 
   }
   
    public function UpdateInToTable($tablename,$dataArr,$where){
       try {
            $columns = $this->columnName($tablename);
            for ($i = 0; $i < count($dataArr); $i++) {
                foreach ($columns as $key => $value) {
                    if (array_key_exists($value, $dataArr[$i])) {
                        $isertArr[$value] = trim(preg_replace('/\s+/', ' ', str_replace('"', '', $dataArr[$i][$value])));
					    if($value=='modify_date'){
					        $isertArr[$value] = new Zend_Db_Expr('NOW()');
					    }
                    }
                }
                $this->_db->update($tablename, $isertArr,$where);
            }

        } catch (Exception $e) {

            echo $e->getMessage(); die;

        }

       return true;
   }
   
   public function columnName($tablename) {
        try {
            $column = $this->_db->describeTable($tablename);
            $columnNames = array_keys($column);
        } catch (Exception $e) {
             echo $e->getMessage(); die;
        }
        return $columnNames;
    }
	public function ForwarderDetail(){
		$select = $this->_db->select()
							  ->from(array('FT'=>FORWARDERS),array('*'))
							  ->where('FT.forwarder_id=?', $this->RecordData['forwarder_id']);
		$this->FS = $this->getAdapter()->fetchRow($select);
		

		$this->getCustomerForwarderDetail();
		$this->FS['CustomerAddress'] = $this->getCustomerDetails($this->RecordData['user_id']);
		$this->FS['sender_address'] = str_replace(array('[company_name]','[contact_name]'),array($this->FS['CustomerAddress']['company_name'],$this->FS['CustomerAddress']['name']),$this->FS['sender_address']);
		$this->FS['return_address'] = str_replace(array('[company_name]','[contact_name]'),array($this->FS['CustomerAddress']['company_name'],$this->FS['CustomerAddress']['name']),$this->FS['return_address']);
		 $this->FS['SenderAddress'] = array();
		 $this->FS['DepotAddress'] = array();
		if($this->RecordData['senderaddress_id']=='B'){ 
			$this->FS['SenderAddress'] = array();
			
		}elseif($this->RecordData['senderaddress_id']=='C' || $this->RecordData['senderaddress_id']==''){
			foreach($this->FS['CustomerAddress'] as $customeradd){
			     $this->FS['SenderAddress'][] = $customeradd;
			}
	   }elseif($this->RecordData['senderaddress_id']!='' && !in_array($this->RecordData['senderaddress_id'],array('B','C'))){
	   
	   }
	   $this->FS['DepotAddress'] = commonfunction::explode_string($this->FS['depot_address'],"\n");
	   $this->switchAddress();
		 //echo "<pre>";print_r($this->FS);die;
	   $this->RecordData['sender_cncode'] = isset($returnData['SenderAddress'][5])?$returnData['SenderAddress'][5]:'';
	   $rec_country = $this->getCountryDetail($this->RecordData[COUNTRY_ID],1);
	   $this->RecordData['rec_cncode'] = $rec_country['cncode'];
	   $this->RecordData['rec_country_name'] = $rec_country['country_name'];
	   $this->RecordData['rec_continent_id'] = $rec_country['continent_id'];
	   $this->RecordData['goods_id'] = $this->getGoodsDetail($this->RecordData['goods_id']);
	   
	   //$returnData = array_merge($returnData,$forwarderdetail);
	   return $this->FS;		
						  
	}
	public function getCustomerForwarderDetail(){
	    switch($this->RecordData['forwarder_id']){
		   case 1:
		   case 2:
		   case 3:
		     $this->DPDServiceCode();
		   break;
		   case 24:
		   case 25:
		     $forwarderdetails = $this->getDHLSpecialAccount(1);
			 $this->FS['separate_tracking'] = (!empty($forwarderdetails))?1:0;
		   break;
		}
		foreach($forwarderdetails as $key=>$forwarderdetail){
		   $this->FS[$key] = $forwarderdetail;
		}
		return true;
	}
	
	public function DPDServiceCode(){
	
	}
	
	public function getDHLSpecialAccount($count){
	      $service_id = ($this->RecordData['addservice_id']==124 || $this->RecordData['addservice_id']==148)?124:0;
		  $select = $this->_db->select()
						  ->from(array('FDS'=>DHL_SETTINGS),array('*'))
						  ->where('FDS.forwarder_id=?', $this->RecordData['forwarder_id'])
						  ->where("(FDS.user_id='".$this->RecordData['user_id']."')")
						  ->where("FDS.service_id='".$service_id."'");
						  //print_r($select->__toString());die;
		$forData = $this->getAdapter()->fetchRow($select);
		if(!empty($forData) || $count>=2){
		  return (isset($forData['customer_number']) && isset($forData['contract_number']) && $forData['customer_number']!='' && $forData['contract_number'])?$forData:array();
		}else{
		  $count++;
		  return $this->getDHLSpecialAccount($count);
		}
	}
	
	public function getCustomerDetails($user_id){
	   try{
	    $select = $this->_db->select()
                ->from(array('AT'=>USERS_DETAILS), array('AT.company_name','CONCAT(AT.first_name," ",AT.middle_name," ",AT.last_name) AS name','AT.address1','AT.city','AT.postalcode','CT.cncode','CT.country_name','PT.company_name as parent_company','AT.parent_id','AT.email','AT.phoneno'))
				->joininner(array('CT'=>COUNTRIES),"CT.country_id=AT.country_id",array())
				->joinleft(array('PT'=>USERS_DETAILS),"PT.user_id=AT.parent_id",array())
				->where("AT.user_id='".$user_id."'");//print_r($select->__toString());die;
       }catch(Exception $e){
	     echo $e->getMessage();die;
	   }
	   return  $this->getAdapter()->fetchRow($select);
	}
	
	public function switchAddress(){
	   if($this->RecordData['addservice_id']==124 && $this->RecordData['addservice_id']==148){		
	     switch($this->RecordData['forwarder_id']){
		    case 11:
			case 24:
			case 27:
			 $receiverAdd = commonfunction::explode_string($this->FS['return_address'],"\n");
			break;
			case 32:
			  if($this->FS['SenderAddress'][5]!='NL' && $this->FS['SenderAddress'][5]!='DE'){
			     if($this->RecordData[COUNTRY_ID]==9){
				   $receiverAdd = array($this->FS['SenderAddress'][0],$this->FS['SenderAddress'][1],'Euregioweg 332','Enschede','7532SN','NL');
				 }elseif($this->RecordData[COUNTRY_ID]==15){
				   $receiverAdd = array($this->FS['SenderAddress'][0],$this->FS['SenderAddress'][1],'Warschauerstr 8','Bad Bentheim','48455','DE');
				 }
			  
			  }else{
			     $receiverAdd = $this->FS['SenderAddress'];  
			  }
			break;
			default:
			 $receiverAdd = $this->FS['SenderAddress'];
		 }
		$this->RecordData[RECEIVER] = $receiverAdd[0];
		$this->RecordData[CONTACT] = $receiverAdd[1];
		$this->RecordData[STREET] = $receiverAdd[2];
		$this->RecordData[CITY] = $receiverAdd[3];
		$this->RecordData[ZIPCODE] = $receiverAdd[4];
		$country_details = $this->getCountryDetail($receiverAdd[5],2);
		$this->RecordData[COUNTRY_ID] = $country_details['country_id'];
		$this->RecordData[PHONE] = $this->FS['CustomerAddress']['phoneno'];
		$this->RecordData[EMAIL] = $this->FS['CustomerAddress']['email'];
		$this->RecordData[STREETNR] = '';
		$this->RecordData[ADDRESS] = '';
		$this->RecordData[STREET2] = '';
	   }else{
	       switch($this->RecordData['forwarder_id']){
		      case 24:
			     if($this->FS['SenderAddress']!='DE'){
				     $this->FS['SenderAddress'] = commonfunction::explode_string($this->FS['sender_address'],"\n");
				 }
			  break;
		   } 
	   }
	}
	
	public function getUniqueTracking(){
	     $forwarderdetail =  $this->getCustomerForwarderDetail($this->RecordData[ADMIN_ID]); 
		 $customerTracking = 1;
		 if(empty($forwarderdetail)){
		    $select = $this->_db->select()
							  ->from(array('FT'=>FORWARDERS),array('*'))
							  ->where('FT.forwarder_id=?', $this->RecordData[FORWARDER_ID]);
			$forwarderdetail = $this->getAdapter()->fetchRow($select);
			$customerTracking = 0;
		 }
		 $trackingnumber = 0;
		 if($forwarderdetail[LAST_TRACKING]<=$forwarderdetail[END_TRACKING]){
		      $trackingnumber = commonfunction::paddingleft(($forwarderdetail[LAST_TRACKING] + 1),$forwarderdetail['tracking_length'],0);
		 }else{
		    
		 }
		 if($customerTracking==1){
		    $this->_db->update(FORWARDERS_CUST,array(LAST_TRACKING=>$trackingnumber),"fc_id='".$forwarderdetail['fc_id']."'");
		 }elseif($customerTracking==0){
		    $this->_db->update(FORWARDERS,array(LAST_TRACKING=>$trackingnumber),"forwarder_id='".$this->RecordData[FORWARDER_ID]."'");
		 }
		return $trackingnumber; 
	}
	
	public function getRoutingID($forwarder_id=false){
         $userdetail = $this->getCustomerDetails($this->getData[ADMIN_ID]);
		 $this->getData[PARENT_ID]= $userdetail['parent_id'];
		 $begainpostCode = substr(ltrim($this->getData[ZIPCODE]),'0','2'); 
        if(!is_numeric($begainpostCode)){  
            $secondbegainpostalCode = substr(ltrim($this->getData[ZIPCODE]), '2', '2');
            $secondbegainpostalCode = (!empty($secondbegainpostalCode))?$secondbegainpostalCode:0;
			$select = $this->_db->select()
							->from(ROUTING, array(ROUTING_ID, 'if(user_id,1,1) as special'))
							->where("country_id='" . $this->getData[COUNTRY_ID] . "' AND user_id='" . $this->getData[PARENT_ID] . "'
									 AND min_weight<'" . $this->getData[WEIGHT] . "' AND max_weight>='" . $this->getData[WEIGHT] . "'
									 AND substr(beginPostCode,1,2)='" . $begainpostCode . "'
									 AND if(substr(beginPostCode,3,2)!='',substr(beginPostCode,3,2)<='" . $secondbegainpostalCode . "',1)
									 AND if(substr(endPostCode,3,2)!='',substr(endPostCode,3,2)>='" . $secondbegainpostalCode . "',1)");
		 if ($forwarder_id) {
             $select->where('forwarder_id=?',$forwarder_id);
          }						 
        $result = $this->getAdapter()->fetchAll($select);  
        }elseif(is_numeric($begainpostCode)){
		    $postCode = commonfunction::onlynumbers($this->getData[ZIPCODE]);
            $select = $this->_db->select()
                        ->from(ROUTING, array(ROUTING_ID, 'if(user_id,1,1) as special'))
                        ->where("" . COUNTRY_ID . "='" . $this->getData[COUNTRY_ID] . "' AND " . ADMIN_ID . "='" . $this->getData[PARENT_ID] . "'
								 AND min_weight<'" . $this->getData[WEIGHT] . "' AND max_weight>='" . $this->getData[WEIGHT] . "'
								 AND beginPostCode<=" . trim($postCode) . " AND endPostCode>=" . trim($postCode) . "");
        	if ($forwarder_id) {
             $select->where('forwarder_id=?',$forwarder_id);
            }	
           $result = $this->getAdapter()->fetchAll($select);
        }
        if(empty($result)) { 
            $select = $this->_db->select()
                            ->from(ROUTING, array(ROUTING_ID, 'if(user_id,0,0) as special'))
                            ->where("" . COUNTRY_ID . "='" . $this->getData[COUNTRY_ID] . "' AND " . ADMIN_ID . "='" . $this->getData[PARENT_ID] . "'
                                    AND min_weight<'" . $this->getData[WEIGHT] . "' AND max_weight>='" . $this->getData[WEIGHT] . "'
                                    AND (beginPostCode IS NULL OR beginPostCode='') AND (endPostCode IS NULL OR endPostCode='')");
            				//print_r($select->__toString());die;
           $result = $this->getAdapter()->fetchAll($select);
        }        
        return $result;
	 } 
	 
	public function getServiceID($service_id=0){
		    $routings = $this->getRoutingID();
			$routingservice = array();
			$serName = array();
			if($routings){
			   foreach($routings as $rounting){
			   $serviceWHere = ($service_id>0)?" AND ST.parent_service='".$service_id."'":" AND ST.parent_service=0";
				$select = $this->_db->select()
									->from(array('RPT'=>ROUTING_PRICE),array('RPT.'.SERVICE_ID,'RPT.'.ROUTING_ID,'RPT.depot_price','RPT.customer_price','RPT.'.FORWARDER_ID))
								    ->joinleft(array('ST'=>SERVICES),'ST.'.SERVICE_ID."=RPT.".SERVICE_ID,array('ST.service_name','ST.service_icon','ST.description'))
									->where("".ROUTING_ID."='".$rounting[ROUTING_ID]."' AND depot_price>0 AND customer_price>0".$serviceWHere) 
									//->where("ST.parent_service=0")
									->order('ST.service_name ASC');
								//print_r($select->__toString());
				$result = $this->getAdapter()->fetchAll($select);
				if($result){
				   foreach($result as $services){
                       $services['special'] = $rounting['special'];
				       $routingservice[] = $services ;
					   $serName[] = $services['service_id'];
				   }
				}
			}
			array_multisort($serName, SORT_ASC, $routingservice);
		 }
		return $routingservice;
	}
	
	public function getAddServiceID($serviceData){
				$routingservice = array();
				$select = $this->_db->select()
									->from(array('RPT'=>ROUTING_PRICE),array('RPT.'.SERVICE_ID,'RPT.'.ROUTING_ID,'RPT.depot_price','RPT.customer_price','RPT.'.FORWARDER_ID))
								    ->joinleft(array('ST'=>SERVICES),'ST.'.SERVICE_ID."=RPT.".SERVICE_ID,array('ST.service_name','ST.service_icon','ST.description'))
									->where("".ROUTING_ID."='".$serviceData[ROUTING_ID]."' AND depot_price>0 AND customer_price>0") 
									->where("ST.parent_service='".$serviceData['service_id']."'")
									->order('ST.service_name ASC');
								//print_r($select->__toString());
				$result = $this->getAdapter()->fetchAll($select);
				if($result){
				   foreach($result as $services){
                       $services['special'] = $serviceData['special'];
				       $routingservice[] = $services ;
					   $serName[] = $services['service_id'];
				   }
				}
			array_multisort($serName, SORT_ASC, $routingservice);
		 return $routingservice;
	}
	
	public function FindServiceID($service_id){
		    $routings = $this->getRoutingID();
			$routingids = array();
			$serdetails = array();
			if($routings){
			   foreach($routings as $rounting){
			       $routingids[] = $rounting[ROUTING_ID];
				}
			 try{	
				$select = $this->_db->select()
									->from(array('RPT'=>ROUTING_PRICE),array('RPT.'.SERVICE_ID,'RPT.'.ROUTING_ID,'RPT.depot_price','RPT.customer_price','RPT.'.FORWARDER_ID))
								    ->joinleft(array('ST'=>SERVICES),'ST.'.SERVICE_ID."=RPT.".SERVICE_ID,array('ST.service_name','ST.service_icon','ST.description'))
									->where("".ROUTING_ID." IN(".commonfunction::implod_array($routingids).") AND depot_price>0 AND customer_price>0")
									->where("RPT.service_id='".$service_id."'");
								//print_r($select->__toString());die;
				  $serdetails = $this->getAdapter()->fetchRow($select);
				}catch(Exception $e){$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());}
		  }
		return $serdetails;
	}
	public function getParcelPrice(){
	    $service_id = 0;
		if($this->RecordData['service_attribute']>0){
		   $service_id = $this->RecordData['service_attribute'];
		}elseif($this->RecordData['addservice_id']>0){
		   $service_id = $this->RecordData['addservice_id'];
		}else{
		   $service_id = $this->RecordData['service_id'];
		}
		$select = $this->_db->select()
									->from(array('RPT'=>ROUTING_PRICE),array('RPT.'.SERVICE_ID,'RPT.'.ROUTING_ID,'RPT.depot_price','RPT.customer_price','RPT.'.FORWARDER_ID))
									->where("depot_price>0 AND customer_price>0 AND service_id='".$service_id."' AND forwarder_id='".$this->RecordData['original_forwarder']."'");
								//print_r($select->__toString());die;
		$serviceprice = $this->getAdapter()->fetchRow($select);
		$this->RecordData[CUSTOMER_PRICE] = $serviceprice[CUSTOMER_PRICE];
		$this->RecordData[DEPOT_PRICE]	= $serviceprice[DEPOT_PRICE];
		$configCustomer = $this->CustomerConfig($this->RecordData['user_id']);
		if($configCustomer['special_price']=='1'){
		   $specialPrice = $this->getSpecialPrice($serviceprice,$this->RecordData['user_id']);
		   if($specialPrice>0){
		     $this->RecordData[CUSTOMER_PRICE] = $specialPrice;
		   }
		}//echo "<pre>";print_r($this->RecordData);die;
	}
	public function getSpecialPrice($data,$user_id){
	   $select = $this->_db->select()
								->from(array('RSP'=>ROUTING_SPECIAL_PRICE),array('special_price'))
								->where("routing_id='".$data[ROUTING_ID]."' AND service_id='".$data[SERVICE_ID]."' AND user_id='".$user_id."'");
								//print_r($select->__toString());die;
		$specialprice = $this->getAdapter()->fetchRow($select);
		return isset($specialprice['special_price'])?$specialprice['special_price']:0;
	}
	public function CustomerConfig($user_id){
	   $select = $this->_db->select()
						  ->from(array('UD'=>USERS_DETAILS),array('*'))
						  ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=UD.user_id",array('*'))
						  ->where('UD.user_id=?', $user_id);
	   return $this->getAdapter()->fetchRow($select);					  
	}
	
	public function CompanyName($user_id){
	   try{
	    $select = $this->_db->select()
                ->from(array('AT'=>USERS_DETAILS), array('AT.company_name'))
				->where("AT.user_id='".$user_id."'");//print_r($select->__toString());die;
       }catch(Exception $e){
	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	   }
	   $userdetails =  $this->getAdapter()->fetchRow($select);
	   return $userdetails[COMPANY_NAME];
	}
	public function getServices(){
	   try{
	    $select = $this->_db->select()
                ->from(array('SV'=>SERVICES), array('*'))
				->where("SV.status='1'");//print_r($select->__toString());die;
		  return  $this->getAdapter()->fetchAll($select);		
       }catch(Exception $e){
	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	   }
	  
	}
   public function getServiceDetails($cond_id,$check=1){
       try{
	    $select = $this->_db->select()
                ->from(array('SV'=>SERVICES), array('*'));
				switch($check){
					case 1:
					 $select->where('SV.service_id=?',$cond_id);
					break;
					case 2:
					  $select->where('SV.internal_code=?',$cond_id);
					break;
					case 3:
					  $select->where('LOWER(SV.service_name)=?',$cond_id);
					break;
				  default:
					 $select->where('SV.service_id=?',$cond_id);
				  break;	
				}
		  return  $this->getAdapter()->fetchRow($select);		
       }catch(Exception $e){
	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	   }
	   
   }
   public function CheckIN($barcode_id,$type){
        $select = $this->_db->select()
                ->from(array('BT'=>SHIPMENT_BARCODE), array('barcode_id'))
				->joininner(array('ST'=>SHIPMENT),"BT.shipment_id=ST.shipment_id",array('country_id','rec_name','rec_contact','rec_street','rec_streetnr','rec_address','rec_street2','rec_zipcode','rec_city','rec_phone','rec_email','addservice_id'))
				->where("BT.barcode_id='".$barcode_id."'");
		$ShipBarc = $this->getAdapter()->fetchRow($select);
		
		$select = $this->_db->select()
                ->from(array('CL'=>SHIPMENT_BARCODE_LOG), array('COUNT(1) AS CNT'))
				->where("CL.barcode_id='".$barcode_id."'");
		$check_checkedin = $this->getAdapter()->fetchRow($select);
		
		$dataarray = array('barcode_id'=>$ShipBarc['barcode_id'],'country_id'=>$ShipBarc['country_id'],'rec_name'=>$ShipBarc['rec_name'],'rec_contact'=>$ShipBarc['rec_contact'],'rec_street'=>$ShipBarc['rec_street'],'rec_streetnr'=>$ShipBarc['rec_streetnr'],'rec_address'=>$ShipBarc['rec_address'],'rec_street2'=>$ShipBarc['rec_street2'],'rec_zipcode'=>$ShipBarc['rec_zipcode'],'rec_city'=>$ShipBarc['rec_city'],'rec_phone'=>$ShipBarc['rec_phone'],'rec_email'=>$ShipBarc['rec_email'],'addservice_id'=>$ShipBarc['addservice_id']);
		
		if($check_checkedin['CNT']>0){
		     $execute =  $this->_db->update(SHIPMENT_BARCODE_LOG,$dataarray,"barcode_id='".$ShipBarc['barcode_id']."'");
		}else{
		     $execute = $this->_db->insert(SHIPMENT_BARCODE_LOG,array_filter($dataarray));
		}
		if($execute){
		   $this->_db->update(SHIPMENT_BARCODE,array('checkin_status'=>'1'),"barcode_id='".$ShipBarc['barcode_id']."'");
		   $this->_db->update(SHIPMENT_BARCODE_DETAIL,array('checkin_date'=>new Zend_Db_Expr('NOW()'),'checkin_by'=>$this->Useconfig['user_id'],'checkin_ip'=>commonfunction::loggedinIP(),'checkin_type'=>$type),"barcode_id='".$ShipBarc['barcode_id']."'"); 
		   return true; 
		}else{
		  return false;
		}	
   }
   public function getDynamicField($notification_id){
      try{
	    $select = $this->_db->select()
                ->from(array('MD'=>MAIL_DYNAMIC_FIELD), array('*'))
				->where("FIND_IN_SET(".$notification_id.",MD.notification_id)")
				->order("id ASC");//print_r($select->__toString());die;
		$dynamicfields =   $this->getAdapter()->fetchAll($select);
       }catch(Exception $e){
	      $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	   }
	   $fields = array();
	   foreach($dynamicfields as $dynamicfield){
	     $fields[]  = $dynamicfield['fieldname'];
	   } 
	   return $fields;
   }
   public function getEmailData(){
	     try{
		 $select = $this->_db->select()
						  ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id','barcode','reference_barcode','customer_price','weight','forwarder_id','tracenr_barcode'))
						  ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID,array('checkin_date','rec_reference'))
						  ->joininner(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array(RECEIVER,STREET,STREETNR,ADDRESS,CITY,'addservice_id','email_notification as EMAIL','ST.user_id','ST.rec_zipcode','ST.country_id','ST.senderaddress_id','ST.rec_email'))
						  ->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.user_id',array(COMPANY_NAME,'parent_id'))
						  ->joininner(array('CT'=>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID,array('country_name'))
						  ->joininner(array('FT'=>FORWARDERS),'FT.'.FORWARDER_ID.'=ST.'.FORWARDER_ID,array('forwarder_name'))
						  ->joininner(array('GT'=>SERVICES),'GT.'.SERVICE_ID.'=ST.'.SERVICE_ID,array('service_name'))
						  ->where("BT.barcode_id='".$this->getData['barcode_id']."'");//print_r($select->__toString());die;
		 $result = $this->getAdapter()->fetchRow($select);
		}catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); }
		 $this->RecordData[ADMIN_ID] = $result[ADMIN_ID];
		 $this->RecordData[COUNTRY_ID] = $result[COUNTRY_ID];
		 $this->RecordData['senderaddress_id'] = $result['senderaddress_id'];
		 $this->RecordData['forwarder_id'] = $result[FORWARDER_ID];
		 $forwarderInfo = $this->ForwarderDetail();
		 if($result[FORWARDER_ID]<=4 || $result[FORWARDER_ID]==23 || $result[FORWARDER_ID]==26 || $result[FORWARDER_ID]==32){
		  $trackingno = $result[TRACENR_BARCODE];
		 }else{
		  $trackingno = $result[BARCODE]; 
		 }//echo "<pre>";print_r($forwarderInfo);die;
		 $senderlogo = '';
		 $devivery_time = 'test';
		  $trackingurl = "<a href='".BASE_URL.'Parceltracking/tracking/tockenno/'.Zend_Encript_Encription::encode($this->getData['barcode_id'])."'>Trace Shipment</a>" ;
	      return array('SenderEmail'=>$forwarderInfo['SenderAddress'][9],
						 'ReceiverEmail'=>$result[EMAIL],
						 'SenderName'=>utf8_decode($forwarderInfo['SenderAddress'][0]),
						 'ReceiverName'=>utf8_decode($result[RECEIVER]),
						 'notification_id'=>2,
						 ADMIN_ID=>$result[ADMIN_ID],
						 COUNTRY_ID=>$result['country_id'],
						 PARENT_ID=>$result['parent_id'],
						 'Dynamic'=>array(utf8_decode($result[RECEIVER]),$result[STREET].' '.$result[STREETNR].' '.$result[ADDRESS],utf8_decode($result[CITY]),
						 				  $result['country_name'],$result[WEIGHT],1,$result['service_name'],$trackingno,$trackingurl,
										  $result['forwarder_name'],$result[COMPANY_NAME],utf8_decode($result[REFERENCE]),$result[ZIPCODE],
										  utf8_decode($forwarderInfo['SenderAddress'][0]),$senderlogo,substr($devivery_time,26)));		 				  
	}
   
   public function getWeightScaleList(){
         $select = $this->_db->select()
                ->from(array('MD'=>WEIGHT_SCALER_INFO), array('*'))
				->where("is_delete='0'");//print_r($select->__toString());die;
	  return $this->getAdapter()->fetchAll($select);
   }
   /**
	*Faetch All Languages
	*Function : AdminLanguage()
	*Fetch All languages of application
	**/
	public function AdminLanguage($tokenkeys=0){
		if($tokenkeys!='0'){
			$languageID = Zend_Encript_Encription::decode($tokenkeys);
			$where = 'language_id='.$languageID;
		}else{
			$where = 1;
		}
		
		$select = $this->_db->select()
							->from(LANGUAGE,array('*'))
							->where($where);
		//echo $select->__toString();die;
		$result = $this->getAdapter()->fetchAll($select);
		return $result; 
	}
	
	/**
     * Return list of hubusers
	 * Function : Gethubuserlist()
     * @param  null
     * @return array
     */
	public function Gethubuserlist(){
		
		$select = $this->_db->select()
				->from(array('UT'=>USERS), array('*'))
				->joininner(array('UD'=>USERS_DETAILS), 'UT.user_id=UD.user_id', array('*'))
				->where('UT.level_id=7')
				->where('UT.delete_status=?',"0")
				->order('UD.company_name');	//echo $select->__tostring(); //die;
		$result = $this->getAdapter()->fetchAll($select);
		return $result;
	}
	public function getGoodsDetail($goods_id='D'){
	     $select = $this->_db->select()
				->from(array('GC'=>GOODS_CATEGORY), array('*'))
				->where('GC.goods_id=?',($goods_id!='')?$goods_id:'D');	//echo $select->__tostring(); //die;
		 $result = $this->getAdapter()->fetchRow($select);
		return $result['goods_name'];
	}
	public function ServiceName($service_id){
	    try{ 
		 $select = $this->_db->select()
                ->from(array('SV'=>SERVICES), array('*'));
		 $select->where('SV.service_id=?',$service_id);
       }catch(Exception $e){
	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	   }
	   $service =   $this->getAdapter()->fetchRow($select);
	   return $service['service_name'];
	}
	public function ForwarderName($forwarder_id,$full_record=false){
	    try{ 
		 $select = $this->_db->select()
                ->from(array('FT'=>FORWARDERS), array('*'));
		 $select->where('FT.forwarder_id=?',$forwarder_id);
       }catch(Exception $e){
	     $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	   }
	   $forwarder =   $this->getAdapter()->fetchRow($select);
	   return ($full_record)?$forwarder:$forwarder['forwarder_name'];
	}
  public function getShipmentSeriesno($tracenr){ 
	  return commonfunction::paddingleft(rand(100000, 999999).commonfunction::sub_string(commonfunction::str_reverse($tracenr),0,4).commonfunction::sub_string(commonfunction::onlynumbers(microtime(true)),9,5),15,0);
  }
  public function ValidateZipcode($zipcode,$country_id){
      $removestr = array("NL-", "AT-", "BE-", "FR-","ES-","DE-","B-", "A-", "D-", "E-", "F-", "L-","N-",".","-");
	  $zipcode = commonfunction::stringReplace($removestr,"",$zipcode);
      $alphanumeric_zipcde = commonfunction::Alphanumeric($zipcode);
	  $countryDetails  = $this->getCountryDetail($country_id,1);
	  if($countryDetails['postcode_length']>0){
	      $onlyconsonants = commonfunction::paddingleft($alphanumeric_zipcde,$countryDetails['postcode_length'],0);
		  $zipcode = commonfunction::sub_string($onlyconsonants,-$countryDetails['postcode_length'],$countryDetails['postcode_length']);
	  }
	return  $zipcode;  
  }
  public function getLatLong($countryCode,$zipcode_city){
      $countryDetails = $this->getCountryDetail($countryCode,2);
	  $select = $this->masterdb->select()
                ->from(array('LL'=>CITY_LAT_LNG), array('latitude','longitude'))
				->where("LL.country_id='".$countryDetails['country_id']."' AND LL.city_zipcode='".$zipcode_city."'");
	  $latlang =   $this->masterdb->fetchRow($select);
	  if(empty($latlang)){
			$url ="http://maps.googleapis.com/maps/api/geocode/json?address=".$zipcode_city."+".$countryDetails['country_name']."&sensor=false";	
			$geocode	= file_get_contents($url); 
			$output 	= json_decode($geocode);
			if(!empty($output->results[0]->geometry->location->lat)){		
				$latlang['latitude'] 	= $output->results[0]->geometry->location->lat;
				$latlang['longitude'] 	= $output->results[0]->geometry->location->lng;
				$this->masterdb->insert(CITY_LAT_LNG,array('latitude'=>$latlang['latitude'],'longitude'=>$latlang['longitude'],'city_zipcode'=>$zipcode_city,'country_id'=>$countryDetails['country_id']));
				return $latlang;
			}
	  }
	  return $latlang;			
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
								->order(array('service_name ASC'));
		
		   }catch(Exception $e){
		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		}
    return  $this->getAdapter()->fetchAll($select);
 }	
 
  public function LevelClause(){
	     $where = '';
		switch($this->Useconfig['level_id']){
		   case 4:
		   	  $where = " AND AT.parent_id='".$this->Useconfig['user_id']."'";
		   break;
		   case 5:
		   	  $where = " AND AT.user_id='".$this->Useconfig['user_id']."'";
		   break;
		   case 6:
		      $parent_id = $this->getDepotID($this->Useconfig['user_id']);
		   	  $where = " AND AT.parent_id='".$parent_id."'";
		   break;
		   case 10:
		   	  $parent_id = $this->getDepotID($this->Useconfig['user_id']);
		   	  $where = " AND AT.user_id='".$parent_id."'";
		   break;
		}
		return $where;
	}
	
	public function LevelAsDepots(){
	     $where = '';
		switch($this->Useconfig['level_id']){
		   case 4:
		   	  $where = " AND AT.user_id='".$this->Useconfig['user_id']."'";
		   break;
		   case 6:
		      $parent_id = $this->getDepotID($this->Useconfig['user_id']);
		   	  $where = " AND AT.user_id='".$parent_id."'";
		   break;
		   default:
		   $where = " AND AT.parent_id='".$this->Useconfig['user_id']."'";
		}
		return $where;
	}
	
}
?>