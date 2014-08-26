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
 * User view
 *
 * @package Prenotown
 * @subpackage Views
 */
class PrenotownViewUser extends JView
{
	function display($tmpl=null)
	{
		// get components parameters
		global $mainframe, $prenotown_user;
		$params =& $mainframe->getParams('com_prenotown');
		
		$document =& JFactory::getDocument();
		$document->addStyleSheet( "components/com_prenotown/assets/css/prenotown.css" );

		$user = &JFactory::getUser();
		$acl = &JFactory::getACL();
		$db = &JFactory::getDBO();
		$this->assignRef('db', $db);
		$application = &JFactory::getApplication();

		if ($user) {
			$gid = $user->get('gid');
			$gid = $gid?$gid:$acl->get_group_id(null, 'ROOT');
		}
		
		// load a model
		$user_model =& JModel::getInstance('User', 'PrenotownModel');
		$this->assignRef('user_model', $user_model);

		$users_model =& JModel::getInstance('Users', 'PrenotownModel');
		$this->assignRef('users_model', $users_model);

		// Choose which layout is accessible to unauth users
		$layout = JRequest::getString('layout','default');
		if (!_status('user')) {
			switch ($layout) {
				case 'registration':
				case 'crsregistration':
					break;
				default:
					forceLogin("Please login before accessing this area");
					return;
			}
		} else {
			$this->user_model->setId($user->id);
			$userdata = $this->user_model->getUser();
			$this->assignRef('userdata', $userdata);
		}

		parent::display($tmpl);
	}
}
?>
