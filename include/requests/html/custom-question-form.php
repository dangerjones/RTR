<?php
require_once '../../critical.php';
require_once ROOT .'classes/simplesanitize.php';
require_once ROOT .'classes/customquestion.php';

$post = new SimpleSanitize('post', 'both', 255);

$e_id = $post->getInt('e_id');
$q_id = $post->getInt('question_id');

if(!$util->eventExists($e_id))
	die($util->errorFormat('Invalid event'));

$event = new Event($e_id);

if(!$event->hasAccess($user->getId()))
	die($util->errorFormat('Permissions denied'));

if($event->hasQuestion($q_id)) {
	$question = new CustomQuestion($q_id);
	$q = $question->getQuestion();
	$a = $question->getAnswers();
	$type = 'edit-custom-q';
} else {
	$type = 'new-custom-q';
}

?>
<form action="/include/requests/editEventProcess.php" method="post" id="edit-event-custom-questions-form">
	<div class="form-errors"></div>
	<ul id="edit-event-custom-questions-list">
		<li>
			<label for="c-q-f-question">Question:</label>
			<input type="text" id="c-q-f-question" name="question" value="<?php echo $q; ?>" size="45" maxlength="100" />
		</li>
		<li>
			<label for="c-q-f-answer1">Available answers:</label>
			<a id="edit-event-add-a-question" class="button">Add answer</a>
			<a id="edit-event-remove-a-question" class="button">Remove</a>
		</li>
		<li class="level-2">
			<input type="text" class="answer-boxes" id="c-q-f-answer1" name="answer[]" value="<?php echo $a[0]; ?>" size="30" maxlength="75" />
		</li>
		<li class="level-2">
			<input type="text" class="answer-boxes" name="answer[]" value="<?php echo $a[1]; ?>" size="30" maxlength="75" />
		</li>
		<?php
		for($i = 2; $i < count($a); $i++) {
			echo '<li class="level-2">';
			echo '<input type="text" class="answer-boxes" name="answer[]" value="'. $a[$i] .'" size="30" maxlength="75" />';
			echo '</li>';
		}
		?>
	</ul>
	<div class="submit-wrap">
		<img src="/img/loaderbar.gif" alt="Loading..." class="invisible" />
	</div>
	<div class="bottom-buttons">
		<input type="submit" class="button" value="Save" />
		<a class="button">Cancel</a>
		<input type="hidden" name="edit_type" value="<?php echo $type; ?>" />
		<input type="hidden" name="e_id" value="<?php echo $e_id; ?>" />
		<input type="hidden" name="q_id" value="<?php echo $q_id; ?>" />
	</div>
</form>