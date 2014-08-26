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
 * #__prenotown_user_group_entries table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableUserGroupEntries extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $group_id = null;

	/** @var int Foreign Key */
	var $user_id = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_user_group_entries', 'id', $db);
	}

	function __tostring()
	{
		return "PrenotownTableUserGroupEntries";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		if (!$this->user_id) {
			$this->setError(JText::_('No user_id provided'));
			return false;
		}

		if (!$this->group_id) {
			$this->setError(JText::_('No group_id provided'));
			return false;
		}

		return true;
	}
}
?>
