<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This page handles importing of assign overrides
 *
 * @package   mod_assign
 * @copyright 2019 Michael Hughes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/*
 * The lifecycle of the import is:
 *
 * 1. Prompt the user for a file
 * 2. Pre-flight the file (for any issues that may exist)
 * 3. Perform the Import
 * 4. Display the results.
 *
 */

use mod_assign\import_export_override_manager;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/assign/lib.php');
require_once($CFG->dirroot.'/mod/assign/locallib.php');
require_once($CFG->dirroot.'/mod/assign/override_form.php');

$cmid = optional_param('id', 0, PARAM_INT);
$mode = optional_param('mode', '', PARAM_ALPHA); // One of 'user' or 'group', default is 'group'.
$action = optional_param('action', null, PARAM_ALPHA);

if ($cmid) {
    list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'assign');

} else {
    print_error('invalidcoursemodule');
}

$url = new moodle_url('/mod/assign/overrideimport.php');
if ($action) {
    $url->param('action', $action);
}
$url->param('id', $cmid);

$PAGE->set_url($url);

list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'assign');
$assign = $DB->get_record('assign', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
$assign = new assign($context, $cm, $course);
//$assigninstance = $assign->get_instance($userid);

// Add or edit an override.
require_capability('mod/assign:manageoverrides', $context);

$assigngroupmode = groups_get_activity_groupmode($cm);
$accessallgroups = ($assigngroupmode == NOGROUPS) || has_capability('moodle/site:accessallgroups', $context);

$overridecountgroup = $DB->count_records('assign_overrides', array('userid' => null, 'assignid' => $assign->get_instance()->id));

// Get the course groups that the current user can access.
$groups = $accessallgroups ? groups_get_all_groups($cm->course) : groups_get_activity_allowed_groups($cm);

// Default mode is "group", unless there are no groups.
if ($mode != import_export_override_manager::MODE_USER and $mode != import_export_override_manager::MODE_GROUP) {
    if (!empty($groups)) {
        $mode = import_export_override_manager::MODE_GROUP;
    } else {
        $mode = import_export_override_manager::MODE_USER;
    }
}

$manager = new import_export_override_manager($assign, $mode);

$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('importoverrides'. $mode, 'assign'));
$PAGE->set_heading($course->fullname);
//echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('importoverrides'. $mode, 'assign') . ': '. format_string($assign->get_instance()->name, true, array('context' => $context)));

echo $manager->uploadform();

//echo $OUTPUT->footer();
