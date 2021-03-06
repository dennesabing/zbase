<?php

namespace Zbase\Entity\Laravel\Message;

/**
 * Zbase-UserProfile Entity
 *
 * UserProfile Entity Model
 *
 * @link http://zbase.dennesabing.com
 * @author Dennes B Abing <dennes.b.abing@gmail.com>
 * @license proprietary
 * @copyright Copyright (c) 2016 ClaremontDesign/MadLabs-Dx
 * @file UserProfile.php
 * @project Zbase
 * @package Zbase/Entity/User
 */
use Zbase\Entity\Laravel\Entity as BaseEntity;
use Zbase\Widgets\EntityInterface as WidgetEntityInterface;

class Message extends BaseEntity implements WidgetEntityInterface
{

	/**
	 * Entity name as described in the config
	 * @var string
	 */
	protected $entityName = 'messages';

	/**
	 * The Action Messages
	 * @var array
	 */
	protected $_actionMessages = [];

	/**
	 * The Message REcipient Object
	 * @var Recipient
	 */
	protected $_msgRecipient = null;

//	protected static function boot()
//	{
//		parent::boot();
//	}

	public function id()
	{
		return $this->message_id;
	}

	public function subject()
	{
		return $this->subject;
	}

	public function message()
	{
		return $this->content;
	}

	public function excerpt()
	{
		return substr(0, 100) . '...';
	}

	public function getTimeSent()
	{
		return $this->created_at;
	}

	public function alphaId()
	{
		return $this->recipient_alpha_id;
	}

	public function sender()
	{
		return zbase_user_byid($this->sender_id);
	}

	public function recipient()
	{
		return zbase_user_byid($this->owner_id);
	}

	/**
	 * @return Recipient
	 */
	public function messageRecipient()
	{
		if(!$this->_msgRecipient instanceof Recipient)
		{
			if(!empty($this->message_recipient_id))
			{
				$msgRecipient = new Recipient();
				$this->_msgRecipient = $msgRecipient->repository()->byId($this->message_recipient_id);
			}
		}
		return $this->_msgRecipient;
	}

	/**
	 * Return the Sender
	 * @return String
	 */
	public function senderName()
	{
		if(property_exists($this, 'sender_first_name'))
		{
			return $this->sender_first_name . ' ' . $this->sender_last_name;
		}
		return $this->sender()->displayName();
	}

	/**
	 * Sender Avatar
	 * @return string
	 */
	public function senderAvatarUrl()
	{
		return $this->sender()->profile()->avatarUrl();
	}

	/**
	 * Return the Recipient
	 * @return String
	 */
	public function recipientName()
	{
		if(property_exists($this, 'owner_first_name'))
		{
			return $this->owner_first_name . ' ' . $this->owner_last_name;
		}
		return $this->recipient()->displayName();
	}

	/**
	 * Sender Avatar
	 * @return string
	 */
	public function recipientAvatarUrl()
	{
		return $this->recipient()->profile()->avatarUrl();
	}

	public function readUrl()
	{
		return zbase_url_from_route('messages', ['action' => 'read', 'id' => $this->alphaId()]);
	}

	/**
	 * Read Status
	 * @return boolean
	 */
	public function readStatus()
	{
		return (bool) $this->read_status;
	}

	/**
	 * Reply Status
	 * @return boolean
	 */
	public function replyStatus()
	{
		return (bool) $this->reply_status;
	}

	/**
	 * Starred
	 * @return boolean
	 */
	public function isStarred()
	{
		return (bool) $this->is_starred;
	}

	/**
	 * Is Important
	 * @return boolean
	 */
	public function isImportant()
	{
		return (bool) $this->is_important;
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
	public function widgetController($method, $action, $data, \Zbase\Widgets\Widget $widget)
	{
		if(!empty($this->message_recipient_id))
		{
			$recipient = new Recipient;
			$msgRecipient = $recipient->repository()->byId($this->message_recipient_id);
		}
		if($action == 'read')
		{
			if(empty($msgRecipient->read_status))
			{
				$msgRecipient->read_status = 1;
				$msgRecipient->save();
			}
		}
		if(strtolower($method) == 'post')
		{
			if(!empty($data['msg']))
			{
				$oMessage = $this->fetchByAlphaId($data['msg']);
			}
			if(!empty($oMessage))
			{
				/**
				 * Action is read, but posting, means that it is a reply
				 */
				if($action == 'read' || $action == 'reply')
				{
					$message = $data['content'];
					$subject = 'RE: ' . $oMessage->subject();
					$sender = zbase_auth_user()->id();
					$recipient = $oMessage->sender_id;
					if(!empty($oMessage->node_id) && !empty($oMessage->node_prefix))
					{
						$options['node_id'] = $oMessage->node_id;
						$options['node_prefix'] = $oMessage->node_prefix;
						if(!empty($oMessage->parent_id))
						{
							$options['parent_id'] = $oMessage->parent_id;
						}
						else
						{
							$options['parent_id'] = $oMessage->id();
						}
					}
					$messageObject = zbase_entity($this->entityName, [], true);
					$newMessage = $messageObject->newMessage($message, $subject, $sender, $recipient, $options);
					$msgRecipient->reply_status = 1;
					$msgRecipient->save();
					$this->_actionMessages[$action]['success'][] = _zt('Message sent.', ['%title%' => $newMessage->subject()]);
					return true;
				}
			}
			if($action == 'trash')
			{
				$msgRecipient->trash_status = 1;
				$msgRecipient->save();
				$this->_actionMessages[$action]['success'][] = _zt('Message trashed.', ['%title%' => $this->subject()]);
				return true;
			}
			$this->_actionMessages[$action]['error'][] = _zt('Message reference not found. Kindly check.', ['%title%' => $this->title, '%id%' => $this->id()]);
			return false;
		}
		return true;
	}

	/**
	 * Return a messages based on the Action made
	 * @param boolean $flag
	 * @param string $action create|update|delete|restore|ddelete
	 * @return array
	 */
	public function getActionMessages($action)
	{
		if(!empty($this->_actionMessages[$action]))
		{
			return $this->_actionMessages[$action];
		}
		return [];
	}

	// <editor-fold defaultstate="collapsed" desc="DataTable Widget Query Interface/Methods">
	/**
	 * Sorting Query
	 * @param array $sorting Array of Sorting
	 * @param array $filters Array of Filters
	 * @param array $options some options
	 * @return array
	 */
	public function querySorting($sorting, $filters = [], $options = [])
	{
		$sort = ['messages.created_at' => 'DESC'];
		return $sort;
	}

	/**
	 * Join Query
	 * @param array $filters Array of Filters
	 * @param array $sorting Array of Sorting
	 * @param array $options some options
	 * @return array
	 */
	public function queryJoins($filters, $sorting = [], $options = [])
	{
		$joins = [];
		$joins[] = [
			'type' => 'join',
			'model' => 'messages_recipient as messages_recipient',
			'foreign_key' => 'messages_recipient.message_id',
			'local_key' => 'messages.message_id',
		];
		$joins[] = [
			'type' => 'join',
			'model' => 'users as sender',
			'foreign_key' => 'messages.sender_id',
			'local_key' => 'sender.user_id',
		];
		$joins[] = [
			'type' => 'join',
			'model' => 'users_profile as sender_profile',
			'foreign_key' => 'messages.sender_id',
			'local_key' => 'sender_profile.user_id',
		];
		$joins[] = [
			'type' => 'join',
			'model' => 'users as owner',
			'foreign_key' => 'messages_recipient.user_id',
			'local_key' => 'owner.user_id',
		];
		$joins[] = [
			'type' => 'join',
			'model' => 'users_profile as owner_profile',
			'foreign_key' => 'messages_recipient.user_id',
			'local_key' => 'owner_profile.user_id',
		];
		return $joins;
	}

	/**
	 * REturn selects
	 * @param array $filters
	 * @return array
	 */
	public function querySelects($filters)
	{
		$selects = [];
		$selects[] = 'messages.*';
		$selects[] = 'messages_recipient.*';
		$selects[] = 'messages_recipient.user_id as owner_id';
		$selects[] = 'messages_recipient.alpha_id as recipient_alpha_id';
		$selects[] = 'sender_profile.first_name as sender_first_name';
		$selects[] = 'sender_profile.last_name as sender_last_name';
		$selects[] = 'sender_profile.avatar as sender_avatar';
		$selects[] = 'owner_profile.first_name as owner_first_name';
		$selects[] = 'owner_profile.last_name as owner_last_name';
		$selects[] = 'owner_profile.avatar as owner_avatar';
		return $selects;
	}

	/**
	 * Filter Query
	 * @param array $filters Array of Filters
	 * @param array $sorting Array of Sorting
	 * @param array $options some options
	 * @return array
	 */
	public function queryFilters($filters, $sorting = [], $options = [])
	{
		$queryFilters = [];
		if(!empty($filters))
		{
			if(!empty($filters['alpha']))
			{
				$queryFilters['alpha'] = [
					'eq' => [
						'field' => 'messages_recipient.alpha_id',
						'value' => $filters['alpha']['eq']['value']
					]
				];
			}
			$isPublic = !empty($filters['public']) ? true : false;
			if(!empty($isPublic))
			{
				$queryFilters['status'] = [
					'eq' => [
						'field' => 'messages_recipient.status',
						'value' => 2
					]
				];
			}
			$currentUser = !empty($filters['currentUser']) ? true : false;
			if(!empty($currentUser))
			{
				$queryFilters['user'] = [
					'eq' => [
						'field' => 'messages_recipient.user_id',
						'value' => zbase_auth_user()->id()
					]
				];
			}
		}
		return $queryFilters;
	}

	// </editor-fold>

	/**
	 * Create new Message
	 * @param string $message The MEssage
	 * @param string $subject Subject
	 * @param string $sender UserId or User objcet
	 * @param string $recipient UserId or User objeect
	 * @param array $options
	 * @return \Zbase\Entity\Laravel\Message\Message
	 */
	public function newMessage($message, $subject, $sender, $recipient, $options)
	{
		try
		{
			$this->subject = $subject;
			$this->content = $message;
			if(!empty($options['parent_id']))
			{
				$this->parent_id = $options['parent_id'];
			}
			if(!empty($options['node_id']))
			{
				$this->node_id = $options['node_id'];
			}
			if(!empty($options['node_prefix']))
			{
				$this->node_prefix = $options['node_prefix'];
			}
			if(!$sender instanceof \Zbase\Entity\Laravel\User\User && is_numeric($sender))
			{
				$sender = zbase_user_byid($sender);
			}
			if($sender instanceof \Zbase\Entity\Laravel\User\User)
			{
				$this->sender_id = $sender->id();
			}
			$this->save();
			// For the Recipient
			$this->_msgRecipient($recipient);
			$this->_msgSender($sender);
			return $this;
		} catch (\Zbase\Exceptions\RuntimeException $e)
		{
			if(zbase_is_dev())
			{
				dd($e);
			}
			zbase_abort(503);
		}
	}

	/**
	 *
	 * @param \Zbase\Entity\Laravel\User\User $sender
	 * @return type
	 */
	protected function _msgSender($sender)
	{
		// For the Sender
		$msgSender = new Recipient();
		$msgSender->read_status = 1;
		$msgSender->admin_read_status = 1;
		if($sender instanceof \Zbase\Entity\Laravel\User\User)
		{
			$msgSender->user_id = $sender->id();
		}
		$msgSender->trash_status = 0;
		$msgSender->reply_status = 0;
		$msgSender->admin_read_status = 1;
		$msgSender->status = 2;
		$msgSender->is_in = 0;
		$msgSender->is_out = 1;
		$msgSender->message_id = $this->message_id;
		$msgSender->save();
		return $msgSender;
	}

	/**
	 *
	 * @param type $recipient
	 * @return \Zbase\Entity\Laravel\Message\Recipient
	 */
	protected function _msgRecipient($recipient)
	{
		$msgRecipient = new Recipient();
		if(!$recipient instanceof \Zbase\Entity\Laravel\User\User && is_numeric($recipient))
		{
			$recipient = zbase_user_byid($recipient);
		}
		if($recipient instanceof \Zbase\Entity\Laravel\User\User)
		{
			$msgRecipient->user_id = $recipient->id();
		}
		$msgRecipient->read_status = 0;
		$msgRecipient->admin_read_status = 1;
		$msgRecipient->trash_status = 0;
		$msgRecipient->reply_status = 0;
		$msgRecipient->status = 2;
		$msgRecipient->is_in = 1;
		$msgRecipient->is_out = 0;
		$msgRecipient->message_id = $this->message_id;
		$msgRecipient->save();
		return $msgRecipient;
	}

	/**
	 * Fetch a Row By AlphaId
	 * @param string $alphaId
	 * @return Collection[]
	 */
	public function fetchByAlphaId($alphaId)
	{
		$fil = [];
		$fil['alpha'] = ['eq' => ['value' => $alphaId]];
		$filters = $this->queryFilters($fil);
		$joins = $this->queryJoins($filters);
		$selects = $this->querySelects($filters);
		return $this->repository()->all($selects, $filters, [], $joins)->first();
	}

	// <editor-fold defaultstate="collapsed" desc="Entity Configuration">

	/**
	 * Table Relations
	 * @param array $relations Configuration default data
	 * @return array
	 */
	public static function tableRelations($relations = [])
	{
		$relations = [
			'user' => [
				'entity' => 'user',
				'type' => 'onetomany',
				'class' => [
					'method' => 'sender'
				],
				'keys' => [
					'local' => 'user_id',
					'foreign' => 'sender_id'
				],
			],
		];
		return $relations;
	}

	/**
	 * Return table minimum columns requirement
	 * @param array $columns Some columns
	 * @param array $entity Entity Configuration
	 * @return array
	 */
	public static function tableColumns($columns = [], $entity = [])
	{
		$columns = [
			'sender_id' => [
				'length' => 16,
				'hidden' => false,
				'fillable' => true,
				'type' => 'integer',
				'index' => true,
				'unsigned' => true,
				'comment' => 'Sender ID'
			],
			'node_id' => [
				'length' => 16,
				'hidden' => false,
				'fillable' => true,
				'type' => 'integer',
				'nullable' => true,
				'unsigned' => true,
				'comment' => 'Node ID'
			],
			'node_prefix' => [
				'length' => 255,
				'hidden' => false,
				'fillable' => true,
				'type' => 'string',
				'nullable' => true,
				'index' => true,
				'comment' => 'Node Prefix'
			],
			'subject' => [
				'filterable' => [
					'name' => 'subject',
					'enable' => true
				],
				'sortable' => [
					'name' => 'subject',
					'enable' => true
				],
				'length' => 255,
				'nullable' => true,
				'hidden' => false,
				'fillable' => true,
				'type' => 'string',
				'comment' => 'Subject'
			],
			'content' => [
				'nullable' => true,
				'hidden' => false,
				'fillable' => true,
				'type' => 'text',
				'comment' => 'Content'
			],
			'parent_id' => [
				'filterable' => [
					'name' => 'parentid',
					'enable' => true
				],
				'hidden' => false,
				'fillable' => false,
				'nullable' => true,
				'unsigned' => true,
				'type' => 'integer',
				'index' => true,
				'comment' => 'Parent ID'
			],
		];
		return $columns;
	}

	/**
	 * Table Entity Configuration
	 * @param array $entity Configuration default data
	 * @return array
	 */
	public static function entityConfiguration($entity = [])
	{
		$entity['table'] = [
			'name' => 'messages',
			'primaryKey' => 'message_id',
			'timestamp' => true,
			'alphaId' => false,
			'optionable' => true,
			'description' => 'Messaging',
		];
		return $entity;
	}

	// </editor-fold>
}
