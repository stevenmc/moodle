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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once('HTML/QuickForm/input.php');

/**
 * Assignment grade form
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_assign_grade_form extends moodleform {
    /** @var assignment $assignment */
    private $assignment;

    /**
     * Define the form - called by parent constructor.
     */
    public function definition() {
        global $DB;
        $mform = $this->_form;

        list($assignment, $data, $params) = $this->_customdata;
        // Visible elements.
        $this->assignment = $assignment;
        $assignment->add_grade_form_elements($mform, $data, $params);
        $userid = isset($params['userid']) ? $params['userid'] : 0;
        // Why this can't be done in the above call I don't know, but Moodle's only
        // passing the form, not $this.
        /* MDL-49320 */
        if ($this->assignment->get_instance()->markingworkflow &&
            $this->assignment->get_instance()->markingallocation &&
            has_capability('mod/assign:manageallocations', $this->assignment->get_context())) {
            
            // Team submissions.
            if ($this->assignment->get_instance()->teamsubmission) {
                $submission = $this->assignment->get_group_submission($userid, 0, false);
            } else {
                $submission = $this->assignment->get_user_submission($userid, false);
            }
            $grades = $this->assignment->get_user_grade($userid, false);
            //var_dump($grades);
            $mform->addElement('static', 'expected', 'Number of gradings required', 2);
            $dispgrade = "!!!";
            $mform->addElement('static', 'completed', 'Number of gradings completed', $dispgrade);
            $amparams = [
                'assignment' => $this->assignment->get_instance()->id,
                'userid' => $userid
            ];

            $allocatedmarkers = $DB->get_records("assign_allocated_marker", $amparams);

            $i = 0;
            foreach($allocatedmarkers as $m) {
                $u = $DB->get_record('user', ['id' => $m->allocatedmarker]);
                //$markeroptions[] = fullname($u);
                // check if marker has graded
                $hasgrade = "No";
                $grade = $DB->get_record('assign_grades', [
                    'assignment' => $this->assignment->get_instance()->id,
                    'userid' => $userid,
                    'grader' => $u->id,
                    'attemptnumber' => $submission->attemptnumber
                ]);
                if ($grade) {
                    $hasgrade = "Yes";
                }
                $mform->addElement('static', "allocatedmarker{$i}", "Marker {$i} -  ($u->id}" . fullname($u). " - {$hasgrade}");
                $i++;
            }
            //$this->repeat_elements($markersarray, $repeatno, $markeroptions, 'assign_allocatedmarkers', 'assign_add_markers', 3, null, true);

            /*
                list($sort, $params) = users_order_by_sql();
            $markers = get_users_by_capability($this->assignment->get_context(), 'mod/assign:grade', '', $sort);
            $markerlist = array('' =>  get_string('choosemarker', 'assign'));
            $viewfullnames = has_capability('moodle/site:viewfullnames', $this->assignment->get_context());
            foreach ($markers as $marker) {
                $markerlist[$marker->id] = fullname($marker, $viewfullnames);
            }        
            array_unshift($options, "Unassigned");
            $markersarray = [];

            $markerstring = "Marker {no}";
            $markersarray[] = $mform->createElement('select', 'allocatedmarker', $markerstring , $options);

            $repeatno = 2;  // TODO This should be picked up from the assignment config
            /*if ($this->_instance){
                //$repeatno = $DB->count_records('choice_options', array('choiceid'=>$this->_instance));
                $repeatno += 2;
            }
            $repeatoptions = [];

            $this->repeat_elements($markersarray, $repeatno, $repeatoptions, 'assign_allocatedmarkers', 'assign_add_markers', 3, null, true);
            $mform->addHelpButton('assign_allocatedmarkers', 'allocatedmarker', 'assign');
            $mform->disabledIf('assign_allocatedmarkers', 'workflowstate', 'eq', ASSIGN_MARKING_WORKFLOW_STATE_READYFORREVIEW);
            $mform->disabledIf('assign_allocatedmarkers', 'workflowstate', 'eq', ASSIGN_MARKING_WORKFLOW_STATE_INREVIEW);
            $mform->disabledIf('assign_allocatedmarkers', 'workflowstate', 'eq', ASSIGN_MARKING_WORKFLOW_STATE_READYFORRELEASE);
            $mform->disabledIf('assign_allocatedmarkers', 'workflowstate', 'eq', ASSIGN_MARKING_WORKFLOW_STATE_RELEASED);
            */
        }
        if ($data) {
            $this->set_data($data);
        }
    }

    /**
     * This is required so when using "Save and next", each form is not defaulted to the previous form.
     * Giving each form a unique identitifer is enough to prevent this
     * (include the rownum in the form name).
     *
     * @return string - The unique identifier for this form.
     */
    protected function get_form_identifier() {
        $params = $this->_customdata[2];
        return get_class($this) . '_' . $params['userid'];
    }

    /**
     * Perform minimal validation on the grade form
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $instance = $this->assignment->get_instance();

        if ($instance->markingworkflow && !empty($data['sendstudentnotifications']) &&
                $data['workflowstate'] != ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
            $errors['workflowstate'] = get_string('studentnotificationworkflowstateerror', 'assign');
        }

        // Advanced grading.
        if (!array_key_exists('grade', $data)) {
            return $errors;
        }

        if ($instance->grade > 0) {
            if (unformat_float($data['grade'], true) === false && (!empty($data['grade']))) {
                $errors['grade'] = get_string('invalidfloatforgrade', 'assign', $data['grade']);
            } else if (unformat_float($data['grade']) > $instance->grade) {
                $errors['grade'] = get_string('gradeabovemaximum', 'assign', $instance->grade);
            } else if (unformat_float($data['grade']) < 0) {
                $errors['grade'] = get_string('gradebelowzero', 'assign');
            }
        } else {
            // This is a scale.
            if ($scale = $DB->get_record('scale', array('id'=>-($instance->grade)))) {
                $scaleoptions = make_menu_from_list($scale->scale);
                if ((int)$data['grade'] !== -1 && !array_key_exists((int)$data['grade'], $scaleoptions)) {
                    $errors['grade'] = get_string('invalidgradeforscale', 'assign');
                }
            }
        }
        return $errors;
    }
}
