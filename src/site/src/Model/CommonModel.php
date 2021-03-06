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

use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die();

/**
 * Common Model for Kunena
 *
 * @since   Kunena 2.0
 */
class CommonModel extends ListModel
{
	/**
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	protected function populateState()
	{
		$params = $this->getParameters();
		$this->setState('params', $params);
	}
}
