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

JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models', 'SolidresModel');

/**
 * RoomType handler class
 * 
 * @package 	Solidres
 * @subpackage	RoomType
 */
class SRRoomType
{
	const PER_ROOM_PER_NIGHT = 0;

	const PER_PERSON_PER_NIGHT = 1;

	const PACKAGE_PER_ROOM = 2;

	const PACKAGE_PER_PERSON = 3;

	protected $_dbo = null;
	
	public function __construct()
	{
		$this->_dbo = JFactory::getDbo();
	}
	
	/**
	 * Get list of Room is reserved and belong to a RoomType.
     * 
	 * @param int $roomTypeId
	 * @param int $reservationId
     * 
	 * @return array An array of room object
	 */
	public function getListReservedRoom($roomTypeId, $reservationId)
	{
		$query = $this->_dbo->getQuery(true);

		$query->select('r1.id, r1.label, r2.adults_number, r2.children_number');
		$query->from($this->_dbo->quoteName('#__sr_rooms').' r1');
		$query->join('INNER', $this->_dbo->quoteName('#__sr_reservation_room_xref').' r2 ON r1.id = r2.room_id');
		$query->where('r1.room_type_id = '.$this->_dbo->quote($roomTypeId).' AND r2.reservation_id = '.$this->_dbo->quote($reservationId));

		$this->_dbo->setQuery($query);
		$results = $this->_dbo->loadObjectList();
        
		return $results;
	}

	/**
	 * Get list rooms belong to a RoomType
     * 
	 * @param int $roomtypeId
     *
	 * @return array object
	 */
	public function getListRooms($roomtypeId)
	{
		$query = $this->_dbo->getQuery(true);
		
		$query->clear();
		$query->select('id, label, room_type_id');
		$query->from($this->_dbo->quoteName('#__sr_rooms'));
		$query->where('room_type_id = '.$this->_dbo->quote($roomtypeId));
		
		$this->_dbo->setQuery($query);
		$result = $this->_dbo->loadObjectList();
		
		if(empty($result))
        {
			return false;
		}

		return $result;
	}
	
	/**
	 * Method to get a list of available rooms of a RoomType based on check in and check out date
     * 
	 * @param   int     $roomtypeId
	 * @param   int     $checkin
	 * @param   int     $checkout
	 * 
	 * @return  mixed   An array of room object if successfully
     *                  otherwise return false
	 */
	public function getListAvailableRoom($roomtypeId = 0, $checkin, $checkout)
	{

		$srReservation = SRFactory::get('solidres.reservation.reservation');
		$availableRooms = array();

		$query = $this->_dbo->getQuery(true);
		$query->select('id, label')->from($this->_dbo->quoteName('#__sr_rooms'));

        if ($roomtypeId > 0)
		{
            $query->where('room_type_id = '.$this->_dbo->quote($roomtypeId));
        }

		if (SR_PLUGIN_LIMITBOOKING_ENABLED)
		{
			$checkinMySQLFormat = $this->_dbo->quote(date('Y-m-d', strtotime($checkin)));
			$checkoutMySQLFormat = $this->_dbo->quote(date('Y-m-d', strtotime($checkout)));

			$query->where('id NOT IN (SELECT room_id FROM '.$this->_dbo->quoteName('#__sr_limit_booking_details').'
											WHERE limit_booking_id IN (SELECT id FROM '.$this->_dbo->quoteName('#__sr_limit_bookings').'
											WHERE
											(
												('.$checkinMySQLFormat.' <= start_date AND '.$checkoutMySQLFormat.' > start_date )
												OR
												('.$checkinMySQLFormat.' >= start_date AND '.$checkoutMySQLFormat.' <= end_date )
												OR
												('.$checkinMySQLFormat.' < end_date AND '.$checkoutMySQLFormat.' >= end_date )
											)
											AND state = 1
											))');
		}

		$this->_dbo->setQuery($query);
		$rooms = $this->_dbo->loadObjectList();

		if (empty($rooms))
		{
			return false;
		}

		foreach ($rooms as $room)
		{
			// If this room is available, add it to the returned list
			if ($srReservation->isRoomAvailable($room->id, $checkin, $checkout))
			{
				$availableRooms[] = $room;
			}
		}
		
		return $availableRooms;
	}
	
	/**
	 * Check a room to determine whether it can be deleted or not, if yes then delete it
	 * 
	 * When delete a room, we will need to make sure that all related
	 * Reservation of that room must be removed first 
	 *  
	 * @param 	int 	    $roomId
     * 
	 * @return 	boolean     True if a room is safe to be deleted
     *                      False otherwise
	 */
	public function canDeleteRoom($roomId = 0) {
		$query		= $this->_dbo->getQuery(true);
        
		$query->select('COUNT(*)')->from($this->_dbo->quoteName('#__sr_reservation_room_xref'))->where('room_id = '.$this->_dbo->quote($roomId));
		$this->_dbo->setQuery($query);
		$result = (int) $this->_dbo->loadResult();

		if ($result > 0)
        {
			return false;
		}
        
		$query->clear();
		$query->delete('')->from($this->_dbo->quoteName('#__sr_rooms'))->where('id = '.$this->_dbo->quote($roomId));
		$this->_dbo->setQuery($query);
		$result = $this->_dbo->execute();
        
		if (!$result)
        {
			return false;
		}
        
		return true;
	}
	
	/**
     * @param  int $roomtypeId
     * @param  int $couponId
     * @return bool|mixed
     */
	public function storeCoupon($roomtypeId = 0, $couponId = 0)
	{
		if($roomtypeId <= 0 && $couponId <= 0)
		{
			return false;
		}

        $query = $this->_dbo->getQuery(true);
        $query->insert($this->_dbo->quoteName('#__sr_room_type_coupon_xref'))
              ->columns(array($this->_dbo->quoteName('room_type_id'), $this->_dbo->quoteName('coupon_id')))
              ->values((int) $roomtypeId . ',' . (int)$couponId) ;
		$this->_dbo->setQuery($query);
        
		return $this->_dbo->execute();
	}


    /**
     * @param  int $roomtypeId
     * @param  int $extraId
     * @return bool|mixed
     */
    public function storeExtra($roomtypeId = 0, $extraId = 0)
    {
        if($roomtypeId <= 0 && $extraId <= 0)
        {
            return false;
        }

        $query = $this->_dbo->getQuery(true);
        $query->insert($this->_dbo->quoteName('#__sr_room_type_extra_xref'))
              ->columns( array($this->_dbo->quoteName('room_type_id'), $this->_dbo->quoteName('extra_id')))
              ->values((int) $roomtypeId . ',' . (int)$extraId) ;
        $this->_dbo->setQuery($query);

        return $this->_dbo->execute();
    }
	
    /**
     * Method to store Room information
     *
     * TODO move this function to corresponding model/table
     *
     * @param   int     $roomTypeId
     * @param   string  $roomLabel
     * 
     * @return  boolean
     */
    public function storeRoom($roomTypeId = 0, $roomLabel = '')
    {
        $table = JTable::getInstance('Room', 'SolidresTable');

        $table->room_type_id    = $roomTypeId;
        $table->label           = $roomLabel;

        return $table->store();
    }

    /**
     * Find room type by room id
     *
     * TODO move this function to corresponding model/table
     *
     * @param  int $roomId
     *
     * @return mixed
     */
    public function findByRoomId($roomId)
    {
        $query = $this->_dbo->getQuery(true);
        
        $query->select('*')->from($this->_dbo->quoteName('#__sr_room_types'));
        $query->where('id IN (SELECT room_type_id
                              FROM '.$this->_dbo->quoteName('#__sr_rooms').'
                              WHERE id = '.$this->_dbo->quote($roomId).')');

        $this->_dbo->setQuery($query);

        return $this->_dbo->loadObject();
    }
    
    /**
     * Get list coupon id belong to $roomtypeId
     *
     * @param   int $roomtypeId
     *
     * @return  array
     */
    public function getCoupon($roomtypeId)
    {
    	$query = $this->_dbo->getQuery(true);

    	$query->select('coupon_id')->from($this->_dbo->quoteName('#__sr_room_type_coupon_xref'));
    	$query->where('room_type_id = '.$this->_dbo->quote($roomtypeId));

    	$this->_dbo->setQuery($query);
        
    	return $this->_dbo->loadColumn();
    }

    /**
     * Get list extra id belong to $roomtypeId
     *
     * @param   int $roomtypeId
     *
     * @return  array
     */
    public function getExtra($roomtypeId)
    {
        $query = $this->_dbo->getQuery(true);

        $query->select('extra_id')->from($this->_dbo->quoteName('#__sr_room_type_extra_xref'));
        $query->where('room_type_id = '.$this->_dbo->quote($roomtypeId));

        $this->_dbo->setQuery($query);

        return $this->_dbo->loadColumn();
    }

	/**
	 * Get price of a room type from a list of room type's tariff that matches the conditions:
	 *        Customer group
	 *        Checkin && Checkout date
	 *        Adult number
	 *        Child number & ages
	 *        Min & Max number of nights
	 *
	 * @param   int $roomTypeId
	 * @param   $customerGroupId
	 * @param   $imposedTaxTypes
	 * @param   bool $defaultTariff
	 * @param   bool $dateConstraint @deprecated
	 * @param   string $checkin
	 * @param   string $checkout
	 * @param   SRCurrency $solidresCurrency The currency object
	 * @param   array $coupon An array of coupon information
	 * @param   int $adultNumber Number of adult, default is 0
	 * @param   int $childNumber Number of child, default is 0
	 * @param   array $childAges An array of children age, it is associated with the $childNumber
	 * @param   int $numberOfNights 0 means ignore this condition
	 * @param   int $tariffId Search for specific tariff id
	 *
	 * @return  array    An array of SRCurrency for Tax and Without Tax
	 */
	public function getPrice($roomTypeId, $customerGroupId, $imposedTaxTypes, $defaultTariff = false, $dateConstraint = false, $checkin = '', $checkout = '', SRCurrency $solidresCurrency, $coupon = NULL, $adultNumber = 0, $childNumber = 0, $childAges = array(), $numberOfNights = 0, $tariffId = NULL )
	{
		$modelTariff = JModelLegacy::getInstance('Tariff', 'SolidresModel', array('ignore_request' => true));
		$tariffWithDetails = NULL;

		// This is package type, do not need to calculate per day
		if (isset($tariffId))
		{
			$tariffWithDetails = $modelTariff->getItem($tariffId);

			if (isset($tariffWithDetails) && ($tariffWithDetails->type == 2 || $tariffWithDetails->type == 3))
			{
				$response = $this->getPricePackage($tariffWithDetails, $checkin, $checkout, $imposedTaxTypes, $solidresCurrency, $coupon = NULL, $adultNumber, $childNumber, $childAges);
			}
			else // This is normal tariffs, need to calculate per day
			{
				$response = $this->getPriceDaily($tariffWithDetails, $roomTypeId, $customerGroupId, $imposedTaxTypes, $defaultTariff, $dateConstraint, $checkin, $checkout, $solidresCurrency, $coupon, $adultNumber, $childNumber, $childAges, $numberOfNights);
			}
		}
		else // No tariff id specified, back to old behavior of 0.6.x and before
		{
			$response = $this->getPriceLegacy($roomTypeId, $customerGroupId, $imposedTaxTypes, $defaultTariff, $dateConstraint, $checkin, $checkout, $solidresCurrency, $coupon, $adultNumber, $childNumber, $childAges, $numberOfNights);
		}



		return $response;
	}

	/**
	 * Get price for Package tariff type: either Package per room or Package per person.
	 *
	 */
	public function getPricePackage($tariffWithDetails, $checkin, $checkout, $imposedTaxTypes, $solidresCurrency, $coupon = NULL, $adultNumber, $childNumber, $childAges)
	{
		$isAppliedCoupon = false;
		$tariffBreakDown = array();
		$totalBookingCost = 0;
		$totalBookingCostIncludedTaxFormatted = NULL;
		$totalBookingCostExcludedTaxedFormatted = NULL;
		$totalBookingCostTaxed = NULL;

		$checkinDay = new DateTime($checkin);
		$checkoutDay = new DateTime($checkout);
		$checkinDayInfo = getdate($checkinDay->format('U'));
		$checkoutDay = getdate($checkoutDay->format('U'));
		$nights = $this->calculateDateDiff($checkin, $checkout);

		$isValid = false;

		// Check to see if the general checkin/out match this tariff's valid from and valid to
		// We also have to check if the checkin match the allowed checkin days.
		// We also have to check if the general nights number match this tariff's min nights and max nights
		if (
			strtotime($tariffWithDetails->valid_from) <= strtotime($checkin) &&
			strtotime($tariffWithDetails->valid_to)  >= strtotime($checkout) &&
			in_array($checkinDayInfo['wday'], $tariffWithDetails->limit_checkin) &&
			($nights >= $tariffWithDetails->d_min && $nights <= $tariffWithDetails->d_max)
		)
		{
			$isValid = true;
		}


		if ($isValid)
		{
			$cost = 0;
			if ($tariffWithDetails->type == self::PACKAGE_PER_ROOM)
			{
				$cost = $tariffWithDetails->details['per_room'][0]->price;
			}
			else if ($tariffWithDetails->type == self::PACKAGE_PER_PERSON)
			{
				for ($i = 1; $i <= $adultNumber; $i++)
				{
					$cost += $tariffWithDetails->details['adult'.$i][0]->price;
				}

				for ($i = 0; $i < count($childAges); $i++)
				{
					foreach ($tariffWithDetails->details as $guestType => $guesTypeTariff)
					{
						if (substr($guestType, 0, 5) == 'adult')
						{
							continue; // skip all adult's tariff
						}

						if
						(
							$childAges[$i] >= $tariffWithDetails->details[$guestType][0]->from_age
							&&
							$childAges[$i] <= $tariffWithDetails->details[$guestType][0]->to_age
						)
						{
							$cost += $tariffWithDetails->details[$guestType][0]->price;
						}
					}
				}
			}

			if (isset($coupon) && is_array($coupon))
			{
				if ($coupon['coupon_is_percent'] == 1)
				{
					$deductionAmount = $cost * ( $coupon['coupon_amount'] / 100 );
				}
				else
				{
					$deductionAmount = $coupon['coupon_amount'];
				}
				$cost -= $deductionAmount;
				$isAppliedCoupon = true;
			}

			// Calculate the imposed tax amount per day
			$totalImposedTaxAmountPerDay = 0;
			foreach ($imposedTaxTypes as $taxType)
			{
				$totalImposedTaxAmountPerDay += $cost * $taxType->rate;
			}

			$totalBookingCost = $cost;
			$tariffBreakDown[8]['gross'] = $cost;
			$tariffBreakDown[8]['tax'] = $totalImposedTaxAmountPerDay;
			$tariffBreakDown[8]['net'] = $cost + $totalImposedTaxAmountPerDay;

			$result = array(
				'total_booking_cost' => $totalBookingCost,
				'tariff_break_down' => $tariffBreakDown,
				'is_applied_coupon' => $isAppliedCoupon
			);

			$totalBookingCost = $result['total_booking_cost'];
			$tempKeyWeekDay = key($result['tariff_break_down']);
			$tempSolidresCurrencyCostPerDayGross = clone $solidresCurrency;
			$tempSolidresCurrencyCostPerDayTax = clone $solidresCurrency;
			$tempSolidresCurrencyCostPerDayNet = clone $solidresCurrency;
			$tempSolidresCurrencyCostPerDayGross->setValue($result['tariff_break_down'][$tempKeyWeekDay]['gross']);
			$tempSolidresCurrencyCostPerDayTax->setValue($result['tariff_break_down'][$tempKeyWeekDay]['tax']);
			$tempSolidresCurrencyCostPerDayNet->setValue($result['tariff_break_down'][$tempKeyWeekDay]['net']);
			$tariffBreakDown[][$tempKeyWeekDay] = array(
				'gross' => $tempSolidresCurrencyCostPerDayGross,
				'tax' => $tempSolidresCurrencyCostPerDayTax,
				'net' => $tempSolidresCurrencyCostPerDayNet
			);

			unset($tempSolidresCurrencyCostPerDayGross);
			unset($tempSolidresCurrencyCostPerDayTax);
			unset($tempSolidresCurrencyCostPerDayNet);
			unset($tempKeyWeekDay);

			if ($totalBookingCost > 0)
			{
				// Calculate the imposed tax amount
				$totalImposedTaxAmount = 0;
				foreach ($imposedTaxTypes as $taxType)
				{
					$totalImposedTaxAmount += $totalBookingCost * $taxType->rate;
				}

				$totalBookingCostTaxed = $totalBookingCost + $totalImposedTaxAmount;

				// Format the number with correct currency
				$totalBookingCostExcludedTaxedFormatted = clone $solidresCurrency;
				$totalBookingCostExcludedTaxedFormatted->setValue($totalBookingCost);

				// Format the number with correct currency
				$totalBookingCostIncludedTaxFormatted = clone $solidresCurrency;
				$totalBookingCostIncludedTaxFormatted->setValue($totalBookingCostTaxed);
			}
		}

		$response = array(
			'total_price_formatted' => $totalBookingCostIncludedTaxFormatted,
			'total_price_tax_incl_formatted' => $totalBookingCostIncludedTaxFormatted,
			'total_price_tax_excl_formatted' => $totalBookingCostExcludedTaxedFormatted,
			'total_price' => $totalBookingCostTaxed,
			'total_price_tax_incl' => $totalBookingCostTaxed,
			'total_price_tax_excl' => $totalBookingCost,
			'tariff_break_down' => $tariffBreakDown,
			'is_applied_coupon' => isset($result['is_applied_coupon']) ? $result['is_applied_coupon'] : NULL,
			'type' => isset($tariffWithDetails->type) ? $tariffWithDetails->type : NULL,
			'id' => isset($tariffWithDetails->id) ? $tariffWithDetails->id : NULL,
			'title' => isset($tariffWithDetails->title) ? $tariffWithDetails->title : NULL,
			'description' => isset($tariffWithDetails->description) ? $tariffWithDetails->description : NULL,
		);

		return $response;
	}

	/**
	 * Get price for Rate tariff type: either Rate per room per night or Rate per person per night
	 *
	 *
	 */
    public function getPriceDaily($tariffWithDetails, $roomTypeId, $customerGroupId, $imposedTaxTypes, $defaultTariff = false, $dateConstraint = false, $checkin = '', $checkout = '', SRCurrency $solidresCurrency, $coupon = NULL, $adultNumber = 0, $childNumber = 0, $childAges = array(), $numberOfNights = 0)
    {
		$srCoupon = SRFactory::get('solidres.coupon.coupon');
		$totalBookingCost = 0;
		$bookWeekDays = $this->calculateWeekDay($checkin, $checkout);

		$isCouponApplicable = false;
		if (isset($coupon) && is_array($coupon))
		{
			$isCouponApplicable = $srCoupon->isApplicable($coupon['coupon_id'], $roomTypeId);
		}

		$nightCount = 1;
		$tariffBreakDown = array();
		$tmpKeyWeekDay = NULL;

		if (isset($tariffWithDetails))
		{
			foreach ($bookWeekDays as $bookWeekDay)
			{
				$theDay = new DateTime($bookWeekDay);
				$dayInfo = getdate($theDay->format('U'));
				// We calculate per nights, not per day, for example 2011-08-24 to 2012-08-29 is 6 days but only 5 nights
				if ($nightCount < count($bookWeekDays))
				{
					$result = array(
						'total_booking_cost' => 0,
						'tariff_break_down' => array(),
						'is_applied_coupon' => false
					);

					// Deal with Coupon
					if ($isCouponApplicable)
					{
						$result = $this->calculateCostPerDay($tariffWithDetails, $dayInfo, $coupon, $adultNumber, $childNumber, $childAges, $imposedTaxTypes);
					}
					else
					{
						$result = $this->calculateCostPerDay($tariffWithDetails, $dayInfo, NULL, $adultNumber, $childNumber, $childAges, $imposedTaxTypes);
					}

					$totalBookingCost += $result['total_booking_cost'];
					$tempKeyWeekDay = key($result['tariff_break_down']);
					$tempSolidresCurrencyCostPerDayGross = clone $solidresCurrency;
					$tempSolidresCurrencyCostPerDayTax = clone $solidresCurrency;
					$tempSolidresCurrencyCostPerDayNet = clone $solidresCurrency;
					$tempSolidresCurrencyCostPerDayGross->setValue($result['tariff_break_down'][$tempKeyWeekDay]['gross']);
					$tempSolidresCurrencyCostPerDayTax->setValue($result['tariff_break_down'][$tempKeyWeekDay]['tax']);
					$tempSolidresCurrencyCostPerDayNet->setValue($result['tariff_break_down'][$tempKeyWeekDay]['net']);
					$tariffBreakDown[][$tempKeyWeekDay] = array(
						'gross' => $tempSolidresCurrencyCostPerDayGross,
						'tax' => $tempSolidresCurrencyCostPerDayTax,
						'net' => $tempSolidresCurrencyCostPerDayNet
					);
				}
				$nightCount ++;
			}
		}

		unset($tempSolidresCurrencyCostPerDayGross);
		unset($tempSolidresCurrencyCostPerDayTax);
		unset($tempSolidresCurrencyCostPerDayNet);
		unset($tempKeyWeekDay);

		$totalBookingCostIncludedTaxFormatted = NULL;
		$totalBookingCostExcludedTaxedFormatted = NULL;
		$totalBookingCostTaxed = NULL;

		if ($totalBookingCost > 0)
		{
			// Calculate the imposed tax amount
			$totalImposedTaxAmount = 0;
			foreach ($imposedTaxTypes as $taxType)
			{
				$totalImposedTaxAmount += $totalBookingCost * $taxType->rate;
			}

			$totalBookingCostTaxed = $totalBookingCost + $totalImposedTaxAmount;

			// Format the number with correct currency
			$totalBookingCostExcludedTaxedFormatted = clone $solidresCurrency;
			$totalBookingCostExcludedTaxedFormatted->setValue($totalBookingCost);

			// Format the number with correct currency
			$totalBookingCostIncludedTaxFormatted = clone $solidresCurrency;
			$totalBookingCostIncludedTaxFormatted->setValue($totalBookingCostTaxed);
		}

		$response = array(
			'total_price_formatted' => $totalBookingCostIncludedTaxFormatted,
			'total_price_tax_incl_formatted' => $totalBookingCostIncludedTaxFormatted,
			'total_price_tax_excl_formatted' => $totalBookingCostExcludedTaxedFormatted,
			'total_price' => $totalBookingCostTaxed,
			'total_price_tax_incl' => $totalBookingCostTaxed,
			'total_price_tax_excl' => $totalBookingCost,
			'tariff_break_down' => $tariffBreakDown,
			'is_applied_coupon' => isset($result['is_applied_coupon']) ? $result['is_applied_coupon'] : false,
			'type' => isset($tariffWithDetails->type) ? $tariffWithDetails->type : NULL,
			'id' => isset($tariffWithDetails->id) ? $tariffWithDetails->id : NULL,
			'title' => isset($tariffWithDetails->title) ? $tariffWithDetails->title : NULL,
			'description' => isset($tariffWithDetails->description) ? $tariffWithDetails->description : NULL,
		);

		return $response;
    }

	/**
	 * Get price of a room type from a list of room type's tariff that matches the conditions:
	 *        Customer group
	 *        Checkin && Checkout date
	 *        Adult number
	 *        Child number & ages
	 *        Min & Max number of nights
	 *
	 * @param   int $roomTypeId
	 * @param   $customerGroupId
	 * @param   $imposedTaxTypes
	 * @param   bool $defaultTariff
	 * @param   bool $dateConstraint @deprecated
	 * @param   string $checkin
	 * @param   string $checkout
	 * @param   SRCurrency $solidresCurrency The currency object
	 * @param   array $coupon An array of coupon information
	 * @param   int $adultNumber Number of adult, default is 0
	 * @param   int $childNumber Number of child, default is 0
	 * @param   array $childAges An array of children age, it is associated with the $childNumber
	 * @param   int $numberOfNights 0 means ignore this condition
	 *
	 * @return  array    An array of SRCurrency for Tax and Without Tax
	 */
	public function getPriceLegacy($roomTypeId, $customerGroupId, $imposedTaxTypes, $defaultTariff = false, $dateConstraint = false, $checkin = '', $checkout = '', SRCurrency $solidresCurrency, $coupon = NULL, $adultNumber = 0, $childNumber = 0, $childAges = array(), $numberOfNights = 0 )
	{
		$modelTariffs = JModelLegacy::getInstance('Tariffs', 'SolidresModel', array('ignore_request' => true));
		$modelTariff = JModelLegacy::getInstance('Tariff', 'SolidresModel', array('ignore_request' => true));
		$srCoupon = SRFactory::get('solidres.coupon.coupon');

		$totalBookingCost = 0;

		$modelTariffs->setState('filter.room_type_id', $roomTypeId);
		$modelTariffs->setState('filter.customer_group_id', $customerGroupId);

		if ($defaultTariff)
		{
			$modelTariffs->setState('filter.default_tariff', 1);
			// If we need to get the default price, set customer group to -1, means we do not care about customer group
			$modelTariffs->setState('filter.customer_group_id', -1);
		}

		$bookWeekDays = $this->calculateWeekDay($checkin, $checkout);

		if ($dateConstraint)
		{
			$modelTariffs->setState('filter.date_constraint', 1);
		}

		$isCouponApplicable = false;
		if (isset($coupon) && is_array($coupon))
		{
			$isCouponApplicable = $srCoupon->isApplicable($coupon['coupon_id'], $roomTypeId);
		}

		$nightCount = 1;
		$tariffBreakDown = array();
		$tempTariffId = 0;
		$tmpKeyWeekDay = NULL;
		foreach ($bookWeekDays as $bookWeekDay)
		{
			$theDay = new DateTime($bookWeekDay);
			$dayInfo = getdate($theDay->format('U'));
			// We calculate per nights, not per day, for example 2011-08-24 to 2012-08-29 is 6 days but only 5 nights
			if ($nightCount < count($bookWeekDays))
			{
				// Find Complex Tariff
				if ($dateConstraint)
				{
					// Reset these state because we may override it in other steps
					$modelTariffs->setState('filter.date_constraint', 1);
					$modelTariffs->setState('filter.default_tariff', NULL);
					$modelTariffs->setState('filter.customer_group_id', $customerGroupId);
					$modelTariffs->setState('filter.bookday',  JFactory::getDate($bookWeekDay)->toSql());
					if ($numberOfNights > 0)
					{
						$modelTariffs->setState('filter.number_of_nights', $numberOfNights);
					}
					$tariff = $modelTariffs->getItems();
				}
				else // Or find Standard Tariff
				{
					$modelTariffs->setState('filter.date_constraint', NULL);
					$modelTariffs->setState('filter.default_tariff', 1);
					$modelTariffs->setState('filter.customer_group_id', -1);
					$tariff = $modelTariffs->getItems();
				}

				$result = array(
					'total_booking_cost' => 0,
					'tariff_break_down' => array(),
					'is_applied_coupon' => false
				);
				if (!empty($tariff))
				{
					// Then we load the tariff details: price for each week day
					// Caching stuff
					if ($tempTariffId != $tariff[0]->id)
					{
						$tariffWithDetails = $modelTariff->getItem($tariff[0]->id);
						$tempTariffId = $tariff[0]->id;
					}

					// Deal with Coupon
					if ($isCouponApplicable)
					{
						$result = $this->calculateCostPerDay($tariffWithDetails, $dayInfo, $coupon, $adultNumber, $childNumber, $childAges, $imposedTaxTypes);
					}
					else
					{
						$result = $this->calculateCostPerDay($tariffWithDetails, $dayInfo, NULL, $adultNumber, $childNumber, $childAges, $imposedTaxTypes);
					}

					$totalBookingCost += $result['total_booking_cost'];
					$tempKeyWeekDay = key($result['tariff_break_down']);
					$tempSolidresCurrencyCostPerDayGross = clone $solidresCurrency;
					$tempSolidresCurrencyCostPerDayTax = clone $solidresCurrency;
					$tempSolidresCurrencyCostPerDayNet = clone $solidresCurrency;
					$tempSolidresCurrencyCostPerDayGross->setValue($result['tariff_break_down'][$tempKeyWeekDay]['gross']);
					$tempSolidresCurrencyCostPerDayTax->setValue($result['tariff_break_down'][$tempKeyWeekDay]['tax']);
					$tempSolidresCurrencyCostPerDayNet->setValue($result['tariff_break_down'][$tempKeyWeekDay]['net']);
					$tariffBreakDown[][$tempKeyWeekDay] = array(
						'gross' => $tempSolidresCurrencyCostPerDayGross,
						'tax' => $tempSolidresCurrencyCostPerDayTax,
						'net' => $tempSolidresCurrencyCostPerDayNet
					);
				}
			}
			$nightCount ++;
		}

		unset($tempSolidresCurrencyCostPerDayGross);
		unset($tempSolidresCurrencyCostPerDayTax);
		unset($tempSolidresCurrencyCostPerDayNet);
		unset($tempKeyWeekDay);

		$totalBookingCostIncludedTaxFormatted = NULL;
		$totalBookingCostExcludedTaxedFormatted = NULL;
		$totalBookingCostTaxed = NULL;

		if ($totalBookingCost > 0)
		{
			// Calculate the imposed tax amount
			$totalImposedTaxAmount = 0;
			foreach ($imposedTaxTypes as $taxType)
			{
				$totalImposedTaxAmount += $totalBookingCost * $taxType->rate;
			}

			$totalBookingCostTaxed = $totalBookingCost + $totalImposedTaxAmount;

			// Format the number with correct currency
			$totalBookingCostExcludedTaxedFormatted = clone $solidresCurrency;
			$totalBookingCostExcludedTaxedFormatted->setValue($totalBookingCost);

			// Format the number with correct currency
			$totalBookingCostIncludedTaxFormatted = clone $solidresCurrency;
			$totalBookingCostIncludedTaxFormatted->setValue($totalBookingCostTaxed);
		}

		$response = array(
			'total_price_formatted' => $totalBookingCostIncludedTaxFormatted,
			'total_price_tax_incl_formatted' => $totalBookingCostIncludedTaxFormatted,
			'total_price_tax_excl_formatted' => $totalBookingCostExcludedTaxedFormatted,
			'total_price' => $totalBookingCostTaxed,
			'total_price_tax_incl' => $totalBookingCostTaxed,
			'total_price_tax_excl' => $totalBookingCost,
			'tariff_break_down' => $tariffBreakDown,
			'is_applied_coupon' => $result['is_applied_coupon'],
			'type' => isset($tariff[0]->type) ? $tariff[0]->type : NULL,
			'id' => 0, // special id for joined tariffs case
			'title' => NULL,
			'description' => NULL
		);

		return $response;
	}

	/**
	 * Get an array of week days in the period between $from and $to
	 *
	 * @param    string   From date
	 * @param    string   To date
	 *
	 * @return   array	  An array in format array(0 => 'Y-m-d', 1 => 'Y-m-d')
	 */
	private function calculateWeekDay($from, $to)
	{
		$datetime1 	= new DateTime($from);
		$interval 	= $this->calculateDateDiff($from, $to);
		$weekDays 	= array();

		$weekDays[] = $datetime1->format('Y-m-d');

		for ($i = 1; $i <= (int)$interval; $i++)
		{
			$weekDays[] = $datetime1->modify('+1 day')->format('Y-m-d');
		}

		return $weekDays;
	}

	/**
	 * Calculate the number of day from a given range
	 *
	 * Note: DateTime is PHP 5.3 only
	 *
	 * @param  string  $from   Begin of date range
	 * @param  string  $to     End of date range
	 * @param  string  $format The format indicator
	 *
	 * @return string
	 */
	public function calculateDateDiff($from, $to, $format = '%a')
	{
		$datetime1 = new DateTime($from);
		$datetime2 = new DateTime($to);

		$interval = $datetime1->diff($datetime2);

		return $interval->format($format);
	}

	/**
	 * Calculate booking cost per day and apply the coupon if possible
	 *
	 * @param   array   $tariff   	An array of tariffs for searching
	 * @param   array   $dayInfo 	The date that we need to find tariff for it from above $tariff
	 * @param   array   $coupon 	An array of coupon information
	 * @param   int     $adultNumber Number of adult, only used for tariff Per person per room
	 * @param   int     $childNumber Number of child, only used for tariff Per person per room
	 * @param   array   $childAges   Children ages, it is associated with $childNumber
	 * @param   arrray  $imposedTaxTypes All imposed tax types
	 *
	 * @return  array
	 */
	private function calculateCostPerDay($tariff, $dayInfo, $coupon = NULL, $adultNumber, $childNumber, $childAges, $imposedTaxTypes)
	{
		$totalBookingCost = 0;
		$tariffBreakDown = array();
		$costPerDay = 0;
		$isAppliedCoupon = false;

		if ($tariff->type == self::PER_ROOM_PER_NIGHT)
		{
			for ($i = 0, $count = count($tariff->details['per_room']); $i < $count; $i ++)
			{
				if ($tariff->details['per_room'][$i]->w_day == $dayInfo['wday'])
				{
					$costPerDay = $tariff->details['per_room'][$i]->price;
					break; // we found the tariff we need, get out of here
				}
			}
		}
		else if ($tariff->type == self::PER_PERSON_PER_NIGHT)
		{
			// Calculate cost per day for each adult
			for ($i = 1; $i <= $adultNumber; $i++)
			{
				$adultIndex = 'adult'.$i;
				for ($t = 0, $count = count($tariff->details[$adultIndex]); $t < $count; $t ++)
				{
					if ($tariff->details[$adultIndex][$t]->w_day == $dayInfo['wday'])
					{
						$costPerDay += $tariff->details[$adultIndex][$t]->price;
						break; // we found the tariff we need, get out of here
					}
				}
			}

			// Calculate cost per day for each child, take their ages into consideration
			for ($i = 0; $i < count($childAges); $i++)
			{
				foreach ($tariff->details as $guestType => $guesTypeTariff)
				{
					if (substr($guestType, 0, 5) == 'adult')
					{
						continue; // skip all adult's tariff
					}

					for ($t = 0, $count = count($tariff->details[$guestType]); $t < $count; $t ++)
					{
						if
						(
							$tariff->details[$guestType][$t]->w_day == $dayInfo['wday']
							&&
							($childAges[$i] >= $tariff->details[$guestType][$t]->from_age && $childAges[$i] <= $tariff->details[$guestType][$t]->to_age)
						)
						{
							$costPerDay += $tariff->details[$guestType][$t]->price;
							break; // found it, get out of here
						}
					}
				}
			}
		}

		if (isset($coupon) && is_array($coupon))
		{
			if ($coupon['coupon_is_percent'] == 1)
			{
				$deductionAmount = $costPerDay * ( $coupon['coupon_amount'] / 100 );
			}
			else
			{
				$deductionAmount = $coupon['coupon_amount'];
			}
			$costPerDay -= $deductionAmount;
			$isAppliedCoupon = true;
		}

		// Calculate the imposed tax amount per day
		$totalImposedTaxAmountPerDay = 0;
		foreach ($imposedTaxTypes as $taxType)
		{
			$totalImposedTaxAmountPerDay += $costPerDay * $taxType->rate;
		}

		$totalBookingCost += $costPerDay;
		$tariffBreakDown[$dayInfo['wday']]['gross'] = $costPerDay;
		$tariffBreakDown[$dayInfo['wday']]['tax'] = $totalImposedTaxAmountPerDay;
		$tariffBreakDown[$dayInfo['wday']]['net'] = $costPerDay + $totalImposedTaxAmountPerDay;

		return array(
			'total_booking_cost' => $totalBookingCost,
			'tariff_break_down' => $tariffBreakDown,
			'is_applied_coupon' => $isAppliedCoupon
		);
	}
}