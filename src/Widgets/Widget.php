<?php

namespace Zbase\Widgets;

/**
 * Zbase-Widgets Widget
 *
 * Widget base model
 *
 * @link http://zbase.dennesabing.com
 * @author Dennes B Abing <dennes.b.abing@gmail.com>
 * @license proprietary
 * @copyright Copyright (c) 2016 ClaremontDesign/MadLabs-Dx
 * @file Widget.php
 * @project Zbase
 * @package Zbase/Widgets
 *
 * type = Type of Widget eg. form
 * id = name of widget; unique; [optional], default to name of widget file
 * enable = true|false
 * access = access [optional], default to minimum access
 * 		string: minimum|admin
 * 		array: [admin, user]
 * 		Who has access.
 * 		minimum|role name
 * 		minimum is the minimum role for the current section, else a role name or array of role names
 * 
 * config = array; widget-type-specific configuration
 */
use Zbase\Traits;

class Widget extends \Zbase\Ui\Ui implements \Zbase\Ui\UiInterface
{

	use Traits\Attribute,
	 Traits\Id,
	 Traits\Position;

	/**
	 * Widget Type
	 * @var string
	 */
	protected $_type = null;

	/**
	 * The Widget ID
	 * @var string
	 */
	protected $_widgetId = null;

	/**
	 * Current task
	 * display|update|delete|restore|ddelete|create
	 * @var string
	 */
	protected $_task = null;

	/**
	 * The Entity task add|update|delete|restore|ddelete|row|rows
	 * @var string
	 */
	protected $_entityTask = null;

	/**
	 * Constructor
	 * @param string $widgetId
	 * @param array $configuration
	 */
	public function __construct($widgetId, $configuration)
	{
		$this->_widgetId = $widgetId;
		$this->setAttributes($configuration);
	}

	/**
	 * Set Task
	 * @param string $task
	 * @return \Zbase\Widgets\Widget|string
	 */
	public function setTask($task)
	{
		$this->_task = $task;
		return $this;
	}

	/**
	 * Get Task
	 * @return string
	 */
	public function task()
	{
		return $this->_task;
	}

}
