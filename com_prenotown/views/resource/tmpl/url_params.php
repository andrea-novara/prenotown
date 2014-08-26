<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<input type="hidden" name="option" value="com_prenotown"/>
<input type="hidden" name="view" value="<?php echo JRequest::getString("view","resource") ?>"/>
<input type="hidden" name="layout" value="<?php echo JRequest::getString("layout","default") ?>"/>
<input type="hidden" name="id" value="<?php echo $this->id ?>"/>
