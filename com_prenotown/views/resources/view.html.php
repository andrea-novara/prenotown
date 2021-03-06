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
 * Resources view
 *
 * @package Prenotown
 * @subpackage Views
 */
class PrenotownViewResources extends JView
{
	function display($tmpl=null)
	{
		$document =& JFactory::getDocument();
		$document->addStyleSheet( "components/com_prenotown/assets/css/prenotown.css" );
		$document->addStyleSheet( "components/com_prenotown/assets/css/booking.css" );

		// get components parameters
		global $mainframe;
		$params =& $mainframe->getParams('com_prenotown');
		$this->assignRef('params', $params);
		
		$user = &JFactory::getUser();
		$this->assignRef('user', $user);

		$acl = &JFactory::getACL();
		$gid = $user->get('gid');
		$gid = $gid?$gid:$acl->get_group_id(null, 'ROOT');
		$application = &JFactory::getApplication();
		
		// load a model
		$model =& JModel::getInstance('Resources', 'PrenotownModel');
		$this->assignRef('model', $model);

		$attachment =& JModel::getInstance('ResourceAttachment', 'PrenotownModel');
		$this->assignRef('attachment', $attachment);

		$attachments =& JModel::getInstance('ResourceAttachments', 'PrenotownModel');
		$this->assignRef('attachments', $attachments);

		$bookings =& JModel::getInstance('Superbookings', 'PrenotownModel');
		$this->assignRef('bookings', $bookings);

		$resourceGroups =& JModel::getInstance('ResourceGroups', 'PrenotownModel');
		$this->assignRef('resourceGroups', $resourceGroups);
		
		// get the id from the request
		$id = JRequest::getInt('id', 0);
		$this->assignRef('id', $id);

		$db =& JFactory::getDBO();
		$this->assignRef('db', $db);

		// Choose which layout is accessible to unauth users
		$layout = JRequest::getString('layout','default');
		if (!$user or !$user->id) {
			switch ($layout) {
				case 'default':
				case 'tree':
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
