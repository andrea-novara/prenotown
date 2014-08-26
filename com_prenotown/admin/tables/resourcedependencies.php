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
 * #__prenotown_resource_dependencies table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableResourceDependencies extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $slave_resource_id = null;

	/** @var varchar name */
	var $master_resource_id = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_resource_dependencies', 'id', $db);
	}

	function __tostring()
	{
		return "PrenotownTableResourceDependencies";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		if (!$this->slave_resource_id) {
			$this->setError(JText::_('No slave_resource_id provided'));
			return false;
		}

		if (!$this->master_resource_id) {
			$this->setError(JText::_('No master_resource_id provided'));
			return false;
		}

		return true;
	}
}
?>
