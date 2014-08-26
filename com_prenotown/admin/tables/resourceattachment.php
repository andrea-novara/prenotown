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
 * #__prenotown_resource_attachment table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableResourceAttachment extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $resource_id = null;

	/** @var varchar name */
	var $name = '';

	/** @var varchar filename */
	var $filename = '';

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_resource_attachment', 'id', $db);
	}

	function __tostring()
	{
		return "PrenotownTableResourceAttachment";
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

		if (!$this->name) {
			$this->setError(JText::_('No name provided'));
			return false;
		}

		if (!$this->filename) {
			$this->setError(JText::_('No filename provided'));
			return false;
		}

		return true;
	}
}
?>
