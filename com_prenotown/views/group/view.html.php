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
 * Group view
 *
 * @package Prenotown
 * @subpackage Views
 */
class PrenotownViewGroup extends JView
{
	function display($tmpl=null)
	{
		// get components parameters
		global $mainframe;
		$params =& $mainframe->getParams('com_prenotown');
		$this->assignRef('params', $params);

		$document =& JFactory::getDocument();
		$document->addStyleSheet( "components/com_prenotown/assets/css/prenotown.css" );
		$document->addStyleSheet( "components/com_prenotown/assets/css/booking.css" );
		
		$user = &JFactory::getUser();
		$acl = &JFactory::getACL();
		$gid = $user->get('gid');
		$gid = $gid?$gid:$acl->get_group_id(null, 'ROOT');
		$application = &JFactory::getApplication();
		
		// load a model
		$group_model =& JModel::getInstance('Group', 'PrenotownModel');
		$this->assignRef('group_model', $group_model);

		$groups_model =& JModel::getInstance('Groups', 'PrenotownModel');
		$this->assignRef('groups_model', $groups_model);

		$users_model =& JModel::getInstance('Users', 'PrenotownModel');
		$this->assignRef('users_model', $users_model);
		
		$resource_model =& JModel::getInstance('Resource', 'PrenotownModel');
		$this->assignRef('resource_model', $resource_model);
		
		// get the id from the request
		$id = JRequest::getInt('id', 0);
		$this->assignRef('id', $id);
		$this->group_model->setid($id);
		
		// Choose which layout is accessible to unauth users
		$layout = JRequest::getString('layout','default');
		if (!$user or !$user->id) {
			switch ($layout) {
				case 'default':
					break;
				default:
					forceLogin("Please login before accessing this area");
					return;
			}
		}
		
		_ghost_popup();
		parent::display($tmpl);
	}
}
?>
