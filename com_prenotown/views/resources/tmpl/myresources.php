<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<script language="Javascript" type="text/Javascript">
	function deleteResource(id) {
		if (!id) {
			alert("<?php echo JText::_("No resource id provided") ?>");
			return;
		}

		if (confirm("<?php echo JText::_("Do you really want to delete this resource?") ?>")) {
			redirect('index.php?option=com_prenotown&view=resources&layout=myresources&task=delete_resource&resource_id=' + id);
		}
	}

	function editResource(id) {
		if (!id) {
			alert("<?php echo JText::_("No resource id provided") ?>");
			return;
		}

		redirect("index.php?option=com_prenotown&view=resource&layout=edit&id=" + id);
	}

	function suspendResource(id) {
		if (!id) {
			alert("<?php echo JText::_("No resource id provided") ?>");
			return;
		}

		if (confirm("<?php echo JText::_("Do you really want to suspend this resource?") ?>")) {
			alert("Suspend resource... code me!");
		}
	}

	function resourceBooking(id) {
		if (!id) {
			alert("<?php echo JText::_("No resource id provided") ?>");
			return;
		}
		redirect("index.php?option=com_prenotown&view=resource&id=" + id + "&layout=currentBooking");
		return false;
	}
</script>

<h2><?php echo JText::_("My resources") ?></h1>

<?php numbullet('Choose a resource') ?>
<table class="hl">
	<thead>
		<th><?php echo JText::_("Name") ?></th>
		<th><?php echo JText::_("Operations") ?></th>
	</thead>
	<?php
		if (_status('superadmin')) {
			$resources = $this->model->getData();
		} else {
			$resources = $this->model->getByAdmin($this->user->id);
		}

		if (!is_array($resources)) {
			$resources = array();
		}

		foreach ($resources as $resource) {
			$description = $resource['name'] . '<br>' . $resource['address'];
			if (_status('superadmin')) {
				$this->db->setQuery("SELECT name FROM #__users WHERE id = " . $resource['admin_id']);
				$adm = $this->db->loadResult();
				$description .= "<br/>(Admin: $adm)";
			}

			echo '<tr>';
			echo '<td>' . $description . '</td>';
			echo '<td>';
			echo '<button class="button" onClick="deleteResource(' . $resource['id'] . ')">' . JText::_("Delete") . '</button> ';
			echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
			echo '<button class="button" onClick="editResource(' . $resource['id'] . ')">' . JText::_("Edit") . '</button> ';
			echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
			echo '<button class="button" onClick="resourceBooking(' . $resource['id'] . ')">' . JText::_("Current booking") . '</button> ';
			// echo '<button class="button" onClick="suspendResource(' . $resource['id'] . ')">' . JText::_("Suspend (??)") . '</button> ';
			echo '</td>';
			echo '</tr>';
		}
	?>
</table>

<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
