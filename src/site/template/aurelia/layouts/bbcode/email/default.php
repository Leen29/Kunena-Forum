<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.BBCode
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
**/

namespace Kunena\Forum\Site;

defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use function defined;

// [email]john.doe@domain.com[/email]
// [email=john.doe@domain.com]John Doe[/email]

// Display email address (cloak it).
echo HTMLHelper::_(
	'email.cloak',
	$this->escape($this->email), $this->mailto,
	$this->escape($this->text), $this->textCloak
);
