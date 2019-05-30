<?php 

namespace Admin\View\Helper;

use Zend\View\Model\ViewModel;
 
class MsgTemplateHelper extends DbTablehelper
{
	public function render_msg_temp($temp_name,$msg,$msg_type,$class1='')
	{
		$view = new ViewModel(array('template'=>$temp_name,'msg'=>$msg,'msg_type'=>$msg_type,'class1'=>$class1));
		$view->setTemplate($temp_name);

		$temp = $this->getServiceLocator()->getServiceLocator()->get('ViewRenderer')->render($view);
		return $temp;
	}
	
}