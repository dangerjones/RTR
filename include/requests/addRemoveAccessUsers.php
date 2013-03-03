<?php
require_once '../critical.php';

if(!$util->isAjax())
	die('Unauthorized');

$remove = $_POST['type'] == 'remove';
$invite = $_POST['type'] == 'invite';

if($invite)
	require_once ROOT .'classes/registration.php';

$email	= $_POST['email'];
$e_id	= (int)$_POST['e_id'];
$error	= array();

$event		= EventHandler::getEvent($e_id);
$e_exists	= $event !== null;
$u_id		= $util->getUser($email);
$can_edit	= $e_exists ? $event->hasAccess($user->getId()) && ($event->isOwner($user->getId()) || $user->isAdmin()):false;
$has_access = $e_exists ? $event->getUserIDsWithAccess():array();

/*
 * Error checking
 */
if(!$e_exists)
	addError('Invalid event');
else if(!$can_edit)
	addError('Permissions denied');

if(hasErrors())
	die($util->errorFormat(firstError()));

if($remove)
	removeUser($u_id);
else {
	addUserErrorCheck($email, $u_id, $invite);

	if(hasErrors())
		die($util->errorFormat(firstError()));

	if($invite) {
		$newaccount = new Registration();
		$u_id = $newaccount->addUser($email, $util->generatePassword());

		if($newaccount->numErr() > 0)
			die($util->errorFormat($newaccount->getErrList()));


	}

	if(!$invite && $event->hasAccess($u_id))
		die($util->errorFormat('This user already has access'));

	$has_access[] = $u_id;

	$query = 'UPDATE '. TABLE_EVENTS .' SET grant_access="'. implode(';', $has_access) .'"
		WHERE id='. $e_id;

	if(!$sql->q($query))
		die($util->errorFormat(CRITICAL_ERROR));

	$to_user		= User::getUser($u_id);
	$name			= ucfirst($user->getIdentifier());
	$event_name		= $event->getDecodedName();
	$subject		= $name .' shared an event with you!';
	$message		= 'Dear '. $to_user->getAddressingIdentifier() .','. "\r\n".
			"\t". $name .' just gave you access to edit and moderate his event, "'. $event_name .'". '.
			'Feel free to edit the event details or view current registrants.'. "\r\n\r\n" .
			'View event:'. "\r\n".
			"\t". BASEURL .'events/'. $event->getPermalink() ."\r\n\r\n".
			'Edit event:'. "\r\n".
			"\t". BASEURL .'edit-event/'. $event->getPermalink() ."\r\n\r\n".
			'View registrants:'. "\r\n".
			"\t". BASEURL .'registrants/'. $event->getPermalink() ."\r\n\r\n". 
			'Thank you!';

	$mail->sendAdmin($email, $subject, $message);
}

/*
 * Script functions
 */

function addUserErrorCheck($email, $u_id, $invite) {
	if(empty($email)) {
		if($invite)
			$person = 'an';
		else
			$person = 'a user\'s';
		
		addError('Please enter '. $person .' email address');
	}
	else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		addError('Invalid email address');
	else if(!$u_id && !$invite)
		addError('This user does not exist. Try the "Invite and add" button.');
}

function removeUser($u_id) {
	global $sql, $has_access, $e_id;

	$idx = array_search($u_id, $has_access);
	if($idx === false)
		return false;

	unset($has_access[$idx]);
	$query = 'UPDATE '. TABLE_EVENTS .' SET grant_access="'. implode(';', $has_access) .'"
		WHERE id='. $e_id;

	return $sql->q($query);
}

function addError($e) {
	global $error;
	$error[] = $e;
}

function hasErrors() {
	global $error;
	return count($error) > 0;
}

function firstError() {
	global $error;
	return $error[0];
}
?>
