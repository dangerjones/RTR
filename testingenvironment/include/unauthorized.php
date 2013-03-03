<?php

echo '<h3>Unable to proceed! Please login.</h3>';

if(!$user->loggedIn()) {
	echo '<p>In order to continue, you must login to our site. If you have already created an account,';
	echo 'you may login above. Otherwise, you will need to <a href="/new-account">create your '. COMPANY_NAME .' account</a>.';
}
else
	echo '<p>Please login as an administrator.</p>'

?>
