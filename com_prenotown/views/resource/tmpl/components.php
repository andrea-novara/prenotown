<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<script language="javascript" type="text/javascript">
	function check_form() {
		var form = document.getElementById('components-form');
		if (!form) {
			return;
		}

		form.submit();
	}
</script>
<h2><?php echo $this->name . ": " . JText::_("Resource components") ?></h1>
<form name="components-form" id="components-form" method="POST">
<input type="hidden" name="task" value="update_components"/>
<table class="hl">
<tr><td colspan=2><?php numbullet("Current components") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Current components") ?></td><td>
<table>
	<thead>
		<th width="50%"><?php echo JText::_("Components of this resource") ?></th>
		<th width="50%"><?php echo JText::_("Resources this one is a component of") ?></th>
	</thead>
	<tr>
		<td><ul>
<?php
	$result = $this->model->getComposingResources();
	foreach ($result as $r) {
		echo '<input type="checkbox" name="delete_composing_resource[]" value="' . $r['id'] . '" title="' . JText::_("Remove this component") . '"/>&nbsp;' . $r['name'] . ', ' . $r['address'] . '<br/>';
	}
?>
		</ul></td>
		<td><ul>
<?php
	$result = $this->model->getComposedResources();
	foreach ($result as $r) {
		echo "<li><a href=\"index.php?option=com_prenotown&view=resource&layout=edit&id=" . $r['id'] . "\">" . $r['name'] . ', ' . $r['address'] . "</a>";
	}
?>
		</ul></td>
	</tr></table>
</td></tr>
<tr><td colspan=2><?php numbullet("Add component") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Add component") ?></td><td>
	<select name="add_component">
	<option value="0"><?php echo JText::_("Select a component") ?></option>
	<?php
		$result = $this->model->getUnrelatedResources();
		foreach ($result as $r) {
			echo '<option value="'. $r['id'] . '">' . $r['name'] . ', ' . $r['address'] . '</option>';
		}
	?>
	</select>
</td></tr>
</table>

<br>
<?php require_once("url_params.php") ?>
</form>
<div class="button-footer">
<button class="button" onClick="check_form()"><?php echo JText::_("Update") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=edit&id=<?php echo $this->id ?>')"><?php echo JText::_("Back to resource") ?></button>
</div>
