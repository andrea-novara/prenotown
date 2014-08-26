<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<?php require_once(JPATH_COMPONENT_SITE . "/assets/resource_style.php") ?>
<h2><?php echo JText::_("User status") ?></h1>

<form method="POST">
	<?php JText::printf("Assigning to user %s state", "Utente DiProva") ?> <select>
		<option><?php echo JText::_("Citizen") ?></option>
		<option><?php echo JText::_("Operator") ?></option>
		<option><?php echo JText::_("Resource Admin") ?></option>
		<option><?php echo JText::_("Super Admin") ?></option>
	</select><br>
	<input type="submit" value="<?php echo JText::_("Change status") ?>">
</form>
<div class="button-footer">
<a href="index.php?option=com_prenotown&view=user"><button><?php echo JText::_("User panel") ?></button></a>
</div>
