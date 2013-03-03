<?php
require '../critical.php';

if(!$util->isAjax())
	die();

$email = $util->cleanUp($_POST['email'], 100);
$errors = array();

switch($_POST['type']) {
	case 'sendEmail':
		sendEmail($email);
		break;
	default:
}

outputErrors();

function sendEmail($email) {
	global $util, $sql;

	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		addError('Invalid email address');
		return;
	} else if(!$util->getUser($email)) {
		addError('This email is not registered on our website');
		return;
	}

	$attempts = $util->getPasswordResetAttempts($email);
	if($attempts >= PASS_RESET_MAX) {
		addError('You may only send this email a maximum of '. PASS_RESET_MAX .' times per day. If you need to send it again, please try again tomorrow.');
		return;
	}

	if(!$util->updateResetAttempts($email))
		addError(CRITICAL_ERROR);

	sendResetEmail($email);
}

function sendResetEmail($email) {
	global $mail, $util;
	$code = $util->getPassResetCode($email);
	$user = new User($util->getUser($email), false);

	$subject = 'Password Reset Instructions';
	$message = 'Dear '. $user->getAddressingIdentifier() .', '. "\r\n".
		"\t". 'We have received a request to reset your password for your account on '. BASEURL .'. '.
		'In order to complete this request, we require further action by you. By clicking the '.
		'following link, your password will be reset and your new password will be emailed to you. '.
		'Remember, this link will expire after today!'. "\r\n\r\n".
		'Reset password: '. BASEURL .'forgot-password/'. $code .'/'. urlencode($email) . "\r\n\r\n".
		'If you wish to keep your old password or you did not request a password reset, please ignore '.
		'this email. If you continue to receive these emails '.
		'unexpectedly, please contact us immediately.'. "\r\n\r\n".
		'Thank you!';

	$mail->sendAdmin($email, $subject, $message);
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
