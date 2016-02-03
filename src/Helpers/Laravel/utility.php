<?php

/**
 * Zbase-Laravel Helpers-Utility
 *
 * Functions and Helpers and other Utility
 *
 * @link http://zbase.dennesabing.com
 * @author Dennes B Abing <dennes.b.abing@gmail.com>
 * @license proprietary
 * @copyright Copyright (c) 2016 ClaremontDesign/MadLabs-Dx
 * @file utility.php
 * @project Zbase
 * @package Zbase/Laravel/Helpers
 */

/**
 * Return the Zbase Object
 * @return Zbase
 */
function zbase()
{
	return app(zbase_tag());
}

/**
 * Create an object that implements ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
 *
 * @param array $array
 * @return ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
 */
function zbase_collection($array)
{
	return collect($array);
}

/**
 * Zbase is Testing
 */
function zbase_is_testing()
{
	return \App::environment('testing');
}