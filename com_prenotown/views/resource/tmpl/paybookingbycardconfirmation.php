<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<h2><?php echo JText::_("Booking payment") ?></h1>
<h2><?php echo JText::_("Your booking has been paid") ?></h2>
<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resources&layout=tree')"><?php echo JText::_("Book another resource") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user&layout=default')"><?php echo JText::_("User panel") ?></button>
</div>
