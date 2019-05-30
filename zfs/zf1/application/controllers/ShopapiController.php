<?php
class ShopapiController extends Zend_Controller_Action
{
	public $ModelObj = null;
    private $Request = array();
	public $WebshopArr = array();
	public $ServiceArr = array();
	
	public function init()
    {
		$this->_helper->layout->setLayout('main');
		$this->Request = $this->_request->getParams();
		$this->ModelObj = new Application_Model_Shopapi();
		$this->ModelObj->getData  = $this->Request;
		$this->view->Request = $this->Request;
		$this->view->ModelObj = $this->ModelObj;
		
		$this->view->token = (isset($this->Request['token'])) ? $this->Request['token'] : '';
		
		$this->view->customerName = (isset($this->Request['token'])) ? $this->ModelObj->CompanyName(Zend_Encript_Encription::decode($this->Request['token'])) : '';
		
		$this->WebshopArr = $this->ModelObj->getShoptypelist();
		$this->view->WebshopList = commonfunction::scalarToAssociative($this->WebshopArr,array('shop_type_id','shop_name'));
		
		$this->ServiceArr = $this->ModelObj->getmainservicelist();
		$this->view->ServiceList = commonfunction::scalarToAssociative($this->ServiceArr,array('service_id','service_name'));
	 }

    public function indexAction()
    {
        // action body
    }
	
	
	public function shopimportAction(){
	     if($this->_request->isPost()){
		    $import = $this->ModelObj->ImportFromShop();
		    if($import){
			     $this->_redirect('Shipment/importlist');
			}
		 }
	     $this->view->shopList = $this->ModelObj->getShopList();
	     $this->view->ExactShopList = $this->ModelObj->getExactShopList(7);
	}
	
	
	public function ordercountAction(){
	    $this->_helper->layout->setLayout('main');
		$this->ModelObj->getOrderCount();
	}
	
	
	public function shopsettingAction(){
		$this->view->records = $this->ModelObj->getUserShopSettings();
		//echo"<pre>";print_r($this->Request);die;
	}
	
	
	public function addnewshopAction(){
		
	   $this->_helper->layout->setLayout('popup');
	  
	   if($this->getRequest()->isPost()){
	   		//echo"<pre>";print_r($this->Request);die;
			$insertedId = $this->ModelObj->addusershopsetting();
			if($insertedId>0){
				
				echo '<script type="text/javascript">parent.window.location.reload();
			 	parent.jQuery.fancybox.close();</script>';
			 	exit();
			} 
	   }
	}
	
	
	public function getadditionalserviceAction(){
		$ChildHtml ='';
		$addserviceDetail =$this->ModelObj->additionalserviceDetail();
		 
		if(!empty($addserviceDetail)){
				
			$ChildHtml .="<select name='add_service_id' id='addservice'><option value='0'>---Additional Service---</option>";		
			foreach($addserviceDetail as $key=>$value){
				$selected = ($this->Request['addservice']==$value['service_id']) ? 'selected="selected"' : '';
				$ChildHtml .= "<option $selected value=".$value['service_id'].">".$value['service_name']."</option>";	 					
			}	
			$ChildHtml .="</select>";	
		}
		else{
			$ChildHtml .="<select name='add_service_id' id='addservice'><option value='0'>---No Additional Service---</option></select>";
		}
		echo $ChildHtml;			               
		exit;
	}
	
	
	public function editshopsettingAction(){
		$this->_helper->layout->setLayout('popup');
		
		$Records = $this->ModelObj->getUserShopSettings();
		$this->view->customerName = $this->ModelObj->CompanyName($Records[0]['user_id']);
		
		$this->view->multishopUrls = $this->ModelObj->getUserShopSettings($Records[0]['shop_id']);
		
		$this->view->Nflag = (count($this->view->multishopUrls)>0) ? '' : 'checked="checked"';
		$this->view->Yflag = (count($this->view->multishopUrls)>0) ? 'checked="checked"' : '';
		
		$this->view->record = $Records[0];
		
		if($this->getRequest()->isPost()){	
			//echo"<pre>";print_r($this->Request);die;
		
			$this->ModelObj->getData['user_id'] = $Records[0]['user_id'];
			if($this->ModelObj->editshopsetting()){
				echo '<script type="text/javascript">parent.window.location.reload();
				parent.jQuery.fancybox.close();</script>';
				exit();
			}
		}
	}
	
	public function deleteusershopAction(){
		$this->ModelObj->deleteshop();
		$this->_redirect($this->_request->getControllerName().'/shopsetting?token='.$this->Request['token']);
				
	}
	
	public function bulkapiimportAction(){
	    $this->ModelObj->ShopBulkimport();
		 $this->_redirect('Shipment/importlist');
	}
}
