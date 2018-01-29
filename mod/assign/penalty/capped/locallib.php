<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/comment/lib.php');
require_once($CFG->dirroot . '/mod/assign/penaltyplugin.php');
class assign_penalty_capped extends assign_penalty_plugin {
    function get_name() {
        return "Capped";
    }
    /**
     * Apply penalty to the grade.
     */
    function prepare_for_gradebook($grade) {
        $grade->grade = "999";
        return $grade;
    }
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;
//$mform->addElement('static','ehll','aasdfa');
    }
    function is_configurable() {
        return true;
    }
}