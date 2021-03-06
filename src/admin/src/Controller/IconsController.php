<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Administrator
 * @subpackage      Controllers
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Administrator\Controller;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\MVC\Controller\FormController;
use function defined;

/**
 * Kunena Backend Icons Controller
 *
 * @since   Kunena 5.1
 */
class IconsController extends FormController
{
	/**
	 * @var     null|string
	 *
	 * @since   Kunena 5.1
	 */
	protected $baseurl = null;

	/**
	 * Construct
	 *
	 * @param   array  $config  config
	 *
	 * @since   Kunena 5.1
	 *
	 * @throws  Exception
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);
		$this->baseurl = 'administrator/index.php?option=com_kunena&view=icons';
	}
}
