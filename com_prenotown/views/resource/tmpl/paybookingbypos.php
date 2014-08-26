<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	$booking_id = JRequest::getInt("booking_id", 0);
	$this->db->setQuery("CALL #__prenotown_expand_booking($booking_id, @cost, 1)");
	$this->db->query();
	$this->db->setQuery("SELECT @cost");
	$cost = $this->db->loadResult();
	$cost = float_point_to_comma(sprintf("%.2f", $cost));
?>
<?php global $prenotown_user, $booking_user, $ghost_user, $ghost_group; ?>
<h2><?php echo $this->name . ":<br/>" . JText::_("Booking payment") . " " . JText::_("by POS") ?></h1>
<h2><?php JText::printf("Your booking") ?></h2>

<?php format_booking_by_id($booking_id) ?>
<br/><h2><?php JText::printf("Paying instructions with POS") ?></h2><br/><br/>
Devi presentarti allo sportello ATOS entro il giorno lavorativo precedente a quello della prenotazione con la tessera Bancomat per ritirare le chiavi e lasciare copia della carta d’identit&agrave;.
</b>
<br><br>
<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=paybookinglater&id=<?php echo $this->id ?>&booking_id=<?php echo $booking_id ?>')"><?php echo JText::_("Pay otherwise") ?></button>
&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=book&id=<?php echo $this->id ?>')"><?php echo JText::_("Book this resource again") ?></button>
&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resources&layout=tree')"><?php echo JText::_("Book another resource") ?></button>
&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
