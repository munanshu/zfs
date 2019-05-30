<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;

class DashboardController extends AppController{


	public function indexAction()
	{  
        $this->layout('admin/layout');    
		
        // $a = "aa";
        // $view = new ViewModel();
        // $viewpart = new ViewModel();
        // $viewpart->setTemplate('dashboard/test');
        // $view->addChild($viewpart,'test');

        // $mailbody = new ViewModel(array('mun'=>$a));
        // $mailbody->setTemplate('dashboard/test');
         

        // $htmlMail = $this->getServicelocator()->get('ViewRenderer')->render($mailbody);

        // print_r($htmlMail);die;
        $view = new ViewModel();    
        return $view;

	}

	 public function logoutAction()
    {
    	$auth = new AuthenticationService();
    	if($auth->hasIdentity()){
    		$auth->getStorage()->clear();
            $this->flashMessenger()->addMessage(array('type'=>'loggedout','msg'=>'You have been logged out'));
    		return $this->redirect()->toRoute('admin/default');
    	}
      die;
    }

}
