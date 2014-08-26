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
<?php
	$begin_date = JRequest::getString('begin_date', strftime('%d-%m-%Y'));
	$end_date = JRequest::getString('end_date', strftime('%d-%m-%Y'));
?>
<style>
	td { vertical-align: top }
	#begin_date, #end_date { width: 100px }
	.no-booking { color: #999 }
	.with-booking { background-color: #eee }
	.day-line { }
	.day-line td { border-top: 1px solid #ccc !important; }
	.day-line td, .day-line td:hover { background-color: #ddd !important }
</style>
<script language="Javascript" type="text/Javascript">
	function updateView() {
		// get begin and end dates
		var begin_date = document.getElementById('begin_date').value;
		var end_date = document.getElementById('end_date').value;

		// trim begin and end dates
		begin_date = begin_date.replace(/^\s\+/, '');
		begin_date = begin_date.replace(/\s\+$/, '');
		end_date = end_date.replace(/^\s\+/, '');
		end_date = end_date.replace(/\s\+$/, '');

		// check data correctness
		if (!begin_date.match(/^\d\d?-\d\d?-\d\d\d\d$/)) {
			alert("<?php JText::printf("Null or incorrect begin date") ?>");
			return false;
		}

		if (!end_date.match(/^\d\d?-\d\d?-\d\d\d\d$/)) {
			alert("<?php JText::printf("Null or wrong end date") ?>");
			return false;
		}

		document.getElementById('booking-form').submit();
		return false;
	}

	function delete_booking(id) {
		if (confirm("<?php JText::printf("If this booking has already been in effect, deleting it will make impossible to bill the booker. Do you really want to delete this booking?") ?>")) {
			redirect("index.php?option=com_prenotown&task=retract_booking&view=resource&layout=currentBooking&id=<?php echo $this->id ?>&booking_id=" + id + "&begin_date=<?php echo $begin_date ?>&end_date=<?php echo $end_date ?>");
		}
	}

	function add_exception(id, exception_date) {
		if (confirm("<?php echo JText::_("Add an exception on day") ?> " + exception_date + "?")) {
			redirect("index.php?option=com_prenotown&task=add_exception&view=resource&layout=currentBooking&id=<?php echo $this->id ?>&booking_id=" + id + "&exception_date=" + exception_date + "&begin_date=<?php echo $begin_date ?>&end_date=<?php echo $end_date ?>");
		}
	}

	function delete_exception(id, exception_date) {
		if (confirm("<?php echo JText::_("Delete exception on day") ?> " + exception_date + "?")) {
			redirect("index.php?option=com_prenotown&task=delete_exception&view=resource&layout=currentBooking&id=<?php echo $this->id ?>&exception_id=" + id + "&begin_date=<?php echo $begin_date ?>&end_date=<?php echo $end_date ?>");
		}
	}
</script>
<h2><?php echo $this->name . ": " . JText::_("Current booking for resource") ?></h1>
<form method="POST" name="booking-form" id="booking-form">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="resource"/>
<input type="hidden" name="layout" value="currentbooking"/>
<input type="hidden" name="option" value="com_prenotown"/>
<?php numbullet('Specify time range') ?>
<div style="display: block; text-align: center"><?php
	$resource_id = $this->id;
	$include_null = JRequest::getInt('include_null', 0);

	list($d, $m, $y) = preg_split("/-/", $begin_date);
	$begin_date = sprintf("%02d-%02d-%04d", $d, $m, $y);

	list($d, $m, $y) = preg_split("/-/", $end_date);
	$end_date = sprintf("%02d-%02d-%04d", $d, $m, $y);

	echo " " . JText::_('From:') . " ";
	echo JHTML::_('calendar', $begin_date, 'begin_date', 'begin_date', '%d-%m-%Y');
	echo "&nbsp;&nbsp;&nbsp;&nbsp;" . JText::_('up to:') . " ";
	echo JHTML::_('calendar', $end_date, 'end_date', 'end_date', '%d-%m-%Y');
?>&nbsp;<input type="checkbox" name="include_null" value="1" <?php echo $include_null ? "checked" : "" ?>/>&nbsp;<?php echo JText::_("Include empty days") ?>
<button style="margin-left: 20px" class="button" onClick="updateView(); return false;"><?php JText::printf("Update view") ?></button>
</div>
</form>

<br/>
<?php numbullet('Current booking') ?>
<?php echo JText::_("Note: only approved bookings will appear in this list") ?><br/><br/>
<table class="hl" cellspacing=0 cellpadding=0>
	<thead>
		<th style="width: 20px"><?php echo JText::_("ID") ?></th>
		<th><?php echo JText::_("Range") ?></th>
		<th><?php echo JText::_("Booker") ?></th>
		<th><?php echo JText::_("As group") ?></th>
		<th><?php echo JText::_("Actions") ?></th>
	</thead>
	<?php
		list($d, $m, $y) = preg_split("/-/", $begin_date);
		$begin_date = sprintf("%04d-%02d-%02d", $y, $m, $d);

		list($d, $m, $y) = preg_split("/-/", $end_date);
		$end_date = sprintf("%04d-%02d-%02d", $y, $m, $d);

		$date = $begin_date;
		while (strcmp($date, $end_date) <= 0) {
			$current_booking = $this->superbookings->getBookingsByResourceAndRange($resource_id, $date);

			$is_null = count($current_booking) ? 0 : 1;

			$tr_class = $is_null ? "no-booking" : "with-booking";

			if (!$is_null || $include_null) {
				echo '<tr class="' . $tr_class . ' day-line"><td colspan="5"><b>' . preg_replace('/(\d\d\d\d)-(\d\d?)-(\d\d?)/', '$3-$2-$1', $date) . '</b></td></tr>';
			}

			$total_for_today = count($current_booking);
			for ($i = 0; $i < $total_for_today; $i++) {
				$booking = $current_booking[$i];
				$paid = false;
				$booking['begin_date'] = date_sql_to_human($booking['begin_date']);
				$booking['end_date'] = date_sql_to_human($booking['end_date']);
				echo '<tr class="' . $tr_class . '"><td>' . $booking['id'] . '</td>';
				echo '<td style="white-space: nowrap">' . $booking['begin_time'];
				// if (strcmp($booking['begin_date'], date_sql_to_human($date)) != 0) { echo " (del " . $booking['begin_date'] . ")"; }
				echo "<br>";
				echo $booking['end_time'];
				// if (strcmp($booking['end_date'], date_sql_to_human($date)) != 0) { echo " (del " . $booking['end_date'] . ")"; }
				echo '</td>';
				echo '<td>' . $booking['user_name'] . " - " . $booking['social_security_number'] . '<br>' . $booking['user_address'] . '</td>';
				echo '<td>';
				echo $booking['group_name'] ? $booking['group_name'] : JText::sprintf("No group");
				echo '</td>';
				echo '<td>';
				echo '<button class="button" onClick="delete_booking(\'' . $booking['id'] . '\');">' . JText::_("Delete") . '</button>';
				if ($booking['periodic']) {
					echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
					$sql = "SELECT id FROM #__prenotown_superbooking_exception WHERE booking_id = " . $booking['id'] . " AND exception_date = '" . date_human_to_sql($date) . "'";
					$this->db->setQuery($sql);
					$exception_id = $this->db->loadResult();
					if ($exception_id) {
						echo '<button class="button" onClick="delete_exception(\'' . $exception_id . '\', \'' . date_sql_to_human($date) . '\');">' . JText::_("Delete exception") . '</button>';
					} else {
						echo '<button class="button" onClick="add_exception(\'' . $booking['id'] . '\', \'' . date_sql_to_human($date) . '\');">' . JText::_("Add exception") . '</button>';
					}
				}
				echo '</td>';
				echo '</tr>';
				if ($i < $total_for_today - 1) {
					echo '<tr class="' . $tr_class . '"><td colspan="5"><hr style="border: 1px solid #ccc"></td></tr>';
				}
			}

			if ($is_null && $include_null) {
				echo '<tr class="' . $tr_class . '"><td style="text-align: center; font-weight: bold" colspan="4">' . JText::_("No booking") . '</td></tr>';
			}

			$this->db->setQuery("SELECT ADDDATE('$date', 1)");
			$date = $this->db->loadResult();
		}
	?>
</table>

<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resources&layout=myresources')"><?php echo JText::_("Other resources") ?></button>&nbsp;|&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
