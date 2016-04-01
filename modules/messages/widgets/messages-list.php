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
		'access' => [
			'enable' => true,
			'role' => 'user',
			'noauth' => [
				'route' => 'login',
			]
		],
		'entity' => [
			'node' => [
				'enable' => true
			],
			'name' => 'messages',
			'filter' => ['public' => true, 'currentUser' => true]
		],
		'view' => [
			'file' => 'modules.messages.list'
		],
		'actions' => [],
		'columns' => [],
	],
];
