<?php
/**
 * Prentowon base class for costfunctions
 *
 * @package		Prenotown
 * @subpackage	CostFunctions
 */

/** ensure a valid entry point */
defined('_JEXEC') or die("Restricted Access");

/** import logging facilities */
require_once(JPATH_COMPONENT.DS."assets".DS."logging.php");

/** import session */
require_once(JPATH_COMPONENT.DS."assets".DS."user_session.php");

/**
 * Prenotown base class for costfunctions
 *
 * @version		0.2
 * @package		Prenotown
 * @subpackage	CostFunctions
 * @copyright	XSec
 * @license		GNU/GPL, see LICENSE.php
 */
class PrenotownCostFunction
{
	protected $func_type = 'none';
	protected $db = null;
	protected $resource_id = null;

	function __construct($resource_id)
	{
		/* check if resource id is valid */
		if ((int) $resource_id <= 0) {
			_log('ERROR', "Cost function created without (valid) resource_id [$resource_id]");
			_warn("WARN", JText::_("No (valid) resource_id provided to CostFunction") . "[$resource_id]");
			return null;
		}

		/* store resource id */
		$this->resource_id = (int) $resource_id;

		/* get database connection */
		$this->db =& JFactory::getDBO();

		/* get resource model and initiate it */
		$this->_resource_model =& JModel::getInstance('Resource', 'PrenotownModel');
		if (!isset($this->_resource_model) and $this->_resource_model) {
			_log('ERROR', "Can't create PrenotownModelResource model instance");
			_warn("WARN", JText::_("Can't create PrenotownModelResource model instance"));
			return NULL;
		}
		$this->_resource_model->setId($resource_id);

		/* get resources model */
		$this->_resources_model =& JModel::getInstance('Resources', 'PrenotownModel');
		if (!isset($this->_resources_model) and $this->_resources_model) {
			_log('ERROR', "Can't create PrenotownModelResources model instance");
			_warn("WARN", JText::_("Can't create PrenotownModelResources model instance"));
			return NULL;
		}

		$this->_cf_model =& JModel::getInstance('CostFunction', 'PrenotownModel');
		if (!isset($this->_cf_model) and $this->_cf_model) {
			_log('ERROR', "Can't create PrenotownModelCostFunction model instance");
			_warn("WARN", JText::_("Can't create PrenotownModelCostFunction model instance"));
			return NULL;
		}

		$this->loadLimits();

		return TRUE;
	}

	function __tostring()
	{
		return "PrenotownCostFunction";
	}

	function bookingInterface()
	{
	}

	function getCost()
	{
	}

	function hSeparator()
	{
		echo '&nbsp;|&nbsp;';
	}

	/**
	 * Load resource limits
	 *
	 * Called: here in the class
	 */
	function loadLimits()
	{
		/* load deadline and max_advance limits from the database */
		$this->limits = $this->loadAssoc("SELECT CONCAT(deadline * 24, ':00:00') AS deadline, DATE_ADD(now(), INTERVAL deadline DAY) AS deadline_date, max_advance * 24 * 60 * 60 AS max_advance_in_sec, deadline AS deadline_days, max_advance AS max_advance_days FROM #__prenotown_resource WHERE id = $this->resource_id");

		return $this->limits;
	}

	function daymask($mask)
	{
		$days = array();

		if ($mask &  1) { $days[] = JText::_("monday"); }
		if ($mask &  2) { $days[] = JText::_("tuesday"); }
		if ($mask &  4) { $days[] = JText::_("wednesday"); }
		if ($mask &  8) { $days[] = JText::_("thursday"); }
		if ($mask & 16) { $days[] = JText::_("friday"); }
		if ($mask & 32) { $days[] = JText::_("saturday"); }
		if ($mask & 64) { $days[] = JText::_("sunday"); }

		return implode(', ', $days);
	}

	/**
	 * Check if another booking overlaps the one ranging from $begin to $end.
	 * If found, the overlapping booking ID is returned. Otherwise, 0 is returned.
	 *
	 * @param string $begin Booking begin in format "YYYY-MM-DD HH:MM:SS"
	 * @param string $end Booking end in format "YYYY-MM-DD HH:MM:SS"
	 * @param int $periodiciy periodicity bitmask (less significant bit is Monday, sorry british folks)
	 * @param array $already_checked a list of already checked resource IDs (avoids endless recursion)
	 * @return int
	 */
	function checkOverlappingBooking($begin, $end, $periodicity=0, $already_checked=null)
	{
		_log("INFO", "Checking overlapping for resource $this->resource_id on range [$begin == $end] width periodiciy $periodicity");

		if ($already_checked == null) {
			$this->query("CREATE TEMPORARY TABLE IF NOT EXISTS #__prenotown_exceptions (exception_date DATE NOT NULL)");
			$this->query("DELETE FROM #__prenotown_exceptions");
		}

		/* check if this resource has been already checked */
		if (isset($already_checked) && is_array($already_checked) && in_array($this->resource_id, $already_checked)) {
			return FALSE;
		}

		/* if no ID array is provided, a custom one is created with this resource id in it */
		/* that's performed after creating a custom $already_checked array just to avoid a constant false positive */
		/* on this resource id */
		if (!isset($already_checked) || !is_array($already_checked)) {
			$already_checked = array();
		}

		// error_log("checkOverlappingBooking() is using [" . implode(", ", $already_checked) . "]");

		/* check begin and end with regular expressions */
		if (!preg_match('/(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d)(:(\d\d))?/', $begin)) {
			_warn("WARN", JText::sprintf("costfunction\-\>checkOverlappingBooking() called with wrong or null begin datetime (%s)", $begin));
			return FALSE;
		}

		if (!preg_match('/(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d)(:(\d\d))?/', $end)) {
			_warn("WARN", JText::sprintf("costfunction\-\>checkOverlappingBooking() called with wrong or null end datetime (%s)", $end));
			return FALSE;
		}

		$overlaps = $this->loadResult("SELECT #__prenotown_booking_overlapping(" . querymplode(array($this->resource_id, $begin, $end, $periodicity)) . ")");
		if ($overlaps > 0) {
			if (_status('operator')) {
				_warn("WARN", JText::sprintf("Your booking overlaps number %s and will be rejected", "<a href=\"index.php?option=com_prenotown&view=user&layout=globalbooking&filter_booking_id=$overlaps\">$overlaps</a>"));
			} else {
				_warn("WARN", JText::_("Your booking overlaps another one and will be rejected"));
			}
			return $overlaps;
		} else if ($overlaps < 0) {
			/* booking crosses availability range */
			_warn("WARN", JText::sprintf("Your booking crosses availability range on day %s", $this->daymask(-1 * $overlaps)));
			return -1 * $overlaps;
		}

		// save exceptions
		if ($periodicity) {
			$this->query("CALL #__prenotown_expand_booking_profile('$begin', '$end', 1, $periodicity, 1, 1)");

			$this->query("UPDATE #__prenotown_booking_expansion SET excepted = 2 WHERE (SELECT 1 AS excepted FROM #__prenotown_superbooking WHERE DATE(begin) <= DATE(begin_date) AND DATE(end) >= DATE(begin_date) AND group_id = 2 AND #__prenotown_superbooking.resource_id = $this->resource_id)");

			$this->query("UPDATE #__prenotown_booking_expansion SET excepted = 1 WHERE (SELECT 1 AS excepted FROM #__prenotown_superbooking WHERE DATE(begin) <= DATE(begin_date) AND DATE(end) >= DATE(begin_date) AND group_id <> 2 AND NOT periodic AND TIME(begin) <= TIME(end_time) AND TIME(end) >= TIME(begin_time) AND #__prenotown_superbooking.resource_id = $this->resource_id AND begin_date NOT IN (SELECT exception_date FROM #__prenotown_superbooking_exception WHERE booking_id = #__prenotown_superbooking.id))");

			$this->query("INSERT INTO #__prenotown_exceptions SELECT begin_date FROM #__prenotown_booking_expansion WHERE excepted");
			_log("INFO", "Eccezioni create: " . implode(", ", $this->loadResultArray("SELECT exception_date FROM #__prenotown_exceptions")));
		}

		/* this resource has been checked */
		$already_checked[] = $this->resource_id;

		/* get all related resources */
		$resources = $this->_resource_model->getRelatedResources(1,0,1,1);

		/* recursively check for availability */
		foreach ($resources as $r) {
			_log("NOTICE", "This resource is related to " . $r['name'] . ": checking for availability");

			// select requested costfunction
			$this->_cf_model->tables['main']->id = $r['cost_function_id'];
			$this->_cf_model->tables['main']->load();

			// get costfunction class
			$class = $this->_cf_model->tables['main']->class;

			// create a new instance of this CF class
			$cf = new $class($r['id']);

			// check overlapping in the class
			if (($overlaps = $cf->checkOverlappingBooking($begin, $end, $periodicity, $already_checked)) != 0) {
				JError::raiseNotice(500, JText::_("Your booking overlaps another in resource") . " " . $r['name']);
				return $overlaps;
			}

			unset($cf);
		}

		/* no overlapping, so return FALSE */
		return FALSE;
	}

	/**
	 * Return a url describing this resource
	 *
	 * @return string
	 */
	function selfUrl()
	{
		$uri =& JURI::getInstance();
		return $uri->toString();
	}

	/**
	 * Generate HTML code to enable hour:minute selector
	 */
	function hourMinuteSelector($id)
	{
		$result = '<table class="hm-selector"><tr><td>' . "\n";
        	$result .= '<table class="spinbox" cellpadding="0" cellspacing="0"><tr>' . "\n";
        	$result .= '<td rowspan="2"><b>h</b>&nbsp;<input id="' . $id . '_hour" name="' . $id . '_hour" value="';
		$result .= JRequest::getInt("${id}_hour", 0);
		$result .= '" onChange="check_in_range(\'' . $id . '_hour\', ' . $id . 'MinHour, ' . $id . 'MaxHour);"></td><td>' . "\n";
        	$result .= '<img id="hup" onClick="spin_up_' . $id . '_hour()" src="components/com_prenotown/assets/spin_up.png"/>' . "\n";
        	$result .= '</td></tr><tr><td>' . "\n";
        	$result .= '<img id="hdown" onClick="spin_down_' . $id . '_hour()" src="components/com_prenotown/assets/spin_down.png"/>' . "\n";
        	$result .= '</td></tr></table></td><td style="width:1em">:</td><td>' . "\n";
        	$result .= '<table class="spinbox" cellpadding="0" cellspacing="0">' . "\n";
        	$result .= '<tr><td rowspan="2"><b>m</b>&nbsp;<input id="' . $id . '_minute" name="' . $id . '_minute" value="';
		$result .= JRequest::getInt("${id}_minute", 0);
		$result .= '" onChange="check_in_range(\'' . $id . '_minute\', ' . $id . 'MinMinute, ' . $id . 'MaxMinute);"></td><td>' . "\n";
        	$result .= '<img id="mup" onClick="spin_up_' . $id . '_minute()" src="components/com_prenotown/assets/spin_up.png"/>' . "\n";
        	$result .= '</td></tr><tr><td>' . "\n";
        	$result .= '<img id="mdown" onClick="spin_down_' . $id . '_minute()" src="components/com_prenotown/assets/spin_down.png"/>' . "\n";
        	$result .= '</td></tr></table></td></tr></table>' . "\n";
		$result .= "<script>var ${id}MinHour = 0; var ${id}MaxHour = 23; var ${id}MinMinute = 0; var ${id}MaxMinute = 55;\n";
		$result .= "function spin_up_${id}_hour()     { spin('${id}_hour',   1, ${id}MinHour,   ${id}MaxHour,   1); }\n";
		$result .= "function spin_down_${id}_hour()   { spin('${id}_hour',   0, ${id}MinHour,   ${id}MaxHour,   1); }\n";
		$result .= "function spin_up_${id}_minute()   { spin('${id}_minute', 1, ${id}MinMinute, ${id}MaxMinute, 5); }\n";
		$result .= "function spin_down_${id}_minute() { spin('${id}_minute', 0, ${id}MinMinute, ${id}MaxMinute, 5); }\n";
		$result .= "function check_${id}_hour()       { check_in_range('${id}_hour',   ${id}MinHour,   ${id}MaxHour);   }\n"; 
		$result .= "function check_${id}_minute()     { check_in_range('${id}_minute', ${id}MinMinute, ${id}MaxMinute); }\n"; 
		$result .= "</script>\n";

		return $result;
	}

	function loadAssoc($query) {
		$result = array();
		if (isset($query)) {
			_log_sql($query);
			$this->db->setQuery($query);
			$result = $this->db->loadAssoc();
		}
		return $result;
	}

	function loadAssocList($query) {
		$result = array();
		if (isset($query)) {
			_log_sql($query);
			$this->db->setQuery($query);
			$result = $this->db->loadAssocList();
		}
		return $result;
	}

	function loadResultArray($query) {
		$result = array();
		if (isset($query)) {
			_log_sql($query);
			$this->db->setQuery($query);
			$result = $this->db->loadResultArray();
		}
		return $result;
	}

	function loadResult($query) {
		$result = "";
		if (isset($query)) {
			_log_sql($query);
			$this->db->setQuery($query);
			$result = $this->db->loadResult();
		}
		return $result;
	}

	function query($query) {
		if (isset($query)) {
			_log_sql($query);
			$this->db->setQuery($query);
			$result = $this->db->query();
		}
	}
}

# importing other cost functions
foreach (glob(dirname(__FILE__).DS."*.php") as $cf) {
	if (strcmp(basename($cf), "baseCostFunction.php") != 0) {
		require_once($cf);
	}
}

?>
