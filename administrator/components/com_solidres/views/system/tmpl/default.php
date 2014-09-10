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
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
$user	= JFactory::getUser();
$userId	= $user->get('id');


$solidresPlugins = array(
	'extension' => array(
		'solidres'
	),
	'solidres' => array(
		'camera_slideshow',
		'complextariff',
		//'feedback',
		'hub',
		'invoice',
		'limitbooking',
		'simple_gallery',
		'statistics'
	),
	'solidrespayment' => array(
		'paypal'
	),
	'system' => array(
		'solidres'
	),
	'user' => array(
		'solidres'
	)
);

$solidresModules = array(
	'mod_sr_advancedsearch',
	'mod_sr_camera',
	'mod_sr_checkavailability',
	'mod_sr_currency',
	'mod_sr_filter',
	'mod_sr_roomtypes',
	'mod_sr_locationmap',
	'mod_sr_coupons',
	'mod_sr_extras',
	'mod_sr_assets',
	'mod_sr_map'
);

$phpSettings = array(

);


?>

<div id="solidres">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<div class="row-fluid">
				<div class="span12">
					<img src="<?php echo JUri::root() ?>/media/com_solidres/assets/images/logo_black.png"
							 alt="Solidres Logo" class="pull-right" />
					<p>
					</p>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="alert alert-success">
						Version <?php echo SRVersion::getShortVersion() ?>
					</div>

					<div class="alert alert-info">
						If you use Solidres, please post a rating and a review at the
							<a href="http://extensions.joomla.org/extensions/vertical-markets/booking-a-reservations/booking/23594" target="_blank">
								Joomla! Extensions Directory
							</a>
					</div>


					<h3>Plugins status</h3>

					<table class="table table-condensed system-table">
						<thead>
							<tr>
								<th>
									Plugin Name
								</th>
								<th>
									Plugin Status
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($solidresPlugins as $group => $plugins) : ?>
								<?php
									foreach ($plugins as $plugin) :
										$pluginKey = 'plg_'.$group.'_'.$plugin;
										$extTable = JTable::getInstance('Extension');
										$extTable->load(array('name' => $pluginKey));
										$isInstalled = false;
										if ($extTable->extension_id > 0) :
											$isInstalled = true;
										endif;
								?>
								<tr>
									<td>
										<?php echo JText::_('plg_'.$group.'_'.$plugin) ?>
									</td>
									<td>
										<?php
										if ($isInstalled) :
											$pluginInfo = json_decode($extTable->manifest_cache);
											$isEnabled = JPluginHelper::isEnabled($group, $plugin);
											//echo '[Version '. $pluginInfo->version .']';
											echo $isEnabled ? '<span class="label label-success">Version ' .$pluginInfo->version. ' is enabled</span>' : '<span class="label label-warning">Version ' .$pluginInfo->version. ' is not enabled</span>';

										else :
											echo '<span class="label label-important">Not installed</span>';
										endif;
										?>
									</td>
								</tr>
								<?php endforeach ?>
							<?php endforeach ?>
						</tbody>
					</table>


					<h3>Modules status</h3>

					<table class="table table-condensed system-table">
						<thead>
						<tr>
							<th>
								Module Name
							</th>
							<th>
								Module Status
							</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($solidresModules as $module) : ?>
							<?php
								//$pluginKey = 'plg_'.$group.'_'.$plugin;
								$extTable = JTable::getInstance('Extension');
								$extTable->load(array('name' => $module));
								$isInstalled = false;
								if ($extTable->extension_id > 0) :
									$isInstalled = true;
								endif;
								?>
								<tr>
									<td>
										<?php echo JText::_($module) ?>
									</td>
									<td>
										<?php
										if ($isInstalled) :
											$moduleInfo = json_decode($extTable->manifest_cache);
											echo '<span class="label label-success">Version '.$moduleInfo->version.' is installed</span>';
										else :
											echo '<span class="label label-important">Not installed</span>';
										endif;
										?>

									</td>
								</tr>
						<?php endforeach ?>
						</tbody>
					</table>

					<h3>System check list</h3>

					<table class="table table-condensed system-table">
						<thead>
						<tr>
							<th>
								Setting name
							</th>
							<th>
								Status
							</th>
						</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									GD is enabled in your server
								</td>
								<td>
									<?php
									if (extension_loaded('gd') && function_exists('gd_info')) :
										echo '<span class="label label-success">YES</span>';
									else :
										echo '<span class="label label-warning">NO</span>';
									endif;
									?>
								</td>
							</tr>
							<tr>
								<td>
									/media/com_solidres/assets/images/system/thumbnails is writable?
								</td>
								<td>
									<?php
									echo is_writable(JPATH_SITE . '/media/com_solidres/assets/images/system/thumbnails/1')
										? '<span class="label label-success">YES</span>'
										: '<span class="label label-warning">NO</span>';
									?>
								</td>
							</tr>
							<tr>
								<td>
									/media/com_solidres/assets/images/system/thumbnails/1 is writable?
								</td>
								<td>
									<?php
									echo is_writable(JPATH_SITE . '/media/com_solidres/assets/images/system/thumbnails/1')
									? '<span class="label label-success">YES</span>'
									: '<span class="label label-warning">NO</span>';
									?>
								</td>
							</tr>
							<tr>
								<td>
									/media/com_solidres/assets/images/system/thumbnails/2 is writable?
								</td>
								<td>
									<?php
									echo is_writable(JPATH_SITE . '/media/com_solidres/assets/images/system/thumbnails/2')
										? '<span class="label label-success">YES</span>'
										: '<span class="label label-warning">NO</span>';
									?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
</div>