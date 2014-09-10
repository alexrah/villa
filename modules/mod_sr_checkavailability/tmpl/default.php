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

JHtml::_('behavior.framework');
$config = JFactory::getConfig();
$solidresUtilities = SRFactory::get('solidres.utilities.utilities');
$tzoffset = $config->get('offset');
$timezone = new DateTimeZone($tzoffset);
$dateCheckIn = JDate::getInstance();
$dateCheckOut = JDate::getInstance();
$solidresConfig = JComponentHelper::getParams('com_solidres');
$minDaysBookInAdvance = $solidresConfig->get('min_days_book_in_advance', 0);
$maxDaysBookInAdvance = $solidresConfig->get('max_days_book_in_advance', 0);
$minLengthOfStay = $solidresConfig->get('min_length_of_stay', 1);
$datePickerMonthNum = $solidresConfig->get('datepicker_month_number', 3);
$weekStartDay = $solidresConfig->get('week_start_day', 1);
$dateFormat = $solidresConfig->get('date_format', 'd-m-Y');
$jsDateFormat = $solidresUtilities::convertDateFormatPattern($dateFormat);
// These variables are used to set the defaultDate of datepicker
$defaultCheckinDate = isset($checkin) ? JDate::getInstance($checkin, $timezone)->format('Y-m-d', true) : '';
$defaultCheckoutDate = isset($checkout) ? JDate::getInstance($checkout, $timezone)->format('Y-m-d', true) : '';
if (!empty($defaultCheckinDate)) :
	$defaultCheckinDateArray = explode('-', $defaultCheckinDate);
	$defaultCheckinDateArray[1] -= 1; // month in javascript is less than 1 in compare with month in PHP
endif;

if (!empty($defaultCheckoutDate)) :
	$defaultCheckoutDateArray = explode('-', $defaultCheckoutDate);
	$defaultCheckoutDateArray[1] -= 1; // month in javascript is less than 1 in compare with month in PHP
endif;

$doc = JFactory::getDocument();
JHtml::_('script', SRURI_MEDIA.'/assets/js/datePicker/localization/jquery.ui.datepicker-'.JFactory::getLanguage()->getTag().'.js', false, false);
$doc->addScriptDeclaration('
	Solidres.jQuery(function($) {
		var minLengthOfStay = '.$minLengthOfStay.';
		var checkout = $(".checkout_datepicker_inline_module").datepicker({
			minDate : "+' . ( $minDaysBookInAdvance + $minLengthOfStay ). '",
			numberOfMonths : '.$datePickerMonthNum.',
			showButtonPanel : true,
			dateFormat : "'.$jsDateFormat.'",
			firstDay: '.$weekStartDay.',
			' . (isset($checkout) ? 'defaultDate: new Date(' . implode(',' , $defaultCheckoutDateArray) .'),' : '') . '
			onSelect: function() {
				$("#sr-checkavailability-form input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", $(this).datepicker("getDate")));
				$("#sr-checkavailability-form .checkout_module").text($.datepicker.formatDate("'.$jsDateFormat.'", $(this).datepicker("getDate")));
				$(".checkout_datepicker_inline_module").slideToggle();
				$(".checkin_module").removeClass("disabledCalendar");
			}
		});
		var checkin = $(".checkin_datepicker_inline_module").datepicker({
			minDate : "+' .  $minDaysBookInAdvance . 'd",
			'.($maxDaysBookInAdvance > 0 ? 'maxDate: "+'. ($maxDaysBookInAdvance) . '",' : '' ).'
			numberOfMonths : '.$datePickerMonthNum.',
			showButtonPanel : true,
			dateFormat : "'.$jsDateFormat.'",
			'. (isset($checkin) ? 'defaultDate: new Date(' . implode(',' , $defaultCheckinDateArray) .'),' : '') . '
			onSelect : function() {
				var currentSelectedDate = $(this).datepicker("getDate");
				var checkoutMinDate = $(this).datepicker("getDate", "+1d");
				checkoutMinDate.setDate(checkoutMinDate.getDate() + minLengthOfStay);
				checkout.datepicker( "option", "minDate", checkoutMinDate );
				checkout.datepicker( "setDate", checkoutMinDate);

				$("#sr-checkavailability-form input[name=\'checkin\']").val($.datepicker.formatDate("yy-mm-dd", currentSelectedDate));
				$("#sr-checkavailability-form input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", checkoutMinDate));

				$("#sr-checkavailability-form .checkin_module").text($.datepicker.formatDate("'.$jsDateFormat.'", currentSelectedDate));
				$("#sr-checkavailability-form .checkout_module").text($.datepicker.formatDate("'.$jsDateFormat.'", checkoutMinDate));
				$(".checkin_datepicker_inline_module").slideToggle();
				$(".checkout_module").removeClass("disabledCalendar");
			},
			firstDay: '.$weekStartDay.'
		});
		$(".ui-datepicker").addClass("notranslate");
		$(".checkin_module").click(function() {
			if (!$(this).hasClass("disabledCalendar")) {
				$(".checkin_datepicker_inline_module").slideToggle("slow", function() {
					if ($(this).is(":hidden")) {
						$(".checkout_module").removeClass("disabledCalendar");
					} else {
						$(".checkout_module").addClass("disabledCalendar");
					}
				});
			}
		});
	
		$(".checkout_module").click(function() {
			if (!$(this).hasClass("disabledCalendar")) {
				$(".checkout_datepicker_inline_module").slideToggle("slow", function() {
					if ($(this).is(":hidden")) {
						$(".checkin_module").removeClass("disabledCalendar");
					} else {
						$(".checkin_module").addClass("disabledCalendar");
					}
				});
			}
		});
    });
');
?>
<div class="row-fluid">
    <form id="sr-checkavailability-form" action="<?php echo JRoute::_('index.php#form', false)?>" method="GET" class="form-stacked sr-validate">
    	<fieldset>
    		<input name="id" value="<?php echo $default->id ?>" type="hidden" />

			<div class="span12">
				<label for="checkin">
					<?php echo JText::_('SR_SEARCH_CHECKIN_DATE')?>
				</label>
				<div class="checkin_module datefield">
					<?php echo isset($checkin) ?
						JDate::getInstance($checkin, $timezone)->format($dateFormat, true) :
						$dateCheckIn->add(new DateInterval('P'.($minDaysBookInAdvance).'D'))->setTimezone($timezone)->format($dateFormat, true) ?>
				</div>
				<div class="checkin_datepicker_inline_module datepicker_inline" style="display: none"></div>
				<?php // this field must always be "Y-m-d" as it is used internally only ?>
				<input type="hidden" name="checkin" value="<?php echo isset($checkin) ?
					JDate::getInstance($checkin, $timezone)->format('Y-m-d', true) :
					$dateCheckIn->add(new DateInterval('P'.($minDaysBookInAdvance).'D'))->setTimezone($timezone)->format('Y-m-d', true) ?>" />
            </div>

            <div class="span12">
				<label for="checkout">
					<?php echo JText::_('SR_SEARCH_CHECKOUT_DATE')?>
				</label>
				<div class="checkout_module datefield">
					<?php echo isset($checkout) ?
						JDate::getInstance($checkout, $timezone)->format($dateFormat, true) :
						$dateCheckOut->add(new DateInterval('P'.($minDaysBookInAdvance + $minLengthOfStay).'D'))->setTimezone($timezone)->format($dateFormat, true)
					?>
				</div>
				<div class="checkout_datepicker_inline_module datepicker_inline" style="display: none"></div>
				<?php // this field must always be "Y-m-d" as it is used internally only ?>
				<input type="hidden" name="checkout" value="<?php echo isset($checkout) ?
					JDate::getInstance($checkout, $timezone)->format('Y-m-d', true) :
					$dateCheckOut->add(new DateInterval('P'.($minDaysBookInAdvance + $minLengthOfStay).'D'))->setTimezone($timezone)->format('Y-m-d', true) ?>" />
			</div>

            <div class="span12">
				<div class="action">
					<button class="btn primary" type="submit"><i class="icon-search"></i> <?php echo JText::_('SR_SEARCH')?></button>
					<button class="btn" type="reset"><?php echo JText::_('SR_RESET')?></button>
				</div>
            </div>

    	</fieldset>

    	<input type="hidden" name="option" value="com_solidres" />
    	<input type="hidden" name="task" value="reservationasset.checkavailability" />
		<input type="hidden" name="Itemid" value="<?php echo $params->get('target_itemid') ?>" />
    	<?php echo JHtml::_('form.token'); ?>
    </form>
</div>