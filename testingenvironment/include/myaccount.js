$(document).ready(function() {
	// Initialize ajax forms
	$('#change-password-form').ajaxForm({
		beforeSubmit: function() {showFormLoader('pass-change-loader');},
		success: onSuccess
	});
});

function onSuccess(data) {
	var errorBox = $('#change-password-form .form-errors');
	hideFormLoader('pass-change-loader');
	errorBox.empty();
	
	if(data.length > 0) {
		errorBox.html(data);
	} else {
		$('#change-password-form').find('input[type="password"]').val('');

		var d = $('<div>');
		d.html('<div class="small-success">Password changed!</div>').dialog({
			modal: true, 
			width: 400,
			title: 'Your password was changed successfully!'
		});
		dialogFadeOut(d);
	}
}

function showFormLoader(id) {
	$('#'+id).css('visibility', 'visible');
}
function hideFormLoader(id) {
	$('#'+id).css('visibility', 'hidden');
}