<?php
/**
 * @package Prenotown
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** ensure a valid entry point */
defined('_JEXEC') or die("Restricted Access");

/** import logging facilities */
require_once(JPATH_COMPONENT.DS."assets".DS."logging.php");

/** import session */
require_once(JPATH_COMPONENT.DS."assets".DS."user_session.php");

/**
 * Time based cost function, supporting periodic booking,
 * availability range, and several fee per resource
 *
 * @version	0.2
 * @package	Prenotown
 * @subpackage	CostFunctions
 * @copyright	XSec
 * @license	GNU/GPL, see LICENSE.php
 */

/**
 * Time cost function fee table
 *
 * @package	Prenotown
 * @subpackage	Tables
 */
class TableTimeCostFunctionFee extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var varchar name */
	var $name = '';

	/** @var int Foreign Key */
	var $resource_id = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_time_cost_function_fee', 'id', $db);
	}

	function __toString()
	{
		return "PrenotownTableTimeCostFunctionFee";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		if (!$this->name) {
			$this->setError(JText::_('No name provided'));
			return false;
		}

		if (!$this->resource_id) {
			$this->setError(JText::_('No resource_id provided'));
			return false;
		}

		return true;
	}
}

/**
 * Time cost function fee <-> group table
 *
 * @package	Prenotown
 * @subpackage	Tables
 */
class TableTimeCostFunctionFeeGroups extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $group_id = null;

	/** @var int Foreign Key */
	var $fee_id = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_time_cost_function_fee_groups', 'id', $db);
	}

	function __toString()
	{
		return "PrenotownTableTimeCostFunctionFeeGroups";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		if (!$this->fee_id) {
			$this->setError(JText::_('No fee_id provided'));
			return false;
		}

		if (!$this->group_id) {
			$this->setError(JText::_('No group_id provided'));
			return false;
		}

		return true;
	}
}

/**
 * Time cost function fee rules table
 *
 * @package	Prenotown
 * @subpackage	Tables
 */
class TableTimeCostFunctionFeeRules extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var int Foreign Key */
	var $fee_id = '';

	/** @var time upper limit of the rule */
	var $upper_limit = '';

	/** @var float cost of the rule */
	var $cost = 0;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_time_cost_function_fee_rules', 'id', $db);
	}

	function __toString()
	{
		return "PrenotownTableTimeCostFunctionFeeRules";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		if (!$this->fee_id) {
			$this->setError(JText::_('No fee_id provided'));
			return false;
		}

		if (!$this->upper_limit) {
			$this->setError(JText::_('No upper limit provided'));
			return false;
		}

		if (!$this->cost) {
			$this->setError(JText::_('No cost provided'));
			return false;
		}

		$this->cost = preg_replace('/,/', '.', $this->cost);

		return true;
	}
}

/**
 * Time cost function profile table
 *
 * @package	Prenotown
 * @subpackage	Tables
 */
class TableTimeCostFunctionProfile extends JTable
{
	var $id = null;
	var $measure_unit_value = null;
	var $measure_unit_base = null;

	function __construct( &$db )
	{
		parent::__construct('#__prenotown_time_cost_function_profile', 'id', $db);
	}

	function __toString()
	{
		return "PrenotownTableTimeCostFunctionProfile";
	}

	function check()
	{
		if (!$this->id) {
			$this->setError(JText::_('No resource id provided'));
			return false;
		}

		if (!$this->measure_unit_value) {
			$this->setError(JText::_('No measure_unit_value provided'));
			return false;
		}

		if (!$this->measure_unit_base) {
			$this->setError(JText::_('No measure_unit_base provided'));
			return false;
		}

		return true;
	}
}

/**
 * Prenotown time based cost function
 *
 * @package	Prenotown
 * @subpackage	CostFunctions
 */
class PrenotownTimeCostFunction extends PrenotownCostFunction
{
	/** type of this cost function */
	protected $func_type = 'time';

	/** id of the resource this function is applied to */
	protected $resource_id = 0;

	/** db handle */
	protected $db = null;

	/** days of the week for internal usage */
	private $dows = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'); // days of a week

	/** days of the week as printend on the interface */
	private $idows = array(); // days of a week
	private $idows_langs = array(
		'it-IT' => array('lunedM-CM-,', 'martedM-CM-,', 'mercoledM-CM-,', 'giovedM-CM-,', 'venerdM-CM-,', 'sabato', 'domenica')
	);

	/** resolution used in hour select inputs */
	private $hour_resolution = 1;

	/** resolution used in minute select inputs */
	private $minute_resolution = 1;

	/** the space, in pixel, spanning between each grid cell and its overlaid content */
	private $grid_3d_spanning = 0;

	function __construct($resource_id)
	{
		/* call parent function and check exit status */
		if (parent::__construct($resource_id) == null) {
			_log("ERROR", "baseCostFunction returned NULL on constructor");
			return NULL;
		}

		/* load days of the week in available languages */
		$this->idows_langs['en-GB'] = $this->dows;
		$lang =& JFactory::getLanguage();
		if (isset($this->idows_langs[$lang->_lang])) {
			$this->idows = $this->idows_langs[$lang->_lang];
		} else {
			$this->idows = $this->dows;
		}

		/* load cost function id from DB */
		$this->loadResult("SELECT id FROM #__prenotown_cost_function WHERE class = 'PrenotownTimeCostFunction'");

		/* tables to be loaded */
		$tables = array(
			'TableTimeCostFunctionFee'		=> 'fee',
			'TableTimeCostFunctionFeeGroups' 	=> 'feegroups',
			'TableTimeCostFunctionFeeRules'		=> 'feerules',
			'TableTimeCostFunctionProfile'		=> 'profile',
			'TableResource'				=> 'resource',
		);

		/* loading tables */
		foreach ($tables as $class => $key) {
			$this->$key = new $class(JFactory::getDBO());
			if ($this->$key == NULL) {
				_log("ERROR", "TimeCostFunction: can't create table $class");
				_warn("WARN", JText::_("Can't create table") . " " . $class);
				return NULL;
			}
		}

		$this->check_integrity();

		/* init tables */
		$this->profile->id = $this->resource_id;
		$this->profile->load($this->resource_id);

		$this->resource->id = $this->resource_id;
		$this->resource->load($this->resource_id);
	}

	function __toString() {
		return "PrenotownTimeCostFunction";
	}

	/**
	 * Create minimal profile for this resource to allow proper operations
	 *
	 * Called: internally by the class
	 */
	private function check_integrity()
	{
		/* create default profile for this resource */
		$profile_exists = $this->loadResult("SELECT 1 FROM #__prenotown_time_cost_function_profile WHERE id = $this->resource_id");
		if (!(isset($profile_exists) and $profile_exists)) {
			$this->query("INSERT INTO #__prenotown_time_cost_function_profile (id, measure_unit_value, measure_unit_base) VALUES (" . $this->resource_id . ", '1', 'minutes')");
		}

		/* insert default fee */
		$this->query("INSERT INTO #__prenotown_time_cost_function_fee (name, resource_id) VALUES ('Default', " . $this->resource_id . ")");
		$fee_id = $this->db->insertid();

		if (!$fee_id) {
			$fee_id = $this->loadResult("SELECT id FROM #__prenotown_time_cost_function_fee WHERE name = 'Default' AND resource_id = $this->resource_id");
		}

		if ($fee_id) {
			/* apply default fee to group All (1) */
			$this->query("INSERT INTO #__prenotown_time_cost_function_fee_groups (fee_id, group_id) VALUES ($fee_id, 1)");

			$group_id = $this->db->insertid();
			if (!$group_id) {
				$group_id = $this->loadResult("SELECT id FROM #__prenotown_time_cost_function_fee_groups WHERE fee_id = $fee_id AND group_id = 1");

				if (!$group_id) {
					_warn("WARN", JText::sprintf("Unable to apply default fee on group All for resource %d", $this->resource_id));
				}
			}

			/* insert default rule for this fee on this resource */
			$rules_count = $this->loadResult("SELECT COUNT(id) FROM #__prenotown_time_cost_function_fee_rules WHERE fee_id = $fee_id");
			if (!$rules_count) {
				$this->query("INSERT INTO #__prenotown_time_cost_function_fee_rules (fee_id, upper_limit, cost) VALUES ($fee_id, '1:00:00', 1)");
				$rules_count = $this->loadResult("SELECT COUNT(id) FROM #__prenotown_time_cost_function_fee_rules WHERE fee_id = $fee_id");

				if (!$rules_count) {
					_warn("WARN", JText::sprintf("Unable to insert first rule for Default fee on resource %d", $this->resource_id));
				}
			}
		} else {
			_warn("WARN", JText::sprintf("Unable to insert Default fee for resource %d", $this->resource_id));
			return;
		}

		if (!isset($this->profile->measure_unit_value)) {
			$this->profile->measure_unit_value = 1;
		}

		if (!isset($this->profile->measure_unit_base)) {
			$this->profile->measure_unit_base = 'minutes';
		}

		$this->profile->store();
	}

	/**
	 * Return the next day of the date specified
	 *
	 * @param string $date SQL format date (YYYY-MM-DD)
	 * @return string
	 */
	function next_day($date)
	{
		$day_expanded = getdate(strtotime("$date + 1 day"));
		return sprintf("%04d-%02d-%02d", $day_expanded['year'], $day_expanded['mon'], $day_expanded['mday']);
		
		// return $this->loadResult("SELECT ADDDATE('$date', 1)");
	}

	/**
	 * Return the day of the week corresponding to provided date
	 *
	 * @param string $date SQL format date (YYYY-MM-DD)
	 * @return integer
	 */
	function dow($date)
	{
		$day_expanded = getdate(strtotime($date));
		$day_expanded['weekday'] -= 1;
		if ($day_expanded['weekday'] == -1) {
			$day_expanded['weekday'] = 6;
		}
		return $day_expanded['weekday'];
		
		// return $this->loadResult("SELECT WEEKDAY('$date')");
	}

	/**
	 * Return the cost of current booking applying provided fee rules for one single day,
	 * if a resource has availability ranges enabled. This method is invoked multiple
	 * times by its parent interface method until $booking_in_seconds amount runs out.
	 *
	 * @param int $booking_in_seconds the amount of booking expressed in seconds
	 * @param string $booking_day a day name (es. 'monday' or 'wednesday') all lowercase
	 * @param array $fee_rules a set of rules that describe the fee
	 * @return int
	 *
	 * Called: only internally by this class method getCostForFeeWithAvailability()
	 */
	private function getCostForFeeWithAvailabilityPerDay(&$booking_in_seconds, $booking_day, $fee_rules)
	{
		// JError::raiseNotice(500, "getCostForFeeWithAvailabilityPerDay($booking_in_seconds, $booking_day, $fee_rules)");

		if (!isset($fee_rules) && !is_array($fee_rules)) {
			_warn("WARN", JText::_("getCostForFeeWithAvailabilityPerDay() called without fee_rules (or not an array)"));
			return 0;
		}

		if (!isset($booking_day)) {
			_warn("WARN", JText::_("getCostForFeeWithAvailabilityPerDay() called without booking_day"));
			return 0;
		}

		// get availability range as $day_begin - $day_end
		$day_begin = $booking_day . '_begin';
		$day_begin = $this->resource->$day_begin;

		$day_end = $booking_day . '_end';
		$day_end = $this->resource->$day_end;

		// compute booking time covered by this single day
		$covered_time = $this->timeToSec($day_end) - $this->timeToSec($day_begin);
		if ($covered_time > $booking_in_seconds) {
			$covered_time = $booking_in_seconds;
		}

		// subtract this day time from booking total time
		$booking_in_seconds -= $covered_time;

		// return cost for this day
		$cost = $this->getCostForFee($covered_time, $fee_rules);
		$cost = sprintf("%.2f", $cost); // round cost to x.yy
		return $cost;
	}

	/**
	 * Return the cost of current booking applying provided fee rules if a resource has
	 * availability ranges enabled
	 *
	 * @param int $booking_in_seconds the amount of booking expressed in seconds
	 * @param array $fee_rules a set of rules that describe the fee
	 * @param string $begin_date SQL date string (YYYY-MM-DD)
	 * @param string $end_date SQL date string (YYYY-MM-DD)
	 * @return int
	 *
	 * Called: only internally by this class method getCostForFee()
	 */
	private function getCostForFeeWithAvailability($booking_in_seconds, $fee_rules, $begin_date, $end_date)
	{
		// check input data
		if (!$booking_in_seconds) {
			_warn("WARN", "getCostForFeeWithAvailability(): invalid booking_in_seconds: $booking_in_seconds");
			return 0;
		}

		if (!isset($fee_rules) && !is_array($fee_rules)) {
			_warn("WARN", "getCostForFeeWithAvailability(): invalid fee_rules: $fee_rules");
			return 0;
		}

		if (!preg_match('/^\d\d\d\d-\d\d-\d\d$/', $begin_date)) {
			_warn("WARN", "getCostForFeeWithAvailability(): invalid begin_date: $begin_date");
			return 0;
		}

		if (!preg_match('/^\d\d\d\d-\d\d-\d\d$/', $end_date)) {
			_warn("WARN", "getCostForFeeWithAvailability(): invalid end_date: $end_date");
			return 0;
		}

		$cost = 0;
		$booking = $booking_in_seconds;

		// JError::raiseNotice(500, "getCostForFeeWithAvailability($booking_in_seconds, $fee_rules, $begin_date, $end_date);");

		$date = $begin_date;
		while ((strcmp($date, $end_date) <= 0) and $booking) {
			$dow = $this->dow($date);
			$next_dow = ($dow + 1) % 7;

			$day_of_the_week = $this->dows[$dow];
			$next_day_of_the_week = $this->dows[$next_dow];

			$cost += $this->getCostForFeeWithAvailabilityPerDay($booking, $day_of_the_week, $fee_rules);

			$day_end = $day_of_the_week . "_end";
			$booking -= ($this->timeToSec('23:59:59') - $this->timeToSec($this->resource->$day_end));

			$next_day_begin = $next_day_of_the_week . "_begin";
			$booking -= $this->timeToSec($this->resource->$next_day_begin);

			$date = $this->next_day($date);
		}

		// JError::raiseNotice(500, "Cost for fee " . $fee_rules[0]['fee_id'] . " is $cost");
		return $cost;
	}

	/**
	 * Return the cost of current booking applying provided fee rules
	 *
	 * @param int $booking_in_seconds the amount of booking expressed in seconds
	 * @param array $fee_rules a set of rules that describe the fee
	 * @return int
	 *
	 * Called: only internally by this class method getCost()
	 */
	private function getCostForFee($booking_in_seconds, $fee_rules)
	{
		$cost = 0;
		$booking = $booking_in_seconds;
		$lower_limit = 0;
		$last_cost = 0;
		$last_range = 0;

		/*
		 * at each iteration, subtract the lower limit from the next range
		 * than calculate the cost of the next range in proportion to the
		 * booking seconds left, and add it to $cost variable. $booking and
		 * $lower_limit are than adjusted by this range amount of seconds
		 * (subtracted from $booking and added to $lower_limit) so next
		 * iteration will refers to an arbitrary zero in time.
		 */
		foreach ($fee_rules as $rule) {
			/* transform upper_limit in seconds and subtract previous lower_limit */
			$range = $this->timeToSec($rule['upper_limit']) - $lower_limit;

			$rule_cost = 0;
			if ($booking >= $range) {
				/* add the whole rule cost */
				$rule_cost += (float) $rule['cost'];
			} else {
				/* add only the proportional amount of cost covered by left booking seconds */
				$rule_cost += (float) $rule['cost'] / $range * $booking;
			}

			$cost += $rule_cost;

			$last_cost = $rule['cost'];
			$last_range = $range;

			$booking -= $range;
			$lower_limit += $range;

			if ($booking <= 0) {
				return $cost;
			}
		}

		/* if booking is longer than time described in ruleset, use last rule to fill it */
		if ($booking) {
			$cost_left = (float) $last_cost / $last_range * $booking;
			$cost += $cost_left;
		}

		return $cost;
	}

	/**
	 * return an array of arrays. each sub-array is the application of the cost
	 * function to a different group. the zero element is the total, while
	 * any following element is the amount added by a specific rule of the profile.
	 *
	 * Called: by view resource/tmpl/paybooking.php
	 */
	function getCost()
	{
		$booking = $this->splittedBooking();

		// transform booking interval in seconds
		$query = sprintf("SELECT UNIX_TIMESTAMP('%04d-%02d-%02d %02d:%02d') - UNIX_TIMESTAMp('%04d-%02d-%02d %02d:%02d')",
			$booking['end']['year'],
			$booking['end']['month'],
			$booking['end']['day'],
			$booking['end']['hour'],
			$booking['end']['minute'],
			$booking['begin']['year'],
			$booking['begin']['month'],
			$booking['begin']['day'],
			$booking['begin']['hour'],
			$booking['begin']['minute']
		);

		$booking_in_seconds = $this->loadResult($query);

		/*
		 * iterate through all the fees associated to this resource,
		 * and compute the cost for each
		 */
		$fees = $this->loadAssocList("SELECT * FROM #__prenotown_time_cost_function_fee WHERE resource_id = " . $this->db->quote($this->resource_id));

		$cost_per_fee = array();
		foreach ($fees as $fee) {
			$rules = $this->loadAssocList("SELECT * FROM #__prenotown_time_cost_function_fee_rules WHERE fee_id = " . $this->db->quote($fee['id']));

			if ($this->resource->availability_enabled) {
				// JError::raiseNotice(500, JText::_("Calculating fees with availability range on resource $this->resource_id"));
				$cost_per_fee[$fee['id']] = $this->getCostForFeeWithAvailability($booking_in_seconds, $rules, $booking['begin']['sqldate'], $booking['end']['sqldate']);
			} else {
				// JError::raiseNotice(500, JText::_("Calculating fees ignoring availability range"));
				$cost_per_fee[$fee['id']] = $this->getCostForFee($booking_in_seconds, $rules);
			}
		}

		/*
		 * load all associated groups and apply costs
		 */
		$cost = array();
		foreach ($cost_per_fee as $fee_id => $howmuch) {
			$groups = $this->loadAssocList("SELECT #__prenotown_user_groups.* FROM #__prenotown_user_groups JOIN #__prenotown_time_cost_function_fee_groups ON #__prenotown_time_cost_function_fee_groups.group_id = #__prenotown_user_groups.id WHERE fee_id = " . $this->db->quote($fee_id));

			foreach ($groups as $group) {
				$cost[$group['id']] = array('group_name' => $group['name'], 'cost' => $howmuch);
			}
		}

		return $cost;
	}

	/** Calulate the cost for a booking
	 */
	function getCostForSavedBooking($booking_id)
	{
		if (!$booking_id) return 0;

		// expand the booking inside the database
		$this->query("CALL #__prenotown_expand_booking($booking_id)");

		// load the booking
		$booking = $this->loadAssoc("SELECT * FROM #__prenotown_superbooking WHERE id = $booking_id");

		// load the fee for current group
		$fees = $this->loadAssocList("SELECT * FROM #__prenotown_time_cost_function_fee_rules JOIN #__prenotown_time_cost_function_fee ON #__prenotown_time_cost_function_fee_rules.fee_id = #__prenotown_time_cost_function_fee.id JOIN #__prenotown_time_cost_function_fee_groups ON #__prenotown_time_cost_function_fee_groups.fee_id = #__prenotown_time_cost_function_fee.id WHERE #__prenotown_time_cost_function_fee_groups.group_id = " . $booking['group_id'] . " AND #__prenotown_time_cost_function_fee.resource_id = " . $booking['resource_id'] . " ORDER BY fee_id, upper_limit");

		
	}

	/**
	 * Return the cost for current booking using group id $group_id
	 *
	 * @param int $group_id the group ID
	 * called: by views
	 */
	function getCostForGroup($group_id)
	{
		global $ghost_group;

		$cost = $this->getCost();
		$your_cost = 0;

		if (isset($cost[$group_id])) {
			$your_cost = $cost[$group_id]['cost'];
		} else {
			// JError::raiseNotice(500, JText::sprintf("No cost rules defined for group id %d, using group All", $group_id));
			$your_cost = $cost[1]['cost'];
		}

		return $your_cost;
	}

	/**
	 * Return the cost for current booking using currently selected group as profile
	 *
	 * called: by views
	 */
	function getCostForCurrentGroup()
	{
		global $ghost_group;

		return $this->getCostForGroup(_group_id());
	}

	/**
	 * Return the cost of a booking profile
	 *
	 */
	function getCostForBookingProfile($begin, $end, $periodicity, $resource_id, $group_id)
	{
		$is_periodic = $periodicity ? 1 : 0;

		$this->query("CALL #__prenotown_expand_booking_profile('$begin', '$end', $is_periodic, $periodicity, 1, 1)");
		$this->query("CALL #__prenotown_expand_booking_apply_availability($resource_id)");
		$fee_id = $this->loadResult("SELECT fee_id FROM #__prenotown_time_cost_function_fee_groups JOIN #__prenotown_time_cost_function_fee ON #__prenotown_time_cost_function_fee.id = #__prenotown_time_cost_function_fee_groups.fee_id WHERE group_id IN ($group_id, 1) AND resource_id = $resource_id ORDER BY group_id DESC");
		return $this->loadResult("SELECT #__prenotown_get_cost($fee_id, 0)");
	}


	/**
	 * provides the HTML interface to define a profile
	 *
	 * Called: by view resource/tmpl/costfunction.php
	 */
	function profileInterface()
	{
		if (!_status('user')) {
			forceLogin();
			return;
		}

		jimport('joomla.html.pane');

		$confirm_delete_fee = JText::_("Do you really want to delete this fee?");
		$select_a_group = JText::_("Please select a group to add");
		$insert_fee_name = JText::_("Please select a name for the new fee");

		echo <<< JSEND
<script language="javascript" type="text/javascript">
	function check_form() {
		var form = $('costfunction-form');
		if (!form) {
			return false;
		}

		form.method.value = 'updateProfile';

		form.submit();
		return false;
	}

	function deleteFee(id) {
		if (confirm("$confirm_delete_fee")) {
			$("method").value = 'deleteFeeProfile';
			$("feeId").value = id;
			$("costfunction-form").submit();
			return false;
		}
		return false;
	}

	function addRuleBefore(groupId, ruleId) {
		$("method").value = 'addRuleBefore';
		$("feeId").value = groupId;
		$("ruleId").value = ruleId;
		$("costfunction-form").submit();
		return false;
	}

	function addRuleAfter(groupId, ruleId) {
		$("method").value = 'addRuleAfter';
		$("feeId").value = groupId;
		$("ruleId").value = ruleId;
		$("costfunction-form").submit();
		return false;
	}

	function addNewFee() {
		$("method").value = 'addNewFee';
		if ($("new_fee_name").value.length == 0) {
			alert("$insert_fee_name");
			return false;
		}
		$("newFee").value = $("new_fee_name").value;
		$("costfunction-form").submit();
		return false;
	}

	function grantFeeToGroup(feeId) {
		$("method").value = 'grantFeeToGroup';
		$("feeId").value = feeId;

		var l = $("availableGroups" + feeId);
		if (l) {
			$('groupId').value = l.options[l.selectedIndex].value;	
			$("costfunction-form").submit();
		}
		return false;
	}

	function revokeFeeFromGroup(feeId) {
		$("method").value = 'revokeFeeFromGroup';
		$("feeId").value = feeId;

		var l = $("enabledGroups" + feeId);
		if (l) {
			$('groupId').value = l.options[l.selectedIndex].value;	
			$("costfunction-form").submit();
		}
		return false;
	}

	function updateRules() {
	}
</script>
JSEND;

		echo <<< STYLE
<style type="text/css">
/* tabs */
dl.tabs { float: left; margin: 10px 0 -1px 0; z-index: 50; }
dl.tabs dt { float: left; padding: 4px 10px; border-left: 1px solid #ccc; border-right: 1px solid #ccc; border-top: 1px solid #ccc; margin-left: 3px; background: #f0f0f0; color: #666; }
dl.tabs dt.open { background: #F9F9F9; border-bottom: 1px solid #F9F9F9; z-index: 100; color: #000; }
div.current { clear: both; border: 1px solid #ccc; padding: 10px 10px; }
div.current dd { padding: 0; margin: 0; }
</style>
STYLE;

		$hidden = Array('option','view','layout');
		foreach ($hidden as $field) {
			echo '<input type="hidden" name="'. $field . '" value="' . htmlspecialchars(JRequest::getString($field)) . '">';
		}

		echo '<div class="cost-function profile">';
		echo '<form name="costfunction-form" id="costfunction-form" method="post">';

		// insert costfunction profile (resource_id, C.F. class, method to be called)
		echo '<input type="hidden" name="id" value="' . $this->resource_id . '">';
		echo '<input type="hidden" name="task" value="cost_function">';
		echo '<input type="hidden" name="resource_id" value="' . $this->resource_id . '">';
		echo '<input type="hidden" name="class" value="PrenotownTimeCostFunction">';
		echo '<input type="hidden" name="method" value="updateProfile" id="method">';
		echo '<input type="hidden" name="feeId" value="" id="feeId">';
		echo '<input type="hidden" name="groupId" value="" id="groupId">';
		echo '<input type="hidden" name="ruleId" value="" id="ruleId">';

		numbullet("Booking unit");
		echo '<div style="width:100%;text-align:center">';
		echo '<input size="5" name="measure_unit_value" value="' . htmlspecialchars($this->profile->measure_unit_value) . '"> ';
		echo '<select name="measure_unit_base"><option value="">' . JText::_("Choose a unit") . "</option>";

		$unit_type = Array();
		$unit_type[] = 'seconds';
		$unit_type[] = 'minutes';
		$unit_type[] = 'hours';
		$unit_type[] = 'days';
		$unit_type[] = 'weeks';

		foreach ($unit_type as $ut) {
			echo '<option value="' . $ut . '"';
			if ($ut == $this->profile->measure_unit_base) {
				echo " selected";
			}
			echo ">" . JText::_($ut) . "</option>";
		}

		echo '</select></div> <br/><br/>';

		/* traverse all the fees applying to resource $this->resource_id */
		$fees = $this->loadAssocList("SELECT * FROM #__prenotown_time_cost_function_fee WHERE resource_id = $this->resource_id ORDER BY name ASC");

		/* put default fee on top */
		for ($i = 0; $i < count($fees); $i++) {
			if ($fees[$i]['name'] == 'Default') {
				$default_fee = $fees[$i];
				unset($fees[$i]);
				array_unshift($fees, $default_fee);
				break;
			}
		}

		numbullet('Current fees');

		foreach ($fees as $fee) {
			echo '<a name="fee-' . $fee['id'] . '"></a><br/>';
			echo '<h2>' . JText::_("Fee") . ': ' . $fee['name'] . '</h2><br/>';
			$pane =& JPane::getInstance('Tabs');
			echo $pane->startPane('Pane_' . $fee['id']);
			echo $pane->startPanel(JText::_('Rules'), 'panel1');

			/* select all the rules describing this fee */
			$rules = $this->loadAssocList("SELECT * FROM #__prenotown_time_cost_function_fee_rules WHERE fee_id = " . $fee['id'] . " ORDER BY upper_limit ASC");

			echo '<table class="hl"><thead>';
			echo '<th style="width: 20px;"><img src="components/com_prenotown/assets/trash.png" title="' . JText::_("Mark the checkbox to delete the rule") . '"></th>';
			echo '<th>' . JText::_("From") . '</th>';
			echo '<th>' . JText::_("To") . '</th>';
			echo '<th>' . JText::_("Cost of this range") . '</th>';
			echo '<th>' . JText::_("New rule") . '</th>';
			echo '</thead>';

			/* $minimum gets updated at each iteration with the last upper_limit */
			$minimum = 0;

			/* print a row for each rule */
			foreach ($rules as $rule) {
				echo "<tr><td><input name=\"deleterule[" . $rule['id'] . "]\" type=\"checkbox\"></td>";
				echo "<td>" . $this->secToTime($minimum) . "</td>";
				echo "<td><input type=\"hidden\" name=\"minimum[" . $rule['id'] . "]\" value=\"$minimum\">";
				echo "<input onBlur=\"checkTime('maximum[" . $rule['id'] . "]'); updateRules(" . $rule['id'] . "); return false;\" id=\"maximum[" . $rule['id'] . "]\" name=\"maximum[" . $rule['id'] . "]\" value=\"" . preg_replace('/:\d\d$/', '', $rule['upper_limit']) . "\"></td>";
				echo "<td><input style=\"width: 100px\" name=\"costPerHour[" . $rule['id'] . "]\" value=\"" . preg_replace('/\./', ',', sprintf("%.2f", $rule['cost']*60*60/($this->timeToSec($rule['upper_limit'])-$minimum))) . "\"> &euro;/" . JText::_("hour") . "</td>";
				echo '<td style="text-align:center">&uarr; <a href="#" onClick="addRuleBefore(' . $rule['fee_id'] . ", " . $rule['id'] . '); return false;">' . JText::_("before") . "</a>";
				$this->hSeparator();
				echo '<a href="#" onClick="addRuleAfter(' . $rule['fee_id'] . ", " . $rule['id'] . '); return false;">' . JText::_("after") . "</a> &darr;</td>";
				echo "</tr>";

				$minimum = $this->timeToSec($rule['upper_limit']);
			}

			echo '</table>';

			if ($fee['name'] != 'Default') {
				echo '<hr>';
				echo '<button class="button" onClick="deleteFee(' . $fee['id'] . '); return false;">' . JText::_("Delete this fee") . '</button>';
			}

			echo $pane->endPanel();
			if ($fee['name'] != 'Default') {
				echo $pane->startPanel(JText::_('Groups'), 'panel2');

				/* select all the groups applying or not to this fee */
				$groups_query = "
SELECT #__prenotown_user_groups.name AS group_name,
	#__prenotown_user_groups.id AS group_id,
	#__prenotown_time_cost_function_fee_groups.fee_id
FROM #__prenotown_user_groups
LEFT JOIN #__prenotown_time_cost_function_fee_groups ON #__prenotown_user_groups.id = #__prenotown_time_cost_function_fee_groups.group_id
WHERE #__prenotown_time_cost_function_fee_groups.fee_id = " . $fee['id'] . "
GROUP BY group_name
ORDER BY #__prenotown_user_groups.name ASC";
				$groups = $this->loadAssocList($groups_query);

				$groups_available_query = "
SELECT name AS group_name, id AS group_id
FROM #__prenotown_user_groups
WHERE id NOT IN (
	SELECT group_id 
	FROM #__prenotown_time_cost_function_fee_groups
	JOIN #__prenotown_time_cost_function_fee
	ON #__prenotown_time_cost_function_fee_groups.fee_id = #__prenotown_time_cost_function_fee.id
	WHERE #__prenotown_time_cost_function_fee.resource_id = $this->resource_id
) ORDER BY group_name ASC";

				$groups_available = $this->loadAssocList($groups_available_query);

				echo '<h4>' . JText::_("Groups this fee applies to") . ':</h4>';
				echo '<table style="width: 100%"><tr><td style="width: 45%"><select style="display: block; width: 100%;" size=10 name="enabledGroups' . $fee['id'] . '" id="enabledGroups' . $fee['id'] . '">';

				foreach ($groups as $group) {
					echo '<option value="' . $group['group_id'] . '">' . $group['group_name'] . '<br>';
				}

				echo "</select></td><td style=\"text-align: center\">";

				echo '<button class="button" onClick="grantFeeToGroup(' . $fee['id'] . '); return false;">&larr;&nbsp;' . JText::_("Apply") . '</button>';
				echo '<br><br>';
				echo '<button class="button" onClick="revokeFeeFromGroup(' . $fee['id'] . '); return false;">' . JText::_("Remove") . '&nbsp;&rarr;</button>';

				echo '</td><td style="width: 45%"><select style="display: block; width: 100%;" size=10 name="availableGroups' . $fee['id'] . '" id="availableGroups' . $fee['id'] . '">';

				foreach ($groups_available as $group) {
					echo '<option value="' . $group['group_id'] . '"> ' . $group['group_name'] . '<br>';
				}

				echo "</select></td></tr></table>";

				echo $pane->endPanel();
			}
			echo $pane->endPane();
		}

		echo '<input type="hidden" name="newFee" value="" id="newFee">';
		echo '</form>';
		echo '</div>';

		echo '<br/><div class="button-footer">';
		echo '<button class="button" onClick="check_form()">' . JText::_("Update") . '</button>';

		$this->hSeparator();

		echo '<input type="text" name="new_fee_name" id="new_fee_name" value="' . htmlspecialchars(JRequest::getString('new_fee_name')) . '">';
		echo '&nbsp;&nbsp;<button class="button" onClick="addNewFee(); return false;">' . JText::_("Add new fee") . '</button>';

		$this->hSeparator();

		echo '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=resource&layout=edit&id=' . $this->resource_id . '\'); return false;">' . JText::_("Back to resource") . '</button>';

		echo '</div><br/><br/>';

		/* avoid redirects from the controller */
		JRequest::setVar('noRedirect', 1);
	}

	/**
	 * Drop an fee inside costfunction profile
	 *
	 * Called: by controller
	 */
	function deleteFeeProfile()
	{
		$r = Array();
		$r['message'] = "";
		$r['redirect'] = "index.php?option=com_prenotown&view=resource&layout=costfunction&id=" . $this->resource_id;

		if (!_status('user')) {
			forceLogin();		
			return $r;
		}

		$feeId = JRequest::getInt('feeId', 0);
		if (isset($feeId) and $feeId) {
			$this->fee->reset();
			if ($this->fee->delete($feeId)) {
				$r['message'] = JText::_("Group deleted");
			} else {
				_warn("WARN", JText::_("Error deleting fee") . ": " . $this->fee->getError());
			}
		} else {
			_warn("WARN", JText::_("No group id provided"));
		}

		return $r;
	}

	/**
	 * Return booking unit value in seconds
	 *
	 * Called: internally by this class
	 */
	private function bookingUnitInSec()
	{
		$unit = 0;

		switch ($this->profile->measure_unit_base) {
			case "seconds":	$unit = 1; break;
			case "minutes":	$unit = 60; break;
			case "hours":	$unit = 3600; break;
			case "days":	$unit = 86400; break;
			case "weeks":	$unit = 604800; break;
		}

		$unit *= $this->profile->measure_unit_value;

		return $unit;
	}

	/**
	 * Add a rule with a time difference comparing to a reference url.
	 * The returned array should be returned in turn by calling function
	 *
	 * Called: by internal methods
	 *
	 * @param int $delta_seconds The delta in seconds
	 * @return array
	 */
	private function addRuleDelta($delta_seconds)
	{
		$r = Array();
		$r['redirect'] = "index.php?option=com_prenotown&view=resource&layout=costfunction&id=" . $this->resource_id;

		/* check input data */
		$delta_seconds = (int) $delta_seconds;
		if (empty($delta_seconds)) {
			_warn("WARN", JText::_("addRuleDelta() called without a \$delta_seconds parameter"));
			return $r;
		}

		$fee_id = JRequest::getInt('feeId', 0);
		if (!(isset($fee_id) and $fee_id)) {
			_warn("WARN", JText::_("No fee_id provided"));
			return $r;
		}

		$rule_id = JRequest::getInt('ruleId', 0);
		if (!(isset($rule_id) and $rule_id)) {
			_warn("WARN", JText::_("No rule_id provided"));
			return $r;
		}

		/* 0. Load reference rule from DB */
		$this->feerules->reset();
		$this->feerules->load($rule_id);

		/* 1. convert add after cases into add before cases */
		if ($delta_seconds > 0) {
			$new_id = $this->loadResult("SELECT id FROM #__prenotown_time_cost_function_fee_rules WHERE fee_id = $fee_id AND upper_limit > '" .  $this->feerules->upper_limit . "' ORDER BY upper_limit ASC LIMIT 1");

			if ($new_id) {
				// JError::raiseNotice(500, "Add after $rule_id with delta $delta_seconds");
				$added_limit = $this->timeToSec($this->feerules->upper_limit) + $delta_seconds;
				$rule_id = $new_id;
				$this->feerules->reset();
				$this->feerules->load($rule_id);
				$delta_seconds = $added_limit - $this->timeToSec($this->feerules->upper_limit);
				// JError::raiseNotice(500, "Became Add before $rule_id with delta $delta_seconds");
			} else {
				/* 1.bis the reference rule is the last one, so just add the rule */
				$new_upper_limit = $this->secToTime($this->timeToSec($this->feerules->upper_limit) + $delta_seconds);
				$new_cost = $delta_seconds / 3600; /* 1 euro per hour */
				$this->query("INSERT INTO #__prenotown_time_cost_function_fee_rules (fee_id, upper_limit, cost) values($fee_id,'$new_upper_limit',$new_cost);");
				$r['message'] = JText::_("New rule added");
				return $r;
			}
		}
		
		/* 2. load the reference rule */
		// JError::raiseNotice(500, "Loading rule $rule_id");
		$this->feerules->reset();
		$this->feerules->load($rule_id);
		$upper_limit = $this->timeToSec($this->feerules->upper_limit);
		// JError::raiseNotice(500, "Upper_limit = $upper_limit");

		/* 3. load the lower limit in seconds */
		$lower_limit = $this->loadResult("SELECT upper_limit FROM #__prenotown_time_cost_function_fee_rules WHERE fee_id = $fee_id AND upper_limit < '" . $this->feerules->upper_limit . "' ORDER BY upper_limit DESC LIMIT 1");
		if ($lower_limit) {
			$lower_limit = $this->timeToSec($lower_limit);
		} else {
			$lower_limit = 0; /* just in case reference rule is the first one */
		}
		// JError::raiseNotice(500, " + lower_limit = $lower_limit");

		/* 4. get the cost per second */
		$cost_per_second = $this->feerules->cost / ($upper_limit - $lower_limit);
		// JError::raiseNotice(500, " + cost_per_second = " . $this->feerules->cost . " / ($upper_limit - $lower_limit) = $cost_per_second");

		/* 5. modify reference rule */
		$this->feerules->cost = $cost_per_second * $delta_seconds * -1;
		if (!($this->feerules->check() && $this->feerules->store())) {
			_warn("WARN", JText::_("Error while adding new rule: ") . $this->feerules->getError());
			return $r;
		}
		// JError::raiseNotice(500, " + rule new cost: " . $this->feerules->cost);

		/* 6. add the new rule */
		$this->feerules->reset();
		$this->feerules->id = null;
		$this->feerules->fee_id = $fee_id;
		$this->feerules->upper_limit = $this->secToTime($upper_limit + $delta_seconds);
		$this->feerules->cost = $cost_per_second * ($upper_limit + $delta_seconds - $lower_limit);
		if (!($this->feerules->check() && $this->feerules->store())) {
			_warn("WARN", JText::_("Error while adding new rule: ") . $this->feerules->getError());
			return $r;
		}

		$r['message'] = JText::_("New rule added");
		return $r;
	}

	/**
	 * Add a rule before another one
	 *
	 * Called: by controller
	 */
	function addRuleBefore()
	{
		if (!_status('admin')) {
			forceLogin();
			return;
		}

		return $this->addRuleDelta(-15*60);
	}

	/**
	 * Add a rule before another one
	 *
	 * Called: by controller
	 */
	function addRuleAfter()
	{
		if (!_status('admin')) {
			forceLogin();
			return;
		}

		return $this->addRuleDelta(15*60);
	}

	/**
	 * Add a new fee to this resource
	 *
	 * Called: by controller
	 */
	function addNewFee()
	{
		$r = Array();
		$r['redirect'] = "index.php?option=com_prenotown&view=resource&layout=costfunction&id=" . $this->resource_id;

		if (!_status('admin')) {
			forceLogin();
			return $r;
		}

		$fee_name = JRequest::getString("newFee", '');
		if (!(isset($fee_name) and $fee_name)) {
			_warn("WARN", JText::_("No fee namfee name provided"));
			return $r;
		}

		/* create new fee */
		$this->fee->reset();
		$this->fee->name = $fee_name;
		$this->fee->resource_id = $this->resource_id;
		if ($this->fee->check()) {
			if ($this->fee->store()) {
				$r['message'] = JText::_("New fee added");
			} else {
				_warn("WARN", JText::_("Error creating new fee: ") . $this->fee->getError());
			}
		} else {
			_warn("WARN", JText::_("Error creating new fee: ") . $this->fee->getError());
		}

		return $r;
	}

	/**
	 * Update existing profile
	 *
	 * Called: by controller
	 */
	function updateProfile()
	{
		$r = Array();
		$r['redirect'] = "index.php?option=com_prenotown&view=resource&layout=costfunction&id=" . $this->resource_id;

		if (!_status('admin')) {
			forceLogin();
			return $r;
		}

		// get all input data
		$measure_unit_value = JRequest::getInt('measure_unit_value', null);
		$measure_unit_base = JRequest::getString('measure_unit_base', null);
		$deleteRule = JRequest::getVar('deleterule', Array(), 'DEFAULT', 'ARRAY');
		$maximum = JRequest::getVar('maximum', Array(), 'DEFAULT', 'ARRAY');
		$minimum = JRequest::getVar('minimum', Array(), 'DEFAULT', 'ARRAY');
		$costPerHour = JRequest::getVar('costPerHour', Array(), 'DEFAULT', 'ARRAY');

		// save resource profile
		$this->profile->reset();
		$this->profile->id = $this->resource_id;
		$this->profile->measure_unit_value = $measure_unit_value;
		$this->profile->measure_unit_base = $measure_unit_base;
		if ($this->profile->check()) {
			if (!$this->profile->store()) {
				_warn("WARN", JText::_("Can't update booking unit") . ": " . $this->profile->getError());
			}
		} else {
			_warn("WARN", JText::_("Can't update booking unit") . ": " . $this->profile->getError());
		}

		// update other rules
		foreach ($maximum as $ri => $max) {
			$this->feerules->id = $ri;
			$this->feerules->load($id);
			$this->feerules->upper_limit = $max;
			$costPerHour[$ri] = preg_replace('/,/', '.', $costPerHour[$ri]);
			$this->feerules->cost = (double) $costPerHour[$ri] * ( $this->timeToSec($max) - $minimum[$ri] ) / 3600;
			// JError::raiseNotice(500, "Cost: " . $costPerHour[$ri] . ' * ( ' . $this->timeToSec($max) . ' - '. $minimum[$ri] . ') /' . 3600);
			if ($this->feerules->check()) {
				if ($this->feerules->store()) {
					$r['message'] = JText::_("Profile updated");
				} else {
					_warn("WARN", JText::_("Error while updating rule: ") . $this->feerules->getError());
				}
			} else {
				_warn("WARN", JText::_("Error while updating rule: ") . $this->feerules->getError());
			}
		}

		// delete selected rules
		foreach ($deleteRule as $ri => $dr) {
			// _warn("WARN", "Delete rule n. $ri");
			$this->feerules->id = $ri;
			$this->feerules->load($id);

			$old_lower_limit = $this->feerules->upper_limit;

			$new_lower_limit = $this->loadResult("SELECT upper_limit FROM #__prenotown_time_cost_function_fee_rules WHERE fee_id = " . $this->feerules->fee_id . " AND upper_limit < '" . $this->feerules->upper_limit . "' ORDER BY upper_limit DESC LIMIT 1");

			$new_id = $this->loadResult("SELECT id FROM #__prenotown_time_cost_function_fee_rules WHERE fee_id = " . $this->feerules->fee_id . " AND upper_limit > '" . $this->feerules->upper_limit . "' ORDER BY upper_limit ASC LIMIT 1");

			$this->feerules->delete($id);
			unset($maximum[$ri]);
			unset($cost[$ri]);

			$this->feerules->id = $new_id;
			$this->feerules->load($new_id);
			$current_cost = $this->feerules->cost;
			$upper_limit = $this->timeToSec($this->feerules->upper_limit);
			$old_lower_limit = $this->timeToSec($old_lower_limit);
			$new_lower_limit = $this->timeToSec($new_lower_limit);
			$new_cost = $current_cost * ($upper_limit - $new_lower_limit) / ($upper_limit - $old_lower_limit);
			// JError::raiseNotice(500, "$new_id: $current_cost / ($upper_limit - $old_lower_limit) * ($upper_limit - $new_lower_limit) = $new_cost");

			$this->feerules->cost = $new_cost;
			if (!($this->feerules->check() && $this->feerules->store())) {
				// _warn("WARN", JText::_("Error updating ruleset: ") . $this->feerules->getError());
				return;
			}

			$this->query("UPDATE #__prenotown_time_cost_function_fee_rules SET cost = $new_cost WHERE id = $new_id");
		}

		// apply fees to groups
		foreach ($feeGroups as $fg) {
			list($fee_id, $group_id) = explode(":", $fg);
			$this->query("DELETE FROM #__prenotown_time_cost_function_fee_groups WHERE fee_id = $fee_id");
		}
		foreach ($feeGroups as $fg) {
			list($fee_id, $group_id) = explode(":", $fg);
			$this->query("INSERT INTO #__prenotown_time_cost_function_fee_groups (fee_id, group_id) VALUES ($fee_id, $group_id)");
		}

		return $r;
	}

	/**
	 * Applies a fee to a group
	 *
	 * Called: by controller
	 */
	function grantFeeToGroup()
	{
		$r = array();
		$r['message'] = '';
		$r['redirect'] = "index.php?option=com_prenotown&view=resource&layout=costfunction&id=" . $this->resource_id;
		
		if (!_status('admin')) {
			forceLogin();
			return $r;
		}

		$fee_id = JRequest::getInt('feeId', 0);
		$group_id = JRequest::getInt('groupId', 0);

		$this->feegroups->reset();
		$this->feegroups->fee_id = $fee_id;
		$this->feegroups->group_id = $group_id;
		if ($this->feegroups->check()) {
			if ($this->feegroups->store()) {
				$r['message'] = JText::_("Fee applied");
			} else {
				_warn("WARN", JText::sprintf("Can't apply fee %d to group %d", $fee_id, $group_id) . ": " . $this->feegroups->getError());
			}
		} else {
			_warn("WARN", JText::sprintf("Can't apply fee %d to group %d", $fee_id, $group_id) . ": " . $this->feegroups->getError());
		}

		return $r;
	}

	/**
	 * Revoke a fee from a group
	 *
	 * Called: by controller
	 */
	function revokeFeeFromGroup()
	{
		$r = array();
		$r['message'] = '';
		$r['redirect'] = "index.php?option=com_prenotown&view=resource&layout=costfunction&id=" . $this->resource_id;
		
		if (!_status('admin')) {
			forceLogin();
			return $r;
		}

		$fee_id = JRequest::getInt('feeId', 0);
		$group_id = JRequest::getInt('groupId', 0);

		$this->query("DELETE FROM #__prenotown_time_cost_function_fee_groups WHERE fee_id = $fee_id AND group_id = $group_id");

		$r['message'] = JText::_("Fee revoked");
		return $r;
	}


	/**
	 * Convert an amount of seconds into HH:MM:SS format
	 *
	 * @param int $seconds Second to convert
	 * @return string
	 */
	private function secToTime($seconds)
	{
		$hours = floor($seconds / 60 / 60);
		$seconds -= $hours * 60 * 60;

		$minutes = floor($seconds / 60);
		$seconds -= $minutes * 60;

		return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
	}

	/**
	 * Converts a HH:MM:SS format into the corresponding seconds amount
	 *
	 * @param string $time Time in HH:MM:SS format
	 * @return int
	 */
	private function timeToSec($time)
	{
		$values = preg_split('/:/', $time);

		if (count($values) == 2) {
			if (pref('debug')) {
				// _warn("WARN", "timeCostFunction::timeToSec called without seconds [" . implode(":", $values) . "]");
			}
			$values[2] = 0;
		}

		if (count($values) != 3) {
			return 0;
		}

		foreach ($values as $value) {
			if (!preg_match('/^\d+$/', $value)) {
				return 0;
			}
		}

		return $values[0] * 60 * 60 + $values[1] * 60 + $values[2];
	}

	/**
	 * Draw a single booking on availability image. To be used only inside bookingAvailability()
	 *
	 * @param GD::Image $i GD image object
	 * @param int $begin_date begin data as second from epoch
	 * @param int $begin_time begin time as second from midnight
	 * @param int $end_date end data as second from epoch
	 * @param int $end_time end time as second from midnight
	 * @param int $this->color GD indexed color
	 */
	private function draw_booking($i, $begin_date, $begin_time, $end_date, $end_time, $color, $bordercolor=1)
	{
		global $cell_width, $cell_height, $grid_offset;

		// _log("INFO", "draw_booking: $begin_date, $begin_time, $end_date, $end_time, $color, $bordercolor");

		if ($begin_date == $end_date) {
			/* booking is all contained in a single day */
			$ux = $begin_date * $cell_width + $grid_offset['x'];
			$dx = $end_date * $cell_width + $cell_width + $grid_offset['x'];

			$uy = $begin_time / 60 * $cell_height + $grid_offset['y'];
			$dy = $end_time / 60 * $cell_height + $grid_offset['y'];

			imagefilledrectangle($i, $ux + $this->grid_3d_spanning, $uy + $this->grid_3d_spanning, $dx + $this->grid_3d_spanning, $dy + $this->grid_3d_spanning, $color);
			imagerectangle($i, $ux + $this->grid_3d_spanning, $uy + $this->grid_3d_spanning, $dx + $this->grid_3d_spanning, $dy + $this->grid_3d_spanning, $bordercolor);
		} else {
			/* booking is divided in more than one day */
			$this->draw_booking($i, $begin_date, $begin_time, $begin_date, 24 * 60, $color, $bordercolor);
			for ($day = $begin_date + 1; $day < $end_date; $day++) {
				$this->draw_booking($i, $day, 0, $day, 24 * 60, $color, $bordercolor);
			}
			$this->draw_booking($i, $end_date, 0, $end_date, $end_time, $color, $bordercolor);
		}
	}

	/**
	 * Create a PNG image showing availability of the resource
	 *
	 * Called: by controller (after being included by method bookingInterface())
	 */
	function bookingAvailability()
	{
		global $cell_width, $cell_height, $grid_width, $grid_height, $grid_offset;

		if (!_status('user')) {
			forceLogin();
			return;
		}

		/* set MIME type */
		$document =& JFactory::getDocument();
		$document->setMimeEncoding("image/png");
		header ("Content-type: image/png");

		/* resource availability picture path */
		$cached_image_path = resource_booking_availability_cache_path($this->resource_id);

		/* search for cache images and return it */
		if (file_exists($cached_image_path) && ($cached_image = fopen($cached_image_path, "r+"))) {
			$image = "";
			while (!feof($cached_image)) {
				$image .= fread($cached_image, 8192);
			}
			fclose($cached_image);
			
			/* Avoid controller redirect */
			JRequest::setVar('noRedirect', 1);

			/* output the image */
			echo $image;

			return Array();
		}

		/* resource limits */
		$limits =& $this->limits;

		/* cell width in pixel */
		$cell_width = 16;

		/* cell height in pixel */
		// $cell_height = 40;
		$cell_height = $cell_width;

		/* grid dimentions */
		$grid_offset['x'] = 45;
		$grid_offset['y'] = 45;

		$grid_width = $cell_width * (($limits['max_advance_in_sec'] / 60 / 60 / 24) - $limits['deadline_days']);
		$grid_height = 24 * $cell_height;

		/* image width */
		$width = $grid_width + $grid_offset['x'] + 10;

		/* image height */
		$height = $cell_height * (24 + 2) + $grid_offset['y'] + 10;

		/* creating image and allocating colors */
		$i = imagecreatetruecolor($width, $height);

		$this->color = array();

		$this->color['white']	= imagecolorallocate($i, 255,	255,	255);
		$this->color['black']	= imagecolorallocate($i, 0,	0,	0);
		$this->color['gray']	= imagecolorallocate($i, 170,	170,	200);
		$this->color['dgray']	= imagecolorallocate($i, 110,	110,	110);
		$this->color['red']	= imagecolorallocate($i, 255,	91,	64);
		$this->color['blue']	= imagecolorallocate($i, 28,	50,	213);
		$this->color['orange']	= imagecolorallocate($i, 255,	151,	16);

		$this->color['background']		=& $this->color['white'];
		$this->color['lettering']		=& $this->color['black'];
		$this->color['grid']			=& $this->color['gray'];
		$this->color['month_separator']		=& $this->color['gray'];
		$this->color['free']			=& $this->color['white'];
		$this->color['free_border']		=& $this->color['black'];
		$this->color['unavailable']		=& $this->color['gray'];
		$this->color['unavailable_border']	=& $this->color['dgray'];
		$this->color['booked']			=& $this->color['orange'];
		$this->color['booked_border']		=& $this->color['black'];
		$this->color['periodic_booked']		=& $this->color['orange']; /* was red */
		$this->color['periodic_booked_border']	=& $this->color['black'];
		$this->color['unavailability']		=& $this->color['gray'];
		$this->color['unavailability_border']	=& $this->color['dgray'];

		imagecolortransparent($i, $this->color['white']);

		/* background */
		imagefilledrectangle($i, 1, 1, $width - 2, $height - 2, $this->color['background']);

		/* draw the grid */
		$day_count = $limits['deadline_days'];
		for ($c = $grid_offset['x']; $c <= $grid_offset['x'] + $grid_width; $c += $cell_width) {
			imageline($i, $c, $grid_offset['y'], $c, $grid_offset['y'] + $grid_height, $this->color['grid']);

			if (($day_count * 24 * 60 * 60) < $limits['max_advance_in_sec']) {
				// get day of month ($day) and day of week ($dow)
				$day_expanded = getdate(strtotime(date("Y-m-d") . " + $day_count day"));
				$day = $day_expanded['mday'];
				$dow = $day_expanded['wday'];
				$dow = $dow == 0 ? 6 : $dow - 1;

				if ($day == 1) {
					/* different background for second month */
					/*** TODO BUGGY! We need layers to do that (use three canvas: background, booking, grid) ***/
					// imagefilledrectangle($i, $c, $grid_offset['y'], $grid_offset['x'] + $grid_width, $grid_offset['y'] + $grid_height, $month2);

					/* make vertical line longer and thicker */
					imageline($i, $c, $grid_offset['y'] - 2 * $cell_height, $c, $grid_offset['y'] + $grid_height, $this->color['month_separator']);
					imageline($i, $c + 1, $grid_offset['y'] - 2 * $cell_height, $c + 1, $grid_offset['y'] + $grid_height, $this->color['month_separator']);

					/* print month name */
					$month = JText::_($day_expanded['month']);
					imagestring($i, 2, $c + 8, $grid_offset['y'] - 2.6 * $cell_height, $month, $this->color['lettering']);
				}
				if ($day < 10) {
					imagestring($i, 2, $c + 8, $grid_offset['y'] - $cell_height, $day, $this->color['lettering']);
				} else {
					imagestring($i, 2, $c + 4, $grid_offset['y'] - $cell_height, $day, $this->color['lettering']);
				}
				imagestring($i, 1, $c + 8, $grid_offset['y'] - 1.6 * $cell_height, ucfirst(substr($this->idows[$dow], 0, 1)), $this->color['lettering']);

				$day_count++;
			} else {
				break;
			}
		}

		$hour = 0;
		for ($h = 0; $h <= 24; $h += $this->hour_resolution) {
			imageline($i, $grid_offset['x'], $grid_offset['y'] + $h * $cell_height, $grid_offset['x'] + $grid_width, $grid_offset['y'] + $h * $cell_height, $this->color['grid']);
			if ($h < 24) {
				imagestring($i, 2, 10, $grid_offset['y'] + $h * $cell_height - $cell_height / 2 + 3, $h . ":00", $this->color['lettering']);
			}
		}

		// create a list of all the ID to be mapped
		$rids = array();
		$rids[] = $this->resource_id;

		// get all related resources and form one single array, excluding slave resources (2nd arg)
		$related_resources = $this->_resource_model->getRelatedResources(1,0,1,1);
		foreach ($related_resources as $r) {
			$rids[] = $r['id'];
		}
		$id_set = implode(", ", $rids);
		_log("INFO", "Checking availability on this set of resources: $id_set");

		# get the date range
		$dates = $this->loadAssoc("SELECT DATE(ADDDATE(CURDATE(), INTERVAL deadline DAY)) AS begin_day, DATE(ADDDATE(CURDATE(), INTERVAL max_advance DAY)) AS end_day FROM #__prenotown_resource WHERE id = " . $this->resource_id);
		# system("echo '" . $dates['begin_day'] . ", " . $dates['end_day'] . "' > /tmp/dbg");

		# query the booking ids
		$ids = $this->loadResultArray("SELECT id FROM #__prenotown_superbooking WHERE group_id <> 2 AND resource_id IN ($id_set) AND (end >= '" . $dates['begin_day'] . "' OR begin <= '" . $dates['end_day'] . "')");

		# load all the booking ids
		foreach ($ids as $bid) {
			$this->query("CALL #__prenotown_expand_booking($bid, @cost, 0)");
		}

		# remove days before the beginning and after the end
		$this->query("DELETE FROM #__prenotown_booking_expansion WHERE begin_date < '" . $dates['begin_day'] . "' OR end_date >= '" . $dates['end_day'] . "'");
		$this->query("UPDATE #__prenotown_booking_expansion SET begin_date_sec = DATEDIFF(SUBDATE(begin_date, INTERVAL HOUR('" . $limits['deadline'] . "') HOUR), NOW())");
		$this->query("UPDATE #__prenotown_booking_expansion SET end_date_sec = DATEDIFF(SUBDATE(end_date, INTERVAL HOUR('" . $limits['deadline'] . "') HOUR), NOW())");
		$this->query("UPDATE #__prenotown_booking_expansion SET begin_time_sec = HOUR(begin_time) * 60 + MINUTE(begin_time)");
		$this->query("UPDATE #__prenotown_booking_expansion SET end_time_sec = HOUR(end_time) * 60 + MINUTE(end_time)");

		$entries = $this->loadAssocList("SELECT * FROM #__prenotown_booking_expansion ORDER BY begin_date, begin_time");
		if (isset($entries) && is_array($entries)) {
			foreach ($entries as $e) {
				if (!$e['excepted']) {
					$this->draw_booking($i, $e['begin_date_sec'], $e['begin_time_sec'], $e['end_date_sec'], $e['end_time_sec'],
						$this->color['booked'], $this->color['booked_border']);
				}
			}
		}

		/* draw availability range */
		if ($this->resource->availability_enabled) {
			// select starting and stopping date
			// $start_day = $day = $this->loadResult("SELECT UNIX_TIMESTAMP(ADDDATE(CURDATE(), deadline)) FROM jos_prenotown_resource WHERE id = '$this->resource_id'");
			// $end_day = $this->loadResult("SELECT UNIX_TIMESTAMP(ADDDATE(CURDATE(), max_advance)) FROM #__prenotown_resource WHERE id = '$this->resource_id'");
			$start_day = $day = strtotime(date("Y-m-d") . " + " . $this->limits['deadline_days'] . " day") + 12 * 3600;
			$end_day = strtotime(date("Y-m-d") . " + " . $this->limits['max_advance_days'] . " day");

			while ($day < $end_day - 12 * 60 * 60 + 1) {
				/* load day of week */
				$data = getdate($day);
				# $dow = $this->loadResult("SELECT WEEKDAY('$day_date')");

				$relative_day = ($day - $start_day) / 24 / 60 / 60;

				/* get begin time */
				# $day_begin = $this->dows[$dow] . '_begin';
				$day_begin = strtolower($data['weekday'] . '_begin');
				$day_begin = $this->resource->$day_begin;

				/* get end time */
				# $day_end = $this->dows[$dow] . '_end';
				$day_end = strtolower($data['weekday'] . '_end');
				$day_end = $this->resource->$day_end;

				if ($fh = fopen("/tmp/text.txt", "a+")) {
					fwrite($fh, date("Y-m-d", $day) . " is a " . $data['weekday'] . "\n");
					fclose($fh);
				}

				/* draw availability */
				$this->draw_booking($i, $relative_day, 0, $relative_day, $this->timeToSec($day_begin) / 60, $this->color['unavailable'], $this->color['unavailable_border']);
				$this->draw_booking($i, $relative_day, $this->timeToSec($day_end) / 60, $relative_day, 1440, $this->color['unavailable'], $this->color['unavailable_border']);

				// next day is another day :)
				$day += 24 * 60 * 60;
			}
		}

		// ------------------------------------ RIPRENDERE DA QUI E FAR STAMPARE SUL GRAFICO I GIORNI DI NON DISPONIBILITA' ---------------------------
		# query the booking ids
		$this->query("DROP TABLE #__prenotown_booking_expansion");
		$ids = $this->loadResultArray("SELECT id FROM #__prenotown_superbooking WHERE group_id = 2 AND resource_id IN ($id_set) AND (end >= '" . $dates['begin_day'] . "' OR begin <= '" . $dates['end_day'] . "')");

		# load all the booking ids
		foreach ($ids as $bid) {
			$this->query("CALL #__prenotown_expand_booking($bid, @cost, 0)");
		}

		# remove days before the beginning and after the end
		$this->query("DELETE FROM #__prenotown_booking_expansion WHERE begin_date < '" . $dates['begin_day'] . "' OR end_date >= '" . $dates['end_day'] . "'");
		$this->query("UPDATE #__prenotown_booking_expansion SET begin_date_sec = DATEDIFF(SUBDATE(begin_date, INTERVAL HOUR('" . $limits['deadline'] . "') HOUR), NOW())");
		$this->query("UPDATE #__prenotown_booking_expansion SET end_date_sec = DATEDIFF(SUBDATE(end_date, INTERVAL HOUR('" . $limits['deadline'] . "') HOUR), NOW())");
		$this->query("UPDATE #__prenotown_booking_expansion SET begin_time_sec = 0");
		$this->query("UPDATE #__prenotown_booking_expansion SET end_time_sec = 1440");

		$entries = $this->loadAssocList("SELECT * FROM #__prenotown_booking_expansion ORDER BY begin_date, begin_time");
		if (isset($entries) && is_array($entries)) {
			foreach ($entries as $e) {
				$this->draw_booking($i, $e['begin_date_sec'], $e['begin_time_sec'], $e['end_date_sec'], $e['end_time_sec'], 
					$this->color['unavailability'], $this->color['unavailability_border']);
			}
		}

		/* draw legend */
		$ypos = $grid_height + $grid_offset['y'] + 12;
		$xpos = 60;
		$xdelta = 140;

		imagefilledrectangle($i, $xpos, $ypos, $xpos + $cell_width, $ypos + $cell_height, $this->color['free']);
		imagerectangle($i, $xpos, $ypos, $xpos + $cell_width, $ypos + $cell_height, $this->color['free_border']);
		imagefilledrectangle($i, $xpos + $this->grid_3d_spanning, $ypos + $this->grid_3d_spanning, $xpos + $cell_width + $this->grid_3d_spanning, $ypos + $this->grid_3d_spanning + $cell_height, $this->color['periodic_booked']);
		imagerectangle($i, $xpos + $this->grid_3d_spanning, $ypos + $this->grid_3d_spanning, $xpos + $this->grid_3d_spanning + $cell_width, $ypos + $this->grid_3d_spanning + $cell_height, $this->color['periodic_booked_border']);
		$xpos += $cell_width * 1.4;

		/*
		imagefilledrectangle($i, $xpos, $ypos, $xpos + $cell_width, $ypos + $cell_height, $this->color['free']);
		imagerectangle($i, $xpos, $ypos, $xpos + $cell_width, $ypos + $cell_height, $this->color['free_border']);
		imagefilledrectangle($i, $xpos + $this->grid_3d_spanning, $ypos + $this->grid_3d_spanning, $xpos + $cell_width + $this->grid_3d_spanning, $ypos + $this->grid_3d_spanning + $cell_height, $this->color['booked']);
		imagerectangle($i, $xpos + $this->grid_3d_spanning, $ypos + $this->grid_3d_spanning, $xpos + $this->grid_3d_spanning + $cell_width, $ypos + $this->grid_3d_spanning + $cell_height, $this->color['booked_border']);
		*/
		imagestring($i, 2, $xpos + 10, $ypos + 2, JText::_("Booked"), $this->color['lettering']);
		$xpos += $xdelta;

		imagefilledrectangle($i, $xpos, $ypos, $xpos + $cell_width, $ypos + $cell_height, $this->color['free']);
		imagerectangle($i, $xpos, $ypos, $xpos + $cell_width, $ypos + $cell_height, $this->color['free_border']);
		imagefilledrectangle($i, $xpos + $this->grid_3d_spanning, $ypos + $this->grid_3d_spanning, $xpos + $this->grid_3d_spanning + $cell_width, $ypos + $this->grid_3d_spanning + $cell_height, $this->color['unavailable']);
		imagerectangle($i, $xpos + $this->grid_3d_spanning, $ypos + $this->grid_3d_spanning, $xpos + $this->grid_3d_spanning + $cell_width, $ypos + $this->grid_3d_spanning + $cell_height, $this->color['unavailable_border']);
		imagestring($i, 2, $xpos + $cell_width + 10, $ypos + 2, JText::_("Unavailable"), $this->color['lettering']);
		$xpos += $xdelta;

		imagefilledrectangle($i, $xpos, $ypos, $xpos + $cell_width, $ypos + $cell_height, $this->color['free']);
		imagerectangle($i, $xpos, $ypos, $xpos + $cell_width, $ypos + $cell_height, $this->color['free_border']);
		imagestring($i, 2, $xpos + $cell_width + 10, $ypos + 2, JText::_("Free"), $this->color['lettering']);
		$xpos += $xdelta;

		/* Avoid controller redirect */
		JRequest::setVar('noRedirect', 1);

		/* output the image */
		imagepng($i);

		/* cache the image */
		imagepng($i, $cached_image_path);

		# imagepng($i, "/var/www/trezzo/tmp/availability.png");
		imagedestroy($i);

		/* return value of this method is ignored since it outputs RAW image data */
		return Array();
	}

	/**
	 * return the HTML interface to book the resource
	 *
	 * Called: by view resource/tmpl/book.php
	 */
	function bookingInterface()
	{
		global $prenotown_user;

		JHTML::_('behavior.calendar'); //load the calendar behavior
		$document =& JFactory::getDocument();

		if (!_status('user')) {
			forceLogin();
			return;
		}

		// prepare translations
		$please_select_a_begin_date = JText::_("Please select a begin date");
		$how_should_i_book_this_in_the_past_man = JText::_("How should I book this in the past, man?");
		$end_date_preceeds_begin_date = JText::_("End date preceeds begin date");
		$end_hour_preceeds_begin_hour = JText::_("End hour preceeds begin hour");
		$zero_or_negative_booking_not_allowed = JText::_("Zero or negative prenotation not allowed");
		$cant_book_after = JText::_("Can't book after");
		$cant_book_before = JText::_("Can't book before");
		$you_are_booking_for = JText::_("you are booking for");
		$provide_periodicity = JText::_("Please select at least one week day");
		$booking_not_in_unit = JText::_("Your booking is not a multiple of booking unit");

		$max_advance_in_millisec = $this->_resource_model->getMaxAdvanceInMillisec();
		$deadline_in_millisec = $this->_resource_model->getDeadlineInMillisec();

		$min_book = $this->timeToSec($this->limits['deadline']);
		if ($min_book < 60) {
			$min_book = $min_book . " " . strtolower(JText::_("seconds"));
		} else if ($min_book < 60 * 60) {
			$min_book = $min_book / 60 . " " . strtolower(JText::_("minutes"));
		} else if ($min_book < 24 * 60 * 60) {
			$min_book = $min_book / 60 / 60 . " " . strtolower(JText::_("hours"));
		} else {
			$min_book = $min_book / 60 / 60 / 24 . " " . strtolower(JText::_("days"));
		}

		$max_book = $this->limits['max_advance_in_sec'];
		if ($max_book < 60) {
			$max_book = $max_book . " " . strtolower(JText::_("seconds"));
		} else if ($max_book < 60 * 60) {
			$max_book = $max_book / 60 . " " . strtolower(JText::_("minutes"));
		} else if ($max_book < 24 * 60 * 60) {
			$max_book = $max_book / 60 / 60 . " " . strtolower(JText::_("hours"));
		} else {
			$max_book = $max_book / 60 / 60 / 24 . " " . strtolower(JText::_("days"));
		}

		numbullet('Limits');
		echo "<ul>";
		echo "<li>" . JText::sprintf("You must book at least %s before, and you can book up to %s", $min_book, $max_book);
		echo "<br/>";
		echo "<li>" . JText::sprintf("You can book by slot of %d %s", $this->profile->measure_unit_value, JText::_($this->profile->measure_unit_base));
		echo "</ul>";

		numbullet('Availability');
		echo '<div id="booking-availability">';
		echo "<img src=\"index.php?option=com_prenotown&task=cost_function&resource_id=$this->resource_id&class=prenotownTimeCostFunction&method=bookingAvailability&noRedirect=1&contentType=image/png&format=raw&randomizer=" . rand(0, 1000) . "\">";
		echo '</div>';

		numbullet('Your booking');

		echo "<script language=\"javascript\">\n";

		$query = "
SELECT DATE(ADDDATE(NOW(), deadline)) AS begin_date,
	DATE(ADDDATE(NOW(), max_advance)) AS end_date,
	DAYOFYEAR(DATE(ADDDATE(NOW(), deadline))) AS begin_day,
	DAYOFYEAR(DATE(ADDDATE(NOW(), max_advance))) AS end_day, 
	WEEKDAY(DATE(ADDDATE(NOW(), deadline))) AS weekday
FROM #__prenotown_resource
WHERE id = $this->resource_id";
		$limits = $this->loadAssoc($query);

		echo "var first_valid_day = '" . $limits['begin_date'] . "';";
		echo "var last_valid_day = '" . $limits['end_date'] . "';";

		// insert javascript support for availability ranges
		if ($this->resource->availability_enabled) {
			echo "var availability_enabled = true;\n";
		} else {
			echo "var availability_enabled = false;";
		}

		// The authenticated user is the resource admin?
		if ($prenotown_user['id'] == $this->resource->admin_id) {
			echo "var user_is_admin = true;";
		} else {
			echo "var user_is_admin = false;";
		}

		// booking interface javascript checks
		echo <<< JSEND
	function check_booking_form() {
		var periodic = $('periodic').value * 1;
		if (periodic) {
			periodic = "periodic_";
			var periodicity =
				$('monday').checked	*  1 +
				$('tuesday').checked	*  2 +
				$('wednesday').checked	*  4 +
				$('thursday').checked	*  8 +
				$('friday').checked	* 16 +
				$('saturday').checked	* 32 +
				$('sunday').checked	* 64;
			if (periodicity == 0) {
				alert("$provide_periodicity");
				return;
			}
		} else {
			periodic = "";
		}

		var begin_date = $(periodic + 'booking_begin_date').value;

		// null begin date
		if (begin_date == "") {
			alert("$please_select_a_begin_date");
			return;
		}

		var end_date = $(periodic + 'booking_end_date').value;

		// null end date
		if (end_date == "") {
			$(periodic + 'booking_end_date').value = begin_date;
			end_date = $(periodic + 'booking_end_date').value;
		}

		// parse begin and end dates
		var begin	= begin_date.split(/-/);
		var end		= end_date.split(/-/);

		var begin_date_object	= new Date(begin[1] + "/" + begin[0] + "/" + begin[2]);
		var end_date_object	= new Date(end[1] + "/" + end[0] + "/" + end[2]);

		if (! user_is_admin) {
			if (begin_date_object.getTime() <= (new Date()).getTime()) {
				alert("$how_should_i_book_this_in_the_past_man");
				return;
			}
		}

		// begin date before end date
		if (end_date_object < begin_date_object) {
			alert("$end_date_preceeds_begin_date");
			return;
		}

		var begin_hour = $(periodic + 'booking_begin_hour').value * 1.0;
		var end_hour = $(periodic + 'booking_end_hour').value * 1.0;

		var begin_minute = $(periodic + 'booking_begin_minute').value * 1.0;
		var end_minute = $(periodic + 'booking_end_minute').value * 1.0;

		if (end_date_object.getTime() == begin_date_object.getTime()) {
			// begin hour before end hour
			if (end_hour < begin_hour) {
				alert("$end_hour_preceeds_begin_hour");
				return;
			}

			if (end_hour == begin_hour) {
				// begin minute before or equal to begin minute
				if (end_minute <= begin_minute) {
					alert("$zero_or_negative_booking_not_allowed");
					return;
				}
			}
		}

		begin_date_object.setHours(begin_hour);
		end_date_object.setHours(end_hour);

		begin_date_object.setMinutes(begin_minute);
		end_date_object.setMinutes(end_minute);

		// with the only exception of resource admin...
		if (! user_is_admin) {
			// check advance
			var advance_in_millisec = $max_advance_in_millisec;

			var max_advance = new Date();
			max_advance.setHours(0);
			max_advance.setMinutes(0);
			max_advance.setSeconds(0);
			max_advance.setMilliseconds(0);
			max_advance.setTime(max_advance.getTime() + advance_in_millisec);

			if (begin_date_object.getTime() >= max_advance.getTime()) {
				alert("$cant_book_after" + " " + max_advance.getDate() + "/" + (1 + max_advance.getMonth()) + "/" + (1900 + max_advance.getYear()) + " (" + "$you_are_booking_for" + ": " + begin_date_object.getDate() + "/" + (1 + begin_date_object.getMonth()) + "/" + (1900 + begin_date_object.getYear()) + ")");
				return;
			}

			// check deadline
			var deadline_in_millisec = $deadline_in_millisec;

			var deadline = new Date();
			deadline.setHours(0);
			deadline.setMinutes(0);
			deadline.setSeconds(0);
			deadline.setMilliseconds(0);
			deadline.setTime(deadline.getTime() + deadline_in_millisec);

			if (begin_date_object.getTime() < deadline.getTime()) {
				alert("$cant_book_before" + " " + deadline.getDate() + "/" + (1 + deadline.getMonth()) + "/" + (1900 + deadline.getYear()) + " (" + "$you_are_booking_for" + ": " + begin_date_object.getDate() + "/" + (1 + begin_date_object.getMonth()) + "/" + (1900 + begin_date_object.getYear()) + ")");
				return;
			}
		}

		$('booking-form').submit();
	}

	// toggle interface between single and periodic booking
	// copies all the values from one to the other
	function toggleBookingInterface(obj) {
		var obj = document.getElementById('periodic');
		if (obj.value == 0) {
			obj.value = 1;
			$('periodic_booking_begin_date').value = $('booking_begin_date').value;
			$('periodic_booking_begin_hour').selectedIndex = $('booking_begin_hour').selectedIndex;
			$('periodic_booking_begin_minute').selectedIndex = $('booking_begin_minute').selectedIndex;

			$('periodic_booking_end_date').value = $('booking_end_date').value;
			$('periodic_booking_end_hour').selectedIndex = $('booking_end_hour').selectedIndex;
			$('periodic_booking_end_minute').selectedIndex = $('booking_end_minute').selectedIndex;

			$('single-booking-interface').style.display = 'none';
			$('periodic-booking-interface').style.display = 'block';
		} else {
			obj.value = 0;
			$('booking_begin_date').value = $('periodic_booking_begin_date').value;
			$('booking_begin_hour').selectedIndex = $('periodic_booking_begin_hour').selectedIndex;
			$('booking_begin_minute').selectedIndex = $('periodic_booking_begin_minute').selectedIndex;

			$('booking_end_date').value = $('periodic_booking_end_date').value;
			$('booking_end_hour').selectedIndex = $('periodic_booking_end_hour').selectedIndex;
			$('booking_end_minute').selectedIndex = $('periodic_booking_end_minute').selectedIndex;

			$('single-booking-interface').style.display = 'block';
			$('periodic-booking-interface').style.display = 'none';
		}
	}
</script>
JSEND;

		echo '<form method="POST" action="index.php" id="booking-form" name="booking-form">';
		echo '<input type="hidden" name="option" value="com_prenotown"/>';
		echo '<input type="hidden" name="view" value="resource"/>';
		echo '<input type="hidden" name="layout" value="paybooking"/>';
		echo '<input type="hidden" name="id" value="' . $this->resource_id . '"/>';
		echo '<input type="hidden" name="resource_id" value="' . $this->resource_id . '"/>';
		echo '<input type="hidden" name="task" value="cost_function"/>';
		echo '<input type="hidden" name="class" value="PrenotownTimeCostFunction"/>';
		echo '<input type="hidden" name="method" value="checkBookingAvailability"/>';

		$periodic = JRequest::getInt('periodic', 0);

		if (_has_ghost_group()) {
			echo '&nbsp;&nbsp;<input type="hidden" name="periodic" id="periodic" value="' . $periodic . '"/>';
			echo JText::_("Periodic booking") . '?';
			# echo "</legend>";
			if ($periodic) {
				echo "<input type=\"radio\" type=\"hidden\" name=\"periodic-yn\" onClick=\"toggleBookingInterface()\" checked>" . JText::_("Yes");
				echo "<input type=\"radio\" type=\"hidden\" name=\"periodic-yn\" onClick=\"toggleBookingInterface()\">" . JText::_("No");
			} else {
				echo "<input type=\"radio\" type=\"hidden\" name=\"periodic-yn\" onClick=\"toggleBookingInterface()\">" . JText::_("Yes");
				echo "<input type=\"radio\" type=\"hidden\" name=\"periodic-yn\" onClick=\"toggleBookingInterface()\" checked>" . JText::_("No");
			}
			echo "<br/><br/>";
			# echo "</fieldset>";
		} else {
			echo '<input type="hidden" name="periodic" id="periodic" value="0"/>';
		}

		if ($periodic) {
			echo '<div id="single-booking-interface" style="display: none">';
		} else {
			echo '<div id="single-booking-interface">';
		}

		$booking_begin_date = JRequest::getString('booking_begin_date', strftime('%d-%m-%Y', time() + $this->timeToSec($this->limits['deadline'])));
		$booking_begin_hour = JRequest::getInt('booking_begin_hour', 0);
		$booking_begin_minute = JRequest::getInt('booking_begin_minute', 0);

		$booking_end_date = JRequest::getString('booking_end_date', strftime('%d-%m-%Y', time() + $this->timeToSec($this->limits['deadline'])));
		$booking_end_hour = JRequest::getInt('booking_end_hour', 0);
		$booking_end_minute = JRequest::getInt('booking_end_minute', 0);

		$periodic_booking_begin_date = JRequest::getString('periodic_booking_begin_date', strftime('%d-%m-%Y', time() + $this->timeToSec($this->limits['deadline'])));
		$periodic_booking_begin_hour = JRequest::getInt('periodic_booking_begin_hour', 0);
		$periodic_booking_begin_minute = JRequest::getInt('periodic_booking_begin_minute', 0);

		$periodic_booking_end_date = JRequest::getString('periodic_booking_end_date', strftime('%d-%m-%Y', time() + $this->timeToSec($this->limits['deadline'])));
		$periodic_booking_end_hour = JRequest::getInt('periodic_booking_end_hour', 0);
		$periodic_booking_end_minute = JRequest::getInt('periodic_booking_end_minute', 0);

		echo '<fieldset><legend>' . JText::_("Booking begin") . "</legend>";
		echo '<table style="width: auto"><tr><td style="width:90px; text-align:right">' . JText::_("Begin date") . "</td><td>";

		// calendar control
		$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
		inputField     :    "booking_begin_date",
		ifFormat       :    "%d-%m-%Y",
		button         :    "booking_begin_date_img",
		align          :    "Tl",
		singleClick    :    true,
		onUpdate       :    syncDates
		});});');   
		echo '<input type="text" name="booking_begin_date" id="booking_begin_date" value="';
		echo htmlspecialchars($booking_begin_date, ENT_COMPAT, 'UTF-8') . '"/>';
		echo ' <img class="calendar" src="' . JURI::root(true);
		echo '/templates/system/images/calendar.png" alt="calendar" id="booking_begin_date_img" />';

		echo "&nbsp;&nbsp;" . JText::_("at") . '</td><td>';
		echo $this->hourMinuteSelector('booking_begin');

		echo "</td></tr></table></fieldset>";
		echo '<fieldset><legend>' . JText::_("Booking end") . "</legend>";

		echo '<table style="width: auto"><tr><td style="width:90px; text-align:right">' . JText::_("End date") . '</td><td>';

		// calendar control
		$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
		inputField     :    "booking_end_date",
		ifFormat       :    "%d-%m-%Y",
		button         :    "booking_end_date_img",
		align          :    "Tl",
		singleClick    :    true,
		onUpdate       :    syncDates
		});});');   
		echo '<input type="text" name="booking_end_date" id="booking_end_date" value="';
		echo htmlspecialchars($booking_end_date, ENT_COMPAT, 'UTF-8') . '"/>';
		echo ' <img class="calendar" src="' . JURI::root(true);
		echo '/templates/system/images/calendar.png" alt="calendar" id="booking_end_date_img" />';

		echo "&nbsp;&nbsp;" . JText::_("at") . "</td><td>";
		echo $this->hourMinuteSelector('booking_end');

		echo '</td></tr></table></div>';

		if (_has_ghost_group()) {
			if ($periodic) {
				echo '<div id="periodic-booking-interface">';
			} else {
				echo '<div id="periodic-booking-interface" style="display: none">';
			}
			echo '<fieldset><legend>' . JText::_("Booking from:") . "</legend><table><tr><td>" . JText::_("From:") . "&nbsp;&nbsp;";

			// calendar control
			$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
			inputField     :    "periodic_booking_begin_date",
			ifFormat       :    "%d-%m-%Y",
			button         :    "periodic_booking_begin_date_img",
			align          :    "Tl",
			singleClick    :    true,
			onUpdate       :    syncDates
			});});');   
			echo '<input type="text" name="periodic_booking_begin_date" id="periodic_booking_begin_date" value="';
			echo htmlspecialchars($periodic_booking_begin_date, ENT_COMPAT, 'UTF-8') . '"/>';
			echo ' <img class="calendar" src="' . JURI::root(true);
			echo '/templates/system/images/calendar.png" alt="calendar" id="periodic_booking_begin_date_img" />';

			echo '&nbsp;&nbsp;&nbsp; ' . JText::_("up to:") . "&nbsp;&nbsp;";

			// calendar control
			$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
			inputField     :    "periodic_booking_end_date",
			ifFormat       :    "%d-%m-%Y",
			button         :    "periodic_booking_end_date_img",
			align          :    "Tl",
			singleClick    :    true,
			onUpdate       :    syncDates
			});});');
			echo '<input type="text" name="periodic_booking_end_date" id="periodic_booking_end_date" value="';
			echo htmlspecialchars($periodic_booking_end_date, ENT_COMPAT, 'UTF-8') . '"/>';
			echo ' <img class="calendar" src="' . JURI::root(true);
			echo '/templates/system/images/calendar.png" alt="calendar" id="periodic_booking_end_date_img" />';

			echo '</td></tr></table></fieldset>';
			echo '<fieldset><legend>' . JText::_("Booking valid on:") . "</legend>";
			echo '<table style="margin-left:0px"><tr>';
			foreach (array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') as $day) {
				echo '<td style="text-align:center">';
				echo JText::_($day);
				echo '<br><input type="checkbox" name="' . strtolower($day) . '" id="' . strtolower($day) . '" value="1"';
				$day_checked = JRequest::getInt(strtolower($day), "");
				echo $day_checked ? " checked" : "";
				echo '/></td>';
			}
			echo '</tr></table>';
			echo '</fieldset>';

			echo '<fieldset><legend>' . JText::_("Time range") . '</legend>';

			echo '<table style="width: auto"><tr><td style="text-align: right">' . JText::_("Booking begins at:") . '</td><td>';
			echo $this->hourMinuteSelector('periodic_booking_begin');

			echo '</td><td style="text-align: right">' . JText::_("Booking ends at:") . '</td><td>';
			echo $this->hourMinuteSelector('periodic_booking_end');

			echo '</td></tr></table></div>';
		}

		echo '</form>';

		echo '<div class="button-footer">';
		echo '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=resource&layout=default&id=' . $this->resource_id . '&Itemid=' . JRequest::getInt("ItemID", 0) . '\')">' . JText::_("Back") . "</button>";
		$this->hSeparator();
		echo '<button class="button" onClick="check_booking_form()">' . JText::_("Proceed") . '</button>';
		echo '</div>';

		echo <<< JSEND
<script language="javascript" type="text/javascript">

var hour_resolution = $this->hour_resolution;
var minute_resolution = $this->minute_resolution;

function updatePulldown(menu, start, stop, resolution) {
	return;	
	// saving current
	var current_index = $(menu).selectedIndex;
	var current_hour = $(menu).options[current_index].value.replace(/^(\d\d?).*$/, '$1');

	// clear previous entries
	for (i = $(menu).options.length - 1; i >= 0; i--) {
		if (BrowserDetect.browser == "Explorer") {
			$(menu).removeChild($(menu).childNodes[i]);
		} else {
			$(menu).remove(i);
		}
	}

	// add new ones
	var position = 0;
	for (i = start; i <= stop; i = i * 1.0 + resolution) {
		var opt = new Option(i * 1.0, i * 1.0);
		if (BrowserDetect.browser == "Explorer") {
			$(menu).add(opt, position); // IE!
		} else {
			$(menu).add(opt, null);
		}

		position = position + 1;

		if (current_hour == i * 1.0) {
			opt.selected = true;
		}
	}
}

function syncHour(select_id, day_date) {
	if (availability_enabled) {
		var begin_time = availability[day_date]['begin'];
		var end_time = availability[day_date]['end'];

		var splitted_begin_time = begin_time.split(':');
		var splitted_end_time = end_time.split(':');

		updatePulldown(select_id, splitted_begin_time[0], splitted_end_time[0], hour_resolution);
	}
}

function syncDates() {
	if ($('periodic') && $('periodic').value * 1) {
		/*
		 * check date formats:
		 * admin has no limits
		 * groups can book from first valid day up to the future (year 3000 is enough?)
		 */
		if (! user_is_admin) {
			checkDate('periodic_booking_begin_date', first_valid_day, '3000-01-01');
			checkDate('periodic_booking_end_date', first_valid_day, '3000-01-01');
		} else {
			checkDate('periodic_booking_begin_date', '0001-01-01', '3000-01-01');
			checkDate('periodic_booking_end_date', '0001-01-01', '3000-01-01');
		}
		
		var begin_day = $('periodic_booking_begin_date').value.replace(/(\d\d)-(\d\d)-(\d\d\d\d)/, '$3-$2-$1');
		var end_day = $('periodic_booking_end_date').value.replace(/(\d\d)-(\d\d)-(\d\d\d\d)/, '$3-$2-$1');
		if (end_day < begin_day) {
			end_day = begin_day;
			$('periodic_booking_end_date').value = $('periodic_booking_begin_date').value;
		}
	} else {
		/*
		 * check date formats:
		 * admin has no limits
		 * normal users has is limited on first and last date
		 */
		if (! user_is_admin) {
			checkDate('booking_begin_date', first_valid_day, last_valid_day);
			checkDate('booking_end_date', first_valid_day, last_valid_day);
		} else {
			checkDate('booking_begin_date', '0001-01-01', '3001-01-01');
			checkDate('booking_end_date', '0001-01-01', '3001-01-01');
		}

		// end date can't preceed begin_date, don't you agree?
		var begin_day = $('booking_begin_date').value.replace(/(\d\d)-(\d\d)-(\d\d\d\d)/, '$3-$2-$1');
		var end_day = $('booking_end_date').value.replace(/(\d\d)-(\d\d)-(\d\d\d\d)/, '$3-$2-$1');
		if (end_day < begin_day) {
			end_day = begin_day;
			$('booking_end_date').value = $('booking_begin_date').value;
		}

		// change hours and minutes in pulldown menus according to availability range
		// syncHour('booking_begin_hour', begin_day);
		// syncHour('booking_end_hour', end_day);
	}
}

function init_interface() {
	$('booking_begin_date').addEvent('blur', syncDates);
	$('booking_end_date').addEvent('blur', syncDates);
	if ($('periodic_booking_begin_date')) {
		$('periodic_booking_begin_date').addEvent('blur', syncDates);
	}
	if ($('periodic_booking_end_date')) {
		$('periodic_booking_end_date').addEvent('blur', syncDates);
	}

//	if (availability_enabled) {
//		var splitted_begin_time = availability[first_valid_day]['begin'].split(':');
//		var splitted_end_time = availability[first_valid_day]['end'].split(':');
//		// updatePulldown('booking_begin_hour', splitted_begin_time[0], splitted_end_time[0], hour_resolution);
//		// updatePulldown('booking_end_hour', splitted_begin_time[0], splitted_end_time[0], hour_resolution);
//
//		if ($('periodic')) {
//			// updatePulldown('periodic_booking_begin_hour', splitted_begin_time[0], splitted_end_time[0], hour_resolution);	
//			// updatePulldown('periodic_booking_end_hour', splitted_begin_time[0], splitted_end_time[0], hour_resolution);	
//		}
//	}

}

init_interface();

</script>
JSEND;

		echo "<br/>";
		echo "<br/>";
		echo "<br/>";
	}

	/**
	 * Return booking extension splitted by components
	 *
	 * Called: only internally
	 */
	private function splittedBooking()
	{
		$periodic = JRequest::getInt('periodic', 0);

		if ($periodic) {
			/* get begin and end dates */
			$booking_begin_date = JRequest::getString('periodic_booking_begin_date', '00-00-0000');
			$booking_end_date = JRequest::getString('periodic_booking_end_date', '00-00-0000');

			/* get begin and end hours */
			$booking_begin_hour = JRequest::getInt('periodic_booking_begin_hour', 0);
			$booking_end_hour = JRequest::getInt('periodic_booking_end_hour', 0);

			/* get begin and end minutes */
			$booking_begin_minute = JRequest::getInt('periodic_booking_begin_minute', 0);
			$booking_end_minute = JRequest::getInt('periodic_booking_end_minute', 0);
		} else {
			/* get begin and end dates */
			$booking_begin_date = JRequest::getString('booking_begin_date', '00-00-0000');
			$booking_end_date = JRequest::getString('booking_end_date', '00-00-0000');

			/* get begin and end hours */
			$booking_begin_hour = JRequest::getInt('booking_begin_hour', 0);
			$booking_end_hour = JRequest::getInt('booking_end_hour', 0);

			/* get begin and end minutes */
			$booking_begin_minute = JRequest::getInt('booking_begin_minute', 0);
			$booking_end_minute = JRequest::getInt('booking_end_minute', 0);
		}

		// JError::raiseNotice(500, "Working on booking $booking_begin_date $booking_begin_hour:$booking_begin_minute - $booking_end_date $booking_end_hour:$booking_end_minute");

		/* split begin and end dates; 0: day, 1: month, 2: year */
		$booking_begin_date_array = preg_split('/-/', $booking_begin_date);
		$booking_end_date_array = preg_split('/-/', $booking_end_date);

		$splitted = Array();

		$splitted['begin']['sqldate'] = $booking_begin_date_array[2] . "-" . $booking_begin_date_array[1] . "-" . $booking_begin_date_array[0];
		$splitted['begin']['date'] = $booking_begin_date;
		$splitted['begin']['year'] = $booking_begin_date_array[2];
		$splitted['begin']['month'] = $booking_begin_date_array[1];
		$splitted['begin']['day'] = $booking_begin_date_array[0];
		$splitted['begin']['hour'] = sprintf("%02d", $booking_begin_hour);
		$splitted['begin']['minute'] = sprintf("%02d", $booking_begin_minute);
		$splitted['begin']['time'] = $splitted['begin']['hour'] . ":" . $splitted['begin']['minute'] . ":00";

		// JError::raiseNotice(500, "Booking begin instant: " . $splitted['begin']['sqldate'] . " " . $splitted['begin']['time']);

		$splitted['end']['sqldate'] = $booking_end_date_array[2] . "-" . $booking_end_date_array[1] . "-" . $booking_end_date_array[0];
		$splitted['end']['date'] = $booking_end_date;
		$splitted['end']['year'] = $booking_end_date_array[2];
		$splitted['end']['month'] = $booking_end_date_array[1];
		$splitted['end']['day'] = $booking_end_date_array[0];
		$splitted['end']['hour'] = sprintf("%02d", $booking_end_hour);
		$splitted['end']['minute'] = sprintf("%02d", $booking_end_minute);
		$splitted['end']['time'] = $splitted['end']['hour'] . ":" . $splitted['end']['minute'] . ":00";

		// JError::raiseNotice(500, "Booking end instant: " . $splitted['end']['sqldate'] . " " . $splitted['end']['time']);

		// time difference in seconds
		$splitted['timediff'] = $this->loadResult("SELECT TIMEDIFF('" . $splitted['end']['time'] . "', '" . $splitted['begin']['time'] . "')");
		$splitted['timediff_sec'] = $this->timeToSec($splitted['timediff']);

		return $splitted;
	}

	/**
	 * return a url describing current location
	 *
	 * @return string
	 */
	function selfUrl()
	{
		$uri = parent::selfUrl();

		$url = "index.php?option=com_prenotown&view=" . JRequest::getString('view', '') . "&layout=" . JRequest::getString('layout', 'default');
		$url .= "&id=" . $this->resource_id;
		$url .= "&resource_id=" . $this->resource_id;

		$url .= "&periodic=" . JRequest::getString('periodic', 0);

		// add booking limits
		$url .= "&booking_begin_date=" . JRequest::getString('booking_begin_date', '0000-00-00');
		$url .= "&booking_end_date=" . JRequest::getString('booking_end_date', '0000-00-00');
		$url .= "&booking_begin_hour=" . JRequest::getString('booking_begin_hour', '0000-00-00');
		$url .= "&booking_end_hour=" . JRequest::getString('booking_end_hour', '0000-00-00');
		$url .= "&booking_begin_minute=" . JRequest::getString('booking_begin_minute', '0000-00-00');
		$url .= "&booking_end_minute=" . JRequest::getString('booking_end_minute', '0000-00-00');

		$url .= "&periodic_booking_begin_date=" . JRequest::getString('periodic_booking_begin_date', '0000-00-00');
		$url .= "&periodic_booking_end_date=" . JRequest::getString('periodic_booking_end_date', '0000-00-00');
		$url .= "&periodic_booking_begin_hour=" . JRequest::getString('periodic_booking_begin_hour', '0000-00-00');
		$url .= "&periodic_booking_end_hour=" . JRequest::getString('periodic_booking_end_hour', '0000-00-00');
		$url .= "&periodic_booking_begin_minute=" . JRequest::getString('periodic_booking_begin_minute', '0000-00-00');
		$url .= "&periodic_booking_end_minute=" . JRequest::getString('periodic_booking_end_minute', '0000-00-00');

		// add periodicity
		foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') as $day) {
			$url .= "&$day=" . JRequest::getInt($day, 0);
		}

		return $url;
	}

	/**
	 * Checks if a booking is acceptable
	 *
	 * Called: by controller
	 */
	function checkBookingAvailability()
	{
		global $prenotown_user;

		// is authenticated user the admin of this resource?
		$is_admin = false;
		if ($prenotown_user['id'] == $this->resource->admin_id) {
			$is_admin = true;
		}

		$r = Array();

		if (!_status('user')) {
			forceLogin();
			return $r;
		}

		$booking = $this->splittedBooking();

		// check if booking is a multiple of booking unit
		$booking_unit = $this->profile->measure_unit_value;
		switch ($this->profile->measure_unit_base) {
			case 'weeks':
				$booking_unit *= 7;
			case 'days':
				$booking_unit *= 24;
			case 'hours':
				$booking_unit *= 60;
			case 'minutes':
				$booking_unit *= 60;
			case 'seconds':
		}

		// check if booking range is a multiple of booking unit
		if (!$is_admin) {
			if (floor($booking['timediff_sec'] / $booking_unit) != ($booking['timediff_sec'] / $booking_unit)) {
				_warn("WARN", JText::_("Your booking is not a multiple of booking unit:") . " " .
					$this->profile->measure_unit_value . " " .  JText::_($this->profile->measure_unit_base));
			
				$r['redirect'] = preg_replace('/layout=[^&]+/', 'layout=book', $this->selfUrl());

				_log('FAILED','Booking not a multiple of booking unit');
				return $r;
			}
		}

		$periodic = JRequest::getInt('periodic', 0);
		$periodicity = JRequest::getInt('monday', 0)	*  1 +
			JRequest::getInt('tuesday', 0)		*  2 +
			JRequest::getInt('wednesday', 0)	*  4 +
			JRequest::getInt('thursday', 0)		*  8 +
			JRequest::getInt('friday', 0)		* 16 +
			JRequest::getInt('saturday', 0)		* 32 +
			JRequest::getInt('sunday', 0)		* 64;

		/* craft begin and end delimiters in SQL compliant syntax */
		$begin = sprintf("%04d-%02d-%02d %02d:%02d:00",
			$booking['begin']['year'], $booking['begin']['month'], $booking['begin']['day'],
			$booking['begin']['hour'], $booking['begin']['minute']);

		$end = sprintf("%04d-%02d-%02d %02d:%02d:00",
			$booking['end']['year'], $booking['end']['month'], $booking['end']['day'],
			$booking['end']['hour'], $booking['end']['minute']);

		/* search for overlapping booking */
		$overlap = $this->checkOverlappingBooking($begin, $end, $periodicity);

		$r = Array();

		if (isset($overlap) and $overlap != 0) {
			$r['redirect'] = "index.php?option=com_prenotown&view=resource&layout=book&id=" . $this->resource_id;
			_log('FAILED','Booking unavailable');
		} else {
			$r['message'] = JText::_("Booking is valid");
			$r['redirect'] = "index.php?option=com_prenotown&view=resource&layout=paybooking&id=" . $this->resource_id;
		}

		if ($periodic) {
			$r['redirect'] .= "&periodic_booking_begin_date=" .	$booking['begin']['date'];
			$r['redirect'] .= "&periodic_booking_begin_hour=" .	$booking['begin']['hour'];
			$r['redirect'] .= "&periodic_booking_begin_minute=" .	$booking['begin']['minute'];
			$r['redirect'] .= "&periodic_booking_end_date=" .	$booking['end']['date'];
			$r['redirect'] .= "&periodic_booking_end_hour=" .	$booking['end']['hour'];
			$r['redirect'] .= "&periodic_booking_end_minute=" .	$booking['end']['minute'];
			$r['redirect'] .= "&periodic=1";
			$r['redirect'] .= "&monday=" .				JRequest::getInt('monday', 0);
			$r['redirect'] .= "&tuesday=" .				JRequest::getInt('tuesday', 0);
			$r['redirect'] .= "&wednesday=" .			JRequest::getInt('wednesday', 0);
			$r['redirect'] .= "&thursday=" .			JRequest::getInt('thursday', 0);
			$r['redirect'] .= "&friday=" .				JRequest::getInt('friday', 0);
			$r['redirect'] .= "&saturday=" .			JRequest::getInt('saturday', 0);
			$r['redirect'] .= "&sunday=" .				JRequest::getInt('sunday', 0);

			$exceptions = $this->loadResultArray("SELECT DISTINCT exception_date FROM #__prenotown_exceptions ORDER BY exception_date ASC");
			_log("INFO", "Eccezioni create: " . implode(", ", $exceptions));

			if (count($exceptions)) {
				$r['redirect'] .= "&exceptions=" .		implode(',', $exceptions);
			}
		} else {
			$r['redirect'] .= "&booking_begin_date=" .		$booking['begin']['date'];
			$r['redirect'] .= "&booking_begin_hour=" .		$booking['begin']['hour'];
			$r['redirect'] .= "&booking_begin_minute=" .		$booking['begin']['minute'];
			$r['redirect'] .= "&booking_end_date=" .		$booking['end']['date'];
			$r['redirect'] .= "&booking_end_hour=" .		$booking['end']['hour'];
			$r['redirect'] .= "&booking_end_minute=" .		$booking['end']['minute'];
		}

		$this->query("DROP TABLE IF EXISTS #__prenotown_exceptions");

		return $r;
	}
}
?>
