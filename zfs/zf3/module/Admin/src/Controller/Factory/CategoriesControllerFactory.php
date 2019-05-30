<?php 
namespace Admin\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Service\CategoryService;
use Admin\Service\MediaService;
use Admin\Controller\IndexController;
use Interop\Container\ContainerInterface;

class CategoriesControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null) 
    {
        $CategoryService = $container->get(CategoryService::class);
        $MediaService = $container->get(MediaService::class);
        
        return new $requestedName($CategoryService,$MediaService);	
    }
}
