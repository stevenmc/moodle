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

namespace mod_assign\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

use mod_assign\import_export_override_manager;
use mod_assign\overrideimporter;
use renderer_base;
use renderable;
use templatable;
use stdClass;

class override_import_form extends \moodleform {
    public function definition()
    {
        global $COURSE, $USER, $PAGE;
        $mform = $this->_form;
        $params = $this->_customdata;

        $renderer = $PAGE->get_renderer('assign');
        /**
         * @var \assign $assignment
         */
        $assignment = $params['assignment'];
        $csvdata = $params['csvdata'];
        $mode = $params['mode'];
        $draftid = $params['draftid'];
        $createmissinggroups = $params['createmissing'];
        /**
         * @var overrideimporter $overrideimporter
         */
        $overrideimporter = $params['overridesimporter'];
        $draftid = $params['draftid'];

        if (!$overrideimporter) {
            print_error('invalidarguments');
            return;
        }

        if ($csvdata) {
            $overrideimporter->parsecsv($csvdata);
        }

        if (!$overrideimporter->init()) {
            $thisurl = new moodle_url('/mod/assign/overrideimport.php', [
                'action' => 'importgroup',
                'cmid' => $this->assignment->get_course_module()->id
            ]);
            print_error('invalidoverrideimport', 'assign', $thisurl);
            return;
        }

        $mform->addElement('header', 'importoverrides', get_string('importoverrides'. $mode, 'assign'));

        // Render preview
        $strgroupname = get_string('groupname', 'group');
        $strgroupingname = get_string('groupingname', 'group');
        $straollowsubmissionsfrom = get_string('allowsubmissionsfromdate', 'assign');
        $strduedate = get_string('duedate', 'assign');
        $strcutoffdate = get_string('cutoffdate', 'assign');
        $i = 1;
        $table = new \flexible_table('importoverridestable');
        $table->define_columns([
            'groupinfo',
            'allowsubmissionsfromdate',
            'duedate',
            'cutoffdate',
            'errors'
        ]);
        $table->define_headers([
            $strgroupname,
            //$strgroupingname,
            $straollowsubmissionsfrom,
            $strduedate,
            $strcutoffdate,
            'Issues'
        ]);

        $table->setup();
        $seengroups = [];

        ob_start();

        while ($record = $overrideimporter->next()) {
            $errors = [];
            if (in_array($record->groupname, $seengroups)) {
                $errors[]  = $record->groupname . ' already exists in import file. This override will be skipped.';
            } else {
                $seengroups[] = $record->groupname;
            }
            $group = groups_get_group_by_name($assignment->get_course()->id, $record->groupname);
            $groupinfo = get_string('importgroupinfo', 'assign', [
                'groupname' => $record->groupname,
                'tocreate' => ($createmissinggroups && $group == false) ? "*" : ""
            ]);
            if (!empty($record->groupingname)) {
                $groupinfo = get_string('importgroupingroupinginfo', 'assign', [
                    'groupname' => $record->groupname,
                    'groupingname' => $record->groupingname,
                    'tocreate' => ($createmissinggroups && $group == false) ? "*" : ""
                ]);
            }

            $table->add_data([
                $groupinfo,
                userdate($record->allowsubmissionsfromdate),
                userdate($record->duedate),
                userdate($record->cutoffdate),
                implode(' ' , $errors)
            ]
            );
        }
        //ob_start();
        $table->finish_output();
        $otable = ob_get_clean();

        $mform->addElement('static', "previewtable", get_string($mode.'overrides', 'assign'), $otable);

        /* Add all the parameters we need to make this work. */
        $mform->addElement('hidden', 'id', $assignment->get_course_module()->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $params['mode']);
        if ($params['mode'] == import_export_override_manager::MODE_USER) {
            $mform->addElement('hidden', 'action', 'importuser');
        } else if ($params['mode'] == import_export_override_manager::MODE_GROUP) {
            $mform->addElement('hidden', 'action', 'importgroup');
        } else {
            print_error('invalidargument');
        }
        $mform->setType('action', PARAM_TEXT);

        $mform->addElement('hidden', 'importid', $overrideimporter->importid);
        $mform->setType('importid', PARAM_INT);
        $mform->addElement('hidden', 'draftid', $draftid);
        $mform->setType('draftid', PARAM_INT);
        $mform->addElement('hidden', 'confirm', 'true');
        $mform->setType('confirm', PARAM_BOOL);

        $this->add_action_buttons(true, get_string('confirm'));

    }
}
