<?php

class Application_Model_Dashboard extends Zend_Custom
{
   public function AddedParcel(){
        $where = $this->LevelClause();
		$select = $this->_db->select()
									->from(array('BT' =>SHIPMENT_BARCODE),array("COUNT(1) AS CNT"))
									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(""))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array(""))
									->where("BT.checkin_status='0' AND BT.delete_status='0' AND ST.delete_status='0' AND DATE(ST.create_date)= CURDATE()".$where);
									//print_r($select->__toString());die;
		$count = $this->getAdapter()->fetchRow($select);
		return $count['CNT'];
   }
   public function AddedFreight(){
        $where = $this->LevelClause();
		$select = $this->_db->select()
									->from(array('BT' =>SHIPMENT_BARCODE),array("COUNT(1) AS CNT"))
									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(""))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array(""))
									->where("BT.checkin_status='0' AND BT.delete_status='0' AND ST.delete_status='0' AND DATE(ST.create_date)= CURDATE() AND BT.service_id=6".$where);
									//print_r($select->__toString());die;
		$count = $this->getAdapter()->fetchRow($select);
		return $count['CNT'];
   }
    public function AddedExpress(){
        $where = $this->LevelClause();
		$select = $this->_db->select()
									->from(array('BT' =>SHIPMENT_BARCODE),array("COUNT(1) AS CNT"))
									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(""))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array(""))
									->where("BT.checkin_status='0' AND BT.delete_status='0' AND ST.delete_status='0' AND DATE(ST.create_date)= CURDATE() AND ST.addservice_id IN(5,101,102,103,104,105,106,147,151,152,153,154,155,156)".$where);
									//print_r($select->__toString());die;
		$count = $this->getAdapter()->fetchRow($select);
		return $count['CNT'];
   }
   public function DeliveredParcel(){
        $where = $this->LevelClause();
		$select = $this->_db->select()
									->from(array('BT' =>SHIPMENT_BARCODE),array("DATE(BD.delivery_date)","COUNT(1) AS CNT"))
									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(""))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array(""))
									->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array(""))
									->where("BT.checkin_status='1' AND BT.delivery_status='1' AND BT.delete_status='0' AND ST.delete_status='0' AND DATE(BD.delivery_date) BETWEEN  CURDATE() - INTERVAL 1 DAY AND CURDATE()".$where)
									->group("DATE(BD.delivery_date)")
									->order("DATE(BD.delivery_date) DESC");
									//print_r($select->__toString());die;
		$count = $this->getAdapter()->fetchAll($select);
		return $count;
   }
   public function ErrorParcel(){
        $where = $this->LevelClause();
		$select = $this->_db->select()
									->from(array('BT' =>SHIPMENT_BARCODE),array("COUNT(1) AS CNT"))
									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(""))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array(""))
									->where("BT.checkin_status='1' AND BT.error_status='1' AND BT.delete_status='0' AND ST.delete_status='0'".$where);
									//print_r($select->__toString());die;
		$count = $this->getAdapter()->fetchRow($select);
		return $count['CNT'];
   }
   
   public function PlannedPickup(){
		$select = $this->_db->select()
									->from(array('DH' =>DRIVER_HISTORY),array('pickup_time','driver_id'))
									->joininner(array('DD'=>DRIVER_DETAIL_TABLE),"DH.driver_id=DD.driver_id",array("driver_name"))
									->where("DH.user_id='".$this->Useconfig['user_id']."' AND DH.assign_date= CURDATE()")
									->group("DH.user_id");
									//print_r($select->__toString());die;
		return $this->getAdapter()->fetchRow($select);
   }
   public function openInvoices(){
        $where = $this->LevelClause();
		$select = $this->_db->select()
									->from(array('IV' =>INVOICE),array('*'))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=IV.user_id",array(""))
									->where("IV.payment_status='0'".$where)
									->order("IV.invoice_date DESC")
									->limit(5);
									//print_r($select->__toString());die;
		return $this->getAdapter()->fetchAll($select);
   }
   public function openTickets(){
        $where = $this->LevelClause();
		$select = $this->_db->select()
									->from(array('HT' =>HELPDESK_TICKET),array('*'))
									->joininner(array('BT' =>SHIPMENT_BARCODE),"BT.barcode_id=HT.barcode_id",array("tracenr_barcode"))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=HT.user_id",array(""))
									->where("HT.delete_status='0' AND HT.user_id='".$this->Useconfig['user_id']."'")
									->order("HT.create_date DESC")
									->limit(5);
									//print_r($select->__toString());die;
		return $this->getAdapter()->fetchAll($select);
   }
   
   public function TransitParcel(){
       $where = $this->LevelClause();
	   $select = $this->_db->select()
								->from(array('BT' =>SHIPMENT_BARCODE),array("COUNT(1) AS CNT"))
								->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(""))
								->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array(""))
								//->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array(""))
								->where("BT.checkin_status='1' AND BT.delivery_status='0' AND BT.error_status='0' AND BT.delete_status='0' AND ST.delete_status='0'".$where);
									//print_r($select->__toString());die;
		$count = $this->getAdapter()->fetchRow($select);
		return $count['CNT'];
   }
   
   public function Assignedpickup(){
	     $this->_db->query("SET SESSION group_concat_max_len = 1000000");
	     $accesslevel = $this->LevelClause();
		 $select = $this->_db->select()
		               ->from(array('BT'=>SHIPMENT_BARCODE), array('COUNT(1) AS total_quantity','GROUP_CONCAT(BT.service_id) AS freight'))
					   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array("GROUP_CONCAT(ST.addservice_id) AS express"))
					   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array())
					   ->joininner(array('DH'=>DRIVER_HISTORY),"DH.barcode_id=BT.barcode_id AND DH.pickup_date = CURDATE() AND DH.parcel_type=1",array('pickup_status'))
					   ->joininner(array('AT'=>USERS_DETAILS),"DH.user_id=AT.user_id",array('company_name','user_id'))
					   ->joininner(array('DD'=>DRIVER_DETAIL_TABLE),"DD.driver_id=BD.driver_id",array('driver_name'))
					   ->where("BT.checkin_status='0' AND BD.assigned_date=CURDATE()".$accesslevel)
					   ->group(array('AT.user_id'))
					   ->order(array('DH.pickup_time ASC'));//print_r($select->__tostring());die;
		return  $this->getAdapter()->fetchAll($select);
	
   }
   
   public function PickedUpDetails(){
        $accesslevel = $this->LevelClause();
		 $select = $this->_db->select()
                    ->from(array("DPH" => DRIVER_PICKEDUP_HISTORY),array('SUM(total_quantity) AS total','SUM(express_quantity) AS express'))
                    ->joinInner(array("DD" =>DRIVER_DETAIL_TABLE),"DD.driver_id = DPH.driver_id", array("DD.driver_name",'DD.driver_id'))
                    ->joinInner(array("AT" => USERS_DETAILS),"AT.user_id = DPH.user_id", array("AT.company_name"))
                    ->where('DATE(DPH.pickup_datetime)=CURDATE()'.$accesslevel)
					->group("DPH.driver_id");//print_r($select->__tostring());die;
	    return $this->getAdapter()->fetchAll($select);
   }
   
    public function DriverPickupSummary(){
	     $this->_db->query("SET SESSION group_concat_max_len = 1000000");
	     $accesslevel = $this->LevelClause();
		 $select = $this->_db->select()
		               ->from(array('BT'=>SHIPMENT_BARCODE), array('COUNT(1) AS total_quantity','GROUP_CONCAT(BT.forwarder_id) AS forwarderwise'))
					   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array("GROUP_CONCAT(ST.addservice_id) AS express"))
					   ->joininner(array('DH'=>DRIVER_HISTORY),"DH.barcode_id=BT.barcode_id AND DH.pickup_date = CURDATE() AND DH.parcel_type=1",array('pickup_status','pickup_time'))
					   ->joininner(array('DPH'=>DRIVER_PICKEDUP_HISTORY),"DPH.driver_id=DH.driver_id AND DPH.user_id=DH.user_id AND DATE(pickup_datetime)=CURDATE()",array('pickup_datetime AS actual_pickup'))
					   ->joininner(array('AT'=>USERS_DETAILS),"DH.user_id=AT.user_id",array('company_name','user_id'))
					   ->joininner(array('DD'=>DRIVER_DETAIL_TABLE),"DD.driver_id=DPH.driver_id",array('driver_name','driver_id'))
					   ->where("BT.checkin_status='0' AND DH.pickup_status='1' AND DH.assign_date=CURDATE()".$accesslevel)
					   ->group(array('AT.user_id'))
					   ->order(array('DH.pickup_time ASC'));//print_r($select->__tostring());die;
		return  $this->getAdapter()->fetchAll($select);
	
   }
   
   public function shopAPiorders(){
      $shopobjects = new Application_Model_Shopapi();
	  $shoplists = $shopobjects->getShopList();
	  $totalorder = 0;
	  foreach($shoplists as $shoplist){
	    $shopobjects->getData = $shoplist;
		$shopobjects->getData['onlycount'] = 1;
		$totalorder =  $totalorder + $shopobjects->getOrderCount();
	  }
	  return $totalorder;
   }
}

