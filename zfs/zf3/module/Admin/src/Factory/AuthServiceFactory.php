<?php 

namespace Admin\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Admin\Service\AuthService;
use Interop\Container\ContainerInterface;
 

class AuthServiceFactory implements FactoryInterface
{
	
	public function createService(ServiceLocatorInterface $sm)
	{
    }

    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null) 
    {
		$Adapter = $container->get('Zend\Db\Adapter\Adapter');
		$UserMapper = $container->get('UserMapper');
		return new AuthService($Adapter,$UserMapper);
    }
}

