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
 * Resource view
 *
 * @package Prenotown
 * @subpackage Views
 */
class PrenotownViewResource extends JView
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
		$acl = &JFactory::getACL();
		$gid = $user->get('gid');
		$gid = $gid?$gid:$acl->get_group_id(null, 'ROOT');
		$application = &JFactory::getApplication();

		// Only default view can be accessed by unauth users
		$layout = JRequest::getString('layout','default');
		if (!$user or !$user->id) {
			switch ($layout) {
				case 'default':
				case 'fees':
					break;
				default:
					forceLogin("Please login before accessing this area");
					return;
			}
		}

		$db =& JFactory::getDBO();
		$this->assignRef('db', $db);

		// load a model
		$model =& JModel::getInstance('Resource', 'PrenotownModel');
		$this->assignRef('model', $model);

		$costfunctions =& JModel::getInstance('CostFunctions', 'PrenotownModel');
		$this->assignRef('costfunctions', $costfunctions);

		$attachments =& JModel::getInstance('ResourceAttachments', 'PrenotownModel');
		$this->assignRef('attachments', $attachments);
		
		// get the id from the request
		$id = JRequest::getInt('id', 0);

		if (isset($id) && $id > 0) {
			$this->assignRef('id', $id);
			$this->model->setId($id);
			$this->model->load();
			$cfid = $this->model->tables['main']->cost_function_id;

			// get costfunction class
			$this->costfunctions->reset();
			$this->costfunctions->addFilter("id = $cfid");
			$cfs = $this->costfunctions->getData();
			$cfclass= $cfs[0]['class'];

			$costfunction = new $cfclass((int) $this->id);
			if (isset($costfunction) and $costfunction) {
				$this->assignRef('costfunction', $costfunction);
			} else {
				_warn("WARN", JText::_("Can't create costfunction") . " $cfclass");
			}
		} else {
			if ($layout != 'create') {
				_warn("WARN", JText::_("No resource_id provided to resource view"));
				return;
			}
		}
		
		_ghost_popup();
		parent::display($tmpl);
	}
}
?>
