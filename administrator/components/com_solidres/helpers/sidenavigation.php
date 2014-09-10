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

jimport('solidres.version');

/**
 * Solidres Side Navigation Helper class
 *
 * @package     Solidres
 */
class SolidresHelperSideNavigation
{
	public static $extention = 'com_solidres';
	
	/**
	 * Display the side navigation bar, ACL aware
	 * 
	 * @return  string the html representation of side navigation
 	 */
	public static function getSideNavigation()
	{
	    JHtml::_('behavior.framework', true);
		$input = JFactory::getApplication()->input;
		$viewName 	= $input->get('view', '', 'cmd');
		$disabled	= $input->get('disablesidebar', '0', 'int');
		$link 		= '';
		$doc 		= JFactory::getDocument();
		JLoader::register('SRSystemHelper', JPATH_LIBRARIES . '/solidres/system/helper.php');

		if ($disabled) return;

		$menuStructure['SR_SUBMENU_ASSET'] = array(
			0 => array( 'SR_SUBMENU_ASSETS_CATEGORY', 'index.php?option=com_categories&extension=com_solidres' ),
			1 => array( 'SR_SUBMENU_ASSETS_LIST', 'index.php?option=com_solidres&view=reservationassets' ),
			2 => array( 'SR_SUBMENU_ROOM_TYPE_LIST', 'index.php?option=com_solidres&view=roomtypes' )
		);

		$menuStructure['SR_SUBMENU_CUSTOMER'] = array(
			0 => array( 'SR_SUBMENU_CUSTOMERS_LIST', 'index.php?option=com_solidres&view=customers' ),
			1 => array( 'SR_SUBMENU_CUSTOMERGROUPS_LIST', 'index.php?option=com_solidres&view=customergroups' )
		);

		$menuStructure['SR_SUBMENU_RESERVATION'] = array(
			0 => array( 'SR_SUBMENU_RESERVATIONS_LIST', 'index.php?option=com_solidres&view=reservations' )
		);

		$menuStructure['SR_SUBMENU_COUPON_EXTRA'] = array(
			0 => array( 'SR_SUBMENU_COUPONS_LIST', 'index.php?option=com_solidres&view=coupons' ),
			1 => array( 'SR_SUBMENU_EXTRAS_LIST', 'index.php?option=com_solidres&view=extras' )
		);

		if (SR_PLUGIN_FEEDBACK_ENABLED)
		{
			$menuStructure['SR_SUBMENU_CUSTOMER_FEEDBACK'] = array(
				0 => array( 'SR_SUBMENU_COMMENT_LIST', 'index.php?option=com_solidres&view=feedbacks' ),
				1 => array( 'SR_SUBMENU_CONDITION_LIST', 'index.php?option=com_solidres&view=feedbackconditions' ),
				2 => array( 'SR_SUBMENU_CUSTOMER_FEEDBACK_TYPE_LIST', 'index.php?option=com_solidres&view=feedbacktypes')
			);
		}

		$menuStructure['SR_SUBMENU_SYSTEM'] = array(
			0 => array( 'SR_SUBMENU_CURRENCIES_LIST', 'index.php?option=com_solidres&view=currencies' ),
			//0 => array( 'SR_SUBMENU_SYSTEM_BACKUP', '&task=system.backup' ),
			//1 => array( 'SR_SUBMENU_SYSTEM_RESTORE', '&view=system&layout=restore' ),
			//2 => array( 'SR_SUBMENU_SYSTEM_RESET_SAMPLE_DATA', '&task=system.resetsampledata'),
			3 => array( 'SR_SUBMENU_SYSTEM_INSTALL_SAMPLE_DATA', 'index.php?option=com_solidres&view=system&layout=installsampledata'),
			4 => array( 'SR_SUBMENU_COUNTRY_LIST', 'index.php?option=com_solidres&view=countries'),
			5 => array( 'SR_SUBMENU_STATE_LIST', 'index.php?option=com_solidres&view=states'),
			6 => array( 'SR_SUBMENU_TAX_LIST', 'index.php?option=com_solidres&view=taxes'),
			7 => array( 'SR_SUBMENU_EMPLOYEES', 'index.php?option=com_users'),
			8 => array( 'SR_SUBMENU_LIMITBOOKINGS', 'index.php?option=com_solidres&view=limitbookings'),
			9 => array( 'SR_SUBMENU_FACILITIES', 'index.php?option=com_solidres&view=facilities'),
			10 => array( 'SR_SUBMENU_THEMES', 'index.php?option=com_solidres&view=themes'),
			11 => array( 'SR_SUBMENU_SYSTEM', 'index.php?option=com_solidres&view=system')
		);

		$html = '';
		$html .= '<div id="sr_panel_left" class="span2">';
		$html .= '<ul id="sr_side_navigation">';
		
		$html .= '<li class="sr_tools">
					<a id="sr_dashboard" title="'.JText::_('SR_SUBMENU_DASHBOARD').'"
					   href="'.JRoute::_('index.php?option=com_solidres').'">
					   <img src="'.JUri::root().'media/com_solidres/assets/images/logo.png" alt="Solidres" title="Solidres" />
					</a>
					<a id="sr_current_ver">'.SRVersion::getShortVersion().'</a>
				  </li>';

		$iconMap = array(
			'asset' => 'icon-home',
			'customer' => 'icon-user',
			'reservation' => 'icon-key',
			'coupon_extra' => 'icon-file-add',
			'customer_feedback' => 'icon-comments-2',
			'system' => 'icon-wrench'
		);

		foreach ($menuStructure as $menuName => $menuDetails)
		{
			$html .= '<li class="sr_toggle" id="sr_sn_'.strtolower(substr($menuName, 11)).'"><a class="sr_indicator">Open</a><a class="sr_title"><i class="'. $iconMap[strtolower(substr($menuName, 11))] .'"></i> '.JText::_($menuName).'</a>';
			$html .= '<ul>';
			foreach ($menuDetails as $menu)
			{
				if ((substr($menu[1], 30, 4) == 'view'))
				{
					$html .= '<li class="'.(substr($menu[1], 35) == $viewName ? 'active': '').'">';
				}
				else
				{
					$html .= '<li class="">';
				}
				$html .= '<a id="'.strtolower($menu[0]).'" href="'.JRoute::_($link.$menu[1]).'">'.JText::_($menu[0]).'</a></li>';
			}
			$html .= '</ul>';
			$html .= '</li>';
		}

		$html .= '</ul>';
		$html .= '</div>';

		return $html;
	}
}