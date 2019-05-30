<?php
 
class Seafreight_Model_Searfreight extends Zend_Custom
{
    public function getPortList($port_id=false){
	   if(isset($this->getData['country_id']) && $this->getData['country_id']>0){
	   $select = $this->_db->select()
	   					->from(array('PT'=>COUNTRYPORT),array('*'))
						->where("PT.country_id='".$this->getData['country_id']."'");
		if($port_id){
		   return $this->getAdapter()->fetchRow($select);
		}				
		return $this->getAdapter()->fetchAll($select);	
	  }else{
	    return array();
	  }
	}
	
	public function AddRouting(){
	   //echo "<pre>";print_r($this->getData);die;
	   global $objSession;
	   $this->getData['user_id']  = Zend_Encript_Encription::decode($this->getData['user_id']);
	   foreach($this->getData['port'] as $key=>$port_id){
	      $select = $this->_db->select()
									->from(array('SF'=>SEAFREIGHT_ROUTING),array('COUNT(1) AS CNT'))
									->where("SF.country_id='".$this->getData['country_id']."' AND SF.des_port='".$port_id."' AND SF.user_id ='".$this->getData['user_id']."'")
									->where("((SF.min_weight=".$this->getData['min_weight']." AND SF.max_weight=".$this->getData['max_weight'].") OR  (SF.min_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001).") OR (SF.max_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001)."))");
				 //echo $select->__toString();die;
		$validate = $this->getAdapter()->fetchRow($select);
		$portDetails = $this->getPortList($port_id);
		if($validate['CNT']<=0){
		  $this->_db->insert(SEAFREIGHT_ROUTING,
		  							array_filter(array(
										'user_id'=>$this->getData['user_id'],
										'country_id'=>$this->getData['country_id'],
										'des_port'=>$port_id,
										'min_weight'=>$this->getData['min_weight'],
										'max_weight'=>$this->getData['max_weight'],
										'depot_price'=>$this->getData['price'][$key],
										'dep_import_surcharge'=>$this->getData['import_sercharge'][$key],
										'dep_destination_charge'=>$this->getData['destination_sercharge'][$key],
										'remark'=>$this->getData['remark'])));
		 $objSession->successMsg .= 'Routing added for Port '.$portDetails['port_name'];
		}else{
		  $objSession->errorMsg .= 'Routing already added for Port '.$portDetails['port_name'];
		}
	   }
	}
	
	public function EditRouting(){
	   global $objSession;
	   //$this->getData['user_id']  = Zend_Encript_Encription::decode($this->getData['user_id']);
	   $select = $this->_db->select()
									->from(array('SF'=>SEAFREIGHT_ROUTING),array('COUNT(1) AS CNT'))
									->where("SF.country_id='".$this->getData['country_id']."' AND SF.des_port='".$this->getData['port']."' AND SF.user_id ='".$this->getData['user_id']."'")
									->where("((SF.min_weight=".$this->getData['min_weight']." AND SF.max_weight=".$this->getData['max_weight'].") OR  (SF.min_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001).") OR (SF.max_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001).")) AND SF.routing_id!='".$this->getData['routing_id']."'");
				 //echo $select->__toString();die;
		$validate = $this->getAdapter()->fetchRow($select);
		$portDetails = $this->getPortList($port_id);
		if($validate['CNT']<=0){
		  $this->_db->update(SEAFREIGHT_ROUTING,
		  							array(
										'des_port'=>$this->getData['port'],
										'depot_price'=>$this->getData['price'],
										'dep_import_surcharge'=>$this->getData['import_sercharge'],
										'dep_destination_charge'=>$this->getData['destination_sercharge']),"routing_id='".$this->getData['routing_id']."'");
		 $objSession->successMsg .= 'Routing Updated for Port '.$portDetails['port_name'];
		}else{
		  $objSession->errorMsg .= 'Routing already added for Port '.$portDetails['port_name'];
		}
	}
	
	public function getRoutingList(){
	   $where = $this->LevelAsDepots();
		   if(isset($this->getData['country_id']) && $this->getData['country_id']>0){
			  $where .= " AND SF.country_id='".$this->getData['country_id']."'";
		   }
		   if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){
			 $where .= " AND SF.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";
		   }
		   
		  $select = $this->_db->select()
	   					->from(array('SF'=>SEAFREIGHT_ROUTING),array('COUNT(1) AS CNT'))
						->joininner(array('CT'=>COUNTRIES),"CT.country_id=SF.country_id",array(''))
						->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=SF.user_id",array(''))
						->where("1".$where);
		$total = $this->getAdapter()->fetchRow($select);				
		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'AT.company_name','ASC');				  
        $select = $this->_db->select()
	   					->from(array('SF'=>SEAFREIGHT_ROUTING),array('*'))
						->joininner(array('CT'=>COUNTRIES),"CT.country_id=SF.country_id",array('country_name'))
						->joininner(array('PT'=>COUNTRYPORT),"PT.port_id=SF.des_port",array('port_name'))
						->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=SF.user_id",array('company_name'))
						->where("1".$where)
						->order("SF.max_weight ASC")
						->order("SF.min_weight ASC")
						->order("CT.country_name ASC")
						->order("AT.company_name ASC")
						->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
		  				->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
						//echo $select->__toString();die;
		$result =  $this->getAdapter()->fetchAll($select);
		return array('Total'=>$total['CNT'],'Records'=>$result);
	}
	
	public function getRoutingDetail(){ 
	    $select = $this->_db->select()
	   					->from(array('SF'=>SEAFREIGHT_ROUTING),array('*'))
						->joininner(array('PT'=>COUNTRYPORT),"PT.port_id=SF.des_port",array('port_name'))
						->where("routing_id='".$this->getData['routing_id']."'");
						//echo $select->__toString();die;
		return $this->getAdapter()->fetchRow($select);
	}
	
	public function UpdatePrice($update_type){
	    global $objSession;
	   if($update_type==1){
	      $this->_db->update(SEAFREIGHT_ROUTING,
		  							array(
										'customer_price'=>$this->getData['price'],
										'cus_import_surcharge'=>$this->getData['import_sercharge'],
										'cus_destination_surcharge'=>$this->getData['destination_sercharge']),"routing_id='".$this->getData['routing_id']."'");
		 $objSession->successMsg .= 'Routing price updated!!';
	   }
	   if($update_type==2){
	      
		  $this->_db->update(SEAFREIGHT_ROUTING,
		  							array(
										'special_price'=>$this->getData['special_price'],
										'import_surcharge'=>$this->getData['import_surcharge'],
										'destination_surcharge'=>$this->getData['destination_surcharge']),"routing_id='".$this->getData['routing_id']."'");
		 $objSession->successMsg .= 'Routing price updated!!';
	   }
	}
	
	public function getSpecialPriceCustomers(){
       $select = $this->_db->select()
						  ->from(array('UT'=>USERS),array('*'))
						  ->joininner(array('UD'=>USERS_DETAILS),"UT.user_id=UD.user_id",array('user_id','company_name','postalcode','city'))
						  ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=UD.user_id",array(''))
						  ->where('UT.user_status=?', '1')
						  ->where('UT.delete_status=?', '0')
						  ->where('UT.level_id=?', 5)
						  ->where('US.special_price=?', 1);
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

}

