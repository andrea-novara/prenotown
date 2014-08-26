<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	$url = base64_encode("index.php?option=com_prenotown&view=user");
	$url = JURI::base() . "index.php?option=com_prenotown&view=login&layout=redirect&format=raw&return=$url";
	$url = urlencode($url);
?>
<h2><?php echo JText::_("Modifica del proprio profilo") ?></h1>
<iframe style="border: 0px; width: 800px;" width="100%" height="500" frameborder="0" border="0" src="<?php echo pref('profileUpdateUrl') . "?originator=prenotown&returnUrl=$url" ?>">
</iframe>
