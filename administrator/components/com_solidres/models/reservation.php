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
 * Reservation model.
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresModelReservation extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = null;

	/**
	 * @var		string	The event to trigger after deleting the data.
	 * @since	1.6
	 */
	protected $event_after_delete = null;

	/**
	 * @var		string	The event to trigger after saving the data.
	 * @since	1.6
	 */
	protected $event_after_save = null;

	/**
	 * @var		string	The event to trigger after deleting the data.
	 * @since	1.6
	 */
	protected $event_before_delete = null;

	/**
	 * @var		string	The event to trigger after saving the data.
	 * @since	1.6
	 */
	protected $event_before_save = null;

	/**
	 * @var		string	The event to trigger after changing the published state of the data.
	 * @since	1.6
	 */
	protected $event_change_state = null;

	/**
	 * Constructor.
	 *
	 * @param	array $config An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->event_after_delete 	= 'onReservationAfterDelete';
		$this->event_after_save 	= 'onReservationAfterSave';
		$this->event_before_delete 	= 'onReservationBeforeDelete';
		$this->event_before_save 	= 'onReservationBeforeSave';
		$this->event_change_state 	= 'onReservationChangeState';
		$this->text_prefix 			= strtoupper($this->option);

		// This context is mainly used in front end reservation processing
		if (JFactory::getApplication()->isSite())
		{
			$this->context = 'com_solidres.reservation.process';
		}
	}

	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('reservation.id', $pk);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	$record A record object.
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		$user = JFactory::getUser();
		
		return $user->authorise('core.delete', 'com_solidres.reservation.'.(int) $record->id);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	$record A record object.
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		return $user->authorise('core.edit.state', 'com_solidres.reservation.'.(int) $record->id);
	}
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	string	$type The table type to instantiate
	 * @param	string	$prefix A prefix for the table class name. Optional.
	 * @param	array	$config Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Reservation', $prefix = 'SolidresTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_solidres.reservation', 'reservation', array('control' => 'jform', 'load_data' => $loadData));
        
		if (empty($form))
        {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_solidres.edit.reservation.data', array());

		if (empty($data))
        {
			$data = $this->getItem();
		}

		return $data;
	}

    /**
	 * Method to get a single record.
	 *
	 * @param	integer	$pk The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('reservation.id');
		$item = parent::getItem($pk);
		if ($item->id)
        {
			$modelCoupon = JModelLegacy::getInstance('Coupon', 'SolidresModel', array('ignore_request' => true));
			$notesModel = JModelLegacy::getInstance('ReservationNotes', 'SolidresModel', array('ignore_request' => true));
			$item->coupon_code = empty($item->coupon_id) ? '' : $modelCoupon->getItem($item->coupon_id)->coupon_code;
			$query = $this->_db->getQuery(true);

            if(!empty($item->customer_id))
            {
                $query->select('CONCAT(u.name, " (", c.customer_code, " - ", cg.name, ")" )');
                $query->from($this->_db->quoteName('#__sr_customers').'as c');
                $query->join('LEFT', $this->_db->quoteName('#__sr_customer_groups').' as cg ON cg.id = c.customer_group_id');
                $query->join('LEFT', $this->_db->quoteName('#__users').' as u ON u.id = c.user_id');
				$query->where('c.id = '. (int) $item->customer_id);
                $item->customer_code = $this->_db->setQuery($query)->loadResult();
            }

			if(!empty($item->customer_country_id))
			{
				$query->clear();
				$query->select('ct.name as countryname');
				$query->from($this->_db->quoteName('#__sr_countries').'as ct');
				$query->where('ct.id = '. (int) $item->customer_country_id);
				$item->customer_country_name = $this->_db->setQuery($query)->loadResult();
			}

			if(!empty($item->geo_state_id))
			{
				$query->clear();
				$query->select('gst.name as geostatename');
				$query->from($this->_db->quoteName('#__sr_geo_states').'as gst');
				$query->where('gst.id = '. (int) $item->geo_state_id);
				$item->customer_geostate_name = $this->_db->setQuery($query)->loadResult();
			}

            $query = $this->_db->getQuery(true);
            $query->select('x.*, rtype.id as room_type_id, rtype.name as room_type_name, room.label as room_label')
				  ->from($this->_db->quoteName('#__sr_reservation_room_xref'). 'as x')
				  ->join('INNER', $this->_db->quoteName('#__sr_rooms').' as room ON room.id = x.room_id')
				  ->join('INNER', $this->_db->quoteName('#__sr_room_types').' as rtype ON rtype.id = room.room_type_id')
				  ->where('reservation_id = '.$this->_db->quote($item->id));

            $item->reserved_room_details = $this->_db->setQuery($query)->loadObjectList();

			foreach($item->reserved_room_details as $reserved_room_detail)
			{
				$query->clear();
				$query->select('x.*, extra.id as extra_id, extra.name as extra_name')->from($this->_db->quoteName('#__sr_reservation_room_extra_xref').' as x')
					  ->join('INNER', $this->_db->quoteName('#__sr_extras').' as extra ON extra.id = x.extra_id')
					  ->where('reservation_id = '.$this->_db->quote($item->id))
					  ->where('room_id = '. (int) $reserved_room_detail->room_id);

				$result = $this->_db->setQuery($query)->loadObjectList();

				if (!empty($result))
				{
					$reserved_room_detail->extras =  $result;
				}

				$query->clear();
				$query->select('*')
					  ->from($this->_db->quoteName('#__sr_reservation_room_details'))
					  ->where($this->_db->quoteName('reservation_room_id') .' = '.$reserved_room_detail->id);

				$result = $this->_db->setQuery($query)->loadObjectList();

				$reserved_room_detail->other_info = array();
				if (!empty($result))
				{
					$reserved_room_detail->other_info =  $result;
				}
			}

			$item->notes = NULL;
			$notesModel->setState('filter.reservation_id', $item->id);
			$notes = $notesModel->getItems();

			if (!empty($notes))
			{
				$item->notes = $notes;
			}

			$query->clear();
			$query->select('*')
				->from($this->_db->quoteName('#__sr_reservation_extra_xref'))
				->where($this->_db->quoteName('reservation_id') .' = ' . $this->_db->quote($item->id) );
			$result = $this->_db->setQuery($query)->loadObjectList();

			if (!empty($result))
			{
				$item->extras = $result;
			}
		}
        
		return $item;
	}

	/**
	 * Get room type information to be display in the reservation confirmation screen
	 *
	 * This is intended to be used in the front end
	 *
	 * @return array $ret An array contain room type information
	 */
	public function getRoomType()
	{
		// Construct a simple array of room type ID and its price
		$roomTypePricesMapping = array();
		JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');

		$app = JFactory::getApplication();
		$srRoomType = SRFactory::get('solidres.roomtype.roomtype');
		$currencyId = $app->getUserState($this->context . '.currency_id');
		$taxId = $app->getUserState($this->context . '.tax_id');
		$solidresCurrency = new SRCurrency(0, $currencyId);

		$modelName = $this->getName();
		$roomTypes = $this->getState($modelName .'.roomTypes');
		$checkin = $this->getState($modelName .'.checkin');
		$checkout = $this->getState($modelName .'.checkout');
		$reservationAssetId = $this->getState($modelName.'.reservationAssetId');
		$coupon = $app->getUserState($this->context . '.coupon');

		// Get imposed taxes
		$imposedTaxTypes = array();
		if (!empty($taxId))
		{
			$taxModel	= JModelLegacy::getInstance('Tax', 'SolidresModel', array('ignore_request' => true));
			$imposedTaxTypes[] = $taxModel->getItem($taxId);
		}

		// Get customer information
		$user = JFactory::getUser();
		$customerGroupId = NULL;  // Non-registered/Public/Non-loggedin customer
		if (SR_PLUGIN_USER_ENABLED)
		{
			$customerTable = JTable::getInstance('Customer', 'SolidresTable');
			$customerTable->load(array('user_id' => $user->id));
			$customerGroupId = $customerTable->customer_group_id;
		}

		$numberOfNights = (int) $srRoomType->calculateDateDiff($checkin, $checkout);

		$roomtypeModel = JModelLegacy::getInstance('RoomType', 'SolidresModel', array('ignore_request' => true));

		$totalPriceTaxIncl = 0;
		$totalPriceTaxExcl = 0;
		$totalReservedRoom = 0;
		$ret = array();

		// Get a list of room type based on search conditions
		foreach ($roomTypes as $roomTypeId => $bookedTariffs )
		{
			$bookedRoomTypeQuantity = count($roomTypes[$roomTypeId]);

			foreach ($bookedTariffs as $tariffId => $roomTypeRoomDetails )
			{
				$r = $roomtypeModel->getItem(array(
					'id' => $roomTypeId,
					'reservation_asset_id' => $reservationAssetId
				));

				$ret[$roomTypeId]['name'] = $r->name;
				$ret[$roomTypeId]['description'] = $r->description;
				$ret[$roomTypeId]['occupancy_adult'] = $r->occupancy_adult;
				$ret[$roomTypeId]['occupancy_child'] = $r->occupancy_child;

				// Some data to query the correct tariff
				foreach ($roomTypeRoomDetails as $roomIndex => $roomDetails)
				{
					if (SR_PLUGIN_COMPLEXTARIFF_ENABLED)
					{
						$cost  = $srRoomType->getPrice(
							$roomTypeId,
							$customerGroupId,
							$imposedTaxTypes,
							false,
							true,
							$checkin,
							$checkout,
							$solidresCurrency,
							$coupon,
							$roomDetails['adults_number'],
							(isset($roomDetails['children_number']) ? $roomDetails['children_number'] : 0),
							(isset($roomDetails['children_ages']) ? $roomDetails['children_ages'] : array()),
							$numberOfNights,
							(isset($tariffId) && $tariffId > 0) ? $tariffId : NULL
						);
					}
					else
					{
						$cost = $srRoomType->getPrice(
							$roomTypeId,
							$customerGroupId,
							$imposedTaxTypes,
							true,
							false,
							$checkin,
							$checkout,
							$solidresCurrency,
							$coupon,
							0,
							0,
							array(),
							0,
							$tariffId
						);
					}

					$ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency'] 	= $cost;
					$totalPriceTaxIncl += $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_incl'];
					$totalPriceTaxExcl += $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_excl'];

					$roomTypePricesMapping[$roomTypeId][$tariffId][$roomIndex] = array(
						'total_price' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price'],
						'total_price_tax_incl' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_incl'],
						'total_price_tax_excl' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_excl']
					);
				}

				// Calculate number of available rooms
				$ret[$roomTypeId]['totalAvailableRoom'] = count( $srRoomType->getListAvailableRoom($roomTypeId, $checkin, $checkout) );
				$ret[$roomTypeId]['quantity'] = $bookedRoomTypeQuantity;

				// Only allow quantity within quota
				if ($bookedRoomTypeQuantity <= $ret[$roomTypeId]['totalAvailableRoom'])
				{
					$totalReservedRoom += $bookedRoomTypeQuantity;
				}
				else
				{
					return false;
				}
			} // end room type loop
		}

		$this->setState($modelName .'.totalReservedRoom', $totalReservedRoom);
		$app->setUserState($this->context . '.cost',
			array(
				'total_price' => $totalPriceTaxIncl,
				'total_price_tax_incl' => $totalPriceTaxIncl,
				'total_price_tax_excl' => $totalPriceTaxExcl
			)
		);

		$app->setUserState($this->context . '.room_type_prices_mapping', $roomTypePricesMapping);

		return $ret;
	}

	/**
	 * Save the reservation data
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function save($data)
	{
		$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;
		$app = JFactory::getApplication();
		$roomTypePricesMapping = $app->getUserState($this->context.'.room_type_prices_mapping', NULL);

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('extension');
		JPluginHelper::importPlugin('user');

		// Load the row if saving an existing record.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());
			return false;
		}

		// Prepare the row for saving
		//$this->prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($data, $table, $isNew, $this));
		if (in_array(false, $result, true))
		{
			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		// Trigger the onContentAfterSave event.
		$this->setState($this->getName().'.room_type_prices_mapping', $roomTypePricesMapping);
		$result = $dispatcher->trigger($this->event_after_save, array($data, $table, $isNew, $this));
		if (in_array(false, $result, true))
		{
			return false;
		}

		$pkName = $table->getKeyName();
		if (isset($table->$pkName))
		{
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);

		return true;
	}
}