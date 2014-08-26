<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	$images = $this->model->getImages();
?>
<script>
	var index = 1;
	var target = 'img1';

	function cycleImages() {
		// the available images
		var images = [
		<?php
			foreach ($images as $i) {
				echo "'" . $i['filename'] . "', ";
			}
		?>
		];

		if (images.length < 2) {
			return;
		}

		// set the image
		if (target == 'img1') {
			tDiv = 'img1_div';
			vDiv = 'img2_div';
			target = 'img2';
		} else {
			tDiv = 'img2_div';
			vDiv = 'img1_div';
			target = 'img1';
		}

		// apply the effect
		if ($(tDiv).fx){$(tDiv).fx.stop();}
		if ($(vDiv).fx){$(vDiv).fx.stop();}

		$(target).src = images[index];

		$(tDiv).fx = $(tDiv).effect('opacity', {duration: 2000}).start(0);
		$(vDiv).fx = $(vDiv).effect('opacity', {duration: 2000}).start(1);

		// increment the index
		index++;
		if (index >= images.length) {
			index = 0;
		}
	}

	window.addEvent('domready', function() { setInterval("cycleImages()", 6000); });
</script>
<?php
	$src0 = "";
	$src1 = "";

	if (isset($images[0])) {
		$src0 = $images[0]['filename'];
	}

	if (isset($images[1])) {
		$src1 = $images[1]['filename'];
	}
?>
<h2><?php echo $this->name ?></h1>
<div id="image_frame" style="float: right">
	<div id="img1_div" style="opacity: 1">
		<img id="img1" src="<?php echo $src0 ?>"/>
	</div>
	<div id="img2_div" style="opacity: 0">
		<img id="img2" src="<?php echo $src1 ?>"/>
	</div>
</div>
<h2><?php echo $this->model->tables['main']->address ?></h2>
<p style="height: 180px"><?php echo $this->model->tables['main']->description ?><br/>
<?php echo $this->model->tables['main']->address ?><br/>
<br><b><?php echo JText::_("Notes") ?>:</b><br>
<?php echo preg_replace("/\n/", "<br>", $this->model->tables['main']->notes) ?>
<?php
	$docs = $this->model->getDocuments();
	if (count($docs)) {
		echo "<hr/><h4>" . JText::_("Attachments") . "</h4><ul>";
		foreach ($docs as $doc) {
			echo '<li><a href="' . $doc['filename'] . '">' . $doc['name'] . '</a></li>';
		}
		echo "</ul>";
	}
?>
<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resources&layout=tree')"><?php echo JText::_("Back") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="popup('index.php?option=com_prenotown&view=resource&layout=fees&format=raw&id=<?php echo $this->id ?>')"><?php echo JText::_("Fees") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=book&id=<?php echo $this->id ?>')"><?php echo JText::_("Book") ?></button><br>
</div>
