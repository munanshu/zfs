<?php
 return array(
     'controllers' => array(
         'invokables' => array(
             'Admin\Controller\Admin' => 'Admin\Controller\AdminController',
             'Admin\Controller\Master' => 'Admin\Controller\MasterController',
             'Admin\Controller\Index' => 'Admin\Controller\IndexController',
             'Admin\Controller\Dashboard' => 'Admin\Controller\DashboardController',
             'Admin\Controller\Category' => 'Admin\Controller\CategoryController',
         ),
     ),

     // The following section is new and should be added to your file
     'router' => array(
         'routes' => array(
             


             'admin' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/admin',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),

            'login' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/login[/:action][/:id]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'id'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         'controller' => 'Admin\Controller\Index',
                         'action'     => 'index',
                     ),
                 ),
             ),

         ),
     ),

     // 'view_manager' => array(
     //  // 'template_map' => array(
     //  //       'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
     //  //       // 'admin/index/index' => __DIR__ . '/../view/admin/index/index.phtml',
     //  //       // 'error/404'               => __DIR__ . '/../view/error/404.phtml',
     //  //       // 'error/index'             => __DIR__ . '/../view/error/index.phtml',
     //  //   ),
     //     'template_path_stack' => array(
     //           __DIR__ . '/../view',
     //     ),
     // ),

     'view_manager' => array(
    'template_path_stack' => array(
        'admin' => __DIR__ . '/../view',
    ),
    'template_map' => array(
        'admin/layout' => __DIR__ . '/../view/layout/layout1.phtml',
        'layout/default' => __DIR__ . '/../view/layout/layout2.phtml',
        'dashboard/test' => __DIR__ . '/../view/admin/dashboard/test.phtml',
        'layout/msgtemplate' => __DIR__ . '/../view/layout/partial/flash-messages.phtml',
      ),
    ),
 );