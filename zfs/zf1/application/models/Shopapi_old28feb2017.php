<?php

class Application_Model_Shopapi extends Application_Model_Shipments
{
  	public $shopCredential = array();
  	
	public function getShopList($shop_id=false){
        $select = $this->_db->select()
						->from(array('SS'=>SHOP_SETTINGS),array('*','CONCAT(SS.shop_name,"-",ST.shop_name) AS full_name'))
						->joininner(array('ST' =>SHOPTYPE_LIST),"ST.shop_type_id=SS.shop_type_id",array(''))
						->where("SS.is_delete='0' AND SS.user_id=412");//print_r($select->__toString());die;
		if($shop_id){
		  $select->where("SS.shop_id=?",$shop_id);
		  return $this->getAdapter()->fetchRow($select);	
		}				
		return $this->getAdapter()->fetchAll($select);				
   }
   
   public function getOrderCount(){ 
        $shopDetails = $this->getShopList($this->getData['shop_id']);
	   $count = 0;
	   if(!empty($shopDetails)){	
	    try { 
		 switch($shopDetails['shop_type_id']){
		     case 1:  //Presta Shop
			    $shopDetails['shop_url'] = commonfunction::stringReplace(array('https://','http://'),'',$shopDetails['shop_url']);
				  $url = 'http://'.$shopDetails['api_key'].'@'.$shopDetails['shop_url'].'/api/orders?display=full&filter[current_state]=3&output_format=JSON';
				if($this->Useconfig['user_id']==3453){
				  $url = 'https://'.$shopDetails['api_key'].'@'.$shopDetails['shop_url'].'/api/orders?display=full&filter[current_state]=3&output_format=JSON';
				}
				$Alldetails = commonfunction::file_contect($url);
			    $jsonArrs = json_decode($Alldetails);
				if(!empty($jsonArrs)){
				   $count = count($jsonArrs->orders);
				}
			 break;
			 case 2:  // Seo Shop
			    $seoshopurl = (isset($shopDetails['shop_url']) && !empty($shopDetails['shop_url'])) ? trim($shopDetails['shop_url']) : 'api.webshopapp.com/nl/';
				$counturl = 'http://'.$shopDetails['api_key'].':'.$shopDetails['api_secret'].'@'.$seoshopurl.'orders/count.json?status=processing_awaiting_shipment&created_at_min=2015-01-01';
				$Alldetails = commonfunction::file_contect($counturl);
				$jsonArrs = json_decode($Alldetails);
				if(!empty($jsonArrs)){
				   $count = $jsonArrs->count;
				}
			 break;
			 case 3:
					 $client = new SoapClient($shopDetails['shop_url'].'/api/?wsdl');
					 $sessioncount = $client->login($shopDetails['api_secret'], $shopDetails['api_key']);
					 $paramscount = array(array(
							 'status'=>array('eq'=>'processing'),
							 ));
			       $resultcount = $client->call($sessioncount, 'sales_order.list',$paramscount);
				   $count = count($resultcount);
               
			 break;
			 case 4:
				$sPublic_key  =  $shopDetails['api_key'];
				$sSecret_key = $shopDetails['api_secret'];
				$sMethod = 'GET';
				$sData = null;
				$sUri = '/api/rest/v1/orders/?status=1';
				$sDomain = $shopDetails['shop_url'];
				date_default_timezone_set('UTC'); 
				$sTimeStamp = date('c'); 
				$sHashString = "$sPublic_key|$sMethod|$sUri|$sData|$sTimeStamp";
				$sHash = hash_hmac('sha512', $sHashString, $sSecret_key);
				$CurlHandler = curl_init();
				curl_setopt($CurlHandler, CURLOPT_URL, $sDomain.$sUri);
				curl_setopt($CurlHandler, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($CurlHandler, CURLOPT_HTTPHEADER,
					array( 
						"x-date: ". $sTimeStamp, 
						"x-hash: ". $sHash, 
						"x-public: ". $sPublic_key 
					) 
				); 
				$sOutput = curl_exec($CurlHandler); 
				curl_close($CurlHandler); 
				$oResponse = json_decode($sOutput);
				$count = 0; 
				 if(!empty($oResponse->items)){
				     foreach($oResponse->items as $item){
					     if($item->paid==1){
						      $count++;
						 }
					 }
				 }
			 break;
			 case 5:
				$PlazaAPI = new Zend_Plaza_Bolapi($shopDetails['api_key'], $shopDetails['api_secret'], true, false);
				$openOrders = $PlazaAPI->getOpenOrders(); 
				if(isset($openOrders['Orders']['Order']) && !empty($openOrders['Orders']['Order'][0])){
					$count = count($openOrders['Orders']['Order']);
				}elseif(isset($openOrders['Orders']['Order']) && !empty($openOrders['Orders']['Order'])){
					$count = 1;
				}
			 break;
			 case 6:
			    $wc_api = new Application_Model_Wcmanager($shopDetails['api_key'], $shopDetails['api_secret'], $shopDetails['shop_url']);
				$ordersDetail = $wc_api->get_orders();
				$count = 0;
				if(isset($ordersDetail->orders)){
					if(count($ordersDetail->orders)>0){ 
					   foreach($ordersDetail->orders as $key=>$Order){
							if($Order->status=='processing'){
								$count = $count +1;
							 }
						}
					}
				}
			 break;
			 case 7:
				  $count = 'Order Count Not availabel!Please check with Import!!';
			 break;
			 case 8:
			    $url="https://api.mijnwebwinkel.nl/v1/orders/count?language=nl_NL&status_id=1&ordering=asc&format=json&start_date=".urlencode(date("Y-m-d", strtotime(date("Y-m-d") . " -30 days")))."&end_date=".urlencode(date('Y-m-d'))."&partner_token=".$shopDetails['api_key']."&token=".$shopDetails['api_secret']."";
				$Alldetails = commonfunction::file_contect($url);
				
				$url1="https://api.mijnwebwinkel.nl/v1/orders/count?language=nl_NL&status_id=3&ordering=asc&format=json&start_date=".urlencode(date("Y-m-d", strtotime(date("Y-m-d") . " -30 days")))."&end_date=".urlencode(date('Y-m-d'))."&partner_token=".$shopDetails['api_key']."&token=".$shopDetails['api_secret']."";
				$Alldetails1 = commonfunction::file_contect($url1);
				$AllOrders = json_decode($Alldetails,true);
				$AllOrders1 = json_decode($Alldetails1,true);
				$count = $AllOrders['count']+$AllOrders1['count'] ;
			 break;
		 }
		}catch (Exception $e) { $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); }
		if(isset($this->getData['onlycount'])){
		   return $count;
		}
		 echo 'Total Order : '.$count;die;
	  }	 
   }
   
   
   public function ImportFromShop(){
      global $objSession;
	  $this->shopCredential =$this->getShopList($this->getData['shop_id']);
	  $orders = array();
	  switch($this->shopCredential['shop_type_id']){
	      case 1:
		     $orders = $this->PrestashopImport();
			 $childshops = $this->getChildShopList($this->shopCredential);
			 foreach($childshops as $childshop){
			    $this->shopCredential =  $childshop;
				$order = $this->PrestashopImport();
				$orders = array_merge($orders,$order);
			 }
			 echo "<pre>";print_r($orders);die;
		  break;
		  case 2:
		     $orders = $this->SeoShopImport();
		  break;
	  }
	  if(!empty($orders)){
	      $this->importShipment(0,$orders);
		  return true;
	  }else{
	      $objSession->errorMsg = "No Data has Imported!!";
	      return false;
	  }
   }
   
   public function PrestashopImport(){
        $Orders = array();
		$this->shopCredential['shop_url'] = commonfunction::stringReplace(array('https://','http://'),'',$this->shopCredential['shop_url']);
		$this->shopCredential['baseurl'] = 'http://'.$this->shopCredential['api_key'].'@'.$this->shopCredential['shop_url'];
		if($this->Useconfig['user_id']==3453){
			$this->shopCredential['baseurl'] = 'https://'.$this->shopCredential['api_key'].'@'.$this->shopCredential['shop_url'];
		}
		$url = $this->shopCredential['baseurl'].'/api/orders?display=full&filter[current_state]=3&output_format=JSON';
		$Alldetails = commonfunction::file_contect($url);
		$user_id = $this->Useconfig['user_id'];
		if($this->Useconfig['level_id'] == 10){
			$user_id  = $this->Useconfig['parent_id'];
		}
		if(!empty($Alldetails)){
		   $jsonArrs = json_decode($Alldetails);
		   if(!empty($jsonArrs->orders)){
		      foreach($jsonArrs->orders as $Order){
						$addressData 	= $this->GetAddresDetails($Order->id_address_delivery); 
						$CarrierData 	= $this->getCarrierDetails($Order->id_carrier);//echo "<pre>"; print_r($addressData);die;
						$country_details = $this->getCountryDetail($addressData['country'],2);
						$order['user_id']  		= $user_id;
						$order['country_id'] 	= $country_details['country_id'];
						$name = $addressData['firstname'].' '.$addressData['lastname'];
						$order['rec_name']   =   ($addressData['company']!='')?$addressData['company']:$name;
						$order['rec_contact']  = ($addressData['company']=='')?'':$name;
						$order['rec_street'] 	= (!empty($addressData['address1'])) ? $addressData['address1'] : $addressData['address2'];
						$order['rec_street2'] 	= (!empty($addressData['address2'])) ? $addressData['address2'] : '';
						$order['rec_zipcode'] 	= (!empty($addressData['postcode'])) ? $addressData['postcode'] : '';
						$order['rec_city'] 		= (!empty($addressData['city'])) ? $addressData['city'] : '';
						$order['rec_phone'] 	= (!empty($addressData['phone'])) ? $addressData['phone'] : '';
						$order['rec_email'] 	= (!empty($addressData['Email'])) ? $addressData['Email'] : '';
						$order['rec_statecode'] = (!empty($addressData['stateCode'])) ? $addressData['stateCode'] : '';
						$order['length'] 		= (!empty($CarrierData['length'])) ? $CarrierData['length'] : '';
						$order['width'] 		= (!empty($CarrierData['width'])) ? $CarrierData['width'] : '';
						$order['height'] 		= (!empty($CarrierData['height'])) ? $CarrierData['height'] : '';
						$order['weight'] 		= (!empty($CarrierData['weight'])) ? $CarrierData['weight'] : '';
						$order['weight'] = ($this->shopCredential['weight'] > 0 && $order['weight']<=0)?$this->shopCredential['weight']: 0.1;
						$order['rec_reference'] = (!empty($Order->reference)) ? $Order->reference : '';
						$order['quantity'] 		= 1;
						$order[SERVICE_ID] 		= (!empty($this->shopCredential['service_id']) && $this->shopCredential['service_id']>0) ? $this->shopCredential['service_id'] : 1;
						$order[ADDSERVICE_ID] 		= (!empty($this->shopCredential['addservice_id']) && $this->shopCredential['addservice_id']>0) ? $this->shopCredential['addservice_id'] : 0;
						$order['senderaddress_id'] 	= $this->getSenderID($this->shopCredential['sender_code']);
						$order['shipment_type'] 	= 7;
						$order['shop_order_id']  	= (!empty($Order->id)) ? $Order->id :0;
			    		$Orders[] =  $order; 
			  }
		   }
		}
		
		return $Orders;
   }
   
   public function getSenderID($sender_code){
      if($sender_code=='' || $sender_code=='C'){
	    return 'C';
	  }elseif($sender_code=='B'){
	    return 'B';
	  }else{
	    return 'C';
	  }
   }
   
   public function SeoShopImport(){
        
        $Orders = array();
		$this->shopCredential['shop_url'] = 'http://'.$this->shopCredential['api_key'].':'.$this->shopCredential['api_secret'].'@api.webshopapp.com/nl/orders.json?status=processing_awaiting_shipment&created_at_min=2016-09-01&limit=50';
		$Alldetails = commonfunction::file_contect($this->shopCredential['shop_url']);
		$user_id = $this->Useconfig['user_id'];
		if($this->Useconfig['level_id'] == 10){
			$user_id  = $this->Useconfig['parent_id'];
		}
		if(!empty($Alldetails)){
		   $jsonArrs = json_decode($Alldetails);
		   if(!empty($jsonArrs->orders)){
		      foreach($jsonArrs->orders as $Order){
						$OdrArrs[$key]['user_id'] 		= $userid;
						$OdrArrs[$key]['country_id'] 	= $this->ObjModel->getcountryid($Order->addressShippingCountry->code);
						$OdrArrs[$key]['rec_name'] 		= (!empty($Order->addressShippingCompany)) ? $Order->addressShippingCompany : $Order->addressShippingName;
						//$OdrArrs[$key]['rec_name'] 	= $Order->addressShippingName;//$Order->firstname.' '.$Order->lastname;
						$OdrArrs[$key]['rec_contact'] 	= (!empty($Order->addressShippingCompany)) ? $Order->addressShippingName : "";	//$Order->companyName;
						$OdrArrs[$key]['rec_street'] 	= (!empty($Order->addressShippingStreet)) ? $Order->addressShippingStreet : $Order->addressShippingStreet2;
						$OdrArrs[$key]['rec_streetnr']  = (!empty($Order->addressShippingNumber)) ? $Order->addressShippingNumber : '';
						$OdrArrs[$key]['rec_address']   = (!empty($Order->addressShippingExtension)) ? $Order->addressShippingExtension : '';
						$OdrArrs[$key]['rec_street2'] 	= (!empty($Order->addressShippingStreet2)) ? $Order->addressShippingStreet2 : '';
						$OdrArrs[$key]['rec_zipcode'] 	= (!empty($Order->addressShippingZipcode)) ? $Order->addressShippingZipcode : '';
						$OdrArrs[$key]['rec_city'] 		= (!empty($Order->addressShippingCity)) ? $Order->addressShippingCity : '';
						$OdrArrs[$key]['rec_phone'] 	= (!empty($Order->phone)) ? $Order->phone : '';
						$OdrArrs[$key]['rec_email'] 	= (!empty($Order->email)) ? $Order->email : '';					
						
						$Order->weight = ($Order->weight/1000);
						if(!empty($Order->weight) && $Order->weight > 0){
							$OdrArrs[$key]['weight'] = $Order->weight;
						}
						else if($servicedetail['weight'] > 0 && (empty($Order->weight) || $Order->weight<=0)){
							$OdrArrs[$key]['weight'] = $servicedetail['weight'];
						}
						else {
							$OdrArrs[$key]['weight'] = 1;
						}
						
						$OdrArrs[$key]['rec_reference'] = $rec_reference;
						$OdrArrs[$key]['quantity'] 		= 1;
						$OdrArrs[$key]['cod_price'] 	= '';
						$OdrArrs[$key]['bpost_price'] 	= '';
						$OdrArrs[$key][SERVICE_ID] 		= ($servicedetail['service_id']>0) ? $servicedetail['service_id'] : 1;
						$OdrArrs[$key]['shipment_status'] = 6;
						
						$OdrArrs[$key]['shop_id']       =  $params['shop_unique_id'];
						$OdrArrs[$key]['shop_order_id'] = (!empty($Order->id)) ? $Order->id :0;
						$OdrArrs[$key]['import_order_status']   = $Order->status;
						$order['quantity'] 		= 1;
						$order[SERVICE_ID] 		= (!empty($this->shopCredential['service_id']) && $this->shopCredential['service_id']>0) ? $this->shopCredential['service_id'] : 1;
						$order[ADDSERVICE_ID] 		= (!empty($this->shopCredential['addservice_id']) && $this->shopCredential['addservice_id']>0) ? $this->shopCredential['addservice_id'] : 0;
						$order['senderaddress_id'] 	= $this->getSenderID($this->shopCredential['sender_code']);
						$order['shipment_type'] 	= 7;
						$order['shop_order_id']  	= (!empty($Order->id)) ? $Order->id :0;
			    		$Orders[] =  $order; 
			  }
		   }
		}
		
		return $Orders;
   
   }
   public function MagentoShopImport(){
      
   }
   public function CCVShopImport(){
      
   }
   public function BOLShopImport(){
      
   }
   public function WoocommerceShopImport(){
      
   }
   public function ExactOnlineShopImport(){
      
   }
   public function MijnwebwinkelShopImport(){
      
   }
   
    /**
	 * Method GetAddresDetails() use to get prestashop Orders address detais for given address id. 
	 * @access	public
	 * @param	$id hold delivery address id
	 * @return	array
	 */
	public function GetAddresDetails($address_id){
		$Address = array();
		if($address_id>0){
			 $AddressUrl = $this->shopCredential['baseurl'].'/api/addresses/'.$address_id;
			$xmlData = simplexml_load_file($AddressUrl);
			foreach($xmlData->children()->address as $AddressArr){
				$Address['company'] 	= trim(utf8_decode(utf8_encode($AddressArr->company))); 
				$Address['firstname'] 	= trim(utf8_decode(utf8_encode($AddressArr->firstname))); 
				$Address['lastname'] 	= trim(utf8_decode(utf8_encode($AddressArr->lastname))); 
				$Address['address1'] 	= trim(utf8_decode(utf8_encode($AddressArr->address1))); 
				$Address['address2'] 	= trim(utf8_decode(utf8_encode($AddressArr->address2)));
				$Address['postcode'] 	= trim(utf8_decode(utf8_encode($AddressArr->postcode)));
				$Address['city'] 		= trim(utf8_decode(utf8_encode($AddressArr->city)));
				$Address['phone'] 		= trim(utf8_decode(utf8_encode($AddressArr->phone)));
				$Address['Email'] 		= $this->getcustomerData($AddressArr->id_customer);
				$Address['country'] 	= $this->getCountryCode($AddressArr->id_country);
				$Address['stateCode'] 	= $this->getState($AddressArr->id_state);
			}
		}
		return $Address;
	}
	
	/**
	 * Method getcustomerData() use to get customer address detais of given customer id. 
	 * @access	public
	 * @param	$id hold prestashop customer id
	 * @return	string or boolean
	 */
	public function getcustomerData($address_id){
		if($address_id>0){ 
			$CustomerUrl = $this->shopCredential['baseurl'].'/api/customers/'.$address_id;
			$xmlData = simplexml_load_file($CustomerUrl);
			
			foreach($xmlData->children()->customer as $customerArr){
				return trim(utf8_decode(utf8_encode($customerArr->email)));
			}
		}
		return false;
	}
	
	/**
	 * Method getCountryCode() use to get country cncode of given country id. 
	 * @access	public
	 * @param	$id hold prestashop country id
	 * @return	string or boolean
	 */
	public function getCountryCode($address_id){
		if($address_id > 0){
			$CountryUrl = $this->shopCredential['baseurl'].'/api/countries/'.$address_id;
			$xmlData = simplexml_load_file($CountryUrl);
			foreach($xmlData->children()->country as $countryArr){
				return trim(utf8_decode(utf8_encode($countryArr->iso_code)));
			}
		}
		return false;
	}
	
	/**
	 * Method getState() use to get statecode for given state id. 
	 * @access	public
	 * @param	$id hold prestashop state id
	 * @return	string or boolean
	 */
	public function getState($address_id){
		if($address_id>0){
			$StateUrl = $this->shopCredential['baseurl'].'/api/states/'.$address_id;
			$xmlData = simplexml_load_file($StateUrl);
			foreach($xmlData->children()->state as $stateyArr){
				return trim(utf8_decode(utf8_encode($stateyArr->iso_code)));
			}
		}
		return false;
	}
	/**
	 * Method getCarrierDetails() use to get order's carrier details(parcel weight and size) for given carrier id. 
	 * @access	public
	 * @param	$id hold prestashop carrier id
	 * @return	array
	 */
	public function getCarrierDetails($address_id){
		$CarrierData = array();
		if($address_id>0){
			$CarrierUrl = $this->shopCredential['baseurl'].'/api/carriers/'.$address_id;
			$xmlData 	= simplexml_load_file($CarrierUrl);
			
			foreach($xmlData->children()->carrier as $carrierArr){
				$CarrierData['width'] 	= trim(utf8_decode(utf8_encode($carrierArr->max_width)));
				$CarrierData['height'] 	= trim(utf8_decode(utf8_encode($carrierArr->max_height)));
				$CarrierData['length'] 	= trim(utf8_decode(utf8_encode($carrierArr->max_depth)));
				$CarrierData['weight'] 	= trim(utf8_decode(utf8_encode($carrierArr->max_weight)));
			}
		}
		return $CarrierData ;
	}
	
	
	public function getShoptypelist(){
		 $select = $this->_db->select()
						->from(array('ST'=>SHOPTYPE_LIST),array('*'));
						//print_r($select->__toString());die;			
		return $this->getAdapter()->fetchAll($select);	
	}
	
	public function getChildShopList($data){
	   $select = $this->_db->select()
						->from(array('SS'=>SHOP_SETTINGS),array('*','CONCAT(SS.shop_name,"-",ST.shop_name) AS full_name'))
						->joininner(array('ST' =>SHOPTYPE_LIST),"ST.shop_type_id=SS.shop_type_id",array(''))
						->where("SS.is_delete='0' AND SS.shop_parent_id='".$data['shop_id']."'");//print_r($select->__toString());die;
		return $this->getAdapter()->fetchAll($select);		
	}
}

