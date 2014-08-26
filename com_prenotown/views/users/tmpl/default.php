<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<?php
	if (!_status('operator')) {
		echo '<h2>' . JText::_("You need to be an operator to access this area") . '</h2>';
	} else {
?>
<h2><?php echo JText::_("User list") ?></h1>
<script language="Javascript" type="text/javascript">
	function submitForm() {
		document.getElementById('task').value = 'update_users';
		document.getElementById('users-form').submit();
	}

	function chooseUser(id) {
		if (!id) {
			alert("<?php echo JText::_("No user provided!") ?>");
			return;
		}

		document.getElementById("ghost_user_id").value = id;
		document.getElementById("user-form").submit();
	}

	function bookAResource(id) {
		document.getElementById("view").value = "resources";
		document.getElementById("layout").value = "tree";
		chooseUser(id);
	}

	function deleteUser(id) {
		if (confirm("<?php echo JText::_("Do you really want to delete this user?") ?>")) {
			redirect('index.php?option=com_prenotown&view=users&task=delete_user&user_id=' + id);
		}
		return false;
	}
</script>
<style> td.left { width: 100px } </style>
<?php
	$user_name_filter = JRequest::getString('user_name_filter','');
?>

<form method="POST">
<div style="width: 100%; margin-bottom: 20px; text-align: center">
<?php numbullet("Filter by name") ?>
<input type="hidden" name="limit" value="<?php echo JRequest::getInt('limit', 10) ?>"/>
<input type="hidden" name="limitstart" value="0"/>
<input style="width: 200px" id="user_name_filter" name="user_name_filter" value="<?php echo $user_name_filter ?>">
<input type="submit" class="button" name="action" value="<?php echo JText::_("Filter") ?>">
<input onClick="document.getElementById('user_name_filter').value = '%'" class="button" type="submit" name="" value="<?php echo JText::_("All") ?>">
<br/>
</div>
</form>

<?php if ($user_name_filter) { ?>
<?php numbullet('Manage users') ?>
	<?php
		$colspan = _status("operator") ? 6 : 5;
		if ($user_name_filter) {
			$this->model->addFilter("name LIKE " . $this->db->quote("%$user_name_filter%"));
			$users = $this->model->getData(1);

			echo '<div id="elements-container">';
			foreach ($users as $user) {
				echo "<table class=\"element\">";
				echo '<tr><td class="left">Nome</td><td>' . $user['name'] . '</td></tr>';
				echo '<tr><td class="left">C.F.</td><td>' . $user['social_security_number'] . '</td>';
				echo '<tr><td class="left">Indirizzo</td><td>' . $user['address'] . '</td></tr>';
				echo '<tr><td class="left">Email</td><td>' . $user['email'] . '</td>';
				echo '<tr><td class="left">Status</td><td>' . $user['status'] . '</td>';
				echo '<tr><td style="text-align: right;" colspan="2"><hr/>';
				echo '<button onclick="chooseUser(' . $user['id'] . ')" class="button">Impersonare</button>';
				echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
				echo '<button onclick="bookAResource(' . $user['id'] . ')" class="button">Prenotare</button>';
				echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
				echo '<button class="button" onClick="return redirect(\'index.php?option=com_prenotown&view=user&layout=modifyother&user_id=' . $user['id'] . '\'); return false;">' . JText::_("Edit") . '</button> ';
				echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
				echo '<button class="button" onClick="return deleteUser(' . $user['id'] . ')">' . JText::_("Delete") . '</button>';
				echo '</td></tr></table><br/>';
			}
			echo "</div>";

			pagination($this->model, 0, array('user_name_filter' => $user_name_filter));
		} else {
			// echo '<tr><td colspan="6" style="text-align: center; font-weight: bold">' . JText::_("Please provide a part of user name to choose one") . '</td></tr>';
			echo '<div style="text-align: center; font-weight: bold">' . JText::_("Please provide a part of user name to choose one") . '</div>';
		}
	?>
<?php } ?>
<br>
<form name="user-form" id="user-form" method="POST">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" id="view" name="view" value="users"/>
<input type="hidden" id="layout" name="layout" value="default"/>
<input type="hidden" name="task" value="set_ghost_identity"/>
<input type="hidden" id="ghost_user_id" name="ghost_user_id" value="0"/>
<input type="hidden" name="user_name_filter" value="<?php echo $user_name_filter ?>"/>
</form>
<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
<?php } ?>
