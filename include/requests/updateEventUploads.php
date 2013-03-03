<?php
require_once '../critical.php';
require_once ROOT .'classes/simplesanitize.php';

if(!$util->isAjax())
	die('Unauthorized');

$post = new SimpleSanitize('post', 'none');

$e_id = $post->getInt('e_id');
if(!$util->eventExists($e_id))
	die($util->errorFormat('Invalid event'));

$event = new Event($e_id);

if(!$event->hasAccess($user->getId()))
	die($util->errorFormat('Permissions denied'));

$r_id = $post->getInt('race_id');
$name = $post->get('name');

$filename = $post->get('file', 255);
switch($post->get('type', 'strict', 50)) {
	case 'update-banner':
		updateUpload('banner', $filename);
		break;
	case 'update-entry-form':
		updateUpload('entry-form', $filename);
		break;
	case 'update-course-map':
		updateUpload('course-map', $filename);
		break;
	case 'add-race-results':
		addRaceResults($filename);
		break;
	case 'remove-results':
		removeRaceResults($post->getInt('results_id'));
		break;
}

function removeRaceResults($results_id) {
	global $sql;
	$query = 'DELETE FROM '. TABLE_RACE_RESULTS .' WHERE id='. $results_id;

	$sql->q($query);
}

function addRaceResults($filename) {
	global $sql, $user, $event, $r_id, $name, $util;
	$errors = array();

	if(!$event->hasRace($r_id))
		$errors[] = 'Invalid race';

	if(strlen($filename) == 0)
		$errors[] = 'Please choose a file';
	else if(!$user->uploadedFileExists($filename))
		$errors[] = 'File does not exist';

	if(strlen($name) == 0)
		$errors[] = 'Results\' name is required';
	else if(preg_match('/[^a-zA-Z0-9\- \'"]/', $name) > 0)
		$errors[] = 'You are using an invalid symbol for the results\' name. Valid symbols are: a-z, 0-9, hyphen, single quote, and double quote.';

	if(count($errors) > 0)
		die($util->errorFormat(outputErrors ($errors)));

	$path = $user->makeUploadedFilePath($filename);

	if(!doAddResults($r_id, $name, $path))
		die($util->errorFormat(CRITICAL_ERROR));
}

function doAddResults($r_id, $name, $path) {
	global $sql, $e_id;

	$r_id = (int)$r_id;
	$safePath = $sql->safeString($path);

	$query = 'INSERT INTO '. TABLE_RACE_RESULTS .' (event_id, race_id, name, file) VALUES ('. $e_id .', '. $r_id .', "'. $name .'", "'. $safePath .'")';

	return $sql->q($query);
}


function updateUpload($type) {
	global $sql, $user, $e_id, $filename;
	
	if(!empty($filename) && !$user->uploadedFileExists($filename)) {
		die('File does not exist');
	}

	if(!empty($filename))
		$path = $user->makeUploadedFilePath($filename);
	else
		$path = '';
	
	$query = 'UPDATE '. TABLE_EVENTS .' SET ';
	switch($type) {
		case 'banner':
			allowOnlyImages($filename);
			$query .= 'banner="'. $path .'"';
			break;
		case 'entry-form':
			$query .= 'entry_form="'. $path .'"';
			break;
		case 'course-map':
			allowOnlyImages($filename);
			$query .= 'course_map="'. $path .'"';
			break;
	}

	$query .= ' WHERE id='. $e_id;


	if(!$sql->q($query))
		die('Filename update failed!');
}

function allowOnlyImages($file) {
	if(strlen($file) == 0)
		return;

	global $allowed_image_types;
	$info = pathinfo($file);

	if(!in_array(strtolower($info['extension']), $allowed_image_types))
		die('Only image files allowed!');
}

function outputErrors($errors) {
	$out = '<ul>';
	foreach($errors as $e) {
		$out .= '<li>'. $e .'</li>';
	}
	$out .= '</ul>';

	return $out;
}

?>
