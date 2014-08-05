<?php

/**
 *
 * Default view
 *
 * @version             1.0.0
 * @package             Gavern Framework
 * @copyright			Copyright (C) 2010 - 2011 GavickPro. All rights reserved.
 *               
 */
 
// No direct access.
defined('_JEXEC') or die;
//
$app = JFactory::getApplication();
$user = JFactory::getUser();
// getting User ID
$userID = $user->get('id');
// getting params
$option = JRequest::getCmd('option', '');
$view = JRequest::getCmd('view', '');
// defines if com_users
define('GK_COM_USERS', $option == 'com_users' && ($view == 'login' || $view == 'registration'));
// other variables
$btn_login_text = ($userID == 0) ? JText::_('TPL_GK_LANG_LOGIN') : JText::_('TPL_GK_LANG_LOGOUT');
$tpl_page_suffix = $this->page_suffix != '' ? ' class="'.$this->page_suffix.'"' : '';
// check if the one page mode is enabled
$one_page_mode = stripos($this->page_suffix, 'onepage') !== FALSE;
// make sure that the modal will be loaded
JHTML::_('behavior.modal');

?>
<!DOCTYPE html>
<html lang="<?php echo $this->APITPL->language; ?>" <?php echo $tpl_page_suffix; ?>>
<head>
<?php $this->layout->addTouchIcon(); ?>
<?php if(
			$this->browser->get('browser') == 'ie6' || 
			$this->browser->get('browser') == 'ie7' || 
			$this->browser->get('browser') == 'ie8' || 
			$this->browser->get('browser') == 'ie9'
		) : ?>
<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
<?php endif; ?>
<?php if($this->API->get('rwd', 1)) : ?>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2.0">
<?php else : ?>
<meta name="viewport" content="width=<?php echo $this->API->get('template_width', 1020)+80; ?>">
<?php endif; ?>
<jdoc:include type="head" />
<?php $this->layout->loadBlock('head'); ?>
<?php $this->layout->loadBlock('cookielaw'); ?>
</head>
<body<?php echo $tpl_page_suffix; ?><?php if($this->browser->get("tablet") == true) echo ' data-tablet="true"'; ?><?php if($this->browser->get("mobile") == true) echo ' data-mobile="true"'; ?><?php $this->layout->generateLayoutWidths(); ?>>
<?php if ($this->browser->get('browser') == 'ie7' || $this->browser->get('browser') == 'ie6') : ?>
	<!--[if lte IE 7]>
		<div id="ieToolbar"><div><?php echo JText::_('TPL_GK_LANG_IE_TOOLBAR'); ?></div></div>
	<![endif]-->
<?php endif; ?>

<div id="gkTop"<?php if($this->API->modules('intro')) : ?> class="isIntro"<?php endif; ?>>
        <div class="gkPage">
                <?php $this->layout->loadBlock('logo'); ?>
                <?php if($this->API->get('show_menu', 1)) : ?>
                <div id="gkMobileMenu" class="gkPage"> <i class="icon-reorder"></i>
                        <select onChange="window.location.href=this.value;">
                                <?php 
					    $this->mobilemenu->loadMenu($this->API->get('menu_name','mainmenu')); 
					    $this->mobilemenu->genMenu($this->API->get('startlevel', 0), $this->API->get('endlevel',-1));
					?>
                        </select>
                </div>
                <?php endif; ?>
                <?php if($this->API->modules('login')) : ?>
                <div id="gkUserArea">
                        <?php if($this->API->modules('login')) : ?>
                        <a href="<?php echo $this->API->get('login_url', 'index.php?option=com_users&view=login'); ?>" id="gkLogin" class="button border"><?php echo ($userID == 0) ? JText::_('TPL_GK_LANG_LOGIN') : JText::_('TPL_GK_LANG_LOGOUT'); ?></a>
                        <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if($this->API->get('show_menu', 1)) : ?>
                <div id="gkMainMenu" class="gkPage">
                        <?php
			    		$this->mainmenu->loadMenu($this->API->get('menu_name','mainmenu')); 
			    	    $this->mainmenu->genMenu($this->API->get('startlevel', 0), $this->API->get('endlevel',-1));
			    	?>
                </div>
                <?php endif; ?>
        </div>
</div>
<?php if($this->API->modules('intro')) : ?>
<div class="gk-intro">
        <jdoc:include type="modules" name="intro" style="<?php echo $this->module_styles['intro']; ?>" />
</div>
<?php endif; ?>
<div id="gkContentWrapper"<?php if(!$this->API->modules('intro')) : ?> class="noIntro"<?php endif; ?>>
		<?php if(count($app->getMessageQueue())) : ?>
		<jdoc:include type="message" />
		<?php endif; ?>
		
        <?php if($this->API->modules('breadcrumb')) : ?>
        <div id="gkBreadcrumb">
                <div class="gkPage">
                        <?php if($this->API->modules('breadcrumb')) : ?>
                        <jdoc:include type="modules" name="breadcrumb" style="<?php echo $this->module_styles['breadcrumb']; ?>" />
                        <?php endif; ?>
                </div>
        </div>
        <?php endif; ?>
        <div id="gkPageContentWrap"<?php if($this->API->modules('inset and sidebar')) : ?> class="gk3Columns"<?php endif; ?>>
                <?php if($this->API->modules('header')) : ?>
                <div id="gkHeader">
                        <jdoc:include type="modules" name="header" style="<?php echo $this->module_styles['header']; ?>" onepagemode="1" />
                </div>
                <?php endif; ?>
                <div id="gkPageContent" class="gkPage">
                        <section id="gkContent"<?php if($this->API->get('sidebar_position', 'right') == 'left') : ?> class="gkColumnLeft"<?php endif; ?>>
                                <div id="gkContentWrap"<?php if($this->API->get('inset_position', 'right') == 'left') : ?> class="gkInsetLeft"<?php endif; ?>>
                                        <?php if($this->API->modules('top1')) : ?>
                                        <div id="gkTop1" class="gkCols3<?php if($this->API->modules('top1') == 1) : ?> gkNoMargin<?php endif; ?>">
                                                <jdoc:include type="modules" name="top1" style="<?php echo $this->module_styles['top1']; ?>"  modnum="<?php echo $this->API->modules('top1'); ?>" modcol="3" />
                                        </div>
                                        <?php endif; ?>
                                        <?php if($this->API->modules('top2')) : ?>
                                        <div id="gkTop2" class="gkCols3<?php if($this->API->modules('top2') == 1) : ?> gkNoMargin<?php endif; ?>">
                                                <jdoc:include type="modules" name="top2" style="<?php echo $this->module_styles['top2']; ?>" modnum="<?php echo $this->API->modules('top2'); ?>" modcol="3" />
                                        </div>
                                        <?php endif; ?>
                                        <?php if($this->API->modules('mainbody_top')) : ?>
                                        <div id="gkMainbodyTop">
                                                <jdoc:include type="modules" name="mainbody_top" style="<?php echo $this->module_styles['mainbody_top']; ?>" />
                                        </div>
                                        <?php endif; ?>
                                        <div id="gkMainbody">
                                                <?php if(($this->layout->isFrontpage() && !$this->API->modules('mainbody')) || !$this->layout->isFrontpage()) : ?>
                                                <jdoc:include type="component" />
                                                <?php else : ?>
                                                <jdoc:include type="modules" name="mainbody" style="<?php echo $this->module_styles['mainbody']; ?>" />
                                                <?php endif; ?>
                                        </div>
                                        <?php if($this->API->modules('mainbody_bottom')) : ?>
                                        <div id="gkMainbodyBottom">
                                                <jdoc:include type="modules" name="mainbody_bottom" style="<?php echo $this->module_styles['mainbody_bottom']; ?>" />
                                        </div>
                                        <?php endif; ?>
                                </div>
                                <?php if($this->API->modules('inset')) : ?>
                                <aside id="gkInset"<?php if($this->API->modules('inset') == 1) : ?> class="gkOnlyOne"<?php endif; ?>>
                                        <jdoc:include type="modules" name="inset" style="<?php echo $this->module_styles['inset']; ?>" />
                                </aside>
                                <?php endif; ?>
                        </section>
                        <?php if($this->API->modules('sidebar')) : ?>
                        <aside id="gkSidebar"<?php if($this->API->modules('sidebar') == 1) : ?> class="gkOnlyOne"<?php endif; ?>>
                                <jdoc:include type="modules" name="sidebar" style="<?php echo $this->module_styles['sidebar']; ?>" />
                        </aside>
                        <?php endif; ?>
                </div>
                <?php if($this->API->modules('bottom1')) : ?>
                <div id="gkBottom1">
                        <?php if(!$one_page_mode): ?>
                        <div class="gkPage">
                                <?php endif; ?>
                                <div class="gkCols6">
                                        <jdoc:include type="modules" name="bottom1" style="<?php echo $this->module_styles['bottom1']; ?>" modnum="<?php echo $this->API->modules('bottom1'); ?>" onepagemode="<?php if($one_page_mode): ?>1<?php else : ?>0<?php endif; ?>" />
                                </div>
                                <?php if(!$one_page_mode): ?>
                        </div>
                        <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if($this->API->modules('bottom2')) : ?>
                <div id="gkBottom2">
                        <div class="gkPage">
                                <div class="gkCols6">
                                        <jdoc:include type="modules" name="bottom2" style="<?php echo $this->module_styles['bottom2']; ?>" modnum="<?php echo $this->API->modules('bottom2'); ?>" />
                                </div>
                        </div>
                </div>
                <?php endif; ?>
        </div>
        <?php $this->layout->loadBlock('footer'); ?>
        <?php if($this->API->modules('lang')) : ?>
        <div id="gkLang" class="gkPage">
                <jdoc:include type="modules" name="lang" style="<?php echo $this->module_styles['lang']; ?>" />
        </div>
        <?php endif; ?>
</div>
<?php $this->layout->loadBlock('social'); ?>
<?php $this->layout->loadBlock('tools/login'); ?>
<jdoc:include type="modules" name="debug" />

</body>
</html>