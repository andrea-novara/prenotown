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
	input { width: auto }
	td img { background-color: white }
</style>
<h2><?php echo $this->name . ": " . JText::_("Cost function profile") ?></h1>
<?php echo $this->costfunction->profileInterface() ?>
