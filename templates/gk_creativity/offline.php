<?php

/**
 *
 * offline view
 *
 * @version             1.0.0
 * @package             Gavern Framework
 * @copyright			Copyright (C) 2010 - 2011 GavickPro. All rights reserved.
 *               
 */
 
// No direct access.
defined('_JEXEC') or die;

$app = JFactory::getApplication();

$uri = JURI::getInstance();
jimport('joomla.factory');

// get necessary template parameters
$templateParams = JFactory::getApplication()->getTemplate(true)->params;
$pageName = JFactory::getDocument()->getTitle();

// get logo configuration
$logo_type = $templateParams->get('logo_type');
$logo_image = $templateParams->get('logo_image');
$template_style = $templateParams->get('template_color');

if(($logo_image == '') || ($templateParams->get('logo_type') == 'css')) {
     $logo_image = JURI::base() . '../images/logo.png';
} else {
     $logo_image = JURI::base() . $logo_image;
}
$logo_text = $templateParams->get('logo_text', '');
$logo_slogan = $templateParams->get('logo_slogan', '');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
<jdoc:include type="head" />
<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo JURI::base(); ?>templates/<?php echo $this->template; ?>/css/system/offline.style<?php echo $template_style; ?>.css" type="text/css" />
</head>
<body>
<div id="gkPage">
<div id="wrapper">
        <?php if ($logo_type !== 'none' && !$app->getCfg('offline_image')): ?>
        <?php if($logo_type == 'css') : ?>
        <a href="./" id="gkLogo" class="cssLogo"></a>
        <?php elseif($logo_type =='text') : ?>
        <a href="./" id="gkLogo" class="text"> <span><?php echo $logo_text; ?></span> <small class="gkLogoSlogan"><?php echo $logo_slogan; ?></small> </a>
        <?php elseif($logo_type =='image') : ?>
        <a href="./" id="gkLogo">
        <img src="<?php echo $logo_image; ?>" alt="<?php echo $pageName; ?>" />
        </a>
        <?php endif; ?>
        <?php else : ?>
        <?php if($app->getCfg('offline_image')) : ?>
        <a href="./" id="gkLogo">
        <img src="<?php echo $app->getCfg('offline_image'); ?>" alt="<?php echo $app->getCfg('sitename'); ?>" />
        </a>
        <?php endif; ?>
        <?php endif; ?>
        <?php
			$msg = explode('<br />', $app->getCfg('offline_message'));
		?>
        <?php if(trim($msg[0]) != '') : ?>
        <h2><?php echo $msg[0]; ?></h2>
        <?php endif; ?>
        <?php if(isset($msg[1]) && trim($msg[1]) != '') : ?>
        <h3><?php echo $msg[1]; ?></h3>
        <?php endif; ?>
        <h3><?php echo JText::_('TPL_GK_LANG_LOGIN'); ?></h3>
        <form action="index.php" method="post" name="login" id="form-login">
                <fieldset class="input">
                        <label for="username"><?php echo JText::_('JGLOBAL_USERNAME') ?></label>
                        <input name="username" id="username" type="text" class="inputbox" alt="<?php echo JText::_('JGLOBAL_USERNAME') ?>" size="32" />
                        <label for="passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
                        <input type="password" name="password" class="inputbox" size="32" alt="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" id="passwd" />
                        <input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGIN') ?>" />
                        <jdoc:include type="message" />
                        <input type="hidden" name="option" value="com_users" />
                        <input type="hidden" name="task" value="user.login" />
                        <input type="hidden" name="return" value="<?php echo base64_encode(JURI::base()) ?>" />
                        <?php echo JHtml::_('form.token'); ?>
                </fieldset>
        </form>
</div>
</body>
</html>
