<?php 

namespace Admin\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Admin\Service\RestrictionService;
use Interop\Container\ContainerInterface;
 

class RestrictionServiceFactory implements FactoryInterface
{
	
	public function createService(ServiceLocatorInterface $sm)
	{
    }

    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null) 
    {
		$UserMapper = $container->get('UserMapper');
		return new RestrictionService($UserMapper);
    }
}

