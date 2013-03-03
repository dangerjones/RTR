<?php
require_once '../critical.php';
require_once ROOT .'classes/newevent.php';

if(!$util->isAjax())
	die();
if(!$user->loggedIn())
	die($util->errorFormat('Please login. If you have already logged in, refresh your page and try again.'));

$event = new NewEvent($_POST);

if($event->errorsExist()) {
	echo $util->errorFormat($event->errString());
} else {
	if(!$event->addToDB())
		echo $util->errorFormat(CRITICAL_ERROR . mysql_error());
}
?>
