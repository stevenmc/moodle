<?php
/**
 * This page is going to be much like the pick page.
 */
require_once(__DIR__.'/../../../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/edit_form.php');
require_once($CFG->dirroot.'/grade/grading/lib.php');

$areaid = required_param('areaid', PARAM_INT);

$manager = get_grading_manager($areaid);

list($context, $course, $cm) = get_context_info_array($manager->get_context()->id);

require_login($course, true, $cm);
require_capability('moodle/grade:managegradingforms', $context);

$controller = $manager->get_controller('sequence');

$PAGE->set_url(new moodle_url('/grade/grading/form/sequence/edit.php', array('areaid' => $areaid)));
$PAGE->set_title(get_string('definesequence', 'gradingform_sequence'));
$PAGE->set_heading(get_string('definesequence', 'gradingform_sequence'));

$mform = new gradingform_sequence_editsequence(null, [
    'areaid' => $areaid, 'context' => $context, 'allowdraft' => !$controller->has_active_instances(),
    'manager' => $manager
], 'post','', ['class' => 'gradingform_sequence_editform']);
$data = $controller->get_definition_for_editing(true);

$returnurl = optional_param('returnurl', $manager->get_management_url(), PARAM_LOCALURL);
$data->returnurl = $returnurl;
$mform->set_data($data);

if ($mform->is_cancelled()) {
echo  'Cancelled';
} else if ($mform->is_submitted()) {//} && $mform->is_validated() ) {// && !$mform->need_confirm_regrading($controller)) {
    $data = $mform->get_data();
    var_dump($data);
    $controller->update_definition($data);
}

\core\session\manager::keepalive();
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();