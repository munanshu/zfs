<?php
class Oldhistory_Bootstrap extends Zend_Application_Module_Bootstrap	
{
   
 	function _initApplication(){ 
	 
	}

	public function _initLayout() {
   	/* try{
	 $this->bootstrap('layout');
	 }catch(Exception $e){
	   echo $e->getMessage();die;
	 }
	 $this->bootstrap('layout');*/
	//$layout = $this->getResource('layout');
	}

	protected function _initNavigation() { 
		

	}
	
 
    /**
     * return the default bootstrap of the app
     * @return Zend_Application_Bootstrap_Bootstrap
     */
    protected function _getBootstrap()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $bootstrap =  $frontController->getParam('bootstrap');	//deb($bootstrap);
        return $bootstrap;
    }
	
	public function _initRouter()
	{
		$this->FrontController = Zend_Controller_Front::getInstance();
		$this->router = $this->FrontController->getRouter();  
		$this->appRoutes=array();
		   
 	}

	
	
	protected  function _initSiteRouters(){	
		   $this->appRoutes['oldhistory_invoice']= new Zend_Controller_Router_Route('Invoicehistory/getinvoicehistory',array('module'=>'Oldhistory','controller' => 'Invoicehistory','action' => 'getinvoicehistory'));

		   $this->appRoutes['oldhistory_invoiceyear']= new Zend_Controller_Router_Route_Regex('Invoicehistory/getinvoicehistory/(\d+)',array('module'=>'Oldhistory','controller' => 'Invoicehistory','action' => 'getinvoicehistory'),array(1=>'year'),'Invoicehistory/getinvoicehistory/%d');



		   $this->appRoutes['oldhistory_parceldetails']= new Zend_Controller_Router_Route_Regex('Oldhistory/getoldparceldetails',array('module'=>'Oldhistory','controller' => 'Oldhistory','action' => 'getoldparceldetails') );




		   $this->appRoutes['oldhistory_invoiceedit']= new Zend_Controller_Router_Route_Regex('Invoicehistory/editinvoice',array('module'=>'Oldhistory','controller' => 'Invoicehistory','action' => 'editinvoice'));
		   $this->appRoutes['oldhistory_invoiceedityear']= new Zend_Controller_Router_Route_Regex('Invoicehistory/editinvoice/(\d+)',array('module'=>'Oldhistory','controller' => 'Invoicehistory','action' => 'editinvoice'),array(1=>'year'),'Invoicehistory/editinvoice/%d');






		    $this->appRoutes['oldhistory_shipment']= new Zend_Controller_Router_Route('Shipmenthistory/getshipmenthistory',array('module'=>'Oldhistory','controller' => 'Shipmenthistory','action' => 'getshipmenthistory'));

		   $this->appRoutes['oldhistory_shipmentyear']= new Zend_Controller_Router_Route_Regex('Shipmenthistory/getshipmenthistory/(\d+)',array('module'=>'Oldhistory','controller' => 'Shipmenthistory','action' => 'getshipmenthistory'),array(1=>'year'),'Shipmenthistory/getshipmenthistory/%d');





		   $this->appRoutes['oldhistory_edi']= new Zend_Controller_Router_Route('Edihistory/getoldedihistory',array('module'=>'Oldhistory','controller' => 'Edihistory','action' => 'getoldedihistory'));

		   $this->appRoutes['oldhistory_ediyear']= new Zend_Controller_Router_Route_Regex('Edihistory/getoldedihistory/(\d+)',array('module'=>'Oldhistory','controller' => 'Edihistory','action' => 'getoldedihistory'),array(1=>'year'),'Edihistory/getoldedihistory/%d');

		   $this->appRoutes['oldhistory_edidown']= new Zend_Controller_Router_Route('Edihistory/downloadedifile',array('module'=>'Oldhistory','controller' => 'Edihistory','action' => 'downloadedifile'));

		   $this->appRoutes['oldhistory_parceltracking']= new Zend_Controller_Router_Route('Oldhistory/parceltracking',array('module'=>'Oldhistory','controller' => 'Oldhistory','action' => 'parceltracking'));

		  
	}

	 protected  function _initSetupRouting(){	
			
			foreach($this->appRoutes as $key=>$cRouter){
			
				$this->router->addRoute( $key,  $cRouter );
			}
			
	}
	
	
    /**
     * return the bootstrap object for the active module
     * @return Offshoot_Application_Module_Bootstrap
     */
	 
    public function _getActiveBootstrap($activeModuleName)
    { //print_r($activeModuleName);die;
        $moduleList = $this->_getBootstrap()->getResource('modules');
        if (isset($moduleList[$activeModuleName])) {
        }
 
        return null;
    }



}








