<?php
require_once '../include/critical.php';

if(!$user->isAdmin())
	die('Unauthorized');

$refer = $_SERVER['HTTP_REFERER'];

if(!isset($_POST['type'])) {
	$type = $_POST['submit'];

	switch($type) {
		case 'Approve':
			$util->changeEStatus((int)$_POST['e_id'], ESTATUS_OK);
			break;
		case 'Deny':
			$util->changeEStatus((int)$_POST['e_id'], ESTATUS_DENY);
			break;
		case 'Delete':
			$util->deleteEvent((int)$_POST['e_id']);
			break;
		default:

	}
	header('Location: '. $refer);
}

require_once ROOT .'classes/simplesanitize.php';
$post = new SimpleSanitize('post', 'strict', 100);

switch($post->get('type')) {
	case 'saveUserData':
		$u_id = $post->getInt('user_id', 0);
		if($u_id == $user->getId())
			die('Cannot change your own user level');
		$new_user = new User($u_id, false);
		if(!$new_user->updateLvl($post->getInt('lvl')))
			echo 'Failed';
		break;
}

if(!$util->isAjax())
	header('Location: '. $refer);



?>
