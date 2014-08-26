<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	JHTML::_('behavior.calendar');

	$booking_id = esc_query(JRequest::getInt('booking_id', 0));

	// expand the booking
	$this->db->setQuery("CALL #__prenotown_expand_booking($booking_id, @cost, 1)");
	$this->db->query();

	$this->db->setQuery("CALL #__prenotown_expand_booking_apply_unavailability()");
	$this->db->query();

	// load booking details
	$this->db->setQuery("SELECT * FROM #__prenotown_booking_expansion");
	$details = $this->db->loadAssocList();

	// load the total cost
	$this->db->setQuery("SELECT @cost");
	$total_cost = round($this->db->loadResult(), 2);

	// load the booking first
	$booking =& JModel::getInstance('Superbooking', 'PrenotownModel');
	$booking->setId($booking_id);

	// then the resource
	$resource =& JModel::getInstance('Resource', 'PrenotownModel');
	$resource->setId($booking->tables['superbooking']->resource_id);

	// and the user that booked and the group
	$user =& JModel::getInstance('User', 'PrenotownModel');
	$user->setId($booking->tables['superbooking']->user_id);

	$usergroup =& JModel::getInstance('UserGroup', 'PrenotownModel');
	$usergroup->setId($booking->tables['superbooking']->group_id);

	// lastly we load the exceptions
	$exceptions_model =& JModel::getInstance('SuperbookingExceptions', 'PrenotownModel');
	$exceptions_model->addFilter("booking_id = " . $this->db->quote($booking_id));
	$exceptions = $exceptions_model->getData(1);

	$begin_date = preg_replace('/\s.+/', '', $booking->tables['superbooking']->begin);
	$end_date = preg_replace('/\s.+/', '', $booking->tables['superbooking']->end);
	$begin_time = preg_replace('/[^\s]+\s/', '', $booking->tables['superbooking']->begin);
	$end_time = preg_replace('/[^\s]+\s/', '', $booking->tables['superbooking']->end);

	$periodicity = $booking->tables['superbooking']->periodicity;

	$periodicity_list = expand_periodicity($periodicity);
	$periodicity_string = implode(", ", $periodicity_list);
?>
<?php global $prenotown_user, $booking_user ?>
<script language="Javascript" type="text/Javascript">
</script>
<h2><?php echo JText::_("Details on booking") . ' n. ' . $booking_id ?></h1>
<?php format_booking_by_id($booking_id) ?><br/>
<table class="hl" cellspacing=0 cellpadding=0>
	<thead>
		<th></th>
		<th><?php echo JText::_("Date") ?></th>
		<th><?php echo JText::_("Time range") ?></th>
		<th style="text-align: right; padding-right: 10px;"><?php echo JText::_("Cost") ?></th>
	</thead>
	<?php
		foreach ($details as $d) {
			$date = getdate(strtotime($d['begin_date']));
			if ($d['excepted'] == 1) {
				echo '<tr title="Questa giornata è considerata un\'eccezione"><td><span class="redstar">*</span>';
			} else if ($d['excepted'] == 2) {
				echo '<tr title="Questa giornata cade in un periodo di chiusura della risorsa"><td><span class="bluestar">*</span>';
			} else {
				echo '<tr><td>';
			}
			echo "\n";
			echo '</td><td style="font-family: monospace">' . strtoupper(substr(JText::_($date['weekday']), 0, 3)) . " " . date_sql_to_human($d['begin_date']);
			if (strcmp($d['begin_date'], $d['end_date']) != 0) {
				echo " " . JText::_("up to") . " " . date_sql_to_human($d['end_date']);
			}
			echo '</td><td>';
			echo preg_replace('/:00$/', '', $d['begin_time']) . " - " . preg_replace('/:00$/', '', $d['end_time']);
			echo '</td><td style="text-align: right; padding-right: 10px;">';
			if ($d['excepted']) {
				echo '0.00 &euro;';
			} else {
				printf("%.2f &euro;", round($d['cost'], 2));
			}
			echo '</td></tr>';
		}
	?>
	<tr><td colspan="4" style="margin-top: 0em; padding: 2px; padding-right: 10px; width: 99.0%; background-color: black; color: white; text-align: right; font-weight: bold\">Totale: <?php printf("%.2f", $total_cost) ?>&nbsp;&euro;</td></tr>
</table><br/>

<div class="button-footer">
<?php if (_status('superadmin')) { ?>
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user&layout=bookingExceptions&booking_id=<?php echo $booking_id ?>')"><?php echo JText::_("Manage exceptions") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<?php } ?>
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user&layout=currentBooking')"><?php echo JText::_("Current booking") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
