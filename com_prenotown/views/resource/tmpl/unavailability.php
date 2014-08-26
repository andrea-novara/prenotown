<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	JHTML::_('behavior.calendar'); //load the calendar behavior

	$document =& JFactory::getDocument();

	$begin_date = JRequest::getString('begin_date', date("d-m-Y"));
	$end_date = JRequest::getString('end_date', date("d-m-Y"));
	$range_id = JRequest::getInt('range_id', 0);
?>
<script language="Javascript" type="text/javascript">
	function check_form() {
		document.getElementById('availability-form').submit();
	}

	function toggle_crossing(id) {
		var d = document.getElementById(id);
		if (d) {
			state = d.style.display;
			if (state == 'none') {
				d.style.display = 'block';
			} else {
				d.style.display = 'none';
			}
		}
	}

	function drop_range(id) {
		document.getElementById('range_id').value = id;
		document.getElementById('drop_form').submit();
	}
</script>
<style>
	.entry {
		background-color: #eec;
		border-bottom:1px solid #ddd;
		border-right: 1px solid #ddd;
		padding: 4px;
		font-weight: bold;
		margin-bottom: 10px;
	}
</style>
<h2><?php echo $this->name . ": " . JText::_("Resource unavailability") ?></h1>
<?php numbullet("Create new range") ?>
<div style="text-align:center">
<form name="availability-form" id="availability-form" method="POST">
<input type="hidden" value="<?php echo $this->id ?>" name="id"/>
<input type="hidden" value="com_prenotown" name="option"/>
<input type="hidden" value="resource" name="view"/>
<input type="hidden" value="<?php echo $this->id ?>" name="resource_id"/>
<input type="hidden" value="add_unavailability_range" name="task"/>
<input type="hidden" value="unavailability" name="layout"/>
<?php
	echo JText::_('From:') . " ";

	$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
	inputField     :    "begin_date",
	ifFormat       :    "%d-%m-%Y",
	button         :    "begin_date_img",
	align          :    "Tl",
	singleClick    :    true,
	});});');   

	echo '<input type="text" name="begin_date" id="begin_date" value="';
	echo htmlspecialchars($begin_date, ENT_COMPAT, 'UTF-8') . '" onChange="return false; updateView(); return false;"/>';
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
	echo htmlspecialchars($end_date, ENT_COMPAT, 'UTF-8') . '" onChange="return false; updateView(); return false;"/>';
	echo ' <img class="calendar" src="' . JURI::root(true);
	echo '/templates/system/images/calendar.png" alt="calendar" id="end_date_img" />';
?>
&nbsp;&nbsp;&nbsp;&nbsp;<input class="button" type="submit" name="" value="<?php JText::printf("Create range") ?>"/>
</form>
</div>
<br/>
<?php numbullet('Manage ranges') ?>
<?php
	$this->db->setQuery("SELECT id, DATE(begin) AS begin, DATE(end) AS end FROM #__prenotown_superbooking WHERE resource_id = $this->id AND group_id = 2 ORDER BY begin");
	$ranges = $this->db->loadAssocList();

	if (count($ranges)) {
		echo "<ul>";
		foreach ($ranges as $range) {
			$delete_url = auto_url(array('task' => 'delete_unavailability_range', 'unavailability_id' => $range['id'], 'resource_id' => $this->id));
			printf("<div class=\"entry\"><div style=\"float: right\">[<a href=\"%s\">%s</a>]</div> %s %s %s %s</div>", $delete_url, JText::_("Delete"), JText::_("From:"), date_sql_to_human($range['begin']), JText::_("up to:"), date_sql_to_human($range['end']));
			$sql = "SELECT id FROM #__prenotown_superbooking WHERE resource_id = $this->id AND group_id <> 2 AND (DATE(begin) <= '" . $range['end'] . "' OR DATE(end) >= '" . $range['begin'] . "')";
			_log_sql($sql);
			$this->db->setQuery($sql);
			$bookings = $this->db->loadAssocList();
			echo JText::_("Crossing bookings");
			echo ' [<a onClick="toggle_crossing(\'crossing_' . $range['id'] . '\')">' . JText::_("Show/Hide") . '</a>]';
			echo '<div id="crossing_' . $range['id'] . '" style="display: none">';
			foreach ($bookings as $b) {
				format_booking_by_id($b['id'], array(), EXCLUDE_ACTIONS);
				echo "<br/>";
			}
			echo '</div><br/><br/>';
		}
		echo "</ul>";
	} else {
		echo '<div style="text-align:center;font-weight: bold">' . JText::_("No ranges found") . "</div><br/>";
	}
?>
<div class="button-footer">
<button class="button" onClick="check_form()"><?php echo JText::_("Update") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=edit&id=<?php echo $this->id ?>')"><?php echo JText::_("Back to resource") ?></button>
</div>
