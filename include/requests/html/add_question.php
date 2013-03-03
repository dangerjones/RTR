<?php
$num = isset($_POST['num']) ? (int)$_POST['num'] : 1;

?>
<ul class="hide-question">
	<li class="level-1"><span>Question <?php echo $num; ?>:</span><input type="text" name="event-question-<?php echo $num; ?>" maxlength="100" /></li>
	<li class="level-1"><span class="level-1">Available answers:</span><input type="text" name="event-answer-<?php echo $num; ?>[]" maxlength="50" /></li>
	<li class="level-3"><input type="text" name="event-answer-<?php echo $num; ?>[]" maxlength="50" /></li>
	<li class="level-3"><a class="add-answer button">Add answer</a>
		<a class="delete-answer button">Delete</a></li>
</ul>