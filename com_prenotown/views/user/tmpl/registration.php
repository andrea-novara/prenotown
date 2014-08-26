<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	$doc =& JFactory::getDocument();

	$translation["Please insert your first and last name"] = JText::_("Please insert your first and last name");
	$translation["Please insert your social security number"] = JText::_("Please insert your social security number");
	$translation["Please insert your address"] = JText::_("Please insert your address");
	$translation["Please insert your ZIP code"] = JText::_("Please insert your ZIP code");
	$translation["Please insert your town"] = JText::_("Please insert your town");
	$translation["Please insert your nationality"] = JText::_("Please insert your nationality");
	$translation["Please insert your email"] = JText::_("Please insert your email");
	$translation["Your address"] = JText::_("Your address");
	$translation["ZIP code"] = JText::_("ZIP code");
	$translation["Town"] = JText::_("Town");

	$STYLE = <<<EOF
.moowatermark { color: gray; font-style: italic; }
input[type="text"], input[type="password"], textarea { width: 500px }
td input { font-weight: normal; }
EOF;
	$doc->addStyleDeclaration($STYLE);

	$SCRIPT = <<<EOF
	function check_ssn() {
		ssn = document.getElementById('ssn');
		if (!ssn) {
			alert("Can't find SSN field");
			return;
		}

		var value = ssn.value;
		value = value.replace(/[^a-zA-Z0-9]/g, '');
		ssn.value = value;
	}

	var MooWatermark = new Class({ 
		initialize: function(options) {
			this.textField = options.textField;
			this.hint = (options.hint ? options.hint : '');
			this.watermarkClass = (options.watermarkClass ? options.watermarkClass : 'moowatermark');
			
			window.addEvent('load', function() {
				if ($(this.textField)){
					$(this.textField).addEvent('focus', this.clearTextFieldValue.bind(this));
					$(this.textField).addEvent('blur', this.setInitialValue.bind(this));
					this.setInitialValue();
					if(window.ie) {
						var form = document.getElementById(this.textField).form;
						if(form) {
							form.attachEvent("onsubmit", this.clearTextFieldValue.bind(this));
						}
					} else {
						if($(this.textField).getParent('form')){
							$($(this.textField).form).addEvent('submit', this.clearTextFieldValue.bind(this));
						}
					}
				}
			}.bind(this));
		},
		
		setInitialValue: function(){
			if ($(this.textField).value == ''){ 
				$(this.textField).value = this.hint;
				$(this.textField).addClass(this.watermarkClass);	
			}
		},
		
		clearTextFieldValue: function(){
			if ($(this.textField).value == this.hint) {
				$(this.textField).value = '';	
				$(this.textField).removeClass(this.watermarkClass);	
			}
		}
	
	});

	function sendRegistration() {
		if (!document.registrationForm.name.value) {
			alert("{$translation["Please insert your first and last name"]}");
			return;
		}

		if (!document.registrationForm.social_security_number.value) {
			alert("{$translation["Please insert your social security number"]}");
			return;
		}

		if (!document.registrationForm.address.value) {
			alert("{$translation["Please insert your address"]}");
			return;
		}

		if (!document.registrationForm.ZIP.value) {
			alert("{$translation["Please insert your ZIP code"]}");
			return;
		}

		if (!document.registrationForm.town.value) {
			alert("{$translation["Please insert your town"]}");
			return;
		}

		if (!document.registrationForm.nationality.value) {
			alert("{$translation["Please insert your nationality"]}");
			return;
		}

		if (!document.registrationForm.email.value) {
			alert("{$translation["Please insert your email"]}");
			return;
		}

		document.getElementById("registrationForm").submit();
	}
EOF;
	$doc->addScriptDeclaration($SCRIPT);

	global $province;
	sort($province, SORT_STRING);
?>
<h2><?php echo JText::_("New user registration form") ?></h1>
<form name="registrationForm" id="registrationForm" method="POST">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="layout" value="registration"/>
<input type="hidden" name="task" value="create_user_profile"/>
<table class="hl">
<!--
	<tr><td class="left"><?php echo JText::_("Username") ?></td><td><input type="text" name="username" value="<?php echo htmlspecialchars(JRequest::getString('username', '')) ?>"></td></tr>
-->
	<tr><td class="left"><?php echo JText::_("Last and first name") ?></td><td><input type="text" name="name" value="<?php echo htmlspecialchars(JRequest::getString('name', '')) ?>"></td></tr>
	<tr><td class="left"><?php echo JText::_("Social Security Number") ?></td><td><input type="text" name="social_security_number" id="ssn" onBlur="check_ssn()" value="<?php echo htmlspecialchars(JRequest::getString('social_security_number', '')) ?>"></td></tr>
	<tr><td class="left"><?php echo JText::_("Address") ?></td><td>
	<input name="address" id="address" title="<?php echo JText::_("User address") ?>" style="width:500px" value="<?php echo htmlspecialchars(JRequest::getString('address', '')) ?>"><br/>
	<input name="ZIP" id="ZIP" title="CAP" style="width: 100px" value="<?php echo htmlspecialchars(JRequest::getString('ZIP', '')) ?>">
	<input name="town" id="town" title="citt&agrave;" style="width: 300px" value="<?php echo htmlspecialchars(JRequest::getString('town', '')) ?>">
	<select name="district">
		<?php
			$district = JRequest::getString('district', '');
			foreach ($province as $provincia) {
				if ($provincia == $district) {
					echo "<option selected>$provincia</option>\n";
				} else {
					echo "<option>$provincia</option>\n";
				}
			}
		?>
	</select>
	</td></tr>
	<tr><td class="left"><?php echo JText::_("Nationality") ?></td><td><input type="text" name="nationality" value="<?php echo htmlspecialchars(JRequest::getString('nationality', '')) ?>"></td></tr>
	<tr><td class="left"><?php echo JText::_("Email") ?></td><td><input type="text" name="email" value="<?php echo htmlspecialchars(JRequest::getString('email', '')) ?>"></td></tr>
	<tr><td class="left"><?php echo JText::_("Password") ?></td><td><input name="password" type="password"></td></tr>
	<tr><td class="left"><?php echo JText::_("Confirm password") ?></td><td><input name="password2" type="password"></td></tr>
</table><br>
</form>
<script type="text/javascript">
	new MooWatermark({ textField: 'address', hint: '<?php echo $translation['Your address'] ?>' });
	new MooWatermark({ textField: 'ZIP', hint: '<?php echo $translation['ZIP code'] ?>' });
	new MooWatermark({ textField: 'town', hint: '<?php echo $translation['Town'] ?>' });
</script>

<div class="button-footer">
<button class="button" onClick="sendRegistration()"><?php echo JText::_("Register me") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
