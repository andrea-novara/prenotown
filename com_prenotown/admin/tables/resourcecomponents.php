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
 * #__prenotown_resource_components table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableResourceComponents extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $component_resource_id = null;

	/** @var varchar name */
	var $composed_resource_id = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_resource_components', 'id', $db);
	}

	function __tostring()
	{
		return "PrenotownTableResourceComponents";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		if (!$this->component_resource_id) {
			$this->setError(JText::_('No component_resource_id provided'));
			return false;
		}

		if (!$this->composed_resource_id) {
			$this->setError(JText::_('No composed_resource_id provided'));
			return false;
		}

		return true;
	}
}
?>
