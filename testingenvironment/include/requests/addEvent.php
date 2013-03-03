<?php
require_once  '../critical.php';

if(!$user->loggedIn() || !$util->isAjax()) {
	include ROOT .'include/unauthorized.php';
	die();
}
?>
<a name="error"></a>
<div class="form-errors no-show"></div>
<div id="add-event">
    <form action="/include/requests/addEventProcess.php" method="post" id="event-form">
        <ul>
            <li><h3>Event:</h3></li>
            <li><span class="req">Name:</span><input type="text" name="event-name" maxlength="100" size="40" /></li>
            <li><span class="req">Type:</span><select name="event-type">
                <?php
                foreach($event_types as $val => $name) {
                    echo '<option value="'. $val .'">'. $name .'</option>';
                }
                ?>
                </select></li>
            <li><span class="req">Date:</span><input type="text" name="event-date" maxlength="10" size="10" /> (mm/dd/yyyy)</li>
            <li><span class="req no-level">Description:</span></li>
            <li class="level-1"><textarea name="event-description" rows="7" cols="40"></textarea></li>
			<li><span class="req">Permalink:
				<img src="/img/question-icon.png" class="point what-is-permalink" alt="More info" title="What's a permalink?" /></span><input type="text" name="event-permalink" maxlength="255" size="40" /></li>
			<li class="level-2"><a id="get-permalink-suggestions">Get permalink suggestions</a></li>
            <li><span>Contact Info:</span><input type="text" name="event-contact" maxlength="100" size="40" /></li>
            <li>Shirts available? <input type="radio" name="event-shirts-available" value="true" /> Yes
                <input type="radio" name="event-shirts-available" value="false" checked="checked" /> No</li>
            <?php
            // Output available shirt sizes (youth and adult)
            echo '<li class="no-level toggle-shirts"><span>Youth:</span>';
            foreach($youth_shirt_sizes as $val => $name) {
                echo '<input type="checkbox" name="event-youth-shirt[]"'.
                        'value="'. $val .'" /> '. strtoupper($name) .' ';
            }
            echo '</li>';

            echo '<li class="no-level toggle-shirts"><span>Adult:</span>';
            foreach($adult_shirt_sizes as $val => $name) {
                echo '<input type="checkbox" name="event-adult-shirt[]"'.
                        'value="'. $val .'" /> '. strtoupper($name) .' ';
            }
            echo '</li>';
            ?>
            <li class="level-1 toggle-shirts">Allow a "No Shirt" option for registrants?<br />
				(only for online registrations)
                <input type="radio" name="event-no-shirt" value="true" /> Yes
                <input type="radio" name="event-no-shirt" value="false" checked="checked" /> No</li>

            <li class="level-2 toggle-shirt-discount">If any, "no shirt" discount:
                $<input type="text" name="event-no-shirt-discount" value="0.00" size="3" maxlength="10" /></li>

            <li>Registration methods:</li>

            <li class="level-1"><input type="checkbox" name="event-reg-here" value="true" />
				Enable online registration through this website (service fees may apply)</li>

			<li class="level-2 no-show"><input type="checkbox" name="event-reg-fee" value="true" checked="checked" />
				Registrants pay service fee
				<img id="service-fee-question" src="/img/question-icon.png" class="question" alt="More info" title="More info..." /></li>

            <li class="level-1"><span class="level-1">
                <input type="checkbox" name="event-registration-methods[]" value="<?php echo EREG_METHOD_WEB; ?>" />
                    Website</span><input id="toggle-other-web" type="text" name="event-other-web"
                                            value="http://" maxlength="255" size="25" /></li>

            <li class="level-1"><span class="level-1">
                <input type="checkbox" name="event-registration-methods[]" value="<?php echo EREG_METHOD_ADDR1; ?>" />
                    Address 1</span><input id="toggle-addr1" type="text" name="event-addr1" maxlength="100" size="25" /></li>

            <li class="level-1"><span class="level-1">
                <input type="checkbox" name="event-registration-methods[]" value="<?php echo EREG_METHOD_ADDR2; ?>" />
                    Address 2</span><input id="toggle-addr2" type="text" name="event-addr2" maxlength="100" size="25" /></li>

			<li>Promo discount coupons:</li>

			<li class="level-1 promo-codes">Codename:
				<input type="text" name="event-registration-coupons[]" size="10" maxlength="30" />
				Discount:
				$<input type="text" name="event-registration-coupon-amt[]" size="5" maxlength="10" /></li>

			<li class="level-3"><a class="button" id="add-registration-coupon">More coupons</a>
				<a class="button" id="remove-registration-coupon">Less</a></li>

            <li>Add custom registration questions? <input type="radio" name="event-custom-q" value="true" /> Yes
                <input type="radio" name="event-custom-q" value="false" checked="checked" /> No
				<?php
				include ROOT .'include/requests/html/add_question.php';
				?>
            </li>
			<li class="q-buttons"><a id="remove-question" class="button">Delete a question</a>
				<a id="add-question" class="button">Add a question</a></li>
        </ul>
		<?php
		include ROOT .'include/requests/html/add_race.php';
		?>
		<div id="add-race-holder">
			<a id="add-race" class="button">Add a race</a>
		</div>

		<input type="hidden" name="event-banner" />
		<input type="hidden" name="event-entry-form" />
		<input type="hidden" name="event-course-map" />

		<input type="submit" value="Save Event" class="no-show" />
    </form>

		<div id="uploads-holder">
			<h3>File uploads:</h3>
			<input type="file" name="event-banner" id="event-banner" />
			<input type="file" name="event-entry-form" id="event-entry-form" />
			<input type="file" name="event-course-map" id="event-course-map" />
		</div>


		<div id="submit-wrap">
			<img class="invisible" src="/img/loaderbar.gif" alt="Loading..." /><br />
			<a class="button close-dialogBox">Cancel</a>
			<input type="submit" value="Save Event" class="submit" />
		</div>

</div>

<div id="service-fee-information" class="no-show" title="Service Fees">
	<p>
		When users register online through our website, there is a small service fee involved.
		You can choose to either pay for this service fee yourself per registration or you may
		pass this service fee onto those who register. By paying this fee yourself, the service
		fee we charge will be deducted from the total amount you receive after a registration.
		The user will never see a service fee charge for your race. By passing this fee onto
		registrants, the service fee will be added on top of the race pricing. For example:
	</p>
	<p>
		<strong>Race cost = $15.00</strong>
	</p>
	<div class="ui-state-highlight">
		<p><strong>When race director pays service fee:</strong></p>
		<div class="indent-1">
			Registrant total = <strong>$15.00</strong><br />
			Director receives <strong>$14.00</strong> per registrant ($15.00 - $1.00 fee)
		</div>
		<p><strong>When registrant pays service fee:</strong></p>
		<div class="indent-1">
			Registrant total = <strong>$16.00</strong> ($15.00 + $1.00 fee)<br />
			Director receives <strong>$15.00</strong> per registrant
		</div>
	</div>
	<p>
		Note: Races with a $0 cost will not incur service fees for the race director or registrant.
	</p>
</div>

<div id="permalink-information" class="no-show" title="What is a permalink?">
	<p>
		Permalink is short for <strong>permanent link</strong>. This is an unchanging,
		human-readable link that you can give out to your friends so they can see your
		event and register for it. We allow you to choose your event's permalink as long as
		it is relevant to your event and appropriate. Many links on the internet are
		hard to read and understand like this:
	</p>
	<p class="indent-1 req smaller">http://www.runthatrace.com/index.php?e=38288305&amp;name=joe%20pete&amp;email=j-p%40mail.com#id=3975752839</p>
	<p>
		Permalinks allow you to create readable, user-friendly links like the following:
	</p>
	<p class="indent-1 req smaller">http://www.runthatrace.com/events/2011/5k-family-fun-run</p>
	<p>
		Permalinks will always begin with "http://www.runthatrace.com/events/" then it
		will be followed by the event's year. For example, if the event is scheduled
		for June 2011, the next part of the link will be
		"http://www.runthatrace.com/events/<strong>2011</strong>/". Then you get to decide
		what the final portion is:
	</p>
	<p class="indent-1 req smaller">http://www.runthatrace.com/events/2011/<em>your-permalink-goes-here</em></p>
	<p>
		Remember that only alphanumeric characters and hyphens (-) are allowed in permalinks,
		but it may not begin or end with a hyphen. Multiple hyphens in a row (---) is also not allowed.
	</p>
</div>
