<?php
/**
 * Kunena Component
 *
 * @package       Kunena.Framework
 * @subpackage    Private
 *
 * @copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.kunena.org
 **/

namespace Kunena\Forum\Libraries\KunenaPrivate\Message;

defined('_JEXEC') or die();

use Kunena\Forum\Libraries\Database\KunenaDatabaseObject;
use function defined;

/**
 * Private message mapping for user.
 *
 * @since   Kunena 6.0
 * @property int    $user_id
 * @property string $read_at
 * @property string $replied_at
 * @property string $deleted_at
 *
 * @property int    $private_id
 */
class User extends KunenaDatabaseObject
{
	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	protected $_table = 'KunenaPrivateUserMap';
}
