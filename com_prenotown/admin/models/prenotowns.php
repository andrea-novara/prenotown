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

/**
 * Prenotowns model
 * This base class is extended by all the models that rappresent a
 * list of all the entries contained inside the database.
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelPrenotowns extends JModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	var $_table_name = null;

	/**
	 * Data loaded from the database
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * DB link
	 *
	 * @var object
	 */
	var $_db = null;

	/**
	 * Restrict SELECT by name
	 */
	var $_filter = null;

	/**
	 * The field to be matched with $_filter
	 *
	 * @var string
	 */
	var $_filter_field = null;

	/**
	 * A field to be used as ordering field
	 *
	 * @var string
	 */
	var $_ordering_field = null;

	/**
	 * Sortable fields
	 *
	 * @var array
	 */
	var $_sortable_fields = null;

	/**
	 * SQL query to retrieve data
	 *
	 * @var string
	 */
	var $_default_query = "SELECT %%%TABLE_NAME%%%.* FROM %%%TABLE_NAME%%%";
	var $_query = "SELECT %%%TABLE_NAME%%%.* FROM %%%TABLE_NAME%%%";

	/**
	 * Additional filters
	 *
	 * @var array
	 */
	var $_filters = array();

	/**
	 * Additional joins required by additional filters
	 *
	 * @var array
	 */
	var $_joins = array();

	/**
	 * Constructor, builds object
	 */
	function __construct() {
		global $mainframe, $option;

		parent::__construct();

		$this->_db =& JFactory::getDBO();

		$this->_published = null;

		// get the pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'));
		$limitstart = $mainframe->getUserStateFromRequest($option.'limitstart', 'limitstart', 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$this->_lists = array();
		$filter_order = strtolower($mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', 'published'));
		$filter_order_Dir = strtoupper($mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', 'ASC'));
		$filter_state = $mainframe->getUserStateFromRequest($option.'filter_state', 'filter_state');
		$filter_search = $this->_filter ? $this->_filter : $mainframe->getUserStateFromRequest($option.'filter_search', 'filter_search');

		$this->_lists['order_Dir'] = $filter_order_Dir;
		$this->_lists['order'] = $filter_order;
		$this->_lists['search'] = $filter_search;
		$this->_lists['state'] = JHTML::_('grid.state', $filter_state);
	}

	function __tostring() {
		return "PrenotownModelPrenotowns";
	}

	/**
	 * Reset all the internal fields to prepare the class to another query
	 */
	function reset()
	{
		$this->_filter = null;
		$this->_filter_field = null;
		$this->_ordering_field = null;
		$this->_filters = array();
		$this->_joins = array();
		$this->_query = $this->_default_query;
	}

	/**
	 * Set the default SQL query
	 *
	 * @param string $query the SQL query
	 */
	protected function setDefaultQuery($query)
	{
		if (isset($query)) {
			$this->_default_query = $this->_query = $query;
		}
	}

	/**
	 * Set the SQL query
	 *
	 * @param string $query the SQL query
	 */
	function setQuery($query)
	{
		if (isset($query)) {
			$this->_query = $query;
		}
	}

	/**
	 * Set the name of the table hosting this object
	 *
	 * @param string $table_name the name of the table
	 */
	function setTableName($table_name)
	{
		if (isset($table_name)) {
			$this->_table_name = $table_name;
		}
	}

	/**
	 * Set the fields which can be used to sort data
	 *
	 * @param array $fields the list of the field names
	 */
	function setSortableFields($fields)
	{
		if (is_array($fields)) {
			$this->_sortable_fields = $fields;
		}
	}

	/**
	 * Set the field that will be used to filter contents
	 *
	 * @param string $field the name of the field
	 */
	function setFilterField($field)
	{
		if (isset($field)) {
			$this->_filter_field = $field;
		}
	}

	/**
	 * Set the filter used with _filter_field to filter the results
	 *
	 * @param string $filter the filter to be used
	 */
	function setFilter($filter)
	{
		if (isset($filter)) {
			$this->_filter = preg_replace('/([\'\\"])/', "$1", $filter);
		}
	}

	/**
	 * Set the field used as ordering key
	 *
	 * @param string $field the field to be set
	 */
	function setOrderingField($field)
	{
		if (isset($field)) {
			$this->_ordering_field = $field;
		}
	}

	/**
	 * Add an additional filter to WHERE clause
	 *
	 * @param string $filter the filter
	 */
	function addFilter($filter, $join = "")
	{
		if (isset($filter)) {
			$this->_filters[] = $filter;
			if (strlen($join)) {
				$this->_joins[] = $join;
			}
		}
	}

	/**
	 * Format the SQL constraint to order results
	 *
	 * @return string The SQL clause ORDER BY 'field_name' [DESC|ASC]
	 */
	function _getOrderingSQL()
	{
		global $mainframe, $option;

		// get order field and direction field
		$filter_order = $this->_ordering_field ? $this->_ordering_field : strtolower($mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', 'published'));
		$filter_order_Dir = strtoupper($mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', 'ASC'));

		// validate
		if ($filter_order_Dir != 'ASC' && $filter_order_Dir != 'DESC') { $filter_order_Dir = "ASC"; }
		if (!in_array($filter_order, $this->_sortable_fields)) { $filter_order = $this->_sortable_fields[0]; }

		return " ORDER BY $filter_order $filter_order_Dir";
	}

	/**
	 * Format the SQL constraint for WHERE contitions
	 *
	 * @return string The SQL string WHERE field_name = "value"
	 */
	function _getWhereSQL()
	{
		global $mainframe, $option;

		// get state field from HTTP request
		$filter_state = $mainframe->getUserStateFromRequest($option.'filter_state', 'filter_state');
		$filter_search = $this->_filter ? $this->_filter : $mainframe->getUserStateFromRequest($option.'filter_search', 'filter_search');

		// prepare the clause
		$where = array();
		$join = array();

		if ($this->_filter_field and $filter_search) {
			if ($filter_search = trim($filter_search)) {
				$filter_search = JString::strtolower($filter_search);
				$filter_search = $this->_db->getEscaped($filter_search);
				$where[] = 'LOWER(' . $this->_filter_field . ') LIKE "%'.$filter_search.'%"';
			}
		}

		if ($this->_published != null) {
			$filter_state = $this->_published;
		}

		if ($filter_state == "P") {
			$where[] = "%%%TABLE_NAME%%%.published = 1";
		} elseif ($filter_state == "U") {
			$where[] = "%%%TABLE_NAME%%%.published = 0";
		}

		// add custom filters
		$where += $this->_filters;
		$join += $this->_joins;

		if (count($where)) {
			$string = "";
			if (count($join)) {
				$string .= " JOIN " . implode(' JOIN ', $join);
			}
			$string .= " WHERE " . implode(' AND ', $where);
			return $string;
		}

		return "";
	}

	/**
	 * Build the SQL query to retrieve elements
	 *
	 * @param int $include_limit if true, the "LIMIT N OFFSET M" is added to the query
	 * @return string The SQL query
	 */
	function _buildQuery($include_limit=false)
	{
		if (!isset($include_limit)) {
			$include_limit = false;
		}

		if (pref('debug')) _log("DEBUG", "building query with limit = $include_limit");
		if (!isset($this->_table_name)) {
			if (pref('debug')) _warn("WARN", $this->__tostring() . JText::_('->_buildQuery called without setting _table_name'));
			return "";
		}

		$limitstart = JRequest::getInt('limitstart', $this->getState('limitstart'));
		$limit = JRequest::getInt('limit', $this->getState('limit'));

		// copy the query
		$query = $this->_query;

		// add filter criteria
		$query .= $this->_getWhereSQL();

		// add ordering clause
		$query .= $this->_getOrderingSQL();

		// add limit (if requested)
		if ($include_limit && $limit) {
			$query .= " LIMIT $limit OFFSET $limitstart";
		}

		// replace %%%TABLE_NAME%%% with $this->_table_name value
		$query = preg_replace('/%%%TABLE_NAME%%%/', $this->_table_name, $query);

		return $query;
	}

	/**
	 * Count the elements of the standard query
	 *
	 * @return int the number of elements
	 */
	function _getTotal()
	{
		if(empty($this->_total)) {
			$query = $this->_buildQuery(false);
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	/**
	 * Prepare the pagination code (that function shouldn't be placed inside a JView???)
	 *
	 * @return string the HTML pagination code
	 */
	function getPagination()
	{
		$total = $this->_getTotal();
		$limitstart = $this->getState('limitstart');
		$limit = $this->getState('limit');
		
		$this->_pagination = new JPagination($total, $limitstart, $limit);
		return $this->_pagination;
	}

	/**
	 * Loads and returns the data
	 *
	 * @return array An ordered list of associative array describing the lines of the table
	 */
	function getData($include_limit=0)
	{
		if (!isset($include_limit)) {
			$include_limit = 0;
		}

		$query = $this->_buildQuery($include_limit);

		// if (pref('debug')) _warn("WARN", preg_replace('/#__/', 'jos_', $query));
		_log_sql($query);
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadAssocList();

		if (!is_array($this->_data)) {
			$this->_data = array();
		}

		return $this->_data;
	}
}
?>
