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

abstract class SRHtmlJs
{
	/**
	 * Method to load the jQuery UI framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery UI is included for easier debugging.
	 *
	 * @return  void
	 */
	public static function site()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}

		$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';
		
		JHtml::_('script', SRURI_MEDIA.'/assets/js/site'.$uncompressed.'.js', false, false);
		$loaded = true;
	}

	/**
	 * Method to load the jQuery UI framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery UI is included for easier debugging.
	 *
	 * @return  void
	 */
	public static function admin()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}

		$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';
		
		JHtml::_('script', SRURI_MEDIA.'/assets/js/admin'.$uncompressed.'.js', false, false);
		$loaded = true;
	}

	/**
	 * Method to load the call jquery noconflict mode
	 *
	 * @return  void
	 */
	public static function noconflict()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}

		JHtml::_('script', SRURI_MEDIA.'/assets/js/noconflict.js', false, false);
		$loaded = true;
	}
	/*Method to load statistics.js*/
	public static function statistics()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}

		$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';

		JHtml::_('script', SRURI_MEDIA.'/assets/js/statistics'.$uncompressed.'.js', false, false);
		$loaded = true;
	}

	/**
	 * Method to load Angular JS into the document head
	 *
	 * If debugging mode is on an uncompressed version of Angular JS is included for easier debugging.
	 *
	 * @param $extras
	 *
	 * @return  void
	 */
	public static function angular($extras = array())
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}

		$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';

		JHtml::_('script', SRURI_MEDIA.'/assets/js/angular/angular'.$uncompressed.'.js', false, false);
		if (!empty($extras))
		{
			foreach ($extras as $extra)
			{
				JHtml::_('script', SRURI_MEDIA.'/assets/js/angular/angular-'.$extra.$uncompressed.'.js', false, false);
			}
		}

		$loaded = true;
	}

	public static function complextariff()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}
		$langTag = JFactory::getLanguage()->getTag();

		if (!file_exists(JPATH_ROOT . '/media/com_solidres/assets/js/angular/localization/' . $langTag . '.js'))
		{
			$langTag = 'en-GB';
		}

		SRHtml::_('js.angular', array('route'));
		$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';
		JHtml::_('script', SRURI_MEDIA.'/assets/js/complextariff'.$uncompressed.'.js', false, false);
		JHtml::_('script', SRURI_MEDIA.'/assets/js/angular/localization/'.$langTag.'.js', false, false);

		$loaded = true;
	}
}