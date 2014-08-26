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
 * #__prenotown_payments table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TablePayments extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $user_id = null;

	/** @var int Payment amount */
	var $amount = 0;

	/** @var enum method of payment */
	var $method = null;

	/** @var varchar(255) check serial number */
	var $check_number = null;

	/** @var datetime date and time of payment */
	var $date = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_payments', 'id', $db);
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		if (!$this->user_id) {
			$this->setError(JText::_('No user provided'));
			return false;
		}

		if (!$this->amount) {
			$this->amount = 0;
			/*
			$this->setError(JText::_('No amount provided'));
			return false;
			*/
		}

		if (!$this->method) {
			$this->setError(JText::_('No method provided'));
			return false;
		}

		return true;
	}
}
?>
