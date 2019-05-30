<?php

class Planner_Model_Driverreport extends Zend_Custom
{
   public function Navigation(){
         $tabhtml = '';
		$tabArray = array('Login Report'=>'loginreport','Vehicle Report'=>'vehiclereport','Pickup Report'=>'pickupreport','Delivery Report'=>'deliveredreport','Leave Report'=>'leavereport');
		foreach($tabArray as $key=>$value){
			if($value == $this->getData['action']){
				$tabhtml .= '<li class="active"> <a href="'.BASE_URL.'/Driverreport/'.$value.'" data-toggle="">'.$key.'</a> </li>';
			}else{
				$tabhtml .= '<li> <a href="'.BASE_URL.'/Driverreport/'.$value.'" data-toggle="">'.$key.'</a> </li>';
			}
		}
		return $tabhtml;
   }

}

