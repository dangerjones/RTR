<?php
echo '<div class="nav-buttons">';
echo '<a href="/events/'. $event->getPermalink() .'" class="button">View event</a> ';
echo '<a href="/registrants/'. $event->getPermalink() .'" class="button">View registrants ('. $event->numPaidRegistrants() .')</a>';
echo '</div>';

if($event->hasRegistrants())
	echo '<div class="ui-state-highlight"><p class="alert">This event already has registrants so
		some parts may not be editable.</p></div>';
?>

<h3 class="header">Event Details</h3>
<div id="edit-event">
<form id="edit-event-details" action="/include/requests/editEventProcess.php" method="post">
	<div class="form-errors"></div>
	<input type="hidden" name="e_id" value="<?php echo $event->getId(); ?>" />
	<input type="hidden" name="u_id" value="<?php echo $user->getId(); ?>" />
	<div class="column-half">
		<ul>
			<li>
				<label class="req level-1" for="event-name">Name:</label>
					<input type="text" name="event_name" id="event-name"
						value="<?php echo $event->getName(); ?>" size="40" maxlength="255" />
			</li>
			<li><label class="req level-1" for="event-description">Description:</label></li>
			<li class="level-1">
				<textarea rows="7" cols="40" name="event_description"
					id="event-description"><?php echo $event->getDescription(); ?></textarea>
			</li>
			<li>Registration methods:</li>
			<li class="level-1">
				<input type="checkbox" name="event_reg_here" value="true"
					id="event-reg-here"<?php echo $event->registerHere() ? ' checked="checked"':'';?> />
				<label for="event-reg-here">Enable online registration through this website
					(service fees apply)</label>
			</li>
			<li class="level-2">
				<input type="checkbox" name="event_reg_fee" id="event-reg-fee"<?php
					echo $event->directorPays() ? '':' checked="checked"'; ?> value="true" />
				<label for="event-reg-fee">Registrants pay service fee</label>
			</li>
			<li class="level-1 reg-method">
				<input type="checkbox" name="event_reg_methods[]" value="<?php echo EREG_METHOD_WEB; ?>"
					id="event-website"<?php echo $event->hasRegMethod(EREG_METHOD_WEB) ? ' checked="checked"':''; ?> />
				<label class="level-1 text-left" for="event-website">Website</label>
				<input type="text" name="event_website" size="25"
					value="<?php $website = $event->getRegMethod(EREG_METHOD_WEB);
					echo empty($website) ? 'http://':$website; ?>" maxlength="255" />
			</li>
			<li class="level-1 reg-method">
				<input type="checkbox" name="event_reg_methods[]" value="<?php echo EREG_METHOD_ADDR1; ?>"
					id="event-address1"<?php echo $event->hasRegMethod(EREG_METHOD_ADDR1) ? ' checked="checked"':''; ?> />
				<label class="level-1 text-left" for="event-address1">Address 1</label>
				<input type="text" name="event_address1" size="25"
					value="<?php echo $event->getRegMethod(EREG_METHOD_ADDR1); ?>" maxlength="255" />
			</li>
			<li class="level-1 reg-method">
				<input type="checkbox" name="event_reg_methods[]" value="<?php echo EREG_METHOD_ADDR2; ?>"
					id="event-address2"<?php echo $event->hasRegMethod(EREG_METHOD_ADDR2) ? ' checked="checked"':''; ?> />
				<label class="level-1 text-left" for="event-address2">Address 2</label>
				<input type="text" name="event_address2" size="25"
					value="<?php echo $event->getRegMethod(EREG_METHOD_ADDR2); ?>" maxlength="255" />
			</li>
		</ul>
	</div>

	<div class="column-half">
		<ul>
			<li>
				<label class="req level-0" for="event-type">Type:</label>
					<select name="event_type" id="event-type">
						<?php
						foreach($event_types as $val => $name) {
							if($event->getType() == $name)
								$select = ' selected="selected"';
							else
								$select = '';
							echo '<option value="'. $val .'"'. $select .'>'. $name .'</option>';
						}
						?>
					</select>
				<label for="event-date" class="req level-0">Date:</label>
					<input type="text" name="event_date" id="event-date"
						value="<?php echo $event->getDate('m/d/Y'); ?>" maxlength="10" size="10" /> (mm/dd/yyyy)
			</li>
			<li>
				<label class="req" for="event-permalink">Permalink:</label>
				<input type="text" name="event_permalink" id="event-permalink"
					value="<?php echo $event->getPermalink(true); ?>" maxlength="255" size="40" />
			</li>
			<li>
				<span class="smaller">
					(<?php echo BASEURL; ?>events/<span id="edit-permalink"><?php echo $event->getYear(); ?>/<?php echo $event->getPermalink(true); ?></span>)
				</span>
			</li>
			<li>
				<label class="level-5" for="event-contact">Contact Info:</label>
					<input type="text" name="event_contact" id="event-contact"
						value="<?php echo $event->getContactInfo(); ?>" size="40" maxlength="255" />
			</li>
		</ul><?php

		showEventShirtOptions();

		?>
	</div>
	<div class="submit-wrap">
		<img class="invisible" src="/img/loaderbar.gif" alt="Loading..." /><br />
		<input type="hidden" name="edit_type" value="details" />
		<input type="submit" name="edit_event_submit" value="Save Event Details" />
	</div>
</form>
<h3 class="header">Races</h3>
<form action="#" id="edit-event-race-form">
	<p class="indent-1">
		Current races: <select id="edit-event-races">
			<?php
			$races = $event->getRaces();
			foreach($races as $r) {
				echo '<option value="'. $r->getId() .'">'. $r->getName() .'</option>';
			}
			?>
		</select>
		<input type="submit" value="Edit" />
		<input type="submit" value="Delete" />
	</p>
	<p class="indent-1">
		<a id="add-new-race" class="button">New race</a>
	</p>
</form>
<h3 class="header">Promo coupons</h3>
<div class="indent-1">
	<div id="edit-event-coupon-list">
		<?
		include 'include/requests/html/getCouponList.php';
		?>
	</div>
	<p>
		Note: When a coupon has been used by a registrant, that coupon cannot be deleted.
		To stop its usage, use the disable option
		<img src="/img/red-minus-icon.png" alt="Disable image" title="Disable icon" />.
		Coupons may be enabled/disabled freely.
	</p>
	<div id="add-new-coupon-dialog" class="no-show" title="New promo coupon">
		<div class="form-errors"></div>
		<form action="/include/requests/editEventProcess.php" method="post" id="add-new-coupon-form">
			<p>
				<label for="coupon-codename">Codename:</label>
				<input type="text" name="coupon_codename" id="coupon-codename" maxlength="30" />

				<label for="coupon-amount">Discount:</label>
				$<input type="text" name="coupon_amount" id="coupon-amount" maxlength="10" size="5" />
			</p>

			<div class="submit-wrap">
				<img src="/img/loaderbar.gif" alt="Loading..." class="invisible" />
			</div>
		</form>
	</div>
</div>
<p class="indent-1"><a id="add-new-coupon" class="button">New coupon</a></p>
</div>

<?php
if($event->isOwner($user->getId()) || $user->isAdmin()) {
	?>
	<h3 class="header">Event Permissions</h3>
	<p class="indent-1">
		As the owner/creator of this event, you have the option of allowing other users that you
		choose to have the ability to edit this event. By giving users this access, they
		will not have the ability to add other users.
	</p>
	<p class="indent-1">
		<strong>Current users:</strong>
		<span id="has-edit-access"><?php include ROOT .'include/requests/hasEditAccess.php'; ?></span>
	</p>
	<form action="#" class="indent-1">
		<a class="button" id="grant-access-to-user">Add a user</a>
		<a class="button" id="invite-and-add-user">Invite and add</a>
	</form>
	<div id="grant-access" class="no-show">
		<form action="#">
			<div class="form-errors"></div>
			<p>Enter another user's email to give them access to edit this event.</p>
			<label for="access-email" class="req">User's Email:</label>
			<input type="text" name="access_email" id="access-email" size="30" />
			<div class="submit-wrap">
				<img class="invisible" src="/img/loaderbar.gif" alt="Loading..." />
			</div>
			<div class="bottom-buttons">
				<input type="submit" value="Add user" /> <a class="button">Cancel</a>
			</div>
		</form>
	</div>
	<div id="invite-and-add-form" class="no-show">
		<form action="/include/requests/addRemoveAccessUsers.php" method="post">
			<div class="form-errors"></div>
			<p>
				If you would like to give access to a friend that currently does not have an account, add their email here
				and we will automatically create an account for them, send them details on how to login,
				and give them access to edit this event.
			</p>
			<p class="center">
				<label for="invite-someone-to-edit" class="req">Email address:</label>
				<input type="text" name="email" id="invite-someone-to-edit" size="30" maxlength="100" />
			</p>
			<div class="submit-wrap">
				<img src="/img/loaderbar.gif" alt="Loading..." class="invisible" />
				<input type="hidden" name="type" value="invite" />
				<input type="hidden" name="e_id" value="<?php echo $event->getId(); ?>" />
			</div>
			<div class="bottom-buttons">
				<input type="submit" value="Send invitation" />
				<a class="button">Cancel</a>
			</div>
		</form>
	</div>
	
	<?php
}
?>
<h3 class="header">Custom Questions</h3>
	<div class="indent-1">
		<?php include ROOT .'include/pages/edit-custom-questions.inc.php'; ?>
	</div>
<h3 class="header">Uploads</h3>
	<div class="indent-1">
		<?php include ROOT .'include/pages/edit-uploads.inc.php'; ?>
	</div>
	<h4 class="indent-1">Race results</h4>
	<p class="indent-1">Please upload your race results first (above) and then assign it to a race (below). Only results in PDF format are accepted.</p>
	<?php include ROOT .'include/pages/add-race-results.inc.php'; ?>
<?php
if($event->isOwner($user->getId()) || $user->isAdmin()) { ?>
	<h3 class="header">Remove Event</h3>
	<p class="indent-1">
		Deleting your event will remove it completely from our website. You may only delete your event
		if no one has registered for it through our website. To stop online registrations, please edit
		the event above. Deleting is not reversible! <strong>Only you as the creator of this event may delete it.</strong>
	</p>
	<form action="" method="post" id="edit-event-delete-event">
		<div class="submit-wrap">
			<input type="submit" value="Delete event" />
		</div>
	</form>
	<?php
}
?>
<?php
function showEventShirtOptions() {
	global $event, $youth_shirt_sizes, $adult_shirt_sizes;

	$disable_shirts_available = $event->hasRegistrantWithShirt() ? ' readonly="readonly"':'';
	$yes = $event->hasShirts() ? ' checked="checked"':'';
	$no  = !$event->hasShirts() ? ' checked="checked"':'';
	?>
	<ul>
		<li>Shirts Available?
			<input type="radio" name="event_shirts_available" id="event-shirts-available-y"
				   value="true"<?php echo $yes.$disable_shirts_available ?> />
				<label for="event-shirts-available-y">Yes</label>
			<input type="radio" name="event_shirts_available" id="event-shirts-available-n"
				   value="false"<?php echo $no.$disable_shirts_available; ?> />
				<label for="event-shirts-available-n">No</label>
		<ul id="shirt-options">
			<li><span class="level-1">Youth:</span><?php

			foreach($youth_shirt_sizes as $val => $name) {
				if($event->hasShirt($name, 'y'))
					$check = ' checked="checked"';
				else
					$check = '';

				if($event->hasRegistrantWithShirt($name, 'y'))
					$disable_youth = ' readonly="readonly"';
				else
					$disable_youth = '';

				echo '<input type="checkbox" name="event_youth_shirt[]" id="e-y-s-'. $name .'"'.
						'value="'. $val .'"'. $check.$disable_youth .' />
						<label for="e-y-s-'. $name .'">'. strtoupper($name) .'</label> ';
			}

			?></li>
			<li><span class="level-1">Adult:</span><?php

			foreach($adult_shirt_sizes as $val => $name) {
				if($event->hasShirt($name, 'a'))
					$check = ' checked="checked"';
				else
					$check = '';

				if($event->hasRegistrantWithShirt($name, 'a'))
					$disable_adult = ' readonly="readonly"';
				else
					$disable_adult = '';

				echo '<input type="checkbox" name="event_adult_shirt[]" id="e-a-s-'. $name .'"'.
						'value="'. $val .'"'. $check.$disable_adult .' />
						<label for="e-a-s-'. $name .'">'. strtoupper($name) .'</label> ';
			}

			$yes = $event->allowNoShirt() ? ' checked="checked"':'';
			$no = !$event->allowNoShirt() ? ' checked="checked"':'';
			if($event->hasRegistrantWithNoShirt())
				$disable_noshirt = ' readonly="readonly"';
			?></li>
			<li class="level-1">Allow a "No Shirt" option for registrants?<br />
				(only for online registrations)
				<input type="radio" name="event_no_shirt" id="event-no-shirt-y" 
					value="true"<?php echo $yes.$disable_noshirt; ?> />
					<label for="event-no-shirt-y">Yes</label>
				<input type="radio" name="event_no_shirt" id="event-no-shirt-n" 
					value="false"<?php echo $no.$disable_noshirt; ?> />
					<label for="event-no-shirt-n">No</label>
			</li>
			<li class="level-2"><label for="event-shirt-discount">If any, "no shirt" discount:</label>
					$<input type="text" name="event_shirt_discount" id="event-shirt-discount"
						value="<?php echo $event->getNoShirtDiscount(); ?>" 
						size="3" maxlength="10"<?php echo $disable; ?> />
			</li>
		</ul>
		</li>
	</ul>
<?php
}
?>