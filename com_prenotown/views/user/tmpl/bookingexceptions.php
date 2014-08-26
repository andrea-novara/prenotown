<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	JHTML::_('behavior.calendar');

	$this->db->setQuery("DELETE FROM #__prenotown_superbooking_exception WHERE exception_date = '0000-00-00 00:00:00'");
	$this->db->query();

	$booking_id = esc_query(JRequest::getInt('booking_id', 0));

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
	$exceptions = $exceptions_model->getData(DONT_INCLUDE_LIMIT);

	$begin_date = preg_replace('/\s.+/', '', $booking->tables['superbooking']->begin);
	$end_date = preg_replace('/\s.+/', '', $booking->tables['superbooking']->end);
	$begin_time = preg_replace('/[^\s]+\s/', '', $booking->tables['superbooking']->begin);
	$end_time = preg_replace('/[^\s]+\s/', '', $booking->tables['superbooking']->end);

	$periodicity = $booking->tables['superbooking']->periodicity;

	$periodicity_list = expand_periodicity($periodicity);
	$periodicity_string = implode(", ", $periodicity_list);

	$filter_booking_id = JRequest::getInt('filter_booking_id', 0);
	$filter_begin_date = JRequest::getString('filter_begin_date', date("d-m-Y"));
	$filter_begin_hour = JRequest::getString('filter_begin_hour', 0);
	$filter_end_date = JRequest::getString('filter_end_date', date("d-m-Y"));
	$filter_end_hour = JRequest::getString('filter_end_hour', 24);
	$filter_resource_id = JRequest::getInt('filter_resource_id', 0);
	$filter_group_id = JRequest::getInt('filter_group_id', 0);
	$filter_time_range_inclusive = JRequest::getInt('filter_time_range_inclusive', 0);

	$status = "filter_booking_id=$filter_booking_id&filter_begin_date=$filter_begin_date&filter_begin_hour=$filter_begin_hour&filter_end_date=$filter_end_date&filter_end_hour=$filter_end_hour&filter_resource_id=$filter_resource_id&filter_group_id=$filter_group_id&filter_time_range_inclusive=$filter_time_range_inclusive";
?>
<?php global $prenotown_user, $booking_user ?>
<script language="Javascript" type="text/Javascript">
</script>
<h2><?php echo JText::_("Exceptions on booking") . ' n. ' . $booking_id ?></h1>
<?php format_booking_by_id($booking_id) ?>
<?php
	$this->db->setQuery("SELECT DATE(NOW())");
	$now = $this->db->loadResult();
	$now_ = explode('-', $now);
?>
<?php numbullet("Edit current exceptions") ?>
<table class="hl" cellspacing=0 cellpadding=0>
	<thead>
		<th><?php echo JText::_("Date") ?></th>
		<th><?php echo JText::_("Actions") ?></th>
	</thead>
	<?php
		$today = strtotime(date("Y-m-d"));
		foreach ($exceptions as $e) {
			echo '<tr><td>' . date_sql_to_human($e['exception_date']) . '</td><td>';
			$estamp = strtotime($e['exception_date']);
			// JError::raiseNotice(500, "Andrea: estamp = $estamp -- today = $today");
			$date_ = explode("-", $e['exception_date']);
			if (($estamp > $today) or _status('admin')) {
				// exception can be retracted only if not already expired
				echo '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=user&layout=bookingExceptions&task=delete_exception&exception_id=' . $e['id'] . '&booking_id=' . $booking_id . '&' . $status . '\')">' . JText::_("Delete") . '</button>';
			} else {
				echo JText::_("No actions allowed on this exception");
			}
			echo '</td></tr>';
		}

		if (!count($exceptions)) {
			echo '<td style="text-align: center; font-weight: bold" colspan="6">' . JText::_("No exceptions") . '</td>';
		} else {
			pagination($exceptions_model, 6, array('booking_id' => $booking_id));
		}
	?>
</table>

<br/><br/>
<?php numbullet("Add new exception") ?>
<div style="text-align: center; width: 100%">
<form method="POST" id="add-exception" name="add-exception">
	<input type="hidden" name="option" value="com_prenotown"/>
	<input type="hidden" name="view" value="user"/>
	<input type="hidden" name="layout" value="bookingExceptions"/>
	<input type="hidden" name="task" value="add_exception"/>
	<input type="hidden" name="booking_id" id="booking_id" value="<?php echo $booking_id ?>"/>
	<input type="hidden" name="filter_booking_id" value="<?php echo $filter_booking_id ?>"/>
	<input type="hidden" name="filter_begin_date" value="<?php echo $filter_begin_date ?>"/>
	<input type="hidden" name="filter_begin_hour" value="<?php echo $filter_begin_hour ?>"/>
	<input type="hidden" name="filter_end_date" value="<?php echo $filter_end_date ?>"/>
	<input type="hidden" name="filter_end_hour" value="<?php echo $filter_end_hour ?>"/>
	<input type="hidden" name="filter_resource_id" value="<?php echo $filter_resource_id ?>"/>
	<input type="hidden" name="filter_group_id" value="<?php echo $filter_group_id ?>"/>
	<input type="hidden" name="filter_time_range_inclusive" value="<?php echo $filter_time_range_inclusive ?>"/>

	<?php echo JHTML::_('calendar', date("d-m-Y"), 'exception_date', 'exception_date', '%d-%m-%Y') ?>
	&nbsp;&nbsp;<input type="submit" class="button" value="<?php echo JText::_("Add") ?>"/>
</form>
</div><br/>

<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user&layout=globalbooking&<?php echo $status ?>')"><?php echo JText::_("Global booking") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<?php if (_status('superadmin')) { ?>
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user&layout=bookingDetails&booking_id=<?php echo $booking_id ?>')"><?php echo JText::_("Show details") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<?php } ?>
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user&layout=currentBooking')"><?php echo JText::_("Current booking") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
