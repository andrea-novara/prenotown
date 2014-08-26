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
		document.getElementById('categories-form').submit();
	}

	function create_category() {
		document.getElementById('new_category').value = document.getElementById('new_category_stub').value;
		if (document.getElementById('new_category').value.length == 0) {
			alert("<?php echo JText::_("Please provide new category name") ?>");
			return;
		}

		document.getElementById('task').value = 'create_new_category';

		check_form();
	}
</script>
<h2><?php echo $this->name . ": " . JText::_("Resource categories") ?></h1>
<form name="categories-form" id="categories-form" method="POST">
<input type="hidden" name="task" id="task" value="update_categories"/>
<input type="hidden" name="new_category" id="new_category" id="new_category" value="" />
<table class="hl" style="width: 100%" cellspacing=0 cellpadding=0>
<tr><td colspan=2><?php numbullet("Current categories") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Current categories") ?></td><td>
<table>
	<thead>
		<th width="100%"><?php echo JText::_("Declared categories") ?></th>
	</thead>
	<tr>
		<td><ul>
<?php
	$result = $this->model->getCategories();
	foreach ($result as $r) {
		echo '<input type="checkbox" name="delete_category[]" value="' . $r['id'] . '" title="' . JText::_("Remove this category") . '"/>&nbsp;' . $r['name'] . '<br/>';
	}
?>
		</ul></td>
	</tr></table>
</td></tr>
<tr><td colspan=2><?php numbullet("Insert into a category") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Insert into a category") ?></td><td>
	<select name="add_category">
	<option value=""><?php echo JText::_("Select a category") ?></option>
	<?php
		$result = $this->model->getUnrelatedCategories();
		foreach ($result as $r) {
			echo '<option value="'. $r['id'] . '">' . $r['name'] . '</option>';
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
<input title="<?php echo JText::_("New category name") ?>" id="new_category_stub" name="new_category_stub" value=""/>&nbsp;&nbsp;<button class="button" onClick="create_category()"><?php echo JText::_("New category") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=edit&id=<?php echo $this->id ?>')"><?php echo JText::_("Back to resource") ?></button>
</div>
