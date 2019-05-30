<?php 
class commonfunction{
    public static function page_redirect($url) {
        header("Location: " . BASE_URL . $url . "");
        exit();
    }
	public static function onlynumbers($string){
	   return preg_replace("/[^0-9]+/", "", $string);
	}
	public static function addslashesing($string){
	   return addslashes($string);
	}
	public static function paddingleft($string,$length,$padString=" "){
	   return str_pad($string,$length,$padString,STR_PAD_LEFT);
	}
	public static function paddingRight($string,$length,$padString=" "){
	   return str_pad($string,$length,$padString,STR_PAD_RIGHT);
	}
	public static function uppercase($string){
	  return strtoupper($string);
	}
	public static function lowercase($string){
	  return strtolower($string);
	}
	public static function utf8Decode($string){
	  return utf8_decode($string);
	}
	public static function stringReplace($find,$replace,$sting){
	   return str_replace($find,$replace,$sting);
	}
	public static function trimString($string){
	   return trim($string);
	}
	public static function numberformat($number,$decimal=2,$dec_point='.',$separator=','){
	   return number_format($number,$decimal,$dec_point,$separator);
	}
	public static function strip_slashes($string){
	   return stripslashes($string);
	}
	public static function implod_array($array,$delimeter = ','){
	   return implode($delimeter,$array);
	}
	public static function explode_string($string,$delimeter = ','){
	   return explode($delimeter,$string);
	}
	public static function DateNow(){
	  return date('Y-m-d H:i:s');
	} 
	public static function loggedinIP(){
	  return $_SERVER['REMOTE_ADDR'];
	}
	public static function sub_string($string='',$start,$end=''){ 
	   return ($end!='')?substr($string,$start,$end):substr($string,$start);
	}
	public static function string_pos($string,$niddle){ 
	     return strpos($string,$niddle);
	}
	public static function file_contect($file_path){
	  return @file_get_contents($file_path);
	}
	public static function file_pathinfo($file_path){
	   return pathinfo($file_path);
	}
	public static function file_basename($file_path){
	   return basename($file_path);
	}
	public static function Array_key_exist($niddle,$array_mixed){
	   return array_key_exists($niddle,$array_mixed);
	}
	public static function Is_Array($mixed_str){
	     return is_array($mixed_str);
	}
	public static function string_length($string){
	     return strlen($string);
	}
	public static function string_position($mixed_string,$niddle){
	     return strpos($mixed_string,$niddle);
	}
	public static function is_number($c) {
		return preg_match('/[0-9]/', $c);
	}
	public static function arraysum($array){
	  return @array_sum($array);
	}
	public static function String_chunk($string,$length,$separator=' '){
	   return chunk_split($string,$length,$separator);
	}
	public static function mK_time(){
	   return mktime();
	}
	public static function str_reverse($string){
	  return strrev($string);
	}
	public static function Alphanumeric($string){
	    return preg_replace("/[^a-z0-9.]+/i", "", $string);
	}
	public static function float_round($float){
	  return round($float);
	}
	public static function string_suffle($string){
	  return str_shuffle($string);
	}
	
	public static function randomDigits(){
		$length = 7;
		$random = "";
		srand((double)microtime()*1000000);
		$data = "1234567890";
		for($i= 0; $i < $length; $i++) {
		  $random .= substr($data, (rand()%(strlen($data))), 1);
		}
		return $random;
	}
	/**
     * create new array by useing keys value of given array.
     * Function : array_keys()
     * return array
     **/
	 public static function arraykey($data=array()){
	  return array_keys($data);
	 }
	 /**
	 * Return bolian value .
	 * Function : array_keys()
	 * check value exist or not in array
	 **/
	 public static function inArray($value,$data){
	   return in_array($value,$data);
	 }
	/**
     * Get space Between String
     * Function : GetSpaceBetweenString()
     * This Function put the space Between string
     * */
    public static function GetSpaceBetweenString($string, $n = 4) {
        $count = 1;
        $numberStr = "";
        for ($i = 0; $i < strlen($string); $i++) {
            $numberStr .= (string) $string[$i];
            if ($count % $n == 0) {
                $numberStr .= " ";
            }
            $count++;
        }
        return $numberStr;
    }
	public function julianDate($date) {
		$w_time = commonfunction::explode_string(date('Y-n-d',strtotime($date)),"-");
		$year   = $w_time[0];
		$month  = $w_time[1];
		$date   = $w_time[2];
		if(commonfunction::string_length($date)<2) { $date = '0'.$date; }
		$leap = $year / 4;
		$exp = commonfunction::explode_string($leap,".");
		if(isset($exp[1])) { $feb = '28'; } else { $feb = '29';  }
		$months = array(1  => '31',2  => $feb,3  => '31',4  => '30',5  => '31',6  => '30',7  => '31',8  => '31',9  => '30',10 => '31',11 => '30',12 => '31');
		$cal = array();
		for($i=1;$i<$month;$i++){
			$cal[]= $months[$i];
		}
		$total = commonfunction::arraysum($cal) + $date;
		if(commonfunction::string_length($total)<3 && commonfunction::string_length($total)>1) { $total = '0'.$total; }
		if(commonfunction::string_length($total)<2 && commonfunction::string_length($total)>0) { $total = '00'.$total; }
		return $total;
	}
	
	public static function getGoodsType($string){
	   $goods = 'C';
	   switch(self::lowercase($string)){
	      case 'documents':
		  case 'document':
		   $goods = 'D';
		  break;
		  case 'gifts':
		  case 'gift':
		   $goods = 'G';
		  break; 
		  case 'commercial':
		  case 'commercial goods':
		   $goods = 'C';
		  break; 
	   }
	   return $goods;
	}
	 /**
     * Associative arrray
     * Function : scalarToAssociative()
     * convert scalar array to associative array
     * */
    public static function scalarToAssociative($data, $arr) {
        $asso_array = array();
        while (count($data) > 0) {
            $pop = array_shift($data);
            $asso_array[$pop[$arr[0]]] = $pop[$arr[1]];
        }
        return $asso_array;
    }
	 /**
     * Find Needle in comma separated value
     * Function : substr_in_array()
     * This Function Find the key in comma separated value
     * */
    public static function substr_in_array($needle, $haystack) {
        /*         *  cast to array * */
        $needle = (array) $needle;
        /*         *  map with preg_quote * */
        $needle = array_map('preg_quote', $needle);
        /*         *  loop of the array to get the search pattern * */
        foreach ($needle as $key => $pattern) {
            if (count(preg_grep("/$pattern/", $haystack)) > 0) {
                $key = (key(preg_grep("/$pattern/", $haystack)));
                return $key; //return only key
            }
        }
    }
	/**
     * Order By and Limit
     * Function : OdrderByAndLimit()
     * This functio return order by and Limit
     **/
	 public function OdrderByAndLimit($RequestPara,$order_by,$default='DESC'){ 
	    global $queryAttrib;
		//Order
		 if(!isset($RequestPara['order_type'])){
		     $order_type = $default;
		     $queryAttrib['order_type'] = $default;
		 }elseif($RequestPara['order_type']=='DESC'){
		   $order_type = 'ASC';
		   $queryAttrib['order_type'] = 'DESC';
		 }else{
		   $queryAttrib['order_type'] = 'DESC';
		   $order_type = $queryAttrib['order_type'];
		 }
		 
		 $order_by   = (!empty($RequestPara['order_by']))?$RequestPara['order_by']:$order_by;
		 
		
		 $order_type = (!empty($RequestPara['order_type']))?$RequestPara['order_type']:$queryAttrib['order_type'];
		 $queryAttrib['order_type'] = $order_type;
		 //Limit

		 if(!isset($queryAttrib['toshow']) || $queryAttrib['toshow']<=0){
		 	 $queryAttrib['toshow'] = 100;
		 }	

		 $queryAttrib['offset'] = (!empty($RequestPara['offset']))?$RequestPara['offset']:0;

		 $queryAttrib['toshow'] = (!empty($RequestPara['toshow']))?$RequestPara['toshow']:$queryAttrib['toshow'];
		
		 return array('OrderBy'=>$order_by,'OrderType'=>$order_type,'Toshow'=>$queryAttrib['toshow'],'Offset'=>$queryAttrib['offset']); 

	 }
	 /**
     * Page Counter
     * Function : PageCounter()
     * This Function Return The pagination counter of all the record
     * */
    public function PageCounter($total,$RequestPara) {
	global $queryAttrib; 
	$returnTXT = "";
	$toshow = (isset($RequestPara['toshow']))?$RequestPara['toshow']:0;
	$offset = (isset($RequestPara['offset']))?$RequestPara['offset']:0;
	if(!isset($queryAttrib['toshow']) || $toshow<=0) {
		$queryAttrib['toshow'] = 100;
	}else{
		$queryAttrib['toshow'] = $RequestPara['toshow'];
	}
	if(!isset($queryAttrib['offset']) || $offset<=0) {
		$queryAttrib['offset'] = 0;
	}else{
		$queryAttrib['offset'] = $RequestPara['offset'];
	}
	if(!isset($queryAttrib['showcounter']) || $queryAttrib['showcounter']<=0) {
		$queryAttrib['showcounter'] = 1;
	}
	if($queryAttrib['showcounter']>4){
	   $queryAttrib['showcounter'] = 1;
	}
	if($total<($queryAttrib['offset']+$queryAttrib['toshow'])){
		$dispto=$total;
	}else{
		$dispto=$queryAttrib['offset']+$queryAttrib['toshow'];
	}
	$groupby = 10;
	
	if($total>0){
		if($queryAttrib['showcounter']<=2){
			$returnTXT .= "<span>Displaying (".($queryAttrib['offset']+1)." - ".($dispto).") of ".$total."</span>&nbsp;&nbsp;";
		}
		if(($total > $queryAttrib['toshow']) && ($queryAttrib['showcounter']<=4)) {
			$no_of_pages=ceil($total/$queryAttrib['toshow']);
			if($queryAttrib['offset'] != 0) {
				$returnTXT .= "<a href='".BASE_URL.self::GenerateLink(($queryAttrib['offset'] - $queryAttrib['toshow']), $RequestPara)."'>&laquo; Prev</a>&nbsp;";
			}
			for($ii=0; $ii<ceil($total/$queryAttrib['toshow']); $ii++) {  
				if(($ii+1) <= 0 ) {
				}else{
					if($queryAttrib['offset']<(($groupby-(floor($groupby/2)))*$queryAttrib['toshow']-1)){
						$startindex=0;
						$endindex=$groupby;
					}
					elseif(($total-$queryAttrib['offset'])<(($groupby-(floor($groupby/2)))*$queryAttrib['toshow'])){
						$totalpage=ceil($total/$queryAttrib['toshow']);
						$startindex=$totalpage-$groupby+1;
						$endindex=$totalpage;
					}
					else{
						$currentpage=ceil($queryAttrib['offset']/$queryAttrib['toshow']);
						$startindex=$currentpage-(floor($groupby/2))+2;
						$endindex=$currentpage+(floor($groupby/2))+1;
					}
					if(($ii+1)>=$startindex && ($ii+1)<=$endindex && ($queryAttrib['showcounter']==1 || $queryAttrib['showcounter']==3)){
						$link = self::GenerateLink(($ii*$queryAttrib['toshow']),$RequestPara)."";
						if($queryAttrib['offset'] != ($ii*$queryAttrib['toshow']) ) {
							$returnTXT .= "<a href=\"".$link."\">".($ii+1)."</a>&nbsp;";		
						}else {
							$returnTXT .= '<b><span>'.($ii+1).'</b>&nbsp;';			
						}
					}
				}
			}
			if(($queryAttrib['offset'] + $queryAttrib['toshow'])<$total) {
				$returnTXT .= " <a href='".self::GenerateLink(($queryAttrib['offset'] + $queryAttrib['toshow']), $RequestPara)."' >Next &raquo;</a>&nbsp;";
			}
		}
	}
	return $returnTXT;
	}
	/**
     * Generate Link of page counter
     * Function : GenerateLink()
     * This Function Generate Link For page counter
     * */
    public function GenerateLink($offset, $RequestPara) {
	    $linkText = '';
		global $queryAttrib;
		$Attribs = array();
		if($offset!=''){
		  $Attribs[] = "offset=".$offset."&amp;";
		}
		if(isset($queryAttrib['toshow'])){
		  $Attribs[] = "toshow=".$queryAttrib['toshow']."&amp;";
		}
		if(isset($RequestPara['order_by'])){
		  $Attribs[] = "order_by=".$RequestPara['order_by']."&amp;";
		}
		if(isset($queryAttrib['order_type'])){
		  $Attribs[] = "order_type=".$queryAttrib['order_type']."&amp;";
		}
		 
		if(!empty($RequestPara)){
		   foreach($RequestPara as $key=>$val){ 
			   if(!in_array($key,array('controller','action','module','offset','toshow','order_by','order_type'))){
				   $Attribs[] = $key."=".$val;
				  } 
			   }
		}
		$linkText .= self::implod_array($Attribs, '&amp;');  
		if($linkText != ""){
			$linkText = "?".$linkText;
		}
		return $linkText;

	}
	
	public static function remove_accent($str)
    {
      $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o', 'O', 'o', 'O', 'o', 'Œ', 'œ', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'Š', 'š', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Ÿ', 'Z', 'z', 'Z', 'z', 'Ž', 'ž', '?', 'ƒ', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?', '?', '?', '?', '?', '?','/','#','%','@','&','-');
      
      $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o','','','','','',' ',' ');
     return str_replace($a, $b, $str);
    }
	public static function AssociatToLeanier($mixedArr=array(),$niddle){
	    $returnArr = array();
		foreach($mixedArr as $Arr){
		  $returnArr[] = $Arr[$niddle];
		}
	  return $returnArr;
	}
	
	public static function ImportFile($upload_dir='import_shipment',$extension='csv',$user_id){
	    $upload = new Zend_File_Transfer();
	    $datetime = $user_id.'_'.date('Y_m_d_H_i_s');
        $Filename = ROOT_PATH.'/public/'. $upload_dir . '/' . $datetime . '.' . $extension;
        $upload->addFilter('Rename', array('target' => $Filename, 'overwrite' => true));
        $upload->receive();
        return $Filename;
	}
	public static function ReadCsv($Csvfile, $delimiter = ';',$readingLine = 1){
	      $importdata = array();
		  if (($handle = fopen($Csvfile, "r")) !== FALSE) {
		     $counter = 1;
             while (($data = fgetcsv($handle, 10000, $delimiter)) !== FALSE) {
			   if($counter>=$readingLine){
			      $importdata[] = $data;   
			   }
			   $counter++;    
			 }
		  }	
		  return $importdata; 
	}
	public static function importAssociative($importDatas,$csvHeader, $formdata = array()){
	    $FinalData = array();
		$obj = new Zend_Custom();
		foreach($importDatas as $importData){
		     $dataArr = array();
			 $Header = $csvHeader;
		   foreach($importData as $value){ 
			  $key = array_pop($Header); 
			  switch($key){
			     case 'country_id':
				    $country_code = trim(preg_replace('/\s+/', ' ', str_replace('"', '', $value)));
				    $countrydetail   = $obj->getCountryDetail($country_code,2);
				    $dataArr[$key] = $countrydetail['country_id'];
				 break;
				 case 'service_id':
					$service_Code = trim(preg_replace('/\s+/', ' ', str_replace('"', '', $value)));
					$mainservice = self::sub_string($service_Code,0,1);
					$subservice  = self::sub_string($service_Code,1,2);
				    $servicedetail   = $obj->getServiceDetails($mainservice,2);
					$dataArr['service_id'] = 0;
					$dataArr['addservice_id'] = 0;
					if(!empty($servicedetail)){
					   if($servicedetail['parent_service']==0){
						  $dataArr['service_id'] 	= $servicedetail['service_id'];
					   }else{
						  $dataArr['service_id'] 	= $servicedetail['parent_service'];
						  $dataArr['addservice_id'] = $servicedetail['service_id'];
					   }
					  }
					  if(!empty($subservice)){
						  $subservicedetail   = $obj->getServiceDetails($subservice,2);	
						  if(!empty($subservicedetail)){
							   $dataArr['addservice_id'] 	= ($subservicedetail['parent_service']>0)?$subservicedetail['service_id']:0;
						  }	
					 }
				 break;
				 case 'quantity':
				    $quantity = trim(preg_replace('/\s+/', ' ', str_replace('"', '', $value)));
					$dataArr[$key] = ($quantity<=0 || $quantity>10)?1:$quantity;
				 break;
				 case 'rec_zipcode':
				    $dataArr[$key] = $obj->ValidateZipcode(trim(preg_replace('/\s+/', ' ', str_replace('"', '', $value))));
				 break;
				 case 'goods_id':
				   $dataArr[$key] = trim(preg_replace('/\s+/', ' ', str_replace('"', '', $value)));
				 break;
				 case 'parcel_shop':
				     $shop_id = trim(preg_replace('/\s+/', ' ', str_replace('"', '', $value)));
					 $dataArr[$key] =  $obj->getShopdetailByID($shop_id,$dataArr['rec_zipcode'],$country_code);
				 break;
				 case 'weight':
				 case 'cod_price':
				 case 'shipment_worth':
				   $dataArr[$key] = self::stringReplace(',','.',trim(preg_replace('/\s+/', ' ', str_replace('"', '', $value))));
				 break;
				 case 'senderaddress_id':
				     $sendercode = trim(preg_replace('/\s+/', ' ', str_replace('"', '', $value)));
				     $senderAdd = new Application_Model_Senderaddress();
					 $dataArr[$key] = $senderAdd->getAddressID(array('SenderCode'=>$sendercode,'country_id'=>$dataArr['country_id'],'user_id'=>$formdata[ADMIN_ID]));
				   break;
				 default:
				 if($key!=''){
				 	$dataArr[$key] = utf8_encode(trim(preg_replace('/\s+/', ' ', str_replace('"', '', $value))));
				 }
			  }
		  }
		  $dataArr['shipment_type'] = 2;
		  $dataArr[ADMIN_ID] = $formdata[ADMIN_ID];
		  $dataArr['create_date'] = self::DateNow();
		  $dataArr['create_ip'] = self::loggedinIP(); 
		  $FinalData[] = $dataArr;
		}
	   return $FinalData;
	}
	public static function CheckDigitMode36Parcelshop($string) {
        $mod = 36;
		$cd = 36;
		$char2Value = array(
            0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4,
            5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9,
            'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13,
            'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17,
            'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21,
            'M' => 22, 'N' => 23, 'O' => 24, 'P' => 25,
            'Q' => 26, 'R' => 27, 'S' => 28, 'T' => 29,
            'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33,
            'Y' => 34, 'Z' => 35
        );
        $value2Char = array(
            0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4,
            5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9,
            10 => 'A', 11 => 'B', 12 => 'C', 13 => 'D',
            14 => 'E', 15 => 'F', 16 => 'G', 17 => 'H',
            18 => 'I', 19 => 'J', 20 => 'K', 21 => 'L',
            22 => 'M', 23 => 'N', 24 => 'O', 25 => 'P',
            26 => 'Q', 27 => 'R', 28 => 'S', 29 => 'T',
            30 => 'U', 31 => 'V', 32 => 'W', 33 => 'X',
            34 => 'Y', 35 => 'Z'
        );
	for($i=0;$i<strlen($string);$i++){
	  $val = $char2Value[$string[$i]];
	  $cd = $cd + $val;
	  if($cd>$mod){
	    $cd = $cd-$mod;
		$cd = $cd * 2;
	  }else{
	     $cd = $cd * 2;
	  }
	  if($cd>$mod){
	    $cd = $cd - ($mod+1);
	  }
	}
	
		$cd = ($mod + 1) - $cd;
		if($cd == $mod){
		  $checkdigit = 0;
		}else{
		   $checkdigit = $value2Char[$cd];
		}
		return $checkdigit;
  }
    
	public static function PickupSatus($date,$status){
	   if($date==date('Y-m-d') && $status=='0'){
	      return '<img src="'.IMAGE_LINK.'/assigned.png" title="Scheduled for Pickedup"/>';   
	   }elseif($status=='1'){
	   		return '<img src="'.IMAGE_LINK.'/pickup.png" title="Parcel is Picked-Up Already"/>';   
	   }else{
	   		return '<img src="'.IMAGE_LINK.'/not_assigned.png" title="Not Scheduled yet"/>';   
	   }
	}
	public static function writeFile($data,$extension="txt",$path,$append=false){
	    $extension = ($extension!='') ? ".".$extension : '';
	    $filepath = $path.$extension;
		$filename = self::sub_string($filepath,self::string_pos($filepath,'/')+1);
        $time = (file_exists($filepath))?date('Y-m-d',filemtime($filepath)):'';
        // echo $filepath;die;
		if($time==date('Y-m-d') && $append){
	    	$fopen  = fopen($filepath,"a");
			$data = $data."\r\n";
        }else{
		   $fopen  = fopen($filepath,"w");
		   $data = $data;
		}
		//ob_end_clean();
		fwrite($fopen,$data);
		fclose($fopen);
		return $filename;
	 }
	 public static function just_clean($string) {
		// Replace other special chars
		$specialCharacters = array('#' => '','$' => '','%' => '',
								   '&' => '','@' => '','.' => '',
								   '€' => '','+' => '','=' => '',
								   '§' => '','©' => '','/' => '');
		while (list($character, $replacement) = each($specialCharacters)) {
		$string = str_replace($character, '-' . $replacement . '-', $string);
		}
		$string = preg_replace('/^[-]+/', '', $string);
		$string = preg_replace('/[-]+$/', '', $string);
		$string = preg_replace('/[-]{2,}/', '', $string);
		return $string;
	}
	public static function filters($inputs){
	     $where = '';
		 if(isset($inputs['user_id']) && $inputs['user_id']>0){
		    $where .=  " AND AT.user_id='".$inputs['user_id']."'";
		}
		if(isset($inputs['parent_id']) && $inputs['parent_id']>0){
		    $where .=  " AND AT.parent_id='".$inputs['parent_id']."'";
		}
		if(isset($inputs['country_id']) && $inputs['country_id']>0){
		    $where .=  " AND ST.country_id='".$inputs['country_id']."'";
		}
		if(isset($inputs['forwarder_id']) && $inputs['forwarder_id']>0){
		    $where .=  " AND BT.forwarder_id='".$inputs['forwarder_id']."'";
		}
		if(isset($inputs['service_id']) && $inputs['service_id']>0){
		    $where .=  " AND (BT.service_id='".$inputs['service_id']."' OR ST.addservice_id='".$inputs['service_id']."')";
		}
		return $where;
	}
	public static function ExportCsv($Csvdata, $filename = 'CSV Data',$filetype='csv') {
        header("Content-type: application/".$filetype);
        header("Content-Disposition: attachment; filename=" . $filename . ".".$filetype);
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $Csvdata;
        exit();
    }
	public static function TimeFormat($datetime,$format='F-d Y H:i'){
	   return date($format,strtotime($datetime));
	}
	public static function GlsDECheck($number) {
	   $multiply = 0;
	   $revnumber =  strrev($number);
	  for($i=0;$i<strlen($revnumber);$i++){
		$factor  = ($i%2==0)?'3':'1';
		$multiply = $multiply+($revnumber[$i]*$factor);
	  }
	  $multiply = $multiply + 1;
	  $mode = $multiply%10;
	   if($mode==0){
	     $check_digit = $mode;
	   }else{
	     $check_digit = (10-$mode);
	   }
	  return $check_digit;
	}
	public static function first_upper($string){
	  return ucfirst($string);
	}
	
	public function readFile($filepath,$extension="txt"){
	   $allowed_ext = array (
		  // archives
		  'zip' => 'application/zip',
		  // documents
		  'pdf' => 'application/pdf',
		  'doc' => 'application/msword',
		  'txt' => 'application/txt',
		  'txt' => 'application/txt',
		  'csv' => 'application/csv',
		  'xls' => 'application/vnd.ms-excel',
		  'ppt' => 'application/vnd.ms-powerpoint',
		  'lst' => 'application/lst',
		  'exe' => 'application/octet-stream',
		  'gif' => 'image/gif',
		  'png' => 'image/png',
		  'jpg' => 'image/jpeg',
		  'jpeg' => 'image/jpeg',
		  'mp3' => 'audio/mpeg',
		  'wav' => 'audio/x-wav',
		  'mpeg' => 'video/mpeg',
		  'mpg' => 'video/mpeg',
		  'mpe' => 'video/mpeg',
		  'mov' => 'video/quicktime',
		  'avi' => 'video/x-msvideo'
       );
	   if ($fd = fopen ($filepath, "rb")) {
			if (substr($filepath,0,4)=='http') { 
				$x = array_change_key_case(get_headers($filepath, 1),CASE_LOWER); 
				if ( strcasecmp($x[0], 'HTTP/1.1 200 OK') != 0 ) { $fsize = $x['content-length'][1]; } 
				else { $fsize = $x['content-length']; } 
			} 
			else { 
				$fsize = @filesize($filepath); 
			} 
			$path_parts = pathinfo($filepath);
			$ext = strtolower($path_parts["extension"]);
			$header_content = (array_key_exists($extension,$allowed_ext)) ? $allowed_ext[$extension] : 'application/txt';
			header("Content-type: $header_content"); // add here more headers for diff. extensions
			header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-Transfer-Encoding: binary");
			header("Content-length: $fsize");
			header("Cache-control: private"); //use this to open files directly
			ob_end_clean();
			while(!feof($fd)) {
				$buffer = fread($fd, 2048);
				echo $buffer;
			}
		}
	   fclose ($fd);
	   exit;
	 }
	 
	 public static function Compress($string){
	       return base64_encode(gzcompress($string));
	 }
	 public static function DeCompress($string){
	       return gzuncompress(base64_decode($string));
	 }
	 
	 public static function getDatedifference($postdate){
		$post_date = strtotime($postdate);
		$current_date = strtotime(date('Y-m-d H:i:s'));
		$secs = $current_date - $post_date. "Seconds Ago"; 
		$mins= floor($secs/60)." Minutes Ago";
		$hours= floor($secs/3600)." Hours Ago";
		$days = floor($secs/86400)." Days Ago";
		$differnce= (($secs>0 && $secs<60)? $secs : (($mins>1 && $mins<60)? $mins: (($secs>3559 && $secs<86400)? $hours : $days)));
		return $differnce;
	 }
	 
	/*Read The anguage File
	**function : read_file()
	**Description : This Function Read the Specified language file
	*/
	public static function getTranslations($language_id){
			$members = array();
		    $language_name = commonfunction::languagename($language_id);
			$filepath = ROOT_PATH.'/public/languages/'.ucfirst($language_name).'/'.strtolower($language_name).'.txt';
		   	if(file_exists($filepath)){
			$file = fopen($filepath,'r');
			
			$coun = 0;
			while (!feof($file)) {
			   if($coun<2000){
				$member="";
			   $member1 = fgets($file);
			   $member1 = explode('=',$member1);
			   if (substr($member1[0], 0, strlen('#')) == '#') {
					$member1[0] = substr($member1[0], strlen('#'));
				}
				if(isset($member1[1]) && strpos($member1[1],"#")>0){
				$member1[1] = str_replace("#","",$member1[1]);
				}
			   if(isset($member1[0]) && isset($member1[1])){
				   $members[str_replace("?","",$member1[0])] = $member1[1];
			   }
			 }else{
			 	break;
			 }
			}
			fclose($file);
			}
		   return array_filter($members); 
		}
	/*Language Array
	* function : languagename()
	* Date : 25/05/2017
	*/		
	public static function languagename($id = 1){
	   $languageArray=	array(1=>'English',2=>'French',3=>'Nederlands',4=>'Pools',5=>'Swedisch',6=>'German',7=>'Italy');
	   return $languageArray[$id];

	}
	public static function getTranslationArray(){
		 $ipadd = commonfunction::loggedinIP();
		  $json = file_get_contents("http://ipinfo.io/".$ipadd);
		 $details = json_decode($json);
		 $countrycode = isset($details->country)?trim($details->country):'EN';
		 $customObj = new Zend_Custom();
		 $result = $customObj->getTransalorList($countrycode);
		 return $result;
	}

	public function createdByDetails($user_id)
	{		
		 $data['created_ip'] = self::loggedinIP();
		 $data['created_by'] = $user_id;
		 $data['created_date'] = self::DateNow();
		 return $data;
	}

	public function modifiedByDetails($user_id)
	{		
		 $data['modify_ip'] = self::loggedinIP();
		 $data['modify_by'] = $user_id;
		 $data['modify_date'] = self::DateNow();
		 return $data;
	}

	public function jsonconvert($data)
    {
        header("Content-type:Application/json");
        return json_encode($data);
    }

}