<?php
require_once '../critical.php';

$event_ids = $user->getUnpaidEvents(true);

if(empty($event_ids))
	die('<h3>You have no outstanding payments to make on event registrations.</h3>');
?>
<div id="event-payment-form">
	<form action="/include/requests/do-event-payment.php" method="post">
		<?php
		$grand_total = 0;
		foreach($event_ids as $i) {
			$event = new Event($i);
			$paid_type = 'unpaid';

			foreach($event->getRacesWithRegs($paid_type, $user->getId(), true) as $race) {
				echo '<h3>'. $event->getName() .': '. $race->getName() .'</h3>';
				echo '<table class="ui-state-highlight">';
				echo '<tr><th>Name</th><th>Race cost</th>';
				if(!$event->directorPays())
					echo '<th>Fee</th>';
				echo '<th>Subtotal</th></tr>';
				foreach($event->getRegistrantsByRace($race->getId(), $paid_type, $user->getId(), true) as $reg) {
					$due = $reg->getRace()->getPriceByTimestamp();
					$fee = $reg->getFeeDue();
					$reg_total = $reg->getTotalDue();
					$grand_total += $reg_total;
					
					echo '<tr>';
					echo '<td class="first"><input type="checkbox" class="payment-reg-id" name="pay_reg_id[]" '.
							'value="'. $reg->getId() .'" id="reg-id-'. $reg->getId() .'" checked="checked" />'.
							'<label for="reg-id-'. $reg->getId() .'">'. $reg->getFullName() .'</label></td>';

					echo '<td><div>'. $util->money($due);

					if(!$reg->hasShirt() && $reg->getNoShirtDiscount() > 0)
						echo '<br />(No shirt) -'. $util->money($reg->getNoShirtDiscount());
					if($reg->hasCoupon()) {
						$coupon = $reg->getCoupon();
						echo '<br />('. $coupon->getName() .') -'. $util->money($coupon->getAmount());
					}
					if($reg->getDiscounts() > 0)
						echo '<div class="total-after-discounts">'. $util->money($reg->getSubtotal()) .'</div>';
					
					echo  '</div></td>';

					if(!$event->directorPays())
						echo '<td>'. $util->money($fee) .'</td>';
					echo '<td class="reg-subtotal">'. $util->money($reg_total) .'</td>';
					echo '</tr>';
				}
				echo '</table>';
			}
		}

		echo '<h3 id="payment-grand-total">Total: <span class="ui-corner-all">'. $util->money($grand_total) .'</span></h3>';
		echo '<input type="hidden" name="pay_grand_total" value="'. $grand_total .'" />';

		echo '<div class="form-errors"></div>';
		
		if($grand_total > 0) {
			?>
			<div id="payment-creditcard-form">
				<h3>Credit card payment</h3>
				<ul>
					<li><label for="pay-fname" class="req level-0">First name:</label>
						<input type="text" id="pay-fname" name="pay_fname" value="<?php echo $user->getFName(); ?>" maxlength="100" />
						<label for="pay-lname" class="req level-0">Last name:</label>
						<input type="text" id="pay-lname" name="pay_lname" value="<?php echo $user->getLName(); ?>" maxlength="100" /></li>
					<li><label for="pay-card-number" class="req level-0">Card #:</label>
						<input type="text" id="pay-card-number" name="pay_card_number" value="<?php echo (TESTING ? '4371721797044777':''); ?>" maxlength="20" />
						<label for="pay-card-type" class="req level-0">Type:</label>
						<select id="pay-card-type" name="pay_card_type">
							<option>Visa</option>
							<option>MasterCard</option>
							<option>Discover</option>
							<option>Amex</option>
						</select></li>
					<li><label for="pay-card-expiration" class="req level-0">Expires:</label>
						<select id="pay-card-expiration" name="pay_exp_m"><?php
							for($i = 1; $i < 13; $i++) {
								echo '<option>'. sprintf('%02d', $i) .'</option>';
							}
							?>
						</select>
						<select name="pay_exp_y">
							<?php
							$year = date('Y');
							$added_years = 15;
							for($limit = $year+$added_years; $year < $limit; $year++) {
								if($year == $limit-$added_years+1)
									$selected = ' selected="selected"';
								else
									$selected = '';

								echo '<option'. $selected .'>'. $year .'</option>';
							}
							?>
						</select>
						<label for="pay-cvv2" class="req level-0">CVV2
						<img id="what-cvv2" class="question" src="/img/question-icon.png" alt="What is CVV2?" title="What is CVV2?" />:</label>
						<input type="text" id="pay-cvv2" name="pay_cvv2" size="5" maxlength="4" /></li>
					<li>Billing information</li>
					<li><label for="pay-address" class="req level-0">Address:</label>
						<input type="text" id="pay-address" name="pay_address" value="<?php echo $user->getAddr(); ?>" maxlength="100" />
						<label for="pay-city" class="req level-0">City:</label>
						<input type="text" id="pay-city" name="pay_city" value="<?php echo $user->getCity(); ?>" maxlength="100" /></li>
					<li><label for="pay-address2" class="level-0">Line 2:</label>
						<input type="text" id="pay-address2" name="pay_address2" value="<?php echo $user->getAddr2(); ?>" maxlength="100" />
						<label for="pay-state" class="req level-0">State:</label>
						<select id="pay-state" name="pay_state">
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
						</select>
						<label for="pay-zip" class="req">Zip code:</label>
						<input type="text" id="pay-zip" name="pay_zip" value="<?php echo $user->getZip(); ?>" size="5" maxlength="5" /></li>
				</ul>
			</div>
			<?
		}
		?>
		<ul>
			<li id="pay-loader"><img src="/img/loaderbar.gif" alt="Loading..." class="invisible" /></li>
		</ul>
		<input type="submit" value="submit" class="no-show" />
	</form>
</div>
<div id="cvv2-explanation" class="no-show">
	<p>The CVV2 is a numerical code used as an added security measure for credit cards.</p>
	<p><img src="/img/cvv2-back.png" alt="CVV2" />
		If you use Visa, MasterCard, or Discover, the security code can be found
		on the <strong>back</strong> of your card. It is a 3-digit number,
		typically separate and to the right of the signature strip.
	</p>
	<p><img src="/img/cvv2-amex.png" alt="CVV2" />
		If you use American Express, the security code is located on the
		<strong>front</strong> of your card. It is a 4-digit printed group of
		numbers towards the right hand side of your card.
	</p>
</div>