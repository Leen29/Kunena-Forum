<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Views
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\View\Category;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Document\Feed\FeedImage;
use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Html\Parser;
use Kunena\Forum\Libraries\View\View;
use function defined;

/**
 * Category View
 *
 * @since   Kunena 6.0
 */
class feed extends View
{
	/**
	 * @param   null  $tpl  tpl
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function displayDefault($tpl = null)
	{
		if (!$this->config->enablerss)
		{
			throw new Exception(Text::_('COM_KUNENA_RSS_DISABLED'), 500);
		}

		Parser::$relative = false;

		$this->category = $this->get('Category');

		if (!$this->category->isAuthorised('read'))
		{
			throw new Exception($this->category->getError(), 404);
		}

		$this->topics = $this->get('Topics');

		$title = Text::_('COM_KUNENA_THREADS_IN_FORUM') . ': ' . $this->category->name;
		$this->setTitle($title);

		$metaDesc = $this->document->getDescription() . '. ' . $this->escape("{$this->category->name} - {$this->config->board_title}");
		$this->document->setDescription($metaDesc);

		// Create image for feed
		$image                 = new FeedImage;
		$image->title          = $this->document->getTitle();
		$image->url            = $this->ktemplate->getImagePath('icons/rss.png');
		$image->description    = $this->document->getDescription();
		$this->document->image = $image;

		foreach ($this->topics as $topic)
		{
			$description = $topic->last_post_message;
			$date        = new Date($topic->last_post_time);
			$userid      = $topic->last_post_userid;
			$username    = KunenaFactory::getUser($userid)->getName($topic->last_post_guest_name);

			$title    = $topic->subject;
			$category = $topic->getCategory();
			$url      = $topic->getUrl($category, true, 'last');

			$this->createItem($title, $url, $description, $category->name, $date, $userid, $username);
		}
	}

	/**
	 * @param   string   $title        title
	 * @param   string   $url          url
	 * @param   string   $description  description
	 * @param   string   $category     category
	 * @param   integer  $date         date
	 * @param   integer  $userid       userid
	 * @param   string   $username     username
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function createItem($title, $url, $description, $category, $date, $userid, $username)
	{
		if ($this->config->rss_author_in_title)
		{
			// We want author in item titles
			$title .= ' - ' . Text::_('COM_KUNENA_BY') . ': ' . $username;
		}

		if ((int) $this->config->rss_word_count === -1)
		{
			$description = '';
		}
		else
		{
			$description = preg_replace('/\[confidential\](.*?)\[\/confidential\]/s', '', $description);
			$description = preg_replace('/\[hide\](.*?)\[\/hide\]/s', '', $description);
			$description = preg_replace('/\[spoiler\](.*?)\[\/spoiler\]/s', '', $description);
			$description = preg_replace('/\[code\](.*?)\[\/code]/s', '', $description);

			if ((bool) $this->config->rss_allow_html)
			{
				$description = Parser::parseBBCode($description, null, (int) $this->config->rss_word_count);
			}
			else
			{
				$description = Parser::parseText($description, (int) $this->config->rss_word_count);
			}
		}

		// Assign values to feed item
		$item              = new FeedItem;
		$item->title       = $title;
		$item->link        = $url;
		$item->description = $description;
		$item->date        = $date->toSql();
		$item->author      = $username;

		// FIXME: inefficient to load users one by one -- also vulnerable to J! 2.5 user is NULL bug
		if ($this->config->rss_author_format != 'name')
		{
			$item->authorEmail = Factory::getUser($userid)->email;
		}

		$item->category = $category;

		// Finally add item to feed
		$this->document->addItem($item);
	}
}
