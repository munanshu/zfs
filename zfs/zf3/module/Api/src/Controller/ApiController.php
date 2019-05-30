<?php


namespace Api\Controller;

use Zend\View\Model\ViewModel; 
use Zend\Mvc\Controller\AbstractRestfulController; 

class ApiController extends AbstractRestController
{
	

	public function indexAction()
	{
		$operation = $this->Params()->fromRoute('operation');



		echo "<pre>"; 
			print_r($operation);
		die;
		$view = new ViewModel();
		$view->setTerminal(true);
		return $view;
	}



}