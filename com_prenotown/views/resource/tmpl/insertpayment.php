<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<h2><?php echo JText::_("Insert payment") ?></h1>
<?php
	$resource_id = $this->id;
	$booking_id = JRequest::getInt('booking_id', 0);
?>
<h2><?php echo $this->name . ": " . JText::_("Payment for booking n.") . sprintf(" %08d", $booking_id) ?></h2>
<?php format_booking_by_id($booking_id); ?>

<br/>

<h2><?php echo JText::_("Payment data:") ?></h2>

<form method="POST" id="insert-form">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="layout" value="currentbooking"/>
<input type="hidden" name="task" value="insert_payment"/>
<input type="hidden" name="booking_id" value="<?php echo $booking_id ?>"/>
<input type="hidden" name="id" value="<?php echo $booking_id ?>"/>
<br/><br/>
<label for="amount"><?php echo JText::_("Method") ?>:</label>
<input type="radio" name="method" value="pos">Bancomat
<input type="radio" name="method" value="check">Bollettino postale
<br/>
<br/>
<label for="amount"><?php echo JText::_("Amount:") ?></label>
<input type="text" name="amount" value=""/> &euro;<br/><br/><br/>
</form>

<script>
	function submitForm() {
		document.getElementById('insert-form').submit();
	}
</script>

<div class="button-footer">
<button class="button" onClick="submitForm()"><?php echo JText::_("Insert payment") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=paybookingbycard&id=<?php echo $booking->resource_id ?>&booking_id=<?php echo $booking_id ?>&booking_begin_date=<?php echo $booking_begin_date ?>&booking_end_date=<?php echo $booking_end_date ?>&booking_begin_hour=<?php echo $booking_begin_hour ?>&booking_end_hour=<?php echo $booking_end_hour ?>&booking_begin_minute=<?php echo $booking_begin_minute ?>&booking_end_minute=<?php echo $booking_end_minute ?>&cost=<?php echo $cost ?>')"><?php echo JText::_("Pay by credit card") ?></button>
</div>
