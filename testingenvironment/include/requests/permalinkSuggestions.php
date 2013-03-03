<?php
require_once '../critical.php';

if(!$util->isAjax() || !$user->loggedIn())
	die('Unauthorized');

$possibilities = array();

$safeName	= $util->cleanPermalink($_POST['name']);
$date		= strtotime($_POST['date']);
$valid_date = is_numeric($date);

// name-of-event-format
if(!empty($safeName))
	$possibilities[] = $safeName;

if($valid_date) {
	// name-month-format: fun-run-june
	$possibilities[] = $safeName . strtolower(date('-F', $date));

	// name-month-day-suffix-format: fun-run-june-3rd
	$possibilities[] = $safeName . strtolower(date('-F-jS', $date));

	// weekday-name-format: fun-run-june-3rd
	$possibilities[] = strtolower(date('l-', $date)) . $safeName;

	// Getting desperate, suggest more
	if(count($possibilities) == 0) {
		// name-month-day-format: fun-run-june-3
		$possibilities[] = $safeName . strtolower(date('-F-j', $date));

		// month-name-of-event-format: june-fun-run
		$possibilities[] = strtolower(date('F-', $date)) . $safeName;

		// month-day-name-format: june-3-fun-run
		$possibilities[] = strtolower(date('F-j-', $date)) . $safeName;

		// month-day-suffix-name-format: june-3rd-fun-run
		$possibilities[] = strtolower(date('F-jS-', $date)) . $safeName;
	}
}

$available = array();
foreach($possibilities as $permalink) {
	$ok = $valid_date ?
		$util->permalinkAvailable($permalink, date('Y', $date)):$util->permalinkAvailable($permalink);

	if($ok)
		$available[] = $permalink;
}

echo json_encode($available);

?>
