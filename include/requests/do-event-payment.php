<?php
require_once '../critical.php';
require_once ROOT .'classes/registrant.php';

if(!$util->isAjax() || !$user->loggedIn())
	die($util->errorFormat('Please login'));

$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_ENCODED);
$errors = array();

errorCheck($_POST);

if(hasErrors())
	die($util->errorFormat(getErrors()));

/* Paypal API variables */
$environment = (TESTING ? 'sandbox':'live');	// or 'beta-sandbox' or 'live'
// Set request-specific fields.
$paymentType =			urlencode('Sale');				// or 'Authorization'
$firstName =			$post['pay_fname'];
$lastName =				$post['pay_lname'];
$creditCardType =		$post['pay_card_type'];
$creditCardNumber =		$post['pay_card_number'];		// urlencode('4371721797044777');
$expDateMonth =			$post['pay_exp_m'];				// Must be padded with leading 0
$expDateYear =			$post['pay_exp_y'];
$cvv2Number =			$post['pay_cvv2'];
$address1 =				$post['pay_address'];
$address2 =				$post['pay_address2'];
$city =					$post['pay_city'];
$state =				$post['pay_state'];
$zip =					$post['pay_zip'];
$country =				urlencode('US');				// US or other valid country code
$amount =				$post['pay_grand_total'];
$currencyID =			urlencode('USD');				// or ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')

// Add request-specific fields to the request string.
$nvpStr =	"&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber".
			"&EXPDATE=$expDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName".
			"&STREET=$address1&STREET2=$address2&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country&CURRENCYCODE=$currencyID";

// Execute the API operation; see the PPHttpPost function above.
// If payment is $0, skip to next step as if it was successful
$no_charge = (int)$amount === 0;
$httpParsedResponseAr = $no_charge ? array('ACK'=>'SUCCESS'):PPHttpPost('DoDirectPayment', $nvpStr);

if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) ||
		"SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {

	if($no_charge) {
		$amount = 0;
		$transaction_id = 'FREE-'. strtoupper(substr(md5(mt_rand()), 0, 20));
	} else {
		$amount = urldecode($httpParsedResponseAr['AMT']);
		$transaction_id = urldecode($httpParsedResponseAr['TRANSACTIONID']);
	}

	onSuccess($post, $amount, $transaction_id);

	if(hasErrors()) {
		$critical = 'Critical error! Your payment was received, but there was a problem '.
			'processing your transaction. Please contact an administrator immediately. PLEASE '.
			'WRITE DOWN THE FOLLOWING TRANSACTION ID: <strong>'. $transaction_id .
			'</strong>. Do not make any more payments until this is resolved.';
		echo $util->errorFormat($critical);
	} else {
		$payments = $user->getPayments();

		$mail->eventRegistrationDone($user->getContactEmail(), $payments[0]);
		$mailed_to[] = $user->getContactEmail();
		foreach($post['pay_reg_id'] as $r_id) {
			$reg = new Registrant($r_id);
			if(!in_array($reg->getEmail(), $mailed_to)) {
				$mail->eventRegistrationDone($reg->getEmail(), $payments[0], $reg);
				$mailed_to[] = $reg->getEmail();
			}
		}
	}
} else  {
	echo $util->errorFormat(urldecode($httpParsedResponseAr['L_LONGMESSAGE0']));
}

/*
 * Script functions
 */

function onSuccess($post, $amt, $transaction_id) {
	global $user, $sql;
	$reg_ids = $post['pay_reg_id'];

	foreach($reg_ids as $id) {
		$reg_id = (int)$id;
		$reg = new Registrant($reg_id);
		$noshirt = $reg->getEvent()->getNoShirtDiscount();
		$discounts = $reg->getDiscounts();
		$paid = $reg->getSubtotal();
		$fee = $reg->getPaidFee() + $reg->getFeeDue(true);
		$director_paid = $reg->getEvent()->directorPays() ? $reg->getFeeDue(true):0;
		$payment = $reg->getPaid() + $paid - $director_paid;
		$director = $reg->getEvent()->directorPays() ? 1:0;
		
		$query = 'UPDATE '. TABLE_E_REGISTRANTS .'
			SET paid_date="'. $sql->date() .'", paid_time="'. $sql->time() .'", fee='. $fee .', payment='. $payment .', director_paid='. $director .', no_shirt='. $noshirt .', discounts='. $discounts .'
			WHERE id='. $reg_id .' AND user_id='. $user->getId();
		if(!$sql->q($query))
			addError(CRITICAL_ERROR);
	}

	$query = "INSERT INTO ". TABLE_RECEVIED_PAYMENTS .'
		(user_id, transaction, amount, registrants, date, time)
		VALUES ('. $user->getId() .', "'. $transaction_id .'", "'. $amt .'", "'.
			implode(';', $reg_ids) .'", "'. $sql->date() .'", "'. $sql->time() .'")';

	if(!$sql->q($query))
		addError(CRITICAL_ERROR);
}

function checkPaymentAmt($ids, $total) {
	$reg_total = 0;
	foreach($ids as $id) {
		$registrant = new Registrant($id);
		$reg_total += $registrant->getTotalDue();
	}
	if((string)$reg_total != $total)
		addError('Your total was miscalculated. Please refresh the page and try again');
}

function errorCheck($post) {
	$reg_ids =		$post['pay_reg_id'];
	
	if(empty($reg_ids))
		addError('Please select at least one registrant');

	if((int)$post['pay_grand_total'] > 0) {
		$fname =		trim($post['pay_fname']);
		$lname =		trim($post['pay_lname']);
		$cnumber =		trim($post['pay_card_number']);
		$ctype =		trim($post['pay_card_type']);
		$cvv2 =			trim($post['pay_cvv2']);
		$exp_m =		trim($post['pay_exp_m']);
		$exp_y =		trim($post['pay_exp_y']);
		$addr =			trim($post['pay_address']);
		$addr2 =		trim($post['pay_address2']);
		$city =			trim($post['pay_city']);
		$state =		trim($post['pay_state']);
		$zip =			trim($post['pay_zip']);

		if(empty($fname))
			addError('First name is required');
		if(empty($lname))
			addError('Last name is required');
		if(empty($cnumber))
			addError('Credit card number is required');
		if(empty($ctype))
			addError('Credit card type is required');
		if(empty($cvv2))
			addError('CVV2 code is required');
		if(empty($exp_m))
			addError('Expiration month is required');
		if(empty($exp_y))
			addError('Expiration year is required');
		if(empty($addr))
			addError('Address is required');
		if(empty($city))
			addError('City is required');
		if(empty($state))
			addError('State is required');
		if(empty($zip))
			addError('Zip code is required');
	}

	if(!hasErrors())
		checkPaymentAmt($reg_ids, $post['pay_grand_total']);
}

function addError($s) {
	global $errors;
	$errors[] = $s;
}

function hasErrors() {
	global $errors;
	return count($errors) > 0;
}

function getErrors() {
	global $errors;
	$out = '<ul>';
	foreach($errors as $e) {
		$out .= '<li>'. $e .'</li>';
	}
	$out .= '</ul>';
	return $out;
}

/** DoDirectPayment NVP example; last modified 08MAY23.
 *
 *  Process a credit card payment.
*/

/**
 * Send HTTP POST Request
 *
 * @param	string	The API method name
 * @param	string	The POST Message fields in &name=value pair format
 * @return	array	Parsed HTTP Response body
 */
function PPHttpPost($methodName_, $nvpStr_) {
	global $environment;

	// Set up your API credentials, PayPal end point, and API version.
	$API_UserName = urlencode(PAYPAL_USER);
	$API_Password = urlencode(PAYPAL_PASS);
	$API_Signature = urlencode(PAYPAL_SIG);
	$API_Endpoint = "https://api-3t.paypal.com/nvp";
	if("sandbox" === $environment || "beta-sandbox" === $environment) {
		$API_Endpoint = "https://api-3t.$environment.paypal.com/nvp";
	}
	$version = urlencode('51.0');

	// Set the curl parameters.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);

	// Turn off the server and peer verification (TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);

	// Set the API operation, version, and API signature in the request.
	$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

	// Set the request as a POST FIELD for curl.
	curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

	// Get response from the server.
	$httpResponse = curl_exec($ch);

	if(!$httpResponse) {
		exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
	}

	// Extract the response details.
	$httpResponseAr = explode("&", $httpResponse);

	$httpParsedResponseAr = array();
	foreach ($httpResponseAr as $i => $value) {
		$tmpAr = explode("=", $value);
		if(sizeof($tmpAr) > 1) {
			$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
		}
	}

	if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
		exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
	}

	return $httpParsedResponseAr;
}
?>