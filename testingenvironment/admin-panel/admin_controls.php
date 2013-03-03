<?php
if(!$user->isAdmin())
	include ROOT .'include/unauthorized.php';
else {
	require_once ROOT .'classes/admincontrols.php';
	require_once ROOT .'classes/simplesanitize.php';
	$admin = new AdminControls();

	$get = new SimpleSanitize('get', 'strict', 100);
	$post = new SimpleSanitize('post', 'html', 100);

	$action = $get->get('action');
	?>
	<h2>Administrator Panel</h2>

	<h3 class="header">Event Approval</h3>
	<div class="indent-1">
		<h4>Awaiting Approval</h4>
		<?php
		$echo = $admin->formattedEventsByStatus(ESTATUS_WAITING);
		echo empty($echo) ? 'None':$echo;

		echo '<h4>Denied</h4>';
		$echo = $admin->formattedEventsByStatus(ESTATUS_DENY);
		echo empty($echo) ? 'None':$echo; ?>
	</div>

	<h3 class="header">Member controls</h3><?php
	$find_email = $post->get('find_member', 'both');
	$searchby = $post->getInt('find_member_by', 3);
	$selected = ' selected="selected"';
	?>
	<div class="indent-1">
		<form action="" method="post">
			<ul>
				<li>
					<label for="find-member-input">Find a user by email:</label>
					<input type="text" name="find_member" id="find-member-input" value="<?php echo $find_email; ?>" size="30" maxlength="100" />
					<select name="find_member_by">
						<option value="1"<?php echo $searchby == 1 ? $selected:''; ?>>Starts with</option>
						<option value="2"<?php echo $searchby == 2 ? $selected:''; ?>>Ends with</option>
						<option value="3"<?php echo $searchby == 3 ? $selected:''; ?>>Contains</option>
					</select>
					<input type="submit" value="Find" />
				</li>
			</ul>
		</form>
	</div>
	<?php
	if(strlen($find_email) > 0) {
		$search = str_replace(array('_', '%'), array('\_', '\%'), $find_email);
		switch($searchby) {
			case 3: // contains
				$front = '%';
			case 1: // starts with
				$back = '%';
				break;
			case 2: // ends with
				$front = '%';
		}

		$query = 'SELECT * FROM '. TABLE_USER .' WHERE email LIKE "'. $front . $search . $back .'"';
		$user_data = $sql->getAssoc($query);

		if(count($user_data) > 0) {
			echo getUserTable($user_data);
		} else {
			echo '<p><strong>No Emails Found!</strong></p>';
		}
	}

	?>

	<?php
}

function getUserTable($user_data) {
	global $util;
	
	$out = '<table class="data" style="width: 100%">';
	$out .= '<thead><tr>';
	$out .= '<th>ID</th><th>Email address (Total: '. count($user_data) .')</th>';
	$out .= '<th>Level</th><th>Last login</th><th>IP Address</th><th>Registered</th><th><em>Actions</em></th>';
	$out .= '</tr></thead>';
	$out .= '<tbody>';
	$i = 0;
	foreach($user_data as $u) {
		$class = $i++%2 == 0 ? '':' class="off"';
		$lvl = $util->getUserLvlName($u['lvl']);
		$selected = ' selected="selected"';

		$out .= '<tr'. $class .'>';

		$out .= '<td class="title user-id">'. $u['id'] .'</td>';
		$out .= '<td>'. $u['email'] .'</td>';
		$out .= '<td>';

		$out .= '<select class="admin-change-user-lvl">';
		$out .= '<option value="'. LVL_ADMIN .'"'. ($lvl == LVL_ADMIN_TAG ? $selected:'') .'>'. LVL_ADMIN_TAG .'</option>';
		$out .= '<option value="'. LVL_EMPLOYEE .'"'. ($lvl == LVL_EMPLOYEE_TAG ? $selected:'') .'>'. LVL_EMPLOYEE_TAG .'</option>';
		$out .= '<option value="'. LVL_USER .'"'. ($lvl == LVL_USER_TAG ? $selected:'') .'>'. LVL_USER_TAG .'</option>';
		$out .= '</select>';
		$out .= '<input type="hidden" class="original_user_lvl" value="'. $u['lvl'] .'" />';

		$out .= '</td>';
		$login = strtotime($u['last_login']);
		if($login > 0)
			$out .= '<td>'. date('n/d/y g:ia', $login) .'</td>';
		else
			$out .= '<td>Never</td>';
		$out .= '<td>'. $u['ip'] .'</td>';
		$out .= '<td>'. date('n/d/y g:ia', strtotime($u['reg_date'])) .'</td>';
		$out .= '<td class="action invisible"><a class="admin-do-change button">Save</a><img src="/img/loadcircle.gif" alt="Loading..." class="no-show" /></td>';

		$out .= '</tr>';
	}
	$out .= '</tbody>';
	$out .= '</table>';

	return $out;
}
?>