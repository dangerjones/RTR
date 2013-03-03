<?php
require_once '../critical.php';
require_once ROOT .'classes/editevent.php';
require_once ROOT .'classes/event.php';
require_once ROOT .'classes/editrace.php';

if(!$util->isAjax())
	die('Unauthorized');

$post = $util->cleanUp(filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS));

$e_id = (int)$post['e_id'];
if(!$util->eventExists($e_id))
	die($util->errorFormat('Invalid event'));

$event = new Event($e_id);

if(!$event->hasAccess($user->getId()))
	die($util->errorFormat('Permissions denied'));

switch($post['edit_type']) {
	case 'details':
		$edited_event = new EditEvent($post, $post['edit_type']);
		editEventDetails();
		break;
	case 'races':
		if(!$util->raceExists($e_id, (int)$post['race_id']))
			die($util->errorFormat('Invalid race'));
		$edited_race = new EditRace($post);
		editRace();
		break;
	case 'delete_race':
		$race_id = (int)$post['race_id'];
		if(!$util->raceExists($e_id, $race_id))
			die($util->errorFormat('Invalid race'));
		$edited_race = new EditRace($post);
		deleteRace($edited_race);
		break;
	case 'new_race':
		$edited_race = new EditRace($post);
		addNewRace();
		break;
	case 'new_coupon':
		$edited_event = new EditEvent($post, $post['edit_type']);
		addNewCoupon($edited_event, $post['coupon_codename'], $post['coupon_amount']);
		break;
	case 'toggle_coupon':
		$event = new Event($e_id);
		$c_id = (int)$post['coupon'];

		if(!$event->hasCoupon($c_id))
			die($util->errorFormat('Invalid coupon'));
		toggleCoupon($event, $c_id);
		break;
	case 'remove_coupon':
		removeCoupon($e_id, (int)$post['c_id']);
		break;
	case 'questions':
		$edited_event = new EditEvent($post, $post['edit_type']);
		break;
	case 'uploads':
		$edited_event = new EditEvent($post, $post['edit_type']);
		break;
	case 'delete-event':
		if(!($event->isOwner($user->getId()) || $user->isAdmin()))
			die($util->errorFormat('Permissions denied'));
		deleteEvent();
		break;
	case 'new-custom-q':
		saveQuestion();
		break;
	case 'edit-custom-q':
		saveQuestion((int)$post['q_id']);
		break;
	case 'delete-custom-q';
		deleteQuestion((int)$post['q_id']);
		break;
	default:
		die($util->errorFormat('Invalid request'));
}


	
/*
 * Script functions
 */

function deleteQuestion($q_id) {
	global $sql, $e_id;
	$query = 'DELETE FROM '. TABLE_E_QUESTIONS .' WHERE event_id='. $e_id .' AND id='. $q_id;

	return $sql->q($query);
}

function saveQuestion($q_id = null) {
	global $post, $util, $sql, $e_id;

	$q = $post['question'];
	$a = $post['answer'];

	errorCheckQuestions($q, $a);

	$a_string = $sql->safeString(implode(DELIMITER, $a));
	$safeQ = $sql->safeString($q);

	if($q_id === null)
		$query = 'INSERT INTO '. TABLE_E_QUESTIONS .' (event_id, question, answers) VALUES ('. $e_id .', "'. $safeQ .'", "'. $a_string .'")';
	else
		$query = 'UPDATE '. TABLE_E_QUESTIONS .' SET question="'. $safeQ .'", answers="'. $a_string .'" WHERE event_id='. $e_id .' AND id='. $q_id;

	if(!$sql->q($query))
		die($util->errorFormat(CRITICAL_ERROR));
}

function errorCheckQuestions($question, $answers) {
	global $util;
	$errors = array();

	removeEmptySpots($answers);

	if(strlen($question) == 0)
		$errors[] = 'Question is required';

	if(count($answers) < 2)
		$errors[] = 'At least two answers are required';

	if(count($errors) > 0)
		die($util->errorFormat($util->makeList($errors)));
}


function removeEmptySpots(&$array) {
	$temp = array();
	foreach($array as $key => $val) {
		if(strlen($val) > 0)
			$temp[$key] = $val;
	}
	$array = $temp;
}

function deleteEvent() {
	global $event, $util;

	if(!$util->deleteEvent($event->getId()))
		echo $util->errorFormat('This event has registrants and cannot be deleted.');
}

function toggleCoupon($event, $coupon_id) {
	$coupon = $event->getCoupon($coupon_id);

	if(!$coupon->toggleDisabledState())
		die($util->errorFormat(CRITICAL_ERROR));
}

function addNewCoupon($edited, $codename, $amt) {
	global $sql, $util;
	$codename = $sql->safeString($codename);
	$amt = $sql->safeString($amt);

	if(!empty($codename) || !empty($amt))
		$edited->couponErrCheck();
	else
		die($util->errorFormat('All fields are required'));

	if($edited->errorsExist())
		die($util->errorFormat($edited->errString()));

	if(!$edited->addCouponToDB($edited->getId(), $codename, $amt))
		die(CRITICAL_ERROR);
}

function removeCoupon($e_id, $c_id) {
	global $util, $sql;
	$event = new Event($e_id);

	if($event->anyRegistrantHasCoupon($c_id))
		die($util->errorFormat('Registrants have already used this coupon code so it cannot be deleted!
			To stop more registrants from using it, disable it.'));

	$query = 'DELETE FROM '. TABLE_E_COUPONS .' WHERE event_id='. $e_id .' AND id='. $c_id;

	if(!$sql->q($query))
		die($util->errorFormat(CRITICAL_ERROR));
}

function addNewRace() {
	global $util, $edited_race;
	$edited_race->errorCheck();
	if($edited_race->getErrNum() > 0)
		die($util->errorFormat($edited_race->getErrList()));

	$edited_race->prepareForDB();
	if(!$edited_race->addToDB($edited_race->getEventId()))
		die($util->errorFormat(CRITICAL_ERROR));
}

function deleteRace($edited_race) {
	global $util;
	$event = new Event($edited_race->getEventId());

	if($event->hasRegistrantsInRace($edited_race->getId()))
		die($util->errorFormat('This race has registrants and cannot be deleted.'));
	if($event->numRaces() == 1)
		die($util->errorFormat('You must keep at least one race.'));

	if(!$edited_race->deleteSelf())
		die($util->errorFormat(CRITICAL_ERROR));
}

function editRace() {
	global $util, $edited_race;
	$edited_race->errorCheck();
	if($edited_race->getErrNum())
		die($util->errorFormat($edited_race->getErrList()));

	if(!$edited_race->updateDB())
		die($util->errorFormat(CRITICAL_ERROR));
}

function editEventDetails() {
	global $post, $util, $edited_event, $event;

	$edited_event->eventDetailsErrorCheck($event);

	if($edited_event->errorsExist())
		die($util->errorFormat($edited_event->errString()));

	if(!$edited_event->updateEventDetails())
		$edited_event->addErr(CRITICAL_ERROR);

	if($edited_event->errorsExist())
		echo $util->errorFormat($edited_event->errString());
}
?>
