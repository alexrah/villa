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
 * System model.
 *
 * @package     Solidres
 * @subpackage	System
 * @since		0.1.0
 */
class SolidresModelSystem extends JModelAdmin
{
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
		$form = $this->loadForm('com_solidres.reservationasset', 'reservationasset', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
        {
			return false;
		}

		// Determine correct permissions to check.
		if ($this->getState('asset.id'))
        {
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
		}
        else
        {
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		return $form;
	}

    /**
     * Install sample data
     * 
     * @return bool
     */
	public function installSampleData()
	{
		$config  = JFactory::getConfig();

		$defaultDbType = $config->get('dbtype');

		if ($defaultDbType == 'mysql' || $defaultDbType == 'mysqli')
		{
			$defaultDbType = 'mysql';
		}

		$data = JPATH_COMPONENT_ADMINISTRATOR.'/sql/'. $defaultDbType .'/sample.sql';

		// Attempt to import the database schema.
		if (!file_exists($data))
        {
			$this->setError(JText::sprintf('SR_INSTL_DATABASE_FILE_DOES_NOT_EXIST', $data));
			return false;			
		}
		elseif (!$this->populateDatabase($data))
        {
			$this->setError(JText::sprintf('SR_INSTL_ERROR_DB', $this->getError()));
			return false;
		}

		return true;
	}

	/**
	 * Method to import a database schema from a file.
	 *
	 * @access	public
	 * @param	string	$schema Path to the schema file.
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	function populateDatabase($schema)
	{
		// Initialise variables.
		$return = true;

		// Get the contents of the schema file.
		if (!($buffer = file_get_contents($schema)))
        {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Get an array of queries from the schema and process them.
		$queries = $this->_splitQueries($buffer);
		foreach ($queries as $query)
		{
			// Trim any whitespace.
			$query = trim($query);

			// If the query isn't empty and is not a comment, execute it.
			if (!empty($query) && ($query{0} != '#'))
			{
				// Execute the query.
				$this->_db->setQuery($query);
				$this->_db->execute();

				// Check for errors.
				if ($this->_db->getErrorNum())
                {
					$this->setError($this->_db->getErrorMsg());
					$return = false;
				}
			}
		}

		return $return;
	}

	/**
	 * Method to split up queries from a schema file into an array.
	 *
	 * @access	protected
	 * @param	string	$sql SQL schema.
	 * @return	array	Queries to perform.
	 * @since	1.0
	 */
	function _splitQueries($sql)
	{
		// Initialise variables.
		$buffer		= array();
		$queries	= array();
		$in_string	= false;

		// Trim any whitespace.
		$sql = trim($sql);

		// Remove comment lines.
		$sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);

		// Parse the schema file to break up queries.
		for ($i = 0; $i < strlen($sql) - 1; $i ++)
		{
			if ($sql[$i] == ";" && !$in_string)
            {
				$queries[] = substr($sql, 0, $i);
				$sql = substr($sql, $i +1);
				$i = 0;
			}

			if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\")
            {
				$in_string = false;
			}
			elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\"))
            {
				$in_string = $sql[$i];
			}
			if (isset ($buffer[1]))
            {
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $sql[$i];
		}

		// If the is anything left over, add it to the queries.
		if (!empty($sql))
        {
			$queries[] = $sql;
		}

		return $queries;
	}
}