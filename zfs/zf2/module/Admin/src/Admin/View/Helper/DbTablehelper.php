<?php 

namespace Admin\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

 

class DbTablehelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
	protected $adptr;

	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
		return $this;
	}

	public function getServiceLocator() {
		return $this->serviceLocator;
	}


	 public function getTable($id)
	 {	
	 	$config = $this->getServiceLocator()->getServiceLocator()->get('AdminMaster')->getAdminTable()->fetchSingle($id); 
	 	return $config;
	 }


}