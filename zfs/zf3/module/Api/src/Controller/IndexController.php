<?php


namespace Api\Controller;

use Zend\View\Model\ViewModel; 

class IndexController extends AbstractRestController
{
	

	public function IndexAction()
	{
		$view = new ViewModel();
		$view->setTerminal(true);
		return $view;
	}



}