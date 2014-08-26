<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<h2><?php echo JText::_("Booking retract") ?></h1>
<h3><?php echo JText::printf("You are retracting booking for resource %s on %s from %s to %s.<br>Do you want to confirm?", "Risorsa di prova", "3/8/2009", "9:00", "10:30");?></h3>
<a href="index.php?option=com_prenotown&view=resource&layout=confirmbookingretract&id=1"><button><?php echo JText::_("Yes") ?></button></a>
<button class="button" onClick="window.back()"><?php echo JText::_("No") ?></button>
