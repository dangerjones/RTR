<?php

/**
 * Class that takes in a number of registrants and outputs statistical data
 * based on the received registrants
 *
 * @author Sterling
 */
class RegistrantStatistics {
	private $filter_money;
	protected $event_id, $reg_ids, $total_registrants, $male, $total_money,
			$director_receives, $director_paid;
	protected $discounts, $fees, $race_ids, $ages, $youth_shirts, $adult_shirts, $no_shirts, $q_a;
	protected $youngest, $oldest, $registered_days, $registered_hours, $first_reg, $last_reg,
			$earliest, $latest;
	protected $ages_sorted, $shirts_sorted;

	function  __construct($e_id) {
		$this->filter_money			= false;
		$this->event_id				= (int)$e_id;
		$this->reg_ids				= array();
		$this->total_registrants	= 0;
		$this->male					= 0;
		$this->female				= 0;
		$this->total_money			= 0;
		$this->director_receives	= 0;
		$this->director_paid		= 0;
		$this->discounts			= 0;
		$this->fees					= 0;
		$this->race_ids				= array();
		$this->ages					= array();
		$this->ages_sorted			= false;
		$this->youth_shirts			= array();
		$this->adult_shirts			= array();
		$this->no_shirts			= 0;
		$this->shirts_sorted		= false;
		$this->q_a					= array();
		$this->registered_days		= array();
		$this->registered_hours		= array();
	}

	/* get methods */
	function getTotalRegistrants() {
		return $this->total_registrants;
	}
	function getRegistrantPercentage($reg_count) {
		return round($reg_count/$this->total_registrants*100);
	}
	function getTotalMales() {
		return $this->male;
	}
	function getMalePercentage() {
		return $this->getRegistrantPercentage($this->male);
	}
	function getTotalFemales() {
		return $this->total_registrants-$this->male;
	}
	function getFemalePercentage() {
		return $this->getRegistrantPercentage($this->getTotalFemales());
	}
	function getTotalMoneyCollected() {
		return $this->filterMoney($this->total_money);
	}
	function getDirectorsMoney() {
		return $this->filterMoney($this->director_receives);
	}
	function getTotalDiscounts() {
		return $this->filterMoney($this->discounts);
	}
	function getTotalFees() {
		return $this->filterMoney($this->fees);
	}
	function getTotalDirectorPaid() {
		return $this->filterMoney($this->director_paid);
	}
	function getTotalRegistrantsPaidFee() {
		return $this->filterMoney($this->fees-$this->director_paid);
	}
	function getAveragePayment() {
		return $this->filterMoney($this->total_money/$this->total_registrants);
	}
	function getRaces() {
		return $this->race_ids;
	}
	function getAgeRangeCount($low = null, $high = null) {
		if(!$this->ages_sorted)
			$this->ages_sorted = sort($this->ages, SORT_NUMERIC);

		if($low == null && $high == null) {
			return count($this->ages);
		} else if($low == null) {
			$ages = array();
			for($i = 0; $i < count($this->ages); $i++) {
				if($this->ages[$i] <= $high)
					$ages[] = $this->ages[$i];
				else
					break;
			}
		} else if($high == null) {
			$ages = array();
			for($i = 0; $i < count($this->ages); $i++) {
				if($this->ages[$i] >= $low)
					$ages[] = $this->ages[$i];
			}
		} else {
			$ages = array();
			for($i = 0; $i < count($this->ages); $i++) {
				$age = $this->ages[$i];
				if($age >= $low && $age <= $high)
					$ages[] = $age;
				else if($age > $high)
					break;
			}
		}

		return count($ages);
	}
	function getYoungest() {
		return $this->youngest;
	}
	function getOldest() {
		return $this->oldest;
	}
	function getAgeAverage() {
		return round(array_sum($this->ages)/$this->total_registrants, 1);
	}
	function getAgeMedian() {
		if($this->hasAgeMedian())
			return $this->ages[((int)$this->total_registrants/2)];
		return null;
	}
	function getAdultShirts() {
		return $this->adult_shirts;
	}
	function getAdultShirtTotal() {
		return array_sum($this->adult_shirts);
	}
	function getYouthShirts() {
		return $this->youth_shirts;
	}
	function getYouthShirtTotal() {
		return array_sum($this->youth_shirts);
	}
	function getShirtTotal() {
		return $this->getAdultShirtTotal() + $this->getYouthShirtTotal();
	}
	function getNoShirtTotal() {
		return $this->no_shirts;
	}
	function getRegIntervalDays() {
		$first = $this->earliest;
		$last = $this->latest;
		$interval = mktime(0, 0, 0, date('m', $last), date('d', $last), date('Y', $last))
			- mktime(0, 0, 0, date('m', $first), date('d', $first), date('Y', $first));

		$days = $interval/86400;
		return $days == 0 ? 1:$days;
	}
	function getEarliest($format = null) {
		if($format == null)
			return $this->earliest;
		return date($format, $this->earliest);
	}
	function getLatest($format = null) {
		if($format == null)
			return $this->latest;
		return date($format, $this->latest);
	}
	function getPeakDaysRegCount() {
		return max($this->registered_days);
	}
	function getPeakHoursRegCount() {
		return max($this->registered_hours);
	}
	function getPeakDays($format = null) {
		$time_array = array_keys($this->registered_days, $this->getPeakDaysRegCount());

		if($format == null)
			return $time_array;

		$date_array = array();
		foreach($time_array as $time) {
			$date_array[] = date($format, $time);
		}
		return $date_array;
	}
	function getPeakHours($format = null) {
		$hour_array = array_keys($this->registered_hours, $this->getPeakHoursRegCount());

		$formatted = array();
		foreach($hour_array as $hour) {
			$time = strtotime($hour .':00:00');

			if($format == null)
				$formatted[] = $time;
			else
				$formatted[] = date($format, $time);
		}

		return $formatted;
	}
	function getAverageRegsPerDay() {
		return round($this->total_registrants/$this->getRegIntervalDays(), 1);
	}
		// end get methods

	/* set methods */
	function setMoneyFilter($set) {
		$this->filter_money = $set;
	}
		// end set methods

	

	/*
	 * Public methods
	 */
	function addRegistrant($new_regs) {
		if(!is_array($new_regs))
			$new_regs = array($new_regs);

		foreach($new_regs as $new_reg) {
			$this->total_registrants++;

			if($new_reg->isMale())
				$this->male++;

			$this->addMoneyStats($new_reg);
			$this->addRaceStats($new_reg);
			$this->addAgeStats($new_reg);
			$this->addShirtStats($new_reg);
			$this->addRegistrationStats($new_reg);
		}
	}

	function hasAgeMedian() {
		return $this->total_registrants % 2 > 0;
	}

	function sortShirts() {
		global $youth_shirt_sizes, $adult_shirt_sizes;
		if($this->shirts_sorted)
			return;
		else
			$this->shirts_sorted = true;

		$youth = array();
		foreach($youth_shirt_sizes as $y_size) {
			$key = (string)$y_size;
			if(isset($this->youth_shirts[$key]))
				$youth[$key] = $this->youth_shirts[$key];
		}
		$this->youth_shirts = $youth;

		$adult = array();
		foreach($adult_shirt_sizes as $a_size) {
			$key = (string)$a_size;
			if(isset($this->adult_shirts[$key]))
				$adult[$key] = $this->adult_shirts[$key];
		}
		$this->adult_shirts = $adult;
	}
		// end public methods



	/*
	 * Private methods
	 */

	protected function addRegistrationStats($reg) {
		$day_timestamp = $reg->getOnlyPaidDate();
		$completed = $reg->getPaidDate();
		$hour = date('H', $completed);

		$key = (string)$day_timestamp;
		if(!array_key_exists($key, $this->registered_days))
			$this->registered_days[$key] = 1;
		else
			$this->registered_days[$key]++;

		if($completed < $this->earliest || $this->earliest == null)
			$this->earliest = $completed;
		if($completed > $this->latest)
			$this->latest = $completed;

		$key = (string)$hour;
		if(!array_key_exists($key, $this->registered_hours))
			$this->registered_hours[$key] = 1;
		else
			$this->registered_hours[$key]++;
	}

	protected function addShirtStats($reg) {
		if(!$reg->hasShirt()) {
			$this->no_shirts++;
			return;
		}
		else if($reg->isAdultSize())
			$shirts =& $this->adult_shirts;
		else
			$shirts =& $this->youth_shirts;

		$this->shirts_sorted = false;

		$key = $reg->getShirtSize();
		if(!array_key_exists($key, $shirts))
			$shirts[$key] = 1;
		else
			$shirts[$key]++;
	}

	protected function addAgeStats($reg) {
		$this->ages_sorted = false;
		$age = $reg->getAge();
		$this->ages[] = $age;

		if($age < $this->youngest || $this->youngest == null)
			$this->youngest = $age;

		if($age > $this->oldest)
			$this->oldest = $age;
	}

	private function addRaceStats($reg) {
		$key = (string)$reg->getRaceId();
		if(!array_key_exists($key, $this->race_ids))
			$this->race_ids[$key] = 1;
		else
			$this->race_ids[$key]++;
	}

	protected function addMoneyStats($reg) {
		$this->total_money			+= $reg->getTotalPaid();
		$this->director_receives	+= $reg->getPaid();
		$this->discounts			+= $reg->getDiscounts();
		$this->fees					+= $reg->getPaidFee();
		$this->director_paid		+= ($reg->directorPaid() ? $reg->getPaidFee():0);
	}

	private function filterMoney($amt) {
		global $util;
		
		if($this->filter_money)
			return $util->money($amt);

		return $amt;
	}

		// end private methods
}
?>
