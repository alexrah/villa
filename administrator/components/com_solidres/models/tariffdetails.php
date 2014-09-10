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
 * Tariff model
 *
 * @package     Solidres
 * @subpackage	TariffDetails
 * @since		0.1.0
 */
class SolidresModelTariffDetails extends JModelList
{
    /**
     * Constructor.
     *
     * @param	array	$config An optional associative array of configuration settings.
     * @see		JController
     * @since	1.6
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * Override the default function since we need to generate different store id for
     * different data set depended on room type id
     *
     * @see     \components\com_solidres\models\reservation.php (181 ~ 186)
     *
     * @param   string  $id  An identifier string to generate the store id.
     *
     * @return  string  A store id.
     *
     * @since   11.1
     */
    protected function getStoreId($id = '')
    {
        // Add the list state to the store id.
		$id .= ':' . $this->getState('filter.tariff_id');
		$id .= ':' . $this->getState('filter.guest_type');

        return md5($this->context . ':' . $id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);

        $query->select( $this->getState('list.select', 't.*' ));
        $query->from($dbo->quoteName('#__sr_tariff_details').' AS t');
		$tariffId = $this->getState('filter.tariff_id', NULL);
		$guestType = $this->getState('filter.guest_type', NULL);

		if (isset($tariffId))
		{
			$query->where('t.tariff_id = '.(int) $tariffId);
		}

		if (isset($guestType))
		{
			$query->where('t.guest_type = '.$dbo->quote($guestType));
		}

		$query->order('w_day ASC');

        return $query;
    }
}
