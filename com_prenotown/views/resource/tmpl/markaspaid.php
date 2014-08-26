<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<h2><?php echo JText::_("Confirm payment") ?></h1>
<h2><?php echo JText::printf("Confirm the booking for resource %s on %s from %s to %s done by %s?", "Risorsa di prova", "3/5/2009", "9:00", "10:00", "Utente DiProva") ?></h2>
<a href="index.php?option=com_prenotown&view=resource&layout=resourcepaid&id=1"><button><?php echo JText::_("Yes") ?></button></a>
<button class="button" onClick="window.back()"><?php echo JText::_("Cancel") ?></button>
