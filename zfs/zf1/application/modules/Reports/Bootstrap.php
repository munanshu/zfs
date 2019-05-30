<?php

class Reports_Bootstrap extends Zend_Application_Module_Bootstrap	

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

			$this->appRoutes['rep_graphicalreport']= new Zend_Controller_Router_Route('Reports/graphicalreport',

                                     array('module'     => 'Reports', 

									 		'controller' => 'Reports',

                                            'action' => 'graphicalreport')

			);

			$this->appRoutes['rep_differences']= new Zend_Controller_Router_Route('Reports/differences',

                                     array('module'     => 'Reports', 

									 		'controller' => 'Reports',

                                            'action' => 'differences')

			);

			$this->appRoutes['rep_forwarderreport']= new Zend_Controller_Router_Route('Reports/forwarderreport',

                                     array('module'     => 'Reports', 

									 		'controller' => 'Reports',

                                            'action' => 'forwarderreport')

			);

			$this->appRoutes['rep_wrongaddress']= new Zend_Controller_Router_Route('Reports/wrongaddress',

                                     array('module'     => 'Reports', 

									 		'controller' => 'Reports',

                                            'action' => 'wrongaddress')

			);
			$this->appRoutes['rep_testmail']= new Zend_Controller_Router_Route('Reports/testmail',

                                     array('module'     => 'Reports', 

									 		'controller' => 'Reports',

                                            'action' => 'testmail')

			);

			$this->appRoutes['rep_closeworkdaytest']= new Zend_Controller_Router_Route('Reports/glsitbulkcloseworkdaytest',

                                     array('module'     => 'Reports', 

									 		'controller' => 'Reports',

                                            'action' => 'glsitbulkcloseworkdaytest')

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

















