<?php
class Mailshipment_Bootstrap extends Zend_Application_Module_Bootstrap	
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
		 $this->appRoutes['mail_mailrouting']= new Zend_Controller_Router_Route('Mailshipment/mailrouting',
                                     array('module'     => 'Mailshipment', 
									 		'controller' => 'Mailshipment',
                                            'action' => 'mailrouting')
			);
		 $this->appRoutes['mail_mailpostlist']= new Zend_Controller_Router_Route('Mailshipment/mailpostlist',
                                     array('module'     => 'Mailshipment', 
									 		'controller' => 'Mailshipment',
                                            'action' => 'mailpostlist')
		 );
		  $this->appRoutes['mail_addmailshipment']= new Zend_Controller_Router_Route('Mailshipment/addmailshipment',
                                     array('module'     => 'Mailshipment', 
									 		'controller' => 'Mailshipment',
                                            'action' => 'addmailshipment')
		 );
		 $this->appRoutes['mail_mailbarcodecheckin']= new Zend_Controller_Router_Route('Mailshipment/mailbarcodecheckin',
								 array('module'     => 'Mailshipment', 
										'controller' => 'Mailshipment',
										'action' => 'mailbarcodecheckin')
		 );
		 $this->appRoutes['mail_mailhistory']= new Zend_Controller_Router_Route('Mailshipment/mailhistory',
								 array('module'     => 'Mailshipment', 
										'controller' => 'Mailshipment',
										'action' => 'mailhistory')
		 );
		 $this->appRoutes['mail_deletedmailshipment']= new Zend_Controller_Router_Route('Mailshipment/deletedmailshipment',
								 array('module'     => 'Mailshipment', 
										'controller' => 'Mailshipment',
										'action' => 'deletedmailshipment')
		 );	
		 $this->appRoutes['mail_addrouting']= new Zend_Controller_Router_Route('Mailshipment/addmailrouting',
								 array('module'     => 'Mailshipment', 
										'controller' => 'Mailshipment',
										'action' => 'addmailrouting')
		 );
		 $this->appRoutes['mail_routingupd']= new Zend_Controller_Router_Route('Mailshipment/routingupdate',
								 array('module'     => 'Mailshipment', 
										'controller' => 'Mailshipment',
										'action' => 'routingupdate')
		 );
		 $this->appRoutes['mail_mailprice']= new Zend_Controller_Router_Route('Mailshipment/mailprice',
								 array('module'     => 'Mailshipment', 
										'controller' => 'Mailshipment',
										'action' => 'mailprice')
		 );
		  $this->appRoutes['mail_edit']= new Zend_Controller_Router_Route('Mailshipment/editmailpost',
								 array('module'     => 'Mailshipment', 
										'controller' => 'Mailshipment',
										'action' => 'editmailpost')
		 );
		  $this->appRoutes['mail_printmanifest']= new Zend_Controller_Router_Route('Mailshipment/printmanifest',
								 array('module'     => 'Mailshipment', 
										'controller' => 'Mailshipment',
										'action' => 'printmanifest')
		 );
		 $this->appRoutes['mail_checkinmanifest']= new Zend_Controller_Router_Route('Mailshipment/checkinmanifest',
								 array('module'     => 'Mailshipment', 
										'controller' => 'Mailshipment',
										'action' => 'checkinmanifest')
		 );
		 $this->appRoutes['mail_scanmailedit']= new Zend_Controller_Router_Route('Mailshipment/scanmailedit',
								 array('module'     => 'Mailshipment', 
										'controller' => 'Mailshipment',
										'action' => 'scanmailedit')
		 );
		 $this->appRoutes['mail_mailcheckin']= new Zend_Controller_Router_Route('Mailshipment/mailcheckin',
								 array('module'     => 'Mailshipment', 
										'controller' => 'Mailshipment',
										'action' => 'mailcheckin')
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








