<?php
/**
 * @package Prenotown
 * @subpackage Tables
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** ensure a valid entry point */
defined('_JEXEC') or die("Restricted Access");

/**
 * #__prenotown_users table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableUsers extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var string user first and last name */
	var $name = null;

	/** @var string user login name */
	var $username = null;

	/** @var string user email */
	var $email = null;

	/** @var string password */
	var $password = null;

	/** @var string Joomla user type */
	var $usertype = null;

	/** @var int */
	var $block = 0;

	/** @var int */
	var $sendEmail = 0;

	/** @var int unsigned */
	var $gid = 0;

	/** @var datetime */
	var $registerDate = null;

	/** @var datetime */
	var $lastvisitDate = null;

	/** @var string activation key issued by Joomla */
	var $activation = null;

	/** @var string user parameters */
	var $params = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__users', 'id', $db);
	}

	function __toString()
	{
		return "PrenotownTableUsers";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		/* check only if not updating */
		if (isset($this->id) and $this->id) {
		} else {
			if (!$this->name) {
				$this->setError(JText::_("No name provided"));
				return false;
			}
			if (!$this->username) {
				$this->setError(JText::_("No username provided"));
				return false;
			}
			if (!$this->email) {
				$this->setError(JText::_("No email provided"));
				return false;
			}
			if (!$this->password) {
				$this->setError(JText::_("No password hash provided"));
				return false;
			}
			if (!$this->block) {
				$this->block = 0;
			}
			if (!$this->sendEmail) {
				$this->sendEmail = 0;
			}
			if (!$this->usertype) {
				$this->usertype = 'Registered';
			}
			if (!$this->gid) {
				$this->gid = 18;
			}
			if (!$this->registerDate) {
				$db = $this->getDBO();
				$db->setQuery("SELECT NOW()");
				$this->registerDate = $db->loadResult();
			}
			if (!$this->lastvisitDate) {
				$this->lastvisitDate = "0000-00-00 00:00:00";
			}
			if (!$this->activation) {
				$this->activation = '';
			}
		}
	
		return true;
	}
}
?>
