<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Models
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\Model;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Kunena\Forum\Libraries\Forum\Announcement\Announcement;
use Kunena\Forum\Libraries\Forum\Announcement\AnnouncementHelper;
use Kunena\Forum\Libraries\User\KunenaUserHelper;

/**
 * Announcement Model for Kunena
 *
 * @since   Kunena 2.0
 */
class AnnouncementModel extends ListModel
{
	/**
	 * @var     boolean
	 * @since   Kunena 6.0
	 */
	protected $total = false;

	/**
	 * @return  Announcement
	 *
	 * @since   Kunena 6.0
	 */
	public function getNewAnnouncement()
	{
		return new Announcement;
	}

	/**
	 * @return  Announcement
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function getAnnouncement()
	{
		return AnnouncementHelper::get($this->getState('item.id'));
	}

	/**
	 * @return  integer|void
	 *
	 * @since   Kunena 6.0
	 */
	public function getTotal()
	{
		if ($this->total === false)
		{
			return;
		}

		return $this->total;
	}

	/**
	 * @return  Announcement[]
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function getAnnouncements()
	{
		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');

		$this->total = AnnouncementHelper::getCount(!$this->me->isModerator());

		// If out of range, use last page
		if ($limit && $this->total < $start)
		{
			$start = intval($this->total / $limit) * $limit;
		}

		$announces = AnnouncementHelper::getAnnouncements($start, $limit, !$this->me->isModerator());

		if ($this->total < $start)
		{
			$this->setState('list.start', intval($this->total / $limit) * $limit);
		}

		return $announces;
	}

	/**
	 * @return  array
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function getannouncementActions()
	{
		$actions = [];
		$user    = KunenaUserHelper::getMyself();

		if ($user->isModerator())
		{
			$actions[] = HTMLHelper::_('select.option', 'none', Text::_('COM_KUNENA_BULK_CHOOSE_ACTION'));
			$actions[] = HTMLHelper::_('select.option', 'unpublish', Text::_('COM_KUNENA_BULK_ANNOUNCEMENT_UNPUBLISH'));
			$actions[] = HTMLHelper::_('select.option', 'publish', Text::_('COM_KUNENA_BULK_ANNOUNCEMENT_PUBLISH'));
			$actions[] = HTMLHelper::_('select.option', 'edit', Text::_('COM_KUNENA_EDIT'));
			$actions[] = HTMLHelper::_('select.option', 'delete', Text::_('COM_KUNENA__BULK_ANNOUNCEMENT_DELETE'));
		}

		return $actions;
	}

	/**
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	protected function populateState()
	{
		$id = $this->getInt('id', 0);
		$this->setState('item.id', $id);

		$value = $this->getInt('limit', 0);

		if ($value < 1 || $value > 100)
		{
			$value = 20;
		}

		$this->setState('list.limit', $value);

		$value = $this->getInt('limitstart', 0);

		if ($value < 0)
		{
			$value = 0;
		}

		$this->setState('list.start', $value);
	}
}
