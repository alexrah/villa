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
 * System view class
 *
 * @package     Solidres
 * @subpackage	System
 * @since		0.6.0
 */
class SolidresViewSystem extends JViewLegacy
{
    function display($tpl = null)
	{
		$this->addToolbar();

		parent::display($tpl);
    }

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$canDo	= SolidresHelper::getActions();

		JToolBarHelper::title(JText::_('SR_SUBMENU_SYSTEM'), 'generic.png');

		if ($canDo->get('core.admin'))
        {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_solidres');
		}
	}
}