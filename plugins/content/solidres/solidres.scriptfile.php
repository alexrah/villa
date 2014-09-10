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

/**
 * Custom script to hook into installation process
 *
 */
class plgContentSolidresInstallerScript {

	function install($parent)
	{
	}

	function uninstall($parent)
	{
	}

	function update($parent)
	{
	}

	function preflight($type, $parent)
	{
	}

	function postflight($type, $parent) {
		echo '<p>'. JText::_('Solidres - Content plugin is installed successfully.') .'</p>';

		$dbo = JFactory::getDbo();

		$query = $dbo->getQuery(true);

		$query->clear();
		$query->update($dbo->quoteName('#__extensions'));
		$query->set('enabled = 1');
		$query->where("element = 'solidres'");
		$query->where("type = 'plugin'");
		$query->where("folder = 'content'");

		$dbo->setQuery($query);

		$result = $dbo->execute();
		if(!$result) {
			JError::raiseWarning(-1, 'plgContentSolidres: publishing failed');
		} else {
			echo '<p>'. JText::_('Solidres - Content plugin is published successfully.') .'</p>';
		}
	}
}