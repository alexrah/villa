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

class plgSystemSolidres extends JPlugin
{
	function __construct($subject, $config = array()) 
	{
		parent::__construct($subject, $config);

		if ( file_exists(JPATH_LIBRARIES.'/solidres/defines.php') )
		{
			require_once JPATH_LIBRARIES.'/solidres/defines.php';
		}


		JLoader::import('libraries.solidres.factory', 	JPATH_ROOT);
		JLoader::import('libraries.solidres.html.html', JPATH_ROOT);
		JLoader::register('SRConfig', JPATH_LIBRARIES . '/solidres/config/config.php');
	}

	function onAfterRoute()
	{
		JHtml::_('jquery.framework');
		$isAdmin = JFactory::getApplication()->isAdmin();
		$isSite = JFactory::getApplication()->isSite();
		if (class_exists('SRHtml'))
		{
			SRHtml::_('js.noconflict');
			if ($isAdmin && JFactory::getApplication()->input->get('option') == 'com_solidres')
			{
					SRHtml::_('jquery.ui');
					SRHtml::_('js.admin');
			}
			if($isSite)
			{
				SRHtml::_('jquery.ui');
				SRHtml::_('js.site');
			}
			SRHtml::_('jquery.cookie');
			SRHtml::_('jquery.validate');
		}

		if (JPluginHelper::isEnabled('solidres', 'invoice'))
		{
			define('SR_PLUGIN_INVOICE_ENABLED', true);
			if (JFactory::getApplication()->isAdmin())
			{
				$lang = JFactory::getLanguage();
				$lang->load('plg_solidres_invoice', JPATH_ADMINISTRATOR, null, 1);
			}
		}
		else
		{
			define('SR_PLUGIN_INVOICE_ENABLED', false);
		}

		if (JPluginHelper::isEnabled('solidres', 'feedback'))
		{
			define('SR_PLUGIN_FEEDBACK_ENABLED', true);
		}
		else
		{
			define('SR_PLUGIN_FEEDBACK_ENABLED', false);
		}
		if (JPluginHelper::isEnabled('solidres', 'complextariff'))
		{
			define('SR_PLUGIN_COMPLEXTARIFF_ENABLED', true);
		}
		else
		{
			define('SR_PLUGIN_COMPLEXTARIFF_ENABLED', false);
		}

		if (JPluginHelper::isEnabled('solidres', 'limitbooking'))
		{
			define('SR_PLUGIN_LIMITBOOKING_ENABLED', true);
		}
		else
		{
			define('SR_PLUGIN_LIMITBOOKING_ENABLED', false);
		}

		if (JPluginHelper::isEnabled('solidres', 'hub'))
		{
			define('SR_PLUGIN_HUB_ENABLED', true);
			$lang = JFactory::getLanguage();
			$lang->load('com_solidres', JPATH_ADMINISTRATOR, null, 1);
		}
		else
		{
			define('SR_PLUGIN_HUB_ENABLED', false);
		}

		if (JPluginHelper::isEnabled('solidres', 'statistics'))
		{
			define('SR_PLUGIN_STATISTICS_ENABLED', true);
			if (JFactory::getApplication()->isAdmin() || (JFactory::getApplication()->isSite() && SR_PLUGIN_HUB_ENABLED))
			{
				$lang = JFactory::getLanguage();
				$lang->load('plg_solidres_statistics', JPATH_ADMINISTRATOR, null, 1);
				SRHtml::_('js.statistics');
			}
		}
		else
		{
			define('SR_PLUGIN_STATISTICS_ENABLED', false);
		}

		if (JPluginHelper::isEnabled('user', 'solidres'))
		{
			$extTable = JTable::getInstance('Extension');
			$extTable->load(array('name' => 'plg_user_solidres'));
			if ($extTable->extension_id > 0)
			{
				$pluginInfo = json_decode($extTable->manifest_cache);

				// Only enable when the installed user plugin is greater or equal 0.8.0
				if (version_compare($pluginInfo->version, '0.8.0', 'ge'))
				{
					define('SR_PLUGIN_USER_ENABLED', true);
					$lang = JFactory::getLanguage();
					$lang->load('plg_user_solidres', JPATH_ADMINISTRATOR, null, 1);
				}
				else
				{
					define('SR_PLUGIN_USER_ENABLED', false);
				}
			}
		}
		else
		{
			define('SR_PLUGIN_USER_ENABLED', false);
		}
	}

	function onAfterRender()
	{
		/* Translate custom field by using language tag. Author: isApp.it Team */
		// TODO consolidate into SRUtilitises class
		if (JFactory::getApplication()->isAdmin() ) return true;

		$buffer = JResponse::getBody();

		if ( strpos( $buffer, '{lang' ) === false ) return true;

		$regexTextarea = "#<textarea(.*?)>(.*?)<\/textarea>#is";
		$regexInput = "#<input(.*?)>#is";

		$matches = array();
		preg_match_all($regexTextarea, $buffer, $matches, PREG_SET_ORDER);
		$textarea = array();
		foreach ($matches as $key => $match) {
			if(strpos( $match[0], '{lang' ) !== false) {
				$textarea[$key] = $match[0];
				$buffer = str_replace($textarea[$key], '~^t'.$key.'~', $buffer);
			}
		}

		$matches = array();
		preg_match_all($regexInput, $buffer, $matches, PREG_SET_ORDER);
		$input = array();
		foreach ($matches as $key => $match) {
			if(
				(strpos( $match[0], 'type="password"' ) !== false ||
				strpos( $match[0], 'type="text"' ) !== false) &&
				strpos( $match[0], '{lang' ) !== false) {
				$input[$key] = $match[0];
				$buffer = str_replace($input[$key], '~^i'.$key.'~', $buffer);
			}
		}

		if (strpos( $buffer, '{lang' ) !== false) {
			$buffer = plgSystemSolidres::filterText($buffer);

			if ($textarea) {
				foreach ($textarea as $key => $t) {
					$buffer = str_replace('~^t'.$key.'~', $t, $buffer);
				}
				unset($textarea);
			}
			if ($input) {
				foreach ($input as $key => $i) {
					$buffer = str_replace('~^i'.$key.'~', $i, $buffer);
				}
				unset($input);
			}
			JResponse::setBody($buffer);
		}

		unset($buffer);
	}

	/* Translate custom field by using language tag. Author: isApp.it Team */
	// TODO consolidate into SRUtilitises class
	static function getLagnCode()
	{
		$lang_codes = JLanguageHelper::getLanguages('lang_code');
		$lang_code 	= $lang_codes[JFactory::getLanguage()->getTag()]->sef;
		return $lang_code;
	}

	/* Translate custom field by using language tag. Author: isApp.it Team */
	// TODO consolidate into SRUtilitises class
	static function filterText($text)
	{
		if ( strpos( $text, '{lang' ) === false ) return $text;
		$lang_code = plgSystemSolidres::getLagnCode();
		$regex = "#{lang ".$lang_code."}(.*?){\/lang}#is";
		$text = preg_replace($regex,'$1', $text);
		$regex = "#{lang [^}]+}.*?{\/lang}#is";
		$text = preg_replace($regex,'', $text);
		return $text;
	}
}