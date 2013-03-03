$(document).ready(function() {
	$('#reg_bday').datepicker({
		changeMonth: true,
		changeYear: true,
		showButtonPanel: true,
		defaultDate: '+1y',
		yearRange: '-100:+1',
		onChangeMonthYear: function(year, month, d) {
			$(this).datepicker('setDate', month+'/'+d.selectedDay+'/'+year);
		}
	});
	$('#register-terms').dialog({
		autoOpen: false, 
		modal:true,
		width: 500
	});

	$('#reg_phone, #reg_phone2').keyup(function() {
		if(this.value.length == 3)
			$(this).next().select();
	});

	$('#show-terms').click(function() {$('#register-terms').dialog('open');return false;});

	$('#register-form').ajaxForm({
		beforeSubmit: onSubmit,
		success: onSuccess
	});

	$('#reg_save_me').click(function() {
		$('#overwrite-text').remove();
		$('#saved-info').children('option:first').attr('selected', true);
	});

	$('.delete-reg').live('click', showDeleteRegDialog);

	$('#saved-info').livequery('change', getSavedInfo);

	$('#registrant-payment').live('click', addParticipantOpenPaymentDialog);

	$('.payment-reg-id').livequery('change', togglePayment);

	$('#what-cvv2').live('click', showCVV2Explanation);

});

function showCVV2Explanation() {
	$('#cvv2-explanation').dialog({
		modal: true,
		title: 'What is CVV2?',
		width: 400,
		close: function() {$(this).dialog('destroy');},
		buttons: {
			Close: function() {$(this).dialog('close');}
		}
	});
}

function togglePayment() {
	var speed = 400;
	var opacity = .3;
	var checkbox = $(this);
	var row = checkbox.closest('tr');
	var targetTotal = $('#payment-grand-total span');
	var amt = moneyToint(row.children('.reg-subtotal').text());
	var total = moneyToint(targetTotal.text());
	var newTotal = 0;

	if(!checkbox.is(':checked')) {
		row.fadeTo(speed, opacity);
		newTotal = total-amt;
	}
	else {
		row.fadeTo(speed, 1);
		newTotal = total+amt;
	}
	var decimalTotal = newTotal.toFixed(2);
	targetTotal.text('$'+decimalTotal);

	if(decimalTotal == 0)
		$('#payment-creditcard-form').stop(true, true).slideUp();
	else
		$('#payment-creditcard-form').stop(true, true).slideDown();
	
	$('#payment-grand-total').stop(true, true).addClass('total-changed')
		.animate({delay:1}, 1500).delay(1).removeClass('total-changed', 1000);
	$('input[name="pay_grand_total"]').val(newTotal.toFixed(2));
}

function moneyToint(string) {
	return string.split('$')[1]*1;
}

var RTR = {};
RTR.redirectSuccess = null;
function addParticipantOpenPaymentDialog () {
	RTR.redirectSuccess = openPaymentDialog;
	$('#register-form').submit();
};

function openPaymentDialog() {
	var evt = $('input[name="register_event"]').val();
	$('.currently-registered').load('/include/requests/event-registrants.php', {e:evt});
	showLoginLoad();
	$('<div>').load('/include/requests/payment-form.php', {}, function() {
		$(this).dialog({
			modal: true,
			title: 'Event payments and finalizing',
			width: 625,
			beforeclose: canPaymentDialogClose,
			close: function() {$(this).dialog('destroy').remove();},
			buttons: {
				Cancel: function() {$(this).dialog('close')},
				'Confirm Registration': function() {$('#event-payment-form form').submit();}
			}
		});
		$('#event-payment-form form').ajaxForm({
			// target is where the errors are sent
			target: '#event-payment-form .form-errors',
			beforeSubmit: onPaymentSubmit,
			success: onPaymentSuccess
		});
		hideLoginLoad();
	});
}

function isPaymentLoaderOn() {
	var loader = $('#pay-loader img');
	if(loader.size() == 0)
		return false;
	
	return !loader.hasClass('invisible');
}

function showPaymentLoader() {
	$('#pay-loader img').removeClass('invisible');
}

function hidePaymentLoader() {
	$('#pay-loader img').addClass('invisible');
}

function canPaymentDialogClose() {
	if(isPaymentLoaderOn())
		return false;
	return true;
}

function onPaymentSubmit() {
	showPaymentLoader();
}

function onPaymentSuccess(data) {
	$('#event-payment-form .form-errors').hide();
	if(data.length > 0) { // Errors found
		$('#event-payment-form .form-errors').empty().append(data).fadeIn();
		hidePaymentLoader();
	} else { // form submitted successfully
		window.location = '/payment-receipt.php';
	}
}

function registrationSuccessDialog() {
	var event = $('input[name="register_event"]').val();
	var name = $('#reg_fname').val() +' '+ $('#reg_lname').val();

	$.post('/include/requests/html/event-reg-success.php', {person_name:name,e:event}, function(successDialog) {
		$('<div>').html(successDialog).dialog({
			modal: true,
			width: 700,
			title: 'Registration successful!',
			close: function() {$(this).dialog('destroy').remove();},
			buttons: {
				'Finalize Registration': function() {$(this).dialog('close');$('#registrant-payment').click();},
				'Register more for this race': function() {$(this).dialog('close');},
				'View a different event': function() {document.location = '/events';}
			}
		});
		hideLoginLoad();
		$('.button').button();
		resetForm('register-form');
		$('#reg_save_ow, label[for="reg_save_ow"]').remove();
		$('.currently-registered').load('/include/requests/event-registrants.php', {e:event});
	});

}

function showDeleteRegDialog() {
	var person_id = $(this).attr('rel');
	var action = $(this).attr('title');

	$('<div>Are you sure you want to delete this registrant?</div>').dialog({
		modal: true,
		width: 400,
		title: action+"?",
		close: function() {$(this).dialog('destroy').remove();}, 
		buttons: {
			Cancel: function() {$(this).dialog('close');},
			Delete: function() {deleteRegistrant(person_id);$(this).dialog('close');}
		}
	});
}

function deleteRegistrant(person) {
	showLoginLoad();
	var event = $('input[name="register_event"]').val();

	$.post('/include/requests/delete-registrant.php', {person_id:person}, function() {
		$('.currently-registered')
			.load('/include/requests/event-registrants.php', {e:event}, function() {
				$('.button').button();
				hideLoginLoad();
			});
	});
}

function getSavedInfo() {
	showLoginLoad();
	var target = $(this);
	$.post('/include/requests/personalInfo.php', {person_id:this.value}, function(personData) {

		if(target.val() > 0) {
			$('#reg_save_ff').click();
			$('#overwrite-text').html('<input type="checkbox" checked="checked" name="register_save_ow" id="reg_save_ow" value="true" /> '+
				'<label for="reg_save_ow">Overwrite saved info for "'
				+target.find(':selected').text()+'"</label>');
		}
		else
			$('#reg_save_me').click();

		rewriteFormData(personData);
		hideLoginLoad();
	}, 'json');

}

function rewriteFormData(person) {
	$('#reg_fname').val(person.fname);
	$('#reg_lname').val(person.lname);
	$('#reg_email').val(person.email);
	$('#reg_addr').val(person.address_1);
	$('#reg_addr2').val(person.address_2);
	$('#reg_city').val(person.city);
	$('#reg_state').val(person.state);
	$('#reg_zip').val(person.zip);
	$('#reg_bday').val(person.birthday);
	$('#reg_phone').val(person.phone);
	$('#reg_phone2').val(person.phone2);
	$('#reg_phone3').val(person.phone3);

	var shirt = '#reg_shirt_';
	if(person.adult_size > 0)
		shirt += 'a';
	else
		shirt += 'y';

	shirt += person.shirt_size;

	$(shirt).click();

	$('#reg_gender_'+person.gender).click();
}

function onSubmit() {
	showLoginLoad();
}

function onSuccess(data) {
	$('.form-errors').hide();
	if(data.length > 0) {
		$('.form-errors').empty().append(data).fadeIn();
		RTR.redirectSuccess = null;
		hideLoginLoad();
	} else {
		$('#personal-saved-info').load('/include/requests/friendsfamily_select.php', {}, function() {
			if (RTR.redirectSuccess) {
				RTR.redirectSuccess();
				RTR.redirectSuccess = null;
			} else {
				registrationSuccessDialog();
			}
		});
	}
}
