<?php
if(!isset($event)) { // when not included
	require_once '../../critical.php';
	require_once ROOT .'classes/event.php';

	$e_id = (int)$_POST['e_id'];
	if(!$util->eventExists($e_id))
		die('Invalid event');

	$event = new Event($e_id);
	if(!$event->hasAccess($user->getId()))
		die('Permissions denied');
}

?>
<ul>
	<?php
	foreach($event->getCoupons() as $c) {
		if($c->isDisabled()) {
			$class = 'enable';
			$icon = 'green-plus';
			$disabled = ' <em>(Disabled)</em>';
		}
		else if($event->anyRegistrantHasCoupon($c->getId())) {
			$class = 'disable';
			$icon = 'red-minus';
			$disabled = '';
		}
		else {
			$class = 'remove';
			$icon = 'delete';
			$disabled = '';
		}

		echo '<li id="coupon-id-'. $c->getId() .'">';
		echo '<a class="'. $class .'-coupon" rel="'. $c->getId() .'">';
		echo '<img src="/img/'. $icon .'-icon.png" alt="'. ucfirst($class) .' coupon"';
		echo ' title="'. ucfirst($class) .' this coupon" class="point" /></a> ';
		echo '<span class="'. $class .'-coupon-name">';
		echo $c->getName() .': '. $util->money($c->getAmount()) .'</span>'. $disabled .'</li>';
	}

	if($event->numCoupons() == 0)
		echo '<strong>This event has no coupons. Click "New coupon" below to make one.</strong>';
	?>
</ul>