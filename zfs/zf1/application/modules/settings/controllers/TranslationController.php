<?php
class Settings_TranslationController extends Zend_Controller_Action
{

    public $Request = array();

    public $ModelObj = null;
	public $formObj  = NULL;

    public function init()
    { 
		try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new settings_Model_Translation();
			$this->formObj = new settings_Form_Translation();
			$this->ModelObj->getData  = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
			$this->_helper->layout->setLayout('main');
	  }catch(Exception $e){
	    echo $e->getMessage();die;
	  }
    }

	/**
	 * Text Translate List Action 
	 * Function : action to list text translation
	 * View Post Code texttranslatelist page
	 **/
	public function texttranslatelistAction(){
		 $this->view->TranslationList = $this->ModelObj->translationText();
		 $this->view->Language = $this->ModelObj->activeLanguage();
	}
        
	/* call Translation Update Acton
	* Function : translationedit()
	* Check Forwarder Invoice in database or other options
	**/
	public function translationeditAction(){
		$this->ModelObj->UpdateTranslationFile();
		exit;
	}
	
	/* Add text action to add translated text
	* Function : addtextAction()
	* Date : 24/03/2017
	**/
	 public function addtextAction(){
	  global $objSession;
		 $this->_helper->layout->setLayout('popup');
		 $this->formObj->LanguageList = $this->ModelObj->activeLanguage();
	  	 $this->formObj->addTranslationTextForm();
	  if($this->Request['mode'] == 'add'){
	  	   $this->view->title = 'Add New Text';
		  if($this->_request->isPost() && !empty($this->Request['submit'])){
			 if($this->formObj->isValid($this->Request)){
				 $this->ModelObj->addText();
				 echo '<script type="text/javascript">parent.window.location.reload();
				  parent.jQuery.fancybox.close();</script>';
				  exit(); 
			 }else{
				$this->formObj->populate($this->Request);
			 }
			}
	
	   }elseif($this->Request['mode'] == 'edit' && isset($this->Request['token'])){
		if($this->_request->isPost() && !empty($this->Request['submit'])){
		 if($this->formObj->isValid($this->Request)){
		 		  $this->ModelObj->UpdateTranslation();
		  		  echo '<script type="text/javascript">parent.window.location.reload();
				  parent.jQuery.fancybox.close();</script>';
				  exit(); 
		  
		 }else{
		 	$this->formObj->populate($this->Request);
		 }
		}
		$this->view->title = 'Edit Text Translation';
		$this->formObj->addTranslationTextForm()->submit->setLabel('Edit Text');
		$popolateData =  $this->ModelObj->translationText();
	    $this->formObj->populate($popolateData[0]);
	   }	
	   $this->view->addtextform = $this->formObj;
  }
}