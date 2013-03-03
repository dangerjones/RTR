<?php
require_once '../../critical.php';

if(!$util->isAjax())
	die('Unauthorized');

$e_id = (int)$_POST['e_id'];
$r_id = (int)$_POST['race_id'];
$new_race = $_POST['new_race'] == true;


if(!$new_race) {
	if(!$util->raceExists($e_id, $r_id))
		die('Invalid request');

	$race = new Race($r_id);
	$measure = $race->getDistanceMeasure();
	$isam = $race->isAm();
	$select = ' selected="selected"';
	$checked = ' checked="checked"';

	$km = $measure == 'km' ? $select:'';
	$mi = $measure == 'mi' ? $select:'';

	$am = $isam ? $select:'';
	$pm = $isam ? '':$select;

	$early = $race->getPrice('early');
	$prereg = $race->getPrice('prereg');
	$dayof = $race->getPrice('dayof');
	$free = $prereg == 0 ? $checked:'';
}
?>
<div id="edit-form-wrapper">
<form action="/include/requests/editEventProcess.php" method="post"
	  id="<?php echo $new_race ? 'add-new-race-form':'edit-race-form'; ?>">
	<div class="form-errors"></div>
	<ul>
		<li>
			<label for="edit-race-name" class="req level-1">Name:</label>
			<input type="text" name="edit_race_name" id="edit-race-name" size="40"
				value="<?php echo $new_race ? '':$race->getName(); ?>" />
		</li>
		<li>
			<label for="edit-race-distance" class="req level-1">Distance:</label>
			<input type="text" name="edit_race_distance" id="edit-race-distance" size="5"
				value="<?php echo $new_race ? '':$race->getDistanceInt(); ?>" />
			<select name="edit_race_measure">
				<option value="0"<?php echo $km; ?>>km</option>
				<option value="1"<?php echo $mi; ?>>mi</option>
			</select>
			<label for="edit-race-time" class="req">Start time:</label>
			<input type="text" name="edit_race_time" id="edit-race-time" size="5"
				value="<?php echo $new_race ? '':$race->getStartTime('g:i'); ?>"/>
			<select name="edit_race_isam">
				<option value="true"<?php echo $am; ?>>am</option>
				<option value="false"<?php echo $pm; ?>>pm</option>
			</select>
		</li>
		<li>
			<label for="edit-race-location" class="req level-1">Location:</label>
			<input type="text" name="edit_race_location" id="edit-race-location" size="40"
				value="<?php echo $new_race ? '':$race->getLocation(); ?>" />
		</li>
		<li class="req">Registration costs:</li>
		<li class="indent-2">
			<input type="checkbox" id="edit-race-free" name="edit_race_free" value="true"<?php echo $free; ?> />
			<label for="edit-race-free">Free registration costs ($0.00)</label>
		</li>
		<li class="indent-2 edit-price-fields">
			<label for="edit-race-raceday" class="level-1">Race day:</label>
			$<input type="text" name="edit_race_raceday" id="edit-race-raceday" size="5"
				value="<?php echo $dayof > 0 ? $dayof:''; ?>" />
		</li>
		<li class="indent-2 edit-price-fields">
			<label for="edit-race-prereg" class="req level-1">Pre reg:</label>
			$<input type="text" name="edit_race_prereg" id="edit-race-prereg" size="5"
				value="<?php echo $prereg > 0 ? $prereg:''; ?>" />
		</li>
		<li class="indent-2 edit-price-fields">
			<label for="edit-race-early" class="level-1">Early bird:</label>
			$<input type="text" name="edit_race_early" id="edit-race-early" size="5"
				value="<?php echo $early > 0 ? $early:''; ?>" />
		</li>
		<li class="indent-3 edit-price-fields">
			<label for="edit-race-early-date">Early price until:</label>
			<input type="text" name="edit_race_early_date" id="edit-race-early-date" size="10"
				   value="<?php echo $new_race ? '':$race->getEarlyDate('m/d/Y'); ?>" /><br />(mm/dd/yyyy)
		</li>
	</ul>
	<div class="submit-wrap">
		<img src="/img/loaderbar.gif" alt="Loading..." class="invisible" />
	</div>
	<div class="bottom-buttons">
		<input type="hidden" name="edit_type" value="<?php echo $new_race ? 'new_race':'races'; ?>" />
		<input type="hidden" name="e_id" value="<?php echo $e_id; ?>" />
		<?php
		if(!$new_race) { ?>
			<input type="hidden" name="race_id" value="<?php echo $race->getId(); ?>" />
			<?php
		}
		?>

		<input type="submit" value="<?php echo $new_race ? 'Add race':'Save changes'; ?>" class="button" />
		<a class="button">Cancel</a>
	</div>
</form>
</div>