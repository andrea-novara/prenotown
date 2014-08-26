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
 * #__prenotown_preferences table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TablePreferences extends JTable
{
	/** @var string Primary Key */
	var $key = null;

	/** @var string value of the key */
	var $value = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_preferences', 'id', $db);
	}

	function __tostring()
	{
		return "PrenotownTablePreferences";
	}

	function setId($id)
	{
		if (isset($id) && !empty($id)) {
			$this->key = $id;
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
			return false;
		}

		if (!isset($this->value

		return true;
	}
}
?>
