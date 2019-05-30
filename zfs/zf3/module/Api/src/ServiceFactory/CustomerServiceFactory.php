<?php

namespace Api\ServiceFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Api\Service\CustomerService; 
use Interop\Container\ContainerInterface;

class CustomerServiceFactory implements FactoryInterface
{
	
	public function createService(ServiceLocatorInterface $sm)
	{
    }

    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null) 
    {
		 $CustomerMapper = $container->get('Api\Mapper\CustomerMapper');
		return new CustomerService($CustomerMapper);
    }

	 
	
}