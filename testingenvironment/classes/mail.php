<?php
/**
 * Sends and handles all mail
 *
 * @author Sterling
 */
class Mail {
	private $footer;

	function  __construct() {
		$this->footer = '';
	}

	function eventRegistrationDone($to, $payment, $reg = null) {
		global $user, $util;
		if($reg == null) {
			$subject = 'Event registration complete!';
			$message = 'Dear '. $user->getAddressingIdentifier() .", \r\n". 
				"\t". 'Thank you for your recent payment of '. $util->money($payment->getAmt()) .
				' on '. BASEURL .'. It has been succesfully processed, and your event '.
				'registration is complete!'. "\r\n\r\n".
				"\t". 'TRANSACTION ID: '. "\t\t\t". $payment->getTransaction() ."\r\n".
				"\t". 'DATE OF PAYMENT: '. "\t\t". $payment->getDateTime() ."\r\n".
				"\t". 'REGISTRANTS: '. "\t\t\t". implode(', ', $payment->getRegistrants()) ."\r\n\r\n".

				'You can view your receipt here: '. BASEURL .'payment-receipt.php?id='. $payment->getId();
		} else {
			$event = $reg->getEvent();
			$e_name = $event->getDecodedName();
			$race = $reg->getRace();

			$subject = 'You were registered for "'. $e_name .'"';
			$message = 'Dear '. $reg->getAddressingIdentifier() .", \r\n".
				"\t". 'You are receiving this email because '. $user->getIdentifier() .
				' has registered you through our website, '. BASEURL .', for the "'.
				$e_name .'" ['. $race->getDecodedName() .']. This event is scheduled for '. $event->getDate('M j, Y') .
				' at '. date('g:i a', strtotime($race->getStartTime())) ."\r\n\r\n".
				'If you believe this is a mistake, please contact us immediately.' ."\r\n\r\n".
				'Thank you!';
		}
		
		$this->sendAdmin($to, $subject, $message);
	}


	function notifyStatusChange($e_id, $status) {

		$event = new Event($e_id);
		$to_user = new User($event->getUserId(), false);
		$s = ($status == ESTATUS_OK ? 'approved!':'denied.');

		$subject = 'Your event has been '. $s;
		$message = 'Dear '. $to_user->getFName() .', '. "\r\n".
				'Your event "'. $event->getName() .'"';

		$ok =
				' has been approved and is online on our website, '. BASEURL .'. '.
				'The following is a direct link to your event\'s page: '. "\r\n\r\n".
				$event->getPermalinkURL();
		$denied =
				' has been denied from submission on '. BASEURL .'. '.
				'If you have  any questions about this, please contact us.';

		if($status == ESTATUS_OK)
			$message .= $ok;
		else
			$message .= $denied;

		$message .= "\r\n\r\n".
				'Thank you!';

		$this->sendAdmin($to_user->getEmail(), $subject, $message);

	}



	/*
	 * Send mail from the admin email (defined as a constant)
	 */
	function sendAdmin($to, $subject, $message) {
		return $this->send($to, '"'. COMPANY_NAME .'" <'.ADMIN_EMAIL.'>', $subject, $message);
	}

	function send($to, $from, $subject, $message) {
		$headers = "From: $from\r\nReply-To: $from";
		return @mail($to, $subject, $message.$this->footer, $headers);
	}
}
?>
