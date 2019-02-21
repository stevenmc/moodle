<?php
/**
 * Created by PhpStorm.
 * User: igs03102
 * Date: 21/02/19
 * Time: 14:47
 */

namespace mod_assign\local;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/mod/assign/mod_form.php');
require_once($CFG->libdir .'/csvlib.class.php');

class importoverrideform extends \moodleform
{
    public function __construct($submiturl, $cm, $assign, $context) {
        $this->cm = $cm;
        $this->assign = $assign;
        $this->context = $context;
        //$this->groupmode = $groupmode;
        //$this->groupid = empty($override->groupid) ? 0 : $override->groupid;
        //$this->userid = empty($override->userid) ? 0 : $override->userid;
        //$this->sortorder = empty($override->sortorder) ? null : $override->sortorder;

        parent::__construct($submiturl, null, 'post');

    }

    protected function definition() {
        global $CFG, $DB;

        $cm = $this->cm;
        $data = $this->_customdata;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'saveimportoverrides');
        $mform->setType('action', PARAM_ALPHA);

        $mform->addElement('header', 'general', get_string('importoverrides', 'mod_assign'));

        $filepickeroptions = [];
        $filepickeroptions['filetypes'] = '*';
        $filepickeroptions['maxbytes'] = get_max_upload_file_size();

        $mform->addElement('filepicker', 'userfile', get_string('import'), null, $filepickeroptions);

        $choices = \csv_import_reader::get_delimiter_list();
        $key = array_search(';', $choices);
        if (! $key === FALSE) {
            // array $choices contains the semicolon -> drop it (because its encrypted form also contains a semicolon):
            unset($choices[$key]);
        }

        $delimiters = \csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'fielddelimiter', get_string('fielddelimiter', 'data'), $delimiters);
        $mform->setDefault('fielddelimiter', 'comma');
        $choices = \core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('fileencoding', 'mod_data'), $choices);
        $mform->setDefault('encoding', 'UTF-8');

        $this->add_action_buttons(true, get_string('importoverrides', 'mod_assign'));
        $this->set_data($data);
    }
}