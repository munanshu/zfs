<?php

class Application_Model_ShopTrader extends Zend_custom {
 
    private $strApiBaseUrl = 'api.shoptrader.nl';
    private $strOutput = 'json'; 
    private $strUrl = '';
    private $OrderUpdateUrl = '';
    private $ApiUrl = 'Api/v1/Get/Orders/';
    private $ApiPostOrderUrl = '/Api/v1/Post/Orderstatus/';
    private $ApiCall = '';
    private $_params = array();
    private $orders_statuses = array('7','4','9011');
    private $url;
    private $ReqParams = array();
  	const METHOD_POST = 'post';
	const METHOD_GET = 'get';
	const METHOD_PUT = 'put';
	public $allorders = array();


  	public function init()
  	{	
		// echo "<pre>";print_r($this);die;

  		parent::init();
  		 
  		// $this->setApiCredentials($cred);
        $this->ShopApiObjModel = new Application_Model_Shopapi();
  	}

  	 

 //    private function inputs() {

 //        $this->_params = $this->getData;
 //    }

     public function setApiCredentials($cred='')
	{
		$this->api_key =  $cred['api_key']; 	
	 	$this->api_secret = $cred['api_secret']; 	
	 	 	
	}


	 public function SendRequest($apicall,$data,$headers='',$returnformat='')
	{	
		$this->query_string = http_build_query($data);
		$this->ApiCall = $apicall;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_URL, $this->ApiCall);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->query_string);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		if($returnformat == 'json')
			$response = json_decode($response);
		curl_close($ch);

		return $response; 
		 
	}

	 public function getOrders($limit,$statuses)
	{	
		ini_set('display_errors', 1);
		$resp = array();
		$resporders = array();
		$this->strUrl = 'https://'.$this->strApiBaseUrl.'/'.$this->ApiUrl.'?'.$this->strOutput;
		$this->orders_statuses = $statuses;
		$arrParams = array();
    	$arrParams['shopnr'] = $this->api_secret;
    	$arrParams['token']  = $this->api_key;
        $arrParams['limit'] = $limit;
    		foreach ($this->orders_statuses as $key => $value) {
    				// echo $value;
    				$arrParams['orders_status_id'] = $value;
    				$response = $this->SendRequest($this->strUrl,$arrParams,'','json');
    				if(@property_exists($response, 'order')){
    						$resp = (array)$response->order;

    						$this->setupOrders($resp);
    				}
    		}
    	 return $this->allorders;
	}


	public function setupOrders($orders='')
	{

		foreach ($orders as $k => $val) {

    							foreach ($val->order_totals->order_total as $taxkey => $taxvalue) {

	 	   								$val->totaling[$taxvalue->class] = $taxvalue;
								}

    							$this->allorders[$k] = $val;
    		}


	}

	 

	public function PerformShopTradeQuery($action='',$data='')
	{

		global $objSession;
		$usercredential = $this->ShopApiObjModel->Getcredentials($objSession->shoptradeShop_id);
		$cred = $this->ShopApiObjModel->getShopList($objSession->shoptradeShop_id);
		$this->setApiCredentials($cred);
		// echo "<pre>";print_r($cred);die;

		switch ($action) {
			case 'count':
				// $this->getData['orderlimit'] = $limit;	
				// $this->getData['statuses'] = $statuses;	
				$totalOrders = $this->getOrders($data['limit'],$data['statuses']);
				// echo "<pre>"; print_r($totalOrders);die;
				return count($totalOrders);
				break;
			
			case 'import':
				// $this->getData['orderlimit'] = $limit;	
				// $this->getData['statuses'] = $statuses;	
				$totalOrders = $this->getShopTradeOrders($data['limit'],$data['statuses'],$usercredential);
				return $totalOrders;
				break;
			case 'update':
				// $this->getData['orderlimit'] = $limit;	
				// $this->getData['statuses'] = $statuses;	
				$totalOrders = $this->UpdateSingleOrderStatusWebshop($data['order'],$data['status'],$usercredential);
				return $totalOrders;
				break;	
			default:
				# code...
				break;
		}

		# code...
	}



	public function getShopTradeOrders($limit,$statuses,$usercredential='')
	{	
		$orderdata = array();
		try {
				
				$orders = $this->getOrders($limit,$statuses);
				$resp = array('status'=>1,'response'=>$orders);
			} catch (Exception $e) {
				$resp = array('status'=>0,'message'=>$e->getMessage());
			}	

		// echo "<pre>";print_r($orders);die;

			if(!$resp['status'])
				return $resp;
			 
			
				 	foreach ($orders as $key => $value) {

				 	 

				 	$OrderID = current($value)->order_id;	
				 	$OrderInfo = $value->info;	
					$CustomerResponse = $value->customer;	
				 	$CustomerId = current($CustomerResponse)->customer_id;	
					$data['user_id']  = $usercredential['user_id'];
					$data['price']  = $value->totaling['ot_total']->value_in;
					$data['currency']  = $OrderInfo->currency;
					$data['status']  = $this->orders_statuses[0];
					$data['orderguid']  = $OrderID;
					$data['shop_order_id']  = $OrderID;
					$data['rec_reference']  = $OrderID;
					$data['eo_order_by']  = $CustomerId;
					$data['shop_id']      =  $usercredential['shop_id'];
					$data['import_order_status']   = 'open';
					$data['weight'] = $usercredential['weight'];
					$data[SERVICE_ID] 		= (!empty($usercredential['service_id']) && $usercredential['service_id']>0) ? $usercredential['service_id'] : 1;
					$data[ADDSERVICE_ID] 		= (!empty($usercredential['addservice_id']) && $usercredential['addservice_id']>0) ? $usercredential['addservice_id'] : '';
					$data['shipment_type'] = 17;


					$invoice = $value->billing;	
					$delivery = $value->delivery;	

					$firstlast =(!empty($CustomerResponse->customers_name))? $CustomerResponse->customers_name : ( (!empty($invoice->billing_name))? $invoice->billing_name: ( (!empty($CustomerResponse->customers_company))? $CustomerResponse->customers_company: ( (!empty($invoice->billing_company))? $billing->billing_company: '' )  )  )    ;

					if(!empty($delivery->delivery_street_address) && !empty($delivery->delivery_city) && !empty($delivery->delivery_postcode) && !empty($delivery->delivery_country_iso_2) ){

	 				 

	 		 

	 				}



					$rec_name = $firstlast;

					$data['rec_name']  = $rec_name;
					$data['rec_street']  = $CustomerResponse->customers_street_address;
					$data['rec_city']  = $CustomerResponse->customers_city;
					$data['senderaddress_id'] 	= $this->ShopApiObjModel->getSenderID($usercredential['sender_code']);

					$country_details = $this->getCountryDetail($CustomerResponse->customers_country_iso_2,2);
					$data['country_id']  = $country_details['country_id'];
					$data['rec_zipcode']  = $CustomerResponse->customers_postcode;
					$data['rec_email']  = $CustomerResponse->customers_email_address;
					 
					$orderdata[] = $data;
				 }	

		 return $orderdata;	

	}

	 

	public function UpdateSingleOrderStatusWebshop($order='',$status)
    {
        if(!empty($order) && is_array($order)){

            $this->strUrl = 'https://'.$this->strApiBaseUrl.'/'.$this->ApiPostOrderUrl.'?'.$this->strOutput;
            			$this->ReqParams['shopnr'] = $this->api_secret;
    					$this->ReqParams['token']  = $this->api_key;
                        $this->ReqParams['order_id'] = array($order['shop_order_id']);
                        $this->ReqParams['orders_status_id'] = $status;

                 try {
                        $resp = $this->SendRequest($this->strUrl,$this->ReqParams,'','json');
                        
                        // echo "<pre>"; print_r($resp);die;
                         
                     if(!empty($resp)){

                        if(@property_exists($resp,'success'))
                           {
                                 
                                $resp = array('status'=>1,'NewStatus'=>$status,'Response'=>$resp,'IsOrderUpdated'=>true);
                           }else {
                             $resp = array('status'=>0,'Response'=>current($resp->order)->error->message,'IsOrderUpdated'=>false);
                           } 
                       }else {
                             $resp = array('status'=>0,'Response'=>$resp,'IsOrderUpdated'=>false);
                       }    
                    } catch (Exception $e) {
                        $resp = array('status'=>0,'Response'=>$e->getMessage(),'IsOrderUpdated'=>false);        
                    }

        }else $resp = array('status'=>0,'message'=>'Empty Order','data'=>$order);

        return $resp;
    }
	 
	

	 

	 

	 

	 

	 

	 

	 

	 
	 
	 

	// public function sendRequest($url, $method, $payload = NULL)
	// {
	// 	if ($payload && !is_array($payload)) {
	// 		throw new \ErrorException('Payload is not valid.');
	// 	}
		
		 
		
	// 	$requestUrl = $url;
	// 	// Base cURL option
	// 	if ($method == self::METHOD_GET) {
	// 		if(isset($payload) && !empty($payload))
	// 		$requestUrl = $requestUrl."&".http_build_query($payload);
			
	// 	}
	// 	// echo "<pre>";
	// 	// print_r($requestUrl);die;
	// 	$curlOpt = array();
	// 	$curlOpt[CURLOPT_URL] = $requestUrl;
	// 	$curlOpt[CURLOPT_RETURNTRANSFER] = TRUE;
	// 	$curlOpt[CURLOPT_SSL_VERIFYPEER] = FALSE;
	// 	$curlOpt[CURLOPT_HEADER] = false;
			
	// 	// if ($method == self::METHOD_POST ) {
	// 	// 	$curlOpt[CURLOPT_HTTPHEADER] = array(
	// 	// 	    'Content-Type:application/json', 
	// 	// 	    'access_token:' . $accessToken, 
	// 	// 	    'Content-length: ' . strlen(json_encode($payload))
	// 	// 	);
	// 	// 	$curlOpt[CURLOPT_POSTFIELDS] = json_encode($payload);
	// 	// 	$curlOpt[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
	// 	// 	// echo "<pre>";
	// 	// 	// print_r($curlOpt);die;
	// 	// }

	// 	// if ($method == self::METHOD_PUT) {
	// 	// 	$curlOpt[CURLOPT_HTTPHEADER] = array(
	// 	// 	    'Content-Type:application/json', 
	// 	// 	    // 'access_token:' . $accessToken, 
	// 	// 	    'Content-length: ' . strlen(json_encode($payload))
	// 	// 	);
	// 	// 	$curlOpt[CURLOPT_POSTFIELDS] = json_encode($payload);
	// 	// 	$curlOpt[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
			
	// 	// 	// echo "<pre>";
	// 	// 	// print_r($curlOpt);die;
	// 	// }
		
	// 	$curlHandle = curl_init();
	// 	curl_setopt_array($curlHandle, $curlOpt);
		
	// 	return curl_exec($curlHandle);
	// }

	 

	 

	 

	 


	// public function getPrintedExactLabelTracking()
	// {	

	// 	global $objSession;
	// 	$exact_shop_id = $objSession->exactShop_id;
	// 	$CurrentUser = $this->Useconfig['user_id'];

	// 	// $selectnotin = $this->_db->select()->from(array('CGD'=>CreatedGoodsDeliverylines), array('CGD.shipment_id'))
	// 	// 	->where("CGD.web_shop_id='$exact_shop_id'");
	// 	// 	// echo $selectnotin->__toString();die;
	// 	// $notinres = $this->getAdapter()->fetchAll($selectnotin);
		
	// 	// echo "<pre>"; print_r($notinres);die;	

	// 	$select = $this->_db->select()->from(array('SAS'=>SHOP_API_SHIPMENT),array('SAS.shipment_id','SAS.shop_id','SAS.shop_order_id' ))
	// 		->joininner(array('PS'=>SHIPMENT),'PS.shipment_id = SAS.shipment_id',array('PS.user_id','PS.shipment_id'))
	// 		->joininner(array('SB'=>SHIPMENT_BARCODE),'SB.shipment_id = SAS.shipment_id',array('SB.tracenr_barcode','SB.barcode_id','SB.barcode'))
	// 		->group('SB.shipment_id')
	// 		->where("SAS.shop_id='".$exact_shop_id."' && PS.user_id='$CurrentUser' && SAS.shipment_id NOT IN (select CGD.shipment_id from ".CreatedGoodsDeliverylines." as CGD where CGD.web_shop_id = '".$exact_shop_id."' ) " );
	// 		// echo $this->Useconfig['user_id'];die;
	// 	  try {
		  		
	// 			$result = $this->getAdapter()->fetchAll($select);	
	// 	  		// echo "<pre>"; print_r($result);die;
	// 	  		if(!empty($result) && is_array($result)){

	// 	  			foreach ($result as $key => $value) {
	// 	  				$selectinner = $this->_db->select()->from(array('GDO'=>GoodsDeliveryOrderlines),array('GDO.*'))
	// 					// // ->group('SB.shipment_id')
	// 					->where("GDO.order_id='".$value['shop_order_id']."' &&  GDO.user_id='$CurrentUser'");
	// 					// echo $selectinner->__toString();die;
	// 					$res = $this->getAdapter()->fetchAll($selectinner);	
	// 					$result[$key]['OrderLines']=$res;
	// 	  			}

	// 	  			$resp = array('status'=>1,'data'=>$result);
	// 	  		}
	// 	  		else $resp = array('status'=>0,'message'=>'No Order available with printed label');

	// 	  	} catch (Exception $e) {
	// 	  		$resp = array('status'=>0,'message'=>$e->getMessage());
	// 	  	}	
	// 	  	// die;
	// 	return $resp;		
	// }


}
