<?php
return array(
	'router' => array(
        'routes' => array(
    		'api' => array(
    			'type' => 'Literal',
    			'options' => array(
    				'route' => '/api',
    				'defaults' => array(
                        '__NAMESPACE__' => 'Api\Controller',
    					'controller'=> 'Api\Controller\Index',
    					'action' => 'notFound'
    				)
    			),
    			'may_terminate' => true,
    			'child_routes' => array(
                    
                    
                     
                        'auth' => array(
                            'type' => 'Method',
                            'options' => array(
                                    'verb'=>'post'
                                ),
                                'child_routes' =>  array(
                                        
                                        'createToken' => array(
                                            'type' => 'Segment',
                                                'options' => array(
                                                'route' => '/auth/createToken',
                                                'defaults' => array(
                                                    'controller' => 'Api\Controller\Auth',
                                                    'action' => 'createToken'
                                                    )
                                                ),
                                        ),
                                        'refreshToken' => array(

                                                'type' => 'Segment',
                                                'options' => array(
                                                    'route' => '/auth/refreshToken',
                                                    'defaults' => array(
                                                        'controller' => 'Api\Controller\Auth',
                                                        'action' => 'refreshToken'
                                                    )
                                                )
                                            ),

                                )
                        ),


                        'customers' => [

                            'type' =>'Segment',
                            'options' => [
                                'route' => '/customers',
                            ],
                            'may_terminate' => false,
                            'child_routes' => [

                                    'get' => [

                                           'type' => 'method',
                                           'options' => [
                                                'verb' => 'get',
                                                'defaults' => [
                                                    'controller' => 'Api\Controller\Customer',
                                                    'action' => 'getcustomers'
                                                ]
                                           ] 
                                    ],
                                     'post' => [

                                           'type' => 'method',
                                           'options' => [
                                                'verb' => 'post',
                                                'defaults' => [
                                                    'controller' => 'Api\Controller\Customer',
                                                    'action' => 'addcustomers'
                                                ]
                                           ] 
                                    ],

                            ]
                        ]

                        // 'users' => array(
                        //     'type' => 'method',
                        //     'options' => array(
                        //         'verb' => 'get',
                        //     ),
                        //     'child_routes' => array(
                        //         'getAll' => array(
                        //             'may_terminate' => false,
                        //             'type' => 'Segment',
                        //             'options' => array(
                        //                 'route' => '/user/getall',
                        //                 'constraints' => array(
                        //                     'id' => '[0-9]*'
                        //                 ),
                        //                 'defaults' => array(
                        //                     '__NAMESPACE__'=> 'Api\Controller',
                        //                     'controller' => 'Api\Controller\User',
                        //                     'action' => 'getAll',
                        //                 ),
                        //             ),
                        //         ),
                        //     ),
                        // ),

                        // 'customers' => array(

                        //     'type' => 'Method',
                        //     'options' => array(
                        //         'verb' => 'get,post',
                        //       ),
                        //      'child_routes' => array(

                        //              'getCustomer' => array(
                        //                     'may_terminate' => false,
                        //                     'verb' => 'get,post',

                        //                     'type' => 'Segment',
                        //                     'options' => array(
                        //                         'route' => '/customer/getcustomers[/page/:page][/limit/:limit]',
                        //                         'defaults' => array(
                        //                             '__NAMESPACE__'=> 'Api\Controller',
                        //                             'controller' => 'Api\Controller\Customer', 
                        //                             'action' => 'getcustomers', 
                        //                         ),
                        //                         'constraints' => array(
                        //                             'id' => '[0-9]*',
                        //                             'page'=>'[0-9]*',
                        //                             'limit'=>'[0-9]*',
                        //                         )

                        //                     )
                        //                 ),

                        //              'addCustomer' => array(
                        //                     'may_terminate' => false,
                        //                     'type' => 'Segment',
                        //                     'options' => array(
                        //                         'route' => '/customer/addCustomers',
                        //                         'defaults' => array(
                        //                             '__NAMESPACE__'=> 'Api\Controller',
                        //                             'controller' => 'Api\Controller\Customer', 
                        //                             'action' => 'addCustomers', 
                        //                         ),

                        //                     )
                        //                 )


                        //      )  



                        // )


                ),

    		),

            
        )
    ), 

    'controllers' => array(
        'invokables' => array(
            'Api\Controller\Index' => 'Api\Controller\IndexController',
            'Api\Controller\AbstractRest' => 'Api\Controller\AbstractRestController',
            'Api\Controller\Auth' => 'Api\Controller\AuthController',
            'Api\Controller\User' => 'Api\Controller\UserController',
            'Api\Controller\Customer' => 'Api\Controller\CustomerController',
            'Api\Controller\Api' => 'Api\Controller\ApiController',
        ),
    ),

    'service_manager' => array(
        'factories' => array(
            'Api\ServiceFactory\Apiauth' => 'Api\ServiceFactory\ApiauthServiceFactory',
            'Api\ServiceFactory\User' => 'Api\ServiceFactory\UserServiceFactory',
            'Api\CustomerMapperFactory' => 'Api\ServiceFactory\CustomerServiceFactory',
        ),

        'aliases' => array(
        )
    ),

    'view_manager' => array(
 
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ),

     'api' => [

        'inputFilters' => [
             
            'Api\Controller\Auth' => [

                'createToken' => [
                    [
                        'required' => true,
                        'validators' => [
                            [
                                'name' => 'Zend\Validator\NotEmpty',
                                'options' => [],
                            ],
                        ],
                        'filters' => [],
                        'name' => 'username',
                    ],

                    [
                        'required' => true,
                        'validators' => [
                            [
                                'name' => 'Zend\Validator\NotEmpty',
                                'options' => [],
                            ],
                        ],
                        'filters' => [],
                        'name' => 'password',
                    ],

                ],


                'refreshToken' => [
                    [
                        'required' => true,
                        'validators' => [
                            [
                                'name' => 'Zend\Validator\NotEmpty',
                                'options' => [],
                            ],
                        ],
                        'filters' => [],
                        'name' => 'username',
                    ],

                    [
                        'required' => true,
                        'validators' => [
                            [
                                'name' => 'Zend\Validator\NotEmpty',
                                'options' => [],
                            ],
                        ],
                        'filters' => [],
                        'name' => 'password',
                    ],

                    [
                        'required' => true,
                        'validators' => [
                            [
                                'name' => 'Zend\Validator\NotEmpty',
                                'options' => [],
                            ]
                        ],
                        'name' => 'refreshToken'
                    ]

                ],




            ],

            'Api\Controller\Customer' => [

                'addCustomers' => [
                       

                        [

                            'required' => true,
                            'validators' => [
                                
                                [
                                    'name' => 'Api\Validator\AlphaValidator',
                                ],

                                [
                                    'name' => 'Zend\Validator\NotEmpty',
                                ]
                                
                            ],
                            'name' => 'firstname'
                        ],

                        [
                            'required' => false,
                            'validators' => [
                                [
                                    'name' => 'Api\Validator\AlphaValidator',
                                ]
                            ],
                            'name' => 'lastname'
                        ],

                        [
                            'required' => true,
                            'validators' => [

                                [
                                    'name' => 'Api\Validator\AlnumValidator',
                                ],

                                [
                                    'name' => 'Zend\Validator\NotEmpty',
                                ]
                            ],
                            'name' => 'code'
                        ],

                        [
                            'required' => true,
                            'validators' => [

                                [
                                    'name' => 'Zend\Validator\EmailAddress',
                                ],

                            ],
                            'name' => 'email'
                        ],

                        [
                            'required' => false,
                            'validators' => [

                                [
                                    'name' => 'Zend\Validator\Digits',
                                    'options' => [
                                        'min' => 10,
                                        'max' => 10
                                    ]
                                ],
                                [
                                    'name' => 'Zend\Validator\StringLength',
                                    'options' => [
                                        'min' => 10,
                                        'max' => 10
                                    ]
                                ]
                            ],
                            'name' => 'mobile'
                        ],

                        [
                            'required' => false,
                            'name' => 'addressline1'
                        ],

                        [
                            'required' => false,
                            'validators' => [
                                [
                                    'name' => 'Api\Validator\AlphaValidator'
                                ],
                            ],
                            'name' => 'pincode'
                        ],


                ]
                 


            ]

        ]
    ],

);