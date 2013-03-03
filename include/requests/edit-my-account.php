<?php
require_once '../critical.php';

if(!$util->isAjax() || !$user->loggedIn())
	die('Unauthorized');

$post = $util->cleanUp(filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS));
$errors = array();

switch($post['type']) {
	case 'change-pass':
		changePassword(md5($_POST['curr_pass'] . SALT), md5($_POST['new_pass'] . SALT), md5($_POST['confirm_pass'] . SALT), $_POST['new_pass']);
		break;
	default:
}

outputErrors();

function changePassword($current, $new, $confirmed, $raw_pass) {
	global $user, $mail;

	if($user->getPass() != $current)
		addError('Current password is incorrect');

	if(strlen($raw_pass) < MIN_PASS_LEN)
		addError('New password must be '. MIN_PASS_LEN .' or more characters');

	if($new != $confirmed)
		addError('New passwords do not match');

	if(hasErrors())
		return;

	if(!$user->updatePass($raw_pass))
		addError(CRITICAL_ERROR);
	else {
		$subject = 'Account Password Changed!';
		$message = 'Dear '. $user->getAddressingIdentifier() .",\r\n".
			"\t". 'Recently your account for '. BASEURL .' had its password changed. ' .
			'Your new password has become effective immediately. Please use it '.
			'from now on. If you did not request this change or feel this is a mistake, please '.
			'contact us immediately.'. "\r\n\r\n".

			'If you have forgotton your new password or ever need to reset it in the future, '.
			'please visit the following link: '. BASEURL .'forgot-password/'. "\r\n\r\n".
			'Thank you!';

		$mail->sendAdmin($user->getEmail(), $subject, $message);
	}
}

function addError($e) {
	global $errors;
	$errors[] = $e;
}

function hasErrors() {
	global $errors;
	return count($errors) > 0;
}

function outputErrors() {
	global $errors;
	if(hasErrors()) {
		global $util;
		$out = '<ul>';
		foreach($errors as $e) {
			$out .= '<li>'. $e .'</li>';
		}
		$out .= '</ul>';

		echo $util->errorFormat($out);
	}
}
?>
