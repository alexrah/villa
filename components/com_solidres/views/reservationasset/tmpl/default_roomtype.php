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

$dayMapping = array('0' => JText::_('SUN'), '1' => JText::_('MON'), '2' => JText::_('TUE'), '3' => JText::_('WED'), '4' => JText::_('THU'), '5' => JText::_('FRI'), '6' => JText::_('SAT') );
$tariffBreakDownNetOrGross = $this->showTaxIncl == 1 ? 'net' : 'gross';
$isFresh = !isset($this->checkin) && !isset($this->checkout);

if (!isset($this->item->params['enable_coupon'])) :
	$this->item->params['enable_coupon'] = 0;
endif;

if ( $this->item->params['enable_coupon'] == 1 ) :
	if (!$isFresh) :
?>
	<div class="coupon">
		<input type="text" name="coupon_code" class="span12" id="coupon_code" placeholder="<?php echo JText::_('SR_COUPON_ENTER') ?>"/>
		<?php if (isset($this->coupon)) : ?>
			<?php echo JText::_('SR_APPLIED_COUPON') ?>
			<span class="label label-success">
			<?php echo $this->coupon['coupon_name']	?>
		</span>&nbsp;
			<a id="sr-remove-coupon" href="javascript:void(0)" data-couponid="<?php echo $this->coupon['coupon_id'] ?>">
				<?php echo JText::_('SR_REMOVE') ?>
			</a>
		<?php endif ?>
	</div>
<?php
	endif;
endif;
?>
<a name="form"></a>
<div class="wizard">
	<ul class="steps">
		<li data-target="#step1" class="active reservation-tab reservation-tab-room span4"><span class="badge badge-info">1</span><?php echo JText::_('SR_STEP_ROOM_AND_RATE') ?><span class="chevron"></span></li>
		<li data-target="#step2" class="reservation-tab reservation-tab-guestinfo span4"><span class="badge">2</span><?php echo JText::_('SR_STEP_GUEST_INFO_AND_PAYMENT') ?><span class="chevron"></span></li>
		<li data-target="#step3" class="reservation-tab reservation-tab-confirmation span4"><span class="badge">3</span><?php echo JText::_('SR_STEP_CONFIRMATION') ?><!--<span class="chevron"></span>--></li>
	</ul>
</div>

<div class="step-content">
	<div class="step-pane active" id="step1">
	<!-- Tab 1 -->
	<div class="reservation-single-step-holder room">
	<?php echo $this->loadTemplate('searchinfo'); ?>
	<form enctype="multipart/form-data"
		  id="sr-reservation-form-room"
		  class="sr-reservation-form"
		  action="index.php?option=com_solidres&task=reservation.process&step=room&format=json"
		  method="POST">
	<?php if(count($this->item->roomTypes) > 0) : ?>
		<?php if (!$isFresh) : ?>
			<div class="row-fluid button-row button-row-top">
				<div class="span9">
					<div class="inner">
						<p><?php echo JText::_('SR_ROOMINFO_STEP_NOTICE_MESSAGE') ?></p>
					</div>
				</div>
				<div class="span3">
					<div class="inner">
						<div class="btn-group">
							<button data-step="room" type="submit" class="btn btn-success">
								<i class="icon-arrow-right uk-icon-arrow-right fa-arrow-right"></i> <?php echo JText::_('SR_NEXT') ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		<?php endif ?>

		<?php
		$count = 1;
		foreach($this->item->roomTypes as $roomType ) :
			if (isset($roomType->defaultTariffBreakDown)) :
				$defaultTariffBreakDownHtml = '<table class=\"tariff-break-down\">';
				foreach ($roomType->defaultTariffBreakDown as $key => $breakDownDetails ) :
					if ($key % 7 == 0 && $key == 0) :
						$defaultTariffBreakDownHtml .= '<tr>';
					elseif ($key % 7 == 0) :
						$defaultTariffBreakDownHtml .= '</tr><tr>';
					endif;
					$tmpKey = key($breakDownDetails);
					$defaultTariffBreakDownHtml .= '<td><p>'.$dayMapping[$tmpKey].'</p><span class=\"'.$tariffBreakDownNetOrGross.'\">'.$breakDownDetails[$tmpKey][$tariffBreakDownNetOrGross]->format().'</span>';
				endforeach;
				$defaultTariffBreakDownHtml .= '</tr></table>';

				$this->document->addScriptDeclaration('
					Solidres.jQuery(function($){
						$(".default_tariff_break_down_'.$roomType->id.'").popover({
							html: true,
							content: "'.$defaultTariffBreakDownHtml.'",
							title: "'.JText::_('SR_TARIFF_BREAK_DOWN').'",
							placement: "bottom",
							trigger: "click"
						});
					});
				');
			endif;

			if (isset($roomType->complexTariffBreakDown)) :
				$complexTariffBreakDownHtml = '<table class=\"tariff-break-down\">';
				foreach ($roomType->complexTariffBreakDown as $key => $breakDownDetails ) :
					if ($key % 7 == 0 && $key == 0) :
						$complexTariffBreakDownHtml .= '<tr>';
					elseif ($key % 7 == 0) :
						$complexTariffBreakDownHtml .= '</tr><tr>';
					endif;
					$tmpKey = key($breakDownDetails);
					$complexTariffBreakDownHtml .= '<td><p>'.$dayMapping[$tmpKey].'</p><span class=\"'.$tariffBreakDownNetOrGross.'\">'.$breakDownDetails[$tmpKey][$tariffBreakDownNetOrGross]->format().'</span>';
				endforeach;

				$complexTariffBreakDownHtml .= '</tr></table>';
				$this->document->addScriptDeclaration('
					Solidres.jQuery(function($){
						$(".complex_tariff_break_down_'.$roomType->id.'").popover({
							html: true,
							content: "'.$complexTariffBreakDownHtml.'",
							title: "'.JText::_('SR_TARIFF_BREAK_DOWN').'",
							placement: "bottom",
							trigger: "click"
						});
					});
				');
			endif;

			$this->document->addScriptDeclaration('
				Solidres.jQuery(function($){
					$(".sr-photo-'.$roomType->id.'").colorbox({rel:"sr-photo-'.$roomType->id.'", transition:"fade"});
					$(".carousel").carousel();
				});
			');

			$rowCSSClass = ($count % 2) ? ' even' : ' odd';
			$rowCSSClass .= $roomType->featured == 1 ? ' featured' : '';
			$currentSelectedRoomNumberPerTariff = array();

			?>
			<div class="row-fluid <?php echo $rowCSSClass ?>" id="room_type_row_<?php echo $roomType->id ?>">
				<div class="span12">
					<div class="row-fluid">
						<div class="span12">
							<div class="inner">
								<h4 class="roomtype_name" id="room_type_details_handler_<?php echo $roomType->id ?>">
									<span class="label label-info">
										<?php echo (int)$roomType->occupancy_adult + (int)$roomType->occupancy_child ?>
										<i class="icon-user uk-icon-user fa-user"></i>
									</span>

									<?php echo $roomType->name; ?>
									<?php if ($roomType->featured == 1) : ?>
										<span class="label label-success"><?php echo JText::_('SR_FEATURED_ROOM_TYPE') ?></span>
									<?php endif ?>
								</h4>
							</div>
						</div>
					</div>

					<div class="row-fluid">
						<div class="span4">
							<div class="inner">
								<?php
								if( !empty($roomType->media) ) :
									echo '<div id="carousel'.$roomType->id.'" class="carousel slide">';
									echo '<div class="carousel-inner">';
									$countMedia = 0;
									$active = '';
									foreach ($roomType->media as $media) :
										$active = ($countMedia == 0) ? 'active' : '';
								?>
										<div class="item <?php echo $active ?>">
											<a class="room_type_details sr-photo-<?php echo $roomType->id ?>" href="<?php echo SRURI_MEDIA.'/assets/images/system/'.$media->value; ?>">
												<img src="<?php echo SRURI_MEDIA.'/assets/images/system/thumbnails/1/'.$media->value; ?>"
												 	 alt="<?php echo $media->name ?>"/>
											</a>
										</div>
								<?php
										$countMedia ++;
									endforeach;
									echo '</div>';
									echo '<a class="carousel-control left" href="#carousel'.$roomType->id.'" data-slide="prev">&lsaquo;</a>';
  									echo '<a class="carousel-control right" href="#carousel'.$roomType->id.'" data-slide="next">&rsaquo;</a>';
									echo '</div>';
								endif;
								?>
							</div>
						</div>

						<div class="span8">
							<div class="inner">
								<div class="roomtype_desc">
									<?php echo $roomType->description ?>
								</div>
								<?php if ( isset($roomType->totalAvailableRoom) ) : ?>
								<p>
									<span class="num_rooms_available_msg" id="num_rooms_available_msg_<?php echo $roomType->id ?>"
										  data-original-text="<?php echo JText::plural('SR_WE_HAVE_X_ROOM_LEFT', $roomType->totalAvailableRoom) ?>">
										<?php echo JText::plural('SR_WE_HAVE_X_ROOM_LEFT', $roomType->totalAvailableRoom) ?>
									</span>
								</p>
								<?php endif; ?>
								<div class="btn-group">
									<button type="button" class="btn toggle_more_desc" data-target="<?php echo $roomType->id ?>">
										<i class="icon-eye-open uk-icon-eye fa-eye"></i>
										<?php echo JText::_('SR_SHOW_MORE_INFO') ?>
									</button>
									<?php if ($this->config->get('availability_calendar_enable', 1)) : ?>
									<button type="button" data-roomtypeid="<?php echo $roomType->id ?>" class="btn load-calendar">
										<i class="icon-calendar uk-icon-calendar fa-calendar"></i> <?php echo JText::_('SR_AVAILABILITY_CALENDAR_VIEW') ?>
									</button>
									<?php endif ?>
								</div>

								<div class="unstyled more_desc" id="more_desc_<?php echo $roomType->id ?>" style="display: none">
									<?php
									if (!empty($roomType->roomtype_custom_fields['room_facilities'])) :
										echo '<p><strong>'. JText::_('SR_ROOM_FACILITIES') .':</strong> '.  $roomType->roomtype_custom_fields['room_facilities'] .'</p>';
									endif;

									if (!empty($roomType->roomtype_custom_fields['room_size'])) :
										echo '<p><strong>'. JText::_('SR_ROOM_SIZE') .':</strong> '.  $roomType->roomtype_custom_fields['room_size'] .'</p>';
									endif;

									if (!empty($roomType->roomtype_custom_fields['bed_size'])) :
										echo '<p><strong>'. JText::_('SR_BED_SIZE') .':</strong> '.  $roomType->roomtype_custom_fields['bed_size'] .'</p>';
									endif;

									if (!empty($roomType->roomtype_custom_fields['taxes'])) :
										echo '<p><strong>'. JText::_('SR_TAXES') .':</strong> '.  $roomType->roomtype_custom_fields['taxes'] .'</p>';
									endif;

									if (!empty($roomType->roomtype_custom_fields['prepayment'])) :
										echo '<p><strong>'. JText::_('SR_PREPAYMENT') .':</strong> '.  $roomType->roomtype_custom_fields['prepayment'] .'</p>';
									endif;
									?>
								</div>
							</div>
						</div> <!-- end of span8 -->
					</div> <!-- end of row-fluid -->

					<?php if ($this->config->get('availability_calendar_enable', 1)) : ?>
					<div class="row-fluid">
						<div class="span12 availability-calendar" id="availability-calendar-<?php echo $roomType->id ?>" style="display: none">
						</div>
					</div>
					<?php endif ?>


					<div class="row-fluid">
						<div class="span12">
							<div class="inner">
								<?php
								$hasMatchedTariffs = true;
								if (SR_PLUGIN_COMPLEXTARIFF_ENABLED &&
									count($roomType->tariffs) == 0 &&
									(!$isFresh)
								) :
									$hasMatchedTariffs = false;
									// Special case: join tariffs
									if (!$hasMatchedTariffs && !empty($roomType->availableTariffs) && !is_null($roomType->availableTariffs[0]['val'])) :
										foreach ($roomType->availableTariffs as $tariffKey => $tariffInfo) :
									?>

										<div class="row-fluid">
											<div id="tariff-box-<?php echo $roomType->id ?>-<?php echo $tariffKey ?>" class="span12 tariff-box <?php //echo $tariffIsSelected ?>">
												<div class="row-fluid">
													<div class="span5">
														<strong>
															<?php echo JText::plural('SR_PRICE_IS_FOR_X_NIGHT', $this->numberOfNights) ?>
														</strong>
													</div>
													<div class="span5 align-right normal_tariff">
														<div class="inner">
															<?php echo $tariffInfo['val']->format() ?>
														</div>
													</div>
													<div class="span2">
														<div class="inner">
															<?php
																if (isset ($roomType->totalAvailableRoom)) :
																	if ($roomType->totalAvailableRoom == 0) :
																		echo JText::_('SR_NO_ROOM_AVAILABLE');
																	else :
																		?>
																		<select
																			name="solidres[ign<?php echo rand() ?>]"
																			data-raid="<?php echo $this->item->id ?>"
																			data-rtid="<?php echo $roomType->id ?>"
																			data-tariffid="<?php echo $tariffKey ?>"
																			data-totalroomsleft="<?php echo $roomType->totalAvailableRoom ?>"
																			class="span12 roomtype-quantity-selection quantity_<?php echo $roomType->id ?>">
																			<option value="0"><?php echo JText::_('SR_ROOMTYPE_QUANTITY') ?></option>
																			<?php
																			for($i = 1; $i <= $roomType->totalAvailableRoom; $i ++) :
																				$selected = '';
																				if (isset($this->selectedRoomTypes['room_types'][$roomType->id][$tariffKey])) :
																					$selected = ($i == count($this->selectedRoomTypes['room_types'][$roomType->id][$tariffKey])) ? 'selected="selected"': '';
																				endif;

																				echo '<option '.$selected.' value="'.$i.'">'. JText::plural('SR_SELECT_ROOM_QUANTITY', $i) . '</option>';
																			endfor;
																			?>
																		</select>
																		<input type="hidden"
																			   name="jform[selected_tariffs][<?php echo $roomType->id ?>][]"
																			   value="<?php echo $tariffKey ?>"
																			   id="selected_tariff_<?php echo $roomType->id ?>_<?php echo $tariffKey ?>"
																			   class="selected_tariff_hidden_<?php echo $roomType->id ?>"
																			   disabled
																			/>
																		<div class="processing" style="display: none"></div>
																	<?php
																	endif;
																endif;
															?>
														</div>
													</div>
												</div>

												<!-- check in form -->
												<div class="row-fluid">
													<div class="span12 checkinoutform" id="checkinoutform-<?php echo $roomType->id ?>-<?php echo $tariffKey ?>" style="display: none">

													</div>
												</div>
												<!-- /check in form -->


												<div class="row-fluid">
													<div class="span12 room-form room-form-<?php echo $roomType->id ?>-<?php echo $tariffKey ?>" id="room-form-<?php echo $roomType->id ?>-<?php echo $tariffKey ?>" style="display: none">

													</div>
												</div>


											</div> <!-- end of span12 -->
										</div> <!-- end of row-fluid -->
									<?php
										endforeach;
									else :
										$link = JRoute::_('index.php?option=com_solidres&view=reservationasset&id=' . $this->item->id . '#form');
										echo '<div class="alert alert-notice">'.JText::sprintf('SR_NO_TARIFF_MATCH_CHECKIN_CHECKOUT', $link) .'</div>';
									endif;
								else:

									if (isset($roomType->tariffs) && is_array($roomType->tariffs)) :
										foreach ($roomType->tariffs as $tariff) :
											$tariffIsSelected = '';

											if (isset($this->selectedTariffs[$roomType->id])) :
												$tariffIsSelected = in_array($tariff->id, $this->selectedTariffs[$roomType->id]) ? 'selected' : '';
											endif;

											if ( isset($this->selectedRoomTypes['room_types'][$roomType->id][$tariff->id])) :
												$currentSelectedRoomNumberPerTariff[$tariff->id] = count($this->selectedRoomTypes['room_types'][$roomType->id][$tariff->id]);
											endif;

											$min = 0;
											?>
											<div class="row-fluid">
												<div id="tariff-box-<?php echo $roomType->id ?>-<?php echo $tariff->id ?>" class="span12 tariff-box <?php echo $tariffIsSelected ?>">
													<div class="row-fluid">
														<div class="span5">
															<strong><?php echo empty($tariff->title) ? JText::_('SR_STANDARD_TARIFF'): $tariff->title ?></strong>
															<p><?php echo $tariff->description ?></p>
														</div>
														<div class="span5 align-right ">
															<?php echo $this->getMinPrice($tariff) ?>
														</div>
														<div class="span2">
															<div class="inner">
																<?php if ($isFresh) : ?>
																	<button class="btn btn-block trigger_checkinoutform" type="button"
																			data-roomtypeid="<?php echo $roomType->id ?>"
																			data-itemid="<?php echo $this->itemid ?>"
																			data-assetid="<?php echo $this->item->id ?>"
																			data-tariffid="<?php echo $tariff->id ?>"
																		><?php echo JText::_('SR_SELECT_TARIFF') ?></button>
																<?php else :
																	if (isset ($roomType->totalAvailableRoom)) :
																		if ($roomType->totalAvailableRoom == 0) :
																			echo JText::_('SR_NO_ROOM_AVAILABLE');
																		else :
																			?>
																			<select
																				name="solidres[ign<?php echo $tariff->id ?>]"
																				data-raid="<?php echo $this->item->id ?>"
																				data-rtid="<?php echo $roomType->id ?>"
																				data-tariffid="<?php echo $tariff->id ?>"
																				data-totalroomsleft="<?php echo $roomType->totalAvailableRoom ?>"
																				class="span12 roomtype-quantity-selection quantity_<?php echo $roomType->id ?>">
																				<option value="0"><?php echo JText::_('SR_ROOMTYPE_QUANTITY') ?></option>
																				<?php
																				for($i = 1; $i <= $roomType->totalAvailableRoom; $i ++) :
																					$selected = '';
																					if (isset($currentSelectedRoomNumberPerTariff[$tariff->id])) :
																						$selected = ($i == $currentSelectedRoomNumberPerTariff[$tariff->id]) ? 'selected': '';
																					endif;

																					echo '<option '.$selected.' value="'.$i.'">'. JText::plural('SR_SELECT_ROOM_QUANTITY', $i) . '</option>';
																				endfor;
																				?>
																			</select>
																			<input type="hidden"
																				   name="jform[selected_tariffs][<?php echo $roomType->id ?>][]"
																				   value="<?php echo $tariff->id ?>"
																				   id="selected_tariff_<?php echo $roomType->id ?>_<?php echo $tariff->id ?>"
																				   class="selected_tariff_hidden_<?php echo $roomType->id ?>"
																				   disabled
																				/>
																			<div class="processing" style="display: none"></div>
																		<?php
																		endif;
																	endif;
																endif;
																?>
															</div>
														</div>
													</div>

													<!-- check in form -->
													<div class="row-fluid">
														<div class="span12 checkinoutform" id="checkinoutform-<?php echo $roomType->id ?>-<?php echo $tariff->id ?>" style="display: none">

														</div>
													</div>
													<!-- /check in form -->


													<div class="row-fluid">
														<div class="span12 room-form room-form-<?php echo $roomType->id ?>-<?php echo $roomType->id ?>" id="room-form-<?php echo $roomType->id ?>-<?php echo $tariff->id ?>" style="display: none">

														</div>
													</div>


												</div> <!-- end of span12 -->
											</div> <!-- end of row-fluid -->
										<?php
										endforeach; // end foreach of complex tariffs
									endif;
								endif // end if in line 274 ?>
							</div>
						</div> <!-- end of span12 -->
					</div> <!-- end of row-fluid -->
				</div>  <!-- end of span12 -->
			</div> <!-- end of row-fluid -->

			<?php
			$count ++;
		endforeach
		?>
	<?php endif ?>

	<?php if (isset($this->checkin) && $this->checkout) : ?>
		<div class="row-fluid button-row button-row-bottom">
			<div class="span9">
				<div class="inner">
					<p><?php echo JText::_('SR_ROOMINFO_STEP_NOTICE_MESSAGE') ?></p>
				</div>
			</div>
			<div class="span3">
				<div class="inner">
					<div class="btn-group">
						<button data-step="room" type="submit" class="btn btn-success">
							<i class="icon-arrow-right uk-icon-arrow-right fa-arrow-right"></i> <?php echo JText::_('SR_NEXT') ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	<?php endif ?>

	<input type="hidden" name="jform[customer_id]" value="" />
	<input type="hidden" name="jform[raid]" value="<?php echo $this->item->id ?>" />
	<input type="hidden" name="jform[state]" value="0" />
	<input type="hidden" name="jform[next_step]" value="guestinfo" />
	<input type="hidden" name="jform[bookingconditions]" value="<?php echo $this->item->params['termsofuse'] ?>" />
	<input type="hidden" name="jform[privacypolicy]" value="<?php echo $this->item->params['privacypolicy'] ?>" />

	<?php echo JHtml::_('form.token'); ?>
	</form>
	</div>
	<!-- /Tab 1 -->

	</div>

	<div class="step-pane" id="step2">
		<!-- Tab 2 -->
		<div class="reservation-single-step-holder guestinfo nodisplay">
		</div>
		<!-- /Tab 2 -->
	</div>

	<div class="step-pane" id="step3">
		<!-- Tab 3 -->
		<div class="reservation-single-step-holder confirmation nodisplay">
		</div>
		<!-- /Tab 3 -->
	</div>

</div>
