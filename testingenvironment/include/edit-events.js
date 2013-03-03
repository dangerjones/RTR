$(document).ready(function() {
	// Initialize event details ajax form
	$('#edit-event-details').ajaxForm({
		beforeSubmit: onSubmit,
		success: onSuccess
	});

	// Initialize datepicker
	$('#event-date').datepicker({
		showButtonPanel: true
	});

	// Set shirts' initial state
	setShirtsState();
	setRegMethodsState();

	var readonly = 'input[readonly="readonly"]';
	// Toggle shirts available
	$('input[name="event_shirts_available"]').not(readonly).click(toggleShirtsAvailable);

	// Toggle shirt discount
	$('input[name="event_no_shirt"]').not(readonly).click(toggleShirtDiscount);

	// Toggle registration methods
	$('.reg-method input[type="checkbox"]').not(readonly).click(toggleRegistrationMethods);
	$('#event-reg-here').not(readonly).click(toggleRegistrationMethodRegHere);

	// Cancel shirt toggle for readonly inputs
	$(readonly).click(function() {return false;});

	// Open grant-user-access dialog
	$('#grant-access-to-user').click(openAccessToUserDialog);

	// Remove a user with access
	$('.remove-access-to-user').live('click', removeUserWithAccess);

	// On submit to add a user
	$('#grant-access').submit(function() {showAddUserLoader();giveAccessToUser();return false;});

	// On edit/add a race button click (opens dialog)
	$('#edit-event-race-form :submit').click(function() {onEditDeleteRace($(this).val());return false;});

	// On race edit submit
	$('#edit-race-form').livequery('submit', function() {formatRaceTime();doEditRace();return false;})

	// Format race start time
	$('#edit-race-time').live('blur', formatRaceTime);

	// Add a new race
	$('#add-new-race').click(addNewRaceForm);

	// On add new race form submit
	$('#add-new-race-form').livequery('submit', function() {formatRaceTime();addNewRace();return false;});

	// Remove promo coupon
	$('.remove-coupon').live('click', removeCouponDialog);

	// Add new promo coupon
	$('#add-new-coupon').click(addNewPromoCouponDialog);

	// On submit of new coupon form
	$('#add-new-coupon-form').submit(function() {addNewPromoCoupon();return false;});

	// Disable/enable coupon
	$('.enable-coupon, .disable-coupon').live('click', toggleCoupon);

	// Toggle price fields according to whether or not it's a free race
	$('#edit-race-free').live('click', function() {toggleEditPriceFields(false);});

	// Open dialog with form in it for invite and add functionality
	$('#invite-and-add-user').click(openInviteAndAddForm);

	// Initialize uploadify for new uploads
	var uploadifyDefaults = {
		'uploader':		'/include/core/plugins/uploadify/uploadify.swf',
		'script':		'/include/core/plugins/uploadify/uploadify.php',
		'checkScript':	'/include/core/plugins/uploadify/check.php',
		'cancelImg':	'/include/core/plugins/uploadify/cancel.png',
		'scriptData':	{cookie:document.cookie},
		'auto':			true,
		'fileDesc':		'PDF or Images (*.pdf; *.jpg; *.jpeg; *.gif; *.png)',
		'fileExt':		'*.pdf; *.jpg; *.jpeg; *.gif; *.png',
		'wmode':		'transparent',
		'onComplete':	uploadComplete,
		'onProgress':	showUploadSpeed,
		'buttonText':	'Upload a File'
	}
	$('#edit-upload-new-file').uploadify(uploadifyDefaults);

	// Open my uploads to edit
	$('.change-file').click(function() {
		var clicked = this;
		showMyUploads(function(d) {
			onMyUploadOpen(d, clicked);
		});
	});

	// Remove uploaded file
	$('.remove-file').click(openRemoveFileConfirmation);

	// Make sure hidden things are hidden
	$('.no-show').hide();

	// Show my uploads for race results
	$('#edit-race-add-results-to-race').click(function() {
		showMyUploads(function(d) {
			onMyUploadOpenForRaceResults(d);
		})
	});

	// Initialize ajax form for race results
	$('#edit-event-assign-race-results-form').ajaxForm({
		beforeSubmit: onRaceResultsSubmit,
		success: onRaceResultsSuccess
	});

	// Open confirmation dialog for deleting race results
	$('.edit-event-remove-race-results').live('click', openRemoveRaceResultsConfirmation);

	// Delete event confirmation
	$('#edit-event-delete-event').submit(function() {deleteEventConfirmation();return false;});

	// New question button pressed. Open dialog and load form
	$('#edit-event-new-custom-question').click(function() {openCustomQuestionFormDialog();});

	// Edit question button pressed. Open dialog and load form
	$('#edit-event-edit-custom-question').click(function() {
		openCustomQuestionFormDialog($('#edit-event-all-custom-questions').val());
	});

	// Delete question button pressed. Open confirmation
	$('#edit-event-delete-custom-question').click(openDeleteQuestionConfirmation);

});

function openDeleteQuestionConfirmation() {
	var message = 'Are you sure you want to delete this question? Deleting is irreversible!';
	var d = $('<div id="edit-event-delete-question-confirmation">');

	d.dialog({
		modal: true,
		title: 'Are you sure?',
		width: 400,
		close: function() {$(this).dialog('destroy').remove();},
		buttons: {
			'Cancel': function() {d.dialog('close');},
			'Delete': function() {doQuestionDelete(function() {d.dialog('close');});}
		}
	}).html('<p>'+message+'</p><div class="submit-wrap"><img src="/img/loaderbar.gif" class="invisible" /></div>');
}

function doQuestionDelete(callback) {
	var e_id = $('input[name="e_id"]').val();
	var q_id = $('#edit-event-all-custom-questions').val();
	showEditEventLoader('edit-event-delete-question-confirmation');

	$.post('/include/requests/editEventProcess.php', {e_id:e_id,q_id:q_id,edit_type:'delete-custom-q'}, function() {
		reloadCustomQuestions(function() {
			openSuccessDialog('Question deleted!', 400, true, false);
			dialogFadeOut($('.small-success').parent('div'));

			if(typeof callback == 'function')
				callback.call(this);
		});
	});

}

function openCustomQuestionFormDialog(q_id) {
	var e_id = $('input[name="e_id"]').val();
	var d = $('<div id="edit-event-custom-question-form-dialog">');

	if(!q_id > 0)
		q_id = 0;

	d.html('<div class="ajax-loader">').dialog({
		modal: true,
		title: 'Custom Question',
		width: 500,
		close: function() {$(this).dialog('destroy').remove();}
	}).load('/include/requests/html/custom-question-form.php', {e_id:e_id,question_id:q_id}, function() {
		d.dialog('option', 'position', 'center').find('.button').button();

		// Add event watchers for buttons in dialog box
		var removeButton	= $('#edit-event-remove-a-question');
		var AddButton		= $('#edit-event-add-a-question')

		AddButton.click(addQuestionAnswer);
		removeButton.click(removeQuestionAnswer);

		if($('.answer-boxes').size() <= 2)
			removeButton.hide();

		d.find('form').ajaxForm({
			beforeSubmit: onCustomQFormSubmit,
			success: onCustomQFormSuccess
		});

		d.find('.bottom-buttons a').click(function() {
			d.dialog('close');
		});
	});
}

function onCustomQFormSubmit() {
	showEditEventLoader('edit-event-custom-questions-form');
}

function onCustomQFormSuccess(data) {
	var eBox = $('#edit-event-custom-question-form-dialog').find('.form-errors');

	if(data.length > 0) {
		eBox.hide();
		hideEditEventLoader('edit-event-custom-questions-form');
		eBox.html(data).fadeIn();
	} else {
		reloadCustomQuestions(function() {
			$('#edit-event-custom-question-form-dialog').dialog('close');
			openSuccessDialog('Question saved!', 400, true, false);
			dialogFadeOut($('.small-success').parent('div'));
		});
	}
}

function reloadCustomQuestions(callback) {
	var e_id = $('input[name="e_id"]').val();

	$.post('/include/requests/json/getCustomQuestions.php', {e_id:e_id}, function(data) {
		var select = $('#edit-event-all-custom-questions');

		select.empty();
		for(var i = 0; i < data.length; i++) {
			select.append('<option value="'+data[i].id+'">'+data[i].question+'</option>')
		}

		if(typeof callback == 'function')
			callback.call(this);
	}, 'json');
}

function addQuestionAnswer() {
	$('#edit-event-remove-a-question').show();

	var clone = $('#c-q-f-answer1').parent().clone();
	clone.children('input').val('');
	$('#edit-event-custom-questions-list').append(clone);
	clone.highlightFade();
}

function removeQuestionAnswer() {
	var answers = $('.answer-boxes');
	if(answers.size() <= 3)
		$('#edit-event-remove-a-question').hide();

	answers.filter(':last').parent().remove();
}

function deleteEventConfirmation() {
	var e_id = $('input[name="e_id"]').val();

	$('<div id="edit-event-delete-event-dialog">').dialog({
		modal: true,
		title: 'Delete event?',
		width: 400,
		close: function() {$(this).dialog('destroy').remove();},
		buttons: {
			'Cancel': function() {$(this).dialog('close');},
			'Delete': function() {doDeleteEvent(e_id, $(this));}
		}
	}).html('<div class="form-errors"></div><p>Are you sure you want to delete this event? This is permanent!</p><div class="submit-wrap"><img src="/img/loaderbar.gif" alt="Loading..." class="invisible" /></div>');

}

function doDeleteEvent(e_id, d) {
	var eBox = $('#edit-event-delete-event-dialog .form-errors');
	var loader = $('#edit-event-delete-event-dialog').find('img.invisible');
	loader.removeClass('invisible');

	$.post('/include/requests/editEventProcess.php', {e_id:e_id,edit_type:'delete-event'}, function(data) {
		loader.addClass('invisible');
		eBox.hide();

		if(data.length > 0) {
			eBox.html(data).fadeIn();
		} else {
			d.dialog('close');
			openSuccessDialog('Event deleted', 400, true, false);
			setTimeout(function() {
				window.location = '/';
			}, 800);
		}
	});

}

function removeRaceResults(results_id) {
	var e_id = $('input[name="e_id"]').val();
	var loader = $('#remove-race-results-confirmation-dialog').find('img.invisible');
	loader.removeClass('invisible');

	$.post('/include/requests/updateEventUploads.php', {e_id:e_id,results_id:results_id,type:'remove-results'}, function() {
		reloadRaceResults(function() {
			$('#remove-race-results-confirmation-dialog').dialog('close');
		});
	});
}

function openRemoveRaceResultsConfirmation() {
	var results_id = $(this).attr('rel');
	var name = $(this).parent().find('a').text();

	$('<div id="remove-race-results-confirmation-dialog">').dialog({
		modal: true,
		width: 400,
		title: 'Remove race results?',
		close: function() {$(this).dialog('destroy').remove();},
		buttons: {
			'Cancel': function() {$(this).dialog('close');},
			'Remove': function() {removeRaceResults(results_id);}
		}
	}).html('<p>Are you sure you want to remove "'+name+'" from the race results?</p><div class="submit-wrap"><img src="/img/loaderbar.gif" alt="Loading..." class="invisible" /></div>');
}

function onRaceResultsSuccess(data) {
	var eBox = $('#edit-event-assign-race-results-form .form-errors');
	eBox.hide();

	if(data.length > 0) {
		hideEditEventLoader('edit-event-assign-race-results-form');
		eBox.html(data).fadeIn();
	} else {
		reloadRaceResults(function() {
			hideEditEventLoader('edit-event-assign-race-results-form');
			resetForm('edit-event-assign-race-results-form');
		});
	}
	
}

function reloadRaceResults(callback) {
	var e_id = $('input[name="e_id"]').val();
	var race_results = $('#edit-event-current-race-results');

	race_results.load('/include/requests/html/getRaceResults.php', {e_id:e_id,editing:true}, function() {
		race_results.highlightFade();
		if(typeof callback == 'function')
			callback.call(this);
	});
}

function onRaceResultsSubmit() {
	showEditEventLoader('edit-event-assign-race-results-form');
}

function onMyUploadOpenForRaceResults(my_uploads) {
	$('.my-uploads-file').click(function() {
		var filename = $(this).text();
		var pieces = filename.split('.');
		var ext = pieces[pieces.length-1];

		if(ext.toLowerCase() != 'pdf')
			showMyUploadsError('Only PDF files allowed!');
		else {
			$('#edit-event-results-filename').val(filename);
			my_uploads.dialog('close');
		}
	});
}

function openRemoveFileConfirmation() {
	var parent = $(this).parent();
	var type = parent.find('.change-file').attr('rel');
	var content = 'Are you sure you would like to remove this file from the '+type+'?';
	content += '<div class="submit-wrap"><img src="/img/loaderbar.gif" alt="Loading..." class="invisible" /></div>'

	$('<div id="edit-upload-remove-file-upload-confirmation">').html(content).dialog({
		modal: true,
		width: 350,
		title: 'Remove',
		close: function() {$(this).dialog('destroy').remove();},
		buttons: {
			'Cancel': function() {$(this).dialog('close');},
			'Remove': function() {
				showRemoveUploadLoader();
				removeUploadedFile(parent, type, $(this));
			}
		}
	});
}

function removeUploadedFile(parent, type, d) {
	var e_id = $('input[name="e_id"]').val();

	$.post('/include/requests/updateEventUploads.php', {e_id:e_id,type:'update-'+type,file:''}, function(data) {
		showRemoveUploadLoader(false);

		if(data.length > 0)
			alert(data);
		else {
			d.dialog('close');
			parent.children('input[readonly="readonly"]').val('').highlightFade({end:'#fff'});
			parent.children('.change-file').children('span').text('Add');
			parent.find('.view-uploaded-preview, .remove-file').hide();
		}
	});
}

function showRemoveUploadLoader(show) {
	var loader = $('#edit-upload-remove-file-upload-confirmation').find('.submit-wrap img');
	if(show != false)
		loader.removeClass('invisible');
	else
		loader.addClass('invisible');
}

function onMyUploadOpen(dialog, goes_to) {
	var e_id = $('input[name="e_id"]').val();
	var type = $(goes_to).attr('rel');

	$('.my-uploads-file').click(function() {
		var filename = $(this).text();

		showMyUploadsLoader();
		$.post('/include/requests/updateEventUploads.php', {e_id:e_id,type:'update-'+type,file:filename}, function(data) {
			showMyUploadsLoader(false);
			if(data.length > 0) {
				showMyUploadsError(data);
			} else {
				var readonlyInput = $('#edit-upload-'+type);
				var parent = readonlyInput.parent();
				var u_id = $('input[name="u_id"]').val();

				readonlyInput.val(filename);
				parent.children('.view-uploaded-preview').attr('href', '/uploads/'+u_id+'/'+filename).show().prev().show();
				parent.children('.change-file').children('span').text('Change')

				dialog.dialog('close');
				readonlyInput.highlightFade({end:'#fff'});
			}
		});
	});
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

	}
	else {
		alert(response);
	}
}

function openInviteAndAddForm() {
	var d = $('#invite-and-add-form');
	d.dialog({
		modal: true,
		width: 550,
		title: 'Invite a friend!',
		close: function() {$(this).dialog('destroy');}
	});

	d.find('.bottom-buttons a.button').click(function() {d.dialog('close')});

	d.children('form').ajaxForm({
		beforeSubmit: onInviteFormSubmit,
		success: onInviteFormSuccess
	});

	$('#invite-someone-to-edit').val('').focus();
	d.find('.form-errors').empty();
}

function onInviteFormSubmit() {
	showInviteUserLoader();
}

function onInviteFormSuccess(data) {
	var eBox = $('#invite-and-add-form').find('.form-errors');
	var e_id = $('input[name="e_id"]').val();
	eBox.hide().empty();

	if(data.length > 0) {
		eBox.html(data).fadeIn();
		hideInviteUserLoader();
	} else {
		$('#has-edit-access').load('/include/requests/hasEditAccess.php', {e:e_id}, function() {
			$('#invite-and-add-form').dialog('close');
			hideInviteUserLoader();
			$(this).add($(this).closest('p')).highlightFade();
		});
	}
}

function toggleEditPriceFields(quick) {
	var box = $('#edit-race-free');
	var checked = box.is(':checked');
	var fields = box.closest('ul').children('.edit-price-fields');

	if(checked) {
		if(quick)
			fields.hide();
		else
			fields.fadeOut('fast');
	} else {
		fields.stop(true, true).show();
	}
}

function toggleCoupon() {
	var e_id = $('input[name="e_id"]').val();
	var li_id = $(this).parent().attr('id');
	var c_id = $(this).attr('rel');

	showLoginLoad();
	$.post('/include/requests/editEventProcess.php',
	{e_id:e_id,coupon:c_id,edit_type:'toggle_coupon'}, function() {
		$('#edit-event-coupon-list').load('/include/requests/html/getCouponList.php', {e_id:e_id}, function() {
			$('#'+li_id).highlightFade();
			hideLoginLoad();
		});
	});
}

function addNewPromoCouponDialog() {
	$('#add-new-coupon-dialog').dialog({
		modal: true,
		width: 450,
		close: function() {$(this).dialog('destroy').children('.form-errors').hide();$(this).find('input').val('')},
		buttons: {
			Cancel: function() {$(this).dialog('close');},
			Add: addNewPromoCoupon
		}
	});
	$('#coupon-codename').focus();
}

function addNewPromoCoupon() {
	var e_id = $('input[name="e_id"]').val();

	$('#add-new-coupon-form').ajaxSubmit({
		data: {e_id:e_id,edit_type:'new_coupon'},
		beforeSubmit: function() {showEditEventLoader('add-new-coupon-dialog');},
		success: addNewCouponSuccess
	});
}

function addNewCouponSuccess(data) {
	var d = $('#add-new-coupon-dialog');
	var errors = d.children('.form-errors');
	var e_id = $('input[name="e_id"]').val();

	if(data.length > 0) { // on error
		hideEditEventLoader('add-new-coupon-dialog');
		errors.hide().html(data).fadeIn();
	} else {
		$('#edit-event-coupon-list').load('/include/requests/html/getCouponList.php', {e_id:e_id}, function() {
			d.dialog('close');
			$(this).highlightFade();
			hideEditEventLoader('add-new-coupon-dialog');
		});
	}
}

function removeCouponDialog() {
	var coupon = $(this).attr('rel');
	var name = $(this).closest('li').text().split(':')[0];

	$('<div id="remove-coupon-dialog">').html('<div class="form-errors"></div>'+
		'Are you sure you want to remove <strong>'+name+'</strong>?')
		.append('<div class="submit-wrap"><img src="/img/loaderbar.gif" alt="Loading..." class="invisible" />')
		.dialog({
		modal: true,
		width: 450,
		title: 'Remove coupon?',
		close: function() {$(this).dialog('destroy').remove();},
		buttons: {
			Cancel: function() {$(this).dialog('close');},
			Remove: function() {doRemoveCoupon(coupon);}
		}
	});
}

function doRemoveCoupon(coupon) {
	var e_id = $('input[name="e_id"]').val();
	var d = $('#remove-coupon-dialog');
	var errors = d.find('.form-errors');

	showRemoveCouponLoader();
	$.post('/include/requests/editEventProcess.php',
	{c_id:coupon,e_id:e_id,edit_type:'remove_coupon'}, function(data) {
		errors.hide();
		if(data.length > 0) { // on error
			errors.html(data).fadeIn();
			hideRemoveCouponLoader();
		} else {
			d.dialog('close');
			$('a[rel="'+coupon+'"]').closest('li').slideUp(function(){$(this).remove();})
				.parent().children().highlightFade();
		}
	});
}

function addNewRace() {
	$('#add-new-race-form').ajaxSubmit({
		beforeSubmit: showEditRaceLoader,
		success: onAddNewRaceSuccess
	});
}

function onAddNewRaceSuccess(data) {
	var errors = $('#edit-form-wrapper .form-errors');
	var e_id = $('input[name="e_id"]').val();

	errors.hide();
	hideEditRaceLoader();

	if(data.length > 0) { //on error
		errors.html(data).fadeIn();
	} else {
		reloadRaceList(e_id, openSuccessDialog('New race added successfully!', 450));
		$('#new-race-dialog').dialog('close');
	}
}

function addNewRaceForm() {
	var dialog = $('<div id="new-race-dialog">');
	var e_id = $('input[name="e_id"]').val();

	dialog.html('<div class="ajax-loader">').dialog({
		modal: true,
		width: 500,
		title: 'Add a new race',
		close: function() {$(this).dialog('destroy').remove();}
	});
	dialog.load('/include/requests/html/edit_race_form.php', {new_race:true,e_id:e_id}, function() {
		$(this).dialog('option', 'position', 'center').find('.button').button();
		dialog.find('.bottom-buttons a').click(function() {
			dialog.dialog('close');
		})
		$('#edit-race-early-date').datepicker({
			showButtonPanel: true
		});
		$('#edit-race-name').focus({
			showButtonPanel: true
		});
	});
}

function formatRaceTime() {
	var target = $('#edit-race-time');
	if(target.val() <= 12 && target.val() > 0)
		target.val(target.val() + ':00');
}

function doEditRace() {
	$('#edit-race-form').ajaxSubmit({
		beforeSubmit: showEditRaceLoader,
		success: function(data) {onEditRaceSuccess(data, $('#edit-race-name').val());}
	});
}

function onEditRaceSuccess(data, e_name) {
	var form_errors = $('#edit-form-wrapper .form-errors');
	form_errors.hide();
	if(data.length > 0) { // on error
		form_errors.html(data).fadeIn();
		hideEditRaceLoader();
	} else {
		var e_id = $('input[name="e_id"]').val();
		reloadRaceList(e_id, openSuccessDialog('Changes to <em>'+e_name+'</em> were saved successfully!', 450));
	}
}

function reloadRaceList(e_id, callback) {
	var select = $('#edit-event-races, #edit-event-race-results');
	$.post('/include/requests/getAllraces.php', {e_id:e_id}, function(races) {
		select.empty();
		for(var i = 0; i < races.length; i++) {
			select.append('<option value="'+races[i].id+'">'+races[i].name+'</option>');
		}
		$('#edit-race-dialog').dialog('close');
		callback;
	}, 'json');
}

function onEditDeleteRace(type) {
	var e_id = $('input[name="e_id"]').val();
	var race_id = $('#edit-event-races').val();
	var race_name = $('#edit-event-races :selected').text();

	if(type == 'Delete') {
		$('<div id="delete-race-confirmation">')
		.html('<div class="form-errors"></div><p>Are you sure you want to delete this race?\n\
		Once deleted, it cannot be undone!</p><div class="submit-wrap">\n\
		<img src="/img/loaderbar.gif" alt="Loading..." class="invisible" /></div>').dialog({
			modal: true,
			width: 400,
			title: 'Delete '+race_name+'?',
			close: function() {$(this).dialog('destroy').remove();},
			buttons: {
				'Cancel': function() {$(this).dialog('close');},
				'Delete': function() {deleteRace(e_id, race_id, race_name)}
			}
		});
	} else {
		$('<div id="edit-race-dialog">').html('<div class="ajax-loader">').dialog({
			modal: true,
			width: 500,
			title: 'Edit '+race_name,
			close: function() {$(this).dialog('destroy').remove();}
		}).load('/include/requests/html/edit_race_form.php', {e_id:e_id,race_id:race_id}, function() {
			toggleEditPriceFields(true);
			$(this).dialog('option', 'position', 'center');
			$('#edit-race-dialog .button').button();
			$('#edit-race-early-date').datepicker({
				showButtonPanel: true
			});
			$('#edit-race-dialog .bottom-buttons a').click(function() {
				$('#edit-race-dialog').dialog('close');
			});
		});
	}
}

function deleteRace(e_id, race_id, race_name) {
	showDeleteRaceLoader();
	var errors = $('#delete-race-confirmation .form-errors');
	$.post('/include/requests/editEventProcess.php', {e_id:e_id,race_id:race_id,edit_type:'delete_race'}, function(data) {
		errors.hide();
		if(data.length > 0) { // on error
			errors.html(data).fadeIn();
			hideDeleteRaceLoader();
		} else {
			reloadRaceList(e_id, openSuccessDialog('<em>'+race_name+'</em> was deleted successfully!', 450));
			reloadRaceResults();
			$('#delete-race-confirmation').dialog('close');
		}
	});
}

function removeUserWithAccess() {
	var email = $(this).attr('rel');
	var e_id = $('input[name="e_id"]').val();
	var target = $(this).closest('#has-edit-access');
	showLoginLoad();

	$.post('/include/requests/addRemoveAccessUsers.php', {email:email,e_id:e_id,type:'remove'}, function() {
		target.load('/include/requests/hasEditAccess.php', {e:e_id}, function() {
			target.add($(this).closest('p')).highlightFade();
			hideLoginLoad();
		});
	});
}

function giveAccessToUser() {
	var email = $('#access-email').val();
	var e_id = $('input[name="e_id"]').val();

	$.post('/include/requests/addRemoveAccessUsers.php', {email:email,e_id:e_id}, function(data) {
		var errors = $('#grant-access .form-errors');
		errors.hide();
		if(data.length > 0) { // error occurred
			errors.html(data).fadeIn();
			hideAddUserLoader();
		} else {
			$('#has-edit-access').load('/include/requests/hasEditAccess.php', {e:e_id}, function() {
				$('#grant-access').dialog('close');
				hideAddUserLoader();
				$(this).add($(this).closest('p')).highlightFade();
			});
		}
	});
}

function openAccessToUserDialog() {
	$('#grant-access').dialog({
		modal: true,
		width: 400,
		title: 'Give edit access to user',
		close: function() {$(this).dialog('destroy');}
	}).find('.form-errors').empty();
	$('#access-email').val('').focus();
	$('#grant-access a.button').click(function() {$('#grant-access').dialog('close');});
}

function toggleRegistrationMethodRegHere() {
	var box = $(this);
	var target = box.parent().next();

	if(box.is(':checked')) {
		target.show().highlightFade();
	} else {
		target.hide();
		box.parent().highlightFade();
	}
}

function toggleRegistrationMethods() {
	var checkbox = $(this);
	var target = checkbox.parent().children('input[type="text"]');

	if(checkbox.is(':checked')) {
		target.hide().removeClass('invisible').fadeIn().focus();
	} else {
		target.addClass('invisible');
	}
	target.parent().highlightFade();
}

function setRegMethodsState() {
	if(!$('#event-reg-here').is(':checked')) {
		$('#event-reg-here').parent().next().hide();
	}

	$('.reg-method input[type="checkbox"]').not(':checked').each(function() {
		$(this).parent().children('input[type="text"]').addClass('invisible');
	});
}

function setShirtsState() {
	if($('#event-shirts-available-n').is(':checked')) {
		$('#shirt-options').hide();
	}
	if($('#event-no-shirt-n').is(':checked')) {
		$('#event-shirt-discount').parent().hide();
	}
}

function toggleShirtDiscount() {
	var show = getLast('-', $(this).attr('id'));
	var target = $(this).parent().next();

	if(show == 'y') {
		target.show().highlightFade();
		$('#event-shirt-discount').focus();
	} else {
		target.hide().prev().highlightFade();
	}
}

function toggleShirtsAvailable() {
	var show = getLast('-', $(this).attr('id'));
	var target = $(this).parent().children('ul');

	if(show == 'y') {
		target.slideDown().children('li').highlightFade();
	} else {
		target.slideUp().parent().add(target.children('li')).highlightFade();
	}
}

function getLast(delimiter, parse) {
	var parsed = parse.split(delimiter);
	var count = parsed.length-1;
	return parsed[count];
}

function showEventDetailLoader() {
	$('#edit-event-details .submit-wrap img.invisible').removeClass('invisible');
}

function hideEventDetailLoader() {
	$('#edit-event-details .submit-wrap img').addClass('invisible');
}

function onSubmit() {
	showEventDetailLoader();
}

function onSuccess(data) {
	hideEventDetailLoader();

	$('#edit-event-details .form-errors').hide();
	if(data.length > 0) { // on error
		$('#edit-event-details .form-errors').fadeIn().html(data);
	} else {
		$('<div>').html('<div class="success"><p>Event details were saved successfully!</p></div>')
			.dialog({
				modal: true,
				title: 'Saved successfully!',
				width: 425,
				close: function() {
					if(getEventPermalink() != getOriginalPermalink()) {
						window.location = '/edit-event/'+getEventPermalink();
					} else {
						$(this).dialog('destroy').remove();
					}
				},
				buttons: {
					'Close window': function() {$(this).dialog('close');},
					'View Event': function() {window.location = '/events/'+getEventPermalink();}
				}
			});
		$('#edit-event-page-title').text('"'+$('#event-name').val()+'"');
	}
}

function getOriginalPermalink() {
	return $('#edit-permalink').text();
}

function getEventPermalink() {
	var date = $('#event-date').val().split('/')[2];
	var permalink = $('#event-permalink').val();

	return date +'/'+ permalink;
}

function showAddUserLoader() {
	$('#grant-access .submit-wrap img.invisible').removeClass('invisible');
}
function hideAddUserLoader() {
	$('#grant-access .submit-wrap img').addClass('invisible');
}
function showInviteUserLoader() {
	$('#invite-and-add-form .submit-wrap img.invisible').removeClass('invisible');
}
function hideInviteUserLoader() {
	$('#invite-and-add-form .submit-wrap img').addClass('invisible');
}
function showEditRaceLoader() {
	$('#edit-form-wrapper .submit-wrap img.invisible').removeClass('invisible');
}
function hideEditRaceLoader() {
	$('#edit-form-wrapper .submit-wrap img').addClass('invisible');
}
function showDeleteRaceLoader() {
	$('#delete-race-confirmation .submit-wrap img.invisible').removeClass('invisible');
}
function hideDeleteRaceLoader() {
	$('#delete-race-confirmation .submit-wrap img').addClass('invisible');
}
function showRemoveCouponLoader() {
	$('#remove-coupon-dialog .submit-wrap img.invisible').removeClass('invisible');
}
function hideRemoveCouponLoader() {
	$('#remove-coupon-dialog .submit-wrap img').addClass('invisible');
}

function showEditEventLoader(wrapper_id) {
	$('#'+wrapper_id+' .submit-wrap img.invisible').removeClass('invisible');
}

function hideEditEventLoader(wrapper_id) {
	$('#'+wrapper_id+' .submit-wrap img').addClass('invisible');
}