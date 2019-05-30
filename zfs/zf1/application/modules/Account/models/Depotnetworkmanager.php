<?php
class Account_Model_Depotnetworkmanager extends Zend_Custom
{
	/**

     * Controll the Depot Network Routing (For Depot) module

     * @Auth : SJM softech Pvt. Ltd.

     * @Created Date: 4-9-17th-September-2017

     * @Description : Controll the functionality related to Depot Network Routing

     **/

    public function __construct()
    {   
		parent::__construct();
	}


	public function getAllDepotRouting()
	{	
		$depot_id = Zend_Encript_Encription::decode($this->getData['token']);
		$data = array();
    	$select = $this->_db->select()->from(array('DNR'=>DepotNetworkRouting),array('DNR.country_id','DNR.depot_id','DNR.service_id','DNR.dn_routing_id'))
    		->joininner(array('DNRD'=>DepotNetworkRoutingDetails),'DNR.dn_routing_id = DNRD.dn_routing_id',array('DNRD.postcode_start','DNRD.postcode_end','DNRD.sc_routing_id','GROUP_CONCAT(DNRD.postcode_start,"-",DNRD.postcode_end,"-",PR.routename) as postcodes'))
    		->joininner(array('US'=>USERS_DETAILS),'DNR.depot_id = US.user_id',array('US.company_name'))
    		->joininner(array('CN'=>COUNTRIES),'DNR.country_id = CN.country_id',array('CN.country_name'))
    		->joininner(array('SV'=>SERVICES),'DNR.service_id = SV.service_id',array('SV.service_name'))
    		->joininner(array('PR'=>PLANNER_ROUTELIST),' DNRD.sc_routing_id = PR.route_id',array('PR.routename'))
    		->where("DNR.depot_id='$depot_id'")
    		->group(array('DNR.country_id','DNR.service_id'))
    		;
    		// echo $select->__tostring();die;
    	$result = $this->getAdapter()->fetchAll($select);
    	return $result;
	}
	

	public function getExistingRoutings($depot_id='')
	{	
		$data = array();
		$falg = array();
		try {
		    $editToken = Zend_Encript_Encription::decode($this->getData['editToken']);	
		    // echo $editToken;die;		
    		$select = $this->_db->select()->from(array('DNR'=>DepotNetworkRouting),array('DNR.country_id','DNR.depot_id','DNR.service_id','DNR.dn_routing_id'))
    				->joinleft(array('DNRD'=>DepotNetworkRoutingDetails),'DNR.dn_routing_id = DNRD.dn_routing_id',array('DNRD.postcode_start','DNRD.postcode_end','DNRD.sc_routing_id'))
    				->where('DNR.depot_id='.$depot_id.' and DNR.dn_routing_id='.$editToken)
    				;
    			$result = $this->getAdapter()->fetchAll($select);
    			 if(!empty($result) && count($result)>0){

					foreach ($result as $key => $value) {

							$data['country_id'] = $value['country_id'];
							$data['depot_id'] = $value['depot_id'];
							$data['dn_routing_id'] = $value['dn_routing_id'];
							$data['service_id'][$value['service_id']] = $value['service_id'];
							$data['postcode_start'][$value['postcode_start']] = $value['postcode_start'];
							$data['postcode_end'][$value['postcode_end']] = $value['postcode_end'];
							$data['sc_routing_id'][] = $value['sc_routing_id'];
					}
					$data['service_id'] = array_values($data['service_id']);
		    		$data['postcode_start'] = array_values($data['postcode_start']);
		    		$data['postcode_end'] = array_values($data['postcode_end']);
		    		$len = count($data['sc_routing_id']);
		    		$servicelen = count($data['service_id']);
		    		$data['sc_routing_id'] = array_slice($data['sc_routing_id'], 0,$len/$servicelen);
				}

    		} catch (Exception $e) {

    			return array('status'=>0,'message'=>$e->getMessage());
    		}
    		
    			// echo "<pre>"; print_r($data);die;

		if(!empty($data))
			return array('status'=>1,'data'=>$data);
		else return array('status'=>0,'message'=>'No record exists');
	}
 
	 
	public function addNewRouting($valueService='',$dn_routing_id='')
	{

	  $respsub = array();
	  $respsubErr = array();
	  $resp = array();
	  $data = $this->getData;
	  $depot_id = Zend_Encript_Encription:: decode($data['token']);
	  $service_name = $this->getAllServices('','',$valueService['service_id'])[0]['service_name'];

	  if($valueService['is_new']){
	   
			   if(  in_array($valueService['service_id'], $data['service_id']) !== false && empty($dn_routing_id)){

				    $dataArr = array('depot_id'=>$depot_id,'country_id'=>$data['country_id'],'service_id'=>$valueService['service_id']);

		  			$createdbydetails = commonfunction::createdByDetails($this->Useconfig['user_id']);	
		  			$newdata = array_merge($createdbydetails,$dataArr);
				    
		  			try {
	                	
						$entryid = $this->_db->insert(DepotNetworkRouting, array_filter($newdata));
						$lastinsertId = $this->getAdapter()->lastInsertId();
						if($entryid)
		  					$resp = array('status'=>1,'data'=>$entryid);
		  				else $resp = array('status'=>0,'message'=>'Some internal error occurred','service'=>$data['service_id']);


		  			} catch (Exception $e) {
		  				if($e->getCode() == 23000)
		  					$msg = "This country or service '$service_name' has already been assigned previously";
		  				else $msg = 'Some internal error occurred';
		  				$resp = array('status'=>0,'message'=>$msg,'service'=>$data['service_id']);

		  			}
    			}
    	}

			if( (isset($resp['status']) && $resp['status']== 1) || !empty($dn_routing_id)){

				foreach ($data['postcode_start'] as $k => $val) {
						$parentid = ($valueService['is_new'] && empty($dn_routing_id) )? $lastinsertId : $dn_routing_id ;

					if(!empty($data['postcode_start'][$k])){
						$detailsArray = array(
							'dn_routing_id'=>$parentid,
							'sc_routing_id'=>$data['sc_routing_id'][$k],
							'postcode_start'=>$data['postcode_start'][$k],
							'postcode_end'=>$data['postcode_end'][$k],
						);
						try {

							$subentryid = $this->_db->insert(DepotNetworkRoutingDetails,array_filter($detailsArray));
							if($subentryid)
			  					$respsub[] = array('status'=>1,'data'=>$subentryid);
			  				else { $respsub[] = array('status'=>0,'message'=>'Some internal error occurred');
			  					$respsubErr[] = 'Some internal error occurred';
			  				}

						} catch (Exception $e) {
							$respsubErr[] = $e->getMessage;
  							$respsub[] = array('status'=>0,'message'=>$e->getMessage(),'errorcode'=>$e->getCode());
							
						}
						 
					}
				}
			}	

		 
	  if( count($respsubErr) == 0 && $valueService['is_new'] == false ){

			$valueService['alreadyExisted'] = true;	
	  		$msgs = count($respsubErr)>0? implode(' , ', $respsubErr)  : "Routing for already existing service $service_name has been added successfully" ;
	  }
	  elseif( count($respsubErr) == 0 && $valueService['is_new'] == true ){
	   		$valueService['alreadyExisted'] = false;	
			$msgs = count($respsubErr)>0? implode(' , ', $respsubErr)  : "Routing for service $service_name has been added successfully" ; 
		}
			$msgsErr = count($respsubErr)>0? true  : false ;
	  		$respsub['service_name'] = $service_name;
	  		$respsub['msgs'] = $msgs;
	  		$respsub['msgsErr'] = $msgsErr;
	  		$valueService['insertionResponse'] = $respsub;		

	  return $valueService;

	}

	 
	public function AddDepotNetworkRouting()
	{

	  $respsub = array();
	  $respsubErr = array();
	  $data = $this->getData;
	  $depot_id = Zend_Encript_Encription:: decode($data['token']);
	  
	  $result = $this->validatePostcodesDb();

	   foreach ($result as $key => $value){

	   		if($value['is_new'])
	   			$res[$key] = $this->addNewRouting($value);
	   		elseif($value['is_new'] == 0 && $value['status'] == 1)
	   			$res[$key] = $this->addNewRouting($value,$value['dn_routing_id']);
	   		else $res[$key] = $value;

	   		$msgs['success'][] = (isset($res[$key]['insertionResponse']['msgsErr']) && $res[$key]['insertionResponse']['msgsErr'] == false)?$res[$key]['insertionResponse']['msgs']:'';

	   		$msgs['error'][] = (isset($res[$key]['insertionResponse']['msgsErr']) && $res[$key]['insertionResponse']['msgsErr'] == true)?$res[$key]['insertionResponse']['msgs']: ($res[$key]['status']==0 ? $res[$key]['message']." for service ".$res[$key]['service_name'] : '' ) ;
	 
	   	}
	   	return $msgs;	
	}
	 
	public function EditDepotNetworkRouting()
	{	
		$respsub = array();
	  	$respsubErr = array();
		$data = $this->getData;
		$depot_id = Zend_Encript_Encription::decode($data['token']);
		$editToken = Zend_Encript_Encription::decode($this->getData['editToken']);
		$this->getData['service_id'] = array($this->getData['service_id']);		
		// echo "<pre>";
		// print_r($data);
		// print_r($editToken);
		 
		 $check = $this->validatePostcodesDb($editToken);

		// print_r($check);
		// die;	
	  	if($check[$data['service_id']]['is_new']==1){

				try {
					$arr = array('country_id'=>$data['country_id'],'service_id'=>$data['service_id'],'modified_by'=>$this->Useconfig['user_id'],'modified_date'=>commonfunction::DateNow(),'modified_ip'=>commonfunction::loggedinIP());
                	$resp = $this->_db->update(DepotNetworkRouting, $arr ,"dn_routing_id=".$editToken);

					 	$res = $this->_db->delete(DepotNetworkRoutingDetails,"dn_routing_id='".$editToken."'");
						if($res){

							foreach ($data['postcode_start'] as $k => $val) {

								if(!empty($data['postcode_start'][$k])){
									$detailsArray = array(
										'dn_routing_id'=>$editToken,
										'sc_routing_id'=>$data['sc_routing_id'][$k],
										'postcode_start'=>$data['postcode_start'][$k],
										'postcode_end'=>$data['postcode_end'][$k],
									);
									try {

										$subentryid = $this->_db->insert(DepotNetworkRoutingDetails,array_filter($detailsArray));
										if($subentryid)
						  					$respsub[] = array('status'=>1,'data'=>$subentryid,'service'=>$data['service_id']);
						  				else { $respsub[] = array('status'=>0,'message'=>'Some internal error occurred','service'=>$data['service_id']);
						  					$respsubErr[] = 'Some internal error occurred';
						  				}

									} catch (Exception $e) {
			  							// echo $e->getMessage();die;

										$respsubErr[] = $e->getMessage;
			  							$respsub[] = array('status'=>0,'message'=>$e->getMessage(),'errorcode'=>$e->getCode(),'service'=>$data['service_id']);
										
									}
									 
								}
							}
						}



					  

				} catch (Exception $e) {
					// echo $e->getMessage();die;
					if($e->getCode() == 23000)
		  					$msg = "This country or service has already been assigned previously";
		  				else $msg = 'Some internal error occurred';
		  				$res = array('status'=>0,'message'=>$msg,'service'=>$data['service_id']);
				}
		  }

		if( !isset($res['status']) && $check[$data['service_id']]['is_new']==1){
	  		if(!empty($respsubErr) && count($respsubErr)>0){
	  			$msgs['error'][] = "Routing can not be edited successfully";
	  			return $msgs;
	  		}else {
	  			$msgs['success'][] = "Routing edited successfully";
	  		return $msgs;		
	  		}


	  	}else {
			    $msgs['error'][] = $check[$data['service_id']]['is_new']==1? $res['message'] : 'Service Already in use with country';
		  		return $msgs;		
	  		} 
	  		
	}

    public function validatePostcodesDb($dn_routing_id='')
    {	
    	$messages = array();
    	$context = $this->getData;
    	$depot_id = Zend_Encript_Encription::decode($context['token']);

    	// echo "<pre>"; print_r($context);die;

    	  $in = "'".implode("','",$context['postcode_start'])."'";
    	  $in2 = "'".implode("','",$context['postcode_end'])."'";
    	  $postcodes = $in.','.$in2;
    	  // echo $postcodes;die;
    	  $where = isset($dn_routing_id) && !empty($dn_routing_id) ? ' and (DNR.dn_routing_id <>'.$dn_routing_id.')' : '' ;

    	  foreach ($context['service_id'] as $key => $service) {

	  		$select = $this->_db->select()->from(array('DNR'=>DepotNetworkRouting),array('count(DNR.service_id) as existingservices','DNR.dn_routing_id'))
	  		->joininner(array('SV'=>SERVICES),'DNR.service_id = SV.service_id',array('SV.service_name'))
	    	->where("DNR.depot_id='$depot_id' and DNR.country_id='".$context['country_id']."' and DNR.service_id='".$service."'$where")
	    	 ;	
	    	 	// echo $select->__tostring();die;

	    	try {

		    	 $res = $this->getAdapter()->fetchAll($select);
	    	 		// echo "<pre>"; print_r($res); die;

		    	 if($res[0]['existingservices'] == 1){

		    	 	$selectpostcodes = $this->_db->select()->from(array('DNRD'=>DepotNetworkRoutingDetails),array('DNRD.postcode_start','DNRD.postcode_end'))
			    	->where("DNRD.dn_routing_id='".$res[0]['dn_routing_id']."' ")
			    	->where("DNRD.postcode_start IN($postcodes) OR DNRD.postcode_end IN($postcodes)")
			    	 ;	
			    	 $res1 = $this->getAdapter()->fetchAll($selectpostcodes);
	    	 	

			    		if(count($res1)>0)
			    			$messages[$service] = array('is_new'=>0,'status'=>0,'service_name'=>$res[0]['service_name'],'message'=>'postcodes already taken','dn_routing_id'=>$res[0]['dn_routing_id'],'service_id'=>$service);
			    		else $messages[$service] = array('is_new'=>0,'status'=>true,'service_name'=>$res[0]['service_name'],'dn_routing_id'=>$res[0]['dn_routing_id'],'service_id'=>$service); 	
	    	 		 // echo $selectpostcodes->__tostring();die;

		    	 } else $messages[$service] = array('is_new'=>1,'status'=>true,'service_name'=>$res[0]['service_name'],'service_id'=>$service);


	    	} catch (Exception $e) {

	    		$messages[$service] = array('error'=>true,'message'=>$e->getMessage());
	    	}

    	}
	    	 		// echo "<pre>"; print_r($messages); die;
	    			 
    	return $messages;

		     
    }


    public function DeleteDepotRouting()
    {	
    	$data = $this->getData;
    	$dn_routing_id = Zend_Encript_Encription::decode($data['editToken']);
    	// echo $dn_routing_id;die;
    		try {
    			
    			$res = $this->_db->delete(DepotNetworkRouting,'dn_routing_id='.$dn_routing_id);
    			if($res)
    				return array('status'=>1,'message'=>'Routing deleted successfully');
    			else return array('status'=>0,'message'=>'Some internal error occurred');
    		} catch (Exception $e) {
    			
    			return array('status'=>0,'message'=>'Some internal error occurred');
    			
    		}
	}



    public function validatePostcodes()
    {	
    	$messages = array();
    	$context = $this->getData;

    	  if(is_array($context['postcode_start']) && !empty($context['postcode_start']) ) { 

    	  	 $postcode_start =  array_count_values($context['postcode_start']);
    	  	 $postcode_ends =  array_count_values($context['postcode_end']);
    	  	 foreach ($context['postcode_start'] as $key => $value) {

  	 				foreach ($context['postcode_end'] as $k => $val) {

  	 						if($k == $key)
  	 							continue; 
  	 						if($value == $val)
  	 							$messages[] = $value." Is Repeating ";
  	 				}

  	 			}	

  	 			foreach ($postcode_start as $k => $val) {

  	 					if($val>1)
  	 						$messages[] = $k." Is Repeating ";
  	 			}	

  	 			foreach ($postcode_ends as $k => $val) {

  	 					if($val>1)
  	 						$messages[] = $k." Is Repeating ";
  	 			}



    	  	} 		

    	  if(empty($messages) && count($messages) == 0)	
		    return true;
		  else return $messages;  	

    }


}