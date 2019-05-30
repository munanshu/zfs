<?php

class Shipmentmanager_Model_Shipmentmanager extends Planner_Model_Planner
{
	public function PickupRequest(){
				$filterString = '';
				if (isset($this->getData['filter_customer']) && $this->getData['filter_customer'] != '') {
					$filterString = ' AND ST.user_id=' . Zend_Encript_Encription:: decode($this->getData['filter_customer']);
				}

				if ((isset($this->getData['fromdate']) && $this->getData['fromdate'] != '') && (isset($this->getData['todate']) && $this->getData['todate'] != '')) {
					$filterString .=" AND ST.create_date >= '".$this->getData['fromdate']."' AND ST.create_date <= '".$this->getData['todate']."'";
				}
	   $accesslevel = $this->LevelClause();
	   $this->_db->query("SET SESSION group_concat_max_len = 1000000");
	   $select = $this->_db->select()
		               ->from(array('ST'=>SHIPMENT), array('DATE(ST.create_date) AS create_date','GROUP_CONCAT(DISTINCT DATE(ST.create_date)) AS Alldate','IF(BT.barcode_id,1,1) AS parcel_type'))
					   ->joininner(array('BT'=>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array('SUM(BT.weight) AS total_weight','COUNT(1) AS total_quantity','GROUP_CONCAT(DISTINCT BT.barcode_id) AS barcode_id'))
					   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array(''))
					   ->joininner(array('AT'=>USERS_DETAILS),"ST.user_id=AT.user_id",array('company_name','user_id'))
					   ->joinleft(array('ASP'=>USERS_SCHEDULE_PICKUP),'ASP.user_id=AT.user_id',array())
					   ->joininner(array('CT'=>COUNTRIES),'AT.country_id=CT.country_id',array('customer_address' => "CONCAT(ST.rec_name,'^',ST.rec_contact,'^',CONCAT(ST.rec_street,' ',ST.rec_streetnr),'^',ST.rec_address,'^',CONCAT(ST.rec_zipcode,' ',ST.rec_city),'^',CT.country_name)"))
					   ->joinleft(array('SST'=>SHIPMENT_SCHEDULE_TIME),'SST.barcode_id=BT.barcode_id AND DATE(SST.date_added) = CURDATE() AND SST.parcel_type=1',array(''))
					   ->joinleft(array('MPT'=>SHIPMENT_MANUAL_PICKUP),'MPT.manual_pickup_id=BD.manual_pickup_id',
					   array('pickup_address'=>"if((BD.manual_pickup_id>0),CONCAT(MPT.name,'^',MPT.street1,'^',MPT.street2,'^',MPT.zipcode,'^',MPT.city,'^',MPT.country),if((ASP.zipcode!='' && ASP.city!=''),CONCAT(ASP.name,'^',ASP.street1,'^',ASP.street2,'^',ASP.zipcode,'^',ASP.city,'^',ASP.country),CONCAT(AT.company_name,'^',AT.address1,'^',AT.address2,'^',AT.postalcode,'^',AT.city,'^',CT.country_name)))",
					   	  'pickup_date'=>"if((BD.manual_pickup_id>0 && MPT.pickup_date!='0000-00-00'),MPT.pickup_date,if(ASP.user_id>0 && (".commonfunction::lowercase(date('D'))."_start"."!='00:00:00' || default_time_start!='00:00:00'),CURDATE(),CURDATE()))",
					      'pickup_time'=>"if((BD.manual_pickup_id>0 && MPT.pickup_time!='00:00:00'),MPT.pickup_time,IF(SST.pickup_time!='00:00:00' && SST.pickup_time!='',SST.pickup_time,if(ASP.user_id>0 && ".commonfunction::lowercase(date('D'))."_start"."!='00:00:00',".commonfunction::lowercase(date('D'))."_start".",if(default_time_start!='00-00-00',default_time_start,'00-00-00'))))"))
					   ->where("BT.checkin_status='0' AND BT.pickup_status='0' AND BT.show_planner='1' AND BD.driver_id=0 AND BD.assigned_date < CURDATE() AND ST.shipment_type!=5")
					   //->where("US.gls_pickup = '0'".$accesslevel)
					   ->where("(ST.create_date > NOW() - INTERVAL 60 DAY OR (BD.manual_pickup_id>0 AND DATE(MPT.pickup_date) = CURDATE() OR (ASP.picked_by_driver='0' AND BD.manual_pickup_id>0)))".$accesslevel.$filterString)
					   ->group("DATE(ST.create_date)")
					   //->group(new Zend_Db_Expr('pickup_time'))
					   ->having("pickup_date <= CURDATE()")
					   ->order(new Zend_Db_Expr('pickup_time'))
					   ->order(new Zend_Db_Expr('pickup_date'));//print_r($select->__tostring());die;
					   
					   
		return  $this->getAdapter()->fetchAll($select);
	}

	public function getReturnShipmentList(){
	    try{
	    $this->getData['user_id'] = isset($this->getData['user_id'])?Zend_Encript_Encription::decode($this->getData['user_id']):'';
		$this->getData['parent_id'] = isset($this->getData['parent_id'])?Zend_Encript_Encription::decode($this->getData['parent_id']):'';
		$where = $this->LevelClause();
		$where .= commonfunction::filters($this->getData);
								
	    $select = $this->_db->select()
									->from(array('ST'=>SHIPMENT),array('rec_name','rec_reference','create_date','rec_zipcode','quantity','addservice_id','shipment_id','weight'))
									->joinleft(array('BT' =>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array('COUNT(1) AS CNT'))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("AT.company_name"))
									->joininner(array('FT' =>FORWARDERS),"FT.forwarder_id=ST.forwarder_id",array("FT.forwarder_name"))
									->joininner(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array("CT.country_name"))
									->joininner(array('SV' =>SERVICES),"ST.service_id=SV.service_id",array("SV.service_name"))
									->where("ST.delete_status='0'  AND ISNULL(BT.barcode_id) AND ST.shipment_type = 16".$where)
									->group("ST.shipment_id");
									//print_r($select->__tostring());die;
		 $records =  $this->getAdapter()->fetchAll($select);								
		}catch(Exception $e){$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());}
		
		return $records;
	}
		
	public function getCustomerScanList(){
		try{
			$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'BT.barcode_id','DESC');
			$filterparam = '';
			if(isset($this->getData['forwarder_filter']) && !empty($this->getData['forwarder_filter'])){
				$filterparam.= " AND BT.forwarder_id = '". Zend_Encript_Encription:: decode($this->getData['forwarder_filter'])."'";
			}
			if(isset($this->getData['filter_country']) && !empty($this->getData['filter_country'])){
				$filterparam.= " AND ST.country_id = '". Zend_Encript_Encription:: decode($this->getData['filter_country'])."'";
			}
			if(isset($this->getData['filter_customer']) && !empty($this->getData['filter_customer'])){
				$filterparam.= " AND AT.user_id = '". Zend_Encript_Encription:: decode($this->getData['filter_customer'])."'";
			}

	    $filterparam.= $this->LevelClause();
	   $select = $this->_db->select()
	   							   ->from(array('BT'=>SHIPMENT_BARCODE),array('COUNT(1) AS CNT'))
								   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BT.barcode_id=BD.barcode_id",array(''))
								   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(''))
								   ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array(''))
								   ->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array(''))
								   ->joininner(array('SR'=>SERVICES),"SR.service_id=BT.service_id",array(''))
								   ->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array(''))
								   ->where("BT.checkin_status='0'".$filterparam);
		$total = $this->getAdapter()->fetchRow($select);						   
		//print_r($total); die;						    
	   $select = $this->_db->select()
	   							   ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode','weight','barcode_id'))
								   ->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BT.barcode_id=BD.barcode_id",array('rec_reference','label_date'))
								   ->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('create_date','quantity'))
								   ->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array('AT.user_id','AT.company_name'))
								   ->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array('forwarder_name'))
								   ->joininner(array('SR'=>SERVICES),"SR.service_id=BT.service_id",array('service_name'))
								   ->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array('country_name'))
								   ->where("BT.checkin_status='0' AND AT.parent_id=188".$filterparam)
								   ->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
								   ->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);//print_r($select->__toString());die;
			$result = $this->getAdapter()->fetchAll($select);
			return array('Total'=>$total['CNT'],'Records'=>$result);
		}catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  return array('Total'=>0,'Records'=>array());
		}
    }
}

