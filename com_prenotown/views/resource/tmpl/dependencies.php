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
		document.getElementById('dependencies-form').submit();
	}
</script>
<h2><?php echo $this->name . ": " . JText::_("Resource dependencies") ?></h1>
<form name="dependencies-form" id="dependencies-form" method="POST">
<input type="hidden" name="task" value="update_dependencies"/>
<table class="hl" style="width: 100%" cellspacing=0 cellpadding=0>
<tr><td colspan=2><?php numbullet("Current dependences") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Current dependences") ?></td><td>
<table>
	<thead>
		<th width="50%"><?php echo JText::_("Resources that depend on this one") ?></th>
		<th width="50%"><?php echo JText::_("Resources this one depends on") ?></th>
	</thead>
	<tr>
		<td><ul>
<?php
	$result = $this->model->getSlaveResources();
	foreach ($result as $r) {
		echo '<input type="checkbox" name="delete_dependant_resource[]" value="' . $r['id'] . '" title="' . JText::_("Remove this dependence") . '"/>&nbsp;' . $r['name'] . ', ' . $r['address'] . '<br/>';
	}
?>
		</ul></td>
		<td><ul>
<?php
	$result = $this->model->getMasterResources();
	foreach ($result as $r) {
		echo "<li><a href=\"index.php?option=com_prenotown&view=resource&layout=edit&id=" . $r['id'] . "\">" . $r['name'] . ', ' . $r['address'] . "</a>";
	}
?>
		</ul></td>
	</tr></table>
</td></tr>
<tr><td colspan=2><?php numbullet("Add dependence") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Add dependence") ?></td><td>
	<select name="add_dependence">
	<option value=""><?php echo JText::_("Select a resource") ?></option>
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
