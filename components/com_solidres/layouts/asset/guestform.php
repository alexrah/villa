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

$selectedCustomerTitle = !empty($displayData['reservationDetails']->guest["customer_title"]) ? $displayData['reservationDetails']->guest["customer_title"] : '';
$user = JFactory::getUser();

?>
<form enctype="multipart/form-data"
	  id="sr-reservation-form-guest"
	  class="sr-reservation-form form-stacked sr-validate"
	  action="<?php echo JRoute::_("index.php?option=com_solidres&task=reservation.process&step=guestinfo&format=json") ?>"
	  method="POST">

<div class="row-fluid button-row button-row-top">
	<div class="span8">
		<div class="inner">
			<p><?php echo JText::_('SR_GUEST_INFO_STEP_NOTICE') ?></p>
		</div>
	</div>
	<div class="span4">
		<div class="inner">
			<div class="btn-group">
				<button data-step="guestinfo" type="submit" class="btn btn-success">
					<i class="icon-arrow-right uk-icon-arrow-right fa-arrow-right"></i> <?php echo JText::_('SR_NEXT') ?>
				</button>
				<button type="button" class="btn reservation-navigate-back" data-step="guestinfo" data-prevstep="room">
					<i class="icon-arrow-left uk-icon-arrow-left fa-arrow-left"></i> <?php echo JText::_('SR_BACK') ?>
				</button>
			</div>
		</div>
	</div>
</div>

<div class="row-fluid">
	<div class="span12">
		<div class="inner">
			<h3><?php echo JText::_('SR_GUEST_INFORMATION') ?></h3>
		</div>
	</div>
</div>

<div class="row-fluid">
	<div class="span6">
		<div class="inner">

			<fieldset>
				<label for="firstname">
					<?php echo JText::_("SR_CUSTOMER_TITLE") ?>
				</label>
				<?php
				echo JHtml::_("select.genericlist", $displayData['customerTitles'], "jform[customer_title]", array("class" => "span12", 'required'), "value", "text", $selectedCustomerTitle, "")
				?>

				<label for="firstname">
					<?php echo JText::_("SR_FIRSTNAME") ?>
				</label>
				<input id="firstname"
					   required
					   name="jform[customer_firstname]"
					   type="text"
					   class="span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_firstname"]) ? $displayData['reservationDetails']->guest["customer_firstname"] : "") ?>"/>

				<label for="middlename">
					<?php echo JText::_("SR_MIDDLENAME") ?>
				</label>
				<input id="middlename"
					   name="jform[customer_middlename]"
					   type="text"
					   class="span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_middlename"]) ? $displayData['reservationDetails']->guest["customer_middlename"] : "") ?>"/>

				<label for="lastname">
					<?php echo JText::_("SR_LASTNAME") ?>
				</label>
				<input id="lastname"
					   required
					   name="jform[customer_lastname]"
					   type="text"
					   class="span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_lastname"]) ? $displayData['reservationDetails']->guest["customer_lastname"] : "") ?>"/>

				<label for="email">
					<?php echo JText::_("SR_EMAIL") ?>
				</label>
				<input id="email"
					   required
					   name="jform[customer_email]"
					   type="text"
					   class="span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_email"]) ? $displayData['reservationDetails']->guest["customer_email"] : "") ?>"/>

				<label for="phonenumber">
					<?php echo JText::_("SR_PHONENUMBER") ?>
				</label>
				<input id="phonenumber"
					   required
					   name="jform[customer_phonenumber]"
					   type="text"
					   class="span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_phonenumber"]) ? $displayData['reservationDetails']->guest["customer_phonenumber"] : "") ?>"/>

				<label for="company">
					<?php echo JText::_("SR_COMPANY") ?>
				</label>
				<input id="company"
					   name="jform[customer_company]"
					   type="text"
					   class="span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_company"]) ? $displayData['reservationDetails']->guest["customer_company"] : "") ?>"/>

				<label for="address1">
					<?php echo JText::_("SR_ADDRESS_1") ?>
				</label>
				<input id="address1"
					   required
					   name="jform[customer_address1]"
					   type="text"
					   class="span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_address1"]) ? $displayData['reservationDetails']->guest["customer_address1"] : "") ?>"/>

				<label for="address2">
					<?php echo JText::_("SR_ADDRESS_2") ?>
				</label>
				<input id="address2"
					   name="jform[customer_address2]"
					   type="text"
					   class="span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_address2"]) ? $displayData['reservationDetails']->guest["customer_address2"] : "") ?>"/>

				<label for="address_2">
					<?php echo JText::_("SR_VAT_NUMBER") ?>
				</label>
				<input id="address_2"
					   name="jform[customer_vat_number]"
					   type="text"
					   class="span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_vat_number"]) ? $displayData['reservationDetails']->guest["customer_vat_number"] : "") ?>"/>
			</fieldset>
		</div>
	</div>

	<div class="span6">
		<div class="inner">
			<fieldset>
				<label for="city"><?php echo JText::_("SR_CITY") ?></label>
				<input id="city"
					   required
					   name="jform[customer_city]"
					   type="text"
					   class="span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_city"]) ? $displayData['reservationDetails']->guest["customer_city"] : "") ?>"/>

				<label for="zip"><?php echo JText::_("SR_ZIP") ?></label>
				<input id="zip"
					   name="jform[customer_zipcode]"
					   type="text"
					   class="span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_zipcode"]) ? $displayData['reservationDetails']->guest["customer_zipcode"] : "") ?>"/>

				<label for="jform[country_id]"><?php echo JText::_("SR_COUNTRY") ?></label>

				<?php
				$selectedCountryId = isset($displayData['reservationDetails']->guest["customer_country_id"]) ? $displayData['reservationDetails']->guest["customer_country_id"] : 0;
				echo JHtml::_("select.genericlist", $displayData['countries'], "jform[customer_country_id]", array("class" => "country_select span12", 'required' => 'required'), "value", "text", $selectedCountryId, "country");
				?>
				<label for="jform[customer_geo_state_id]"><?php echo JText::_("SR_STATE") ?></label>
				<?php
				$selectedGeoStateId = isset($displayData['reservationDetails']->guest["customer_geo_state_id"]) ? $displayData['reservationDetails']->guest["customer_geo_state_id"] : 0;

				echo JHtml::_("select.genericlist", $displayData['geoStates'], "jform[customer_geo_state_id]", array("class" => "state_select span12"), "value", "text", $selectedGeoStateId, "state");
				?>
				<label for="note"><?php echo JText::_("SR_NOTE") ?></label>
				<textarea id="note" name="jform[note]" rows="10" cols="30"
						  class="span12"><?php echo (isset($displayData['reservationDetails']->guest["note"]) ? $displayData['reservationDetails']->guest["note"] : "") ?></textarea>

				<p class="help-block"><?php echo JText::_("SR_RESERVATION_NOTE") ?></p>

				<?php if (SR_PLUGIN_USER_ENABLED && $user->get('id') <= 0) : ?>
					<label class="checkbox">
						<input id="register_an_account_form" type="checkbox"> <?php echo JText::_('SR_REGISTER_WITH_US_TEXT') ?>
					</label>
					<div class="register_an_account_form" style="display: none">
						<label for="username">
							<?php echo JText::_("SR_USERNAME") ?>
						</label>
						<input id="username"
							   name="jform[customer_username]"
							   type="text"
							   class="span12"
							   value=""/>

						<label for="password">
							<?php echo JText::_("SR_PASSWORD") ?>
						</label>
						<input id="password"
							   name="jform[customer_password]"
							   type="password"
							   class="span12"
							   value=""
							   autocomplete="off"
							/>
					</div>


				<?php endif ?>
			</fieldset>
		</div>
	</div>
</div>

<?php
// Show Per Booking Extras
if (count($displayData['extras'])) :
?>
<div class="row-fluid">
	<div class="span12">
		<div class="inner">
			<h3><?php echo JText::_('SR_ENHANCE_YOUR_STAY') ?></h3>
		</div>
	</div>
</div>

<div class="row-fluid">
	<div class="span12">
		<div class="inner">
			<ul class="unstyled">
				<?php
				foreach ($displayData['extras'] as $extra) :
					$extraInputCommonName = 'jform[extras][' . $extra->id . ']';
					$checked = '';
					$disabledCheckbox = '';
					$disabledSelect = 'disabled="disabled"';
					$alreadySelected = false;
					if (isset($displayData['reservationDetails']->guest['extras'])) :
						$alreadySelected = array_key_exists($extra->id, (array)$displayData['reservationDetails']->guest['extras']);
					endif;

					if ($extra->mandatory == 1 || $alreadySelected) :
						$checked = 'checked="checked"';
					endif;

					if ($extra->mandatory == 1) :
						$disabledCheckbox = 'disabled="disabled"';
						$disabledSelect = '';
					endif;

					if ($alreadySelected && $extra->mandatory == 0) :
						$disabledSelect = '';
					endif;
?>
								<li>
									<input <?php echo $checked ?> <?php echo $disabledCheckbox ?> type="checkbox" data-target="guest_extra_<?php echo $extra->id ?>" />

<?php
					if ($extra->mandatory == 1) :
?>
									<input type="hidden" name="<?php echo $extraInputCommonName ?>[quantity]" value="1" />
<?php
					endif;
?>
									<select class="span3 guest_extra_<?php echo $extra->id ?>"
									 		name="<?php echo $extraInputCommonName ?>[quantity]"
											<?php echo $disabledSelect ?>>
<?php
					for ($quantitySelection = 1; $quantitySelection <= $extra->max_quantity; $quantitySelection++) :
						$checked = '';
						if (isset($displayData['reservationDetails']->guest['extras'][$extra->id]['quantity'])) :
							$checked = ($displayData['reservationDetails']->guest['extras'][$extra->id]['quantity'] == $quantitySelection) ? 'selected="selected"' : '';
						endif;
?>
						<option <?php echo $checked ?> value="<?php echo $quantitySelection ?>"><?php echo $quantitySelection ?></option>
<?php
					endfor;
?>
									</select>
									<span data-content="<?php echo $extra->description ?>" class="extra_desc_tips" title="<?php echo $extra->name ?>">
										<?php echo $extra->name . ' (' . $extra->currency->format() . ')' ?>
										<i  class="icon-help uk-icon-question-circle fa-question-circle"></i>
									</span>
								</li>
<?php
				endforeach;
				endif;
				?>
			</ul>
		</div>
	</div>
</div>
<?php
// Show available payment methods
$solidresPaymentConfigData = new SRConfig(array('scope_id' => $displayData['assetId']));
?>
<div class="row-fluid">
	<div class="span12">
		<div class="inner">
			<h3><?php echo JText::_('SR_PAYMENT_INFO') ?></h3>
		</div>
	</div>
</div>

<div class="row-fluid">
	<div class="span12">
		<div class="inner">
			<ul class="unstyled payment_method_list">
			<?php
			$solidresUtilities = SRFactory::get('solidres.utilities.utilities');

			if ($solidresPaymentConfigData->get('payments/paylater/paylater_enabled')) :
				$checkPayLater = '';
				if (isset($displayData['reservationDetails']->guest["payment_method_id"])) :
					if ($displayData['reservationDetails']->guest["payment_method_id"] == "paylater") :
						$checkPayLater = "checked";
					endif;
				else :
					if ($solidresPaymentConfigData->get('payments/paylater/paylater_is_default') == 1):
						$checkPayLater = "checked";
					endif;
				endif;
				?>
				<li>
					<input id="payment_method_paylater" type="radio" class="payment_method_radio"
						   name="jform[payment_method_id]"
						   value="paylater" <?php echo $checkPayLater ?>/>
					<span class="popover_payment_methods"
						  data-content="<?php echo $solidresUtilities::translateText($solidresPaymentConfigData->get('payments/paylater/paylater_frontend_message')) ?>"
						  data-title="<?php echo JText::_("SR_PAYMENT_METHOD_PAYLATER") ?>">
						<?php echo JText::_("SR_PAYMENT_METHOD_PAYLATER") ?>

						<i class="icon-help icon-question-sign uk-icon-question-circle fa-question-cirlce "></i>
					</span>
				</li>
			<?php
			endif;

			if ($solidresPaymentConfigData->get('payments/bankwire/bankwire_enabled')) :
				$checkBankWire = '';
				if (isset($displayData['reservationDetails']->guest["payment_method_id"])) :
					if ($displayData['reservationDetails']->guest["payment_method_id"] == "bankwire") :
						$checkBankWire = "checked";
					endif;
				else :
					if ($solidresPaymentConfigData->get('payments/bankwire/bankwire_is_default') == 1):
						$checkBankWire = "checked";
					endif;
				endif;
				?>
				<li>
					<input id="payment_method_bankwire" class="payment_method_radio" type="radio"
						   name="jform[payment_method_id]"
						   value="bankwire" <?php echo $checkBankWire ?> />
					<span class="popover_payment_methods"
						  data-content="<?php echo $solidresUtilities::translateText($solidresPaymentConfigData->get('payments/bankwire/bankwire_frontend_message')) ?>"
						  data-title="<?php echo JText::_("SR_PAYMENT_METHOD_BANKWIRE") ?>">
						<?php echo JText::_("SR_PAYMENT_METHOD_BANKWIRE") ?>
						<i class="icon-help icon-question-sign uk-icon-question-circle fa-question-cirlce"></i>
					</span>
				</li>
			<?php
			endif;

			// For extra payment methods provide via plugins
			foreach ($displayData['solidresPaymentPlugins'] as $paymentPlugin) :
				$paymentPluginId = $paymentPlugin->element;

				if ($solidresPaymentConfigData->get('payments/' . $paymentPluginId . '/' . $paymentPluginId . '_enabled')) :
					$checked = '';
					if (isset($displayData['reservationDetails']->guest["payment_method_id"])) :
						if ($displayData['reservationDetails']->guest["payment_method_id"] == $paymentPluginId) :
							$checked = "checked";
						endif;
					else :
						if ($solidresPaymentConfigData->get("payments/$paymentPluginId/{$paymentPluginId}_is_default") == 1):
							$checked = "checked";
						endif;
					endif;

					// Load custom payment plugin field template if it is available, otherwise just render it normally
					$fieldTemplatePath = JPATH_PLUGINS . '/solidrespayment/' . $paymentPluginId . '/form/field.php';
					if (file_exists($fieldTemplatePath)) :
						@ob_start();
						include $fieldTemplatePath;
						echo @ob_get_clean();
					else :
						?>
						<li>
							<input id="payment_method_<?php echo $paymentPluginId ?>"
								   type="radio"
								   name="jform[payment_method_id]"
								   value="<?php echo $paymentPluginId ?>"
								   class="payment_method_radio"
								<?php echo $checked ?>
								/>
							<span class="popover_payment_methods"
								  data-content="<?php echo $solidresUtilities::translateText($solidresPaymentConfigData->get('payments/' . $paymentPluginId . '/' . $paymentPluginId . '_frontend_message')) ?>"
								  data-title="<?php echo JText::_("SR_PAYMENT_METHOD_" . $paymentPluginId) ?>">
								<?php echo JText::_("SR_PAYMENT_METHOD_" . $paymentPluginId) ?>
								<i class="icon-help icon-question-sign uk-icon-question-circle fa-question-cirlce"></i>
							</span>
						</li>
					<?php
					endif;

				endif;
			endforeach;
			?>
			</ul>
		</div>
	</div>
</div>

<div class="row-fluid button-row button-row-bottom">
	<div class="span8">
		<div class="inner">
			<p><?php echo JText::_('SR_GUEST_INFO_STEP_NOTICE') ?></p>
		</div>
	</div>
	<div class="span4">
		<div class="inner">
			<div class="btn-group">
				<button data-step="guestinfo" type="submit" class="btn btn-success">
					<i class="icon-arrow-right uk-icon-arrow-right fa-arrow-right"></i> <?php echo JText::_('SR_NEXT') ?>
				</button>
				<button type="button" class="btn reservation-navigate-back" data-step="guestinfo" data-prevstep="room">
					<i class="icon-arrow-left uk-icon-arrow-left fa-arrow-left"></i> <?php echo JText::_('SR_BACK') ?>
				</button>
			</div>
		</div>
	</div>
</div>

<?php echo JHtml::_("form.token") ?>
<input type="hidden" name="jform[next_step]" value="confirmation"/>
</form>