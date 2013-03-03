<?php
if(!defined('ROOT'))
	die();

require_once ROOT .'classes/eventhandler.php';

$eventList = new EventHandler();
?>
<div id="event-content">
	<div class="three-month">
		<?php printCalendar($year, $month); ?>
	</div>
	<div class="clear quickmonth">
		<?php $eventList->printQuickLinks($year, $month); ?>
	</div>
	<div id="event-options">
		<a href="/events" id="current-month" class="button">Go to <?php echo date("M 'y"); ?></a>
		<a id="add-event-link" class="button">Add Event</a>
		<img src="/img/loadcircle.gif" class="no-show loader" id="event-loading" alt="Loading..." />
	</div>

	<div id="add-event-holder"><?php
		 if((int)$_GET['addevent'] > 0) { include ROOT .'include/requests/addEvent.php'; }
	?></div>

	<div class="event-container">
		<?php $eventList->printYearEvents($year, $month); ?>
	</div>
</div>