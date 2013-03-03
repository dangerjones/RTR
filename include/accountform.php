<form action="/include/core/loginProcesses.php" method="post" id="new-account-form">
	<div class="form-errors"></div>
		<p>
			<label for="new-account-email" class="req level-1">Email:</label>
			<input id="new-account-email" type="text" name="email" size="30" maxlength="100" />
		</p>
		<p>
			<label for="new-account-pass" class="req level-1">Password:</label>
			<input id="new-account-pass" type="password" name="pass" value="" />
		</p>
		<p>
			<label for="new-account-retype-pass" class="req level-1">Re-type:</label>
			<input id="new-account-retype-pass" type="password" name="retype_pass" />
		</p>
		<p>
			<label for="new-account-fname" class="req level-1">First name:</label>
			<input type="text" id="new-account-fname" name="fname" maxlength="100" />
		</p>
		<p>
			<label for="new-account-lname" class="req level-1">Last name:</label>
			<input type="text" id="new-account-lname" name="lname" maxlength="100" />
		</p>
		<p class="b-control"><label for="reg-b-control">Do not change this:</label>
			<input id="reg-b-control" type="text" name="b_name" />
		</p>
	<div class="submit-wrap">
			<input type="submit" value="Next" />
	</div>
	<input type="hidden" name="f_submit" value="register" />
</form>

