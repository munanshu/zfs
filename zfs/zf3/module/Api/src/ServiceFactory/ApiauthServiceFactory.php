<?php


namespace Api\ServiceFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Api\Service\ApiauthService;


class ApiauthServiceFactory implements FactoryInterface
{

	public function createService(ServiceLocatorInterface $sm)
	{
		 $Adapter = $sm->get('Zend\Db\Adapter\Adapter');
		 $UserTable = $sm->get('Api\Model\User');
          
         return new ApiauthService($Adapter,$UserTable);
	}
	
}