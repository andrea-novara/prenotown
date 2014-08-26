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
 * #__prenotown_resource_availability table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableResourceAvailability extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_resource_availability', 'id', $db);
	}

	function __tostring()
	{
		return "PrenotownTableResourceAvailability";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		return true;
	}
}
?>
