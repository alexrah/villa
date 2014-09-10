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
 * Solidres Email handler class, extends from JMail and add support for replacements
 *
 * @package     Solidres
 * @subpackage	Mail
 * @since		0.3.0
 */
class SRMail extends JMail
{
	protected $replacements = array();

	public function __construct()
	{
		$jConfig = JFactory::getConfig();

		$smtpauth = ($jConfig->get('smtpauth') == 0) ? null : 1;
		$smtpuser = $jConfig->get('smtpuser');
		$smtppass = $jConfig->get('smtppass');
		$smtphost = $jConfig->get('smtphost');
		$smtpsecure = $jConfig->get('smtpsecure');
		$smtpport = $jConfig->get('smtpport');
		$mailfrom = $jConfig->get('mailfrom');
		$fromname = $jConfig->get('fromname');
		$mailer = $jConfig->get('mailer');

		parent::__construct();

		// Default mailer is to use PHP's mail function
		switch ($mailer)
		{
			case 'smtp':
				$this->useSMTP($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
				break;

			case 'sendmail':
				$this->IsSendmail();
				break;

			default:
				$this->IsMail();
				break;
		}
	}

	public function setReplacements($replacements)
	{
		$this->replacements = $replacements;
	}

	public function setBody($content)
	{
		parent::setBody($content);

		$this->Body = strtr($this->Body, $this->replacements);
	}
}