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

JLoader::register('SolidresHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/helper.php');

/**
 * HTML View class for the Solidres component
 *
 * @package     Solidres
 * @since		0.1.0
 */
class SolidresViewReservationAsset extends JViewLegacy
{
	protected $item;
	protected $solidresCurrency;

    function display($tpl = null)
	{
		$model = $this->getModel();
		$this->config = JComponentHelper::getParams('com_solidres');
		$this->systemConfig = JFactory::getConfig();
		$this->defaultGallery = $this->config->get('default_gallery', 'simple_gallery');
		$this->showPoweredByLink = $this->config->get('show_solidres_copyright', '1');
		$app = JFactory::getApplication();

		$this->item	= $model->getItem();
		$this->checkin = $model->getState('checkin');
		$this->checkout = $model->getState('checkout');
		$this->adults = $model->getState('adults');
		$this->children = $model->getState('children');
		$this->countryId = $model->getState('country_id');
		$this->geoStateId = $model->getState('geo_state_id');
		$this->roomTypeObj = SRFactory::get('solidres.roomtype.roomtype');
		$this->numberOfNights = $this->roomTypeObj->calculateDateDiff($this->checkin, $this->checkout);
		$this->document = JFactory::getDocument();
		$this->context = 'com_solidres.reservation.process';
		$this->coupon  = JFactory::getApplication()->getUserState($this->context . '.coupon');
		$this->tzoffset = $this->systemConfig->get('offset');
		$this->selectedRoomTypes = $app->getUserState($this->context . '.room');
		$this->showTaxIncl = $this->config->get('show_price_with_tax', 0);
		$this->selectedTariffs = $app->getUserState($this->context . '.current_selected_tariffs');
		$this->solidresCurrency = new SRCurrency(0, $this->item->currency_id);

		$this->timezone = new DateTimeZone($this->tzoffset);
		$this->minDaysBookInAdvance = $this->config->get('min_days_book_in_advance', 0);
		$this->maxDaysBookInAdvance = $this->config->get('max_days_book_in_advance', 0);
		$this->minLengthOfStay = $this->config->get('min_length_of_stay', 1);
		$this->dateFormat = $this->config->get('date_format', 'd-m-Y');

		$activeMenu = JFactory::getApplication()->getMenu()->getActive();
		$this->itemid = NULL;
		if (isset($activeMenu))
		{
			$this->itemid = $activeMenu->id ;
		}

		$datePickerMonthNum = $this->config->get('datepicker_month_number', 3);
		$weekStartDay = $this->config->get('week_start_day', 1);

		JHtml::_('behavior.framework');
		JHtml::_('jquery.framework');
		JHtml::_('bootstrap.framework');
		SRHtml::_('jquery.colorbox', 'show_map', '700px', '650px', 'true', 'false');

		JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);
		JHtml::_('script', SRURI_MEDIA.'/assets/js/datePicker/localization/jquery.ui.datepicker-'.JFactory::getLanguage()->getTag().'.js', false, false);
		$this->document->addScriptDeclaration('
			Solidres.jQuery(function ($) {
				$(".sr-photo").colorbox({rel:"sr-photo", transition:"fade"});
				var minLengthOfStay = '.$this->minLengthOfStay.';
				var checkout_component = $(".checkout_component").datepicker({
					minDate : "+' . ( $this->minDaysBookInAdvance + $this->minLengthOfStay ). '",
					numberOfMonths : '.$datePickerMonthNum.',
					showButtonPanel : true,
					dateFormat : "dd-mm-yy",
					firstDay: '.$weekStartDay.'
				});
				var checkin_component = $(".checkin_component").datepicker({
					minDate : "+' . ($this->minDaysBookInAdvance ) . 'd",
					'.($this->maxDaysBookInAdvance > 0 ? 'maxDate: "+'. ($this->maxDaysBookInAdvance) . '",' : '' ).'
					numberOfMonths : '.$datePickerMonthNum.',
					showButtonPanel : true,
					dateFormat : "dd-mm-yy",
					onSelect : function() {
						var checkoutMinDate = $(this).datepicker("getDate", "+1d");
						checkoutMinDate.setDate(checkoutMinDate.getDate() + minLengthOfStay);
						checkout_component.datepicker( "option", "minDate", checkoutMinDate );
						checkout_component.datepicker( "setDate", checkoutMinDate);
					},
					firstDay: '.$weekStartDay.'
				});
				$(".ui-datepicker").addClass("notranslate");
			});

			Solidres.child_max_age_limit = '.$this->config->get('child_max_age_limit', 17).';
		');

		JText::script('SR_CAN_NOT_REMOVE_COUPON');
		JText::script('SR_SELECT_AT_LEAST_ONE_ROOMTYPE');
		JText::script('SR_ERROR_CHILD_MAX_AGE');
		JText::script('SR_AND');
		JText::script('SR_TARIFF_BREAK_DOWN');
		JText::script('SUN');
		JText::script('MON');
		JText::script('TUE');
		JText::script('WED');
		JText::script('THU');
		JText::script('FRI');
		JText::script('SAT');
		JText::script('SR_NEXT');
		JText::script('SR_BACK');
		JText::script('SR_PROCESSING');
		JText::script('SR_CHILD');
		JText::script('SR_CHILD_AGE_SELECTION_JS');
		JText::script('SR_CHILD_AGE_SELECTION_1_JS');
		JText::script('SR_ONLY_1_LEFT');
		JText::script('SR_ONLY_2_LEFT');
		JText::script('SR_ONLY_3_LEFT');
		JText::script('SR_ONLY_4_LEFT');
		JText::script('SR_ONLY_5_LEFT');
		JText::script('SR_ONLY_6_LEFT');
		JText::script('SR_ONLY_7_LEFT');
		JText::script('SR_ONLY_8_LEFT');
		JText::script('SR_ONLY_9_LEFT');
		JText::script('SR_ONLY_10_LEFT');
		JText::script('SR_ONLY_11_LEFT');
		JText::script('SR_ONLY_12_LEFT');
		JText::script('SR_ONLY_13_LEFT');
		JText::script('SR_ONLY_14_LEFT');
		JText::script('SR_ONLY_15_LEFT');
		JText::script('SR_ONLY_16_LEFT');
		JText::script('SR_ONLY_17_LEFT');
		JText::script('SR_ONLY_18_LEFT');
		JText::script('SR_ONLY_19_LEFT');
		JText::script('SR_ONLY_20_LEFT');
		JText::script('SR_SHOW_MORE_INFO');
		JText::script('SR_HIDE_MORE_INFO');
		JText::script('SR_AVAILABILITY_CALENDAR_CLOSE');
		JText::script('SR_AVAILABILITY_CALENDAR_VIEW');
		JText::script('SR_PROCESSING');
		JText::script('SR_USERNAME_EXISTS');

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->_prepareDocument();
		
		parent::display($tpl);
    }
	
    /**
	 * Prepares the document like adding meta tags/site name per ReservationAsset
	 * 
	 * @return void
	 */
	protected function _prepareDocument()
	{
		if ($this->item->name)
		{
			$this->document->setTitle($this->item->name);
		}

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}

		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}

		if ($this->item->metadata)
		{
			foreach ($this->item->metadata as $k => $v)
			{
				if ($v)
				{
					$this->document->setMetadata($k, $v);
				}
			}
		}
	}

	/**
	 * Get the min price from a given tariff and show the formatted result
	 *
	 * @param $tariff
	 *
	 * @return string
	 */
	protected function getMinPrice($tariff)
	{
		$tariffSuffix = '';
		$min = NULL;
		$numberOfNights = 0;
		if ($tariff->type == 0 || $tariff->type == 2) :
			$tariffSuffix .= JText::_('SR_TARIFF_SUFFIX_PER_ROOM');
		else :
			$tariffSuffix .= JText::_('SR_TARIFF_SUFFIX_PER_PERSON');
		endif;

		switch ($tariff->type)
		{
			case 0: // rate per room per night
				$min = array_reduce($tariff->details['per_room'], function($t1, $t2) {
					return $t1->price < $t2->price ? $t1 : $t2;
				}, array_shift($tariff->details['per_room']));
				$numberOfNights = 1;
				break;
			case 1: // rate per person per night
				$min = array_reduce($tariff->details['adult1'], function($t1, $t2) {
					return $t1->price < $t2->price ? $t1 : $t2;
				}, array_shift($tariff->details['adult1']));
				$numberOfNights = 1;
				break;
			case 2: // package per room
				$min = $tariff->details['per_room'][0];
				$numberOfNights = $tariff->d_min;
				break;
			case 3: // package per person
				$min = $tariff->details['adult1'][0];
				$numberOfNights = $tariff->d_min;
				break;
			default:
				break;

		}

		// Calculate tax amount
		$totalImposedTaxAmount = 0;
		if ($this->showTaxIncl)
		{
			if (count($this->item->taxes) > 0)
			{
				foreach ($this->item->taxes as $taxType)
				{
					$totalImposedTaxAmount += $min->price * $taxType->rate;
				}
			}
		}


		$minCurrency = clone $this->solidresCurrency;
		$minCurrency->setValue($min->price + $totalImposedTaxAmount);

		$tariffSuffix .= JText::plural('SR_TARIFF_SUFFIX_NIGHT_NUMBER', $numberOfNights);

		return '<span class="starting_from">' . JText::_('SR_STARTING_FROM') . '</span><span class="min_tariff">' . $minCurrency->format() . '</span><span class="tariff_suffix">' . $tariffSuffix . '</span>';
	}
}
