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
 * #__prenotown_resource_groups table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableResourceGroups extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Group name */
	var $name = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_resource_groups', 'id', $db);
	}

	function __tostring()
	{
		return "PrenotownTableResourceGroups";
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

		return true;
	}
}
?>
