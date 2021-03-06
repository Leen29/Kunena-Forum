<?php
/**
 * @package        EasySocial
 * @copyright      Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * EasySocial is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

namespace Kunena\Forum\Plugin\Kunena\Easysocial;

defined('_JEXEC') or die('Unauthorized Access');

use Exception;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;
use Kunena\Forum\Libraries\Access\Access;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Integration\Activity;
use function defined;

/**
 * @package  Easysocial
 *
 * @since    Kunena 6.0
 */
class KunenaActivityEasySocial extends Activity
{
	/**
	 * @var     null
	 * @since   Kunena 6.0
	 */
	protected $params = null;

	/**
	 * KunenaActivityEasySocial constructor.
	 *
	 * @param   object  $params  params
	 *
	 * @since   Kunena 6.0
	 * @throws  Exception
	 */
	public function __construct($params)
	{
		$this->params = $params;

		parent::__construct();
	}

	/**
	 * @param   string  $message  message
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	public function onAfterPost($message)
	{
		if (StringHelper::strlen($message->message) > $this->params->get('activity_points_limit', 0))
		{
			$this->assignPoints('thread.new');
		}

		if (StringHelper::strlen($message->message) > $this->params->get('activity_badge_limit', 0))
		{
			$this->assignBadge('thread.new', Text::_('PLG_KUNENA_EASYSOCIAL_BADGE_NEW_TITLE'));
		}

		$stream = FD::stream();

		$tmpl = $stream->getTemplate();

		$tmpl->setActor($message->userid, SOCIAL_TYPE_USER);
		$tmpl->setContext($message->thread, 'kunena');
		$tmpl->setVerb('create');
		$tmpl->setAccess('core.view');

		$stream->add($tmpl);
	}

	/**
	 * @param   string  $command  command
	 * @param   null    $target   target
	 *
	 * @return  mixed
	 *
	 * @since   Kunena 6.0
	 */
	public function assignPoints($command, $target = null)
	{
		$user = FD::user($target);

		$points = FD::points();

		return $points->assign($command, 'com_kunena', $user->id);
	}

	/**
	 * @param   string  $command  command
	 * @param   string  $message  message
	 * @param   null    $target   target
	 *
	 * @return  mixed
	 *
	 * @since   Kunena 6.0
	 */
	public function assignBadge($command, $message, $target = null)
	{
		$user  = FD::user($target);
		$badge = FD::badges();

		return $badge->log('com_kunena', $command, $user->id, $user->id);
	}

	/**
	 * After a person replies a topic
	 *
	 * @internal  param $string
	 *
	 * @param   string  $message  message
	 *
	 * @return  void
	 *
	 * @since     1.3
	 *
	 * @access    public
	 *
	 * @throws  Exception
	 */
	public function onAfterReply($message)
	{
		$length = StringHelper::strlen($message->message);

		// Assign points for replying a thread
		if ($length > $this->params->get('activity_points_limit', 0))
		{
			$this->assignPoints('thread.reply');
		}

		// Assign badge for replying to a thread
		if ($length > $this->params->get('activity_badge_limit', 0))
		{
			$this->assignBadge('thread.reply', Text::_('PLG_KUNENA_EASYSOCIAL_BADGE_REPLY_TITLE'));
		}

		$stream = FD::stream();
		$tmpl   = $stream->getTemplate();
		$tmpl->setActor($message->userid, SOCIAL_TYPE_USER);
		$tmpl->setContext($message->id, 'kunena');
		$tmpl->setVerb('reply');
		$tmpl->setAccess('core.view');

		// Add into stream
		$stream->add($tmpl);

		// Get a list of subscribers
		$recipients = $this->getSubscribers($message);

		if (!$recipients)
		{
			return;
		}

		$permalink = Uri::getInstance()->toString(['scheme', 'host', 'port']) . $message->getPermaUrl(null);

		$options = [
			'uid'      => $message->id,
			'actor_id' => $message->userid,
			'title'    => '',
			'type'     => 'post',
			'url'      => $permalink,
			'image'    => '',
		];

		// Add notifications in EasySocial
		FD::notify('post.reply', $recipients, [], $options);
	}

	/**
	 * Get a list of subscribers for a thread
	 *
	 * @internal  param $string
	 *
	 * @param   string  $message  message
	 *
	 * @return   array|boolean
	 *
	 * @since     5.0
	 *
	 * @access    public
	 *
	 * @throws  Exception
	 */
	public function getSubscribers($message)
	{
		$config = KunenaFactory::getConfig();

		if ($message->hold > 1)
		{
			return false;
		}
		elseif ($message->hold == 1)
		{
			$mailsubs   = 0;
			$mailmods   = $config->mailmod >= 0;
			$mailadmins = $config->mailadmin >= 0;
		}
		else
		{
			$mailsubs   = (bool) $config->allowsubscriptions;
			$mailmods   = $config->mailmod >= 1;
			$mailadmins = $config->mailadmin >= 1;
		}

		$once = false;

		if ($mailsubs)
		{
			if (!$message->parent)
			{
				// New topic: Send email only to category subscribers
				$mailsubs = $config->category_subscriptions != 'disabled' ? Access::CATEGORY_SUBSCRIPTION : 0;
				$once     = $config->category_subscriptions == 'topic';
			}
			elseif ($config->category_subscriptions != 'post')
			{
				// Existing topic: Send email only to topic subscribers
				$mailsubs = $config->topic_subscriptions != 'disabled' ? Access::TOPIC_SUBSCRIPTION : 0;
				$once     = $config->topic_subscriptions == 'first';
			}
			else
			{
				// Existing topic: Send email to both category and topic subscribers
				$mailsubs = $config->topic_subscriptions == 'disabled' ? Access::CATEGORY_SUBSCRIPTION : Access::CATEGORY_SUBSCRIPTION | Access::TOPIC_SUBSCRIPTION;

				// FIXME: category subscription can override topic
				$once = $config->topic_subscriptions == 'first';
			}
		}

		// Get all subscribers, moderators and admins who will get the email
		$me          = Helper::get();
		$acl         = Access::getInstance();
		$subscribers = $acl->getSubscribers($message->catid, $message->thread, $mailsubs, $mailmods, $mailadmins, $me->userid);

		if (!$subscribers)
		{
			return false;
		}

		$result = [];

		foreach ($subscribers as $subscriber)
		{
			if ($subscriber->id)
			{
				$result[] = $subscriber->id;
			}
		}

		return $result;
	}

	/**
	 * @param   int  $actor    actor
	 * @param   int  $target   target
	 * @param   int  $message  message
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	public function onAfterThankyou($actor, $target, $message)
	{
		if (StringHelper::strlen($message->message) > $this->params->get('activity_points_limit', 0))
		{
			$this->assignPoints('thread.thanks', $target);
		}

		$this->assignBadge('thread.thanks', Text::_('PLG_KUNENA_EASYSOCIAL_BADGE_THANKED_TITLE'), $target);

		$tmpl = FD::stream()->getTemplate();

		$tmpl->setActor($actor, SOCIAL_TYPE_USER);
		$tmpl->setTarget($target);
		$tmpl->setContext($message->id, 'kunena');
		$tmpl->setVerb('thanked');
		$tmpl->setAccess('core.view');

		FD::stream()->add($tmpl);
	}

	/**
	 * @param   object  $target  target
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	public function onBeforeDeleteTopic($target)
	{
		FD::stream()->delete($target->id, 'thread.new');
	}

	/**
	 * @param   object  $topic  topic
	 *
	 * @return  void
	 *
	 * @since  Kunena 6.0
	 */
	public function onAfterDeleteTopic($topic)
	{
		FD::stream()->delete($topic->id, 'thread.new');
	}
}
