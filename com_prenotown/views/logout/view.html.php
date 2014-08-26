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
 * Logout view
 *
 * @package Prenotown
 * @subpackage Views
 */
class PrenotownViewLogout extends JView
{
	function display($tmpl=null)
	{
		// get components parameters
		global $mainframe;
		$params =& $mainframe->getParams('com_prenotown');
		$this->assignRef('params', $params);
		
		$user = &JFactory::getUser();
		$acl = &JFactory::getACL();
		$gid = $user->get('gid');
		$gid = $gid?$gid:$acl->get_group_id(null, 'ROOT');
		
		// load a model
		$a_model =& JModel::getInstance('ModelName', 'PrenotownModel');
		$this->assignRef('a_model', $a_model);
		$application = &JFactory::getApplication();
		
		// get the id from the request
		$id = JRequest::getInt('id', 0);
		$this->assignRef('id', $id);
		
		_ghost_popup();
		parent::display($tmpl);
	}
}
?>
