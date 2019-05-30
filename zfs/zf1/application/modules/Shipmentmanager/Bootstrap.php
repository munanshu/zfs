<?php
class Shipmentmanager_Bootstrap extends Zend_Application_Module_Bootstrap	
{
   
 	function _initApplication(){ 
	 
	}

	public function _initLayout() {

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
			$this->appRoutes['pickrequest']= new Zend_Controller_Router_Route('/Shipmentmanager/pickrequest',
                                     array('module'     => 'Shipmentmanager', 
									 		'controller' => 'Shipmentmanager',
                                            'action' => 'pickrequest')
			);
			$this->appRoutes['returnshipment']= new Zend_Controller_Router_Route('/Shipmentmanager/returnshipment',
                                     array('module'     => 'Shipmentmanager', 
									 		'controller' => 'Shipmentmanager',
                                            'action' => 'returnshipment')
			);
			$this->appRoutes['customerscan']= new Zend_Controller_Router_Route('/Shipmentmanager/customerscan',
                                     array('module'     => 'Shipmentmanager', 
									 		'controller' => 'Shipmentmanager',
                                            'action' => 'customerscan')
			);
			$this->appRoutes['returncheckin']= new Zend_Controller_Router_Route('/Shipmentmanager/returncheckin',
                                     array('module'     => 'Shipmentmanager', 
									 		'controller' => 'Shipmentmanager',
                                            'action' => 'returncheckin')
			);
			$this->appRoutes['deliverytracker']= new Zend_Controller_Router_Route('/Shipmentmanager/deliverytracker',
                                     array('module'     => 'Shipmentmanager', 
									 		'controller' => 'Shipmentmanager',
                                            'action' => 'deliverytracker')
			);
			$this->appRoutes['defaultmanualpickup']= new Zend_Controller_Router_Route('/Shipmentmanager/defaultmanualpickup',
                                     array('module'     => 'Shipmentmanager', 
									 		'controller' => 'Shipmentmanager',
                                            'action' => 'defaultmanualpickup')
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








