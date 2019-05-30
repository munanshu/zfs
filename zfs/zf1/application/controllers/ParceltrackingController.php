<?php
class ParceltrackingController extends Zend_Controller_Action
{

    public function init()
    {
		   try{	
				$this->_helper->layout->setLayout('popup');
				$this->Request = $this->_request->getParams();
				$this->ModelObj = new Application_Model_Parceltracking();
				$this->ModelObj->getData  = $this->Request;
				$this->view->Request = $this->Request;
				$this->view->ModelObj = $this->ModelObj;
				if(isset($this->Request['tockenno']) && $this->Request['tockenno']!=''){
				 
				 $barcode_id=trim(Zend_Encript_Encription::decode($this->Request['tockenno']));
					
					if ( is_numeric($barcode_id))
					{
							$barcode_id = $barcode_id;
					}
					else
					{
						$barcode_id_old=$this->ModelObj->oldParcelBarcodeId($this->Request['tockenno']);
						$barcode_id=$barcode_id_old['barcode_id'];
					}
					//die;
				  $this->ModelObj->getData[BARCODE_ID] = $barcode_id;

				 
				 // $this->ModelObj->getData[BARCODE_ID] = Zend_Encript_Encription::decode($this->Request['tockenno']);
				}
				if(isset($this->Request['tocken']) && $this->Request['tocken']!=''){
				  $this->ModelObj->getData[BARCODE_ID] = base64_decode(base64_decode($this->Request['tocken']));
				}
				if(isset($this->Request['trackingnumber']) && $this->Request['trackingnumber']!=''){
					$this->ModelObj->getData[BARCODE_ID] = $this->ModelObj->SpecialTrackingBarcodeid($this->Request['trackingnumber']);
				}
		 }catch(Exception $e){
			echo $e->getMessage();die;
		 }
    }

    public function indexAction()
    {
        // action body
    }

    public function trackingAction()
    {  
        $this->_helper->layout->setLayout('popup');
		$this->view->parcelinfo = $this->ModelObj->parcelinformation();
		
    }
	public function parcelerrorAction(){
	   $this->_helper->layout->setLayout('popup');
	   if($this->_request->isPost() && isset($this->Request['update'])){
	        $this->ModelObj->parcelErrorModification($this->view->parcelinfo);
	   }
	   $this->view->parcelinfo = $this->ModelObj->parcelinformation();   
	}


}



