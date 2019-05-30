<?php
 include 'Common.php';
 
 class Login extends Common{
 
    public function doLogin(){
	      if($this->getData['access_text']==''){
		    $this->rander(array(),'Please valid Username!');
		  }
		  if($this->getData['access_key']==''){
		    $this->rander(array(),'Please valid Pasword!');
		  }
		  $this->PDOQuery("SELECT AT.user_id,AT.company_name,UT.level_id,AT.parent_id FROM ".$this->db_prefix."users UT INNER JOIN ".$this->db_prefix."users_detail AT ON AT.user_id=UT.user_id WHERE UT.username='".addslashes($this->getData['access_text'])."' AND UT.password='".md5(addslashes($this->getData['access_key']))."' AND UT.delete_status='0' AND UT.user_status='1'");		  $users = $this->PDOFetchAll();
		  if(!empty($users)){
		     $this->rander($users,'Access Granted');
		  }else{
		     $this->rander(array(),'Invalid Username or Password');
		  }
	}
 }
 $getObj = new Login();
 $data = $getObj->doLogin();
?>