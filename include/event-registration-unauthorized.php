
<h3>Step 1 - Login or Create Account</h3>
<?php if(!$user->loggedIn()) { ?>
	<p>Please login above or create a new account below.</p>
<?php } else { ?>
	<p>Please login as an administrator.</p>

<?php } ?>
