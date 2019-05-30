<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AppController extends AbstractActionController
{

    protected $_userTable;

    public function getUserTable() {

        if (!$this->_userTable) {
            $sm = $this->getServiceLocator();
            $this->_userTable = $sm->get('Application\Model\UserTable');
        }
        return $this->_userTable;
    }


}

