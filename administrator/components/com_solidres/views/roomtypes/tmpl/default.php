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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$saveOrder	= $listOrder == 'r.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_solidres&task=roomtypes.saveOrderAjax&tmpl=component';
	SRHtml::_('jquery.sortable', 'roomtypeList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		}
		else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<div id="solidres">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=roomtypes'); ?>" method="post" name="adminForm" id="adminForm">
				<div id="filter-bar" class="btn-toolbar">
					<div class="btn-group pull-right hidden-phone">
						<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
					<div class="clearfix"></div>
				</div>
				<table class="table table-striped" id="roomtypeList">
					<thead>
						<tr>
							<th width="1%" class="nowrap center hidden-phone">
								<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'r.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
							</th>
							<th>
								<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
							</th>
		                    <th width="1%" class="nowrap">
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_ID', 'r.id', $listDirn, $listOrder); ?>
							</th>
							<th class="title">
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_NAME', 'r.name', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_PUBLISHED', 'r.state', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_RESERVATIONASSET', 'reservationasset', $listDirn, $listOrder); ?>
							</th>
		                    <th>
		                        <?php echo JHtml::_('grid.sort',  'SR_HEADING_NUMBEROFROOM', 'number_of_room', $listDirn, $listOrder); ?>
		                    </th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_OCCUPANCY_ADULT', 'occupancy_adult', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_OCCUPANCY_CHILDREN', 'occupancy_children', $listDirn, $listOrder); ?>
							</th>
						</tr>
		                <tr class="filter-row">
		                    <th></th>
		                    <th></th>
							<th></th>
		                    <th>
		                        <input class="inputbox" type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo JText::_('SR_SEARCH_IN_TITLE'); ?>" />
		                    </th>
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
							<th></th>
		                </tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="10">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$ordering	= ($listOrder == 'r.ordering');						
						$canCreate	= $user->authorise('core.create',		'com_solidres.roomtype.'.$item->id);
						$canEdit	= $user->authorise('core.edit',			'com_solidres.roomtype.'.$item->id);
						$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
						$canChange	= $user->authorise('core.edit.state',	'com_solidres.roomtype.'.$item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->reservation_asset_id ?>">
							<td class="order nowrap center hidden-phone">
								<?php
								$iconClass = '';
								if (!$canChange)
								{
									$iconClass = ' inactive';
								}
								elseif (!$saveOrder)
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
								}
								?>
								<span class="sortable-handler<?php echo $iconClass ?>">
								<i class="icon-menu"></i>
								</span>
								<?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering ?>" class="width-20 text-area-order "/>
								<?php endif; ?>
							</td>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
		                    <td class="center">
								<?php echo (int) $item->id; ?>
							</td>
							<td style="width: 35%">
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'roomtypes.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canCreate || $canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=roomtype.edit&id='.(int) $item->id); ?>">
										<?php echo $this->escape($item->name); ?></a>
								<?php else : ?>
										<?php echo $this->escape($item->name); ?>
								<?php endif; ?>
								<!--<p class="smallsub">
									(<span><?php /*echo JText::_('SR_FIELD_ALIAS_LABEL'); */?>:</span> <?php /*echo $this->escape($item->alias);*/?>)</p>-->
							</td>
							<td class="center">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'roomtypes.', $canChange);?>
							</td>
							<td>
		                        <a href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationasset.edit&id='.(int) $item->reservation_asset_id); ?>">
								<?php echo $item->reservationasset; ?>
		                        </a>
							</td>
		                    <td class="center"><?php echo $item->number_of_room ?></td>
							<td>
								<?php echo $item->occupancy_adult ?>
							</td>
							<td>
								<?php echo $item->occupancy_child ?>
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
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
</div>