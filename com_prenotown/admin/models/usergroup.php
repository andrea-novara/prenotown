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
 * User Group model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelUserGroup extends PrenotownModelPrenotown
{
	/**
	 * Constructor, builds object and determines the foobar ID
	 *
	 */
	function __construct() {
		/* calling parent constructor */
		parent::__construct();

		/* loading tables */
		$this->addTable('userGroups', true);	// this is also saved as 'main' table
		$this->addTable('userGroupEntries');
	}

	function __tostring()
	{
		return "PrenotownModelUserGroup";
	}

	/**
	 * Create a new user group
	 *
	 * @param string $name new user group name
	 * @return boolean
	 */
	public function createUserGroup($name)
	{
		$this->tables['userGroups']->reset();
		$this->tables['userGroups']->name = $name;
		$this->tables['userGroups']->id = null;

		if ($this->tables['userGroups']->check() && $this->tables['userGroups']->store()) {
			return $this->_id = $this->tables['userGroups']->id;
		}

		return 0;
	}

	/**
	 * Resets the user ID and data
	 *
	 * @param int $id user ID
	 */
	function setId($id) {
		parent::setId($id);

		if (isset($this->tables['userGroups'])) {
			$this->tables['userGroups']->reset();
			$this->tables['userGroups']->id = $id;
			$this->tables['userGroups']->load();
		}
	}

	/**
	 * Return value of a field
	 *
	 * @param string $field field name
	 * @return mixed
	 */
	function get($field) {
		return $this->tables['userGroups'][$field];
	}

	/**
	 * Set a field to a value
	 *
	 * @param string $field field name
	 * @param mixed $value field value
	 * @return mixed
	 */
	function set($field, $value) {
		return $this->tables['userGroups'][$field] = $value;
	}
}
?>
