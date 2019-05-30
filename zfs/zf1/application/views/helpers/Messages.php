<?php
class Zend_View_Helper_Messages extends Zend_View_Helper_Partial
{
	public function getMessages(){
  		//return $this->partial('error/error.phtml');
		global $objSession;
		if(isset($objSession->defaultMsg)){
		   echo '<div class="alert alert-block" role="alert"><button type="button" class="close" data-dismiss="alert">&times;</button>'.$objSession->defaultMsg.'</div>';
		   unset($objSession->defaultMsg);
		}
		if(isset($objSession->infoMsg)){
		   echo '<div class="alert alert-icon alert-icon-info alert-info" role="alert"><button type="button" class="close" data-dismiss="alert">&times;</button>'.$objSession->infoMsg.'</div>';
		   unset($objSession->infoMsg);
		}
		if(isset($objSession->successMsg)){
		   echo '<div class="alert alert-icon alert-icon-success alert-success" role="alert"><button type="button" class="close" data-dismiss="alert">&times;</button>'.$objSession->successMsg.'</div>';
		   unset($objSession->successMsg);
		}
		if(isset($objSession->errorMsg)){
		   echo '<div class="alert alert-icon alert-icon-danger alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert">&times;</button>'.$objSession->errorMsg.'</div>';
		   unset($objSession->errorMsg);
		}
		return;
	}

}

?>