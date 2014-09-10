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
class pkg_solidresInstallerScript
{
	function install($parent)
	{
	}

	function uninstall($parent)
	{
		// Also uninstall sample media file package
		$this->dbo = JFactory::getDbo();
		$query = $this->dbo->getQuery(true);
		$query->delete()->from('#__extensions')->where('name LIKE '.$this->dbo->quote('files_solidres_media'));
		$this->dbo->setQuery($query);
		$this->dbo->execute();
		$mediaLangFile = JPATH_SITE.'/language/en-GB/en-GB.files_solidres_media.sys.ini';
		if (JFile::exists($mediaLangFile))
		{
			JFile::delete($mediaLangFile);
		}

		// Remove content elements files
		$destinationDir = JPATH_SITE . '/administrator/components/com_falang/contentelements/';
		$contentElementFiles = array('sr_coupons.xml', 'sr_extras.xml', 'sr_reservation_assets.xml', 'sr_room_types.xml');
		foreach($contentElementFiles as $file)
		{
			$target = $destinationDir . $file;
			if (JFile::exists($target))
			{
				JFile::delete($target);
			}
		}
	}

	function update($parent)
	{
		$filelist = array(
			// From 0.5.0
			JPATH_SITE . '/administrator/components/com_solidres/models/fields/price.php',
			//JPATH_SITE . '/administrator/components/com_solidres/views/roomtype/tmpl/edit_tariff.php',
			JPATH_SITE . '/components/com_solidres/models/form/index.html',
			JPATH_SITE . '/components/com_solidres/models/form/reservation.xml',
			JPATH_SITE . '/components/com_solidres/models/reservation.php',
			JPATH_SITE . '/components/com_solidres/views/reservation/tmpl/default.php',
			JPATH_SITE . '/components/com_solidres/views/reservation/tmpl/default_confirmation.php',
			JPATH_SITE . '/components/com_solidres/views/reservation/tmpl/default_guest.php',
			JPATH_SITE . '/components/com_solidres/views/reservation/tmpl/default_payment.php',
			JPATH_SITE . '/components/com_solidres/views/reservation/tmpl/default_room.php',
			JPATH_SITE . '/components/com_solidres/views/reservation/tmpl/default_summary.php',
			JPATH_SITE . '/components/com_solidres/views/reservation/tmpl/processing.php',
			JPATH_SITE . '/media/com_solidres/assets/css/main-uncompressed.css',
			JPATH_SITE . '/media/com_solidres/assets/images/system/index.html',
			JPATH_SITE . '/media/com_solidres/assets/images/system/thumbnails/1/index.html',
			JPATH_SITE . '/media/com_solidres/assets/images/system/thumbnails/2/index.html',
			JPATH_SITE . '/media/com_solidres/assets/images/system/thumbnails/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_ar.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_bg.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_ca.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_cs.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_da.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_de.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_el.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_es.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_et.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_fa.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_fi.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_fr.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_he.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_hr.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_hu.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_it.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_ja.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_ka.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_kk.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_lt.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_lv.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_nl.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_no.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_pl.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_pt_BR.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_pt_PT.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_ro.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_ru.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_si.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_sk.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_sl.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_sr.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_sv.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_th.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_tr.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_uk.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_vi.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_zh.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_zh_TW.js',
			// From 0.6.0
			JPATH_SITE . '/administrator/components/com_solidres/controllers/categories.php',
			JPATH_SITE . '/administrator/components/com_solidres/controllers/category.json.php',
			JPATH_SITE . '/administrator/components/com_solidres/controllers/category.php',
			JPATH_SITE . '/administrator/components/com_solidres/controllers/tariff.json.php',
			JPATH_SITE . '/administrator/components/com_solidres/controllers/tariffs.json.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/fields/categories.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/categories.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/category.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/fields/modal/article.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/forms/category.xml',
			JPATH_SITE . '/administrator/components/com_solidres/models/tables/category.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/view/system/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/models/view/system/view.file.php',
			JPATH_SITE . '/components/com_solidres/router.php',
			JPATH_SITE . '/libraries/solidres/index.html',
			JPATH_SITE . '/libraries/solidres/nestedsetmodel/index.html',
			JPATH_SITE . '/libraries/solidres/nestedsetmodel/node.php',
			JPATH_SITE . '/libraries/solidres/system/backup.php',
			JPATH_SITE . '/libraries/solidres/system/index.html',
			JPATH_SITE . '/libraries/solidres/utilities/ziparchive.php',
			JPATH_SITE . '/media/com_solidres/assets/images/res-process.png',
			JPATH_SITE . '/media/com_solidres/assets/images/stars.gif',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/plupload.browserplus.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/plupload.flash.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/plupload.flash.swf',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/plupload.full.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/plupload.gears.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/plupload.html4.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/plupload.html5.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/plupload.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/plupload.silverlight.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/plupload.silverlight.xap',
			// From 0.7.0
			JPATH_SITE . '/administrator/components/com_solidres/controllers/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/helpers/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/models/fields/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/models/fields/modal/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/models/fields/ordering.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/forms/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/models/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/tables/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/countries/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/countries/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/country/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/country/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/coupon/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/coupon/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/coupons/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/coupons/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/currencies/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/currencies/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/currency/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/currency/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/customer/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/customer/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/customergroup/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/customergroup/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/customergroups/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/customergroups/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/customers/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/customers/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/extra/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/extra/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/extras/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/extras/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/medialist/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/medialist/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/reservation/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/reservation/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/reservationasset/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/reservationasset/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/reservationassets/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/reservationassets/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/reservations/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/reservations/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/roomtype/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/roomtype/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/roomtypes/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/roomtypes/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/state/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/state/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/states/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/states/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/tax/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/tax/tmpl/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/taxes/index.html',
			JPATH_SITE . '/administrator/components/com_solidres/views/taxes/tmpl/index.html',
			JPATH_SITE . '/components/com_solidres/controllers/index.html',
			JPATH_SITE . '/components/com_solidres/helpers/index.html',
			JPATH_SITE . '/components/com_solidres/index.html',
			JPATH_SITE . '/components/com_solidres/models/fields/index.html',
			JPATH_SITE . '/components/com_solidres/models/index.html',
			JPATH_SITE . '/components/com_solidres/views/customer/index.html',
			JPATH_SITE . '/components/com_solidres/views/customer/tmpl/index.html',
			JPATH_SITE . '/components/com_solidres/views/index.html',
			JPATH_SITE . '/components/com_solidres/views/map/index.html',
			JPATH_SITE . '/components/com_solidres/views/map/tmpl/index.html',
			JPATH_SITE . '/components/com_solidres/views/media/index.html',
			JPATH_SITE . '/components/com_solidres/views/media/tmpl/default.php',
			JPATH_SITE . '/components/com_solidres/views/media/tmpl/index.html',
			JPATH_SITE . '/components/com_solidres/views/media/view.html.php',
			JPATH_SITE . '/components/com_solidres/views/reservation/index.html',
			JPATH_SITE . '/components/com_solidres/views/reservation/tmpl/index.html',
			JPATH_SITE . '/components/com_solidres/views/reservationasset/index.html',
			JPATH_SITE . '/components/com_solidres/views/reservationasset/tmpl/index.html',
			JPATH_SITE . '/language/en-GB/index.html',
			JPATH_SITE . '/language/index.html',
			JPATH_SITE . '/libraries/language/en-GB/index.html',
			JPATH_SITE . '/libraries/language/index.html',
			JPATH_SITE . '/libraries/solidres/config/index.html',
			JPATH_SITE . '/libraries/solidres/coupon/index.html',
			JPATH_SITE . '/libraries/solidres/currency/index.html',
			JPATH_SITE . '/libraries/solidres/html/index.html',
			JPATH_SITE . '/libraries/solidres/mail/en-GB/index.html',
			JPATH_SITE . '/libraries/solidres/mail/index.html',
			JPATH_SITE . '/libraries/solidres/media/getid3/index.html',
			JPATH_SITE . '/libraries/solidres/media/index.html',
			JPATH_SITE . '/libraries/solidres/media/zebra/index.html',
			JPATH_SITE . '/libraries/solidres/reservation/index.html',
			JPATH_SITE . '/libraries/solidres/roomtype/index.html',
			JPATH_SITE . '/libraries/solidres/user/index.html',
			JPATH_SITE . '/libraries/solidres/utilities/index.html',
			JPATH_SITE . '/media/com_solidres/assets/audio/index.html',
			JPATH_SITE . '/media/com_solidres/assets/css/index.html',
			JPATH_SITE . '/media/com_solidres/assets/css/jquery/index.html',
			JPATH_SITE . '/media/com_solidres/assets/css/jquery/themes/base/images/index.html',
			JPATH_SITE . '/media/com_solidres/assets/css/jquery/themes/base/index.html',
			JPATH_SITE . '/media/com_solidres/assets/css/jquery/themes/index.html',
			JPATH_SITE . '/media/com_solidres/assets/images/index.html',
			JPATH_SITE . '/media/com_solidres/assets/images/socials/index.html',
			JPATH_SITE . '/media/com_solidres/assets/images/system/index.html',
			JPATH_SITE . '/media/com_solidres/assets/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/colorbox/images/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/colorbox/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/jquery/external/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/jquery/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/jquery/ui/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/bs.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/cs.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/cy.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/da.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/de.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/el.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/en.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/es.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/et.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/fa.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/fi.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/fr-ca.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/fr.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/hr.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/hu.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/hy.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/it.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/ja.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/ka.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/ko.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/lt.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/lv.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/nl.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/pl.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/pt-br.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/pt_BR.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/ro.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/ru.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/sk.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/sr.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/sv.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/th_TH.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/tr.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/uk_UA.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/i18n/zh_CN.js',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/jquery.plupload.queue/css/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/jquery.plupload.queue/img/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/jquery.plupload.queue/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/jquery.ui.plupload/css/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/jquery.ui.plupload/img/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/plupload/jquery.ui.plupload/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/jquery.metadata.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/index.html',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_en-GB.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_eu.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_ka-GE.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_kk-KZ.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_lt-LT.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_my-MY.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_si-SI.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_sl-SL.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/messages_sr-YU.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/methods_de.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/methods_nl.js',
			JPATH_SITE . '/media/com_solidres/assets/js/validate/localization/methods_pt.js',
			JPATH_SITE . '/media/com_solidres/index.html',
			JPATH_SITE . '/media/com_solidres/assets/images/sep.png',
			JPATH_SITE . '/modules/mod_sr_camera/index.html',
			JPATH_SITE . '/modules/mod_sr_camera/tmpl/index.html',
			JPATH_SITE . '/modules/mod_sr_checkavailability/index.html',
			JPATH_SITE . '/modules/mod_sr_checkavailability/tmpl/index.html',
			JPATH_SITE . '/modules/mod_sr_currency/index.html',
			JPATH_SITE . '/modules/mod_sr_currency/tmpl/index.html',
			JPATH_SITE . '/modules/mod_sr_roomtypes/index.html',
			JPATH_SITE . '/modules/mod_sr_roomtypes/tmpl/index.html',
			JPATH_SITE . '/plugins/content/index.html',
			JPATH_SITE . '/plugins/content/solidres/index.html',
			JPATH_SITE . '/plugins/content/solidres/language/en-GB/index.html',
			JPATH_SITE . '/plugins/extension/index.html',
			JPATH_SITE . '/plugins/extension/solidres/fields/index.html',
			JPATH_SITE . '/plugins/extension/solidres/index.html',
			JPATH_SITE . '/plugins/extension/solidres/language/en-GB/index.html',
			JPATH_SITE . '/plugins/extension/solidres/language/index.html',
			JPATH_SITE . '/plugins/solidres/camera_slideshow/index.html',
			JPATH_SITE . '/plugins/solidres/complextariff/media/com_solidres/assets/js/angular/angular.1.0.7.js',
			JPATH_SITE . '/plugins/solidres/complextariff/media/com_solidres/assets/js/angular/angular.1.0.7.min.js',
			JPATH_SITE . '/plugins/solidres/complextariff/media/com_solidres/assets/js/angular/angular.min.1.0.7.js',
			JPATH_SITE . '/plugins/solidres/complextariff/media/com_solidres/assets/js/angular/angular.min.1.0.7.min.js',
			JPATH_SITE . '/plugins/solidres/hub/layouts/com_solidres/hub/filter.php',
			JPATH_SITE . '/plugins/solidres/hub/layouts/com_solidres/hub/navbar.php',
			JPATH_SITE . '/plugins/solidres/hub/layouts/com_solidres/hub/searchresultsgridview.php',
			JPATH_SITE . '/plugins/solidres/hub/layouts/com_solidres/hub/searchresultslistview.php',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/en-GB/email.html',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/en-GB/paid.html',
			JPATH_SITE . '/plugins/solidres/simple_gallery/index.html',
			JPATH_SITE . '/plugins/solidres/statistics/administrator/components/com_solidres/views/statistics/index.html',
			JPATH_SITE . '/plugins/solidres/statistics/administrator/components/com_solidres/views/statistics/tmpl/index.html',
			JPATH_SITE . '/plugins/solidres/statistics/index.html',
			JPATH_SITE . '/plugins/solidres/statistics/language/en-GB/index.html',
			JPATH_SITE . '/plugins/solidres/statistics/language/index.html',
			JPATH_SITE . '/plugins/system/index.html',
			JPATH_SITE . '/plugins/system/solidres/index.html',
			JPATH_SITE . '/plugins/system/solidres/language/en-GB/index.html',
			JPATH_SITE . '/plugins/user/solidres/index.html',
			JPATH_SITE . '/plugins/user/solidres/language/en-GB/index.html',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/tcpdf/cache/index.html',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/tcpdf/config/index.html',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/tcpdf/config/tcpdf_config_alt.php',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/tcpdf/fonts/index.html',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/tcpdf/include/barcodes/index.html',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/tcpdf/include/index.html',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/tcpdf/index.html',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/tcpdf/tcpdf.xml',
			JPATH_SITE . '/administrator/components/com_solidres/controllers/customer.json.php',
			JPATH_SITE . '/administrator/components/com_solidres/controllers/customer.php',
			JPATH_SITE . '/administrator/components/com_solidres/controllers/customergroup.php',
			JPATH_SITE . '/administrator/components/com_solidres/controllers/customergroups.json.php',
			JPATH_SITE . '/administrator/components/com_solidres/controllers/customergroups.php',
			JPATH_SITE . '/administrator/components/com_solidres/controllers/customers.json.php',
			JPATH_SITE . '/administrator/components/com_solidres/controllers/customers.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/fields/customergroup.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/forms/customer.xml',
			JPATH_SITE . '/administrator/components/com_solidres/models/forms/customergroup.xml',
			JPATH_SITE . '/administrator/components/com_solidres/models/customer.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/customergroup.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/customergroups.php',
			JPATH_SITE . '/administrator/components/com_solidres/models/customers.php'
		);

		foreach ($filelist as $file)
		{
			if(JFile::exists($file))
			{
				JFile::delete($file);
			}
		}

		$folderList = array(
			// From 0.5.0
			JPATH_SITE . '/libraries/solidres/swift',
			// From 0.6.0
			JPATH_SITE . '/administrator/components/com_solidres/liveupdate',
			JPATH_SITE . '/administrator/components/com_solidres/views/categories',
			JPATH_SITE . '/administrator/components/com_solidres/views/category',
			// From 0.7.0
			JPATH_SITE . '/libraries/solidres/invoice',
			JPATH_SITE . '/libraries/solidres/mail/en-GB',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/tcpdf/config/cert',
			JPATH_SITE . '/plugins/solidres/invoice/libraries/solidres/invoice/tcpdf/config/lang',
			JPATH_SITE . '/administrator/components/com_solidres/views/customer',
			JPATH_SITE . '/administrator/components/com_solidres/views/customergroup'
		);

		foreach ($folderList as $folder)
		{
			if(JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}
		}
	}

	function preflight($type, $parent)
	{
	}

	function postflight($type, $parent, $results)
	{
		// Install content elements files
		$destinationDir = JPATH_SITE . '/administrator/components/com_falang/contentelements/';
		$sourceDir = JPATH_SITE . '/administrator/components/com_solidres/falang/';

		if (JFolder::exists($destinationDir))
		{
			$files = JFolder::files($sourceDir);
			if(!empty($files))
			{
				foreach($files as $file)
				{
					JFile::copy($sourceDir . $file, $destinationDir . $file);
				}
			}
		}


		echo '
		<style>
			.solidres-installation-result {
				margin: 15px 0;
			}
			.solidres-installation-result .solidres-ext {
				padding: 8px;
				border-left: 3px solid #63B75D;
				background: #EEE;
				margin: 0 0 2px 0;
			}
			.solidres-installation-result label {
				font-weight: bold;
				margin-bottom: 0;
				display: inline-block;

			}
			.solidres-installation-result ul {
				margin: 20px 0 20px 10px;
			}

			.solidres-installation-result ul li {
				list-style: none;
			}

			.solidres-installation-result dl dd,
			 .solidres-installation-result dl dt{
				margin-bottom: 5px;
			}
		</style>
		';

		echo '<div class="row-fluid solidres-installation-result">
				<div class="span6">
					<img src="'. JUri::root() .'/media/com_solidres/assets/images/logo_black.png" width="250" height="52" alt="Solidres\'s logo"/>
					<dl>
						<dt>Solidres 0.7.1 has been installed/upgraded successfully.</dt>
						<dd><span class="badge badge-success">1</span> Please visit our Blog for full change log (new features, bug fixes, improvements, ...) of this version.</dd>
						<dd><span class="badge badge-success">2</span> If you are a Solidres\'s subscriber, don\' forget to reinstall all plugins (Complex Tariff, Invoice, Limit Booking, Hub etc) to ensure maximum compatibility with new version.</dd>
						<dd><span class="badge badge-success">3</span> Make sure that you visit our website to find new releases for your installed solidres\'s plugins and update them as well (if available).</dd>
						<dd><span class="badge badge-success">4</span> Make a test reservation to make sure everything works normally.</dd>
					</dl>
					<dl>
						<dt>Useful links</dt>
						<dd><a href="index.php?option=com_solidres&view=system" target="_blank">Your Solidres system page</a></dd>
						<dd><a href="http://www.solidres.com" target="_blank">Solidres Official Website</a></dd>
						<dd><a href="http://www.solidres.com/documentation" target="_blank">Solidres Documentation Site</a></dd>
						<dd><a href="http://www.solidres.com/support/frequently-asked-questions" target="_blank">Frequently asked questions</a></dd>
						<dd><a href="http://www.solidres.com/forum/index" target="_blank">Solidres Community Forum</a></dd>
						<dd><a href="https://www.solidres.com/subscribe/levels" target="_blank">Become a subscriber to access more features and official support</a></dd>
					</dl>
					<p><a href="'.JUri::root().'/administrator/index.php?option=com_solidres" class="btn btn-primary"><i class="icon-out "></i> Go to Solidres now</a></p>
			   	</div>
				<div class="span6">';
		foreach ($results as $result)
		{
			echo '<div class="solidres-ext '.($result['result'] == true ? 'ok' : 'not-ok' ).'">';
			echo '<label>' . $result['name'] . '</label>';
			echo ' has been ' . ($type == 'install' ? 'installed' : 'upgraded' );
			echo ($result['result'] == true ?  ' successfully' : ' failed'  ) . '</div>';
		}
		echo ' </div>
			</div>';
	}
}
