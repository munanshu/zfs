<?php
class Account_Bootstrap extends Zend_Application_Module_Bootstrap	
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
		   $this->appRoutes['acc_administrator']= new Zend_Controller_Router_Route('Account/myprofile',array('module'=>'Account','controller' => 'Account','action' => 'myprofile'));
		
		$this->appRoutes['acc_depotmanager']= new Zend_Controller_Router_Route('Account/depotmanager',array('module'=>'Account','controller' => 'Account','action' => 'depotmanager'));
		
		$this->appRoutes['acc_customer']= new Zend_Controller_Router_Route('Account/customer',array('module'=>'Account','controller' => 'Account','action' => 'customer'));
		
		$this->appRoutes['acc_customerOperator']= new Zend_Controller_Router_Route('Account/customeroperator',array('module'=>'Account','controller' => 'Account','action' => 'customeroperator'));
		
		$this->appRoutes['acc_operator']= new Zend_Controller_Router_Route('Account/operator',array('module'=>'Account','controller' => 'Account','action' => 'operator'));
		
		$this->appRoutes['acc_hubuser']= new Zend_Controller_Router_Route('Account/hubuser',array('module'=>'Account','controller' => 'Account','action' => 'hubuser'));
		
		$this->appRoutes['acc_huboperator']= new Zend_Controller_Router_Route('Account/huboperator',array('module'=>'Account','controller' => 'Account','action' => 'huboperator'));
		
		$this->appRoutes['acc_depotform']= new Zend_Controller_Router_Route('Account/depotform',array('module'=>'Account','controller' => 'Account','action' => 'depotform'));
		
		$this->appRoutes['acc_customerform']= new Zend_Controller_Router_Route('Account/customerform',array('module'=>'Account','controller' => 'Account','action' => 'customerform'));
		
		$this->appRoutes['acc_operatorform']= new Zend_Controller_Router_Route('Account/operatorform',array('module'=>'Account','controller' => 'Account','action' => 'operatorform'));
		
		$this->appRoutes['acc_customeroperatorform']= new Zend_Controller_Router_Route('Account/customeroperatorform',array('module'=>'Account','controller' => 'Account','action' => 'customeroperatorform'));
		
		$this->appRoutes['acc_hubuserform']= new Zend_Controller_Router_Route('Account/hubuserform',array('module'=>'Account','controller' => 'Account','action' => 'hubuserform'));
		
		$this->appRoutes['acc_huboperatorform']= new Zend_Controller_Router_Route('Account/huboperatorform',array('module'=>'Account','controller' => 'Account','action' => 'huboperatorform'));
		
		$this->appRoutes['acc_driversettings']= new Zend_Controller_Router_Route('Account/driversettings',array('module'=>'Account','controller' => 'Account','action' => 'driversettings'));
		
		$this->appRoutes['acc_driverform']= new Zend_Controller_Router_Route('Account/driverform',array('module'=>'Account','controller' => 'Account','action' => 'driverform'));
		
		$this->appRoutes['acc_changepassword']= new Zend_Controller_Router_Route('Account/changepassword',array('module'=>'Account','controller' => 'Account','action' => 'changepassword'));
		
		$this->appRoutes['acc_delete']= new Zend_Controller_Router_Route('Account/delete',array('module'=>'Account','controller' => 'Account','action' => 'delete'));
		
		$this->appRoutes['acc_driverconfig']= new Zend_Controller_Router_Route('Account/driverconfig',array('module'=>'Account','controller' => 'Account','action' => 'driverconfig'));
		
		$this->appRoutes['acc_settings']= new Zend_Controller_Router_Route('Account/settings',array('module'=>'Account','controller' => 'Account','action' => 'settings'));
		
		$this->appRoutes['acc_terms']= new Zend_Controller_Router_Route('Terms/termcondition',array('module'=>'Account','controller' => 'Terms','action' => 'termcondition'));
		
		$this->appRoutes['acc_notification']= new Zend_Controller_Router_Route('Terms/depotnotification',array('module'=>'Account','controller' => 'Terms','action' => 'depotnotification'));
		
		$this->appRoutes['acc_pickupschedular']= new Zend_Controller_Router_Route('Account/pickupschedular',array('module'=>'Account','controller' => 'Account','action' => 'pickupschedular'));
		
		$this->appRoutes['acc_senderaddress']= new Zend_Controller_Router_Route('Senderaddress/senderaddress',array('module'=>'Account','controller' => 'Senderaddress','action' => 'senderaddress'));
		
		$this->appRoutes['acc_senderaddressform']= new Zend_Controller_Router_Route('Senderaddress/senderaddressform',array('module'=>'Account','controller' => 'Senderaddress','action' => 'senderaddressform'));
		
		$this->appRoutes['acc_senderaddresscountry']= new Zend_Controller_Router_Route('Senderaddress/senderaddresscountry',array('module'=>'Account','controller' => 'Senderaddress','action' => 'senderaddresscountry'));
		
		$this->appRoutes['acc_defaultcountry']= new Zend_Controller_Router_Route('Senderaddress/defaultcountry',array('module'=>'Account','controller' => 'Senderaddress','action' => 'defaultcountry'));
		
		$this->appRoutes['acc_deletesenderaddress']= new Zend_Controller_Router_Route('Senderaddress/deleteaddress',array('module'=>'Account','controller' => 'Senderaddress','action' => 'deleteaddress'));
		
		$this->appRoutes['acc_countryaddress']= new Zend_Controller_Router_Route('Senderaddress/countryaddress',array('module'=>'Account','controller' => 'Senderaddress','action' => 'countryaddress'));
		
		$this->appRoutes['acc_index']= new Zend_Controller_Router_Route('Privilege',array('module'=>'Account','controller' => 'Privilege','action' => 'index'));
		
		$this->appRoutes['acc_userprivilege']= new Zend_Controller_Router_Route('Privilege/userprivilege',array('module'=>'Account','controller' => 'Privilege','action' => 'userprivilege'));
		
		$this->appRoutes['acc_viewprivilege']= new Zend_Controller_Router_Route('Privilege/view',array('module'=>'Account','controller' => 'Privilege','action' => 'view'));
		
		$this->appRoutes['acc_newsindex']= new Zend_Controller_Router_Route('Newsletter',array('module'=>'Account','controller' => 'Newsletter','action' => 'index'));
		
		$this->appRoutes['acc_newsletter']= new Zend_Controller_Router_Route('Newsletter/newsletter',array('module'=>'Account','controller' => 'Newsletter','action' => 'newsletter'));
		
		$this->appRoutes['acc_deletetemplate']= new Zend_Controller_Router_Route('Newsletter/deletetemplate',array('module'=>'Account','controller' => 'Newsletter','action' => 'deletetemplate'));
		
		$this->appRoutes['acc_customerrouting']= new Zend_Controller_Router_Route('Customerrouting/customerrouting',array('module'=>'Account','controller' => 'Customerrouting','action' => 'customerrouting'));
		
		$this->appRoutes['acc_addrouting']= new Zend_Controller_Router_Route('Customerrouting/addcustomerrouting',array('module'=>'Account','controller' => 'Customerrouting','action' => 'addcustomerrouting'));
		
		$this->appRoutes['acc_editrouting']= new Zend_Controller_Router_Route('Customerrouting/editrouting',array('module'=>'Account','controller' => 'Customerrouting','action' => 'editrouting'));
		
		$this->appRoutes['acc_deleterouting']= new Zend_Controller_Router_Route('Customerrouting/deleterouting',array('module'=>'Account','controller' => 'Customerrouting','action' => 'deleterouting'));
		$this->appRoutes['acc_isuseravailable']= new Zend_Controller_Router_Route('Account/isuseravailable',array('module'=>'Account','controller' => 'Account','action' => 'isuseravailable'));
		$this->appRoutes['acc_emailvalidation']= new Zend_Controller_Router_Route('Account/emailvalidation',array('module'=>'Account','controller' => 'Account','action' => 'emailvalidation'));
		$this->appRoutes['acc_emailvalidation']= new Zend_Controller_Router_Route('Account/emailvalidation',array('module'=>'Account','controller' => 'Account','action' => 'emailvalidation'));

		$this->appRoutes['acc_alldepotnetrouting']= new Zend_Controller_Router_Route_Regex('Depotnetwork/alldepotnetworkrouting/([a-zA-Z0-9_-]+)',array('module'=>'Account','controller' => 'Depotnetwork','action' => 'alldepotnetworkrouting'),array(1=>'token'),'Depotnetwork/alldepotnetworkrouting/%s');

		$this->appRoutes['acc_depotnetrouting']= new Zend_Controller_Router_Route_Regex('Depotnetwork/addeditdepotnetrouting/([a-zA-Z0-9_-]+)',array('module'=>'Account','controller' => 'Depotnetwork','action' => 'addeditdepotnetrouting'),array(1=>'token'),'Depotnetwork/addeditdepotnetrouting/%s');

		$this->appRoutes['acc_depotnetroutingaddnew']= new Zend_Controller_Router_Route_Regex('Depotnetwork/addeditdepotnetrouting/([a-zA-Z0-9_-]+)/(.*)',array('module'=>'Account','controller' => 'Depotnetwork','action' => 'addeditdepotnetrouting'),array(1=>'token',2=>'currentMode'),'Depotnetwork/addeditdepotnetrouting/%s/%s');

		$this->appRoutes['acc_depotnetroutingedit']= new Zend_Controller_Router_Route_Regex('Depotnetwork/addeditdepotnetrouting/([a-zA-Z0-9_-]+)/(.*)/([a-zA-Z0-9_-]+)',array('module'=>'Account','controller' => 'Depotnetwork','action' => 'addeditdepotnetrouting'),array(1=>'token',2=>'currentMode',3=>'editToken'),'Depotnetwork/addeditdepotnetrouting/%s/%s/%s');

		$this->appRoutes['acc_depotnetroutingdelete']= new Zend_Controller_Router_Route_Regex('Depotnetwork/deletedepotnetrouting/([a-zA-Z0-9_-]+)/(.*)/([a-zA-Z0-9_-]+)',array('module'=>'Account','controller' => 'Depotnetwork','action' => 'deletedepotnetrouting'),array(1=>'token',2=>'currentMode',3=>'editToken'),'Depotnetwork/deletedepotnetrouting/%s/%s/%s');
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








