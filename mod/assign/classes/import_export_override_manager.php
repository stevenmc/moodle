<?php


namespace mod_assign;

require_once($CFG->libdir . '/csvlib.class.php');

use context_user;
use mod_assign\output\override_import_form;
use mod_assign\output\override_upload_form;
use \assign_form;
use \assign_header;

class import_export_override_manager
{
    const MODE_USER = 'user';
    const MODE_GROUP = 'group';

    /** @var \assign $assignment the assignment record that contains the global
     *              settings for this assign instance
     */
    private $assignment;

    private $mode;
    public function __construct($assignment, $mode = '') {
        $this->assignment = $assignment;
        $this->mode = $mode;
    }
    /**
     * Handles the display of the CSV file upload
     */
    public function uploadform() {
        $o = '';
        $mform = new override_upload_form(null,
        [
            'context' => $this->assignment->get_context(),
            'id' => $this->assignment->get_course_module()->id,
            'mode'=> $this->mode
        ]);

        $confirm = optional_param('confirm', 0, PARAM_BOOL);
        $renderer = $this->assignment->get_renderer();

        if ($mform->is_cancelled()) {
            // return out
            redirect(new moodle_url('view.php',
            [
                'id' => $this->assignment->get_course_module()->id,
                'mode'=> $this->mode
            ]));
            return;
        } else if (
            ($data = $mform->get_data())  &&
            ($csvdata = $mform->get_file_content('overridesfiles'))
        ) {
            // we have a form and we have a file.
            $importid = \csv_import_reader::get_new_iid('overridesfile');
            $overrideimporter = new overrideimporter($importid, $this->assignment,
                $data->encoding, $data->separator);
            $draftid = $data->overridesfiles;

            // Preview Import
            $mform = new override_import_form(null,
            [
                'assignment' => $this->assignment,
                'csvdata' => $csvdata,
                'context' => $this->assignment->get_context(),
                'id' => $this->assignment->get_course_module()->id,
                'overridesimporter' => $overrideimporter,
                'draftid' => $draftid,
                'mode'=> $this->mode,
                'createmissing' => false
            ]);

            $o .= $renderer->render(new assign_header(
                    $this->assignment->get_instance(),
                    $this->assignment->get_context(),
                    false,
                    $this->assignment->get_course_module()->id,
                    get_string('importoverrides', 'assign')
            ));

            $o .= $renderer->render(new assign_form('confirmimport', $mform));
            $o .= $renderer->render_footer();
        } else if ($confirm) {
            // User has uploaded
            $o = '';

            $importid = optional_param('importid', 0, PARAM_INT);
            $draftid = optional_param('draftid', 0, PARAM_INT);
            $encoding = optional_param('encoding', 'utf-8', PARAM_ALPHANUMEXT);
            $separator = optional_param('separator', 'comma', PARAM_ALPHA);
            $overrideimporter = new overrideimporter($importid, $this->assignment, $encoding, $separator);
            $mform = new override_import_form(null,
            [
                'assignment' => $this->assignment,
                'csvdata' => '',
                'context' => $this->assignment->get_context(),
                'id' => $this->assignment->get_course_module()->id,
                'overridesimporter' => $overrideimporter,
                'draftid' => $draftid,
                'mode'=> $this->mode
            ]);
            if ($mform->is_cancelled()) {
                redirect(new moodle_url('view.php',
                    array('id'=>$this->assignment->get_course_module()->id,
                        'action'=>'grading')));
                return;
            }
            $o = $this->process_import_overrides($draftid, $importid, $encoding, $separator);

            return $o;

        } else {
            $o .= $renderer->render(new assign_header($this->assignment->get_instance(),
                $this->assignment->get_context(),
                false,
                $this->assignment->get_course_module()->id,
                get_string('importoverrides', 'assign')));
            $o .= $renderer->render(new assign_form('uploadoverrides', $mform));
            $o .= $renderer->render_footer();
        }
        return $o;
    }

    public function process_import_overrides($draftid, $importid, $encoding, $separator)
    {
        global $USER, $DB;

        require_sesskey();
        require_capability('mod/assign:grade', $this->assignment->get_context());

        $overrideimporter = new overrideimporter($importid, $this->assignment, $encoding, $separator);

        $context = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
            redirect(new moodle_url('view.php',
                array('id'=>$this->assignment->get_course_module()->id,
                    'action'=>'grading')));
            return;
        }
        $file = reset($files);

        $csvdata = $file->get_content();
        if ($csvdata) {
            $overrideimporter->parsecsv($csvdata);
        }
        if (!$overrideimporter->init()) {
            $thisurl = new moodle_url('view.php',
                [
                    'id' => $this->assignment->get_course_module()->id,
                    'mode'=> $this->mode
                ]);
            print_error('invalidoverideimport', 'assign', $thisurl);
            return;
        }

        /*
         * These are the form values that we need to submit.
        $this->cm = $cm;
        $this->assign = $assign;
        $this->context = $context;
        $this->groupmode = $groupmode;
        $this->groupid = empty($override->groupid) ? 0 : $override->groupid;
        $this->userid = empty($override->userid) ? 0 : $override->userid;
         */

        while ($record = $overrideimporter->next()) {

        }
    }
}