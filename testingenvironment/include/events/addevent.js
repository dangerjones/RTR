{
var addEventFormIsLoading = false;

$(document).ready(function() {

	// Initialize datepicker
	$('input[name*="-date"]').live('focus', function() {
		$(this).datepicker({
			showButtonPanel: true
		});
	});

	// Reformat the time input to make it valid
	$('input[name="race-time[]"]').live('blur', fixTime);

	// Duplicate races (add more)
	$('#add-race').live('click', addRace);

	// Remove a race
	$('.remove-race').live('click', removeRace);

    // Display extra shirt options
    $('input[name="event-shirts-available"]').live('click', function() {
        var target = $('.toggle-shirts');
        var show = $(this).attr('value') == 'true' ? true : false;
        var hidden = $('.toggle-shirts:hidden');

        if(show && hidden.size() > 0) {
            target.slideDown().highlightFade();
        }
        else if(!show) {
            if(hidden.size() == 0)
                $(this).parent().highlightFade();
            target.slideUp('fast');
			$('input[name="event-no-shirt"][value="false"]').click();
        }
    });

    // Display shirt discount
    $('input[name="event-no-shirt"]').live('click', function() {
        var target = $('.toggle-shirt-discount');
        var show = this.value == 'true' ? true : false;

        if(show) {
            target.slideDown(function() {
                $(this).children().focus();
            }).highlightFade();
        } else {
            target.slideUp('fast');
            $(this).parent().highlightFade();
        }
    });

    // Display extra registration options
    $('input[name="event-registration-methods[]"]').live('click', function() {
        var target = $(this).parent().siblings('input');
        var checked = $(this).is(':checked') ? true : false;

        if(checked) {
            target.css('visibility', 'visible').hide().fadeIn().focus();
        } else {
            target.css('visibility', 'hidden');
        }

        $(this).closest('li').highlightFade();
    });

    // Allow custom questions
    $('input[name="event-custom-q"]').live('click', allowQ);

    // Add more questions
    $('#add-question').live('click', addOneQ);

	// Remove question
	$('#remove-question').live('click', deleteOneQ);

    // Add more answers
    $('.add-answer').live('click', addAnswer);

	// Delete answer
	$('.delete-answer').live('click', deleteAnswer);

	// Submit for on form's submit button's click
	$('#submit-wrap input:submit').live('click', function() {$('#event-form').submit();});

	// Remove the uploaded file from form submission
	$('.uploaded-file img').live('click', removeUploadedFile);

	// Show my uploads
	$('.get-my-uploads').live('click', function() {
		var pass = this;
		showMyUploads(function() {
			onMyUploadsFileChosen(pass);
		});
	});

	// Show service fee question on form
	$('input[name="event-reg-here"]').livequery('change', toggleServiceFeeInfo);

	// Show more information about service fees
	$('#service-fee-question').live('click', serviceFeeDialog);

	// Add more promo coupons
	$('#add-registration-coupon').live('click', addMorePromoCoupons);

	// Remove promo coupons
	$('#remove-registration-coupon').live('click', removePromoCoupons);

	// Display more info for permalinks
	$('.what-is-permalink').live('click', showPermalinkInfo);

	// Get permalink suggestions
	$('#get-permalink-suggestions').live('click', openPermalinkSuggestions);

	// Fill in permalink field with chosen permalink
	$('.use-permalink-suggestion').live('click', fillInChosenPermalink);

	// Toggle price fields according to whether or not the race is free
	$('input[name="race-free[]"]').live('click', togglePriceFields);
});

function onMyUploadsFileChosen(clicked) {
	var container = $(clicked).closest('.uploaded-file');

	$('#show-my-uploads-dialog .my-uploads-file').click(function() {
		container.empty().removeClass('uploaded-no-file')
			.html('<img src="/img/round-cancel.png" alt="Remove" title="Remove file" />'+
				'<span class="filename">'+fitStringToWidth($(this).text(), 250)+'</span>')

		$('#show-my-uploads-dialog').dialog('close');
	});
}

function togglePriceFields() {
	var box = $(this);
	var checked = box.is(':checked');
	var priceFields = box.closest('ul.race-holder').children('.race-prices');

	if(checked) {
		priceFields.fadeOut('fast');
		box.parent().children('.race-free-filler').attr('name', 'race-x');
	} else {
		priceFields.stop(true, true).show();
		box.parent().children('.race-free-filler').attr('name', 'race-free[]');
	}
}

function fillInChosenPermalink() {
	var permalink = $(this).text();
	$('input[name="event-permalink"]').val(permalink);
	$('#permalink-suggestions-loaded').dialog('close');
}

function openPermalinkSuggestions() {
	var name = $('input[name="event-name"]').val();
	var date = $('input[name="event-date"]').val();

	$('<div id="permalink-suggestions-loaded"><div class="ajax-loader"></div>').dialog({
		modal: true,
		width: 500,
		title: 'Permalink suggestions',
		close: function() {$(this).dialog('destroy').remove();},
		buttons: {
			'Close window': function() {$(this).dialog('close');}
		}
	});

	$.post('/include/requests/permalinkSuggestions.php', {name:name,date:date}, function(data) {
		var show;
		var num = data.length;

		if(num > 0) {
			show = '<h3>You may use a suggestion below</h3>'
			show += '<ul id="permalink-suggestion-list">';
			for(var i in data) {
				show += '<li title="Use this permalink">';
				show += '<a class="use-permalink-suggestion">'+data[i]+'</a>';
				show += '</li>'
			}
			show += '</ul>';
		} else {
			show = '<p>No suggestions are available according to the data you provided. Please make sure you have filled in all required information pertaining to the event and try again.</p>';
		}

		$('#permalink-suggestions-loaded').html(show);
	}, 'json');
}

function showPermalinkInfo() {
	var d = $('#permalink-information');

	d.dialog({
		modal: true,
		width: 600,
		close: function() {$(this).dialog('destroy');},
		buttons: {
			'Close window': function() {$(this).dialog('close');}
		}
	})
}

function removePromoCoupons() {
	var target = $(this).closest('li').prev();
	var coupons = $('.promo-codes');
	if(coupons.length > 1) {
		target.slideUp(function() {$(this).remove();});
	}
	if(coupons.length == 2) {
		$(this).hide();
	}
}

function addMorePromoCoupons() {
	var buttons = $(this).closest('li');
	var clone = buttons.prev().clone();
	clone.hide().insertBefore(buttons).slideDown().highlightFade().find('input').val('');
	$('#remove-registration-coupon').show();
}

function serviceFeeDialog() {
	$('#service-fee-information').dialog({
		modal: true,
		width: 550,
		close: function() {$(this).dialog('destroy');},
		buttons: {
			'Close window': function() {$(this).dialog('close');}
		}
	});
}

function toggleServiceFeeInfo() {
	var target = $('input[name="event-reg-fee"]').parent();

	if($(this).is(':checked')) {
		target.slideDown().highlightFade();
	} else {
		target.slideUp().highlightFade();
	}
}

function removeUploadedFile() {
	var link = '<a class="get-my-uploads">Select From My Uploads</a>';

	$(this).parent().empty().append(link).addClass('uploaded-no-file');
}

function isLoading() {
	return addEventFormIsLoading;
}

function onSubmit() {
	showLoader();
	$('#submit-wrap input:submit').attr('disabled', 'disabled');
}

function onSuccess(data) {
	// Errors found
	if(data.length > 0) {
		$('.form-errors').hide().fadeIn('slow');
		hideAddEventLoader();
		$('#submit-wrap input:submit').removeAttr('disabled');
		location.hash = 'error';
	} else {
		$(this).closest('.dialogBox').dialog('close');
		$('<div>').load('/include/requests/html/addEventSuccess.php', {}, function() {
			$(this).dialog({
				modal: true,
				width: 750,
				title: 'Event added!',
				close: function() {$(this).dialog('destroy').remove();},
				buttons: {
					'Close window': function() {$(this).dialog('close');}
				}
			});
		});
	}
}

function showLoader() {
	$('#submit-wrap img').css('visibility', 'visible');
	addEventFormIsLoading = true;
}

function hideAddEventLoader() {
	$('#submit-wrap img').css('visibility', 'hidden');
	addEventFormIsLoading = false;
}

function allowQ() {
	var show = this.value == 'true' ? true:false;
	var target = $('.hide-question, .q-buttons');

	if(show) {
		target.slideDown().highlightFade();
		target.find('.delete-answer').hide();
	} else {
		target.slideUp();
		$('.q-buttons').prev().highlightFade();
	}
}

function addOneQ() {
	var file = '/include/requests/html/add_question.php';
	var questions = $('.hide-question');
    var target = questions.filter(':last');

	$.post(file, {'num':questions.size()+1}, function(data) {
		$(data).hide().insertAfter(target).slideDown().highlightFade()
			.find('a').button().filter('.delete-answer').hide();

	});

	if(questions.size() == 4) {
		$(this).hide();
	}
}

function deleteOneQ() {
    var target = $('.hide-question:last');
	var questions = $('.hide-question');

	$('#add-question').show();
	if(questions.size() == 1) {
		$('input[name="event-custom-q"]').click();
	} else {
		target.slideUp(function() {
			$(this).remove();
		});
	}
}

function addAnswer() {
	var target = $(this).parent().prev();
	var clone = target.clone();

	clone.hide().insertAfter(target).slideDown().highlightFade()
		.children('input').val('');
	$(this).next().show()
}

function deleteAnswer() {
	var target = $(this).parent().prev();
	var answers = $(this).closest('ul').find('input[name^="event-answer"]');
	
	if(answers.size() == 3)
		$(this).hide();

	target.slideUp(function() {
		$(this).remove();
	});
}

function addRace() {
	var target = $(this).parent();
	var num = $('.race-holder').size();
	if(typeof addRace.isntLoading == 'undefined')
		addRace.isntLoading = true;

	if(addRace.isntLoading) {
		addRace.isntLoading = false;
		$.post('/include/requests/html/add_race.php', {'num':num+1}, function(data) {
			$(data).insertBefore(target).hide().slideDown(function(){
				addRace.isntLoading = true;
			}).highlightFade().find('.remove-race').button();
		});
	}
}

function fixTime() {
	var target = $('input[name="race-time[]"]');
	target.each(function() {
		if($(this).val() <= 12 && $(this).val() > 0)
			$(this).val($(this).val() + ':00');
	});
}

function removeRace() {
	$(this).closest('.race-holder').slideUp(function() {
		$(this).remove();
		rewriteRaceNums();
	});

}

function rewriteRaceNums() {
	var races = $('.race-holder');

	races.each(function(idx) {
		$(this).find('h3').text('Race '+ (idx+1) +':');
		$(this).find('span.ui-button-text').text('Delete Race #'+ (idx+1));
	});
}
}