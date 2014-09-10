<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2014 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('_JEXEC') or die;

?>
<div id="solidres">
	<div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<div class="alert alert-block">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<h4><?php echo JText::_('SR_SYSTEM_INSTALL_SAMPLE_DATA_WARNING') ?></h4>
				<?php echo JText::_('SR_SYSTEM_INSTALL_SAMPLE_DATA_WARNING_MESSAGE') ?>
				<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=system.installsampledata') ?>"
				   class="btn btn-large btn-info">
					<?php echo JText::_('SR_SYSTEM_INSTALL_SAMPLE_DATA_WARNING_BTN') ?>
				</a>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
</div>