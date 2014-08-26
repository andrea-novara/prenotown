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
	error_log("modal.php loads: $url");
?>
<script type="text/javascript">
/* <![CDATA[ */
window.addEvent('domready', function() {
	SqueezeBox.initialize();
	SqueezeBox.setOptions({size: {x: 900, y: 700}}).setContent('adopt', $('adopt-modal-div'));
	var e = document.getElementById('adopt-modal-div');
	e.parentNode.removeChild(e);
});
/* ]]> */
</script>
<div id="adopt-modal-div" style="width: 100%; height: 100%">
<iframe style="border: 0px; width: 800px;" width="100%" height="100%" frameborder="0" border="0" src="<?php echo $url ?>"></iframe>
</div>
