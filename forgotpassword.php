<?php
require_once 'include/critical.php';

$code = $util->cleanUp($_GET['code'], 32);
$email = $util->cleanUp(urldecode($_GET['email']), 255);

$reset_pass = isset($_GET['code']) && isset($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title>Run That Race - Reset Password</title>
	<?php
	include ROOT .'include/core/head_tag.php';
	?>
    <script type="text/javascript" src="/include/resetpass.js"></script>
    <script type="text/javascript" src="/include/base.js"></script>
</head>
<body><?php
	include ROOT .'include/core/navigation.php';
	?>
	<div id="wrapper">
		<?php
		include ROOT .'include/core/head.php';
		?>
		<div id="content">
			<h2>Password Reset</h2><?php
			if($reset_pass && $util->getPasswordResetAttempts($email) > 0) {
				if($util->resetPassAuthenticated($code, $email)) {
					$util->updateResetAttempts($email, 0);
					echo '<h3>Thank you!</h3><p>Your password has been reset and emailed to you. Please check your email for your new password and change it as soon as you can in "My account".</p>';
				} else {
					echo '<h3>Sorry!</h3><p>There was a problem with your authentication. Please try again later and make sure you used the correct link found in your email.</p>';
				}
			} else {?>
				<p>
					If you have forgotten your password, you may use our password reset form here.
					Just fill in this form and you will receive an email with further
					instructions.
				</p>
				<p>
					Please follow the instructions you receive in the email immediately.
					It will expire after the day it is sent and you will need to submit
					this form again.
				</p>
				<form action="/include/requests/resetPasswordProcess.php" method="post" id="reset-password-form">
					<div class="form-errors"></div>
					<label for="pass-reset-email" class="req">Email Address:</label>
					<input type="text" id="pass-reset-email" name="email" size="30" maxlength="100" />
					<p class="indent-2">
						<input type="submit" value="Send Email" />
						<input type="hidden" name="type" value="sendEmail" />
					</p>
				</form><?php
			}?>
		</div>
		<div id="footer">
			<?php include ROOT .'include/core/footer.php'; ?>
		</div>
	</div>
</body>
</html>
