<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<?php global $prenotown_user, $booking_user ?>
<script language="Javascript" type="text/Javascript">
	function retractBooking(id) {
		if (confirm("<?php echo JText::_("Do you really want to retract this booking?") ?>")) {
			document.getElementById('booking_id').value = id;
			document.getElementById('retract-booking').submit();
		}
	}
</script>
<h2><?php echo JText::_("Current booking for user") . " " . $booking_user['name'] ?></h1>
	<div id="elements-container">
<?php
	$superbookings =& JModel::getInstance('Superbookings', 'PrenotownModel');
	$superbookings->addFilter("#__prenotown_superbooking.end >= now()");
	$current_booking = $superbookings->getByUser(_user_id(), INCLUDE_LIMIT);
	$counter = 1;
	$total_cost = 0;

	$resources = array();
	$groups = array();

	foreach ($current_booking as $booking) {
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
			$actions[] = '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=resource&layout=paybookinglater&id=' . $booking['resource_id'] . '&resource_id=' . $booking['resource_id'] . '&group_id=' . $booking['group_id'] . '&booking_id=' . $booking['id'] . '\')">' . JText::_("Pay") . '</button>';
			if (_status('operator')) {
				$actions[] = '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=resource&layout=insertPayment&id=' . $booking['resource_id'] . '&booking_id=' . $booking['id'] . '\')">' . JText::_("Insert payment") . '</button>';
			}
			if (_status('admin')) {
				if (!$retract_button) {
					$actions[] = '<button class="button" onClick="retractBooking(' . $booking['id'] . ')">' . JText::_("Retract") . '</button>';
				}
			}
		/*
		} else {
			if ($booking['approved']  && !$booking['periodic']) {
				$actions[] = '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=resource&layout=ticket&format=raw&id=' . $booking['resource_id'] . "&booking_begin_date=$begin_date&booking_begin_hour=" . $begin_hour_split[0] . "&booking_begin_minute=" . $begin_hour_split[1] . "&booking_end_date=$end_date&booking_end_hour=" . $end_hour_split[0] . "&booking_end_minute=" . $end_hour_split[1] . "')\">" . JText::_("Cedolino di prenotazione") . "</button>";
			}
		*/
		}
		if ($booking['periodic']) {
			$actions[] = '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=user&layout=bookingExceptions&booking_id=' . $booking['id'] . '\'); return false">' . JText::_('Manage exceptions') . '</button>';
			if (_status("superadmin")) {
				$actions[] = '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=user&layout=bookingDetails&booking_id=' . $booking['id'] . '\'); return false">' . JText::_("Show details") . '</button>';
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
		echo "<div style=\"margin-top: 0em; padding: 2px; padding-right: 10px; width: 99.0%; background-color: black; color: white; text-align: right; font-weight: bold\">Totale: " . float_point_to_comma(sprintf("%.2f", $total_cost)) . "&nbsp;&euro;</div>";
		echo "<form>";
		pagination($superbookings, 0);
		echo "</form>";
	}
?>
</div>
<form method="POST" id="retract-booking" name="retract-booking">
	<input type="hidden" name="option" value="com_prenotown"/>
	<input type="hidden" name="view" value="user"/>
	<input type="hidden" name="layout" value="currentBooking"/>
	<input type="hidden" name="task" value="retract_booking"/>
	<input type="hidden" name="booking_id" id="booking_id" value="0"/>
</form>

<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
