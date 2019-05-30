<?php

class Application_Model_Shippingcenter extends Zend_Custom

{

	public function getPrice(){

	 		$prices = $this->CalculateParcelPrice($this->getData);

			//$onjStatus = new StatusManager();

			//$delivery_time = $onjStatus->DeleveryTime($this->getData);

	  if($this->getData['dimension']<=300 && $this->getData['length']<=150){

	       return array(DEPOT_PRICE=>$prices['depot_price'],CUSTOMER_PRICE=>$prices['customer_price'],'DeliveryTime'=>'');

	   }

	   elseif(($this->getData['dimension']>300 || $this->getData['length']>150) && $this->getData['service_id']==6){

	       return array(DEPOT_PRICE=>$prices['depot_price'],CUSTOMER_PRICE=>$prices['customer_price'],'DeliveryTime'=>'');

	   }		

	 return array(DEPOT_PRICE=>0,CUSTOMER_PRICE=>0,'DeliveryTime'=>'');

	 //return array(DEPOT_PRICE=>1,CUSTOMER_PRICE=>1,'DeliveryTime'=>'3545435');



	}



	public function getCountryService(){
	  $servicecheckArr = $this->getRoutingID();
	  $services = array();
	  $responseservicelist = array();
	  foreach($servicecheckArr as $servicecheck){
	       $servicearr = array();
		   if(($this->getData['dimension']>300 || $this->getData['length']>150) && $service['service_id']==6){
		       $servicearr['service_id']  =  $servicecheck['service_id'];
			   $servicearr['service_name'] =  $servicecheck['service_name'];
			   $responseservicelist[] = $servicearr;
		   }elseif($this->getData['dimension']<=300 && $this->getData['length']<=150){
			   $servicearr['service_id']  =  $servicecheck['service_id'];
			   $servicearr['service_name'] =  $servicecheck['service_name'];
			   $responseservicelist[] = $servicearr;
		   }
	  }
	  return array('Status'=>$responseservicelist);
	}



	public function getAdditionalService($data){

		

		if($data[SERVICE_ID]==6){

	     $dimension = ($data['length']*$data['width']*$data['height'])/1000000;

	     $frienghtservices = $this->GlsFreightAdditionalService($dimension, $data[SERVICE_ID],$data[SHIPMENT_WEIGHT], $data[COUNTRY_ID], 5, $data[ADMIN_ID], $data['length'], $data['width'], $data['height']);

		 if(!empty($frienghtservices)){

		      foreach ($frienghtservices as $addservice) {

                    $returndata[] = array('service_id'=>$addservice[SERVICE_ID],'service_name'=>$addservice['service_name'],'forwarder_id'=>5);

                }

		   return array('Status'=>$returndata);

		 }else{

		   return array('Status'=>array());

		 }

	   }

	   

        $string = '';

        $count = 1;

        $select = $this->_db->select()

                        ->from(array('RCT' => ROUTING_COUNTRY_SERVICE_TABLE), array('RCT.additional_services', 'RCT.forwarder_id'))

                        ->where("RCT." . COUNTRY_ID . "='" . $data[COUNTRY_ID] . "'

									AND RCT." . SERVICE_ID . " ='" . $data[SERVICE_ID] . "'");

       // echo $select->__toString();die;

        $results = $this->getAdapter()->fetchAll($select);

        $serviceArr = array();

        foreach ($results as $result) {

            if ($result['additional_services']) {

                //$addser = explode(',',$result['additional_services']);

                $user = $this->getUserDetail($data[ADMIN_ID]);

                $select = $this->_db->select()

                                ->from(array('RT' => ROUTING_TABLE), array())

                                ->joinleft(array('RPT' => ROUTING_PRICE_TABLE), "RPT.routing_id=RT.routing_id", array(SERVICE_ID))

                                ->where("RT." . COUNTRY_ID . "='" . $data[COUNTRY_ID] . "'

										AND RT." . ADMIN_ID . "='" . $user[ADMIN_PARENT_ID] . "'

										AND RT.min_weight<'" . $data[SHIPMENT_WEIGHT] . "'

										AND RT.max_weight>='" . $data[SHIPMENT_WEIGHT] . "'

										AND RT.forwarder_id='" . $result[FORWARDER_ID] . "'

										AND RPT.service_id IN(" . $result['additional_services'] . ")

										AND RPT.depot_price>0 AND RPT.standar_price>0");

                //print_r($select->__toString());die;

                $addservices = $this->getAdapter()->fetchAll($select);

                foreach ($addservices as $addservice) {

                    if (!in_array($addservice[SERVICE_ID], $serviceArr)) {

                        array_push($serviceArr, $addservice[SERVICE_ID]);

                         $returndata[] = array('service_id'=>$addservice[SERVICE_ID],'service_name'=>$this->ServiceName($addservice[SERVICE_ID]),'forwarder_id'=>$result[FORWARDER_ID]);

                    }

                }

            }

        }

        if(count($returndata)>0){

	     return array('Status'=>$returndata);

		 }

		  else{

			return array('Status'=>array());

		 }

	}

	public function getServiceCountryList($data){

	    $select = $this->_db->select()

                        ->from(array('RCT' => ROUTING_COUNTRY_SERVICE_TABLE), array('DISTINCT(RCT.country_id)','*'))

						->joinleft(array('CT'=>COUNTRY_TABLE),"CT.country_id=RCT.country_id",array('country_name','cncode'))

                        ->where("RCT.service_id='".$data['service_id']."'");

       // echo $select->__toString();die;

        return $this->getAdapter()->fetchAll($select);

	}

	public function getWaightRange($data){

	    $select = $this->_db->select()

                        ->from(array('RT' => ROUTING_TABLE), array('min(min_weight) as MIN_WEIGHT','max(max_weight) AS MAX_WEIGHT'))

                        ->where("RT.user_id='188' AND RT.country_id='".$data['country_id']."' AND RT.forwarder_id='".$data['forwarder_id']."'");

       //echo $select->__toString();die;

        return $this->getAdapter()->fetchAll($select);

	}

	public function getServiceList($data){

	   $select = $this->_db->select()

                        ->from(array('ST' => GENERAL_SERVICES), array('*'))

                        ->where("parent_service=0 AND status='1'");

       //echo $select->__toString();die;

        return $this->getAdapter()->fetchAll($select);

	}

	public function getNetherlandsAddress($data){

	   $select = $this->_db->select()

							->from(array('NPC' =>'netherlandpostcodes'),array('NPC.Postcode'))

							->joininner(array('NST' =>'netherlandstraat'),'NST.StraatID=NPC.StraatID',

																									array('NST.Straat'))

							->joininner(array('NCT'=>'netherlandplaats'),'NCT.PlaatsID  = NST.PlaatsID',

																							array('NCT.Plaats as City'))

							->where("NPC.Postcode='".str_replace(' ','',trim($data['rec_zipcode']))."'");

								//print_r($select->__toString());die;

			    $result = $this->getAdapter()->fetchRow($select);

			    return $result;

	}



	public function errorcheck(){



	}

	public function setDataToIndex($data){ 

	   $this->getData['weight'] 		=  str_replace(',','.',preg_replace("/[^0-9,.]/", "",$data['weight']));
       $countryDetail = $this->getCountryDetail($data['CountryCode'],2);
	   $this->getData['country_id']   =   $countryDetail['country_id'];

	   $this->getData['service_id']   = $data['ServiceCode'];

	   $this->getData['addservice_id']   = $data['additional_service'];

	    $this->getData['forwarder_id']   = $data['forwarder_id'];

	   $this->getData['rec_zipcode'] = $data['zip'];

	   $this->getData['mode']   		= $data['mode'];

	   $this->getData['user_id'] = 2475;

	   $this->getData['length']   = $data['length'];

	   $this->getData['width'] = $data['width'];

	   $this->getData['height'] = $data['height'];

	   if($data['length']>0 && $data['width']>0 && $data['height']>0){

	      $weight = ($data['length'] * $data['width'] * $data['height']) /6000;

		  if($weight>$this->getData['weight']){

		      $this->getData['weight'] 		=  $weight;

		  }

	   }

	    $this->getData['dimension'] =  (($this->getData['length'] *1 ) + ( $this->getData['width']*2) + ($this->getData['height'] * 2));

	}



  public function AddSenderaddress($data){

	    $obj = new AccountManager();

		$data['user_id'] 	= 2475;

		$data['added_by'] 	 = 2475;

		$address_id = $obj->addnewaddr($data);

		$data['address_id'] 	= $address_id;

		$data['country_id']  =  array($this->countryCode(strtoupper(trim($data['country_id'])),false));

		$obj->senderaddressaddcountry($data);

		  $select = $this->_db->select()

							->from('usersenderaddress',array('api_code'))

							->where("address_id='".$address_id."'");

	    $result = $this->getAdapter()->fetchRow($select);

		return array('api_code'=>$result['api_code']);

	}



  public function UpdatedServiceCountryList(){

	    //Get Service List

		 $parent_id = 188;

	     /*$select = $this->_db->select()

							->from(array('CR' =>'customerroutings'),array(''))

							->joininner(array('ST' =>'services'),'ST.service_id=CR.service_id',array('*'))

							->where("CR.user_id='".$user_id."'")

							->group("CR.service_id");*/

		$select = $this->_db->select()

							->from(array('RT' =>'routings'),array(''))

							->joininner(array('RP' =>'routingprices'),'RT.routing_id = RP.routing_id',array(''))

							->joininner(array('RF' =>'routingcountryforwarders'),'RF.forwarder_id = RT.forwarder_id AND RF.country_id = RT.country_id',array(''))

							->joininner(array('RS' =>'routingcountryservices'),'RS.forwarder_id = RT.forwarder_id AND RS.country_id = RT.country_id AND RS.service_id = RP.service_id',array(''))

							->joininner(array('ST' =>'services'),'RP.service_id = ST.service_id',array('*'))

							->where("RT.user_id =188 AND RP.depot_price >0 AND RP.standar_price >0")

							->group("ST.service_id");

								//print_r($select->__toString());die;

	    $result = $this->getAdapter()->fetchAll($select);//echo "<pre>";print_r($result);die;

		$ServiceList = array();

		foreach($result as $datas){

		     $services[]						  =   $datas['service_id'];

	    	//$ServiceList[$datas['service_id']] =  $datas; 

			 $service['Service'] =  $datas['service_id']; 

			 $ServiceList[]   = $service;

		}

		$select = $this->_db->select()

							->from(array('ST' =>'services'),array('service_id'))

							->where("ST.parent_service IN(".implode(',',$services).") AND status='1'");

		$addservices = $this->getAdapter()->fetchAll($select);

		foreach($addservices as $addservice){

		     $services[]						  =   $addservice['service_id'];

			 $service['Service'] =  $addservice['service_id']; 

			 $ServiceList[]   = $service;

		}	 

	   //Getcountry List

	    $select = $this->_db->select()

							->from(array('RT' =>'routings'),array(''))

							->joininner(array('RP' =>'routingprices'),'RP.routing_id = RT.routing_id',array('RP.service_id'))

							->joininner(array('CS' =>'routingcountryservices'),'CS.country_id = RT.country_id',array(''))

							->joininner(array('CT' =>'countries'),'CT.country_id = RT.country_id',array('CT.cncode'))

							->where(" RT.user_id='".$parent_id."' AND RP.depot_price>0 AND RP.standar_price>0 AND RP.service_id IN(".implode(',',$services).")")

							->group("RT.country_id")

							->group("RP.service_id");

				//echo $select->__toString();die;			

		$result = $this->getAdapter()->fetchAll($select);

		 $CountryList = array();

		foreach($result as $datas){

		    $CountryArr = array();

		    $CountryArr['Service'] =  $datas['service_id']; 

			$CountryArr['Country'] =  $datas['cncode']; 

			$CountryList[]   = $CountryArr;  

		}

		//echo "<pre>";print_r($CountryList);die;  

	 //Get Service list

	  $select = $this->_db->select()

							->from(array('ST' =>'services'),array('*'))

							->where("ST.status='1'");

								//print_r($select->__toString());die;

	    $result = $this->getAdapter()->fetchAll($select);

		$ServiceArr = array();

		foreach($result as $datas){

	    	$ServiceArr[$datas['service_id']] =  $datas; 

		}

		

		

		//print_r(array('Service'=>$services,'Countries'=>$CountryList,'ALlService'=>$ServiceArr));die;		

	  return array('AllService'=>$ServiceArr,'Service'=>$ServiceList,'Countries'=>$CountryList);		

	}

}

?>