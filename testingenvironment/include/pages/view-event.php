<?php
if($event->hasAccess($user->getId())) {
	echo '<div class="nav-buttons">';
	echo '<a href="/edit-event/'. $event->getPermalink() .'" class="button">Edit event</a> ';
	echo '<a href="/registrants/'. $event->getPermalink() .'" class="button">';
	echo 'View registrants ('. $event->numPaidRegistrants() .')</a> ';
	echo '</div>';
}

if($e_status == ESTATUS_WAITING)
	echo '<div class="ui-state-highlight"><p class="alert">This event is still waiting for approval.</p></div>';
else if($e_status == ESTATUS_DENY)
	echo '<div class="ui-state-highlight"><p class="alert">This event has been denied approval.</p></div>';
?>

<div class="event-holder">
	<?
	if($event->hasBanner())
		echo '<div style="clear:both;text-align:center;padding:5px 0;"><img src="/uploads/image.php?image='.
				urlencode($event->getBannerPath()) .'&width='.
				BANNER_MAX_WIDTH .'&height='. BANNER_MAX_HEIGHT .'" /></div>';
		?>

	<p class="event-date" style="clear: both;">
		<?php echo $event->getDate('l, M jS'); ?> (<?php echo $event->getType(); ?>)
		<?php
		if($event->hasRaceResults())
			echo $event->raceResultsImage();
		?>
	</p>

	<p class="event-description"><?php echo $event->getfDesc(); ?></p>

	<?php
	if($event->hasContactInfo())
		echo '<p>Contact Info: '. $event->getContactInfo() .'</p>';

	if($event->hasShirts()) {
		echo '<p>Available shirt sizes: ';
		if($event->numYouthShirts() > 0)
				echo '<strong>Youth - </strong> ('. strtoupper(implode(', ', $event->getYouthShirts())) .') ';
		if($event->numAdultShirts() > 0)
				echo '<strong>Adult - </strong> ('. strtoupper(implode(', ', $event->getAdultShirts())) .')';
		echo '</p>';
	} else {
		echo '<p>No shirts available</p>';
	}

	if($event->hasNoShirtDiscount())
		echo '<p>No-shirt discount: '. $util->money($event->getNoShirtDiscount()) .'</p>';


	echo '<div class="event-races" style="font-size: .9em">';
	foreach($event->getRaces() as $race) {
		echo $race->formattedContent();
	}
	echo '</div>';

	if($event->hasRegOptions()) {
		echo '<p>Registration options:</p>';
		echo $event->regOptionsToString();
	}


	echo '<div class="event-buttons">';

	if($event->hasCourseMap())
		echo '<a href="'. $event->getCourseMapPath() .'" '.
			'class="course-map button" title="Course Map">View course map</a> ';

	if($event->hasEntryForm())
		echo '<a href="'. $event->getEntryFormPath() .'" target="_blank" class="button" '.
			'>Downloadable Entry Form</a> ';

	if($event->getStatus() == ESTATUS_OK) {
		if($event->registerHere() && !$event->hasPassed())
			echo '<a class="register-button button" href="/register/'. $event->getPermalink() .'">Register online</a> ';
	}
	echo '</div>';


	echo '<div id="share-link">Share: ';
	echo $event->getFacebookShareLink();
	echo ' <a href="'. $event->getPermalinkURL() .'" title="Event\'s page">';
	echo $event->getPermalinkURL() .'</a>';
	echo '</div>';
	
	?>
</div>