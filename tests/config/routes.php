<?php

/**
 * Routes configuration
 *
 * @link http://zbase.dennesabing.com
 * @author Dennes B Abing <dennes.b.abing@gmail.com>
 * @license proprietary
 * @copyright Copyright (c) 2016 ClaremontDesign/MadLabs-Dx
 * @file routes.php
 * @project Zbase
 * @package config
 *
 * routes.home.controller.name = classname
 * routes.home.controller.method = class method
 * routes.home.controller.enable = true|false
 * routes.home.view.name = view name
 * routes.home.view.enable = true|false
 * routes.home.view.layout = true|false, to return with layout
 * routes.home.method = index
 * routes.home.form.enable = true|false
 * routes.home.url = /
 * routes.home.params = []
 * routes.home.auth = true|false, authenticated users only
 * routes.home.enable = true|false
 * routes.home.httpverb = [get,post, put, patch, delete, options]
 */
return [
	'routes' => [
		'index' => [
			'controller' => [
				'name' => 'page',
				'method' => 'index',
				'enable' => true
			],
			'url' => '/',
			'auth' => false,
			'enable' => true
		],
		'testParams' => [
			'controller' => [
				'name' => 'page',
				'method' => 'index',
				'enable' => true
			],
			'url' => '/testparams/{paramOne?}/{paramTwo?}',
			'auth' => false,
			'enable' => true
		],
		'testForm' => [
			'controller' => [
				'name' => 'page',
				'method' => 'form',
				'enable' => true
			],
			'form' => [
				'enable' => true
			],
			'url' => '/tests/form',
			'auth' => false,
			'enable' => true
		],
		'viewroute' => [
			'view' => [
				'name' => 'viewroute',
				'enable' => true
			],
			'url' => '/tests/view-route',
			'auth' => false,
			'enable' => true
		],
		'rout-auth' => [
			'view' => [
				'name' => 'route-auth',
				'enable' => true
			],
			'url' => '/tests/route-auth',
			'auth' => true,
			'enable' => true
		],
	],
];
