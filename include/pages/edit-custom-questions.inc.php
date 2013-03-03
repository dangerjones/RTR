<?php

require_once ROOT .'classes/customquestion.php';

if(!$event->hasQuestions())
	echo '<p>This event has no custom questions for registration.</p>';
else { ?>
	<form action="" method="post">
		<p>Question:
			<select id="edit-event-all-custom-questions">
			<?php
			$questions = $event->getQuestionIds();

			foreach($questions as $q_id) {
				$q = new CustomQuestion($q_id);
				echo '<option value="'. $q->getId() .'">'. $q->getQuestion() .'</option>';
			} ?>
			</select>
			<a id="edit-event-edit-custom-question" class="button">Edit</a>
			<a id="edit-event-delete-custom-question" class="button">Delete</a>
		</p>
	</form>
	<?php
}
?>
<p>
	<a id="edit-event-new-custom-question" class="button">New Question</a>
</p>