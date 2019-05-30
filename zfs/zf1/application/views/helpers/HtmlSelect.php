<?php
class Zend_View_Helper_HtmlSelect extends Zend_View_Helper_Partial
{
	public function getSelect($name,$options=array(),$value,$show,$select='',$enctipt=false,$properties=array()){
  	   $html = '';
	   $html .= '<select name='.$name.'';
	   foreach($properties as $key=>$property){
	     $html .=  ' '.$key.'='.$property.'';
	   }
	    $html .= ' id='.$name.'';
	   $html .= '>';
	    
		$html .='<option value="">--Select--</option>';
	   
	   foreach($options as $option){
	     $selected = '';
		 if(($enctipt &&  Zend_Encript_Encription::encode($option[$value])==$select) || (!$enctipt &&  $option[$value]==$select)){
		   $selected ='selected="selected"';
		 }
		 if($enctipt){
	    	 $html .='<option value='.Zend_Encript_Encription::encode($option[$value]).' '.$selected.'>'.$option[$show].'</option>';
	     }else{
		 	 $html .='<option value='.$option[$value].' '.$selected.'>'.$option[$show].'</option>';
		 }
	   }
	   $html .= '</select>';
	   return $html;
	}

}

?>