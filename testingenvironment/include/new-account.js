$(document).ready(function() {
	// Initialize ajax forms
	$('#new-account-form').ajaxForm({
		beforeSubmit: onSubmit,
		success: onSuccess
	});
});

function onSuccess(data) {
	var eBox =  $('#new-account-form').children('.form-errors');
	var ref = document.referrer;
	hideLoginLoad();
	eBox.hide().empty();

	if(data.length > 0) {
		eBox.html(data).fadeIn();
	} else {
		openSuccessDialog('Registration complete!', 500, true);

		setTimeout(function() {
			if(ref.search('/new-account/?') > 0)
				location.href = '/';
			else if(ref.search('https?://[a-zA-z0-9\.]*?262running\.com/?') >= 0)
				location.href = ref;
			else
				location.href = '/';
		}, 1000);
	}
}

function onSubmit() {
	showLoginLoad();
}
