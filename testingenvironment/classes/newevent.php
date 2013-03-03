<?php
require_once 'newrace.php';
/*
 * Handles a submission for a new event
 */


class NewEvent {
	protected $numErr;
	protected $err;
	protected $races;

	/*
	 * Event variables
	 */
	protected $e_name, $e_type, $e_date, $e_desc, $e_contact;
	protected $e_shirt_avail, $e_youth, $e_adult, $e_no_shirt, $e_no_shirt_disc;
	protected $e_reg_here, $reg_methods, $e_other_web, $e_addr1, $e_addr2;
	protected $e_allow_custom_q, $banner, $entry_form, $course_map, $user_pays_fee;
	protected $coupon_codes, $coupon_code_amts, $permalink;


	function  __construct($eventInfo) {
		global $util;
		$eventInfo = $util->cleanUp(filter_var_array($eventInfo, FILTER_SANITIZE_SPECIAL_CHARS));
		$this->numErr = 0;
		$this->err = array();
		$this->races = array();

		$this->setVariables($eventInfo);
		$this->errorCheck();
		$this->addRaces($eventInfo);
	}

	function errString() {
		$output =	$this->getErrList();
		$output .=	$this->getRaceErrLists();
		return $output;
	}

	function errorsExist() {
		if($this->numErr+$this->getRaceNumErrs() > 0)
			return true;
		else
			return false;
	}

	function addToDB() {
		global $sql, $util;

		// Add event to DB
		if(!$this->addEventToDB())
			return false;

		// Add races to DB
		$event_id = $sql->id();
		foreach($this->races as $race) {
			if(!$race->addToDB($event_id))
				return false;
		}

		// Add registration methods to DB
		if(!$this->addRegMethodsToDB($event_id))
			return false;

		// Add promo codes to DB
		if(!empty($this->coupon_codes)) {
			for($i = 0; $i < count($this->coupon_codes); $i++) {
				if(!$this->addCouponToDB($event_id, $this->coupon_codes[$i], $this->coupon_code_amts[$i]))
					return false;
			}
		}

		// Add questions to DB
		for($i = 1; $i < E_MAX_Q+1; $i++) {
			$q_var = 'e_q_'. $i;
			$a_var = 'e_a_'. $i;
			if(isset($this->$q_var)) {
				if(!$this->addQToDB($event_id, $this->$q_var, $this->$a_var))
					return false;
			} else
				break;
		}

		/*
		 * By this time, there have been no db errors so update the event entry
		 * to be valid and looked at by an administrator
		 */
		if(!$util->updateEStatus($event_id, ESTATUS_WAITING))
			return false;

		// All is well is DB land...
		return true;
	}

	function addErr($err) {
		$this->numErr++;
		$this->err[] = $err;
	}

	function shirtSizesToArray($type) {
		$sizes = $this->shirtSizesToString($type);
		if(empty($sizes))
			return array();
		else
			return explode(',', $sizes);
	}

	function shirtSizesToString($type) {
		global $youth_shirt_sizes, $adult_shirt_sizes;
		$sizes = $type == 'a' ? $this->e_adult:$this->e_youth;
		$string = '';
		$i = 0;

		foreach($sizes as $s) {
			$string .= $type == 'a' ? $adult_shirt_sizes[$s]:$youth_shirt_sizes[$s];
			if(++$i < count($sizes))
				$string .= ',';
		}

		return $string;
	}

	function couponErrCheck() {
		$coupon_num = count($this->coupon_codes);
		$coupon_amt_num = count($this->coupon_code_amts);

		if($coupon_num > $coupon_amt_num)
			$this->addErr('Coupon code\'s discount is missing');
		else if($coupon_num < $coupon_amt_num)
			$this->addErr('Coupon codename is missing');

		foreach($this->coupon_codes as $c) {
			$validate = preg_match('/^[a-zA-Z0-9]*$/', $c);
			if(!$validate) {
				$this->addErr('Coupon codenames must be alphanumeric with no spaces');
				break;
			}
		}
		foreach($this->coupon_code_amts as $amt) {
			$validate = is_numeric($amt);
			if(!$validate) {
				$this->addErr('Coupon discounts must be numeric');
				break;
			}
		}
	}


	function addCouponToDB($e_id, $codename, $amt) {
		global $sql;
		$query = 'INSERT INTO '. TABLE_E_COUPONS .' (event_id, code, amount)
			VALUES ('. $e_id .', "'. strtoupper($codename) .'", "'. $amt .'")';

		return $sql->q($query);
	}

	protected function addRegMethodsToDB($event_id) {
		if(!in_array(EREG_METHOD_WEB, $this->reg_methods))
			$this->e_other_web = '';
		if(!in_array(EREG_METHOD_ADDR1, $this->reg_methods))
			$this->e_addr1 = '';
		if(!in_array(EREG_METHOD_ADDR2, $this->reg_methods))
			$this->e_addr2 = '';

		if(empty($this->e_addr1) && !empty($this->e_addr2)) {
			$this->e_addr1 = $this->e_addr2;
			$this->e_addr2 = '';
		}

		if(!$this->addRegMethodToDB($event_id, EREG_METHOD_WEB, $this->e_other_web) ||
				!$this->addRegMethodToDB($event_id, EREG_METHOD_ADDR1, $this->e_addr1) ||
				!$this->addRegMethodToDB($event_id, EREG_METHOD_ADDR2, $this->e_addr2))
			return false;

		return true;
	}

	protected function addRegMethodToDB($event_id, $type, $info) {
		if(empty($info))
			return true;
		
		global $sql;
		$query = "INSERT INTO ". TABLE_REG_METHODS ." (`event_id`, `type`, `info`) ".
				"VALUES ($event_id, $type, '$info')";
		
		return $sql->q($query);

	}

	protected function addEventToDB() {
		global $util, $sql, $user, $event_types;

		$month			= date('n', $this->e_date);
		$day			= date('j', $this->e_date);
		$year			= date('Y', $this->e_date);
		$type			= $util->getEventType($this->e_type);
		$youth			= empty($this->e_youth) ? '':$this->shirtSizesToString('y');
		$adult			= empty($this->e_adult) ? '':$this->shirtSizesToString('a');
		$reg_here		= $this->e_reg_here == true ? 1:0;
		$fee_payer		= $this->user_pays_fee == true ? 1:0;
		$allow_no_shirt = $this->e_no_shirt == true ? 1:0;

		$query = 'INSERT INTO '. TABLE_EVENTS .
				' (user_id, name, month, day, year, permalink, type, description, contact_info, youth_shirt_sizes, adult_shirt_sizes, allow_no_shirt, no_shirt_discount, reg_here, user_pays_fee, banner, entry_form, course_map, status)'.
				" VALUES (". $user->getId() .", '$this->e_name', $month, $day, $year, '$this->permalink', '$type', '$this->e_desc', '$this->e_contact', '$youth', '$adult', $allow_no_shirt, $this->e_no_shirt_disc, $reg_here, $fee_payer, '$this->banner', '$this->entry_form', '$this->course_map', ". ESTATUS_INC .")";

		return $sql->q($query);
	}

	protected function addQToDB($event_id, $q, $a) {
		global $sql;
		$a_delimited = implode(DELIMITER, $a);

		$query = "INSERT INTO ". TABLE_E_QUESTIONS ." (event_id, question, answers) ".
				"VALUES ($event_id, '$q', '$a_delimited')";

		return $sql->q($query);
	}

	protected function getRaceErrLists() {
		$out = '';
		if($this->races == null)
			return $out;
		
		foreach($this->races as $r) {
			$out .= $r->getErrList();
		}
		return $out;
	}

	protected function getRaceNumErrs() {
		$errs = 0;
		if($this->races == null)
			return 0;
		
		foreach($this->races as $r) {
			$errs += $r->getErrNum();
		}
		return $errs;
	}

	protected function addRaces($r) {
		global $util;
		$race = array();
		for($i = 0; $i < count($r['race-name']); $i++) {
			$race['event-date']				= $this->e_date;

			$race['race-name']				= $util->cleanUp($r['race-name'][$i], 100);
			$race['race-distance']			= $r['race-distance'][$i];
			$race['race-dist-measure']		= (int)$r['race-dist-measure'][$i];
			$race['race-time']				= strtotime($r['race-time'][$i]);
			$race['race-time-isam']			= $r['race-time-isam'][$i] == 'true';
			$race['race-location']			= $util->cleanUp($r['race-location'][$i], 255);

			$race['race-free']				= $r['race-free'][$i] == 'true';

			if(!$race['race-free']) {
				$race['race-dayof']				= $r['race-dayof'][$i];
				$race['race-prereg']			= $r['race-prereg'][$i];
				$race['race-early']				= $r['race-early'][$i];

				$race['race-early-date']		= !empty($r['race-early-date'][$i]) ?
									strtotime($r['race-early-date'][$i]) : $r['race-early-date'][$i];
			} else {
				$race['race-dayof']				= 0;
				$race['race-prereg']			= 0;
				$race['race-early']				= 0;
				$race['race-early-date']		= '';
			}

			$this->addRace(new NewRace($race, $i+1));
		}
	}

	protected function addRace($r) {
		$this->races[] = $r;
	}

	function getErrList() {
		$output = '<ul>';
		if($this->numErr > 0) {
			foreach($this->err as $e) {
				$output .= '<li>'. $e .'</li>';
			}
			$output .= '</ul>';
		} else {
			$output = '';
		}
		return $output;
	}

	protected function errorCheck() {
		global $util;

		if(empty($this->e_name))
			$this->addErr('Event name required');

		if(!isset($GLOBALS['event_types'][$this->e_type]))
			$this->addErr('Invalid type');

		if(!$this->e_date)
			$this->addErr('Invalid event date');
		else if($this->e_date < time())
			$this->addErr('Event date must be in the future');

		if(empty($this->e_desc))
			$this->addErr('Description required');

		if(empty($this->permalink))
			$this->addErr('Permalink is required');
		else if($this->e_date && !$util->permalinkAvailable($this->permalink, date('Y', $this->e_date)))
			$this->addErr('Permalink is already in use by another event');
		else if($util->cleanPermalink($this->permalink) != $this->permalink)
			$this->addErr('Invalid permalink <img src="/img/question-icon.png" class="point what-is-permalink" alt="More info" title="More info" />');

		if($this->e_shirt_avail) {
			$this->shirtErrCheck();
			if($this->e_no_shirt && !is_numeric($this->e_no_shirt_disc))
				$this->addErr('Invalid "No-shirt" discount');
		}

		if(in_array(EREG_METHOD_WEB, $this->reg_methods))
			$this->urlErrCheck();

		if(in_array(EREG_METHOD_ADDR1, $this->reg_methods) && empty($this->e_addr1))
			$this->addErr('Enter an address under registration methods');

		if(in_array(EREG_METHOD_ADDR2, $this->reg_methods) && empty($this->e_addr2))
			$this->addErr('Enter an alternate address under registration methods');

		if($this->e_allow_custom_q)
			$this->questionErrCheck();

		if(count($this->coupon_codes) + count($this->coupon_code_amts) > 0)
			$this->couponErrCheck();

	}

	protected function urlErrCheck() {
		if(empty($this->e_other_web))
			$this->addErr('Enter a website under registration methods');
		else if(!filter_var($this->e_other_web, FILTER_VALIDATE_URL))
			$this->addErr('Invalid website url under registration methods');
	}

	protected function questionErrCheck() {
		for($i = 1; $i < E_MAX_Q+1; $i++) {
			$q_var = 'e_q_'. $i;
			if(isset($this->$q_var)) {
				if(empty($this->$q_var))
					$this->addErr('Enter in something for Question '. $i);
				$a_var = 'e_a_'. $i;
				if(count($this->$a_var) < 2)
					$this->addErr('At least two answers are required for Question '. $i);
			}
		}
	}

	protected function removeEmptyAnswers($answers) {
		global $util;
		$noEmpty = array();
		foreach($answers as $a) {
			$a = $util->cleanUp($a, 50);
			if(!empty($a))
				$noEmpty[] = $a;
		}
		return $noEmpty;
	}

	protected function shirtErrCheck() {
		global $youth_shirt_sizes, $adult_shirt_sizes;

		if(empty($this->e_youth) && empty($this->e_adult))
			$this->addErr('Choose at least one shirt size');
		else {
			foreach($this->e_youth as $y) {
				if(!array_key_exists($y, $youth_shirt_sizes)) {
					$this->addErr('Invalid youth shirt size');
					break;
				}
			}

			foreach($this->e_adult as $a) {
				if(!array_key_exists($a, $adult_shirt_sizes)) {
					$this->addErr('Invalid adult shirt size');
					break;
				}
			}
		}

	}

	protected function setVariables($e) {
		global $util, $user;
		$this->e_name					= $util->cleanUp($e['event-name'], 100);
		$this->e_type					= (int)$e['event-type'];
		$this->e_date					= strtotime($e['event-date']);
		$this->e_desc					= $e['event-description'];
		$this->permalink				= strtolower($util->cleanUp($e['event-permalink'], 255));
		$this->e_contact				= $util->cleanUp($e['event-contact'], 100);

		$this->setShirtVariables($e);

		$this->e_reg_here				= $e['event-reg-here'] == 'true';

		if($this->e_reg_here)
			$this->user_pays_fee = $e['event-reg-fee'] != 'true';

		$this->reg_methods				= isset($e['event-registration-methods']) ?
											$e['event-registration-methods']:array();
		$this->e_other_web				= $e['event-other-web'] == 'http://' ? '':
										$util->cleanUp($e['event-other-web'], 255);
		$this->e_addr1					= $e['event-addr1'];
		$this->e_addr2					= $e['event-addr2'];

		$this->e_allow_custom_q			= $e['event-custom-q'] == 'true';

		if($this->e_allow_custom_q) {
			for($i = 1; $i < E_MAX_Q+1; $i++) {
				if(isset($e['event-question-'. $i])) {
					$q_var = 'e_q_'. $i;
					$a_var = 'e_a_'. $i;
					$this->$q_var = $util->cleanUp($e['event-question-'. $i], 100);
					$this->$a_var = $this->removeEmptyAnswers($e['event-answer-'. $i]);
				}
			}
		}

		$filtered_coupons = array();
		foreach($e['event-registration-coupons'] as $coupon) {
			if(!empty($coupon))
				$filtered_coupons[] = $coupon;
		}
		$filtered_coupon_amts = array();
		foreach($e['event-registration-coupon-amt'] as $amt) {
			if(!empty($amt))
				$filtered_coupon_amts[] = $amt;
		}

		$this->coupon_codes		= $filtered_coupons;
		$this->coupon_code_amts = $filtered_coupon_amts;

		$this->banner			= $user->makeUploadedFilePath($e['event-banner']);
		$this->entry_form		= $user->makeUploadedFilePath($e['event-entry-form']);
		$this->course_map		= $user->makeUploadedFilePath($e['event-course-map']);
	}

	protected function setShirtVariables($e) {
		$this->e_shirt_avail			= $e['event-shirts-available'] == 'true';

		if($this->e_shirt_avail) {
			$this->e_youth				= empty($e['event-youth-shirt']) ? array():$e['event-youth-shirt'];
			$this->e_adult				= empty($e['event-adult-shirt']) ? array():$e['event-adult-shirt'];
			$this->e_no_shirt			= $e['event-no-shirt'] == 'true';
		} else {
			$this->e_youth				= array();
			$this->e_adult				= array();
			$this->e_no_shirt			= false;
		}

		if($this->e_no_shirt)
			$this->e_no_shirt_disc		= $e['event-no-shirt-discount'];
		else
			$this->e_no_shirt_disc		= 0;
	}
}
?>
