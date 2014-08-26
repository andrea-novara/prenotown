<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	global $booking_user;
	$booking_id = JRequest::getInt('booking_id', 0);
?><html><head><link rel="stylesheet" href="/trezzo/components/com_prenotown/assets/css/prenotown.css" type="text/css" />
<?php if (JRequest::getString('format', 'html') != 'pdf') { ?>
<style>
	* {
		font-family: sans-serif;
		font-size: 14px;
	}
	.booking_ticket {
		border: 1px solid #ccc;
		line-height: 22px;
		font-size: 16px;
		background-image: url('components/com_prenotown/assets/ticket_background.jpg');
		width: 800px;
	}
	.booking_ticket div {
		padding: 10px;
	}
</style></head><body>
<? } ?>
<div style="width: auto; border-bottom: 1px dashed black; padding: 20px;">
<?php format_booking_by_id($booking_id) ?>
</div>
<br/>
Tagliare lungo la linea tratteggiata<br/><br/>
[<a href="javascript:window.back()"><?php echo JText::_("Back") ?></a>]
[<a href="index.php?option=com_prenotown&view=resources&layout=tree"><?php echo JText::_("Other resources") ?></a>]
[<a href="index.php?option=com_prenotown&view=resource&id=<?php echo $this->id ?>"><?php echo JText::_("This resource") ?></a>]
[<a href="index.php?option=com_prenotown&view=user"><?php echo JText::_("User panel") ?></a>]
</body></html>
