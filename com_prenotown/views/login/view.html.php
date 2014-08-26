<?php
/**
 * @package Prenotown
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** ensure a valid entry point */
defined('_JEXEC') or die("Restricted Access");

/** import the JView class */
jimport("joomla.application.component.view");

/** import global facilities */
require_once(JPATH_COMPONENT.DS."assets".DS."logging.php");
require_once(JPATH_COMPONENT.DS."assets".DS."user_session.php");

/**
 * Login view
 *
 * @package Prenotown
 * @subpackage Views
 */
class PrenotownViewLogin extends JView
{
	function display($tmpl=null)
	{
		parent::display($tmpl);
	}
}
?>
