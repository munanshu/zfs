<?php



namespace Api\ServiceFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Api\Service\UserService;

class UserServiceFactory implements FactoryInterface
{
	
	public function createService(ServiceLocatorInterface $sm)
	{
		$usertable = $sm->get('Api\Model\User');
		return new UserService($usertable);
	}
}