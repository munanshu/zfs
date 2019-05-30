<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AppController extends AbstractActionController
{

    protected $_adminTable;

    public function getAdminTable() {

        if (!$this->_adminTable) {
            $sm = $this->getServiceLocator();
            $this->_adminTable = $sm->get('Admin\Model\AdminTable');
        }
        return $this->_adminTable;
    }


}

