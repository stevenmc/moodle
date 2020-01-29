<?php
require_once(__DIR__.'/../../../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/edit_form.php');
require_once($CFG->dirroot.'/grade/grading/lib.php');

// This area id is the sequence's areaid.
$areaid = required_param('areaid', PARAM_INT);
$targetid = $areaid;
$pick       = optional_param('pick', null, PARAM_INT); // create new form from this template

$manager = get_grading_manager($areaid);

list($context, $course, $cm) = get_context_info_array($manager->get_context()->id);

require_login($course, true, $cm);
require_capability('moodle/grade:managegradingforms', $context);

$controller = $manager->get_controller('sequence');

// the manager of the target area
$targetmanager = get_grading_manager($targetid);

if ($targetmanager->get_context()->contextlevel < CONTEXT_COURSE) {
    throw new coding_exception('Unsupported gradable area context level');
}

list($context, $course, $cm) = get_context_info_array($targetmanager->get_context()->id);

require_login($course, true, $cm);
require_capability('moodle/grade:managegradingforms', $context);

$PAGE->set_url(new moodle_url('/grade/grading/form/sequence/editstep.php', array('areaid' => $areaid)));
$PAGE->set_title(get_string('definesequence', 'gradingform_sequence'));
$PAGE->set_heading(get_string('definesequence', 'gradingform_sequence'));
$output = $PAGE->get_renderer('core_grading');

if ($pick) {
    // Process picking a template
}


echo $OUTPUT->header();
echo 'Adding step to sequence area' . $areaid;
$data = $controller->get_definition_for_editing(true);
$steps = $manager->get_available_methods(false);
var_dump($steps);
echo $OUTPUT->footer();