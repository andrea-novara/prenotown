<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	global $booking_user;
?>
<?php if (JRequest::getString('format', 'html') != 'pdf') { ?>
<style>
	* {
		font-family: sans-serif;
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
</style>
<? } ?>
<!--
<h2><?php echo JText::_("Booking ticket") ?></h1>
-->
<div style="float: right">
[<a href="index.php?option=com_prenotown&view=resources&layout=tree"><?php echo JText::_("Other resources") ?></a>]<br/>
[<a href="index.php?option=com_prenotown&view=resource&id=<?php echo $this->id ?>"><?php echo JText::_("This resource") ?></a>]<br/>
[<a href="index.php?option=com_prenotown&view=user"><?php echo JText::_("User panel") ?></a>]<br/>
</div>
<div class="booking_ticket">
<div style="background-color: #ccc; color: #333; font-weight: bold">
<?php JText::printf("BOOKING TICKET on resource %s", $this->model->tables['resource']->name) ?>
<br/></div>
<div><b><?php JText::printf("User name") ?>:</b><?php echo $booking_user['name'] ?><br/></div>
<div><b><?php JText::printf("Booked from") ?>:</b> <?php echo JRequest::getString('booking_begin_date') ?> <b><?php JText::printf("hour") ?>:</b> <?php printf("%02d:%02d", JRequest::getString('booking_begin_hour'), JRequest::getString('booking_begin_minute')) ?><br/></div>
<div><b><?php JText::printf("Up to") ?>:</b> <?php echo JRequest::getString('booking_end_date') ?> <b><?php JText::printf("hour") ?>:</b> <?php printf("%02d:%02d", JRequest::getString('booking_end_hour'), JRequest::getString('booking_end_minute')) ?><br/></div>

<div style="border-top: 1px solid #ccc; font-style: italic">
<?php JText::printf("This ticked must be produced on request of control staff members") ?>
</div>
</div>

<?php if (JRequest::getString('format', 'html') == 'html') { ?>
<br><br>
<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=ticket&format=pdf&id=<?php echo $this->id ?>&booking_begin_date=<?php echo $booking_begin_date ?>&booking_begin_hour=<?php echo $booking_begin_hour ?>&booking_begin_minute=<?php echo $booking_begin_minute ?>&booking_end_date=<?php echo $booking_end_date ?>&booking_end_hour=<?php echo $booking_end_hour ?>&booking_end_minute=<?php echo $booking_end_minute ?>')"><?php JText::printf("Printable version") ?></button>
&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=book&id=<?php echo $this->id ?>')"><?php echo JText::_("Book this resource again") ?></button>
&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resources&layout=tree')"><?php echo JText::_("Book another resource") ?></button>
</div>
<?php } ?>
