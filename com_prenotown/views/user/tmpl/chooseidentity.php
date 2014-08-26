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
	function chooseUser(id) {
		if (!id) {
			alert("<?php echo JText::_("No user provided!") ?>");
			return;
		}

		document.getElementById("ghost_user_id").value = id;
		document.getElementById("user-form").submit();
	}

	function chooseGroup(id) {
		if (!id) {
			alert("<?php echo JText::_("No group provided!") ?>");
			return;
		}

		document.getElementById("ghost_group_id").value = id;
		document.getElementById("group-form").submit();
	}
</script>
<h2><?php echo JText::_("Choose an identity") ?></h1>
<?php
	global $booking_user, $ghost_user;
	$user_name_filter = JRequest::getString("user_name_filter","");
?>
<?php numbullet("User name filter") ?>
<div style="width: 100%; text-align: center">
<form method="POST" action="index.php">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="layout" value="chooseidentity"/>
<!--
<input type="hidden" name="limit" value="<?php echo JRequest::getInt('limit', 10) ?>"/>
<input type="hidden" name="limitstart" value="<?php echo JRequest::getInt('limitstart', 0) ?>"/>
-->
<input id="user_name_filter" name="user_name_filter" value="<?php echo $user_name_filter ?>">
<input class="button" type="submit" name="" value="<?php echo JText::_("Filter") ?>">
<input onClick="document.getElementById('user_name_filter').value = '%'" class="button" type="submit" name="" value="<?php echo JText::_("All") ?>">
</form>
</div>

<?php if ($user_name_filter || _has_ghost_user()) { ?>
<?php numbullet('Choose an user') ?>
<form name="user-form" id="user-form" method="POST">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="layout" value="chooseidentity"/>
<input type="hidden" name="task" value="set_ghost_identity"/>
<input type="hidden" id="ghost_user_id" name="ghost_user_id" value="0"/>
<input type="hidden" name="user_name_filter" value="<?php echo $user_name_filter ?>"/>
</form>

<table class="hl">
	<thead>
		<th><?php echo JText::_("User") ?></th>
		<th><?php echo JText::_("Address") ?></th>
		<th><?php echo JText::_("Social Security Number") ?></th>
		<th></th>
	</thead>
	<?php
		if ($user_name_filter) {
			$this->users_model->setFilter($user_name_filter);
			$this->users_model->setFilterField('name');
			$users = $this->users_model->getData(INCLUDE_LIMIT);

			foreach ($users as $user) {
				echo "<tr>";
				echo "<td>" . $user['name'] . "</td>";
				echo "<td>" . $user['address'] . " " . $user['ZIP'] . " " . $user['town'] . " (" . $user['district'] . ")</td>";
				echo "<td>" . $user['social_security_number'] . "</td>";
				echo "<td><button class=\"button\" onClick=\"chooseUser(" . $user['id'] . ")\">" . JText::_("Choose") . "</button></td>";
				echo "</tr>";
			}

			if (!count($users)) {
				echo '<tr><td colspan="4" style="text-align: center; font-weight: bold">' . JText::_("No user available") . '</td></tr>';
			} else {
				pagination($this->users_model, 4, array("user_name_filter" => $user_name_filter));
			}
		} else {
			echo '<tr><td colspan="4" style="text-align: center; font-weight: bold">' . JText::_("Please provide a part of user name to choose one") . '</td></tr>';
		}
	?>
</table>

<?php if (_has_ghost_user()) { ?>

<form name="group-form" id="group-form" method="POST">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="layout" value="chooseidentity"/>
<input type="hidden" name="task" value="set_ghost_group"/>
<input type="hidden" id="ghost_group_id" name="ghost_group_id" value="0"/>
<input type="hidden" name="user_name_filter" value="<?php echo $user_name_filter ?>"/>
</form>

<?php numbullet(JText::sprintf("Choose one of %s groups", $ghost_user['name'] )) ?>
<table class="hl">
	<thead>
		<th><?php echo JText::_("Group") ?></th>
		<th></th>
	</thead>
	<?php
		$this->user_model->setId(_user_id());
		$groups = $this->user_model->getMyGroups();

		foreach ($groups as $group) {
			echo '<tr>';
			echo '<td>' . $group['name'] . '</td>';
			echo "<td><button class=\"button\" onClick=\"chooseGroup(" . $group['id'] . ")\">" . JText::_("Choose") . "</button></td>";
			echo '</tr>';
		}
	?>
</table>
<?php } ?>
<?php } ?>

<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user&layout=choosegroup')"><?php echo JText::_("Group identity") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resources&layout=tree')"><?php echo JText::_("Book a resource") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
