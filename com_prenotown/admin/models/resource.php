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
 * Resource model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelResource extends PrenotownModelPrenotown
{
	/**
	 * Constructor, builds object and determines the foobar ID
	 *
	 */
	function __construct() {
		/* calling parent constructor */
		parent::__construct();

		/* loading tables */
		$this->addTable('resource', true);	// this is also saved as 'main' table
		$this->addTable('resourceComponents');
		$this->addTable('resourceDependencies');
		$this->addTable('resourceAttachment');
		$this->addTable('superbooking');
		$this->addTable('payments');
		$this->addTable('costFunction');
	}

	function __tostring()
	{
		return "PrenotownModelResource";
	}

	/**
	 * Create a new resource into the DB
	 *
	 * @param array $properies all the keys that define a resource
	 * @return boolean
	 */
	public function createResource(array $properties)
	{
		$this->tables['resource']->reset();

		foreach ($properties as $key => $value) {
			switch ($key) {
				case 'deadline':
					$this->setDeadline($value);
					break;
				case 'max_advance':
					$this->setMaxAdvance($value);
					break;
				case 'paying_period':
					$this->setPayingPeriod($value);
					break;
				case 'approval_period':
					$this->setApprovalPeriod($value);
					break;
				default:
					$this->tables['resource']->$key = $value;
					break;
			}
		}

		$this->tables['resource']->id = null;
		if ($this->tables['resource']->check() && $this->tables['resource']->store()) {
			return $this->tables['resource']->id;
		}

		return false;
	}

	/**
	 * Resets the resource ID and data
	 *
	 * @param int $id resource ID
	 */
	function setId($id) {
		parent::setId($id);

		if (isset($this->tables['resource'])) {
			$this->tables['resource']->reset();
			$this->tables['resource']->id = $id;
			$this->tables['resource']->load();

			$this->tables['costFunction']->reset();
			$this->tables['costFunction']->id = $this->tables['resource']->cost_function_id;
			$this->tables['costFunction']->load();

			/*

			# transform SQL TIME values into days (as integers)
			$time = preg_split("/:/", $this->tables['resource']->deadline);
			$this->deadline = $time[0] / 24;

			$time = preg_split("/:/", $this->tables['resource']->max_advance);
			$this->max_advance = $time[0] / 24;

			$time = preg_split("/:/", $this->tables['resource']->paying_period);
			$this->paying_period = $time[0] / 24;

			$time = preg_split("/:/", $this->tables['resource']->approval_period);
			$this->approval_period = $time[0] / 24;

			*/
		}
	}

	/**
	 * Set deadline period in days
	 *
	 * @param int $days number of days
	 */
	function setDeadline($days) {
		$this->tables['resource']->deadline = $days; // * 24 . ":00:00";
	}

	/**
	 * Set max advance period in days
	 *
	 * @param int $days number of days
	 */
	function setMaxAdvance($days) {
		$this->tables['resource']->max_advance = $days; // * 24 . ":00:00";
	}

	/**
	 * Set paying period in days
	 *
	 * @param int $days number of days
	 */
	function setPayingPeriod($days) {
		$this->tables['resource']->paying_period = $days; // * 24 . ":00:00";
	}

	/**
	 * Set approval period in days
	 *
	 * @param int $days number of days
	 */
	function setApprovalPeriod($days) {
		$this->tables['resource']->approval_period = $days; // * 24 . ":00:00";
	}

	/**
	 * Return value of a field
	 *
	 * @param string $field field name
	 * @return mixed
	 */
	function get($field) {
		return $this->tables['resource'][$field];
	}

	/**
	 * Set a field to a value
	 *
	 * @param string $field field name
	 * @param mixed $value field value
	 * @return mixed
	 */
	function set($field, $value) {
		return $this->tables['resource'][$field] = $value;
	}

	/**
	 * Return a time in milliseconds
	 *
	 * @param string $time an arbitrary type in format HH:MM:SS
	 * @return int
	 */
	function getTimeInMillisec($time) {
		$matches = Array();

		if (preg_match('/(\d+):(\d+):(\d+)/', $time, $matches)) {
			return $matches[1] * 60 * 60 * 1000 + $matches[2] * 60 * 1000 + $matches[2] * 1000;
		}

		return null;
	}

	/**
	 * Return max advance period in milliseconds
	 *
	 * @return int
	 */
	function getMaxAdvanceInMillisec() {
		return $this->tables['resource']->max_advance * 24 * 60 * 60 * 1000;
		return $this->getTimeInMillisec($this->tables['resource']->max_advance);
	}

	/**
	 * Return deadline period in milliseconds
	 *
	 * @return int
	 */
	function getDeadlineInMillisec() {
		return $this->tables['resource']->deadline * 24 * 60 * 60 * 1000;
		return $this->getTimeInMillisec($this->tables['resource']->deadline);
	}

	/**
	 * Return approval period in milliseconds
	 *
	 * @return int
	 */
	function getApprovalPeriodInMillisec() {
		return $this->tables['resource']->approval_period * 24 * 60 * 60 * 1000;
		return $this->getTimeInMillisec($this->tables['resource']->approval_period);
	}

	/**
	 * Return paying period in milliseconds
	 *
	 * @return int
	 */
	function getPayingPeriodInMillisec() {
		return $this->tables['resource']->paying_period * 24 * 60 * 60 * 1000;
		return $this->getTimeInMillisec($this->tables['resource']->paying_period);
	}

	/**
	 * return resources that compose this one
	 *
	 * @return array
	 */
	function getComposingResources() {
		$query = "SELECT #__prenotown_resource.* from #__prenotown_resource JOIN #__prenotown_resource_components ON #__prenotown_resource.id = #__prenotown_resource_components.component_resource_id WHERE #__prenotown_resource_components.composed_resource_id = $this->_id";
		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();
		$result = $this->db->loadAssocList();
		if (!is_array($result)) {
			$result = Array();
		}
		return $result;
	}

	/**
	 * return resources composed by this one
	 */
	function getComposedResources() {
		$query = "SELECT #__prenotown_resource.* from #__prenotown_resource JOIN #__prenotown_resource_components ON #__prenotown_resource.id = #__prenotown_resource_components.composed_resource_id WHERE #__prenotown_resource_components.component_resource_id = $this->_id";
		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();
		$result = $this->db->loadAssocList();
		if (!is_array($result)) {
			$result = Array();
		}
		return $result;
	}

	/**
	 * return resources this one depends on
	 *
	 * @return array
	 */
	function getMasterResources() {
		$query = "SELECT #__prenotown_resource.* from #__prenotown_resource JOIN #__prenotown_resource_dependencies ON #__prenotown_resource.id = #__prenotown_resource_dependencies.master_resource_id WHERE #__prenotown_resource_dependencies.slave_resource_id = $this->_id";
		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();
		$result = $this->db->loadAssocList();
		if (!is_array($result)) {
			$result = Array();
		}
		return $result;
	}

	/**
	 * return resources depending on this one
	 *
	 * @return array
	 */
	function getSlaveResources() {
		$query = "SELECT #__prenotown_resource.* from #__prenotown_resource JOIN #__prenotown_resource_dependencies ON #__prenotown_resource.id = #__prenotown_resource_dependencies.slave_resource_id WHERE #__prenotown_resource_dependencies.master_resource_id = $this->_id";
		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();
		$result = $this->db->loadAssocList();
		if (!is_array($result)) {
			$result = Array();
		}
		return $result;
	}

	/**
	 * return all the resources related to this one
	 *
	 * @return related resources as associative array
	 */
	function getRelatedResources($master=1, $slave=1, $composing=1, $composed=1)
	{
		$related_resources = array();

		if ($master) {
			$master_resources = $this->getMasterResources();
			while (list(,$v) = each($master_resources)) { $related_resources[] = $v; }
		}

		if ($slave) {
			$slave_resources = $this->getSlaveResources();
			while (list(,$v) = each($slave_resources)) { $related_resources[] = $v; }
		}

		if ($composing) {
			$composing_resources = $this->getComposingResources();
			while (list(,$v) = each($composing_resources)) { $related_resources[] = $v; }
		}

		if ($composed) {
			$composed_resources = $this->getComposedResources();
			while (list(,$v) = each($composed_resources)) { $related_resources[] = $v; }
		}

		return $related_resources;
	}

	/**
	 * return full informations about all resources not related to this one
	 * (that's any resource which is not composing this one, composed by this
	 * one, master or slave of this one)
	 *
	 * @return array
	 */
	function getUnrelatedResources() {
		$sub1 = "SELECT composed_resource_id FROM #__prenotown_resource_components WHERE component_resource_id = $this->_id";
		$sub2 = "SELECT component_resource_id FROM #__prenotown_resource_components WHERE composed_resource_id = $this->_id";
		$sub3 = "SELECT master_resource_id FROM #__prenotown_resource_dependencies WHERE slave_resource_id = $this->_id";
		$sub4 = "SELECT slave_resource_id FROM #__prenotown_resource_dependencies WHERE master_resource_id = $this->_id";

		$query = "SELECT * FROM #__prenotown_resource WHERE id NOT IN ($sub1) AND id NOT IN ($sub2) AND id NOT IN ($sub3) AND id NOT IN ($sub4) AND id != $this->_id ORDER BY name ASC";

		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();

		$result = $this->db->loadAssocList();
		if (!is_array($result)) {
			$result = Array();
		}

		return $result;
	}

	/**
	 * add an attachment to this resource
	 *
	 * @param string $filename filename of the new attachment
	 * @param string $name name of the attachment, shown in the resource view
	 * @return boolean
	 */
	function add_attachment($filename, $name)
	{
		$this->tables['resourceAttachment']->reset();
		$this->tables['resourceAttachment']->filename = $filename;
		$this->tables['resourceAttachment']->name = $name;
		$this->tables['resourceAttachment']->resource_id = $this->_id;
		
		if ($this->tables['resourceAttachment']->check() && $this->tables['resourceAttachment']->store()) {
			return true;
		}
		if (pref('debug')) _warn("WARN", $this->tables['resourceAttachment']->getError());
		return false;
	}

	/**
	 * Propagate availability range from this resource to all composing resources
	 */
	function propagate_availability_range()
	{
		$composing = $this->getComposingResources();
		foreach ($composing as $r) {
			$sql = "UPDATE #__prenotown_resource SET availability_enabled = " . $this->tables['resource']->availability_enabled .
				", monday_begin = '" . $this->tables['resource']->monday_begin .
				"', monday_end = '" . $this->tables['resource']->monday_end .
				"', tuesday_begin = '" . $this->tables['resource']->tuesday_begin .
				"', tuesday_end = '" . $this->tables['resource']->tuesday_end .
				"', wednesday_begin = '" . $this->tables['resource']->wednesday_begin .
				"', wednesday_end = '" . $this->tables['resource']->wednesday_end .
				"', thursday_begin = '" . $this->tables['resource']->thursday_begin .
				"', thursday_end = '" . $this->tables['resource']->thursday_end .
				"', friday_begin = '" . $this->tables['resource']->friday_begin .
				"', friday_end = '" . $this->tables['resource']->friday_end .
				"', saturday_begin = '" . $this->tables['resource']->saturday_begin .
				"', saturday_end = '" . $this->tables['resource']->saturday_end .
				"', sunday_begin = '" . $this->tables['resource']->sunday_begin .
				"', sunday_end = '" . $this->tables['resource']->sunday_end .
				"' WHERE id = " . $r['id'];
			$this->db->setQuery($sql);
			$this->db->query();
		}
	}

	/**
	 * add a component to this resource
	 * 
	 * @param int $id id of the component to be added
	 * @return boolean
	 */
	function add_component($id)
	{
		$this->tables['resourceComponents']->component_resource_id = $id;
		$this->tables['resourceComponents']->composed_resource_id = $this->_id;
		if ($this->tables['resourceComponents']->check() && $this->tables['resourceComponents']->store()) {
			$this->propagate_availability_range();
			return true;
		}
		if (pref('debug')) _warn("WARN", $this->tables['resourceComponents']->getError());
		return false;
	}


	/**
	 * delete a component from this resource
	 *
	 * @param int $id id of the component to be removed
	 * @return boolean
	 */
	function delete_component($id)
	{
		if (!isset($id)) {
			return 0;
		}

		$query = "DELETE FROM #__prenotown_resource_components WHERE component_resource_id = $id AND composed_resource_id = $this->_id";
		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();
		return 1;
	}

	/**
	 * Delete an attachment from a resource
	 *
	 * @param int id the id of the attachment
	 * @return boolean
	 */
	function delete_attachment($id)
	{
		if (!isset($id)) {
			return 0;
		}

		$this->tables['resourceAttachment']->reset();
		if (!$this->tables['resourceAttachment']->delete($id)) {
			if (pref('debug')) _warn("WARN", JText::_("Error deleting attachment") . " $id: " . $this->tables['resourceAttachment']->getError());
			return false;
		}
		return true;
	}

	/**
	 * Add a dependency to this resource
	 *
	 * @param int $id the id of the slave resource
	 * @return boolean
	 */
	function add_dependence($id)
	{
		$this->tables['resourceDependencies']->slave_resource_id = $id;
		$this->tables['resourceDependencies']->master_resource_id = $this->_id;
		if ($this->tables['resourceDependencies']->check() && $this->tables['resourceDependencies']->store()) {
			return 1;
		} else {
			if (pref('debug')) _warn("WARN", $this->tables['resourceDependencies']->getError());
			return 0;
		}
	}

	/**
	 * Remove a dependence from this resource
	 *
	 * @param int $id Dependence resource id
	 * @return true
	 */
	function delete_dependence($id)
	{
		if (!isset($id)) {
			return 0;
		}

		$query = "DELETE FROM #__prenotown_resource_dependencies WHERE slave_resource_id = $id AND master_resource_id = $this->_id";
		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();
		return 1;
	}

	/**
	 * Add this resource into a category
	 *
	 * @param int $id category id
	 * @return true
	 */
	function add_category($id)
	{
		$query = "INSERT INTO #__prenotown_resource_group_entries (resource_id, group_id) values ($this->_id, $id)";
		_log("NOTICE", "User " . $prenotown_user['name'] . " is putting resource $this->_id into group $id");
		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();
		return 1;
	}

	/**
	 * Remove this resource from a category
	 *
	 * @param int $id category id
	 * @return true
	 */
	function delete_category($id)
	{
		$query = "DELETE FROM #__prenotown_resource_group_entries WHERE resource_id = $this->_id AND group_id = $id";
		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();
		return 1;
	}

	/**
	 * Provides all the categories this resource is member of
	 *
	 * @return array
	 */
	function getCategories()
	{
		$query = "SELECT #__prenotown_resource_groups.id, #__prenotown_resource_groups.name FROM #__prenotown_resource_groups JOIN #__prenotown_resource_group_entries ON #__prenotown_resource_groups.id = #__prenotown_resource_group_entries.group_id WHERE #__prenotown_resource_group_entries.resource_id = " . $this->_id;
		_log_sql($query);
		$this->db->setQuery($query);
		return $this->db->loadAssocList();
	}

	/**
	 * Provides all the categories this resource is not member of
	 *
	 * @return array
	 */
	function getUnrelatedCategories()
	{
		$sub1 = "SELECT DISTINCT group_id FROM #__prenotown_resource_group_entries WHERE resource_id = $this->_id";
		$query = "SELECT * FROM #__prenotown_resource_groups WHERE id NOT IN ($sub1) ORDER BY name ASC";

		_log_sql($query);
		$this->db->setQuery($query);
		return $this->db->loadAssocList();
	}

	/**
	 * Provides all the attachments attached to this resource
	 *
	 * @return array
	 */
	function getAttachments()
	{
		// load attachments
		$query = "SELECT * FROM #__prenotown_resource_attachment WHERE resource_id = $this->_id";
		_log_sql($query);
		$this->db->setQuery($query);
		$attachments = $this->db->loadAssocList();

		if (!is_array($attachments)) {
			return array();
		}
		return $attachments;
	}

	/**
	 * Provides all the attachment attached to this resource that match provided pattern
	 *
	 * @param string $pattern a regular expression to be matched on each attachment filename
	 * @return array
	 */
	function getAttachmentsFiltered($pattern)
	{
		if (!isset($pattern)) {
			return array();
		}

		$attachments = $this->getAttachments();
		$filtered = array();

		foreach ($attachments as $a) {
			if (preg_match($pattern, $a['filename'])) {
				$filtered[] = $a;
			}
		}

		return $filtered;
	}

	/**
	 * Provides all the images (subset of attachments) attached to this resource
	 *
	 * @return array
	 */
	function getImages()
	{
		return $this->getAttachmentsFiltered('/\.(jpeg|jpg|gif|png)$/');
	}

	/**
	 * Provides all the documents attached to this resource
	 *
	 * @return array
	 */
	function getDocuments()
	{
		return $this->getAttachmentsFiltered('/\.(doc|pdf|rtf|ps|txt|odt|xls|csv|html?)$/');
	}

	/**
	 * Provides all the attachments not attached to this resource
	 *
	 * @return array
	 */
	function getUnrelatedAttachments()
	{
		$attachments = array();

		if ($fh = opendir(JPATH_BASE . DS . "images" . DS . "prenotown" . DS . "attachments")) {
			while ($filename = readdir($fh)) {
				if (!preg_match('/^\./', $filename)) {
					$attachments[$filename] = 1;
				}
			}
			closedir($fh);

			var_dump($attachments);

			$query = "SELECT id, name, filename FROM #__prenotown_resource_attachment WHERE resource_id = $this->_id";
			_log_sql($query);
			$this->db->setQuery($query);
			$this->db->query();
			$attached = $this->db->loadAssocList();

			echo count($attached) . " attachments here!";

			foreach ($attached as $a) {
				echo "Excluding filename $filename";
				$filename = $a['filename'];
				unset($attachments[$filename]);
			}

			var_dump($attachments);

			$attachments = array_keys($attachments);
			sort($attachments);
		} else {
			if (pref('debug')) _warn("WARN", JText::_("Unable to open attachment dir") . ": " . JPATH_BASE . DS . "images" . DS . "prenotown" . DS . "attachments");
		}
		return $attachments;
	}

	/**
	 * Book this resource
	 *
	 * @param string $start The booking start date in format "DD-MM-YYYY HH:MM:SS"
	 * @param string $stop The booking stop date in format "DD-MM-YYYY HH:MM:SS"
	 * @param int $user_id The ID of the user that's booking
	 * @param int $group_id The ID of the group used to calculate the cost
	 * @param int $operator_id The ID of the operator doing the booking for the user, if any
	 * @param int $cost The cost of the booking
	 * @param string $method the method choosen to pay ('check', 'creditcard')
	 * @param boolean $periodic the booking is periodic (recurring)
	 * @param int $periodicity the periodicity bitmask
	 * @param array exceptions a list of SQL compliant dates to be saved as exceptions for a periodic booking
	 * @return int the id of the booking or 0
	 */
	function book($begin, $end, $user_id, $group_id, $operator_id, $cost, $method, $periodic=0, $periodicity=0, $exceptions=null)
	{
		$datetime_pattern = '/(\d\d\d\d)-(\d\d?)-(\d\d?) (\d\d?):((\d\d?)(:(\d\d?))?)?/';

		if (!preg_match($datetime_pattern, $begin)) {
			if (pref('debug')) _warn("WARN", JText::sprintf("Booking without begin date and time (%s)", $begin));
			return 0;
		}

		if (!preg_match($datetime_pattern, $end)) {
			if (pref('debug')) _warn("WARN", JText::sprintf("Booking without end date and time (%s)", $time));
			return 0;
		}

		if (!$cost) {
			if (!$periodic) {
				if (pref('debug')) _warn("WARN", JText::_("Wrong or null cost"));
				return 0;
			}
		}

		if (!$user_id) {
			if (pref('debug')) _warn("WARN", JText::_("No user_id provided"));
			return 0;
		}

		if ($method != 'check' AND $method != 'creditcard' AND $method != 'special') {
			if (pref('debug')) _warn("WARN", JText::_("No or wrong payment method provided") . " $method");
			return 0;
		}

		if (!$group_id) {
			$group_id = 1;
		}

		if (!$operator_id) {
			$operator_id = 'NULL';
		}

		// Add the payment entry (still not paid)
		$this->tables['payments']->reset();
		$this->tables['payments']->id = 0;
		$this->tables['payments']->user_id = $user_id;
		$this->tables['payments']->amount = $cost;
		$this->tables['payments']->method = $method;

		if (!($this->tables['payments']->check() && $this->tables['payments']->store())) {
			if (pref('debug')) {
				_warn("WARN", JText::_("Can't save your booking:") . " " . $this->tables['payments']->getError());
			} else {
				_warn("WARN", JText::_("Can't save your booking:") . " " . JText::_("perhaps you are booking twice the same resource?"));
			}
			return false;
		}
		$payment_id = $this->tables['payments']->id;

		// Add the booking
		$this->tables['superbooking']->reset();
		$this->tables['superbooking']->id = null;
		$this->tables['superbooking']->resource_id = $this->_id;
		$this->tables['superbooking']->user_id = $user_id;
		$this->tables['superbooking']->group_id = $group_id;
		$this->tables['superbooking']->operator_id = $operator_id;
		$this->tables['superbooking']->begin = $begin;
		$this->tables['superbooking']->end = $end;
		$this->tables['superbooking']->cost = $cost;
		$this->tables['superbooking']->payment_id = $payment_id;
		$this->tables['superbooking']->periodic = $periodic;
		$this->tables['superbooking']->periodicity = $periodicity;
		$this->tables['superbooking']->created = date("Y-m-d H:i:s");

		// Admin inserted bookings get automatic approval
		if (_status("admin")) {
			$this->tables['superbooking']->approved = 1;
		}

		if ($this->tables['superbooking']->check() && $this->tables['superbooking']->store()) {
			$id = $this->tables['superbooking']->id;
			_log("NOTICE", "BOOKING: [ID $id] [Resourceid " . $this->_id . "] [Userid $user_id] [Groupid $group_id] [Operid $operator_id] [Begin $begin] [End $end] [Cost $cost] [Payid $payment_id] [Periodic $periodic] [Periodicity $periodicity]");

			/* add provided exceptions if booking is periodic */
			if ($periodic && $exceptions != null && is_array($exceptions)) {
				foreach ($exceptions as $e) {
					$sql  = "INSERT INTO #__prenotown_superbooking_exception (booking_id, exception_date) VALUES ($id, ";
					$sql .= $this->db->quote($e) . ")";
					_log_sql($sql);
					$this->db->setQuery($sql);
					$this->db->query();
				}
			}
			return $id;
		} else {
			if (pref('debug')) {
				_warn("WARN", JText::_("Can't save your booking:") . " " . $this->tables['superbooking']->getError());
			} else {
				_warn("WARN", JText::_("Can't save your booking:") . " " . JText::_("perhaps you are booking twice the same resource?"));
			}
		}

		return 0;
	}

	/**
	 * Return an instance of a Cost Function object
	 * 
	 * @return object cost function object
	 */
	function getCostFunctionInstance()
	{
		$cf_class = $this->tables['costFunction']->class;
		return new $cf_class($this->_id);
	}
}
?>
