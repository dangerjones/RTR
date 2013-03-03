<?php
if($event !== null) {
	$event_id = $event->getId();

	echo '<h2 class="float-l">Registration: '. $event->getName() .'</h2>';
	echo '<div class="nav-buttons"><a href="/events/'. $event->getPermalink() .'" class="button">Back to event</a></div>';
	if(!$event->registrationAllowed())
		echo 'Online registration through '. COMPANY_NAME .' is closed';
	else if($event->canRegister()) { 
		?>
		<div class="form-errors"></div>
		<form action="/include/requests/registerProcess.php" method="post" id="register-form">
			<div class="currently-registered"><?php include ROOT .'include/requests/event-registrants.php'; ?></div>
			
			<input type="hidden" name="register_event" value="<?php echo $event->getId(); ?>" />
			
			<div id="personal-saved-info">
				<?php include ROOT .'include/requests/friendsfamily_select.php'; ?>
			</div>
			
			<ul>
				<li>Save this information for future use as:
					<input type="radio" name="register_save" id="reg_save_me" value="0"
						<?php echo $user->hasPersonalInfo() ? 'checked="checked "':'';?>/> <label for="reg_save_me">Myself</label>
					<input type="radio" name="register_save" id="reg_save_ff" value="1" /> <label for="reg_save_ff">Friend/Family</label>
					<input type="radio" name="register_save" id="reg_save_ds" value="2" /> <label for="reg_save_ds">Don't save</label></li>

				<li><label for="register_race" class="req level-0">Race<?php echo $event->numRaces() > 1 ? 's':'';?>:</label>
					<select name="register_race" id="register_race">
						<?php
						if($event->numRaces() > 1)
							echo '<option value="" disabled="disabled">Please select one</option>';
						foreach($event->getRaces() as $race) {
							echo '<option value="'. $race->getId() .'">'. $race->getName() .' ($'.
								$race->getPriceByTimestamp() .')</option>';
						}
						?>
					</select></li>
			</ul>
			
			<h3 class="header">Participant's Information</h3>
			<ul>
				<li><label for="reg_fname" class="req level-0">First name:</label>
						<input type="text" name="register_fname" id="reg_fname" value="<?php echo $user->getFName(); ?>" />
					<label for="reg_lname" class="req level-0">Last name:</label>
						<input type="text" name="register_lname" id="reg_lname" value="<?php echo $user->getLName(); ?>" />
					<label for="reg_email" class="req level-0">Email:</label>
						<input type="text" name="register_email" id="reg_email" value="<?php echo $user->getContactEmail(); ?>" size="30" /></li>

				<li><label for="reg_addr" class="req level-0">Address:</label>
						<input type="text" name="register_addr" id="reg_addr" size="30" value="<?php echo $user->getAddr(); ?>" />
					<label for="reg_city" class="req level-0">City:</label>
						<input type="text" name="register_city" id="reg_city" value="<?php echo $user->getCity(); ?>" />
					<label for="reg_state" class="req level-0">State:</label>
						<select name="register_state" id="reg_state">
							<option value="" disabled="disabled">--</option>
							<?
							foreach(array_keys($util->getAllStates()) as $state) {
								if($state == $user->getState())
									$selected = ' selected="selected"';
								else
									$selected = '';

								echo '<option'. $selected .'>'. $state .'</option>';
							}
							?>
						</select></li>

				<li><label for="reg_addr2" class="level-0">Line 2:</label>
						<input type="text" name="register_addr2" id="reg_addr2" size="30" value="<?php echo $user->getAddr2(); ?>" />
					<label for="reg_zip" class="req level-0">Zip:</label>
						<input type="text" name="register_zip" id="reg_zip" size="5" value="<?php echo $user->getZip(); ?>" maxlength="5" /></li>

				<li><label for="reg_bday" class="req level-0">Birthday:</label>
						<input type="text" name="register_bday" id="reg_bday" value="<?php echo $user->getfBday() ?>" maxlength="10" size="10" /> (mm/dd/yyyy)
					<label for="reg_phone" class="req level-0">Phone:</label>
						(<input type="text" name="register_phone" id="reg_phone" value="<?php echo $user->getPhoneRange(0, 3); ?>" maxlength="3" size="5" />)
						<input type="text" name="register_phone2" id="reg_phone2" value="<?php echo $user->getPhoneRange(3, 3); ?>" maxlength="3" size="5" /> -
						<input type="text" name="register_phone3" id="reg_phone3" value="<?php echo $user->getPhoneRange(6, 4); ?>" maxlength="4" size="5" /></li>

				<li><span class="req level-0">Shirt size:</span></li>
				<li><span class="level-0">(Adult)</span>
					<?php
					if($event->numAdultShirts() == 0)
						echo 'None available';

					foreach($event->getAdultShirts() as $size) {
						$key = array_search($size, $adult_shirt_sizes);
						$checked = '';
						if($size == $user->getShirtSize() && $user->isAdultSize())
							$checked = ' checked="checked"';
						echo '<input type="radio" name="register_shirt" id="reg_shirt_a'. $size .'" value="a-'. $key .'"'. $checked .' /> ';
						echo '<label for="reg_shirt_a'. $size .'">'. strtoupper($size) .'</label>';
					}
					?></li>
				<li><span class="level-0">(Youth)</span>
					<?php
					if($event->numYouthShirts() == 0)
						echo 'None available';

					foreach($event->getYouthShirts() as $size) {
						$key = array_search($size, $youth_shirt_sizes);
						$checked = '';
						if($size == $user->getShirtSize() && !$user->isAdultSize())
							$checked = ' checked="checked"';
						echo '<input type="radio" name="register_shirt" id="reg_shirt_y'. $size .'" value="y-'. $key .'"'. $checked .' /> ';
						echo '<label for="reg_shirt_y'. $size .'">'. strtoupper($size) .'</label>';
					}
					?></li>
				<?php
				if($event->allowNoShirt()) {
					echo '<li><span class="level-0">(No shirt)</span> ';
					echo '<input type="radio" name="register_shirt" id="register_shirt" value="none" /> ';
					echo '<label for="register_shirt">'. $util->money($event->getNoShirtDiscount()) .
							' Discount</label></li>';
				}
				?>

				<li><span class="req level-0">Gender:</span>
					<input type="radio" name="register_gender" id="reg_gender_m"
					value="0"<?php echo $user->isMale() ? ' checked="checked"':'';?> />
						<label for="reg_gender_m">Male</label>
					<input type="radio" name="register_gender" id="reg_gender_f"
					value="1"<?php echo $user->isFemale() ? ' checked="checked"':'';?> />
						<label for="reg_gender_f">Female</label></li>

				<?php
				if($event->hasQuestions())
					echo '<li>Race director\'s questions for you:</li>';

				$idx = 0;
				foreach($event->getQuestions() as $q) {
					$idx++;
					echo '<li>'. $idx .'. <span class="req">'. array_shift($q) .'</span>';
					echo '<li class="indent-1">';

					$a_idx = 0;

					echo '<ul class="answers">';
					foreach($q as $a) {
						$a_idx++;
						echo '<li><input type="radio" name="register_q'. $idx .'" id="reg_q'. $idx .'a'. $a_idx .'" value="'. $a .'" /> ';
						echo '<label for="reg_q'. $idx .'a'. $a_idx .'">'. $a .'</label></li>';
					}
					echo '</ul>';

					echo '</li>';
				}
				?>

				<li><label for="reg_coupon">Coupon code:</label>
					<input type="text" id="reg_coupon" name="register_coupon" size="15" maxlength="30" />
					(Note: One coupon code per registrant.)</li>

				<li><input type="checkbox" name="register_agree" id="reg_agree" value="true" />
					<label for="reg_agree">I, the participant, have read and agree to the <a href="#show-terms" id="show-terms">event disclosure</a>.</label>
						I also ensure that the above participant is at least 18 years of age OR has the explicit permission of their parent/legal guardian to register for this event.
					<div class="no-show" id="register-terms" title="Event registration: Disclosure">
						<p>I know that running a road race is a potentially hazardous activity. I should not enter and run unless I
						am medically able and properly trained. I know that there may be traffic on the course route.  I assume all
						risks involved in participating in traffic. I also assume any and all other risks
						associated with participating in this event, including but not limited to falls, contact with other participants, the effects
						of the weather, including high heat and/or humidity, and the conditions of the roads, all such risks being known
						and appreciated by me knowing these facts, and in consideration of your accepting my entry fee, I hereby for myself,
						my heirs, executors, administrators and anyone else who might claim on my behalf, covenant not to sue and waive, release and
						discharge the race officials, volunteers and any and all sponsors including their agents, employees, assigns or
						anyone acting for or on their behalf, for any and all claims or liability for death, personal injury or property
						damage of any kind or nature whatsoever arising out of, or in the course of, my participation in this event.
						This release and waiver extends to all claims of every kind or nature whatsoever.</p>
						<p>THE RACE DIRECTOR RESERVES THE RIGHT TO REJECT ANY ENTRY.</p></div></li>
			</ul>
			<a class="reset-form" rel="register-form">Clear form</a>
			<input type="submit" name="register_submit" value="Submit this Registrant" />
			<a id="registrant-payment" class="button float-r">Finalize Registration</a>
		</form>

	<?php
	} else {
		echo '<p class="clear">Sorry! Online registration closes the day before the event at 5pm. '.
		'This event is scheduled for '. $event->getDate('M d, Y') .'</p>';
	}
} else
	echo '<p class="clear">Invalid event</p>';
?>
