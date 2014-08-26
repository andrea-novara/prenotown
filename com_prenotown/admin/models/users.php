<?php
/**
 * @package Prenotown
 * @subpackage Models
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** ensure a valid entry point */
defined('_JEXEC') or die('Restricted Access');

/** import the JModel class */
jimport('joomla.application.component.model');

/** import the code to paginate list of elements */
jimport('joomla.html.pagination');

/** import logging facilities */
require_once(JPATH_COMPONENT_SITE . DS . "assets" . DS . "logging.php");
require_once(JPATH_COMPONENT_SITE . DS . "assets" . DS . "user_session.php");
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . "models" . DS . "prenotowns.php");

/**
 * Users model
 *
 * @package Prenotown
 * @subpackage Models
 */
class PrenotownModelUsers extends PrenotownModelPrenotowns
{
	/**
	 * Constructor, builds object
	 *
	 */
	function __construct() {
		global $mainframe, $option;

		parent::__construct();

		$this->setTableName('#__users');
		$this->setSortableFields(array(
			'id', 'name', 'username', 'email', 'usertype', 'gid', 'registerDate',
			'lastvisitDate', 'status', 'social_security_number', 'address',
			'town', 'district', 'nationality', 'ZIP'
		));
		$this->setFilterField('name');
		$this->setOrderingField('name');
		$this->setDefaultQuery("SELECT %%%TABLE_NAME%%%.*, #__prenotown_user_complement.status, UCASE(#__prenotown_user_complement.social_security_number) AS social_security_number, #__prenotown_user_complement.address, #__prenotown_user_complement.ZIP, #__prenotown_user_complement.town, #__prenotown_user_complement.district, #__prenotown_user_complement.nationality, concat(#__prenotown_user_complement.address, ', ', #__prenotown_user_complement.ZIP, ' ', #__prenotown_user_complement.town) AS full_address FROM %%%TABLE_NAME%%% JOIN #__prenotown_user_complement ON %%%TABLE_NAME%%%.id = #__prenotown_user_complement.id");
	}

	function __tostring()
	{
		return "PrenotownModelUsers";
	}
}
?>
