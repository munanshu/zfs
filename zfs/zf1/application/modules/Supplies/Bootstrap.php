<?php
class Supplies_Bootstrap extends Zend_Application_Module_Bootstrap	
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
		// make sure the layout is loaded
		// $layout = $this->bootstrap('layout')->getResource('layout');
   // $layout->setLayout('somelayout');
		//$layout = $this->getResource('main');
		
		// get the view of the layout
		//$layout = $this->getResource('main');		
		//$view = $layout->getView();echo "Hi";die;
		
		//load the navigation xml
		//$config = new Zend_Config_Xml(ROOT_PATH.'/private/navigation.xml','nav');
		
 	 
		// pass the navigation xml to the zend_navigation component
		//$nav = new Zend_Navigation($config);
		
		
		
		// pass the zend_navigation component to the view of the layout 
		//$view->navigation($nav);
		

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
			$this->appRoutes['productlist']= new Zend_Controller_Router_Route('/Supplies/productlist',
                                     array('module'     => 'Supplies', 
									 		'controller' => 'Supplies',
                                            'action' => 'productlist')
			);
			$this->appRoutes['addeditshopproduct']= new Zend_Controller_Router_Route('/Supplies/addeditshopproduct',
                                     array('module'     => 'Supplies', 
									 		'controller' => 'Supplies',
                                            'action' => 'addeditshopproduct')
			);
			$this->appRoutes['productshop']= new Zend_Controller_Router_Route('/Supplies/productshop',
                                     array('module'     => 'Supplies', 
									 		'controller' => 'Supplies',
                                            'action' => 'productshop')
			);
			$this->appRoutes['orderlist']= new Zend_Controller_Router_Route('/Supplies/orderlist',
                                     array('module'     => 'Supplies', 
									 		'controller' => 'Supplies',
                                            'action' => 'orderlist')
			);
			$this->appRoutes['editorder']= new Zend_Controller_Router_Route('/Supplies/editorder',
                                     array('module'     => 'Supplies', 
									 		'controller' => 'Supplies',
                                            'action' => 'editorder')
			);
			$this->appRoutes['getproductprice']= new Zend_Controller_Router_Route('/Supplies/getproductprice',
                                     array('module'     => 'Supplies', 
									 		'controller' => 'Supplies',
                                            'action' => 'getproductprice')
			);
			$this->appRoutes['checkorder']= new Zend_Controller_Router_Route('/Supplies/checkorder',
                                     array('module'     => 'Supplies', 
									 		'controller' => 'Supplies',
                                            'action' => 'checkorder')
			);
			$this->appRoutes['shoppingcart']= new Zend_Controller_Router_Route('/Supplies/shoppingcart',
                                     array('module'     => 'Supplies', 
									 		'controller' => 'Supplies',
                                            'action' => 'shoppingcart')
			);
			$this->appRoutes['addtocart']= new Zend_Controller_Router_Route('/Supplies/addtocart',
                                     array('module'     => 'Supplies', 
									 		'controller' => 'Supplies',
                                            'action' => 'addtocart')
			);
			$this->appRoutes['updatecart']= new Zend_Controller_Router_Route('/Supplies/updatecart',
                                     array('module'     => 'Supplies', 
									 		'controller' => 'Supplies',
                                            'action' => 'updatecart')
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
    { 	print_r($activeModuleName);die;
        $moduleList = $this->_getBootstrap()->getResource('modules');
        if (isset($moduleList[$activeModuleName])) {
        }
 
        return null;
    }



}








