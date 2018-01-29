<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/assignmentplugin.php');

/**
 * Abstract base class for penalty plugin types.
 *
 * @package   mod_assign
 * @copyright 2018 Michael Huhges
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class assign_penalty_plugin extends assign_plugin {
    /**
     * return subtype name of the plugin
     *
     * @return string
     */
    public final function get_subtype() {
        return 'assignpenalty';
    }

    /**
     * 
     */
    public abstract function prepare_for_gradebook($grade);
}