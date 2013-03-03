<?php
if(!defined('ROOT')) {
	require_once '../../critical.php';
	require_once ROOT .'classes/simplesanitize.php';

	if(!$util->isAjax())
		die('Unauthorized');

	$post			= new SimpleSanitize('post', 'none');
	$editing		= $post->getBoolean('editing');
	$e_id			= $post->getInt('e_id');
	$event			= EventHandler::getEvent($e_id);
	$exists			= $event !== null;
	$can_edit		= $exists ? $event->hasAccess($user->getId()):false;

	if(!$exists)
		die('Invalid event');

	$races = $event->getRaces();

} else {
	$editing = true;
}

require_once ROOT .'classes/raceresult.php';

foreach($races as $r) {
	if($r->hasResults()) {
		echo '<h4>'. $r->getName() .'</h4>';

		echo '<ul>';
		foreach($r->getResults() as $result_id) {
			$result = new RaceResult($result_id);
			echo '<li>';
			if($editing && $can_edit)
				echo '<a class="edit-event-remove-race-results" rel="'. $result->getId() .'"><img src="/img/round-cancel.png" alt="Delete" title="Remove results" /></a> ';
			echo '<a href="'. $result->getPathToFile() .'" target="_blank">'. $result->getName() .'</a>';
			echo '</li>';
		}
		echo '</ul>';
	}
}

?>
