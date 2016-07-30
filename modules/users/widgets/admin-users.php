<?php

/**
 * Dx
 *
 * @link http://dennesabing.com
 * @author Dennes B Abing <dennes.b.abing@gmail.com>
 * @license proprietary
 * @copyright Copyright (c) 2015 ClaremontDesign/MadLabs-Dx
 * @version 0.0.0.1
 * @since Mar 8, 2016 10:37:59 AM
 * @file widget.php
 * @project Expression project.name is undefined on line 13, column 15 in Templates/Scripting/EmptyPHP.php.
 * @package Expression package is undefined on line 14, column 15 in Templates/Scripting/EmptyPHP.php.
 *
 */
return [
	'type' => 'datatable',
	'enable' => true,
	'config' => [
		'angular' => [
			'route' => [
				'name' => 'admin.users'
			],
			'controller' => 'adminUsersController',
			'view' => [
				'list' => [
					'type' => 'list',
					'link' => '#/users/view/<% APINAME.id %>',
					'url' => '/users/view/',
				],
				'file' => null,
				'format' => '<span>Email Address</span>: <span><% APINAME.email %></span>',
			]
		],
		'entity' => [
			'name' => 'user',
			'filter' => ['admin' => true],
		],
		'actions' => [
			'create' => [
				'enable' => false,
				'label' => 'Create new node',
				'route' => [
					'name' => 'admin.users',
					'params' => ['action' => 'create']
				],
			],
			'update' => [
				'enable' => true,
				'route' => [
					'name' => 'admin.users',
					'params' => ['action' => 'update', 'id' => 'row::alpha_id']
				],
			],
			'delete' => [
				'enable' => true,
				'route' => [
					'name' => 'admin.users',
					'params' => ['action' => 'delete', 'id' => 'row::alpha_id']
				],
			],
			'restore' => [
				'enable' => true,
				'route' => [
					'name' => 'admin.users',
					'params' => ['action' => 'restore', 'id' => 'row::alpha_id']
				],
			],
			'ddelete' => [
				'enable' => true,
				'route' => [
					'name' => 'admin.users',
					'params' => ['action' => 'ddelete', 'id' => 'row::alpha_id']
				],
			],
		],
		'columns' => [
			'id' => [
				'label' => 'ID',
				'enable' => true,
				'data' => [
					'type' => 'integer',
					'index' => 'alpha_id'
				],
			],
			'pageTitle' => [
				'label' => 'Page Title',
				'enable' => function(){
					if(zbase_is_json())
					{
						return true;
					}
					return false;
				},
				'data' => [
					'type' => 'string',
					'index' => 'User: $$profile.first_name $$profile.last_name'
				],
			],
			'viewTitle' => [
				'label' => 'Title',
				'enable' => function(){
					if(zbase_is_json())
					{
						return true;
					}
					return false;
				},
				'data' => [
					'type' => 'string',
					'index' => 'User: $$profile.first_name $$profile.last_name'
				],
			],
			'email' => [
				'label' => 'Email Address',
				'enable' => true,
				'data' => [
					'type' => 'string',
					'index' => 'email'
				],
			],
			'first_name' => [
				'label' => 'First Name',
				'enable' => true,
				'data' => [
					'type' => 'string',
					'index' => 'profile.first_name'
				],
			],
			'last_name' => [
				'label' => 'Last Name',
				'enable' => true,
				'data' => [
					'type' => 'string',
					'index' => 'profile.last_name'
				],
			],
			'status' => [
				'label' => 'Status',
				'enable' => true,
				'data' => [
					'type' => 'displayStatus',
					'index' => 'status'
				],
			],
			'created_at' => [
				'label' => 'Created',
				'enable' => true,
				'data' => [
					'type' => 'timestamp',
					'index' => 'created_at'
				],
			],
			'updated_at' => [
				'label' => 'Updated',
				'enable' => true,
				'data' => [
					'type' => 'timestamp',
					'index' => 'updated_at'
				],
			],
		],
	],
];