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

/**
 * Group model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelGroup extends PrenotownModelPrenotown
{
	/**
	 * Constructor, builds object and determines the foobar ID
	 *
	 */
	function __construct() {
		/* calling parent constructor */
		parent::__construct();

		/* loading tables */
		$this->addTable('usergroups', true);	// this is also saved as 'main' table
		$this->addTable('usergroupentries');

		if (!isset($this->tables['usergroups'])) {
			if (pref('debug')) _warn("WARN", "No user groups table!");
		}
	}

	function __tostring()
	{
		return "PrenotownModelGroup";
	}

	/**
	 * Resets the foobar ID and data
	 *
	 * @param int foobar ID
	 */
	function setId($id) {
		$this->_id = $id;

		if (isset($this->tables['usergroups'])) {
			$this->tables['usergroups']->reset();
			$this->tables['usergroups']->load($id);
		}

		if (isset($this->tables['usergroupentries'])) {
			$this->tables['usergroupentries']->reset();
			$this->tables['usergroupentries']->id = null;
		}
	}

	/**
	 * Create a new group
	 *
	 * @param string $name group name
	 * @return boolead
	 */
	function add_group($name)
	{
		$this->tables['usergroups']->name = $name;
		if ($this->tables['usergroups']->check() && $this->tables['usergroups']->store()) {
			return 1;
		}
		if (pref('debug')) _warn("WARN", $this->tables['usergroups']->getError());
		return 0;
	}

	/**
	 * Delete an existing group by id
	 *
	 * @param int $id group ID
	 * @return boolean
	 */
	function delete_group($id)
	{
		if (!isset($id)) {
			return 0;
		}

		if ($this->tables['usergroups']->delete($id)) {
			return 1;
		}
		if (pref('debug')) _warn("WARN", $this->tables['usergroups']->getError());
		return 0;
	}

	/**
	 * Return user information about all users subscribed to this group
	 *
	 * @returns array
	 */
	function getUsers()
	{
		$query = "
SELECT #__users.*, #__prenotown_user_complement.*,
	concat(address, ' ', ZIP, ' ', town) AS address
FROM #__users
JOIN #__prenotown_user_complement ON #__users.id = #__prenotown_user_complement.id
JOIN #__prenotown_user_group_entries ON #__users.id = #__prenotown_user_group_entries.user_id
WHERE #__prenotown_user_group_entries.group_id = $this->_id";
		_log_sql($query);

		$this->db->setQuery($query);
		return $this->db->loadAssocList();
	}

	/**
	 * Add a user to a group
	 *
	 * @param int $id user id
	 * @returns boolean
	 */
	function addUser($id)
	{
		$this->tables['usergroupentries']->reset();
		$this->tables['usergroupentries']->id = 0;
		$this->tables['usergroupentries']->user_id = $id;
		$this->tables['usergroupentries']->group_id = $this->_id;
		if ($this->tables['usergroupentries']->check() && $this->tables['usergroupentries']->store()) {
			return 1;
		}
		if (pref('debug')) _warn("WARN", JText::sprintf("Error adding user %d to group %d: ", $id, $this->_id) . $this->tables['usergroupentries']->getError());
		return 0;
	}

	/**
	 * Remove a user from a group
	 *
	 * @param int $id user id
	 * @return boolean
	 */
	function deleteUser($id)
	{
		$query = "DELETE FROM #__prenotown_user_group_entries WHERE user_id = $id AND group_id = " . $this->_id;
		_log_sql($query);
		$this->db->setQuery($query);
		return $this->db->query();
	}

	/**
	 * Return all the fees associated to a group
	 *
	 * @return array
	 */
	function getFees()
	{
		$fees = array();
		$query = "
SELECT #__prenotown_time_cost_function_fee.*,
	#__prenotown_resource.name AS resource_name,
	#__prenotown_resource.address AS resource_address
FROM #__prenotown_time_cost_function_fee
JOIN #__prenotown_resource
	ON #__prenotown_resource.id = #__prenotown_time_cost_function_fee.resource_id
JOIN #__prenotown_time_cost_function_fee_groups
	ON #__prenotown_time_cost_function_fee_groups.fee_id = #__prenotown_time_cost_function_fee.id
WHERE #__prenotown_time_cost_function_fee_groups.group_id = $this->_id
ORDER BY resource_name ASC";
		_log_sql($query);
		$this->db->setQuery($query);
		return $this->db->loadAssocList();
	}

	/**
	 * Returns the rules of provided fee applied on this group
	 *
	 * @param int $fee_id the id of the fee
	 * @return array()
	 */
	function getFeeRulesByFee($fee_id)
	{
		$rules = array();
		$query = "
SELECT *, TIME_TO_SEC(upper_limit) AS upper_limit_seconds
FROM #__prenotown_time_cost_function_fee_rules
JOIN #__prenotown_time_cost_function_fee_groups
	ON #__prenotown_time_cost_function_fee_groups.fee_id = #__prenotown_time_cost_function_fee_rules.fee_id
WHERE #__prenotown_time_cost_function_fee_rules.fee_id = $fee_id
	AND #__prenotown_time_cost_function_fee_groups.group_id = $this->_id
ORDER BY upper_limit ASC";
		_log_sql($query);
		$this->db->setQuery($query);
		return $this->db->loadAssocList();
	}

	/**
	 * Returns the rules on provided resource applied on this group
	 *
	 * @param int $resource_id the id of the resource
	 * @return array()
	 */
	function getFeeRulesByResource($resource_id)
	{
		$rules = array();
		$query = "
SELECT *, TIME_TO_SEC(upper_limit) AS upper_limit_seconds
FROM #__prenotown_time_cost_function_fee_rules
JOIN #__prenotown_time_cost_function_fees
	ON #__prenotown_time_cost_function_fee_rules.fee_id = #__prenotown_time_cost_function_fee.id
JOIN #__prenotown_time_cost_function_fee_groups
	ON #__prenotown_time_cost_function_fee.id = #__prenotown_time_cost_function_fee_groups.fee_id
WHERE #__prenotown_time_cost_function_fee.resource_id = $resource_id
	AND #__prenotown_time_cost_function_fee_groups.group_id = $this->_id
ORDER BY upper_limit ASC";
		_log_sql($query);
		$this->db->setQuery($query);
		return $this->db->loadAssocList();
	}
}
?>
