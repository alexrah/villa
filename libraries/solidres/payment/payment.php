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
 * The base payment class for Solidres's payment implementation
 *
 * @package     Solidres
 * @subpackage	Payment
 * @since		0.5.0
 */
class SRPayment extends JPlugin
{
	/**
	 * The payment method identifier, it should be lowercase, unique and with no empty space
	 *
	 * @var string
	 */
	protected $identifier = "base";

	/**
	 * The title of payment method, this will be used in front end
	 *
	 * @var string
	 */
	protected $title = "base";

	/**
	 * The description of this payment method, this will be used in the front end.
	 *
	 * @var string
	 */
	protected $description = "";

	/**
	 * A list of accepted currencies for this payment method
	 *
	 * @var array
	 */
	protected $acceptedCurrencies = array();

	/**
	 * The payment method image, this will be used in the front end
	 *
	 * @var string
	 */
	protected $image = "";

	/**
	 * The default reservation status for this payment method
	 *
	 * @var int
	 */
	protected $defaultStatus;

	function __construct($subject, $config = array())
	{
		parent::__construct($subject, $config);
	}

	public function OnSolidresPaymentNew($reservationData)
	{

	}

	public function OnSolidresPaymentCallback($paymentMethodId, $callbackData)
	{

	}

	private function getEndPoint()
	{
	}

	protected $currencyMapISO4217 = array(
		'AED' => 784, 'AFN' => 971, 'ALL' => 8, 'AMD' => 51, 'ANG' => 532, 'AOA' => 973,
		'ARS' => 32, 'AUD' => 36, 'AWG' => 533, 'AZN' => 944, 'BAM' => 977, 'BBD' => 52,
		'BDT' => 50, 'BGN' => 975, 'BHD' => 48, 'BIF' => 108, 'BMD' => 60, 'BND' => 96,
		'BOB' => 68, 'BOV' => 984, 'BRL' => 986, 'BSD' => 44, 'BTN' => 64, 'BWP' => 72,
		'BYR' => 974, 'BZD' => 84, 'CAD' => 124, 'CDF' => 976, 'CHE' => 947, 'CHF' => 756,
		'CHW' => 948, 'CLF' => 990, 'CLP' => 152, 'CNY' => 156, 'COP' => 170, 'COU' => 970,
		'CRC' => 188, 'CUC' => 931, 'CUP' => 192, 'CVE' => 132, 'CZK' => 203, 'DJF' => 262,
		'DKK' => 208, 'DOP' => 214, 'DZD' => 12, 'EGP' => 818, 'ERN' => 232, 'ETB' => 230,
		'EUR' => 978, 'FJD' => 242, 'FKP' => 238, 'GBP' => 826, 'GEL' => 981, 'GHS' => 936,
		'GIP' => 292, 'GMD' => 270, 'GNF' => 324, 'GTQ' => 320, 'GYD' => 328, 'HKD' => 344,
		'HNL' => 340, 'HRK' => 191, 'HTG' => 332, 'HUF' => 348, 'IDR' => 360, 'ILS' => 376,
		'INR' => 356, 'IQD' => 368, 'IRR' => 364, 'ISK' => 352, 'JMD' => 388, 'JOD' => 400,
		'JPY' => 392, 'KES' => 404, 'KGS' => 417, 'KHR' => 116, 'KMF' => 174, 'KPW' => 408,
		'KRW' => 410, 'KWD' => 414, 'KYD' => 136, 'KZT' => 398, 'LAK' => 418, 'LBP' => 422,
		'LKR' => 144, 'LRD' => 430, 'LSL' => 426, 'LTL' => 440, 'LVL' => 428, 'LYD' => 434,
		'MAD' => 504, 'MDL' => 498, 'MGA' => 969, 'MKD' => 807, 'MMK' => 104, 'MNT' => 496,
		'MOP' => 446, 'MRO' => 478, 'MUR' => 480, 'MVR' => 462, 'MWK' => 454, 'MXN' => 484,
		'MXV' => 979, 'MYR' => 458, 'MZN' => 943, 'NAD' => 516, 'NGN' => 566, 'NIO' => 558,
		'NOK' => 578, 'NPR' => 524, 'NZD' => 554, 'OMR' => 512, 'PAB' => 590, 'PEN' => 604,
		'PGK' => 598, 'PHP' => 608, 'PKR' => 586, 'PLN' => 985, 'PYG' => 600, 'QAR' => 634,
		'RON' => 946, 'RSD' => 941, 'RUB' => 643, 'RWF' => 646, 'SAR' => 682, 'SBD' => 90,
		'SCR' => 690, 'SDG' => 938, 'SEK' => 752, 'SGD' => 702, 'SHP' => 654, 'SLL' => 694,
		'SOS' => 706, 'SRD' => 968, 'SSP' => 728, 'STD' => 678, 'SYP' => 760, 'SZL' => 748,
		'THB' => 764, 'TJS' => 972, 'TMT' => 934, 'TND' => 788, 'TOP' => 776, 'TRY' => 949,
		'TTD' => 780, 'TWD' => 901, 'TZS' => 834, 'UAH' => 980, 'UGX' => 800, 'USD' => 840,
		'USN' => 997, 'USS' => 998, 'UYI' => 940, 'UYU' => 858, 'UZS' => 860,  'VEF' => 937,
		'VND' => 704, 'VUV' => 548, 'WST' => 882, 'XXX' => 999, 'YER' => 886, 'ZAR' => 710,
		'ZMK' => 894, 'ZWL' => 932,
	);
}