$(document).ready(function() {
var opacity = .4;
doOnStartup();
function doOnStartup() {
doMonthPrepend($('#event-content .is-active table').attr('class'));
toggleMonthFocus();

    /*
     * Highlight the corresponding day on
     * calendar while hovering over an
     * event
     */
    $("ul.event-list > li").hover(eventOnLiHover, eventOnLiHover);

    /*
     * Highlight all the corresponding
     * events for a single day while
     * hovering over it on the calendar
     */
    $("td.is-event").hover(eventOnTdHover, eventOnTdHover);

    /*
     * Toggle focus on each month.
     * Gives focus on click
     */
	function toggleMonthFocus() {
        $("div.inactive").fadeTo('fast', opacity);
        $("div.month-container").click(function() {
            var monthYear = $(this).children('table').attr('class');
            doFocus(monthYear);
		});
	}

    function doFocus(monthYear) {
		var target = $('table.'+ monthYear).parent('div.month-container');
		var turnOff = $('div.is-active');

		if(!isActive(target.attr('class'))) { // when non-focus object is clicked
			toggleEventView(monthYear); // show desired events

			target.toggleClass('inactive'); // move focus to clicked object
			target.toggleClass('is-active');

			turnOff.toggleClass('inactive');
			turnOff.toggleClass('is-active');

                        target.fadeTo('fast', 1);
                        turnOff.fadeTo('fast', opacity);
		}
    }

	/*
	* When months are clicked,
	* toggle the view of
	* corresponding events
	*/
	function toggleEventView(monthYear) {
		var divTurnOff = $('div.event-wrap-on');
		var divTurnOn = $('div#event-wrap-'+ monthYear);

		divTurnOff.toggleClass('event-wrap-on');
		divTurnOff.toggleClass('event-wrap-off');

		divTurnOn.toggleClass('event-wrap-on');
		divTurnOn.toggleClass('event-wrap-off');

	}

	/*
	* When event days on the
	* calendar are clicked, reorder
	* the list
	*/
	$("td.is-event").click(function() {
		var date = dateFromClass($(this).attr("class"));
		var ulist = $('div.event-wrap-on ul.event-list');
		var targetLi = ulist.children('li.'+date);
		var liMonth = firstPartSplit(targetLi.attr('class'));
		var tdMonth = firstPartSplit(date);

		if(liMonth == tdMonth) {
                    targetLi.fadeOut('fast', function() {
                            $(this).prependTo(ulist);
                            $(this).fadeIn('fast');
                    });
		}
	});

	/*
	* When quicklinks are clicked, show
	* corresponding month and neighboring months
	*/
	$('.quickmonth a[class!="change-year"]').click(function() {
		var date = $(this).attr('class');

		doMonthPrepend(date);
		showOnlyThreeMonths();
		doFocus(date);

	});

	/*
	* Show the event information window
	*/
	$('ul.event-list li.event').click(showWindow);

        /*
         * Show the previous/next year
         */
        $('.change-year').click(function() {
            var year = firstPartSplit($(this).attr('id'))
            var month = firstPartSplit($('.is-active table').attr('class'));
            reloadEvents(month, year);
	});

        /*
         * Return to the current month
         */
        $('#current-month').click(function() {
            reloadEvents(0, 0);
        });

        /*
         * Loads the next/previous year's months and replaces
         * the current information with the new info
         */
        function reloadEvents(month, year) {
            showLoader();
            $('#content').load("index.php #event-content", {'year':year, 'month':month}, function() {
                doMonthPrepend(month+'-'+year);
                doOnStartup();
				showOnlyThreeMonths()
            });
        }
}
});

function showLoader() {
    $('#event-loading').removeClass('no-show');
}

function showWindow() {
	var data = $(this).find('div.content').html();
	/*
	 * Each event contains a set of hidden divs that hold
	 * additional information about the event:
	 */

	var windowBoxW = 500;
	var windowBoxH = 650;

	$('<div id="overlay">').css({

		width: $(document).width(),
		height: $(document).height(),
		opacity: 0.5

	}).appendTo('body').click(function(){

		$(this).remove();
		$('#windowBox').remove();
 	});

 	$('body').append('<div id="windowBox">'+data+'</div>');

 	$('#windowBox').css({
		width: windowBoxW,
		height: windowBoxH,
		left: ($(window).width() - windowBoxW)/2,
		top: ($(window).height() - windowBoxH)/2
	});

}

function doMonthPrepend(date) {
	var startMonth = firstPartSplit(date)*1+1;
	var container = $('div.three-month');

	if(startMonth == 13)
		startMonth = 12;

	for(var i = 0; i < 3; i++) {
		var move = $('table[class^='+ startMonth +'-]').parent('div');
		container.prepend(move);
		startMonth--;
	}
}

function showOnlyThreeMonths() {
	$('div.month-container table').each(function(index) {
		if(index > 2)
		$(this).addClass('no-show');
		else
		$(this).removeClass('no-show');
	});
}
/*
* Compares two dates to see if the months
* are next to each other
*/
function isNeighbor(first, second) {
	var date = firstPartSplit(first)*1;
	var compare = firstPartSplit(second)*1;

	if(date+1 == compare || date-1 == compare)
		return true;
	else
		return false;
}

/*
 * Get first class which should
 * be the date
 */
function dateFromClass(className) {
    var classArray = className.split(' ');
    var newClass = classArray[0];

    return newClass;
}

/*
* Returns the first part of a string split by hyphens
* eg. "2-3" => "2", "4-10-2010" => "4"
*/
function firstPartSplit(date) {
	var dateArray = date.split('-');
	date = dateArray[0];

	return date;
}

function isActive(className) {
    classArray = className.split(' ');
    var active = 'is-active';

    if(jQuery.inArray(active, classArray) >= 0)
        return true;
    else return false;
}

function eventOnLiHover() {
	var day = $(this).attr('class').split(' ');

    $("td#day-"+ day[0]).toggleClass('highlight-event');
    $("td#day-"+ day[0]).toggleClass('is-event');
}

function eventOnTdHover() {
	var day = $(this).attr('class').split(' ');

    $("li."+ day[0]).toggleClass('highlight-event');
    $("td#day-"+ day[0]).toggleClass('highlight-event');
    $("td#day-"+ day[0]).toggleClass('is-event');
}