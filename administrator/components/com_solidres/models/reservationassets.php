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
 * Reservation Assets model
 *
 * @package     Solidres
 * @subpackage	ReservationAsset
 * @since		0.1.0
 */
class SolidresModelReservationAssets extends JModelList
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
				'id', 'a.id',
				'name', 'a.name',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'category_name', 'category_name',
                'number_of_roomtype', 'number_of_roomtype'
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
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$accessId = $app->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);

		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
		
		$categoryId = $app->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

        $countryId = $app->getUserStateFromRequest($this->context.'.filter.country_id', 'filter_country_id', '');
		$this->setState('filter.country_id', $countryId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_solidres');
		$this->setState('params', $params);

		// Load the request parameters (for parameters set in menu type)
		$location = $app->input->getString('location');
		$this->setState('filter.city', $location);
		$categories = $app->input->getString('categories');
		$this->setState('filter.category_id', !empty($categories) ? array_map('intval', explode(',', $categories)) : '');
		$displayMode = $app->input->getString('mode');
		$this->setState('display.mode', $displayMode);

		// Determine what view we are in because this model is used from multiple views
		$displayView = $app->input->getString('view');
		$this->setState('display.view', $displayView);

		// List state information.
		parent::populateState('a.name', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user = JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
				)
		);
		$query->from($db->quoteName('#__sr_reservation_assets').' AS a');

		$query->select('cat.title AS category_name');
		$query->join('LEFT', $db->quoteName('#__categories').' AS cat ON cat.id = a.category_id');
		$query->group('cat.title');

        $query->select('COUNT(rt.id) AS number_of_roomtype');
		$query->join('LEFT', $db->quoteName('#__sr_room_types').' AS rt ON rt.reservation_asset_id = a.id');

        $query->select('cou.name AS country_name');
		$query->join('LEFT', $db->quoteName('#__sr_countries').' AS cou ON cou.id = a.country_id');
		$query->group('cou.name');
		
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', $db->quoteName('#__users').' AS uc ON uc.id=a.checked_out');
		$query->group('uc.name');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', $db->quoteName('#__viewlevels').' AS ag ON ag.id = a.access');
		$query->group('ag.title');

		// Filter by access level.
		/*if ($access = $this->getState('filter.access'))
        {
			$query->where('a.access = '.(int) $access);
		}*/

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')')
				->where('cat.access IN (' . $groups . ')');
		}

		// If loading from front end, make sure we only load asset belongs to current user
		$isFrontEnd = JFactory::getApplication()->isSite();
		$partnerId = $this->getState('filter.partner_id', 0);
		if ($isFrontEnd && $partnerId > 0)
		{
			$query->where('a.partner_id = ' . (int) $partnerId);
		}

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
        {
			$query->where('a.state = '.(int) $published);
		}
        else if ($published === '')
        {
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by category, support multiple category filter
		$categoryIds = $this->getState('filter.category_id');
		if (!empty($categoryIds))
        {
			$categoryIds = (array) $categoryIds;
			$whereClauseFilterByCategories = array();
			foreach ($categoryIds as $categoryId)
			{
				$whereClauseFilterByCategories[] = 'a.category_id = '. $db->quote($categoryId);
			}
			$query->where('(' . implode(' OR ', $whereClauseFilterByCategories) . ')');
		}

		// Filter by facility, support multiple facility filter
		$facilityIds = $this->getState('filter.facility_id');
		if (!empty($facilityIds))
		{
			$facilityIds = (array) $facilityIds;
			$whereClauseFilterByFacilities = array();
			foreach ($facilityIds as $facilityId)
			{
				$whereClauseFilterByFacilities[] =
					'1 = (SELECT count(*) FROM '. $db->quoteName('#__sr_facility_reservation_asset_xref') .'
           			WHERE facility_id = '.(int) $facilityId.'  AND reservation_asset_id = a.id )';
			}
			$query->where('(' . implode(' AND ', $whereClauseFilterByFacilities) . ')');
		}

		// Filter by theme, support multiple theme filter
		$themeIds = $this->getState('filter.theme_id');
		if (!empty($themeIds))
		{
			$themeIds = (array) $themeIds;
			$whereClauseFilterByThemes = array();
			foreach ($themeIds as $themeId)
			{
				$whereClauseFilterByThemes[] =
					'1 = (SELECT count(*) FROM '. $db->quoteName('#__sr_reservation_asset_theme_xref') .'
           			WHERE theme_id = '.(int) $themeId.'  AND reservation_asset_id = a.id )';
			}
			$query->where('(' . implode(' AND ', $whereClauseFilterByThemes) . ')');
		}

        // Filter by country.
		$countryId = $this->getState('filter.country_id');
		if (is_numeric($countryId))
        {
			$query->where('a.country_id = '.(int) $countryId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
        {
			if (stripos($search, 'id:') === 0)
            {
				$query->where('a.id = '.(int) substr($search, 3));
			}
            else
            {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('a.name LIKE '.$search.' OR a.alias LIKE '.$search);
			}
		}

		// Filter by city name
		$city = $this->getState('filter.city', '');
		if (!empty($city))
		{
			$query->where('a.city LIKE '. $db->quote('%'.$city.'%'));
		}

		// Filter by star
		$stars = $this->getState('filter.stars', '');
		if (!empty($stars))
		{
			$whereClauseFilterByStars = array();
			foreach ($stars as $star)
			{
				$whereClauseFilterByStars[] = 'a.rating = '. $db->quote($star);
			}
			$query->where('(' . implode(' OR ', $whereClauseFilterByStars) . ')');
		}

        $query->group('a.id');

		if ($this->getState('list.ordering', 'a.ordering') == 'a.ordering')
        {
			$query->order($db->escape($this->getState('list.ordering', 'a.ordering')).' '.$db->escape($this->getState('list.direction', 'ASC')));
		}
        else
        {
			// Add the list ordering clause.
			$query->order($db->escape($this->getState('list.ordering', 'a.name')).' '.$db->escape($this->getState('list.direction', 'ASC')));
		}
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$checkin = $this->getState('filter.checkin');
		$checkout = $this->getState('filter.checkout');
		$displayView = $this->getState('display.view');

		// For front end
		if (SR_PLUGIN_HUB_ENABLED)
		{
			$isFrontEnd = JFactory::getApplication()->isSite();
			if ($isFrontEnd && $displayView != 'reservationassets')
			{
				$modelReservationAsset = JModelLegacy::getInstance('ReservationAsset', 'SolidresModel', array('ignore_request' => true));
				if (!empty($checkin) && !empty($checkout))
				{
					$modelReservationAsset->setState('checkin',	$checkin);
					$modelReservationAsset->setState('checkout', $checkout);
					$modelReservationAsset->setState('prices', $this->getState('filter.prices'));
					$modelReservationAsset->setState('show_price_with_tax', $this->getState('list.show_price_with_tax'));
					$modelReservationAsset->setState('origin', $this->getState('origin'));
				}

				$results = array();

				if (!empty($items))
				{
					foreach ($items as $item)
					{
						$modelReservationAsset->setState('reservationasset.id', $item->id);
						$results[$item->id] = $modelReservationAsset->getItem();
					}
				}

				return $results;
			}
		}

		return $items;
	}

	public function getStart()
	{
		return $this->getState('list.start');
	}

	/**
	 * Method to get a JPagination object for the data set.
	 *
	 * @return  JPagination  A JPagination object for the data set.
	 *
	 * @since   12.2
	 */
	public function getPagination()
	{
		// Get a storage key.
		$store = $this->getStoreId('getPagination');
		JLoader::register('SRPagination', SRPATH_LIBRARY . '/pagination/pagination.php');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Create the pagination object.
		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new SRPagination($this->getTotal(), $this->getStart(), $limit);

		// Add the object to the internal cache.
		$this->cache[$store] = $page;

		return $this->cache[$store];
	}
}