<?php
/**
 * @package Prenotown
 * @subpackage Models
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** ensure a valid entry point */
defined('_JEXEC') or die("Restricted Access");

/** import logging facilities */
require_once(JPATH_COMPONENT_SITE . DS . "assets" . DS . "logging.php");
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . "models" . DS . "prenotown.php");

/**
 * Resource attachment model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelResourceAttachment extends PrenotownModelPrenotown
{
	/**
	 * Constructor, builds object and determines the foobar ID
	 *
	 */
	function __construct() {
		/* calling parent constructor */
		parent::__construct();

		/* loading tables */
		$this->addTable('resourceattachment', true);	// this is also saved as 'main' table
	}

	function __tostring()
	{
		return "PrenotownModelResourceAttachment";
	}

	/**
	 * Resets the resource ID and data
	 *
	 * @param int $id resource ID
	 */
	function setId($id) {
		parent::setId($id);
	}
}
?>
