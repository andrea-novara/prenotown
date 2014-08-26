<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	$style = <<<EOF
	#resource-list {
		list-style-image: url("<?php echo $resources_image ?>");
		list-style: none;
	}
EOF;

	$document =& JFactory::getDocument();
	$document->addStyleDeclaration($style);
?>
<h2><?php echo JText::_("Resources map") ?></h1>
<?php
	$resource_category = JRequest::getInt('resource_category', 0);
	$resource_name = JRequest::getString('resource_name', '');
	$resource_address = JRequest::getString('resource_address', '');
	$resource_keywords = JRequest::getString('resource_keywords', '');
	$total_results = 0;
?>

<div style="float:right; border: 1px solid #000; background-color: #e9e9e9; margin-left: 20px;">
<div class="boxheader">
<b><?php echo JText::_("Search:") ?></b>
</div>
<div style="padding: 10px">
<form method="POST">
<table style="margin-left: auto; margin-right: auto;" class="hl">
<tr><td class="left"><?php echo JText::_("Resource name") ?></td></tr><tr><td><input name="resource_name" value="<?php echo htmlspecialchars($resource_name) ?>"></td></tr>
<tr><td class="left"><?php echo JText::_("Address") ?></td></tr><tr><td><input name="resource_address" value="<?php echo htmlspecialchars($resource_address) ?>"></td></tr>
<!--
<tr><td class="left"><?php echo JText::_("Category") ?></td></tr><tr><td><select name="resource_category">
<option value=""><?php echo JText::_("All") ?></option>
<?php
	#
	# Load categories from the DB
	#
	$categories = $this->resourceGroups->getData();

	foreach ($categories as $category) {
		if ((int) $resource_category == $category['id']) {
			echo '<option value="' . $category['id'] . '" selected>' . $category['name'] . '</option>';
		} else {
			echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
		}
	}
?>
</select></td></tr>
-->
<tr><td colspan="1" style="text-align:center"><input class="button" type="submit" value="<?php echo JText::_("Search") ?>"></td></tr>
</table>
</form>
</div>
</div>

<?php
	$resources_image = JURI::base() . "/components/com_prenotown/assets/resources.png";
	if ($resource_category) {
		foreach ($categories as $category) {
			if ($resource_category && ($resource_category != $category['id'])) {
				continue;
			}

			# echo "<b>" . $category['name'] . "</b><br/><br/>";
			echo "<ol id=\"resource-list\">";

			$resources = $this->model->getByCategory($category['id']);
			foreach ($resources as $resource) {
				if (!filter($resource, array('name' => $resource_name, 'address' => $resource_address, 'keywords' => $resource_keywords))) {
					continue;
				}
				echo "<li><a href=\"index.php?option=com_prenotown&view=resource&layout=default&id=" . $resource['id'] . "\">";
				echo $resource['name'] . ", " . $resource['address'];
				echo "</a>";
				echo "</li>";
				$total_results++;
			}

			if (!$total_results) {
				echo JText::_("No resources found");
			}

			echo "</ol>";
		}
	} else {
		# all the resources in a full list
		// echo "<b>" . JText::_("All") . "</b><br/><br/>";
		echo '<div id="resource-list">';
		$resources = $this->model->getData();
		foreach ($resources as $resource) {
			if (!filter($resource, array('name' => $resource_name, 'address' => $resource_address, 'keywords' => $resource_keywords))) {
				continue;
			}
			echo "<div style=\"display:block;\">";

			# check for attachments, to get a thumbnail
			$at = $this->attachments->getByResource($resource['id']);
			if (isset($at[0])) {
				echo "<img src=\"" . $at[0]['filename'] . "\"/>";
			}

			echo "<a href=\"index.php?option=com_prenotown&view=resource&layout=default&id=" . $resource['id'] . "\">";
			echo $resource['name'] . ", " . $resource['address'];
			echo "</a><br/>";
			echo $resource['description'];
			echo "<br/>";
			echo $resource['notes'];
			// echo " [<a class=\"resource-edit\" href=\"index.php?option=com_prenotown&view=resource&layout=edit&id=" . $resource['id'] . "\">Edit</a>]";
			echo "</div>";
			$total_results++;
		}

		echo "</div>";

		if (!$total_results) {
			echo JText::_("No resources found");
		}
	}

	function filter($resource, $rules)
	{
		if (isset($rules['name']) and (!preg_match("/" . $rules['name'] . "/i", $resource['name']))) {
			return false;
		}
		if (isset($rules['address']) and (!preg_match("/" . $rules['address'] . "/i", $resource['address']))) {
			return false;
		}
		/*
		if (isset($rules['keywords']) and (!preg_match("/" . $rules['keywords'] . "/", $resource['keywords']))) {
			return false;
		}
		*/
		return true;
	}
?>
