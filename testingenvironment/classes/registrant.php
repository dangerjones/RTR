<?php
require_once 'event.php';
require_once 'race.php';
require_once 'coupon.php';

class Registrant extends Person {
	private $event_id;
	private $race_id;
	private $paid;
	private $paid_fee;
	private $payment_due;
	private $paid_date;
	private $paid_time;
	private $director_paid;
	private $coupon;
	private $no_shirt;
	private $questions;
	private $answers;

	private $event;
	private $race;

	static private $all_registrants = array(); // Structure: array(id -> Registrant obj, id -> Registrant obj)

	function  __construct($person_id, $event = null) {
		if(!is_array($person_id)) {
			$this->id = (int)$person_id;
			if($event != null)
				$this->event = $event;
			$this->setVariablesFromDB();
		} else {
			$reg_details =& $person_id;
			$this->setProperties($reg_details);
		}
	}

	/* get methods */
	function getRace() {
		return $this->race;
	}
	function getRaceId() {
		return $this->race_id;
	}
	function getEvent() {
		return $this->event;
	}
	function getUser() {
		return User::getUser($this->user_id);
	}
	function getAnswers() {
		return $this->answers;
	}
	function getQuestions() {
		return $this->questions;
	}
	function getPaymentDue() {
		$paid_total = $this->paid;
		if($this->event->directorPays())
			$paid_total += $this->paid_fee;

		if($this->hasPaid())
			$race_price = $this->race->getPriceByTimestamp(strtotime($this->paid_date));
		else
			$race_price = $this->race->getPriceByTimestamp();

		$due = $race_price - $paid_total;

		if($due > 0)
			return $due;
		return 0;
	}
	function getFeeDue($force_return_fee = false) {
		global $util;
		if($this->event->directorPays() && !$force_return_fee)
			return 0;

		if($this->hasPaid())
			$race_price = $this->race->getPriceByTimestamp(strtotime($this->paid_date));
		else
			$race_price = $this->race->getPriceByTimestamp();

		$due = $util->getFee($race_price) - $this->paid_fee;
		if($due > 0)
			return $due;
		else
			return 0;
	}
	/* this is only used to calculate the discount before payment has been made */
	function getNoShirtDiscount() {
		if(!$this->hasPaid())
			return $this->event->getNoShirtDiscount();
		else
			return $this->no_shirt;
	}
	function getDiscounts() {
		if(!$this->hasShirt())
			$total_discounts = $this->getNoShirtDiscount();
		else
			$total_discounts = 0;

		if($this->hasCoupon())
			$total_discounts += $this->coupon->getAmount();

		return $total_discounts;
	}
	function getSubtotal() {
		$subtotal = $this->getPaymentDue()-$this->getDiscounts();
		return $subtotal < 0 ? 0:$subtotal;
	}
	function getTotalDue() {
		return $this->getSubtotal()+$this->getFeeDue();
	}
	function getPaid() {
		return $this->paid;
	}
	function getPaidFee() {
		return $this->paid_fee;
	}
	function getTotalPaid() {
		$total = $this->paid;
		$total += $this->paid_fee;
		return $total;
	}
	function getCoupon() {
		return $this->coupon;
	}
	function getPaidDate($format = null) {
		$date = explode('-', $this->paid_date);
		$time = explode(':', $this->paid_time);
		$timestamp = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);

		if($format == null) {
			return $timestamp;
		}
		return date($format, $timestamp);
	}
	function getOnlyPaidDate($format = null) {
		$date = explode('-', $this->paid_date);
		$timestamp = mktime(0, 0, 0, $date[1], $date[2], $date[0]);

		if($format == null) {
			return $timestamp;
		}
		return date($format, $timestamp);
	}
		// end get methods


	/*
	 * Public methods
	 */
	function hasCoupon() {
		return !empty($this->coupon);
	}
	function hasAnswers() {
		return count($this->answers) > 0;
	}
	function hasDiscounts() {
		return $this->getDiscounts() > 0;
	}
	function hasPaid() {
		return strtotime($this->paid_date) > 0;
	}
	function directorPaid() {
		return $this->director_paid;
	}
	function spreadsheetAnswers() {
		$string = '';
		for($i = 0; $i < count($this->answers); $i++) {
			$string .= $this->questions[$i] .' '. $this->answers[$i];
			if($i < count($this->answers)-1)
				$string .= ';  ';
		}

		return $string;
	}
		// end public methods

	/*
	 * Compare methods
	 */
    static function cmp_byFName($a, $b) {
        $a_name = strtolower($a->getFName());
        $b_name = strtolower($b->getFName());

        if ($a_name == $b_name) {
            return 0;
        }
        return ($a_name > $b_name) ? +1 : -1;
    }
    static function cmp_byLName($a, $b) {
        $a_name = strtolower($a->getLName());
        $b_name = strtolower($b->getLName());
		
        if ($a_name == $b_name) {
            return 0;
        }
        return ($a_name > $b_name) ? +1 : -1;
    }


	/*
	 * Private methods
	 */

	private function setVariablesFromDB() {
		global $user, $sql;

		$query = 'SELECT * FROM '. TABLE_E_REGISTRANTS .' WHERE id='. $this->id;
		$info = $sql->getAssocRow($query);

		$this->user_id =			$info['user_id'];
		$this->event_id =			$info['event_id'];
		$this->race_id =			$info['race_id'];
		$this->email =				$info['email'];
		$this->fname =				$info['fname'];
		$this->lname =				$info['lname'];
		$this->address_1 =			$info['address_1'];
		$this->address_2 =			$info['address_2'];
		$this->city =				$info['city'];
		$this->state =				$info['state'];
		$this->zip =				$info['zip'];
		$this->phone =				$info['phone'];
		$this->bday =				$info['birthday'];
		$this->gender =				$info['gender'];
		$this->shirt_size =			$info['shirt_size'];
		$this->adult_shirt =		$info['adult_size'];
		$this->paid =				$info['payment'];
		$this->paid_fee =			$info['fee'];
		$this->paid_date =			$info['paid_date'];
		$this->paid_time =			$info['paid_time'];
		$this->director_paid =		$info['director_paid'] == 1;
		$q_a =						$info['answers'];

		$this->setAge($this->bday);
		$this->setQandA($q_a);

		if(!empty($info['coupon_id']))
			$this->coupon = new Coupon($info['coupon_id']);

		if($this->event == null)
			$this->event = EventHandler::getEvent($this->event_id);

		$this->no_shirt				= $info['no_shirt'];

		$this->race =			$this->event->getRace($this->race_id);
		
		$this->payment_due =	$this->race->getPriceByTimestamp();
	}

	private function setQandA($q_a) {
		$this->questions = array();
		$this->answers = array();
		$broken = explode(DELIMITER, $q_a);

		for($i = 0; $i < count($broken); $i++) {
			$this->questions[] = $broken[$i];
			$this->answers[] = $broken[++$i];
		}

	}

	private function setProperties($reg_details) {

		$this->id					= $reg_details['id'];
		$this->user_id				= $reg_details['user_id'];
		$this->event_id				= $reg_details['event_id'];
		$this->race_id				= $reg_details['race_id'];
		$this->email				= $reg_details['email'];
		$this->fname				= $reg_details['fname'];
		$this->lname				= $reg_details['lname'];
		$this->address_1			= $reg_details['address_1'];
		$this->address_2			= $reg_details['address_2'];
		$this->city					= $reg_details['city'];
		$this->state				= $reg_details['state'];
		$this->zip					= $reg_details['zip'];
		$this->phone				= $reg_details['phone'];
		$this->bday					= $reg_details['birthday'];
		$this->gender				= $reg_details['gender'];
		$this->shirt_size			= $reg_details['shirt_size'];
		$this->adult_size			= $reg_details['adult_size'];
		$this->paid					= $reg_details['payment'];
		$this->paid_fee				= $reg_details['fee'];
		$this->paid_date			= $reg_details['paid_date'];
		$this->paid_time			= $reg_details['paid_time'];
		$this->director_paid		= $reg_details['director_paid'] == 1;
		$this->zip					= $reg_details['zip'];
		$this->user_id				= $reg_details['user_id'];
		$q_a						= $reg_details['answers'];


		$this->setAge($this->bday);
		$this->setQandA($q_a);

		if(!empty($reg_details['coupon_id']))
			$this->coupon = Coupon::getCoupon($reg_details['coupon_id']);

		if($this->event == null)
			$this->event = EventHandler::getEvent($this->event_id);

		$this->no_shirt				= $reg_details['no_shirt'];
		$this->race					= $this->event->getRace($this->race_id);
		$this->payment_due			= $this->race->getPriceByTimestamp();
		
	}

	static function cacheRegistrant($where) {
		global $sql;
		if(strlen($where) > 0)
			$where = ' WHERE '. $where;
		$query = 'SELECT * FROM '. TABLE_E_REGISTRANTS . $where;

		$registrants = $sql->getAssoc($query);
		foreach($registrants as $reg_details) {
			self::addRegistrant(new Registrant($reg_details));
		}
	}

	static function addRegistrant($registrant_obj) {
		self::$all_registrants[$registrant_obj->getId()] = $registrant_obj;
	}

	static function getRegistrant($reg_id) {
		$registrant =& self::$all_registrants[$reg_id];
		if(!is_object($registrant))
			self::cacheRegistrant ('id="'. $reg_id .'"');

		return $registrant;
	}

	function  __toString() {
		return $this->getFullName();
	}
}

?>
