<?php 

namespace Admin\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Admin\Service\CategoryService;
use Interop\Container\ContainerInterface;
 

class CategoryServiceFactory implements FactoryInterface
{
	
	public function createService(ServiceLocatorInterface $sm)
	{
    }

    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null) 
    {
		$CategoryMapper = $container->get('CategoryMapper');
		return new CategoryService($CategoryMapper);
    }
}

