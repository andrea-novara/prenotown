<?php defined('_JEXEC') or die("Restricted Access"); ?>
<script language="Javascript" type="text/javascript">
	function check_form() {
		document.getElementById("single-group-groups").submit();
	}
</script>
<h2><?php echo JText::_("Group management") ?></h1>
<h3><?php echo JText::_("Choose group") ?></h3>
<form name="edit-single-group" id="single-group-groups" method="POST">
<input type="hidden" name="task" value="edit_single_group"/>
<table class="hl">
	<thead>
		<th style="width: 20px"><img src="components/com_prenotown/assets/trash.png"></th>
		<th><?php echo JText::_("Name") ?></th>
	</thead>
<?php
	$groups = $this->model->getGroups();
	foreach ($groups as $group) {
		echo "<tr><td><input type=\"checkbox\" name=\"delete_group[]\" value=\"".
			$group['id'] . "\"></td><td>" . $group['name'] . "</td></tr>";
	}
?>

</table>
<br>
<h3><?php echo JText::_("Create new group") ?></h3>
<?php echo JText::_("New group name") ?>: <input type="text" name="new_group">
<?php include("url_params.php") ?>
</form>
<br>
<div class="button-footer">
<button class="button" onClick="check_form()"><?php echo JText::_("Update") ?></button>
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=groups')"><?php echo JText::_("Manage groups") ?></button>
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
