<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	global $prenotown_user;

	$document =& JFactory::getDocument();
	$script = <<<EOF
	function check_form() {
		document.resourcesForm.submit();
	}

	function showMessage(id) {
		if (!id) { return; }
		document.getElementById("reason" + id).style['position'] = 'relative';
		document.getElementById("reason" + id).style['top'] = '0px';
	}

	function hideMessage(id) {
		if (!id) { return; }
		document.getElementById("reason" + id).style['position'] = 'absolute';
		document.getElementById("reason" + id).style['top'] = '-2000px';
	}
EOF;
	$document->addScriptDeclaration($script);
	$document->addStyleDeclaration("textarea { border: 1px solid black; }");
?>
<h2><?php echo JText::_("Pending booking") ?></h1>
<form name="resourcesForm" method="POST">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="resources"/>
<input type="hidden" name="layout" value="approvallist"/>
<input type="hidden" name="task" value="approve_resources"/>
<table class="hl">
	<thead>
		<th><?php echo JText::_("ID") ?></th>
		<th><?php echo JText::_("Booker") ?></th>
		<th><?php echo JText::_("Resource") ?></th>
		<th><?php echo JText::_("Range") ?></th>
		<th><?php echo JText::_("Periodicity") ?></th>
		<th><?php echo JText::_("Cost") ?></th>
		<!--
		<th><?php echo JText::_("Begin") ?></th>
		<th><?php echo JText::_("End") ?></th>
		-->
		<th style="width: 200px;"><?php echo JText::_("Approval") ?></th>
	</thead>
	<?php
		$booking = $this->bookings->getByAdmin($prenotown_user['id']);

		echo "<!--" ;
		print_r($booking);
		echo "-->";

		foreach ($booking as $b) {
			echo '<tr><td>' . $b['id'] . '<td title="' . $b['user_address'] . '">';
			if ($b['group_id'] > 100) {
				$this->db->setQuery("SELECT name FROM #__prenotown_user_groups WHERE id = " . $b['group_id']);
				$group_name = $this->db->loadResult();
				echo '<b>' . JText::_("Group") . ": $group_name</b><br/>";
			}
			echo $b['user_name'] . " - " . $b['social_security_number'] . '</td>';
			echo '<td title="' . $b['resource_address'] . '">' . $b['resource_name'] . '</td>';

			list($begin_date, $begin_time) = explode(" ", $b['begin']);
			list($end_date, $end_time) = explode(" ", $b['end']);

			$begin_date = date_sql_to_human($begin_date);
			$end_date = date_sql_to_human($end_date);

			$begin_time = preg_replace("/:..$/", "", $begin_time);
			$end_time = preg_replace("/:..$/", "", $end_time);

			echo '<td>';
			
			if ($b['periodic']) {
				echo date_sql_to_human($begin_date) . ' - ' . date_sql_to_human($end_date) . '<br/>';
				echo "$begin_time - $end_time";
			} else if (strcmp($begin_date, $end_date) == 0) {
				echo JText::_("On") . ": $begin_date<br/>" . JText::_("from-hour") . " $begin_time " . JText::_("up to-hour") . " $end_time";
			} else {
				echo JText::_("From") . ": $begin_date " . JText::_("at") . " $begin_time<br/>" . JText::_("up to") . ": $end_date " . JText::_("at") . " $end_time";
			}
			echo '</td>';
			echo '<td>';
			if ($b['periodic']) {
				echo implode(", ", expand_periodicity($b['periodicity']));
			} else {
				echo JText::_("Single");
			}
			echo '</td>';
			echo '<td>' . float_point_to_comma($b['cost']) . '&euro;</td>';
			echo '<td>' . JText::_("Yes") . '<input type="radio" name="approve[' . $b['id'] . ']" value="1" onClick="hideMessage(' . $b['id'] . ')">';
			echo JText::_("No") . '<input type="radio" name="approve[' . $b['id'] . ']" value="0" onClick="showMessage(' . $b['id'] . ')">';
			echo '<br><textarea title="' . JText::_("Deny reason") . '" id="reason' . $b['id'] . '" name="reason[' . $b['id'] . ']" style="position: absolute; top: -2000px"></textarea></td>';
			echo '</tr>';
		}

		if (!count($booking)) {
			echo '<tr><td colspan="7" style="text-align: center; font-weight: bold; padding: 10px;">' . JText::_("No pending booking on your resources") . '</td></tr>';
		}
	?>
</table><br>
</form>

<div class="button-footer">
<?php if (count($booking)) { ?>
<button class="button" onClick="check_form()"><?php echo JText::_("Submit") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<?php } ?>
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
