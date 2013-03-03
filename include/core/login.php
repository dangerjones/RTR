<?php

if($user->loggedIn())
	showAccount();
else
	showLoginForm();

/*
 * functions to output the corresponding form
 */
function showLoginForm() {
    $err = isset($_SESSION['err']['log']) ? $_SESSION['err']['log'] : '';
    ?>
    <div id="log-form">
		<?php if(!empty($err)) { echo $err; unset($_SESSION['err']['log']); } ?>
        <form action="/include/core/loginProcesses.php" method="post" id="log-container">
            <ul class="clear">
                <li><input id="login-email" class="login dummy" type="text" name="email" value="Email" maxlength="100" size="25" />
					<input id="dummy-input" type="text" class="login dummy" value="Password" size="25" />
                    <input id="login-pass" class="login no-show" type="password" name="pass" value="" size="25" /></li>
                <li><input id="login-submit" type="submit" value="Login" /></li>
                <li><a href="/new-account" id="change-to-reg-">Register Now!</a> &raquo;</li>
                <li><a href="/forgot-password">Forgot Password?</a> &raquo;</li>
                <li class="b-control">Do not change this: <input type="text" name="b-name" value="" /></li>
            </ul>
            <input type="hidden" name="f_submit" value="login" />
        </form>
    </div>
    <?php
}

function showAccount() {
    global $user;
    ?>
    <p>Welcome <strong><?php echo $user->getFname() == '' ? $user->getEmail() : $user->getFName(); ?></strong>!<?php
	if($user->isAdmin())
		echo ' <em>('. LVL_ADMIN_TAG .')</em>';
	?></p>
	<ul><?php
		if($user->isAdmin()) {
		echo '<li><a href="/admin-panel">Admin Panel</a> &raquo;</li>';
		}
		?>
		<li><a href="/my-account">My account</a> &raquo;</li>
	</ul>
    <form action="/include/core/loginProcesses.php?f_submit=logout" method="post"><input type="submit" id="logout" value="Logout" /></form>
    <?php
}
?>



