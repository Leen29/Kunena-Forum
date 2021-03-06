<?php
/**
 * Kunena Component
 *
 * @package       Kunena.Framework
 * @subpackage    User
 *
 * @copyright     Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license       https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

namespace Kunena\Forum\Libraries\User;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject as parentAlias;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\ExecutionFailureException;
use Kunena\Forum\Libraries\Date\KunenaDate;
use Kunena\Forum\Libraries\Error\KunenaError;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use stdClass;
use function defined;

/**
 * Class \Kunena\Forum\Libraries\User\Ban
 *
 * @since   Kunena 6.0
 * @property    integer $created_time
 * @property    integer $created_by
 * @property    integer $modified_by
 * @property    integer $userid
 * @property    integer $id
 * @property    array   $comments
 * @property    string  $ip
 * @property    string  $reason_public
 * @property    string  $reason_private
 * @property    integer $modified_time
 * @property    integer $blocked
 * @property    string  $comment
 * @property    integer $expiration
 *
 */
class Ban extends parentAlias
{
	/**
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	const ANY = 0;

	/**
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	const ACTIVE = 1;

	/**
	 * @var     array|Ban[]
	 * @since   Kunena 6.0
	 */
	protected static $_instances = [];

	/**
	 * @var     array
	 * @since   Kunena 6.0
	 */
	protected static $_instancesByUserid = [];

	/**
	 * @var     array
	 * @since   Kunena 6.0
	 */
	protected static $_instancesByIP = [];

	/**
	 * @var     array
	 * @since   Kunena 6.0
	 */
	protected static $_useridcache = [];

	/**
	 * @var     Date|null
	 * @since   Kunena 6.0
	 */
	protected static $_now = null;

	/**
	 * @var     User|null
	 * @since   Kunena 6.0
	 */
	protected static $_my = null;

	/**
	 * @var     DatabaseDriver|null
	 * @since   Kunena 6.0
	 */
	protected $_db = null;

	/**
	 * @var     boolean
	 * @since   Kunena 6.0
	 */
	protected $_exists = false;
	/**
	 * @var null
	 * @since version
	 */
	private $identifier;

	/**
	 * Constructor
	 *
	 * @access  protected
	 *
	 * @param   null  $identifier  identifier
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function __construct($identifier = null)
	{
		if (self::$_now === null)
		{
			self::$_now = new Date;
		}

		if (self::$_my === null)
		{
			self::$_my = Factory::getApplication()->getIdentity();
		}

		// Always load the data -- if item does not exist: fill empty data
		$this->load($identifier);
		$this->_db = Factory::getDBO();

		parent::__construct($identifier);
	}

	/**
	 * Method to load a \Kunena\Forum\Libraries\User\Ban object by ban id
	 *
	 * @access    public
	 *
	 * @param   int  $id  The ban id of the item to load
	 *
	 * @return    boolean True on success
	 *
	 * @since     Kunena 1.6
	 */
	public function load($id)
	{
		// Create the user table object
		$table = $this->getTable();

		// Load the KunenaTableUser object based on the user id
		$exists = $table->load($id);

		$this->bind($table->getProperties());
		$this->_exists = $exists;

		return $exists;
	}

	/**
	 * Method to get the ban table object
	 *
	 * This function uses a static variable to store the table name of the user table to
	 * it instantiates. You can call this function statically to set the table name if
	 * needed.
	 *
	 * @access    public
	 *
	 * @param   string  $type    The user table name to be used
	 * @param   string  $prefix  The user table prefix to be used
	 *
	 * @return    object  The user table object
	 *
	 * @since     Kunena 1.6
	 */
	public function getTable($type = 'Kunena\\Forum\\Libraries\\Tables\\', $prefix = 'KunenaUserBans')
	{
		static $tabletype = null;

		// Set a custom table type is defined
		if ($tabletype === null || $type != $tabletype['name'] || $prefix != $tabletype['prefix'])
		{
			$tabletype['name']   = $type;
			$tabletype['prefix'] = $prefix;
		}

		// Create the user table object
		return Table::getInstance($tabletype['prefix'], $tabletype['name']);
	}

	/**
	 * @param   mixed  $data  data
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	protected function bind($data)
	{
		$this->setProperties($data);
		$this->comments = !empty($this->comments) ? json_decode((string) $this->comments) : [];
		$this->params   = !empty($this->params) ? json_decode($this->params) : [];
	}

	/**
	 * Returns the global \Kunena\Forum\Libraries\User\Ban object, only creating it if it doesn't already exist.
	 *
	 * @access  public
	 *
	 * @param   int  $identifier  The ban object to be loaded
	 *
	 * @return  Ban  The ban object.
	 *
	 * @since   Kunena 1.6
	 */
	public static function getInstance($identifier = null)
	{
		$c = __CLASS__;

		if (intval($identifier) < 1)
		{
			return new $c;
		}

		if (!isset(self::$_instances[$identifier]))
		{
			$instance = new $c($identifier);
			self::storeInstance($instance);
		}

		return isset(self::$_instances[$identifier]) ? self::$_instances[$identifier] : null;
	}

	/**
	 * @param   Ban  $instance  instance
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	private static function storeInstance($instance)
	{
		// Fill userid cache
		self::cacheUserid($instance->userid);
		self::cacheUserid($instance->created_by);
		self::cacheUserid($instance->modified_by);

		foreach ($instance->comments as $comment)
		{
			self::cacheUserid($comment->userid);
		}

		if ($instance->id)
		{
			self::$_instances[$instance->id] = $instance;
		}

		if ($instance->userid && ($instance->isEnabled() || !$instance->id))
		{
			self::$_instancesByUserid[$instance->userid] = $instance;
		}

		if ($instance->ip && ($instance->isEnabled() || !$instance->id))
		{
			self::$_instancesByIP[$instance->ip] = $instance;
		}
	}

	/**
	 * @param   integer  $userid  userid
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	private static function cacheUserid($userid)
	{
		if ($userid > 0)
		{
			self::$_useridcache[$userid] = $userid;
		}
	}

	/**
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 */
	public function isEnabled()
	{
		if ($this->isLifetime())
		{
			return true;
		}

		$expiration = new Date($this->expiration);

		if ($expiration->toUnix() > self::$_now->toUnix())
		{
			return true;
		}

		return false;
	}

	/**
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 */
	public function isLifetime()
	{
		$nullDate = $this->_db->getNullDate() ? $this->_db->quote($this->_db->getNullDate()) : 'NULL';

		return $this->expiration == $nullDate;
	}

	/**
	 * Returns the global \Kunena\Forum\Libraries\User\Ban object, only creating it if it doesn't already exist.
	 *
	 * @access  public
	 *
	 * @param   int   $identifier  The ban object to be loaded
	 * @param   bool  $create      create
	 *
	 * @return  Ban  The ban object.
	 *
	 * @since   Kunena 1.6
	 */
	public static function getInstanceByUserid($identifier = null, $create = false)
	{
		$c = __CLASS__;

		if (intval($identifier) < 1)
		{
			return new $c;
		}

		if (!isset(self::$_instancesByUserid[$identifier]))
		{
			$instance = new $c;
			$instance->loadByUserid($identifier);
			self::storeInstance($instance);
		}

		return $create || !empty(self::$_instancesByUserid[$identifier]->id) ? self::$_instancesByUserid[$identifier] : null;
	}

	/**
	 * Returns the global \Kunena\Forum\Libraries\User\Ban object, only creating it if it doesn't already exist.
	 *
	 * @access  public
	 *
	 * @param   int   $identifier  The ban object to be loaded
	 * @param   bool  $create      create
	 *
	 * @return  Ban The ban object.
	 *
	 * @since   Kunena 1.6
	 */
	public static function getInstanceByIP($identifier = null, $create = false)
	{
		$c = __CLASS__;

		if (empty($identifier))
		{
			return new $c;
		}

		if (!isset(self::$_instancesByIP[$identifier]))
		{
			$instance = new $c;
			$instance->loadByIP($identifier);
			self::storeInstance($instance);
		}

		return $create || !empty(self::$_instancesByIP[$identifier]->id) ? self::$_instancesByIP[$identifier] : null;
	}

	/**
	 * @param   int  $start  start
	 * @param   int  $limit  limit
	 *
	 * @return  array
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public static function getBannedUsers($start = 0, $limit = 50)
	{
		$c        = __CLASS__;
		$db       = Factory::getDBO();
		$now      = new Date;
		$nullDate = $db->getNullDate() ? $db->quote($db->getNullDate()) : 'NULL';

		$query = $db->getQuery(true);
		$query->select($db->quoteName('b.*'))
			->from($db->quoteName('#__kunena_users_banned', 'b'))
			->innerJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('b.userid'))
			->where($db->quoteName('b.expiration') . ' = ' . $nullDate)
			->orWhere($db->quoteName('b.expiration') . ' > ' . $db->quote($now->toSql()))
			->order($db->quoteName('b.created_time') . ' DESC');
		$query->setLimit($limit, $start);
		$db->setQuery($query);

		try
		{
			$results = $db->loadAssocList();
		}
		catch (ExecutionFailureException $e)
		{
			KunenaError::displayDatabaseError($e);
		}

		$list = [];

		foreach ($results as $ban)
		{
			$instance = new $c;
			$instance->bind($ban);
			$instance->_exists = true;
			self::storeInstance($instance);
			$list[$instance->userid] = $instance;
		}

		return $list;
	}

	/**
	 * @param   integer  $userid  userid
	 *
	 * @return  array
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public static function getUserHistory($userid)
	{
		if (!$userid)
		{
			return [];
		}

		$c  = __CLASS__;
		$db = Factory::getDBO();

		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__kunena_users_banned'))
			->where($db->quoteName('userid') . ' = ' . $db->quote($userid))
			->order($db->quoteName('id') . ' DESC');
		$db->setQuery($query);

		try
		{
			$results = $db->loadAssocList();
		}
		catch (ExecutionFailureException $e)
		{
			KunenaError::displayDatabaseError($e);
		}

		$list = [];

		foreach ($results as $ban)
		{
			$instance = new $c;
			$instance->bind($ban);
			$instance->_exists = true;
			self::storeInstance($instance);
			$list[] = $instance;
		}

		return $list;
	}

	/**
	 * @return  KunenaUser
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function getUser()
	{
		return KunenaUserHelper::get((int) $this->userid);
	}

	/**
	 * @return  KunenaUser
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function getCreator()
	{
		return KunenaUserHelper::get((int) $this->created_by);
	}

	/**
	 * @return  KunenaUser
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function getModifier()
	{
		return KunenaUserHelper::get((int) $this->modified_by);
	}

	/**
	 * Return ban creation date.
	 *
	 * @return  KunenaDate
	 *
	 * @since   Kunena 6.0
	 */
	public function getCreationDate()
	{
		return KunenaDate::getInstance($this->created_time);
	}

	/**
	 * Return ban expiration date.
	 *
	 * @return  KunenaDate
	 *
	 * @since   Kunena 6.0
	 */
	public function getExpirationDate()
	{
		return KunenaDate::getInstance($this->expiration);
	}

	/**
	 * Return ban modification date.
	 *
	 * @return  KunenaDate
	 *
	 * @since   Kunena 6.0
	 */
	public function getModificationDate()
	{
		return KunenaDate::getInstance($this->modified_time);
	}

	/**
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 */
	public function exists()
	{
		return $this->_exists;
	}

	/**
	 * Method to load a \Kunena\Forum\Libraries\User\Ban object by user id
	 *
	 * @access  public
	 *
	 * @param   int  $userid  The user id of the user to load
	 * @param   int  $mode    \Kunena\Forum\Libraries\User\Ban::ANY or \Kunena\Forum\Libraries\User\Ban::ACTIVE
	 *
	 * @return  boolean            True on success
	 *
	 * @since   Kunena 1.6
	 */
	public function loadByUserid($userid, $mode = self::ACTIVE)
	{
		// Create the user table object
		$table = $this->getTable();

		// Load the KunenaTableUser object based on the user id
		$exists = $table->loadByUserid($userid, $mode);
		$this->bind($table->getProperties());
		$this->_exists = $exists;

		return $exists;
	}

	/**
	 * Method to load a \Kunena\Forum\Libraries\User\Ban object by user id
	 *
	 * @access  public
	 *
	 * @param   string  $ip    ip
	 * @param   int     $mode  \Kunena\Forum\Libraries\User\Ban::ANY or \Kunena\Forum\Libraries\User\Ban::ACTIVE
	 *
	 * @return  boolean  True on success
	 *
	 * @since   Kunena 1.6
	 */
	public function loadByIP($ip, $mode = self::ACTIVE)
	{
		// Create the user table object
		$table = $this->getTable();

		// Load the KunenaTableUser object based on the user id
		$exists = $table->loadByIP($ip, $mode);
		$this->bind($table->getProperties());
		$this->_exists = $exists;

		return $exists;
	}

	/**
	 * @param   null  $public   public
	 * @param   null  $private  private
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	public function setReason($public = null, $private = null)
	{
		$set = false;

		if ($public !== null && $public != $this->reason_public)
		{
			$this->reason_public = (string) $public;
			$set                 = true;
		}

		if ($private !== null && $private != $this->reason_private)
		{
			$this->reason_private = (string) $private;
			$set                  = true;
		}

		if ($this->_exists && $set)
		{
			$this->modified_time = self::$_now->toSql();
			$this->modified_by   = self::$_my->id;
		}
	}

	/**
	 * @param   null    $userid          userid
	 * @param   null    $ip              ip
	 * @param   int     $block           block
	 * @param   null    $expiration      expiration
	 * @param   string  $reason_private  private
	 * @param   string  $reason_public   public
	 * @param   string  $comment         comment
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	public function ban($userid = null, $ip = null, $block = 0, $expiration = null, $reason_private = '', $reason_public = '', $comment = '')
	{
		$this->userid  = intval($userid) > 0 ? (int) $userid : null;
		$this->ip      = $ip ? (string) $ip : null;
		$this->blocked = (int) $block;
		$this->setExpiration($expiration);
		$this->reason_private = (string) $reason_private;
		$this->reason_public  = (string) $reason_public;
		$this->addComment($comment);
	}

	/**
	 * @param   mixed   $expiration  expiration
	 * @param   string  $comment     comment
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	public function setExpiration($expiration, $comment = '')
	{
		// Cannot change expiration if ban is not enabled
		if (!$this->isEnabled() && $this->id)
		{
			return;
		}

		if (!$expiration || $expiration == '9999-12-31 23:59:59')
		{
			$this->expiration = '9999-12-31 23:59:59';
		}
		else
		{
			$date             = new Date($expiration);
			$this->expiration = $date->toUnix() > self::$_now->toUnix() ? $date->toSql() : self::$_now->toSql();
		}

		if ($this->_exists)
		{
			$this->modified_time = self::$_now->toSql();
			$this->modified_by   = self::$_my->id;
		}

		$this->addComment($comment);
	}

	/**
	 * @param   string  $comment  comment
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	public function addComment($comment)
	{
		if (is_string($comment) && !empty($comment))
		{
			$c                = new stdClass;
			$c->userid        = self::$_my->id;
			$c->time          = self::$_now->toSql();
			$c->comment       = $comment;
			$this->comments[] = $c;
		}
	}

	/**
	 * @param   string  $comment  comment
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	public function unBan($comment = '')
	{
		// Cannot change expiration if ban is not enabled
		if (!$this->isEnabled())
		{
			return;
		}

		$this->expiration    = self::$_now->toSql();
		$this->modified_time = self::$_now->toSql();
		$this->modified_by   = self::$_my->id;
		$this->addComment($comment);
	}

	/**
	 * Method to save the \Kunena\Forum\Libraries\User\Ban object to the database
	 *
	 * @access  public
	 *
	 * @param   boolean  $updateOnly  Save the object only if not a new ban
	 *
	 * @return  boolean True on success
	 *
	 * @since   Kunena 1.6
	 *
	 * @throws  Exception
	 */
	public function save($updateOnly = false)
	{
		try
		{
			$this->canBan();
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}

		$user = Factory::getUser($this->userid);
		$app  = Factory::getApplication();
		$app->logout((int) $this->userid);

		if (!$this->id)
		{
			// If we have new ban, add creation date and user if they do not exist
			if ($this->created_time == '1000-01-01 00:00:00')
			{
				$now                = new Date;
				$this->created_time = $now->toSql();
			}

			if (!$this->created_by)
			{
				$my               = Factory::getApplication()->getIdentity();
				$this->created_by = $my->id;
			}
		}

		// Create the user table object
		$table = $this->getTable();
		$table->bind($this->getProperties());

		// Check and store the object.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Are we creating a new ban
		$isnew = !$this->_exists;

		// If we aren't allowed to create new ban, return
		if ($isnew && $updateOnly)
		{
			return true;
		}

		if ($this->userid)
		{
			// Change user block also in Joomla
			if (!$user)
			{
				$this->setError("User {$this->userid} does not exist!");

				return false;
			}

			$block = 0;

			if ($this->isEnabled())
			{
				$block = $this->blocked;
			}

			if ($user->block != $block)
			{
				$user->block = $block;
				$user->save();
			}

			// Change user state also in #__kunena_users
			$profile         = KunenaFactory::getUser($this->userid);
			$profile->banned = $this->expiration;
			$profile->save(true);
		}

		// Store the ban data in the database
		$result = $table->store();

		if (!$result)
		{
			$this->setError($table->getError());
		}

		// Set the id for the \Kunena\Forum\Libraries\User\Ban object in case we created a new ban.
		if ($result && $isnew)
		{
			$this->load($table->get('id'));
			self::storeInstance($this);
		}

		return $result;
	}

	/**
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function canBan()
	{
		$userid = $this->userid;
		$me     = KunenaUserHelper::getMyself();
		$user   = KunenaUserHelper::get($userid);

		if (!$me->isModerator())
		{
			throw new Exception(Text::_('COM_KUNENA_MODERATION_ERROR_NOT_MODERATOR'));
		}

		if (!$user->exists())
		{
			throw new Exception(Text::_('COM_KUNENA_LIB_USER_BAN_ERROR_NOT_USER', $userid));
		}

		if ($userid == $me->userid)
		{
			throw new Exception(Text::_('COM_KUNENA_LIB_USER_BAN_ERROR_YOURSELF'));
		}

		if ($user->isAdmin())
		{
			throw new Exception(Text::sprintf('COM_KUNENA_LIB_USER_BAN_ERROR_ADMIN', $user->getName()));
		}

		if ($user->isModerator() && !$me->isAdmin())
		{
			throw new Exception(Text::sprintf('COM_KUNENA_LIB_USER_BAN_ERROR_MODERATOR', $user->getName()));
		}

		return true;
	}

	/**
	 * Method to delete the \Kunena\Forum\Libraries\User\Ban object from the database
	 *
	 * @access  public
	 *
	 * @return  boolean  True on success
	 *
	 * @since   Kunena 1.6
	 */
	public function delete()
	{
		// Create the user table object
		$table = $this->getTable();

		$result = $table->delete($this->id);

		if (!$result)
		{
			$this->setError($table->getError());
		}

		return $result;
	}
}
