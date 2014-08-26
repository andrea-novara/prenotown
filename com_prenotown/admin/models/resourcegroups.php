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
 * Resource groups model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelResourceGroups extends PrenotownModelPrenotowns
{
	/**
	 * Constructor, builds object
	 */
	function __construct() {
		global $mainframe, $option;

		parent::__construct();

		$this->setTableName('#__prenotown_resource_groups');
		$this->setSortableFields(array('name', 'id', 'admin_id', 'address', 'deadline', 'max_advance', 'paying_period', 'approval_period'));
		$this->setFilterField('name');
		$this->setOrderingField('name');
		$this->setDefaultQuery("SELECT %%%TABLE_NAME%%%.* FROM %%%TABLE_NAME%%%");
	}

	function __tostring() {
		return "PrenotownModelResourceGroups";
	}
}
?>
