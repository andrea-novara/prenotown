<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<HTML><head>
<link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="templates/system/css/general.css" type="text/css" />
<link rel="stylesheet" href="templates/atos/css/atos.css" type="text/css" />
<link rel="stylesheet" href="media/system/css/modal.css" type="text/css" />
<link rel="stylesheet" href="components/com_prenotown/assets/css/prenotown.css" type="text/css" />
<link rel="stylesheet" href="components/com_prenotown/assets/css/booking.css" type="text/css" />
<style>
	tr.rule-starter td {
		border-top: 1px solid #000 !important;
	}
</style>
</head><body>
<h2><?php echo $this->model->tables['resource']->name ?>: Quadro tariffario</h1>
<table class="hl" cellpadding="0" cellspacing="0" style="width:100%;">
<thead>
	<th><?php echo JText::_("Fee name") ?></th>
	<th><!--<?php echo JText::_("Time range") ?>-->&nbsp;</th>
	<th><?php echo JText::_("Cost") ?></th>
</thead>
<?php
	$resource_id = $this->model->tables['resource']->id;
	$this->db->setQuery("SELECT DISTINCT *, HOUR(upper_limit) * 60 * 60 + MINUTE(upper_limit) * 60 + SECOND(upper_limit) AS upper_limit_second FROM #__prenotown_time_cost_function_fee_rules JOIN #__prenotown_time_cost_function_fee ON #__prenotown_time_cost_function_fee.id = #__prenotown_time_cost_function_fee_rules.fee_id WHERE #__prenotown_time_cost_function_fee.resource_id = $resource_id ORDER BY fee_id, upper_limit");
	$rules = $this->db->loadAssocList();

	$last_id = 0;
	$lower_limit = "00:00";
	$lower_limit_second = 0;
	foreach ($rules as $rule) {
		$upper_limit = preg_replace('/:\d\d$/', '', $rule['upper_limit']);
		$difference = ($rule['upper_limit_second'] - $lower_limit_second) / 3600;
		if ($last_id != $rule['fee_id']) {
			$lower_limit = "00:00";
			$lower_limit_second = 0;
			$difference = ($rule['upper_limit_second'] - $lower_limit_second) / 3600;
			$last_id = $rule['fee_id'];
			if ($rule['name'] == 'Default') {
				$rule['name'] = "Base";
			}
			echo "<tr class=\"rule-starter\"><td><b>" . $rule["name"] . "</b></td><td>&nbsp;</td><td>" . float_point_to_comma(sprintf("%.2f", ($rule['cost']/$difference))) . "&nbsp;&euro;/ora</td></tr>";
		} else {
			$previous_hour = $lower_limit_second / 3600;
			if ($previous_hour == 1) {
				$time_range = JText::sprintf("After %s hour", $previous_hour);
			} else {
				$time_range = JText::sprintf("After %s hour(s)", $previous_hour);
			}
			echo "<tr><td></td><td>$time_range</td><td>" . float_point_to_comma(sprintf("%.2f", ($rule['cost']/$difference))) . "&nbsp;&euro;/ora</td></tr>";
		}
		$lower_limit = $upper_limit;
		$lower_limit_second = $rule['upper_limit_second'];
	}
?>
</table>
<p style="margin-top: 20px">Per usufruire delle tariffe riservate a specifiche categorie è necessario essere iscritti al gruppo rivolgendosi agli uffici ATOS ed effettuare la prenotazione per conto del gruppo stesso. Per verificare i tuoi attuali gruppi attivi clicca su Gestione gruppi. Le tariffe agevolate sono utilizzabili solo se la maggioranza degli utenti rientrano nel gruppo.
<div class="button-footer">
<button class="button" onClick="window.close()"><?php echo JText::_("Close") ?></button>
</div>
</body></html>
