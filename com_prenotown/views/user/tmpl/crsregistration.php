<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<html><body>
<h2><?php echo JText::_("CRS Registration") ?></h1>
<iframe style="border: 0px; width: 800px;" width="100%" height="500" frameborder="0" border="0" src="<?php echo pref('registrationUrl') ?>"></iframe>
</body></html>
