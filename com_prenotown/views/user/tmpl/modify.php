<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<style>
input[type="text"], input[type="password"], textarea { width: 500px }
</style>
<script language="Javascript" type="text/javascript">
	function sendForm() {
		document.getElementById("profile-form").submit();
	}
</script>
<?php
	global $province;
	sort($province, SORT_STRING);
?>
<style>
	td input {
		font-family: monospace;
		font-weight: bold;
	}
	input[type="text"], input[type="password"] {
		width: 500px;
	}
</style>
<h2><?php echo JText::_("Edit user profile") ?></h1>
<form id="profile-form" name="profile-form" method="POST">
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="layout" value="modify"/>
<input type="hidden" name="task" value="update_user_profile"/>
<table class="hl">
	<tr><td class="left"><?php echo JText::_("Username") ?></td><td style="font-family: monospace; font-weight: bold"><?php echo $this->userdata['username'] ?></td></tr>
	<tr><td class="left"><?php echo JText::_("Password") ?></td><td><input name="password" type="password"></td></tr>
	<tr><td class="left"><?php echo JText::_("Confirm password") ?></td><td><input name="password2" type="password"></td></tr>
	<tr><td class="left"><?php echo JText::_("Last and first name") ?></td><td><input type="text" name="name" value="<?php echo htmlspecialchars($this->userdata['name']) ?>"></td></tr>
	<tr><td class="left"><?php echo JText::_("Social Security Number") ?></td><td><input type="text" name="social_security_number" value="<?php echo htmlspecialchars($this->userdata['social_security_number']) ?>"></td></tr>
	<tr><td class="left"><?php echo JText::_("Address") ?></td><td>
	<input name="address" title="<?php echo JText::_("User address") ?>" style="width:500px" value="<?php echo htmlspecialchars($this->userdata['address']) ?>"><br/>
	<input name="ZIP" title="CAP" style="width: 100px" value="<?php echo htmlspecialchars($this->userdata['ZIP']) ?>">
	<input name="town" title="citt&agrave;" style="width: 300px" value="<?php echo htmlspecialchars($this->userdata['town']) ?>">
	<select name="district">
		<?php
			foreach ($province as $provincia) {
				if ($provincia == $this->userdata['district']) {
					echo "<option selected>$provincia</option>\n";
				} else {
					echo "<option>$provincia</option>\n";
				}
			}
		?>
	</select>
	</td></tr>
	<tr><td class="left"><?php echo JText::_("Nationality") ?></td><td><input type="text" name="nationality" value="<?php echo htmlspecialchars($this->userdata['nationality']) ?>"></td></tr>
	<tr><td class="left"><?php echo JText::_("Email") ?></td><td><input type="text" name="email" value="<?php echo htmlspecialchars($this->userdata['email']) ?>"></td></tr>
</table><br>
</form>
<div class="button-footer">
<button class="button" onClick="sendForm()"><?php echo JText::_("Update my profile") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user')"><?php echo JText::_("User panel") ?></button>
</div>
