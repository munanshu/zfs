<?php


namespace Admin;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Admin\Controller\Factory\IndexControllerFactory;

return [
    'router' => [
        'routes' => [

               
            'admin' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/admin',
                    'defaults' => [
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [

                        'default' => [
                            'type'    => Segment::class,
                            'options' => [
                                'route'    => '/[:controller[/:action]]',
                                'constraints' => array(
                                    'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                ),
                                'defaults' => [
                                ],
                            ],

                        ],

                ]
            ],




        ],
    ],

    'controllers' => [

        'aliases' => [

            'Admin\Controller\Categories' => 'Admin\Controller\CategoriesController',
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            // 'Admin\Controller\Media' => 'Admin\Controller\MediaController',

        ],

       
        'invokables' => [

                 // 'Admin\Controller\Index' => 'Admin\Controller\IndexController',
                'Admin\Controller\Media' => 'Admin\Controller\MediaController',
                'Admin\Controller\App' => 'Admin\Controller\AppController',
                'Admin\Controller\Dashboard' => 'Admin\Controller\DashboardController',
                'Admin\Controller\User' => 'Admin\Controller\UserController',

            ],

        'factories' => [

            // Controller\IndexController::class => function($container) {
            //     // $UserMapper = $container->get('UserMapper');
            //     // $UserService = new \Admin\Service\UserService($UserMapper);
            //     $UserService = $container->get('Admin\Service\UserService');

            //     return new Controller\IndexController( $UserService );
            // },

              Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
        Controller\CategoriesController::class => Controller\Factory\CategoriesControllerFactory::class,
        ],

      
    ],  

    'view_manager' => [
         
         'template_path_stack' => [
            'admin' => __DIR__ . '/../view',
         ],
         'template_map' => array(
            'admin/layout' => __DIR__ . '/../view/layout/adminlayout.phtml',
            'admin/login' => __DIR__ . '/../view/layout/login.phtml',
          ),
    ],


    'service_manager' => [

        'factories' => [

            'Admin\Service\UserService' => 'Admin\Factory\UserServiceFactory',
            'Admin\Service\AuthService' => 'Admin\Factory\AuthServiceFactory',
            'Admin\Service\CategoryService' => 'Admin\Factory\CategoryServiceFactory',
            'Admin\Service\MediaService' => 'Admin\Factory\MediaServiceFactory',
            'Admin\Service\RestrictionService' => 'Admin\Factory\RestrictionServiceFactory',
            'Customacl' => 'Admin\Factory\CustomaclFactory',
        ]

    ],

 



   

];
