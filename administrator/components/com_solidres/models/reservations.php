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
 * Reservations model
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresModelReservations extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'r.id',
				'code', 'r.code',
				'state', 'r.state',
				'username', 'r1.username',
				'created_date', 'r.created_date',
				'modified_date', 'r.modifed_date',
				'modified_by', 'r.modifed_by',
				'checkin', 'r.checkin',
				'checkout', 'r.checkout',
				'customer_fullname'
			);
		}

		parent::__construct($config);
	}
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$paymentStatus = $app->getUserStateFromRequest($this->context.'.filter.payment_status', 'filter_payment_status', '', 'string');
		$this->setState('filter.payment_status', $paymentStatus);

		$paymentTransactionId = $app->getUserStateFromRequest($this->context.'.filter.payment_method_txn_id', 'filter_payment_method_txn_id', '', 'string');
		$this->setState('filter.payment_method_txn_id', $paymentTransactionId);
		
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$search = $app->getUserStateFromRequest($this->context.'.filter.checkin_from', 'filter_checkin_from');
		$this->setState('filter.checkin_from', $search);

		$search = $app->getUserStateFromRequest($this->context.'.filter.checkin_to', 'filter_checkin_to');
		$this->setState('filter.checkin_to', $search);

		$search = $app->getUserStateFromRequest($this->context.'.filter.checkout_from', 'filter_checkout_from');
		$this->setState('filter.checkout_from', $search);

		$search = $app->getUserStateFromRequest($this->context.'.filter.checkout_to', 'filter_checkout_to');
		$this->setState('filter.checkout_to', $search);

		$filterClear = $app->getUserStateFromRequest($this->context.'.filter.clear', 'filter_clear');
		$this->setState('filter.clear', $filterClear);

		$reservationAssetId = $app->getUserStateFromRequest($this->context.'.filter.reservation_asset_id', 'filter_reservation_asset_id', '');
		$this->setState('filter.reservation_asset_id', $reservationAssetId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_solidres');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('r.created_date', 'desc');

		if ($filterClear)
		{
			$this->setState('filter.state', NULL);
			$this->setState('filter.search', NULL);
			$this->setState('filter.checkin_from', NULL);
			$this->setState('filter.checkin_to', NULL);
			$this->setState('filter.checkout_from', NULL);
			$this->setState('filter.checkout_to', NULL);
			$this->setState('filter.payment_status', NULL);
			$this->setState('filter.payment_method_txn_id', NULL);
			$this->setState('filter.reservation_asset_id', NULL);
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$db	= $this->getDbo();
		$query	= $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'r.*, CONCAT(r.customer_firstname, \' \', r.customer_middlename, \' \', r.customer_lastname ) as customer_fullname'
			)
		);
		$query->from($db->quoteName('#__sr_reservations').' AS r');
		$query->join('LEFT', $db->quoteName('#__users').'r1 ON r.customer_id = r1.id');

		
		// Filter by published state
		$published = $this->getState('filter.state', NULL);
		if (!empty($published))
        {
			$query->where('r.state IN ( '. $published .')');
			//$query->where('r.state = '. $db->quote($published));
		}

		// Filter by payment status
		$paymentStatus = $this->getState('filter.payment_status', NULL);
		if (!empty($paymentStatus))
		{
			$query->where('r.payment_status = '. $db->quote($paymentStatus));
		}

		// Filter by reservation asset.
		$reservationAssetId = $this->getState('filter.reservation_asset_id');
		if (is_numeric($reservationAssetId))
		{
			$query->where('r.reservation_asset_id = '.(int) $reservationAssetId);
		}

		// Filter by payment transaction id
		$paymentTransactionId = $this->getState('filter.payment_method_txn_id', NULL);
		if (!empty($paymentTransactionId))
		{
			$query->where('r.payment_method_txn_id = '. $db->quote($paymentTransactionId));
		}

		// Filter by customer
		$customerId = $this->getState('filter.customer_id');
		if (is_numeric($customerId))
		{
			$query->where('r.customer_id = ' . (int) $customerId);
		}

		// If loading from front end, make sure we only load room types belongs to current user
		$isFrontEnd = JFactory::getApplication()->isSite();
		$partnerId = $this->getState('filter.partner_id', 0);
		if ($isFrontEnd && $partnerId > 0)
		{
			$query->join('INNER', $db->quoteName('#__sr_reservation_assets').' AS ra ON ra.id = r.reservation_asset_id AND ra.partner_id = ' . (int) $partnerId);
		}

		// Filter by checkin dates
		$checkinFrom = $this->getState('filter.checkin_from', '');
		$checkinTo = $this->getState('filter.checkin_to', '');
		if (!empty($checkinFrom) && !empty($checkinTo))
		{
			$query->where('checkin >= ' . $db->quote(date('Y-m-d', strtotime($checkinFrom))));
			$query->where('checkin <= ' . $db->quote(date('Y-m-d', strtotime($checkinTo))));
		}
		// Filter by checkin in period dates
		$checkin_next_dates = $this->getState('filter.checkin_next_dates', '');
		$checkin_previous_dates = $this->getState('filter.checkin_previous_dates', '');
		if (!empty($checkin_next_dates))
		{
			$query->where('checkin > ' . $db->quote(date('Y-m-d', strtotime($checkin_next_dates))));
		}
		if(!empty($checkin_previous_dates))
		{
			$query->where('checkin < ' . $db->quote(date('Y-m-d', strtotime($checkin_previous_dates))));
		}
		// Filter by checkout in period dates
		$checkout_next_dates = $this->getState('filter.checkout_next_dates', '');
		$checkout_previous_dates = $this->getState('filter.checkout_previous_dates', '');
		if (!empty($checkout_next_dates))
		{
			$query->where('checkout > ' . $db->quote(date('Y-m-d', strtotime($checkout_next_dates))));
		}
		if(!empty($checkout_previous_dates))
		{
			$query->where('checkout < ' . $db->quote(date('Y-m-d', strtotime($checkout_previous_dates))));
		}
		// Filter by checkout dates
		$checkoutFrom = $this->getState('filter.checkout_from', '');
		$checkoutTo = $this->getState('filter.checkout_to', '');
		if (!empty($checkoutFrom) && !empty($checkoutTo))
		{
			$query->where('checkout >= ' . $db->quote(date('Y-m-d', strtotime($checkoutFrom))));
			$query->where('checkout <= ' . $db->quote(date('Y-m-d', strtotime($checkoutTo))));
		}


		$search = $this->getState('filter.search');
		if (!empty($search))
        {
			if (stripos($search, 'id:') === 0)
            {
				$query->where('r.id = '.(int) substr($search, 3));
			}
            else
            {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('r.code LIKE '.$search);
			}
		}

		$groupBy = $this->getState('groupby');
		if (!empty($groupBy))
		{
			$query->group($groupBy);
		}

		$range = $this->getState('range');
		if($db->name == 'postgresql')
		{
			if (!empty($range))
			{
				if ($range == 'today')
				{
					$query->where('checkin = CURRENT_DATE');
				}
				else if ($range == 'thisweek')
				{
					$query->where('extract(week from checkin) = extract(week from CURRENT_DATE)');
				}
				else if ($range == 'thismonth')
				{
					$query->where('extract(month from checkin) = extract(month from CURRENT_DATE) and extract(year from checkin) = extract(year from CURRENT_DATE)');
				}
				else if ($range == 'last3')
				{
					$query->where('extract(month from checkin) >= extract(month from CURRENT_DATE) - 2 and extract(year from checkin) = extract(year from CURRENT_DATE)');
				}
				else if ($range == 'last6')
				{
					$query->where('extract(month from checkin) >= extract(month from CURRENT_DATE) - 5 and extract(year from checkin) = extract(year from CURRENT_DATE)');
				}
				else if ($range == 'lastweek')
				{
					$query->where('extract(week from checkin) = extract(week from CURRENT_DATE) - 1 and extract(year from checkin) = extract(year from CURRENT_DATE)');
				}
				else if ($range == 'lastmonth')
				{
					$query->where('extract(month from checkin) = extract(month from CURRENT_DATE) - 1 and extract(year from checkin) = extract(year from CURRENT_DATE)');
				}
				else if ($range == 'lastyear')
				{
					$query->where('extract(year from checkin) = extract(year from CURRENT_DATE) - 1');
				}
				else if ($range == 'customrange')
				{
					$query->where('checkin >= '.$this->_db->quote(date('Y-m-d', strtotime($this->getState('startDateTime')))).
					' AND checkin <= '.$this->_db->quote(date('Y-m-d', strtotime($this->getState('endDateTime')))));
				}
			}
		}
		else
		{
			if (!empty($range))
			{
				if ($range == 'today')
				{
					$query->where('checkin = CURRENT_DATE');
				}else if ($range == 'thisweek')
				{
					$query->where('WEEKOFYEAR(checkin) = WEEKOFYEAR(NOW())');
				}
				else if ($range == 'thismonth')
				{
					$query->where('MONTH(checkin) = MONTH(NOW()) and YEAR(checkin) = YEAR(NOW())');
				}
				else if ($range == 'last3')
				{
					$query->where('MONTH(checkin) >= (MONTH(NOW()) - 2) and YEAR(checkin) = YEAR(NOW())');
				}
				else if ($range == 'last6')
				{
					$query->where('MONTH(checkin) >= (MONTH(NOW()) - 5) and YEAR(checkin) = YEAR(NOW())');
				}
				else if ($range == 'lastweek')
				{
					$query->where('WEEK(checkin) = (WEEK(NOW()) - 1) and YEAR(checkin) = YEAR(NOW())');
				}
				else if ($range == 'lastmonth')
				{
					$query->where('MONTH(checkin) = (MONTH(NOW() - INTERVAL 1 MONTH)) and YEAR(checkin) = YEAR(NOW())');
				}
				else if ($range == 'lastyear')
				{
					$query->where('YEAR(checkin) = (YEAR(NOW()) - 1)');
				}
				else if ($range == 'customrange')
				{
					$query->where('checkin >= '.$this->_db->quote(date('Y-m-d', strtotime($this->getState('startDateTime')))).
					' AND checkin <= '.$this->_db->quote(date('Y-m-d', strtotime($this->getState('endDateTime')))));
				}
			}
		}
		if($this->getState('list.ordering', 'r.id') == 'r.id')
        {
			$query->order($db->escape($this->getState('list.ordering', 'r.id')).' '.$db->escape($this->getState('list.direction', 'ASC')));
		}
        else
        {
			// Add the list ordering clause.
			$query->order($db->escape($this->getState('list.ordering')).' '.$db->escape($this->getState('list.direction', 'ASC')));
		}
		return $query;
	}
}