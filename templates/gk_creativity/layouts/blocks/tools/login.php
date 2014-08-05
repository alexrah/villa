<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

// getting user ID
$user = JFactory::getUser();
$userID = $user->get('id');

?>
<?php if($this->API->modules('login') && !GK_COM_USERS) : ?>

<div id="gkPopupLogin">
	<div class="gkPopupWrap">
		<div id="loginForm">
			<h3><?php echo JText::_(($userID == 0) ? 'TPL_GK_LANG_LOGIN' : 'TPL_GK_LANG_LOGOUT'); ?>
				<?php
					$usersConfig = JComponentHelper::getParams('com_users');
					if ($usersConfig->get('allowUserRegistration') && $userID == 0) : 
				?>
				<?php echo JText::_('TPL_GK_LANG_OR'); ?><a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>"> <?php echo JText::_('TPL_GK_LANG_REGISTER'); ?></a>
				<?php endif; ?>
			</h3>
			<div class="overflow">
				<?php if($userID > 0) : ?>
				<jdoc:include type="modules" name="usermenu" style="<?php echo $this->module_styles['usermenu']; ?>" />
				<?php endif; ?>
				<?php if($userID > 0) : ?>
				<div>
					<?php endif; ?>
					<jdoc:include type="modules" name="login" style="<?php echo $this->module_styles['login']; ?>" />
					<?php if($userID > 0) : ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<div id="gkPopupOverlay">
</div>
<?php endif; ?>
