<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */

	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	/** include joomla controller */
	require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'controller.php');

	$controller = new PrenotownController();
	$controller->execute(JRequest::getCmd('task', 'display'));
	$controller->redirect();
?>
