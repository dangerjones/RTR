<?php
require_once '../critical.php';
require_once ROOT .'classes/eventregistration.php';

if(!$util->isAjax())
	die();

$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
$registrant = new EventRegistration($post);

if($registrant->hasErrors())
	echo $util->errorFormat($registrant->getfErrors());
else {
	if(!$registrant->addToDb())
		$util->errorFormat(CRITICAL_ERROR);
}

?>
