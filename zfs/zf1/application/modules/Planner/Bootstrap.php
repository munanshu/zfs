<?php
class Planner_Bootstrap extends Zend_Application_Module_Bootstrap	
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
			$this->appRoutes['planner_schedulepickup']= new Zend_Controller_Router_Route('Planner/schedulepickup',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'schedulepickup')
			);
			$this->appRoutes['planner_todaypicked']= new Zend_Controller_Router_Route('Planner/todaypicked',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'todaypicked')
			);
			$this->appRoutes['planner_failedpickupshipment']= new Zend_Controller_Router_Route('Planner/failedpickupshipment',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'failedpickupshipment')
			);
			$this->appRoutes['planner_assignedshipment']= new Zend_Controller_Router_Route('Planner/assignedshipment',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'assignedshipment')
			);	
			$this->appRoutes['planner_driverlist']= new Zend_Controller_Router_Route('Planner/driverlist',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'driverlist')
			);
			$this->appRoutes['planner_assignedshipment']= new Zend_Controller_Router_Route('Planner/assignedshipment',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'assignedshipment')
			);
			$this->appRoutes['planner_loginreport']= new Zend_Controller_Router_Route('Driverreport/loginreport',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Driverreport',
                                            'action' => 'loginreport')
			);
			$this->appRoutes['planner_manualpick']= new Zend_Controller_Router_Route('Planner/manualpickup',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'manualpickup')
			);
			$this->appRoutes['planner_singleassigned']= new Zend_Controller_Router_Route('Planner/singleassigned',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'singleassigned')
			);
			$this->appRoutes['planner_reassign']= new Zend_Controller_Router_Route('Planner/reassign',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'reassign')
			);
			$this->appRoutes['route']= new Zend_Controller_Router_Route('/Scheduleroute/route',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Scheduleroute',
                                            'action' => 'route')
			);
			$this->appRoutes['assignroute']= new Zend_Controller_Router_Route('/Scheduleroute/assignroute',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Scheduleroute',
                                            'action' => 'assignroute')
			);
			$this->appRoutes['routesettinglist']= new Zend_Controller_Router_Route('/Scheduleroute/routesettinglist',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Scheduleroute',
                                            'action' => 'routesettinglist')
			);
			$this->appRoutes['addeditroutesetting']= new Zend_Controller_Router_Route('/Scheduleroute/addeditroutesetting',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Scheduleroute',
                                            'action' => 'addeditroutesetting')
			);
			$this->appRoutes['viewschedule']= new Zend_Controller_Router_Route('/Scheduleroute/viewschedule',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Scheduleroute',
                                            'action' => 'viewschedule')
			);
			$this->appRoutes['getdetailsdata']= new Zend_Controller_Router_Route('/Scheduleroute/getdetailsdata',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Scheduleroute',
                                            'action' => 'getdetailsdata')
			);
			$this->appRoutes['checkconflicttime']= new Zend_Controller_Router_Route('/Scheduleroute/checkconflicttime',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Scheduleroute',
                                            'action' => 'checkconflicttime')
			);
						
						
			$this->appRoutes['scheduledelivery']= new Zend_Controller_Router_Route('/Delivery/scheduledelivery',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Delivery',
                                            'action' => 'scheduledelivery')
			);
			$this->appRoutes['setdatetime']= new Zend_Controller_Router_Route('/Delivery/setdatetime',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Delivery',
                                            'action' => 'setdatetime')
			);
			$this->appRoutes['assigneddelivery']= new Zend_Controller_Router_Route('/Delivery/assigneddelivery',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Delivery',
                                            'action' => 'assigneddelivery')
			);
			$this->appRoutes['planner_nonlistedcustomer']= new Zend_Controller_Router_Route('Planner/nonlistedcustomer',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'nonlistedcustomer')
			);
			$this->appRoutes['planner_drivermanifest']= new Zend_Controller_Router_Route('Planner/drivermanifest',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'drivermanifest')
			);
			$this->appRoutes['planner_deliveryscan']= new Zend_Controller_Router_Route('Delivery/deliveryscan',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Delivery',
                                            'action' => 'deliveryscan')
			);
			$this->appRoutes['planner_checkscan']= new Zend_Controller_Router_Route('Delivery/checkscan',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Delivery',
                                            'action' => 'checkscan')
			);
			
			$this->appRoutes['planner_vehiclereport']= new Zend_Controller_Router_Route('Driverreport/vehiclereport',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Driverreport',
                                            'action' => 'vehiclereport')
			);
			$this->appRoutes['planner_pickupreport']= new Zend_Controller_Router_Route('Driverreport/pickupreport',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Driverreport',
                                            'action' => 'pickupreport')
			);
			$this->appRoutes['planner_deliveredreport']= new Zend_Controller_Router_Route('Driverreport/deliveredreport',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Driverreport',
                                            'action' => 'deliveredreport')
			);
			$this->appRoutes['planner_leavereport']= new Zend_Controller_Router_Route('Driverreport/leavereport',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Driverreport',
                                            'action' => 'leavereport')
			);
			$this->appRoutes['planner_gpstracking']= new Zend_Controller_Router_Route('Drivertracking/gpstracking',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Drivertracking',
                                            'action' => 'gpstracking')
			);
			$this->appRoutes['planner_drivertrack']= new Zend_Controller_Router_Route('Drivertracking/drivertrack',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Drivertracking',
                                            'action' => 'drivertrack')
			);
			$this->appRoutes['planner_glsmailedparcel']= new Zend_Controller_Router_Route('Planner/glsmailedparcel',
                                     array('module'     => 'Planner', 
									 		'controller' => 'Planner',
                                            'action' => 'glsmailedparcel')
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








