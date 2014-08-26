<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<h2><?php echo JText::_("Booking retracted") ?></h1>
<h3><?php echo JText::printf("Your booking for resource %s on %s from %s to %s has been retracted.", "Risorsa di prova", "3/8/2009", "9:00", "10:30");?></h3>
<a href="index.php?option=com_prenotown&view=resource&layout=confirmbookingretract&id=1"><button><?php echo JText::_("Yes") ?></button></a>

