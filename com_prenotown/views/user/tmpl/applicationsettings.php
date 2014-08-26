<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	$currency = array(
		'AED' => 784, 'AFN' => 971, 'ALL' => 008, 'AMD' => 051, 'ANG' => 532, 'AOA' => 973, 'ARS' => 032, 'AUD' => 036,
		'AWG' => 533, 'AZN' => 944, 'BAM' => 977, 'BBD' => 052, 'BDT' => 050, 'BGN' => 975, 'BHD' => 048, 'BIF' => 108,
		'BMD' => 060, 'BND' => 096, 'BOB' => 068, 'BOV' => 984, 'BRL' => 986, 'BSD' => 044, 'BTN' => 064, 'BWP' => 072,
		'BYR' => 974, 'BZD' => 084, 'CAD' => 124, 'CDF' => 976, 'CHE' => 947, 'CHF' => 756, 'CHW' => 948, 'CLF' => 990,
		'CLP' => 152, 'CNY' => 156, 'COP' => 170, 'COU' => 970, 'CRC' => 188, 'CUP' => 192, 'CVE' => 132, 'CZK' => 203,
		'DJF' => 262, 'DKK' => 208, 'DOP' => 214, 'DZD' => 012, 'EEK' => 233, 'EGP' => 818, 'ERN' => 232, 'ETB' => 230,
		'EUR' => 978, 'FJD' => 242, 'FKP' => 238, 'GBP' => 826, 'GEL' => 981, 'GHC' => 288, 'GIP' => 292, 'GMD' => 270,
		'GNF' => 324, 'GTQ' => 320, 'GYD' => 328, 'HKD' => 344, 'HNL' => 340, 'HRK' => 191, 'HTG' => 332, 'HUF' => 348,
		'IDR' => 360, 'ILS' => 376, 'INR' => 356, 'IQD' => 368, 'IRR' => 364, 'ISK' => 352, 'JMD' => 388, 'JOD' => 400,
		'JPY' => 392, 'KES' => 404, 'KGS' => 417, 'KHR' => 116, 'KMF' => 174, 'KPW' => 408, 'KRW' => 410, 'KWD' => 414,
		'KYD' => 136, 'KZT' => 398, 'LAK' => 418, 'LBP' => 422, 'LKR' => 144, 'LRD' => 430, 'LSL' => 426, 'LTL' => 440,
		'LVL' => 428, 'LYD' => 434, 'MAD' => 504, 'MDL' => 498, 'MGA' => 969, 'MKD' => 807, 'MMK' => 104, 'MNT' => 496,
		'MOP' => 446, 'MRO' => 478, 'MUR' => 480, 'MVR' => 462, 'MWK' => 454, 'MXN' => 484, 'MXV' => 979, 'MYR' => 458,
		'MZN' => 943, 'NAD' => 516, 'NGN' => 566, 'NIO' => 558, 'NOK' => 578, 'NPR' => 524, 'NZD' => 554, 'OMR' => 512,
		'PAB' => 590, 'PEN' => 604, 'PGK' => 598, 'PHP' => 608, 'PKR' => 586, 'PLN' => 985, 'PYG' => 600, 'QAR' => 634,
		'ROL' => 642, 'RON' => 946, 'RSD' => 941, 'RUB' => 643, 'RWF' => 646, 'SAR' => 682, 'SBD' => 090, 'SCR' => 690,
		'SDG' => 938, 'SEK' => 752, 'SGD' => 702, 'SHP' => 654, 'SLL' => 694, 'SOS' => 706, 'SRD' => 968, 'STD' => 678,
		'SYP' => 760, 'SZL' => 748, 'THB' => 764, 'TJS' => 972, 'TMM' => 795, 'TND' => 788, 'TOP' => 776, 'TRY' => 949,
		'TTD' => 780, 'TWD' => 901, 'TZS' => 834, 'UAH' => 980, 'UGX' => 800, 'USD' => 840, 'USN' => 997, 'USS' => 998,
		'UYU' => 858, 'UZS' => 860, 'VEB' => 862, 'VND' => 704, 'VUV' => 548, 'WST' => 882, 'XAF' => 950, 'XAG' => 961,
		'XAU' => 959, 'XBA' => 955, 'XBB' => 956, 'XBC' => 957, 'XBD' => 958, 'XCD' => 951, 'XDR' => 960, 'XOF' => 952,
		'XPD' => 964, 'XPF' => 953, 'XPT' => 962, 'XTS' => 963, 'XXX' => 999, 'YER' => 886, 'ZAR' => 710, 'ZMK' => 894
	);
?>
<style>
	input { width: 37em; font-family: monospace; margin-bottom: 6px; }
</style>
<script language="Javascript" type="text/javascript">
	function submit_form() {
		document.getElementById('settings-form').submit();
	}
</script>
<h2><?php echo JText::_("Application settings:") ?></h1>

<form name="settings-form" id="settings-form" method="POST">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="layout" value="applicationsettings"/>
<input type="hidden" name="task" value="update_preferences"/>

<table class="hl">
	<tr><td colspan=2><?php numbullet("Internal settings") ?></td></tr>
	<tr>
		<td class="left"><?php JText::printf("Group retract advance:") ?></td>
		<td><input type="text" name="groupRetractTime" value="<?php echo htmlspecialchars(pref('groupRetractTime')) ?>"> <?php JText::printf("days") ?></td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("Application level debugging:") ?></td>
		<td><select name="debug"><?php if (pref('debug')) { ?>
			<option value="0"><?php echo JText::_("Disabled") ?></option>
			<option value="1" selected><?php echo JText::_("Enabled") ?></option>
		<? } else { ?>
			<option value="0" selected><?php echo JText::_("Disabled") ?></option>
			<option value="1"><?php echo JText::_("Enabled") ?></option>
		<? } ?>
		</select></td>
	</tr>
	<tr><td colspan=2><?php numbullet("Xign On - external authenticator") ?></td></tr>
	<tr>
		<td class="left"><?php JText::printf("Login behavior:") ?></td>
		<td><select name="loginBehavior">
		<?php foreach (array("disabled", "direct", "iframe", "modal") as $behavior) {
			echo "<option value=\"$behavior\" ";
			if (pref("loginBehavior") == $behavior) {
				echo "selected";
			}
			echo "/>" . JText::_($behavior) . "</option>\n";
		} ?>
		</select></td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("Registration URL") ?>:</td>
		<td><input name="registrationUrl" id="registrationUrl" value="<?php echo htmlentities(pref("registrationUrl")) ?>"/></td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("Profile update URL") ?>:</td>
		<td><input name="profileUpdateUrl" id="profileUpdateUrl" value="<?php echo htmlentities(pref("profileUpdateUrl")) ?>"/></td>
	</tr>
	<tr><td colspan=2><?php numbullet("BankPass options") ?></td></tr>
	<tr>
		<td class="left"><?php JText::printf("BPW_IDNEGOZIO") ?>:</td>
		<td><input name="bpw_idnegozio" id="bpw_idnegozio" value="<?php echo htmlspecialchars(pref("bpw_idnegozio")) ?>"/></td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("BPW_VALUTA") ?>:</td>
		<td>
			<select name="bpw_valuta" id="bpw_valuta">
				<?php foreach ($currency as $name => $number) {
					echo "<option value=\"$number\" ";
					if ($number == pref('bpw_valuta')) {
						echo "selected";
					}
					echo ">$name</option>";
				} ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("BPW_URL_PAGAMENTO") ?>:</td>
		<td><input name="bpw_url_pagamento" id="bpw_url_pagamento" value="<?php echo htmlentities(pref("bpw_url_pagamento")) ?>"/></td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("BPW_TCONTAB") ?>:</td>
		<td>
			<input name="bpw_tcontab" type="radio" value="D" <?php echo pref("bpw_tcontab") == "D" ? "checked" : "" ?>/> <?php echo JText::_("Differita") ?>
			<input name="bpw_tcontab" type="radio" value="I" <?php echo pref("bpw_tcontab") == "I" ? "checked" : "" ?>/> <?php echo JText::_("Immediata") ?>
		</td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("BPW_TAUTOR") ?>:</td>
		<td>
			<input name="bpw_tautor" type="radio" value="D" <?php echo pref("bpw_tautor") == "D" ? "checked" : "" ?>/> <?php echo JText::_("Differita") ?>
			<input name="bpw_tautor" type="radio" value="I" <?php echo pref("bpw_tautor") == "I" ? "checked" : "" ?>/> <?php echo JText::_("Immediata") ?>
		</td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("BPW_EMAILESERC") ?>:</td>
		<td><input name="bpw_emaileserc" id="bpw_emaileserc" value="<?php echo htmlspecialchars(pref("bpw_emaileserc")) ?>"/></td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("BPW_START_KEY") ?>:</td>
		<td><input name="bpw_start_key" id="bpw_start_key" value="<?php echo htmlspecialchars(pref("bpw_start_key")) ?>"/></td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("BPW_STATUS_API_KEY") ?>:</td>
		<td><input name="bpw_status_api_key" id="bpw_status_api_key" value="<?php echo htmlspecialchars(pref("bpw_status_api_key")) ?>"/></td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("BPW_NOME_NEGOZIO") ?>:</td>
		<td><input name="bpw_nome_negozio" id="bpw_nome_negozio" value="<?php echo htmlspecialchars(pref("bpw_nome_negozio")) ?>"/></td>
	</tr>
	<tr>
		<td class="left"><?php JText::printf("BPW_OPTIONS") ?>:</td>
		<td>
			<input type="checkbox" name="bpw_options_a" id="bpw_options_a" value="1" <?php echo pref("bpw_options_a") ? "checked" : "" ?>/>
			<?php echo JText::_("BPW_OPTIONS_A") ?><br/>
			<input type="checkbox" name="bpw_options_b" id="bpw_options_b" value="1" <?php echo pref("bpw_options_b") ? "checked" : "" ?>/>
			<?php echo JText::_("BPW_OPTIONS_B") ?><br/>
			<input type="checkbox" name="bpw_options_c" id="bpw_options_c" value="1" <?php echo pref("bpw_options_c") ? "checked" : "" ?>/>
			<?php echo JText::_("BPW_OPTIONS_C") ?><br/>
			<input type="checkbox" name="bpw_options_d" id="bpw_options_d" value="1" <?php echo pref("bpw_options_d") ? "checked" : "" ?>/>
			<?php echo JText::_("BPW_OPTIONS_D") ?><br/>
			<input type="checkbox" name="bpw_options_e" id="bpw_options_e" value="1" <?php echo pref("bpw_options_e") ? "checked" : "" ?>/>
			<?php echo JText::_("BPW_OPTIONS_E") ?><br/>
			<input type="checkbox" name="bpw_options_i" id="bpw_options_i" value="1" <?php echo pref("bpw_options_i") ? "checked" : "" ?>/>
			<?php echo JText::_("BPW_OPTIONS_I") ?><br/>
		</td>
	</tr>
	<tr><td colspan=2><?php numbullet("Email templates") ?></td></tr>
	<?php
		global $prenotown_pref;
		foreach ($prenotown_pref as $action) {
			if (preg_match("/^email_template_/", $action)) {
				if (preg_match("/subject$/", $action)) continue;
				echo "<tr><td class=\"left\">" . JText::_($action) . "<br/><br/>";

				$keys_found = array();
				if (preg_match_all('/(%[^%]+%)/', pref($action), $keys)) {
					foreach (array_unique($keys[0]) as $key) {
						$keys_found[] = $key;
					}
				}
				if (preg_match_all('/(%[^%]+%)/', pref($action . "_subject"), $keys)) {
					foreach (array_unique($keys[0]) as $key) {
						$keys_found[] = $key;
					}
				}

				if (count($keys_found)) {
					sort($keys_found);
					echo JText::_("Keys used") . ":<br/>";
					foreach (array_unique($keys_found) as $key) {
						echo "$key<br/>";
					}
				} else {
					echo JText::_("No keys found");
				}

				echo "</td><td>";
				echo JText::_("Subject") . ": <input name=\"" . $action . "_subject\" value=\"" . pref($action . "_subject") . "\"><br/>";
				echo "<textarea style=\"width: 95%; height: 150px\" name=\"$action\">" . pref($action) . "</textarea>";
				echo "</td></tr>";
			}
		}
	?>
</table>
</form>
<br>
<div class="button-footer">
<button class="button" onClick="submit_form()"><?php JText::printf('Save') ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>

