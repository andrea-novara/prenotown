<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<style>
	* { font-size: 10px; font-family: sans-serif; }
	table { width: 100px; }
	tr:hover td { background-color: #eef; }
	div.extframe {
		width: 100px;
		font-size: 10px !important;
		line-height: 10px;
	}

	div.extframe img { };

	div.user-panel-task {
	}
	div.user-panel-task img {
		width: 40px !important;
		background-color: #EEEEDD;
		border: 1px solid #ccc !important;
		margin: 1px !important;
		margin-right: 3px !important;
		float: left;
		vertical-align:top
	}
	a { text-decoration: none; vertical-align: middle; }
	img:hover, tr:hover img { background-color: #ffc }
	td { padding: 2px; }
	body { border-right: 1px solid #ccc; padding: 0px; }
	table { margin: 0px !important }
</style>
<?php
	function menu_entry($text, $img, $link) {
		$img = JURI::base() . "/components/com_prenotown/assets/$img"; 
		$translated = JText::_($text);
		echo '<tr>';
		echo "<td style=\"text-align:right\"><a target=\"_top\" href=\"$link\">$translated</a></td>";
		echo "<td><div class=\"user-panel-task\"><a target=\"_top\" href=\"$link\"><img title=\"$translated\" src=\"$img\"/></a></td>";
		echo "</tr>";
	}

	global $prenotown_user, $booking_user;
	echo '<div class="extframe"><table>';

	if (_status('user')) {
		if ($prenotown_user['id'] != $booking_user['id']) {
			menu_entry(JText::sprintf("User profile of %s", $booking_user['name']), "otheruserprofile.png", "index.php?option=com_prenotown&view=user&layout=modifyother&user_id=" . $booking_user['id']);
			menu_entry(JText::sprintf("Book a resource as %s", $booking_user['name']), "resources.png", "index.php?option=com_prenotown&view=resources&layout=tree&limit=20&limitstart=0"); 
			menu_entry(JText::sprintf("Current booking of %s", $booking_user['name']), "booking.png", "index.php?option=com_prenotown&view=user&layout=currentbooking&limit=20&limitstart=0");
			echo "<tr><td>&nbsp;</td></tr>";
		}
	}

	# using iframe
	# $url = "index.php?option=com_prenotown&view=user&layout=crsmodify";

	# using direct link
	$url = base64_encode("index.php?option=com_prenotown&view=user");
	$url = JURI::base() . "index.php?option=com_prenotown&view=login&layout=redirect&format=raw&return=$url";
	$url = urlencode($url);
	$url = pref('profileUpdateUrl') . "?originator=prenotown&returnUrl=$url";
	menu_entry("My profile", "userprofile.png", $url);

	if ( $prenotown_user['id'] == $booking_user['id'] ) {
		menu_entry("Book a resource", "resources.png", "index.php?option=com_prenotown&view=resources&layout=tree&limit=20&limitstart=0");
		menu_entry("Current booking", "booking.png", "index.php?option=com_prenotown&view=user&layout=currentbooking&limit=20&limitstart=0");
	}
	if (!_status('operator')) {
		if (_status('operator')) {
			menu_entry("Group management", "groupidentity.png", "index.php?option=com_prenotown&view=groups&limit=20&limitstart=0");
		} else {
			menu_entry("Group management", "groupidentity.png", "index.php?option=com_prenotown&view=user&layout=mygroups&limit=20&limitstart=0");
		}
	}

	if (_status('operator')) {
		menu_entry("Manage users", "useridentity.png", "index.php?option=com_prenotown&view=users&limit=20&limitstart=0");
		menu_entry("Manage groups", "groupidentity.png", "index.php?option=com_prenotown&view=groups&limit=20&limitstart=0");
	}

	if (_status("admin")) {
		menu_entry("Create resource", "new_resource.png", "index.php?option=com_prenotown&view=resource&layout=create&limit=20&limitstart=0");
		menu_entry("Manage my resources", "edit_resource.png", "index.php?option=com_prenotown&view=resources&layout=myresources&limit=20&limitstart=0");
		menu_entry("Pending booking", "pendingbooking.png", "index.php?option=com_prenotown&view=resources&layout=approvallist&limit=20&limitstart=0");
		menu_entry("Global booking", "global_booking.png", "index.php?option=com_prenotown&view=user&layout=globalbooking&limit=20&limitstart=0");
	}
	echo "</table></div>";
?>
