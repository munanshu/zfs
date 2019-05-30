<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 *
 * @category   Zend_Controller_Action
 * @version    1.12.20
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/*

 * ShipmentmanagerController of Logicparcel ERP System

 * @PHP version  : 5.6

 * @ZEND version : 1.12.20

 * @author       : SJM Softech Private Limited <contact@sjmsoftech.com>

 * @created on   : 22 Oct 2016

 * @link         : http://www.dpost.be
 
 * @description : Handles shipment pickuprequests, returnshipments and delivery tracking
 
 */
class ShipmentmanagerController extends Zend_Controller_Action
{
   /**
    * The corresponding model 
    *
    * @var class Pikupmanager
    */
    public $ModelObj = null;

   /**
    * The request parameters
    *
    * @var array
    */
    private $Request = array();
    
   /**
    * The array of user configuration details retrieved from Zend_Session_Namespace
    *
    * @var array 
    */
    public $userconfig = null;

    
   /**
    * Initialization of ShipmentmanagerController
    *
    * The function sets layout,sets properties 'Request', 'ModelObj' and 'userconfig' and checks invoice due 
    * date for certain users 
    *
    * @param 
    *
    * @return void()
    */
    public function init()
    {
        /* Initialize action controller here */
		try{
			$this->_helper->layout->setLayout('main');
			$this->Request  = $this->getRequest()->getParams();
			$this->ModelObj = new Application_Model_Pickupmanager();
			
			$this->userconfig = $this->ModelObj->Useconfig;
		
		if(in_array($this->userconfig['level_id'], array(4, 5, 6, 10))){
		
		   $check_data = $this->ModelObj->InvoiceDueDateCheck();
		   
		   if($check_data[0]<=0){
		   
		   }
		   elseif($check_data[0]>0){
		     
		   }
		
		}
			
			
	 } catch(Exception $e) {
	    echo $e->getMessage(); die;
	}
	 
    }


   /**
    * Handles pickuprequests
    *
    * @param void()
    *
    * @return 
    */
    public function pickrequestAction()
    {
        // action body
	$this->view->filterdata = $this->Request;
	
	$records = $this->ModelObj->pickuprequest($this->Request);
	$manualpickupdata = $this->ModelObj->Customermanualpickupdate($this->Request, $records);
	
	if(!empty($manualpickupdata)){
	       $records = array_merge($records, $manualpickupdata);
	}
	
		
	if(count($records)>0){
	   foreach($records as $key=> $row){
	       $pickupdate[$key] = $row['pickup_date'];
	       $pickuptime[$key] = isset($row['pickup_time'])?$row['pickup_time']:'00-00-00';
	       $createdate[$key] = $row['create_date'];
	   }
	   
	   array_multisort($pickupdate, SORT_ASC, $pickuptime, SORT_ASC, $createdate, SORT_DESC, $records);
	   
	}
	
	$paginator = Zend_Paginator::factory($records);
	$currentPage = isset($this->Request['page'])?$this->Request['page']: 1;
	$paginator->setCurrentPageNumber($currentPage);
	$itemsPerPage = isset($this->Request['count'])?$this->Request['count']: 5;
	$paginator->setItemCountPerPage($itemsPerPage);
	
	$pages = $paginator->getPages();
	
	$this->view->page = $pages;
	$this->view->records = $paginator;
	
	$this->view->customerList = $this->ModelObj->getCustomerList();
	$this->view->userconfig = $this->userconfig;
    }
    
    
    /**
     * Handles returnshipments 
     *
     * @param
     *
     * @return
     */
     public function returnshipmentAction(){
     
        $this->view->shipmentList = $this->ModelObj->returnShipmentList();
        $this->view->ModelObj = $this->ModelObj;
     }
     
     
     
    /**
     * Handles delivery tracking
     * 
     * @param
     *
     * @return void() 
     */
     public function deliverytrackerAction(){
      
        if($this->getRequest()->isPost()){
	  
	   $deliveryData = $this->ModelObj->getDeliverytime($this->getRequest()->getPost());
	   
	   $this->view->PostData = $this->getRequest()->getPost();
	   $this->view->deliveryData = $deliveryData;
	   $this->view->ModelObj = $this->ModelObj;
	}
	
	$this->view->countries = $this->ModelObj->getCountries();
	$this->view->forwarders = $this->ModelObj->getForwarders();
      
      }
      
      


}



