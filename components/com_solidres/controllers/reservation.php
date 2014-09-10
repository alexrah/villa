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

JLoader::register('SolidresHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/helper.php');

/**
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresControllerReservation extends JControllerLegacy
{
	protected $reservationData = array();

	protected $selectedRoomTypes = array();

	protected $reservationAssetId = array();

	protected $bookingConditionsArticleId = 0;

	protected $privacyPolicyArticleId = 0;

	protected $solidresConfig;

	protected $solidresPaymentPlugins;

	public function __construct($config = array())
	{
		$config['model_path'] = JPATH_COMPONENT_ADMINISTRATOR . '/models';
		parent::__construct($config);

		$this->app = JFactory::getApplication();
		$this->context = 'com_solidres.reservation.process';
		$this->solidresConfig = JComponentHelper::getParams('com_solidres');
		$this->reservationData['checkin'] = $this->app->getUserState($this->context . '.checkin');
		$this->reservationData['checkout'] = $this->app->getUserState($this->context . '.checkout');
		$this->solidresPaymentPlugins = SolidresHelper::getPaymentPluginOptions(true);
		// Load payment plugins language
		foreach ($this->solidresPaymentPlugins as $paymentPlugin)
		{
			$paymentPluginId = $paymentPlugin->element;
			// Load solidres plugin language
			$lang = JFactory::getLanguage();
			$lang->load('plg_solidrespayment_'.$paymentPluginId, JPATH_ADMINISTRATOR);
		}
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	$name The model name. Optional.
	 * @param	string	$prefix The class prefix. Optional.
	 * @param	array	$config Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 * @since	1.5
	 */
	public function &getModel($name = 'Reservation', $prefix = 'SolidresModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Build a correct data structure for the saving
	 *
	 * @since 0.3.0
	 */
	protected function prepareSavingData()
	{
		if (is_array($this->app->getUserState($this->context.'.room')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.room'));
		}

		if (is_array($this->app->getUserState($this->context.'.guest')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.guest'));
		}

		if (is_array($this->app->getUserState($this->context.'.cost')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.cost'));
		}

		if (is_array($this->app->getUserState($this->context.'.discount')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.discount'));
		}

		if (is_array($this->app->getUserState($this->context.'.coupon')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.coupon'));
		}

		if (is_array($this->app->getUserState($this->context.'.deposit')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.deposit'));
		}

		$this->reservationData['total_extra_price'] = $this->reservationData['total_extra_price_per_room'] + $this->reservationData['total_extra_price_per_booking'];
		$this->reservationData['total_extra_price_tax_incl'] = $this->reservationData['total_extra_price_tax_incl_per_room'] + $this->reservationData['total_extra_price_tax_incl_per_booking'];
		$this->reservationData['total_extra_price_tax_excl'] = $this->reservationData['total_extra_price_tax_excl_per_room'] + $this->reservationData['total_extra_price_tax_excl_per_booking'];

		$raTable = JTable::getInstance('ReservationAsset', 'SolidresTable');
		$raTable->load($this->reservationData['raid']);
		$this->reservationData['reservation_asset_name'] = $raTable->name;
		$this->reservationData['reservation_asset_id'] = $this->reservationData['raid'];
		$this->reservationData['currency_id'] = $this->app->getUserState($this->context.'.currency_id');
		$this->reservationData['currency_code'] = $this->app->getUserState($this->context.'.currency_code');
	}

	/**
	 * Save the reservation data
	 *
	 * @since  0.1.0
	 * 
	 * @return void
	 */
	public function save()
	{
		$model = $this->getModel();
		$resTable = JTable::getInstance('Reservation', 'SolidresTable');
		JPluginHelper::importPlugin('solidrespayment');

		// Get the data from user state and build a correct array that is ready to be stored
		$this->prepareSavingData();

		if(!$model->save($this->reservationData))
		{
			// Fail, turn back and correct
			$msg = JText::_(' SR_RESERVATION_SAVE_ERROR');
			$this->setRedirect($this->reservationData['returnurl'], $msg);
		}
		else
		{
			// Prepare some data for final layout
			$savedReservationId = $model->getState($model->getName().'.id');
			$resTable->load($savedReservationId);
			$this->app->setUserState($this->context.'.savedReservationId', $savedReservationId);
			$this->app->setUserState($this->context.'.code', $resTable->code);
			$this->app->setUserState($this->context.'.payment_method_id', $resTable->payment_method_id);
			$this->app->setUserState($this->context.'.customeremail', $this->reservationData['customer_email']);

			//
			if ($resTable->payment_method_id != 'paylater' && $resTable->payment_method_id != 'bankwire')
			{
				// Run payment plugin here
				$responses = $this->app->triggerEvent('OnSolidresPaymentNew', array(	$resTable ));
				$document = JFactory::getDocument();
				$viewType = $document->getType();
				$viewName = 'Reservation';
				$viewLayout = 'payment';

				$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				if (!empty($responses))
				{
					foreach ($responses as $response)
					{
						if ($response === false) continue;
						$view->paymentForm = $response;
					}
				}

				$view->display();
			}
			else
			{
				$link = JRoute::_('index.php?option=com_solidres&task=reservation.finalize&reservation_id='.$savedReservationId, false);
				$this->setRedirect($link);
			}
		}
	}

	/**
	 * Send email when reservation is completed
	 *
	 * @since  0.1.0
	 *
	 * @return boolean True if email sending completed successfully. False otherwise
	 */
	private function sendEmail()
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_solidres', JPATH_ADMINISTRATOR, null, 1);
		$subject = array();
		$body = array();
		$emailFormat = $this->solidresConfig->get('email_format', 'text/html');
		$dateFormat = $this->solidresConfig->get('date_format', 'd-m-Y');
		$config = JFactory::getConfig();
		$tzoffset = $config->get('offset');
		$timezone = new DateTimeZone($tzoffset);
		$messageTemplateExt = ($emailFormat == 'text/html' ? 'html' : 'txt') ;
		$savedReservationId = $this->app->getUserState($this->context.'.savedReservationId');
		$modelReservation = $this->getModel('Reservation', 'SolidresModel', array('ignore_request' => true));
		$savedReservationData = $modelReservation->getItem($savedReservationId);
		$solidresRoomType = SRFactory::get('solidres.roomtype.roomtype');
		$numberOfNights = (int)$solidresRoomType->calculateDateDiff($savedReservationData->checkin, $savedReservationData->checkout);

		$modelAsset = $this->getModel('ReservationAsset', 'SolidresModel', array('ignore_request' => true));
		$asset = $modelAsset->getItem($savedReservationData->reservation_asset_id);

		$hotelEmail = $asset->email;
		$hotelName = $asset->name;
		$customerEmail = $savedReservationData->customer_email;
		$hotelEmailList[] = $hotelEmail;
		// If User plugin is installed and enabled
		if (SR_PLUGIN_USER_ENABLED && !is_null($asset->partner_id))
		{
			$modelCustomer = $this->getModel('Customer', 'SolidresModel', array('ignore_request' => true));
			$customer = $modelCustomer->getItem($asset->partner_id);
			if (!empty($customer->email) && $customer->email != $hotelEmail )
			{
				$hotelEmailList[] = $customer->email;
			}
		}

		$subject[$customerEmail] = JText::_('SR_EMAIL_RESERVATION_COMPLETE');
		$subject[$hotelEmail] = JText::_('SR_EMAIL_NEW_RESERVATION_NOTIFICATION');

		$bankWireInstructions = array();

		if ($savedReservationData->payment_method_id == 'bankwire')
		{
			$solidresPaymentConfigData = new SRConfig(array('scope_id' => $savedReservationData->reservation_asset_id));
			$bankWireInstructions['account_name'] = $solidresPaymentConfigData->get('payments/bankwire/bankwire_accountname');
			$bankWireInstructions['account_details'] = $solidresPaymentConfigData->get('payments/bankwire/bankwire_accountdetails');
		}

		// We are free to choose between the inliner version and noninliner version
		// Inliner version is hard to maintain but it displays well in gmail (web).
		$reservationCompleteCustomerEmailTemplate = new JLayoutFile('emails.reservation_complete_customer_'.$messageTemplateExt.'_inliner');
		$reservationCompleteOwnerEmailTemplate = new JLayoutFile('emails.reservation_complete_owner_html_inliner');

		// Prepare some currency data to be showed
		$baseCurrency = new SRCurrency(0, $savedReservationData->currency_id);
		$subTotal = clone $baseCurrency;
		$subTotal->setValue($savedReservationData->total_price_tax_excl);
		$tax = clone $baseCurrency;
		$tax->setValue($savedReservationData->total_price_tax_incl - $savedReservationData->total_price_tax_excl);
		$totalExtraPriceTaxExcl = clone $baseCurrency;
		$totalExtraPriceTaxExcl->setValue($savedReservationData->total_extra_price_tax_excl);
		$extraTax = clone $baseCurrency;
		$extraTax->setValue($savedReservationData->total_extra_price_tax_incl - $savedReservationData->total_extra_price_tax_excl);
		$grandTotal = clone $baseCurrency;
		$grandTotal->setValue($savedReservationData->total_price_tax_incl + $savedReservationData->total_extra_price);
		$depositAmount = clone $baseCurrency;
		$depositAmount->setValue(isset($savedReservationData->deposit_amount) ? $savedReservationData->deposit_amount : 0);

		$displayData = array(
			'reservation' => $savedReservationData,
			'sub_total' => $subTotal->format(),
			'tax' => $tax->format(),
			'total_extra_price_tax_excl' => $totalExtraPriceTaxExcl->format(),
			'extra_tax' => $extraTax->format(),
			'grand_total' => $grandTotal->format(),
			'number_of_nights' => $numberOfNights,
			'deposit_amount' => $depositAmount->format(),
			'bankwire_instructions' => $bankWireInstructions,
			'asset' => $asset,
			'date_format' => $dateFormat,
			'timezone' => $timezone,
			'base_currency' => $baseCurrency
		);

		$body[$customerEmail] = $reservationCompleteCustomerEmailTemplate->render($displayData);

		$mail = SRFactory::get('solidres.mail.mail');
		$mail->setSender(array($hotelEmail, $hotelName));
		$mail->addRecipient($customerEmail);
		$mail->setSubject($subject[$customerEmail]);
		$mail->setBody($body[$customerEmail]);

		if(SR_PLUGIN_INVOICE_ENABLED)
		{
			// This is a workaroud for this Joomla's bug  https://github.com/joomla/joomla-cms/issues/3451
			// When it is fixed, update this logic
			if (file_exists(JPATH_BASE . '/templates/' . JFactory::getApplication()->getTemplate() . '/html/layouts/com_solidres/emails/reservation_complete_customer_pdf.php' ))
			{
				$reservationCompleteCustomerPdfTemplate = new JLayoutFile('emails.reservation_complete_customer_pdf');
			}
			else
			{
				$reservationCompleteCustomerPdfTemplate = new JLayoutFile(
					'emails.reservation_complete_customer_pdf',
					JPATH_ROOT . '/plugins/solidres/invoice/layouts'
				);
			}

			$pdf = NULL;
			$pdf = $reservationCompleteCustomerPdfTemplate->render($displayData);

			if($this->solidresConfig->get('enable_pdf_attachment',1) == 1)
			{
				$this->getPDFAttachment($mail, $pdf, $savedReservationId, $savedReservationData->code);
			}
		}

		$mail->IsHTML($emailFormat == 'text/html' ? true : false);

		if (!$mail->send())
		{
			return false;
		}

		// Send to the hotel owner
		$body[$hotelEmail] = $reservationCompleteOwnerEmailTemplate->render($displayData);

		$mail2 = SRFactory::get('solidres.mail.mail');
		$mail2->setSender(array($hotelEmail, $hotelName));
		$mail2->addRecipient($hotelEmailList);
		$mail2->setSubject($subject[$hotelEmail]);
		$mail2->setBody($body[$hotelEmail]);
		$mail2->IsHTML($emailFormat == 'text/html' ? true : false);

		if (!$mail2->send())
		{
			return false;
		}

		return true;
	}

	/**
	 * Finalize the reservation process
	 *
	 * @since  0.3.0
	 *
	 * @return void
	 */
	public function finalize()
	{
		$savedReservationId = $this->app->getUserState($this->context.'.savedReservationId');
		$reservationId = $this->input->get('reservation_id', 0, 'int');
		$activeItemId = $this->app->getUserState($this->context . '.activeItemId');

		if ($savedReservationId == $reservationId)
		{
			$msg = $this->sendEmail();

			if (!is_string($msg))
			{
				$msg = NULL;
			}

			// Done, we do not need these data, wipe them !!!
			$this->app->setUserState($this->context . '.room', NULL);
			$this->app->setUserState($this->context . '.extra', NULL);
			$this->app->setUserState($this->context . '.guest', NULL);
			$this->app->setUserState($this->context . '.discount', NULL);
			$this->app->setUserState($this->context . '.deposit', NULL);
			$this->app->setUserState($this->context . '.coupon', NULL);
			$this->app->setUserState($this->context . '.token', NULL);
			$this->app->setUserState($this->context . '.cost', NULL);
			$this->app->setUserState($this->context . '.checkin', NULL);
			$this->app->setUserState($this->context . '.checkout', NULL);
			$this->app->setUserState($this->context . '.room_type_prices_mapping', NULL);
			$this->app->setUserState($this->context . '.selected_room_types', NULL);
			$this->app->setUserState($this->context . '.reservation_asset_id', NULL);
			$this->app->setUserState($this->context . '.current_selected_tariffs', NULL);

			$link = JRoute::_('index.php?option=com_solidres&view=reservation&layout=final&Itemid='.$activeItemId, false);
			$this->setRedirect($link, $msg);
		}
	}

	/**
	 * Decide which will be the next screen
	 *
	 * @return void
	 */
	public function progress()
	{
		$next	= $this->input->get('next_step', '', 'string');
		if (!empty($next))
		{
			switch($next)
			{
				case 'guestinfo':
					$this->getHtmlGuestInfo();
					break;
				case 'confirmation':
					$this->getHtmlConfirmation();
					break;
				default:
					$response = array('status' => 1, 'message' => '', 'next' => '');
					echo json_encode($response);die(1);
					break;
			}
		}
	}

	/**
	 * Return html to display guest info form in one-page reservation, data is retrieved from user session
	 *
	 * @return string $html The HTML output
	 */
	public function getHtmlGuestInfo()
	{
		$this->countries = SolidresHelper::getCountryOptions();
		$this->reservationDetails = $this->app->getUserState($this->context);
		$showTaxIncl = $this->solidresConfig->get('show_price_with_tax', 0);
		$customerTitles = array(
			'' => '',
			JText::_("SR_CUSTOMER_TITLE_MR") => JText::_("SR_CUSTOMER_TITLE_MR"),
			JText::_("SR_CUSTOMER_TITLE_MRS") => JText::_("SR_CUSTOMER_TITLE_MRS"),
			JText::_("SR_CUSTOMER_TITLE_MS") => JText::_("SR_CUSTOMER_TITLE_MS")
		);
		$raId = $this->reservationDetails->room['raid'];

		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables', 'SolidresTable');
		JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models', 'SolidresModel');
		$modelExtras = JModelLegacy::getInstance('Extras', 'SolidresModel', array('ignore_request' => true));
		$modelExtras->setState('filter.reservation_asset_id', $raId);
		$modelExtras->setState('filter.charge_type', 1); // Only get extra item with charge type = Per Booking
		$modelExtras->setState('filter.state', 1);
		$modelExtras->setState('filter.show_price_with_tax', $showTaxIncl);
		$extras = $modelExtras->getItems();

		// Try to get the customer information if he/she logged in
		$selectedCountryId = 0;
		if (SR_PLUGIN_USER_ENABLED)
		{
			$customerTable = JTable::getInstance('Customer', 'SolidresTable');
			$user = JFactory::getUser();
			$customerTable->load(array('user_id' => $user->get('id')));
			$guestFields = array(
				'customer_firstname',
				'customer_middlename',
				'customer_lastname',
				'customer_vat_number',
				'customer_company',
				'customer_phonenumber',
				'customer_address1',
				'customer_address2',
				'customer_city',
				'customer_zipcode',
				'customer_country_id',
				'customer_geo_state_id'
			);

			if (!empty($customerTable->id))
			{
				foreach ($guestFields as $guestField)
				{
					if (!isset($this->reservationDetails->guest[$guestField]))
					{
						$this->reservationDetails->guest[$guestField] = $customerTable->{substr($guestField, 9)};
					}
				}

				$this->reservationDetails->guest["customer_email"] = !isset($this->reservationDetails->guest["customer_email"]) ? $user->get('email') : $this->reservationDetails->guest["customer_email"] ;
			}

			$selectedCountryId = isset($this->reservationDetails->guest["customer_country_id"]) ? $this->reservationDetails->guest["customer_country_id"] : 0;
		}

		$options = array();
		$options[] = JHTML::_('select.option', NULL, JText::_('SR_SELECT') );
		$this->geoStates = $selectedCountryId > 0 ? SolidresHelper::getGeoStateOptions($selectedCountryId) : $options;

		$form = new JLayoutFile('asset.guestform');
		$displayData = array(
			'customerTitles' => $customerTitles,
			'reservationDetails' => $this->reservationDetails,
			'extras' => $extras,
			'assetId' => $raId,
			'countries' => $this->countries,
			'geoStates' => $this->geoStates,
			'solidresPaymentPlugins' => $this->solidresPaymentPlugins
		);

		echo $form->render($displayData);
		$this->app->close();
	}

	/**
	 * Return html to display confirmation form in one-page reservation, data is retrieved from user session
	 *
	 * @return string $html The HTML output
	 */
	public function getHtmlConfirmation()
	{
		JLoader::register('ContentHelperRoute', JPATH_SITE.'/components/com_content/helpers/route.php');
		// TODO replace this manual call with autoloading later
		JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');

		$this->reservationDetails = $this->app->getUserState($this->context);

		$solidresConfig = JComponentHelper::getParams('com_solidres');
		$model = $this->getModel();
		$modelName = $model->getName();
		$checkin = $this->reservationDetails->checkin;
		$checkout = $this->reservationDetails->checkout;
		$raId = $this->reservationDetails->room['raid'];
		$roomTypeObj = SRFactory::get('solidres.roomtype.roomtype');
		$currency = new SRCurrency(0, $this->reservationDetails->currency_id);
		$totalRoomTypeExtraCostTaxIncl = $this->reservationDetails->room['total_extra_price_tax_incl_per_room'] + $this->reservationDetails->guest['total_extra_price_tax_incl_per_booking'];
		$totalRoomTypeExtraCostTaxExcl = $this->reservationDetails->room['total_extra_price_tax_excl_per_room'] + $this->reservationDetails->guest['total_extra_price_tax_excl_per_booking'];
		$numberOfNights = $roomTypeObj->calculateDateDiff($checkin, $checkout);
		$solidresUtilities = SRFactory::get('solidres.utilities.utilities');
		$dateFormat = $solidresConfig->get('date_format', 'd-m-Y');
		$jsDateFormat = $solidresUtilities::convertDateFormatPattern($dateFormat);
		$tzoffset = JFactory::getConfig()->get('offset');
		$timezone = new DateTimeZone($tzoffset);

		$model->setState($modelName.'.roomTypes', $this->reservationDetails->room['room_types']);
		$model->setState($modelName.'.checkin',  $checkin);
		$model->setState($modelName.'.checkout', $checkout);
		$model->setState($modelName.'.reservationAssetId',  $raId);

		$task = 'reservation.save';

		// Query for room types data and their associated costs
		$roomTypes = $model->getRoomType();

		// Rebind the session data because it has been changed in the previous line
		$this->reservationDetails = $this->app->getUserState($this->context);
		$cost = $this->app->getUserState($this->context.'.cost');

		$form = new JLayoutFile('asset.confirmationform');
		$displayData = array(
			'roomTypes' => $roomTypes,
			'reservationDetails' => $this->reservationDetails,
			'totalRoomTypeExtraCostTaxIncl' => $totalRoomTypeExtraCostTaxIncl,
			'totalRoomTypeExtraCostTaxExcl' => $totalRoomTypeExtraCostTaxExcl,
			'task' => $task,
			'assetId' => $raId,
			'cost' => $cost,
			'numberOfNights' => $numberOfNights,
			'currency' => $currency,
			'context' => $this->context,
			'dateFormat' => $dateFormat, // default format d-m-y
			'jsDateFormat' => $jsDateFormat,
			'timezone' => $timezone
		);

		echo $form->render($displayData);
		$this->app->close();
	}

	public function paymentcallback()
	{
		JPluginHelper::importPlugin('solidrespayment');
		$callbackData = $this->input->getArray($_REQUEST);

		$responses = $this->app->triggerEvent('OnSolidresPaymentCallback', array(
			$callbackData['payment_method_id'],
			$callbackData
		));
	}

	/**
	 * Create PDF attachment.
	 * @param $mail		mail object.
	 * @param $reid		reservation id.
	 * @param $reCode	reservation code.
	 */
	private function getPDFAttachment($mail, $content, $reid, $reCode)
	{
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('solidres');
		$results = $dispatcher->trigger('onSolidresReservationEmail', array($content, $reid));

		if($results)
		{
			$mail->addAttachment($results[0], 'voucher_'.$reCode.'.pdf', 'base64', 'application/pdf' );
		}
	}
}