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
 * Bookings model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelBookings extends PrenotownModelPrenotowns
{
	/**
	 * Constructor, builds object
	 *
	 */
	function __construct() {
		global $mainframe, $option, $prenotown_user;

		parent::__construct();

		$this->setTableName('#__prenotown_booking');
		$this->setSortableFields(array('name', 'id'));
		$this->setFilterField('begin');
		$this->setOrderingField('begin');
		$this->setQuery("SELECT %%%TABLE_NAME%%%.id AS id, %%%TABLE_NAME%%%.resource_id, %%%TABLE_NAME%%%.payment_id, %%%TABLE_NAME%%%.start, %%%TABLE_NAME%%%.stop, %%%TABLE_NAME%%%.approved, #__prenotown_resource.name AS resource_name, #__prenotown_resource.address AS resource_address, #__prenotown_payments.amount, #__prenotown_payments.method, #__prenotown_payments.date, #__users.id AS user_id, #__users.name AS user_name, #__prenotown_user_complement.social_security_number FROM %%%TABLE_NAME%%% JOIN #__prenotown_resource ON #__prenotown_resource.id = %%%TABLE_NAME%%%.resource_id LEFT JOIN #__prenotown_payments ON #__prenotown_payments.id = %%%TABLE_NAME%%%.payment_id JOIN #__users ON %%%TABLE_NAME%%%.user_id = #__users.id JOIN #__prenotown_user_complement ON #__prenotown_user_complement.id = #__users.id GROUP BY #__prenotown_resource.id ORDER BY #__prenotown_resource.name ASC");
	}

	function __tostring() {
		return "PrenotownModelBookings";
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

		$this->setQuery("SELECT %%%TABLE_NAME%%%.id AS id, %%%TABLE_NAME%%%.resource_id, %%%TABLE_NAME%%%.payment_id, %%%TABLE_NAME%%%.start, %%%TABLE_NAME%%%.stop, %%%TABLE_NAME%%%.approved, #__prenotown_resource.name AS resource_name, #__prenotown_resource.address AS resource_address, #__prenotown_payments.amount, #__prenotown_payments.method, #__prenotown_payments.date, #__users.id AS user_id, #__users.name AS user_name, #__prenotown_user_complement.social_security_number FROM %%%TABLE_NAME%%% JOIN #__prenotown_resource ON #__prenotown_resource.id = %%%TABLE_NAME%%%.resource_id LEFT JOIN #__prenotown_payments ON #__prenotown_payments.id = %%%TABLE_NAME%%%.payment_id JOIN #__users ON %%%TABLE_NAME%%%.user_id = #__users.id JOIN #__prenotown_user_complement ON #__prenotown_user_complement.id = #__users.id WHERE #__prenotown_resource.admin_id = $id AND %%%TABLE_NAME%%%.stop > NOW() AND %%%TABLE_NAME%%%.approved = FALSE GROUP BY #__prenotown_resource.id ORDER BY #__prenotown_resource.name ASC");

		$this->_buildQuery();
		return $this->getData();
	}

	/**
	 * Load all the booking on a single resource, optionally limiting
	 * selected period between start and stop
	 *
	 * @param int $resource_id the ID of the resource
	 * @param string $start the start date as "YYYY-MM-DD HH:MM:SS"
	 * @param string $stop the stop date as "YYYY-MM-DD HH:MM:SS"
	 * @return the booking as an associative array
	 */
	function getByResource($resource_id, $start=null, $stop=null)
	{
		$result = array();

		$resource_id = intval($resource_id);

		if (!$resource_id) {
			return $result;
		}

		if ($start and !preg_match('/^\d\d\d\d-\d\d-\d\d(\s\d\d(:\d\d(:\d\d)?)?)?$/', $start)) {
			_log("WARNING", "Wrong start date in bookings::getByResoource(): $start");
			$start = null;
		}

		if ($stop and !preg_match('/^\d\d\d\d-\d\d-\d\d(\s\d\d(:\d\d(:\d\d)?)?)?$/', $stop)) {
			_log("WARNING", "Wrong stop date in bookings::getByResoource(): $stop");
			$stop = null;
		}

		$this->db->setQuery($query = "SELECT %%%TABLE_NAME%%%.*, #__prenotown_resource.name AS resource_name, #__prenotown_resource.address AS resource_address, #__prenotown_payments.amount, #__prenotown_payments.method, #__prenotown_payments.date, #__users.id AS user_id, #__users.name AS user_name, #__prenotown_user_complement.social_security_number FROM %%%TABLE_NAME%%% JOIN #__prenotown_resource ON #__prenotown_resource.id = %%%TABLE_NAME%%%.resource_id LEFT JOIN #__prenotown_payments ON #__prenotown_payments.id = %%%TABLE_NAME%%%.payment_id JOIN #__users ON %%%TABLE_NAME%%%.user_id = #__users.id JOIN #__prenotown_user_complement ON #__prenotown_user_complement.id = #__users.id");

		$this->addFilter("#__prenotown_resource.id = $resource_id");

		if ($start and $stop) {
			$this->addFilter("%%%TABLE_NAME%%%.start < $stop");
			$this->addFilter("%%%TABLE_NAME%%%.stop > $start");
		}
		
		return $this->getData();
	}
}

