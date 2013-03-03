<?php
require_once '../../critical.php';

$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

$name = $post['person_name'];
?>
<p class="success">
	Registration for <?php echo $name; ?> was successfully submitted,
	but the registration process is NOT yet complete! Please review those that
	are currently registering and choose another option below.
</p>

<div class="ui-state-highlight">
<p class="alert">
	<strong>REMEMBER!</strong> Your registrations will not be complete or accepted
	until payment is made or registration is finalized!
</p>
</div>

<div class="currently-registered">
	<?php
	include ROOT .'include/requests/event-registrants.php';
	?>
</div>