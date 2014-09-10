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

abstract class SRHtmlJquery
{
	/**
	 * Method to load the jQuery UI framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery UI is included for easier debugging.
	 *
	 * @return  void
	 */
	public static function ui()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}
		JHtml::_('jquery.framework');
		$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';
		JHtml::_('stylesheet', SRURI_MEDIA.'/assets/css/jquery/themes/base/jquery-ui'.$uncompressed.'.css', false, false);
		JHtml::_('script', SRURI_MEDIA.'/assets/js/jquery/ui/jquery-ui'.$uncompressed.'.js', false, false);
		$loaded = true;
	}

	/**
	 * Method to load the jQuery Cookie into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery Cookie is included for easier debugging.
	 *
	 * @return  void
	 */
	public static function cookie()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}
		JHtml::_('script', SRURI_MEDIA.'/assets/js/jquery/external/jquery.cookie.js', false, false);
		$loaded = true;
	}

	/**
	 * Method to load the plupload into the document head
	 *
	 * If debugging mode is on an uncompressed version of plupload is included for easier debugging.
	 *
	 * @return  void
	 */
	public static function upload()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}
		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', SRURI_MEDIA.'/assets/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css', false, false);
		JHtml::_('script', SRURI_MEDIA.'/assets/js/plupload/plupload.full.min.js', false, false);
		JHtml::_('script', SRURI_MEDIA.'/assets/js/plupload/jquery.plupload.queue/jquery.plupload.queue.min.js', false, false);

		$activeLanguageTag = JFactory::getLanguage()->getTag();
		$allowedLanguageTags = array('ar-AA', 'bs-BA', 'ca-ES', 'cs-CZ', 'da-DK', 'de-DE', 'el-GR', 'en-AU', 'en-GB',
			'en-US', 'es-ES', 'et-EE', 'fa-IR', 'fi-FI', 'fr-FR', 'he-IL', 'hr-HR', 'hu-HU', 'it-IT', 'ja-JP', 'ko-KR',
			'lv-LV', 'nl-NL', 'pl-PL', 'pt-BR', 'ro-RO', 'ru-RU', 'sk-SK', 'sr-RS', 'sr-YU', 'sv-SE', 'th-TH', 'tr-TR',
			'uk-UA', 'zh-CN', 'zh-TW'
		);
		$showedLanguage = in_array($activeLanguageTag, $allowedLanguageTags) ? $activeLanguageTag : 'en-GB';

		JHtml::_('script', SRURI_MEDIA.'/assets/js/plupload/i18n/' . $showedLanguage . '.js', false, false);

		$loaded = true;
	}

	/**
	 * Method to load the colorbox into the document head
	 *
	 * If debugging mode is on an uncompressed version of colorbox is included for easier debugging.
	 *
	 * @param string $class
	 * @param string $width
	 * @param string $height
	 * @param string $iframe
	 * @param string $inline
	 *
	 * @return  void
	 */
	public static function colorbox($class = 'sr-iframe', $width = '80%', $height = '80%', $iframe = "true", $inline = "false")
	{
		static $loaded = false;
		if (!$loaded)
		{
			$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';
			JHtml::_('stylesheet', SRURI_MEDIA.'/assets/js/colorbox/colorbox.css', false, false);
			JHtml::_('script', SRURI_MEDIA.'/assets/js/colorbox/jquery.colorbox'.$uncompressed.'.js', false, false);

			$activeLanguageTag = JFactory::getLanguage()->getTag();
			$allowedLanguageTags = array('ar-AA', 'bg-BG', 'ca-ES', 'cs-CZ', 'da-DK', 'de-DE', 'el-GR', 'es-ES', 'et-EE',
				'fa-IR', 'fi-FI', 'fr-FR', 'he-IL', 'hr-HR', 'hu-HU', 'it-IT', 'ja-JP', 'ko-KR', 'lv-LV', 'nb-NO', 'nl-NL',
				'pl-PL', 'pt-BR', 'ro-RO', 'ru-RU', 'sk-SK', 'sr-RS', 'sv-SE', 'tr-TR', 'uk-UA', 'zh-CN', 'zh-TW'
			);

			// English is bundled into the source therefore we don't have to load it.
			if (in_array($activeLanguageTag, $allowedLanguageTags))
			{
				JHtml::_('script', SRURI_MEDIA.'/assets/js/colorbox/i18n/jquery.colorbox-' . $activeLanguageTag . '.js', false, false);
			}

			$script = '
				Solidres.jQuery(document).ready(function($){
					$(".'.$class.'").colorbox({iframe: '.$iframe.', inline: '.$inline.', width:"'.$width.'", height:"'.$height.'"});
				});
			';
			JFactory::getDocument()->addScriptDeclaration($script);
		}
		else
		{
			$script = '
				Solidres.jQuery(document).ready(function($){
					$(".'.$class.'").colorbox({iframe: '.$iframe.', inline: '.$inline.', width:"'.$width.'", height:"'.$height.'"});
				});
			';
			JFactory::getDocument()->addScriptDeclaration($script);
			return;
		}

		$loaded = true;
	}

	/**
	 * Method to load the datepicker into the document head
	 *
	 * @param string $format
	 *
	 * @return  void
	 */
	public static function datepicker($format = 'dd-mm-yy')
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}
		JHtml::_('script', SRURI_MEDIA.'/assets/js/datePicker/localization/jquery.ui.datepicker-'.JFactory::getLanguage()->getTag().'.js', false, false);
		$script = '
		Solidres.jQuery(function($) {
			$( ".datepicker" ).datepicker({
				dateFormat : "'.$format.'"
			});
			$(".datepicker").datepicker($.datepicker.regional["'.JFactory::getLanguage()->getTag().'"]);
			$(".ui-datepicker").addClass("notranslate");
		});';
		JFactory::getDocument()->addScriptDeclaration($script);

		$loaded = true;
	}

	public static function validate()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}
		$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';
		JHtml::_('jquery.framework');
		JHtml::_('script', SRURI_MEDIA.'/assets/js/validate/jquery.validate'.$uncompressed.'.js', false, false);

		$activeLanguageTag = JFactory::getLanguage()->getTag();
		$allowedLanguageTags = array('ar-AA', 'bg-BG', 'ca-ES', 'cs-CZ', 'da-DK', 'de-DE', 'el-GR', 'es-AR', 'es-ES', 'et-EE',
			'fa-IR', 'fi-FI', 'fr-FR', 'he-IL', 'hr-HR', 'hu-HU', 'it-IT', 'ja-JP', 'ko-KR', 'lv-LV', 'nb-NO', 'nl-NL',
			'pl-PL', 'pt-BR', 'ro-RO', 'ru-RU', 'sk-SK', 'sr-RS', 'sv-SE', 'th-TH', 'tr-TR', 'uk-UA', 'vi-VN', 'zh-CN', 'zh-TW'
		);

		// English is bundled into the source therefore we don't have to load it.
		if (in_array($activeLanguageTag, $allowedLanguageTags))
		{
			JHtml::_('script', SRURI_MEDIA.'/assets/js/validate/localization/messages_'.$activeLanguageTag.'.js', false, false);
		}


		$loaded = true;
	}

	/**
	 * Method to load jqplot
	 *
	 * If debugging mode is on an uncompressed version of jqplot is included for easier debugging.
	 *
	 * @param   array $plugins An array of plugin that needed to be loaded with jqplot
	 *
	 * @return  void
	 */
	public static function chart($plugins = array())
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}
		JHtml::_('jquery.framework');
		$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';
		JHtml::_('stylesheet', SRURI_MEDIA.'/assets/css/jquery.jqplot'.$uncompressed.'.css', false, false);
		JHtml::_('script', SRURI_MEDIA.'/assets/js/statistics/jquery.jqplot'.$uncompressed.'.js', false, false);
		if (!empty($plugins))
		{
			foreach ($plugins as $plugin)
			{
				JHtml::_('script', SRURI_MEDIA.'/assets/js/statistics/jqplot.'.$plugin.$uncompressed.'.js', false, false);
			}
		}

		$loaded = true;
	}

	/**
	 * Method to load the jquery editable into the document head
	 *
	 * @return  void
	 */
	public static function editable()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}
		$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';

		JHtml::_('jquery.framework');
		JHtml::_('bootstrap.framework');
		JHtml::_('stylesheet', SRURI_MEDIA.'/assets/css/bootstrap-editable.css', false, false);
		JHtml::_('script', SRURI_MEDIA.'/assets/js/editable/bootstrap-editable'.$uncompressed.'.js', false, false);
		$loaded = true;
	}

	/**
	 * Method to load the jquery editable into the document head
	 *
	 * @return  void
	 */
	public static function camera()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}
		$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';

		JHtml::_('jquery.framework');

		JHtml::_('stylesheet', SRURI_MEDIA.'/assets/css/camera.css', false, false);
		JHtml::_('script', SRURI_MEDIA.'/assets/js/camera/jquery.mobile.customized.min.js', false, false);
		JHtml::_('script', SRURI_MEDIA.'/assets/js/camera/jquery.easing.1.3.js', false, false);
		JHtml::_('script', SRURI_MEDIA.'/assets/js/camera/camera'.$uncompressed.'.js', false, false);
		$loaded = true;
	}

	/**
	 * Method to load the Sortable script and make table sortable
	 *
	 * @param   string   $tableId                 DOM id of the table
	 * @param   string   $formId                  DOM id of the form
	 * @param   string   $sortDir                 Sort direction
	 * @param   string   $saveOrderingUrl         Save ordering url, ajax-load after an item dropped
	 * @param   boolean  $proceedSaveOrderButton  Set whether a save order button is displayed
	 * @param   boolean  $nestedList              Set whether the list is a nested list
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function sortable($tableId, $formId, $sortDir = 'asc', $saveOrderingUrl, $proceedSaveOrderButton = true, $nestedList = false)
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}

		// Depends on jQuery UI
		//JHtml::_('jquery.ui', array('core', 'sortable'));

		JHtml::_('script', 'jui/sortablelist.js', false, true);
		JHtml::_('stylesheet', 'jui/sortablelist.css', false, true, false);

		// Attach sortable to document
		JFactory::getDocument()->addScriptDeclaration("
			(function ($){
				$(document).ready(function (){
					var sortableList = new $.JSortableList('#" . $tableId . " tbody','" . $formId . "','" . $sortDir . "' , '" . $saveOrderingUrl . "','','" . $nestedList . "');
				});
			})(jQuery);
			"
		);

		if ($proceedSaveOrderButton)
		{
			static::_proceedSaveOrderButton();
		}

		// Set static array
		$loaded = true;
		return;
	}

	/**
	 * Method to inject script for enabled and disable Save order button
	 * when changing value of ordering input boxes
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function _proceedSaveOrderButton()
	{
		JFactory::getDocument()->addScriptDeclaration(
			"(function ($){
				$(document).ready(function (){
					var saveOrderButton = $('.saveorder');
					saveOrderButton.css({'opacity':'0.2', 'cursor':'default'}).attr('onclick','return false;');
					var oldOrderingValue = '';
					$('.text-area-order').focus(function ()
					{
						oldOrderingValue = $(this).attr('value');
					})
					.keyup(function (){
						var newOrderingValue = $(this).attr('value');
						if (oldOrderingValue != newOrderingValue)
						{
							saveOrderButton.css({'opacity':'1', 'cursor':'pointer'}).removeAttr('onclick')
						}
					});
				});
			})(jQuery);"
		);
		return;
	}
}