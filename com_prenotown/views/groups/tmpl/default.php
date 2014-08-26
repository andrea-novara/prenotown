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
		document.getElementById("new-group").submit();
	}

	function deleteGroup(id) {
		if (confirm("<?php echo JText::_("Do you really want to delete this group?") ?>")) {
			redirect("index.php?option=com_prenotown&view=groups&task=delete_group&id=" + id);
		}
	}

	function chooseGroup(id) {
		if (!id) {
			alert("<?php echo JText::_("No group provided!") ?>");
			return;
		}

		document.getElementById("ghost_group_id").value = id;
		document.getElementById("group-form").submit();
	}

	function bookAResource(id) {
		document.getElementById("view").value = "resources";
		document.getElementById("layout").value = "tree";
		chooseGroup(id);
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
<style> td.left { width: 100px } </style>
<?php
	$name_filter = esc_query(JRequest::getString("name_filter",null));
	$this->model->addFilter("id <> 1"); // avoid "All" group
	if (!is_null($name_filter) and strlen($name_filter)) {
		$this->model->addFilter("name LIKE " . $this->db->quote("%$name_filter%"));
	}
?>
<h2><?php echo JText::_("Groups management") ?></h1>
<div style="width: 100%; text-align: center">
<form name="search-form" method="POST" style="display: inline" id="search-form">
<?php numbullet("Filter by group name") ?>
<input type="hidden" name="limit" value="<?php echo JRequest::getInt('limit', 10) ?>"/>
<input type="hidden" name="limitstart" value="0"/>
<input name="name_filter" id="name_filter" value="<?php echo $name_filter ?>"/>
<input class="button" type="submit" value="<?php echo JText::_("Filter") ?>"/>
</form>
<button class="button" onClick="document.getElementById('name_filter').value='%';document.getElementById('search-form').submit();"><?php echo JText::_("All") ?></button>
<button class="button" onClick="document.getElementById('name_filter').value=''"><?php echo JText::_("Reset") ?></button>
</div>

<br/>
<?php
	if (!is_null($name_filter) and strlen($name_filter)) {
		numbullet("Manage groups");
		$groups = $this->model->getData(1);

		echo '<div id="elements-container">';
		foreach ($groups as $group) {
			echo "<table class=\"element\">";
				echo '<tr><td class="left">' . JText::_("Name") . ':</td><td>' . $group['name'] . '</td></tr>';
				echo '<tr><td class="left">' . JText::_("Members") . ':</td><td>';
				$query = "SELECT name FROM #__users JOIN #__prenotown_user_group_entries ON #__prenotown_user_group_entries.user_id = #__users.id WHERE #__prenotown_user_group_entries.group_id = " . $group['id'];
				_log_sql($query);
				$this->db->setQuery($query);
				$members = $this->db->loadResultArray();
				$txt = "";
				$count = 0;
				foreach ($members as $member) {
					$txt .= "$member, ";
					$count++;
				}
				if ($count) {
					$txt = preg_replace("/, $/", "", $txt);
					echo "$txt";
				} else {
					echo JText::_("No members");
				}
				echo '</td></tr>';
				echo '<tr><td style="text-align: right;" colspan="2"><hr/>';
				echo '<button class="button" onclick="chooseGroup(' . $group['id'] . ')">Impersonare</button>';
				echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
				echo '<button class="button" onclick="bookAResource(' . $group['id'] . ')">Prenotare</button>';
				echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
				echo '<button class="button" onclick="bookingHistory(' . $group['id'] . ')">Storico prenotazioni</button>';
				echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
				echo "<button class=\"button\" onClick=\"redirect('index.php?option=com_prenotown&view=group&layout=fees&id=" . $group['id'] . "')\">" . JText::_("Fees") . "</button> ";
				echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
				echo "<button class=\"button\" onClick=\"redirect('index.php?option=com_prenotown&view=group&layout=edit&id=" . $group['id'] . "')\">" . JText::_("Edit") . "</button> ";
				echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
				echo "<button class=\"button\" onClick=\"deleteGroup(" . $group['id'] . ")\">" . JText::_("Delete") . "</button> ";
	
				echo '</td></tr></table><br/>';
		}
		echo "</div>";

		if (count($groups)) {
			pagination($this->model, 0, array('name_filter' => $name_filter));
		} else {
			echo '<div style="font-weight:bold;text-align:center">' . JText::_("No group matches the filter") . '</div>';
		}
	}

?>

<form name="group-form" id="group-form" method="POST">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" id="view" name="view" value="groups"/>
<input type="hidden" id="layout" name="layout" value="default"/>
<input type="hidden" name="task" value="set_ghost_group"/>
<input type="hidden" id="ghost_group_id" name="ghost_group_id" value="0"/>
<input type="hidden" id="name_filter" name="name_filter" value="<?php echo $name_filter ?>"/>
</form>

<br>
<div class="button-footer">
<form method="POST" name="new-group" id="new-group">
<?php echo JText::_("New group name") ?>: <input type="text" name="new_group_name">
<input type="hidden" name="task" value="create_new_group"/>
<input class="button" type="submit" value="<?php echo JText::_("Create") ?>">
<?php include("url_params.php") ?>
</form>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
