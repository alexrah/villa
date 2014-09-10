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

JLoader::import('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models', 'SolidresModel');

/**
 * Solidres Extension Plugin
 *
 * @package		Solidres
 * @subpackage	Extension.Plugin
 * @since		1.5
 */
class plgExtensionSolidres extends JPlugin
{
    /**
     * Allow to processing of Reservation data after it is saved.
     *
     * @param object    $data
     * @param object    $table
     * @param boolean   $isNew
     * @param object    $model
     *
     * @since    1.6
     */
	function onReservationAfterSave($data, $table, $isNew, $model)
	{
        $dbo 			= JFactory::getDbo();
		$srReservation 	= SRFactory::get('solidres.reservation.reservation');
		$srRoomType 	= SRFactory::get('solidres.roomtype.roomtype');
		$query          = $dbo->getQuery(true);
        $reservationId 	= $table->id;
		$roomTypePricesMapping = $model->getState($model->getName().'.room_type_prices_mapping', NULL);
		$tariffTable = JTable::getInstance('Tariff', 'SolidresTable');

		$query->clear();
		$query->delete()->from($dbo->quoteName('#__sr_reservation_room_xref'))->where('reservation_id = '.$reservationId);
		$dbo->setQuery($query);
		$dbo->execute();

		// Insert new records
		foreach($data['room_types'] as $roomTypeId => $bookedTariffs)
		{
			// Find a list of available rooms
			$availableRoomList = $srRoomType->getListAvailableRoom($roomTypeId, $data['checkin'], $data['checkout']);

			foreach($bookedTariffs as $tariffId => $rooms)
			{
				foreach ($rooms as $roomIndex => $room)
				{
					// Pick the first and assign it
					$pickedRoom = array_shift($availableRoomList);

					// Get the tariff info
					$tariffTable->load($tariffId);

					$room['room_id'] = $pickedRoom->id;
					$room['room_label'] = $pickedRoom->label;
					$room['room_price'] = $roomTypePricesMapping[$roomTypeId][$tariffId][$roomIndex]['total_price_tax_incl'];
					$room['room_price_tax_incl'] = $roomTypePricesMapping[$roomTypeId][$tariffId][$roomIndex]['total_price_tax_incl'];
					$room['room_price_tax_excl'] = $roomTypePricesMapping[$roomTypeId][$tariffId][$roomIndex]['total_price_tax_excl'];
					$room['tariff_id'] = $tariffId > 0 ? $tariffId : NULL;
					$room['tariff_title'] = !empty($tariffTable->title) ? $tariffTable->title : '';
					$room['tariff_description'] = !empty($tariffTable->description) ? $tariffTable->description : '';

					$srReservation->storeRoom($reservationId, $room);

					// Insert new records
					if (isset($room['extras']))
					{
						foreach ($room['extras'] as $extraId => $extraDetails)
						{
							if (isset($extraDetails['quantity']))
							{
								$srReservation->storeExtra($reservationId, $room['room_id'], $room['room_label'], $extraId, $extraDetails['name'], $extraDetails['quantity'], $extraDetails['price_tax_incl']);
							}
							else
							{
								$srReservation->storeExtra($reservationId, $room['room_id'], $room['room_label'], $extraId, $extraDetails['name'], NULL, $extraDetails['price_tax_incl']);
							}
						}
					}
				}
			}
		}

		// Store extra items (Per booking)
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/tables', 'SolidresTable');

		if (isset($data['extras']))
		{
			foreach ($data['extras'] as $extraId => $extraDetails)
			{
				$reservationExtraData = array(
					'reservation_id' => $reservationId,
					'extra_id' => $extraId,
					'extra_name' => $extraDetails['name'],
					'extra_quantity' => (isset($extraDetails['quantity']) ? $extraDetails['quantity'] : NULL),
					'extra_price' => $extraDetails['price_tax_incl']
				);

				$tableReservationExtra = JTable::getInstance('ReservationExtra', 'SolidresTable');
				$tableReservationExtra->bind($reservationExtraData);
				$tableReservationExtra->check();
				$tableReservationExtra->store();
				$tableReservationExtra->reset();
			}
		}

		// Update the quantity of coupon
		if ($isNew)
		{
			if ( isset($data['coupon_id']) && $data['coupon_id'] > 0)
			{
				$tableCoupon = JTable::getInstance('Coupon', 'SolidresTable');
				$tableCoupon->load($data['coupon_id']);
				if (!is_null($tableCoupon->quantity) && $tableCoupon->quantity > 0)
				{
					$tableCoupon->quantity -= 1;
					$tableCoupon->check();
					$tableCoupon->store();
					$tableCoupon->reset();
				}
			}
		}
	}

	/*function onReservationAssetBeforeSave($data, $table, $isNew) {}*/

	/**
	 * Allow to processing of ReservationAsset data after it is saved.
	 *
	 * @param    object    $data The data representing the ReservationAsset.
	 * @param    object    $table
	 * @param    boolean   $result
	 * @param    boolean   $isNew True is this is new data, false if it is existing data.
	 *
	 * @throws Exception
	 * @return    boolean
	 * @since    1.6
	 */
	function  onReservationAssetAfterSave($data, $table, $result, $isNew)
	{
		$dbo = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$media = SRFactory::get('solidres.media.media');

		$media->store($data, $table, 0);

        // Process extra fields
        if ($table->id && $result && isset($data['reservationasset_extra_fields']) && (count($data['reservationasset_extra_fields']))) {
			try {

                $query->clear();
                $query->delete()->from($dbo->quoteName('#__sr_reservation_asset_fields'));
                $query->where('reservation_asset_id = '.$table->id);
                $query->where("field_key LIKE 'reservationasset_extra_fields.%'");
				$dbo->setQuery($query);

				if (!$dbo->execute()) {
					throw new Exception($dbo->getErrorMsg());
				}

				$tuples = array();
				$order	= 1;

				foreach ($data['reservationasset_extra_fields'] as $k => $v)
				{
					$tuples[] = '('.$table->id.', '.$dbo->quote('reservationasset_extra_fields.'.$k).', '.$dbo->quote($v).', '.$order++.')';
				}

				$dbo->setQuery('INSERT INTO '.$dbo->quoteName('#__sr_reservation_asset_fields').' VALUES '.implode(', ', $tuples));

				if (!$dbo->execute()) {
					throw new Exception($dbo->getErrorMsg());
				}

			} catch (JException $e) {
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}
        // end of extra field processing

		// Process Paylater's payment fields
		$solidresConfig = new SRConfig(array('scope_id' => $table->id, 'data_namespace' => 'payments/paylater'));
		$solidresConfig->set(array(
			'paylater_enabled' => $data['payments']['paylater_enabled'],
			'paylater_is_default' => $data['payments']['paylater_is_default'],
			'paylater_frontend_message' => $data['payments']['paylater_frontend_message']
		));

		// Process BankWire's payment fields
		$solidresConfig = new SRConfig(array('scope_id' => $table->id, 'data_namespace' => 'payments/bankwire'));
		$solidresConfig->set(array(
			'bankwire_enabled' => $data['payments']['bankwire_enabled'],
			'bankwire_is_default' => $data['payments']['bankwire_is_default'],
			'bankwire_frontend_message' => $data['payments']['bankwire_frontend_message'],
			'bankwire_accountname' => $data['payments']['bankwire_accountname'],
			'bankwire_accountdetails' => $data['payments']['bankwire_accountdetails']
		));

		// End of processing payment fields
	}

	/**
	 * Allow to processing of unit data after it is saved.
	 *
	 * @param    object    $data The data representing the unit.
	 * @param    object    $table
	 * @param    boolean   $isNew True is this is new data, false if it is existing data.
	 *
	 * @throws Exception
	 * @return  void
	 * @since    1.6
	 */
	function onRoomTypeAfterSave($data, $table, $isNew)
	{
		$dbo 		= JFactory::getDbo();
		$query 		= $dbo->getQuery(true);
		$srRoomType = SRFactory::get('solidres.roomtype.roomtype');
		$nullDate   = substr($dbo->getNullDate(), 0, 10);
		$media = SRFactory::get('solidres.media.media');
		$tariffModel = JModelLegacy::getInstance('Tariff', 'SolidresModel', array('ignore_request' => true));

		$media->store($data, $table, 1);

		// ==  Processing tariff/prices == //
		// Delete existing standard tariffs first
		$query->clear();
		$query->select('id');
		$query->from($dbo->quoteName('#__sr_tariffs'));
		$query->where('room_type_id = '.$dbo->quote($table->id));
		$query->where('valid_from = ' . $dbo->quote($nullDate));
		$query->where('valid_to = ' . $dbo->quote($nullDate));

		$currentDefaultTariffId = $dbo->setQuery($query)->loadResult();
		$tariffModel->delete($currentDefaultTariffId);

		// Check the current currency_id of this roomtype's reservation asset.
		$query->clear();
		$query->select('currency_id');
		$query->from($dbo->quoteName('#__sr_reservation_assets'));
		$query->where('id = '.$data['reservation_asset_id']);
		$currencyId = $dbo->setQuery($query)->loadResult();

		// Store the default tariff
		if(isset($data['default_tariff']))
		{
			if (is_array($data['default_tariff'])) // Store price for separated day of week
			{
				$tariffData = array(
					'currency_id' => $currencyId,
					'customer_group_id' => NULL,
					'valid_from' => $nullDate,
					'valid_to' => $nullDate,
					'room_type_id' => $table->id,
					'title' => $data['standard_tariff_title'],
					'description' => $data['standard_tariff_description'],
					'd_min' => NULL,
					'd_max'	=> NULL,
					'p_min'	=> NULL,
					'p_max'	=> NULL,
					'type' => 0 // Default is per room per night
				);

				foreach ($data['default_tariff'] as $day => $price)
				{
					$tariffData['details']['per_room'][$day]['price'] = $price;
					$tariffData['details']['per_room'][$day]['w_day'] = $day;
					$tariffData['details']['per_room'][$day]['guest_type'] = NULL;
					$tariffData['details']['per_room'][$day]['from_age'] = NULL;
					$tariffData['details']['per_room'][$day]['to_age'] = NULL;
				}

				$tariffModel->save($tariffData);
			}
		}

		// ==  Processing tariff/prices == //

		$query->clear();
		$query->delete('')->from($dbo->quoteName('#__sr_room_type_coupon_xref'))->where('room_type_id = '.$dbo->quote($table->id));
		$dbo->setQuery($query);
		$result = $dbo->execute();
		if(!$result)
		{
			JError::raiseWarning(-1, 'plgExtensionSolidres::onRoomTypeAfterSave: Delete from '.$dbo->quoteName('#__sr_room_type_coupon_xref').' '. ($result ? 'success' : 'failure'));
		}

		if(isset($data['coupon_id']) && count($data['coupon_id']))
		{
			foreach ($data['coupon_id'] as $value)
			{
				$srRoomType->storeCoupon($table->id, $value);
			}
		}

        $query->clear();
        $query->delete('')->from($dbo->quoteName('#__sr_room_type_extra_xref'))->where('room_type_id = '.$dbo->quote($table->id));
        $dbo->setQuery($query);
        $result = $dbo->execute();
        if(!$result)
        {
            JError::raiseWarning(-1, 'plgExtensionSolidres::onRoomTypeAfterSave: Delete from '.$dbo->quoteName('#__sr_room_type_extra_xref').' '. ($result ? 'success' : 'failure'));
        }

        if(isset($data['extra_id']) && count($data['extra_id']))
        {
            foreach ($data['extra_id'] as $value)
            {
                $srRoomType->storeExtra($table->id, $value);
            }
        }

		if(isset($data['rooms']) && count($data['rooms'])) {
			foreach($data['rooms'] as $value) {
				if($value['id'] == 'new' && !empty($value['label']) ) {
	                $srRoomType->storeRoom($table->id, $value['label']);
				}
			}
		}

		// Process extra fields
		if ($table->id && $result && isset($data['roomtype_custom_fields']) && (count($data['roomtype_custom_fields']))) {
			try {

				$query->clear();
				$query->delete()->from($dbo->quoteName('#__sr_room_type_fields'));
				$query->where('room_type_id = '.$table->id);
				$query->where("field_key LIKE 'roomtype_custom_fields.%'");
				$dbo->setQuery($query);

				if (!$dbo->execute()) {
					throw new Exception($dbo->getErrorMsg());
				}

				$tuples = array();
				$order	= 1;

				foreach ($data['roomtype_custom_fields'] as $k => $v)
				{
					$tuples[] = '('.$table->id.', '.$dbo->quote('roomtype_custom_fields.'.$k).', '.$dbo->quote($v).', '.$order++.')';
				}

				$dbo->setQuery('INSERT INTO '.$dbo->quoteName('#__sr_room_type_fields').' VALUES '.implode(', ', $tuples));

				if (!$dbo->execute()) {
					throw new Exception($dbo->getErrorMsg());
				}

			} catch (JException $e) {
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

	}

    /**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	public function onReservationAssetPrepareForm($form, $data)
	{
		// Load solidres plugin language
		$lang = JFactory::getLanguage();
		$lang->load('plg_extension_solidres', JPATH_ADMINISTRATOR);

		if (!($form instanceof JForm)) {
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// Check we are manipulating a valid form.
		if (!in_array($form->getName(), array('com_solidres.reservationasset'))) {
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(__DIR__.'/fields');
		$form->loadFile('reservationasset', false);

		// Toggle whether the checkin time field is required.
		if ($this->params->get('param_reservation_asset_checkin_time', 1) > 0) {
			$form->setFieldAttribute('checkin_time', 'required', $this->params->get('param_reservation_asset_checkin_time') == 2, 'reservationasset_extra_fields');
		} else {
			$form->removeField('checkin_time', 'reservationasset_extra_fields');
		}

        // Toggle whether the checkout time field is required.
		if ($this->params->get('param_reservation_asset_checkout_time', 1) > 0) {
			$form->setFieldAttribute('checkout_time', 'required', $this->params->get('param_reservation_asset_checkout_time') == 2, 'reservationasset_extra_fields');
		} else {
			$form->removeField('checkout_time', 'reservationasset_extra_fields');
		}

        // Toggle whether the cancellation prepayment field is required.
		if ($this->params->get('param_reservation_asset_cancellation_prepayment', 1) > 0) {
			$form->setFieldAttribute('cancellation_prepayment', 'required', $this->params->get('param_reservation_asset_cancellation_prepayment') == 2, 'reservationasset_extra_fields');
		} else {
			$form->removeField('cancellation_prepayment', 'reservationasset_extra_fields');
		}

        // Toggle whether the children and extra beds field is required.
		if ($this->params->get('param_reservation_asset_children_and_extra_beds', 1) > 0) {
			$form->setFieldAttribute('children_and_extra_beds', 'required', $this->params->get('param_reservation_asset_children_and_extra_beds') == 2, 'reservationasset_extra_fields');
		} else {
			$form->removeField('children_and_extra_beds', 'reservationasset_extra_fields');
		}

        // Toggle whether the children and extra beds field is required.
		if ($this->params->get('param_reservation_asset_pets', 1) > 0) {
			$form->setFieldAttribute('pets', 'required', $this->params->get('param_reservation_asset_pets') == 2, 'reservationasset_extra_fields');
		} else {
			$form->removeField('pets', 'reservationasset_extra_fields');
		}

        // Toggle whether the facebook field is required.
		if ($this->params->get('param_reservation_asset_facebook', 1) > 0) {
			$form->setFieldAttribute('facebook', 'required', $this->params->get('param_reservation_asset_facebook') == 2, 'reservationasset_extra_fields');
		} else {
			$form->removeField('facebook', 'reservationasset_extra_fields');
		}

        // Toggle whether the twitter field is required.
		if ($this->params->get('param_reservation_asset_twitter', 1) > 0) {
			$form->setFieldAttribute('twitter', 'required', $this->params->get('param_reservation_asset_twitter') == 2, 'reservationasset_extra_fields');
		} else {
			$form->removeField('twitter', 'reservationasset_extra_fields');
		}


		return true;
	}

    /**
	 * @param	string	$context	The context for the data
	 * @param	int		$data		The user id
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	public function onReservationAssetPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_solidres.reservationasset')))
		{
			return true;
		}

		if (is_object($data))
		{
			$reservationAssetId = isset($data->id) ? $data->id : 0;

			// Load the custom fields data from the database.
			$db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('field_key, field_value')->from($db->quoteName('#__sr_reservation_asset_fields'));
            $query->where('reservation_asset_id = '.(int) $reservationAssetId);
            $query->where("field_key LIKE 'reservationasset_extra_fields.%'");
			$db->setQuery($query);

			try
			{
				$results = $db->loadRowList();
			}
			catch (RuntimeException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}

			// Merge the custom fields data into current form data
			$data->reservationasset_extra_fields = array();

			foreach ($results as $v)
			{
				$k = str_replace('reservationasset_extra_fields.', '', $v[0]);
				$data->reservationasset_extra_fields[$k] = $v[1];
			}

			// Load the payments config data
			$solidresConfig = new SRConfig(array('scope_id' => (int) $reservationAssetId));
			if (!isset($data->payments))
			{
				$data->payments = NULL;
			}

			$data->payments = array_merge( (array) $data->payments, array(
				'paylater_enabled' => $solidresConfig->get('payments/paylater/paylater_enabled'),
				'paylater_is_default' => $solidresConfig->get('payments/paylater/paylater_is_default'),
				'paylater_frontend_message' => $solidresConfig->get('payments/paylater/paylater_frontend_message'),
				'bankwire_enabled' => $solidresConfig->get('payments/bankwire/bankwire_enabled'),
				'bankwire_is_default' => $solidresConfig->get('payments/bankwire/bankwire_is_default'),
				'bankwire_frontend_message' => $solidresConfig->get('payments/bankwire/bankwire_frontend_message'),
				'bankwire_accountname' => $solidresConfig->get('payments/bankwire/bankwire_accountname'),
				'bankwire_accountdetails' => $solidresConfig->get('payments/bankwire/bankwire_accountdetails')
			)) ;
		}

		return true;
	}

	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	public function onRoomTypePrepareForm($form, $data)
	{
		// Load solidres plugin language
		$lang = JFactory::getLanguage();
		$lang->load('plg_extension_solidres', JPATH_ADMINISTRATOR);

		if (!($form instanceof JForm)) {
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// Check we are manipulating a valid form.
		if (!in_array($form->getName(), array('com_solidres.roomtype'))) {
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(__DIR__.'/fields');
		$form->loadFile('roomtype', false);

		// Toggle whether the checkin time field is required.
		if ($this->params->get('param_roomtype_room_facilities', 1) > 0) {
			$form->setFieldAttribute('room_facilities', 'required', $this->params->get('param_roomtype_room_facilities') == 2, 'roomtype_custom_fields');
		} else {
			$form->removeField('room_facilities', 'roomtype_custom_fields');
		}

		// Toggle whether the checkout time field is required.
		if ($this->params->get('param_roomtype_room_size', 1) > 0) {
			$form->setFieldAttribute('room_size', 'required', $this->params->get('param_roomtype_room_size') == 2, 'roomtype_custom_fields');
		} else {
			$form->removeField('room_size', 'roomtype_custom_fields');
		}

		// Toggle whether the cancellation prepayment field is required.
		if ($this->params->get('param_roomtype_bed_size', 1) > 0) {
			$form->setFieldAttribute('bed_size', 'required', $this->params->get('param_roomtype_bed_size') == 2, 'roomtype_custom_fields');
		} else {
			$form->removeField('bed_size', 'roomtype_custom_fields');
		}

		return true;
	}

	/**
	 * @param	string	$context	The context for the data
	 * @param	int		$data		The user id
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	public function onRoomTypePrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_solidres.roomtype')))
		{
			return true;
		}

		if (is_object($data))
		{
			$roomTypeId = isset($data->id) ? $data->id : 0;

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('field_key, field_value')->from($db->quoteName('#__sr_room_type_fields'));
			$query->where('room_type_id = '.(int) $roomTypeId);
			$query->where("field_key LIKE 'roomtype_custom_fields.%'");
			$db->setQuery($query);

			try
			{
				$results = $db->loadRowList();
			}
			catch (RuntimeException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}

			// Merge the profile data.
			$data->roomtype_custom_fields = array();

			foreach ($results as $v)
			{
				$k = str_replace('roomtype_custom_fields.', '', $v[0]);
				$data->roomtype_custom_fields[$k] = $v[1];
			}
		}

		return true;
	}

	/**
	 * Create a new Joomla user before we create a new Solidres's customer.
	 *
	 * The procedure is different between front end and back end.
	 *
	 * @param $data
	 * @param $table
	 * @param $isNew
	 * @param $response
	 *
	 * @return bool
	 */
	public function onCustomerBeforeSave($data, $table, $isNew, &$response)
    {
        $userData = array(
            'id'        => $data['user_id'],
            'name'      => $data['firstname'] .' '. $data['middlename'] .' '. $data['lastname'] ,
            'username'  => $data['username'],
            'password'  => $data['password'],
            'password2' => $data['password2'],
            'email'     => $data['email'],
            'groups'    => array( '2' => '2') // Hard coded joomla user group id here, 2 = Registered group
        );

		if (JFactory::getApplication()->isAdmin())
		{
			$pk	= (!empty($userData['id'])) ? $userData['id'] : 0;
			$joomlaUser = JUser::getInstance($pk);

			if (!empty($joomlaUser->groups))
			{
				$userData['groups'] = $joomlaUser->groups;
			}

			if (!$joomlaUser->bind($userData))
			{
				$table->setError($joomlaUser->getError());
				return false;
			}
			$result = $joomlaUser->save();
			if (!$result)
			{
				$table->setError($joomlaUser->getError());
				return false;
			}

			// Assign the recent insert joomla user id
			$response = $joomlaUser->id;

			return true;
		}
		else // For front end, just use the way Joomla register a user
		{

		}
    }

	/**
	 * Example after save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param   string        The context of the content passed to the plugin (added in 1.6)
	 * @param   object        A JTableContent object
	 * @param   bool          If the content is just about to be created
	 * @param   array		  Full data of tariff with tariff details included
	 *
	 * @return boolean
	 * @since   1.6
	 */
	public function onTariffAfterSave($context, $table, $isNew, $tariff)
	{
		if (!isset($tariff['details']))
		{
			return true;
		}

		foreach ($tariff['details'] as $tariffType => $details)
		{
			foreach ($details as $detail)
			{
				$tariffDetailsTable = JTable::getInstance('TariffDetails', 'SolidresTable');
				if (isset($detail['id']))
				{
					$tariffDetailsTable->load($detail['id']);
				}
				$detail['tariff_id'] = $table->id;

				$tariffDetailsTable->bind($detail);
				if ($tariffDetailsTable->price == '')
				{
					$tariffDetailsTable->price = NULL;
				}
				$tariffDetailsTable->check();
				$tariffDetailsTable->store(true); // update null value
				$tariffDetailsTable->reset();
			}
		}
	}
}
