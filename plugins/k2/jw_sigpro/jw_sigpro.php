<?php
/**
 * @version		$Id: jw_sigpro.php 2725 2013-04-06 17:05:49Z joomlaworks $
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

JLoader::register('K2Plugin', JPATH_ADMINISTRATOR.'/components/com_k2/lib/k2plugin.php');

class plgK2Jw_SigPro extends K2Plugin
{

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->pluginName = 'jw_sigpro';
		$this->loadLanguage();
	}

	function onAfterK2Save(&$item, $isNew)
	{
		jimport('joomla.filesystem.folder');
		JLoader::register('SigProHelper', JPATH_ADMINISTRATOR.'/components/com_sigpro/helper.php');
		$path = SigProHelper::getPath('k2');
		$folder = JRequest::getCmd('sigProFolder');
		if ($isNew && $folder && $folder != $item->id && JFolder::exists($path.'/'.$folder))
		{
			JFolder::move($path.'/'.$folder, $path.'/'.$item->id);
		}
		if (JFolder::exists($path.'/'.$item->id) && $item->gallery == null)
		{
			$item->gallery = '{gallery}'.$item->id.'{/gallery}';
			$item->store();
		}
	}

}
