<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UserController extends AppController
{

    public function indexAction()
    {
    	// print_r($this->getUserTable()->getUser(109)); die;	
        return new ViewModel();
    }

    public function testAction()
    {
    	echo "test";die;
        return new ViewModel();
    }



}

