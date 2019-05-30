<?php

class Planner_Model_Scheduleroute extends Zend_Custom
{
	/**
	 * Get driver List 
	 * Function : getDriverList()
	 * Date : 18/01/2017
	 **/
  public function getDriverList() {
		$select = $this->_db->select()
					   ->from(array('DDT'=>DRIVER_DETAIL_TABLE), array('driver_id','driver_name','account_status','total_workhour','driver_work_type'))
					   ->where('delete_status="0"')
					   ->order('driver_name ASC');
		if($this->Useconfig['level_id']==4){
		   $select->where('DDT.parent_id=?',$this->Useconfig['user_id']);
		}
		elseif($this->Useconfig['level_id']==6){
			$depot_id = $this->getDepotID($this->Useconfig['user_id']);
			$select->where('DDT.parent_id=?', $depot_id);
		}
		//echo $select->__tostring(); die;
		return  $this->getAdapter()->fetchAll($select);
	}
	/**
	 * Get Driver Route schedule
	 * Function : Driverouteschedule()
	 * Date : 18/01/2017
	 **/
  public function DriverRouteSchedule(){
       $where = '1';
	   if(!empty($this->getData['driver_id'])){
          $where .= " AND DSR.driver_id='".Zend_Encript_Encription:: decode($this->getData['driver_id'])."'";
		}
	   if(!empty($this->getData['from_date']) && !empty($this->getData['to_date'])){
          $where .= " AND DSR.schedule_date BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";
		}	
	   $select = $this->_db->select()
					   ->from(array('DSR'=>PLANNER_SCHEDULE_ROUTE), array('*','SUM(total_hour) AS duration','min(schedule_date) AS from','max(schedule_date) AS to','GROUP_CONCAT(DISTINCT schedule_day ORDER BY schedule_date ASC) AS driven_days','GROUP_CONCAT(DISTINCT schedule_date ORDER BY schedule_date ASC) AS driven_dates','DATE_ADD(schedule_date, INTERVAL(2-DAYOFWEEK(schedule_date)) DAY) AS start_date','DATE_ADD(schedule_date, INTERVAL(7-DAYOFWEEK(schedule_date)) DAY) AS to_date','WEEK(schedule_date) as week'))
					   ->joininner(array('DT'=>DRIVER_DETAIL_TABLE),"DT.driver_id=DSR.driver_id",array('driver_name','driver_work_type','total_workhour'))
					   ->where($where)
					   ->group("DSR.driver_id")
					   ->group("WEEK(schedule_date)")
					   ->order("schedule_date DESC")
					   ->order("DT.driver_name");
		  //echo $select->__tostring(); die;
		 $result = $this->getAdapter()->fetchAll($select);//print_r($result);die;
		 return $result;
  }
	/* Route setting list function
	 * function RouteSettingList() 
     * Date : 20/01/2016
     */
    public function RouteSettingList() {
			 $select = $this->_db->select()
							->from(array(PLANNER_ROUTELIST), array('*'))
							->where("delete_status='0'");
			if(isset($this->getData['id']) && $this->getData['id']!=''){
			  $select->where("route_id=?",Zend_Encript_Encription:: decode($this->getData['id']));
			}
	   //echo $select->__tostring();die;
	   $result = $this->getAdapter()->fetchAll($select);
	   return $result;
	}
	
	/* Add edit Route setting function
	 * function addeditRouteSetting() 
     * Date : 20/01/2017
     */
    public function addeditRouteSetting(){
	    try{
			if(isset($this->getData['mode']) && $this->getData['mode'] == 'add'){
				$result = $this->insertInToTable(PLANNER_ROUTELIST,array($this->getData));
				return $result;
			}elseif(isset($this->getData['mode']) && $this->getData['mode'] == 'edit'){
				$where = 'route_id ='.Zend_Encript_Encription:: decode($this->getData['id']);
				$this->getData['updated_by'] = $this->Useconfig['user_id'];
				$this->getData['updated_ip'] = commonfunction::loggedinIP();
				$this->getData['updated_date'] = '';
				$result = $this->UpdateInToTable(PLANNER_ROUTELIST,array($this->getData),$where);
				return $result;
		    }
		}catch (Exception $e) {
				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		}
	}
	/* get Driver Route List
	 * function getDriverRouteList() 
     * Date : 20/01/2017
     */
  public function getDriverRouteList(){
      $select = $this->_db->select()
					   ->from(array('DSR'=>PLANNER_SCHEDULE_ROUTE), array('*'))
					   ->joininner(array('DT'=>DRIVER_DETAIL_TABLE),"DT.driver_id=DSR.driver_id",array('driver_name'))
					   ->joininner(array('DR'=>PLANNER_ROUTELIST),"DR.route_id=DSR.route_id",array('*'))
					   ->where("schedule_date BETWEEN '".$this->getData['start']."' AND '".$this->getData['end']."' AND DSR.driver_id='".Zend_Encript_Encription:: decode($this->getData['driver_id'])."' AND DR.delete_status='0'")
					   ->order("DSR.schedule_date");
		 $result = $this->getAdapter()->fetchAll($select);
		 return $result;
  }
  public function AssignScheduleToDriver(){
		  foreach($this->getData['driver_id'] as $date=>$data){
		        $this->_db->delete(PLANNER_SCHEDULE_ROUTE,"schedule_date='".$date."'");
			   foreach($data as $route_id=>$driver_id){
			       if($driver_id>0){
					  $this->_db->insert(PLANNER_SCHEDULE_ROUTE,array_filter(array('driver_id'=>$driver_id,'schedule_date'=>$date,'route_id'=> $route_id,'schedule_day'=>date('l',strtotime($date)),'total_hour'=>$this->getData['route_time'][$route_id],'remark'=>$data['remark'],'added_date'=>new Zend_Db_Expr('NOW()'))));
			       }
			   }
		  }	   
	  return true; 
	  
  }	
    public function daybyRouteList() {
         $select = $this->_db->select()
                        ->from(array(PLANNER_ROUTELIST), array('*'))
                        ->where("delete_status='0'")
						->order("start_time");
		//echo $select->__tostring();die;
	   $result = $this->getAdapter()->fetchAll($select);
	   return $result;
	 }
	 
	  public function getAssignedRouteDriver($date,$route_id){ 
		  $select = $this->_db->select()
						   ->from(array('DSR'=>PLANNER_SCHEDULE_ROUTE), array('*'))
						   ->where("schedule_date='".$date."' AND route_id='".$route_id."'")
						   ->order("DSR.schedule_date");
					  //echo $select->__tostring();die;
			 $result = $this->getAdapter()->fetchRow($select);
			 return $result['driver_id'];
	  }	
  public function getSummeryRoute(){
       $driverArr = array();
	   $driverids = array();
	  foreach($this->getData['driver_id'] as $date=>$data){
		   foreach($data as $route_id=>$driver_id){
			   if($driver_id>0){
			        $driverArr[$driver_id]  = $driverArr[$driver_id]+$this->getData['route_time'][$route_id];
					//$driverArr[$driver_id] = $drivervalue;
					$driverids[] = $driver_id;		
			   }
			}
		}
		$select = $this->_db->select()
					   ->from(array('DD'=>DRIVER_DETAIL_TABLE), array('*'))
					   ->where("driver_id IN(".implode(',',$driverids).")")
					   ->order("DD.driver_name");//echo $select->__tostring();die;
		$results = $this->getAdapter()->fetchAll($select);	
	    return array('Detail'=>$results,'TimeData'=>$driverArr);  	   
  }

   public function checkConflict(){
		$select = $this->_db->select()
							   ->from(array('PR'=>PLANNER_ROUTELIST), array('*'))
							   ->where("route_id='".$this->getData['sele_route']."'");
				$route_detail = $this->getAdapter()->fetchRow($select);
			  foreach($this->getData['driver_id'][$this->getData['sele_date']] as $route_id=>$driver_id){
				 if($route_id!=$this->getData['sele_route'] && $driver_id==$this->getData['sele_driver'] && !empty($this->getData['sele_driver'])){ 
					  $select = $this->_db->select()
								   ->from(array('PR'=>PLANNER_ROUTELIST), array('*'))
								   ->where("route_id='".$route_id."' AND (('".$route_detail['start_time']."' BETWEEN start_time AND end_time OR '".$route_detail['end_time']."' BETWEEN start_time AND end_time) OR (start_time >='".$route_detail['start_time']."' AND end_time <='".$route_detail['end_time']."'))");
								   //echo $select->__tostring();die;
					 $result = $this->getAdapter()->fetchRow($select);
					 if(!empty($result)){
						echo 'T^Time is conflicting with route:'.$result['routename'];exit;
					 }
				 }
			  }
			   echo 'F^Time is conflicting please check';exit;
  }

  public function getLastScheduleDate(){
        $select = $this->_db->select()
					   ->from(array('DSR'=>PLANNER_SCHEDULE_ROUTE), array('min(schedule_date) AS from','max(schedule_date) AS to'))
					   ->group("YEARWEEK(schedule_date)")
					   ->order("DSR.schedule_date DESC")
					   ->limit(1);
		$result = $this->getAdapter()->fetchRow($select);
		$date = array();
		if(!empty($result)){
			for($i=0;$i<5;$i++){
			 $date[] = date('Y-m-d',strtotime("+$i days", strtotime($result['from'])));
			}
		}
		return $date;
  }	  
}

