<?php 


namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
 
class DashboardController extends AbstractActionController
{
	
	public function indexAction()
	{
		return new ViewModel();
	}


	public function errorAction()
	{
		return new ViewModel();
	}

}