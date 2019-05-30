<?php
class Checkin_Bootstrap extends Zend_Application_Module_Bootstrap	
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
			$this->appRoutes['checkin']= new Zend_Controller_Router_Route('/Checkin/checkin',
                                     array('module'     => 'Checkin', 
									 		'controller' => 'Checkin',
                                            'action' => 'checkin')
			);
			$this->appRoutes['batch_checkin']= new Zend_Controller_Router_Route('/Checkin/batchcheckin',
                                     array('module'     => 'Checkin', 
									 		'controller' => 'Checkin',
                                            'action' => 'batchcheckin')
			);
			$this->appRoutes['forwarder_manifest']= new Zend_Controller_Router_Route('/Edimanager/forwardermanifest',
                                     array('module'     => 'Checkin', 
									 		'controller' => 'Edimanager',
                                            'action' => 'forwardermanifest')
			);
			$this->appRoutes['parcel_details']= new Zend_Controller_Router_Route('Checkin/parceldetail',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Checkin',
                                            'action' => 'parceldetail')
			);	
			$this->appRoutes['parcel_fmview']= new Zend_Controller_Router_Route('Edimanager/forwardermanifestview',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Edimanager',
                                            'action' => 'forwardermanifestview')
			);
			$this->appRoutes['parcel_edihistory']= new Zend_Controller_Router_Route('Edimanager/edihistory',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Edimanager',
                                            'action' => 'edihistory')
			);
			$this->appRoutes['parcel_ediupdown']= new Zend_Controller_Router_Route('Edimanager/ediupdown',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Edimanager',
                                            'action' => 'ediupdown')
			);
			$this->appRoutes['parcel_csvcheckin']= new Zend_Controller_Router_Route('Checkin/csvcheckin',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Checkin',
                                            'action' => 'csvcheckin')
			);
			$this->appRoutes['parcel_referencecheckin']= new Zend_Controller_Router_Route('Checkin/referencecheckin',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Checkin',
                                            'action' => 'referencecheckin')
			);
			$this->appRoutes['parcel_parceldooption']= new Zend_Controller_Router_Route('Checkin/parceldooption',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Checkin',
                                            'action' => 'parceldooption')
			);
			$this->appRoutes['parcel_mediatorforwarder']= new Zend_Controller_Router_Route('Edimanager/mediatorforwarder',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Edimanager',
                                            'action' => 'mediatorforwarder')
			);
			$this->appRoutes['parcel_mediatorsinglefor']= new Zend_Controller_Router_Route('Edimanager/singlemediatorfor',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Edimanager',
                                            'action' => 'singlemediatorfor')
			);
			
			
			$this->appRoutes['parcel_specialbpost']= new Zend_Controller_Router_Route('Edimanager/specialbpost',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Edimanager',
                                            'action' => 'specialbpost')
			);
			$this->appRoutes['parcel_specialups']= new Zend_Controller_Router_Route('Edimanager/specialups',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Edimanager',
                                            'action' => 'specialups')
			);
			$this->appRoutes['parcel_urgentletter']= new Zend_Controller_Router_Route('Edimanager/urgentletter',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Edimanager',
                                            'action' => 'urgentletter')
			);
			
			$this->appRoutes['parcel_specialdhl']= new Zend_Controller_Router_Route('Edimanager/specialdhl',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Edimanager',
                                            'action' => 'specialdhl')
			);

			$this->appRoutes['parcel_updatedelivery']= new Zend_Controller_Router_Route('Parcel/updatedelivery',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Parcel',
                                            'action' => 'updatedelivery')
			);

			$this->appRoutes['parcel_forwarderStatuses']= new Zend_Controller_Router_Route('Parcel/getforwarderstatuscode',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Parcel',
                                            'action' => 'getforwarderstatuscode')
			);

			$this->appRoutes['parcel_additionaldoc']= new Zend_Controller_Router_Route('Parcel/additionaldocument',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Parcel',
                                            'action' => 'additionaldocument')
			);

			$this->appRoutes['parcel_deleteadditionaldoc']= new Zend_Controller_Router_Route('Parcel/deleteadditionaldoc',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Parcel',
                                            'action' => 'deleteadditionaldoc')
			);

			$this->appRoutes['parcel_downadditionaldoc']= new Zend_Controller_Router_Route('Parcel/docdownload',
                                      array('module'     => 'Checkin', 
									 		'controller' => 'Parcel',
                                            'action' => 'docdownload')
			);
			
			// $this->appRoutes['parcel_deleteadditionaldoc']= new Zend_Controller_Router_Route_Regex('Parcel/deleteadditionaldoc/(a-zA-Z0-9-)',
			// 	array('module'     => 'Checkin', 
			// 						 		'controller' => 'Parcel',
   //                                          'action' => 'deleteadditionaldoc'),array(1=>'doc_id'),'Parcel/deleteadditionaldoc/%s');
			
			
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








