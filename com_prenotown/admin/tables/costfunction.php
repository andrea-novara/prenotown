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
 * #__prenotown_cost_function table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableCostFunction extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var varchar Cost Function name */
	var $name = "";

	/** @var varchar Cost Function PHP class */
	var $class = '';

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_cost_function', 'id', $db);
	}

	function __tostring()
	{
		return "PrenotownTableCostFunction";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		if (!$this->name) {
			$this->setError(JText::_('No name provided'));
			return false;
		}

		if (!$this->class) {
			$this->setError(JText::_('No class provided'));
			return false;
		}

		return true;
	}
}
?>
