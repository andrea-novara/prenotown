<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");
?>
<h2><?php JText::printf("Booking resource %s", $this->model->tables['main']->name) ?></h1>
<?php $this->costfunction->bookingInterface() ?>
