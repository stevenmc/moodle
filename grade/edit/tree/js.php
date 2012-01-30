<?php
	require_once '../../../config.php';
	header("Content-Type: text/javascript");
?>
function autocomplete(request, callback) {
	var url = "<?php echo $CFG->wwwroot; ?>/grade/edit/tree/ajax.php?autocomplete=" + request.term;
	$.ajax(url, {
		method: 'GET',
		success: callback,
		dataType: 'json'
	});
}

$(document).ready(function() {

	// Load CSS (#964)
	$('<link/>').appendTo('head').attr({
		rel: 'stylesheet',
		type: 'text/css',
		href: '<?php echo $CFG->wwwroot; ?>/grade/edit/tree/widget-style.css'
		});
		
	var editor = $('textarea#id_calculation').gradeeditor();
});