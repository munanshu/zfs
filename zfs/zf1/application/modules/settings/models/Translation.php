<?php
class settings_Model_Translation extends Zend_Custom
{

	public $language_name;
	/**
	* Faetch All Translation Word
	* Function : TranslationWord()
	* Fetch All Word Translaton For
	**/
	public function translationText(){
	try{
		$where = '1';
		if(isset($this->getData['token']) && !empty($this->getData['token'])){
		   $where = "id='".Zend_Encript_Encription:: decode($this->getData['token'])."'";
		}
		
		$select = $this->_db->select()
							->from(TRANSLATION,array('*'))
							->where($where);
		return $this->getAdapter()->fetchAll($select);
	  }catch (Exception $e) {
		 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	  }
	}
   /**
	*Faetch active Language
	*Function : activeLanguage()
	*Fetch All Active languages of application
	**/
	public function activeLanguage(){
	 try{
		$select = $this->_db->select()
							->from(LANGUAGE,array('*'));
		//echo $select->__toString();die;
		$result = $this->getAdapter()->fetchAll($select);
		return $result; 
	  }catch (Exception $e) {
		 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	  }
	}

		/*Update Translation
		**function : UpdateTranslationFile()
		**Description : This Function Update TRanslation File
		*/
		public function UpdateTranslation(){
		try{
				$this->UpdateInToTable(TRANSLATION,array($this->getData),'id="'.Zend_Encript_Encription:: decode($this->getData['token']).'"');
			}catch (Exception $e) {
			 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
			}	
		}


	  function addText(){
	    try{
		   global $objSession;
		   	  $select = $this->_db->select()
							->from(TRANSLATION,array('COUNT(1) as CNT'))
							->where("translationFor='".$this->getData['translationFor']."'");
           	  $result = $this->getAdapter()->fetchRow($select);
		   if($result['CNT']!=0){
		      $objSession->errorMsg = "Text Already Exist !!";
		   }else{
			  $this->insertInToTable(TRANSLATION,array($this->getData));
 			  $objSession->successMsg = "Text added successfully !!";
		   }
		  }catch (Exception $e) {
			 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  }
		}	
}