<?php

namespace Zbase\Ui\Data;

/**
 * Zbase-Ui-Data-DisplayStatus
 *
 * PageHeader
 *
 * @link http://zbase.dennesabing.com
 * @author Dennes B Abing <dennes.b.abing@gmail.com>
 * @license proprietary
 * @copyright Copyright (c) 2016 ClaremontDesign/MadLabs-Dx
 * @file DisplayStatus.php
 * @project Zbase
 * @package Zbase/Ui/Data
 */
use Zbase\Traits;
use Zbase\Interfaces;
use Zbase\Ui as UIs;

abstract class Data extends UIs\Ui implements UIs\UiInterface, Interfaces\IdInterface
{

	use Traits\Attribute,
	 Traits\Id;

	/**
	 * Widget Type
	 * @var string
	 */
	protected $_type = null;

	/**
	 * The Value
	 * @var string
	 */
	protected $_value = null;

	public function _pre()
	{
		$this->_viewFile = 'ui.data.' . $this->_type;
	}

	/**
	 * Set Value
	 * @param type $value
	 * @return \Zbase\Ui\PageHeader
	 */
	public function setValue($value)
	{
		$this->_value = $value;
		return $this;
	}

	public function getValue()
	{
		return $this->_value;
	}

	/**
	 * Return the Wrapper Attributes
	 * @return array
	 */
	public function wrapperAttributes()
	{
		$attr = $this->_v('html.attributes.wrapper', []);
		$attr['class'][] = 'zbase-ui-data';
		$attr['class'][] = 'zbase-ui-data-' . $this->_type;
		return $attr;
	}

	/**
	 * HTML the ui
	 * @return string
	 */
	public function __toString()
	{
		$this->prepare();
		if(!is_null($this->_viewFile))
		{
			return zbase_view_render(zbase_view_file_contents($this->_viewFile), ['ui' => $this])->__toString();
		}
		return '';
	}
}