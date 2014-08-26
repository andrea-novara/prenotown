<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
	JHTML::_('behavior.modal');
?>
<script language="Javascript" type="text/javascript">
	function check_form() {
		if (document.getElementById('add_name').value == '') {
			alert("<?php echo JText::_("Please provide a name for this attachment") ?>");
			return;
		}

		if (document.getElementById('filename').value.length == 0) {
			alert("<?php echo JText::_("Please provide a filename") ?>");
			return;
		}

		document.getElementById('attachments-form').submit();
	}

	function deleteAttachment(id) {
		if (confirm('<?php echo JText::_("Do you really want to delete this attachment?") ?>')) {
			document.getElementById('attachment_id').value = id;
			document.getElementById('attachment-form').submit();
		}
		return false;
	}

	function jInsertEditorText(tag, editor) {
		var path = tag.substring(tag.search('"') + 1);
		path = path.substring(0, path.search('"'));
		// alert("Modal returned filename " + path);
		document.getElementById(editor).value = path;
	}
</script>
<h2><?php echo $this->name . ": " . JText::_("Resource attachments") ?></h1>
<form name="attachments-form" id="attachments-form" method="POST">
<input type="hidden" name="task" value="update_attachments"/>
<input type="hidden" name="attachment_id" value="" id="attachment_id"/>
<table class="hl" style="width: 100%" cellspacing=0 cellpadding=0>

<tr><td colspan=2><?php numbullet("Current attachments") ?></td></tr>
<tr><td class="left"></td><td>
<table>
	<thead>
		<th><?php echo JText::_("Name") ?></th>
		<th><?php echo JText::_("Filename") ?></th>
		<th></th>
	</thead>
<?php
	$at = $this->attachments->getByResource($this->id);
	foreach ($at as $a) {
		echo "<tr>";
		echo "<td>" . $a['name'] . "</td>";
		echo "<td>" . $a['filename'] . "</td>";
		echo "<td><button class=\"button\" onClick=\"deleteAttachment(" . $a['id'] . "); return false;\">" . JText::_("Remove attachment") . "</button></td>";
		echo "</tr>";
	}
?>
</table>
</td></tr>
<tr><td colspan=2><?php numbullet("Add an attachment") ?></td></tr>
<tr><td class="left"></td><td colspan="2">
	<table>
	<tr>
	<td style="align:right">
	<?php echo JText::_("Attachment name") ?>:
	</td>
	<td>
	<input name="add_name" id="add_name" value="<?php echo htmlspecialchars(JRequest::getString('add_name')) ?>" />
	</td>
	</tr>
	<tr><td style="align:right">
	<?php echo JText::_("Filename") ?>
	</td><td>
	<?php
		$link = 'index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;e_name=filename&amp;folder=prenotown/attachments';
	?>
	<input name="filename" id="filename" width=200 />&nbsp;<a class="modal"
		rel="{handler: 'iframe', size: {x: 700, y: 500}}" href="<?php echo $link ?>"><button><?php echo JText::_("Select an attachment") ?></button></a>
	</td></tr></table>
</td></tr>
</table>

<br>
<?php
	/** include parameters in form */
	require_once("url_params.php");
?>
</form>
<div class="button-footer">
<button class="button" onClick="check_form()"><?php echo JText::_("Attach") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=edit&id=<?php echo $this->id ?>')"><?php echo JText::_("Back to resource") ?></button>
</div>
