<?php 

namespace Admin\View\Helper;

use Admin\View\Helper\DbTablehelper;
 
class WidgetHelper extends DbTablehelper
{
	
	 public function getwidget($id)
	 {
	 	$res = $this->getTable($id);
	 	return $res;
	 }


}