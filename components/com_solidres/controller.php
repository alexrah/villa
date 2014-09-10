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
 * Solidres Component Controller
 *
 * @package     Solidres
 * @since 		0.1.0
 */
class SolidresController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable If true, the view output will be cached
	 * @param	boolean			$urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JControllerLegacy		This object to support chaining.
	 * @since	1.5
	 */
	function display($cachable = false, $urlparams = false)
	{
		$cachable = true;

		$lang = JFactory::getLanguage();
		$lang->load('com_solidres', JPATH_ADMINISTRATOR);
		if (SR_PLUGIN_HUB_ENABLED)
		{
			$lang->load('plg_solidres_hub', JPATH_ADMINISTRATOR);
		}

		JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);

		// TODO: need to review these params, make sure only allowed params can be set
		$safeurlparams = array('catid'=>'INT','id'=>'INT','cid'=>'ARRAY','year'=>'INT','month'=>'INT','limit'=>'INT','limitstart'=>'INT',
			'showall'=>'INT','return'=>'BASE64','filter'=>'STRING','filter_order'=>'CMD','filter_order_Dir'=>'CMD','filter-search'=>'STRING','print'=>'BOOLEAN','lang'=>'CMD');

		$viewName = $this->input->get('view');
		$user = JFactory::getUser();

		switch ($viewName)
		{
			case 'customer':
			case 'dashboard':
			case 'reservationassetform':
			case 'medialist':
			case 'reservationform':
			case 'roomtypeform':
			case 'couponform':
			case 'extraform':
			case 'limitbookingform':
				// If the user is a guest, redirect to the login page.
				if ($user->get('guest') == 1)
				{
					// Redirect to login page.
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
					return;
				}
				parent::display($cachable, $safeurlparams);
				break;
			case 'reservationassets':
				if ($user->get('guest') == 1)
				{
					// Redirect to login page.
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
					return;
				}

				JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/tables', 'SolidresTable');
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/models', 'SolidresModel');
				$customerTable = JTable::getInstance('Customer', 'SolidresTable');
				$customerTable->load(array('user_id' => $user->get('id')));
				$model = JModelLegacy::getInstance('ReservationAssets', 'SolidresModel', array('ignore_request' => false));
				$model->setState('filter.partner_id', $customerTable->id);

				$document = JFactory::getDocument();
				$viewType = $document->getType();
				$viewName = 'ReservationAssets';
				$viewLayout = 'default';

				$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($model, true);
				$view->document = $document;
				$view->display();
				break;
			case 'roomtypes':
				if ($user->get('guest') == 1)
				{
					// Redirect to login page.
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
					return;
				}

				JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/tables', 'SolidresTable');
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/models', 'SolidresModel');
				$customerTable = JTable::getInstance('Customer', 'SolidresTable');
				$customerTable->load(array('user_id' => $user->get('id')));
				$model = JModelLegacy::getInstance('RoomTypes', 'SolidresModel', array('ignore_request' => false));
				$model->setState('filter.partner_id', $customerTable->id);

				$document = JFactory::getDocument();
				$viewType = $document->getType();
				$viewName = 'RoomTypes';
				$viewLayout = 'default';

				$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($model, true);
				$view->document = $document;
				$view->display();
				break;
			case 'reservations':
				if ($user->get('guest') == 1)
				{
					// Redirect to login page.
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
					return;
				}
				JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/tables', 'SolidresTable');
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/models', 'SolidresModel');
				$customerTable = JTable::getInstance('Customer', 'SolidresTable');
				$customerTable->load(array('user_id' => $user->get('id')));
				$model = JModelLegacy::getInstance('Reservations', 'SolidresModel', array('ignore_request' => false));
				$model->setState('filter.partner_id', $customerTable->id);

				$document = JFactory::getDocument();
				$viewType = $document->getType();
				$viewName = 'Reservations';
				$viewLayout = 'default';

				$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($model, true);
				$view->document = $document;
				$view->display();
				break;
			case 'coupons':
				if ($user->get('guest') == 1)
				{
					// Redirect to login page.
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
					return;
				}

				JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/tables', 'SolidresTable');
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/models', 'SolidresModel');
				$customerTable = JTable::getInstance('Customer', 'SolidresTable');
				$customerTable->load(array('user_id' => $user->get('id')));

				$model = JModelLegacy::getInstance('Coupons', 'SolidresModel', array('ignore_request' => false));
				$model->setState('filter.partner_id', $customerTable->id);

				$document = JFactory::getDocument();
				$viewType = $document->getType();
				$viewName = 'Coupons';
				$viewLayout = 'default';

				$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($model, true);
				$view->document = $document;
				$view->display();
				break;
			case 'extras':
				if ($user->get('guest') == 1)
				{
					// Redirect to login page.
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
					return;
				}

				JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/tables', 'SolidresTable');
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/models', 'SolidresModel');
				$customerTable = JTable::getInstance('Customer', 'SolidresTable');
				$customerTable->load(array('user_id' => $user->get('id')));
				$model = JModelLegacy::getInstance('Extras', 'SolidresModel', array('ignore_request' => false));
				$model->setState('filter.partner_id', $customerTable->id);

				$document = JFactory::getDocument();
				$viewType = $document->getType();
				$viewName = 'Extras';
				$viewLayout = 'default';

				$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($model, true);
				$view->document = $document;
				$view->display();
				break;
			case 'limitbookings':
				if ($user->get('guest') == 1)
				{
					// Redirect to login page.
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
					return;
				}

				JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/tables', 'SolidresTable');
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/models', 'SolidresModel');
				$customerTable = JTable::getInstance('Customer', 'SolidresTable');
				$customerTable->load(array('user_id' => $user->get('id')));
				$model = JModelLegacy::getInstance('LimitBookings', 'SolidresModel', array('ignore_request' => false));
				$model->setState('filter.partner_id', $customerTable->id);

				$document = JFactory::getDocument();
				$viewType = $document->getType();
				$viewName = 'LimitBookings';
				$viewLayout = 'default';

				$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($model, true);
				$view->document = $document;
				$view->display();
				break;
			default:
				parent::display($cachable, $safeurlparams);
				break;
		}

		return $this;
	}
}