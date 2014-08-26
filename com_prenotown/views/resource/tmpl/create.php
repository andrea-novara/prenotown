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
		document.getElementById('creation-form').submit();
	}
</script>
<h2><?php echo JText::_("Resource creation") ?></h1>
<form method="POST" name="creation-form" id="creation-form">
<input type="hidden" name="task" value="create_resource"/>
<table class="hl" cellspacing=0 cellpadding=0>
<tr><td colspan=2><?php numbullet('Resource name and address') ?></td></tr>
<tr><td class="left"><?php echo JText::_("Name") ?></td><td><input type="text" name="resource_name" value="Nome della risorsa"/></td></tr>
<tr><td class="left"><?php echo JText::_("Address") ?></td><td><input type="text" name="resource_address" value="Indirizzo della risorsa"/></td></tr>
<tr><td class="left"><?php echo JText::_("Description") ?></td><td><textarea rows=5 name="resource_description">Una descrizione dell'oggetto</textarea></td></tr>
<tr><td class="left"><?php echo JText::_("Notes") ?></td><td><textarea rows=5 name="resource_notes">Note</textarea></td></tr>
<tr><td colspan=2><?php numbullet('Time limits') ?></td></tr>
<tr><td class="left"><?php echo JText::_("Cost function type") ?></td><td><select name="cost_function_id">
<?php
	$cfs = $this->costfunctions->getData();
	foreach ($cfs as $cf) {
		echo '<option value="' . $cf['id'] . '">' . JText::_($cf['name']) . '</option>';
	}
?>
</select></td></tr>
<!--
<tr><td class="left"><?php echo JText::_("Picture") ?></td><td><input type="file" name="picture"></td></tr>
-->
<tr><td class="left"><?php echo JText::_("Deadline") ?></td><td><input style="width: auto" name="deadline" value="1"/> <?php echo JText::_("days") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Max advance") ?></td><td><input style="width: auto" name="max_advance" value="30"/> <?php echo JText::_("days") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Paying period") ?></td><td><input style="width: auto" name="paying_period" value="7"/> <?php echo JText::_("days") ?></td></tr>
<tr><td class="left"><?php echo JText::_("Approval period") ?></td><td><input style="width: auto" name="approval_period" value="7"/> <?php echo JText::_("days") ?></td></tr>
</table>

<br>
<?php require_once("url_params.php") ?>
<!--
<input type="submit" name="action" value="<?php echo JText::_("Create") ?>"/>
-->
</form>
<div class="button-footer">
<button class="button" onClick="check_form()"><?php echo JText::_("Create") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
