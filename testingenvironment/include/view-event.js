$(document).ready(function() {

	/*
	 * Initialize image viewer for course map
	 */
	$('.course-map').fancybox({
		hideOnOverlayClick: true
	});

	/*
	 * Tooltip for checkered flag
	 */
	$('.event-race-results').tooltip({
		showURL: false
	});

	/*
	 * Show race results when flag clicked
	 */
	$('.event-race-results').live('click', function() {
		var e_id = $(this).attr('rel');
		showRaceResults(e_id);
	});
});