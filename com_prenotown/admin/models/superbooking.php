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
 * Superbooking model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelSuperbooking extends PrenotownModelPrenotown
{
	/**
	 * Constructor, builds object and determines the foobar ID
	 *
	 */
	function __construct() {
		/* calling parent constructor */
		parent::__construct();

		/* loading tables */
		$this->addTable('superbooking', true);	// this is also saved as 'main' table
		$this->addTable('payments');
	}

	function __toString()
	{
		return "PrenotownModelSuperbooking";
	}

	/**
	 * Create a new resource into the DB
	 *
	 * @param array $properies all the keys that define a resource
	 * @return boolean
	 */
	public function createSuperbooking(array $properties)
	{
		$this->tables['superbooking']->reset();

		foreach ($properties as $key => $value) {
			$this->tables['superbooking']->$key = $value;
		}

		$this->tables['superbooking']->id = null;
		if ($this->tables['superbooking']->check() && $this->tables['superbooking']->store()) {
			return true;
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

		if (isset($this->tables['superbooking'])) {
			$this->tables['superbooking']->reset();
			$this->tables['superbooking']->load($id);
		}
	}

	/**
	 * Return value of a field
	 *
	 * @param string $field field name
	 * @return mixed
	 */
	function get($field) {
		return $this->tables['superbooking'][$field];
	}

	/**
	 * Set a field to a value
	 *
	 * @param string $field field name
	 * @param mixed $value field value
	 * @return mixed
	 */
	function set($field, $value) {
		return $this->tables['superbooking'][$field] = $value;
	}

	/**
	 * Delete a booking
	 *
	 * @param int $id booking id
	 * @return boolean
	 */
	function delete($id) {
		$this->tables['superbooking']->reset();
		return $this->tables['superbooking']->delete($id);
	}
}
?>
