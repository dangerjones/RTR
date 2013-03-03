<?php
$_SERVER['DOCUMENT_ROOT'] = '/home/n62run5/public_html/testingenvironment';

require_once $_SERVER['DOCUMENT_ROOT'] .'/include/critical.php';
require_once ROOT .'classes/event.php';

remindRaceDirectorsToUploadRaceResults();


/*
 * Script functions below
 */

function remindRaceDirectorsToUploadRaceResults() {
	global $sql;
	$day		= date('j');
	$month		= date('n');
	$year		= date('Y');

	$query = 'SELECT id FROM '. TABLE_EVENTS .' WHERE month='. $month .' AND day='. $day .' AND year='. $year .' AND status='. ESTATUS_OK;

	$event_ids = $sql->getOneColumn($query);

	if(!empty($event_ids))
		sendRaceResultReminderEmail($event_ids);
}

function sendRaceResultReminderEmail($event_ids) {
	global $mail;

	echo '######### UPLOAD RACE RESULTS REMINDER #########' ."\r\n\r\n";

	foreach($event_ids as $id) {
		$event		= new Event($id);
		$name		= $event->getDecodedName();
		$race_dir	= new User($event->getUserId(), false);
		$to			= $race_dir->getContactEmail();
		$subject	= 'Upload your race results for "'. $name .'"';

		$message = 'Dear '. $race_dir->getAddressingIdentifier() .", \r\n".
				"\t". 'Your event, '. $name .', is scheduled to end today and we would like to remind '.
				'you that once the race has finished and the results are in, you may upload those results '.
				'to our website for everyone to see.'. "\r\n\r\n".
				'In order to do this, please go to your event\'s edit page: '. BASEURL .'edit-event/'. $event->getPermalink() ."\r\n\r\n".
				'From there, you may upload your files with the File Upload form near the bottom of the page '.
				'(race results can only be in PDF format). When you have completed uploading all your files, '.
				'you can assign a file to a race and give it a name. Make sure you save the results.'. "\r\n\r\n".
				'If you have any questions, please don\'t hesitate to ask us!'. "\r\n\r\n".
				'Thank you!';

		if($mail->sendAdmin($to, $subject, $message))
			echo "Success: ". $race_dir->getIdentifier() ." $to -> $name (#". $event->getId() .") \r\n";
		else
			echo "Error: $to \r\n";
	}
}
?>