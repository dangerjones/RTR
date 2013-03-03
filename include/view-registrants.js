var maxRegistrantsPerPage = 20;
var currentRegistrantPage = 1;
var totalRegistrantsForEvent = 0;

$(document).ready(function() {
	$('#registrants-wrap').accordion({
		active: false,
		collapsible: true,
		header: '.accordion-header'
	});

	// Set default total registrants
	totalRegistrantsForEvent = $('.accordion-header').size();

	// Set default for displaying registrant num
	$('#view-reg-num > option[value="'+maxRegistrantsPerPage+'"]').attr('selected', true);

	// Set default displayed page and default page numbers
	showRegistrantPage(currentRegistrantPage);
	setAvailablePageNumbers();

	// When changing the number of registrants per page
	$('#view-reg-num').change(function() {changeRegistrantsPerPage($(this).val());});

	// When changing the page number
	$('#view-reg-page').change(function() {showRegistrantPage($(this).val());});

	// When clicking next or prev page
	$('.view-reg-change-page').click(nextOrPreviousPage);

	// Shorten long race names
	shortenRaceNames();
});

function shortenRaceNames() {
	var race = $('.accordion-header .registered-race-name');

	race.each(function() {
		$(this).html(fitStringToWidth($(this).text(), 180));
	});
}

function nextOrPreviousPage() {
	var next = $(this).attr('alt') == 'Next';
	var min = 0;
	var max = $('#view-reg-num').children().size();
	var page = next ? currentRegistrantPage+1:currentRegistrantPage-1;

	if(page > min && page <= max) {
		showRegistrantPage(page);
	}
}

function changeRegistrantsPerPage(per_page) {
	maxRegistrantsPerPage = per_page;
	setAvailablePageNumbers();
	showRegistrantPage(currentRegistrantPage);
}

function showRegistrantPage(page_num) {
	currentRegistrantPage = page_num;

	// find first available page if page_num is too high
	var pages = $('#view-reg-page').children().size();
	while(currentRegistrantPage > pages) {
		currentRegistrantPage--;
	}

	// set page number to be selected on <select> object
	$('#view-reg-page').children('option[value="'+currentRegistrantPage+'"]').attr('selected', true);

	// calculate what to show and what to hide
	var first = (currentRegistrantPage-1)*maxRegistrantsPerPage;
	var last = currentRegistrantPage*maxRegistrantsPerPage-1;
	var show = $([]);
	var hide = $([]);
	
	$('.accordion-header').each(function(idx) {
		if(idx >= first && idx <= last) {
			show = show.add(this);
		}
		else
			hide = hide.add(this);
	});

	closeAllAccordion();
	hide.hide();
	show.show();
}

function setAvailablePageNumbers() {
	var total_pages = Math.ceil(totalRegistrantsForEvent/maxRegistrantsPerPage);

	$('#view-reg-page').empty();
	for(var i = 0; i < total_pages; i++) {
		var value = i+1;
		$('#view-reg-page').append('<option value="'+value+'">'+value+'</option>');
	}
}

function closeAllAccordion() {
	$('#registrants-wrap').accordion('activate', false);
}