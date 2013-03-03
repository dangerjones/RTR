<?php
if(!defined('ROOT'))
	require_once '../critical.php';

require_once ROOT .'classes/event.php';

if($e_id == null) {
	$e_id = (int)$_REQUEST['e'];
	$event = new Event($e_id);
	$exists = $util->eventExists($e_id);
	$can_edit = $event->hasAccess($user->getId());
}

if(!$exists)
	die('Invalid event');
if(!$can_edit)
	die('Unauthorized');

$user_ids = $event->getUserIDsWithAccess();

if(empty($user_ids))
	echo 'None';
else {
	for($i = 0; $i < count($user_ids); $i++) {
		$u = new User($user_ids[$i], false);
		$fname = $u->getFName() == '' ? 'user':$u->getFName();
		echo $u->getFullName() .' <em>('. $u->getEmail() .')</em> ';
		echo '<a class="remove-access-to-user" rel="'. $u->getEmail() .
				'"><img src="/img/delete-user.png" alt="Remove" title="Remove '.
				$fname .'\'s access" /></a>';
		if($i < count($user_ids)-1)
			echo ', ';

	}
}

?>
