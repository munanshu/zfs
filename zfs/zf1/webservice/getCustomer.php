<?php
 include 'Common.php';
 
 class GetData extends Common{
 
    public function getCustomers(){
	      $this->PDOQuery("SELECT AT.user_id,AT.company_name FROM ".$this->db_prefix."users UT INNER JOIN ".$this->db_prefix."users_detail AT ON AT.user_id=UT.user_id WHERE UT.delete_status='0' AND UT.user_status='1' AND UT.level_id=5");
		  $users = $this->PDOFetchAll();
		  return $users;
	}
 }
 $getObj = new GetData();
 $data = $getObj->getCustomers();
 $getObj->rander($data,'No Record found!');
?>