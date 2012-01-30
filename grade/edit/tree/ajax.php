<?php

require_once '../../../config.php';
require_once dirname(__FILE__).'/ajaxlib.php';
require_once $CFG->libdir.'/grouplib.php';

//TODO: Make this whole script permissions-aware!!!!
//TODO: Check session key

require_login();

$autocomplete = optional_param('autocomplete', null, PARAM_TEXT);
$criteriatext = optional_param('criteriatext', null, PARAM_TEXT);
$id = optional_param('id', null, PARAM_NUMBER);
$type = optional_param('type', null, PARAM_TEXT);
$widget = optional_param('widget', null, PARAM_TEXT);

$result = array();

if(!is_null($autocomplete)) {

	// Strip into "course/grouping/group" format
	$parts = explode('/', $autocomplete);

	if(sizeof($parts) == 1) {
		$count = count_records_select('course', "shortname like '$autocomplete%'");
		if($count > 20) return array();

		if($courses = get_records_select('course', "shortname like '$autocomplete%'", 'fullname', 'id, shortname, fullname', 0, 40) ){
			foreach($courses as $course) {
				$result[] = array('value' => $course->shortname, 'label' => $course->fullname);
			}
		}
	} elseif(sizeof($parts) == 2) {
		if($course = get_record('course', 'shortname', $parts[0])) {
			//TODO: Take notice of $CFG->enablegroupings
			if($groupings = groups_get_all_groupings($course->id)){
				foreach($groupings as $grouping) {
					if(strlen($parts[1]) > 0 && strpos($grouping->name, $parts[1]) !== 0) continue;
					$t = $course->shortname . '/' . $grouping->name;
					$result[] = array('value' => $t, 'label' => $t);
				}
			}
				
		}
	} elseif(sizeof($parts) == 3) {
		if($course = get_record('course', 'shortname', $parts[0])) {
			//TODO: Take notice of $CFG->enablegroupings
			if($groupings = groups_get_all_groupings($course->id)){
				foreach($groupings as $grouping) {
					if(strlen($parts[1]) > 0 && $grouping->name != $parts[1]) continue;
					$groups = groups_get_all_groups($course->id, 0, $grouping->id);
						
					// Add "all groups"
					$result[] = array('value' => $course->shortname . '/' . $grouping->name . '/*', 'label' => $course->shortname . '/' . $grouping->name . '/[All Groups]');
					foreach($groups as $group) {
						$t = $course->shortname . '/' . $grouping->name . '/' . $group->name;
						$result[] = array('value' => $t, 'label' => $t);
					}
					break;
				}
			}

		}
	}

} elseif (!is_null($criteriatext)) {

	$factory = new strathcom_recipient_factory();
	$result = $factory->recipient($criteriatext);

} 
// A hack to let us use the same code for the group selector and drilling down through groups in the course selector
elseif (($type == 'course') && ($id != 0)) {
		
	if(! $course = get_record('course', 'id', $id)) {
		return $result;
	}
	
	$allgroups = groups_get_all_groups($id);
	
	if($CFG->enablegroupings === false) {
		if($allgroups) {
			foreach($allgroups as $group) {
				$result[] = array(
					'data' => $group->name, 
					'attr' => array('id' => $group->id, 'data-type' => 'group'),
					'checked' => true
				);
			}
		}
	} else {
		if($groupings = groups_get_all_groupings($id)){
			foreach($groupings as $grouping) {
				$children = array();
				if($groups = groups_get_all_groups($grouping->courseid, 0, $grouping->id) ) {
					foreach($groups as $group) {
						$children[] = array(
							'data' => $group->name, 
							'attr' => array('id' => $group->id, 'data-type' => 'group'),
							'checked' => true
						);
						
						// Remove group from allgroups
						unset($allgroups[$group->id]);
					}
				}
				$item = array(
					'data' => $grouping->name,
					'attr' => array('id' => $grouping->id, 'data-type' => 'grouping'),
					'checked' => true,
					//'state' => 'closed' // This forces lazy loading of group data
					'children' => $children
				);
				$result[] = $item;
			}
		}
		
		// Now add all the groups that aren't in a grouping
		if($allgroups) {
			foreach($allgroups as $group) {
				$result[] = array(
					'data' => $group->name,
					'attr' => array('id' => $group->id, 'data-type' => 'group'),
					'checked' => true
				);
			}
		}
	}
	
	// Hack: return something to jstree - empty data doesn't fire loaded event. :(
	// http://code.google.com/p/jstree/issues/detail?id=578
	if(sizeof($result) === 0) {
		$result = array(
			array(
				'attr' => array( 'id' => 'no-data'),
				'data' => 'No groups'
			)
		);
	}

} elseif($type === 'grouping' && $id != 0) {
		if($grouping = get_record('groupings', 'id', $id) ){
			if($groups = groups_get_all_groups($grouping->courseid, 0, $id) ) {
				foreach($groups as $group) {
					$result[] = array(
										'data' => $group->name, 
										'attr' => array('id' => $group->id, 'data-type' => 'group'),
										'checked' => true
					);
				}
			}
		}
} elseif($type === 'all-courses') {
	$caps = array('block/crtool:usecrtool', 'moodle/course:manageactivities');
	// If user has either cap at system level, just send back categories
	$what_to_return = 'courses';
	
	foreach($caps as $cap) {
		if(has_capability($cap, get_system_context())) {
			$what_to_return = 'categories';
			break;
		}
	}
	
	switch($what_to_return){
		
		case 'courses':
			load_user_accessdata($USER->id);
			$added = array();
			foreach($caps as $cap) {
				$userclasses = 	get_user_courses_bycap($USER->id, $cap, $ACCESS[$USER->id], false, 'fullname', array('fullname'));
				foreach($userclasses as $c) {
					$added[$c->id] = $c;
				}
			}
			foreach($added as $c) {
				$result[] = array(
						'data' => $c->fullname,
						'attr' => array('id' => $c->id, 'data-type' => 'course'),
						'checked' => false
				);
				
				// if it's the course widget we don't go down any further than courses
				if($widget !== 'course-browser') {
					$entry['state'] = 'closed';
				}
			}
			break;
		
		case 'categories':
			$categories = get_categories('none', 'id');
			foreach($categories as $category) {
				$result[] = array(
					'data' => $category->name,
					'attr' => array('id' => $category->id, 'data-type' => 'category'),
					'checked' => false,
					'state' => 'closed'
				);
			}
			break;
			
		default:
			$result = array();
	}
	
} elseif($type === 'category') {
			
	$courses = get_courses($id);
	foreach($courses as $course) {
		$entry = array(
				'data' => $course->fullname,
				'attr' => array('id' => $course->id, 'data-type' => 'course'),
				'checked' => false
		);
		
		// if it's the course widget we don't go down any further than courses
		if($widget !== 'course-browser') {
			$entry['state'] = 'closed';
		}
		$result[] = $entry;
	}
	
	$categories = get_child_categories($id);
	foreach($categories as $category) {
		$result[] = array(
				'data' => $category->name,
				'attr' => array('id' => $category->id, 'data-type' => 'category'),
				'checked' => false,
				'state' => 'closed'
		);
	}
}

header("Content-Type: text/javascript");
echo json_encode($result);