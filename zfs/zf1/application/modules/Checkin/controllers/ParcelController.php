<?php 
class Checkin_ParcelController extends Zend_Controller_Action
{

    public $Request = array();

    public $ModelObj = null;

    public $Setting_Model = null;

    public function init()
    {
       try{	
			$this->Request = $this->_request->getParams();
			$this->ModelObj = new Checkin_Model_Parcel();
			$this->Setting_Model = new settings_Model_Statuscode();
			$this->formObj = new Checkin_Form_Parcel();
			$this->ModelObj->getData  = $this->Request;
			$this->Setting_Model->getData  = $this->Request;
			$this->view->ModelObj = $this->ModelObj;
			$this->view->Request = $this->Request;
			$this->_helper->layout->setLayout('main');
	  }catch(Exception $e){
	    echo $e->getMessage();die;
	  }
    }

     
	public function updatedeliveryAction()
	{	
		ini_set('display_errors', 1);
		$this->_helper->layout->setLayout('popup');
		 // echo BASE_URL;die;

		$forwarders = $this->ModelObj->getForwarderList();
		 
		$this->formObj->Forwarders = commonfunction::scalarToAssociative($forwarders,array('forwarder_id','forwarder_name'));

	   	
	   	if( (isset($this->Request['forwarder_id']) && !empty($this->Request['forwarder_id'])) || (isset($this->Request['f_name']) && !empty($this->Request['f_name'])) ){

	   		if(!$this->_request->isPost() && isset($this->Request['f_name']) && !empty($this->Request['f_name'])){
	   			$this->Request['forwarder_id'] = Zend_Encript_Encription::decode($this->Request['f_name']);
	   			$this->Setting_Model->getData = $this->Request; 	
	   		}

	   		// if(isset($this->Request['forwarder_id']) && !empty($this->Request['forwarder_id'])){
	   		// 	$this->Request['forwarder_id'] = $this->Request['forwarder_id'];
	   		// 	$this->Setting_Model->getData = $this->Request; 	
	   		// }
	   		// echo "<pre>"; print_r($this->Request);die;
	   		// echo "sdfasd";die;
	   		$forwardersStatuses = $this->Setting_Model->getForwarderStatusCodeList();
	   		$resp = commonfunction::scalarToAssociative($forwardersStatuses['data'],array('error_id','error_desc'));
	   		$this->formObj->Statuses = $resp;
	   	}

	   	$form = $this->formObj->getParcelDeliveryForm();

		   	if( $this->_request->isPost() ){

		   		if($form->isValid($this->Request)){
		   			global $objSession;
		   			$result = $this->ModelObj->UpdateParcelDelivery();
		   			if($result['status'] == 0 )
		   				$objSession->errorMsg = $result['message'];
		   			elseif($result['status'] == 1)
		   				$objSession->successMsg = $result['message'];
		   			else $objSession->infoMsg = $result['message'];
		   			echo '<script type="text/javascript">

                                parent.window.top.location.href = "'.BASE_URL.'/Checkin/parceldetail?search_barcode='.Zend_Encript_Encription::decode($this->Request['search_barcode']).'"
                                // parent.jQuery.fancybox.close();
                                </script>';
		   			
		   			// $this->redirect('Checkin/parceldetail?search_barcode='.$this->Request['search_barcode']);
		   		}
		   		else $form->populate($this->Request);
		   	}else {

				if(isset($this->Request['search_barcode']))   	
					$this->formObj->barcode->setValue( Zend_Encript_Encription::decode($this->Request['search_barcode']));
				if(isset($this->Request['f_name']))   	
					$this->formObj->forwarder_id->setValue(Zend_Encript_Encription::decode($this->Request['f_name']));
				$this->_request->setParams(array('f_name'=>''));
				$this->Request = $this->_request->getParams();
	   		// echo "<pre>"; print_r($this->Request);die;
				// unset($this->Request['f_name']);
		   	}
	   	$this->view->DeliveryForm = $form;
	   	$this->view->title = 'Update Parcel Delivery';
	}

	public function getforwarderstatuscodeAction()
	{	
		if(isset($this->Request['forwarder_id']) && !empty($this->Request['forwarder_id'])){

			$forwardersStatuses = $this->Setting_Model->getForwarderStatusCodeList();
			
			if($forwardersStatuses['Total']>0){
				$resp = commonfunction::scalarToAssociative($forwardersStatuses['data'],array('error_id','error_desc'));
				
			}
			else $resp = array('status'=>0,'message'=>'No status found');

			$resp = Zend_Json::encode($resp, Zend_Json::TYPE_OBJECT);

		}else $resp = array('status'=>0,'message'=>'No Forwarder selected');
		print_r($resp);
		die; 
	}

	public function additionaldocumentAction()
	{	
		if($this->_request->isPost() && isset($this->Request['uploadon'])){

			$response = $this->ModelObj->UploadAdditionalDoc();
			global $objSession;
			if($response['status'] == 1)
				$objSession->successMsg = $response['message'];
			else $objSession->errorMsg = $response['message'];
			header("Content-type:application/json");
			$resp = Zend_Json::encode($response, Zend_Json::TYPE_OBJECT);
			echo $resp;
		}		
		die;
	}

	public function deleteadditionaldocAction()
	{	
		$res = $this->ModelObj->deleteAdditionalDoc();
		global $objSession;
		if($res['status'] == 1)
			$objSession->successMsg = $res['message'];
		else $objSession->errorMsg = $res['message'];

		return $this->redirect('Checkin/parceldetail?search_barcode='.$this->Request['search_barcode']);
	}

	public function docdownloadAction()
	{
		$this->ModelObj->AddDocDownload();
	}


}







