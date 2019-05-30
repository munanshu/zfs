<?php

    /**

     * Controll the Translation module

     * @Auth : SJM softech Pvt. Ltd.

     * @Created Date: 29-Nov-2012

     * @Description : Controll the functionality related to Translation

     **/

    class ExactonlineController extends Zend_Rest_Controller

    {

        public $ObjModel;

		public $response=NULL;
		public $ExactModel=NULL;
		public $ExactSession=NULL;

        public function init(){

            $this->ObjModel 	= new Application_Model_Shopapi();

			$this->_helper->layout->disableLayout();

            $this->ExactModel 	= new Application_Model_Exactnew();

			$this->Request = $this->_request->getParams();
			// print_r($this->Request);die;
			$this->ExactModel->getData  = $this->Request;

			$this->view->Request = $this->Request;

			$this->ExactSession = new Zend_Session_Namespace('ExactSession');

			// print_r($this->ExactSession->access_token);die;
			//$this->ObjModel->setDataToIndex($_POST);

			//$this->ObjModel->systemlog(json_encode($_POST));

        }

		

	     /**

         * get response for APi action

         * Function : shippingAction()

         **/

	public function indexAction()

    {

        $this->_helper->viewRenderer->setNoRender(true);

    }
	public function headAction(){
	     $this->_helper->viewRenderer->setNoRender(true);
	}


	public function performexactqueryAction()
	{	
		global $objSession;
		$usercredential = $this->ObjModel->Getcredentials($objSession->exactShop_id);
		// echo "<pre>";print_r($usercredential);die;

		$action = $objSession->exactAction;

		switch ($action) {
			case 'count':
				$data = $this->ExactModel->getexactorders($usercredential,$action);
					// $data = $this->exactimportcount($apicredential,$action);
				break;

			case 'import':
				$data = $this->ImportExactShipment($usercredential);
				$redirector = 'Shipment/importlist';
				break;
			case 'delivery':
				$data = $this->Deliverexactorders($usercredential);
				
				if(isset($data['status']) && !empty($data['status'])){

					if(isset($data['message']['failure']) && !empty($data['message']['failure'])){

						if(count($data['message']['failure'])>0)
						$objSession->errorMsg = implode('<br>', $data['message']['failure']);
					}
					if(isset($data['message']['success']) && !empty($data['message']['success'])){
						if(count($data['message']['success'])>0)
							$objSession->successMsg = implode('<br>', $data['message']['success']);
					}	
				}else $objSession->errorMsg = $data['message'];
				$redirector = 'Shopapi/shopimport';

				// echo "<pre>"; print_r($data);die;		 
				// return $data;
				break;		
			
			default:
				echo "No Action Performed";die;
				break;
		}
		unset($objSession->exactAction);
		Zend_Session::namespaceUnset('ExactSession');
		// if(isset($objSession->successMsg))
		// 	echo $objSession->successMsg;
		// if(isset($objSession->errorMsg))
		// 	echo $objSession->errorMsg;
		$this->_redirect($redirector);
		die;

	}

	public function updateexactordersAction()
	{
		global $objSession;

		if(isset($this->Request['shop_id']) && !empty($this->Request['shop_id'])){

			$objSession->exactShop_id = $this->Request['shop_id'];
			$objSession->exactAction = 'delivery';

			$data = $this->performexactqueryAction();
		}else $objSession->errorMsg = "Please select shop to be updated";
		 
		$this->_redirect('Shopapi/shopimport');
		# code...
	}

	public function ImportExactShipment($usercredential='',$action='')
	{	

		$resp = $this->ExactModel->getexactorders($usercredential);
		 

		 $orderdata = $resp['orderdata']; 
		echo "<pre>";print_r($resp);die;
		 $reference_sort = array(); 

		 foreach($orderdata as $key => $row) { 

			$reference_sort[$key]  = $row['rec_reference'];

	     }  

		 array_multisort($reference_sort, SORT_ASC, $orderdata); 
		 global $objSession;
		 // print_r($orderdata);die;
		 if(!empty($orderdata)){
			  $this->ObjModel->importShipment(0,$orderdata);
			  
			  foreach ($resp['response'] as $key => $value) {
	    # code...
	     		$insertedOrderLines = $this->ExactModel->InsertOrderLines($value['requiredData']);
			// $orders[$key]['GoodsDeliveryRequest'] = $insertedOrderLines;
	     	}
	     	
		  }else{
			  $objSession->errorMsg = "No Data has Imported!!";
		  }
		  // echo $objSession->successMsg;die;
		  // $data = $this->Deliverexactorders($usercredential,$resp['response']);
		  // return $data;
	}


	public function Deliverexactorders($usercredential='',$orders='')
	{	


		$orders = $this->ExactModel->getPrintedExactLabelTracking();

		// echo "<pre>";
		// print_r($orders);
		// die;

		// $orders = $this->ExactModel->getexactorders($usercredential)['response'];
		// 	 echo "<pre>"; print_r($orders);die;

		// $apicredential = array(
		// 	'api_key'=>'ccf25e43-8ffe-484f-b7af-07eb69353382',
		// 	// 'api_key'=>'a98a36ae-eb1e-40d6-8ae4-d954e7da1666',
		// 	'api_secret'=> 'jZ27hBs8wvC8',
		// 	// 'api_secret'=> 'zTqefYX0NiBH',
		// 	'redirect_uri'=> "http://localhost/parcel/exactonline/performexactquery",
		// 	'division'=> "38345",
		// 	// 'division'=> "38129",
		// 	);
		$apicredential = $this->ExactModel->getuserapicredentials($usercredential);

		if( isset($orders['status']) && !empty($orders['status']) ){

				foreach ($orders['data'] as $key => $order) {

			 // echo "<pre>"; print_r($orders);die;
					$TrackingNumber = $order['tracenr_barcode'];
					foreach ($order['OrderLines'] as $k => $val) {

						$GoodsDeliveryLinesData[] = array(
								'QuantityDelivered' => $val['Quantity'],
								'SalesOrderLineID' => $val['salesorder_line_guid'],
								'TrackingNumber' => $TrackingNumber,
							);
						$DeliveryDate = $val['ExactDeliveryDate'];
						$Description = $val['Description'];
					}

					$GoodsDeliveryData = array(
							'DeliveryDate'=>$DeliveryDate,
							'Description'=>$Description,
							'TrackingNumber'=>$TrackingNumber,
							'GoodsDeliveryLines'=>$GoodsDeliveryLinesData,
							);
					unset($GoodsDeliveryLinesData);

					print_r($GoodsDeliveryLinesData);die;
					// echo "<pre>";print_r($orders[$key]['requiredData']);die;
					// $insertedOrderLines = $this->ExactModel->InsertOrderLines($orders[$key]['requiredData']);
					// $orders[$key]['GoodsDeliveryRequest'] = $insertedOrderLines;
					 

					$CretedGoodsDeliveryResp = $this->ExactModel->Process($apicredential)->CreateGoodsDelivery($GoodsDeliveryData);

					if($CretedGoodsDeliveryResp['status']){
						$messages['success'][] = $order['shop_order_id']." Successfully delivered";
					}else $messages['failure'][] = $order['shop_order_id']." ".$CretedGoodsDeliveryResp['error'];


					$insertedGoodsDeliveryResponse = $this->ExactModel->InsertGoodsDeliveryResponse($CretedGoodsDeliveryResp,$order);

					$orders['data'][$key]['CretedGoodsDeliveryResp'] = array('FromExact'=>$CretedGoodsDeliveryResp,'InDpost'=>$insertedGoodsDeliveryResponse);
					 
				}

				$resp = array('status'=>1,'data'=>$orders,'message'=>$messages);

			}
			else $resp = array('status'=>0,'message'=>$orders['message']);

		// 	echo "<pre>";
		// print_r($orders);
		// die;
			return $resp;

	}

 



    public function getAction()

    {

        $this->_helper->viewRenderer->setNoRender(true);

    }

	 public function postAction()

    {

	}

	public function putAction()

    {

        $this->_helper->viewRenderer->setNoRender(true);

    }



    public function deleteAction()

    {

        $this->_helper->viewRenderer->setNoRender(true);

    }

	public function ResponseAction(){

	     print_r($this->response);

	   $this->_helper->viewRenderer->setNoRender(true);

	}

	public function importAction()

	{
       echo "not this";die;
	   	 global $objSession;
        
		$usercredential = $this->ObjModel->Getcredentials($objSession->exactShop_id);
		
	  // Configuration, change these:

		$clientId 		= $usercredential['api_key'];

		$clientSecret 	= $usercredential['api_secret'];	//'ABCDeFGHijKLm';

		$redirectUri 	= "http://www.dpost.be/Exactonline/import";	//"http://www.domain.com";

		$division		= $usercredential['division'];//"992304";

	

	try { 

		// Initialize ExactAPI

		$exactApi = new Application_Model_Exactapi('nl', $clientId, $clientSecret, $division);	

		

		$exactApi->getOAuthClient()->setRedirectUri($redirectUri);

		

		$authUrl = $exactApi->getOAuthClient()->getAuthenticationUrl();

		

		if (!isset($_GET['code'])){	

			// Redirect to Auth-endpoint

			$authUrl = $exactApi->getOAuthClient()->getAuthenticationUrl();

			header('Location: ' . $authUrl, TRUE, 302);

			die('Redirect');

			

		}else {	

			// Receive data from Token-endpoint
			$nextOrder= 0;
			$tokenResult = $exactApi->getOAuthClient()->getAccessToken($_GET['code']);

			

			$exactApi->setRefreshToken($tokenResult['refresh_token']);

			

			// List accounts

			//$response = $exactApi->sendRequest('crm/Accounts', 'get');

			

			$response = $exactApi->sendRequest("salesorder/SalesOrders",'get');

			$xml1 = str_replace(array('m:type="Edm.Double"','m:type="Edm.Int16"','m:type="Edm.DateTime"','type="application/xml"','m:type="Edm.Guid"','m:null="true"','m:type="Edm.Int32"','type="text"'), "", $response);

			$xml1 = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xml1);

			

			$xml1 = simplexml_load_string($xml1);

			$json1 = json_encode($xml1);

			$responseArray1 = json_decode($json1,true); 

			$nextOrderid = 0;

			$orderdata = array();

			$user_id = $this->ObjModel->Useconfig['user_id'];
			if($this->ObjModel->Useconfig['level_id'] == 10){
				$user_id  = $this->ObjModel->Useconfig['parent_id'];
			}
			for($i=0;$i<5;$i++){	

			   if($nextOrder==1){

					$response = $exactApi->sendRequest("salesorder/SalesOrders",'get',array('$skiptoken'=>"guid'".$data['orderguid']."'"));

					$nextOrder=0;

					$xml1 = str_replace(array('m:type="Edm.Double"','m:type="Edm.Int16"','m:type="Edm.DateTime"','type="application/xml"','m:type="Edm.Guid"','m:null="true"','m:type="Edm.Int32"','type="text"'), "", $response);

					$xml1 = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xml1);

					$xml1 = simplexml_load_string($xml1);

					$json1 = json_encode($xml1);

					$responseArray1 = json_decode($json1,true);

				}

				if(isset($responseArray1['entry']) && !empty($responseArray1['entry'])){

				   foreach($responseArray1['entry'] as $oders){

					   $data = array();

					   if(is_array($oders)){

					    $orderdetail = $oders['content']['mproperties'];

					   }else{

					      $orderdetail = $responseArray1['entry']['content']['mproperties'];

					   }

					   $data['user_id']  = $user_id;

					   $data['price']  = $orderdetail['dAmountDC'];

					   $data['approvestatus']  = $orderdetail['dApprovalStatus'];

					   $data['currency']  = $orderdetail['dCurrency'];

					   $data['status']  = $orderdetail['dDeliveryStatus'];

					   $data['orderguid']  = $orderdetail['dOrderID'];

					   $data['shop_order_id']  = $orderdetail['dOrderNumber'];

					   $data['rec_reference']  = $orderdetail['dDescription'];

					   $data['eo_order_by']  = $orderdetail['dOrderedBy'];

					   $data['shop_id']      =  $usercredential['shop_id'];

                       $data['import_order_status']   = 'open';

					   $data['weight'] = $usercredential['weight'];

					   $data[SERVICE_ID] 		= (!empty($usercredential['service_id']) && $usercredential['service_id']>0) ? $usercredential['service_id'] : 1;
					   $data[ADDSERVICE_ID] 		= (!empty($usercredential['addservice_id']) && $usercredential['addservice_id']>0) ? $usercredential['addservice_id'] : 0;

					   $data['shipment_type'] = 13;

					   $address_guid  = $orderdetail['dDeliverTo'];

					   $deliverto  = $orderdetail['dDeliverToContactPerson'];

					   $address = $exactApi->sendRequest1("crm/Accounts(guid'".$address_guid."')",'get');

					   

					   $address_xml = str_replace(array('m:type="Edm.Double"','m:type="Edm.Int16"','m:type="Edm.DateTime"','type="application/xml"','m:type="Edm.Guid"','m:null="true"','m:type="Edm.Int32"','type="text"'), "", $address);

						$address_xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $address_xml);

						$address_xml = simplexml_load_string($address_xml);

						$address_json = json_encode($address_xml);

						$addressArr = json_decode($address_json,true);

						if(isset($addressArr['content']) && !empty($addressArr['content'])){ 

								$addressdetail = $addressArr['content']['mproperties'];

								$data['rec_name']  = $addressdetail['dName'];

								$data['rec_street']  = $addressdetail['dAddressLine1'];

								$data['rec_city']  = $addressdetail['dCity'];
								$data['senderaddress_id'] 	= $this->ObjModel->getSenderID($usercredential['sender_code']);
								$country_details = $this->ObjModel->getCountryDetail($addressdetail['dCountry'],2);
								$data['country_id']  = $country_details['country_id'];

								$data['rec_zipcode']  = $addressdetail['dPostcode'];

								$data['rec_email']  = $addressdetail['dEmail'];

						}
						$orderdata[] = $data;

						if(!is_array($oders)){

					    	break;

					   }

				   }

				}

				if(isset($responseArray1['link'][1]{'@attributes'}['rel']) && $responseArray1['link'][1]{'@attributes'}['rel']=='next'){

					$nextOrder = 1;

				}else{

				    break;

				}

			}	

				

				  $reference_sort = array(); 

				 foreach($orderdata as $key => $row) { 

					$reference_sort[$key]  = $row['rec_reference'];

			     }  

				 array_multisort($reference_sort, SORT_ASC, $orderdata);  
				 //echo "<pre>";print_r($orderdata);die;
				if(!empty($orderdata)){
					  $this->ObjModel->importShipment(0,$orderdata);
				  }else{
					  $objSession->errorMsg = "No Data has Imported!!";
				  }
				$this->_redirect('Shipment/importlist');

			}

			

		}catch(Exception $e){	//print_r('heloo else');die;

				print_r($e->getMessage());die;

			}

	

	  echo 'Working';die;

	}

	

}

?>

