<?php
/**
 * Global include file logging.php
 *
 * Update user session by loading user informations (both authenticated and ghosted user)
 * Provides authorization code.
 * Popups informations about ghost user and group.
 *
 * @package Prenotown
 * @subpackage Includes
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** ensure valid entry point */
defined('_JEXEC') or die("Restricted Access");

jimport('joomla.error.log');

/**
 * Log an SQL statement in file log/sql.php
 *
 * @param string $query The SQL statement
 */
function _log_sql($query) {
	$options = Array('format' => '{DATE} {TIME} - {C-IP}: {COMMENT}');
	$logger =& JLog::getInstance('sql.php', $options, JPATH_ROOT . DS . "log");
	$msg = trim(preg_replace('/[\n\t]/', ' ', $query));
	$msg = preg_replace('/ +/', ' ', $msg);

	$trace=debug_backtrace();
	$caller=array_shift($trace);
	$caller=array_shift($trace);

	while (preg_match('/pref|loadAssoc|loadAssoc|loadResult|query/', $caller['function'])) {
		$caller=array_shift($trace);
	}

	if (isset($caller['class'])) {
		$msg = $caller['class'] . "::" . $caller['function'] . ": $msg";
	} else {
		$msg = $caller['function'] . ": $msg";
	}

	$logger->addEntry(array('comment' => preg_replace('/#__/', 'jos_', $msg)));
}

/**
 * Log a message to file log/log.php with a status code
 *
 * @param string $status User defined status code, like "OK" or "WARNING"
 * @param string $message Line to be logged
 */
function _log($status, $message="") {
	$options = Array('format' => '{DATE} {TIME} - {C-IP}: [{STATUS}] {COMMENT}');
	$logger =& JLog::getInstance('log.php', $options, JPATH_ROOT . DS . "log");
	$logger->addEntry(array('status' => $status, 'comment' => $message));
}

function _warn($status, $message="") {
	global $prenotown_user;	
	$msg = $prenotown_user['name'] . ": " . JText::_($message);

	_log($status, $msg);
	JError::raiseWarning(500, JText::_($message));
}

function _error($status, $message="") {
	global $prenotown_user;	
	$msg = $prenotown_user['name'] . ": " . JText::_($message);

	_log($status, $msg);
	JError::raiseError(500, JText::_($message));
}

function _notice($status, $message="") {
	global $prenotown_user;	
	$msg = $prenotown_user['name'] . ": " . JText::_($message);

	_log($status, $msg);
	JError::raiseNotice(500, JText::_($message));
}

?>
