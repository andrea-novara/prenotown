<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<h2><?php echo JText::_("User panel") ?></h1>
<style>
	div.extframe {
		width: 100%;
		/* border: 1px solid #ccc; */
		/* background-color: #ca8; */
		padding-top: 20px !important;
		padding-bottom: 20px !important;
		text-align: center;
		margin-bottom: 20px;
	}
</style>


<?php
	global $prenotown_user, $booking_user;
	if (!_status('user')) {
		forceLogin("Please login before accessing user panel");
	} else {
		if ($prenotown_user['id'] != $booking_user['id']) {
?>
<h2><?php echo JText::_("As") . " " . $booking_user['name'] ?></h2>

<div class="extframe">
<table style="width: auto; margin-left: auto; margin-right: auto;">
<tr>
<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=user&layout=modifyother&user_id=<?php echo $booking_user['id'] ?>&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "otheruserprofile.png" ?>"><br>
<?php echo JText::_("User profile") ?></a>
</td>

<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=resources&layout=tree&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "resources.png" ?>"><br>
<?php echo JText::_("Book a resource") ?></a>
</td>

<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=user&layout=currentbooking&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "booking.png" ?>"><br>
<?php echo JText::_("Current booking") ?></a>
</td>

</tr></table>
</div>

<h2><?php echo JText::_("User panel of") . ": " . $prenotown_user['name'] ?></h2>

<?php	} ?>

<?php
	# using iframe
	# $url = "index.php?option=com_prenotown&view=user&layout=crsmodify";

	# using direct link
	$url = base64_encode("index.php?option=com_prenotown&view=user");
	$url = JURI::base() . "index.php?option=com_prenotown&view=login&layout=redirect&format=raw&return=$url";
	$url = urlencode($url);
	$url = pref('profileUpdateUrl') . "?originator=prenotown&returnUrl=$url";
?>
<div class="extframe">
<table style="width: auto; margin-left: auto; margin-right: auto;">
<tr>
<td class="user-panel-task">
<a href="<?php echo $url ?>">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "userprofile.png" ?>"><br>
<?php echo JText::_("My profile") ?></a>
</td>

<?php if ( $prenotown_user['id'] == $booking_user['id'] ) { ?>
<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=resources&layout=tree&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "resources.png" ?>"><br>
<?php echo JText::_("Bookable resources") ?></a>
</td>

<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=user&layout=currentbooking&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "booking.png" ?>"><br>
<?php echo JText::_("Current booking") ?></a>
</td>
<?php } ?>

<?php if (_status('operator')) { ?>

<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=users&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "useridentity.png" ?>"><br>
<?php echo JText::_("User management") ?></a>
</td>

<?php } ?>

<td class="user-panel-task">
<?php if (_status('operator')) { ?>
<a target="_top" href="index.php?option=com_prenotown&view=groups&limit=20&limitstart=0">
<?php } else { ?>
<a target="_top" href="index.php?option=com_prenotown&view=user&layout=mygroups&limit=20&limitstart=0">
<?php } ?>
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "groupidentity.png" ?>"><br>
<?php echo JText::_("Group management") ?></a>
</td>

<tr>

<?php if (_status('operator')) { ?>

<!--
<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=users&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "userlisting.png" ?>"><br>
<?php echo JText::_("Manage users") ?></a>
</td>

<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=groups&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "grouplisting.png" ?>"><br>
<?php echo JText::_("Manage groups") ?></a>
</td>
-->

<?php } ?>

<?php if (_status("admin")) { ?>

<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=resource&layout=create&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "new_resource.png" ?>"><br>
<?php echo JText::_("Create resource") ?></a>
</td>

<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=resources&layout=myresources&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "edit_resource.png" ?>"><br>
<?php echo JText::_("Manage my resources") ?></a>
</td>

<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=resources&layout=approvallist&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "pendingbooking.png" ?>"><br>
<?php echo JText::_("Pending booking") ?></a>
</td>

<td class="user-panel-task">
<a href="index.php?option=com_prenotown&view=user&layout=globalbooking&limit=20&limitstart=0">
<img src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "global_booking.png" ?>"><br>
<?php echo JText::_("Global booking") ?></a>
</td>

</tr>
<?php } ?>

</table>
</div>

<?php if (_status('superadmin')) { ?>
<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=user&layout=applicationsettings')"><?php echo JText::_("Prenotown settings") ?></button>
</div>
<?php } ?>

<?php } ?>

