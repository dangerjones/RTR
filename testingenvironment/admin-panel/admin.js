$(document).ready(function() {
	// Focus if it was the last action made
	var find_member = document.getElementById('find-member-input');
	if(find_member.value != '')
		find_member.select();

	// show loading for any form
	$('form').submit(function() {
		showLoginLoad();
	});

	// Show save button on changes
	$('.admin-change-user-lvl').change(function() {onDataChange(this, 'original_user_lvl');});

	// Save data on save button click
	$('.admin-do-change').click(saveUserData);
});

function onDataChange(new_data, old) {
	var new_value = new_data.value;
	var old_value = $(new_data).closest('tr').find('.'+old).val();
	var hide;

	if(new_value != old_value)
		hide = '';
	else 
		hide = 'hide';
	toggleActionVisibility($(new_data), hide);
}

function saveUserData() {
	var select	= $(this);
	var row		= $(this).closest('tr');
	var lvl		= row.find('.admin-change-user-lvl').val();
	var u_id	= row.find('.user-id').text();

	toggleSaveAndLoadImg(row);
	$.post('/admin-panel/adminProcesses.php', {type:'saveUserData',lvl:lvl,user_id:u_id}, function(data) {
		toggleSaveAndLoadImg(row);
		if(data.length > 0) {
			alert(data);
		} else {
			toggleActionVisibility(select, 'hide');
			row.find('.original_user_lvl').val(lvl);
			rowSaveComplete(row);
		}
	});
}

function rowSaveComplete(row) {
	var color = {};
	if(row.hasClass('off'))
		color.end = '#ddd';
	row.highlightFade(color);
}

function toggleActionVisibility(intherow, hide) {
	var saveCell = intherow.closest('tr').find('.action');
	var className = 'invisible';

	if(hide != 'hide')
		saveCell.removeClass(className);
	else
		saveCell.addClass(className);
}

function toggleSaveAndLoadImg(row) {
	var save = row.find('.admin-do-change');
	var load = row.find('.action img');
	var hide = 'no-show';

	if(load.hasClass(hide)) {
		load.removeClass(hide);
		save.hide();
	} else {
		save.show();
		load.addClass(hide);
	}
}