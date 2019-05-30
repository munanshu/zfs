<?php
 include 'Common.php';
 
 class GetPriv extends Common{
 
    public function getCustomers(){
	      $this->PDOQuery("SELECT AT.user_id,AT.company_name FROM ".$this->db_prefix."users UT INNER JOIN ".$this->db_prefix."users_detail AT ON AT.user_id=UT.user_id WHERE UT.delete_status='0' AND UT.user_status='1' AND UT.level_id IN(4,5,6)");
		  $users = $this->PDOFetchAll();//print_r($users);die;
		  foreach($users as $user){
		     //$this->PDOQuery("SELECT * FROM ".$this->db_prefix."user_privileges UT WHERE  UT.user_id=2977");
		    $privillages = array(array('module_id'=>94));//$this->PDOFetchAll(); 
			foreach($privillages as $privillage){
			    $this->PDOQuery("SELECT * FROM ".$this->db_prefix."user_privileges UT WHERE  UT.user_id='".$user['user_id']."' AND module_id='".$privillage['module_id']."'");
		        $userpriv = $this->PDOFetch();  //print_r($userpriv);die;
			    if(!isset($userpriv['module_id'])){
				  // echo "INSERT INTO ".$this->db_prefix."user_privileges SET user_id='".$user['user_id']."',module_id='".$privillage['module_id']."',assigned_date=NOW()";
				  $this->PDOQuery("INSERT INTO ".$this->db_prefix."user_privileges SET user_id='".$user['user_id']."',module_id='".$privillage['module_id']."',assigned_date=NOW()");
			   }
			}
		  }
		  echo "Done";die;
	}
 }
 $getObj = new GetPriv();
 $data = $getObj->getCustomers();
 $getObj->rander($data,'No Record found!');
?>