<?php
require_once '../critical.php';

if(!$util->isAjax())
	die('Unauthorized');

$e_id = (int)$_POST['e_id'];
$event = new Event($e_id);

if(!$util->eventExists($e_id))
	die('Invalid event');
else if(!$event->hasAccess($user->getId()))
	die('Unauthorized');

$races = array();
foreach($event->getRaces() as $race) {
	$races[] = array('id' => $race->getId(), 'name' => $race->getName());
}

echo json_encode($races);
?>
