<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	JHTML::_('behavior.calendar'); //load the calendar behavior

	$group_id = JRequest::getInt('group_id', 0);
	$this->user_group_model->setId($group_id);
?>
<?php global $prenotown_user, $booking_user ?>
<style>
	td { vertical-align: top }
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

		checkDate('begin_date', '0000-00-00', '3000-01-01');
		checkDate('end_date', '0000-00-00', '3000-01-01');

		if (end_date.replace(/(\d\d?)-(\d\d?)-(\d\d\d\d)/, '$3-$2-$1') < begin_date.replace(/(\d\d?)-(\d\d?)-(\d\d\d\d)/, '$3-$2-$1')) {
			alert("Errore: le date sono in ordine sbagliato");
			return false;
			// $('end_date').value = $('begin_date').value;
		}

		document.getElementById('booking-form').submit();
		return false;
	}
</script>
<h2><?php JText::printf("Current booking for group %s", $this->user_group_model->tables['userGroups']->name); ?></h1>
<?php $title = JText::sprintf('Current booking on group %d', JRequest::getInt('group_id', 1)) ?>
<form method="POST" name="booking-form" id="booking-form">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="layout" value="bookinghistory"/>
<?php numbullet('Search criteria') ?>
<div class="formlabel"><?php echo JText::_("Group") ?>:</div>
<select name="group_id" onChange="updateView(); return false;">
<option value="0"><?php echo JText::_("Please choose a group") ?></option>
<?php
	if (!_status("operator")) {
		$this->user_groups_model->addFilter(
			"id IN (SELECT group_id FROM #__prenotown_user_group_entries WHERE user_id = " . $this->userdata['id'] . ")"
		);
	}

	foreach ($this->user_groups_model->getData() as $g) {
		if ($g['id'] == 1) {
			continue;
		}
		echo "<option value=\"" . $g['id'] . "\"";
		if ($g['id'] == JRequest::getInt('group_id')) {
			echo " selected";
		}
		echo ">" . $g['name'] . "</option>\n";
	}
?>
</select>
<br/><br/><div class="formlabel"><?php echo JText::_('Resource') ?>:</div>
<select name="resource_id" onChange="updateView(); return false;"><option value=""><?php echo JText::_("Any") ?></option>
<?php
	foreach ($this->resources->getData() as $resource) {
		echo "<option value=\"" . $resource['id'] . "\"";
		if ($resource['id'] == JRequest::getInt('resource_id')) {
			echo " selected";
		}
		echo ">" . $resource['name'] . "</option>\n";
	}
?>
</select>
<br/><br/><div class="formlabel"><?php echo JText::_("Date range") ?>:</div>
<?php
	$document =& JFactory::getDocument();

	$resource_id = JRequest::getInt('resource_id', 0);
	$include_null = JRequest::getInt('include_null', 0);

	$begin_date = JRequest::getString('begin_date', strftime('%d-%m-%Y'));
	$end_date = JRequest::getString('end_date', strftime('%d-%m-%Y'));

	list($d, $m, $y) = preg_split("/-/", $begin_date);
	$begin_date = sprintf("%02d-%02d-%04d", $d, $m, $y);

	list($d, $m, $y) = preg_split("/-/", $end_date);
	$end_date = sprintf("%02d-%02d-%04d", $d, $m, $y);

	echo " " . JText::_('From:') . " ";

	$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
	inputField     :    "begin_date",
	ifFormat       :    "%d-%m-%Y",
	button         :    "begin_date_img",
	align          :    "Tl",
	singleClick    :    true,
	});});');   

	echo '<input type="text" name="begin_date" id="begin_date" value="';
	echo htmlspecialchars($begin_date, ENT_COMPAT, 'UTF-8') . '" onChange=""/>';
	echo ' <img class="calendar" src="' . JURI::root(true);
	echo '/templates/system/images/calendar.png" alt="calendar" id="begin_date_img" />';

	echo "&nbsp;&nbsp;&nbsp;&nbsp;" . JText::_('up to:') . " ";

	$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
	inputField     :    "end_date",
	ifFormat       :    "%d-%m-%Y",
	button         :    "end_date_img",
	align          :    "Tl",
	singleClick    :    true,
	});});');   

	echo '<input type="text" name="end_date" id="end_date" value="';
	echo htmlspecialchars($end_date, ENT_COMPAT, 'UTF-8') . '" onChange=""/>';
	echo ' <img class="calendar" src="' . JURI::root(true);
	echo '/templates/system/images/calendar.png" alt="calendar" id="end_date_img" />';
?>
<br/>
</form>
<button style="float: right; margin-right: 10px" class="button" onClick="updateView(); return false;"><?php JText::printf("Update view") ?></button>
<br/>
<!--
&nbsp;<input type="checkbox" name="include_null" value="1" <?php echo $include_null ? "checked" : "" ?>/>&nbsp;<?php echo JText::_("Include empty days") ?>
-->
<input type="hidden" value="0" name="include_null"/>

<br/>
<?php numbullet('Current booking') ?>
<?php if ( $group_id ) { ?>
<table class="hl" cellspacing=0 cellpadding=0>
	<thead>
		<th>&nbsp;</th>
		<th><?php echo JText::_("Time range") ?></th>
		<th><?php echo JText::_("Booker") ?></th>
		<th style="text-align: right"><?php echo JText::_("Time amount") ?></th>
		<th style="text-align: right"><?php echo JText::_("Cost") ?></th>
	</thead>
	<?php
		list($d, $m, $y) = preg_split("/-/", $begin_date);
		$begin_date = sprintf("%04d-%02d-%02d", $y, $m, $d);

		list($d, $m, $y) = preg_split("/-/", $end_date);
		$end_date = sprintf("%04d-%02d-%02d", $y, $m, $d);

		$group_id = JRequest::getInt('group_id', 0);
		$resource_id = JRequest::getInt('resource_id');

		$this->db->setQuery("DROP TABLE IF EXISTS #__prenotown_booking_expansion");
		$this->db->query();

		$sql = "SELECT id FROM #__prenotown_superbooking WHERE (DATE(begin) <= DATE('$end_date') OR DATE(end) >= DATE('$begin_date')) AND group_id = $group_id";
		if ($resource_id) { $sql .= " AND resource_id = $resource_id"; }
		_log_sql($sql);
		$this->db->setQuery($sql);
		foreach ($this->db->loadResultArray() as $bid) {
			# echo "<!-- bid: $bid -->\n";
			$sql = "CALL #__prenotown_expand_booking($bid, @cost, 0)";
			_log_sql($sql);
			$this->db->setQuery($sql);
			$this->db->query();
		}

		$sql = "DELETE FROM #__prenotown_booking_expansion WHERE begin_date < DATE('$begin_date') OR end_date > DATE('$end_date')";
		_log_sql($sql);
		$this->db->setQuery($sql);
		$this->db->query();

		$this->db->setQuery("SELECT * FROM #__prenotown_booking_expansion ORDER BY resource_id, begin_date");
		$bookings = $this->db->loadAssocList();
		if (!isset($bookings)) { $bookings = array(); }
		$current_resource_id = 0;

		$total_minutes = 0;
		$total_cost = 0;

		function print_total($minutes, $cost) {
			echo "<tr><td class=\"total\" colspan=\"2\">";
			echo "<td class=\"total\"><b>" . JText::_("Total:") . "</td>";
			echo "<td class=\"total\"><b>$minutes min.</b></td>";
			echo "<td class=\"total\"><b>" . float_point_to_comma(sprintf("%.2f", $cost)) . " &euro;</td></tr>";
		}

		foreach ($bookings as $bk) {
			if ($current_resource_id != $bk['resource_id']) {
				if ($current_resource_id) print_total($total_minutes, $total_cost);
				echo '<tr class="with-booking day-line"><td colspan="2"><b>' . $bk['resource_name'] . '</b></td><td colspan="4"></td></tr>';
				$total_minutes = 0;
				$total_cost = 0;
				$current_resource_id = $bk['resource_id'];
			}

			if ($bk['excepted']) {
				$bk['cost'] = 0;
				$bk['length'] = 0;
			} else {
				$total_minutes += $bk['length'] / 60;
				$total_cost += $bk['cost'];
			}
			if ($bk['excepted']) {
				echo '<tr title="' . JText::_("This is an exception and will not be considered") . '"><td>';
				echo '<span class="redstar">*</span>';
			} else {
				echo '<tr><td>';
			}

			$bk['begin_time'] = preg_replace("/:..$/", "", $bk['begin_time']);
			$bk['end_time'] = preg_replace("/:..$/", "", $bk['end_time']);

			$trange = "";
			if (strcmp($bk['begin_date'], $bk['end_date']) == 0) {
				$trange = JText::sprintf("From %s up to %s on %s", $bk['begin_time'], $bk['end_time'], date_sql_to_human($bk['begin_date']));
			} else {
				$trange = JText::sprintf("From %s on %s up to %s on %s", $bk['begin_time'], date_sql_to_human($bk['begin_date']), $bk['end_time'], date_sql_to_human($bk['end_date']));
			}

			echo "</td><td>$trange</td>";
			echo "<td>" . $bk['user_name'] . "</td>";
			echo '<td style="text-align: right">' . $bk['length'] / 60 . " min.</td>";
			echo '<td style="text-align: right">' . float_point_to_comma(sprintf("%.2f", $bk['cost'])) . " &euro;</td>";
			echo "</tr>";
		}

		if ($current_resource_id) print_total($total_minutes, $total_cost);
	?>
</table>
<?php } ?>

<br/>
<br/>
<div class="button-footer">
<!--
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user&layout=choosegroup')"><?php echo JText::_("Group identity") ?></button>&nbsp;|&nbsp;
-->
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
