<?php
/**
 * Frontend controller
 *
 * @package Prenotown
 * @subpackage Controllers
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** check that this file is not called outside joomla framework */
defined('_JEXEC') or die("Restricted Access");

/** declare we are in the controller context */
define('_PRENOTOWN_CONTROLLER', TRUE);

/** import controller base class */
jimport("joomla.application.component.controller");

/** import all installed cost functions */
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . "cost_functions" . DS . "baseCostFunction.php");

/** import logging facilities */
require_once(JPATH_COMPONENT_SITE . DS . "assets" . DS . "logging.php");

/**
 * Frontend controller
 *
 * @package Prenotown
 * @subpackage Controllers
 */
class PrenotownController extends JController
{
	/** resource model */
	private $_resource_model = null;

	/** groups model */
	private $_groups_model = null;

	/** group model */
	private $_group_model = null;

	/** resource groups model */
	private $_resource_groups_model = null;

	/** resource group model */
	private $_resource_group_model = null;

	/** user model */
	private $_user_model = null;

	/** superbooking model */
	private $_superbooking_model = null;

	/** superbooking exception model */
	private $_superbooking_exception_model = null;

	/** superbooking exceptions model */
	private $_superbooking_exceptions_model = null;

	/** joomla token, just to ease the coding a bit */
	private $_jtoken = null;

	function __construct()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		parent::__construct();

		$path = JPATH_COMPONENT_ADMINISTRATOR.DS.'models';
		$this->addModelPath($path);

		$this->_resource_model =& JModel::getInstance('Resource', 'PrenotownModel');
		$this->_groups_model =& JModel::getInstance('Groups', 'PrenotownModel');
		$this->_group_model =& JModel::getInstance('Group', 'PrenotownModel');
		$this->_resource_groups_model =& JModel::getInstance('ResourceGroups', 'PrenotownModel');
		$this->_resource_group_model =& JModel::getInstance('ResourceGroup', 'PrenotownModel');
		$this->_user_model =& JModel::getInstance('User', 'PrenotownModel');
		$this->_superbooking_model =& JModel::getInstance('Superbooking', 'PrenotownModel');
		$this->_superbooking_exception_model =& JModel::getInstance('SuperbookingException', 'PrenotownModel');
		$this->_superbooking_exceptions_model =& JModel::getInstance('SuperbookingExceptions', 'PrenotownModel');

		$this->var = Array();
		$this->var['id'] = JRequest::getInt('id', 0);
		$this->var['option'] = JRequest::getString('option', 'com_prenotown');
		$this->var['view'] = JRequest::getString('view', '');
		$this->var['layout'] = JRequest::getString('layout', 'default');

		$this->user =& JFactory::getUser();

		$this->db =& JFactory::getDBO();

		$this->_jtoken = JUtility::getToken();

		/* create image cache */
		global $cache_path;
		if (!is_dir($cache_path)) {
			mkdir($cache_path);
		}

		/* drop old cached images */
		$today = date("Y-m-d");
		if ($cache = opendir($cache_path)) {
			while ($image = readdir($cache)) {
				if ($image != ".." && $image != ".") {
					$stats = lstat("$cache_path/$image");
					if (date("Y-m-d", $stats['mtime']) != $today) {
						unlink("$cache_path/$image");
					}
				}
			}
			closedir($cache);
		}

		// execute periodic checks
		$lastrun = pref('lastrun');
		if ($lastrun < strtotime(date("Y-m-d G:i:s") . " - 1 hour")) {
			_log("INFO", "Executing periodic checks on date " . date("Y-m-d G:i:s"));
			/* remove booking crossing limits */
			$this->db->setQuery("SELECT id, paying_period, approval_period FROM #__prenotown_resource");
			$resources = $this->db->loadAssocList();

			foreach ($resources as $r) {
				$approve_date = strtotime(date("Y-m-d") . " - " . $r['approval_period'] . " day");
				$payment_date = strtotime(date("Y-m-d") . " - " . $r['paying_period'] . " day");

				# select all the bookings not paid
				$sql = sprintf("SELECT #__prenotown_superbooking.id FROM #__prenotown_superbooking JOIN #__prenotown_payments ON #__prenotown_superbooking.payment_id = #__prenotown_payments.id WHERE resource_id = %d AND date <> '0000-00-00 00:00:00' AND UNIX_TIMESTAMP(date) < %d AND NOT periodic", $r['id'], $payment_date);
				_log_sql($sql);
				$this->db->setQuery($sql);
				$bids = $this->db->loadResultArray();

				if (count($bids)) {
					$sql = sprintf("DELETE FROM #__prenotown_superbooking WHERE id IN (%s)", implode(",", $bids));
					_log_sql($sql);
					$this->db->setQuery($sql);
					// $this->db->query();
				}

				# approve bookings after approval time
				$sql = sprintf("UPDATE #__prenotown_superbooking SET approved = 1 WHERE resource_id = %d AND NOT approved AND UNIX_TIMESTAMP(created) < %d", $r['id'], $approve_date);
				_log_sql($sql);
				$this->db->setQuery($sql);
				$this->db->query();
			}

			pref('lastrun', strtotime(date("Y-m-d G:i:s")));
		}

		$this->db->setQuery("DELETE FROM #__prenotown_superbooking_exception WHERE exception_date = \"0000-00-00\"");
		$this->db->query();
	}
	
	function error($msg="", $explain="", $debug_explain="")
	{
		if (!$debug_explain) { $debug_explain = $explain; }
		$text = JText::_($msg) . " " . (pref('debug') ? $debug_explain : JText::_($explain));
		_warn("WARN", $text);
	}

	function display()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		parent::display();
	}

	/**
	 * Send an email
	 *
	 * @param string $subject mail subject
	 * @param string $to recipient address
	 * @param string $body message content
	 * @return boolean
	 */
	function send_email($subject, $to, $body)
	{
		if (!$subject || !$to || !$body) {
			return;
		}

		$user =& JFactory::getUser();
		$message =& JFactory::getMailer();
		$message->addRecipient($to);
		$message->setSubject($subject);
		$message->setBody($body);
		$sender = array(pref('email_from'));
		$message->setSender($sender);
		$sent = $message->send();
		if ($sent != 1) {
			$this->error(JText::sprintf("Error sending mail to %s", $to));
			return false;
		}
		return true;
	}

	/**
	 * Send an email to a user fetching its address from DB by id
	 *
	 * @param string $subject mail subject
	 * @param string $to recipient address
	 * @param string $body message content
	 * @return boolean
	 */
	function send_email_to_id($subject, $to_id, $body)
	{
		if (isset($to_id) and is_int($to_id)) {
			$this->db->setQuery("SELECT email FROM #__users WHERE id = $to_id");
			$to = $this->db->loadResult();
			if ($to) {
				return $this->send_email($subject, $to, $body);
			}
		}
	}

	/**
	 * Send an email using a template
	 *
	 * @param string $subject mail subject
	 * @param string $to recipient address
	 * @param string $body message content
	 * @return boolean
	 */
	function send_template_email_to_id($template, $to_id, $keys=array()) {
		$body = pref("email_template_" . $template);
		$subject = pref("email_template_" . $template . "_subject");

		_log("INFO", "Sending email template $template to user_id $to_id");

		// format keys
		$keys['base_url'] = $keys['base_uri'] = JURI::base();
		if ($keys['group_name'] == 'All') { $keys['group_name'] = JText::_("None"); }
		if (is_numeric($keys['periodicity'])) {
			$keys['periodicity'] = join(", ", expand_periodicity($keys['periodicity']));
		}
		$keys['cost'] = float_point_to_comma(sprintf("%.2f", $keys['cost']));
		if (preg_match('/^[,0-9]+$/', $keys['cost'])) { $keys['cost'] .= "M-bM-^BM-,"; }

		// replace keys
		foreach ($keys as $k => $v) {
			_log("INFO", "Replacing %$k% with $v");
			$body = preg_replace("/%$k%/", $v, $body);
			$subject = preg_replace("/%$k%/", $v, $subject);
		}

		_log("INFO", "Subject: $subject");
		_log("INFO", "Body: $body");

		$this->send_email_to_id($subject, $to_id, $body);
	}

	/**
	 * If a return URL has been provided as a BASE64 encoded parameter named return,
	 * this method will decode it an return it. Otherwise a new URL describing the last view
	 * is built and returned.
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 * @return string
	 */
	function build_standard_url() {
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$return = JRequest::getString('return', "");
		$url = "";

		if ($return) {
			$url = base64_decode($return);
		} else {
			$url = "index.php?option=com_prenotown&view=" . JRequest::getString('view');
			$url .= "&layout=" . JRequest::getString('layout', 'default');
			$url .= "&id=" . JRequest::getInt('id', 0);
			$url .= "&resource_id=" . JRequest::getInt('resource_id', 0);
			$url .= "&group_id=" . JRequest::getInt('group_id', 0);
			# JRequest::setVar('task', null);
			# $url = JRequest::getURI();
		}

		return $url;
	}

	function build_auto_url() {
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$return = JRequest::getString('return', "");
		$url = "";

		if ($return) {
			$url = base64_decode($return);
		} else {
			$url = auto_url(array('task' => null));
		}

		return $url;
	}

	/**
	 * Set a redirect on the URL built by method build_standard_url()
	 *
	 * @param string $message A message showed on the user interface
	 */
	function autoRedirect($message = "")
	{
		$url = $this->build_auto_url();
		$this->setRedirect($url, JText::_($message));
	}

	/**
	 * Call a method of a cost function
	 *
	 * The cost function must accept (at least) one parameter: the resource
	 * id the cost function will operate on. No other data is provided to
	 * c.f. constructor.
	 *
	 * The method must return an array with two keys:
	 *  redirect => URL (a URL to redirect to after this function has exited)
	 *  message => TEXT (a message to print)
	 *
	 * The cost function method should not rely on parameters since this function
	 * will not provide anything. The method will be able to gather all the
	 * informations using JRequest::getVar() calls.
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function cost_function() {
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		// get resource id
		$resource_id = JRequest::getInt('resource_id', 0);
		if (!isset($resource_id)) {
			$this->setRedirect("index.php?option=com_prenotown&view=resources&layout=tree", JText::_("No resource id provided"));
			return;
		}

		// get costfunction class
		$class = JRequest::getString('class', 'baseCostFunction');
		if (!isset($class)) {
			$this->setRedirect("index.php?option=com_prenotown&view=resource&layout=costfunction&id=$resource_id", JText::_("No cost function class provided"));
			return;
		}

		// get costfunction method
		$method = JRequest::getString('method', '');
		if (!isset($method)) {
			$this->setRedirect("index.php?option=com_prenotown&view=resource&layout=costfunction&id=$resource_id", JText::_("No cost function method provided"));
			return;
		}

		// instantiate cost function class
		$cf = new $class($resource_id);

		// check method existance
		if (!method_exists($cf, $method)) {
			$this->error(JText::_("Requested method don't exists") . ": $class::$method");
			_log('ERROR', "Method $cf::$method don't exists");
			$this->setRedirect("index.php?option=com_prenotown&view=resource&layout=costfunction&id=$resource_id");
			return;
		}

		// if noRedirect is set, no redirect will be set
		$noRedirect = JRequest::getInt("noRedirect", 0);

		// call costfunction method
		_log('NOTICE', "Calling " . $cf . "->" . $method . "() on resource id: $resource_id");
		$r = $cf->$method();

		// check if method returned proper values
		if (!is_array($r)) {
			$r = Array();
		}

		if (!isset($r['redirect'])) {
			$r['redirect'] = "index.php?option=com_prenotown&view=resource&layout=costfunction&id=$resource_id";
			if (!$noRedirect) {
				_log('WARNING', "$cf::$method returned no redirect URL");
			}
		}

		if (!isset($r['message'])) {
			$r['message'] = '';
		}

		// report exit status
		if ($noRedirect) {
			_log('OK', "$cf::$method() does not redirect");
		} else {
			_log('OK', "$cf::$method() redirects to " . $r['redirect']);
			$this->setRedirect($r['redirect'], $r['message']);
		}
	}

	/**
	 * create a new resource into database
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function create_resource() {
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		if (_status('admin')) {
			$resource_profile = array(
				'name' => JRequest::getString('resource_name', ''),
				'address' => JRequest::getString('resource_address', ''),
				'description' => JRequest::getString('resource_description', ''),
				'notes' => JRequest::getString('resource_notes', ''),
				'cost_function_id' => JRequest::getInt('cost_function_id', 0),
				'admin_id' => $prenotown_user['id'],
				'deadline' => JRequest::getInt('deadline', 0),
				'max_advance' => JRequest::getInt('max_advance', 0),
				'paying_period' => JRequest::getInt('paying_period', 0),
				'approval_period' => JRequest::getInt('approval_period', 0)
			);

			if ($this->_resource_model->createResource($resource_profile)) {
				_log('OK', "Resource " . $this->_resource_model->tables['resource']->name . " created by user " . $prenotown_user['name']);
				$this->setRedirect("index.php?option=com_prenotown&view=resource&layout=edit&id=" . $this->_resource_model->tables['resource']->id, $message);
				return;
			} else {
				$this->error("Resource creation failed",
					"Check if resource profile is complete or another resource has the same name",
					$this->_resource_model->tables['resource']->getError());

			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		// redirect the user
		$this->setRedirect("index.php?option=com_prenotown&view=resource&layout=create");
	}

	/**
	 * delete a resource from database
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function delete_resource() {
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$message = '';
		
		if (_status("admin")) {
			$resource_id = JRequest::getInt('resource_id', 0);
			if ($resource_id) {
				$this->_resource_model->tables['resource']->id = $resource_id;
				if ($this->_resource_model->tables['resource']->delete()) {
					$message = JText::_("Resource deleted");
				} else {
					$this->error("Error while deleting the resource", "", $this->_resource_model->tables['resource']->getError());
				}
			} else {
				$this->error("No resource_id provided");
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect('index.php?option=com_prenotown&view=resources&layout=myresources', $message);
	}

	/**
	 * modify a resource into database
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function edit_resource() {
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		if (_status('admin')) {
			// get resource ID and check it's not null
			$id = JRequest::getInt('id', 0);
			if (!$id) {
				$this->setRedirect("index.php?option=com_prenotown&view=resources");
				return;
			}

			// set the resource id
			$this->_resource_model->setId($id);

			// copy all the parameters to JTable instance
			$this->_resource_model->tables['resource']->name = JRequest::getString('resource_name', '');
			$this->_resource_model->tables['resource']->address = JRequest::getString('resource_address', '');
			$this->_resource_model->tables['resource']->description = JRequest::getString('resource_description', '');
			$this->_resource_model->tables['resource']->notes = JRequest::getString('resource_notes', '');
			$this->_resource_model->tables['resource']->admin_id = JRequest::getString('admin_id', '');

			// $this->error("Resource: " . $this->_resource_model->tables['resource']->name . ", " . $this->_resource_model->tables['resource']->address);
			// $this->error("Description: " . $this->_resource_model->tables['resource']->description . " -- " . $this->_resource_model->tables['resource']->notes);

			$this->_resource_model->setDeadline(JRequest::getInt('deadline', 0));
			$this->_resource_model->setMaxAdvance(JRequest::getInt('max_advance', 0));
			$this->_resource_model->setPayingPeriod(JRequest::getInt('paying_period', 0));
			$this->_resource_model->setApprovalPeriod(JRequest::getInt('approval_period', 0));
			
			// check'n'store
			if ($this->_resource_model->tables['resource']->check() && $this->_resource_model->tables['resource']->store()) {
				$message = JText::_('Resource edited');
				_log('OK', "Resource " . $this->_resource_model->tables['resource']->name . " edited by " . $prenotown_user);
				$this->setRedirect( "index.php?option=com_prenotown&view=resource&layout=edit&id="
					. $this->_resource_model->tables['resource']->id, $message);
				return;
			} else {
				$message = JText::_("Resource editing failed") . ": " . $this->_resource_model->tables['resource']->getError();
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		// redirect the user
		$this->setRedirect("index.php?option=com_prenotown&view=resources", $message);
	}

	/**
	 * Update resource components, adding and removing as provided
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function update_components() {
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		if (_status('admin')) {
			$id = JRequest::getInt('id', 0);
			if (!isset($id) && $id) {
				$this->setRedirect("index.php?option=com_prenotown&view=resources", JText::_("No resource id provided"));
				return;
			}

			$this->_resource_model->setId($id);
			$this->purge_cache_for_resources($id);
			
			$message = "";
			$db =& JFactory::getDBO();

			$new_component = JRequest::getInt('add_component', 0);
			if (isset($new_component) && $new_component) {
				if ($this->_resource_model->add_component($new_component)) {
					// add something to $message?
					_log('OK', "Component $new_component added to resource $id by user " . $prenotown_user['name']);
					$this->purge_cache_for_resources($new_component);
				}
			}

			$to_be_deleted = JRequest::getVar('delete_composing_resource', null, 'ARRAY');

			if (is_array($to_be_deleted)) {
				foreach ($to_be_deleted as $component) {
					if ($this->_resource_model->delete_component($component)) {
						_log('OK', "Component $component removed from resource $id by user " . $prenotown_user['name']);
						// add something to $message?
						$this->purge_cache_for_resources($component);
					}
				}
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect("index.php?option=com_prenotown&view=resource&layout=components&id=$id", $message);
	}

	/**
	 * Update resource dependencies, adding and removing as provided
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function update_dependencies() {
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		if (_status('admin')) {
			$id = JRequest::getInt('id', 0);
			if (!isset($id) && $id) {
				$this->setRedirect("index.php?option=com_prenotown&view=resources", JText::_("No resource id provided"));
				return;
			}

			$this->_resource_model->setId($id);
			$this->purge_cache_for_resources($id);
			
			$message = "";
			$db =& JFactory::getDBO();

			$new_dependence = JRequest::getInt('add_dependence', 0);
			if (isset($new_dependence) && $new_dependence) {
				if ($this->_resource_model->add_dependence($new_dependence)) {
					// add something to $message?
					_log('OK', "Dependence $new_dependence added to resource $id by user " . $prenotown_user['name']);
					$this->purge_cache_for_resources($new_dependence);
				}
			}

			$to_be_deleted = JRequest::getVar('delete_dependant_resource', null, 'ARRAY');

			if (is_array($to_be_deleted)) {
				foreach ($to_be_deleted as $dependence) {
					if ($this->_resource_model->delete_dependence($dependence)) {
						// add something to $message?
						_log('OK', "Dependence $dependence removed from resource $id by user " . $prenotown_user['name']);
						$this->purge_cache_for_resources($dependence);
					}
				}
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect("index.php?option=com_prenotown&view=resource&layout=dependencies&id=$id", $message);
	}

	/**
	 * Update resource categories, adding and removing as provided
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function update_categories() {
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		if (_status('admin')) {
			$id = JRequest::getInt('id', 0);
			if (!isset($id) && $id) {
				$this->setRedirect("index.php?option=com_prenotown&view=resources", JText::_("No resource id provided"));
				return;
			}

			$this->_resource_model->setId($id);
			
			$message = "";
			$db =& JFactory::getDBO();

			$new_category = JRequest::getInt('add_category', 0);
			if (isset($new_category) && $new_category) {
				if ($this->_resource_model->add_category($new_category)) {
					// add something to $message?
					_log('OK', "Category $new_category added to resource $id by user " . $prenotown_user['name']);
				}
			}

			$to_be_deleted = JRequest::getVar('delete_category', null, 'ARRAY');

			if (is_array($to_be_deleted)) {
				foreach ($to_be_deleted as $category) {
					if ($this->_resource_model->delete_category($category)) {
						// add something to $message?
						_log('OK', "Category $category removed from resource $id by user " . $prenotown_user['name']);
					}
				}
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect("index.php?option=com_prenotown&view=resource&layout=categories&id=$id", $message);
	}

	/**
	 * Edit groups, adding and removing groups as provided
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function edit_groups() {
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$message = "";

		if (_status('operator')) {
			// add new group
			$new_group = JRequest::getString("new_group", '');
			if (isset($new_group) && $new_group) {
				if ($this->_group_model->add_group($new_group)) {
					// add something to $message?
					_log('OK', "Group $new_group created by user " . $prenotown_user['name']);
				}
			}

			// delete one or more existing groups
			$delete_group = JRequest::getVar('delete_group', null, 'ARRAY');
			if (is_array($delete_group)) {
				foreach ($delete_group as $grp) {
					if ($this->_group_model->delete_group($grp)) {
						// add something to $message?
						_log('OK', "Group $grp deleted by user " . $prenotown_user['name']);
					}
				}
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect("index.php?option=com_prenotown&view=groups", $message);
	}

	/**
	 * Create a new user profile inside the database
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function create_user_profile()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$url = "";
		$message = "";

		if ($this->_user_model->createUser()) {
			$message = JText::_("Please use your new account to authenticate yourself");
			$url = "index.php?option=com_prenotown&view=user";
			# $url = "index.php?option=com_user&task=login&" . $this->_jtoken . "=1";
			_log('OK', "New user profile created");
		} else {
			$url = "index.php?option=com_prenotown&view=user&layout=registration";
			$keys = array('username', 'name', 'social_security_number', 'address', 'town', 'ZIP', 'district', 'nationality', 'email');
			foreach ($keys as $k) {
				$url .= "&$k=" . JRequest::getString($k, '');
			}
			
			$this->error("An error occurred while creating your new account");
		}

		$this->setRedirect($url, $message);
	}

	/**
	 * Update user profile
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function update_user_profile()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		if (_status('user')) {
			$user =& JFactory::getUser();
			if (!($user and $user->id)) {
				_log('ERROR', "task update_user_profile() called by unauth user");
				$this->setRedirect("index.php?option=com_prenotown&view=login&" . $this->_jtoken . "=1", JText::_("Please login before"));
				return;
			}

			$this->_user_model->setId($user->id);

			if ($this->_user_model->updateProfile()) {
				_log('OK', "User profile " . $user->id . " updated by user " . $prenotown_user['name']);
				$message = JText::_("Your profile has been successfully saved");
			} else {
				$this->error("Error while saving your profile");
			}

			$message = "";

			// if password has changed, force login
			if (JRequest::getString('password','') and (JRequest::getString('password','') == JRequest::getString('password2',''))) {
				forceLogin();
			} else {
				$this->autoRedirect();
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect($url, $message);
	}

	/**
	 * Update another user profile
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function update_another_user_profile()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		if (_status('operator')) {
			$user_id = JRequest::getInt('user_id', 0);
			if (!$user_id) {
				$this->error("No user_id provided");
				$this->setRedirect("index.php?option=com_prenotown&view=users");
				return;
			}

			$this->_user_model->setId($user_id);

			$url = "index.php?option=com_prenotown&view=user&layout=modifyother&user_id=$user_id";
			$message = "";

			if ($this->_user_model->updateProfile()) {
				_log('OK', "User profile " . $user_id . " updated by user " . $prenotown_user['name']);
				$message = JText::_("User profile has been successfully saved");
			} else {
				$this->error("Error while saving user profile");
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect($url, $message);
	}

	/**
	 * Update resource attachments, adding and removing as provided
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function update_attachments()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		if (_status('admin')) {
			$id = JRequest::getInt('id', 0);
			if (!isset($id) && $id) {
				$this->setRedirect("index.php?option=com_prenotown&view=resources", JText::_("No resource id provided"));
				return;
			}

			$this->_resource_model->setId($id);
			
			$message = "";
			$db =& JFactory::getDBO();

			$new_attachment = JRequest::getString('filename', NULL);
			if (isset($new_attachment) && $new_attachment) {
				$new_name = JRequest::getString('add_name', NULL);
				if (isset($new_name) && $new_name) {
					if ($this->_resource_model->add_attachment($new_attachment, $new_name)) {
						// add something to $message?
						_log('OK', "Attachment $new_attachment added to resource $id by user " . $prenotown_user['name']);
					}
				}
			}

			$to_be_deleted = JRequest::getInt('attachment_id', 0);
			if (isset($to_be_deleted) and $to_be_deleted > 0) {
				if ($this->_resource_model->delete_attachment($to_be_deleted)) {
                    			_log('OK', "Attachment $to_be_deleted removed from resource $id by user " . $prenotown_user['name']);
					// add something to $message?
				}
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect("index.php?option=com_prenotown&view=resource&layout=attachments&id=$id", $message);
	}

	/**
	 * Retract a booking
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function retract_booking()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;
		$message = "";

		$url = $this->build_auto_url();

		if (_status('user')) {
			$booking_id = JRequest::getInt('booking_id', 0);
			$this->_superbooking_model->setId($booking_id);

			$resource_id = $this->_superbooking_model->tables['main']->resource_id;
			$user_id = $this->_superbooking_model->tables['main']->user_id;
			$group_id = $this->_superbooking_model->tables['main']->group_id;

			$this->db->setQuery("SELECT name FROM #__prenotown_user_groups WHERE id = $group_id");
			$group_name = $this->db->loadResult();
			_log("INFO", "Group name: $group_name");

			$this->_resource_model->setId($resource_id);
			$this->_user_model->setId($user_id);

			$resource_name = $this->_resource_model->tables['main']->name;

			if ($booking_id > 0) {
				if ($this->_superbooking_model->delete($booking_id)) {
					_log("OK", "Booking $booking_id retracted by user " . $this->user->id);
					$message = JText::_("Booking retracted");
					$this->purge_cache_for_resources($resource_id);

					$this->send_template_email_to_id('retract_booking', $this->_superbooking_model->tables['main']->user_id, array(
						'booking_interval' => format_booking_period(array(
							'begin' => $this->_superbooking_model->tables['main']->begin,
							'end' => $this->_superbooking_model->tables['main']->end,
							'is_periodic' => $this->_superbooking_model->tables['main']->periodic,
						)),
						'resource_id' => $resource_id,
						'resource_name' => $this->_resource_model->tables['main']->name,
						'resource_address' => $this->_resource_model->tables['main']->address,
						'periodicity' => $this->_superbooking_model->tables['main']->periodicity,
						'cost' => $this->_superbooking_model->tables['main']->cost,
						'booking_id' => $booking_id,
						'group_name' => $group_name
					));
				} else {
					_log("ERROR", "Unable to retract booking $booking_id (user " . $this->user->id . ")");
				}
			} else {
				$this->error("No (valid) booking id provided");
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect($url, $message);
	}

	/**
	 * Set ghost identity for an operator or higher inside its session
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function set_ghost_identity()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$view = JRequest::getString("view", "users");
		$layout = JRequest::getString("layout", "default");

		$url = auto_url();
		$message = "";

		if (_status('operator')) {
			$ghost_user_id = JRequest::getInt("ghost_user_id", 0);
			if (!$ghost_user_id) {
				$this->error("No ghost user id provided");
			} else {
				$session =& JFactory::getSession();
				$session->set('ghost_user_id', $ghost_user_id, 'com_prenotown');
				$session->set('ghost_group_id', 0, 'com_prenotown');
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect($url, $message);
	}

	/**
	 * Set ghost group for an operator or higher in its session
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function set_ghost_group()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$view = JRequest::getString("view", "groups");
		$layout = JRequest::getString("layout", "default");

		// $url = "index.php?option=com_prenotown&view=user&layout=$layout&user_name_filter=" . JRequest::getString('user_name_filter', '');
		$url = "index.php?option=com_prenotown&view=$view&layout=$layout&name_filter=" . JRequest::getString('name_filter', '');
		$message = '';

		if (_status('user')) {
			$ghost_group_id = JRequest::getInt("ghost_group_id", 0);
			if (!$ghost_group_id) {
				$this->error("No ghost group id provided");
			} else {
				$session =& JFactory::getSession();
				$session->set('ghost_group_id', $ghost_group_id, 'com_prenotown');
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect($url, $message);
	}

	/**
	 * Remove ghost identity from operator session
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function forget_user_identity()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		if (_status('operator')) {
			$session =& JFactory::getSession();
			$session->set('ghost_user_id',0,'com_prenotown');
		}

		$this->autoRedirect();
	}

	/**
	 * Remove group identity from operator session
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function forget_group_identity()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		if (_status('user')) {
			$session =& JFactory::getSession();
			$session->set('ghost_group_id',0,'com_prenotown');
		}

		$this->autoRedirect();
	}

	/**
	 * Delete a group from the database
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function delete_group()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$url = "index.php?option=com_prenotown&view=" . $this->var['view'] . "&layout=" . $this->var['layout'];
		$message = '';

		if (_status('operator')) {
			$id = JRequest::getInt('id', 0);
			if (!$id) {
				$this->error("No (valid) group id provided");
				$this->setRedirect($url);
				return;
			}

			if ($this->_group_model->delete_group($id)) {
				$message = JText::_("Group deleted");
				_log("OK", "Group $id deleted by user " . $prenotown_user['name']);
			} else {
				$this->error(JText::sprintf("Error deleting group %d",$id));
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect($url, $message);
	}

	/**
	 * Create a new group
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function create_new_group()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$url = "index.php?option=com_prenotown&view=" . $this->var['view'] . "&layout=" . $this->var['layout'];
		$message = '';

		if (_status('operator')) {
			$new_group_name = JRequest::getString("new_group_name", "");
			if (!$new_group_name) {
				$this->error("Null names not allowed in group creation");
				$this->setRedirect($url);
				return;
			}

			if (!$this->_group_model->add_group($new_group_name)) {
				$this->error("Error creating new group");
			} else {
				$message = JText::_("Group created");
				_log("OK", "Group $new_group_name created by user " . $prenotown_user['name']);
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect($url, $message);
	}

	/**
	 * Add a user to a group
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function add_user_to_group()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$user_id = JRequest::getInt('user_id', 0);
		$group_id = JRequest::getInt('group_id', 0);

		if (_status('operator')) {
			if (!$user_id) {
				$this->error("No user_id provided");
			} else if (!$group_id) {
				$this->error("No group_id provided");
			} else {
				$this->_group_model->setId($group_id);
				$this->_group_model->addUser($user_id);
				_log("OK", "User $user_id added to group $group_id by user " . $prenotown_user['name']);
				$this->send_email_to_id(JText::_("You've been subscribed to group " . $this->_group_model->tables['main']->name,
					$user_id, "Da questo momento in avanti potrai prenotare a nome di questo gruppo inserendo anche prenotazioni periodiche."));
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$url = $this->build_standard_url();
		$this->setRedirect($url);
		_log("OK", "Redirecting to: $url");
	}

	/**
	 * Delete a user from a group
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function delete_user_from_group()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$user_id = JRequest::getInt('user_id', 0);
		$group_id = JRequest::getInt('group_id', 0);

		if (_status('operator')) {
			if (!$user_id) {
				$this->error("No user_id provided");
			} else if (!$group_id) {
				$this->error("No group_id provided");
			} else {
				$this->_group_model->setId($group_id);
				$this->_group_model->deleteUser($user_id);
				_log("OK", "User $user_id deleted from group $group_id by user " . $prenotown_user['name']);
				$this->send_email_to_id(JText::_("You've been unsubscribed from group " . $this->_group_model->tables['main']->name,
					$user_id, "Da questo momento non potrai piM-CM-9 prenotare a nome di questo gruppo.\nSe ritieni che questo non sia corretto contatta lo staff"));
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$url = $this->build_standard_url();
		$this->setRedirect($url);
		_log("OK", "Redirecting to: $url");
	}

	/**
	 * Change the name of a grou
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function change_group_name()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$group_name = JRequest::getString('group_name', "");
		$group_id = JRequest::getInt('id', 0);

		if (_status('operator')) {
			if (!strlen($group_name)) {
				$this->error("No group_name provided");
			} else if (!$group_id) {
				$this->error("No group_id provided");
			} else {
				$this->_group_model->setId($group_id);
				$this->_group_model->tables['usergroups']->name = $group_name;
				if (
					$this->_group_model->tables['usergroups']->check() &&
					$this->_group_model->tables['usergroups']->store()
				) {
					_log("OK", "Group $group_id renamed to $group_name by user " . $prenotown_user['name']);
				} else {
					$this->error(JText::sprintf("Error changing group %d name to %s", $group_id, $group_name),
						"", $this->_group_model->tables['usergroups']->getError());
				}
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$url = $this->build_standard_url();
		$this->setRedirect($url);
		_log("OK", "Redirecting to: $url");
	}

	/**
	 * Save one single booking
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 */
	function save_booking()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$user_id = _user_id();
		$group_id = _group_id();
		$operator_id = $user_id == $prenotown_user['id'] ? 0 : $prenotown_user['id'];
		$resource_id = JRequest::getInt('id', 0);

		$booking_begin_date = '';
		$booking_begin_hour = '';
		$booking_begin_minute = '';
		$booking_end_date = '';
		$booking_end_hour = '';
		$booking_end_minute = '';
		$periodicity = 0;
		$periodic = JRequest::getInt('periodic', 0);

		if ($periodic) {
			if ($group_id <= 100) {
				_warn("WARN", JText::sprintf("Periodic booking is forbidden to private users"));
				$this->autoRedirect("");
				return;
			}
			$booking_begin_date = JRequest::getString('periodic_booking_begin_date', '00-00-0000');
			$booking_end_date = JRequest::getString('periodic_booking_end_date','00-00-0000');
			$booking_begin_hour = JRequest::getInt('periodic_booking_begin_hour',0);
			$booking_end_hour = JRequest::getInt('periodic_booking_end_hour',0);
			$booking_begin_minute = JRequest::getInt('periodic_booking_begin_minute',0);
			$booking_end_minute = JRequest::getInt('periodic_booking_end_minute',0);
			$periodicity = JRequest::getInt('monday', 0)	*  1 +
				JRequest::getInt('tuesday', 0)		*  2 +
				JRequest::getInt('wednesday', 0)	*  4 +
				JRequest::getInt('thursday', 0)		*  8 +
				JRequest::getInt('friday', 0)		* 16 +
				JRequest::getInt('saturday', 0)		* 32 +
				JRequest::getInt('sunday', 0)		* 64;
			$exceptions = explode(",", JRequest::getString('exceptions', ""));
		} else {

			$booking_begin_date = JRequest::getString('booking_begin_date', '00-00-0000');
			$booking_end_date = JRequest::getString('booking_end_date','00-00-0000');
			$booking_begin_hour = JRequest::getInt('booking_begin_hour',0);
			$booking_end_hour = JRequest::getInt('booking_end_hour',0);
			$booking_begin_minute = JRequest::getInt('booking_begin_minute',0);
			$booking_end_minute = JRequest::getInt('booking_end_minute',0);
			$periodicity = 0;

			$exceptions = array();
		}
		$cost = JRequest::getFloat('cost', 0.0);
		$method = JRequest::getString('method', '');

		$message = '';
		$body = "";

		$begin = '';
		$end = '';

		$booking_begin_date = date_human_to_sql($booking_begin_date);
		$booking_end_date = date_human_to_sql($booking_end_date);

		$begin = "$booking_begin_date $booking_begin_hour:$booking_begin_minute:00";
		$end = "$booking_end_date $booking_end_hour:$booking_end_minute:00";

		$exceptions_string = count($exceptions) ? implode(", ", $exceptions) : "none";

		$redirect_url = $this->build_standard_url();

		if (_status('user')) {
			$operator = ($prenotown_user['id'] != $booking_user['id']) ? $prenotown_user['name'] : "none";
			_log("INFO", "Booking attempt [user: " . $booking_user['name'] . "] [resource_id: $resource_id] [group_id: $group_id] [operator: $operator] [start: $begin] [end: $end] [periodicity: $periodicity] [exceptions: $exceptions_string] [cost: $cost]");

			$this->_resource_model->setId($resource_id);
			$booking_id = $this->_resource_model->book($begin, $end, $user_id, $group_id, $operator_id, $cost, $method, $periodic, $periodicity, $exceptions);
			if ($booking_id) {
				$this->db->setQuery("SELECT name FROM #__prenotown_resource WHERE id = $resource_id");
				$resource_name = $this->db->loadResult();

				$this->db->setQuery("SELECT address FROM #__prenotown_resource WHERE id = $resource_id");
				$resource_address = $this->db->loadResult();

				$this->db->setQuery("SELECT name FROM #__prenotown_user_groups WHERE id = $group_id");
				$group_name = $this->db->loadResult();

				$redirect_url .= "&booking_id=$booking_id";
				$message = JText::_("Booking saved");

				# invalidate cached availabililty image
				$this->purge_cache_for_resources($resource_id);

				# send email to booking user
				$this->send_template_email_to_id('booking_creation', $booking_user['id'], array(
					'booking_interval' => format_booking_period(array('begin' => $begin, 'end' => $end, 'is_periodic' => $periodic)),
					'resource_id' => $resource_id,
					'resource_name' => $resource_name,
					'resource_address' => $resource_address,
					'periodicity' => $periodicity,
					'cost' => $cost,
					'booking_id' => $booking_id,
					'group_name' => $group_name,
				));
			} else {
				$profile = array(
					'layout' => 'paybooking',
					'id' => $resource_id,
					'periodic' => $periodic,
				);
				if ($periodic) {
					$profile['periodic_booking_begin_date'] = date_sql_to_human($booking_begin_date);
					$profile['periodic_booking_end_date'] = date_sql_to_human($booking_end_date);
					$profile['periodic_booking_begin_hour'] = $booking_begin_hour;
					$profile['periodic_booking_end_hour'] = $booking_end_hour;
					$profile['periodic_booking_begin_minute'] = $booking_begin_minute;
					$profile['periodic_booking_end_minute'] = $booking_end_minute;
					$profile['monday'] = JRequest::getInt('monday', 0);
					$profile['tuesday'] = JRequest::getInt('tuesday', 0);
					$profile['wednesday'] = JRequest::getInt('wednesday', 0);
					$profile['thursday'] = JRequest::getInt('thursday', 0);
					$profile['friday'] = JRequest::getInt('friday', 0);
					$profile['saturday'] = JRequest::getInt('saturday', 0);
					$profile['sunday'] = JRequest::getInt('sunday', 0);
					$profile['exceptions'] = JRequest::getString('exceptions', "");
				} else {
					$profile['booking_begin_date'] = date_sql_to_human($booking_begin_date);
					$profile['booking_end_date'] = date_sql_to_human($booking_end_date);
					$profile['booking_begin_hour'] = $booking_begin_hour;
					$profile['booking_end_hour'] = $booking_end_hour;
					$profile['booking_begin_minute'] = $booking_begin_minute;
					$profile['booking_end_minute'] = $booking_end_minute;
				}

				$redirect_url = auto_url($profile);
				$this->error("Error recording your booking");
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->setRedirect($redirect_url, $message);
	}

	/**
	 * Insert into payments table the serial number of the check used to pay
	 *
	 * @return boolean
	 */
	function insert_payment()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		if (_status('operator')) {
			$booking_id = JRequest::getInt('booking_id', 0);
			// $check_number = JRequest::getString('check_number', null);
			// $amount = preg_replace('/[^\d]/g', '.', JRequest::getString('amount', 0));
			$amount = float_comma_to_point(JRequest::getString('amount', 0));
			$amount = (float) $amount;

			if (!$booking_id) {
				$this->error("No booking_id provided");
				$this->autoRedirect();
			//} else if (!$check_number) {
			//	$this->error("No check_number provided");
			//	$this->autoRedirect();
			} else if (!$amount) {
				$this->error("No amount provided");
				$this->autoRedirect();
			} else {
				$booking =& JTable::getInstance('Superbooking', 'Table');
				$booking->load($booking_id);

				// Should be already there, but just to be sure...
				$this->db->setQuery("INSERT INTO #__prenotown_payments (id) VALUES (" . $booking->payment_id . ")");
				$this->db->query();

				$payments =& JTable::getInstance('Payments', 'Table');
				$payments->load($booking->payment_id);

				// $payments->check_number = $check_number;
				$payments->user_id = $booking->user_id;
				$payments->amount = $amount;
				$payments->method = float_comma_to_point(JRequest::getString('method', 'check'));
				if ($payments->method != 'check' && $payments->method != 'pos') {
					$payments->method = 'check';
				}
				$payments->date = strftime("%Y-%m-%d %H:%M:%S");

				$message = "";

				if ($payments->check() && $payments->store()) {
					$message = JText::_("Payment saved");
					_log("INFO", "Payment n. " . $booking->payment_id . " paid by " . $payments->method);
					$this->send_template_email_to_id('booking_paid', $user_id, array('booking_id' => $booking_id));
				} else {
					$this->error("Error saving payment: invalid data", "", $payments->getError());
				}
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->autoRedirect($message);
	}

	/**
	 * Delete a user from the database
	 */
	function delete_user()
	{
		$user_id = JRequest::getInt('user_id', 0);
		$message = "";
		
		if (_status('admin')) {
			if ($user_id) {
				if ($this->_user_model->deleteUser($user_id)) {
					$message = JText::_("User deleted");
				}
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->autoRedirect($message);
	}

	/**
	 * Approve pending booking
	 *
	 * @global array $prenotown_user Logged user profile
	 * @global array $ghost_user Ghost user profile
	 * @global array $ghost_group Ghost group profile
	 * @global array $booking_user Booking user profile
	 * @return string
	 */
	function approve_resources()
	{
		global $prenotown_user, $ghost_user, $ghost_group, $booking_user;

		$approve = JRequest::getVar('approve', array(), 'ARRAY');
		$reason = JRequest::getVar('reason', array(), 'ARRAY');

		if (_status('admin')) {
			foreach ($approve as $key => $yn) {
				if ($yn) {
					/* confirm booking */
					$this->_superbooking_model->tables['superbooking']->reset();
					$this->_superbooking_model->tables['superbooking']->load($key);
					$this->_superbooking_model->tables['superbooking']->approved = 1;
					$this->_superbooking_model->tables['superbooking']->check() && $this->_superbooking_model->tables['superbooking']->store();
					$this->send_template_email_to_id('confirm_booking', $this->_superbooking_model->tables['superbooking']->user_id, array('booking_id' => $key));
				} else {
					/* remove booking and send the email to the user */
					$this->_superbooking_model->tables['superbooking']->load($key);
					$this->send_template_email_to_id('reject_booking', $this->_superbooking_model->tables['superbooking']->user_id, array('booking_id' => $key, 'reason' => $reason[$key]));
					$this->_superbooking_model->tables['superbooking']->reset();
					$this->_superbooking_model->tables['superbooking']->delete($key);
				}
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}
		
		$this->setRedirect('index.php?option=com_prenotown&view=resources&layout=approvallist');
	}

	/**
	 * Updates availability range profile for a resource
	 */
	function updateAvailability()
	{
		$message = "";

		$resource_id = JRequest::getInt('id', 0);

		if (!$resource_id) {
			$this->error("No resource id provided");
		} else if (_status('admin')) {
			$this->_resource_model->setId($resource_id);
			$this->_resource_model->tables['resource']->availability_enabled = JRequest::getInt("availability_enabled", 0);

			if ($this->_resource_model->tables['resource']->availability_enabled) {
				foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') as $day) {
					$day_begin = $day . '_begin';
					$this->_resource_model->tables['resource']->$day_begin =
						JRequest::getString($day_begin,
							$this->_resource_model->tables['resource']->$day_begin ?
							$this->_resource_model->tables['resource']->$day_begin :
							"00:00:00");

					$day_end = $day . '_end';
					$this->_resource_model->tables['resource']->$day_end =
						JRequest::getString($day_end,
							$this->_resource_model->tables['resource']->$day_end ?
							$this->_resource_model->tables['resource']->$day_end :
							"23:59:59");
				}

				$this->_resource_model->propagate_availability_range();
			}

			if ($this->_resource_model->tables['resource']->check() && $this->_resource_model->tables['resource']->store()) {
				$message = JText::_("Availability range updated");
			} else {
				$this->error("Error updating availability range", "", $this->_resource_model->tables['resource']->getError());
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->autoRedirect($message);
	}

	/**
	 * Revoke a fee from a group
	 */
	function revokeFee()
	{
		$fee_id = JRequest::getInt('fee_id', 0);

		if (_status('admin')) {
			if (!$fee_id) {
				$this->error("No fee_id provided");
				$this->autoRedirect();
				return;
			}

			$group_id = JRequest::getInt('group_id', 0);
			if (!$group_id) {
				$this->error("No group_id provided");
				$this->autoRedirect();
				return;
			}

			$this->db->setQuery("DELETE FROM #__prenotown_time_cost_function_fee_groups WHERE fee_id = $fee_id AND group_id = $group_id");
			$this->db->query();
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->autoRedirect();
	}

	/**
	 * Update preferences
	 *
	 * @global array $prenotown_pref the list of prenotown preferences
	 */
	function update_preferences()
	{
		global $prenotown_pref;

		if (_status('superadmin')) {
			foreach ($prenotown_pref as $key) {
				if (preg_match('/^bpw_options_/', $key)) {
					pref($key, JRequest::getString($key, 0));
				} else {
					pref($key, JRequest::getString($key, ""));
				}
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->autoRedirect("Preferences saved");
	}

	function create_new_category()
	{
		global $prenotown_pref;

		if (_status('admin')) {
			$new_category = JRequest::getString('new_category', '');
			$resource_id = JRequest::getInt('id', 0);

			if ($new_category and $resource_id) {
				if ($this->_resource_group_model->createResourceGroup($new_category)) {
					if ($this->_resource_group_model->addResource($resource_id)) {
						$this->setRedirect("index.php?option=com_prenotown&view=resource&layout=categories&id=$resource_id");
						return;
					} else {
						$this->error("New category created, but an error occurred while adding resource");
					}
				} else {
					$this->error("Unable to create new category");
				}
			} else {
				$this->error("Resource id or category name not provided");
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$this->autoRedirect();
	}

	function add_exception()
	{
		if (_status('user')) {
			$booking_id = JRequest::getInt('booking_id', 0);
			$exception_date = JRequest::getString('exception_date', '00-00-0000');
			$exception_date = date_human_to_sql($exception_date);

			if ($booking_id and $exception_date) {
				$created = $this->_superbooking_exception_model->createSuperbookingException(array(
					'booking_id' => $booking_id,
					'exception_date' => $exception_date,
				));

				if ($created) {
					$this->db->setQuery("SELECT resource_id FROM #__prenotown_superbooking WHERE id = $booking_id");
					$resource_id = $this->db->loadResult();
					$this->purge_cache_for_resources($resource_id);
					$this->_superbooking_model->tables['main']->load($booking_id);

					$this->send_template_email_to_id('add_exception', $this->_superbooking_model->tables['main']->user_id, array(
						'booking_id' => $booking_id,
						'exception_date' => $exception_date,
					));
				}
			} else {
				$this->error("Booking id or exception date not provided");
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$url = $this->build_auto_url();
		$url .= "&booking_id=$booking_id&exception_date=$exception_date&begin_date=" . JRequest::getString("begin_date") . "&end_date=" . JRequest::getString("end_date");
		$this->setRedirect($url, "");
		# $this->setRedirect("index.php?option=com_prenotown&view=user&layout=bookingExceptions&booking_id=$booking_id&exception_date=$exception_date");
	}

	function delete_exception()
	{
		$msg = "";
		if (_status('user')) {
			$exception_id = JRequest::getInt('exception_id', 0);
			$booking_id = JRequest::getInt('booking_id', 0);

			if ($exception_id) {
				$this->_superbooking_exception_model->tables['superbookingException']->reset();
				$this->_superbooking_exception_model->tables['superbookingException']->id = $exception_id;
				$this->_superbooking_exception_model->tables['superbookingException']->load();
				$exception_date = date_sql_to_human($this->_superbooking_exception_model->tables['superbookingException']->exception_date);
				$this->_superbooking_exception_model->tables['superbookingException']->delete();
				$this->db->setQuery("SELECT resource_id FROM #__prenotown_superbooking WHERE id = $booking_id");
				$resource_id = $this->db->loadResult();
				$this->purge_cache_for_resources($resource_id);
				$msg = Jtext::_("Exception deleted");
				$this->_superbooking_model->tables['main']->load($booking_id);
				$this->send_template_email_to_id('delete_exception', $this->_superbooking_model->tables['main']->user_id, array(
					'booking_date' => $exception_date,
					'booking_id' => $booking_id,
				));
			} else {
				$this->error("Booking id or exception date not provided");
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		$url = $this->build_auto_url();
		# $url .= "&booking_id=$booking_id&begin_date=" . JRequest::getString("begin_date") . "&end_date=" . JRequest::getString("end_date");
		##### $url = $this->build_auto_url();
		$this->setRedirect($url, $msg);
	}

	/**
	 * Funzione di conferma del pagamento via Bankpass
	 */
	function bpw_confirm_booking()
	{
		$uri =& JURI::getInstance();
		_log("INFO", "Url ricevuto da bankpass: " . $uri->toString());

		$mac_string_elements = array();
		foreach (array("NUMORD", "IDNEGOZIO", "AUT", "IMPORTO", "VALUTA", "IDTRANS", "TCONTAB", "TAUTOR", "ESITO") as $field) {
			$mac_string_elements[] = "$field=" . JRequest::getString($field, "");
		}
		foreach (array("BPW_MODPAG", "BPW_TIPO_TRANSAZIONE", "BPW_ISSUER_COUNTRY") as $field) {
			if (JRequest::getString($field, "")) {
				$mac_string_elements[] = "$field=" . JRequest::getString($field, "");
			}
		}
		$mac_string_elements[] = pref('bpw_status_api_key');
		$mac_string = implode("&", $mac_string_elements);

		$hash = strtoupper(sha1($mac_string));

		if ($hash == JRequest::getString('MAC', "")) {
			if (JRequest::getString("ESITO", '') == "00") {
				/* get payment id */
				$payment_id = preg_replace("/^" . pref("bpw_nome_negozio") . "/", "", JRequest::getString("NUMORD", ""));
				_log("WARNING", "Payment $payment_id succeded");

				/* save payment inside DB */
				$sql = "UPDATE #__prenotown_payments SET amount = " . $this->db->quote(JRequest::getInt("IMPORTO", "") / 100) . ", method = 'credit_card', date = now() WHERE id = $payment_id";
				_log_sql($sql);
				$this->db->setQuery($sql);
				$this->db->query();

				$this->db->setQuery("SELECT id FROM #__prenotown_superbooking WHERE payment_id = $payment_id");
				$booking_id = $this->db->loadResult();

				$this->db->setQuery("SELECT user_id FROM #__prenotown_payments WHERE id = $payment_id");
				$user_id = $this->db->loadResult();

				$this->send_template_emai_to_id('booking_paid', $user_id, array('booking_id' => $booking_id));
			} else {
				_log("WARNING", "Bankpass returned an exit status of " . JRequest::getString("ESITO", '') . " on booking " . JRequest::getString("NUMORD", ''));
			}
		} else {
			_log("WARNING", "WARNING: Possibly Bankpass forged message received");
			_log("WARNING", "  Calculated MAC: $hash");
			_log("WARNING", "    Received MAC: " . JRequest::getString('MAC', ""));
			_log("WARNING", "             URL: " . $uri->toString());
		}
	}

	function add_unavailability_range()
	{
		global $booking_user;
		$msg = "";

		if (_status('admin')) {
			$begin = date_human_to_sql(JRequest::getString('begin_date', '00-00-0000'));
			$end = date_human_to_sql(JRequest::getString('end_date', '00-00-0000'));
			$resource_id = JRequest::getInt('resource_id', 0);
			$user_id = $booking_user['id'];

			$sql = "INSERT INTO #__prenotown_superbooking (user_id, resource_id, group_id, begin, end, periodic) VALUES ($user_id, $resource_id, 2, '$begin 00:00:00', '$end 23:59:59', 0)";
			_log_sql($sql);
			$this->db->setQuery($sql);
			if ($this->db->query()) {
				$msg = "Range inserted";
				$this->purge_cache_for_resources($resource_id);

				$sql = "SELECT DISTINCT user_id FROM #__prenotown_superbooking WHERE resource_id = $resource_id AND group_id <> 2 AND (DATE(begin) <= '$end' OR DATE(end) >= '$begin')";
				_log_sql($sql);
				$this->db->setQuery($sql);
				$bookers = $this->db->loadResultArray();

				foreach ($bookers as $booker) {
					$this->_resource_model->setId($resource_id);
					$this->send_template_email_to_id('unavailability_created', $booker, array(
						'begin_date' => date_sql_to_human($begin),
						'end_date' => date_sql_to_human($end),
						'resource_name' => $this->_resource_model->tables['main']->name,
						'resource_address' => $this->_resource_model->tables['main']->address,
					));
				}
			} else {
				$this->error("Unable to insert unavailability range");
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		// redirect the user
		$this->autoRedirect($msg);
	}

	function delete_unavailability_range()
	{
		$msg = "";

		if (_status('admin')) {
			$resource_id = JRequest::getInt('resource_id', 0);
			$unavailability_id = JRequest::getInt('unavailability_id', 0);
			$this->_superbooking_model->setId($unavailability_id);
			$begin = date_sql_to_human(preg_replace('/ .*$/', '', $this->_superbooking_model->tables['main']->begin));
			$end = date_sql_to_human(preg_replace('/ .*$/', '', $this->_superbooking_model->tables['main']->end));;

			if ($unavailability_id) {
				$sql = "SELECT DISTINCT user_id FROM #__prenotown_superbooking WHERE resource_id = $resource_id AND group_id <> 2 AND ((SELECT DATE(begin) FROM #__prenotown_superbooking WHERE id = $unavailability_id) <= '$end' OR (SELECT DATE(end) FROM #__prenotown_superbooking WHERE id = $unavailability_id) >= '$begin')";
				_log_sql($sql);
				$this->db->setQuery($sql);
				$bookers = $this->db->loadResultArray();

				$this->db->setQuery("DELETE FROM #__prenotown_superbooking WHERE id = $unavailability_id");
				if ($this->db->query()) {
					$msg = "Unavailability range deleted";
					$this->purge_cache_for_resources($resource_id);

					foreach ($bookers as $booker) {
						$this->_resource_model->setId($resource_id);
						$this->send_template_email_to_id('unavailability_removed', $booker, array(
							'begin_date' => date_sql_to_human($begin),
							'end_date' => date_sql_to_human($end),
							'resource_name' => $this->_resource_model->tables['main']->name,
							'resource_address' => $this->_resource_model->tables['main']->address,
						));
					}
				}
			} else {
				$this->error("No unavailability range id provided");
			}
		} else {
			$this->error("Insufficient permissions to execute task", JRequest::getString("task"));
		}

		// redirect the user
		$this->autoRedirect($msg);
	}

	function purge_cache_for_resources($ids)
	{
		if (!isset($ids))
			return;

		if (is_array($ids)) {
			foreach ($ids as $id) {
				unlink(resource_booking_availability_cache_path($id));
			}
			return;
		}

		if (is_numeric($ids)) {
			$this->purge_cache_for_resources(array($ids));
		}
	}
}
?>
