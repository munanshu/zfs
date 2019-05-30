<?php
 include 'Common.php';
 
 class GetCountry extends Common{
 
    public function getcountries(){
	      $this->PDOQuery("SELECT CT.country_id,CONCAT(CT.cncode,'-',CT.country_name) AS country_name FROM ".$this->db_prefix."countries CT");
		  $users = $this->PDOFetchAll();
		  return $users;
	}
 }
 $getObj = new GetCountry();
 $data = $getObj->getcountries();
 $getObj->rander($data,'No Record found!');
?>