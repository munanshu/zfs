<?php 


namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
 
class MediaController extends MasterController
{
	
	public function getallAction()
	{
		return new ViewModel();
	}

}