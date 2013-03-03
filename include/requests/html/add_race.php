<?php
$num = isset($_POST['num']) ? (int)$_POST['num'] : 1;
?>
<ul class="race-holder">
	<li><h3>Race <?php echo $num ?>:</h3></li>
	<li><span class="req">Name:</span><input type="text" name="race-name[]" maxlength="100" size="40" /></li>
	<li><span class="req">Distance:</span><input type="text" name="race-distance[]" maxlength="5" size="5" />
		<select name="race-dist-measure[]"><option value="0">km</option><option value="1">mi</option></select>
		<span class="no-level req">Start time:</span> <input type="text" name="race-time[]" maxlength="5" size="5" />
		<select name="race-time-isam[]"><option value="true">am</option><option value="false">pm</option></select></li>
	<li><span class="req">Location:</span><input type="text" name="race-location[]" maxlength="255" size="40" /></li>
	<li><span class="no-level req">Registration costs:</span></li>
	<li class="level-1"><input type="checkbox" name="race-free[]" value="true" /> Free registration costs ($0.00)
		<input class="race-free-filler" type="hidden" name="race-free[]" value="false" /></li>
	<li class="level-1 race-prices"><span>Race day:</span>$<input type="text" name="race-dayof[]" size="5" /></li>
	<li class="level-1 race-prices"><span class="req">Pre Reg:</span>$<input type="text" name="race-prereg[]" size="5" /></li>
	<li class="level-1 race-prices"><span>Early bird:</span>$<input type="text" name="race-early[]" size="5" /></li>
	<li class="level-2 race-prices">Early price until: <input type="text" name="race-early-date[]" size="10" maxlength="10" /><br />(mm/dd/yyyy)</li>
	<?php
		if($num > 1)
			echo '<li><a class="remove-race button">Delete Race #'. $num .'</a></li>';
	?>
</ul>