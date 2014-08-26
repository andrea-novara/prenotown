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
 * Resource model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelResourceGroup extends PrenotownModelPrenotown
{
	/**
	 * Constructor, builds object and determines the foobar ID
	 *
	 */
	function __construct() {
		/* calling parent constructor */
		parent::__construct();

		/* loading tables */
		$this->addTable('resourceGroups', true);	// this is also saved as 'main' table
		$this->addTable('resourceGroupEntries');
	}

	function __tostring()
	{
		return "PrenotownModelResource";
	}

	/**
	 * Create a new resource group
	 *
	 * @param string $name new resource group name
	 * @return boolean
	 */
	public function createResourceGroup($name)
	{
		$this->tables['resourceGroups']->reset();
		$this->tables['resourceGroups']->name = $name;
		$this->tables['resourceGroups']->id = null;

		if ($this->tables['resourceGroups']->check() && $this->tables['resourceGroups']->store()) {
			return $this->_id = $this->tables['resourceGroups']->id;
		}

		return 0;
	}

	/**
	 * Resets the resource ID and data
	 *
	 * @param int $id resource ID
	 */
	function setId($id) {
		parent::setId($id);

		if (isset($this->tables['resourceGroups'])) {
			$this->tables['resourceGroups']->reset();
			$this->tables['resourceGroups']->id = $id;
			$this->tables['resourceGroups']->load();
		}
	}

	/**
	 * Return value of a field
	 *
	 * @param string $field field name
	 * @return mixed
	 */
	function get($field) {
		return $this->tables['resource'][$field];
	}

	/**
	 * Set a field to a value
	 *
	 * @param string $field field name
	 * @param mixed $value field value
	 * @return mixed
	 */
	function set($field, $value) {
		return $this->tables['resource'][$field] = $value;
	}

	/**
	 * Add a resource to this group of resources
	 *
	 * @param int $resource_id the new resource ID
	 * @return boolean
	 */
	function addResource($resource_id)
	{
		$this->tables['resourceGroupEntries']->reset();
		$this->tables['resourceGroupEntries']->id = null;
		$this->tables['resourceGroupEntries']->group_id = $this->_id;
		$this->tables['resourceGroupEntries']->resource_id = $resource_id;

		if ($this->tables['resourceGroupEntries']->check() && $this->tables['resourceGroupEntries']->store()) {
			return true;
		}

		if (pref('debug')) _warn("WARN", "Error while adding resource $resource_id to category $this->_id: " . $this->tables['resourceGroupEntries']->getError());
		return false;
	}
}
?>
