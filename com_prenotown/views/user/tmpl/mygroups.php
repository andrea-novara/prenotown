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
	function chooseGroup(id) {
		if (!id) {
			alert("<?php echo JText::_("No group provided!") ?>");
			return;
		}

		document.getElementById("ghost_group_id").value = id;
		document.getElementById("view").value = 'resources';
		document.getElementById("layout").value = 'tree';
		document.getElementById("group-form").submit();
	}

	function bookingHistory(id) {
		if (!id) {
			alert("<?php echo JText::_("No group provided!") ?>");
			return;
		}

		redirect('index.php?option=com_prenotown&view=user&layout=bookinghistory&group_id=' + id);
		return;
		document.getElementById("ghost_group_id").value = id;
		document.getElementById("group-form").submit();
	}
</script>
<h2><?php echo JText::_("Choose a group to act as:") ?></h1>
<form name="group-form" id="group-form" method="POST">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" id="view" name="view" value="user"/>
<input type="hidden" id="layout" name="layout" value="choosegroup"/>
<input type="hidden" id="task" name="task" value="set_ghost_group"/>
<input type="hidden" id="ghost_group_id" name="ghost_group_id" value="0"/>
</form>

<table class="hl">
	<thead>
		<th style="width: 50%"><?php echo JText::_("Group") ?></th>
		<th></th>
	</thead>
	<?php
		$this->user_groups_model->addFilter(
			"id IN (SELECT group_id FROM #__prenotown_user_group_entries WHERE user_id = " . $this->userdata['id'] . ")"
		);
		$this->user_groups_model->addFilter('id <> 1');
		$groups = $this->user_groups_model->getData(1);

		foreach ($groups as $group) {
			echo "<tr>";
			echo "<td>" . $group['name'] . "</td>";
			echo "<td>";
			echo "<button class=\"button\" onClick=\"chooseGroup(" . $group['id'] . ")\">" . JText::_("Book for this group") . "</button>";
			echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
			echo "<button class=\"button\" onClick=\"bookingHistory(" . $group['id'] . ")\">" . JText::_("Booking history") . "</button>";
			echo "</td>";
			echo "</tr>";
		}

		if (!count($groups)) {
			echo '<tr><td colspan="2" style="text-align: center; font-weight: bold">' . JText::_("No group available") . '</td></tr>';
		} else {
			pagination($this->user_groups_model, 6, array("group_name_filter" => $group_name_filter));
		}
	?>
</table>

<div class="button-footer">
<!--
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resources&layout=tree')"><?php echo JText::_("Book a resource") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
-->
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>

