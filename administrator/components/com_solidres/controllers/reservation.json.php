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
 * Reservation controller class.
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.4.0
 */
class SolidresControllerReservation extends JControllerForm
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
		$allow	= null;

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

	public function save($key = NULL, $urlVar = NULL)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$filterMask = 'int';
		$pk = $input->get('pk', 0);
		$name = $input->get('name', 0, 'string');
		if (in_array($name, array('total_paid')))
		{
			$filterMask = 'double';
		}
		$value = $input->get('value', 0, $filterMask);

		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_solidres/tables');
		$table = JTable::getInstance('Reservation', 'SolidresTable');
		$currencyFields = array(
			'total_price', 'total_price_tax_incl', 'total_price_tax_excl', 'total_extra_price', 'total_extra_price_tax_incl',
			'total_extra_price_tax_excl', 'total_discount', 'total_paid', 'deposit_amount'
		);

		$table->load($pk);
		$table->$name = $value;
		$result = $table->store();
		$newValue = $table->$name;

		if (in_array($name, $currencyFields))
		{
			JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
			$baseCurrency = new SRCurrency($value, $table->currency_id);
			$newValue = $baseCurrency->format();
		}

		if($value == 1)
		{
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('solidres');
			$invoice = $dispatcher->trigger('onSolidresGenerateInvoice', array($pk));
		}
		echo json_encode(array('success' => $result, 'newValue' => $newValue));
	}
}
