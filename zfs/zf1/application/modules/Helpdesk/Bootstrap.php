<?php
class Helpdesk_Bootstrap extends Zend_Application_Module_Bootstrap	
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
				
				$this->appRoutes['helpdesk_ticketlist']= new Zend_Controller_Router_Route('Helpdesk/ticketlist',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'ticketlist')
			    );
				$this->appRoutes['helpdesk_summmarytickets']= new Zend_Controller_Router_Route('Helpdesk/summmarytickets',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'summmarytickets')
			    );
				$this->appRoutes['helpdesk_helpdesksetting']= new Zend_Controller_Router_Route('Helpdesk/helpdesksetting',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'helpdesksetting')
			    );
				$this->appRoutes['helpdesk_helpdeskstatus']= new Zend_Controller_Router_Route('Helpdesk/helpdeskstatus',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'helpdeskstatus')
			    );
				$this->appRoutes['helpdesk_addnewquestion']= new Zend_Controller_Router_Route('Helpdesk/addnewquestion',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'addnewquestion')
			    );
				$this->appRoutes['helpdesk_setstep']= new Zend_Controller_Router_Route('Helpdesk/setstep',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'setstep')
			    );
				$this->appRoutes['helpdesk_faqdetail']= new Zend_Controller_Router_Route('Helpdesk/faqdetail',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'faqdetail')
			    );
				$this->appRoutes['helpdesk_addfaq']= new Zend_Controller_Router_Route('Helpdesk/addfaq',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'addfaq')
			    );
				$this->appRoutes['helpdesk_updatefaq']= new Zend_Controller_Router_Route('Helpdesk/updatefaq',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'updatefaq')
			    );
				$this->appRoutes['helpdesk_addfaq']= new Zend_Controller_Router_Route('Helpdesk/addfaq',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'addfaq')
			    );
				$this->appRoutes['helpdesk_addsteps']= new Zend_Controller_Router_Route('Helpdesk/addsteps',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'addsteps')
			    );
				$this->appRoutes['helpdesk_updatequestion']= new Zend_Controller_Router_Route('Helpdesk/updatequestion',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'updatequestion')
			    );
				$this->appRoutes['helpdesk_editsteps']= new Zend_Controller_Router_Route('Helpdesk/editsteps',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'editsteps')
			    );
				$this->appRoutes['helpdesk_edithelpdeskstatus']= new Zend_Controller_Router_Route('Helpdesk/edithelpdeskstatus',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'edithelpdeskstatus')
			    );
				$this->appRoutes['helpdesk_emailtemplate']= new Zend_Controller_Router_Route('Helpdesk/emailtemplate',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'emailtemplate')
			    );
				$this->appRoutes['helpdesk_addhelpdeskstatus']= new Zend_Controller_Router_Route('Helpdesk/addhelpdeskstatus',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'addhelpdeskstatus')
			    );
				$this->appRoutes['helpdesk_changeticketstatus']= new Zend_Controller_Router_Route('Helpdesk/changeticketstatus',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'changeticketstatus')
			    );
				$this->appRoutes['helpdesk_viewmessage']= new Zend_Controller_Router_Route('Helpdesk/viewmessage',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'viewmessage')
			    );
				$this->appRoutes['helpdesk_replyform']= new Zend_Controller_Router_Route('Helpdesk/replyform',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'replyform')
			    );
				$this->appRoutes['helpdesk_forwardmessage']= new Zend_Controller_Router_Route('Helpdesk/forwardmessage',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'forwardmessage')
			    );
				$this->appRoutes['helpdesk_viewforwarddetail']= new Zend_Controller_Router_Route('Helpdesk/viewforwarddetail',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'viewforwarddetail')
			    );
				$this->appRoutes['claim_claim']= new Zend_Controller_Router_Route('Claim/claimlist',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'claimlist')
			    );
				$this->appRoutes['claim_viewclaims']= new Zend_Controller_Router_Route('Claim/viewclaims',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'viewclaims')
			    );
				$this->appRoutes['claim_replyclaim']= new Zend_Controller_Router_Route('Claim/replyclaim',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'replyclaim')
			    );
				$this->appRoutes['claim_claimsetting']= new Zend_Controller_Router_Route('Claim/claimsetting',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'claimsetting')
			    );
				$this->appRoutes['claim_claimstatus']= new Zend_Controller_Router_Route('Claim/claimstatus',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'claimstatus')
			    );
				$this->appRoutes['claim_addclaimstatus']= new Zend_Controller_Router_Route('Claim/addclaimstatus',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'addclaimstatus')
			    );
				$this->appRoutes['claim_addnewclaimquestion']= new Zend_Controller_Router_Route('Claim/addnewclaimquestion',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'addnewclaimquestion')
			    );
				$this->appRoutes['claimlist_editclaimstatus']= new Zend_Controller_Router_Route('Claim/editclaimstatus',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'editclaimstatus')
			    );
				$this->appRoutes['claim_updateclaimquestion']= new Zend_Controller_Router_Route('Claim/updateclaimquestion',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'updateclaimquestion')
			    );
				$this->appRoutes['claim_emailtemplate']= new Zend_Controller_Router_Route('Claim/emailtemplate',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'emailtemplate')
			    );
				$this->appRoutes['claim_getoptionclaimquestionbyid']= new Zend_Controller_Router_Route('Claim/getoptionclaimquestionbyid',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'getoptionclaimquestionbyid')
			    );
				$this->appRoutes['claim_claimquebyoptionid']= new Zend_Controller_Router_Route('Claim/claimquebyoptionid',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'claimquebyoptionid')
			    );
				$this->appRoutes['claim_mailonupdateclaimstatus']= new Zend_Controller_Router_Route('Claim/mailonupdateclaimstatus',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'mailonupdateclaimstatus')
			    );
				$this->appRoutes['claim_changestatus']= new Zend_Controller_Router_Route('Claim/changestatus',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'changestatus')
			    );
				$this->appRoutes['claim_addclaim']= new Zend_Controller_Router_Route('Claim/addclaim',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'addclaim')
			    );
				$this->appRoutes['addticket_claimquebyoptionid']= new Zend_Controller_Router_Route('Helpdesk/addticket',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'addticket')
			    );
				$this->appRoutes['addticket_checkticket']= new Zend_Controller_Router_Route('Helpdesk/checkticket',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Helpdesk',
                                            'action' => 'checkticket')
			    );
				
				 $this->appRoutes['claimnotification']= new Zend_Controller_Router_Route('Claim/claimnotification',
                                     array('module'     => 'Helpdesk', 
									 		'controller' => 'Claim',
                                            'action' => 'claimnotification')
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








