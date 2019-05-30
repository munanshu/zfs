<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],

              

            // 'default' => array(
            //  'type' => 'segment',
            //  'options' => array(
            //      'route'    => '/[:controller[/:action]]',
            //      'defaults' => array(
            //          '__NAMESPACE__' => 'Application\Controller',
            //          'controller'    => Controller\IndexController::class,
            //          'action'        => 'index',
            //      ),
            //      'constraints' => [
            //          'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
            //          'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
            //      ]
            //   ),
            // )

                   

               
            // 'application' => [
            //     'type'    => Literal::class,
            //     'options' => [
            //         'route'    => '/application',
            //         'defaults' => [
            //             '__NAMESPACE__' => 'Application\Controller',
            //             'controller' => Controller\IndexController::class,
            //             'action'     => 'index',
            //         ],
            //     ],
            //     'may_terminate' => true,
            //     'child_routes' => [

            //             'default' => [
            //                 'type'    => Segment::class,
            //                 'options' => [
            //                     'route'    => '/[:controller[/:action]]',
            //                     'constraints' => array(
            //                         'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
            //                         'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
            //                     ),
            //                     'defaults' => [
            //                         '__NAMESPACE__' => 'Application\Controller',
            //                         '__CONTROLLER__' => 'index',
            //                         'controller' => Controller\IndexController::class,
            //                         'action'     => 'index',
            //                     ],
            //                 ],
            //             ],

            //     ]
            // ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
        ],
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\User' => 'Application\Controller\UserController',
        ),
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
