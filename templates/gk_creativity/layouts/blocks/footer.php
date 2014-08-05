<?php

// No direct access.
defined('_JEXEC') or die;

?>

<footer id="gkFooter">
	<div class="gkPage">
		<?php if($this->API->modules('footer_nav')) : ?>
		<div id="gkFooterNav">
			<jdoc:include type="modules" name="footer_nav" style="<?php echo $this->module_styles['footer_nav']; ?>" modnum="<?php echo $this->API->modules('footer_nav'); ?>" />
		</div>
		<?php endif; ?>
		
		<?php if($this->API->get('stylearea', '0') == '1') : ?>
		<div id="gkStyleArea">
			<a href="#" id="gkColor1"><?php echo JText::_('TPL_GK_LANG_COLOR_1'); ?></a>
			<a href="#" id="gkColor2"><?php echo JText::_('TPL_GK_LANG_COLOR_2'); ?></a>
			<a href="#" id="gkColor3"><?php echo JText::_('TPL_GK_LANG_COLOR_3'); ?></a>
			<a href="#" id="gkColor4"><?php echo JText::_('TPL_GK_LANG_COLOR_4'); ?></a>
			<a href="#" id="gkColor5"><?php echo JText::_('TPL_GK_LANG_COLOR_5'); ?></a>
			<a href="#" id="gkColor6"><?php echo JText::_('TPL_GK_LANG_COLOR_6'); ?></a>
		</div>
		<?php endif; ?>
		
		<?php if($this->API->get('copyrights', '') !== '') : ?>
		<p id="gkCopyrights"><?php echo $this->API->get('copyrights', ''); ?></p>
		<?php else : ?>
		<p id="gkCopyrights">Template Design &copy; <a href="//www.gavick.com" title="Joomla Templates">Joomla Templates</a> GavickPro. All rights reserved.</p>
		<?php endif; ?>
		
		<?php if($this->API->get('framework_logo', '0') == '1') : ?>
		<a href="//gavick.com" id="gkFrameworkLogo" title="Gavern Framework">Gavern Framework</a>
		<?php endif; ?>
	</div>
</footer>