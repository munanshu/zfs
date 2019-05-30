<?php 


namespace Admin\Navigation;

use Zend\Permissions\Acl\Acl; 
use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Service\AbstractNavigationFactory;
use Admin\Mapper\ModuleMapper; 
use Zend\Navigation\Service\AbstractNavigationFactory as NewAbstractNavigationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;


class Customacl extends NewAbstractNavigationFactory
{
	protected $modulemapper;


	 public function __construct(ModuleMapper $modulemapper)
	 {
	 	$this->modulemapper = $modulemapper;
	 	$this->modulemapper->fetchAllModules();
	 }

	  public function getName()
	 {
	 	return 'Customacl';
	 }

	 public function getPages(ContainerInterface $sm)
	 {

	 	 

	 	// $configuration['navigation'][$this->getName()] = array(

			//      // 'navigation' => [

			//      //    'Customacl' => [
			            
			//             [
			//                 'label' => 'Dashboard',
			//                 'route' => 'admin/default',
			//                 'icon' => 'fa fa-dashboard',
			//                 'controller' => 'dashboard',
			//             ],

			//             [
			//                 'label' => 'Manage Categories',
			//                 'route' => 'admin/default',
			//                 'pages' => [
			//                     [
			//                         'label' => 'All Categories',
			//                         'route' => 'admin/default',
			//                         'controller' => 'Categories',
			//                         'action' => 'getall',
			//                     ],
			//                 ],
			//             ],
			             
			//       //   ],
			//       // ]  
			//     );

	 	  // $application = $sm->get('Application');
     //       $routeMatch  = $application->getMvcEvent()->getRouteMatch();
     //        $router      = $application->getMvcEvent()->getRouter();

	 	 	// $pages   = $this->getPagesFromConfig($configuration['navigation'][$this->getName()]);
	    //      $this->pages = $this->injectComponents($pages,$routeMatch,$router);

        // return $this->pages;

        return array( 'navigation' => [

		        'Customacl' => [
		            
		            [
		                'label' => 'Dashboard',
		                'route' => 'admin/default',
		                'icon' => 'fa fa-dashboard',
		                'controller' => 'dashboard',
		            ],

		            [
		                'label' => 'Manage Categories',
		                'route' => 'admin/default',
		                'pages' => [
		                    [
		                        'label' => 'All Categories',
		                        'route' => 'admin/default',
		                        'controller' => 'Categories',
		                        'action' => 'getall',
		                    ],
		                ],
		            ],
		             
		        ],

		    ] 
		);
	 }

 

}