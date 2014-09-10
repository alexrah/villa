<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2014 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
$user	= JFactory::getUser();
$userId	= $user->get('id');


?>
	<div id="solidres">
		<div class="row-fluid">
			<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
			<div id="sr_panel_right" class="sr_list_view span10">
				<?php
				if (SR_PLUGIN_LIMITBOOKING_ENABLED) :
					$listOrder	= $this->state->get('list.ordering');
					$listDirn	= $this->state->get('list.direction');
					$saveOrder	= $listOrder == 'a.ordering';
				?>
				<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=limitbookings'); ?>" method="post" name="adminForm" id="adminForm">
					<table class="table table-striped">
						<thead>
						<tr>
							<th width="20">
								<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
							</th>
							<th width="1%" class="nowrap">
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
							<th class="title">
								<?php echo JHtml::_('grid.sort',  'SR_FIELD_LIMITBOOKING_TITLE_LABEL', 'a.title', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_PUBLISHED', 'a.state', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_RESERVATIONASSET', 'reservationasset', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_FIELD_LIMITBOOKING_START_DATE_LABEL', 'a.start_date', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_FIELD_LIMITBOOKING_END_DATE_LABEL', 'a.end_date', $listDirn, $listOrder); ?>
							</th>
						</tr>
						<tr class="filter-row">
							<th></th>
							<th></th>
							<th></th>
							<th>
								<select name="filter_published" class="inputbox" onchange="this.form.submit()">
									<option value=""></option>
									<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);?>
								</select>
							</th>
							<th>
								<select name="filter_reservation_asset_id" class="inputbox" onchange="this.form.submit()">
									<?php echo JHtml::_('select.options', SolidresHelper::getReservationAssetOptions(), 'value', 'text', $this->state->get('filter.reservation_asset_id'));?>
								</select>
							</th>
							<th></th>
							<th></th>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<td colspan="7">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
						</tfoot>
						<tbody>
						<?php foreach ($this->items as $i => $item) :
							$ordering	= ($listOrder == 'a.ordering');
							$canCreate	= $user->authorise('core.create',		'com_solidres.reservationasset.'.$item->reservation_asset_id);
							$canEdit	= $user->authorise('core.edit',			'com_solidres.reservationasset.'.$item->reservation_asset_id);
							//$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
							$canChange	= $user->authorise('core.edit.state',	'com_solidres.reservationasset.'.$item->reservation_asset_id);
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="center">
									<?php echo JHtml::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="center">
									<?php echo (int) $item->id; ?>
								</td>
								<td>
									<?php if ($canCreate || $canEdit) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=limitbooking.edit&id='.(int) $item->id); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>
								</td>
								<td class="center">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'limitbookings.', $canChange);?>
								</td>
								<td>
									<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationasset.edit&id='.(int) $item->reservation_asset_id); ?>">
										<?php echo $item->reservationasset; ?>
									</a>
								</td>
								<td>
									<?php echo $item->start_date ?>
								</td>
								<td>
									<?php echo $item->end_date ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="boxchecked" value="0" />
					<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
					<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
					<?php echo JHtml::_('form.token'); ?>
				</form>
				<?php else : ?>
					<div class="alert alert-info">
						This feature allows you to take some or all of your rooms out of service either for renovation or other reasons.
					</div>

					<div class="alert alert-success">
						<strong>Notice:</strong> plugin Limit Booking is not installed or enabled. <a target="_blank" href="https://www.solidres.com/subscribe/levels">Become a subscriber and download it now.</a>
					</div>
				<?php endif ?>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12 powered">
				<p>Powered by <a href="http://wwww.solidres.com" target="_blank">Solidres</a></p>
			</div>
		</div>
	</div>