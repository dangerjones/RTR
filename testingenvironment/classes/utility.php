<?php
/*
 * General functions for the application
 */

class Utility {

	function  __construct() {
	}

	function makeList($array) {
		if(!is_array($array))
			$array = array($array);

		$out = '<ul>';
		foreach($array as $val) {
			$out .= '<li>'. $val .'</li>';
		}
		$out .= '</ul>';

		return $out;
	}
	
	function getEventsByStatus($status) {
		global $sql;
		$query = 'SELECT id FROM '. TABLE_EVENTS .' WHERE status='. $status;

		return $sql->getOneColumn($query);
	}

	function getUserLvlName($lvl) {
		if($lvl == LVL_ADMIN)
			$lvl = LVL_ADMIN_TAG;
		else if($lvl < LVL_ADMIN && $lvl >= LVL_EMPLOYEE)
			$lvl = LVL_EMPLOYEE_TAG;
		else
			$lvl = LVL_USER_TAG;

		return $lvl;
	}

	function resetPassAuthenticated($code, $email) {
		global $mail;
		$real_code = $this->getPassResetCode($email);

		if($real_code == $code) {
			$new_pass = $this->generatePassword();
			$user = new User($this->getUser($email), false);

			if($user->updatePass($new_pass)) {
				$subject = 'Account Password was Reset!';
				$message = 'Dear '. $user->getAddressingIdentifier() .','. "\r\n".
					"\t". 'Your account password was successfully reset and a new '.
					'password was randomly generated for you. Please login and change '.
					'it as soon as you can.'. "\r\n\r\n". 
					'Your new password: '. $new_pass ."\r\n\r\n".
					'To change your password after logging in, please go here: '. BASEURL .'my-account/'. "\r\n".
					'If you did not request this password reset, please contact us immediately!'. "\r\n\r\n".
					'Thank you!';

				$mail->sendAdmin($email, $subject, $message);
				return true;
			}
		}

		return false;
	}

	function updateResetAttempts($email, $set_to = null) {
		global $sql;

		$safeEmail = $sql->safeString($email);
		
		if($set_to === null) {
			$attempts = $this->getPasswordResetAttempts($email);
			
			if($attempts > 0)
				$query = 'UPDATE '. TABLE_PASS_RESET .' SET attempts='. ($attempts+1) .' WHERE email="'. $safeEmail .'"';
			else if($this->hasPreviousResetAttempt($email))
				$query = 'UPDATE '. TABLE_PASS_RESET .' SET attempts=1, date="'. $sql->date() .'" WHERE email="'. $safeEmail .'"';
			else
				$query = 'INSERT INTO '. TABLE_PASS_RESET .' (email, date) VALUES ("'. $safeEmail .'", "'. $sql->date() .'")';
		} else if(is_numeric($set_to)) {
			if($this->hasPreviousResetAttempt($email))
				$query = 'UPDATE '. TABLE_PASS_RESET .' SET attempts='. $set_to .', date="'. $sql->date() .'" WHERE email="'. $safeEmail .'"';
			else
				$query = 'INSERT INTO '. TABLE_PASS_RESET .' VALUES ("'. $safeEmail .'", "'. $sql->date() .'", '. $set_to .')';
		}

		return $sql->q($query);
	}

	function getPassResetCode($email) {
		$e = md5($email);
		$d = md5(date('m-d-Y') . SALT);
		$s = md5(SALT);

		$mega_encryption = sha1(md5($e.$d.$s));
		return md5($mega_encryption);
	}

	function generatePassword($len = null) {
		if($len == null)
			$len = mt_rand(8,13);

		$pass = '';
		for($i = 0; $i < $len; $i++) {
			$pass .= $this->getRandomValidPasswordCharacter();
		}

		return $pass;
	}
	
	private function getRandomValidPasswordCharacter() {
		$alphanum= array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',
			'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 1, 2, 3, 4, 5, 6, 7, 8, 9);
		
		$symbols = array('!', '@', '#', '$', '%', '^', '&', '(', ')', '-', '_', '=', '+', '[', ']', '{', '}', '?');

		$get_symbol = (10/mt_rand(1,10) <= 1);

		if($get_symbol) {
			$chars = $symbols;
			$up	= false;
		}
		else {
			$chars = $alphanum;
			$up	= mt_rand() % 2 == 1;
		}
		

		$max		= count($chars)-1;
		$char		= $chars[mt_rand(0, $max)];
		$is_alpha	= ctype_alpha($char);
		
		return ($is_alpha && $up ? strtoupper($char):$char);
	}

	function getPasswordResetAttempts($email) {
		global $sql;
		$email = $sql->safeString($email);

		$query = 'SELECT attempts FROM '. TABLE_PASS_RESET .' WHERE email="'. $email .'" AND date="'. $sql->date() .'"';
		$result = $sql->getOneFieldEntry($query, 'attempts');

		if($result > 0)
			return (int)$result;
		else
			return 0;
	}

	function hasPreviousResetAttempt($email) {
		global $sql;
		$email = $sql->safeString($email);

		$query = 'SELECT email FROM '. TABLE_PASS_RESET .' WHERE email="'. $email .'"';
		return $sql->rows($query) > 0;
	}

	function getUser($email_or_id) {
		if(is_numeric($email_or_id))
			return $this->getUserById($email_or_id);
		else
			return $this->getUserByEmail($email_or_id);
	}

	function getEventType($type) {
		global $event_types;
		
		$type = (int)$type;
		return $event_types[$type];
	}

	function getEventIdByPermalink($permalink, $year) {
		global $sql;
		$safe = $sql->safeString($permalink);
		$year = (int)$year;
		$query = 'SELECT id FROM '. TABLE_EVENTS .' WHERE permalink="'. $safe .'" AND year='. $year;
		
		return (int)$sql->getOneFieldEntry($query, 'id');
	}

	function sortShirtSizes($size_array, $youth_or_adult) {
		global $youth_shirt_sizes, $adult_shirt_sizes;
		$sizes = $youth_or_adult == 'a' ? $adult_shirt_sizes:$youth_shirt_sizes;
		$sorted = array();

		foreach($sizes as $s) {
			if(in_array($s, $size_array))
				$sorted[] = $s;
		}
		return $sorted;
	}

	function getFee($cost) {
		$fees = array('0'=>0, '5.99'=>.5, '19.99'=>1, '29.99'=>1.5, '39.99'=>2, '59.99'=>3, '79.99'=>4);
		$max_fee = 5;
		foreach($fees as $c => $f) {
			if($cost <= $c)
				return $f;
		}
		return $max_fee;
	}

    function money($amt) {
        return '$'. number_format($amt, 2);
    }

	function eventExists($event_id) {
		global $sql;

		$query = 'SELECT id FROM '. TABLE_EVENTS .' WHERE id='. $event_id;
		return $sql->rows($query) > 0;
	}

	function raceExists($event_id, $race_id) {
		global $sql;

		$query = 'SELECT id FROM '. TABLE_RACES .'
			WHERE id=' . $race_id .' AND event_id='. $event_id;
		return $sql->rows($query) > 0;
	}

	function updateEStatus($event_id, $status) {
		global $sql;
		
		$query = 'UPDATE '. TABLE_EVENTS .' SET status='. $status .' WHERE id='. $event_id;
		return $sql->q($query);
	}

	function userEmail($user_id) {
		global $sql;

		$query = 'SELECT email FROM '. TABLE_USER .' WHERE id='. $user_id;
		return $sql->getOneFieldEntry($query, 'email');
	}

	function changeEStatus($event_id, $status) {
		global $sql;

		$query = 'UPDATE '. TABLE_EVENTS .' SET status='. $status .' WHERE id='. $event_id;
		return $sql->q($query);
	}

	function deleteEvent($event_id) {
		global $sql;
		$query = array();
		$event_id = (int)$event_id;

		// Don't delete event if there are registrants
		$q = 'SELECT id FROM '. TABLE_E_REGISTRANTS .' WHERE event_id='. $event_id;
		if($sql->rows($q) > 0)
			return false;

		// Delete all information associated with an event
		$query[] = 'DELETE FROM '. TABLE_EVENTS .' WHERE id='. $event_id;
		$query[] = 'DELETE FROM '. TABLE_E_QUESTIONS .' WHERE event_id='. $event_id;
		$query[] = 'DELETE FROM '. TABLE_RACES .' WHERE event_id='. $event_id;
		$query[] = 'DELETE FROM '. TABLE_REG_METHODS .' WHERE event_id='. $event_id;
		$query[] = 'DELETE FROM '. TABLE_E_COUPONS .' WHERE event_id='. $event_id;
		$query[] = 'DELETE FROM '. TABLE_RACE_RESULTS .' WHERE event_id='. $event_id;
		
		return $sql->q($query);
	}

	function isAjax() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
	}

	function isSSL() {
		return $_SERVER["HTTPS"] == "on";
	}

	function forceSSL() {
		if(!$this->isSSL() && !TESTING) {
		   header("HTTP/1.1 301 Moved Permanently");
		   header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		   die();
		}
	}

	function cleanUp($s, $limit = 0) {
		if(is_array($s)) {
			foreach($s as $k => $val)
				$s[$k] = $this->cleanUp($val, $limit);
			return $s;

		} else {
			$filtered = trim($s);
			if($limit > 0)
				$filtered = substr($filtered, 0, $limit);
			return mysql_real_escape_string($filtered);
		}
	}

	function cleanPermalink($link) {
		$cleaned = strtolower($this->cleanUp($link, 255));
		$cleaned = str_replace('&', 'and', $cleaned);

		// remove everything except: alphanumeric, hyphen, and whitespace
		$cleaned = preg_replace('/[^a-z0-9-\s]/', '', $cleaned);

		// replace whitespace with hyphen
		$cleaned = preg_replace('/\s+/', '-', $cleaned);

		// remove multiple occurences of hyphens
		$cleaned = preg_replace('/(-)\\1+/', '$1', $cleaned);

		// remove hyphens from beginning and end
		$cleaned = preg_replace('/^-|-$/', '', $cleaned);

		return $cleaned;
	}

	function permalinkAvailable($link, $date = null) {
		global $sql;
		$query = 'SELECT id FROM '. TABLE_EVENTS .' WHERE permalink="'. $sql->safeString($link) .'"';

		if($date != null && is_numeric($date))
			$query .= ' AND year='. (int)$date;

		return $sql->rows($query) == 0;
	}

	function filenameSafe($filename) {
		$filename = preg_replace('/[^a-zA-Z0-9\s_]/', '', $filename);
		$filename = preg_replace('/\s+/', '_', $filename);
		return $filename;
	}
	
	function errorFormat($errorList) {
		$output =	'<div class="ui-state-error ui-corner-all">';
		$output .=	'<strong>Error: </strong>';
		$output .=	$errorList;
		$output .=	'</div>';

		return $output;
	}

	function getAllStates() {
		return array("AL" => "Alabama", "AK" => "Alaska", "AZ" => "Arizona", "AR" => "Arkansas",
			"CA" => "California", "CO" => "Colorado", "CT" => "Connecticut", "DE" => "Delaware",
			"FL" => "Florida", "GA" => "Georgia", "HI" => "Hawaii", "ID" => "Idaho", "IL" => "Illinois",
			"IN" => "Indiana", "IA" => "Iowa", "KS" => "Kansas", "KY" => "Kentucky", "LA" => "Louisiana",
			"ME" => "Maine", "MD" => "Maryland", "MA" => "Massachusetts", "MI" => "Michigan",
			"MN" => "Minnesota", "MS" => "Mississippi", "MO" => "Missouri", "MT" => "Montana",
			"NE" => "Nebraska", "NV" => "Nevada", "NH" => "New Hampshire", "NJ" => "New Jersey",
			"NM" => "New Mexico", "NY" => "New York", "NC" => "North Carolina", "ND" => "North Dakota",
			"OH" => "Ohio", "OK" => "Oklahoma", "OR" => "Oregon", "PA" => "Pennsylvania",
			"RI" => "Rhode Island", "SC" => "South Carolina", "SD" => "South Dakota", "TN" => "Tennessee",
			"TX" => "Texas", "UT" => "Utah", "VT" => "Vermont", "VA" => "Virginia", "WA" => "Washington",
			"WV" => "West Virginia", "WI" => "Wisconsin", "WY" => "Wyoming");
	}

	private function getUserById($id) {
		global $sql;
		$user_id = (int)$id;
		$query = 'SELECT `email` FROM '. TABLE_USER .' WHERE id='. $user_id;
		return $sql->getOneFieldEntry($query, 'email');
	}

	private function getUserByEmail($email) {
		global $sql;
		$email = $sql->safeString($email);
		$query = 'SELECT `id` FROM '. TABLE_USER .' WHERE email="'. $email .'"';
		return $sql->getOneFieldEntry($query, 'id');
	}
}
?>
