<?php 

namespace Admin\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Admin\Service\MediaService;
use Interop\Container\ContainerInterface;
 

class MediaServiceFactory implements FactoryInterface
{
	
	public function createService(ServiceLocatorInterface $sm)
	{
    }

    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null) 
    {
		$MediaMapper = $container->get('MediaMapper');
		return new MediaService($MediaMapper);
    }
}

