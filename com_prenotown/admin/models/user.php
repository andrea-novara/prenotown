<?php
/**
 * @package Prenotown
 * @subpackage Models
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** ensure a valid entry point */
defined('_JEXEC') or die("Restricted Access");

/** import logging facilities */
require_once(JPATH_COMPONENT_SITE . DS . "assets" . DS . "logging.php");
require_once(JPATH_BASE . DS . "libraries" . DS . "joomla" . DS . "user" . DS . "helper.php");

/**
 * User model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelUser extends PrenotownModelPrenotown
{
	/**
	 * Constructor, builds object and determines the foobar ID
	 *
	 */
	function __construct() {
		/* calling parent constructor */
		parent::__construct();

		/* loading tables */
		$this->addTable('users', true);			// this is also saved as 'main' table
		$this->addTable('usercomplement');
	}

	/**
	 * Resets the user ID and data
	 *
	 * @param int user ID
	 */
	function setId($id=0) {
		$this->_id = $id;

		if (isset($this->tables['users'])) {
			$this->tables['users']->reset();
			$this->tables['users']->id = $id;
			$this->tables['users']->load();
		}

		if (isset($this->tables['usercomplement'])) {
			$this->tables['usercomplement']->reset();
			$this->tables['usercomplement']->id = $id;
			$this->tables['usercomplement']->load();
		}
	}

	/**
	 * Gets user data
	 *
	 * @return object
	 */
	function getUser() {
		$user = array();

		// merge #__users and #__prenotown_user_complement
		foreach ($this->tables['users']->getProperties() as $k => $v) {
			$user[$k] = $this->tables['users']->$k;
		}

		foreach ($this->tables['usercomplement']->getProperties() as $k => $v) {
			$user[$k] = $this->tables['usercomplement']->$k;
		}

		// full_address is a custom field not available in SQL
		$user['full_address'] = implode(" ",
			array($user['address'], $user['ZIP'], $user['town']));;

		return $user;
	}

	/**
	 * Gets current booking
	 */
	function currentBooking() {
		$superbookings =& JModel::getInstance('Superbookings', 'PrenotownModel');
		$superbookings->addFilter("#__prenotown_superbooking.end >= now()");
		return $superbookings->getByUser($this->_id);
	}

	/**
	 * create a new user
	 *
	 * @return boolean
	 */
	function createUser() {
		/* get user password and hash it */
		$password = JRequest::getString("password","");
		if (!$password) {
			_warn("WARN", JText::_("No password provided"));
			return false;
		}
		$password2 = JRequest::getString("password2","");
		if (!$password2 or $password2 != $password) {
			_warn("WARN", JText::_("Password mismatch"));
			return false;
		}
		$salt  = JUserHelper::genRandomPassword(32);
		$password_hash = JUserHelper::getCryptedPassword($password, $salt) . ":" . $salt;

		/* first, create Joomla main table #__users entry */
		$this->tables['users']->reset();
		foreach ($this->tables['users']->getProperties() as $k => $v) {
			$this->tables['users']->$k = JRequest::getVar($k, '');
		}
		$this->tables['users']->username = JRequest::getString('email', '');
		$this->tables['users']->password = $password_hash;
		$this->tables['users']->id = 0; // new user

		/* save the Joomla part of the user */
		if (!($this->tables['users']->check() && $this->tables['users']->store())) {
			if (pref('debug')) _warn("WARN", JText::_("Can't create user: ") . $this->tables['users']->getError());
			else _warn("WARN", JText::_("Can't create user: "));
			return false;
		}
		$user_id = $this->tables['users']->id;

		$query = "SELECT id FROM #__users WHERE username = '" . JRequest::getString('email', '') . "'";
		JError::raiseNotice(100, preg_replace('/#__/', 'jos_', $query));
		_log_sql($query);
		$this->db->setQuery($query);
		$user_id = $this->db->loadResult();

		/* insert the user into ARO list */
		$query = "INSERT INTO #__core_acl_aro (section_value, value, name) values ('users', $user_id, " . $this->db->quote($this->tables['users']->name) . ")";
		JError::raiseNotice(100, preg_replace('/#__/', 'jos_', $query));
		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();
		$aro_id = $this->db->insertid();

		/* insert the user into ARO map */
		$query = "INSERT INTO #__core_acl_groups_aro_map (group_id, aro_id) values (18, $aro_id)";
		JError::raiseNotice(100, preg_replace('/#__/', 'jos_', $query));
		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();

		/* ... and then save into #__prenotown_user_complement */
		$ssn = JRequest::getString('social_security_number', '');
		$address = JRequest::getString('address', '');
		$town = JRequest::getString('town', '');
		$district = JRequest::getString('district', '');
		$nationality = JRequest::getString('nationality', '');
		$ZIP = JRequest::getString('ZIP', '');

		$query = "INSERT INTO #__prenotown_user_complement (id, status, social_security_number, session_id, address, town, district, nationality, ZIP) VALUES ($user_id, 'user', '$ssn', NULL, '$address', '$town', '$district', '$nationality', '$ZIP')";
		_log_sql($query);
		JError::raiseNotice(100, preg_replace('/#__/', 'jos_', $query));
		$this->db->setQuery($query);
		$this->db->query();

		return true;

		/** no longer used

		$this->tables['usercomplement']->reset();
		$this->tables['usercomplement']->load($user_id); // link to #__users table

		if ($this->tables['usercomplement']->check() && $this->tables['usercomplement']->store()) {
			return true;
		} else {
			$this->db->setQuery("DELETE FROM #__core_acl_aro WHERE value = $user_id");
			$this->db->query();
			$this->db->setQuery("DELETE FROM #__core_acl_groups_aro_map WHERE aro_id = $aro_id");
			$this->db->query();
			$this->db->setQuery("DELETE FROM #__users WHERE id = $user_id");
			$this->db->query();
			if (pref('debug')) _warn("WARN", JText::_("Error creating user: ") . $this->tables['usercomplement']->getError());
			return false;
		}
		*/
	}

	/**
	 * Delete a user from main and complement table
	 *
	 * @param int $id the user id
	 * @return boolean
	 */
	function deleteUser($id) {
		$query = "DELETE FROM #__users WHERE id = $id";
		$this->db->setQuery($query);
		if ($this->db->query()) {
			$query = "DELETE FROM #__prenotown_user_complement WHERE id = $id";
			$this->db->setQuery($query);
			if ($this->db->query()) {
				return true;
			} else {
				if (pref('debug')) _warn("WARN", JText::_("Error removing user from complement table"));
				return false;
			}
		} else {
			if (pref('debug')) _warn("WARN", JText::_("Error removing user from main table"));
			return false;
		}
	}

	/**
	 * Save new values for user profile
	 */
	function updateProfile() {
		/* changing password */
		$password = JRequest::getString('password', '');
		$password2 = JRequest::getString('password2', '');

		if ($password && ($password == $password2)) {
			$salt  = JUserHelper::genRandomPassword(32);
			$password_hash = JUserHelper::getCryptedPassword($password, $salt) . ":" . $salt;

			$query = "UPDATE #__users SET password = " . $this->db->quote($password_hash) . " WHERE id = " . $this->_id;
			$this->db->setQuery($query);
			if (!$this->db->query()) {
				if (pref('debug')) _warn("WARN", JText::_("Error while setting password"));
				return false;
			}
		} else if ($password) {
			if (pref('debug')) JError::raiseNotice(500, JText::_("Your password has not been updated because of a mismatch"));
		}

		/* saving user name and email */
		$email = $this->db->quote(JRequest::getString('email', ''));
		$name = $this->db->quote(JRequest::getString('name',''));
		$query = "UPDATE #__users SET name = $name, email = $email, username = $email WHERE id = " . $this->_id;
		_log_sql($query);
		$this->db->setQuery($query);
		$this->db->query();

		/* saving user complement */
		$this->tables['usercomplement']->id = $this->_id;
		$this->tables['usercomplement']->load();

		$this->tables['usercomplement']->social_security_number = JRequest::getString('social_security_number', $this->tables['usercomplement']->social_security_number);
		$this->tables['usercomplement']->address = JRequest::getString('address', $this->tables['usercomplement']->address);
		$this->tables['usercomplement']->town = JRequest::getString('town', $this->tables['usercomplement']->town);
		$this->tables['usercomplement']->district = JRequest::getString('district', $this->tables['usercomplement']->district);
		$this->tables['usercomplement']->nationality = JRequest::getString('nationality', $this->tables['usercomplement']->nationality);
		$this->tables['usercomplement']->ZIP = JRequest::getInt('ZIP', $this->tables['usercomplement']->ZIP);
		$this->tables['usercomplement']->status = JRequest::getString('status', $this->tables['usercomplement']->status);

		$result = null;

		if ($this->tables['usercomplement']->check()) {
			if ($this->tables['usercomplement']->store()) {
				$result = true;
			} else {
				if (pref('debug')) _warn("WARN", $this->tables['usercomplement']->getError());
				$result = false;
			}
		} else {
			if (pref('debug')) _warn("WARN", $this->tables['usercomplement']->getError());
			$result = false;
		}

		return $result;
	}

	/**
	 * Return a list of groups the user is member of
	 */
	function getMyGroups() {
		$query = "SELECT * FROM #__prenotown_user_groups WHERE id IN (SELECT group_id FROM #__prenotown_user_group_entries WHERE user_id = $this->_id )";
		_log_sql($query);
		$this->db->setQuery($query);
		return $this->db->loadAssocList();
	}
}
?>
