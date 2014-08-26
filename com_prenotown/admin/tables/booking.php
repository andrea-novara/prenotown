<?php
/**
 * @package Prenotown
 * @subpackage Tables
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** ensure a valid entry point */
defined('_JEXEC') or die("Restricted Access");

/**
 * #__prenotown_booking table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableBooking extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $resource_id = null;

	/** @var int Foreign Key */
	var $user_id = null;

	/** @var int Foreign Key */
	var $payment_id = null;

	/** @var datetime Booking start instant */
	var $start = null;

	/** @var datetime Booking stop instant */
	var $stop = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_booking', 'id', $db);
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		/* payment can be null if not done yet */

		if (!$this->resource_id) {
			$this->setError(JText::_('No resource provided'));
			return false;
		}

		if (!$this->user_id) {
			$this->setError(JText::_('No user provided'));
			return false;
		}

		if (!$this->start) {
			$this->setError(JText::_('No start time provided'));
			return false;
		}

		if (!$this->stop) {
			$this->setError(JText::_('No stop time provided'));
			return false;
		}

		return true;
	}
}
?>
