<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Controllers
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\Controller;

defined('_JEXEC') or die();

use Kunena\Forum\Libraries\Controller\KunenaController;
use function defined;

/**
 * Kunena Search Controller
 *
 * @since   Kunena 2.0
 */
class SearchController extends KunenaController
{
	/**
	 * @param   array  $config  config
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  \Exception
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);
	}

	/**
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	public function results()
	{
		$model = $this->getModel('Search');
		$this->setRedirect(
			$model->getSearchURL(
				'search', $model->getState('searchwords'),
				$model->getState('list.start'), $model->getState('list.limit'), $model->getUrlParams(), false
			)
		);
	}
}
