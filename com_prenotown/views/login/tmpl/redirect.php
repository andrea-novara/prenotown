<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	$return = base64_decode(JRequest::getString('return', base64_encode(JURI::base())));

	if ($token = JRequest::getString('token', '')) {
		$return = preg_replace('/[?&]?token=[^&]+/', '', $return);
		if (preg_match('/\?/', $return)) {
			$return .= "&token=$token";
		} else {
			$return .= "?token=$token";
		}
	}

	$return .= "&" . JUtility::getToken() . "=1";

	error_log("redirect.php redirects to: $return");
?>
<html>
<head>
<script type="text/javascript" src="<?php echo JURI::base() ?>/components/com_prenotown/assets/prenotown.js"></script>
<script type="text/javascript" src="<?php echo JURI::base() ?>/media/system/js/mootools.js"></script>
<script type="text/javascript" src="<?php echo JURI::base() ?>/media/system/js/modal.js"></script>
</head>
<body>
<script type="text/javascript">
/* <![CDATA[ */
window.addEvent('domready', function() {
	window.parent.location = '<?php echo $return ?>';
});
/* ]]> */
</script>
</body></html>
