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
 * #__prenotown_user_groups table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableUserGroups extends JTable
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
		parent::__construct('#__prenotown_user_groups', 'id', $db);
	}

	function __tostring()
	{
		return "PrenotownTableUserGroups";
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
