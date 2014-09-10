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
 * @package     Solidres
 * @subpackage	ReservationAsset
 * @since		0.4.0
 */
class SolidresControllerReservationAsset extends JControllerLegacy
{
	private $context;

	protected $reservationDetails;

	public function __construct($config = array())
	{
		$config['model_path'] = JPATH_COMPONENT_ADMINISTRATOR . '/models';
		$this->context = 'com_solidres.reservation.process';
		$this->app = JFactory::getApplication();
		parent::__construct($config);
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	$name The model name. Optional.
	 * @param	string	$prefix The class prefix. Optional.
	 * @param	array	$config Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 * @since	1.5
	 */
	public function &getModel($name = 'ReservationAsset', $prefix = 'SolidresModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Recalculate the tariff accoriding to guest's room selection (adult number, child number, child's ages)
	 *
	 * This is used for tariff per person per night, when guest enter adults and children quantity as well as children's
	 * ages, we re-calculate the tariff.
	 *
	 * @return json
	 */
	public function calculateTariff()
	{
		JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
		$adultNumber = $this->app->input->get('adult_number', 0, 'int');
		$childNumber = $this->app->input->get('child_number', 0, 'int');
		$roomTypeId = $this->app->input->get('room_type_id', 0, 'int');
		$roomIndex = $this->app->input->get('room_index', 0, 'int');
		$raId = $this->app->input->get('raid', 0, 'int');
		$tariffId = $this->app->input->get('tariff_id', 0, 'int');
		$currencyId = $this->app->getUserState($this->context . '.currency_id');
		$taxId = $this->app->getUserState($this->context . '.tax_id');
		$solidresCurrency = new SRCurrency(0, $currencyId);
		$checkIn = $this->app->getUserState($this->context.'.checkin');
		$checkOut = $this->app->getUserState($this->context.'.checkout');
		$coupon  = $this->app->getUserState($this->context.'.coupon');
		$srRoomType = SRFactory::get('solidres.roomtype.roomtype');
		$dayMapping = array('0' => JText::_('SUN'), '1' => JText::_('MON'), '2' => JText::_('TUE'), '3' => JText::_('WED'), '4' => JText::_('THU'), '5' => JText::_('FRI'), '6' => JText::_('SAT') );
		$solidresParams = JComponentHelper::getParams('com_solidres');
		$showTaxIncl = $solidresParams->get('show_price_with_tax', 0);
		$tariffBreakDownNetOrGross = $showTaxIncl== 1 ? 'net' : 'gross';

		// Get imposed taxes
		$imposedTaxTypes = array();
		if (!empty($taxId))
		{
			$taxModel = JModelLegacy::getInstance('Tax', 'SolidresModel', array('ignore_request' => true));
			$imposedTaxTypes[] = $taxModel->getItem($taxId);
		}

		// Get customer information
		$user = JFactory::getUser();
		$customerGroupId = NULL;
		if (SR_PLUGIN_USER_ENABLED)
		{
			$customerTable = JTable::getInstance('Customer', 'SolidresTable');
			$customerTable->load(array('user_id' => $user->id));
			$customerGroupId = $customerTable->customer_group_id;
		}

		$numberOfNights = (int) $srRoomType->calculateDateDiff($checkIn, $checkOut);

		// get children ages
		$childAges = array();
		for ($i = 0; $i < $childNumber; $i++)
		{
			$childAges[] = $this->app->input->get('child_age_'.$roomTypeId.'_'.$tariffId.'_'.$roomIndex.'_'.$i, '0', 'int');
		}

		// Search for complex tariff first, if no complex tariff found, we will search for Standard Tariff
		if (SR_PLUGIN_COMPLEXTARIFF_ENABLED)
		{
			$tariff = $srRoomType->getPrice($roomTypeId, $customerGroupId, $imposedTaxTypes, false, true, $checkIn, $checkOut, $solidresCurrency, $coupon, $adultNumber, $childNumber, $childAges, $numberOfNights, (isset($tariffId) && $tariffId > 0 ? $tariffId : NULL ));
		}
		else
		{
			$tariff = $srRoomType->getPrice($roomTypeId, $customerGroupId, $imposedTaxTypes, true, false, $checkIn, $checkOut, $solidresCurrency, $coupon, 0, 0, array(), 0, $tariffId);
		}

		// Prepare tariff break down, since JSON is not able to handle PHP object correctly, we should prepare a simple array
		$tariffBreakDown = array();
		$tariffBreakDownHtml = '';
		if ($tariff['type'] == 0 || $tariff['type'] == 1)
		{
			$tariffBreakDown = array();
			$tariffBreakDownHtml = '';
			$tempKeyWeekDay = NULL;
			$tariffBreakDownHtml .= '<table class="tariff-break-down">';
			foreach ($tariff['tariff_break_down'] as $key => $priceOfDayDetails)
			{
				if ($key % 7 == 0 && $key == 0) :
					$tariffBreakDownHtml .= '<tr>';
				elseif ($key % 7 == 0) :
					$tariffBreakDownHtml .= '</tr><tr>';
				endif;
				$tempKeyWeekDay = key($priceOfDayDetails);
				$tariffBreakDownHtml .= '<td><p>'.$dayMapping[$tempKeyWeekDay].'</p><span class="'.$tariffBreakDownNetOrGross.'">'.$priceOfDayDetails[$tempKeyWeekDay][$tariffBreakDownNetOrGross]->format().'</span>';
				$tariffBreakDown[][$tempKeyWeekDay] = array('wday' => $tempKeyWeekDay, 'priceOfDay' => $priceOfDayDetails[$tempKeyWeekDay]['gross']->format());
			}
			$tariffBreakDownHtml .= '</tr></table>';
		}

		$shownTariff = $tariff['total_price_tax_excl_formatted'];
		if ($showTaxIncl)
		{
			$shownTariff = $tariff['total_price_tax_incl_formatted'];
		}

		echo json_encode(array(
			'room_index' => $roomIndex,
			'room_index_tariff' => array(
				'id' => !empty($shownTariff) ? $shownTariff->getId() : NULL ,
				'activeId' => !empty($shownTariff) ? $shownTariff->getActiveId() : NULL ,
				'code' => !empty($shownTariff) ? $shownTariff->getCode() : NULL ,
				'sign' => !empty($shownTariff) ? $shownTariff->getSign() : NULL ,
				'name' => !empty($shownTariff) ? $shownTariff->getName() : NULL ,
				'rate' => !empty($shownTariff) ? $shownTariff->getRate() : NULL ,
				'value' => !empty($shownTariff) ? $shownTariff->getValue() : NULL,
				'formatted' => !empty($shownTariff) ? $shownTariff->format() : NULL
			),
			'room_index_tariff_breakdown' => $tariffBreakDown,
			'room_index_tariff_breakdown_html' => $tariffBreakDownHtml
		));

		$this->app->close();
	}
};