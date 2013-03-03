<h3 class="header">Change Password</h3>
<div class="indent-1">
	<form id="change-password-form" action="/include/requests/edit-my-account.php" method="post">
		<div class="form-errors"></div>
		<ul>
			<li>
				<label for="cp-curr-pass" class="req level-2">Current Password:</label>
				<input type="password" id="cp-curr-pass" name="curr_pass" size="30" maxlength="100" />
			</li>
			<li>
				<label for="cp-new-pass" class="req level-2">New Password:</label>
				<input type="password" id="cp-new-pass" name="new_pass" size="30" maxlength="100" />
			</li>
			<li>
				<label for="cp-confirm-pass" class="req level-2">Confirm Password:</label>
				<input type="password" id="cp-confirm-pass" name="confirm_pass" size="30" maxlength="100" />
			</li>
		</ul>
		<div class="submit-wrap">
			<img src="/img/loaderbar.gif" alt="Loading..." id="pass-change-loader" class="invisible" /><br />
			<input type="submit" value="Save" />
			<input type="hidden" name="type" value="change-pass" />
		</div>
	</form>
</div>