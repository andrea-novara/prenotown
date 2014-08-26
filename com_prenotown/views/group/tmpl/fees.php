<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<h2><?php echo JText::_("Group fees") ?></h1>
<h3><?php echo JText::sprintf("Fees applied to group %s", $this->group_model->tables['usergroups']->name) ?></h3>
<script language="Javascript" type="text/javascript">
	function revokeFee(fee_id, group_id) {
		if (confirm('<?php echo JText::_("Do you really want to revoke this fee from this group?") ?>')) {
			redirect('index.php?option=com_prenotown&view=group&layout=fees&id=<?php echo $this->id ?>&task=revokeFee&fee_id=' + fee_id + '&group_id=' + group_id);
		}
		return false;
	}
</script>
<table class="hl">
<thead>
	<th><?php echo JText::_("Resource") ?></th>
	<th><?php echo JText::_("Fee name") ?></th>
	<th><?php echo JText::_("Fee rules") ?></th>
	<th><?php echo JText::_("Actions") ?></th>
</thead>
<?php
	$fees = $this->group_model->getFees();
	foreach ($fees as $fee) {
		$rules = $this->group_model->getFeeRulesByFee($fee['id']);
		echo '<tr>';
		echo '<td>' . $fee['resource_name'] . '<br>' . $fee['resource_address'] . '</td>';
		echo '<td>' . $fee['name'] . '</td>';
		echo '<td>';
		$lower_limit = "00:00:00";
		$lower_limit_seconds = 0;
		foreach ($rules as $rule) {
			$cost = $rule['cost'] * 3600 / ($rule['upper_limit_seconds'] - $lower_limit_seconds);
			echo float_point_to_comma(JText::sprintf("%s - %s cost %.2f &euro;/hour<br>", $lower_limit, $rule['upper_limit'], $cost));
			$lower_limit = $rule['upper_limit'];
			$lower_limit_seconds = $rule['upper_limit_seconds'];
		}
		echo '</td>';
		echo '<td>';
		echo '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=resource&layout=costfunction&id=' . $fee['resource_id'] . '#fee-' . $fee['id'] . '\')">';
		echo JText::_("Edit") . '</button> ';
		echo '<button class="button" onClick="revokeFee(' . $fee['id'] . ', ' . $this->id . ')">' . JText::_("Revoke") . '</button>';
		echo '</td>';
		echo '</tr>';
	}
?>
</table>
<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=groups')"><?php echo JText::_("Manage groups") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
