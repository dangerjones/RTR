<form action="/include/requests/updateEventUploads.php" method="post" id="edit-event-assign-race-results-form">
	<div class="form-errors"></div>
	<ul>
		<?php

		echo '<li><label for="edit-event-race-results" class="level-2">Select a race:</label> ';
		echo '<select id="edit-event-race-results" name="race_id">';

		foreach($races as $race) {
			echo '<option value="'. $race->getId() .'">'. $race->getName() .'</option>';
		}

		echo '</select></li>';

		?>
		<li>
			<label for="edit-event-results-name" class="level-2">Name for results:</label>
			<input type="text" id="edit-event-results-name" name="name" size="30" />
		</li>
		<li>
			<label class="level-2"><a class="button" id="edit-race-add-results-to-race">Choose a file</a></label>
			<input type="text" id="edit-event-results-filename" name="file" readonly="readonly" size="50" />
		</li>
	</ul>
	<div class="submit-wrap">
		<img src="/img/loaderbar.gif" alt="Loading..." class="invisible" /><br />
		<input type="submit" value="Save results" />
		<input type="hidden" name="e_id" value="<?php echo $e_id; ?>" />
		<input type="hidden" name="type" value="add-race-results" />
	</div>
</form>
<div id="edit-event-current-race-results"class="indent-1">
	<?php
	include ROOT .'include/requests/html/getRaceResults.php';
	?>
</div>