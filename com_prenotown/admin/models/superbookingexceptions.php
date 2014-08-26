<?php
/**
 * @package Prenotown
 * @subpackage Models
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** ensure a valid entry point */
defined('_JEXEC') or die('Restricted Access');

/** import the JModel class */
jimport('joomla.application.component.model');

/** import the code to paginate list of elements */
jimport('joomla.html.pagination');

/** import logging facilities */
require_once(JPATH_COMPONENT_SITE . DS . "assets" . DS . "logging.php");
require_once(JPATH_COMPONENT_SITE . DS . "assets" . DS . "user_session.php");
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . "models" . DS . "prenotowns.php");

/**
 * SuperbookingExceptions model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelSuperbookingExceptions extends PrenotownModelPrenotowns
{
	/**
	 * Constructor, builds object
	 *
	 */
	function __construct() {
		global $mainframe, $option, $prenotown_user;

		parent::__construct();

		$this->setTableName('#__prenotown_superbooking_exception');
		$this->setSortableFields(array('booking_id', 'exception_date'));
		$this->setFilterField('booking_id');
		$this->setOrderingField('exception_date');
		$this->setQuery("SELECT %%%TABLE_NAME%%%.* FROM %%%TABLE_NAME%%%");

		$this->db =& JFactory::getDBO();
	}

	function __tostring() {
		return "PrenotownModelSuperbookingExceptions";
	}
}
?>
