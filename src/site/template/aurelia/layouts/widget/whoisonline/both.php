<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Statistics
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
**/

namespace Kunena\Forum\Site;

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use function defined;

?>

<?php if (!empty($this->onlineList)) : ?>
	<div id="whoisonlinelist">
		<?php
		foreach ($this->onlineList as $user)
		{
			$avatar       = $user->getAvatarImage(\Kunena\Forum\Libraries\Factory\KunenaFactory::getTemplate()->params->get('avatarType') . ' ', 20, 20);
			$onlinelist[] = $user->getLink($avatar, null, '', '', null, 0, \Kunena\Forum\Libraries\Config\KunenaConfig::getInstance()->avataredit) . $user->getLink();
		}
		?>
		<?php echo implode(', ', $onlinelist); ?>
	</div>
<?php endif; ?>

<?php if (!empty($this->hiddenList)) : ?>
	<div id="whoisonlinelist">
		<span><?php echo Text::_('COM_KUNENA_HIDDEN_USERS'); ?>:</span>

		<?php
		foreach ($this->hiddenList as $user)
		{
			$avatar       = $user->getAvatarImage(\Kunena\Forum\Libraries\Factory\KunenaFactory::getTemplate()->params->get('avatarType') . ' ', 20, 20);
			$hiddenlist[] = $user->getLink($avatar, null, '', '', null, 0, \Kunena\Forum\Libraries\Config\KunenaConfig::getInstance()->avataredit) . $user->getLink();
		}
		?>
		<?php echo implode(', ', $hiddenlist); ?>
	</div>
<?php endif; ?>

