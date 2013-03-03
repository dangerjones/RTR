<?php
require_once ROOT .'classes/registrantstatistics.php';

$regs = $event->getPaidRegistrants();
$stats = new RegistrantStatistics($event->getId());
$stats->addRegistrant($regs);
$num_regs = $stats->getTotalRegistrants();

usort($regs, array('Registrant', 'cmp_byLName'));
?>
<div class="nav-buttons">
	<a href="/events/<?php echo $event->getPermalink() ?>" class="button">View event</a>
	<a href="/edit-event/<?php echo $event->getPermalink(); ?>" class="button">Edit event</a>
</div>


<h3 class="header">Currently registered (Total: <?php echo $num_regs; ?>)</h3>
<?php
if($num_regs == 0) {
	echo '<p class="indent-1">This event does not have any registrants.</p>';
	if(!$event->registerHere())
		echo '<p class="indent-1">Also, online registration is currently disabled for this event.';
} else {
	?>
	<div>
		Registrants per page:
		<select id="view-reg-num">
			<option value="9000000">all</option>
			<option value="5">5</option>
			<option value="10">10</option>
			<option value="15">15</option>
			<option value="20">20</option>
			<option value="25">25</option>
			<option value="30">30</option>
			<option value="40">40</option>
			<option value="50">50</option>
			<option value="75">75</option>
			<option value="100">100</option>
		</select>
		<span class="indent-1">Go to page:</span>
		<img src="/img/prev-icon.png" alt="Previous" title="Previous page" class="view-reg-change-page point" />
		<select id="view-reg-page">
			<option value="1">1</option>
		</select>
		<img src="/img/next-icon.png" alt="Next" title="Next page" class="view-reg-change-page point" />
		<span class="indent-1"><a href="/spreadsheet.php?e=<?php echo $event->getId(); ?>" class="button">Export spreadsheet</a></span>
	</div>
	<div id="registrants-wrap">
		<h3 class="caption"><a><span class="registrant-title">Name</span><span class="registrant-title">Race</span><span>Sex/Age</span><span>Paid</span><span>Date</span></a></h3>

	<?php
	
	User::cacheRegistrantsUsers($event->getId());
	foreach($regs as $reg) {
		$registered_by = $reg->getUser();
		$used_coupon = $reg->getCoupon();

		echo '<h3 class="accordion-header"><a>';
		echo	'<span class="registrant-title">'. $reg->getLName() .', '. $reg->getFName() .'</span>';
		echo	'<span class="registrant-title registered-race-name">'. $reg->getRace()->getName() .'</span>';
		echo	'<span>'. strtoupper($reg->getGender(true)) .'/'. ($reg->getAge() < 1 ? '< 1':$reg->getAge()) .'</span>';
		echo	'<span>'. $util->money($reg->getTotalPaid()) .'</span>';
		echo	'<span>'. $reg->getPaidDate('d M Y') .'</span>';
		echo '</a></h3>';


		echo '<div>';
		echo '<ul>';
		echo	'<li><span class="req">Registered by:</span> ';
		echo			$registered_by->hasPersonalInfo() ? $registered_by->getFullName() .' - ':'';
		echo			$registered_by->getEmail() .'</li>';
		echo	'<li><span class="req">Registration completed on:</span> '. $reg->getPaidDate('M jS, h:i a') .'</li>';
		echo	'<li><span class="req">Total paid:</span> '. $util->money($reg->getTotalPaid());
		echo		'<span class="req indent-1">Director receives:</span> '. $util->money($reg->getPaid());
		echo		'<span class="req indent-1">Fee:</span> '. $util->money($reg->getPaidFee());
		echo		'<span class="req indent-1">Director paid fee?</span> '. ($reg->directorPaid() ? 'Yes':'No') .'</li>';
		echo	'<li><span class="req">Total discounts:</span> '. $util->money($reg->getDiscounts());
		echo		'<span class="req indent-1">Used coupon:</span> ';
		echo			(empty($used_coupon) ? 'None':$used_coupon);
		echo		'<span class="req indent-1">No-shirt discount:</span> ';
		echo			($reg->hasShirt() ? 'None':$util->money($reg->getDiscounts()-
						(empty($used_coupon) ? 0:$used_coupon->getAmount())));
		echo	'</li>';


		echo	'<li><h3 class="header">'. $reg->getFullName() .'\'s Information</h3></li>';
		echo	'<li><span class="req indent-1">Contact email:</span> '. $reg->getEmail();
		echo		'<span class="req indent-1">Phone:</span> '. $reg->getfPhone() .'</li>';
		echo	'<li><span class="req indent-1">Sex:</span> '. $reg->getGender();
		echo		'<span class="req indent-1">Birthday:</span> '. $reg->getfBirthday('d M Y') .' ('. $reg->getAge() .'yrs)';
		echo		'<span class="req indent-1">Shirt size:</span> '. ($reg->hasShirt() ? (strtoupper($reg->getShirtSize()).
						' '. ($reg->isAdultSize() ? '(Adult)':'(Youth)')):'None') .'</li>';
		echo	'<li class="req indent-1">Address:</li>';
		echo	'<li class="indent-2">'. $reg->getAddr() .'<br />';
		echo		$reg->hasAddr2() ? $reg->getAddr2() .'<br />':'';
		echo		$reg->getCity() .', '. $reg->getState() .' '. $reg->getZip() .'</li>';

		if($reg->hasAnswers()) {
			$q = $reg->getQuestions();
			$a = $reg->getAnswers();

			for($i = 0; $i < count($q); $i++) {
				echo '<li class="indent-1"><span class="req">'. $q[$i] .'</span> '. $a[$i] .'</li>';
			}

		}
		echo '</ul>';
		echo '</div>';
	}

	?>
	</div><?php
}

$stats->setMoneyFilter(true);
if($num_regs > 0) {
	?>
	<h3 class="header">Event Statistics</h3>
	<ul class="stat-list">
		<li><span class="req">Total registrants: <?php echo $stats->getTotalRegistrants(); ?></span></li>
		<li class="indent-1"><span class="raeq">Male:</span> <?php echo $stats->getTotalMales(); ?>
			<span class="percent">(<?php echo $stats->getMalePercentage(); ?>%)</span></li>
		<li class="indent-1"><span class="raeq">Female:</span> <?php echo $stats->getTotalFemales(); ?>
			<span class="percent">(<?php echo $stats->getFemalePercentage(); ?>%)</span></li>
		<li class="indent-1">Peak registration day: 
			<?php $num = $stats->getPeakDaysRegCount(); echo implode(', ', $stats->getPeakDays('M jS')) .
			' <span class="percent">('. $num .'; '. $stats->getRegistrantPercentage($num) .'%)</span>'; ?></li>
		<li class="indent-1">Peak registration hour:
			<?php $num = $stats->getPeakHoursRegCount(); echo implode(', ', $stats->getPeakHours('ga')) .
			' <span class="percent">('. $num .'; '. $stats->getRegistrantPercentage($num) .'%)</span>'; ?></li>
		<li class="indent-1">Average daily registrations: <?php echo $stats->getAverageRegsPerDay(); ?></li>
		<li class="indent-1">First registration: <?php echo $stats->getEarliest('M jS, h:i a'); ?></li>
		<li class="indent-1">Last registration: <?php echo $stats->getLatest('M jS, g:i a'); ?>
			<span class="percent">(<?php echo $stats->getRegIntervalDays(); ?> day interval)</span></li>

		<li><span class="req">Total money collected: <?php echo $stats->getTotalMoneyCollected(); ?></span></li>
		<li class="indent-1">Race director receives: <?php echo $stats->getDirectorsMoney(); ?></li>
		<li class="indent-1">Total discounts: <?php echo $stats->getTotalDiscounts(); ?></li>
		<li class="indent-1">Total fees: <?php echo $stats->getTotalFees(); ?></li>
		<li class="indent-2">Director: <?php echo $stats->getTotalDirectorPaid(); ?></li>
		<li class="indent-2">Registrants: <?php echo $stats->getTotalRegistrantsPaidFee(); ?></li>
		<li class="indent-1">Average payment: <?php echo $stats->getAveragePayment(); ?></li>
	</ul>
	<ul class="stat-list">
		<li><span class="req">Race breakdown</span></li>
		<?php
		foreach($stats->getRaces() as $r_id => $count) {
			$race = race::getRace($r_id);
			$percent = $stats->getRegistrantPercentage($count);
			echo '<li class="indent-1">'. $race->getName() .': '. $count .' racers ';
			echo '<span class="percent">('. $percent .'%)</span></li>';
		}

		$under	= $stats->getAgeRangeCount(null, 11);
		$first	= $stats->getAgeRangeCount(12, 18);
		$second = $stats->getAgeRangeCount(19, 30);
		$third	= $stats->getAgeRangeCount(31, 55);
		$over	= $stats->getAgeRangeCount(56);
		?>
		<li class="req">Age breakdown</li>
		<li class="indent-1"><span class="req">under 12:</span>
			<?php echo $under .' <span class="percent">('. $stats->getRegistrantPercentage($under) .'%)</span>'; ?>
			<span class="req indent-1">12-18:</span>
			<?php echo $first .' <span class="percent">('. $stats->getRegistrantPercentage($first) .'%)</span>'; ?></li>
		<li class="indent-1"><span class="req">19-30:</span>
			<?php echo $second .' <span class="percent">('. $stats->getRegistrantPercentage($second) .'%)</span>'; ?>
			<span class="req indent-1">31-55:</span>
			<?php echo $third .' <span class="percent">('. $stats->getRegistrantPercentage($third) .'%)</span>'; ?>
		<li class="indent-1">
			<span class="req">over 55:</span>
			<?php echo $over .' <span class="percent">('. $stats->getRegistrantPercentage($over) .'%)</span>'; ?></li>
		<li class="indent-1">Youngest: <?php echo $stats->getYoungest(); ?> yrs
			<span class="indent-1">Oldest:</span> <?php echo $stats->getOldest(); ?> yrs</li>
		<li class="indent-1">Average: <?php echo $stats->getAgeAverage(); ?> yrs
			<span class="indent-1">Median:</span>
			<?php echo $stats->hasAgeMedian() ? $stats->getAgeMedian() .' yrs':'None'; ?>
		<li class="req">Shirt breakdown</li>
		<?php
		$stats->sortShirts();
		$youth = $stats->getYouthShirts();
		$adult = $stats->getAdultShirts();

		echo '<li class="indent-1">(Youth) ';
		foreach($youth as $size => $num) {
			echo '<span class="req">'. strtoupper($size) .':</span> '. $num .
				' <span class="percent">('. $stats->getRegistrantPercentage($num) .'%)</span> ';
		}
		$y_total = $stats->getYouthShirtTotal();
		echo '<span class="percent">[Total: '. $y_total .'; '. $stats->getRegistrantPercentage($y_total) .'%]</span>';
		echo '</li>';

		$i = 0;
		echo '<li class="indent-1">(Adult) ';
		foreach($adult as $size => $num) {
			echo '<span class="req">'. strtoupper($size) .':</span> '. $num .
				' <span class="percent">('. $stats->getRegistrantPercentage($num) .'%)</span> ';
			if(++$i == 3)
				echo '</li><li class="indent-1">';
		}
		$a_total = $stats->getAdultShirtTotal();
		echo '<span class="percent">[Total: '. $a_total .'; '. $stats->getRegistrantPercentage($a_total) .'%]</span>';
		echo '</li>';
		?>
		<li class="indent-1">No-shirt registrants: <?php $noshirt = $stats->getNoShirtTotal();
			echo $noshirt .' <span class="percent">('. $stats->getRegistrantPercentage($noshirt) .'%)</span>'; ?></li>
		<li class="indent-2">Shirt total: 
			<?php
			$shirt_t = $stats->getShirtTotal();
			echo $shirt_t .' <span class="percent">('. $stats->getRegistrantPercentage($shirt_t) .'%)</span>'; ?></li>
	</ul>
	<h4></h4>
	<?php
}