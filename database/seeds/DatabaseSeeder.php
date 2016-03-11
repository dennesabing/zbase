<?php

use Illuminate\Database\Eloquent\Model as LaravelModel;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

	/**
	 * Collection of entityNames that was processed already
	 * @var array
	 */
	protected $processedEntities = [];

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		LaravelModel::unguard();
		$entities = zbase_config_get('entity', []);

		if(!empty($entities))
		{
			foreach ($entities as $entityName => $entity)
			{
				$preEvent = zbase_data_get($entity, 'data.events.pre', false);
				if(!empty($preEvent))
				{
					if($preEvent instanceof \Closure)
					{
						$preEvent();
					}
				}
			}
			foreach ($entities as $entityName => $entity)
			{
				$this->_defaults($entityName, $entity);
			}
			foreach ($entities as $entityName => $entity)
			{
				$this->_factory($entityName, $entity);
			}
			foreach ($entities as $entityName => $entity)
			{
				$postEvent = zbase_data_get($entity, 'data.events.post', false);
				if(!empty($postEvent))
				{
					if($postEvent instanceof \Closure)
					{
						$postEvent();
					}
				}
			}
		}
		LaravelModel::reguard();
	}

	/**
	 * Run all default
	 * @param type $entityName
	 * @param type $entityConfig
	 */
	protected function _defaults($entityName, $entityConfig, $related = false)
	{
		if(in_array($entityName, $this->processedEntities))
		{
			return;
		}
		$defaults = zbase_data_get($entityConfig, 'data.defaults', []);
		$factory = zbase_data_get($entityConfig, 'data.factory.enable', false);
		if(empty($factory))
		{
			$this->processedEntities[] = $entityName;
		}
		if(!empty($defaults))
		{
			$tableName = zbase_data_get($entityConfig, 'table.name', null);
			$primaryKey = zbase_data_get($entityConfig, 'table.primaryKey', null);
			foreach ($defaults as $default)
			{
				$insertedId = \DB::table($tableName)->insertGetId($default);
				if(empty($related))
				{
					if(!empty($insertedId))
					{
						$default[$primaryKey] = $insertedId;
						$relations = zbase_data_get($entityConfig, 'relations', []);
						if(!empty($relations))
						{
							$this->_relations($relations, $default);
						}
					}
				}
			}
		}
	}

	/**
	 * Run factory for an entity
	 * @param string $entityName
	 * @param array $entityConfig
	 */
	protected function _factory($entityName, $entityConfig, $related = false)
	{
		if(in_array($entityName, $this->processedEntities))
		{
			return;
		}
		$this->processedEntities[] = $entityName;
		$factory = zbase_data_get($entityConfig, 'data.factory.enable', false);
		if($factory)
		{
			$factoryDependent = zbase_data_get($entityConfig, 'data.factory.dependent', false);
			if(!empty($factoryDependent))
			{
				return;
			}
			$rows = zbase_data_get($entityConfig, 'data.factory.rows', 5);
			for ($x = 0; $x < $rows; $x++)
			{
				$this->_rows($entityName, $entityConfig, $related);
			}
		}
	}

	/**
	 * Create new Rows
	 *
	 * @param string $entityName Entity Name
	 * @param array $entityConfig Entity Configuration
	 * @param array $foreignData The Foreign/Parent Data
	 */
	protected function _rows($entityName, $entityConfig, $foreignData = [], $related = false)
	{
		$f = [];
		$tableName = zbase_data_get($entityConfig, 'table.name', null);
		$primaryKey = zbase_data_get($entityConfig, 'table.primaryKey', null);
		$columns = zbase_data_get($entityConfig, 'table.columns', []);
		$timestamp = zbase_data_get($entityConfig, 'table.timestamp', []);
		$softDelete = zbase_data_get($entityConfig, 'table.softDelete', []);
		$alphaId = zbase_data_get($entityConfig, 'table.alphaId', false);
		$relations = zbase_data_get($entityConfig, 'relations', []);
		$now = \Carbon\Carbon::now();
		foreach ($columns as $columnName => $column)
		{
			if($columnName != $primaryKey)
			{
				$columnModel = zbase_data_column($columnName, $column);
				$f[$columnName] = $columnModel->faker();
			}
		}
		if($timestamp)
		{
			$f['created_at'] = $now;
			$f['updated_at'] = $now;
		}
		if($softDelete)
		{
			$f['deleted_at'] = rand(0, 1) == 1 ? $now : null;
		}
		if(!empty($foreignData))
		{
			$f = array_replace($f, $foreignData);
		}
		$entityModel = zbase_entity($entityName);
		if(!empty($entityModel))
		{
			$f = $entityModel->fixDataArray($f, 'insert');
		}
		$insertedId = \DB::table($tableName)->insertGetId($f);
		if(!empty($alphaId))
		{
			\DB::table($tableName)->where($primaryKey, $insertedId)->update(['alpha_id' => alphaId($insertedId, false, true, $tableName)]);
		}
		if(!empty($primaryKey))
		{
			$f[$primaryKey] = $insertedId;
		}
		if(!empty($relations) && empty($related))
		{
			$this->_relations($relations, $f);
		}
	}

	/**
	 * Seed Related tables/models
	 * @param array $relations
	 * @param array $f
	 */
	protected function _relations($relations, $f)
	{
		if(!empty($relations))
		{
			foreach ($relations as $rEntityName => $rEntityConfig)
			{
				$rInverse = !empty($rEntityConfig['inverse']) ? true : false;
				if($rInverse)
				{
					continue;
				}
				$rEntityName = !empty($rEntityConfig['entity']) ? $rEntityConfig['entity'] : $rEntityName;
				$rEntity = zbase_config_get('entity.' . $rEntityName, []);
				if(!empty($rEntity))
				{
					$type = zbase_data_get($rEntityConfig, 'type', []);
					$fData = [];
					if($type == 'onetoone')
					{
						/**
						 * https://laravel.com/docs/5.2/eloquent-relationships#one-to-many
						 */
						$foreignKey = zbase_data_get($rEntityConfig, 'keys.foreign', []);
						$localKey = zbase_data_get($rEntityConfig, 'keys.local', []);
						if(!empty($f[$localKey]))
						{
							$fData[$foreignKey] = $f[$localKey];
						}
						$this->_rows($rEntityName, $rEntity, $fData, true);
					}
					if($type == 'manytomany')
					{
						/**
						 * We need to run factory for the related entity
						 */
						$this->_defaults($rEntityName, $rEntity, true);
						$this->_factory($rEntityName, $rEntity, true);
						/**
						 * https://laravel.com/docs/5.2/eloquent-relationships#many-to-many
						 *
						 * Data will be inserted into the pivot table
						 */
						$pivot = zbase_data_get($rEntityConfig, 'pivot', []);
						$pivotTableName = zbase_config_get('entity.' . $pivot . '.table.name', []);

						/**
						 * the foreign key name of the model on which you are defining the relationship
						 */
						$localKey = zbase_data_get($rEntityConfig, 'keys.foreign', []);
						/**
						 * the foreign key name of the model that you are joining to
						 * We will select random data from this entity
						 */
						$foreignKey = zbase_data_get($rEntityConfig, 'keys.local', []);
						$fTableName = zbase_data_get($rEntity, 'table.name', null);
						// dd($foreignKey, $fTableName);
						$foreignTwoValue = collect(\DB::table($fTableName)->lists($foreignKey))->random();
						\DB::table($pivotTableName)->insert([$localKey => $f[$localKey], $foreignKey => $foreignTwoValue]);
					}
				}
			}
		}
	}

}