<?php
require_once '../critical.php';

if(!$util->isAjax())
	die('Unauthorized');

if($user->loggedIn())
	echo '1';
else
	echo '0';

?>