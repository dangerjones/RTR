<?php
require_once '../../critical.php';
require_once ROOT .'classes/simplesanitize.php';
require_once ROOT .'classes/customquestion.php';

if(!$util->isAjax())
	die('Unauthorized');

$post = new SimpleSanitize('post', 'strict');

$e_id = $post->getInt('e_id');

if(!$util->eventExists($e_id))
	die('Invalid event');

$event = new Event($e_id);

if(!$event->hasAccess($user->getId()))
	die('Unauthorized');

$q_ids = $event->getQuestionIds();
$questions = array();

foreach($q_ids as $q_id) {
	$q = new CustomQuestion($q_id);
	$questions[] = array('question' => $q->getQuestion(), 'id' => $q_id);
}

echo json_encode($questions);

?>
