<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<script language="Javascript" type="text/javascript">
	function payByCard() {
		document.getElementById('layout').value = "paybookingbycard";
		document.getElementById('method').value = "creditcard";
		document.getElementById('pay-booking').submit();
	}

	function payByCheck() {
		document.getElementById('layout').value = "paybookingbycheck";
		document.getElementById('method').value = "check";
		document.getElementById('pay-booking').submit();
	}

	function payLater() {
		document.getElementById('layout').value = "paybookinglater";
		document.getElementById('method').value = "special";
		document.getElementById('pay-booking').submit();
	}

	function changeBooking() {
		document.getElementById('layout').value = "book";
		document.getElementById('task').value = "";
		document.getElementById('pay-booking').submit();
	}
</script>
<?php
	$periodic = "";
	$is_periodic = 0;
	if (JRequest::getInt('periodic', 0)) {
		$periodic = "periodic_";
		$is_periodic++;
	}

	$begin_date = JRequest::getString($periodic . 'booking_begin_date', '');
	$end_date = JRequest::getString($periodic . 'booking_end_date', '');
	$begin_time = sprintf("%02d:%02d", JRequest::getInt($periodic . "booking_begin_hour", 0), JRequest::getInt($periodic . "booking_begin_minute", 0));
	$end_time = sprintf("%02d:%02d", JRequest::getInt($periodic . "booking_end_hour", 0), JRequest::getInt($periodic . "booking_end_minute", 0));
	$begin = date_human_to_sql($begin_date) . " $begin_time";
	$end = date_human_to_sql($end_date) . " $end_time";
	# $periodicity = 

	$group_id = _has_ghost_group() ? _has_ghost_group() : 1;
?>
<h2><?php echo $this->name . ": " . JText::_("Booking payment") ?></h1>
<?php numbullet("Summary of your booking") ?>
<?php 
	global $ghost_group;
	global $booking_user;
	$profile = array(
		'begin_date' => $begin_date,
		'end_date' => $end_date,
		'begin_time' => $begin_time,
		'end_time' => $end_time,
		'user_name' => $booking_user['name'],
		'user_ssn' => $booking_user['social_security_number'],
		'group_name' => $ghost_group['name'],
		'user_address' => $booking_user['address'],
		'resource_name' => $this->model->tables['main']->name,
		'resource_address' => $this->model->tables['main']->address,
		'exceptions' => explode(",", JRequest::getString("exceptions","")),
	);
	$periodicity_mask = 0;
	if (strlen($periodic)) {
		$periodicity = array();
		foreach(array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') as $day) {
			if (JRequest::getInt(strtolower($day), 0)) {
				$periodicity[] = JText::_($day) . " ";
			}
		}
		$profile['periodicity'] = implode(" - ", $periodicity);

		foreach(array('Sunday', 'Saturday', 'Friday', 'Thursday', 'Wednesday', 'Tuesday', 'Monday') as $day) {
			$periodicity_mask = $periodicity_mask << 1;
			if (JRequest::getInt(strtolower($day), 0)) {
				$periodicity_mask += 1;
			}
		}
	} 

	// $cost_for_current_group = (float) $this->costfunction->getCostForCurrentGroup();
	$cost_for_current_group = (float) $this->costfunction->getCostForBookingProfile($begin, $end, $periodicity_mask, $this->id, $group_id);

	if (strlen($periodic)) {
		$profile['cost'] = JText::_("Periodic booking with special payment accounting");
	} else {
		$profile['cost'] = sprintf("%.2f", $cost_for_current_group);
	}
	format_booking($profile);
?>
<br><br>
<?php numbullet("Read resource regulation") ?>
<a href="images/stories/34reg.pdf"><?php echo JText::_("By booking this resource you agree to comply with the rules detailed here") ?></a>
<?php
	if (_has_ghost_group()) {
		numbullet("Retract advance");
		echo "<b>";
		JText::printf("You have %d days to retract this booking", pref("groupRetractTime"));
		echo "</b>";
	}
?>
<?php numbullet("Confirm") ?>
<?php if ($periodic) { ?>
<b><?php echo JText::_("Press confirm booking button to activate your booking") ?></b>
<?php } else { ?>
<b><?php echo JText::_("Do you want to confirm this booking?") ?></b>
<?php } ?>

<form method="POST" name="pay-booking" id="pay-booking">
<input type="hidden" name="id" value="<?php echo JRequest::getInt('id',0) ?>"/>
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="resource"/>
<input type="hidden" name="layout" id="layout" value=""/>
<input type="hidden" name="task" id="task" value="save_booking"/>
<?php if (JRequest::getInt('periodic', 0)) { ?>
<input type="hidden" name="periodic_booking_begin_date" value="<?php echo htmlspecialchars(JRequest::getString('periodic_booking_begin_date','00-00-0000')) ?>"/>
<input type="hidden" name="periodic_booking_end_date" value="<?php echo htmlspecialchars(JRequest::getString('periodic_booking_end_date','00-00-0000')) ?>"/>
<input type="hidden" name="periodic_booking_begin_hour" value="<?php echo htmlspecialchars(JRequest::getInt('periodic_booking_begin_hour',0)) ?>"/>
<input type="hidden" name="periodic_booking_end_hour" value="<?php echo htmlspecialchars(JRequest::getInt('periodic_booking_end_hour',0)) ?>"/>
<input type="hidden" name="periodic_booking_begin_minute" value="<?php echo htmlspecialchars(JRequest::getInt('periodic_booking_begin_minute',0)) ?>"/>
<input type="hidden" name="periodic_booking_end_minute" value="<?php echo htmlspecialchars(JRequest::getInt('periodic_booking_end_minute',0)) ?>"/>
<input type="hidden" name="periodic" value="1"/>
<input type="hidden" name="monday" value="<?php echo JRequest::getInt('monday', 0) ?>"/>
<input type="hidden" name="tuesday" value="<?php echo JRequest::getInt('tuesday', 0) ?>"/>
<input type="hidden" name="wednesday" value="<?php echo JRequest::getInt('wednesday', 0) ?>"/>
<input type="hidden" name="thursday" value="<?php echo JRequest::getInt('thursday', 0) ?>"/>
<input type="hidden" name="friday" value="<?php echo JRequest::getInt('friday', 0) ?>"/>
<input type="hidden" name="saturday" value="<?php echo JRequest::getInt('saturday', 0) ?>"/>
<input type="hidden" name="sunday" value="<?php echo JRequest::getInt('sunday', 0) ?>"/>
<input type="hidden" name="exceptions" value="<?php echo htmlspecialchars(JRequest::getString('exceptions', '')) ?>"/>
<?php } else { ?>
<input type="hidden" name="booking_begin_date" value="<?php echo htmlspecialchars(JRequest::getString('booking_begin_date','00-00-0000')) ?>"/>
<input type="hidden" name="booking_end_date" value="<?php echo htmlspecialchars(JRequest::getString('booking_end_date','00-00-0000')) ?>"/>
<input type="hidden" name="booking_begin_hour" value="<?php echo htmlspecialchars(JRequest::getInt('booking_begin_hour',0)) ?>"/>
<input type="hidden" name="booking_end_hour" value="<?php echo htmlspecialchars(JRequest::getInt('booking_end_hour',0)) ?>"/>
<input type="hidden" name="booking_begin_minute" value="<?php echo htmlspecialchars(JRequest::getInt('booking_begin_minute',0)) ?>"/>
<input type="hidden" name="booking_end_minute" value="<?php echo htmlspecialchars(JRequest::getInt('booking_end_minute',0)) ?>"/>
<?php } ?>
<input type="hidden" name="cost" value="<?php printf("%.2f", $cost_for_current_group) ?>"/>
<input type="hidden" name="method" id="method" value=""/>
</form>

<br><br>
<div class="button-footer">
<button class="button" onClick="payLater()"><?php echo JText::_("Confirm booking") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="changeBooking()"><?php echo JText::_("Change booking") ?></button>
</div>
