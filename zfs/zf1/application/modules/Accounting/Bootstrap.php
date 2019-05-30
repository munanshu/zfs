<?php
class Accounting_Bootstrap extends Zend_Application_Module_Bootstrap	
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
		   $this->appRoutes['accntng_headmanager']= new Zend_Controller_Router_Route('Accountsetting/getaccountheadlist',array('module'=>'Accounting','controller' => 'Accountsetting','action' => 'getaccountheadlist'));



		   $this->appRoutes['accntng_classmanager']= new Zend_Controller_Router_Route('Accountsetting/getaccountclasslist',array('module'=>'Accounting','controller' => 'Accountsetting','action' => 'getaccountclasslist'));

		   $this->appRoutes['accntng_addeditaccntclass']= new Zend_Controller_Router_Route('Accountsetting/addeditaccountingclass',array('module'=>'Accounting','controller' => 'Accountsetting','action' => 'addeditaccountingclass'));




		   $this->appRoutes['accntng_groupmanager']= new Zend_Controller_Router_Route('Accountsetting/getaccountgrouplist',array('module'=>'Accounting','controller' => 'Accountsetting','action' => 'getaccountgrouplist'));

		   $this->appRoutes['accntng_addeditaccntgroup']= new Zend_Controller_Router_Route('Accountsetting/addeditaccountinggroup',array('module'=>'Accounting','controller' => 'Accountsetting','action' => 'addeditaccountinggroup'));




		   $this->appRoutes['accntng_bankmanager']= new Zend_Controller_Router_Route('Accountsetting/getaccountbanklist',array('module'=>'Accounting','controller' => 'Accountsetting','action' => 'getaccountbanklist'));





		   $this->appRoutes['accntng_functionmanager']= new Zend_Controller_Router_Route('Accountsetting/getaccountfunctionlist',array('module'=>'Accounting','controller' => 'Accountsetting','action' => 'getaccountfunctionlist'));

		   $this->appRoutes['accntng_addeditaccntfunction']= new Zend_Controller_Router_Route('Accountsetting/addeditaccountingfunction',array('module'=>'Accounting','controller' => 'Accountsetting','action' => 'addeditaccountingfunction'));




		   $this->appRoutes['accntng_addeditaccnthead']= new Zend_Controller_Router_Route('Accountsetting/addeditaccountinghead',array('module'=>'Accounting','controller' => 'Accountsetting','action' => 'addeditaccountinghead'));

		   $this->appRoutes['accntng_getaccountgroup']= new Zend_Controller_Router_Route('Accountsetting/getaccountgroupbyclass',array('module'=>'Accounting','controller' => 'Accountsetting','action' => 'getaccountgroupbyclass'));



		   
		   $this->appRoutes['accntng_bookkeepingmanager']= new Zend_Controller_Router_Route('Bookkeeping/getbookkeepinglist',array('module'=>'Accounting','controller' => 'Bookkeeping','action' => 'getbookkeepinglist'));

		   $this->appRoutes['accntng_bookformmanager']= new Zend_Controller_Router_Route('Bookkeeping/getbookform',array('module'=>'Accounting','controller' => 'Bookkeeping','action' => 'getbookform'));

		   $this->appRoutes['accntng_addeditinvmanager']= new Zend_Controller_Router_Route('Bookkeeping/addeditinvoice',array('module'=>'Accounting','controller' => 'Bookkeeping','action' => 'addeditinvoice'));



		
			$this->appRoutes['accntng_btwratemanager']= new Zend_Controller_Router_Route('Accounting/getbtwrates',array('module'=>'Accounting','controller' => 'Accounting','action' => 'getbtwrates'));

			$this->appRoutes['accntng_addeditbtwrates']= new Zend_Controller_Router_Route('Accounting/addeditbtwrates',array('module'=>'Accounting','controller' => 'Accounting','action' => 'addeditbtwrates'));


			$this->appRoutes['accntng_testarch']= new Zend_Controller_Router_Route('Accounting/testarchive',array('module'=>'Accounting','controller' => 'Accounting','action' => 'testarchive'));




			$this->appRoutes['accntng_suppliers']= new Zend_Controller_Router_Route('Accounting/getsupplierlist',array('module'=>'Accounting','controller' => 'Accounting','action' => 'getsupplierlist'));

			$this->appRoutes['accntng_addeditsupplier']= new Zend_Controller_Router_Route('Accounting/addeditsupplier',array('module'=>'Accounting','controller' => 'Accounting','action' => 'addeditsupplier'));



			
		
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








