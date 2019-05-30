<?php
class ApiController extends Zend_Rest_Controller
{
	public $ObjModel;
	public $response=NULL;

    public function init()
    {  
       $this->_helper->layout->disableLayout();
	   $this->ObjModel 	= new Application_Model_ApiShipping();
	   $this->ObjModel->setDataToIndex($this->_request->getParams());
	   
    }

    public function indexAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
    }
	public function getAction()
    {
    	 
        $this->_helper->viewRenderer->setNoRender(true);
    }
	public function headAction(){
	     $this->_helper->viewRenderer->setNoRender(true);
	}

	public function handlerequest()
	{
	    $server = new Zend_Rest_Server();
    	$server->setClass('CustomApiClass');
    	// $server->addFunction('test');
    	$server->handle();	
		echo "<pre>"; print_r($server);die;
		# code...
	}

	public function getbulktrackingAction()
	{
		$ValidateResult = $this->ObjModel->UsernamePasswordValidation();
		
			if(!empty($ValidateResult)){
		  		print $this->_handleStruct($ValidateResult);exit;
	    	}

	   $this->response = $this->ObjModel->GetBulkShipments();	
	   $this->getResponse ()->setHeader ( 'Content-Type', 'text/xml' ); 
	   print $this->_handleStruct($this->response);exit;
	   $this->_helper->viewRenderer->setNoRender(true);
	}


	public function postAction(){
	 $uservalidation = $this->ObjModel->UsernamePasswordValidation();
	 if(!empty($uservalidation)){
		  print $this->_handleStruct($uservalidation);exit;
	 }
	 switch($this->ObjModel->getData['ActionCode']){
		   case 'add':
				$this->response = $this->ObjModel->AddApiShipment();
				$this->ObjModel->activityLog(json_encode($this->response));
		   break;
		   case 'edit':
		        $this->response = $this->ObjModel->EditApiShipment();
		   break;
		   case 'delete':
		        $this->response = $this->ObjModel->DeleteShipment();
		   break;
		   case 'sendercodelist':
		       $this->response = $this->ObjModel->SenderCodeList();
		   break;
		   case 'parcelstatus':
		   	   $this->response = $this->ObjModel->ParcelStatus();
		   break;
		   case 'checkinshipment':
		       $this->response = $this->ObjModel->CheckInShipmentList();
		   break;
		   case 'newshipment':
		   break;
		   case 'getrouting':
		   break;
		   case 'getstatus':
		   		$this->response = $this->ObjModel->getParcelStatus();
		   break;
		   case 'tracking':
		   		$this->response = $this->ObjModel->ApitrackingInfo();
		   break;
		   case 'services':
		        $this->response = $this->ObjModel->getAPIServiceCode();
		   break;
		  default:
		      $this->response = array('Status'=>'Invalid Action');
		  break; 
	   }	
		$this->getResponse ()->setHeader ( 'Content-Type', 'text/xml' ); 
		print $this->_handleStruct($this->response);exit;
		$this->_helper->viewRenderer->setNoRender(true);
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
	     $this->_helper->viewRenderer->setNoRender(true);
	}
	public function picqerAction()
	{
		// These variables are standard variables used by PHP for Basic Auth
		//if (isset($_POST['username']) && isset($_POST['username'])) {
		// echo "<pre>";print_r($_SERVER);die;
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			// Get the username and password from Basic Auth
			$_POST['username'] = $_SERVER['PHP_AUTH_USER'];
			$_POST['password'] = $_SERVER['PHP_AUTH_PW'];
			
			
			// Check if username and password are correct (as given in Picqer JSON Push settings)
    		if (!empty($_POST['username']) && !empty($_POST['password'])) {
				// Retrieve the JSON push data 
				$postData = file_get_contents('php://input');
				
				/*$LogFileName = YODEL_LABEL_LINK.'/'.date('YmdHis').'_picqer.log';
				$LogHeader = fopen($LogFileName,'a+');
				fwrite($LogHeader, $postData);
				fclose($LogHeader);*/
				
				$rawData  = json_decode($postData);
				$orderDetail = $rawData->picklist;
				
				//URL Data
				$service = $this->_request->getParams();
				
				$_POST['weight'] 		= (isset($rawData->weight)) ? ($rawData->weight/1000) : 0.1;
				$_POST['quantity'] 		= 1;
				$_POST['shipto'] 		= $orderDetail->deliveryname;
				$_POST['contact'] 		= $orderDetail->deliverycontact;
				$_POST['street'] 		= $orderDetail->deliveryaddress;
				$_POST['streetno'] 		= '';
				$_POST['address1'] 		= '';
				$_POST['address2'] 		= $orderDetail->deliveryaddress2;
				$_POST['postalcode'] 	= $orderDetail->deliveryzipcode;
				$_POST['city'] 			= $orderDetail->deliverycity;
				$_POST['countrycode'] 	= $orderDetail->deliverycountry;
				$_POST['phone'] 		= $orderDetail->telephone;
				$_POST['email'] 		= $orderDetail->emailaddress;
				$_POST['servicecode'] 	= (isset($service['service']) && !empty($service['service'])) ? trim($service['service']) : 'A';
				$_POST['price'] 	    = '';
				$_POST['reference'] 	= (isset($orderDetail->reference) && !empty($orderDetail->reference)) ? $orderDetail->reference : $orderDetail->idorder;
				$_POST['goods_type'] 	= ''; //Goods Tyoe of the Parcel - Optional Field
				$_POST['goods_description'] = ''; //Goods Description of the Parcel - Optional Field
				$_POST['labeltype']		= 'PDF';
				$_POST['shipment_status']= 10;
				
				$this->ObjModel->setDataToIndex($_POST); //print_r($_POST);die;
				$uservalidation = $this->ObjModel->UsernamePasswordValidation();
				if(!empty($uservalidation)){
				  print $this->_handleStruct($uservalidation);exit;
				}
					
				//$Error = $this->ObjModel->ErrorCheck();
				
					$this->response = $this->ObjModel->AddApiShipment();
					$barcode = explode(':',$this->response['Success']['TrackingDetails']['ParcelNumber1']);
					$filenme = explode('pdf/',$this->response['Success']['LabelURL']);
					$trackurl= explode('URL:',$this->response['Success']['TrackingDetails']['TrackingURL1']);
				  
					$resp['identifier'] = $barcode[1];
					$resp['trackingurl'] = $trackurl[1];
					$resp['label']['filename'] = $filenme[1];
					$resp['label']['filetype'] = "application/pdf";
					$resp['label']['file'] =  base64_encode(file_get_contents(API_SAVE_LABEL.$this->ObjModel->RecordData['forwarder_detail']['forwarder_name'].'/pdf/'.$filenme[1]));
					$this->ObjModel->activityLog(json_encode($resp));
					// Send the response back to Picqer
				if($resp['identifier']!=''){	
					header("HTTP/1.1 200 OK");
					echo json_encode($resp);
					exit;
				}else{
				    header("HTTP/1.1 401 Empty Weight");
					//$this->response = $this->response;
					print_r(json_encode($this->response));
					exit;
				}
			}
			else{
				// No authorization data given
				header("HTTP/1.1 401 Empty Credential");
				exit;
			}
		}
		else{
			// No authorization data given
			header("HTTP/1.1 401 Unauthorized Credential");
			exit;
		}
	   
	   	$this->_helper->viewRenderer->setNoRender(true);
	}


	
	public function parcelsopfinderAction(){
	     $uservalidation = $this->ObjModel->UsernamePasswordValidation();
		if(!empty($uservalidation)){
		  print $this->_handleStruct($uservalidation);exit;
		}
		$this->response = $this->ObjModel->getParcelshopData();
		$this->getResponse ()->setHeader ( 'Content-Type', 'text/xml' ); 
		print $this->_handleStruct($this->response);exit;
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	
	public function shipmentsAction(){  //print_r($this->_request->getParams());die;
	    $datas = $this->_request->getParams();
	   // Data submitted
        $data = $datas['shipments'];
        $data = json_decode($data, TRUE);
        $sipmentdata = $data['data'];
        $received_signature = $data['sig'];
        $private_key = 'eiruoi8jzsozoieijzoijeosjosoj';//get_private_key_for_public_key($data['pubKey']);
        $computed_signature = base64_encode(hash_hmac('sha1', $sipmentdata, $private_key, TRUE));
        if($computed_signature == $received_signature) {
		   $sipmentdatas = json_decode($sipmentdata);
		   foreach($sipmentdatas as $sipment){
		      $this->ObjModel->setDataMultishipment($sipment);
			  $shipmentResponse = $this->ObjModel->AddApiShipment();
			  echo "<pre>"; print_r($shipmentResponse);die;
		   }
		   echo "<pre>"; print_r($sipmentdatas);die;
			echo json_encode(array('Status'=>'OK','message'=>'Signature Varified'));exit; 
        }
        else {
            echo json_encode(array('Status'=>'Error','message'=>'Invalid signature'));exit;
        }
	  
	   //$this->ObjModel->setDataToIndex($this->_request->getParams());
	}
	
	/**
	  * Handle an array or object result
	  *
	  * @param array|object $struct Result Value
	  * @return string XML Response
	  */
    protected function _handleStruct($struct) {
	  $dom = new DOMDocument ( '1.0', 'UTF-8' );
	  $root = $dom->createElement ( "xml" );
	  $method = $root;
	   
	 // $root->setAttribute ( 'generator', 'Yiyu Blog' );
	  //$root->setAttribute ( 'version', '1.0' );
	  $dom->appendChild ( $root );
	   
	  $this->_structValue ( $struct, $dom, $method );
	   
	  $struct = ( array ) $struct;
	   //$status = $dom->createElement ( 'status', 'success' );
	   //$method->appendChild ( $status );
	   //$dom->save('simple_eng.xml');
	   return $dom->saveXML ();
	 }
	 /**
	  * Recursively iterate through a struct
	  *
	  * Recursively iterates through an associative array or object's properties
	  * to build XML response.
	  *
	  * @param mixed $struct
	  * @param DOMDocument $dom
	  * @param DOMElement $parent
	  * @return void
	  */
   protected function _structValue($struct, DOMDocument $dom, DOMElement $parent) {
  $struct = ( array ) $struct;
   
  foreach ( $struct as $key => $value ) {
   if ($value === false) {
    $value = 0;
   } elseif ($value === true) {
    $value = 1;
   }
    
   if (ctype_digit ( ( string ) $key )) {
    $key = 'key_' . $key;
   }
    
   if (is_array ( $value ) || is_object ( $value )) {
    $element = $dom->createElement (str_replace(range(0,9),'',$key));
    $this->_structValue ( $value, $dom, $element );
   } else {
    $element = $dom->createElement (str_replace(range(0,9),'',$key));
    $element->appendChild ( $dom->createTextNode ( $value ) );
   }
    
   $parent->appendChild ( $element );
  }
 }


}

