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
 * #__prenotown_user_complement table handler
 *
 * @package Prenotown
 * @subpackage Tables
 */
class TableUserComplement extends JTable
{
	/** @var int Primary Key */
	var $id = null;

	/** @var enum status */
	var $status = null;

	/** @var string */
	var $social_security_number = null;

	/** @var string */
	var $address = null;

	/** @var varchar */
	var $town = null;

	/** @var varchar */
	var $district = null;

	/** @var int(5) unsigned */
	var $ZIP = null;

	/** @var varchar */
	var $nationality = null;

	/** @var session_id */
	var $session_id = null;

	/**
	 * Constructor
	 *
	 * @param database Database object
	 */
	function __construct( &$db )
	{
		parent::__construct('#__prenotown_user_complement', 'id', $db);
	}

	function __toString()
	{
		return "PrenotownTableUserComplement";
	}

	/**
	 * Validation
	 *
	 * @return boolean True if buffer is valid
	 */
	function check()
	{
		$error = JText::_("Missing fields: ");
		$missing = array();

		if ($this->id) {
			if ($this->status || $this->social_security_number || $this->address || $this->nationality || $this->ZIP || $this->town || $this->district) {
				return true;
			} else {
				return false;
			}
		}
		if ($this->status && !preg_match('/user|operator|admin|superadmin/', $this->status)) {
			$this->setError(JText::_("Invalid status provided. Must be one of: user, operator, admin, superadmin"));
			$this->status = null;
		}
		if (!$this->social_security_number) {
			$this->setError(JText::_("No social security number provided"));
			return false;
		} else {
			$this->social_security_number = strtoupper($this->social_security_number); // go uppercase
			$this->social_security_number = preg_replace('/[ 	]/', '', $this->social_security_number); // no spaces or tabs
		}
		if (!$this->address) {
			$missing[] = JText::_("address");
		}
		if (!$this->town) {
			$missing[] = JText::_("town");
		}
		if (!$this->district) {
			$missing[] = JText::_("district");
		}
		if (!$this->ZIP) {
			$missing[] = JText::_("ZIP");
		}
		if (!$this->nationality) {
			$missing[] = JText::_("nationality");
		}

		if (count($missing)) {
			JError::raiseNotice(500, $error . implode(', ', $missing));
		}

		return true;
	}
}
?>
