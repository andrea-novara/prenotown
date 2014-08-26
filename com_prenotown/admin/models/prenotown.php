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

/**
 * Prenotown singular model
 * This class is extended by all the models that rappresent a singular
 * entity inside the DB, typically using JTable instances to manipulate
 * data.
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelPrenotown extends JModel
{
	/**
	 * JTable instances, if used, in a key indexed array
	 *
	 * @var array
	 */
	public $tables;

	/**
	 * ID
	 *
	 * @var int
	 */
	var $_id;

	/**
	 * DB handle
	 */
	var $db;

	/**
	 * Constructor, builds object and determines the foobar ID
	 *
	 */
	function __construct() {
		parent::__construct();

		# Adding table path
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DS . "tables");

		// get the cid array from the default request hash
		$cid = JRequest::getVar('cid', false, 'DEFAULT', 'ARRAY');
		if ($cid) {
			$id = $cid[0];
		} else {
			$id = JRequest::getInt('id', 0);
		}

		/* get database connection */
		$this->db =& JFactory::getDBO();

		/* set the Id */
		$this->setId($id);
	}

	function __tostring()
	{
		return "PrenotownModelResource";
	}

	/**
	 * Add a new instance of table $table_name in the tables
	 * array as $table_index
	 *
	 * @param string $table The name of the table
	 * @param boolean $is_main If true, the table is also saved as 'main'
	 * @return JTable instance
	 */
	function addTable($table, $is_main = 0)
	{
		if ($is_main) {
			$this->tables['main'] =& $this->getTable($table);
		}
		return $this->tables[$table] =& $this->getTable($table);
	}

	/**
	 * Resets the resource ID and data
	 *
	 * @param int $id resource ID
	 */
	function setId($id) {
		$this->_id = $id;
		$this->load();
	}

	/**
	 * Load the row
	 *
	 * @param int $id the ID of the row
	 */
	function load($id=0)
	{
		if (!$id) {
			$id = $this->_id;
		}

		if (isset($this->tables['main'])) {
			$this->tables['main']->reset();
			$this->tables['main']->id = $id;
			$this->tables['main']->load();
		}
	}

	/**
	 * Return value of a field
	 *
	 * @param string $field field name
	 * @param string $table table name
	 * @return mixed
	 */
	function get($field, $table = "") {
		if (isset($table)) {
			return $this->tables[$table][$field];
		} else if (isset($this->tables['main'][$field])) {
			return $this->tables['main'][$field];
		} else {
			while (list($k, $v) = each($this->tables)) {
				if (isset($this->tables[$k][$field])) {
					return $this->tables[$k][$field];
				}
			}
		}

		return null;
	}

	/**
	 * Set a field to a value
	 *
	 * @param string $field field name
	 * @param mixed $value field value
	 * @return mixed
	 */
	function set($field, $table, $value) {
		return $this->tables[$table][$field] = $value;
	}

	function getRow()
	{
	}
}
?>
