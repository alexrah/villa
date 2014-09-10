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
 * Tariff model.
 *
 * @package     Solidres
 * @subpackage	Tariiff
 * @since		0.1.0
 */
class SolidresModelTariff extends JModelAdmin
{
	const PER_ROOM_PER_NIGHT = 0;

	const PER_PERSON_PER_NIGHT = 1;

	const PACKAGE_PER_ROOM = 2;

	const PACKAGE_PER_PERSON = 3;

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
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->event_after_delete 	= 'onTariffAfterDelete';
		$this->event_after_save 	= 'onTariffAfterSave';
		$this->event_before_delete 	= 'onTariffBeforeDelete';
		$this->event_before_save 	= 'onTariffBeforeSave';
		$this->event_change_state 	= 'onTariffChangeState';
		$this->text_prefix 			= strtoupper($this->option);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		$user = JFactory::getUser();
		
		return $user->authorise('core.delete', 'com_solidres.tariff.'.(int) $record->id);
	}
	
	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		$table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);
		$nullDate = substr($this->_db->getNullDate(), 0, 10);

		// If customer group id is empty, then set it to nulll
		if ($table->customer_group_id === '')
		{
			$table->customer_group_id = NULL;
		}

		if ($table->valid_from != $nullDate )
		{
			$table->valid_from = date('Y-m-d', strtotime($table->valid_from));
		}

		if ($table->valid_to != $nullDate )
		{
			$table->valid_to = date('Y-m-d', strtotime($table->valid_to));
		}

		if (!empty($table->limit_checkin))
		{
			$table->limit_checkin = json_encode($table->limit_checkin);
		}
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		return $user->authorise('core.edit.state', 'com_solidres.tariff.'.(int) $record->id);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getTable($name = 'Tariff', $prefix = 'SolidresTable', $options = array())
	{
		return JTable::getInstance($name, $prefix, $options);
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
		$form = $this->loadForm('com_solidres.tariff', 'tariff', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
        {
			return false;
		}

		return $form;
	}

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, False on error.
     *
     * @since   11.1
     */
    public function save($data)
    {
        // Initialise variables;
        $dispatcher = JDispatcher::getInstance();
        $table = $this->getTable();
        $key = $table->getKeyName();
        $pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $isNew = true;

        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('extension');

        // Allow an exception to be thrown.
        try
        {
            // Load the row if saving an existing record.
            if ($pk > 0)
            {
                $table->load($pk);
                $isNew = false;
				if (!$isNew)
				{
					if ($table->type != $data['type'])
					{
						$dbo = JFactory::getDbo();
						$query = $dbo->getQuery(true);
						$query->delete()
							->from($dbo->quoteName('#__sr_tariff_details'))
							->where('tariff_id = ' . $table->id);
						$dbo->setQuery($query)->execute();
					}
				}
            }

            // Bind the data.
            if (!$table->bind($data))
            {
                $this->setError($table->getError());
                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check())
            {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew, $data));
            if (in_array(false, $result, true))
            {
                $this->setError($table->getError());
                return false;
            }
            // Store the data.
            if (!$table->store(true))
            {
                $this->setError($table->getError());
                return false;
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the onContentAfterSave event.
            $dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew, $data));
        }
        catch (Exception $e)
        {
            $this->setError($e->getMessage());

            return false;
        }

        $pkName = $table->getKeyName();

        if (isset($table->$pkName))
        {
            $this->setState($this->getName() . '.id', $table->$pkName);
        }
        $this->setState($this->getName() . '.new', $isNew);

        return true;
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
		$data = JFactory::getApplication()->getUserState('com_solidres.edit.tariff.data', array());

		if (empty($data))
        {
			$data = $this->getItem();
		}

		return $data;
	}

	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		// TODO replace this manual call with autoloading later
		JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');

		$typeNameMapping = array(
			0 => JText::_('SR_TARIFF_PER_ROOM_PER_NIGHT'),
			1 => JText::_('SR_TARIFF_PER_PERSON_PER_NIGHT'),
			2 => JText::_('SR_TARIFF_PACKAGE_PER_ROOM'),
			3 => JText::_('SR_TARIFF_PACKAGE_PER_PERSON')
		);

		if (isset($item->id))
		{
			$modelTariffDetails = JModelLegacy::getInstance('TariffDetails', 'SolidresModel', array('ignore_request' => true));
			$item->type_name = $typeNameMapping[$item->type];
			$item->valid_from = $item->valid_from != '0000-00-00' ?  date('d-m-Y', strtotime($item->valid_from)) : '00-00-0000';
			$item->valid_to = $item->valid_to != '0000-00-00' ? date('d-m-Y', strtotime($item->valid_to)) : '00-00-0000';
			$item->customer_group_id = is_null($item->customer_group_id) ? '' : $item->customer_group_id;
			$item->limit_checkin = isset($item->limit_checkin) ? json_decode($item->limit_checkin) : NULL ;

			if ( (int)$item->type == self::PER_ROOM_PER_NIGHT )
			{
				$modelTariffDetails->setState('filter.tariff_id', (int) $item->id);
				$modelTariffDetails->setState('filter.guest_type', NULL);
				$results = $modelTariffDetails->getItems();

				if (!empty($results))
				{
					$item->details['per_room'] = $results;
				}
				else
				{
					$item->details['per_room'] = SRUtilities::getTariffDetailsScaffoldings(array(
						'tariff_id' => $item->id,
						'guest_type' => NULL,
						'type' => $item->type
					));
				}

				$item->details['per_room'] = SRUtilities::translateDayWeekName($item->details['per_room']);
			}
			else if ( (int) $item->type == self::PER_PERSON_PER_NIGHT )
			{
				// Query to get tariff details for each guest type
				// First we need to get the occupancy number
				$modelRoomType = JModelLegacy::getInstance('RoomType', 'SolidresModel', array('ignore_request' => true));
				$roomType = $modelRoomType->getItem($item->room_type_id);
				$occupancyAdult = $roomType->occupancy_adult;
				$occupancyChild = $roomType->occupancy_child;

				// Get tariff details for all adults
				for ($i = 1; $i <= $occupancyAdult; $i++)
				{
					$modelTariffDetails->setState('filter.tariff_id', (int) $item->id);
					$modelTariffDetails->setState('filter.guest_type', 'adult'.$i);
					$results = $modelTariffDetails->getItems();

					if (!empty($results))
					{
						$item->details['adult'.$i] = $results;
					}
					else
					{
						$item->details['adult'.$i] = SRUtilities::getTariffDetailsScaffoldings(array(
							'tariff_id' => $item->id,
							'guest_type' => 'adult'.$i,
							'type' => $item->type
						));
					}

					$item->details['adult'.$i] = SRUtilities::translateDayWeekName($item->details['adult'.$i]);
				}

				// Get tariff details for all children
				for ($i = 1; $i <= $occupancyChild; $i++)
				{
					$modelTariffDetails->setState('filter.tariff_id', (int) $item->id);
					$modelTariffDetails->setState('filter.guest_type', 'child'.$i);
					$results = $modelTariffDetails->getItems();

					if (!empty($results))
					{
						$item->details['child'.$i] = $results;
					}
					else
					{
						$item->details['child'.$i] = SRUtilities::getTariffDetailsScaffoldings(array(
							'tariff_id' => $item->id,
							'guest_type' => 'child'.$i,
							'type' => $item->type
						));
					}

					$item->details['child'.$i] = SRUtilities::translateDayWeekName($item->details['child'.$i]);
				}
			}
			else if ( (int) $item->type == self::PACKAGE_PER_ROOM )
			{
				$modelTariffDetails->setState('filter.tariff_id', (int) $item->id);
				$modelTariffDetails->setState('filter.guest_type', NULL);
				$results = $modelTariffDetails->getItems();

				if (!empty($results))
				{
					$item->details['per_room'] = $results;
				}
				else
				{
					$item->details['per_room'] = SRUtilities::getTariffDetailsScaffoldings(array(
						'tariff_id' => $item->id,
						'guest_type' => NULL,
						'type' => $item->type
					));
				}
			}
			else if ( (int) $item->type == self::PACKAGE_PER_PERSON )
			{
				// Query to get tariff details for each guest type
				// First we need to get the occupancy number
				$modelRoomType = JModelLegacy::getInstance('RoomType', 'SolidresModel', array('ignore_request' => true));
				$roomType = $modelRoomType->getItem($item->room_type_id);
				$occupancyAdult = $roomType->occupancy_adult;
				$occupancyChild = $roomType->occupancy_child;

				// Get tariff details for all adults
				for ($i = 1; $i <= $occupancyAdult; $i++)
				{
					$modelTariffDetails->setState('filter.tariff_id', (int) $item->id);
					$modelTariffDetails->setState('filter.guest_type', 'adult'.$i);
					$results = $modelTariffDetails->getItems();

					if (!empty($results))
					{
						$item->details['adult'.$i] = $results;
					}
					else
					{
						$item->details['adult'.$i] = SRUtilities::getTariffDetailsScaffoldings(array(
							'tariff_id' => $item->id,
							'guest_type' => 'adult'.$i,
							'type' => $item->type
						));
					}

					//$item->details['adult'.$i] = SRUtilities::translateDayWeekName($item->details['adult'.$i]);
				}

				// Get tariff details for all children
				for ($i = 1; $i <= $occupancyChild; $i++)
				{
					$modelTariffDetails->setState('filter.tariff_id', (int) $item->id);
					$modelTariffDetails->setState('filter.guest_type', 'child'.$i);
					$results = $modelTariffDetails->getItems();

					if (!empty($results))
					{
						$item->details['child'.$i] = $results;
					}
					else
					{
						$item->details['child'.$i] = SRUtilities::getTariffDetailsScaffoldings(array(
							'tariff_id' => $item->id,
							'guest_type' => 'child'.$i,
							'type' => $item->type
						));
					}

					//$item->details['child'.$i] = SRUtilities::translateDayWeekName($item->details['child'.$i]);
				}
			}
		}

		return $item;
	}
}