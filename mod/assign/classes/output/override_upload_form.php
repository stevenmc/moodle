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

class override_upload_form extends \moodleform {

    public function definition() {
        global $COURSE, $USER, $PAGE;
        $mform = $this->_form;
        $params = $this->_customdata;

        $mform->addElement("header", 'importoverride', "Import Overrides");
        $fileoptions = array('subdirs'=>0,
            'maxbytes'=>$COURSE->maxbytes,
            'accepted_types'=>'csv',
            'maxfiles'=>1,
            'return_types'=>FILE_INTERNAL);

        $mform->addElement('filepicker', 'overridesfiles', get_string('uploadafile'), null, $fileoptions);
        $mform->addRule('overridesfiles', get_string('uploadnofilefound'), 'required', null, 'client');
        $mform->addHelpButton('overridesfiles', 'overridesfiles', 'assignfeedback_offline');

        $encodings = \core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'grades'), $encodings);
        $mform->addHelpButton('encoding', 'encoding', 'grades');

        $radio = array();
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('septab', 'grades'), 'tab');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcomma', 'grades'), 'comma');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcolon', 'grades'), 'colon');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepsemicolon', 'grades'), 'semicolon');
        $mform->addGroup($radio, 'separator', get_string('separator', 'grades'), ' ', false);
        $mform->addHelpButton('separator', 'separator', 'grades');
        $mform->setDefault('separator', 'comma');

        $mform->addElement('hidden', 'id', $params['id']);
        $mform->setType('id', PARAM_INT);
//        if ($params['mode'] == import_export_override_manager::MODE_USER) {
//            $mform->addElement('hidden', 'action', 'importuser');
//        } else if ($params['mode'] == import_export_override_manager::MODE_GROUP) {
//            $mform->addElement('hidden', 'action', 'importgroup');
//        } else {
//            print_error('invalidargument');
//        }
//        $mform->setType('action', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('importoverrides', 'assignfeedback_offline'));
    }
}