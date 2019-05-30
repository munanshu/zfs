<?php
class Hubcheckin_Bootstrap extends Zend_Application_Module_Bootstrap	
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
			$this->appRoutes['hub_forwardermanifest']= new Zend_Controller_Router_Route('Hubcheckin/forwardermanifest',
                                     array('module'     => 'Hubcheckin', 
									 		'controller' => 'Hubcheckin',
                                            'action' => 'forwardermanifest')
			);
			$this->appRoutes['hub_batchchekin']= new Zend_Controller_Router_Route('Hubcheckin/batchchekin',
                                     array('module'     => 'Hubcheckin', 
									 		'controller' => 'Hubcheckin',
                                            'action' => 'batchchekin')
			);
			$this->appRoutes['hub_checkin']= new Zend_Controller_Router_Route('Hubcheckin/checkin',
                                     array('module'     => 'Hubcheckin', 
									 		'controller' => 'Hubcheckin',
                                            'action' => 'checkin')
			);
			$this->appRoutes['hub_singlescan']= new Zend_Controller_Router_Route('Hubcheckin/singlescan',
                                     array('module'     => 'Hubcheckin', 
									 		'controller' => 'Hubcheckin',
                                            'action' => 'singlescan')
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








