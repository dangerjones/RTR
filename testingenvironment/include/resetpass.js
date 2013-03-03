$(document).ready(function() {
	// Initialize ajax form
	$('#reset-password-form').ajaxForm({
		beforeSubmit: onSubmit,
		success: onSuccess
	});

	// Give focus to form
	$('#pass-reset-email').focus();
});

function onSuccess(data) {
	hideLoginLoad();
	var errorBox = $('#reset-password-form .form-errors');
	errorBox.empty();

	if(data.length > 0) {
		errorBox.html(data);
	} else {
		var d = $('<div>');
		d.html('<div class="small-success">Email sent!</div>').dialog({
			modal: true,
			width: 350,
			title: 'Email sent successfully!'
		});

		$('#reset-password-form').children('input[type="text"]').val('');
		dialogFadeOut(d);
	}
}

function onSubmit() {
	showLoginLoad();
}