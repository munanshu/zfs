<?php
class Application_Model_Shopapi extends Application_Model_Shipments
{
  	public $shopCredential = array();
  	
	public function getShopList($shop_id=false){
        $select = $this->_db->select()
						->from(array('SS'=>SHOP_SETTINGS),array('*','CONCAT(SS.shop_name,"-",ST.shop_name) AS full_name'))
						->joininner(array('ST' =>SHOPTYPE_LIST),"ST.shop_type_id=SS.shop_type_id",array(''))
						->where("SS.is_delete='0' AND SS.user_id='".$this->Useconfig['user_id']."' AND SS.shop_parent_id=0");//print_r($select->__toString());die;
		if($shop_id){
		  $select->where("SS.shop_id=?",$shop_id);
		  return $this->getAdapter()->fetchRow($select);	
		}				
		return $this->getAdapter()->fetchAll($select);				
   }

   public function getExactShopList($shop_type_id=false){
        $select = $this->_db->select()
						->from(array('SS'=>SHOP_SETTINGS),array('*','CONCAT(SS.shop_name,"-",ST.shop_name) AS full_name'))
						->joininner(array('ST' =>SHOPTYPE_LIST),"ST.shop_type_id=SS.shop_type_id",array(''))
						->where("SS.is_delete='0' AND SS.user_id='".$this->Useconfig['user_id']."' AND SS.shop_parent_id=0 AND ST.shop_type_id='$shop_type_id'");//print_r($select->__toString());die;
		 				
		return $this->getAdapter()->fetchAll($select);				
   }

   public function getChildShopList($data){
	   $select = $this->_db->select()
						->from(array('SS'=>SHOP_SETTINGS),array('*','CONCAT(SS.shop_name,"-",ST.shop_name) AS full_name'))
						->joininner(array('ST' =>SHOPTYPE_LIST),"ST.shop_type_id=SS.shop_type_id",array(''))
						->where("SS.is_delete='0' AND SS.shop_parent_id='".$data['shop_id']."'");//print_r($select->__toString());die;
		return $this->getAdapter()->fetchAll($select);		
	}
   
   public function getOrderCount(){ 
       $shopDetails = $this->getShopList($this->getData['shop_id']);
	   $count = 0;
	   if(!empty($shopDetails)){	
	    try { 
		 switch($shopDetails['shop_type_id']){
		     case 1:  //Presta Shop
			    $url = $this->getPrestashopUrl($shopDetails);
				$Alldetails = commonfunction::file_contect($url);
			    $jsonArrs = json_decode($Alldetails);
				if(!empty($jsonArrs)){
				   $count = count($jsonArrs->orders);
				   $childshops = $this->getChildShopList($shopDetails);
				   foreach($childshops as $childshop){
						$url = $this->getPrestashopUrl($childshop);
						$childorders = commonfunction::file_contect($url);
						$jsonArrs = json_decode($childorders);
						if(!empty($jsonArrs)){
						   $count = $count + count($jsonArrs->orders);
						}  
					}
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
			  try { 
					 $client = new SoapClient($shopDetails['shop_url'].'/api/?wsdl');
					 $sessioncount = $client->login($shopDetails['api_secret'], $shopDetails['api_key']);
					 $paramscount = array(array(
							 'status'=>array('eq'=>'processing'),
							 ));
			       $resultcount = $client->call($sessioncount, 'sales_order.list',$paramscount);
				   $count = count($resultcount);
			  }catch (Exception $e) {$count = 0; $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); }   
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
				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$sOutput);
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
				$ordersDetail = $wc_api->get_orders(array( 'status' => 'processing','filter[limit]' => 200 )); 
				$count = 0;
				if(isset($ordersDetail->orders)){
					if(count($ordersDetail->orders)>0){ 
					   foreach($ordersDetail->orders as $key=>$Order){
								$count = $count +1;
						}
					}
				}
			 break;
			 case 7:
					// $usercredential = $this->Getcredentials($this->getData['shop_id']);

					 
					// $ExactModel = new Application_Model_Exactnew();

					// $resp = $ExactModel->Performexactquery($usercredential,'count');
			 	// 	 echo "<pre>";print_r($resp);die;
			 		// $objSession->exactShop_id = $this->getData['shop_id'];
			 		// $objSession->exactAction = 'count';

			  //    if(isset($this->getData['onlycount'])){
					//  $count = 0;
				 // }else{
				 //  $count = 'dfsfs';
				 // }
			 	if(isset($this->getData['onlycount'])){
					 $count = 0;
				 }else{
				  $count = 'Order Count Not availabel!Please check with Import!!';
				 }
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
			 case 9:

			  global $objSession;
        		$objSession->shoptradeShop_id = $this->getData['shop_id'];
			 	$credentials = array('api_key'=>$shopDetails['api_key'],'api_secret'=>$shopDetails['api_secret']);
			 	// echo "<pre>"; echo($credentials);die;
			 	$data = array('limit'=>200,'statuses'=>array('9011'));
			 	$ShopTradeObj = new Application_Model_ShopTrader();
			 	$count = $ShopTradeObj->PerformShopTradeQuery('count',$data);
			 break;
		 }
		}catch (Exception $e) { $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); }
		if(isset($this->getData['onlycount'])){
		   return $count;
		}
		 echo 'Total Order : '.$count;die;
	  }	 
   }
   
   public function getPrestashopUrl($shopDetails){
        $shopDetails['shop_url'] = commonfunction::stringReplace(array('https://','http://'),'',$shopDetails['shop_url']);
		  $url = 'http://'.$shopDetails['api_key'].'@'.$shopDetails['shop_url'].'/api/orders?display=full&filter[current_state]=3&output_format=JSON';
		if($this->Useconfig['user_id']==3453){
		  $url = 'https://'.$shopDetails['api_key'].'@'.$shopDetails['shop_url'].'/api/orders?display=full&filter[current_state]=3&output_format=JSON';
		}
	  return $url;	
   }
 
   public function ImportFromShop($order_return = false){
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
		  break;
		  case 2:
		     $orders = $this->SeoShopImport();
		  break;
		  case 3:
		     $orders = $this->MagentoShopImport();
		  break;
		  case 4:
		    $orders = $this->CCVShopImport();
		  break;
		  case 5:
		    $orders = $this->BOLShopImport();
		  break;
		  case 6:
		    $orders = $this->WoocommerceShopImport();
		  break;
		  case 7:
		     $objSession->exactShop_id = $this->getData['shop_id'];
		     $objSession->exactAction = 'import';
		     if(!$order_return){
		    	$this->ExactOnlineShopImport();
			 }
			break;
		  case 8:
		    $orders = $this->MijnwebwinkelShopImport();
		  break;
		  case 9:

		    $orders = $this->ImportShopTradeOrder();
		  break;
	  }
	  if($order_return){
	    return $orders;
	  }
	  if(!empty($orders)){
	      $this->importShipment(0,$orders);
		  return true;
	  }else{
	      $objSession->errorMsg = "No Data has Imported!!";
	      return false;
	  }
   }

   public function ImportShopTradeOrder()
   {
        global $objSession;
        $objSession->shoptradeShop_id = $this->getData['shop_id'];
		$data = array('limit'=>200,'statuses'=>array('9011'));
		
		$ShopTradeObj = new Application_Model_ShopTrader();
		$orders = $ShopTradeObj->PerformShopTradeQuery('import',$data);
		return $orders;
		// echo "<pre>";print_r($orders);die;

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
						$order['shop_id']  			= $this->shopCredential['shop_id'];
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
		$seoshopurl = (isset($this->shopCredential['shop_url']) && !empty($this->shopCredential['shop_url'])) ? trim($this->shopCredential['shop_url']) : 'api.webshopapp.com/nl/';
		$this->shopCredential['shop_url'] = 'http://'.$this->shopCredential['api_key'].':'.$this->shopCredential['api_secret'].'@'.$seoshopurl.'orders.json?status=processing_awaiting_shipment&created_at_min=2015-01-01&limit=100';
		
		
		//$counturl = 'http://'.$shopDetails['api_key'].':'.$shopDetails['api_secret'].'@'.$seoshopurl.'orders/count.json?status=processing_awaiting_shipment&created_at_min=2015-01-01';
		
		$Alldetails = commonfunction::file_contect($this->shopCredential['shop_url']);
		$user_id = $this->Useconfig['user_id'];
		if($this->Useconfig['level_id'] == 10){
			$user_id  = $this->Useconfig['parent_id'];
		}
		if(!empty($Alldetails)){
		   $jsonArrs = json_decode($Alldetails); 
		   if(!empty($jsonArrs->orders)){
		      foreach($jsonArrs->orders as $Order){
						$country_details = $this->getCountryDetail($Order->addressShippingCountry->code,2);
						$order['user_id']  		= $user_id;
						$order['country_id'] 	= $country_details['country_id'];
						
						$name = $Order->addressShippingName;
						$order['rec_name']   =   ($Order->addressShippingCompany!='')?$Order->addressShippingCompany:$name;
						
						$order['rec_contact']  = ($Order->addressShippingCompany=='')?'':$name;
						$order['rec_street'] 	= (!empty($Order->addressShippingStreet)) ? $Order->addressShippingStreet : '';
						$order['rec_streetnr']  = (!empty($Order->addressShippingNumber)) ? $Order->addressShippingNumber : '';
						$order['rec_address']   = (!empty($Order->addressShippingExtension)) ? $Order->addressShippingExtension : '';
						$order['rec_street2'] 	= (!empty($Order->addressShippingStreet2)) ? $Order->addressShippingStreet2 : '';
						$order['rec_zipcode'] 	= (!empty($Order->addressShippingZipcode)) ? $Order->addressShippingZipcode : '';
						$order['rec_city'] 		= (!empty($Order->addressShippingCity)) ? $Order->addressShippingCity : '';
						$order['rec_phone'] 	= (!empty($Order->phone)) ? $Order->phone : '';
						$order['rec_email'] 	= (!empty($Order->email)) ? $Order->email : '';					
						if($Order->weight>0){
						  $order['weight'] = ($Order->weight/1000);
						}else{
						   $order['weight'] = ($this->shopCredential['weight'] > 0)?$this->shopCredential['weight']: 1;
						}						
						$order['rec_reference'] = $Order->number;
						$order['quantity'] 		= 1;
						$order['cod_price'] 	= 0;
						$order['shipment_worth']= 0;
						$order[SERVICE_ID] 		= (!empty($this->shopCredential['service_id']) && $this->shopCredential['service_id']>0) ? $this->shopCredential['service_id'] : 1;
						$order[ADDSERVICE_ID] 		= (!empty($this->shopCredential['addservice_id']) && $this->shopCredential['addservice_id']>0) ? $this->shopCredential['addservice_id'] : 0;
						$order['senderaddress_id'] 	= $this->getSenderID($this->shopCredential['sender_code']);
						$order['shipment_type'] 	= 6;
						$order['shop_id']  			= $this->shopCredential['shop_id'];
						$order['shop_order_id']  	= (!empty($Order->id)) ? $Order->id :0;
			    		$Orders[] =  $order; 
			  }
		   }
		}
		
		return $Orders;
   
   }
   
   public function MagentoShopImport(){
         $Orders = array();
		 try{
         $client = new SoapClient($this->shopCredential['shop_url'].'/api/?wsdl');
		 $session = $client->login($this->shopCredential['api_secret'],$this->shopCredential['api_key']);
		 $params = array(array(
		 'status'=>array('eq'=>'processing'),
		 ));
		 $result = $client->call($session, 'sales_order.list',$params);
		}catch (Exception $e) {$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());return $Orders; } 
		 $user_id = $this->Useconfig['user_id'];
		if($this->Useconfig['level_id'] == 10){
			$user_id  = $this->Useconfig['parent_id'];
		}
		
		 if(count($result)>0){
		    foreach($result as $Order){ //echo "<pre>";print_r($Order);die;
			  $orderinfo = $client->call($session, 'sales_order.info', $Order['increment_id']);
			  $order['user_id']  		= $user_id;
			  if($Order['customer_is_guest']==1 || !empty($orderinfo['shipping_address']))
			  {
				 try{
					$shipAdd = $orderinfo['shipping_address'];
					$country_details = $this->getCountryDetail($shipAdd['country_id'],2);
					$order['country_id'] = $country_details['country_id'];
					$order['rec_name'] 	 = $shipAdd['firstname'].' '.$shipAdd['lastname'];
					$order['rec_contact']= '';
					$order['rec_street'] = (!empty($shipAdd['street'])) ? trim($shipAdd['street']) : '';
					$order['rec_zipcode']= (!empty($shipAdd['postcode'])) ?  $shipAdd['postcode'] : $Order['postcode'];
					$order['rec_city'] 	= (!empty($shipAdd['city'])) ? $shipAdd['city'] : '';
					$order['rec_phone'] = (!empty($shipAdd['telephone'])) ? $shipAdd['telephone'] : $Order['telephone'];
					$order['rec_email'] = (!empty($shipAdd['email'])) ? $shipAdd['email'] :$Order['customer_email'];
				 }catch(Exception $e){}
			}elseif(!empty($Order['customer_id']))
				{
					$addr = $client->call($session, 'customer_address.list', $Order['customer_id']); 
					$country_details = $this->getCountryDetail($addr[0]['country_id'],2);
					$order['country_id'] = $country_details['country_id'];
					$order['rec_name'] 		= $addr[0]['firstname'].' '.$addr[0]['lastname'];
					$order['rec_contact'] 	= '';
					$order['rec_street'] 	= (!empty($addr[0]['street'])) ? trim($addr[0]['street']) : '';
					$order['rec_zipcode'] 	= (!empty($addr[0]['postcode'])) ?  $addr[0]['postcode'] : '';
					$order['rec_city'] 		= (!empty($addr[0]['city'])) ? $addr[0]['city'] : '';
					$order['rec_phone'] 	= (!empty($addr[0]['telephone'])) ? $$addr[0]['telephone'] : '';
					$order['rec_email'] 	= (!empty($Order['customer_email'])) ? $Order['customer_email'] : '';
			}
			
			//$Orders['senderaddress_id'] =   $this->ObjModel->getSetSenderAddressBYShopname($_SESSION['adminLoginID'],$value['store_name'],$Orders['country_id']);
			$order['weight'] = (commonfunction::stringReplace(',','.',$Order['weight'])/1000);
			if($order['weight']<=0){
			   $order['weight'] = ($this->shopCredential['weight'] > 0)?$this->shopCredential['weight']:1;
			}
			$order['rec_reference'] = (!empty($Order['increment_id'])) ? $Order['increment_id'] : $Order['order_id'];
			$order['quantity'] 		= 1;
			$order['cod_price'] 	= 0;
			$order['shipment_worth'] 	= 0;
			$order[SERVICE_ID] 		= (!empty($this->shopCredential['service_id']) && $this->shopCredential['service_id']>0) ? $this->shopCredential['service_id'] : 1;
			$order[ADDSERVICE_ID] 		= (!empty($this->shopCredential['addservice_id']) && $this->shopCredential['addservice_id']>0) ? $this->shopCredential['addservice_id'] : 0;
			$order['shipment_type'] = 8;
			$order['shop_id']   	= $this->shopCredential['shop_id'];
			$order['shop_order_id']    =(!empty($Order['increment_id'])) ? $Order['increment_id'] : '';
			$Orders[] =  $order; 
			}
		 }
		 return $Orders;
		 
   }
   
   
   public function CCVShopImport(){
		  $Orders = array();
		  $user_id = $this->Useconfig['user_id'];
			if($this->Useconfig['level_id'] == 10){
				$user_id  = $this->Useconfig['parent_id'];
			}
		try{	
		  $sPublic_key = $this->shopCredential['api_key'];					
		  $sSecret_key = $this->shopCredential['api_secret'];					
		  $sMethod = 'GET';			 
		  $sData = null;					
		  $sUri = '/api/rest/v1/orders/?status=1';					
		  $sDomain = $this->shopCredential['shop_url'];					
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
		  }catch (Exception $e) {$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());return $Orders; } 
		  if(isset($oResponse->items)){
		      foreach($oResponse->items as $item){
			     if($item->paid==1){ 
			       $countrycode = isset($item->customer->deliveryaddress->country_code) ?$item->customer->deliveryaddress->country_code:$item->customer->billingaddress->country_code;
				   if($this->Useconfig['user_id']==412){
				   //echo "<pre>";print_r($item);die;
				}
				   $firstname = (isset($item->customer->billingaddress->first_name)) ? $item->customer->billingaddress->first_name : '';
                   $lastname = (isset($item->customer->billingaddress->last_name)) ? $item->customer->billingaddress->last_name : '';
                   $companyname = (isset($item->customer->billingaddress->company)) ? $item->customer->billingaddress->company : '';
				   
				   		$country_details = $this->getCountryDetail($countrycode,2);
						$order['user_id']  		= $user_id;
						$order['country_id'] 	= $country_details['country_id'];
						$order['rec_name']   =   ($companyname!='')?$companyname:($firstname.' '.$lastname);
						$order['rec_contact']  = ($companyname=='')?'':($firstname.' '.$lastname);
						if((!empty($item->customer->deliveryaddress->street)) && (!empty($item->customer->deliveryaddress->zipcode)) && (!empty($item->customer->deliveryaddress->city))){
							$rec_street = (isset($item->customer->deliveryaddress->street)) ? $item->customer->deliveryaddress->street : '';
							$houseno = (isset($item->customer->deliveryaddress->housenumber)) ? $item->customer->deliveryaddress->housenumber : '';
							$housesuffix = (isset($item->customer->deliveryaddress->housenumber_suffix)) ? $item->customer->deliveryaddress->housenumber_suffix : '';
							$order['rec_street'] = (!empty($housesuffix)) ? $rec_street.' '.$houseno : $rec_street;
							$order['rec_streetnr'] = (!empty($housesuffix)) ? $housesuffix : $houseno;
							$order['rec_street2'] = (isset($item->customer->deliveryaddress->address_line_2)) ? $item->customer->deliveryaddress->address_line_2 : '';
							$order['rec_zipcode']  = (isset($item->customer->deliveryaddress->zipcode))?$item->customer->deliveryaddress->zipcode:'';
							$order['rec_city']    = (isset($item->customer->deliveryaddress->city)) ? $item->customer->deliveryaddress->city :''; 
						}
						else
						 {
							$rec_street = (isset($item->customer->billingaddress->street)) ? $item->customer->billingaddress->street : '';
							$houseno = (isset($item->customer->billingaddress->housenumber)) ? $item->customer->billingaddress->housenumber : '';
							$housesuffix = (isset($item->customer->billingaddress->housenumber_suffix)) ? $item->customer->billingaddress->housenumber_suffix : '';
							$order['rec_street'] = (!empty($housesuffix)) ? $rec_street.' '.$houseno : $rec_street;
							$order['rec_streetnr'] = (!empty($housesuffix)) ? $housesuffix : $houseno;
							$order['rec_street2'] = (isset($item->customer->billingaddress->address_line_2)) ? $item->customer->billingaddress->address_line_2 : '';
							$order['rec_zipcode']  = (isset($item->customer->billingaddress->zipcode))?$item->customer->billingaddress->zipcode:'';
							$order['rec_city']    = (isset($item->customer->billingaddress->city)) ? $item->customer->billingaddress->city :''; 
					  }
						$order['rec_phone'] 	= ($item->customer->deliveryaddress->telephone!='') ? $item->customer->deliveryaddress->telephone : $item->customer->billingaddress->telephone; 
						$order['rec_email'] 	= $item->customer->email; 		
						if($item->total_weight>0){
						  $order['weight'] = $item->total_weight;
						}else{
						   $order['weight'] = ($this->shopCredential['weight'] > 0)?$this->shopCredential['weight']: 1;
						}						
						$order['rec_reference'] = $item->ordernumber;
						$order['quantity'] 		= 1;
						$order['cod_price'] 	= 0;
						$order['shipment_worth']= 0;
						$order[SERVICE_ID] 		= (!empty($this->shopCredential['service_id']) && $this->shopCredential['service_id']>0) ? $this->shopCredential['service_id'] : 1;
						$order[ADDSERVICE_ID] 	= (!empty($this->shopCredential['addservice_id']) && $this->shopCredential['addservice_id']>0) ? $this->shopCredential['addservice_id'] : 0;
						$order['senderaddress_id'] 	= $this->getSenderID($this->shopCredential['sender_code']);
						$order['shipment_type'] 	= 9;
						$order['shop_id']  			= $this->shopCredential['shop_id'];
						$order['shop_order_id']  	= (!empty($item->id)) ? $item->id :0;
			    		$Orders[] =  $order; 
				 }  
				   
			  }
		  }
		  
		 return $Orders; 
   }
  
   public function BOLShopImport(){
		 $Orders = array();
		 try {
		   $PlazaAPI = new Zend_Plaza_Bolapi($this->shopCredential['api_key'], $this->shopCredential['api_secret'], true, false);
		   $openOrders = $PlazaAPI->getOpenOrders();
		 } catch (Exception $e) {
		   //$_SESSION[ERROR_MSG] = 'Some error in APi response';
		   $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());return $Orders;
		 }
	    $user_id = $this->Useconfig['user_id'];
		if($this->Useconfig['level_id'] == 10){
			$user_id  = $this->Useconfig['parent_id'];
		}
		
	  	if(isset($openOrders['Orders']['Order']) && !empty($openOrders['Orders']['Order']) && isset($openOrders['Orders']['Order'][0])){
			 foreach($openOrders['Orders']['Order'] as $Order){
			        $order['user_id']  		= $user_id;
					$ReceiverAddress = $Order['CustomerDetails']['ShipmentDetails'];
					$country_details = $this->getCountryDetail($ReceiverAddress['CountryCode'],2);
					$order['country_id'] 	= $country_details['country_id'];
					$order['rec_name']   = $ReceiverAddress['Firstname'].' '.$ReceiverAddress['Surname'];
					$order['rec_street']  =  $ReceiverAddress['Streetname'];
					$order['rec_streetnr']  =  $ReceiverAddress['Housenumber'];
					$order['rec_zipcode']  = $ReceiverAddress['ZipCode'];
					$order['rec_city']   =  $ReceiverAddress['City'];
					$order['rec_email']  = $ReceiverAddress['Email'];
					$reference = array();
					if(isset($Order['OrderItems']['OrderItem'][0]['OrderItemId'])){
						foreach($Order['OrderItems']['OrderItem'] as $orderitems){
						   $reference[] = $orderitems['OrderItemId'];
						}
					}else{
					   $reference[] = $Order['OrderItems']['OrderItem']['OrderItemId'];
					}
					$order['rec_reference'] = commonfunction::implod_array($reference,',');
					$order['shop_order_id']    = $Order['OrderId'];
					$order['order_item_id'] = $Order['OrderItems']['OrderItem']['OrderItemId'];
					$order['weight'] = ($this->shopCredential['weight'] > 0)?$this->shopCredential['weight']: 1;
					$order['senderaddress_id'] 	= $this->getSenderID($this->shopCredential['sender_code']);
					$order['quantity']   = 1;
					$order[SERVICE_ID] 		= (!empty($this->shopCredential['service_id']) && $this->shopCredential['service_id']>0) ? $this->shopCredential['service_id'] : 1;
					$order[ADDSERVICE_ID] 	= (!empty($this->shopCredential['addservice_id']) && $this->shopCredential['addservice_id']>0) ? $this->shopCredential['addservice_id'] : 0;
					$order['shipment_type'] =11;
					$order['shop_id']   = $this->shopCredential['shop_id'];
					$Orders[] =  $order; 
				 }	
	      }elseif(isset($openOrders['Orders']['Order']) && !empty($openOrders['Orders']['Order'])){
	  			$order['user_id']  		= $user_id;
				$Order = $openOrders['Orders']['Order'];
				$ReceiverAddress = $Order['CustomerDetails']['ShipmentDetails'];
				$country_details = $this->getCountryDetail($ReceiverAddress['CountryCode'],2);
				$order['country_id'] 	= $country_details['country_id'];
				$order['rec_name']   = $ReceiverAddress['Firstname'].' '.$ReceiverAddress['Surname'];
				$order['rec_street']  =  $ReceiverAddress['Streetname'];
				$order['rec_streetnr']  =  $ReceiverAddress['Housenumber'];
				$order['rec_zipcode']  = $ReceiverAddress['ZipCode'];
				$order['rec_city']   =  $ReceiverAddress['City'];
				$order['rec_email']  = $ReceiverAddress['Email'];
				$reference = array();
				if(isset($Order['OrderItems']['OrderItem'][0]['OrderItemId'])){
					foreach($Order['OrderItems']['OrderItem'] as $orderitems){
					   $reference[] = $orderitems['OrderItemId'];
					}
				}else{
				   $reference[] = $Order['OrderItems']['OrderItem']['OrderItemId'];
				}
				$order['rec_reference'] = commonfunction::implod_array($reference,',');
				$order['shop_order_id']    = $Order['OrderId'];
				$order['order_item_id'] = $Order['OrderItems']['OrderItem']['OrderItemId'];
				$order['weight'] = ($this->shopCredential['weight'] > 0)?$this->shopCredential['weight']: 1;
				$order['senderaddress_id'] 	= $this->getSenderID($this->shopCredential['sender_code']);
				$order['quantity']   = 1;
				$order[SERVICE_ID] 		= (!empty($this->shopCredential['service_id']) && $this->shopCredential['service_id']>0) ? $this->shopCredential['service_id'] : 1;
				$order[ADDSERVICE_ID] 	= (!empty($this->shopCredential['addservice_id']) && $this->shopCredential['addservice_id']>0) ? $this->shopCredential['addservice_id'] : 0;
				$order['shipment_type'] =11;
				$order['shop_id']   = $this->shopCredential['shop_id'];
				if($this->Useconfig['user_id']==412){
				  //echo "<pre>";print_r($order);die;
				}
				$Orders[] =  $order;
	       }
	    return $Orders;
	 
   }
  
   public function WoocommerceShopImport(){
      $Orders = array();
	 try{
		 $wc_api = new Application_Model_Wcmanager($this->shopCredential['api_key'], $this->shopCredential['api_secret'], $this->shopCredential['shop_url']);
		 $ordersDetail = $wc_api->get_orders(array( 'status' => 'processing','filter[limit]' => 200 ));
	  }catch (Exception $e) {$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());return $Orders; } 
	  if(isset($ordersDetail->orders) && count($ordersDetail->orders)>0){ 
	    $user_id = $this->Useconfig['user_id'];
		if($this->Useconfig['level_id'] == 10){
			$user_id  = $this->Useconfig['parent_id'];
		}
	   foreach($ordersDetail->orders as $Order){
			if(isset($Order->shipping_address->first_name) && isset($Order->shipping_address->city) && isset($Order->shipping_address->postcode) && $Order->shipping_address->city!=''){
			  $ShippingAddres = $Order->shipping_address;
			}else{
			  $ShippingAddres = $Order->billing_address;
			}
			$name = $ShippingAddres->first_name.' '.$ShippingAddres->last_name;
			$country_details = $this->getCountryDetail($ShippingAddres->country,2);
			$order['user_id']  		= $user_id;
			$order['country_id'] 	= $country_details['country_id'];
			$order['rec_name']   =  ($ShippingAddres->company!='')?$ShippingAddres->company:$name;
			$order['rec_contact']  = ($ShippingAddres->company=='')?'':$name;
			$order['ordernumber']  = $Order->order_number;
			$order['rec_street']  = $ShippingAddres->address_1;
			$order['rec_street2']  = $ShippingAddres->address_2;
			$order['rec_zipcode']  = (!empty($ShippingAddres->postcode)) ? $ShippingAddres->postcode : '';
			$order['rec_city']   = (!empty($ShippingAddres->city)) ? $ShippingAddres->city : '';
			$order['rec_phone']  = (!empty($Order->billing_address->phone)) ? $Order->billing_address->phone : '';
			$order['rec_email']  = (!empty($Order->billing_address->email)) ? $Order->billing_address->email : '';
			$order['weight'] = ($this->shopCredential['weight'] > 0)?$this->shopCredential['weight']: 1;
			$order['rec_reference'] = (!empty($Order->id)) ? $Order->id : '';
			$order['senderaddress_id'] 	= $this->getSenderID($this->shopCredential['sender_code']);
			$order['quantity']   = 1;
			$order[SERVICE_ID] 		= (!empty($this->shopCredential['service_id']) && $this->shopCredential['service_id']>0) ? $this->shopCredential['service_id'] : 1;
			$order[ADDSERVICE_ID] 	= (!empty($this->shopCredential['addservice_id']) && $this->shopCredential['addservice_id']>0) ? $this->shopCredential['addservice_id'] : 0;
			$order['shop_id']   = $this->shopCredential['shop_id'];
			$order['shipment_type'] = 12;
			$order['shop_order_id'] = $Order->id;
			$Orders[] =  $order;
		 }	
	   }
	   return $Orders;
   }
  
   public function ExactOnlineShopImport(){
        $redirector =	Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
		$redirector->gotoSimple('performexactquery','Exactonline');
   
       $this->_redirect("Exactonline/performexactquery");echo "Here1";die;
   }
   public function MijnwebwinkelShopImport(){
		 $AllOrders = array();
		 $url="https://api.mijnwebwinkel.nl/v1/orders?language=nl_NL&status_id=1&ordering=asc&format=json&start_date=".urlencode(date("Y-m-d", strtotime(date("Y-m-d") . " -30 days")))."&end_date=".urlencode(date('Y-m-d'))."&partner_token=".$this->shopCredential['api_key']."&token=".$this->shopCredential['api_secret']."";
		$Alldetails = commonfunction::file_contect($url);
		$shop1orders = json_decode($Alldetails,true);
		$AllOrders = array_merge($shop1orders,$AllOrders);
		
		$url1="https://api.mijnwebwinkel.nl/v1/orders?language=nl_NL&status_id=3&ordering=asc&format=json&start_date=".urlencode(date("Y-m-d", strtotime(date("Y-m-d") . " -30 days")))."&end_date=".urlencode(date('Y-m-d'))."&partner_token=".$this->shopCredential['api_key']."&token=".$this->shopCredential['api_secret']."";
		$Alldetails1 = commonfunction::file_contect($url1);
		$shop1orders1 = json_decode($Alldetails1,true);
		$AllOrders = array_merge($shop1orders1,$AllOrders);
		$Orders = array();
		$user_id = $this->Useconfig['user_id'];
		if($this->Useconfig['level_id'] == 10){
			$user_id  = $this->Useconfig['parent_id'];
		}
		if(count($AllOrders)>0){
			foreach($AllOrders as $AllOrder){ 
			    if($AllOrder['status']==3 || $AllOrder['status']==1){
					$addressdatd = $AllOrder['debtor']['address']['delivery'];
					$order['user_id']  		= $user_id;
					$country_details = $this->getCountryDetail($AllOrder['shipping'][0]['country']['isocode'],2);
					$order['country_id'] 	= $country_details['country_id'];
					$order['rec_name'] 		= $addressdatd['name'];
					$order['rec_contact'] 	= '';
					$order['rec_street'] 	= $addressdatd['street'];
					$order['rec_streetnr']  = $addressdatd['number'];
					$order['rec_address']   = '';
					$order['rec_street2'] 	= '';
					$order['rec_zipcode'] 	= $addressdatd['zipcode'];
					$order['rec_city'] 		= $addressdatd['city'];
					$order['rec_phone'] 	= isset($AllOrder['debtor']['phone'])?$AllOrder['debtor']['phone']:'';
					$order['rec_email'] 	= $AllOrder['debtor']['email'];
					if(isset($AllOrder['weight']) && $AllOrder['weight']> 0){
						$order['weight'] = $AllOrder['weight'];
					}
					else{
						$order['weight'] = ($this->shopCredential['weight'] > 0)?$this->shopCredential['weight']: 1;
					}
					$order['rec_reference'] = $AllOrder['number'];
					$order['senderaddress_id'] 	= $this->getSenderID($this->shopCredential['sender_code']);
					$order['quantity']   = 1;
					$order[SERVICE_ID] 		= (!empty($this->shopCredential['service_id']) && $this->shopCredential['service_id']>0) ? $this->shopCredential['service_id'] : 1;
					$order[ADDSERVICE_ID] 	= (!empty($this->shopCredential['addservice_id']) && $this->shopCredential['addservice_id']>0) ? $this->shopCredential['addservice_id'] : 0;
					$order['shipment_type'] = 14;
					$order['shop_id']       =  $this->shopCredential['shop_id'];
					$order['shop_order_id'] = (!empty($AllOrder['number'])) ? $AllOrder['number'] :0;
					$Orders[] =  $order;
				}	
			}
		}
		
		return $Orders;
	
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
		   try{
			 $AddressUrl = $this->shopCredential['baseurl'].'/api/addresses/'.$address_id;
			 $xmlData = simplexml_load_file($AddressUrl);
			}catch(Exception $e){return $Address;} 
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
			try{
			$CustomerUrl = $this->shopCredential['baseurl'].'/api/customers/'.$address_id;
			$xmlData = simplexml_load_file($CustomerUrl);
			}catch(Exception $e){return array();} 
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
			try{
				$CountryUrl = $this->shopCredential['baseurl'].'/api/countries/'.$address_id;
				$xmlData = simplexml_load_file($CountryUrl);
			}catch(Exception $e){return array();} 
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
	
	public function getmainservicelist(){
     	try{
		 	$select = $this->_db->select()
						->from(array('SV'=>SERVICES), array('*'))
						->where("SV.status='1'")
						->where("SV.parent_service=0")
						->order("SV.service_id");
			 $services = $this->getAdapter()->fetchAll($select);
			 return $services;
			 					
		   }catch(Exception $e){
		  	$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  	return array();
		}
	}
	
	public function additionalserviceDetail(){
		try{
		 	$select = $this->_db->select()
						->from(array('SV'=>SERVICES), array('*'))
						->where("SV.status='1'")
						->where("SV.parent_service=".$this->getData['service'])
						->order("SV.service_id");
			 $addservices = $this->getAdapter()->fetchAll($select);
			 return $addservices;
			 					
		   }catch(Exception $e){
		  	$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  	return array();
		}
	}
	
	
	public function addusershopsetting(){
		
		$this->getData['user_id'] = Zend_Encript_Encription::decode($this->getData['token']);
		$this->getData['create_by'] = $this->Useconfig['user_id'];
		$this->getData['create_ip'] = commonfunction::loggedinIP();
		
		$parentId = $this->insertInToTable(SHOP_SETTINGS,array($this->getData));
		
		if(($this->getData['multishop']==1) && (count($this->getData['multiurl'])>0)){
			$MultiDataArr = array();
			$index = 0;
			foreach($this->getData['multiurl'] as $url){
				$MultiDataArr[$index]['shop_parent_id'] =  $parentId;
				$MultiDataArr[$index]['user_id'] 		= $this->getData['user_id'];
				$MultiDataArr[$index]['shop_type_id'] 	= $this->getData['shop_type_id'];
				$MultiDataArr[$index]['shop_name'] 		= $this->getData['shop_name'];
				$MultiDataArr[$index]['shop_url'] 		= $url;
				$MultiDataArr[$index]['api_key'] 		= $this->getData['api_key'];
				$MultiDataArr[$index]['api_secret'] 	= $this->getData['api_secret'];
				$MultiDataArr[$index]['service_id'] 	= $this->getData['service_id'];
				$MultiDataArr[$index]['add_service_id'] = $this->getData['add_service_id'];
				$MultiDataArr[$index]['weight'] 		= $this->getData['weight'];
				$MultiDataArr[$index]['created_by'] 	= $this->getData['created_by'];
				$MultiDataArr[$index]['created_ip'] 	= $this->getData['created_ip'];
				
				$index++;
			}
			
			$this->insertInToTable(SHOP_SETTINGS,$MultiDataArr);
		}
		
		return $parentId;
	}
	
	public function getUserShopSettings($parentId=0){
		$where = '1';
		if($parentId>0){
			$Parent = "SST.shop_parent_id=".$parentId ;
		}
		else{
			if(isset($this->getData['token'])){
				$UserId = Zend_Encript_Encription::decode($this->getData['token']);
				$where .= " AND SST.user_id=".$UserId ; 
			}
			if(isset($this->getData['shop'])){
				$ShopId = Zend_Encript_Encription::decode($this->getData['shop']);
				$where .= " AND SST.shop_id=".$ShopId ;
			}
			$Parent = "SST.shop_parent_id=0" ;
		}
		if($this->Useconfig['level_id']==5){
		  $where .= " AND SST.user_id='".$this->Useconfig['user_id']."'";
		}
		try{
		 	$select = $this->_db->select()
						->from(array('SST'=>SHOP_SETTINGS), array('*'))
						->joininner(array('STL'=>SHOPTYPE_LIST), 'STL.shop_type_id=SST.shop_type_id', array('shop_name as webshop'))
						->joinleft(array('SV'=>SERVICES), 'SV.service_id=SST.service_id', array('service_name as service'))
						->where($where)
						->where("SST.is_delete='0'")
						->where($Parent)
						->order("SST.shop_id",'ASC');	//print_r($select->__toString());die;
			 $ShopDetails = $this->getAdapter()->fetchAll($select);
			 return $ShopDetails;
			 					
		   }catch(Exception $e){
		  	$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  	return array();
		}
	}
	
	
	public function editshopsetting(){
		$ShopId = Zend_Encript_Encription::decode($this->getData['shop']);
		
		$this->getData['modify_by'] 	= $this->Useconfig['user_id'];
		$this->getData['modify_date']   = new Zend_Db_Expr('NOW()');
		$this->getData['modify_ip'] 	= commonfunction::loggedinIP();
		
		$this->UpdateInToTable(SHOP_SETTINGS,array($this->getData),'shop_id='.$ShopId);
		
		if(($this->getData['multishop']==1) && (count($this->getData['multiurl'])>0)){
			
			$index = 0;	$insertedId = array();
			foreach($this->getData['multiurl'] as $url){
				if(!empty($url)){
					$MultiDataArr = array();
					$MultiDataArr['shop_parent_id'] = $ShopId;
					$MultiDataArr['user_id'] 		= $this->getData['user_id'];
					$MultiDataArr['shop_type_id'] 	= $this->getData['shop_type_id'];
					$MultiDataArr['shop_name'] 		= $this->getData['shop_name'];
					$MultiDataArr['shop_url'] 		= $url;
					$MultiDataArr['api_key'] 		= $this->getData['api_key'];
					$MultiDataArr['api_secret'] 	= $this->getData['api_secret'];
					$MultiDataArr['service_id'] 	= $this->getData['service_id'];
					$MultiDataArr['add_service_id'] = $this->getData['add_service_id'];
					$MultiDataArr['weight'] 		= $this->getData['weight'];
					$MultiDataArr['created_by'] 	= $this->Useconfig['user_id'];;
					$MultiDataArr['created_ip'] 	= commonfunction::loggedinIP();
					
					$insertedId[$index] = $this->insertInToTable(SHOP_SETTINGS,array($MultiDataArr));
					$index++;
				}
			}
			
			if(count($insertedId)>0){
				$AllId = commonfunction :: implod_array($insertedId);
				$this->_db->delete(SHOP_SETTINGS,"shop_parent_id=".$ShopId." AND shop_id NOT IN(".$AllId.")");
			}
			
			
		}
		else if($this->getData['multishop']==0){
			$updatedArr['is_delete'] 	= '1';
			$updatedArr['deleted_by'] 	= $this->Useconfig['user_id'];
			$updatedArr['deleted_date'] = new Zend_Db_Expr('NOW()');
			$updatedArr['deleted_ip'] 	= commonfunction::loggedinIP();
			$updatedArr['modify_by'] 	= $this->Useconfig['user_id'];
			$updatedArr['modify_date']  = new Zend_Db_Expr('NOW()');
			$updatedArr['modify_ip'] 	= commonfunction::loggedinIP();
		
		$this->UpdateInToTable(SHOP_SETTINGS,array($updatedArr),"shop_parent_id=".$ShopId);
		}
		return $ShopId;
	}
	
	
	public function deleteshop(){
		
		$ShopId = Zend_Encript_Encription::decode($this->getData['shop']);
		
		$updatedArr['is_delete'] 	= '1';
		$updatedArr['deleted_by'] 	= $this->Useconfig['user_id'];
		$updatedArr['deleted_date'] = new Zend_Db_Expr('NOW()');
		$updatedArr['deleted_ip'] 	= commonfunction::loggedinIP();
		
		$this->UpdateInToTable(SHOP_SETTINGS,array($updatedArr),'shop_id='.$ShopId." OR shop_parent_id=".$ShopId);
		return $ShopId;
	}
	
  public function ShopBulkimport(){
      $select = $this->_db->select()
						->from(array('SS'=>SHOP_SETTINGS),array('*','CONCAT(SS.shop_name,"-",ST.shop_name) AS full_name'))
						->joininner(array('ST' =>SHOPTYPE_LIST),"ST.shop_type_id=SS.shop_type_id",array(''))
						->where("SS.is_delete='0' AND SS.user_id='".$this->Useconfig['user_id']."' AND SS.shop_parent_id=0");//print_r($select->__toString());die;
		$allshops =  $this->getAdapter()->fetchAll($select);
		$Allorders = array();
		foreach($allshops as $allshop){
		    $this->getData['shop_id'] = $allshop['shop_id'];
		    $orders = $this->ImportFromShop(true);//print_r($orders);die;
			$Allorders = array_merge($orders,$Allorders);
		}
		if(!empty($Allorders)){
	      $this->importShipment(0,$Allorders);
		  //$this->getData['shipment_mode']='Print';
		 // $this->CreateLabel();
	    }	
  }
  public function updateOrders($data){ 
  	// echo "<pre>"; print_r($data);die;

       $data['shipmentData'] = $this->getShopShipment($data);
	   switch($data['shipment_type']){
	      case 6:
		     $orders = $this->SeoShopUpdate($data);
		  break;
	      case 7:
		     $orders = $this->PrestashopUpdate($data);
		  break;
		  case 8:
		     $orders = $this->MagentoUpdate($data);
		  break;
		  case 9:
		    $orders = $this->CCVShopUpdate($data);
		  break;
		  case 11:
		    $orders = $this->BOLShopUpdate($data);
		  break;
		  case 12:
		    $orders = $this->WoocommerceUpdate($data);
		  break;
		  case 14:
		    $orders = $this->MijnwebwinkelUpdate($data);
		  break;
		  case 17:
		    $orders = $this->ShopTradeUpdate($data);
		  break;
	  }
	  return true;
  }

  public function ShopTradeUpdate($orderdata='')
  {
  	global $objSession;
  	$shipmentData = $orderdata['shipmentData'];
	$objSession->shoptradeShop_id = $shipmentData['shop_id'];
 	// echo "<pre>"; print_r($orderdata);die;
 	 
 	$data = array('order'=>$shipmentData,'status'=>9011); 
 	$ShopTradeObj = new Application_Model_ShopTrader();
 	$update = $ShopTradeObj->PerformShopTradeQuery('update',$data);
 	if($update['status'])
 		$this->UpdateShopShipment($orderdata);
 	else return false;
 	
  	// echo "<pre>"; print_r($update);die;
  	# code...
  }

  public function PrestashopUpdate($data){
        $this->shopCredential['shop_url'] = commonfunction::stringReplace(array('https://','http://'),'',$this->shopCredential['shop_url']);
		$this->shopCredential['baseurl'] = 'http://'.$this->shopCredential['shop_url'].'/';
		if($this->Useconfig['user_id']==3453){
			$this->shopCredential['baseurl'] = 'https://'.$this->shopCredential['shop_url'].'/';
		}
	
		 try{
			$PSWebService = new Zend_Pswebservicelibrary($this->shopCredential['baseurl'],$this->shopCredential['api_key'],true);
			$opt = array('resource' => 'orders'); 
			$opt['id'] = $this->shopCredential['shop_order_id']; 
			$xml = $PSWebService->get( $opt );
			$resources = $xml->children()->children();
			
			foreach ($resources as $nodeKey => $node)
			{	if($nodeKey=='current_state'){
					$resources->$nodeKey = 4;
				}
				if($nodeKey=='shipping_number'){
					$resources->$nodeKey = $data[TRACENR_BARCODE];
				}
			}
			$opt = array('resource' => 'orders');
			$opt['putXml'] = $xml->asXML();
			$opt['id'] = $this->shopCredential['shop_order_id']; 
			$response = $PSWebService->edit($opt);
			if(trim(commonfunction::utf8Decode(commonfunction::utf8Decode($response->children()->order->current_state)))==4){
			     $this->UpdateShopShipment($data);
			}
		   }catch(Exception $e){	
		   $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  	return false; 
		}  
  }
  
  public function SeoShopUpdate($data){
	   $seoshopurl = (isset($this->shopCredential['shop_url']) && !empty($this->shopCredential['shop_url'])) ? trim($this->shopCredential['shop_url']) : 'api.webshopapp.com/nl/';
	 try{  
	    $xmlData = simplexml_load_file('http://'.$this->shopCredential['api_key'].':'.$this->shopCredential['api_secret'].'@'.$seoshopurl.'shipments.xml?order='.$data['shipmentData']['shop_order_id']);
	   $shipments = json_encode($xmlData);
	   $shipmentdecoded = json_decode($shipments,TRUE);
	   $shop_shipment_id = isset($shipmentdecoded['shipment']['id'])?$shipmentdecoded['shipment']['id']:0;
	   
	   $fullurl ='http://'.$this->shopCredential['api_key'].':'.$this->shopCredential['api_secret'].'@'.$seoshopurl.'shipments/'.$shop_shipment_id.'.json';
	   
	   //$fullurl ='http://'.$this->shopCredential['api_key'].':'.$this->shopCredential['api_secret'].'@'.$seoshopurl.'shipments/'.$data['shipment_id'].'.json';
	  
	   $data_shop['shipment'] = array("number"=>$data[TRACENR_BARCODE],'status'=>'shipped','trackingCode'=>'http://www.dpost.be/Parceltracking/tracking/tockenno/'.Zend_Encript_Encription::encode($data[BARCODE_ID]),'doNotifyTrackingCode'=>true);
	  
	   $data_json = json_encode($data_shop);
	   $ch = curl_init();
	   curl_setopt($ch, CURLOPT_URL, $fullurl);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	   curl_setopt($ch, CURLOPT_POST, true);
	   curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // note the PUT here
	   curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
	   curl_setopt($ch, CURLOPT_HEADER, true);
	   curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: '. strlen($data_json)));    
	   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	   if(curl_exec($ch) === false){
	      $response = curl_error($ch);
		   $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.json_encode($response));
	   }
	   else{
		$response  = curl_exec($ch);
		$response = json_encode($response);
	   	$response = substr($response,11,6);
		$this->UpdateShopShipment($data);
	   }
	   curl_close($ch);
	   
	  }catch(Exception $e){	
		   $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  	return false; 
		} 
  }
  
  public function MagentoUpdate($data){
      try{
		$client = new SoapClient($this->shopCredential['shop_url'].'/api/?wsdl');
		$session = $client->login($this->shopCredential['api_secret'], $this->shopCredential['api_key']);
        $result = $client->call($session, 'sales_order.addComment', array('shipmentIncrementId' => $this->shopCredential['shop_order_id'], 'carrier_code' =>$data['forwarder_detail']['forwarder_name'], 'title' => 'Complete', 'track_number'=>$data[TRACENR_BARCODE],'status' =>'complete'));
		$this->UpdateShopShipment($data);
        }catch(Exception $e){
		   $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		}
  }
  
  public function CCVShopUpdate($data){
        $sPublic_key = $this->shopCredential['api_key'];
  		$sSecret_key = $this->shopCredential['api_secret'];
  		$sMethod = 'PATCH';
  		$sUri = '/api/rest/v1/orders/'.$this->shopCredential['shop_order_id'];//.70657845
		$sDomain = $this->shopCredential['shop_url'];
  		date_default_timezone_set('UTC'); 
  		$sTimeStamp = date('c'); 
		$aData = array(
				"status"=>5,
				"mail"=>false,
				"note"=>"test note",
				"track_and_trace_code"=>"http://www.dpost.be/Parceltracking/tracking/tockenno/".Zend_Encript_Encription::encode($data[BARCODE_ID])
		);
  		$sData = json_encode($aData); 
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
  		curl_setopt($CurlHandler, CURLOPT_POSTFIELDS, $sData); 
  		curl_setopt($CurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod); 

  		$sOutput = curl_exec($CurlHandler); //print_r($sOutput);exit;
  		//Response : a product object
  		$oResponse = json_decode($sOutput); 
  		curl_close($CurlHandler); 
		$this->UpdateShopShipment($data);
		return true;
  }
  
  public function BOLShopUpdate($data){
    	$url   = 'https://plazaapi.bol.com';
		$uri   = '/services/rest/shipments/v2';
		$public_key  = $this->shopCredential['api_key'];
		$private_key  = $this->shopCredential['api_secret'];
		$port = 443;
		
		$contentType = 'application/xml';
		$date = gmdate('D, d M Y H:i:s T'); 
		$httpmethod = 'POST';
		
		$signatureString = $httpmethod . "\n\n"; 
		$signatureString .= $contentType . "\n"; 
		$signatureString .= $date."\n"; 
		$signatureString .= "x-bol-date:" . $date . "\n";
		$signatureString .= $uri;
		
		$signature = $public_key . ':' . base64_encode (hash_hmac ('SHA256', $signatureString, $private_key, true));

		$orderitems_ids = explode(',',$data[REFERENCE]);
		foreach($orderitems_ids as $orderitems_id){
		$field = '<ShipmentRequest xmlns="https://plazaapi.bol.com/services/xsd/v2/plazaapi.xsd">
				 <OrderItemId>'.$orderitems_id.'</OrderItemId>
				 <ShipmentReference>'.$orderitems_id.'</ShipmentReference>
				 <DateTime>'.date("Y-m-d").'T'.date("h:i:s").'+01:00'.'</DateTime>
				 <ExpectedDeliveryDate>'.date("Y-m-d",strtotime('+2 days')).'T'.date("h:i:s").'+01:00'.'</ExpectedDeliveryDate>
				 <Transport>
				   <TransporterCode>PARCEL-NL</TransporterCode>
				   <TrackAndTrace>'.$data[TRACENR_BARCODE].'</TrackAndTrace>
				 </Transport>
				</ShipmentRequest>';
		 
		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Content-type:" .$contentType, "X-BOL-Date:".$date, "X-BOL-Authorization:".$signature));
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_URL, $url . $uri);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $field);
		if (curl_errno($ch)) {
		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.json_encode(curl_errno($ch)));
		}
		$curlresponse = curl_exec($ch);
		curl_close ($ch);
		$this->UpdateShopShipment($data);
		}
  }
  
  
  public function WoocommerceUpdate($data){
       try{
		$wc_api = new Application_Model_Wcmanager($this->shopCredential['api_key'],$this->shopCredential['api_secret'], $this->shopCredential['shop_url']);
        $ordersDetail = $wc_api->update_order($this->shopCredential['shop_order_id'],array( 'status' => 'completed' ));
		$this->UpdateShopShipment($data);
	   }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
	   }
		return true;
  }
  
  public function MijnwebwinkelUpdate($data){
    if($this->shopCredential['api_key']!=''){
			$payload = array('status'=>2,'archived'=>false,'finished'=>false,'comments'=>array('internal'=>$data['forwarder_detail']['forwarder_name'].':'.$data[TRACENR_BARCODE]));
			$curlOpt[CURLOPT_URL] = "https://api.mijnwebwinkel.nl/v1/orders/".$this->shopCredential['shop_order_id']."?language=nl_NL&format=json&partner_token=".$this->shopCredential['api_key']."&token=".$this->shopCredential['api_secret']."";
		$curlOpt[CURLOPT_RETURNTRANSFER] = TRUE;
		$curlOpt[CURLOPT_SSL_VERIFYPEER] = TRUE;
		$curlOpt[CURLOPT_HEADER] = false;

	$curlOpt[CURLOPT_HTTPHEADER] = array(
		'Content-Type:application/json',
		'Accept: application/json', 
		'Content-length: ' . strlen(json_encode($payload))
	);
		$curlOpt[CURLOPT_POSTFIELDS] = json_encode($payload);
		$curlOpt[CURLOPT_CUSTOMREQUEST] = strtoupper('PATCH');
		$curlHandle = curl_init();
		curl_setopt_array($curlHandle, $curlOpt);
		$execute = curl_exec($curlHandle);
		$this->UpdateShopShipment($data);
	}
		return false;
  }
  
  public function getShopShipment($data){
       $select = $this->_db->select()
						->from(array('SS'=>SHOP_API_SHIPMENT),array('shop_order_id'))
						->joininner(array('ST' =>SHOP_SETTINGS),"ST.shop_id=SS.shop_id",array('*'))
						->where("SS.shipment_id='".$data['shipment_id']."'");//print_r($select->__toString());die;
		$this->shopCredential =  $this->getAdapter()->fetchRow($select);
		return $this->shopCredential;
  }
  
  public function UpdateShopShipment($data){
    $this->_db->update(SHOP_API_SHIPMENT , array('update_date'=>new Zend_Db_Expr('NOW()'),'updated_ip'=>commonfunction::loggedinIP(),'updated_by'=>$this->Useconfig['user_id']),"shipment_id='".$data['shipment_id']."'");
  }
  
  public function Getcredentials($shop_id){
        $select = $this->_db->select()
						->from(array('SS'=>SHOP_SETTINGS),array('*'))
						->joininner(array('ST' =>SHOPTYPE_LIST),"ST.shop_type_id=SS.shop_type_id",array(''))
						->where("SS.is_delete='0' AND SS.shop_id='".$shop_id."' AND SS.shop_parent_id=0");
		return   $this->getAdapter()->fetchRow($select);
  
  }
	
}

