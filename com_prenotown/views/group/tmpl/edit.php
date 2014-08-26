<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<?php $name_filter = JRequest::getString('name_filter',''); ?>
<script language="Javascript" type="text/javascript">
	function check_form() {
		document.getElementById("editGroup").submit();
	}

	function add_user(id) {
		if (!id) {
			return;
		}

		redirect('index.php?option=com_prenotown&view=group&layout=edit&task=add_user_to_group&user_id=' + id + '&group_id=<?php echo $this->id ?>&id=<?php echo $this->id ?>');
	}

	function delete_user(id) {
		if (!id) {
			return;
		}

		if (confirm("<?php echo JText::_("Do you really want to remove the user from this group?") ?>")) {
			redirect('index.php?option=com_prenotown&view=group&layout=edit&task=delete_user_from_group&user_id=' + id + '&group_id=<?php echo $this->id ?>&id=<?php echo $this->id ?>');
		}
	}
</script>
<h2><?php echo JText::_("Group management") . ": " . $this->group_model->tables['usergroups']->name ?></h1>
<?php numbullet("Change group name") ?>
<form id="group-form" name="group-form" method="POST">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="group"/>
<input type="hidden" name="layout" value="edit"/>
<input type="hidden" name="task" value="change_group_name"/>
<input type="hidden" name="id" value="<?php echo $this->id ?>"/>
<?php echo JTExt::_("New name") ?>:
<input type="text" name="group_name" style="width: 500px" value="<?php echo $this->group_model->tables['usergroups']->name ?>"/>
<input type="submit" class="button" value="<?php echo JText::_("Change") ?>"/>
</form>
<?php numbullet("Manage membership") ?>
<input type="hidden" name="task" value="edit_single_group"/>
<table class="hl">
	<thead>
		<th><?php echo JText::_("Name") ?></th>
		<th><?php echo JText::_("Social security number") ?></th>
		<th><?php echo JText::_("Address") ?></th>
		<th><?php echo JText::_("Operations") ?></th>
	</thead>
<?php
	$users = $this->group_model->getUsers();
	foreach ($users as $user) {
		echo "<tr><td>" . $user['name'] . "</td><td>" . $user['social_security_number'] . "</td><td>" .
			$user['address'] . "</td><td>" . '<button class="button" onClick="delete_user(' . $user['id'] . ')">' .
			JText::_("Delete") . "</button></td></tr>";
	}

	if (!count($users)) {
		echo '<tr><td style="text-align: center; font-weight: bold" colspan="4">';
		echo JText::_("No users in this group");
		echo '</td></tr>';
	}
?>

</table>
<br>
<hr>
<?php numbullet("Add a user to this group") ?>
<div style="width: 100%; text-align: center">
<form id="search-form" name="search-form" method="POST" style="display: inline">
<?php echo JText::_("Filter by user first and last name") ?>:
<input name="name_filter" id="name_filter" value="<?php echo $name_filter ?>"/>
<input class="button" type="submit" value="<?php echo JText::_("Filter") ?>"/>
</form>
<button class="button" onClick="document.getElementById('name_filter').value='%';document.getElementById('search-form').submit();"><?php echo JText::_("All") ?></button>
<button class="button" onClick="document.getElementById('name_filter').value='';document.getElementById('search-form').submit();"><?php echo JText::_("Reset") ?></button>
</div>
<?php
	if ($name_filter) {
?>
<table class="hl" cellspacing=0 cellpadding=0>
	<thead>
		<th><?php echo JText::_("Name") ?></th>
		<th><?php echo JText::_("Social security number") ?></th>
		<th><?php echo JText::_("Address") ?></th>
		<th><?php echo JText::_("Operations") ?></th>
	</thead>
<?php
		$this->users_model->addFilter("name LIKE '%$name_filter%'");
		$users = $this->users_model->getData();
		foreach ($users as $user) {
			echo '<tr><td>' . $user['name'] . '</td><td>' . $user['social_security_number'] . '</td><td>' . $user['address'] . '</td><td>';
			echo '<button class="button" onClick="add_user(' . $user['id'] . ')">' . JText::_("Add user") . '</button>';
			echo '</td></tr>';
		}

		if (!count($users)) {
			echo '<tr><td style="text-align: center; font-weight: bold" colspan="4">';
			echo JText::_("No users found");
			echo '</td></tr>';
		}
?>
</table>
<?php } ?>
<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=groups')"><?php echo JText::_("Manage groups") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
