<?php

class Planner_Model_Drivertracking extends Zend_Custom
{
    public function getDriverLocation(){
	    $filter = '';
		if(isset($this->getData['date'])){
		  $filter = " AND DATE(date_time)='".$this->getData['date']."'";
		}else{
		   $filter = " AND DATE(date_time)= CURDATE()";
		}
	    $select = $this->_db->select()
							->from(array('DL'=>DRIVER_GPS_LOCATION),array('*'))
							->joininner(array('DT'=>DRIVER_DETAIL_TABLE),"DT.driver_id=DL.driver_id",array('driver_name'))
							->where("DL.driver_id='".$this->getData['driver_id']."' AND DL.lat>0 AND DL.lng>0".$filter)
							->group("DATE_FORMAT(DL.date_time,'%Y-%m-%d %H:%i')")
							->order("DL.date_time");
		$result = $this->getAdapter()->fetchAll($select);
		$latlong = array();
		$timestamp = array();
		if(!empty($result)){
		    foreach($result as $value){
			    $latlong[] = $value['lat'].','.$value['lng'];
				$timestamp[] = $value['driver_name'].'-'.date('H:i',strtotime($value['date_time']));
			}
		}
		return 	array('Location'=>commonfunction::implod_array($latlong,'#'),'Time'=>commonfunction::implod_array($timestamp,'#'));
	}
	
	
	public function getDriverPickupLocaions(){
	   $select = $this->_db->select()
									->from(array('DH' =>DRIVER_HISTORY),array('pickup_time','driver_id','zipcode','city','country','user_id'))
									->joininner(array('DD'=>DRIVER_DETAIL_TABLE),"DH.driver_id=DD.driver_id",array("driver_name"))
									->joinleft(array('CT'=>COUNTRIES),"CT.country_name=DH.country",array("cncode"))
									->where("DH.driver_id='".$this->getData['driver_id']."' AND DH.assign_date = CURDATE()")
									->group("DH.user_id")
									->order("DH.pickup_time ASC");
									//print_r($select->__toString());die;
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function driverCurrentLocation(){
	    $select = $this->_db->select()
							->from(array('DL'=>DRIVER_GPS_LOCATION),array('*'))
							->joininner(array('DT'=>DRIVER_DETAIL_TABLE),"DT.driver_id=DL.driver_id",array('driver_name'))
							->where("DL.driver_id=11 AND DL.lat>0 AND DL.lng>0")
							->order("DL.date_time DESC")
							->limit(1);
		return $this->getAdapter()->fetchRow($select);
	}

}

