<?php

class Application_Model_Exactnew extends Zend_custom {
 
    public $client_id = '';	
	public $client_secret = '';	
	// public $redirect_uri = "http://exact.sjmsoftech.in/oauth/index.php";	
	public $redirect_uri = "";	
	public $division = "";	
	public $grant_type = "authorization_code";	
	public $tokenurl = "https://start.exactonline.com/api/oauth2/token";	
	public $authurl = "https://start.exactonline.com/api/oauth2/auth";	
	private $accessToken;	
	private $refreshToken;	
	private $tokenType;	
	private $expiresIn;	
	private $countryCode = 'com';	
	private $OrdersUrl = 'salesorder/SalesOrders';
	private $GoodsDeliveryUrl = 'salesorder/GoodsDeliveries';
	private $ItemsUrl = 'logistics/Items';
	const GRANT_REFRESH_TOKEN = 'refresh_token';
	const URL_API = 'https://start.exactonline.%s/api/v1/';
	const METHOD_POST = 'post';
	const METHOD_GET = 'get';
	const METHOD_PUT = 'put';
	public $_params = array();
    public $_session = [];
    public $TotalExactOrders = array();
    public $SkipToken = "";
  	
  	public function init()
  	{	
  		parent::init();
  		$this->inputs();
        $this->enable_session();
        $this->ShopApiObjModel = new Application_Model_Shopapi();
  		// print_r($this->_session);die;
  	}

  	public function enable_session()
  	{	
		$logicSeesion = new Zend_Session_Namespace('ExactSession');

  		$this->_session = $logicSeesion;
  	}

    private function inputs() {

        $this->_params = $this->getData;
        // print_r($this->_params);die;
    }

     public function SetCredentialsSession($cred)
    {   
        // print_r($this->_params);die;
         if(isset($cred) && !empty($cred) && is_array($cred)){

                foreach ($cred as $key => $value) {
                	$this->_session->$key=$value;
                }

         }
         
          
    }

	public function getResponse($url, $params)
	{	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
		$result = curl_exec($ch);
		$decodedResult = json_decode($result, TRUE);
		
		if (isset($decodedResult['error'])) {
			return FALSE;
		}
		
		return $decodedResult;
	}

	public function getuserapicredentials($usercredential='')
	 {
	 	$apicredential = array(
			'api_key'=> $usercredential['api_key'],
			'api_secret'=> $usercredential['api_secret'],
			'redirect_uri'=> "http://localhost/parcel/exactonline/performexactquery",
			'division'=> $usercredential['division'],
			);
	 	return $apicredential;
	 } 

	public function getexactorders($usercredential,$action='')
	{	

		// $apicredential = array(
		// 	'api_key'=>'ccf25e43-8ffe-484f-b7af-07eb69353382',
		// 	'api_secret'=> 'jZ27hBs8wvC8',
		// 	'redirect_uri'=> "http://localhost/parcel/exactonline/performexactquery",
		// 	'division'=> "38345",
		// 	);
		// $apicredential = array(
		// 	'api_key'=>'ccf25e43-8ffe-484f-b7af-07eb69353382',
		// 	// 'api_key'=>'a98a36ae-eb1e-40d6-8ae4-d954e7da1666',
		// 	'api_secret'=> 'jZ27hBs8wvC8',
		// 	// 'api_secret'=> 'zTqefYX0NiBH',
		// 	'redirect_uri'=> "http://localhost/parcel/exactonline/performexactquery",
		// 	'division'=> "38345",
		// 	// 'division'=> "38129",
		// 	);
		$apicredential = $this->getuserapicredentials($usercredential);
echo "<pre>";print_r($apicredential);die;

		try {
				
				$orders = $this->Process($apicredential)->getOrders();
				$resp = array('status'=>1,'response'=>$orders);
			} catch (Exception $e) {
				$resp = array('status'=>0,'message'=>$e->getMessage());
			}	


			if(!$resp['status'])
				return $resp;
			if( isset($action) && $action == 'count'){
				$resp = array('status'=>1,'response'=>count($orders));
				return $resp;
			}
			foreach ($orders as $key => $order) {
				 		
					$OrderInfo = $order['OrderInfo'][0];	
					$CustomerResponse = $order['CustomerResponse'];	
					$OrderLines = $order['OrderLines'];	
				 	$reference = (is_string($OrderInfo->Description))?$OrderInfo->Description:'';

					$data['user_id']  = $this->Useconfig['user_id'];
					$data['price']  = $OrderInfo->AmountDC;
					$data['approvestatus']  = $OrderInfo->ApprovalStatus;
					$data['currency']  = $OrderInfo->Currency;
					$data['status']  = $OrderInfo->DeliveryStatus;
					$data['orderguid']  = $OrderInfo->OrderID;
					$data['shop_order_id']  = $OrderInfo->OrderNumber;
					$data['rec_reference']  = $reference;
					$data['eo_order_by']  = $OrderInfo->OrderedBy;
					$data['shop_id']      =  $usercredential['shop_id'];
					$data['import_order_status']   = 'open';
					$data['weight'] = $usercredential['weight'];
					$data[SERVICE_ID] 		= (!empty($usercredential['service_id']) && $usercredential['service_id']>0) ? $usercredential['service_id'] : 1;
					$data[ADDSERVICE_ID] 		= (!empty($usercredential['addservice_id']) && $usercredential['addservice_id']>0) ? $usercredential['addservice_id'] : '';
					$data['shipment_type'] = 13;
					$data['rec_name']  = $CustomerResponse->Name;
					$data['rec_street']  = $CustomerResponse->AddressLine1;
					$data['rec_city']  = $CustomerResponse->City;
					$data['senderaddress_id'] 	= $this->ShopApiObjModel->getSenderID($usercredential['sender_code']);
					$country_details = $this->getCountryDetail($CustomerResponse->Country,2);
					$data['country_id']  = $country_details['country_id'];
					$data['rec_zipcode']  = $CustomerResponse->Postcode;
					$data['rec_email']  = $CustomerResponse->Email;
					$TrackingNumber = "Dpost".$OrderInfo->OrderNumber;
					foreach ($OrderLines as $k => $val) {


						$dataOrderLines[]  = array(
							'salesorder_line_guid' => $val->ID,
							'order_id' => $OrderInfo->OrderNumber,
							'user_id' => $this->Useconfig['user_id'],
							'ExactDeliveryDate' => $OrderInfo->DeliveryDate,
							'Deliverydate' =>  $OrderInfo->DeliveryDate,
							'customer_guid' => $OrderInfo->DeliverTo,
							'Item_guid' => $val->Item,
							'ItemCode' => $val->ItemCode,
							'Quantity' => $val->Quantity,
							'QuantityDelivered' => $val->QuantityDelivered,
							// 'TrackingNumber' => $TrackingNumber,
							'Notes' =>'',
							'Description' => '',
							);
					}

					$data['dataOrderLines']  = $dataOrderLines;

					// $insertedOrderLines = $this->ExactModel->InsertOrderLines($data);
					// $data['insertedOrderLines']  = $insertedOrderLines;
					$orders[$key]['requiredData'] = $data;
					$orders[$key]['TrackingNumber'] = $TrackingNumber;
					unset($dataOrderLines);
					unset($data['dataOrderLines']);
					$orderdata[] = $data;
					// $CompleteOrderData[] = $order;
			}

				$resp = array('status'=>1,'response'=>$orders,'orderdata'=>$orderdata);

		 return $resp;	

	}

	public function ConvertExactDate($orderdate='')
	{

		$pos1 = strpos($orderdate, "(");
			 $orderdate = substr($orderdate, $pos1+1,-2);
			 $orderdate = "/Date($orderdate-0500)/";
			 preg_match('/(\d{10})(\d{3})([\+\-]\d{4})/', $orderdate, $matches);
			 $orderdate = DateTime::createFromFormat("U.u.O",vsprintf('%2$s.%3$s.%4$s', $matches));
			 $orderdate = date_format($orderdate,'l d-m-Y H:i:s');
			 return $orderdate;
	}

	 

	public function setApiCredentials($cred='')
	{
		$this->client_id =  $cred['api_key']; 	
	 	$this->client_secret = $cred['api_secret']; 	
	 	$this->redirect_uri = $cred['redirect_uri']; 	
	 	$this->division = $cred['division']; "38345";	
	}

	public function Process($credentials)
	{
		// echo"<pre>";print_r($credentials); die;
		$this->setApiCredentials($credentials);
		if(!isset($this->getData['code'])){
			header("Location:".$this->authurl."?client_id=".$this->client_id."&client_secret=".$this->client_secret."&redirect_uri=".$this->redirect_uri."&response_type=code");
			exit;
		}
		else {
				if( !isset($this->_session->access_token) || empty($this->_session->access_token) ) 
					 
			 	$this->getAccessToken($this->getData['code']);
					 
				 
			 	else $this->GetCredentials();
					 
			 	 
				 		
		}
		return $this;
		 
	}

	public function getOrders()
	{	
        
		// $this->Process($Credentials);

	 	$response = $this->sendRequest($this->OrdersUrl,'get',array('$select'=>'OrderID,OrderNumber,AmountDC,Currency,DeliveryStatus,Description,OrderedBy,DeliverTo,DeliverToContactPerson,OrderDate,ApprovalStatus,ApprovalStatusDescription,ShippingMethod,ShippingMethodDescription,DeliveryDate,SalesOrderLines/ID,SalesOrderLines/Item, SalesOrderLines/ItemCode,SalesOrderLines/Quantity,SalesOrderLines/QuantityDelivered','$expand'=>'SalesOrderLines','$filter'=>'Status eq 12','$skiptoken' => $this->SkipToken  ));
	 	$data = $this->ConvertXmlToArray($response);

			// echo "<pre>"; print_r($data);die;
	 	if(isset($data->entry)){

	 		if(!is_array($data->entry)){
	 			$data->entry = array($data->entry);
	 		}

		 	foreach ($data->entry as $key => $value) {
		 			if(is_array($value->link['1']->inline->feed->entry)){

		 				foreach ($value->link['1']->inline->feed->entry as $k => $val) {
		 		 			$OrderLines[] = $val->content->properties;
		 				}
		 			}else $OrderLines[] = $value->link['1']->inline->feed->entry->content->properties;

		 		 $OrderInfo[] = $value->content->properties;
		 		 $CustomerResponse = $this->sendRequest("crm/Accounts(guid'".$value->content->properties->DeliverTo."')",'get',array('$select'=>'Name,AddressLine1,City,Country,Postcode,Email'  ));
	 			 $CustomerData = $this->ConvertXmlToArray($CustomerResponse);
	 			 // echo"<pre>"; print_r($CustomerData);die;

		 		 $CurrentTokenOrders[] = array('OrderLines'=>$OrderLines,'OrderInfo'=>$OrderInfo,'CustomerResponse'=>$CustomerData->content->properties); 
		 		 unset($OrderLines);
		 		 unset($OrderInfo);
		 	}
		 	$guid = end($CurrentTokenOrders)['OrderInfo'][0]->OrderID;
			$this->setSkipToken($guid);
		 	foreach ($CurrentTokenOrders as $key => $value) {
				array_push($this->TotalExactOrders, $value);
			}
			$this->getOrders();
	 	}

		return $this->TotalExactOrders;

	}

	public function setSkipToken($guid='')
	{
		$this->SkipToken = "guid'$guid'";
	}

	public function ConvertXmlToArray($response='')
	{	

		$response = str_replace(array('d:','m:'), array('',''), $response); 
		$p = simplexml_load_string($response);
		$p = json_encode($p);
		$p = json_decode($p);
		return $p;
	}

	public function getAccessToken($code)
	{	
		 $params = array(
		    'code' => $code,
		    'client_id' => $this->client_id,
		    'grant_type' => $this->grant_type,
		    'client_secret' => $this->client_secret,
		    'redirect_uri' => $this->redirect_uri,
		);
		$resp = $this->getResponse($this->tokenurl,$params);
		$this->SetCredentialsSession($resp);
		$this->GetCredentials();
	}

	 

	public function GetCredentials()
	{	
		$userdata = $this->_session;
		$this->accessToken = $userdata->access_token;
		$this->tokenType = $userdata->token_type;
		$this->expiresIn = time() + $userdata->expires_in;
		$this->refreshToken = $userdata->refresh_token;
	}

	protected function initAccessToken()
	{
		if (empty($this->accessToken) || $this->isExpired()) {
			// echo "yes";
			if (empty($this->refreshToken)) {
				throw new \ErrorException('Refresh token is not specified.');
			}
			
			$refreshed =  $this->refreshAccessToken($this->refreshToken);
			// echo "<pre>"; print_r($refreshed);die;
			if (!$refreshed) {
				return FALSE;
			}
			$this->setExpiresIn($refreshed['expires_in']);
			$this->refreshToken = $refreshed['refresh_token'];
			$this->accessToken = $refreshed['access_token'];
			// echo "Token Updated<pre>";	
		}  
		return $this->accessToken;
	}

	public function refreshAccessToken($refreshToken)
	{
		$params = array(
		    'refresh_token' => $refreshToken,
		    'grant_type' => self::GRANT_REFRESH_TOKEN,
		    'client_id' => $this->client_id,
		    'client_secret' => $this->client_secret
		);
		
		$url = sprintf($this->tokenurl, $this->countryCode);
			
		return $this->getResponse($url, $params);
	}
	 
	protected function setExpiresIn($expiresInTime)
	{
		$this->expiresIn = time() + $expiresInTime;
	}
	
	 
	protected function isExpired()
	{
		return $this->expiresIn > time();
	}

	protected function getRequestUrl($resourceUrl, $params = NULL)
	{
		$resourceUrlParts = parse_url($resourceUrl);
		$baseUrl = sprintf(self::URL_API, $this->countryCode);
		$apiUrl = $baseUrl . $this->division.'/'.$resourceUrlParts['path'];
		
		if (isset($resourceUrlParts['query'])) {
			$apiUrl .= '?' . $resourceUrlParts['query'];
		} else
		if ($params && is_array($params)) {
			$apiUrl .= '?' . http_build_query($params, '', '&');
		}
		
		return $apiUrl;
	}

	public function sendRequest($url, $method, $payload = NULL)
	{
		if ($payload && !is_array($payload)) {
			throw new \ErrorException('Payload is not valid.');
		}
		
		if (!$accessToken = $this->initAccessToken()) {
			throw new \ErrorException('Access token was not initialized');
		}
		
		$requestUrl = $this->getRequestUrl($url, array(
		    'access_token' => $accessToken
		));
		// Base cURL option
		if ($method == self::METHOD_GET) {
			if(isset($payload) && !empty($payload))
			$requestUrl = $requestUrl."&".http_build_query($payload);
			
		}
		// echo "<pre>";
		// print_r($requestUrl);die;
		$curlOpt = array();
		$curlOpt[CURLOPT_URL] = $requestUrl;
		$curlOpt[CURLOPT_RETURNTRANSFER] = TRUE;
		$curlOpt[CURLOPT_SSL_VERIFYPEER] = FALSE;
		$curlOpt[CURLOPT_HEADER] = false;
			
		if ($method == self::METHOD_POST ) {
			$curlOpt[CURLOPT_HTTPHEADER] = array(
			    'Content-Type:application/json', 
			    'access_token:' . $accessToken, 
			    'Content-length: ' . strlen(json_encode($payload))
			);
			$curlOpt[CURLOPT_POSTFIELDS] = json_encode($payload);
			$curlOpt[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
			// echo "<pre>";
			// print_r($curlOpt);die;
		}

		if ($method == self::METHOD_PUT) {
			$curlOpt[CURLOPT_HTTPHEADER] = array(
			    'Content-Type:application/json', 
			    // 'access_token:' . $accessToken, 
			    'Content-length: ' . strlen(json_encode($payload))
			);
			$curlOpt[CURLOPT_POSTFIELDS] = json_encode($payload);
			$curlOpt[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
			
			// echo "<pre>";
			// print_r($curlOpt);die;
		}
		
		$curlHandle = curl_init();
		curl_setopt_array($curlHandle, $curlOpt);
		
		return curl_exec($curlHandle);
	}

	public function InsertOrderLines($data='')
	{	
		// echo"<pre>";print_r($data);die;
		if(isset($data['dataOrderLines']) && !empty($data['dataOrderLines'])){

			foreach ($data['dataOrderLines'] as $key => $value) {

					$createdby = commonfunction::createdByDetails($data['user_id']);	
					$dataTobeInserted = array_merge($createdby,$value);

					// print_r($dataTobeInserted);die;
					try{

				   		$insid = $this->_db->insert(GoodsDeliveryOrderlines,$dataTobeInserted);
				   		if($insid)
				   			$resp = array('status'=>1,'message'=>'orderline successfully inserted');
				   		else $resp = array('status'=>0,'message'=>'Some internal error occurred while inserting orderline');	
				   		// print_r($insid);die;
				   		// return $insid;
					  }catch (Exception $e) {

					     // $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
					     $resp = array('status'=>0,'message'=>$e->getMessage());
					  }


					$OrderLines[] = array('response'=>$resp,'orderline'=>$value);  
					   


				}

				return $OrderLines;	
		} return false;





	}


	public function InsertGoodsDeliveryResponse($GoodsDelivery='',$OrderInfo='')
	{	
		global $objSession;
		$exact_shop_id = $objSession->exactShop_id;


		if(!isset($GoodsDelivery['error'])){
					$DeliveryResponse = $GoodsDelivery['response']->content->properties;
					$data  = array(
							'Deliveryline_EntryID' => $DeliveryResponse->EntryID,
							'TrackingNumber' => $DeliveryResponse->TrackingNumber,
							'shipment_id' => $OrderInfo['shipment_id'],
							'user_id' => $this->Useconfig['user_id'],
							'web_shop_id' => $exact_shop_id,
							'order_id' => $OrderInfo['shop_order_id'],
							'Warehouse_guid' => $DeliveryResponse->Warehouse,
							'WarehouseCode' => $DeliveryResponse->WarehouseCode,
							'Creator_guid' => $DeliveryResponse->Creator,
							'DeliveryAccount_guid' => $DeliveryResponse->DeliveryAccount,
							'DeliveryAddress_guid' =>$DeliveryResponse->DeliveryAddress,
							'DeliveryNumber' => $DeliveryResponse->DeliveryNumber,
							'DeliveryDate' => $DeliveryResponse->DeliveryDate,
							);
			 

					$createdby = commonfunction::createdByDetails($this->Useconfig['level_id']);	
					$dataTobeInserted = array_merge($createdby,$data);
					try{

				   		$insid = $this->_db->insert(CreatedGoodsDeliverylines,$dataTobeInserted);
				   		if($insid)
				   			$resp = array('status'=>1,'message'=>'Goods Delivery successfully inserted');
				   		else $resp = array('status'=>0,'message'=>'Some internal error occurred while inserting orderline');	
					  }catch (Exception $e) {

					     $resp = array('status'=>0,'message'=>$e->getMessage());
					  }

					   
		} else $resp = array('status'=>0,'message'=>'Goods Delivery is not created');
				
		return $resp;	
	}


	public function CreateGoodsDelivery($GoodDeliveryLine='')
	{
		// echo "SDfsdf"; print_r($OrderInfo->OrderNumber);die;
		try {

			$response = $this->sendRequest($this->GoodsDeliveryUrl,'post',$GoodDeliveryLine);

	 		$data = $this->ConvertXmlToArray($response);
			// if($OrderInfo->OrderNumber == 1232112){

			 // echo "<pre>"; print_r($response);die;
			// }
			if(!empty($data)){

				if(isset($data->message) && !empty($data->message))	
					$resp = array('status'=>0,'error'=>$data->message.' Or Order already delivered','message'=>'Unable to create goods delivery Or Order already delivered','response'=>$data);
			 // echo "<pre>"; print_r($data);die;
	 			else $resp = array('status'=>1,'message'=>'Goods Delivery successfully created','response'=>$data);
			} else $resp = array('status'=>0,'error'=>'request can not be completed you may have been logged in with another account in exact please login with correct credentials and try again','message'=>'request can not be completed','response'=>$data);
			
		} catch (Exception $e) {
			$resp = array('status'=>0,'error'=>$e->getMessage()." Or Order already delivered",'message'=>'Unable to create goods delivery','response'=>$data);
		}
		// echo "<pre>";print_r($resp);die;
		return $resp;
	}

	public function ApproveOrder($OrderInfo='')
	{
		try {
			// echo "Dfgds";die;
			 
			$payload = array('ApprovalStatus'=>1);	
			 // echo "<pre>"; print_r($payload);die;

			$response = $this->sendRequest("$this->OrdersUrl(guid'".$OrderInfo->OrderID."')",'put',$payload);
	 		$data = $this->ConvertXmlToArray($response);

	 		$resp = array('status'=>1,'message'=>'Approval Status changed successfully','response'=>array('previous_approvalstatus'=>$OrderInfo->ApprovalStatus,'new_approvalstatus'=>1,'resp'=>$data));
			
		} catch (Exception $e) {

			$resp = array('status'=>0,'error'=>$e->getMessage(),'message'=>'Approval Status changed successfully','response'=>array('previous_approvalstatus'=>$OrderInfo->ApprovalStatus,'new_approvalstatus'=>1,'resp'=>$data));
		}
		// echo "<pre>";print_r($resp);die;
		return $resp;
	}


	public function getPrintedExactLabelTracking()
	{	

		global $objSession;
		$exact_shop_id = $objSession->exactShop_id;
		$CurrentUser = $this->Useconfig['user_id'];

		// $selectnotin = $this->_db->select()->from(array('CGD'=>CreatedGoodsDeliverylines), array('CGD.shipment_id'))
		// 	->where("CGD.web_shop_id='$exact_shop_id'");
		// 	// echo $selectnotin->__toString();die;
		// $notinres = $this->getAdapter()->fetchAll($selectnotin);
		
		// echo "<pre>"; print_r($notinres);die;	

		$select = $this->_db->select()->from(array('SAS'=>SHOP_API_SHIPMENT),array('SAS.shipment_id','SAS.shop_id','SAS.shop_order_id' ))
			->joininner(array('PS'=>SHIPMENT),'PS.shipment_id = SAS.shipment_id',array('PS.user_id','PS.shipment_id'))
			->joininner(array('SB'=>SHIPMENT_BARCODE),'SB.shipment_id = SAS.shipment_id',array('SB.tracenr_barcode','SB.barcode_id','SB.barcode'))
			->group('SB.shipment_id')
			->where("SAS.shop_id='".$exact_shop_id."' && PS.user_id='$CurrentUser' && SAS.shipment_id NOT IN (select CGD.shipment_id from ".CreatedGoodsDeliverylines." as CGD where CGD.web_shop_id = '".$exact_shop_id."' ) " );
			// echo $this->Useconfig['user_id'];die;
		  try {
		  		
				$result = $this->getAdapter()->fetchAll($select);	
		  		// echo "<pre>"; print_r($result);die;
		  		if(!empty($result) && is_array($result)){

		  			foreach ($result as $key => $value) {
		  				$selectinner = $this->_db->select()->from(array('GDO'=>GoodsDeliveryOrderlines),array('GDO.*'))
						// // ->group('SB.shipment_id')
						->where("GDO.order_id='".$value['shop_order_id']."' &&  GDO.user_id='$CurrentUser'");
						// echo $selectinner->__toString();die;
						$res = $this->getAdapter()->fetchAll($selectinner);	
						$result[$key]['OrderLines']=$res;
		  			}

		  			$resp = array('status'=>1,'data'=>$result);
		  		}
		  		else $resp = array('status'=>0,'message'=>'No Order available with printed label');

		  	} catch (Exception $e) {
		  		$resp = array('status'=>0,'message'=>$e->getMessage());
		  	}	
		  	// die;
		return $resp;		
	}


}
