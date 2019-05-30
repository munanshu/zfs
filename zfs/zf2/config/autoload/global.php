<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 *
 *'service_manager' => array(
 *				 'factories' => array(
 *				 'Zend\Db\Adapter\Adapter'
 *
 *				 => 'Zend\Db\Adapter\AdapterServiceFactory' ,
 *				 ),
 *			 ),
 */

return array(
			 'db' => array(
				 'driver' => 'Pdo' ,
				 'dsn' => 'mysql:dbname=zfshop_db;host=localhost' ,
				 'driver_options' => array(
				 PDO:: MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
				 ),
			 ),			 
			'service_manager' => array(
             'factories' => array(
			 'Zend\Db\Adapter\Adapter' => function ($serviceManager) {
				$adapterFactory = new Zend\Db\Adapter\AdapterServiceFactory();
				   $adapter = $adapterFactory->createService($serviceManager);

				   \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);

				   return $adapter;
				}
			  ),
		   ),
			'static_salt' => '1d6e2c766df1caaf885e9f39efb461ba928e36f2'

			 
 );