<?php

namespace Zbase\Entity\Laravel\Traits;

/**
 * Zbase-Entity Zbase Entity Maker
 *
 * Filterable Entity
 *
 * @link http://zbase.dennesabing.com
 * @author Dennes B Abing <dennes.b.abing@gmail.com>
 * @license proprietary
 * @copyright Copyright (c) 2016 ClaremontDesign/MadLabs-Dx
 * @file Entity.php
 * @project Zbase
 * @package Zbase/Entity/Traits
 */
use Zbase\Entity\Laravel\Relations\HasMany;
use Zbase\Entity\Laravel\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
//use Illuminate\Database\Eloquent\Relations\Pivot;
//use Illuminate\Database\Eloquent\Relations\MorphTo;
//use Illuminate\Database\Eloquent\Relations\MorphOne;
//use Illuminate\Database\Eloquent\Relations\MorphMany;
//use Illuminate\Database\Eloquent\Relations\MorphToMany;
//use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Zbase\Interfaces;

trait Entity
{

	/**
	 * Entity name as described in the config
	 * @var string
	 */
	protected $entityName = null;

	/**
	 * Column Map
	 * @var array
	 */
	protected $dbColumns = [];

	/**
	 * Array of Relationships
	 * @var array
	 */
	protected $relationship = [];

	/**
	 * The entity attributes
	 * @var array
	 */
	protected $entityAttributes = [];

	/**
	 * The Relation mode
	 * if relationshipMode == result, will return results when relationship method is called
	 * else, will return the Relation Model
	 * @var string
	 */
	protected $relationshipMode = 'result';

	/**
	 * If entity is sluggable
	 * @var array|booleaan
	 */
	protected $sluggable = false;

	/**
	 * If entity has alpha id
	 * @var boolean
	 */
	protected $alphable = false;

	/**
	 * Soft Delete
	 * @var boolean
	 */
	protected $softDelete = false;

	/**
	 *
	 * @var Interfaces\EntityRepositoryInterface
	 */
	protected $repository = null;

	/**
	 * Create zbase like entity
	 */
	public function __initEntity()
	{
		$this->entityAttributes = $this->entityAttributes();
		$this->table = zbase_data_get($this->entityAttributes, 'table.name', $this->table);
		$this->primaryKey = zbase_data_get($this->entityAttributes, 'table.primaryKey', false);
		$this->timestamps = zbase_data_get($this->entityAttributes, 'table.timestamp', false);
		$this->sluggable = zbase_data_get($this->entityAttributes, 'table.sluggable', false);
		$this->alphable = zbase_data_get($this->entityAttributes, 'table.alphaId', false);
		$this->softDelete = zbase_data_get($this->entityAttributes, 'table.softDelete', false);
		if(!empty($this->softDelete))
		{
			$this->dates[] = 'deleted_at';
		}
		if(empty($this->primaryKey))
		{
			$this->incrementing = false;
		}
		$this->dbColumns = zbase_data_get($this->entityAttributes, 'table.columns', $this->dbColumns);
		$this->relationship = zbase_data_get($this->entityAttributes, 'relations', $this->relationship);
		$optionable = zbase_data_get($this->entityAttributes, 'table.optionable', false);
		if(!empty($optionable))
		{
			$this->casts['options'] = 'array';
		}
		$this->perPage = zbase_data_get($this->entityAttributes, 'table.perPage', $this->perPage);
		if(!empty($this->dbColumns))
		{
			foreach ($this->dbColumns as $columnName => $column)
			{
				$type = $column['type'];
				if(!empty($column['fillable']))
				{
					$this->fillable[] = $columnName;
				}
				if(!empty($column['hidden']))
				{
					$this->hidden[] = $columnName;
				}
				if($type == 'timestamp')
				{
					$this->dates[] = $columnName;
				}
			}
		}
	}

	/**
	 * Return an entity attribute
	 * @param string $key
	 * @return array
	 */
	public function entityAttributes($key = null)
	{
		if(empty($this->entityAttributes))
		{
			$this->entityAttributes = zbase_config_get('entity.' . $this->entityName);
		}
		if(!empty($key))
		{
			return zbase_data_get($this->entityAttributes, $key);
		}
		return $this->entityAttributes;
	}

	/**
	 * @see $this->repository
	 * @return Interfaces\EntityRepositoryInterface
	 */
	public function repository()
	{
		if(!$this->repository instanceof Interfaces\EntityRepositoryInterface)
		{
			$this->repository = new \Zbase\Entity\Laravel\Repository($this);
		}
		return $this->repository;
	}

	// <editor-fold defaultstate="collapsed" desc="DataOptions">

	/**
	 * Return the DAta Options
	 * @return array
	 */
	public function getDataOptions()
	{
		return $this->options;
	}

	/**
	 * Set Data Options
	 * @param array $options
	 * @return \Zbase\Entity\Laravel\Entity
	 */
	public function setDataOptions($options)
	{
		if(!empty($options))
		{
			foreach ($options as $k => $v)
			{
				$this->setDataOption($k, $v);
			}
		}
		return $this;
	}

	/**
	 * SEt the Data Option
	 * @param string $key The key
	 * @param string|mixed $value
	 * @return \Zbase\Entity\Laravel\Entity
	 */
	public function setDataOption($key, $value)
	{
		$options = $this->options;
		$options[$key] = $value;
		$this->options = $options;
		return $this;
	}

	/**
	 * Unset/Remove a data option
	 * @param string $key the key to remove from the options
	 * @return \Zbase\Entity\Laravel\Entity
	 */
	public function unsetDataOption($key)
	{
		$options = $this->options;
		if(isset($options[$key]))
		{
			unset($options[$key]);
		}
		$this->options = $options;
		return $this;
	}

	/**
	 * Unset all options
	 * @return \Zbase\Entity\Laravel\Entity
	 */
	public function unsetAllOptions()
	{
		$this->options = null;
		return $this;
	}

	/**
	 * Return a Data Option
	 * @param string $key The Key to return
	 * @return string|mixed|null
	 */
	public function getDataOption($key, $default = null)
	{
		$options = $this->options;
		if(isset($options[$key]))
		{
			return $options[$key];
		}
		return $default;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="GETTER/SETTER">
	/**
	 * Dynamically retrieve attributes on the model.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if(!empty($this->relationship))
		{
			foreach ($this->relationship as $rK => $rV)
			{
				$r = $this->_relationship($key, $rK, $rV);
				if($r !== false)
				{
					return $r;
				}
			}
		}
		return parent::__get($key);
	}

	/**
	 * Handle dynamic method calls into the model.
	 *
	 * https://laravel.com/docs/5.1/eloquent-relationships#one-to-one
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if(!empty($this->relationship))
		{
			foreach ($this->relationship as $rK => $rV)
			{
				$r = $this->_relationship($method, $rK, $rV);
				if($r !== false)
				{
					return $r;
				}
			}
		}
		return parent::__call($method, $parameters);
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="RELATIONSHIP">
	/**
	 *
	 * @param string $method The property or method to be called
	 * @param string $name The name of the relationship index
	 * @param array $config The config of the relationship
	 * @return HasOne|BelongsTo
	 */
	protected function _relationship($method, $name, $config)
	{
		$rType = zbase_value_get($config, 'type', false);
		if(!empty($rType))
		{
			$rMethod = zbase_value_get($config, 'class.method', zbase_string_camel_case($name));
			$isFetching = preg_match('/fetch/', $method);
			if($isFetching)
			{
				$method = zbase_string_camel_case(str_replace('fetch', '', $method));
			}
			if($rMethod == $method)
			{
				$rEntity = zbase_value_get($config, 'entity', false);
				$rInverse = zbase_value_get($config, 'inverse', false);
				$model = zbase_entity($rEntity);
				$lKey = zbase_value_get($config, 'keys.local', null);
				$fKey = zbase_value_get($config, 'keys.foreign', null);
				if(!empty($rEntity))
				{
					switch (strtolower($rType))
					{
						case 'onetoone':
							if(!empty($rInverse))
							{
								$relObj = new BelongsTo($model->newQuery(), $this, $model->getTable() . '.' . $fKey, $lKey, $name);
							}
							else
							{
								$relObj = new HasOne($model->newQuery(), $this, $model->getTable() . '.' . $fKey, $lKey);
							}
							break;
						case 'onetomany':
							if(!empty($rInverse))
							{
								$relObj = new BelongsTo($model->newQuery(), $this, $model->getTable() . '.' . $fKey, $lKey, $name);
							}
							else
							{
								return new HasMany($model->newQuery(), $this, $model->getTable() . '.' . $fKey, $lKey);
							}
							break;
						case 'manytomany':
							$pivot = zbase_value_get($config, 'pivot', null);
							return new BelongsToMany($model->newQuery(), $this, $pivot, $fKey, $lKey, $name);
						case 'hasmanythrough':
							break;
						case 'morphto':
							break;
						case 'morphtomany':
							break;
						default;
					}
					if(!empty($relObj))
					{
						if($this->relationshipMode == 'result')
						{
							return zbase_cache(
									$this->cacheKey($relObj), function() use ($relObj){
								return $relObj->getResults();
							}, [$this->getTable()]
							);
						}
						return $relObj;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Toggle the relationMode
	 * @return void
	 */
	public function toggleRelationshipMode()
	{
		if($this->relationshipMode == 'result')
		{
			$this->relationshipMode = false;
		}
		else
		{
			$this->relationshipMode = 'result';
		}
		return $this;
	}

	// </editor-fold>

	/**
	 * Check if SoftDeleting
	 * @return boolean
	 */
	public function hasSoftDelete()
	{
		return $this->softDelete;
	}
}
