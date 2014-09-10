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
 * View to edit a Reservation.
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresViewReservation extends JViewLegacy
{
	protected $state;
	protected $form;
	protected $invoiceTable;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$this->state = $model->getState();
		$this->form	= $model->getForm();

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if (!in_array($this->form->getValue('payment_method_id'), array('paylater', 'bankwire')))
		{
			JFactory::getLanguage()->load('plg_solidrespayment_'.$this->form->getValue('payment_method_id'), JPATH_ADMINISTRATOR, null, 1);
		}

		JText::script("SR_RESERVATION_NOTE_NOTIFY_CUSTOMER");
		JText::script("SR_RESERVATION_NOTE_DISPLAY_IN_FRONTEND");

		JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);

		$solidresRoomType = SRFactory::get('solidres.roomtype.roomtype');
		$this->numberOfNights = (int)$solidresRoomType->calculateDateDiff($this->form->getValue('checkin'), $this->form->getValue('checkout'));
		if(SR_PLUGIN_INVOICE_ENABLED)
		{
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('solidres');
			$this->invoiceTable = $dispatcher->trigger('onSolidresLoadReservation', array($this->form->getValue('id')));
		}
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);
		include JPATH_COMPONENT.'/helpers/toolbar.php';

		JToolBarHelper::title(JText::_('SR_EDIT_RESERVATION'), 'generic.png');
		JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
		JHtml::_('behavior.tooltip');
		SRHtml::_('jquery.validate');
		$id = $this->form->getValue('id');
		
		if (empty($id))
		{
			JToolBarHelper::cancel('reservation.cancel', 'JToolbar_Cancel');
		}
		else
		{
			JToolBarHelper::cancel('reservation.cancel', 'JToolbar_Close');
		}
	}
}
