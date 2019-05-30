<?php
class Seafreight_Bootstrap extends Zend_Application_Module_Bootstrap	
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
			$this->appRoutes['sea_routing']= new Zend_Controller_Router_Route('Seafreight/routing',
                                     array('module'     => 'Seafreight', 
									 		'controller' => 'Seafreight',
                                            'action' => 'routing')
			);
			$this->appRoutes['sea_weightclass']= new Zend_Controller_Router_Route('Seafreight/weightclass',
                                     array('module'     => 'Seafreight', 
									 		'controller' => 'Seafreight',
                                            'action' => 'weightclass')
			);
			$this->appRoutes['sea_addrouting']= new Zend_Controller_Router_Route('Seafreight/addrouting',
                                     array('module'     => 'Seafreight', 
									 		'controller' => 'Seafreight',
                                            'action' => 'addrouting')
			);
			$this->appRoutes['sea_editrouting']= new Zend_Controller_Router_Route('Seafreight/editrouting',
                                     array('module'     => 'Seafreight', 
									 		'controller' => 'Seafreight',
                                            'action' => 'editrouting')
			);
			$this->appRoutes['sea_customerprice']= new Zend_Controller_Router_Route('Seafreight/customerprice',
                                     array('module'     => 'Seafreight', 
									 		'controller' => 'Seafreight',
                                            'action' => 'customerprice')
			);
			$this->appRoutes['sea_specialprice']= new Zend_Controller_Router_Route('Seafreight/specialprice',
                                     array('module'     => 'Seafreight', 
									 		'controller' => 'Seafreight',
                                            'action' => 'specialprice')
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








