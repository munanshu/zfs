<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 *
 * @category   Application_Model
 * @version    1.12.20
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/*

 * Model Pickupmanager of Logicparcel ERP System

 * @PHP version  : 5.6

 * @ZEND version : 1.12.20

 * @author       : SJM Softech Private Limited <contact@sjmsoftech.com>

 * @created on   : 22 Oct 2016

 * @link         : http://www.dpost.be
 
 * @description : Handles shipment pickup, shipment return, shipment delivery tracking 
 
 */
class Application_Model_Pickupmanager extends Zend_Custom
{

 /**
  * Returns pickuprequests 
  *
  * Returns shipments to be delivered
  *
  * @param array $request
  *
  * @return array
  */
  public function pickuprequest($request){
        //global $objSession;
	
	$userconfig = $this->Useconfig;
	
	$where = "1";
	if($userconfig['level_id'] == 4){
	    $where .= " AND AT.parent_id=" . $userconfig['user_id'];
	}
	elseif($userconfig['level_id'] == 5){
	    $where .= " AND AT.user_id=" . $userconfig['user_id'];
	}
	elseif($userconfig['level_id'] == 6){
	    $depot_id = $this->getDepotID($userconfig['user_id']);
	    $where .= ' AND AT.parent_id= ' . $depot_id;
	}
	if(!empty($request['user_id'])){
	    $where .= " AND AT.user_id='" . addslashes($request['user_id']) . "'";
	}
	 
	
	try {
	$select = $this->getAdapter()->select()->
	          from(array('ST' => SHIPMENT), 
	           array('create_date' => 'DATE_FORMAT(ST.create_date, "%Y-%m-%d")'))
	          ->joininner(array('BT'=>SHIPMENT_BARCODE), 'ST.shipment_id = BT.shipment_id', array('total_weight'=>'SUM(BT.weight)', 'total_quantity'=> 'COUNT(1)'))
		  ->joininner(array('AT'=>USERS_DETAILS), 'At.user_id=ST.user_id', array('company' => 'company_name', 'user_id'=>'user_id', 
		  'customer_address' => "CONCAT(AT.company_name, '^', AT.address1, '^', AT.address2, '^', AT.postalcode, '^', AT.city, '^', CT.country_name)",
		  'pickup_date' => 'CURDATE()'
		  )) 
		  ->joininner(array('CT'=> COUNTRIES), 'CT.country_id = AT.country_id', array())
		  ->joininner(array('GST'=>SERVICES), 'GST.service_id = ST.service_id', array('express' => 'express'))
		  
		  ->joinleft(array('SPT'=>'parcel_schedulepickup'), 'SPT.user_id=ST.user_id', array())
		  ->joinleft(array('MPT' =>'parcel_manualpickup'), 'MPT.manual_pickup_id=BT.manual_pickup_id',    array('pickup_address'=>"if(MPT.manual_pickup_id>0,CONCAT(MPT.name,'^',MPT.street1,'^',MPT.street2,'^',MPT.zipcode,'^',MPT.city,'^',MPT.country),if(SPT.user_id>0 && SPT.name!='',CONCAT(SPT.name,'^',SPT.street1,'^',SPT.street2,'^',SPT.zipcode,'^',SPT.city,'^',SPT.country),CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name)))",
		  "pickup_date"=>"if(MPT.manual_pickup_id>0 && MPT.pickup_date !='00-00-00',MPT.pickup_date, if(SPT.user_id>0 && (" .strtolower(date('D')) . "_start != '00-00-00' || default_time_start != '00-00-00'), CURDATE(), CURDATE()))",
		  "pickup_time"=>"if(SPT.user_id>0 && " . strtolower(date('D')) . "_start != '00-00-00' , " . strtolower(date('D')). "_start, if(default_time_start !='00-00-00', default_time_start, '00-00-00'))"
		  ))
		  
		  ->group(array('AT.user_id', 'DATE_FORMAT(ST.create_date, "%d-%m-%Y")'))
		  ->where("BT.checkin_status='0' AND BT.pickup_status=0 AND BT.show_planner='1'")
		  ->where('ST.create_date > NOW() - INTERVAL 3 MONTH')
		  ->where($where);
	
	$records = $this->getAdapter()->fetchAll($select); 
	return $records;
	} catch(Exception $e){ echo $e->getMessage(). $e->getFile(). $e->getLine();}
	
  }
  

 /**
  * Returns shipments with manual pickups (without barcodes) 
  *
  * @param array $request, array $records
  *
  * @return array
  */  
  public function Customermanualpickupdate($request, $records){
        $userconfig = $this->Useconfig;
        
	$where = '';
	if($userconfig['level_id'] == 4){
	    $where .= " AND AT.parent_id=" . $userconfig['user_id'];
	}
	elseif($userconfig['level_id'] == 5){
	    $where .= " AND AT.user_id=" . $userconfig['user_id'];
	}
	elseif($userconfig['level_id'] == 6){
	    $depot_id = $this->getDepotID($userconfig['user_id']);
	    $where .= ' AND AT.parent_id= ' . $depot_id;
	}
	if(!empty($request['user_id'])){
	    $where .= " AND AT.user_id='" . addslashes($request['user_id']) . "'";
	}
	 
	 $user_id = array();
	 if(!empty($records)){
	     foreach($records as $record){
	         $user_id[] = $record['user_id'];
	     }
	     
	     $where .= " AND AT.user_id NOT IN('" . implode("','", $user_id) . "')";	 
	 }
	 
	try{
	
	   $select = $this->getAdapter()->select()
	           ->from(array('MPT'=>'parcel_manualpickup'), array('total_weight'=>'manual_weight', 'total_quantity'=>'manual_quantity', 'create_date', 'parcel_type'=>'if(manual_pickup_id, 2,2)', 'manual_pickup_id'=>'manual_pickup_id'))
		   ->joininner(array('AT'=>'parcel_users_detail'), 'AT.user_id=MPT.user_id', array('company'=>'company_name', 'admin_id'=>'user_id', 'parent_id','customer_address'=> "CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name)"))
		   ->joininner(array('CT'=>'parcel_countries'), 'AT.country_id=CT.country_id', array(''))
		   ->joinleft(array('SPT'=>'parcel_schedulepickup'), 'SPT.user_id=AT.user_id', array('pickup_address'=> "if(CONCAT(MPT.name, '^', MPT.zipcode, '^', MPT.city)!='^^^', CONCAT(MPT.name, '^', MPT.street1, '^', MPT.street2, '^', MPT.zipcode, '^', MPT.city, '^', MPT.country), if(SPT.user_id>0 && SPT.name !='', CONCAT(SPT.name, '^', SPT.street1, '^', SPT.street2, '^', SPT.zipcode, '^', SPT.city, '^', SPT.country), CONCAT(AT.company_name,'^', AT.address1, '^', AT.address2, '^', AT.postalcode, '^', AT.city, '^', CT.country_name)))",
		   'pickup_date'=>"if(MPT.pickup_date !='00-00-00', MPT.pickup_date, if(SPT.user_id>0 && (" . strtolower(date('D')) .  "_start != '00:00:00'|| default_time_start != '00-00-00'),CURDATE(),CURDATE()))",
		   'pickup_time'=>"if(SPT.user_id>0 && " . strtolower(date('D')) . "_start != '00:00:00'," . strtolower(date('D')) . "_start, if(default_time_start !='00-00-00', default_time_start, '00-00-00'))"
		   ) )
		   ->where('MPT.create_date > NOW() - INTERVAL 7 DAY AND DATE(MPT.pickup_date)>= CURDATE()' . $where)
		   ->group("AT.user_id");
	//echo $select->__toString();die;
	   $result = $this->getAdapter()->fetchAll($select);
	   
	   return $result;
	} catch(Exception $e){
	
	  echo $e->getMessage();
	
	}
	
  } 
  
  

 /**
  * Returns shipment returns (to be returned ... barcodes)
  *
  * @param void()
  *
  * @return array
  */
  public function returnShipmentList(){
  
       try{
         $userconfig = $this->Useconfig;
	 
         $where = "";
	 if($userconfig['level_id'] == 4){
	     $where .= " AND AT.parent_id = " . $userconfig['user_id'];
	 }
	 elseif($userconfig['level_id'] == 5){
	     $where .= " AND AT.user_id = " . $userconfig['user_id'];
	 }
	 elseif($userconfig['level_id'] == 6){
	     $depot_id = $this->getDepotID($userconfig['user_id']);
	     $where .= " AND AT.parent_id = " . $depot_id;;
	 }
	 
	 //some request parameters inclusion (though ui ?) where.=(for filtering by forwarder_id or country_id of Shipment)
	 
	 
	 $orderlimit = ''; //define this using common function orderby ordertype toshow limit
	 
	 $select = $this->getAdapter()->select()
	        ->from(array('Shipment'=>SHIPMENT), array('COUNT(1) AS CNT'))
		->joinleft(array('AT'=>USERS_DETAILS), "AT.user_id=Shipment.user_id", array())
		->where('Shipment.return_shipment="0" AND Shipment.quantity>(SELECT COUNT(1) AS TCNT  FROM parcel_shipment_barcodes AS BT WHERE BT.shipment_id=Shipment.shipment_id)' . $where)
		->order('Shipment.shipment_id');  //make it dynamic .. return_shipment="1" should be

	 $total = $this->getAdapter()->fetchRow($select);
	 
	 $select = $this->getAdapter()->select()
	        ->from(array('Shipment'=>SHIPMENT))
		->joinleft(array('AT' => USERS_DETAILS), "AT.user_id=Shipment.user_id", array())
		->where("Shipment.return_shipment='0 ' AND Shipment.quantity>(SELECT COUNT(1) TCNT FROM parcel_shipment_barcodes as BT where BT.shipment_id=Shipment.shipment_id)" . $where)
		->order('Shipment.shipment_id')
		->limit(50,0);  //make these order and limit dynamic
		
	 $result = $this->getAdapter()->fetchAll($select);
		
	 
        } catch(Exception $e){
	   echo $e->getMessage();
	}
	
	return array('Total'=> $total['CNT'], 'Records'=> $result, 'Toshow'=> 50, 'Offset' => 0);
  //Toshow and offset should be dynamic
  }
  


 /**
  * Returns checked in shipments (shipments with barcodes)
  *
  * @param int $shipment_id
  *
  * @return string (number)
  */
  public function getCheckedQuantity($shipment_id){
         
        $select = $this->getAdapter()->select()
	        ->from(array(SHIPMENT_BARCODE), array('COUNT(1) AS CNT'))
		->where("shipment_id=" . addslashes($shipment_id));
	$quantity = $this->getAdapter()->fetchRow($select);
	return $quantity['CNT'];
  
  }
  
  
 /**
  * Return delivery time for shipments
  *
  * @param array $data
  *
  * @return array
  */
   public function getdeliverytime($data){
       try{
        $data['postalcode'] = str_replace(" ", "", $data['postalcode']);
	$select = $this->getAdapter()->select()
	        ->from(array('dt' =>'parcel_deliverytime') , array('*',
       		 new Zend_Db_Expr("(SELECT delivery_time FROM parcel_deliverytime WHERE country_id='" . addslashes($data['country_id']) . "' AND forwarder_id='" . addslashes($data['forwarder_id']) . "' AND dt.delivery_zipcode=delivery_zipcode ORDER BY delivery_id DESC limit 1) as last_delivery") 
		  )
		 )
		->where("country_id='" . addslashes($data['country_id']) ."'")
		->where("forwarder_id='" . addslashes($data['forwarder_id']) . "'")
		->where("delivery_zipcode LIKE '" . addslashes($data['postalcode']) . "%'")
		->group("TIME_FORMAT(delivery_time, '%H:%i')")
		->group("delivery_zipcode")
		->order("delivery_id DESC");
		//echo $select->__toString(); die;
	$result = $this->getAdapter()->fetchAll($select);
	
	return $result;
   
        }catch(Exception $e){
	  echo $e->getMessage();
	}
	
   }
  
  

}

