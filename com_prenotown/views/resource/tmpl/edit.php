<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<style>
input[type="text"], input[type="password"], textarea { width: 500px }
</style>
<script>
	function check_form() {
		document.getElementById('edit-form').submit();
	}
</script>
<h2><?php echo $this->name . ": " . JText::_("Resource edit") ?></h1>
<form method="POST" name="creation-form" id="edit-form">
<input type="hidden" name="task" value="edit_resource"/>
<table class="hl" cellspacing=0 cellpadding=0>
<tr><td colspan=2><?php numbullet('Resource name and address') ?></td></tr>
<tr><td class="left"><?php echo JText::_("Name") ?></td><td><input type="text" name="resource_name" value="<?php echo htmlspecialchars($this->model->tables['main']->name) ?>"/></td></tr>
<tr><td class="left"><?php echo JText::_("Address") ?></td><td><input type="text" name="resource_address" value="<?php echo htmlspecialchars($this->model->tables['main']->address) ?>"/></td></tr>
<tr><td class="left"><?php echo JText::_("Description") ?></td><td><textarea rows=5 name="resource_description"><?php echo htmlspecialchars($this->model->tables['main']->description) ?></textarea></td></tr>
<tr><td class="left"><?php echo JText::_("Notes") ?></td><td><textarea rows=5 name="resource_notes"><?php echo htmlspecialchars($this->model->tables['main']->notes) ?></textarea></td></tr>
<tr><td colspan=2><?php numbullet('Administrator') ?></td></tr>
<tr><td class="left"><?php echo ucfirst(JText::_("Administrator")) ?></td><td>
	<select name="admin_id" id="admin_id">
	<?php
		$sql = "SELECT DISTINCT #__users.id, name FROM #__users JOIN #__prenotown_user_complement WHERE #__prenotown_user_complement.status IN ('admin','superadmin')";
		$this->db->setQuery($sql);
		$admins = $this->db->loadAssocList();
		foreach ($admins as $a) {
			$selected = '';
			if ($this->model->tables['main']->admin_id == $a['id']) {
				$selected = 'selected';
			}
			printf('<option value="%d" %s>%s</option>', $a['id'], $selected, $a['name']);
		}
	?>
	</select>
</td></tr>
<tr><td colspan=2><?php numbullet('Time limits') ?></td></tr>
<tr><td class="left"><?php echo JText::_("Cost function type") ?></td><td>
<?php
	$this->costfunctions->addFilter("id = " . $this->model->tables['main']->cost_function_id);
	$cfs = $this->costfunctions->getData();
	echo "<b>" . JText::_($cfs[0]['name']) . "</b>";
?>
</td></tr>
<tr><td class="left"><?php echo JText::_("Deadline") ?></td><td><input style="width: auto" name="deadline" value="<?php echo htmlspecialchars($this->model->tables['resource']->deadline) ?>"/> <?php echo JText::_("days") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Max advance") ?></td><td><input style="width: auto" name="max_advance" value="<?php echo htmlspecialchars($this->model->tables['resource']->max_advance) ?>"/> <?php echo JText::_("days") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Paying period") ?></td><td><input style="width: auto" name="paying_period" value="<?php echo htmlspecialchars($this->model->tables['resource']->paying_period) ?>"/> <?php echo JText::_("days") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Approval period") ?></td><td><input style="width: auto" name="approval_period" value="<?php echo htmlspecialchars($this->model->tables['resource']->approval_period) ?>"/> <?php echo JText::_("days") ?></td></tr>
<tr><td colspan=2><?php numbullet('Categories') ?></td></tr>
<tr><td class="left"><?php echo JText::_("Current categories") ?></td><td>
	<ul>
<?php
	$result = $this->model->getCategories();
	foreach ($result as $r) {
		echo "<li>" . $r['name'] . "</li>";
	}
	if (!count($result)) {
		echo "<b>" . JText::_("No categories") . "</b>";
	}
?>
	</ul>
</td></tr>
<tr><td colspan=2><?php numbullet('Related resources') ?></td></tr>
<tr><td class="left"><?php echo JText::_("Dependencies") ?></td><td>
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
		echo "<li><a href=\"index.php?option=com_prenotown&view=resource&layout=edit&id=" . $r['id'] . "\">" . $r['name'] . ', ' . $r['address'] . "</a>";
	}
	if (!count($result)) {
		echo "<b>" . JText::_("No resources") . "</b>";
	}
?>
		</ul></td>
		<td><ul>
<?php
	$result = $this->model->getMasterResources();
	foreach ($result as $r) {
		echo "<li><a href=\"index.php?option=com_prenotown&view=resource&layout=edit&id=" . $r['id'] . "\">" . $r['name'] . ', ' . $r['address'] . "</a>";
	}
	if (!count($result)) {
		echo "<b>" . JText::_("No resources") . "</b>";
	}
?>
		</ul></td>
	</tr></table>
</td></tr>
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
		echo "<li><a href=\"index.php?option=com_prenotown&view=resource&layout=edit&id=" . $r['id'] . "\">" . $r['name'] . ', ' . $r['address'] . "</a>";
	}
	if (!count($result)) {
		echo "<b>" . JText::_("No resources") . "</b>";
	}
?>
		</ul></td>
		<td><ul>
<?php
	$result = $this->model->getComposedResources();
	foreach ($result as $r) {
		echo "<li><a href=\"index.php?option=com_prenotown&view=resource&layout=edit&id=" . $r['id'] . "\">" . $r['name'] . ', ' . $r['address'] . "</a>";
	}
	if (!count($result)) {
		echo "<b>" . JText::_("No resources") . "</b>";
	}
?>
		</ul></td>
	</tr>
</table>
</td></tr>
</table>

<br>
<?php require_once("url_params.php") ?>
<!--
<input type="submit" name="action" value="<?php echo JText::_("Update") ?>"/>
-->
</form>
<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=costfunction&id=<?php echo $this->id ?>')"><?php echo JText::_("Cost Function") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<?php if (count($this->model->getComposedResources()) <= 0) { ?>
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=availabilityrange&id=<?php echo $this->id ?>')"><?php echo JText::_("Availability range") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<?php } ?>
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=unavailability&id=<?php echo $this->id ?>')"><?php echo JText::_("Unavailability ranges") ?></button><br/>

<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=categories&id=<?php echo $this->id ?>')"><?php echo JText::_("Categories") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=dependencies&id=<?php echo $this->id ?>')"><?php echo JText::_("Dependencies") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=components&id=<?php echo $this->id ?>')"><?php echo JText::_("Components") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=attachments&id=<?php echo $this->id ?>')"><?php echo JText::_("Attachments") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="check_form()"><?php echo JText::_("Update") ?></button><br/>
<!--
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=book&id=<?php echo $this->id?>')"><?php echo JText::_("Book") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resources&layout=myresources')"><?php echo JText::_("Other resources") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
-->
</div>
