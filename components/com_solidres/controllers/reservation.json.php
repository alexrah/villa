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
 * Controller to handle one-page reservation form
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresControllerReservation extends JControllerLegacy
{
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->app = JFactory::getApplication();
		$this->context = 'com_solidres.reservation.process';
	}

	public function getModel($name = 'Reservation', $prefix = 'SolidresModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Prepare the reservation data, store them into user session so that it can be saved into the db later
	 *
	 * @params string $type Type of data to process
	 *
	 * @return void
	 */
	public function process()
	{
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$data = $this->input->post->get('jform', array(), 'array');
		$step = $this->input->get('step', '', 'string');
		$this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR.'/models');

		switch ($step)
		{
			case 'room':
				$this->processRoom($data);
				break;
			case 'guestinfo':
				$this->processGuestInfo($data);
				break;
			default:
				break;
		}
	}

	/**
	 * Process submitted room information and store some data into session for further steps
	 *
	 * @param $data array The submitted data
	 *
	 * @return json
	 */
	public function processRoom($data)
	{
		// Get the extra price to display in the confirmmation screen
		$extraModel = $this->getModel('Extra', 	'SolidresModel') ;
		$totalRoomTypeExtraCostTaxExcl = 0;
		$totalRoomTypeExtraCostTaxIncl = 0;
		//$activeMenu = $this->app->getMenu()->getActive();

		foreach ($data['room_types'] as $roomTypeId => &$bookedTariffs)
		{
			foreach ($bookedTariffs as $tariffId => &$rooms)
			{
				foreach ($rooms as &$room)
				{
					if (isset($room['extras']))
					{
						foreach ($room['extras'] as $extraId => &$extraDetails)
						{
							$extra = $extraModel->getItem($extraId);
							$extraDetails['price'] = $extra->price;
							$extraDetails['price_tax_incl'] = $extra->price_tax_incl;
							$extraDetails['price_tax_excl'] = $extra->price_tax_excl;
							$extraDetails['name'] = $extra->name;

							if (isset($extraDetails['quantity']))
							{
								$totalRoomTypeExtraCostTaxIncl += $extraDetails['price_tax_incl']  * $extraDetails['quantity'];
								$totalRoomTypeExtraCostTaxExcl += $extraDetails['price_tax_excl'] * $extraDetails['quantity'];
							}
							else
							{
								$totalRoomTypeExtraCostTaxIncl += $extraDetails['price_tax_incl'];
								$totalRoomTypeExtraCostTaxExcl += $extraDetails['price_tax_excl'];
							}
						}
					}
				}
			}
		}

		// manually unset those referenced instances
		unset($rooms);
		unset($room);
		unset($extraDetails);

		$data['total_extra_price_per_room'] = $totalRoomTypeExtraCostTaxIncl;
		$data['total_extra_price_tax_incl_per_room'] = $totalRoomTypeExtraCostTaxIncl;
		$data['total_extra_price_tax_excl_per_room'] = $totalRoomTypeExtraCostTaxExcl;

		$this->app->setUserState($this->context.'.room', $data);

		$this->app->setUserState($this->context . '.booking_conditions', $data['bookingconditions']);
		$this->app->setUserState($this->context . '.privacy_policy', $data['privacypolicy']);

		// Store all selected tariffs
		$this->app->setUserState($this->context.'.current_selected_tariffs', $data['selected_tariffs']);

		// If error happened, output correct error message in json format so that we can handle in the front end
		$response = array('status' => 1, 'message' => '', 'next_step' => $data['next_step']);

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Process submitted guest information: guest personal information and their payment method
	 *
	 * @param $data
	 *
	 * @return json
	 */
	public function processGuestInfo($data)
	{
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables', 'SolidresTable');
		$countryModel = $this->getModel('Country', 'SolidresModel');
		$geostateModel = $this->getModel('State', 'SolidresModel');
		$extraModel = $this->getModel('Extra', 	'SolidresModel') ;
		$country = $countryModel->getItem($data['customer_country_id']);
		$totalRoomTypeExtraCostTaxExcl = 0;
		$totalRoomTypeExtraCostTaxIncl = 0;

		// Query country and geo state name
		if (isset($data['customer_geo_state_id']))
		{
			$geoState = $geostateModel->getItem($data['customer_geo_state_id']);
			$data['geo_state_name'] = $geoState->name;
		}
		$data['country_name'] 	= $country->name;

		// Process customer group
		$customerId = NULL;
		if (SR_PLUGIN_USER_ENABLED)
		{
			$user = JFactory::getUser();
			if ($user->get('id') > 0)
			{
				$customerTable = JTable::getInstance('Customer', 'SolidresTable');
				$customerTable->load(array('user_id' => $user->get('id')));
				$customerId = $customerTable->id;
			}
		}

		$data['customer_id'] = $customerId;

		// Process extra (Per booking)
		if (isset($data['extras']))
		{
			foreach ($data['extras'] as $extraId => &$extraDetails)
			{
				$extra = $extraModel->getItem($extraId);
				$extraDetails['price'] = $extra->price;
				$extraDetails['price_tax_incl'] = $extra->price_tax_incl;
				$extraDetails['price_tax_excl'] = $extra->price_tax_excl;
				$extraDetails['name'] = $extra->name;

				if (isset($extraDetails['quantity']))
				{
					$totalRoomTypeExtraCostTaxIncl += $extraDetails['price_tax_incl'] * $extraDetails['quantity'];
					$totalRoomTypeExtraCostTaxExcl += $extraDetails['price_tax_excl'] * $extraDetails['quantity'];
				}
				else
				{
					$totalRoomTypeExtraCostTaxIncl += $extraDetails['price_tax_incl'];
					$totalRoomTypeExtraCostTaxExcl += $extraDetails['price_tax_excl'];
				}
			}
		}

		$data['total_extra_price_per_booking'] = $totalRoomTypeExtraCostTaxIncl;
		$data['total_extra_price_tax_incl_per_booking'] = $totalRoomTypeExtraCostTaxIncl;
		$data['total_extra_price_tax_excl_per_booking'] = $totalRoomTypeExtraCostTaxExcl;

		// Bind them to session
		$this->app->setUserState($this->context.'.guest', $data);

		// If error happened, output correct error message in json format so that we can handle in the front end
		$response = array('status' => 1, 'message' => '', 'next_step' => $data['next_step']);

		echo json_encode($response);

		$this->app->close();
	}

	public function removeCoupon()
	{
		$app = JFactory::getApplication();
		$context = 'com_solidres.reservation.process';
		$status = false;

		$currentAppliedCoupon = $app->getUserState($context.'.coupon');

		if ($currentAppliedCoupon['coupon_id'] == $app->input->get('id', 0, 'int'))
		{
			$app->setUserState($context.'.coupon', NULL);
			$status = true;
		}

		$response = array('status' => $status, 'message' => '');

		echo json_encode($response);die(1);
	}
}