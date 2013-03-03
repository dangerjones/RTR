<?php
if(defined('ROOT'))
	require_once ROOT .'include/critical.php';
else
	require_once '../critical.php';
require_once ROOT .'classes/event.php';

if(!$user->loggedIn())
	die('Unauthorized');

$e_id = $event_id == null ? (int)$_POST['e']:$event_id;

if(!isset($event))
	$event = EventHandler::getEvent($e_id);

if(!is_object($event))
	die('Invalid event '.$e_id);


$hasRegs = $event->hasRegistrants($paid_type, $user->getId());

echo '<p><strong>Currently registering:</strong> ';

if(!$hasRegs)
	echo 'None</p>';
else {
	echo '</p>';

	$paid_type = 'all';

	$races = $event->getRacesWithRegs($paid_type, $user->getId());

	echo '<ul>';
	foreach($races as $r) {
		$regs = $event->getRegistrantsByRace($r->getId(), $paid_type, $user->getId());
		$idx = 0;
		$numReg = count($regs);

		echo '<li><strong>['. $r->getName() .']</strong> ';
		foreach($regs as $reg) {
			echo '<span>'. $reg->getFullName(). '</span>';
			
			if(!$reg->hasPaid() && $reg->getDiscounts() > 0)
				echo ' <img src="/img/discount-icon.png" alt="Discounts" title="Discounts: '.
					$util->money($reg->getDiscounts()) .'" />';

			if(!$reg->hasPaid())
				echo ' <a class="delete-reg" rel="'. $reg->getId() .
				'" title="Remove '. $reg->getFullName() .'\'s registration">'.
				'<img src="/img/delete-user.png" alt="Remove" /></a>';
			else if($reg->hasPaid())
				echo ' <img src="/img/check-icon.png" alt="Payment complete" title="Payment complete" />';
			else
				echo ' <img src="/img/alert-icon.png" alt="Partial payment" title="Partial payment" />';

			if(++$idx < $numReg)
				echo ', ';
			else
				echo ' ';
		}
		echo '</li>';
	}
}
?>
