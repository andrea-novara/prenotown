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
<h2>Nome della risorsa</h1>
<h3><?php echo JText::_("Are you sure you want to delete this resource?") ?></h3>
<form method="POST">
	<a href="index.php?option=com_prenotown&view=resource&layout=delete&id=<?php echo $this->id ?>&task=delete_resource"><button><?php echo JText::_("Yes") ?></button></a>
	<a href="index.php?option=com_prenotown&view=resource&layout=edit&id=<?php echo $this->id ?>"><button><?php echo JText::_("No") ?></button></a>
</form>
