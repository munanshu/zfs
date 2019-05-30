<?php 
namespace Admin\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Service\UserService;
use Admin\Service\AuthService;
use Admin\Controller\IndexController;
use Interop\Container\ContainerInterface;
// Factory class
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null) 
    {
    	// echo $requestedName;die;
        // Get the instance of CurrencyConverter service from the service manager.
        $UserService = $container->get(UserService::class);
        $AuthService = $container->get(AuthService::class);
        
        // Create an instance of the controller and pass the dependency 
        // to controller's constructor.
        return new $requestedName($UserService,$AuthService);	
    }
}
