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
 * #__prenotown_resource table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableResource extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $cost_function_id = null;

	/** @var varchar name */
	var $name = null;

	/** @var varchar address */
	var $address = null;

	/** @var time deadline of booking */
	var $deadline = null;

	/** @var time maximum advance of booking */
	var $max_advance = null;

	/** @var time time allowed for paying */
	var $paying_period = null;

	/** @var time approval period */
	var $approval_period = null;

	/** @var text description of the resource */
	var $description = null;

	/** @var int administrator id */
	var $admin_id = null;

	/** @var text notes about the resource */
	var $notes = null;

	/** @var boolean Is enabled? */
	var $enabled = null;

	/** @var boolean Is enabled? */
	var $availability_enabled = null;

	/** @var time monday start time */
	var $monday_begin = null;

	/** @var time monday end time */
	var $monday_end = null;

	/** @var time tuesday start time */
	var $tuesday_begin = null;

	/** @var time tuesday end time */
	var $tuesday_end = null;

	/** @var time wednesday start time */
	var $wednesday_begin = null;

	/** @var time wednesday end time */
	var $wednesday_end = null;

	/** @var time thursday start time */
	var $thursday_begin = null;

	/** @var time thursday end time */
	var $thursday_end = null;

	/** @var time friday start time */
	var $friday_begin = null;

	/** @var time friday end time */
	var $friday_end = null;

	/** @var time saturday start time */
	var $saturday_begin = null;

	/** @var time saturday end time */
	var $saturday_end = null;

	/** @var time sunday start time */
	var $sunday_begin = null;

	/** @var time sunday end time */
	var $sunday_end = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_resource', 'id', $db);
	}

	function __tostring()
	{
		return "PrenotownTableResource";
	}

	/**
	 * Check time format, and resets it to 00:00:00 if invalid
	 */
	private function check_time($key)
	{
		if (isset($key)) {
			// add seconds field, if missing
			if (preg_match('/^[0-9][0-9]:[0-9][0-9]$/', $this->$key)) {
				$this->$key .= ":00";
			}

			if (!preg_match('/^[0-9][0-9]:[0-9][0-9]:[0-9][0-9]$/', $this->$key)) {
				_warn("WARN", "Time " . $this->$key . " is invalid");
				if (preg_match('/_begin$/', $key)) {
					$this->$key = "00:00:00";
				} else {
					$this->$key = "23:59:59";
				}
			}
		}
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		# new entry
		if (!isset($this->id)) {
			if (!$this->cost_function_id) {
				$this->setError(JText::_('No cost_function_id provided'));
				return false;
			}

			if (!$this->admin_id) {
				$this->setError(JText::_('No admin_id provided'));
				return false;
			}

			if (!$this->name) {
				$this->setError(JText::_('No name provided'));
				return false;
			}

			if (!$this->deadline) {
				$this->setError(JText::_('No deadline provided'));
				return false;
			}

			if (!$this->max_advance) {
				$this->setError(JText::_('No max advance provided'));
				return false;
			}

			if (!$this->paying_period) {
				$this->setError(JText::_('No paying period provided'));
				return false;
			}

			if (!$this->approval_period) {
				$this->setError(JText::_('No approval period provided'));
				return false;
			}

			if (!$this->description) {
				$this->setError(JText::_('No description provided'));
				return false;
			}
		}

		if ($this->availability_enabled) {
			foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') as $day) {
				$this->check_time($day . "_begin");
				$this->check_time($day . "_end");
			}
		}

		return true;
	}
}
?>
