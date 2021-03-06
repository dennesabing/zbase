<?php

namespace Zbase\Post\Traits;

/**
 * Zbase-Entity Zbase Post Maker
 *
 * @link http://zbase.dennesabing.com
 * @author Dennes B Abing <dennes.b.abing@gmail.com>
 * @license proprietary
 * @copyright Copyright (c) 2016 ClaremontDesign/MadLabs-Dx
 * @file Entity.php
 * @project Zbase
 * @package Zbase/Entity/Traits
 */
use Zbase\Exceptions;
use Zbase\Post\Repository;
use Zbase\Post\PostInterface;
use Zbase\Entity\Laravel\User\User;

trait Post
{

	/**
	 *
	 * @var Repository
	 */
	protected $repository = null;

	/**
	 * The Post Owner
	 * @var User
	 */
	protected $postOwner = null;

	/**
	 * Owner Display Details
	 * @var string
	 */
	protected $ownerDisplayDetails = null;

	/**
	 * Default Rows Per Page
	 * @var integer
	 */
	protected $postRowsPerPage = 10;

	/**
	 * Post Action Map
	 * @var array
	 */
	protected $postActionMap = [];

	/**
	 * Post Options column Optioin
	 * @var type
	 */
	protected $postOptions = [];

	/**
	 * Post Id
	 * @var integer
	 */
	protected $id = null;

	/**
	 * Index of Messages
	 * @var array
	 */
	protected $messages = [];

	/**
	 * The Post Parent
	 * @var PostInterface
	 */
	protected $postParent = null;

	/**
	 * Initialized
	 */
	public function postInit()
	{
		if($this->postFileEnabled())
		{
			if(property_exists($this, 'actionMap'))
			{
				$this->actionMap['upload'] = [
					'color' => 'blue',
					'text' => 'Upload'
				];
			}
			$this->postActionMap['upload'] = [
				'color' => 'blue',
				'text' => 'Upload'
			];
		}
	}

	// <editor-fold defaultstate="collapsed" desc="Events">
	/**
	 * Cast all property values properly
	 *
	 * @return $this
	 */
	public function postCastProperties()
	{
		if($this->postTableIsOptionable())
		{
			if(!empty($this->options) && is_array($this->options))
			{
				$this->options = json_encode($this->options, JSON_UNESCAPED_SLASHES);
			}
			if(!empty($this->options) && is_object($this->options))
			{
				$this->options = json_encode($this->options, JSON_UNESCAPED_SLASHES);
			}
		}
		if($this->postTableIsUserable() && $this->user_id instanceof User)
		{
			$this->user_id = $this->user_id->id();
		}
		return $this;
	}

	/**
	 * Events
	 */
	protected static function boot()
	{
		parent::boot();
		static::saved(function($post) {
			$post->clearPostCacheById();
			$post->clearPostCacheByTableColumns();
		});
		static::creating(function($post) {
			return $post->postCastProperties();
		});
		static::saving(function($post) {
			return $post->postCastProperties();
		});
		static::updating(function($post) {
			return $post->postCastProperties();
		});
		static::deleted(function($post) {
			$post->clearPostCacheById();
			$post->clearPostCacheByTableColumns();
		});
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Messages">

	/**
	 * Force Toast message
	 *
	 * @return void
	 */
	public function postMessageForceToast()
	{
		zbase()->json()->addVariable('_toastit', 1);
	}

	/**
	 * Add a message/alert
	 * @param string $type The type of mesage
	 * @param string $msg the message
	 *
	 * @return $this
	 */
	public function postAddMessage($type, $msg)
	{
		if(!empty($msg))
		{
			if(isset($this->messages[$type]))
			{
				$this->messages[$type][] = $msg;
			}
			else
			{
				$this->messages[$type] = [$msg];
			}
		}
	}

	/**
	 * Return the Messages
	 * @return array
	 */
	public function postMessages()
	{
		return $this->messages;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Setter/Getters/Properties">
	/**
	 * Return a Text to be displayed to the Console
	 * @param type $methodCalled
	 * @return type
	 */
	public function postConsoleDiplayText()
	{
		if(method_exists($this, 'consoleDisplayText'))
		{
			return $this->consoleDisplayText();
		}
		return ' ' . strip_tags($this->postDisplayText() . ' ' . $this->postTypeText() . ' ' . $this->postStatusText());
	}

	/**
	 * Post Owner
	 * @return User
	 */
	public function postOwner()
	{
		if($this->postTableIsUserable())
		{
			if(method_exists($this, 'owner'))
			{
				return $this->postOwner = $this->owner();
			}
			if(is_null($this->postOwner))
			{
				$this->postOwner = false;
				if(!empty($this->user_id))
				{
					$user = zbase_user_byid($this->user_id);
					if($user instanceof User)
					{
						$this->postOwner = $user;
					}
				}
			}
			return $this->postOwner;
		}
		throw new Exceptions\PropertyNotFoundException('Post is not userable. No ownership found in ' . __CLASS__);
	}

	/**
	 * Post Owner Display Details
	 *
	 * @return string
	 */
	public function postOwnerDisplayDetails()
	{
		if($this->postTableIsUserable())
		{
			if(method_exists($this, 'ownerDisplayDetails'))
			{
				return $this->ownerDisplayDetails();
			}
			if(is_null($this->postOwner))
			{
				if(!empty($this->userDisplayName))
				{
					$ownerDetails = [];
					$ownerDetails[] = $this->userDisplayName;
					$ownerDetails[] = $this->userId . '/' . $this->userUsername . '/' . $this->userEmail;
					$roles = json_decode($this->userRoles, true);
					if(!empty($roles))
					{
						$userRoles = [];
						foreach ($roles as $role)
						{
							$userRoles[] = ucfirst($role);
						}
						$ownerDetails[] = implode(',', $userRoles);
					}
					$ownerDetails[] = $this->userLocation;
					$this->ownerDisplayDetails = implode('<br />', $ownerDetails);
				}
				else
				{
					$this->ownerDisplayDetails = $this->postOwner()->displayName();
				}
			}
			return $this->ownerDisplayDetails;
		}
		throw new Exceptions\PropertyNotFoundException('Post is not userable. No ownership found in ' . __CLASS__);
	}

	/**
	 * Post Owner Export Details
	 *
	 * @return string
	 */
	public function postOwnerExportDetails()
	{
		if($this->postTableIsUserable())
		{
			if(method_exists($this, 'ownerExportDetails'))
			{
				return $this->ownerExportDetails();
			}
			if(is_null($this->postOwner))
			{
				if(!empty($this->userDisplayName))
				{
					$ownerDetails = [];
					$ownerDetails[] = $this->userDisplayName;
					$ownerDetails[] = $this->userId . '/' . $this->userUsername . '/' . $this->userEmail;
					$roles = json_decode($this->userRoles, true);
					if(!empty($roles))
					{
						$userRoles = [];
						foreach ($roles as $role)
						{
							$userRoles[] = ucfirst($role);
						}
						$ownerDetails[] = implode(',', $userRoles);
					}
					$ownerDetails[] = $this->userLocation;
					$this->ownerDisplayDetails = implode(PHP_EOL, $ownerDetails);
				}
				else
				{
					$this->ownerDisplayDetails = $this->postOwner()->displayDetails();
				}
			}
			return $this->ownerDisplayDetails;
		}
		throw new Exceptions\PropertyNotFoundException('Post is not userable. No ownership found in ' . __CLASS__);
	}

	/**
	 * Set the Owner
	 * @param User $user
	 *
	 * @return $this
	 */
	public function setPostOwner(User $user)
	{
		$this->postOwner = $user;
		return $this;
	}

	/**
	 * Return the Post Owner Id
	 *
	 * @return integer
	 */
	public function postOwnerId()
	{
		if($this->postTableIsUserable())
		{
			if(method_exists($this, 'ownerId'))
			{
				return $this->ownerId();
			}
			return $this->user_id;
		}
		return false;
		// throw new Exceptions\PropertyNotFoundException('Post is not userable. No ownership found in ' . __CLASS__);
	}

	/**
	 * The Display Status Text with colors
	 * Uses Bootstrap
	 * @return string|Html
	 */
	public function postStatusText()
	{
		if($this->postTableIsStatusable())
		{
			if(method_exists($this, 'statusText'))
			{
				return $this->statusText();
			}
			return $this->postStatusTextByValue($this->status);
		}
		return null;
	}

	/**
	 * Post Status Text by Value
	 * @param string|int $status
	 * @return string|HTML
	 */
	public function postStatusTextByValue($status)
	{
		if(property_exists($this, 'statusDisplayConfiguration'))
		{
			if(!empty($this->statusDisplayConfiguration[$status]))
			{
				$text = !empty($this->statusDisplayConfiguration[$status]['text']) ? $this->statusDisplayConfiguration[$status]['text'] : $status;
				$color = !empty($this->statusDisplayConfiguration[$status]['color']) ? $this->statusDisplayConfiguration[$status]['color'] : 'gray';
				$colorMap = [
					'red' => 'danger',
					'yellow' => 'warning',
					'green' => 'success',
					'gray' => 'default',
					'blue' => 'info',
				];
				if(array_key_exists($color, $colorMap))
				{
					$color = $colorMap[$color];
				}
				return '<span class="label label-' . $color . ' postStatusText' . $this->postHtmlId() . '">' . $text . '</span>';
			}
		}
		return $status;
	}

	/**
	 * Return the TypeDisplayConfiguration
	 *
	 * @return array
	 */
	public function postTypeDisplayConfiguration()
	{
		if(method_exists($this, 'typeDisplayConfiguration'))
		{
			return $this->typeDisplayConfiguration();
		}
		return $this->typeDisplayConfiguration;
	}

	/**
	 * Return the StatusDisplayConfiguration
	 *
	 * @return array
	 */
	public function postStatusDisplayConfiguration()
	{
		if(method_exists($this, 'statusDisplayConfiguration'))
		{
			return $this->statusDisplayConfiguration();
		}
		return $this->statusDisplayConfiguration;
	}

	/**
	 * Post Type Text
	 *
	 * @return string
	 */
	public function postTypeText()
	{
		if($this->postTableIsTypeable())
		{
			if(method_exists($this, 'typeText'))
			{
				return $this->typeText();
			}
			if(property_exists($this, 'typeDisplayConfiguration'))
			{
				if(!empty($this->typeDisplayConfiguration[$this->type]))
				{
					$text = !empty($this->typeDisplayConfiguration[$this->type]['text']) ? $this->typeDisplayConfiguration[$this->type]['text'] : $this->type;
					$color = !empty($this->typeDisplayConfiguration[$this->type]['color']) ? $this->typeDisplayConfiguration[$this->type]['color'] : 'info';
					$colorMap = [
						'red' => 'danger',
						'yellow' => 'warning',
						'green' => 'success',
						'gray' => 'default',
						'blue' => 'info',
					];
					if(array_key_exists($color, $colorMap))
					{
						$color = $colorMap[$color];
					}
					return '<span class="label label-' . $color . ' postTypeText' . $this->postHtmlId() . '">' . $text . '</span>';
				}
			}
			return $this->type;
		}
		return null;
	}

	/**
	 * Create a colored labeled text
	 *
	 * @param string|integer $value the value
	 * @param array $displayConfiguration Display configuration
	 * @return string
	 */
	public function postCreateLabeledText($value, $displayConfiguration, $name)
	{
		if(!empty($displayConfiguration[$value]))
		{
			$text = !empty($displayConfiguration[$value]['text']) ? $displayConfiguration[$value]['text'] : $value;
			$color = !empty($displayConfiguration[$value]['color']) ? $displayConfiguration[$value]['color'] : 'gray';
			$colorMap = [
				'red' => 'danger',
				'yellow' => 'warning',
				'green' => 'success',
				'gray' => 'default',
				'blue' => 'info',
			];
			if(array_key_exists($color, $colorMap))
			{
				$color = $colorMap[$color];
			}
			return '<span class="label label-' . $color . ' post' . ucfirst($name) . 'Text' . $this->postHtmlId() . '">' . $text . '</span>';
		}
	}

	/**
	 * Return a POST to be used as HTML ID
	 *
	 * @return string
	 */
	public function postHtmlId()
	{
		return $this->postTableName() . '_' . $this->postId();
	}

	/**
	 * Return a POST to be used as HTML ID for template
	 *
	 * @return string
	 */
	public function postHtmlTemplateId()
	{
		return $this->postTableName() . '___' . $this->postTablePrimaryKey() . '__';
	}

	/**
	 * Return a POST to be used as HTML ID
	 *
	 * @return string
	 */
	public function postHtmlCommonId()
	{
		return $this->postTableName();
	}

	/**
	 * Return the Post Id
	 *
	 * @return id
	 */
	public function postId()
	{
		if(method_exists($this, 'id'))
		{
			return $this->id = $this->id();
		}
		if(!empty($this->{$this->postTablePrimaryKey()}))
		{
			return $this->{$this->postTablePrimaryKey()};
		}
		return 0;
	}

	/**
	 * Text that will be displayed as a title
	 *
	 * @return id
	 */
	public function postTitle()
	{
		if(method_exists($this, 'title'))
		{
			return $this->title = $this->title();
		}
		return $this->postDisplayText();
	}

	/**
	 * Text that will be displayed as a Label
	 * 	Used usually on Form Select options or label
	 *  Or anything that we called "label"
	 *
	 * @return string
	 */
	public function postDisplayLabel()
	{
		if(method_exists($this, 'displayLabel'))
		{
			return $this->displayLabel();
		}
		return $this->postTitle();
	}

	/**
	 * Id that will be displayed to the User
	 *
	 * @return integer
	 */
	public function postDisplayId()
	{
		if(method_exists($this, 'displayId'))
		{
			return $this->displayId();
		}
		return $this->postId();
	}

	/**
	 * Text that will be displayed to the User
	 *
	 * @return string
	 */
	public function postDisplayText()
	{
		if(method_exists($this, 'displayText'))
		{
			return $this->displayText();
		}
		return $this->postCommonName() . '#' . $this->postId();
	}

	/**
	 * Return this Post common Name
	 * like, if these are articles, then: Articles, or News
	 *
	 * @param boolean $plural
	 * @return string
	 */
	public function postCommonName($plural = false)
	{
		$funcName = 'commonName';
		if(!empty($plural))
		{
			$funcName = 'commonNamePlural';
		}
		if(method_exists($this, $funcName))
		{
			return $this->{$funcName}();
		}
		if(property_exists($this, $funcName))
		{
			return $this->{$funcName};
		}
		if(!empty($plural))
		{
			return ucfirst(zbase_string_camel_case($this->postTableName())) . '_s';
		}
		return ucfirst(zbase_string_camel_case($this->postTableName()));
	}

	/**
	 * Return an array that will be inserted in the option
	 * of some other entities/tables like notify/logs or anything
	 *
	 * @return array
	 */
	public function postToColumnOption()
	{
		if(method_exists($this, 'toColumnOption'))
		{
			return $this->toColumnOption();
		}
		return [
			$this->postTablePrimaryKey() => $this->postId(),
			$this->postTablePrimaryKey() . '_entity' => $this->postTableName()
		];
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Manipulations">
	/**
	 * Fill this Object based on the given Assoc Array
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function postObjectInitProperties($data, $in = true)
	{
		if(method_exists($this, 'objectInitProperties'))
		{
			$this->objectInitProperties($data, $in);
		}
		else
		{
			$columns = $this->postTableColumns();
			foreach ($data as $k => $v)
			{
				if(in_array($k, $columns))
				{
					if($v instanceof User)
					{
						$this->{$k} = $v->id();
					}
					else
					{
						$this->{$k} = $v;
					}
				}
			}
		}
	}

	/**
	 * Create a new row
	 * @param array $data
	 */
	public function postRowCreate($data)
	{
		if(!empty($data))
		{
			if($this->postTableIsTimestampable())
			{
				if(empty($data['created_at']))
				{
					$data['created_at'] = zbase_date_now();
					$data['updated_at'] = zbase_date_now();
				}
			}
			if(method_exists($this, 'rowCreate'))
			{
				return $this->rowCreate($data);
			}
			return $this->insert($data);
		}
	}

	/**
	 * Update Row
	 * @param array $data
	 */
	public function postRowUpdate($data)
	{
		if(!empty($data))
		{
			if($this->postTableIsTimestampable())
			{
				if(empty($data['updated_at']))
				{
					$data['updated_at'] = zbase_date_now();
				}
			}
			if(method_exists($this, 'rowUpdate'))
			{
				return $this->rowUpdate($data);
			}
			return $this->fill($data)->save();
		}
	}

	/**
	 * Update Row
	 * @param array $data
	 */
	public function postRowDelete()
	{
		if(method_exists($this, 'rowDelete'))
		{
			return $this->rowDelete();
		}
		return $this->delete();
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Widget: DatatableQueries">
	/**
	 * Datatable Filters
	 * @param array $filters
	 * @param WidgetInterface $datatable
	 *
	 * @return boolean
	 */
	public function postDatatableQueryFilters($filters, $datatable)
	{
		$queryFilters = $this->postQueryFilters($filters, $datatable);
		if($datatable->isSearchable() && $datatable->isSearching())
		{
			$queryFilters = array_replace_recursive($queryFilters, $this->postSearchQueryFilters($datatable->getSearchKeyword()));
		}
		if($datatable->isExportable() && $datatable->isExporting())
		{
			$queryFilters = array_replace_recursive($queryFilters, $this->postExportQueryFilters($datatable->exportFilters()));
		}
		if(method_exists($this, 'datatableQueryFilters'))
		{
			return $this->datatableQueryFilters($filters, $queryFilters, $datatable);
		}
		return $queryFilters;
	}

	/**
	 * DataTable selected columns
	 * @param WidgetInterface $datatable
	 *
	 * @return array
	 */
	public function postDatatableQuerySelects($datatable)
	{
		$querySelects = $this->postQuerySelects($datatable);
		if(method_exists($this, 'datatableQuerySelects'))
		{
			return $this->datatableQuerySelects($querySelects, $datatable);
		}
		return $querySelects;
	}

	/**
	 * Return the Joinable Tables
	 *
	 * @param WidgetInterface $datatable
	 * @return array
	 */
	public function postDatatableQueryJoins($datatable)
	{
		$queryJoins = $this->postQueryJoins($datatable);
		if(method_exists($this, 'datatableQueryJoins'))
		{
			return $this->datatableQueryJoins($queryJoins, $datatable);
		}
		return $queryJoins;
	}

	/**
	 * Query Sorting
	 * @param array $sorting
	 * @param WidgetInterface $datatable
	 *
	 * @return array
	 */
	public function postDatatableQuerySorting($sorting, $datatable)
	{
		$tableName = $this->postTableName();
		$querySorting = $this->postQuerySorting($datatable);
		if(method_exists($this, 'datatableQuerySorting'))
		{
			return $this->datatableQuerySorting($sorting, $querySorting, $datatable);
		}
		return $querySorting;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="HTMLS Generators">
	/**
	 * Returnable Jsons
	 * @param type $method
	 * @param type $action
	 * @param type $data
	 * @param type $widget
	 * @return type
	 */
	public function postReturnableJson($method, $action, $data, $widget)
	{
		if(method_exists($this, 'returnableJson'))
		{
			return $this->returnableJson($method, $action, $data, $widget);
		}
		$action = zbase_string_camel_case($method . '_' . $action);
		$postHtmlId = $this->postHtmlId();
		zbase()->json()->setVariable('_html_selector_replace', ['#postMainContentWrapper' . $postHtmlId => $this->postHtmlContent()], true);
		$widget->getModule()->pageProperties($action);
		$this->postPageProperties($widget);
	}

	/**
	 * Returnable Jsons Without the PageProperties
	 * @param type $method
	 * @param type $action
	 * @param type $data
	 * @param type $widget
	 * @return type
	 */
	public function postReturnableJsonWithoutPageProperties($method, $action, $data, $widget)
	{
		if(method_exists($this, 'returnableJsonWithOutPageProperties'))
		{
			return $this->returnableJson($method, $action, $data, $widget);
		}
		$action = zbase_string_camel_case($method . '_' . $action);
		$postHtmlId = $this->postHtmlId();
		zbase()->json()->setVariable('_html_selector_replace', ['#postMainContentWrapper' . $postHtmlId => $this->postHtmlContent()], true);
		zbase()->json()->setVariable('_html_selector_replace', ['.postStatusText' . $postHtmlId => $this->postStatusText()], true);
		$widget->getModule()->pageProperties($action);
	}

	/**
	 * Javascript actiions
	 */
	public function postHtmlContent()
	{
		if(method_exists($this, 'htmlContent'))
		{
			return $this->htmlContent();
		}
		return zbase_widget($this->postModuleName() . '-view', [], true)->render();
	}

	/**
	 * Ccheck if Action is valie
	 * 	This is the Action that is mapped int he actionMap.
	 * 		Actions that is intended to create button
	 * 		Action like: cancel, process, complete, disable, enable, update, new, delete, restore, ddelete
	 * @return boolean
	 */
	public function postCheckAction($action)
	{
		$postActionMap = $this->postActionMap;
		if(property_exists($this, 'actionMap'))
		{
			$postActionMap = $this->actionMap;
		}
		if(!empty($postActionMap[$action]))
		{
			return true;
		}
		return false;
	}

	/**
	 * Create Action Button
	 *
	 * @return string|HTML
	 */
	public function postCreateActionButton($action, $options = [])
	{
		if(!$this->postCheckAction($action))
		{
			throw new \Zbase\Exceptions\ConfigNotFoundException('Action ' . $action . ' not found in the actionMap.' . __CLASS__);
		}
		if(method_exists($this, 'createActionButton'))
		{
			return $this->createActionButton($action, $options);
		}
		$postHtmlId = $this->postHtmlId();
		$postActionMap = $this->postActionMap;
		$color = 'default';
		if(property_exists($this, 'actionMap'))
		{
			$postActionMap = $this->actionMap;
		}
		$attr = [];
		$color = !empty($postActionMap[$action]['color']) ? $postActionMap[$action]['color'] : $color;
		$label = !empty($postActionMap[$action]['text']) ? $postActionMap[$action]['text'] : $action;
		$btnClass = !empty($postActionMap[$action]['btnClass']) ? ' ' . $postActionMap[$action]['btnClass'] . ' ' : '';
		return '<a class="btn ' . $color . ' btn btnPost' . ucfirst($action) . ' btnPost' . $postHtmlId . $btnClass . '" '
				. 'id="btnPost' . ucfirst($action) . '' . $postHtmlId . '" href="#" title="' . $label . '" ' . implode(' ', $attr) . '>' . ucfirst($label) . '</a>';
	}

	/**
	 * Create Action Script
	 * @return string|Javascript
	 */
	public function postCreateActionScript($action, $options = [])
	{
		// zbase_ajax_post
		if(!$this->postCheckAction($action))
		{
			throw new \Zbase\Exceptions\ConfigNotFoundException('Action ' . $action . ' not found in the actionMap.' . __CLASS__);
		}
		if(method_exists($this, 'createActionScript'))
		{
			return $this->createActionScript($action, $options);
		}
		$postHtmlId = $this->postHtmlId();
		if(!empty($options['zbase-ajax-url']))
		{
			if(zbase_is_back())
			{
				$url = zbase_url_from_route('admin.' . $this->postModuleName(), ['action' => $action, 'id' => $this->postId()]);
			}
			else
			{
				$url = zbase_url_from_route($this->postModuleName(), ['action' => $action, 'id' => $this->postId()]);
			}
			return 'jQuery(\'#btnPost' . ucfirst($action) . $postHtmlId . '\').click(function(e){e.preventDefault();zbase_ajax_post(\'' . $url . '\',{},function(){},{})});';
		}
		/**
		 * zbase_attach_toggle_event(e, ele, showEle, hiddenEle, selectorsToHide, showCb, hiddenCb)
		 */
		$script = 'zbase_attach_toggle_event(\'click\', \'#formCancelButton' . ucfirst($action) . $postHtmlId . '\', \'#formPostWrapperAction' . ucfirst($action) . $postHtmlId . '\', \'#postMainWrapperDetails' . $postHtmlId . '\', \'.formPostWrapperAction' . $postHtmlId . '\');';
		return $script . 'zbase_attach_toggle_event(\'click\', \'#btnPost' . ucfirst($action) . $postHtmlId . '\', \'#formPostWrapperAction' . ucfirst($action) . $postHtmlId . '\', \'#postMainWrapperDetails' . $postHtmlId . '\', \'.formPostWrapperAction' . $postHtmlId . '\');';
	}

	/**
	 * Page Meta Properties
	 *
	 * @return void
	 */
	public function postPageProperties($widget = null)
	{
		if(method_exists($this, 'pageProperties'))
		{
			$this->pageProperties($widget);
		}
		else
		{
			$breadcrumbs = zbase_view_breadcrumb_get();
			$breadcrumbs[] = ['label' => $this->postDisplayText(), 'link' => '#'];
			$page['title'] = $this->postDisplayText() . $this->postStatusText();
			$page['headTitle'] = $this->postDisplayText();
			$page['breadcrumbs'] = $breadcrumbs;
			zbase_view_page_details(['page' => $page]);
		}
		$postHtmlId = $this->postHtmlId();
		if(zbase_request_is_ajax())
		{
			$this->postPageOnInnerContentScript();
		}
	}

	/**
	 * SCripts when this content/view is displayed on innerContent via Ajax
	 *
	 * @param boolean $load Data is Loading else we are leaving the page, going back
	 * @return void
	 */
	public function postPageOnInnerContentScript($tag = null)
	{
		if(!empty(zbase_request_input('_innercontent')))
		{
			if(method_exists($this, 'pageOnInnerContentScript'))
			{
				$this->pageOnInnerContentScript($tag);
			}
			$postHtmlId = $this->postHtmlId();
			$away = zbase_request_input('_innercontentaway', false);
			$tableRowId = zbase_request_input('_datatableRow', false);
			$innerContentId = zbase_request_input('_innerContentId', false);
			/**
			 * Currently viewing the data
			 */
			$innerContentView = zbase_request_input('_innerContentView', false);
			if(empty($away))
			{
				if(!empty($innerContentView))
				{
					zbase()->json()->setVariable('_html_selector_replace', ['h3.page-title span.' . $this->postHtmlCommonId() => '<span class="' . $this->postHtmlCommonId() . '">' . zbase()->view()->title() . '<small>' . zbase()->view()->subTitle() . '</small></span>'], true);
				}
				else
				{
					if(!empty($tableRowId) && !empty($innerContentId))
					{
						zbase()->json()->setVariable('_html_selector_remove', ['.' . $this->postHtmlCommonId() => ''], true);
						zbase()->json()->setVariable('_html_selector_append', ['.page-breadcrumb.breadcrumb' => '<li class="' . $this->postHtmlCommonId() . '"><i class="fa fa-angle-right"></i><a title="' . $this->postDisplayText() . '" href="#">' . $this->postDisplayText() . '</a></li>'], true);
						zbase()->json()->setVariable('_html_selector_append', ['h3.page-title' => '<span class="' . $this->postHtmlCommonId() . '">' . zbase()->view()->title() . '<small>' . zbase()->view()->subTitle() . '</small></span>'], true);
						zbase()->json()->setVariable('_html_selector_hide', ['.zbase-page-title' => ''], true);
						$script = 'jQuery(\'#' . $innerContentId . '\').closest(\'.zbase-widget-wrapper-datatable\').hide();jQuery(\'.breadcrumb li\').eq(2).find(\'a\').click(function(e){
								e.preventDefault();
								window.history.pushState(\'\',\'' . zbase()->view()->title() . '\',\'' . zbase_url_previous() . '\');
								jQuery(\'#' . $innerContentId . '\').closest(\'.zbase-widget-wrapper-datatable\').show();
								jQuery(\'#' . $innerContentId . '\').closest(\'.zbase-widget-wrapper-datatable\').siblings().remove();
								jQuery(\'h3.page-title span.' . $this->postHtmlCommonId() . '\').remove();
								jQuery(\'h3.page-title span.zbase-page-title\').show();
								jQuery(\'.breadcrumb li.' . $this->postHtmlCommonId() . '\').remove();
							})';
						zbase()->json()->setVariable('_html_script', [$script], true);
					}
				}
			}
			else
			{
				$script = 'jQuery(\'#' . $innerContentId . '\').closest(\'.zbase-widget-wrapper-datatable\').show();
						jQuery(\'#' . $innerContentId . '\').closest(\'.zbase-widget-wrapper-datatable\').siblings().remove();
						jQuery(\'h3.page-title span.' . $this->postHtmlCommonId() . '\').remove();
						jQuery(\'h3.page-title span.zbase-page-title\').show();
						jQuery(\'.breadcrumb li.' . $this->postHtmlCommonId() . '\').remove();';
				zbase()->json()->setVariable('_html_script', [$script], true);
			}
		}
		else
		{
			zbase()->json()->setVariable('_html_selector_replace', ['.page-breadcrumb.breadcrumb' => zbase_view_render(zbase_view_file('partial.breadcrumb', zbase_section()))], true);
			zbase()->json()->setVariable('_html_selector_html', ['.page-title' => zbase()->view()->title() . '<small>' . zbase()->view()->subTitle() . '</small>'], true);
		}
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Widget: Form">

	/**
	 * REset the Form Values
	 * @param string $action
	 */
	public function postFormReset($action)
	{
		$script = 'zbase_form_reset(\'#zbase-ui-wrapper-form' . ucfirst(strtolower($action)) . $this->postHtmlId() . ' form\');';
		zbase()->json()->setVariable('_html_script', [$script], true);
	}

	/**
	 * Form Select Options
	 * 	The value => label for the select/radio/checkbox Element
	 *
	 * @return array
	 */
	public function postFormMultiOptions($options = [])
	{
		if(method_exists($this, 'formMultiOptions'))
		{
			return $this->formMultiOptions($options);
		}
		$rows = $this->postAll();
		$options = [];
		if(!empty($rows))
		{
			foreach ($rows as $row)
			{
				$options[$row->postId()] = $row->postDisplayLabel();
			}
		}
		return $options;
	}

	/**
	 * The Form Init Values
	 * @return array
	 */
	public function postFormValues()
	{
		if(method_exists($this, 'formValues'))
		{
			return $this->formValues();
		}
		return $this->__toArray();
	}

	/**
	 * Post Log
	 * @param string $log
	 */
	public function postLog($log)
	{

	}

	/**
	 * Widget entity interface.
	 * 	Data should be validated first before passing it here
	 * @param string $method post|get
	 * @param string $action the controller action
	 * @param array $data validated; assoc array
	 * @param Zbase\Widgets\Widget $widget
	 */
	public function widgetController($method, $action, $data, \Zbase\Widgets\Widget $widget)
	{
		return $this->postNodeWidgetController($method, $action, $data, $widget);
	}

	/**
	 * Widget entity interface.
	 * 	Data should be validated first before passing it here
	 * @param string $method post|get
	 * @param string $action the controller action
	 * @param array $data validated; assoc array
	 * @param Zbase\Widgets\Widget $widget
	 * @return boolean
	 */
//	public function nodePostACTIONWidgetController($method, $action, $data, $widget = null)
//	{
//		try
//		{
//			zbase_db_transaction_start();
//			zbase_db_transaction_commit();
//		} catch (\Zbase\Exceptions\RuntimeException $e)
//		{
//			zbase_db_transaction_rollback();
//			zbase_exception_throw($e);
//		}
//		return false;
//	}

	public function postNodeWidgetController($method, $action, $data, \Zbase\Widgets\Widget $widget)
	{
		if(method_exists($widget, 'nodeWidgetController'))
		{
			return $this->nodeWidgetController($method, $action, $data, $widget);
		}
		$methodName = zbase_string_camel_case('node_' . $method . '_' . $action . '_widget_controller');
		if(zbase_is_dev())
		{
			zbase()->json()->addVariable(__METHOD__ . ' : ' . $this->postTableName(), $methodName);
		}
		if(method_exists($this, $methodName))
		{
			return $this->{$methodName}($method, $action, $data, $widget);
		}
		try
		{
			if($action == 'index')
			{
				return;
			}
			if($action == 'create' && strtolower($method) == 'post')
			{
				zbase_db_transaction_start();
				$this->postRowCreate($data);
				$this->postLog($this->postTableName() . '_' . $action);
				zbase_db_transaction_commit();
				return true;
			}
			if($action == 'update' && strtolower($method) == 'post')
			{
				zbase_db_transaction_start();
				$this->postRowUpdate($data);
				$this->postLog($this->postTableName() . '_' . $action);
				zbase_db_transaction_commit();
				return true;
			}
			if($action == 'upload' && strtolower($method) == 'post')
			{
				zbase_db_transaction_start();
				$options = [
					'postObject' => $this,
					'upload_dir' => $this->postFileUploadFolder(),
					'image_versions' => false,
					'script_url' => $this->postFileUrl()
				];
				$uploadHandler = new \Zbase\Utility\FileUploadHandler($options);
				$this->postLog($this->postTableName() . '_' . $action);
				zbase()->json()->addVariable('_postHtmlId', $this->postHtmlId());
				zbase_db_transaction_commit();
				return true;
			}
			if($action == 'delete' && strtolower($method) == 'post')
			{
				zbase_db_transaction_start();
				$this->postRowDelete();
				$this->postLog($this->postTableName() . '_' . $action);
				zbase_db_transaction_commit();
				return true;
			}
		} catch (\Zbase\Exceptions\RuntimeException $e)
		{
			zbase_db_transaction_rollback();
		}
		return false;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Queries">

	/**
	 * Query Sorting
	 * @param WidgetInterface $datatable
	 *
	 * @return array
	 */
	public function postQuerySorting($datatable = false)
	{
		$tableName = $this->postTableName();
		$querySorting = [$tableName . '.created_at' => 'DESC'];
		$requestSortings = zbase_request_input('sorting', zbase_request_query_input('sorting', []));
		if(method_exists($this, 'querySorting'))
		{
			return $this->querySorting($querySorting, $datatable, $requestSortings);
		}
		return $querySorting;
	}

	/**
	 * Return the Joinable Tables
	 *
	 * @param WidgetInterface $datatable
	 * @return array
	 */
	public function postQueryJoins($datatable = false)
	{
		$queryJoins = [];
		$tableName = $this->postTableName();
		if($this->postTableIsUserable())
		{
			$queryJoins[] = [
				'type' => 'join',
				'model' => 'users as users',
				'foreign_key' => $tableName . '.user_id',
				'local_key' => 'users.user_id',
			];
		}
		if($this->postTableIsOtherUserable())
		{
			$queryJoins[] = [
				'type' => 'join',
				'model' => 'users as other_users',
				'foreign_key' => $tableName . '.other_user_id',
				'local_key' => 'users.user_id',
			];
		}
		if($this->postTableIsPostParentable() && !empty($datatable))
		{
			$queryJoins[] = [
				'type' => 'join',
				'model' => $this->postParentTableName() . ' as ' . $this->postParentTableName(),
				'foreign_key' => $tableName . '.' . $this->postParentColumnName(),
				'local_key' => $this->postParentTableName() . '.' . $this->postParentReferenceColumnName(),
			];
		}
		if(method_exists($this, 'queryJoins'))
		{
			return $this->queryJoins($queryJoins, $datatable);
		}
		return $queryJoins;
	}

	/**
	 * Return the Columns to Query
	 * @param WidgetInterface $datatable
	 * @return aray
	 */
	public function postQuerySelects($datatable = null)
	{
		$tableName = $this->postTableName();
		$querySelects = [];
		if($this->postTableIsUserable())
		{
			$querySelects = [
				$tableName . '.*',
				'users.user_id as userId',
				'users.name as userDisplayName',
				'users.email as userEmail',
				'users.username as userUsername',
				'users.roles as userRoles',
				'users.location as userLocation',
				'users.avatar as userAvatar',
			];
		}
		if($this->postTableIsOtherUserable())
		{
			$querySelects[] = 'other_users.user_id as otherUserId';
			$querySelects[] = 'other_users.name as otherUserDisplayName';
			$querySelects[] = 'other_users.email as otherUserEmail';
			$querySelects[] = 'other_users.username as otherUserUsername';
			$querySelects[] = 'other_users.roles as otherUserRoles';
			$querySelects[] = 'other_users.location as otherUserLocation';
			$querySelects[] = 'other_users.avatar as otherUserAvatar';
		}
		if($this->postTableIsPostParentable() && !empty($datatable))
		{
			$querySelects[] = $this->postParentColumnsSelect();
		}
		if(method_exists($this, 'querySelects'))
		{
			return $this->querySelects($querySelects, $datatable);
		}
		return $querySelects;
	}

	/**
	 * Query Filters
	 * @param array $filters
	 * @param WidgetInterface $datatable
	 * @return array
	 */
	public function postQueryFilters($filters, $datatable = null)
	{
		$queryFilters = [];
		$tableName = $this->postTableName();
		if($this->postTableIsStatusable() && !empty($filters['public']))
		{
			$queryFilters[$tableName . '.status'] = self::STATUS_DISPLAY;
		}
		if($this->postTableIsStatusable() && !empty($filters['status']))
		{
			$queryFilters[$tableName . '.status'] = intval($filters['status']);
		}
		if($this->postTableIsUserable())
		{
			/**
			 * Query by the current User
			 */
			if(!empty($filters['currentUser']))
			{
				$queryFilters[$tableName . '.user_id'] = zbase_auth_user()->id();
			}
		}
		/**
		 * If not in datatable, singleRow mode
		 * we test for parent object
		 */
		if($this->postTableIsPostParentable() && empty($datatable) && $this->postParentGet() instanceof PostInterface)
		{
			$queryFilters[$this->postParentTableName() . '.' . $this->postParentColumnName()] = [
				'eq' => [
					'field' => $this->postParentTableName() . '.' . $this->postParentColumnName(),
					'value' => $this->postParentGet()->postId()
				]
			];
		}
		if(method_exists($this, 'queryFilters'))
		{
			return $this->queryFilters($queryFilters, $datatable);
		}
		return $queryFilters;
	}

	/**
	 * Export Filters
	 * @param array $exportFilters
	 * @return array
	 */
	public function postExportQueryFilters($exportFilters)
	{
		if(method_exists($this, 'exportQueryFilters'))
		{
			return $this->exportQueryFilters($exportFilters);
		}
		$exportFilterValueMap = $this->postTableColumns();
		if(property_exists($this, 'exportFilterValueMap'))
		{
			$exportFilterValueMap = $this->exportFilterValueMap;
		}
		$queryFilters = [];
		$tableName = $this->postTableName();
		foreach ($exportFilters as $index => $value)
		{
			if(array_key_exists($index, $exportFilterValueMap))
			{
				$column = zbase_data_get($exportFilterValueMap, $index . '.column', $tableName . '.' . $index);
				$value = zbase_data_get($exportFilterValueMap, $index . '.value.' . $value, $value);
				if(strtolower($value) != 'all')
				{
					$queryFilters[$column] = [
						'eq' => [
							'field' => $column,
							'value' => $value
						]
					];
				}
			}
		}
		return $queryFilters;
	}

	/**
	 * Query by search keyword
	 *
	 * @param string|integer $keyword The keyword
	 * @return boolean
	 */
	public function postSearchQueryFilters($query)
	{
		$queryFilters = [];
		$theQuery = $query;
		$queries = [];
		if(preg_match('/\,/', $query) > 0)
		{
			$queries = explode(',', $query);
		}
		else
		{
			$queries[] = trim($query);
		}
		foreach ($queries as $query)
		{
			$query = trim($query);
			/**
			 * Searching for Name
			 */
			if(preg_match('/name\:/', $query) > 0)
			{
				$queryFilters['users.name'] = function($q) use ($query){
					$name = trim(str_replace('name:', '', $query));
					zbase()->json()->addVariable('__searchKeywords', $name, true);
					return $q->orWhere('users.name', 'LIKE', '%' . $name . '%')
									->orWhere('users.username', 'LIKE', '%' . $name . '%');
					};
			}
			/**
			 * Searching for Email
			 */
			if(preg_match('/\@/', $query) > 0)
			{
				zbase()->json()->addVariable('__searchKeywords', $query, true);
				$queryFilters['users.email'] = [
					'eq' => [
						'field' => 'users.email',
						'value' => $query
					]
				];
			}
			/**
			 * Serachng by Username
			 */
			if(preg_match('/username\:/', $query) > 0)
			{
				$username = str_replace('username:', '', $query);
				zbase()->json()->addVariable('__searchKeywords', $username, true);
				$queryFilters['users.username'] = [
					'like' => [
						'field' => 'users.username',
						'value' => '%' . $username . '%'
					]
				];
			}
			/**
			 * Searching By user Id
			 */
			if(preg_match('/userid\:/', $query) > 0)
			{
				$userId = intval(trim(str_replace('userid:', '', $query)));
				$queryFilters['users.user_id'] = [
					'eq' => [
						'field' => 'users.user_id',
						'value' => $userId
					]
				];
			}
			/**
			 * If numeric, search by Primary key
			 */
			if(is_numeric($query))
			{
				$queryFilters[$this->postTableName() . '.' . $this->postTablePrimaryKey()] = [
					'eq' => [
						'field' => $this->postTableName() . '.' . $this->postTablePrimaryKey(),
						'value' => intval($query)
					]
				];
			}
			if(empty($queryFilters))
			{
				zbase()->json()->addVariable('__searchKeywords', $query, true);
				$queryFilters['users.name'] = function($q) use ($query){
					return $q->orWhere('users.name', 'LIKE', '%' . $query . '%')
									->orWhere('users.email', 'LIKE', '%' . $query . '%')
									->orWhere('users.location', 'LIKE', '%' . $query . '%')
									->orWhere('users.username', 'LIKE', '%' . $query . '%');
					};
			}
		}
		if(method_exists($this, 'searchQueryFilters'))
		{
			return $this->searchQueryFilters($queryFilters, $theQuery);
		}
		return $queryFilters;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Repository">
	/**
	 * Acount all rows
	 *
	 * @return Integer
	 */
	public function countAll()
	{
		$cachePrefix = '';
		if(method_exists($this, 'cachePrefix'))
		{
			$cachePrefix = $this->cachePrefix();
		}
		if(method_exists($this, 'countAllRows'))
		{
			return $this->countAllRows();
		}
		$tableName = $this->postTableName();
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_all_count');
		return zbase_cache($cacheKey, function(){
			return $this->repo()->count([], $this->postQuerySorting(), $this->postQueryJoins(), false);
		}, [$tableName], $this->postCacheMinutes(), ['forceCache' => $this->postCacheForce(), 'driver' => $this->postCacheDriver()]);
	}

	/**
	 * Return all rows
	 *
	 * @return Collection
	 */
	public function postAll($filters = [])
	{
		$cachePrefix = '';
		if(method_exists($this, 'cachePrefix'))
		{
			$cachePrefix = $this->cachePrefix();
		}
		if(method_exists($this, 'allRows'))
		{
			return $this->allRows();
		}
		$tableName = $this->postTableName();
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_all_' . md5($filters));
		return zbase_cache($cacheKey, function() use ($filters){
			return $this->repo()->all($this->postQuerySelects(), $filters, $this->postQuerySorting(), $this->postQueryJoins(), false);
		}, [$tableName], $this->postCacheMinutes(), ['forceCache' => $this->postCacheForce(), 'driver' => $this->postCacheDriver()]);
	}

	/**
	 * Post By ID
	 *
	 * @return PostInterface
	 */
	public function postById($postId)
	{
		$cachePrefix = '';
		if(method_exists($this, 'cachePrefix'))
		{
			$cachePrefix = $this->cachePrefix();
		}
		if(method_exists($this, 'byId'))
		{
			return $this->byId();
		}
		$tableName = $this->postTableName();
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_byId_' . $postId);
		return zbase_cache($cacheKey, function() use ($postId, $cacheKey){
			return $this->repo()->byId($postId);
		}, [$tableName], $this->postCacheMinutes(), ['forceCache' => $this->postCacheForce(), 'driver' => $this->postCacheDriver()]);
	}

	/**
	 * Return a User By attribute
	 * @param type $attribute
	 * @param type $value
	 */
	public function postBy($attribute, $value)
	{
		$cachePrefix = '';
		if(method_exists($this, 'cachePrefix'))
		{
			$cachePrefix = $this->cachePrefix();
		}
		$tableName = $this->postTableName();
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_by_' . $attribute . '_' . $value);
		return zbase_cache($cacheKey, function() use ($attribute, $value){
			return $this->repo()->by($attribute, $value)->first();
		}, [$tableName], $this->postCacheMinutes(), ['forceCache' => $this->postCacheForce(), 'driver' => $this->postCacheDriver()]);
	}

	/**
	 * Return a Items By attribute
	 * @param type $attribute
	 * @param type $value
	 */
	public function postAllBy($attribute, $value)
	{
		$cachePrefix = '';
		if(method_exists($this, 'cachePrefix'))
		{
			$cachePrefix = $this->cachePrefix();
		}
		$tableName = $this->postTableName();
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_allby_' . $attribute . '_' . $value);
		return zbase_cache($cacheKey, function() use ($attribute, $value){
			return $this->repo()->by($attribute, $value);
		}, [$tableName], $this->postCacheMinutes(), ['forceCache' => $this->postCacheForce(), 'driver' => $this->postCacheDriver()]);
	}

	/**
	 * Return the Rows Per Page
	 *
	 * @return integer
	 */
	public function postRowsPerPage()
	{
		if(method_exists($this, 'rowsPerPage'))
		{
			return $this->rowsPerPage();
		}
		if(property_exists($this, 'rowsPerPage'))
		{
			return $this->rowsPerPage;
		}
		return $this->postRowsPerPage;
	}

	/**
	 * @see $this->repository
	 * @return Repository
	 */
	public function repository()
	{
		if(method_exists($this, '_repository'))
		{
			return $this->_repository();
		}
		if(!$this->repository instanceof Repository)
		{
			$this->repository = new Repository($this);
		}
		return $this->repository;
	}

	/**
	 * Proxy to self::repository()
	 * @return Repository
	 */
	public function repo()
	{
		return $this->repository();
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Caching">
	/**
	 * Return the NUmber of minutes to keep the cache
	 *
	 * @return integer
	 */
	public function postCacheMinutes()
	{
		if(method_exists($this, 'cacheMinutes'))
		{
			return $this->cacheMinutes();
		}
		return (60 * 24);
	}

	/**
	 * If to use ForceCaching
	 * @return boolean
	 */
	public function postCacheForce()
	{
		if(method_exists($this, 'cacheForce'))
		{
			return $this->cacheForce();
		}
		return true;
	}

	/**
	 * Return the Cache Driver
	 * @default to file
	 * @return string
	 */
	public function postCacheDriver()
	{
		if(method_exists($this, 'cacheDriver'))
		{
			return $this->cacheDriver();
		}
		return 'file';
	}

	/**
	 * Clear Post Cache
	 *
	 * @return void
	 */
	public function clearPostCache()
	{
		$cachePrefix = '';
		if(method_exists($this, 'cachePrefix'))
		{
			$cachePrefix = $this->cachePrefix();
		}
		if(method_exists($this, 'clearCache'))
		{
			$this->clearCache($cachePrefix);
		}
		$tableName = $this->postTableName();
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_all_');
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_all_count');
		zbase_cache_remove($cacheKey, [$tableName], ['driver' => $this->postCacheDriver()]);
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_all_onlytrashed');
		zbase_cache_remove($cacheKey, [$tableName], ['driver' => $this->postCacheDriver()]);
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_all_withtrashed');
		zbase_cache_remove($cacheKey, [$tableName], ['driver' => $this->postCacheDriver()]);
	}

	/**
	 * Clear entity cache by Id
	 *
	 * @return void
	 */
	public function clearPostCacheById()
	{
		$cachePrefix = '';
		if(method_exists($this, 'cachePrefix'))
		{
			$cachePrefix = $this->cachePrefix();
		}
		if(method_exists($this, 'clearCacheById'))
		{
			$this->clearCacheById($cachePrefix);
		}
		$tableName = $this->postTableName();
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_byId_' . $this->postId());
		zbase_cache_remove($cacheKey, [$tableName], ['driver' => $this->postCacheDriver()]);
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_byId_' . $this->postId() . '_withtrashed');
		zbase_cache_remove($cacheKey, [$tableName], ['driver' => $this->postCacheDriver()]);
		$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_byId_' . $this->postId() . '_onlytrashed');
		zbase_cache_remove($cacheKey, [$tableName], ['driver' => $this->postCacheDriver()]);
	}

	/**
	 * Clear entity cache by Attributes/Value
	 *
	 * @return void
	 */
	public function clearPostCacheByTableColumns()
	{
		$cachePrefix = '';
		if(method_exists($this, 'cachePrefix'))
		{
			$cachePrefix = $this->cachePrefix();
		}
		if(method_exists($this, 'clearCacheByTableColumns'))
		{
			$this->clearCacheByTableColumns($cachePrefix);
		}
		$tableName = $this->postTableName();
		foreach ($this->postTableColumns() as $columnName => $columnConfig)
		{
			$cacheKey = zbase_cache_key(zbase_entity($tableName), $cachePrefix . '_by_' . $columnName . '_' . $this->{$columnName});
			zbase_cache_remove($cacheKey, [$tableName], ['driver' => $this->postCacheDriver()]);
		}
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Admin">
	/**
	 * Return a site Admin
	 *
	 * @return User
	 */
	public function postAdmin()
	{
		if(method_exists($this, 'admin'))
		{
			$user = $this->admin();
			if(!$user instanceof User)
			{
				return $user;
			}
			throw new \Zbase\Exceptions\ConfigNotFoundException('Given admin is not a User. ' . __CLASS__);
		}
		if(property_exists($this, 'adminUserId'))
		{
			if(!empty($this->adminUserId))
			{
				$user = zbase_user_byid($this->adminUserId);
				if(!$user instanceof User)
				{
					return $user;
				}
				throw new \Zbase\Exceptions\ConfigNotFoundException('Given adminUserId is not a User. ' . __CLASS__);
			}
		}
		if(property_exists($this, 'adminUsername'))
		{
			if(!empty($this->adminUsername))
			{
				$user = zbase_user_by('username', $this->adminUsername);
				if(!$user instanceof User)
				{
					return $user;
				}
				throw new \Zbase\Exceptions\ConfigNotFoundException('Given adminUsername is not a User. ' . __CLASS__);
			}
		}
		$admin = zbase_config_get($this->postModuleName() . '.admin.username', zbase_config_get($this->postModuleName() . '.admin.userid', false));
		if(!empty($admin) && is_numeric($admin))
		{
			$user = zbase_user_byid($admin);
			if(!$user instanceof User)
			{
				return $user;
			}
			throw new \Zbase\Exceptions\ConfigNotFoundException('Given admin via config by admin.userid is not a User. ' . __CLASS__);
		}
		if(!empty($admin))
		{
			$user = zbase_user_by('username', $admin);
			if(!$user instanceof User)
			{
				return $user;
			}
			throw new \Zbase\Exceptions\ConfigNotFoundException('Given admin via config by admin.username is not a User. ' . __CLASS__);
		}

		/**
		 * All else, return the default admin
		 */
		return zbase_user_by('username', 'adminx');
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="TableMigrations and Setup">
	/**
	 * Return the Table Primary Key
	 *
	 * @return string
	 */
	public function postTablePrimaryKey()
	{
		if(property_exists($this, 'primaryKey'))
		{
			if(!is_null($this->primaryKey))
			{
				return $this->primaryKey;
			}
		}
		throw new Exceptions\PropertyNotFoundException('$primaryKey Property not found or is empty in ' . __CLASS__);
	}

	/**
	 * The Module Name
	 *
	 * @return string
	 */
	public function postModuleName()
	{
		if(method_exists($this, 'moduleName'))
		{
			return $this->moduleName();
		}
		if(property_exists($this, 'moduleName'))
		{
			return $this->moduleName;
		}
		return $this->postTableName();
	}

	/**
	 * Return Table Prefix
	 * @return string
	 */
	public function postTablePrefix()
	{
		return \DB::getTablePrefix();
	}

	/**
	 * REturn the Post Table/Entity Name
	 *
	 * @return string
	 */
	public function postTableName()
	{
		if(property_exists($this, 'table'))
		{
			if(!is_null($this->table))
			{
				return $this->table;
			}
		}
		throw new Exceptions\PropertyNotFoundException('$table Property not found or is empty in ' . __CLASS__);
	}

	/**
	 * REturn the Post Table Description
	 *
	 * @return string
	 */
	public function postTableDescription()
	{
		if(property_exists($this, 'tableDescription'))
		{
			return $this->tableDescription;
		}
		return null;
	}

	/**
	 * Return the Table Columns based on the DB and not this class configuration
	 *
	 * @return array
	 */
	public function postTableColumns()
	{
		return \DB::getSchemaBuilder()->getColumnListing($this->postTableName());
	}

	/**
	 * Table Configuration
	 *
	 * @param array $postTableConfiguration
	 * @return string
	 */
	public function postTableConfigurations(array $postTableConfiguration)
	{
		$postTableConfiguration['table'] = [
			'name' => $this->postTableName(),
			'primaryKey' => $this->postTablePrimaryKey(),
			'description' => $this->postTableDescription()
		];
		if($this->postTableIsOptionable())
		{
			$postTableConfiguration['table']['optionable'] = true;
		}
		if($this->postTableIsTimestampable())
		{
			$postTableConfiguration['table']['timestamp'] = true;
		}
		if($this->postTableIsSluggable())
		{
			$postTableConfiguration['table']['sluggable'] = true;
		}
		if($this->postTableIsAlphable())
		{
			$postTableConfiguration['table']['alphable'] = true;
		}
		if($this->postTableIsUserable())
		{
			$postTableConfiguration['table']['userable'] = true;
		}
		if($this->postTableIsOrderable())
		{
			$postTableConfiguration['table']['orderable'] = true;
		}
		if($this->postTableIsStatusable())
		{
			$postTableConfiguration['table']['statusable'] = true;
		}
		if($this->postTableIsSoftableDelete())
		{
			$postTableConfiguration['table']['softDelete'] = true;
		}
		if($this->postTableIsPostParentable())
		{
			/**
			 * @TODO see migration if this is integrated
			 */
			$postTableConfiguration['table']['postParentable'] = true;
			$parentColumnName = $this->postParentColumnName();
			$parentReferenceColumnName = $this->postParentReferenceColumnName();
			$postTableConfiguration['table']['postParentable']['column'] = $parentColumnName;
			$postTableConfiguration['table']['postParentable']['referenceColumn'] = $parentReferenceColumnName;
		}
		if($this->postTableIsIpAddressable())
		{
			$postTableConfiguration['table']['ipAddress'] = true;
		}
		if($this->postTableIsRemarkable())
		{
			$postTableConfiguration['table']['remarkable'] = true;
		}
		if($this->postTableIsRoleable())
		{
			$postTableConfiguration['table']['roleable'] = true;
		}
		if($this->postTableIsTypeable())
		{
			$postTableConfiguration['table']['typeable'] = true;
		}
		if($this->postTableIsAddedable())
		{
			$postTableConfiguration['table']['addedable'] = true;
		}
		if($this->postTableIsOtherUserable())
		{
			$postTableConfiguration['table']['otherUserable'] = true;
		}
		if($this->postTableIsHashable())
		{
			$postTableConfiguration['table']['hashable'] = true;
		}
		return $postTableConfiguration;
	}

	/**
	 * Table Column Configuration
	 * @param type $columns
	 */
	public function postTableColumnsConfiguration($columns)
	{
		if(method_exists($this, 'tableColumnsConfiguration'))
		{
			return $this->tableColumnsConfiguration($columns);
		}
		return $columns;
	}

	/**
	 * Return a table configuration
	 * @param type $index
	 */
	public function postTableConfiguration($property)
	{
		if(property_exists($this, 'tableConfiguration'))
		{
			if(is_array($this->tableConfiguration) && array_key_exists($property, $this->tableConfiguration))
			{
				return $this->tableConfiguration[$property];
			}
		}
		return false;
	}

	/**
	 * Table has column type
	 * @return boolean
	 */
	public function postTableIsTypeable()
	{
		if(method_exists($this, 'tableIsTypeable'))
		{
			return $this->tableIsTypeable();
		}
		return $this->postTableConfiguration('typeable');
	}

	/**
	 * Table has column hash
	 * @return boolean
	 */
	public function postTableIsHashable()
	{
		if(method_exists($this, 'tableIsHashable'))
		{
			return $this->tableIsHashable();
		}
		return $this->postTableConfiguration('hashable');
	}

	/**
	 * Table has column type
	 * @return boolean
	 */
	public function postTableIsIpAddressable()
	{
		if(method_exists($this, 'tableIsIpAddressable'))
		{
			return $this->tableIsIpAddressable();
		}
		return $this->postTableConfiguration('ipAddressable');
	}

	/**
	 * Table has column type
	 * @return boolean
	 */
	public function postTableIsOtherUserable()
	{
		if(method_exists($this, 'tableIsOtherUserable'))
		{
			return $this->tableIsOtherUserable();
		}
		return $this->postTableConfiguration('otherUserable');
	}

	/**
	 * Table has column remarks
	 * @return boolean
	 */
	public function postTableIsRemarkable()
	{
		if(method_exists($this, 'tableIsRemarkable'))
		{
			return $this->tableIsRemarkable();
		}
		return $this->postTableConfiguration('remarkable');
	}

	/**
	 * Table has column post_id as a reference to the Parent Table
	 * @return boolean
	 */
	public function postTableIsPostParentable()
	{
		if(method_exists($this, 'tableIsPostParentable'))
		{
			return $this->tableIsPostParentable();
		}
		return $this->postTableConfiguration('postParentable');
	}

	/**
	 * Table has column position
	 * @return boolean
	 */
	public function postTableIsOrderable()
	{
		if(method_exists($this, 'tableIsOrderable'))
		{
			return $this->tableIsOrderable();
		}
		return $this->postTableConfiguration('orderable');
	}

	/**
	 * Table has column status integer
	 * @return boolean
	 */
	public function postTableIsStatusable()
	{
		if(method_exists($this, 'tableIsStatusable'))
		{
			return $this->tableIsStatusable();
		}
		return $this->postTableConfiguration('statusable');
	}

	/**
	 * Table has column status integer
	 * @return boolean
	 */
	public function postTableIsSoftableDelete()
	{
		if(method_exists($this, 'tableIsSoftableDelete'))
		{
			return $this->tableIsSoftableDelete();
		}
		return $this->postTableConfiguration('softableDelete');
	}

	/**
	 * Table has column options
	 * @return boolean
	 */
	public function postTableIsOptionable()
	{
		if(method_exists($this, 'tableIsOptionable'))
		{
			return $this->tableIsOptionable();
		}
		return $this->postTableConfiguration('optionable');
	}

	/**
	 * Table has column created_at and updated_at
	 * @return boolean
	 */
	public function postTableIsTimestampable()
	{
		if(method_exists($this, 'tableIsTimestampable'))
		{
			return $this->tableIsTimestampable();
		}
		return $this->postTableConfiguration('timestampable');
	}

	/**
	 * Table has column alpha_id
	 * @return boolean
	 */
	public function postTableIsAlphable()
	{
		if(method_exists($this, 'tableIsAlphable'))
		{
			return $this->tableIsAlphable();
		}
		return $this->postTableConfiguration('alphable');
	}

	/**
	 * Table has column slug
	 * @return boolean
	 */
	public function postTableIsSluggable()
	{
		if(method_exists($this, 'tableIsSluggable'))
		{
			return $this->tableIsSluggable();
		}
		return $this->postTableConfiguration('sluggable');
	}

	/**
	 * Table has column user_id as the PostOwner
	 * @return boolean
	 */
	public function postTableIsUserable()
	{
		if(method_exists($this, 'tableIsUserable'))
		{
			return $this->tableIsUserable();
		}
		return $this->postTableConfiguration('userable');
	}

	/**
	 * Table has column added_by
	 * @return boolean
	 */
	public function postTableIsAddedable()
	{
		if(method_exists($this, 'tableIsAddedable'))
		{
			return $this->tableIsAddedable();
		}
		return $this->postTableConfiguration('addedable');
	}

	/**
	 * Table has column roles for row CSV roles
	 * @return boolean
	 */
	public function postTableIsRoleable()
	{
		if(method_exists($this, 'tableIsRoleable'))
		{
			return $this->tableIsRoleable();
		}
		return $this->postTableConfiguration('roleable');
	}

	/**
	 * Has SoftDelete
	 *
	 * @return boolean
	 */
	public function hasSoftDelete()
	{
		return $this->postTableIsSoftableDelete();
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Seeding">

	/**
	 * Seeding
	 *
	 * @return
	 */
	public function postTableSeeder()
	{
		if(method_exists($this, 'tableSeeder'))
		{
			return $this->tableSeeder();
		}
		$rowsToCreate = 5;
		for ($x = 0; $x < $rowsToCreate; $x++)
		{
			zbase_entity($this->postTableName())->postRowCreate($this->postTableRowFactory());
		}
	}

	/**
	 * Create a dummy data for seeding
	 * @return array
	 */
	public function postTableRowFactory()
	{
		if(method_exists($this, 'tableRowFactory'))
		{
			return $this->tableRowFactory();
		}
		$data = [];
		return $data;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Post Comments">
	/**
	 *
	 * @TODO Add Note
	 * @param \Zbase\Post\Traits\PostNote $note
	 */
//	public function postAddNote(PostComments $note)
//	{
//
//	}
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Post Images">

	/**
	 * Javascript
	 * @param type $file
	 * @param type $action
	 */
	public function postFileScript($action, $file = null)
	{
		$action = ucfirst($action);
		$postHtmlId = $this->postHtmlId();
		$script = "jQuery('.{$postHtmlId}File{$action}Btn').unbind('click').click(function(e){
			e.preventDefault();
			var ele = jQuery(this);
			ele.hide();
			var btnNo = jQuery('<button type=\"button\" class=\"btn btn-no btn-success btn-sm\">No, don\'t delete</button>');
			var btnYes = jQuery('<button type=\"button\" class=\"btn btn-yes btn-danger btn-sm\">Yes</button>');
			var btnDiv = jQuery('<div>').html(btnNo.outerHtml() + btnYes.outerHtml());
			jQuery(btnDiv).find('.btn-no').click(function () {
				btnDiv.remove();
				ele.show();
			});
			jQuery(btnDiv).find('.btn-yes').click(function () {
				zbase_ajax_post(ele.attr('href'), {}, function(){
					jQuery('.' + ele.attr('data-id')).remove();
					if(jQuery('#{$postHtmlId}FilesWrapper').find('.thumbnail').length == 0)
					{
						jQuery('#{$postHtmlId}FilesWrapper').html('<p class=\"empty\">No images found.</p>');
					}
				}, {loaderTarget: jQuery(this).parent('.{$postHtmlId}FileWrapper')});
			});
			zbase_dom_insert_html(btnDiv, ele, 'insertbefore');
			})";
		return $script;
	}

	/**
	 * Post Upload a new File
	 * @param type $fileIndex
	 */
	public function postUploadFile($fileIndex = 'file', $caption = null)
	{
		try
		{
			if(!$this->postFileEnabled())
			{
				throw new \Zbase\Exceptions\ConfigNotFoundException('Uploading is disabled for ' . $this->postTableName());
			}
			$defaultImageFormat = zbase_config_get('post.files.image.format', 'png');
			$folder = $this->postFileUploadFolder();
			zbase_directory_check($folder, true);
			/**
			 * Check if we have a URL given
			 * @TODO save image that are from remote URL
			 */
			if(preg_match('/http\:/', $fileIndex) || preg_match('/https\:/', $fileIndex))
			{
				return;
			}
			if(!empty($_FILES[$fileIndex]['name']))
			{
				$filename = zbase_file_name_from_file($_FILES[$fileIndex]['name'], time(), true);
				$uploadedFile = zbase_file_upload_image($fileIndex, $folder, $filename, $defaultImageFormat);
			}
			if(!empty($uploadedFile) && zbase_file_exists($uploadedFile))
			{
				/**
				 * No Table, let's use the option column
				 * to save the image link
				 */
				if(!$this->postFileHasTable())
				{
					$file = new \stdClass();
				}
				if(!empty($file))
				{
					$file->is_primary = 0;
					$file->status = 2;
					$file->mimetype = zbase_file_mime_type($uploadedFile);
					$file->size = zbase_file_size($uploadedFile);
					$file->filename = basename($uploadedFile);
					$file->post_id = $this->postId();
					$file->caption = $caption;
					return $this->postFileAdd($file);
				}
			}
		} catch (\Zbase\Exceptions\RuntimeException $e)
		{
			zbase_exception_throw($e);
			return false;
		}
	}

	/**
	 * Url to a file
	 *
	 * @return string
	 */
	public function postFileUrl($file = null, $action = 'view', $options = [])
	{
		if(method_exists($this, 'fileUrl'))
		{
			return $this->fileUrl($file, $action, $options);
		}
		$task = null;
		if(is_object($file) && !empty($file->name))
		{
			$task = $file->name;
		}
		if(is_object($file) && !empty($file->filename))
		{
			$task = $file->filename;
		}
		if(is_string($file))
		{
			$task = $file;
		}
		$action = str_replace('file-', '', $action);
		if($action != 'delete')
		{
//			if(!empty($options['thumbnail']))
//			{
//				$width = 100;
//				$height = 100;
//				$task = '100x100_' . $task;
//			}
//			$width = !empty($options['w']) ? $options['w'] : (!empty($width) ? $width : null);
//			$height = !empty($options['h']) ? $options['h'] : (!empty($height) ? $height : null);
		}
		if(zbase_auth_user()->isAdmin())
		{
			return zbase_url_from_route('admin.file', ['table' => $this->postTableName(), 'action' => $action, 'id' => $this->postId(), 'file' => $task]);
		}
		else
		{
			return zbase_url_from_route('file', ['table' => $this->postTableName(), 'action' => $action, 'id' => $this->postId(), 'file' => $task]);
		}
	}

	/**
	 * Add a new file/image
	 * @param PostFile $file
	 *
	 * @return PostFile
	 */
	public function postFileAdd($file)
	{
		if(method_exists($this, 'fileAdd'))
		{
			return $this->fileAdd($file);
		}
		try
		{
			zbase_db_transaction_start();
			if(!$this->postFileHasTable())
			{
				$file->user_id = zbase_auth_user()->id();
				$files = $this->postFiles();
				if(empty($files))
				{
					$files = [(array) $file];
				}
				else
				{
					$files[] = (array) $file;
				}
				$this->postSetOption('_files', $files);
				$this->save();
				zbase_db_transaction_commit();
				return $file;
			}
		} catch (\Zbase\Exceptions\RuntimeException $e)
		{
			zbase_db_transaction_rollback();
			zbase_exception_throw($e);
			return false;
		}
	}

	/**
	 * Check if file can be deleted
	 *
	 * @return boolean
	 */
	public function postFileCanBeDeleted($file, User $user = null)
	{
		return true;
//		$user = $user instanceof \Zbase\Entity\Laravel\User\User ? $user : zbase_auth_user();
//		if($user->isAdmin())
//		{
//			return true;
//		}
//		if($user->id() == $file->user_id)
//		{
//			return true;
//		}
//		return false;
	}

	/**
	 * Delete/Remove File
	 * @param object|array $file
	 *
	 * return boolean
	 */
	public function postFileDelete($file)
	{
		if(method_exists($this, 'fileDelete'))
		{
			return $this->fileDelete($file);
		}
		try
		{
			$file = (object) $file;
			if(file_exists($this->postFileUploadFolder() . $file->filename))
			{
				zbase_db_transaction_start();
				$files = $this->postFiles();
				$i = 0;
				foreach ($files as $f)
				{
					if($f['filename'] == $file->filename)
					{
						unset($files[$i]);
						unlink($this->postFileUploadFolder() . $file->filename);
						break;
					}
					$i++;
				}
				$this->postSetOption('_files', $files);
				$this->save();
				zbase()->json()->addVariable('post_file_deleted', 1);
				zbase_db_transaction_commit();
				return true;
			}
		} catch (\Zbase\Exceptions\RuntimeException $e)
		{
			zbase_db_transaction_rollback();
			zbase_exception_throw($e);
			return false;
		}
	}

	/**
	 * Serve/View the Image
	 * @param object|array $file
	 * @param integer $width
	 * @param integer $height
	 * @param integer $quality
	 * @return Response
	 */
	public function postFileServe($file, $width = null, $height = null, $quality = 80)
	{
		$file = (object) $file;
		if(file_exists($this->postFileUploadFolder() . $file->filename))
		{
			return \Response::make($this->postFileResize($this->postFileUploadFolder() . $file->filename, $width, $height), 200, array('Content-Type' => $file->mimetype));
		}
	}

	/**
	 * REsize the File
	 * @param type $file
	 * @param type $w
	 * @param type $h
	 * @param type $q
	 */
	public function postFileResize($path, $width, $height, $q = 80)
	{
		if(!class_exists('\Image'))
		{
			$image = zbase_file_serve_image($path, $width, $height, $q);
			if(!empty($image))
			{
				header('Content-type: ' . $image['mime']);
				return \Response::make(readfile($image['src'], $image['size'])->header('Content-Type', $image['mime']));
			}
			return zbase_abort(404);
		}
		return \Image::cache(function($image) use ($width, $height, $path){
					if(empty($width))
					{
						$size = getimagesize($path);
						$width = $size[0];
						$height = $size[1];
					}
					if(!empty($width) && !empty($height))
					{
						return $image->make($path)->resize($width, $height, function($constraint)
						{
									$constraint->upsize();
									$constraint->aspectRatio();
						});
					}
					if(!empty($width) && empty($height))
					{
						return $image->make($path)->resize($width, null, function($constraint)
						{
									$constraint->upsize();
									$constraint->aspectRatio();
						});
					}
					if(empty($width) && !empty($height))
					{
						return $image->make($path)->resize(null, $height, function($constraint)
						{
									$constraint->upsize();
									$constraint->aspectRatio();
						});
					}
					return $image->make($path)->resize($width, $height);
		});
	}

	/**
	 * Return all uploaded files
	 *
	 * @return array
	 */
	public function postFiles()
	{
		if(method_exists($this, 'files'))
		{
			return $this->files();
		}
		return $this->postGetOption('_files');
	}

	/**
	 * Return a file by filename
	 *
	 * return PostFile
	 */
	public function postFileByFilename($filename)
	{
		if(method_exists($this, 'fileByFilename'))
		{
			return $this->fileByFilename($filename);
		}
		$files = $this->postFiles();
		if(!empty($files))
		{
			foreach ($files as $file)
			{
				if($file['filename'] == $filename)
				{
					if(file_exists($this->postFileUploadFolder() . $filename))
					{
						return $file;
					}
				}
			}
		}
		return false;
	}

	public function postFileByFilenameTmp($filename)
	{
		if(method_exists($this, 'fileByFilenameTmp'))
		{
			return $this->fileByFilenameTmp($filename);
		}
		$files = $this->postFiles();
		if(!empty($files))
		{
			foreach ($files as $file)
			{
				if($file['filename'] == $filename)
				{
					if(file_exists($this->postFileUploadFolder() . $filename))
					{
						return $file;
					}
				}
			}
		}
		return false;
	}

	/**
	 * check if we have to upload files
	 *
	 * @return boolean
	 */
	public function postFileEnabled()
	{
		if(method_exists($this, 'fileEnabled'))
		{
			return $this->fileEnabled();
		}
		if(property_exists($this, 'postFileEnabled'))
		{
			return $this->postFileEnabled;
		}
		return false;
	}

	/**
	 * Return the File Upload Folder
	 *
	 * @return string
	 */
	public function postFileUploadFolder($tmp = false)
	{
		if(method_exists($this, 'fileUploadFolder'))
		{
			return $this->fileUploadFolder($tmp);
		}
		return zbase_storage_path() . '/' . zbase_tag() . '/' . $this->postTableName() . '/' . $this->postId() . '/';
	}

	/**
	 * Check that file table is present
	 *
	 * @return boolean
	 */
	public function postFileHasTable()
	{
		return \Schema::hasTable($this->postTableName() . '_files');
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Post Logs">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Post Category">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="PostColumnOptions">
	/**
	 * Set an Option
	 * @param string $key The option key
	 * @param mixed $val The value to set
	 *
	 * @return PostInterface
	 */
	public function postSetOption($key, $val)
	{
		if(!is_array($this->options))
		{
			$options = json_decode($this->options, true);
			if(is_string($options))
			{
				$options = json_decode($options, true);
			}
		}
		else
		{
			$options = $this->options;
		}
		$options[$key] = $val;
		$this->options = $options;
		return $this;
	}

	/**
	 * Return an Option
	 * @param string $key The option key
	 * @param mixed $default The Default value
	 *
	 * @return mixed
	 */
	public function postGetOption($key, $default = null)
	{
		if(!is_array($this->options))
		{
			$options = json_decode($this->options, true);
			if(is_string($options))
			{
				$options = json_decode($options, true);
			}
		}
		else
		{
			$options = $this->options;
		}
		if(isset($options[$key]))
		{
			return $options[$key];
		}
		return $default;
	}

	/**
	 * Remove/Unset an option
	 * @param string $key The option key
	 * @return PostInterface
	 */
	public function postUnsetOption($key)
	{
		if(!is_array($this->options))
		{
			$options = json_decode($this->options, true);
			if(is_string($options))
			{
				$options = json_decode($options, true);
			}
		}
		else
		{
			$options = $this->options;
		}
		if(isset($options[$key]))
		{
			unset($options[$key]);
		}
		$this->options = $options;
		return $this;
	}

	/**
	 * Return the Post Options
	 *
	 * @return array
	 */
	public function postGetOptions()
	{
		if(!is_array($this->options))
		{
			$options = json_decode($this->options, true);
			if(is_string($options))
			{
				$options = json_decode($options, true);
			}
		}
		else
		{
			$options = $this->options;
		}
		return $options;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Access">
	/**
	 * Check if current user has access
	 *
	 * @return boolean
	 */
	public function postUserHasAccess(User $user = null)
	{
		if(method_exists($this, 'userHasAccess'))
		{
			return $this->userHasAccess($user);
		}
		$user = !empty($user) ? $user : zbase_auth_user();
		if($user->id() == $this->postOwnerId())
		{
			return true;
		}
		if($user->isAdmin())
		{
			return true;
		}
		return true;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="ToArray">
	/**
	 * To Array
	 *
	 * @return array
	 */
	public function __toArray()
	{
		if(method_exists($this, 'toArray'))
		{
			return $this->toArray();
		}
		return $this->postToArray();
	}

	/**
	 * Convert this object toArray
	 *
	 * @return array
	 */
	public function postToArray()
	{
		if(method_exists($this, 'getAttributes'))
		{
			return $this->getAttributes();
		}
		return [];
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Parents">
	/**
	 * Set the Post Parent Id
	 * @param PostInterface $parent The Parent ID or the PArent Object
	 * @return PostInterface
	 */
	public function postParentSet(PostInterface $parent)
	{
		if(method_exists($this, 'parentSet'))
		{
			return $this->parentSet($parent);
		}
		$this->postParent = $parent;
		if(property_exists($this, 'parent'))
		{
			$this->parent = $parent;
			return $this;
		}
		return $this;
	}

	/**
	 * Return the Parent Post
	 *
	 * @return PostInterface
	 */
	public function postParentGet()
	{
		if(method_exists($this, 'parentGet'))
		{
			return $this->parentGet();
		}
		return $this->postParent;
	}

	/**
	 * @proxy postParentGet
	 * @return PostInterface
	 */
	public function postParent()
	{
		return $this->postParentGet();
	}

	/**
	 * The Parent/Other table that has reference
	 * @return string
	 */
	public function postParentTableName()
	{
		if(method_exists($this, 'parentTableName'))
		{
			return $this->parentTableName();
		}
		if(property_exists($this, 'parentTableName'))
		{
			return $this->parentTableName;
		}
		return null;
	}

	/**
	 * The Columns to select when selectin the Parents
	 * @return string
	 */
	public function postParentColumnsSelect()
	{
		if(method_exists($this, 'parentColumnsSelect'))
		{
			return $this->parentColumnsSelect();
		}
		if(property_exists($this, 'parentColumnsSelect'))
		{
			return $this->parentColumnsSelect;
		}
		return $this->postParentTableName() . '.*';
	}

	/**
	 * Return the Parent's table Reference Column
	 * @return string
	 */
	public function postParentReferenceColumnName()
	{
		if(method_exists($this, 'parentReferenceColumnName'))
		{
			return $this->parentReferenceColumnName();
		}
		if(property_exists($this, 'parentReferenceColumnName'))
		{
			return $this->parentReferenceColumnName;
		}
		return 'post_id';
	}

	/**
	 * Return the Parent Column Name in the Entity/Table
	 * @return string
	 */
	public function postParentColumnName()
	{
		if(method_exists($this, 'parentColumnName'))
		{
			return $this->parentColumnName();
		}
		if(property_exists($this, 'parentColumnName'))
		{
			return $this->parentColumnName;
		}
		return 'post_id';
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Filters ">

	/**
	 * Return the Data Filters from Datatable
	 * @param Widget $datatable
	 * @return array
	 */
	public function postGetDataFiltersFromDatatable($datatable)
	{
		$filters = [];
		if($datatable instanceof \Zbase\Widgets\Type\Datatable)
		{
			foreach ($datatable->getProcessedColumns() as $column)
			{
				if($column->filterable())
				{
					$filterName = zbase_string_camel_case($column->id());
					$filters[$filterName] = zbase_data_get($column->getAttributes(), 'filter.query', []);
					if(empty($filters[$filterName]['type']))
					{
						$filters[$filterName]['type'] = $column->filterType();
					}
				}
			}
		}
		return $filters;
	}

	/**
	 * REturn Dates from the Date RAnge Filter
	 * @param type $name Name of the Field
	 * @return array
	 */
	public function postGetFilterDateRangeFromPost($name)
	{
		$dateTo = zbase_request_input($name . 'Filter_to', zbase_request_input($name . 'FilterTo', false));
		$dateFrom = zbase_request_input($name . 'Filter_from', zbase_request_input($name . 'FilterFrom', false));

		if(!empty($dateFrom))
		{
			$dateFrom = zbase_date_from_format('d/m/Y', $dateFrom);
		}
		if(!empty($dateTo))
		{
			$dateTo = zbase_date_from_format('d/m/Y', $dateTo);
		}
		if($dateFrom instanceof \DateTime && !$dateTo instanceof \DateTime)
		{
			$dateTo = new \DateTime();
		}

		if(!$dateFrom instanceof \DateTime && $dateTo instanceof \DateTime)
		{
			$dateFrom = zbase_date_from_format('Y-m-d', '2000-01-01');
		}
		return [
			'from' => $dateFrom,
			'to' => $dateTo
		];
	}

	/**
	 * REturn Query Filters based on the Post
	 * @param array $queryFilters
	 * @param type $filters
	 * @return array
	 */
	public function postGetFiltersFromPost($queryFilters, $filters)
	{
		if(!zbase_is_post())
		{
			return $queryFilters;
		}
		foreach ($filters as $filterName => $option)
		{
			$type = !empty($option['type']) ? $option['type'] : 'text';
			$columnName = !empty($option['column']) ? $option['column'] : null;
			$operation = !empty($option['operation']) ? $option['operation'] : 'eq';
			if(!empty($columnName))
			{
				if($type == 'date')
				{
					$dates = $this->postGetFilterDateRangeFromPost($filterName);
					if($dates['from'] instanceof \DateTime && $dates['to'] instanceof \DateTime)
					{
						$queryFilters[$filterName] = [
							'between' => [
								'field' => $columnName,
								'from' => $dates['from']->format('Y-m-d'),
								'to' => $dates['to']->format('Y-m-d')
							]
						];
					}
				}
				else
				{
					$postValue = zbase_request_input($filterName . 'Filter', zbase_request_input($filterName . 'Filter', false));
					if($postValue != '' && $postValue != 'null')
					{
						switch (strtolower($operation))
						{
							case 'like':
								if(is_array($columnName))
								{
									$queryFilters[$filterName] = function($q) use ($postValue, $columnName){
										foreach ($columnName as $column)
										{
											$q->orWhere($column, 'LIKE', '%' . $postValue . '%');
										}
										return $q;
										};
								}
								else
								{
									$queryFilters[$filterName] = [
										$operation => [
											'field' => $columnName,
											'value' => '%' . $postValue . '%',
										]
									];
								}
								break;
							default:
								$queryFilters[$filterName] = [
									$operation => [
										'field' => $columnName,
										'value' => $postValue,
									]
								];
						}
					}
				}
			}
		}
		return $queryFilters;
	}

	// </editor-fold>
}
