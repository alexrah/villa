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
 * System controller class.
 *
 * @package     Solidres
 * @subpackage	System
 * @since		0.1.0
 */
class SolidresControllerSystem extends JControllerForm
{
	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param	array $data An array of input data.
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowAdd($data = array())
	{
		$allow		= null;

		if ($allow === null)
        {
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd($data);
		} else {
			return $allow;
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @param	array $data An array of input data.
	 * @param	string $key The name of the key for the primary key.
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return parent::allowEdit($data, $key);
	}

	public function &getModel($name = 'System', $prefix = 'SolidresModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Install sample data
	 *
	 * @return void
	 */
	public function installSampleData()
	{
		$model = $this->getModel();
		$result = $model->installSampleData();
		
		if(!$result) {
			JError::raiseNotice(500, $model->getError());
		} else {
			$msg = JText::_('SR_INSTALL_SAMPLE_DATA_SUCCESS');
			$this->setRedirect('index.php?option=com_solidres', $msg);
		}
	}
}