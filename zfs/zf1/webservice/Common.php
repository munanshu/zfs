<?php
 include 'DbClass.php';
 
 class Common extends DBClass{
     public $getData  = array();
	 public function __construct(){
	    parent::__construct();
	    foreach($_REQUEST as $key=>$request){
		   $this->getData[$key] = ($key=='access_text' || $key=='access_key')?base64_decode($request):$request; 
		}
	 } 
     
	 public function rander($data,$message){
	    if(!empty($data)){
     		echo json_encode(array('Status'=>1,'Response'=>$data));die;
		 }else{
			echo json_encode(array('Status'=>0,'Response'=>$message));die;
		 }
	 }
 }
?>