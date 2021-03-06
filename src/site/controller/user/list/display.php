<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Controller.User
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\Controller\User\KunenaList;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Kunena\Forum\Libraries\Controller\KunenaControllerDisplay;
use Kunena\Forum\Libraries\Exception\Authorise;
use Kunena\Forum\Libraries\Pagination\Pagination;
use Kunena\Forum\Libraries\Route\KunenaRoute;
use Kunena\Forum\Libraries\User\Finder;
use Kunena\Forum\Libraries\User\KunenaUserHelper;
use Kunena\Forum\Site\Model\UserModel;
use function defined;

/**
 * Class ComponentUserControllerListDisplay
 *
 * @since   Kunena 4.0
 */
class ComponentUserControllerListDisplay extends KunenaControllerDisplay
{
	/**
	 * @var     object
	 * @since   Kunena 6.0
	 */
	public $state;

	/**
	 * @var     object
	 * @since   Kunena 6.0
	 */
	public $me;

	/**
	 * @var     integer
	 * @since   Kunena 6.0
	 */
	public $total;

	/**
	 * @var     object
	 * @since   Kunena 6.0
	 */
	public $users;

	/**
	 * @var     integer
	 * @since   Kunena 6.0
	 */
	public $pagination;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	protected $name = 'User/List';

	/**
	 * Load user list.
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  null
	 * @throws  Exception
	 */
	protected function before()
	{
		parent::before();

		if (!$this->config->userlist_allowed && Factory::getApplication()->getIdentity()->guest)
		{
			throw new Authorise(Text::_('COM_KUNENA_NO_ACCESS'), '401');
		}

		$this->model = new UserModel([], $this->input);
		$this->model->initialize($this->getOptions(), $this->getOptions()->get('embedded', false));
		$this->state = $this->model->getState();

		$this->me = KunenaUserHelper::getMyself();

		$start = $this->state->get('list.start');
		$limit = $this->state->get('list.limit');

		$Itemid = $this->input->getInt('Itemid');
		$format = $this->input->getCmd('format');

		if (!$Itemid && $format != 'feed' && $this->config->sef_redirect)
		{
			$itemid     = KunenaRoute::fixMissingItemID();
			$controller = BaseController::getInstance("kunena");
			$controller->setRedirect(KunenaRoute::_("index.php?option=com_kunena&view=user&layout=list&Itemid={$itemid}", false));
			$controller->redirect();
		}

		// Exclude super admins.
		if ($this->config->superadmin_userlist)
		{
			$filter = Access::getUsersByGroup(8);
		}
		else
		{
			$filter = [];
		}

		$finder = new Finder;
		$finder
			->filterByConfiguration($filter)
			->filterByName($this->state->get('list.search'));

		$this->total      = $finder->count();
		$this->pagination = new Pagination($this->total, $start, $limit);

		$alias     = 'ku';
		$aliasList = ['id', 'name', 'username', 'email', 'block', 'registerDate', 'lastvisitDate'];

		if (in_array($this->state->get('list.ordering'), $aliasList))
		{
			$alias = 'a';
		}

		$this->users = $finder
			->order($this->state->get('list.ordering'), $this->state->get('list.direction') == 'asc' ? 1 : -1, $alias)
			->start($this->pagination->limitstart)
			->limit($this->pagination->limit)
			->find();
	}

	/**
	 * Prepare document.
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	protected function prepareDocument()
	{
		$page      = $this->pagination->pagesCurrent;
		$pages     = $this->pagination->pagesTotal;
		$pagesText = ($page > 1 ? " - " . Text::_('COM_KUNENA_PAGES') . " {$page}" : '');

		$menu_item = $this->app->getMenu()->getActive();

		if ($menu_item)
		{
			$params             = $menu_item->getParams();
			$params_title       = $params->get('page_title');
			$params_description = $params->get('menu-meta_description');

			if (!empty($params_title))
			{
				$title = $params->get('page_title') . $pagesText;
				$this->setTitle($title);
			}
			else
			{
				$title = Text::_('COM_KUNENA_VIEW_USER_LIST') . $pagesText;
				$this->setTitle($title);
			}

			if (!empty($params_description))
			{
				$description = $params->get('menu-meta_description') . $pagesText;
				$this->setDescription($description);
			}
			else
			{
				$description = Text::_('COM_KUNENA_VIEW_USER_LIST') . ': ' . $this->config->board_title . $pagesText;
				$this->setDescription($description);
			}
		}
	}
}
