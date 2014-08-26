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
	div.extframe {
		width: 100px;
		/* border: 1px solid #ccc; */
		/* background-color: #ca8; */
		padding-top: 0px !important;
		padding-bottom: 20px !important;
		text-align: left;
		margin-bottom: 20px;
	}

	div.extframe img { };

	div.user-panel-task {
		display: inline;
		width: 60px !important;
	}
	div.user-panel-task img {
		width: 40px !important;
		background-color: #EEEEDD;
		border: 1px solid #ccc !important;
		margin: 1px !important;
	}
	div.user-panel-task img:hover { background-color: #ffc }
</style>
<?php
	global $prenotown_user, $booking_user;
	if (_status('user')) {
		if ($prenotown_user['id'] != $booking_user['id']) {
?>

<div class="extframe">
<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=user&layout=modifyother&user_id=<?php echo $booking_user['id'] ?>&limit=20&limitstart=0">
<img title="<?php echo JText::_("User profile") . ": " . $booking_user['name'] ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "otheruserprofile.png" ?>"><br>
</a>
</div>

<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=resources&layout=tree&limit=20&limitstart=0">
<img title="<?php echo JText::_("Book a resource as") . " " . $booking_user['name'] ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "resources.png" ?>"><br>
</a>
</div>

<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=user&layout=currentbooking&limit=20&limitstart=0">
<img title="<?php echo JText::_("Current booking of"). " " . $booking_user['name']  ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "booking.png" ?>"><br>
</a>
</div>
</div>

<?php	} ?>

<div class="extframe">
<div class="user-panel-task">
<?php
	# using iframe
	# $url = "index.php?option=com_prenotown&view=user&layout=crsmodify";

	# using direct link
	$url = base64_encode("index.php?option=com_prenotown&view=user");
	$url = JURI::base() . "index.php?option=com_prenotown&view=login&layout=redirect&format=raw&return=$url";
	$url = urlencode($url);
	$url = pref('profileUpdateUrl') . "?originator=prenotown&returnUrl=$url";
?>
<a target="_top" href="<?php echo $url ?>">
<img title="<?php echo JText::_("My profile") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "userprofile.png" ?>"><br>
</a>
</div>

<?php if ( $prenotown_user['id'] == $booking_user['id'] ) { ?>
<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=resources&layout=tree&limit=20&limitstart=0">
<img title="<?php echo JText::_("Bookable resources") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "resources.png" ?>"><br>
</a>
</div>

<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=user&layout=currentbooking&limit=20&limitstart=0">
<img title="<?php echo JText::_("Current booking") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "booking.png" ?>"><br>
</a>
</div>
<?php } ?>

<?php if (!_status('operator')) { ?>
<div class="user-panel-task">
<?php if (_status('operator')) { ?>
<a target="_top" href="index.php?option=com_prenotown&view=groups&limit=20&limitstart=0">
<?php } else { ?>
<a target="_top" href="index.php?option=com_prenotown&view=user&layout=mygroups&limit=20&limitstart=0">
<?php } ?>
<img title="<?php echo JText::_("Group management") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "groupidentity.png" ?>"><br>
</a>
</div>
<?php } ?>


<?php if (_status('operator')) { ?>

<!--
<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=user&layout=chooseidentity&limit=20&limitstart=0">
<img title="<?php echo JText::_("Act as a user") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "useridentity.png" ?>"><br>
</a>
</div>
-->

<?php } ?>

<!--
<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=user&layout=choosegroup&limit=20&limitstart=0">
<img title="<?php echo JText::_("Act as a group") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "groupidentity.png" ?>"><br>
</a>
</div>
-->

<?php if (_status('operator')) { ?>

<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=users&limit=20&limitstart=0">
<img title="<?php echo JText::_("Manage users") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "useridentity.png" ?>"><br>
</a>
</div>

<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=groups&limit=20&limitstart=0">
<img title="<?php echo JText::_("Manage groups") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "groupidentity.png" ?>"><br>
</a>
</div>
</div>

<?php } ?>

<?php if (_status("admin")) { ?>

<div class="extframe">
<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=resource&layout=create&limit=20&limitstart=0">
<img title="<?php echo JText::_("Create resource") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "new_resource.png" ?>"><br>
</a>
</div>

<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=resources&layout=myresources&limit=20&limitstart=0">
<img title="<?php echo JText::_("Manage my resources") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "edit_resource.png" ?>"><br>
</a>
</div>

<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=resources&layout=approvallist&limit=20&limitstart=0">
<img title="<?php echo JText::_("Pending booking") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "pendingbooking.png" ?>"><br>
</a>
</div>

<div class="user-panel-task">
<a target="_top" href="index.php?option=com_prenotown&view=user&layout=globalbooking&limit=20&limitstart=0">
<img title="<?php echo JText::_("Global booking") ?>" src="<?php echo JURI::base() . DS . "components" . DS . "com_prenotown" . DS . "assets" . DS . "global_booking.png" ?>"><br>
</a>
</div>

</div>

<?php } ?>


<?php } ?>
