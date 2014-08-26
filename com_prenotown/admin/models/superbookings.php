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
 * Superbookings model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelSuperbookings extends PrenotownModelPrenotowns
{
	/**
	 * Constructor, builds object
	 *
	 */
	function __construct() {
		global $mainframe, $option, $prenotown_user;

		parent::__construct();

		$this->setTableName('#__prenotown_superbooking');
		$this->setSortableFields(array('#__prenotown_superbooking.begin', '#__prenotown_superbooking.id'));
		$this->setFilterField('#__prenotown_resource.name');
		$this->setOrderingField('begin');
		$this->setQuery("SELECT %%%TABLE_NAME%%%.*, #__prenotown_resource.name AS resource_name, #__prenotown_resource.address AS resource_address, #__prenotown_payments.amount, #__prenotown_payments.method, #__prenotown_payments.date, #__users.id AS user_id, #__users.name AS user_name, #__prenotown_user_complement.social_security_number FROM %%%TABLE_NAME%%% JOIN #__prenotown_resource ON #__prenotown_resource.id = %%%TABLE_NAME%%%.resource_id LEFT JOIN #__prenotown_payments ON #__prenotown_payments.id = %%%TABLE_NAME%%%.payment_id JOIN #__users ON %%%TABLE_NAME%%%.user_id = #__users.id JOIN #__prenotown_user_complement ON #__prenotown_user_complement.id = #__users.id");
		$this->addFilter("((group_id > 100) OR (group_id <= 2))");

		$this->db =& JFactory::getDBO();
	}

	function __tostring() {
		return "PrenotownModelSuperbookings";
	}

	/**
	 * Load all the booking on resources related to admin $id
	 *
	 * @param int $id the admin id
	 * @return array
	 */
	function getByAdmin($id)
	{
		$id = intval($id);

		$this->setQuery("SELECT %%%TABLE_NAME%%%.*, #__prenotown_resource.name AS resource_name, #__prenotown_resource.address AS resource_address, #__prenotown_payments.amount, #__prenotown_payments.method, #__prenotown_payments.date, #__users.id AS user_id, #__users.name AS user_name, #__prenotown_user_complement.social_security_number, #__prenotown_user_complement.address AS user_address FROM %%%TABLE_NAME%%% JOIN #__prenotown_resource ON #__prenotown_resource.id = %%%TABLE_NAME%%%.resource_id LEFT JOIN #__prenotown_payments ON #__prenotown_payments.id = %%%TABLE_NAME%%%.payment_id JOIN #__users ON %%%TABLE_NAME%%%.user_id = #__users.id JOIN #__prenotown_user_complement ON #__prenotown_user_complement.id = #__users.id");
		$this->addFilter("#__prenotown_resource.admin_id = $id");
		$this->addFilter("%%%TABLE_NAME%%%.end > NOW()");
		$this->addFilter("%%%TABLE_NAME%%%.approved = FALSE");

		$this->_buildQuery();
		return $this->getData();
	}

	/**
	 * Load all the booking on resources related to user $id
	 *
	 * @param int $id the user id
	 * @return array
	 */
	function getByUser($id, $include_limit=0)
	{
		$id = intval($id);

		$this->setQuery("SELECT %%%TABLE_NAME%%%.*, #__prenotown_resource.name AS resource_name, #__prenotown_resource.address AS resource_address, #__prenotown_payments.amount, #__prenotown_payments.method, #__prenotown_payments.date, #__users.id AS user_id, #__users.name AS user_name, #__prenotown_user_complement.social_security_number, #__prenotown_user_complement.address AS user_address FROM %%%TABLE_NAME%%% JOIN #__prenotown_resource ON #__prenotown_resource.id = %%%TABLE_NAME%%%.resource_id LEFT JOIN #__prenotown_payments ON #__prenotown_payments.id = %%%TABLE_NAME%%%.payment_id JOIN #__users ON %%%TABLE_NAME%%%.user_id = #__users.id JOIN #__prenotown_user_complement ON #__prenotown_user_complement.id = #__users.id");
		if (_has_ghost_group()) {
			$this->addFilter("#__prenotown_superbooking.group_id = " . _has_ghost_group());
		} else {
			$this->addFilter("#__prenotown_superbooking.user_id = $id");
		}

		$this->_buildQuery($include_limit);
		$bookings = $this->getData($include_limit);

		for ($i = 0; $i < count($bookings); $i++) {
			$query = "SELECT * FROM #__prenotown_superbooking_exception WHERE booking_id = " . $bookings[$i]['id'] . " ORDER BY exception_date ASC";
			_log_sql($query);
			$this->db->setQuery($query);
			$bookings[$i]['exceptions'] = $this->db->loadAssocList();
		}

		return $bookings;
	}

	/**
	 * Load all the booking on a single resource, optionally limiting
	 * selected period between begin and end
	 *
	 * @param int $resource_id the ID of the resource
	 * @param string $begin the begin date as "YYYY-MM-DD HH:MM:SS"
	 * @param string $end the end date as "YYYY-MM-DD HH:MM:SS"
	 * @return the booking as an associative array
	 */
	function getByResource($resource_id, $begin=null, $end=null)
	{
		$result = array();

		$resource_id = intval($resource_id);

		if (!$resource_id) {
			return $result;
		}

		if ($begin and !preg_match('/^\d\d\d\d-\d\d-\d\d(\s\d\d(:\d\d(:\d\d)?)?)?$/', $begin)) {
			_log("WARNING", "Wrong begin date in superbookings::getByResoource(): $begin");
			$begin = null;
		}

		if ($end and !preg_match('/^\d\d\d\d-\d\d-\d\d(\s\d\d(:\d\d(:\d\d)?)?)?$/', $end)) {
			_log("WARNING", "Wrong end date in superbookings::getByResoource(): $end");
			$end = null;
		}

		$this->db->setQuery($query = "SELECT %%%TABLE_NAME%%%.*, #__prenotown_resource.name AS resource_name, #__prenotown_resource.address AS resource_address, #__prenotown_payments.amount, #__prenotown_payments.method, #__prenotown_payments.date, #__users.id AS user_id, #__users.name AS user_name, #__prenotown_user_complement.social_security_number, #__prenotown_user_complement.address AS user_address FROM %%%TABLE_NAME%%% JOIN #__prenotown_resource ON #__prenotown_resource.id = %%%TABLE_NAME%%%.resource_id LEFT JOIN #__prenotown_payments ON #__prenotown_payments.id = %%%TABLE_NAME%%%.payment_id JOIN #__users ON %%%TABLE_NAME%%%.user_id = #__users.id JOIN #__prenotown_user_complement ON #__prenotown_user_complement.id = #__users.id");

		$this->addFilter("#__prenotown_resource.id = $resource_id");

		if ($begin and $end) {
			$this->addFilter("%%%TABLE_NAME%%%.begin < $end");
			$this->addFilter("%%%TABLE_NAME%%%.end > $begin");
		}
		
		return $this->getData();
	}

	/**
	 * Load bookings by resource id and date
	 *
	 * @param int $resource_id the resource id
	 * @param string $date booking date
	 * @param string $end_date booking end date (if null is set to $date)
	 * @param string $order_by order booking by this field (defaults to "begin")
	 * @return array
	 */
	function getBookingsByResourceAndRange($resource_id, $date, $end_date="", $order_by="begin", $approved=true)
	{
		$resource_check = "";

		if ($resource_id) {
			$resource_model =& JModel::getInstance('Resource', 'PrenotownModel');
			$resource_model->setId($resource_id);
			$resources = $resource_model->getRelatedResources(1, 0, 1, 1);
			$rid = array();
			foreach ($resources as $r) {
				$rid[] = $r['id'];
			}
			$rid[] = $resource_id;
			$resource_check = "resource_id IN (" . implode(",", $rid) . ") AND ";
		} else {
			$resource_id = "";
		}

		if (!$end_date) {
			$end_date = $date;
		}

		$approved_check = $approved ? " AND #__prenotown_superbooking.approved IS TRUE" : "";

		$query = "
SELECT #__prenotown_superbooking.id, payment_id, user_id, group_id, cost,

        DATE(begin) AS begin_date,
        TIME(begin) AS begin_time,
        HOUR(begin) * 60 + minute(begin) AS begin,

        DATE(end) AS end_date,
        TIME(end) AS end_time,
        HOUR(end) * 60 + minute(begin) AS end,

        periodic,
	periodicity,

	#__prenotown_resource.id AS resource_id,
	#__prenotown_resource.name AS resource_name,
	#__prenotown_resource.address AS resource_address,
	#__prenotown_user_groups.name AS group_name,
	#__users.name AS user_name,
	#__prenotown_user_complement.social_security_number,
	CONCAT(
		#__prenotown_user_complement.address, ' ',
		#__prenotown_user_complement.ZIP, ' ',
		#__prenotown_user_complement.town, ' (',
		#__prenotown_user_complement.district, ')'
	) AS user_address
FROM #__prenotown_superbooking
JOIN #__prenotown_resource ON #__prenotown_resource.id = #__prenotown_superbooking.resource_id
JOIN #__users ON #__users.id = #__prenotown_superbooking.user_id
JOIN #__prenotown_user_complement ON #__prenotown_user_complement.id = #__prenotown_superbooking.user_id
LEFT JOIN #__prenotown_user_groups ON #__prenotown_user_groups.id = #__prenotown_superbooking.group_id
WHERE $resource_check
        date('$end_date') >= date(begin)
        AND date('$date') <= date(end)
        AND (periodic = 0 OR #__prenotown_day_bitmask('$date', '$end_date') & periodicity)
	$approved_check
ORDER BY $order_by";
		_log_sql($query);
		$this->db->setQuery($query);
		$bookings = $this->db->loadAssocList();

		if (!is_array($bookings)) {
			$bookings = array();
		}

		return $bookings;
	}

	/**
	 * Load bookings by group id, resource id and date
	 *
	 * @param int $group_id the group id
	 * @param int $resource_id the resource id
	 * @param string $date booking date
	 * @return array
	 */
	function getBookingsByGroupAndResourceAndRange($group_id, $resource_id, $date, $end_date="", $order_by="begin")
	{
		$all_bookings = $this->getBookingsByResourceAndRange($resource_id, $date, $end_date, $order_by);

		$bookings = array();

		if ($group_id) {
			foreach ($all_bookings as $b) {
				if ($b['group_id'] == $group_id) {
					$bookings[] = $b;
				}
			}
		} else {
			$bookings = $all_bookings;
		}

		return $bookings;
	}
}
?>
