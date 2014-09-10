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

$dateCheckIn = JDate::getInstance();
$dateCheckOut = JDate::getInstance();
$showDateInfo = !empty($this->checkin) && !empty($this->checkout);
?>

<div id="availability-search">
	<?php if ($this->checkin && $this->checkout) : ?>
	<div class="alert alert-info availability-search-info">
		<?php
			echo JText::sprintf('SR_ROOM_AVAILABLE_FROM_TO',
				JDate::getInstance($this->checkin, $this->timezone)->format($this->dateFormat, true) ,
				JDate::getInstance($this->checkout, $this->timezone)->format($this->dateFormat, true)
			);
		?>
		<a class="btn" href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationasset.startOver&id='. $this->item->id ) ?>"><i class="icon-remove uk-icon-refresh fa-refresh"></i> <?php echo JText::_('SR_SEARCH_RESET')?></a>
	</div>
	<?php endif; ?>

	<form id="sr-checkavailability-form-component"
		  action="<?php echo JRoute::_('index.php', false)?>"
		  method="GET"
		>
		<input name="id" value="<?php echo $this->item->id ?>" type="hidden" />
		<input name="Itemid" value="<?php echo $this->itemid ?>" type="hidden" />

		<input type="hidden"
			   name="checkin"
			   value="<?php echo isset($this->checkin) ? $this->checkin : $dateCheckIn->add(new DateInterval('P'.($this->minDaysBookInAdvance).'D'))->setTimezone($this->timezone)->format('d-m-Y', true) ?>"
			   />

		<input type="hidden"
			   name="checkout"
			   value="<?php echo isset($this->checkout) ? $this->checkout : $dateCheckOut->add(new DateInterval('P'.($this->minDaysBookInAdvance + $this->minLengthOfStay).'D'))->setTimezone($this->timezone)->format('d-m-Y', true) ?>"
			   />
		<input type="hidden" name="option" value="com_solidres" />
		<input type="hidden" name="task" value="reservationasset.checkavailability" />
		<input type="hidden" name="ts" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>

