$(document).ready(function() {

	/*
	 * Make buttons
	 */
	$('input:submit, #logout, .reset-form, .button').button();

	/*
	 * Initialize fancybox (ajax image viewer) for images/pdfs
	 */

	$('.fancybox').each(function() {
		var path	= $(this).attr('href');
		var pieces	= path.split('.');
		var num		= pieces.length;
		var ext		= pieces[num-1].toLowerCase();
		var options = {
			hideOnOverlayClick: true
		}
		
		if(ext == 'pdf') {
			options.type = 'iframe';
			options.width = '75%';
			options.height = '90%';
		}

		$(this).fancybox(options);
	});


	/*
	 * Main navigation's dropdown menu
	 */
	$('ul.sf-menu').superfish();

	/*
	 * Don't hide the date picker when clicking a date
	 */
	$.datepicker._selectDateOverload = $.datepicker._selectDate;
	$.datepicker._selectDate = function(id, dateStr) {
		var target = $(id);
		var inst = this._getInst(target[0]);
		inst.inline = true;
		$.datepicker._selectDateOverload(id, dateStr);
		inst.inline = false;
		this._updateDatepicker(inst);
	}

	/*
	 * Reset any given form according to the link's rel attribute
	 */
	$('.reset-form').live('click', function() {
		resetForm($(this).attr('rel'));
	});

	/*
	 * 
	 */
    $('.login').each(function() {
        var default_value = this.value;

        $(this).focus(function() {
            $(this).removeClass('dummy');
            if(this.value == default_value)
                this.value = '';
        });

        $(this).blur(function() {
            if(this.value == '') {
                this.value = default_value;
                $(this).addClass('dummy');
            }
        });
    });

     /*
      * On login submit
      */
     $('#log-container').submit(function() {
         showLoginLoad();
         $('#login-submit').attr('disabled', 'disabled');
     });

     /*
      * Change input type on password input field
      */
     $('#dummy-input').focus(function() {
         $(this).blur().addClass('no-show').hide();
         $('#login-pass').removeClass('no-show').show().focus();
     });
     $('#login-pass').blur(function() {
         var val = this.value;

         if(val == '') {
             $(this).addClass('no-show').hide();
             $('#dummy-input').removeClass('no-show').show();
         }
     });

	/*
	 * Check in the background if user is still logged in
	 */
	if(userIsLoggedIn())
		setTimeout(pollForLoggedInStatus, 10000);
	

});

function showRaceResults(e_id) {
	var d = $('<div>');
	d.html('<div class="ajax-loader">').dialog({
		modal: true,
		width: 400,
		title: 'Race results',
		close: function() {$(this).dialog('destroy').remove();},
		buttons: {
			'Close window': function() {$(this).dialog('close');}
		}
	});

	$.post('/include/requests/html/getRaceResults.php', {e_id:e_id}, function(data) {
		d.html(data);
		d.dialog('option', 'position', 'center');
	});
}


function dialogFadeOut(dialog_obj) {
	setTimeout(function() {
		dialog_obj.closest('.ui-dialog').fadeOut(function() {
			dialog_obj.dialog('destroy').remove();
		});
	}, 1000);
}
 
/* 
 * Checks the page for signs of being logged in
 */ 
function userIsLoggedIn() {
	return $('#logout').size() > 0
}

/*
 * Poll the server to make sure the user is still logged in.
 * If not, bring up a dialog to re-login
 */
function pollForLoggedInStatus() {
	$.post('/include/requests/userIsLoggedIn.php', {}, function(result) {
		if(result == '0') { // not logged in
			reloginDialog();
		} else {
			setTimeout(pollForLoggedInStatus, 10000);
		}
	});
}

function reloginDialog() {
	var form =
		'<form id="relogin-form" action="/include/core/loginProcesses.php" method="post">'+
		'<h3>Sorry!</h3>'+
		'<p>You were unexpectedly logged out from our system. You must login again to continue.</p>'+
		'<div class="form-errors"></div>'+
		'<ul>'+
		'<li><label class="form-label" for="relogin-email">Email:</label> <input type="text" name="email" id="relogin-pass" /></li>'+
		'<li><label class="form-label" for="relogin-pass">Password:</label> <input type="password" name="pass" id="relogin-pass" /></li>'+
		'</ul>'+
		'<div class="bottom-buttons">'+
		'<input type="submit" class="button" value="Login" />'+
		'<input type="hidden" name="f_submit" value="login" />'+
		'</div></form>';
	form = $(form);
	form.dialog({
		modal: true,
		width: 350,
		title: 'Please login again!',
		close: function() {window.location.reload();}
	});
	
	form.find('.button').button();

	$('#relogin-form').ajaxForm({
		beforeSubmit: onReloginSubmit,
		success: onReloginSuccess
	});
}

/*
 * Triggered when the form is submitted
 */
function onReloginSubmit() {
	showLoginLoad();
}

/*
 * Triggered when the ajax request is complete and returns the data
 */
function onReloginSuccess(data) {
	hideLoginLoad();
	if(data.length > 0) { // on error
		$('#relogin-form .form-errors').html(data);
	} else {
		var form = $('#relogin-form');
		form.html('<div class="small-success">Thank you!</div>');
		dialogFadeOut(form);
		pollForLoggedInStatus();
	}
}

/*
 * Show login loading gif
 */
function showLoginLoad() {
    $('#main-loader').css('visibility', 'visible').removeClass('invisible');
}
function hideLoginLoad() {
    $('#main-loader').css('visibility', 'hidden').addClass('invisible');
}

function resetForm(formid) {
	var form = $('form#'+formid);
	
	form.find('input[type="text"], textarea').val('');
	form.find('input:checked').attr('checked', false);
	form.find('select').each(function() {
		$(this).find('option:first').attr('selected', true);
	});
}

function isMainLoaderOn() {
	return !$('#main-loader').hasClass('invisible');
}

function openSuccessDialog(message, width, small, buttons) {
	var classname = small ? 'small-success':'success';
	var doButtons = buttons === false ? {}:{'Close window': function() {$(this).dialog('close');}};

	$('<div>').html('<div class="'+classname+'">'+message+'</div>').dialog({
		modal: true,
		title: 'Success!',
		width: width,
		close: function() {$(this).dialog('destroy').remove();},
		buttons: doButtons
	});
}

function fitStringToWidth(str, width, className) {
	// str    A string where html-entities are allowed but no tags.
	// width  The maximum allowed width in pixels
	// className  A CSS class name with the desired font-name and font-size. (optional)
	// ----
	// _escTag is a helper to escape 'less than' and 'greater than'
	function _escTag(s){return s.replace("<","&lt;").replace(">","&gt;");}

	//Create a span element that will be used to get the width
	var span = document.createElement("span");
	//Allow a classname to be set to get the right font-size.
	if (className) span.className=className;
	span.style.display='inline';
	span.style.visibility = 'hidden';
	span.style.padding = '0px';
	document.body.appendChild(span);

	var result = _escTag(str); // default to the whole string
	span.innerHTML = result;
	// Check if the string will fit in the allowed width. NOTE: if the width
	// can't be determinated (offsetWidth==0) the whole string will be returned.
	if (span.offsetWidth > width) {
	var posStart = 0, posMid, posEnd = str.length, posLength;
	// Calculate (posEnd - posStart) integer division by 2 and
	// assign it to posLength. Repeat until posLength is zero.
	while (posLength = (posEnd - posStart) >> 1) {
		posMid = posStart + posLength;
		//Get the string from the begining up to posMid;
		span.innerHTML = _escTag(str.substring(0,posMid)) + '&hellip;';

		// Check if the current width is too wide (set new end)
		// or too narrow (set new start)
		if ( span.offsetWidth > width ) posEnd = posMid; else posStart=posMid;
	}

	result = '<abbr title="' +
		str.replace("\"","&quot;") + '">' +
		_escTag(str.substring(0,posStart)) +
		'&hellip;<\/abbr>';
	}
	document.body.removeChild(span);
	return result;
}

function showMyUploads(callback) {
	var d = $('<div id="show-my-uploads-dialog"><div class="ajax-loader">');
	d.dialog({
		modal: true,
		width: 450,
		title: 'My Uploads: All previously uploaded files',
		close: function() {$(this).dialog('destroy').remove()},
		buttons: {
			'Cancel': function() {$(this).dialog('close');}
		}
	});

	$.post('/include/requests/myuploads.php', {}, function(data) {

		if(data.length == 0)
			$('#show-my-uploads-dialog .ajax-loader').replaceWith('<h3>Currently no uploads</h3><p>Please upload your files onto the website first. Once they have been uploaded, you can view them here.</p>');
		else {
			var table = $('<table class="float-l left">');

			for(var i = 0; i < data.length; i++) {
				var row			= $('<tr>');
				var name		= data[i].filename;
				var ext			= data[i].type;
				var is_image	= ext != 'pdf';
				var image_path	= '/img/'+ (is_image ? 'image':'file') +'-icon.png';
				var image_class = is_image ? 'fancybox-is-image':'fancybox-not-image';

				row.append('<td><a href="'+data[i].path+'" class="'+image_class+'" title="Preview file"><img src="'+image_path+'" class="my-uploads" /></a><span class="my-uploads-file point">'+
					fitStringToWidth(name, 375)+'</span></td>');
				table.append(row);
			}

			$('#show-my-uploads-dialog .ajax-loader').replaceWith(table);
			table.before('<h3>Please choose a file</h3><div class="form-errors"></div>');
			table.after('<div class="submit-wrap"><img src="/img/loaderbar.gif" alt="Loading..." class="invisible" /></div>');

			d.find('.fancybox-is-image').fancybox({
				hideOnOverlayClick: true
			});
			d.find('.fancybox-not-image').fancybox({
				hideOnOverlayClick: true,
				type: 'iframe',
				width: '75%',
				height: '90%'
			});
			d.find('.fancybox-is-image, .fancybox-not-image').tooltip({
				showURL: false,
				positionLeft: true,
				left: -20
			});

			d.dialog('option', 'position', 'center')
		}
		if(typeof callback == 'function')
			callback.call(this, d);
	}, 'json');
}

function showMyUploadsLoader(show) {
	var loader = $('#show-my-uploads-dialog').find('.submit-wrap img');
	if(show != false)
		loader.removeClass('invisible');
	else
		loader.addClass('invisible');
}

function showMyUploadsError(errors) {
	$('#show-my-uploads-dialog .form-errors').hide()
		.html('<div class="ui-state-error ui-corner-all"><strong>Error: </strong>'+errors+'</div>').fadeIn();
}