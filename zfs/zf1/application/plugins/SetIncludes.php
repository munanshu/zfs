<?php
class Application_Plugin_SetIncludes extends Zend_Controller_Plugin_Abstract
{	
	
	protected $_defaultRole = 'all';
	protected $model = '';
	private $acl = '';
	public $roleArr =  array ("0" => "all");
	public $loggedRole = "";
	
	public $restricted = array("user"=>array("login"));
	public $notRedirect = array('Adminlogin','Api','Shippingcenter');
	private $_site_assets  , $_view , $_logged_user = false , $view;
	
  
 	/* 	Set Document Type Layout  */
	protected function _initDoctype() {
	  
	  $this->bootstrap('view');
	  
	  $view = $this->getResource('view');
	  
	  $view->doctype('XHTML_STRICT');
	  
	  $view->setEncoding('UTF-8');
	}
	
	
  
  	/* 	Pre Dispatch Setting   */
    public function preDispatch(Zend_Controller_Request_Abstract $request){ 
 		//$this->_site_assets = $_site_assets_layout ;
		//$this->_allowed_resources = $_allowed_resources ;
		  
		$layout = Zend_Layout::getMvcInstance();		 
		$this->view = $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
		$this->_set_identity($request);
 	}
	
	
	
	/* 
	 *	Check User / Admin Identity and Assign user identity to respective views
	 
	 */
	private function _set_identity($request){
	       	global $objSession;
		    $logged_identity = Zend_Auth::getInstance()->getInstance();
			$zendcustom = new Zend_Custom();
			if($request->getControllerName()=="public" || $request->getControllerName()=="Parceltracking"){
			   return;
			}
			$zendcustom->activityLog(json_encode($request->getParams()));
			if($logged_identity->hasIdentity()){ 
				 Zend_Layout::getMvcInstance()->assign('Menus', $zendcustom->getMenu());
				 $logicSeesion = new Zend_Session_Namespace('logicSeesion'); 
				 Zend_Layout::getMvcInstance()->assign('User', $logicSeesion->userconfig);
				 Zend_Layout::getMvcInstance()->assign('Headname', $zendcustom->getModuleName($request->getControllerName(),$request->getActionName()));
				 $label_position = (isset($logicSeesion->userconfig['label_position']) && $logicSeesion->userconfig['label_position']!='')?$logicSeesion->userconfig['label_position']:'a6';
				 $this->_setLabelObj($label_position);
				 if($logicSeesion->userconfig['level_id']!=1 && $logicSeesion->userconfig['level_id']!=11){
				      $this->_checkDuesInvoice($request);
				 }
			}else{ 
			   if(!in_array($request->getControllerName(),$this->notRedirect)){  
				$objSession->Noredirect = true;
				$this->_response->setRedirect(BASE_URL.'?url='.urlencode($request->getRequestUri()));
			  }
			  Zend_Layout::getMvcInstance()->assign('Headname','Login');
			  $this->_setLabelObj();
			}
			$this->_setEmailObj();
		
	}
    public function _setLabelObj($format='a6',$forwarder_id=0,$print=false){
	    global $labelObj;
		if($format==''){
		    $format='a6';
		}
		if($format == 'a4' || $format == 'a1') {
			$labelObj = new Zend_Labelclass_PDFLabel('P','mm',$format);
		}
		elseif($format == 'a6' && $forwarder_id==1 && $print==false) {
			$labelObj = new Zend_Labelclass_PDFLabel('P','mm','label');
		}
		elseif($format == 'a6') {
			$labelObj = new Zend_Labelclass_PDFLabel('P','mm','label_postat');
		}
	}
	public function _setEmailObj(){
	   global $EmailObj;
	   $EmailObj = new Zend_Custom_MailManager();
	}
	
	
 	
	/* 
	 *	Load CSS and Javascripts Front/Admin Module Specific	
	 */
 	private function _getAssets($request){
 		foreach($this->_site_assets  as $key=>$values){
 			if(isset($values[$request->getModuleName()][$this->_logged_user ?"user":"guest"]) and count($values[$request->getModuleName()][$this->_logged_user?"user":"guest"])){
 				foreach($values[$request->getModuleName()][$this->_logged_user?"user":"guest"] as $inner_key=>$inner_value){
 					if(is_array($inner_value)){/* Module specific Assets  */
						if(isset($inner_value[$request->getControllerName()])){
							if(isset($inner_value[$request->getControllerName()][$request->getActionName()])){
								foreach($inner_value[$request->getControllerName()][$request->getActionName()] as $moduleKey=>$moduleValue){
									if($key=='css'){	
										$this->view->headLink()->appendStylesheet($this->_assets_path[$key][$request->getModuleName()].$moduleValue);
									}else{
										$this->view->headScript()->appendFile($this->_assets_path[$key][$request->getModuleName()].$moduleValue);
									}
								}
							}
 						}
  					}else{
						if($key=='css'){
							$this->view->headLink()->appendStylesheet($this->_assets_path[$key][$request->getModuleName()].$inner_value);
						}else{
							$this->view->headScript()->appendFile($this->_assets_path[$key][$request->getModuleName()].$inner_value);
						}
					}
				}
			}
		}
		$this->view->headLink()->headLink(array('rel' => 'shortcut icon','href' => HTTP_IMG_PATH.'/site_img/favicon.ico'),'APPEND');
  	}
	
	
	
	/* 	Handle Redirects For Admin and Front Module 
	 
	 */
	private function _handleRedirects($request){	
		//prd($request);
		
		/* Return if Current Request is related to any public folder or related to any resource */
 		if($request->getControllerName()=="public"){
			return ;
		}
		
		if(!$this->_logged_user){
 			if(!in_array($request->getControllerName(),$this->_allowed_resources[$request->getModuleName()])){
				if(isset($this->_allowed_resources[$request->getModuleName()][$request->getControllerName()]) and is_array($this->_allowed_resources[$request->getModuleName()][$request->getControllerName()])){
					if(in_array($request->getActionName(),$this->_allowed_resources[$request->getModuleName()][$request->getControllerName()])){
						return ;							
					}
				}
				 

				$site_name = explode("/",SITE_HTTP_URL);
				
				$exploder = $request->getModuleName()=="admin"?"admin":array_pop($site_name); 
 				
				$redirect_url = explode($exploder,$_SERVER['REQUEST_URI']) ;
				
				//prd($redirect_url);
				
				$exploder = $exploder=="admin"?"/admin":"";
				
				$this->_response->setRedirect($request->getBaseUrl().$exploder .'/login?url='.urlencode("/".$exploder.$redirect_url[1]));
			}
		}
	}
	
 
 
  	
	
  	/* 	Load General Setting [Private Function]
	 
	 */
	private function loadSetting(){

  		/* Set Configs  */
 		$configuration = $this->db->query('SELECT * FROM config')->fetchAll();
		
 		foreach($configuration as $key=>$config){
			$config_data[$config['config_key']]= $config['config_value'] ;
			$config_groups[$config['config_group']][$config['config_key']]=$config['config_value'];	
		}
		
 		$this->site_configs = $config_data;
 		Zend_Registry::set("site_config",$config_data) ;
		
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		if (null === $viewRenderer->view) {
			$viewRenderer->initView();
		}
		$view = $viewRenderer->view;	
		
		 
		
		$view->current_controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		$view->current_action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		
		
 		$view->site_configs=$config_data;  
		$errormessage = Zend_Registry::get("flash_error") ;
		
 		
	}
 
 
 
	/* 	postDispatch Plugin  
	  
	 *	Description - Manage Site Meta and site title for the site 
	 */
   	public function postDispatch(Zend_Controller_Request_Abstract $request){	
	
 	
		$view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
		
		/*$view->headMeta()->appendName('viewport',"width=device-width, initial-scale=1.0");
		$view->headMeta()->appendName('keywords',$this->site_configs['meta_keyword']);
		$view->headMeta()->appendName('description',$this->site_configs['meta_description']);*/
		
  		$view->headTitle()->setSeparator(' | ');
		//$view->headTitle($this->site_configs['site_title']);
 	
		if(isset($view->pageHeading) and !empty($view->pageHeading))
			$view->headTitle($view->pageHeading);
 		 
  	} 
	
	public function _checkDuesInvoice($request){
	    global $objSession;
		 $custom = new Zend_Custom();
		 $invoiceCheck = $custom->InvoiceDueDateCheck();
		 if(!empty($invoiceCheck)){
			 $objSession->errorMsg = $invoiceCheck['Message'];
			 if($request->getActionName() =='logout')
			 {
				 unset ($objSession->errorMsg);
			 }
			 if($invoiceCheck['Block']==1 && $request->getControllerName()!='Dashboard' && $request->getActionName() !='logout' && $request->getActionName() !='invoicelist' ) {
				
					$this->_response->setRedirect('/Dashboard');
				
			 }

			 
			 return true;
		  }
	}
	
 	
	
    
}
?>