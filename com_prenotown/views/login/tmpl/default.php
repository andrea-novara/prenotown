<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<h2><?php echo JText::_("User login") ?></h1>
<?php
	global $prenotown_user;

	// get return URL from JRequest or craft one using BASE64 encoding
	$return = JRequest::getString('return', base64_encode("index.php?option=com_prenotown&view=user"));

	if (_status('user')) {
		$application =& JFactory::getApplication();
	}
?>

<div style="width: 300px; padding: 20px; margin-left: auto; margin-right: auto">
<form id="prenotown-form-login" name="prenotown-login" method="post" action="index.php?option=com_user">
<table>
	<tr><td><?php echo JText::_("Username") ?>:</td><td><input class="inputbox" id="username" type="text" name="username" alt="username"/></td></tr>
	<tr><td><?php echo JText::_("Password") ?>:</td><td><input class="inputbox" id="passwd" name="passwd" alt="password" type="password"/></td></tr>
	<tr><td></td><td><input type="checkbox" alt="Remember Me" value="yes" class="inputbox" name="remember" id="remember"/> <?php echo JText::_("Remember me") ?></td></tr>
	<tr><td></td><td><input class="button" type="submit" value="<?php echo JText::_("Login") ?>" name="Submit"/></td></tr>
</table>
<input type="hidden" value="com_user" name="option"/>
<input type="hidden" value="login" name="task"/>
<?php
?>
<input type="hidden" value="<?php echo $return ?>" name="return"/>
<?php echo JHTML::_( 'form.token' ); ?>
</div>
