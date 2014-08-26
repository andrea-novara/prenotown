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
 * Resources model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelResources extends PrenotownModelPrenotowns
{
	/**
	 * Constructor, builds object
	 */
	function __construct() {
		global $mainframe, $option;

		parent::__construct();

		$this->setTableName('#__prenotown_resource');
		$this->setSortableFields(array('name', 'id', 'admin_id', 'address', 'deadline', 'max_advance', 'paying_period', 'approval_period'));
		$this->setFilterField('name');
		$this->setOrderingField('name');
		$this->setDefaultQuery("SELECT %%%TABLE_NAME%%%.*, #__prenotown_cost_function.class AS cost_function_class, #__prenotown_cost_function.name AS cost_function_name FROM %%%TABLE_NAME%%% JOIN #__prenotown_cost_function ON %%%TABLE_NAME%%%.cost_function_id = #__prenotown_cost_function.id");
	}

	function __tostring() {
		return "PrenotownModelResources";
	}

	function getByAdmin($id)
	{
		$id = intval($id);
		$this->reset();
		$this->setQuery("SELECT %%%TABLE_NAME%%%.* FROM %%%TABLE_NAME%%%");
		$this->addFilter("admin_id = $id");
		return $this->getData();
	}

	function getByCategory($id)
	{
		$id = intval($id);
		$this->reset();
		$this->setQuery("SELECT %%%TABLE_NAME%%%.*, #__prenotown_cost_function.class AS cost_function_class, #__prenotown_cost_function.name AS cost_function_name FROM %%%TABLE_NAME%%% JOIN #__prenotown_cost_function ON %%%TABLE_NAME%%%.cost_function_id = #__prenotown_cost_function.id LEFT JOIN #__prenotown_resource_group_entries ON #__prenotown_resource_group_entries.resource_id = %%%TABLE_NAME%%%.id");
		$this->addFilter("#__prenotown_resource_group_entries.group_id = $id");
		return $this->getData();
	}
}
?>
