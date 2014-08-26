<?php
/**
 * Global include file user_session.php
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

defined('_JEXEC') or die("Restricted Access");

define('DONT_INCLUDE_LIMIT', 0);
define('INCLUDE_LIMIT', 1);
define('EXCLUDE_ACTIONS', 1);

/** include logging */
require_once(JPATH_COMPONENT.DS."assets".DS."logging.php");

/* add javascript files */
$document =& JFactory::getDocument();
$document->addScript(JURI::base().DS."components".DS."com_prenotown".DS."assets".DS."prenotown.js");

/**
 * Automatically redirects to login view without loosing current session.
 * The login view is provided with a "return" parameter which contains the URL to which
 * the login view should redirect the user after a positive login. It's BASE64 encoded.
 *
 * @param string $message a message for the user
 * @param string $extra_params Additional parameters for return url, appended
 * @param string $type a string requesting special behavior:
 *   "direct": the login will happen in the main window
 *   "iframe": the login will happen in an iframe inside the main window
 *    "modal": the login will happen in an overlied modal window
 */
function forceLogin($message="", $extra_params="", $type=null) {
	_log('WARNING', "Unauth user accessing view " . JRequest::getString('view', 'unknown') . ":" . JRequest::getString('layout', 'default'));

	$xignon_enabled = false;
	if (JPluginHelper::isEnabled('authentication', 'xsec')) {
		error_log("Xsec xign-on is enabled");
		$xignon_enabled = true;
	}

	$uri =& JURI::getInstance();
	error_log("Current URI: " . $uri->toString());

	/* application framework */
	$application = &JFactory::getApplication();

	/* get joomla authentication token */
	$jtoken = JUtility::getToken();

	/* get the CRS authenticator */
	$token = JRequest::getString('token', '');

	/* base url */
	$base = JURI::base();

	/* final url */
	$auth_stack = preg_replace('/[0-9a-f]{32}=1/', '', $uri->toString());
	error_log("auth_stack: $auth_stack");

	if ($token) {
		$auth_stack .= "&token=$token&$jtoken=1";
		if ($xignon_enabled) {
			$auth_stack = "index.php?option=com_user&task=login&$jtoken=1&token=$token&return=" . base64_encode($auth_stack);
		} else {
			$auth_stack = "index.php?option=com_user&view=login&$jtoken=1&token=$token&return=" . base64_encode($auth_stack);
		}
		$application->redirect($auth_stack, ""); # JText::_($message));
		return;
	}

	/* check a valid behavior type */
	if (($type == null) or (!preg_match('/^direct|modal|iframe$/', $type))) {
		$pref_behavior = pref("loginBehavior");
		$type = $pref_behavior ? $pref_behavior : 'direct';
	}


	/* if modal or iframe type was requested, jump out of modal window before loading real resource */
	if ($type == "modal" or $type == "iframe") {
		$auth_stack = "${base}index.php?s=1&option=com_prenotown&view=login&layout=redirect&format=raw&$jtoken=1&return=" . base64_encode($auth_stack);
		error_log("auth_stack: $auth_stack");
	}

	/* the url that triggers the login stack */
	if ($xignon_enabled) {
		$auth_stack = "${base}index.php?s=2&option=com_user&task=login&$jtoken=2&return=" . base64_encode($auth_stack);
	} else {
		$auth_stack = "${base}index.php?s=2&option=com_user&view=login&$jtoken=2&return=" . base64_encode($auth_stack);
	}
	error_log("auth_stack: $auth_stack");

	/* add the token from the external authenticator if available */
	if ($token) {
		$auth_stack = preg_replace('/[&?]?token=[^&]+/', '', $auth_stack);
		if (preg_match('/\?/', $auth_stack)) {
			$auth_stack .= "&token=$token";
		} else {
			$auth_stack .= "?token=$token";
		}
		error_log("auth_stack: $auth_stack");
	}

	/* if modal type was requested, open a modal window before starting chatting with the CRS authenticator */
	if ($type == "modal") {
		$auth_stack = "${base}index.php?s=3&option=com_prenotown&view=login&$jtoken=1&layout=modal&return=" . base64_encode($auth_stack);
		error_log("auth_stack: $auth_stack");
	} else if ($type == "iframe") {
		$auth_stack = "${base}index.php?s=3&option=com_prenotown&view=login&$jtoken=1&layout=iframe&return=" . base64_encode($auth_stack);
		error_log("auth_stack: $auth_stack");
	}

	/* setting the redirect */
	error_log("forceLogin: redirecting to $auth_stack");
	$application->redirect($auth_stack, ""); # JText::_($message));
}

global $province;
$province = array(
	'AG', 'AL', 'AN', 'AO', 'AQ', 'AR', 'AP', 'AT', 'AV', 'BA', 'BT', 'BL', 'BN', 'BG', 'BI',
	'BO', 'BZ', 'BS', 'BR', 'CA', 'CL', 'CB', 'CE', 'CT', 'CZ', 'CH', 'CO', 'CS', 'CR', 'KR',
	'CN', 'EN', 'FM', 'FE', 'FI', 'FG', 'FC', 'FR', 'GE', 'GO', 'GR', 'IM', 'IS', 'SP', 'LT',
	'LE', 'LC', 'LI', 'LO', 'LU', 'MC', 'MN', 'MS', 'MT', 'ME', 'MI', 'MO', 'MB', 'NA', 'NO',
	'NU', 'OR', 'PD', 'PA', 'PR', 'PV', 'PG', 'PU', 'PE', 'PC', 'PI', 'PT', 'PN', 'PZ', 'PO',
	'RG', 'RA', 'RC', 'RE', 'RI', 'RN', 'Roma', 'RO', 'SA', 'SS', 'SV', 'SI', 'SR', 'SO', 'TA',
	'TE', 'TR', 'TO', 'TP', 'TN', 'TV', 'TS', 'UD', 'VA', 'VE', 'VB', 'VC', 'VR', 'VV', 'VI', 'VT'
);

/**
 * GLOBAL PROFILES:
 * 
 * @global array $prenotown_pref	Prenotown preferences
 * @global array $prenotown_user	the logged user
 * @global array $ghost_user    	the ghost user choosen by an operator
 * @global array $ghost_group   	the ghost group choosen by a user or an operator
 * @global array $booking_user  	reference to ghost_user if choosen, to prenotown_user otherwise
 * @global string $current_url   	the url of this request encoded in BASE64
 * @global array $bpw_tipi_carta	tipi di carte di credito riconosciuti da Bankpass
 * @global array $bpw_tipi_transazione	tipi di transazioni riconosciuti da Bankpass
 * @global stirng $cache_path		the path used to store booking availability images
 */
global $prenotown_debug_levels, $prenotown_pref, $prenotown_user, $ghost_user, $ghost_group, $booking_user, $current_url;
global $bpw_tipi_carta, $bpw_tipi_transazione;
global $cache_path;

$cache_path = "/tmp/prenotown";

$prenotown_debug_levels = array('DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL');

$prenotown_pref = array(
	/* application options */
	'debug',		# application level debugging
	'groupRetractTime',	# retract time threshold for groups

	/* CRS authenticator options */
	'registrationUrl',	# CRS authenticator registration URL
	'profileUpdateUrl',	# CRS authenticator profile update URL
	'loginBehavior',	# login behavior: direct, iframe, modal; see forceLogin()

	/* BankPass WEB options */
	'bpw_nome_negozio',	# bankpass Nome del negozio aggiunto agli ID di pagamento per generare
				#   gli identificativi di ordine (NUMORD)
	'bpw_valuta',		# bankpass VALUTA (978 for Euro)
	'bpw_url_pagamento',	# bankpass URL_PAGAMENTO
	'bpw_idnegozio',	# bankpass IDNEGOZIO
	'bpw_tcontab',		# bankpass TCONTAB (accounting type: D for "differita" or I for "immediata")
	'bpw_tautor',		# bankpass TAUTOR (authorization type: D for "differita" or I for "immediata")
	'bpw_emaileserc',	# bankpass EMAILESERC (where bankpass should address report mail)
	'bpw_options',		# bankpass OPTIONS ()
	'bpw_start_key',	# bankpass CHIAVE DI AVVIO
	'bpw_status_api_key',	# bankpass CHIAVE MESSAGGI EMESSI DAL PFE E PER API BACKOFFICE
	'bpw_urlback',		# bankpass URLBACK (abort URL)
	'bpw_urldone',		# bankpass URLDONE (successful URL)
	'bpw_urlms',		# bankpass URLMS (merchant store URL, where bankpass informs
				#   the merchant about successful transactions)
	'bpw_options_a',	# bankpass OPTIONS_A
	'bpw_options_b',	# bankpass OPTIONS_B
	'bpw_options_c',	# bankpass OPTIONS_C
	'bpw_options_d',	# bankpass OPTIONS_D
	'bpw_options_e',	# bankpass OPTIONS_E
	'bpw_options_i',	# bankpass OPTIONS_I

	'email_template_booking_creation',	# email templates
	'email_template_booking_creation_subject',
	'email_template_retract_booking',
	'email_template_retract_booking_subject',
	'email_template_add_exception',
	'email_template_add_exception_subject',
	'email_template_delete_exception',
	'email_template_delete_exception_subject',
	'email_template_confirm_booking',
	'email_template_confirm_booking_subject',
	'email_template_reject_booking',
	'email_template_reject_booking_subject',
	'email_template_booking_paid',
	'email_template_booking_paid_subject',
	'email_template_unavailability_created',
	'email_template_unavailability_created_subject',
	'email_template_unavailability_removed',
	'email_template_unavailability_removed_subject',
);

$prenotown_user = array('status' => 'unknown');

$bpw_tipi_carta = array(
	'01' => 'Visa',
	'02' => 'Mastercard',
	'04' => 'Maestro',
	'06' => 'Amex',
	'07' => 'Diners',
	'08' => 'JCB',
	'09' => 'PagoBancomat',
	'10' => 'Carta Aura'
);

$bpw_tipi_transazione = array(
	'TT01' => 'SSL',
	'TT04' => 'Bankpass Pagobancomat',
	'TT05' => 'Bankpass Carte di Credito',
	'TT06' => 'VBV',
	'TT07' => 'Secure Code',
	'TT08' => 'VBV Esercente',
	'TT09' => 'Secure Code Esercente',
	'TT10' => 'VBV Titolare non autenticato',
	'TT11' => 'Mail Order Telephone Order'
);

/**
 * If a ghost user has been choosen, returns the ID of that user,
 * otherwise return the ID provided as first argument, or that of
 * the authenticated user if argument was null.
 *
 * @param int $id a custom ID to be returned
 * @return int The ID of the ghost user, or the ID provided as $id,
 *   or the ID of the authenticated user, or 0 if the session is anonymous.
 */
function _user_id($id = 0) {
	global $booking_user;
	if (!$id) {
		$user =& JFactory::getUser();
		$id = $user->id;
	}
	$session =& JFactory::getSession();
	$uid = $session->get('ghost_user_id', 0, 'com_prenotown');
	if (!$uid) {
		$uid = $booking_user['id'] ? $booking_user['id'] : $id;
	}
	return $uid;
}

/**
 * Return the ghost group ID, if choosen, or group 1 (All)
 * 
 * @return int group id to be used to book
 */
function _group_id() {
	$session =& JFactory::getSession();
	return $session->get('ghost_group_id', 1, 'com_prenotown');
}

/**
 * Return the ghost user id, or zero if none
 * 
 * @return int the ID
 */
function _has_ghost_user() {
	$session =& JFactory::getSession();
	return $session->get('ghost_user_id', 0, 'com_prenotown');
}

/**
 * Return the ghost group id, or zero if none
 *
 * @return int the group ID
 */
function _has_ghost_group() {
	$session =& JFactory::getSession();
	return $session->get('ghost_group_id', 0, 'com_prenotown');
}

/**
 * Check user status for privileges. Status privileges works as a stack.
 * A superadmin is also an admin, which is also an operator, which is also
 * a user. I.E. if a bare user authenticates, the call _status('admin')
 * will return FALSE, but the call _status('user') will return true.
 * Otherwise, if a 'admin' authenticates, all the possible calls will return
 * true, with the only exception of _status('superadmin'), which is of
 * course FALSE.
 *
 * @param string $base The minimum status to be reached to return true.
 * @return boolean
 */
function _status($base) {
	global $prenotown_user;

	$user =& JFactory::getUser();
	// error_log("User " . $user->id . " has status [" . $prenotown_user['status'] . "]");

	// JError::raiseNotice(100, "Status is " . $prenotown_user['status']);

	switch ($base) {
		case 'user':
			if ($prenotown_user['status'] == 'user') {
				return true;
			}
		case 'operator':
			if ($prenotown_user['status'] == 'operator') {
				return true;
			}
		case 'admin':
			if ($prenotown_user['status'] == 'admin') {
				return true;
			}
		case 'superadmin':
			if ($prenotown_user['status'] == 'superadmin') {
				return true;
			}
		default:
			return false;
	}
}

/**
 * Load logged user informations and provided top popup
 * to inform about used identity
 */
function _init_prenotown_user_session() {
	global $prenotown_user, $ghost_user, $ghost_group, $booking_user, $current_url;

	// save current URL in BASE64 encoding
	$current_url = isset($_SERVER['HTTPS']) ? "https://" : "http://";
	$current_url .= $_SERVER['HTTP_HOST'] . ":" . $_SERVER['SERVER_PORT'];
	$current_url .= $_SERVER['REQUEST_URI'];
	$current_url = base64_encode($current_url);

	// modal behavior for identity changing
	$params = array('size' => array('x' => 500, 'y' => 300));
	JHTML::_('behavior.modal', 'a.modal', $params);

	$session =& JFactory::getSession();
	$db =& JFactory::getDBO();
	$user =& JFactory::getUser();

	$ghost_user_id = $session->get('ghost_user_id', 0, 'com_prenotown');
	$ghost_group_id = $session->get('ghost_group_id', 0, 'com_prenotown');

	if ($user->id) {
		$query = "SELECT * FROM #__users JOIN #__prenotown_user_complement ON #__users.id = #__prenotown_user_complement.id WHERE #__users.id = " . $user->id;
		// JError::raiseNotice(100, preg_replace('/#_/', 'jos', $query));
		$db->setQuery($query);
		$prenotown_user = $db->loadAssoc();
	}

	$ghost_user = null;
	$ghost_group = null;

	if ($ghost_user_id) {
		$query = "SELECT * FROM #__users JOIN #__prenotown_user_complement ON #__users.id = #__prenotown_user_complement.id WHERE #__users.id = $ghost_user_id";
		$db->setQuery($query);
		$ghost_user = $db->loadAssoc();
		$booking_user = $ghost_user;
	} else {
		$booking_user = $prenotown_user;
	}

	if ($ghost_group_id) {
		$query = "SELECT * FROM #__prenotown_user_groups WHERE #__prenotown_user_groups.id = $ghost_group_id";
		$db->setQuery($query);
		$ghost_group = $db->loadAssoc();
	}

	$db->setQuery("DELETE FROM #__prenotown_superbooking_exception WHERE exception_date = '0000-00-00 00:00:00'");
	$db->query();
}

/**
 * Prompt some notice to the user informing about choosen ghost user and group
 * To be used inside views only.
 */
function _ghost_popup() {
	global $prenotown_user, $ghost_user, $ghost_group, $booking_user, $current_url;
	$db =& JFactory::getDBO();

	$session =& JFactory::getSession();
	$ghost_user_id = $session->get('ghost_user_id', 0, 'com_prenotown');
	$ghost_group_id = $session->get('ghost_group_id', 0, 'com_prenotown');

	if ($ghost_user_id) {
		$p = array('task' => 'forget_user_identity');
		$p['view'] = JRequest::getString('view', 'groups');
		$p['layout'] = JRequest::getString('layout', 'default');
		$p['id'] = JRequest::getInt('id', 0);
		$p['resource_id'] = JRequest::getInt('resource_id', 0);
		$p['booking_id'] = JRequest::getInt('booking_id', 0);
		$cease_url = auto_url($p);

		JError::raiseNotice(500, '<div style="float:right">[<a href="index.php?option=com_prenotown&view=users">' . JText::_("Change") . '</a>] [<a href="' . $cease_url . '">' . JText::_("Cease") . '</a>]</div>' . JText::sprintf('Acting as user %s (SSN: %s)', $booking_user['name'], $ghost_user['social_security_number']));
	}

	if ($ghost_group_id) {
		$p = array('task' => 'forget_group_identity');
		$p['view'] = JRequest::getString('view', 'groups');
		$p['layout'] = JRequest::getString('layout', 'default');
		$p['id'] = JRequest::getInt('id', 0);
		$p['resource_id'] = JRequest::getInt('resource_id', 0);
		$p['booking_id'] = JRequest::getInt('booking_id', 0);
		$cease_url = auto_url($p);
		
		if (_status('operator')) {
			JError::raiseNotice(500, '<div style="float:right">[<a href="index.php?option=com_prenotown&view=groups">' . JText::_("Change") . '</a>] [<a href="' . $cease_url . '">' . JText::_("Cease") . '</a>]</div>' . JText::sprintf('Acting as group %s', $ghost_group['name']));
		} else {
			JError::raiseNotice(500, '<div style="float:right">[<a href="index.php?option=com_prenotown&view=user&layout=mygroups">' . JText::_("Change") . '</a>] [<a href="' . $cease_url . '">' . JText::_("Cease") . '</a>]</div>' . JText::sprintf('Acting as group %s', $ghost_group['name']));
		}
	}

	// check if ghost user is member of ghost group
	if ($ghost_user_id and $ghost_group_id) {
		$sql = "SELECT COUNT(*) FROM #__prenotown_user_group_entries WHERE user_id = $ghost_user_id AND group_id = $ghost_group_id";
		$db->setQuery($sql);
		$is_member = $db->loadResult();
		if (!$is_member) {
			JError::raiseWarning(500, JText::_("User is not member of selected group")); 
		}
	}

	/*
	$user =& JFactory::getUser();
	if ($user->id) {
		JError::raiseNotice(500, JText::sprintf("Welcome %s (user id %08d)", $prenotown_user['name'], $prenotown_user['id']));
	}
	*/
}

/**
 * Implode a set of SQL values using proper quoting
 * method, returning a properly formatted string
 *
 * @param array $values a set of unquoted SQL values
 * @return string
 */
function querymplode($values) {
	$db =& JFactory::getDBO();
	$a = array();
	foreach ($values as $v) {
		$a[] = $db->quote($v);
	}
	return implode(",", $a);
}

/**
 * Draw a "numbering bullet" on the left
 */
function numbullet($title = "") {
	static $number = 1;
	echo "<div class=\"numbulletframe\"><div class=\"numbullet\">$number</div>" . JText::_($title) . "</div>";
	$number++;
}

/**
 * Manage preferences
 *
 * @param string $preference the name (key) of the preference to set or retrieve
 * @param string $value the (optional) value; if provided, will be updated inside the db
 */
function pref($preference, $value=null)
{
	# internal store for preferences
	static $preferences = array();

	# special case
	if (_status('admin')) {
		if ($preference == 'debug') { return true; }
	}

	# get DB handle
	$db =& JFactory::getDBO();

	# if value is not null, save it
	if (isset($value)) {
		if ($preference == "bpw_nome_negozio") {
			$value = preg_replace("/\s/", "_", $value);
		}

		$sql = "SELECT preference FROM #__prenotown_preferences WHERE preference = " . $db->quote($preference);
		$db->setQuery($sql);
		$exists = $db->loadResult();

		if ($exists == $preference) {
			$sql = "UPDATE #__prenotown_preferences SET value = " . $db->quote($value) . " WHERE preference = " . $db->quote($preference);
		} else {
			$sql = "INSERT INTO #__prenotown_preferences (preference, value)  VALUES (" . $db->quote($preference) . ", " . $db->quote($value) . ")";
		}

		_log_sql($sql);
		$db->setQuery($sql);
		$db->query();

		$preferences[$preference] = $value;
		if (isset($preferences['debug']) and $preferences['debug']) {
			JError::raiseNotice(500, JText::sprintf("Preference %s set to %s", $preference, $value));
		}
	}

	# cache the value for further accesse
	if (!isset($preferences[$preference])) {
		$sql = "SELECT value FROM #__prenotown_preferences WHERE preference = " . $db->quote($preference);
		_log_sql($sql);
		$db->setQuery($sql);
		$preferences[$preference] = $db->loadResult();
	}

	return $preferences[$preference];
}

function check_limit($href) {
	$href = preg_replace('/limit=(\d+)"/', "limit=$1&limitstart=0\"", $href);
	if (!preg_match('/limitstart/', $href)) {
		$href .= "&limitstart=0";
	}
	return $href;
}

function pagination($model, $colspan=1, $params=null) {
	if (!$model) {
		return "";
	}

	if ($colspan) {
		echo '<tfoot><tr><td colspan="'.$colspan.'">';
	}
	echo '<form>';
	echo '<input type="hidden" name="option" value="com_prenotown"/>';
	echo '<input type="hidden" name="view" value="' . JRequest::getString('view') . '"/>';
	echo '<input type="hidden" name="layout" value="' . JRequest::getString('layout', 'default') . '"/>';
	echo '<input type="hidden" name="limitstart" value="' . $model->getState('limitstart') . '"/>';

	$url_appender = "";
	if ($params != null) {
		foreach ($params as $name => $value) {
			echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'"/>';
			$url_appender .= "&$name=$value";
		}
	}

	$pag = $model->getPagination()->getListFooter();
	$pag = preg_replace('/href="([^"]+)"/e', "'href=\"' . check_limit('$1') . '$url_appender\"'", $pag);

	echo $pag;
	echo '</form>';

	if ($colspan) {
		echo '</td></tr></tfoot>';
	}
}

function esc_query($sql) {
	return preg_replace('/[\'";=<>]/', '', $sql);
}

function date_sql_to_human($date) {
	return preg_replace('/(\d\d\d\d)-(\d\d?)-(\d\d?)/', '$3-$2-$1', $date);
}

function date_human_to_sql($date) {
	return preg_replace('/(\d\d?)-(\d\d?)-(\d\d\d\d)/', '$3-$2-$1', $date);
}

function float_point_to_comma($cost) {
	return preg_replace('/\./', ',', $cost);
}

function float_comma_to_point($cost) {
	return preg_replace('/,/', '.', $cost);
}

function expand_periodicity($periodicity = 0) {
	$periodicity_list = array();

	if (!$periodicity) {
		$periodicity_list[] = JText::_("Single");
		return $periodicity_list;
	}
	
	if ($periodicity & 1) {
		$periodicity_list[] = JText::_("Monday");
	}

	if ($periodicity & 2) {
		$periodicity_list[] = JText::_("Tuesday");
	}

	if ($periodicity & 4) {
		$periodicity_list[] = JText::_("Wednesday");
	}

	if ($periodicity & 8) {
		$periodicity_list[] = JText::_("Thursday");
	}

	if ($periodicity & 16) {
		$periodicity_list[] = JText::_("Friday");
	}

	if ($periodicity & 32) {
		$periodicity_list[] = JText::_("Saturday");
	}

	if ($periodicity & 64) {
		$periodicity_list[] = JText::_("Sunday");
	}

	return $periodicity_list;
}

function resource_booking_availability_cache_path($id) {
	global $cache_path;
	return "$cache_path/resource-availability-$id.png";
}

_init_prenotown_user_session();

function format_booking_period($profile) {
	// check incoming data
	if (isset($profile['begin']) && (!isset($profile['begin_date']) || !isset($profile['begin_time']))) {
		list($profile['begin_date'], $profile['begin_time']) = preg_split("/ /", $profile['begin']);
	}
	if (isset($profile['end']) && (!isset($profile['end_date']) || !isset($profile['end_time']))) {
		list($profile['end_date'], $profile['end_time']) = preg_split("/ /", $profile['end']);
	}

	// format begin and end time
	list($h, $m) = preg_split("/:/", $profile['begin_time']);
	$profile['begin_time'] = sprintf("%02d:%02d", $h, $m);

	list($h, $m) = preg_split("/:/", $profile['end_time']);
	$profile['end_time'] = sprintf("%02d:%02d", $h, $m);

	if (!isset($profile['is_periodic'])) {
		$profile['is_periodic'] = false;
	}

	// recheck incoming data
	foreach (array('begin_date', 'begin_time', 'end_date', 'end_time') as $key) {
		if (!isset($profile[$key])) {
			return JText::_("Profilo di tempo inconsistente");
		}
	}

	// return the result
	if ($profile['begin_date'] == $profile['end_date']) {
		return JText::sprintf("From %s up to %s on %s", $profile['begin_time'], $profile['end_time'], date_sql_to_human($profile['begin_date']));
	} else if ($profile['is_periodic']) {
		return JText::sprintf("From day %s up to day %s, from %s up to %s", date_sql_to_human($profile['begin_date']), date_sql_to_human($profile['end_date']), $profile['begin_time'], $profile['end_time']);
	} else {
		return JText::sprintf("From %s on %s up to %s on %s", $profile['begin_time'], date_sql_to_human($profile['begin_date']), $profile['end_time'], date_sql_to_human($profile['end_date']));
	}
}

function format_booking($profile,$add_table=1) {
	$path = JURI::base().DS."components".DS."com_prenotown".DS."assets".DS;

	$clean_profile = array(
		'begin_time' => '', 'end_time' => '', 'begin_date' => '', 'end_date' => '',
		'user_name' => '', 'user_address' => '', 'resource_name' => '', 'resource_address' => '',
		'user_ssn' => '', 'booking_id' => '', 'periodicity' => JText::_("Single booking"),
		'cost' => '', 'exceptions' => array(), 'actions' => array(), 'exclude_actions' => 0
	);

	foreach($profile as $k => $v) {
		$clean_profile[$k] = $profile[$k];
	}

	$clean_profile['user_ssn'] = preg_replace('/(...)(...)(.....)(.....)/', "$1 $2 $3 $4", $clean_profile['user_ssn']);
	$clean_profile['is_periodic'] = $clean_profile['periodicity'] == JText::_("Single booking") ? 0 : 1;

	$trange = format_booking_period($clean_profile);

	if ($add_table) echo '<table cellspacing="0" class="booking">';
	echo '<tr><td class="booking-intro"><img src="' . $path . 'ticket.png"><br/><span title="ID. della prenotazione">' . $clean_profile['booking_id'] . '</span></td>';
    	echo '<td class="booking-resource">' . $clean_profile['resource_name'] . " " . $clean_profile['resource_address'] . '</td>';
    	echo '<td class="booking-user">';
	if (isset($clean_profile['group_name']) && $clean_profile['group_name'] != "All") {
		echo '<span title="' . $clean_profile['user_name'] . ' - ' . $clean_profile['user_ssn'] . ' - ' . $clean_profile['user_address'] . '"><b>' . $clean_profile['group_name'] . "</b><hr>" . $clean_profile['user_name'] . '</span>';
	} else {
		echo '<span title="' . $clean_profile['user_name'] . ' - ' . $clean_profile['user_address'] . '">' . $clean_profile['user_name'] . " - " . $clean_profile['user_ssn'] . "</span>";
	}
	echo '</td>';
    	echo '<td class="booking-time">' . $trange . '</td>';
    	echo '<td class="booking-periodicity">' . $clean_profile['periodicity'] . '</td>';
	if (preg_match("/[^\d.]/", $clean_profile['cost']) || preg_match("/&euro;/", $clean_profile['cost'])) {
    		echo '<td class="booking-cost">' . float_point_to_comma($clean_profile['cost']) . '</td></tr>';
	} else {
    		echo '<td class="booking-cost">' . float_point_to_comma($clean_profile['cost']) . '&euro;</td></tr>';
	}

	for ($i = 0; $i < count($clean_profile['exceptions']); $i++) {
		$clean_profile['exceptions'][$i] = date_sql_to_human($clean_profile['exceptions'][$i]);
	}
	$exceptions = implode(" - ", $clean_profile['exceptions']);
	if (strlen($exceptions)) {
		echo "<tr><td colspan=\"6\" style=\"border-top: 1px solid #dadada !important; text-align:left\"><b>" . JText::_("Exceptions") . ":</b> ";
		echo "$exceptions</td></tr>";
	}

	if ($clean_profile['booking_id']) {
		if (($clean_profile['periodicity'] != JText::_('Single booking')) or ($clean_profile['payment_date'] != '0000-00-00 00:00:00')) {
			$clean_profile['actions'][] = '<button class="button" onClick="redirect(\'index.php?option=com_prenotown&view=user&layout=ticket&format=raw&booking_id=' . $clean_profile['booking_id'] . '\')">' . JText::_("Print booking ticket") . "</button>";
		}
	}

	if ($clean_profile['actions'] and !$clean_profile['exclude_actions']) {
		echo "<tr><td class=\"booking-actions\" colspan=\"6\" style=\"border-top: 1px solid #dadada !important; text-align: right\">";
		echo '<div style="display: block">';
		echo implode("&nbsp;&nbsp;|&nbsp;&nbsp;", $clean_profile['actions']);
		echo "</div></td></tr>";
	}

	if ($add_table) echo '</table>';
}

function format_booking_by_id($booking_id=0, $actions=array(), $exclude_actions=0)
{
	global $ghost_group, $booking_user;

	if (!$booking_id) return;
	$booking_id = (int) $booking_id;
	if (!$booking_id) return;

	// load booking data
	$db =& JFactory::getDBO();
	$sql = "SELECT #__prenotown_superbooking.*, #__prenotown_payments.date AS payment_date, #__users.name AS user_name, #__users.email AS user_email, #__prenotown_user_complement.address AS user_address, #__prenotown_user_complement.social_security_number AS user_ssn, #__prenotown_user_groups.name AS group_name, #__prenotown_resource.name AS resource_name, #__prenotown_resource.address AS resource_address FROM #__prenotown_superbooking JOIN #__users ON #__users.id = #__prenotown_superbooking.user_id JOIN #__prenotown_user_complement ON #__prenotown_user_complement.id = #__prenotown_superbooking.user_id JOIN #__prenotown_user_groups ON #__prenotown_user_groups.id = #__prenotown_superbooking.group_id JOIN #__prenotown_resource ON #__prenotown_resource.id = #__prenotown_superbooking.resource_id JOIN #__prenotown_payments ON #__prenotown_payments.id = #__prenotown_superbooking.payment_id WHERE #__prenotown_superbooking.id = $booking_id";
	_log_sql($sql);
	$db->setQuery($sql);
	$profile = $db->loadAssoc();
	$profile['booking_id'] = $booking_id;

	/* format dates and times */
	list($profile['begin_date'], $profile['begin_time']) = preg_split("/ /", $profile['begin']);
	list($profile['end_date'], $profile['end_time']) = preg_split("/ /", $profile['end']);
	$profile['begin_time'] = preg_replace("/:\d\d$/", '', $profile['begin_time']);
	$profile['end_time'] = preg_replace("/:\d\d$/", '', $profile['end_time']);

	/* booking periodicity */
	if ($profile['periodic']) {
		$profile['periodicity'] = JText::_("Every") . " " . implode(", ", expand_periodicity($profile['periodicity']));

		/* load the exceptions */
		$sql = "SELECT exception_date FROM jos_prenotown_superbooking_exception WHERE booking_id = $booking_id ORDER BY exception_date";
		_log_sql($sql);
		$db->setQuery($sql);
		$profile['exceptions'] = $db->loadResultArray();
	} else {
		$profile['periodicity'] = JText::_("Single booking");
		$sql = "SELECT #__prenotown_day_bitmask('" . date_human_to_sql($profile['begin_date']) . "', '" . date_human_to_sql($profile['end_date']) . "')";
		_log_sql($sql);
		$db->setQuery($sql);
		$periodicity = $db->loadResult();
		$profile['periodicity'] .= " - " . implode(", ", expand_periodicity($periodicity));
	}
	
	// get the cost
	$db->setQuery("CALL #__prenotown_expand_booking($booking_id, @cost, 1)");
	$db->query();
	$db->setQuery("SELECT @cost");
	$profile['cost'] = sprintf("%.2f", $db->loadResult());

	// add the actions
	$profile['actions'] = $actions;
	$profile['exclude_actions'] = $exclude_actions;

	// print it
	format_booking($profile);

	// return the cost
	return $profile['cost'];
}

#
# build current url changing provided parameters
#
function auto_url($options = array()) {
	$url = JRequest::getUri();
	foreach ($options as $k => $v) { $url = preg_replace("/$k=[^\&]+/", "", $url); }
	foreach ($options as $k => $v) { if (isset($v) and $v) { $url .= "&$k=$v"; } }
	$url = preg_replace("/&&/", "&", $url);
	$url = preg_replace("/\/&/", "/?", $url);
	if (pref('debug')) _log("INFO", "auto_url => $url");
	return $url;
}


?>
