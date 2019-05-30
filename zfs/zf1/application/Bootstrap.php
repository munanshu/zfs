<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initLoaderResources()
    {
		
        $this->getResourceLoader()->addResourceType('controller', 'controllers/', 'Controller');
    }
	
	protected function _initHelperPath() {
        $view = $this->bootstrap('view')->getResource('view');
        $view->setHelperPath(APPLICATION_PATH . '/views/helpers', 'Application_View_Helper');
    }
	
	protected function _initDoctype()
	{
		$this->bootstrap('view');
 		$view = $this->getResource('view');
  		$view->setEncoding('UTF-8');
		$view->doctype('HTML5');
 		$view->headMeta()->appendHttpEquiv('Content-Type',  'text/html;charset=utf-8');
	}
	protected function _initNavigation() { 
		$view = $this->bootstrap('view')->getResource('view');
		$config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/navigation.xml');
		$navigation = new Zend_Navigation($config);
		$view->navigation($navigation);

	}
	protected function _initDB() {
		
		$dbConfig = new Zend_Config_Ini(ROOT_PATH.'/application/configs/db.ini',APPLICATION_ENV);
		$dbConfig = $dbConfig->resources->db;
		
		$masterdbConfig = new Zend_Config_Ini(ROOT_PATH.'/application/configs/db.ini',APPLICATION_ENV);
		$masterdbConfig = $masterdbConfig->resources->masterdb;
	
       	$dbAdapter = Zend_Db::factory($dbConfig->adapter, array(
            'host'     => $dbConfig->params->hostname,
            'username' => $dbConfig->params->username,
            'password' => $dbConfig->params->password,
            'dbname'   => $dbConfig->params->dbname
         ));
		$masterdbAdapter = Zend_Db::factory($masterdbConfig->adapter, array(
            'host'     => $masterdbConfig->params->hostname,
            'username' => $masterdbConfig->params->username,
            'password' => $masterdbConfig->params->password,
            'dbname'   => $masterdbConfig->params->dbname
         )); 
		
		$dbAdapter->query("SET NAMES 'utf8'");

        Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
		//Zend_Db_Table_Abstract::setDefaultAdapter($masterdbAdapter);
			//print_r( $dbAdapter);print_r($masterdbAdapter);
        Zend_Registry::set('db', $dbAdapter);
		Zend_Registry::set('masterdb', $masterdbAdapter);
		 
 	
		Zend_Session::start();
		global $objSession;
		$objSession = new Zend_Session_Namespace('SystemSession');
    }
	public function _initPlugins(){ // Add Plugin path
		$front = Zend_Controller_Front::getInstance();
		$front->registerPlugin(new Application_Plugin_SetIncludes());
	}


	protected  function _initApplication(){   
 			$this->FrontController=Zend_Controller_Front::getInstance();
			$this->FrontController->setControllerDirectory(array(
				'default' => '../application/controllers',
				'Shipment'    => '../application/Shipment/controllers',
				'Invoice'    => '../application/Invoice/controllers',
				'settings'    => '../application/settings/controllers',
				'Shipmentmanager'    => '../application/Shipmentmanager/controllers',
				'Reports'    => '../application/Reports/controllers',
				'Hubcheckin'    => '../application/Hubcheckin/controllers',
				'Planner'    => '../application/Planner/controllers',
				'Helpdesk'    => '../application/Helpdesk/controllers',
				'Seafreight'    => '../application/Seafreight/controllers'
			));
			$registry = Zend_Registry::getInstance();
			$registry->set("flash_error",false);
	}
	
	
	public function _initRouter()
        {
            $this->FrontController = Zend_Controller_Front::getInstance();
            $this->router = $this->FrontController->getRouter();
            $this->appRoutes = array ();
        }
	
	
	/* Site Routers */
	protected function _initSiteRouters(){
    
	}
	
	

	protected function _initSetupRouting(){
		foreach ($this->appRoutes as $key => $cRouter)
		{
			$this->router->addRoute($key, $cRouter);
		}
		
/*			prd($this);*/
	}
	
	protected function _initTranslator()
	{
		global $translate;
		/*$enLangData = require_once(ROOT_PATH.'/private/languages/en.php');
		$deLangData = require_once(ROOT_PATH.'/private/languages/fr.php');
 		$translate = new Zend_Translate(
			array(
				'adapter' => 'array',
				'content' => $enLangData,
				'locale'  => 'en',
			)
		);
		/*$translate->addTranslation(
			array(
				'content' => $deLangData,
				'locale'  => 'fr',
				'clear'   => true
			)
		);*/
		//$translate->setLocale('en');
		
		//Zend_Registry::set('Zend_Translate', $translate);
		$logicSeesion = new Zend_Session_Namespace('logicSeesion');
		//$language_id = (isset($logicSeesion->userconfig['language_id']) && $logicSeesion->userconfig['language_id'] !='')? $logicSeesion->userconfig['language_id'] : 1;
		//$LangData  = commonfunction::getTranslations($language_id);
		$LangData = isset($logicSeesion->translationArray)?$logicSeesion->translationArray:commonfunction::getTranslationArray();
		// echo "<pre>"; print_r($LangData);die;
 		$translate = new Zend_Translate(
			array(
				'adapter' => 'array',
				'content' => $LangData,
				'locale'  => 'en',
			)
		);
		$translate->setLocale('en');
		Zend_Registry::set('Zend_Translate', $translate);
		 
	}
	


}

