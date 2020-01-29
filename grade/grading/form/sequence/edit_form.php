<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
//require_once(__DIR__.'/sequenceeditor.php');

/**
 * Class gradingform_sequence_editsequence
 */
class gradingform_sequence_editsequence extends moodleform {
    protected function definition()
    {
        global $PAGE;
        $output = $PAGE->get_renderer('core_grading');
        $form = $this->_form;
        $manager = $this->_customdata['manager'];

        $form->addElement('hidden', 'areaid');
        $form->setType('areaid', PARAM_INT);

        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);

        $form->addElement('hidden', 'returnurl');
        $form->setType('returnurl', PARAM_LOCALURL);

        // name
        $form->addElement('text', 'name', get_string('name', 'gradingform_sequence'), array('size' => 52, 'aria-required' => 'true'));
        $form->addRule('name', get_string('required'), 'required', null, 'client');
        $form->setType('name', PARAM_TEXT);

        // rubric completion status
        $choices = array();
        $choices[gradingform_controller::DEFINITION_STATUS_DRAFT]    = html_writer::tag('span', get_string('statusdraft', 'core_grading'), array('class' => 'status draft'));
        $choices[gradingform_controller::DEFINITION_STATUS_READY]    = html_writer::tag('span', get_string('statusready', 'core_grading'), array('class' => 'status ready'));
        $form->addElement('select', 'status', get_string('sequencestatus', 'gradingform_sequence'), $choices)->freeze();

        $buttonarray = array();
        $btnsavesequence = &$form->createElement('submit', 'savesequence', get_string('savesequence', 'gradingform_sequence'));
        $buttonarray[] = $btnsavesequence;
        // The save sequence button should only be enabled if the sequence has an id (it's been saved) and has steps.
        $form->disabledIf('savesequence', 'id', 'eq', '');

        if ($this->_customdata['allowdraft']) {
            $buttonarray[] = &$form->createElement('submit', 'savesequencedraft', get_string('savesequencedraft', 'gradingform_sequence'));
        }
        $editbutton = &$form->createElement('submit', 'editsequence', ' ');
        $editbutton->freeze();
        $buttonarray[] = &$editbutton;
        $buttonarray[] = &$form->createElement('cancel');
        $form->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $form->closeHeaderBefore('buttonar');
    }

    /**
     * Setup the form depending on current values. This method is called after definition(),
     * data submission and set_data().
     * All form setup that is dependent on form values should go in here.
     *
     * We remove the element status if there is no current status (i.e. rubric is only being created)
     * so the users do not get confused
     */
    public function definition_after_data() {
        global $PAGE, $OUTPUT;
        //$output = $PAGE->get_renderer();
        $form = $this->_form;
        // Sequence Editor
        $id = $form->getElementValue('id');
        $areaid = $form->getElementValue('areaid');
        $manager = $this->_customdata['manager'];

        if (!empty($id)) {

            // Add new step in sequence
            $addstepUrl = new moodle_url('/grade/grading/form/sequence/editstep.php', [
            //$addstepUrl = new moodle_url('/grade/grading/pick.php', [
               'areaid' =>  $areaid
            ]);
            $addstepelement = $form->createElement('static', 'addnewstep', 'Add new step', $OUTPUT->action_link($addstepUrl, 'Add Step'));//$this->method_selector());
            $form->insertElementBefore($addstepelement, 'status');
        }

        $el = $form->getElement('status');
        if (!$el->getValue()) {
            $form->removeElement('status');
        } else {
            $vals = array_values($el->getValue());
            if ($vals[0] == gradingform_controller::DEFINITION_STATUS_READY) {
                $this->findButton('savesequence')->setValue(get_string('save', 'gradingform_sequence'));
            } else {

            }
        }

    }

    public function validation($data, $files) {
        $err = parent::validation($data, $files);
        if (isset($data['editsequence'])) {
            // continue editing

        } else if (isset($data['savesequence']) && $data['savesequence']) {
            // If user attempts to make rubric active - it needs to be validated
        }
        return $err;
    }
    protected function method_selector() {
        global $OUTPUT;
        $methods = grading_manager::available_methods(false);
        $targeturl = '';
        unset($methods['sequence']);
        $methods['none'] = get_string('gradingmethodnone', 'core_grading');
        $selector = new single_select(new moodle_url($targeturl, array('sesskey' => sesskey())),
            'setmethod', $methods, empty($method) ? 'none' : $method, null, 'addstepselector');
        $selector->set_label(get_string('addstep', 'gradingform_sequence'));
        $selector->set_help_icon('addstep', 'gradingform_sequence');
// TODO: Fix this as the output control is a nested form and it breaks the moodleform submission, so an alternative is required.
        return '';//$OUTPUT->render($selector);
    }
    /**
     * Return submitted data if properly submitted or returns NULL if validation fails or
     * if there is no submitted data.
     *
     * @return object submitted data; NULL if not valid or not submitted or cancelled
     */
    public function get_data() {
        $data = parent::get_data();
        if (!empty($data->saverubric)) {
            $data->status = gradingform_controller::DEFINITION_STATUS_READY;
        } else if (!empty($data->saverubricdraft)) {
            $data->status = gradingform_controller::DEFINITION_STATUS_DRAFT;
        }
        return $data;
    }
    /**
     * Returns a form element (submit button) with the name $elementname
     *
     * @param string $elementname
     * @return HTML_QuickForm_element
     */
    protected function &findButton($elementname) {
        $form = $this->_form;
        $buttonar =& $form->getElement('buttonar');
        $elements =& $buttonar->getElements();
        foreach ($elements as $el) {
            if ($el->getName() == $elementname) {
                return $el;
            }
        }
        return null;
    }
}