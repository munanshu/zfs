<?php
class Invoice_Bootstrap extends Zend_Application_Module_Bootstrap	
{
   
 	function _initApplication(){ 
	 
	}

	public function _initLayout() {
   	 /*try{
	 $layout = $this->bootstrap('layout')->getResource('layout');
   	 $layout->setLayout('somelayout');
	 }catch(Exception $e){
	   echo $e->getMessage();die;
	 }*/
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
			$this->appRoutes['invoicelist']= new Zend_Controller_Router_Route('/Invoicemanager/invoice',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Invoicemanager',
                                            'action' => 'invoice')
			);
			
			$this->appRoutes['generate_invoice']= new Zend_Controller_Router_Route('/createinvoice',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Invoicemanager',
                                            'action' => 'createinvoice')
			);
			$this->appRoutes['add_extraAmount']= new Zend_Controller_Router_Route('Invoicemanager/addextrahead',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Invoicemanager',
                                            'action' => 'addextrahead')
			);
			$this->appRoutes['add_invoicelist']= new Zend_Controller_Router_Route('Invoicemanager/invoicelist',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Invoicemanager',
                                            'action' => 'invoicelist')
			);
			$this->appRoutes['add_editinvoice']= new Zend_Controller_Router_Route('Invoicemanager/editinvoice',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Invoicemanager',
                                            'action' => 'editinvoice')
			);
			$this->appRoutes['viewprice_invoice']= new Zend_Controller_Router_Route('Invoicemanager/viewprice',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Invoicemanager',
                                            'action' => 'viewprice')
			);
			$this->appRoutes['updateprice_invoice']= new Zend_Controller_Router_Route('Invoicemanager/updateprice',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Invoicemanager',
                                            'action' => 'updateprice')
			);
			$this->appRoutes['myinvoice_invoice']= new Zend_Controller_Router_Route('Invoicemanager/myinvoice',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Invoicemanager',
                                            'action' => 'myinvoice')
			);
			$this->appRoutes['codamountdetail_invoice']= new Zend_Controller_Router_Route('Invoicecod/codamountdetail',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Invoicecod',
                                            'action' => 'codamountdetail')
			);
			$this->appRoutes['updatecod_invoicecod']= new Zend_Controller_Router_Route('Invoicecod/updatecod',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Invoicecod',
                                            'action' => 'updatecod')
			);
			$this->appRoutes['allduesinvoice_invoicecod']= new Zend_Controller_Router_Route('Duesinvoice/allduesinvoice',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Duesinvoice',
                                            'action' => 'allduesinvoice')
			);
			$this->appRoutes['paymenthistory_invoicecod']= new Zend_Controller_Router_Route('Duesinvoice/paymenthistory',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Duesinvoice',
                                            'action' => 'paymenthistory')
			);
			$this->appRoutes['allduesinvoice_depot']= new Zend_Controller_Router_Route('Depotduesinvoice/allduesinvoice',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Depotduesinvoice',
                                            'action' => 'allduesinvoice')
			);
			$this->appRoutes['paymenthistory_depot']= new Zend_Controller_Router_Route('Depotduesinvoice/paymenthistory',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Depotduesinvoice',
                                            'action' => 'paymenthistory')
			);

			$this->appRoutes['syn_parcel_invoice']= new Zend_Controller_Router_Route('Invoicemanager/syncparcelinvoice',
                                     array('module'     => 'Invoice', 
									 		'controller' => 'Invoicemanager',
                                            'action' => 'syncparcelinvoice')
			);
				
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
    { print_r($activeModuleName);die;
        $moduleList = $this->_getBootstrap()->getResource('modules');
        if (isset($moduleList[$activeModuleName])) {
        }
 
        return null;
    }



}








