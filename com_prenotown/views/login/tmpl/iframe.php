<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	/* build the redirect url */
	$url = base64_decode(JRequest::getString('return',''));
	error_log("iframe.php loads: $url");
?>
<html><body>
<iframe style="border: 0px; width: 800px;" width="100%" height="500" frameborder="0" border="0" src="<?php echo $url ?>"></iframe>
</body></html>
