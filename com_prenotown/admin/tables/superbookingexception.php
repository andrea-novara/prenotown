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
 * #__prenotown_superbooking_exception table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableSuperbookingException extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $booking_id = null;

	/** @var date exception date */
	var $exception_date = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_superbooking_exception', 'id', $db);
	}

	function __toString()
	{
		return "PrenotownTableSuperbookingException";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		/* payment can be null if not done yet */

		if (!$this->booking_id) {
			$this->setError(JText::_('No booking provided'));
			return false;
		}

		if (!$this->exception_date) {
			$this->setError(JText::_('No exception date provided'));
			return false;
		}

		return true;
	}
}
?>
