<?php
require_once 'include/critical.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title><?php echo TITLE_PREFIX; ?>New Account</title>
	<?php
	include ROOT .'include/core/head_tag.php';
	?>
    <link rel="stylesheet" type="text/css" href="/include/core/css/form.css" />
    <script type="text/javascript" src="/include/new-account.js"></script>
</head>
<body><?php
	include ROOT .'include/core/navigation.php';
	?>
	<div id="wrapper">
		<?php
		include ROOT .'include/core/head.php';
		?>
		<div id="content">
			<h2>Register a New Account</h2>
			<?
			if($user->loggedIn()) {
				echo '<p>You already have an account! To register a new one, please logout and try again.</p>';
			} else { ?>

				<form action="/include/core/loginProcesses.php" method="post" id="new-account-form">
					<div class="form-errors"></div>
					<ul>
						<li>
							<label for="new-account-email" class="req level-1">Email:</label>
							<input id="new-account-email" type="text" name="email" size="30" maxlength="100" />
						</li>
						<li>
							<label for="new-account-pass" class="req level-1">Password:</label>
							<input id="new-account-pass" type="password" name="pass" value="" />
							<label for="new-account-retype-pass" class="req level-1">Re-type:</label>
							<input id="new-account-retype-pass" type="password" name="retype_pass" />
						</li>
						<li>
							<label for="new-account-fname" class="req level-1">First name:</label>
							<input type="text" id="new-account-fname" name="fname" maxlength="100" />
							<label for="new-account-lname" class="req level-1">Last name:</label>
							<input type="text" id="new-account-lname" name="lname" maxlength="100" />
						</li>
						<li class="b-control"><label for="reg-b-control">Do not change this:</label>
							<input id="reg-b-control" type="text" name="b_name" />
						</li>
					</ul>
					<div class="submit-wrap">
							<input type="submit" value="Register Account" />
					</div>
					<input type="hidden" name="f_submit" value="register" />
				</form>


			<?php
			}
			?>
		</div>
		<div id="footer">
			<?php include ROOT .'include/core/footer.php'; ?>
		</div>
	</div>
</body>
</html>