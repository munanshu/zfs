<?php
class settings_Bootstrap extends Zend_Application_Module_Bootstrap	
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
	  $this->appRoutes['set_forwarders']= new Zend_Controller_Router_Route('Settings/forwarders',array('module'=>'settings','controller' => 'Settings','action' => 'forwarders'));
			$this->appRoutes['setting_languages']= new Zend_Controller_Router_Route('Settings/languages',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'languages')
			);
			$this->appRoutes['setting_services']= new Zend_Controller_Router_Route('Settings/services',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'services')
			);
			$this->appRoutes['settings_generalsetting']= new Zend_Controller_Router_Route('Settings/generalsetting',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'generalsetting')
			);
			$this->appRoutes['settings_printsetting']= new Zend_Controller_Router_Route('Settings/printsetting',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'printsetting')
			);
			$this->appRoutes['settings_weightscallers']= new Zend_Controller_Router_Route('Settings/weightscallers',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'weightscallers')
			);
			$this->appRoutes['settings_emailnotification']= new Zend_Controller_Router_Route('Settings/emailnotification',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'emailnotification')
			);
			$this->appRoutes['settings_packetshop']= new Zend_Controller_Router_Route('Settings/packetshop',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'packetshop')
			);
			$this->appRoutes['settings_blockip']= new Zend_Controller_Router_Route('Settings/blockiplist',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'blockiplist')
			);
			 $this->appRoutes['set_vehicle']= new Zend_Controller_Router_Route('Settings/vehiclesetting',array('module'=>'settings','controller' => 'Settings','action' => 'vehiclesetting'));
			 $this->appRoutes['set_addeditport']= new Zend_Controller_Router_Route('Settings/addeditport',array('module'=>'settings','controller' => 'Settings','action' => 'addeditport'));
			 $this->appRoutes['set_editCountry']= new Zend_Controller_Router_Route('Settings/editcountry',array('module'=>'settings','controller' => 'Settings','action' => 'editcountry'));
			 $this->appRoutes['set_addCountry']= new Zend_Controller_Router_Route('Settings/addcountry',array('module'=>'settings','controller' => 'Settings','action' => 'addcountry'));
			 $this->appRoutes['set_addeditvahicle']= new Zend_Controller_Router_Route('Settings/addeditvehiclesetting',array('module'=>'settings','controller' => 'Settings','action' => 'addeditvehiclesetting'));
			 $this->appRoutes['set_addpacketshop']= new Zend_Controller_Router_Route('Settings/addpacketshop',array('module'=>'settings','controller' => 'Settings','action' => 'addpacketshop'));
			 $this->appRoutes['set_editpacketshop']= new Zend_Controller_Router_Route('Settings/editpacketshop',array('module'=>'settings','controller' => 'Settings','action' => 'editpacketshop'));
			 $this->appRoutes['set_portlist']= new Zend_Controller_Router_Route('Settings/portlist',array('module'=>'settings','controller' => 'Settings','action' => 'portlist'));
			 $this->appRoutes['set_shopdays']= new Zend_Controller_Router_Route('Settings/shopdays',array('module'=>'settings','controller' => 'Settings','action' => 'shopdays'));
			 $this->appRoutes['set_forwardercountry']= new Zend_Controller_Router_Route('Settings/forwardercountry',array('module'=>'settings','controller' => 'Settings','action' => 'forwardercountry'));
			 $this->appRoutes['settings_countryservice']= new Zend_Controller_Router_Route('Settings/countryservice',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'countryservice')
			);
			$this->appRoutes['settings_closeredirect']= new Zend_Controller_Router_Route('Settings/closeredirect',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'closeredirect')
			);
			$this->appRoutes['settings_addservicecountry']= new Zend_Controller_Router_Route('Settings/addservicecountry',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'addservicecountry')
			);
			$this->appRoutes['settings_addeditservice']= new Zend_Controller_Router_Route('Settings/addeditservice',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'addeditservice')
			);
			$this->appRoutes['settings_additionalservice']= new Zend_Controller_Router_Route('Settings/additionalservice',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'additionalservice')
			);
			$this->appRoutes['settings_addeditlanguage']= new Zend_Controller_Router_Route('Settings/addeditlanguage',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'addeditlanguage')
			);
			$this->appRoutes['settings_codelist']= new Zend_Controller_Router_Route('Statuscode/codelist',
                                     array('module'     => 'settings', 
            							   'controller' => 'Statuscode',
                                           'action' => 'codelist')
  			 );
			 $this->appRoutes['settings_addeditstatuscode']= new Zend_Controller_Router_Route('Statuscode/addeditstatuscode',
                                     array('module'     => 'settings', 
            							   'controller' => 'Statuscode',
                                           'action' => 'addeditstatuscode')
  			 );
			 $this->appRoutes['settings_forwarderstatuslist']= new Zend_Controller_Router_Route('Statuscode/forwarderstatuslist',
									 array('module'     => 'settings', 
										   'controller' => 'Statuscode',
										   'action' => 'forwarderstatuslist')
  			 );
			 $this->appRoutes['settings_associateforwarder']= new Zend_Controller_Router_Route('Statuscode/associateforwarder',
									 array('module'     => 'settings', 
										   'controller' => 'Statuscode',
										   'action' => 'associateforwarder')
  			 );
			$this->appRoutes['changrerrorid']= new Zend_Controller_Router_Route('Statuscode/changrerrorid',
                                     array('module'     => 'settings', 
            							   'controller' => 'Statuscode',
                                           'action' => 'changrerrorid')
  			 );	
			$this->appRoutes['addeditforwarderstatus']= new Zend_Controller_Router_Route('Statuscode/addeditforwarderstatus',
                                     array('module'     => 'settings', 
            							   'controller' => 'Statuscode',
                                           'action' => 'addeditforwarderstatus')
  			 );	
			$this->appRoutes['smssetting']= new Zend_Controller_Router_Route('Statuscode/smssetting',
                                     array('module'     => 'settings', 
            							   'controller' => 'Statuscode',
                                           'action' => 'smssetting')
  			 );
			$this->appRoutes['editsmsdetail']= new Zend_Controller_Router_Route('Statuscode/editsmsdetail',
                                     array('module'     => 'settings', 
            							   'controller' => 'Statuscode',
                                           'action' => 'editsmsdetail')
  			 );
			$this->appRoutes['smsreport']= new Zend_Controller_Router_Route('Statuscode/smsreport',
                                     array('module'     => 'settings', 
            							   'controller' => 'Statuscode',
                                           'action' => 'smsreport')
  			 );		
			 $this->appRoutes['settings_transitdetail']= new Zend_Controller_Router_Route('Settings/transitdetail',
									 array('module'     => 'settings', 
										   'controller' => 'Settings',
										   'action' => 'transitdetail')
  			 );
			$this->appRoutes['settings_editforwarder']= new Zend_Controller_Router_Route('Settings/editforwarder',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'editforwarder')
			);
			
			$this->appRoutes['routing_list']= new Zend_Controller_Router_Route('Routing/routinglist',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'routinglist')
			);
			$this->appRoutes['routing_weightclass']= new Zend_Controller_Router_Route('Routing/weightclasses',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'weightclasses')
			);
			$this->appRoutes['routing_addweightclass']= new Zend_Controller_Router_Route('Routing/addweightclass',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'addweightclass')
			);
			$this->appRoutes['add_routing']= new Zend_Controller_Router_Route('Routing/addrouting',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'addrouting')
			);
			$this->appRoutes['edit_routing']= new Zend_Controller_Router_Route('Routing/editrouting',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'editrouting')
			);
			$this->appRoutes['customerprice_routing']= new Zend_Controller_Router_Route('Routing/customerprice',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'customerprice')
			);
			$this->appRoutes['specialprice_routing']= new Zend_Controller_Router_Route('Routing/specialprice',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'specialprice')
			);
			$this->appRoutes['delete_weightclass']= new Zend_Controller_Router_Route('Routing/deleteweightclass',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'deleteweightclass')
			);
			$this->appRoutes['delete_routing']= new Zend_Controller_Router_Route('Routing/deleteweightrouting',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'deleteweightrouting')
			);
			$this->appRoutes['updateprice_routing']= new Zend_Controller_Router_Route('Routing/updateprice',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'updateprice')
			);
			$this->appRoutes['updatepricespecial_routing']= new Zend_Controller_Router_Route('Routing/updatepricespecial',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'updatepricespecial')
			);
			$this->appRoutes['import_routing']= new Zend_Controller_Router_Route('Routing/import',
                                     array('module'     => 'settings', 
									 		'controller' => 'Routing',
                                            'action' => 'import')
			);
			$this->appRoutes['countrysetting']= new Zend_Controller_Router_Route('Settings/countrysetting',
                                     array('module'     => 'settings', 
									 		'controller' => 'Settings',
                                            'action' => 'countrysetting')
			);
			$this->appRoutes['checkinvoice']= new Zend_Controller_Router_Route('Forwarderinvoicecheckin/checkinvoice',
                                     array('module'     => 'settings', 
									 		'controller' => 'Forwarderinvoicecheckin',
                                            'action' => 'checkinvoice')
			);
			$this->appRoutes['changelanguage']= new Zend_Controller_Router_Route('Settings/changelanguage',
                                     array('module'     => 'settings', 
            								'controller' => 'Settings',
                                            'action' => 'changelanguage')
           );
		   
		   $this->appRoutes['settings_texttranslatelist']= new Zend_Controller_Router_Route('Translation/texttranslatelist',
                                     array('module'     => 'settings', 
            								'controller' => 'Translation',
                                            'action' => 'texttranslatelist')
		   );
		   $this->appRoutes['translationedit']= new Zend_Controller_Router_Route('Translation/translationedit',
											 array('module'     => 'settings', 
													'controller' => 'Translation',
													'action' => 'translationedit')
		   );
		   $this->appRoutes['addtext']= new Zend_Controller_Router_Route('Translation/addtext',
											 array('module'     => 'settings', 
													'controller' => 'Translation',
													'action' => 'addtext')
		   );

		   $this->appRoutes['allstatussmstexts']= new Zend_Controller_Router_Route('Statuscode/allstatussmstexts',
											 array('module'     => 'settings', 
													'controller' => 'Statuscode',
													'action' => 'allstatussmstexts')
		   );
		   $this->appRoutes['addstatussmstexts']= new Zend_Controller_Router_Route('Statuscode/addstatussmstexts',
											 array('module'     => 'settings', 
													'controller' => 'Statuscode',
													'action' => 'addstatussmstexts')
		   );
		   $this->appRoutes['getmastersmstexts']= new Zend_Controller_Router_Route('Statuscode/getmastersmstexts',
											 array('module'     => 'settings', 
													'controller' => 'Statuscode',
													'action' => 'getmastersmstexts')
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








