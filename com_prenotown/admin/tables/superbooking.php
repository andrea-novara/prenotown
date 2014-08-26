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
 * #__prenotown_superbooking table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableSuperbooking extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $resource_id = null;

	/** @var int Foreign Key */
	var $payment_id = null;

	/** @var int Foreign Key */
	var $user_id = null;

	/** @var int Foreign Key */
	var $group_id = null;

	/** @var int Foreign Key */
	var $operator_id = null;

	/** @var boolean booking is periodic? */
	var $periodic = false;

	/** @var bitmask periodicity if periodic */
	var $periodicity = 0;

	/** @var datetime Booking begin instant */
	var $begin = null;

	/** @var datetime Booking end instant */
	var $end = null;

	/** @var int booking cost */
	var $cost = 0;

	/** @var boolean approved? */
	var $approved = false;

	/** @var datetime creation date and time */
	var $created = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_superbooking', 'id', $db);
	}

	function __toString()
	{
		return "PrenotownTableSuperbooking";
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

		if (!$this->begin) {
			$this->setError(JText::_('No begin time provided'));
			return false;
		}

		if (!$this->end) {
			$this->setError(JText::_('No end time provided'));
			return false;
		}

		if ($this->periodic and !$this->periodicity) {
			$this->setError(JText::_('Periodic booking must provide a periodicity mask'));
			return false;
		}

		if (!$this->cost && !$this->periodic) {
			$this->setError(JText::_("No cost provided"));
			return false;
		}

		if (!$this->created && !preg_match("/^\d\d\d\d-\d\d-\d\d/", $this->created)) {
			$this->created = date("Y-m-d H.i.s");
		}

		return true;
	}
}
?>
