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
<?php numbullet("Group name filter") ?>
<?php
	$group_name_filter = JRequest::getString("group_name_filter","");
?>
<div style="width: 100%; text-align: center">
<form method="POST">
<?php echo JText::_("Group name filter") ?>:
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="layout" value="choosegroup"/>
<!--
<input type="hidden" name="limit" value="<?php echo JRequest::getInt('limit', 10) ?>"/>
<input type="hidden" name="limitstart" value="<?php echo JRequest::getInt('limitstart', 0) ?>"/>
-->
<input id="group_name_filter" name="group_name_filter" value="<?php echo $group_name_filter ?>">
<input class="button" type="submit" name="" value="<?php echo JText::_("Filter") ?>">
<input onClick="document.getElementById('group_name_filter').value = '%'" class="button" type="submit" name="" value="<?php echo JText::_("All") ?>">
</form>
</div>

<?php if ($group_name_filter) { ?>

<?php numbullet("Choose a group") ?>
<form name="group-form" id="group-form" method="POST">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="layout" value="choosegroup"/>
<input type="hidden" name="task" value="set_ghost_group"/>
<input type="hidden" id="ghost_group_id" name="ghost_group_id" value="0"/>
<input type="hidden" id="group_name_filter" name="group_name_filter" value="<?php echo $group_name_filter ?>"/>
</form>

<table class="hl">
	<thead>
		<th style="width: 50%"><?php echo JText::_("Group") ?></th>
		<th></th>
	</thead>
	<?php
		if ($group_name_filter) {
			$this->user_groups_model->addFilter("name LIKE '%$group_name_filter%'");
			if (!_status('operator')) {
				$this->user_groups_model->addFilter(
					"id IN (SELECT group_id FROM #__prenotown_user_group_entries WHERE user_id = " . $this->userdata['id'] . ")"
				);
			}
			$this->user_groups_model->addFilter('id <> 1');
			$groups = $this->user_groups_model->getData(1);

			foreach ($groups as $group) {
				echo "<tr>";
				echo "<td>" . $group['name'] . "</td>";
				echo "<td>";
				echo "<button class=\"button\" onClick=\"chooseGroup(" . $group['id'] . ")\">" . JText::_("Choose") . "</button>";
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
		} else {
			echo '<tr><td colspan="2" style="text-align: center; font-weight: bold">' . JText::_("Please provide a part of group name to choose one") . '</td></tr>';
		}
	?>
</table>

<?php } ?>

<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user&layout=chooseidentity')"><?php echo JText::_("User identity") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resources&layout=tree')"><?php echo JText::_("Book a resource") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>

