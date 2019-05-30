<?php

class settings_Model_Routing extends Zend_Custom

{ 

   public $objPHPExcel;

   public function getweightClass(){

       $where = $this->LevelAsDepots();

	   if(isset($this->getData['country_id']) && $this->getData['country_id']>0){

	      $where .= " AND WC.country_id='".$this->getData['country_id']."'";

	   }

	   if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){

	     $where .= " AND WC.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";

	   }

	   if(isset($this->getData['service_id']) && $this->getData['service_id']>0){

	     $where .= " AND WC.service_id='".$this->getData['service_id']."'";

	   }

	    $select = $this->_db->select()

	   					->from(array('WC'=>ROUTING_WEIGHT_CLASS),array('COUNT(1) AS CNT'))

						->joininner(array('CT'=>COUNTRIES),"CT.country_id=WC.country_id",array(''))

						->joininner(array('ST'=>SERVICES),"ST.service_id=WC.service_id",array(''))

						->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=WC.user_id",array(''))

						->joinleft(array('PSV'=>SERVICES),"PSV.service_id=ST.parent_service", array(''))

						->where("1".$where)

						->group("WC.country_id")

						->group("WC.service_id")

						->group("WC.user_id");

			//echo $select->__toString();die;

		$total =  $this->getAdapter()->fetchAll($select);

	   

	   $OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'ST.service_id','ASC');	

	   $select = $this->_db->select()

	   					->from(array('WC'=>ROUTING_WEIGHT_CLASS),array('service_id','country_id','GROUP_CONCAT(CONCAT(min_weight,"--to--",max_weight)) as weight_class','GROUP_CONCAT(class_id) as class_ids'))

						->joininner(array('CT'=>COUNTRIES),"CT.country_id=WC.country_id",array('country_name'))

						->joininner(array('ST'=>SERVICES),"ST.service_id=WC.service_id",array('service_name'))

						->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=WC.user_id",array('company_name'))

						->joinleft(array('PSV'=>SERVICES),"PSV.service_id=ST.parent_service", array('service_name AS parent_name'))

						->where("1".$where)

						->group("WC.country_id")

						->group("WC.service_id")

						->group("WC.user_id")

						->order(new Zend_Db_Expr("CASE WHEN ST.parent_service=0 THEN ST.service_id ELSE ST.parent_service END"))

						->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])

		  				->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);

						//echo $select->__toString();die;

		$result =  $this->getAdapter()->fetchAll($select);

		return array('Total'=>count($total),'Records'=>$result);

   }

   public function CreateWeightClass(){ //echo "<pre>";print_r($this->getData);die;

	  global $objSession;

	  $this->getData['user_id']  = Zend_Encript_Encription::decode($this->getData['user_id']);

	  foreach($this->getData['country_id'] as $key=>$country_id){

	    foreach($this->getData['service_id'] as $servicekey=>$service_id){

	     foreach($this->getData['min_weight'] as $weightkey=>$min_weight){

	         if($min_weight!='' && $this->getData['max_weight'][$weightkey]!=''){

				 $select = $this->_db->select()

									->from(array('WC'=>ROUTING_WEIGHT_CLASS),array('COUNT(1) AS CNT'))

									->where("WC.country_id='".$country_id."' AND WC.service_id='".$service_id."' AND user_id ='".$this->getData['user_id']."'")

									->where("((WC.min_weight=".($min_weight)." AND WC.max_weight=".$this->getData['max_weight'][$weightkey].") OR  (WC.min_weight BETWEEN ".($min_weight+0.0001)." AND ".($this->getData['max_weight'][$weightkey]-0.0001).") OR (WC.max_weight BETWEEN ".($min_weight+0.0001)." AND ".($this->getData['max_weight'][$weightkey]-0.0001)."))");

				 //echo $select->__toString();die;

				 $validate = $this->getAdapter()->fetchRow($select);

				 if($validate['CNT']<=0){	

				   $this->_db->insert(ROUTING_WEIGHT_CLASS,array('country_id'=>$country_id,'service_id'=>$service_id,'min_weight'=>$min_weight,'max_weight'=>$this->getData['max_weight'][$weightkey],'user_id'=>$this->getData['user_id']));

				   $objSession->successMsg .= 'Weight class '.$min_weight.'-'.$this->getData['max_weight'][$weightkey].' Added<br>';

				 }else{

				    $services = $this->getServiceDetails($service_id,1);

					$objSession->errorMsg .= 'Weight class '.$min_weight.'-'.$this->getData['max_weight'][$weightkey].' Already Exist for '.$services['service_name'].'<br>';

				 }

		   } 

	     }

		}  

	  }

   }

   

   public function AddRouting(){ //echo "<pre>";print_r($this->getData);die;

        global $objSession;

		$this->getData['user_id']  = Zend_Encript_Encription::decode($this->getData['user_id']);

		foreach($this->getData['routingdata'] as $weightclass=>$routingprices){

		      $explodeweight = explode('-',$weightclass);

			  foreach($routingprices as $routingprice){
			      $select = $this->_db->select()
													->from(array('RT'=>ROUTING),array('COUNT(1) AS CNT'))
													->joininner(array('RP'=>ROUTING_POSTCODE),"RT.routing_id=RP.routing_id",array())
													->where("RT.country_id='".$this->getData['country_id']."' AND user_id ='".$this->getData['user_id']."'")
													->where("RP.beginPostCode IN('".commonfunction::implod_array($this->getData['beginPostCode'],"','")."') AND RP.endPostCode IN('".commonfunction::implod_array($this->getData['endPostCode'],"','")."')")
													->where("RT.service_id='".$routingprice['service_id']."'")
													->where("((RT.min_weight=".$explodeweight[0]." AND RT.max_weight=".$explodeweight[1].") OR  (RT.min_weight BETWEEN ".($explodeweight[0]+0.0001)." AND ".($explodeweight[1]-0.0001).") OR (RT.max_weight BETWEEN ".($explodeweight[0]+0.0001)." AND ".($explodeweight[1]-0.0001)."))");
											//echo $select->__toString();die;
							$validate = $this->getAdapter()->fetchRow($select);
							
					if($routingprice['routing_id']<=0 || $validate['CNT']<=0){
						 if($routingprice['price']>0 && $routingprice['forwarder_id']>0){
							$services = $this->getServiceDetails($routingprice['service_id'],1);
							$forwarder_name = $this->ForwarderName($routingprice['forwarder_id']);

						if($validate['CNT']<=0){

							$this->_db->insert(ROUTING,array('user_id'=>$this->getData['user_id'],

							'country_id'=>$this->getData['country_id'],

							'min_weight'=>$explodeweight[0],

							'max_weight'=>$explodeweight[1],

							'service_id'=>$routingprice['service_id'],

							'forwarder_id'=>$routingprice['forwarder_id'],

							'depot_price'=>$routingprice['price'],

							'created_date'=>new Zend_Db_Expr('NOW()'),

							'created_by'=>$this->Useconfig['user_id'],

							'created_ip'=>commonfunction::loggedinIP())); 

															

							$routing_id = $this->getAdapter()->lastInsertId();

							foreach($this->getData['beginPostCode'] as $key=>$beginPostcode){

							    if($key==0 && $beginPostcode!='' && $this->getData['endPostCode'][$key]!=''){

								   $this->_db->update(ROUTING,array('special_routing'=>1),"routing_id='".$routing_id."'");

								} 

								$this->_db->insert(ROUTING_POSTCODE,array('routing_id'=>$routing_id,

								'beginPostCode'=>$beginPostcode,

								'endPostCode'=>$this->getData['endPostCode'][$key]));

							}

							$objSession->successMsg .= '<br>Routing Added Details: '.$services['service_name'].' Added for weight-'.$explodeweight[0].'-to-'.$explodeweight[1].' with forwarder-'.$forwarder_name;

						   }else{

						     $objSession->errorMsg .= '<br>Routing Not added: '.$services['service_name'].' for weight-'.$explodeweight[0].'-to-'.$explodeweight[1].' due to duplicacy';

						   }	

					  } 
					}else{
							//$routing_log_id = $this->insertInToTable(ROUTING_EDITED,array($Routingfound));
							//$special_routing = ($this->getData['endPostCode']!='' && $this->getData['beginPostCode']!='')?1:0;
							$this->_db->update(ROUTING,array('depot_price'=>$routingprice['price'],'forwarder_id'=>$routingprice['forwarder_id']),"user_id= '".$this->getData['user_id']."' AND routing_id='".$routingprice['routing_id']."'"); 
						   foreach($this->getData['beginPostCode'] as $key=>$beginPostcode){
							    $specialrouting = ($key==0 && $beginPostcode!='' && $this->getData['endPostCode'][$key]!='')?1:0;
								 $this->_db->update(ROUTING,array('special_routing'=>$specialrouting ),"routing_id='".$routingprice['routing_id']."'");
							
								$this->_db->update(ROUTING_POSTCODE,array('beginPostCode'=>$beginPostcode,
																			'endPostCode'=>$this->getData['endPostCode'][$key]),"routing_id='".$routingprice['routing_id']."'");
							}
						  /* $this->_db->update(ROUTING_POSTCODE,array('beginPostCode'=>$this->getData['beginPostCode'],
																	'endPostCode'=>$this->getData['endPostCode']),"routing_id='".$routingprice['routing_id']."'");*/
					}  
					  

				}

		  }		

   }

   

   public function getRoutings(){

   		$where = $this->LevelAsDepots();

		   if(isset($this->getData['country_id']) && $this->getData['country_id']>0){

			  $where .= " AND RT.country_id='".$this->getData['country_id']."'";

		   }

		   if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){

			 $where .= " AND RT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";

		   }

		   if(isset($this->getData['service_id']) && $this->getData['service_id']>0){

			 $where .= " AND RT.service_id='".$this->getData['service_id']."'";

		   }

		   if(isset($this->getData['forwarder_id']) && $this->getData['forwarder_id']>0){

			 $where .= " AND RT.forwarder_id='".$this->getData['forwarder_id']."'";

		   }

		   if(isset($this->getData['min_weight'])  && isset($this->getData['max_weight']) && $this->getData['min_weight']!='' && $this->getData['max_weight']!=''){

			 $where .= " AND (RT.min_weight>='".$this->getData['min_weight']."' AND RT.max_weight<='".$this->getData['max_weight']."')";

		   }

		   

		  $select = $this->_db->select()

	   					->from(array('RT'=>ROUTING),array('COUNT(1) AS CNT'))

						->joininner(array('RP'=>ROUTING_POSTCODE),"RT.routing_id=RP.routing_id",array(''))

						->joininner(array('CT'=>COUNTRIES),"CT.country_id=RT.country_id",array(''))

						->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=RT.forwarder_id",array(''))

						->joininner(array('ST'=>SERVICES),"ST.service_id=RT.service_id",array(''))

						->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=RT.user_id",array(''))

						->joinleft(array('PS'=>SERVICES),"PS.service_id=ST.parent_service",array(''))

						->where("1".$where)

						->group(array('RT.country_id','RT.user_id','RT.min_weight','RT.max_weight','RT.special_routing'));

		$total = $this->getAdapter()->fetchAll($select);				

		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'AT.company_name','ASC');				  

        $select = $this->_db->select()

	   					->from(array('RT'=>ROUTING),array('min_weight','max_weight','GROUP_CONCAT(RT.routing_id) AS routing_id'))

						->joininner(array('RP'=>ROUTING_POSTCODE),"RT.routing_id=RP.routing_id",array('GROUP_CONCAT(DISTINCT beginPostCode) AS beginPostCode','GROUP_CONCAT(DISTINCT endPostCode) AS endPostCode'))

						->joininner(array('CT'=>COUNTRIES),"CT.country_id=RT.country_id",array('country_name'))

						->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=RT.forwarder_id",array(''))

						->joininner(array('ST'=>SERVICES),"ST.service_id=RT.service_id",array(''))

						->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=RT.user_id",array('company_name'))

						->joinleft(array('PS'=>SERVICES),"PS.service_id=ST.parent_service",array(''))

						->where("1".$where)

						->group(array('RT.country_id','RT.user_id','RT.min_weight','RT.max_weight','RT.special_routing'))

						->order("RT.max_weight ASC")

						->order("RT.min_weight ASC")

						->order("CT.country_name ASC")

						->order("AT.company_name ASC")

						->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])

		  				->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);

						//echo $select->__toString();die;

		$result =  $this->getAdapter()->fetchAll($select);

		return array('Total'=>count($total),'Records'=>$result);

   }

   public function getRoutingServices($routing_id){

          $select = $this->_db->select()

	   					->from(array('RT'=>ROUTING),array('customer_price','depot_price'))

						->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=RT.forwarder_id",array('forwarder_name'))

						->joininner(array('ST'=>SERVICES),"ST.service_id=RT.service_id",array('service_name'))

						->joinleft(array('PS'=>SERVICES),"PS.service_id=ST.parent_service",array('service_name AS parent_service'))

						->where("RT.routing_id IN(".$routing_id.")");

						//echo $select->__toString();die;

		return $this->getAdapter()->fetchAll($select);

		 

   }

   public function getRoutingByRoutingID(){

        $select = $this->_db->select()

						->from(array('RT'=>ROUTING),array('*'))

						->joininner(array('CT'=>COUNTRIES),"CT.country_id=RT.country_id",array('country_name'))

						->joininner(array('UD'=>USERS_DETAILS),"UD.user_id=RT.user_id",array('company_name'))

						->where("RT.routing_id IN(".$this->getData['routing_id'].")")

						->group("RT.routing_id");//echo $select->__toString();die;

		$routings =  $this->getAdapter()->fetchRow($select);

		

		$select = $this->_db->select()

						->from(array('RT'=>ROUTING),array('*',new Zend_Db_Expr('(SELECT GROUP_CONCAT(CONCAT(RP.beginPostCode,"-",RP.endPostCode)) FROM '.ROUTING_POSTCODE.' AS RP WHERE RP.routing_id=RT.routing_id GROUP BY RP.routing_id)  AS begin_endPostCode')))

						->joininner(array('ST'=>SERVICES),"ST.service_id=RT.service_id",array('service_name'))

						->joinleft(array('PS'=>SERVICES),"PS.service_id=ST.parent_service",array('service_name AS parent_service'))

						->where("RT.routing_id IN(".$this->getData['routing_id'].")");//echo $select->__toString();die;

		$Services =  $this->getAdapter()->fetchAll($select);

		//echo "<pre>";print_r(array('Routing'=>$routings,'Services'=>$services));die;

		return array('Routing'=>$routings,'Services'=>$Services);

   }

   public function EditRouting(){

       //echo "<pre>";print_r($this->getData);die;

	    global $objSession;

		   foreach($this->getData['routingdata'] as $routings){ 

			 foreach($routings as $routingprices){

			  if($routingprices['forwarder_id']>0){

			     $this->_db->update(ROUTING,array('forwarder_id'=>$routingprices['forwarder_id'],'depot_price'=>$routingprices['price']),"routing_id='".$routingprices['routing_id']."'");

			   }else{

			       $objSession->errorMsg = 'Can not update Routing without Forwarder!!';

				   return true;

			   }

		 	 }	

	    }

		$objSession->successMsg = 'Routing updated successfully!!';	

   }

   

   public function UpdateCustomer(){

	    global $objSession;

	   foreach($this->getData['routingdata'] as $routings){

	     foreach($routings as $routingprices){

		      $this->_db->update(ROUTING,array('customer_price'=>$routingprices['price']),"routing_id='".$routingprices['routing_id']."' AND service_id='".$routingprices['service_id']."'");

			 } 

		}

		$objSession->successMsg = 'Customer Price updated successfully!!';

   }

   public function getSpecialPriceCustomers(){

       $select = $this->_db->select()

						  ->from(array('UT'=>USERS),array('*'))

						  ->joininner(array('UD'=>USERS_DETAILS),"UT.user_id=UD.user_id",array('user_id','company_name','postalcode','city'))

						  ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=UD.user_id",array(''))

						  ->where('UT.user_status=?', '1')

						  ->where('UT.delete_status=?', '0')

						  ->where('UT.level_id=?', 5)

						  ->where('US.special_price=?', '1');

		 switch($this->Useconfig['level_id']){

		    case 4:

			  $select->where('UD.parent_id=?',$this->Useconfig['user_id']);

			break;

			case 5:

			   $select->where('UD.user_id=?',$this->Useconfig['user_id']);

			break;

			case 6:

			$depot_id = $this->getDepotID($this->user_session['user_id']);

			$this->select->where->equalTo('UD.parent_id', $depot_id);

			break;

			case 10:

			$parent_id = $this->getDepotID($this->user_session['user_id']);

			$this->select->where->equalTo('UD.user_id', $parent_id);

			break;

		}

		

		 $select->order("UD.company_name ASC");				  

		return $this->getAdapter()->fetchAll($select);

   }

   

   public function UpdateSpecial(){ //echo "<pre>";print_r($this->getData['routingdata']);die;

       global $objSession;

       foreach($this->getData['routingdata'] as $routings){

		    foreach($routings as $specialprices){

			 $select = $this->_db->select()

	   					->from(array('SP'=>ROUTING_SPECIAL_PRICE),array('*'))

						->where("SP.routing_id='".$specialprices['routing_id']."' AND  SP.user_id='".$this->getData['user_id']."' AND  SP.service_id='".$specialprices['service_id']."'");

		     $specialPrice =  $this->getAdapter()->fetchRow($select);

			 if(!empty($specialPrice)){

			    $this->_db->update(ROUTING_SPECIAL_PRICE,array('special_price'=>$specialprices['price']),"routing_id='".$specialprices['routing_id']."' AND service_id='".$specialprices['service_id']."' AND user_id='".$this->getData['user_id']."'");

			 }else{

			   $this->_db->insert(ROUTING_SPECIAL_PRICE,array_filter(array('routing_id'=>$specialprices['routing_id'],'user_id'=>$this->getData['user_id'],'service_id'=>$specialprices['service_id'],'special_price'=>$specialprices['price'])));

			 }

		  }	  

		}

		$objSession->successMsg = 'Scpecial Price updated successfully!!';

   }

   

   public function getSpecialPrices(){ 

        $select = $this->_db->select()

	   					->from(array('SP'=>ROUTING_SPECIAL_PRICE),array('*'))

						->where("SP.routing_id IN(".$this->getData['routing_id'].") AND  SP.user_id='".$this->getData['user_id']."'");

		$services =  $this->getAdapter()->fetchAll($select);

		$dataPrice = array();

		foreach($services as $servicepri){

		  $dataPrice[$servicepri['service_id']][$servicepri['routing_id']]  = $servicepri['special_price'];

		}

		return $dataPrice;

   }

   

   public function getAllServicesList(){

       try{

	    $select = $this->_db->select()

                ->from(array('SV'=>SERVICES), array('*'))

				->joinleft(array('PSV'=>SERVICES),"PSV.service_id=SV.parent_service", array('service_name AS parent_name'))

				->where("SV.status='1'")

				->order("SV.service_id ASC");//print_r($select->__toString());die;

       }catch(Exception $e){

	     echo $e->getMessage();die;

	   }

	  return $this->getAdapter()->fetchAll($select);

	   

   }

   

   public function deleteclass(){

        global $objSession;

		if(isset($this->getData['class_ids']) && $this->getData['class_ids']!=''){

			$this->_db->delete(ROUTING_WEIGHT_CLASS,"class_id IN(".$this->getData['class_ids'].")");

			$objSession->successMsg .= 'Weight Class(s) Deleted successfully!';

		}

		return true;

   }

   

   public function deleterouting(){

        global $objSession;

	   $select = $this->_db->select()

								->from(array('RT'=>ROUTING),array('*'))

								->where("routing_id IN(".$this->getData['routing_id'].")");

							//echo $select->__toString();die;

		$routings = $this->getAdapter()->fetchAll($select);

		foreach($routings as $routing){

			$routing['deleted_by'] = $this->Useconfig['user_id'];

		    $routing['deleted_date'] = commonfunction::DateNow();

			$routing['deleted_ip'] = commonfunction::loggedinIP();

			$this->insertInToTable(ROUTING_DELETED,array($routing));

			$this->_db->delete(ROUTING,"routing_id='".$routing['routing_id']."'");

			$this->_db->delete(ROUTING_POSTCODE,"routing_id='".$routing['routing_id']."'");

			$objSession->successMsg = 'Routing Deleted successfully!!';

		}

   }

   

   public function serviceweightClass(){

       $where = $this->LevelAsDepots();

	   $select = $this->_db->select()

	   					->from(array('RT'=>ROUTING),array('*'))

						->joininner(array('RP'=>ROUTING_POSTCODE),"RT.routing_id=RP.routing_id",array('GROUP_CONCAT(DISTINCT beginPostCode) AS beginPostCode','GROUP_CONCAT(DISTINCT endPostCode) AS endPostCode'))

						->joininner(array('CT'=>COUNTRIES),"CT.country_id=RT.country_id",array('country_name'))

						->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=RT.forwarder_id",array('forwarder_name'))

						->joininner(array('ST'=>SERVICES),"ST.service_id=RT.service_id",array(''))

						->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=RT.user_id",array('company_name'))

						->joinleft(array('PS'=>SERVICES),"PS.service_id=ST.parent_service",array(''))

						->where("RT.country_id='".$this->getData['country_id']."' AND RT.service_id='".$this->getData['service_id']."'".$where)

						->group("RT.routing_id")

						->order("RT.max_weight ASC")

						->order("RT.min_weight ASC");

						//echo $select->__toString();die;

		return $this->getAdapter()->fetchAll($select);

   }

   public function servicespecialPrice(){

       $where = $this->LevelAsDepots();

	   $select = $this->_db->select()

	   					->from(array('RT'=>ROUTING),array('*'))

						->joininner(array('RP'=>ROUTING_POSTCODE),"RT.routing_id=RP.routing_id",array('GROUP_CONCAT(DISTINCT beginPostCode) AS beginPostCode','GROUP_CONCAT(DISTINCT endPostCode) AS endPostCode'))

						->joinleft(array('SP'=>ROUTING_SPECIAL_PRICE),"SP.routing_id=RT.routing_id AND SP.service_id=RT.service_id AND SP.user_id='".$this->getData['user_id']."'",array('special_price'))

						->joininner(array('CT'=>COUNTRIES),"CT.country_id=RT.country_id",array('country_name'))

						->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=RT.forwarder_id",array('forwarder_name'))

						->joininner(array('ST'=>SERVICES),"ST.service_id=RT.service_id",array(''))

						->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=RT.user_id",array('company_name'))

						->joinleft(array('PS'=>SERVICES),"PS.service_id=ST.parent_service",array(''))

						->where("RT.country_id='".$this->getData['country_id']."' AND RT.service_id='".$this->getData['service_id']."'".$where)

						->group("RT.routing_id")

						->order("RT.max_weight ASC")

						->order("RT.min_weight ASC");

						//echo $select->__toString();die;

		return $this->getAdapter()->fetchAll($select);

   }

   

   public function ImportDepotRouting(){

       $file_name = commonfunction::ImportFile('import_routing','xlsx',1); 

	   $inputFileType = PHPExcel_IOFactory::identify($file_name);

	   $objReader = PHPExcel_IOFactory::createReader($inputFileType); 

	   //$objReader->setReadDataOnly(true);

	   $reader = $objReader->load($file_name);

	   $data = $reader->getSheet(0);

	   $k = $data->getHighestRow();

	   //$totalColumns = $data->getHighestColumn();

	   $classcount = 0;

	   $dataArr = array();

	   $colors[$reader->getActiveSheet()->getStyle('Y2')->getFill()->getEndColor()->getRGB()] = 6;

	   $colors[$reader->getActiveSheet()->getStyle('Y3')->getFill()->getEndColor()->getRGB()] = 3;

	   $colors[$reader->getActiveSheet()->getStyle('Y4')->getFill()->getEndColor()->getRGB()] = 20;

	   $colors[$reader->getActiveSheet()->getStyle('Y5')->getFill()->getEndColor()->getRGB()] = 40;

	   $colors[$reader->getActiveSheet()->getStyle('Y6')->getFill()->getEndColor()->getRGB()] = 30;

	   $colors[$reader->getActiveSheet()->getStyle('Y7')->getFill()->getEndColor()->getRGB()] = 46;

	   $colors[$reader->getActiveSheet()->getStyle('Y8')->getFill()->getEndColor()->getRGB()] = 36;

	   

	   $columns = range('A', 'Z');

		for($j=4;$j<24;$j++){

			for($i=1;$i<=$k;$i++){

			     $countryData = $this->getCountryDetail($this->getCell($data,$i,2),2);

			     if(!empty( $countryData)){

					 $Arr['country_id'] = $countryData['country_id'];

					 $Arr['max_weight'] = commonfunction::stringReplace(',','.',$this->getCell($data,0,$j));

					 $Arr['min_weight'] = ($Arr['max_weight']-0.1);

					 $Arr['price'] =  $this->getCell($data,$i,$j);

					 $color = $reader->getActiveSheet()->getStyle($columns[($j-1)].($i+1))->getFill()->getEndColor()->getRGB();

					 $Arr['forwarder_id'] = $colors[$color];

					 $dataArr[] = $Arr;

				 }

				

			 }

	   }// echo "<pre>";print_r($dataArr);die;

	   $this->ImportRoutings($dataArr);

	   print_r('ECho');die;

   }

   

   public function ImportRoutings_old_backup($dataArr){

       $service_id = 115;

	   foreach($dataArr as $data){

	        $this->getData = $data;

			$this->getData['user_id'] = 188;

	        $select = $this->_db->select()

									->from(array('WC'=>ROUTING_WEIGHT_CLASS),array('COUNT(1) AS CNT'))

									->where("WC.country_id='".$this->getData['country_id']."' AND WC.service_id='".$service_id."' AND user_id =188")

									->where("((WC.min_weight=".$this->getData['min_weight']." AND WC.max_weight=".$this->getData['max_weight'].") OR  (WC.min_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001).") OR (WC.max_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001)."))");

				// echo $select->__toString();die;

				 $validate = $this->getAdapter()->fetchRow($select);

				 if($validate['CNT']<=0){	

				   $this->_db->insert(ROUTING_WEIGHT_CLASS,array('country_id'=>$this->getData['country_id'],'service_id'=>$service_id,'min_weight'=>$this->getData['min_weight'],'max_weight'=>$this->getData['max_weight'],'user_id'=>188));

				 }

				 $select = $this->_db->select()

								->from(array('RT'=>ROUTING),array('COUNT(1) AS CNT'))

								->where("RT.country_id='".$this->getData['country_id']."' AND user_id ='".$this->getData['user_id']."'")

								->where("RT.service_id='".$service_id."'")

								->where("((RT.min_weight=".$this->getData['min_weight']." AND RT.max_weight=".$this->getData['max_weight'].") OR  (RT.min_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001).") OR (RT.max_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001)."))");

											//echo $select->__toString();die;

						$validateRouting = $this->getAdapter()->fetchRow($select);

						if($validateRouting['CNT']<=0 && $this->getData['price']>0){  

							$this->_db->insert(ROUTING,array('user_id'=>$this->getData['user_id'],

															'country_id'=>$this->getData['country_id'],

															'min_weight'=>$this->getData['min_weight'],

															'max_weight'=>$this->getData['max_weight'],

															'service_id'=>$service_id,

															'forwarder_id'=>$this->getData['forwarder_id'],

															'depot_price'=>$this->getData['price'],

															'created_date'=>new Zend_Db_Expr('NOW()'),

															'created_by'=>$this->Useconfig['user_id'],

															'created_ip'=>commonfunction::loggedinIP()));

							$routing_id = $this->getAdapter()->lastInsertId();

							$this->_db->insert(ROUTING_POSTCODE,array('routing_id'=>$routing_id,'beginPostCode'=>'','endPostCode'=>''));								

					 }										  

				 

	   }

      

   }

   

   

   public function getCell(&$worksheet,$row,$col,$default_val='') {

 			 $col -= 1; // we use 1-based, PHPExcel uses 0-based column index

 			 $row += 1; // we use 0-based, PHPExcel used 1-based row index

  			 return ($worksheet->cellExistsByColumnAndRow($col,$row)) ? $worksheet->getCellByColumnAndRow($col,$row)->getValue() : $default_val;

   }

   

   public function ImportExportRouting(){

     global $objSession;

	 $this->getData['user_id'] = ($this->getData['user_id']!='')?Zend_Encript_Encription::decode($this->getData['user_id']):0;

	 switch($this->getData['import_export_mode']){

	     case 1:

		     $this->EI_ExporRuting();

		 break;

		 case 2:

		    $routingData = $this->EI_RoutingData();

			$this->EI_ImportRouting($routingData);

			$objSession->successMsg = 'Routing Imported successfully!!';

		 break;

		 case 3:

		    $routingData = $this->EI_RoutingData();

			$this->EI_UpdateDepotPrice($routingData);

			$objSession->successMsg = 'Depot Price Updated successfully!!';

		 break;

		 case 4:

		   $this->EI_ExporRuting(true);

		 break;

		 case 5:

		   if($this->getData['user_id']>0){

		      $this->EI_ExporSpecialPrice();

			}else{

			  $objSession->errorMsg = 'Please Select customer!!';

			}

		 break;

		case 6:

		    $routingData = $this->EI_RoutingData();

			$this->EI_UpdateCustomerPrice($routingData);

			$objSession->successMsg = 'Customer Price Updated successfully!!';

		break;

		case 7:

		    $routingData = $this->EI_RoutingData();

			$this->EI_UpdateSpecialPrice($routingData);

			$objSession->successMsg = 'Special Price Updated successfully!!';

		break; 

	 }

   }

   

   public function EI_ExporRuting($depot=false){

       $where = $this->LevelAsDepots();

	   if($this->getData['user_id']>0 && !$depot){

	     $where .= " AND RT.user_id='".$this->getData['user_id']."'";

	   }

	    $select = $this->_db->select()

	   					->from(array('RT'=>ROUTING),array('*'))

						->joininner(array('RP'=>ROUTING_POSTCODE),"RT.routing_id=RP.routing_id",array('beginPostCode','endPostCode'))

						->joininner(array('CT'=>COUNTRIES),"CT.country_id=RT.country_id",array('country_name','cncode'))

						->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=RT.forwarder_id",array('forwarder_name'))

						->joininner(array('ST'=>SERVICES),"ST.service_id=RT.service_id",array('internal_code','service_name'))

						->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=RT.user_id",array('company_name'))

						->joinleft(array('PS'=>SERVICES),"PS.service_id=ST.parent_service",array('service_name as parent_service'))

						->where("1".$where)

						->order("RT.max_weight ASC")

						->order("RT.min_weight ASC")

						->order("CT.country_name ASC")

						->order("AT.company_name ASC");

						//echo $select->__toString();die;

		$results =  $this->getAdapter()->fetchAll($select);
			ini_set("memory_limit", "-1");
			set_time_limit(0);
		   //ini_set("memory_limit","512M");

		  //ini_set("max_execution_time",180);

		  $this->objPHPExcel = new PHPExcel();

		  $this->objPHPExcel->setActiveSheetIndex(0);

		  $this->objPHPExcel->getActiveSheet()->setCellValue('A1', "Country");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('B1', "Min Weight");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('C1', "Max Weight");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('D1', "Service Code");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('E1', "Forwarder Name");

		  if($depot){

		     $this->objPHPExcel->getActiveSheet()->setCellValue('F1', "Customer Price");

		  }else{

		     $this->objPHPExcel->getActiveSheet()->setCellValue('F1', "Depot Price");

		  }

		  $this->objPHPExcel->getActiveSheet()->setCellValue('G1', "BeginPostcode");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('H1', "EndPostcode");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('I1', "Service");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('J1', "Parent Service");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('K1', "Company");

		  $this->objPHPExcel->getActiveSheet()

    		->getStyle('A1:K1')

    		->getFill()

    		->setFillType(PHPExcel_Style_Fill::FILL_SOLID)

    		->getStartColor()

    		->setARGB('FFFF50');

		$row = 2;

		foreach($results as $result){

		   $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$result['cncode']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$result['min_weight']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$result['max_weight']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$result['internal_code']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('E'.$row,$result['forwarder_name']);

		   if($depot){

			 $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$result['customer_price']);

		  }else{

			 $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$result['depot_price']);

		  }

		   

		   $this->objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$result['beginPostCode']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$result['endPostCode']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('I'.$row,$result['service_name']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('J'.$row,$result['parent_service']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('K'.$row,$result['company_name']);

		   $row++;

		}

		$letters = range('A', 'K');

		foreach ($letters as $letter_one) 

		{

			$this->objPHPExcel->getActiveSheet()->getColumnDimension($letter_one)->setAutoSize(true);

		}

		 $styleArray = array(

		  'borders' => array(

			'allborders' => array(

			  'style' => PHPExcel_Style_Border::BORDER_THIN

			)

		  )

		);

		$this->objPHPExcel->getActiveSheet()->getStyle('A1:K'.$row)->applyFromArray($styleArray);

		unset($styleArray);

		$this->objPHPExcel->setActiveSheetIndex(0);

		//$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');

		

		header('Content-Type: application/vnd.ms-excel');

		header('Content-Disposition: attachment;filename="Routing.xls"');

		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');

		ob_end_clean();

		$objWriter->save('php://output');

		$this->objPHPExcel->disconnectWorksheets();

		unset($this->objPHPExcel);die;

   }

   

   

   public function EI_RoutingData(){

       $file_name = commonfunction::ImportFile('import_routing','xlsx',1); 

	   $inputFileType = PHPExcel_IOFactory::identify($file_name);

	   $objReader = PHPExcel_IOFactory::createReader($inputFileType); 

	   //$objReader->setReadDataOnly(true);

	   $reader = $objReader->load($file_name);

	   $data = $reader->getSheet(0);

	   $k = $data->getHighestRow();

	   $RoutingData = array();

      for($i=1;$i<=$k;$i++){

			    $countryData = $this->getCountryDetail($this->getCell($data,$i,1),2);

				$Arr['country_id'] = $countryData['country_id'];

				$Arr['min_weight'] = commonfunction::stringReplace(',','.',$this->getCell($data,$i,2));

				$Arr['max_weight'] = commonfunction::stringReplace(',','.',$this->getCell($data,$i,3));

				$serviceData = $this->getServiceDetails($this->getCell($data,$i,4),2);

				$Arr['service_id'] = $serviceData['service_id'];

				$FOrwarderData = $this->getForwarderID($this->getCell($data,$i,5));

				$Arr['forwarder_id'] = $FOrwarderData['forwarder_id'];

				$Arr['depot_price'] = commonfunction::stringReplace(',','.',$this->getCell($data,$i,6));

				$Arr['beginPostCode'] = $this->getCell($data,$i,7);

				$Arr['endPostCode'] = $this->getCell($data,$i,8);
				//$Arr['customer_price'] = $this->getCell($data,$i,9);

				switch($this->Useconfig['level_id']){

				    case 1:

					case 11:

					  $Arr['user_id'] = $this->getData['user_id'];

					break;

					case 4:

					  $Arr['user_id'] = $this->Useconfig['user_id'];

					break;

					case 6:

					  $Arr['user_id'] = $this->Useconfig['parent_id'];

					break;

					

				}

				//$Arr['user_id'] = $this->getData['user_id'];

				$RoutingData[] = $Arr;

	  }

	  return $RoutingData;

   }

   

   

   public function EI_ImportRouting($routingData){

       $depot_id = $this->getData['user_id'];
	   foreach($routingData as $routing){

	       $this->getData = $routing;

	       if($routing['service_id']>0 && $routing['max_weight']>0 && $this->getData['user_id']>0){

	        $select = $this->_db->select()

									->from(array('WC'=>ROUTING_WEIGHT_CLASS),array('COUNT(1) AS CNT'))

									->where("WC.country_id='".$this->getData['country_id']."' AND WC.service_id='".$this->getData['service_id']."' AND user_id ='".$this->getData['user_id']."'")

									->where("((WC.min_weight=".$this->getData['min_weight']." AND WC.max_weight=".$this->getData['max_weight'].") OR  (WC.min_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001).") OR (WC.max_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001)."))");

				// echo $select->__toString();die;

				 $validate = $this->getAdapter()->fetchRow($select);

				 if($validate['CNT']<=0){	

				   $this->_db->insert(ROUTING_WEIGHT_CLASS,array('country_id'=>$this->getData['country_id'],'service_id'=>$this->getData['service_id'],'min_weight'=>$this->getData['min_weight'],'max_weight'=>$this->getData['max_weight'],'user_id'=>$this->getData['user_id']));

				 }

				/* $select = $this->_db->select()

								->from(array('RT'=>ROUTING),array('COUNT(1) AS CNT'))

								->where("RT.country_id='".$this->getData['country_id']."' AND user_id ='".$this->getData['user_id']."'")

								->where("RT.service_id='".$this->getData['service_id']."'")

								->where("((RT.min_weight=".$this->getData['min_weight']." AND RT.max_weight=".$this->getData['max_weight'].") OR  (RT.min_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001).") OR (RT.max_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001)."))");*/
						$select = $this->_db->select()
									->from(array('RT'=>ROUTING),array('*'))
									->joininner(array('RP'=>ROUTING_POSTCODE),"RT.routing_id=RP.routing_id",array('*'))
									->where("RT.country_id='".$this->getData['country_id']."' AND user_id ='".$this->getData['user_id']."'")
									->where("RP.beginPostCode='".$this->getData['beginPostCode']."' AND RP.endPostCode='".$this->getData['endPostCode']."'")
									->where("RT.service_id='".$this->getData['service_id']."'")
									->where("((RT.min_weight=".$this->getData['min_weight']." AND RT.max_weight=".$this->getData['max_weight'].") OR  (RT.min_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001).") OR (RT.max_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001)."))");

											//echo $select->__toString();die;

						$Routingfound = $this->getAdapter()->fetchRow($select);
						if(empty($Routingfound)){  
							$this->_db->insert(ROUTING,array('user_id'=>$this->getData['user_id'],

															'country_id'=>$this->getData['country_id'],

															'min_weight'=>$this->getData['min_weight'],

															'max_weight'=>$this->getData['max_weight'],

															'service_id'=>$this->getData['service_id'],

															'forwarder_id'=>$this->getData['forwarder_id'],

															'depot_price'=>$this->getData['depot_price'],

															'created_date'=>new Zend_Db_Expr('NOW()'),

															'created_by'=>$this->Useconfig['user_id'],

															'created_ip'=>commonfunction::loggedinIP()));

							$routing_id = $this->getAdapter()->lastInsertId();
							if($this->getData['endPostCode']!='' && $this->getData['beginPostCode']!=''){
								$this->_db->update(ROUTING,array('special_routing'=>1),"routing_id='".$routing_id."'");
						    }
								   $this->_db->insert(ROUTING_POSTCODE,array('routing_id'=>$routing_id,
																			'beginPostCode'=>$this->getData['beginPostCode'],
																			'endPostCode'=>$this->getData['endPostCode']));

					 }
					 else{
							$routing_log_id = $this->insertInToTable(ROUTING_EDITED,array($Routingfound));
							$special_routing = ($this->getData['endPostCode']!='' && $this->getData['beginPostCode']!='')?1:0;
							$this->_db->update(ROUTING,array('depot_price'=>$this->getData['depot_price'],'forwarder_id'=>$this->getData['forwarder_id'],'special_routing'=>$special_routing,'log_id'=>$routing_log_id),"user_id= '".$this->getData['user_id']."' AND routing_id='".$Routingfound['routing_id']."'"); 
						   
						   $this->_db->update(ROUTING_POSTCODE,array('beginPostCode'=>$this->getData['beginPostCode'],
																	'endPostCode'=>$this->getData['endPostCode']),"routing_id='".$Routingfound['routing_id']."'"); 
					}										  
		   }

	   }

   }

   

   public function EI_UpdateDepotPrice($routingData){

       $depot_id = $this->getData['user_id'];

	   foreach($routingData as $routing){

	       $this->getData = $routing;

	       if($routing['service_id']>0 && $routing['max_weight']>0 && $this->getData['user_id']>0){

		      $select = $this->_db->select()
									->from(array('RT'=>ROUTING),array('*'))
									->joininner(array('RP'=>ROUTING_POSTCODE),"RT.routing_id=RP.routing_id",array())
									->where("RT.country_id='".$this->getData['country_id']."' AND user_id ='".$this->getData['user_id']."'")
									->where("RP.beginPostCode='".$this->getData['beginPostCode']."' AND RP.endPostCode='".$this->getData['endPostCode']."'")
									->where("RT.service_id='".$this->getData['service_id']."'")
									->where("((RT.min_weight=".$this->getData['min_weight']." AND RT.max_weight=".$this->getData['max_weight'].") OR  (RT.min_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001).") OR (RT.max_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001)."))");

				//echo $select->__toString();die;

				$Routingfound = $this->getAdapter()->fetchRow($select);

				if(!empty($Routingfound)){

				   $routing_log_id = $this->insertInToTable(ROUTING_EDITED,array($Routingfound));

				   $this->_db->update(ROUTING,array('depot_price'=>$this->getData['depot_price'],'log_id'=>$routing_log_id),"user_id= '".$this->getData['user_id']."' AND routing_id='".$Routingfound['routing_id']."'");

				}

		   }

	   }

   }

   

   public function EI_UpdateCustomerPrice($routingData){
		//echo "<pre>";print_r($routingData);die;
	   foreach($routingData as $routing){

	       $this->getData = $routing;

	       if($routing['service_id']>0 && $routing['max_weight']>0 && $this->getData['user_id']>0){

		      $select = $this->_db->select()
									->from(array('RT'=>ROUTING),array('*'))
									->joininner(array('RP'=>ROUTING_POSTCODE),"RT.routing_id=RP.routing_id",array())
									->where("RT.country_id='".$this->getData['country_id']."' AND user_id ='".$this->getData['user_id']."'")
									->where("RP.beginPostCode='".$this->getData['beginPostCode']."' AND RP.endPostCode='".$this->getData['endPostCode']."'")
									->where("RT.service_id='".$this->getData['service_id']."'")
									->where("((RT.min_weight=".$this->getData['min_weight']." AND RT.max_weight=".$this->getData['max_weight'].") OR  (RT.min_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001).") OR (RT.max_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001)."))");

				//echo $select->__toString();die;

				$Routingfound = $this->getAdapter()->fetchRow($select);

				if(!empty($Routingfound)){

				   $routing_log_id = $this->insertInToTable(ROUTING_EDITED,array($Routingfound));

				   $this->_db->update(ROUTING,array('customer_price'=>$this->getData['depot_price'],'log_id'=>$routing_log_id),"routing_id='".$Routingfound['routing_id']."'");

				}

		   }

	   }

   }

   

   public function EI_UpdateSpecialPrice($routingData){

	   $user_id = $this->getData['user_id'];

	   foreach($routingData as $routing){

	       $this->getData = $routing;

	       if($routing['service_id']>0 && $routing['max_weight']>0 && $this->getData['user_id']>0){

		      $select = $this->_db->select()
									->from(array('RT'=>ROUTING),array('*'))
									->joininner(array('RP'=>ROUTING_POSTCODE),"RT.routing_id=RP.routing_id",array())
									->where("RT.country_id='".$this->getData['country_id']."' AND user_id ='".$this->getData['user_id']."'")
									->where("RP.beginPostCode='".$this->getData['beginPostCode']."' AND RP.endPostCode='".$this->getData['endPostCode']."'")
									->where("RT.service_id='".$this->getData['service_id']."'")
									->where("((RT.min_weight=".$this->getData['min_weight']." AND RT.max_weight=".$this->getData['max_weight'].") OR  (RT.min_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001).") OR (RT.max_weight BETWEEN ".($this->getData['min_weight']+0.0001)." AND ".($this->getData['max_weight']-0.0001)."))");

				//echo $select->__toString();die;

				$Routingfound = $this->getAdapter()->fetchRow($select);

				if(!empty($Routingfound)){

				   //$routing_log_id = $this->insertInToTable(ROUTING_EDITED,array($Routingfound));

				   //$this->_db->update(ROUTING,array('customer_price'=>$this->getData['depot_price'],'log_id'=>$routing_log_id),"user_id= '".$this->getData['user_id']."' AND routing_id='".$Routingfound['routing_id']."'");

				     $select = $this->_db->select()

								->from(array('SP'=>ROUTING_SPECIAL_PRICE),array('*'))

								->where("SP.user_id='".$user_id."' AND routing_id ='".$Routingfound['routing_id']."'")

								->where("SP.service_id='".$Routingfound['service_id']."'");

				//echo $select->__toString();die;

				    $SpecialPricefound = $this->getAdapter()->fetchRow($select);

					if(!empty($SpecialPricefound)){

					   $this->_db->update(ROUTING_SPECIAL_PRICE,array('special_price'=>$this->getData['depot_price']),"user_id= '".$user_id."' AND routing_id='".$Routingfound['routing_id']."'  AND service_id='".$Routingfound['service_id']."'"); 

					}else{

					    $this->_db->insert(ROUTING_SPECIAL_PRICE,array('special_price'=>$this->getData['depot_price'],'user_id'=>$user_id,'routing_id'=>$Routingfound['routing_id'],'service_id'=>$Routingfound['service_id'])); 

					}

				}

		   }

	   }

   }

   

   public function EI_ExporSpecialPrice(){

       $where = $this->LevelAsDepots();

	   if($this->getData['user_id']>0){

	     $where .= " AND SP.user_id='".$this->getData['user_id']."'";

	   }

	    $select = $this->_db->select()

	   					->from(array('RT'=>ROUTING),array('*'))

						->joininner(array('RP'=>ROUTING_POSTCODE),"RT.routing_id=RP.routing_id",array('beginPostCode','endPostCode'))

						->joininner(array('CT'=>COUNTRIES),"CT.country_id=RT.country_id",array('country_name','cncode'))

						->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=RT.forwarder_id",array('forwarder_name'))

						->joininner(array('ST'=>SERVICES),"ST.service_id=RT.service_id",array('internal_code','service_name'))

						->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=RT.user_id",array('company_name'))

						->joininner(array('SP'=>ROUTING_SPECIAL_PRICE),"SP.routing_id=RT.routing_id AND SP.service_id=RT.service_id",array('*'))

						->joininner(array('SC'=>USERS_DETAILS),"SC.user_id=SP.user_id",array('company_name AS customer_name'))

						->joinleft(array('PS'=>SERVICES),"PS.service_id=ST.parent_service",array('service_name as parent_service'))

						->where("1".$where)

						->order("RT.max_weight ASC")

						->order("RT.min_weight ASC")

						->order("CT.country_name ASC")

						->order("AT.company_name ASC");

						//echo $select->__toString();die;

		$results =  $this->getAdapter()->fetchAll($select);

		   ini_set("memory_limit","512M");

		  ini_set("max_execution_time",180);

		  $this->objPHPExcel = new PHPExcel();

		  $this->objPHPExcel->setActiveSheetIndex(0);

		  $this->objPHPExcel->getActiveSheet()->setCellValue('A1', "Country");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('B1', "Min Weight");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('C1', "Max Weight");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('D1', "Service Code");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('E1', "Forwarder Name");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('F1', "Special Price");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('G1', "BeginPostcode");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('H1', "EndPostcode");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('I1', "Service");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('J1', "Parent Service");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('K1', "Company");

		  $this->objPHPExcel->getActiveSheet()

    		->getStyle('A1:K1')

    		->getFill()

    		->setFillType(PHPExcel_Style_Fill::FILL_SOLID)

    		->getStartColor()

    		->setARGB('FFFF50');

		$row = 2;

		foreach($results as $result){

		   $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$result['cncode']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$result['min_weight']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$result['max_weight']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$result['internal_code']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('E'.$row,$result['forwarder_name']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$result['special_price']);

		   

		   $this->objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$result['beginPostCode']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$result['endPostCode']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('I'.$row,$result['service_name']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('J'.$row,$result['parent_service']);

		   $this->objPHPExcel->getActiveSheet()->setCellValue('K'.$row,$result['customer_name']);

		   $row++;

		}

		$letters = range('A', 'K');

		foreach ($letters as $letter_one) 

		{

			$this->objPHPExcel->getActiveSheet()->getColumnDimension($letter_one)->setAutoSize(true);

		}

		 $styleArray = array(

		  'borders' => array(

			'allborders' => array(

			  'style' => PHPExcel_Style_Border::BORDER_THIN

			)

		  )

		);

		$this->objPHPExcel->getActiveSheet()->getStyle('A1:K'.$row)->applyFromArray($styleArray);

		unset($styleArray);

		$this->objPHPExcel->setActiveSheetIndex(0);

		//$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');

		

		header('Content-Type: application/vnd.ms-excel');

		header('Content-Disposition: attachment;filename="SpecialPrice.xls"');

		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');

		ob_end_clean();

		$objWriter->save('php://output');

		$this->objPHPExcel->disconnectWorksheets();

		unset($this->objPHPExcel);die;

   }

   

   

   public function getForwarderID($forwardername){

        $select = $this->_db->select()

						  ->from(array('FT'=>FORWARDERS),array('*'))

						  ->where("LOWER(FT.forwarder_name)='".$forwardername."'");

        return $this->getAdapter()->fetchRow($select);

   }

   

   

}



