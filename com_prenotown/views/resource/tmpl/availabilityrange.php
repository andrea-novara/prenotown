<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<script language="Javascript" type="text/javascript">
	function check_form() {
		document.getElementById('availability-form').submit();
	}

	function availabilityStatus(obj) {
		if (obj.checked) {
			document.getElementById("availability-form").submit();
		} else {
			if (confirm("<?php echo JText::_("Disable availability range?") ?>")) {
				document.getElementById('availability-form').submit();	
			}
		}
	}
</script>
<style>
	.time_limit {
		width: 70px;
	}
</style>
<h2><?php echo $this->name . ": " . JText::_("Resource availability range") ?></h1>
<?php
	if (!$this->model->tables['resource']->availability_enabled) {
		echo JText::_("Availability range is currently disabled. If you wish to enable, check the box below and define a set of ranges.");
		echo "<br><br>";
	}

	numbullet('Enable/disable availability range');
?>
<form name="availability-form" id="availability-form" method="POST">
<input type="hidden" value="<?php echo $this->id ?>" name="id"/>
<input type="hidden" value="com_prenotown" name="option"/>
<input type="hidden" value="resource" name="view"/>
<input type="hidden" value="availabilityRange" name="layout"/>
<input type="hidden" value="updateAvailability" name="task"/>
&nbsp;&nbsp;<input type="checkbox" onChange="availabilityStatus(this);" name="availability_enabled" value="1" <?php
	if ($this->model->tables['resource']->availability_enabled) {
		echo " checked";
	}
?>>&nbsp;&nbsp;<?php JText::printf("Enabled") ?><br/><br/>
<?php if ($this->model->tables['resource']->availability_enabled) { ?>
<br/>
<?php numbullet('Describe availability range') ?>
<table class="hl">
	<thead>
	<?php
		foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') as $day) {
			echo '<th>' . JText::_($day) . '</th>';
		}
	?>
	</thead>
	<tr>
	<?php
		foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') as $day) {
			$key = $day . '_begin';
			echo '<td><input onChange="checkTime(\'' . $key . '\')" class="time_limit" type="text" id="' . $key . '" name="' . $key . '" value="';
			echo htmlspecialchars(preg_replace('/:\d\d$/', '', $this->model->tables['resource']->$key));
			echo '"></td>';
		}
	?>
	</tr>
	<tr>
	<?php
		foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') as $day) {
			$key = $day . '_end';
			echo '<td><input onChange="checkTime(\'' . $key . '\')" class="time_limit" type="text" id="' . $key . '" name="' . $key . '" value="';
			echo htmlspecialchars(preg_replace('/:\d\d$/', '', $this->model->tables['resource']->$key));
			echo '"></td>';
		}
	?>
	</tr>
</table>
<?php } ?>
</form>

<?php if ($this->model->tables['resource']->availability_enabled) { ?><br/>
<?php
	$sorted_bookings = array();

	// load all the bookings on the resource
	$bookings = $this->superbookings->getBookingsByResourceAndRange($this->model->_id, date('Y-m-d'), '3001-01-01', 'resource_id,begin_date,begin', false);

	$total = 0;

	$bids = array();

	// sort all the bookings by availability range crossing
	foreach ($bookings as $bk) {

		$overlaps = false;

		if (!$bk['periodic']) {
			$sql = "SELECT #__prenotown_day_bitmask('" . $bk['begin_date'] . "', '" . $bk['end_date'] . "')";
			$this->db->setQuery($sql);
			$bk['periodicity'] = $this->db->loadResult();
		}

		if ($bk['group_name'] == "All") {
			$bk['group_name'] = 'N/A';
		}

		$sql = "SELECT #__prenotown_booking_crosses_availability(" . $this->db->quote($this->model->_id) . ', ' .
			$bk['periodicity'] . ', "' . $bk['begin_time'] . '", "' . $bk['end_time'] . '")';
		_log_sql($sql);
		$this->db->setQuery($sql);
		$overlaps = $this->db->loadResult();

		if ($overlaps) {
			$resource_id = $bk['resource_id'];
			$sorted_bookings[$resource_id][] = $bk;
			$total++;
			$bids[] = $bk['id'];
		}
	}

	$title = JText::sprintf("Check overlapping bookings (%d detected)", $total);
	numbullet($title);

	foreach ($bids as $bid) {
		format_booking_by_id($bid);
		echo "<br/>";
	}

	if (!$total) {
		echo '<span style="font-weight: bold; text-align: center; display: block">' . JText::_("No booking exceeds availability ranges") . "</span><br/>";
	}
?>
<!--
<table class="hl">
	<thead>
		<th><?php echo JText::_("") ?></th>
		<th><?php echo JText::_("Booker") ?></th>
		<th><?php echo JText::_("Group") ?></th>
		<th><?php echo JText::_("Day range") ?></th>
		<th><?php echo JText::_("Overlapping range") ?></th>
	</thead>
<?php 
	$overlapping_found = false;
	foreach ($sorted_bookings as $resource) {
		echo '<tr class="with-booking day-line"><td colspan="5">' . $bk['resource_name'] . '</td></tr>';
		foreach ($resource as $bk) {
			echo '<tr>';
			echo '<td style="width: 15px !important; padding: 0px; padding-left: 10px !important; color: red; font-weight: bold; font-size: 25px; vertical-align: bottom">*</td>';
			echo '<td>' . $bk['user_name'] . '</td>';
			echo '<td>' . $bk['group_name'] . '</td>';
			echo '<td>' . $bk['begin_date'] . ' - ' . $bk['end_date'] . '</td>';
			echo '<td>' . $bk['begin_time'] . ' - ' . $bk['end_time'] . '</td>';
			echo '</tr>';
		}
		$overlapping_found = true;
	}

	if (!$overlapping_found) {
		echo '<tr><td colspan="5" style="text-align:center; font-weight: bold">';
		echo JText::_("No overlapping found");
		echo '</td></tr>';
	}
?>
</table>
-->
<?php } ?>
<div class="button-footer">
<button class="button" onClick="check_form()"><?php echo JText::_("Update") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=edit&id=<?php echo $this->id ?>')"><?php echo JText::_("Back to resource") ?></button>
</div>
