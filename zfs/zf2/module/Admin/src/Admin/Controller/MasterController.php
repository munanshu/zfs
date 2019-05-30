<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MasterController extends AppController
{

    public function indexAction()
    {
    	// print_r($this->getUserTable()->getUser(109)); die;	
        return new ViewModel();
    }


}

