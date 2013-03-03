$(document).ready(function() {
	/*
	 * Highlight the corresponding day on calendar while hovering
	 * over an event
	 */
	$('.event-list > li, td.is-event').live('hover', highlightEventDays);

	/*
	 * Change month's focus on click
	 */
	$('.month-container table').live('click', function() {
		var date = $(this).attr('class');
		doFocus(date);
		//location.hash = 'month='+date;
	});
	// Page default on load
	$('.month-container:eq(1) > table').click();

	/*
	 * When event days on the calendar are clicked, reorder the list
	 * and bring them to the top
	 */
	$("td.is-event").live('click', bringToTop);

	/*
	 * When quicklinks are clicked, show corresponding month and neighboring months
	 */
	$('.quickmonth a[class!="change-year"]').click(showMonth);

	/*
	* Show the event information window
	*/
	$('ul.event-list li.event').click(showEventWindow);

	/*
	 * Add an event link
	 */
	$('#add-event-link').click(addEventForm);

	/*
	 * Close the event/addevent dialogs
	 */
	$('.close-dialog').live('click', closeEventDialog);
	$('.close-dialogBox').live('click', closeDialogBox);

	/*
	 * Add clicked class when clicked
	 */
	$('.extra img').click(function() {$(this).addClass('clicked');});

	/*
	 * Initialize tooltip for extra buttons
	 */
	$('.extra img').tooltip({
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

function initializeUploadify() {
	var uploadifyDefaults = {
		'uploader':		'/include/core/plugins/uploadify/uploadify.swf',
		'script':		'/include/core/plugins/uploadify/uploadify.php',
		'checkScript':	'/include/core/plugins/uploadify/check.php',
		'cancelImg':	'/include/core/plugins/uploadify/cancel.png',
		'scriptData':	{cookie:document.cookie},
		'auto':			true,
		'width':		130,
		'fileDesc':		'Images (*.jpg; *.jpeg; *.gif; *.png)',
		'fileExt':		'*.jpg; *.jpeg; *.gif; *.png',
		'hideButton':	true,
		'wmode':		'transparent',
		'onComplete':	uploadComplete,
		'onProgress':	showUploadSpeed
	}

	$('#event-banner').uploadify(uploadifyDefaults);

	$('#event-course-map').uploadify(uploadifyDefaults);

	uploadifyDefaults.fileDesc = 'PDF or Images (*.pdf; *.jpg; *.jpeg; *.gif; *.png)';
	uploadifyDefaults.fileExt = '*.pdf; *.jpg; *.jpeg; *.gif; *.png';
	$('#event-entry-form').uploadify(uploadifyDefaults);

	var uploaded = '<a class="browse-button button"></a><div class="uploaded-file uploaded-no-file">'+
			'<a class="get-my-uploads">Select From My Uploads</a></div>';
	var banner = $(uploaded);
	banner.filter('.browse-button').text('Upload Banner');
	var form = $(uploaded);
	form.filter('.browse-button').text('Upload Entry Form');
	var map = $(uploaded);
	map.filter('.browse-button').text('Upload Course Map');

	$('#event-banner').next().after(banner);
	$('#event-entry-form').next().after(form);
	$('#event-course-map').next().after(map);
}

function updateFileUploadValues() {
	var banner = $('#event-bannerQueue').prev().children('.filename').text();
	var form = $('#event-entry-formQueue').prev().children('.filename').text();
	var map = $('#event-course-mapQueue').prev().children('.filename').text();

	try {
		$('input[name="event-banner"]').val(banner);
	} catch(e) {}
	try {
		$('input[name="event-entry-form"]').val(form);
	} catch(e) {}
	try {
		$('input[name="event-course-map"]').val(map);
	} catch(e) {}
}

function showUploadSpeed(e, queueId, f, data) {
	var container = 'div[id$="'+queueId+'"]';
	var speed = $(container+' .speed');
	var percent = $(container+' .percentage');

	if(speed.size() == 0)
		percent.after('<span class="speed">');

	speed.text(' '+data.speed+' KB/s');
}

function uploadComplete(event, queueId, fileObj, response) {
	if(response == 1) { // No errors found
		var filenameHolder = $('div[id$="'+queueId+'"]').parent().prev();
		filenameHolder.removeClass('uploaded-no-file')
			.html('<span class="filename">'+fitStringToWidth(fileObj.name, 250)+'</span>')
			.prepend('<img src="/img/round-cancel.png" alt="Remove" title="Remove file" />');
	}
	else {
		alert(response);
	}
}

function closeDialogBox() {
	$('.dialogBox').dialog('close');
}

function closeEventDialog() {
	$(this).closest('.event-holder').dialog('close');
}

function addEventForm() {
	var button = $(this);
	button.attr('disabled', true);
	showLoginLoad();

	$.post('/include/requests/addEvent.php', {}, function(data) {
		showWindow(data, 925, 'Add Event');
		initializeUploadify();

		$('.button').button();
		$('#submit-wrap input:submit').button();
		$('#remove-registration-coupon').hide();

		$('#event-form').ajaxForm({
			// target is where the errors are sent
			target: '.form-errors',
			beforeSubmit: onSubmit,
			success: onSuccess,
			beforeSerialize: function() {location.hash = 'ok';fixTime();updateFileUploadValues();}
		});
		button.attr('disabled', false);
		hideLoginLoad();
	});

	return false;
}

/*
 * Focus on the table and de-focus the others
 */
function doFocus(monthYear) {
	var opacity = .4;
	var target = $('table.'+ monthYear).parent();
	var turnOff = $('table:not(table.'+ monthYear +'):not(:hidden)').parent();

	if(!target.hasClass('is-active')) { // when unfocused object is clicked
		toggleEventView(monthYear); // show desired events

		target.addClass('is-active');
		turnOff.removeClass('is-active');

		target.fadeTo('fast', 1);
		turnOff.fadeTo('fast', opacity);
	}
}

function showMonth() {
	var container = $('.three-month');
	var date = $(this).attr('class').split('-');
	var month = date[0]*1;
	var year = date[1]*1;
	var show = 3;
	var start = month;

	if(month == 1) {
		start = 3;
	}
	else if(month < 12) {
		start = month+1;
	}

	for(var i = start; i > start-show; i--) {
		var id = 'calendar-month-'+i+'-'+year;
		$('#'+id).show().closest('.month-container').prependTo(container)
			.find('table.no-show').toggleClass('no-show');
	}

	$('table[id^="calendar-month"]:gt(2)').hide().toggleClass('no-show');

	doFocus(month+'-'+year);

	return false;
}

function bringToTop() {
	var date = $(this).attr("class").split(' ')[0];
	var ulist = $('.event-wrap-on .event-list');
	var targetLi = ulist.children('li.'+date);
	if(targetLi.length > 0) {
		targetLi.fadeOut('fast', function() {
			$(this).prependTo(ulist);
			$(this).fadeIn('fast');
		});
	}
}

function highlightEventDays() {
	var doList = !$(this).hasClass('event');
	var day = $(this).attr('class').split(' ')[0];

	if(doList && day)
		$("li."+ day).toggleClass('highlight-event');
	$("td#day-"+ day).toggleClass('highlight-event');
}

/*
 * When months are clicked, toggle the view of corresponding events
 */
function toggleEventView(monthYear) {
	var divTurnOff = $('div.event-wrap-on');
	var divTurnOn = $('div#event-wrap-'+ monthYear);

	divTurnOff.toggleClass('event-wrap-on');
	divTurnOff.toggleClass('event-wrap-off');

	divTurnOn.toggleClass('event-wrap-on');
	divTurnOn.toggleClass('event-wrap-off');
}

function showEventWindow() {
	var extraButtonsClicked = false;
	$(this).find('.extra img').each(function() {
		if($(this).hasClass('clicked')) {
			$(this).removeClass('clicked');
			extraButtonsClicked = true;
			return;
		}
	});

	if(extraButtonsClicked)
		return;

	var data = $(this).find('div.content').html();
	var e_id = $(this).find('div.content').attr('id');
	var title = $(this).find('.e-title').text();
	var target = $('<div class="event-holder">').html(data);

	location.hash = e_id.split('-')[1];

	target.dialog({
		modal: true,
		width: 700,
		title: title,
		close: function() {$(this).dialog('destroy').remove();location.hash = 'none';}
	}).find('.event-buttons a').button();

	target.find('.event-date .event-race-results').tooltip({
		showURL: false
	});

	target.find('.course-map').fancybox({
		hideOnOverlayClick: true
	});
}

function showWindow(data, w, title) {
	$('<div class="dialogBox">').html(data).appendTo('body').dialog({
		modal: true,
		width: w,
		title: title,
		close: function() {$(this).dialog('destroy').remove();}
	});

}