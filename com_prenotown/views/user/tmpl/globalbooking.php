<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
	global $prenotown_user, $booking_user;

	JHTML::_('behavior.calendar'); //load the calendar behavior

	$filter_booking_id = JRequest::getInt('filter_booking_id', 0);
	$filter_begin_date = JRequest::getString('filter_begin_date', date("d-m-Y"));
	$filter_begin_hour = JRequest::getString('filter_begin_hour', 0);
	$filter_end_date = JRequest::getString('filter_end_date', date("d-m-Y"));
	$filter_end_hour = JRequest::getString('filter_end_hour', 24);
	$filter_resource_id = JRequest::getInt('filter_resource_id', 0);
	$filter_group_id = JRequest::getInt('filter_group_id', 0);
	$filter_time_range_inclusive = JRequest::getInt('filter_time_range_inclusive', 0);

	$status = "filter_booking_id=$filter_booking_id&filter_begin_date=$filter_begin_date&filter_begin_hour=$filter_begin_hour&filter_end_date=$filter_end_date&filter_end_hour=$filter_end_hour&filter_resource_id=$filter_resource_id&filter_group_id=$filter_group_id&filter_time_range_inclusive=$filter_time_range_inclusive";

	$document =& JFactory::getDocument();

	$resource_id = JRequest::getInt('resource_id', 0);
	$include_null = JRequest::getInt('include_null', 0);

	$begin_date = JRequest::getString('begin_date', strftime('%d-%m-%Y'));
	$end_date = JRequest::getString('end_date', strftime('%d-%m-%Y'));

?>
<script language="Javascript" type="text/Javascript">
	function retractBooking(id) {
		if (confirm("<?php echo JText::_("Do you really want to retract this booking?") ?>")) {
			document.getElementById('booking_id').value = id;
			document.getElementById('retract-booking').submit();
		}
	}
</script>
<h2><?php echo JText::_("Global booking") ?></h1>
<form method="POST" name="booking-form" id="booking-form">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="layout" value="globalbooking"/>
<?php numbullet('Search criteria') ?>
<div class="formlabel"><?php echo JText::_("Booking id") ?>:</div>
<input name="filter_booking_id" value="<?php echo $filter_booking_id ?>"/> (<?php echo JText::_("if provided overcomes other criteria") ?>)
<br/>
<br/>
<div class="formlabel"><?php echo JText::_("Group") ?>:</div>
<select name="filter_group_id" onChange="updateView(); return false;">
<option value="0"><?php echo JText::_("Any") ?></option>
<?php
	foreach ($this->user_groups_model->getData() as $g) {
		if ($g['id'] != 1) {
			echo "<option value=\"" . $g['id'] . "\"";
			if ($g['id'] == $filter_group_id) {
				echo " selected";
			}
			echo ">" . $g['name'] . "</option>\n";
		}
	}
?>
</select>
<br/><br/><div class="formlabel"><?php echo JText::_('Resource') ?>:</div>
<select name="filter_resource_id" onChange="updateView(); return false;"><option value=""><?php echo JText::_("Any") ?></option>
<?php
	foreach ($this->resources->getData() as $resource) {
		echo "<option value=\"" . $resource['id'] . "\"";
		if ($resource['id'] == $filter_resource_id) {
			echo " selected";
		}
		echo ">" . $resource['name'] . "</option>\n";
	}
?>
</select>
<br/><br/><div class="formlabel"><?php echo JText::_("Date range") ?>:</div>
<?php
	list($d, $m, $y) = preg_split("/-/", $begin_date);
	$begin_date = sprintf("%02d-%02d-%04d", $d, $m, $y);

	list($d, $m, $y) = preg_split("/-/", $end_date);
	$end_date = sprintf("%02d-%02d-%04d", $d, $m, $y);

	echo " " . JText::_('From:') . " ";

	$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
	inputField     :    "filter_begin_date",
	ifFormat       :    "%d-%m-%Y",
	button         :    "begin_date_img",
	align          :    "Tl",
	singleClick    :    true,
	});});');   

	echo '<input type="text" name="filter_begin_date" id="filter_begin_date" value="';
	echo htmlspecialchars($filter_begin_date, ENT_COMPAT, 'UTF-8') . '" onChange="return false; updateView(); return false;"/>';
	echo ' <img class="calendar" src="' . JURI::root(true);
	echo '/templates/system/images/calendar.png" alt="calendar" id="begin_date_img" />';

	echo "&nbsp;&nbsp;&nbsp;&nbsp;" . JText::_('up to:') . " ";

	$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
	inputField     :    "filter_end_date",
	ifFormat       :    "%d-%m-%Y",
	button         :    "end_date_img",
	align          :    "Tl",
	singleClick    :    true,
	});});');   

	echo '<input type="text" name="filter_end_date" id="filter_end_date" value="';
	echo htmlspecialchars($filter_end_date, ENT_COMPAT, 'UTF-8') . '" onChange="return false; updateView(); return false;"/>';
	echo ' <img class="calendar" src="' . JURI::root(true);
	echo '/templates/system/images/calendar.png" alt="calendar" id="end_date_img" />';
?>
<br/>
<?php
	global $prenotown_user;
	if ($prenotown_user['username'] == 'nonovara@xsec.it') {
?>
<br/><div class="formlabel"><?php echo JText::_("Time range") ?>:</div>
Dalle: <select name="filter_begin_hour">
<?php for ($i = 0; $i <= 24; $i++) { echo "<option value=\"$i\""; if ($i == $filter_begin_hour) { echo " selected"; } echo ">$i</option>\n"; } ?>
</select>
 alle: <select name="filter_end_hour">
<?php for ($i = 0; $i <= 24; $i++) { echo "<option value=\"$i\""; if ($i == $filter_end_hour) { echo " selected"; } echo ">$i</option>\n"; } ?>
</select>
<input type="checkbox" name="filter_time_range_inclusive" value="1" <?php if ($filter_time_range_inclusive) { echo "checked"; } ?>><?php echo JText::_("Only if fully included") ?>
<?php } ?>
<br/><br/>
<button style="float: right; margin-right: 10px" class="button" onClick="updateView(); return false;"><?php JText::printf("Update view") ?></button>
<br/>
<input type="hidden" value="0" name="include_null"/>
</form><br/>
<?php numbullet("Bookings") ?><br/>
	<div id="elements-container">
<?php
	$superbookings =& JModel::getInstance('Superbookings', 'PrenotownModel');

	if ($filter_booking_id) $superbookings->addFilter("#__prenotown_superbooking.id = " . esc_query($filter_booking_id));
	else {
		if ($filter_resource_id) $superbookings->addFilter("#__prenotown_superbooking.resource_id = " . esc_query($filter_resource_id));
		if ($filter_group_id) $superbookings->addFilter("#__prenotown_superbooking.group_id = " . esc_query($filter_group_id));
		$superbookings->addFilter("DATE(#__prenotown_superbooking.begin) <= '" . esc_query(date_human_to_sql($filter_end_date)) . "'");
		$superbookings->addFilter("DATE(#__prenotown_superbooking.end) >= '" . esc_query(date_human_to_sql($filter_begin_date)) ."'");

		if ($filter_time_range_inclusive) {
			$superbookings->addFilter("HOUR(#__prenotown_superbooking.begin) >= " . esc_query($filter_begin_hour));
			$superbookings->addFilter("HOUR(#__prenotown_superbooking.begin) < " . esc_query($filter_end_hour));
	
			$superbookings->addFilter("HOUR(#__prenotown_superbooking.end) > " . esc_query($filter_begin_hour));
			$superbookings->addFilter("HOUR(#__prenotown_superbooking.end) * 60 + MINUTE(#__prenotown_superbooking.end) < " . esc_query($filter_end_hour) * 60);
		} else {
			$superbookings->addFilter("HOUR(#__prenotown_superbooking.begin) < " . esc_query($filter_end_hour));
			$superbookings->addFilter("HOUR(#__prenotown_superbooking.end) > " . esc_query($filter_begin_hour));
		}
		$superbookings->addFilter("(NOT periodic OR periodicity & #__prenotown_day_bitmask('" . date_human_to_sql($filter_begin_date) . "', '" . date_human_to_sql($filter_end_date) . "'))");
	}

	$current_booking = $superbookings->getData(INCLUDE_LIMIT);

	$counter = 1;
	$total_cost = 0;

	$resources = array();
	$groups = array();

	foreach ($current_booking as $booking) {
		# format_booking_by_id($booking['id']);
		# continue;

		$paid = false;

		$begin = explode(" ", $booking['begin']);
		$end = explode(" ", $booking['end']);

		$begin_date = date_sql_to_human($begin[0]);
		$end_date = date_sql_to_human($end[0]);

		$begin_hour = $begin[1];
		$end_hour = $end[1];

		$begin_hour_split = explode(":", $begin_hour);
		$end_hour_split = explode(":", $end_hour);

		# echo "<pre>"; print_r($booking); echo "</pre>";

		////////////////////////////////////////////////////
		global $booking_user;

		if (!$booking['periodic']) {
			$this->db->setQuery("CALL #__prenotown_expand_booking(" . $booking['id'] . ", @cost, 1)");
			$this->db->query();
			$this->db->setQuery("SELECT @cost");
			$cost = $this->db->loadResult();
		}

		if (!isset($resources[$booking['resource_id']])) {
			$this->db->setQuery("SELECT name, address FROM #__prenotown_resource WHERE id = " . $booking['resource_id']);
			$resources[$booking['resource_id']] = $this->db->loadAssoc();
		}

		if (isset($booking['group_id']) && !isset($groups[$booking['group_id']])) {
			$this->db->setQuery("SELECT name FROM #__prenotown_user_groups WHERE id = " . $booking['group_id']);
			$groups[$booking['group_id']] = $this->db->loadResult();
		}

		$exceptions = array();
		if (isset($booking['exceptions']) and is_array($booking['exceptions'])) {
			foreach ($booking['exceptions'] as $exception) {
				if (strcmp($exception['exception_date'], "0000-00-00") != 0) {
					$exceptions[] = date_sql_to_human($exception['exception_date']);
					$excount++;
				}
			}
		}
		$this->user_model->setId($booking['user_id']);
		list($begin_date, $begin_time) = preg_split('/ /', $booking['begin']);
		list($end_date, $end_time) = preg_split('/ /', $booking['end']);

		if ($booking['periodic']) {
			$paid = true;
		} else if ($booking['date'] && ($booking['date'] != '0000-00-00 00:00:00')) {
			$paid = true;
		}

		$retract_button = 0;
		$actions = array();
		if (!$paid) {
			$can_retract = 0;
			if ((_has_ghost_group() /* && check_global_time */) || 1 /* check single time */) {
				$actions[] = '<button class="button" onClick="retractBooking(' . $booking['id'] . ')">' . JText::_("Retract") . '</button>';
				$retract_button++;
			}
			if (_status('operator')) {
				$actions[] = '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=resource&layout=insertPayment&id=' . $booking['resource_id'] . '&booking_id=' . $booking['id'] . '\')">' . JText::_("Insert payment") . '</button>';
			}
			if (_status('admin')) {
				if (!$retract_button) {
					$actions[] = '<button class="button" onClick="retractBooking(' . $booking['id'] . ')">' . JText::_("Retract") . '</button>';
				}
			}
		} else {
			if ($booking['approved']  && !$booking['periodic']) {
				# $actions[] = '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=resource&layout=ticket&format=raw&id=' . $booking['resource_id'] . "&booking_begin_date=$begin_date&booking_begin_hour=" . $begin_hour_split[0] . "&booking_begin_minute=" . $begin_hour_split[1] . "&booking_end_date=$end_date&booking_end_hour=" . $end_hour_split[0] . "&booking_end_minute=" . $end_hour_split[1] . "')\">" . JText::_("Cedolino di prenotazione") . "</button>";
			}
		}
		if ($booking['periodic']) {
			$actions[] = '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=user&layout=bookingExceptions&booking_id=' . $booking['id'] . "&$status'); return false\">" . JText::_('Manage exceptions') . '</button>';
			if (_status("superadmin")) {
				$actions[] = '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=user&layout=bookingDetails&booking_id=' . $booking['id'] . "&$status'); return false\">" . JText::_("Show details") . '</button>';
			}
			if (_status('admin') && !$retract_button) {
				$actions[] = '<button class="button" onClick="retractBooking(' . $booking['id'] . ')">' . JText::_("Retract") . '</button>';
			}
		}

		$total_cost += format_booking_by_id($booking['id'], $actions);
		echo "<br>";

		$counter++;
	}

	if (!count($current_booking)) {
		echo '<div style="text-align: center; font-weight: bold">' . JText::_("No booking") . '</div>';
	} else {
		// echo "<div style=\"margin-top: 0em; padding: 2px; padding-right: 10px; width: 99.0%; background-color: black; color: white; text-align: right; font-weight: bold\">Totale: " . float_point_to_comma(sprintf("%.2f", $total_cost)) . "&nbsp;&euro;</div>";
		echo "<form>";
		pagination($superbookings, 0, array(
			'filter_begin_date' => $filter_begin_date, 'filter_end_date' => $filter_end_date,
			'filter_group_id' => $filter_group_id, 'filter_resource_id' => $filter_resource_id,
			'filter_begin_hour' => $filter_begin_hour, 'filter_end_hour' => $filter_end_hour,
			'filter_time_range_inclusive' => $filter_time_range_inclusive,
		));
		echo "</form>";
	}
?>
</div>
<form method="POST" id="retract-booking" name="retract-booking">
	<input type="hidden" name="option" value="com_prenotown"/>
	<input type="hidden" name="view" value="user"/>
	<input type="hidden" name="layout" value="globalBooking"/>
	<input type="hidden" name="task" value="retract_booking"/>
	<input type="hidden" name="booking_id" id="booking_id" value="0"/>
	<input type="hidden" name="filter_group_id" id="filter_group_id" value="<?php echo $filter_group_id ?>"/>
	<input type="hidden" name="filter_resource_id" id="filter_resource_id" value="<?php echo $filter_resource_id ?>"/>
	<input type="hidden" name="filter_begin_date" id="filter_begin_date" value="<?php echo $filter_begin_date ?>"/>
	<input type="hidden" name="filter_end_date" id="filter_end_date" value="<?php echo $filter_end_date ?>"/>
	<input type="hidden" name="filter_begin_hour" id="filter_begin_hour" value="<?php echo $filter_begin_hour ?>"/>
	<input type="hidden" name="filter_end_hour" id="filter_end_hour" value="<?php echo $filter_end_hour ?>"/>
	<input type="hidden" name="filter_time_range_inclusive" id="filter_time_range_inclusive" value="<?php echo $filter_time_range_inclusive ?>"/>
	<input type="hidden" name="filter_booking_id" id="filter_booking_id" value="<?php echo $filter_booking_id ?>"/>
</form>

<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
