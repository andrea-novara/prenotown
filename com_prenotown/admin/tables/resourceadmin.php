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
 * #__prenotown_resource_admin table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableResourceAdmin extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $admin_id = null;

	/** @var int Foreign Key */
	var $resource_id = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_resource_admin', 'id', $db);
	}

	function __tostring()
	{
		return "PrenotownTableResourceAdmin";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		if (!$this->resource_id) {
			$this->setError(JText::_('No resource_id provided'));
			return false;
		}

		if (!$this->admin_id) {
			$this->setError(JText::_('No admin_id provided'));
			return false;
		}

		return true;
	}
}
?>
