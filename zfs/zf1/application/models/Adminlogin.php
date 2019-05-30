<?php
class Application_Model_Adminlogin extends Zend_Custom
{
  public function getUserconfigs($user_id){
     try{
	 $select = $this->_db->select()
						  ->from(array('UD'=>USERS_DETAILS),array('*'))
						  ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=UD.user_id",array('*'))
						  ->where('UD.user_id=?', $user_id);
	 }catch(Exception $e){
	   //echo $e->getMessage();die;
	 }
	 
	 return $this->getAdapter()->fetchRow($select);					  
  }
  
  public function getTraceParcel($barcode)
	 { 
	   try{
		   $barcode = trim($barcode);
		   $select = $this->_db->select()
				->from(array('BT'=>SHIPMENT_BARCODE), array('barcode_id'))
				->where("BT.tracenr_barcode='".$barcode."'");
			 $result = $this->getAdapter()->fetchRow($select);
			 return $result;
		}catch (Exception $e) {
		 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	  }
	}
	
	public function checkusernamedeport($data) {
        $select = $this->_db->select()
                        ->from(array('UD' =>USERS_DETAILS), array('user_id', 'level_id','parent_id'))
                        ->joininner(array("UT" =>USERS), "UD.user_id = UT.user_id", array('UT.username'))
                        ->joininner(array("td" =>TERMS_CONDITION), "td.user_id = UD.parent_id", array('td.term_id','td.message'))
                        ->where("td.message!='' AND td.message is not NULL")
                        ->where("UT.username ='" . $data['user_name'] . "' AND UD.level_id = 5"); //print_r($select->__toString()); die;
        $result = $this->getAdapter()->fetchRow($select);
        return $result;
    }
	
    public function getTextPassword($data) {
	  try{
	  $record = array();
		 $select = $this->_db->select()
			->from(array("UT" =>USERS), array('UT.username','UT.password_text'))
			->joininner(array('UD' =>USERS_DETAILS), "UT.user_id = UD.user_id", array('UD.email'))
			->where("UD.email ='".$data['email']."'"); //print_r($select->__toString()); 
		 $record = $this->getAdapter()->fetchRow($select);
		 if(!empty($record)){
		 	 $this->mailOBj = new Zend_Custom_MailManager();
			 $this->mailOBj->emailData['SenderEmail'] = 'info@dpost.be';
			 $this->mailOBj->emailData['SenderName']    = 'info@dpost.be';
			 $this->mailOBj->emailData['ReceiverEmail']  =$record['email'];
			 $this->mailOBj->emailData['ReceiverName']  = $record['username'];	
			 $this->mailOBj->emailData['MailBody'] = "Your Watchword : ".$record['password_text'];
			 $this->mailOBj->emailData['Subject'] = "Your Password For Logicparcel";
			 $this->mailOBj->Send();
			 return true;
			}else{
				return false;
			}
		   }catch (Exception $e) {
			 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  }
    }
	
	public function setRememberMe($data)
	{	
		if(!empty($data['remember_me']) && $data['remember_me']==1){
			setcookie("admin_userid", $data['admin_userid'],time()+(60*60*24*30));
			setcookie("admin_password", $data['admin_password'],time()+(60*60*24*30));
			setcookie("remember_me", 1,time()+(60*60*24*30));

		}else{
			setcookie("admin_userid",'',time()-1000);
			setcookie("admin_password", '', time()-1000);
			setcookie("remember_me", '', time()-1000);
		}
	}

}

