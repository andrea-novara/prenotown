<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
	$booking_id = JRequest::getInt('booking_id', 0);
	$this->db->setQuery("SELECT group_id FROM #__prenotown_superbooking WHERE id = $booking_id");
	_log_sql("SELECT group_id FROM #__prenotown_superbooking WHERE id = $booking_id");
	$group_id = $this->db->loadResult();
?>
<h2><?php echo $this->name . ": " . JText::_("Booking confirmed") ?></h1>
<h2><?php echo JText::_("Your booking") ?>:</h2><br/><br/>
<?php format_booking_by_id($booking_id); ?>
<br/><br/>
<?php if ($group_id <= 1) { ?>
<h2><?php JText::printf("How to pay") ?>:</h2>
<br>
<div class="pmcontainer">
<?php if (pref('bpw_idnegozio')): ?>
<div class="pm"><a href="index.php?option=com_prenotown&view=resource&layout=paybookingbycard&booking_id=<?php echo $booking_id ?>&id=<?php echo $this->id ?>"><img src="components/com_prenotown/assets/credit-card.png"><br/>Carta di credito</a></div>
<?php endif; ?>
<div class="pm"><a href="index.php?option=com_prenotown&view=resource&layout=paybookingbypos&booking_id=<?php echo $booking_id ?>&id=<?php echo $this->id ?>"><img src="components/com_prenotown/assets/pos.png"><br/>Bancomat</a></div>
<div class="pm"><a href="index.php?option=com_prenotown&view=resource&layout=paybookingbycheck&booking_id=<?php echo $booking_id ?>&id=<?php echo $this->id ?>"><img src="components/com_prenotown/assets/poste.png"><br/>Bollettino postale</a></div>
</div>
<?php } else { ?>
<h2><?php JText::printf("No payment needed") ?>:</h2><br/>
<?php echo JText::_("This booking has been bound to a group and will be billed with other bookings") ?>
<br/>
<?php } ?>
<br>
<div class="button-footer">
<button onClick="redirect('index.php?option=com_prenotown&view=resource&layout=book&id=<?php echo $this->model->_id ?>')"
class="button"><?php echo JText::_("Book this resource again") ?></button>
&nbsp;&nbsp;|&nbsp;&nbsp;
<button onClick="redirect('index.php?option=com_prenotown&view=resources&layout=tree')" class="button"><?php echo JText::_("Book another resource") ?></button>
</div>
