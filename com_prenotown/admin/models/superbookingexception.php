<?php
/**
 * @package Prenotown
 * @subpackage Models
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** ensure a valid entry point */
defined('_JEXEC') or die("Restricted Access");

/** import logging facilities */
require_once(JPATH_COMPONENT_SITE . DS . "assets" . DS . "logging.php");
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . "models" . DS . "prenotown.php");

/**
 * SuperbookingException model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelSuperbookingException extends PrenotownModelPrenotown
{
	var $superbooking;

	/**
	 * Constructor, builds object and determines the foobar ID
	 *
	 */
	function __construct() {
		/* calling parent constructor */
		parent::__construct();

		/* loading tables */
		$this->addTable('superbookingException', true);	// this is also saved as 'main' table

		$this->superbooking =& JModel::getInstance('Superbooking', 'PrenotownModel');
	}

	function __toString()
	{
		return "PrenotownModelSuperbookingException";
	}

	/**
	 * Create a new resource into the DB
	 *
	 * @param array $properies all the keys that define a resource
	 * @return boolean
	 */
	public function createSuperbookingException(array $properties)
	{
		// exceptions are allowed only in the future
		$exception_date = date_human_to_sql($properties['exception_date']);
		$sql = "SELECT DATEDIFF(DATE('$exception_date'), DATE(NOW()))";
		_log_sql($sql);
		$this->db->setQuery($sql);
		$valid_date = $this->db->loadResult();

		if ($valid_date <= pref('groupRetractTime') && !_status('admin')) {
			_warn("WARN", JText::sprintf("You are not allowed to enter an exception before %d day from today", pref('groupRetractTime')));
			return false;
		}

		// if (pref('debug')) JError::raiseNotice(500, "Difference between $exception_date AND NOW() is $valid_date");

		// load booking periodicity
		$sql = "SELECT WEEKDAY(" . $this->db->quote($properties['exception_date']) . ")";
		_log_sql($sql);
		$this->db->setQuery($sql);
		$periodicity = pow(2, $this->db->loadResult());

		// check periodicity matching
		$this->superbooking->setId($properties['booking_id']);
		if (!($this->superbooking->tables['superbooking']->periodicity & $periodicity)) {
			// _warn("WARN", JText::sprintf("Date %s is not on periodicity scheme", $properties['exception_date']));
			JError::raiseWarning(500,
				JText::sprintf("Date %s is not a %s",
					date_sql_to_human($properties['exception_date']),
					implode(JText::_(" or "), expand_periodicity($periodicity))
				)
			);
			return false;
		}

		// set table values
		$this->tables['superbookingException']->reset();
		foreach ($properties as $key => $value) {
			$this->tables['superbookingException']->$key = $value;
		}

		$this->tables['superbookingException']->id = null;
		if ($this->tables['superbookingException']->check() && $this->tables['superbookingException']->store()) {
			# OK!
			// if (pref('debug')) JError::raiseNotice(500, "Exception " . $properties['exception_date'] . " inserted!");
			return true;
		}

		if (pref('debug')) {
			_warn("WARN", JText::sprintf("Error while creating exception on date %s: %s",
				$properties['exception_date'], $this->tables['superbookingException']->getError()));
		} else {
			_warn("WARN", JText::sprintf("Error while creating exception on date %s", date_sql_to_human($properties['exception_date'])));
		}

		return false;
	}

	/**
	 * Resets the superbooking ID and data
	 *
	 * @param int $id superbooking ID
	 */
	function setId($id) {
		parent::setId($id);

		if (isset($this->tables['superbookingException'])) {
			$this->tables['superbookingException']->reset();
			$this->tables['superbookingException']->load($id);
		}
	}

	/**
	 * Return value of a field
	 *
	 * @param string $field field name
	 * @return mixed
	 */
	function get($field) {
		return $this->tables['superbookingException'][$field];
	}

	/**
	 * Set a field to a value
	 *
	 * @param string $field field name
	 * @param mixed $value field value
	 * @return mixed
	 */
	function set($field, $value) {
		return $this->tables['superbookingException'][$field] = $value;
	}

	/**
	 * Delete a booking
	 *
	 * @param int $id booking id
	 * @return boolean
	 */
	function delete($id) {
		$this->tables['superbookingException']->reset();
		return $this->tables['superbookingException']->delete($id);
	}
}
?>
