<?php
/**
 * @package Prenotown
 * @copyright XSec
 * @license GNU GPL v.2
 */

/** ensure a valid entry point */
defined('_JEXEC') or die("Restricted Access");

jimport("joomla.application.component.controller");

/**
 * Backend controller
 *
 * @package Prenotown
 * @subpackage Controllers
 */
class PrenotownController extends JController
{
	/** resource model */
    var $_resource_model = null;

	/** costfunction model */
    var $_costFunction_model = null;

	/** user model */
    var $_user_model = null;

    function PrenotownController()
    {
        parent::__construct();

        $this->_resource_model = $this->getModel('Resource');
        $this->_costFunction_model = $this->getModel('CostFunction');
        $this->_user_model = $this->getModel('User');

        /* register unexplicit tasks */
        // $this->registerTask('task', 'unexplicitTask');
    }

    function display($tpl=null)
    {
        parent::display($tpl);
    }
}
?>
