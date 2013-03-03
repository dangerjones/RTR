<?php
/**
 * Handles a submission of a new race
 */

class NewRace {
	protected $id;
	protected $numErr;
	protected $err;
	protected $e_date;

	protected $r_name, $r_dist, $r_dist_meas, $r_time, $r_time_isam, $r_location;
	protected $r_dayof, $r_prereg, $r_early, $r_early_date, $is_free_race;

	function  __construct($raceInfo, $id) {
		$this->id = $id;
		$this->err = array();

		$this->setVariables($raceInfo);
		$this->errorCheck();
	}

	function getName() {
		return $this->r_name;
	}
	function getDistance() {
		return $this->r_dist;
	}
	function getDistanceMeasure() {
		return $this->r_dist_meas;
	}
	function getTime() {
		return $this->r_time;
	}
	function isAM() {
		return $this->r_time_isam;
	}
	function getLocation() {
		return $this->r_location;
	}
	function getDayOfCost() {
		return $this->r_dayof;
	}
	function getPreRegCost() {
		return $this->r_prereg;
	}
	function getEarlyCost() {
		return $this->r_early;
	}
	function getEarlyDate() {
		return $this->r_early_date;
	}
	function getErrNum() {
		return count($this->err);
	}
		// end get methods

	/*
	 * Public functions
	 */

	function errorCheck() {
		global $race_distance_measurements;

		if(empty($this->r_name))
			$this->addErr('Name required');

		if(empty($this->r_dist))
			$this->addErr('Distance required');
		else if(!is_numeric($this->r_dist))
			$this->addErr('Invalid distance');

		if(!isset($race_distance_measurements[$this->r_dist_meas]))
			$this->addErr('Invalid distance measurement');

		if(!$this->r_time)
			$this->addErr('Invalid starting time');

		if(empty($this->r_location))
			$this->addErr('Location required');

		if(!$this->is_free_race)
			$this->priceErrCheck();
	}

	function getErrList() {
		$out = '<ul>';
		foreach($this->err as $e) {
			$out .= '<li>'. $e .'</li>';
		}
		$out .= '</ul>';
		return $out;
	}

	function addToDB($event_id) {
		global $sql, $race_distance_measurements;

		$distance = $this->r_dist .' '. $race_distance_measurements[$this->r_dist_meas];
		$early_date = empty($this->r_early_date) ? '':date('c', $this->r_early_date);
		$start_time = strftime('%T', $this->r_time + ($this->r_time_isam ? 0:(3600*12)));

		$query = 'INSERT INTO '. TABLE_RACES .' (event_id, name, distance, cost_early, early_date, cost_pre_reg, cost_day_of, start_time, location)'.
					" VALUES ($event_id, '$this->r_name', '$distance', '$this->r_early', '$early_date', '$this->r_prereg', '$this->r_dayof', '$start_time', '$this->r_location')";

		return $sql->q($query);
	}

	private function priceErrCheck() {
		if(!empty($this->r_dayof) && !is_numeric($this->r_dayof))
			$this->addErr('Race day price is invalid. Only numbers allowed');

		if(empty($this->r_prereg))
			$this->addErr('Pre-registration price required');
		else if(!is_numeric($this->r_prereg))
			$this->addErr('Invalid pre-registration price. Only numbers allowed');

		if(!empty($this->r_early) && !is_numeric($this->r_early))
			$this->addErr('Invalid early price. Only numbers allowed');
		else if(!empty($this->r_early))
			$this->earlyDateErrCheck();
		else if(!empty($this->r_early_date))
			$this->addErr('Enter an early bird price or remove the early date');
	}

	private function setVariables($r) {
		$this->e_date			= $r['event-date'];
		$this->r_name			= $r['race-name'];
		$this->r_dist			= $r['race-distance'];
		$this->r_dist_meas		= $r['race-dist-measure'];
		$this->r_time			= $r['race-time'];
		$this->r_time_isam		= $r['race-time-isam'];
		$this->r_location		= $r['race-location'];
		$this->is_free_race		= $r['race-free'];
		$this->r_dayof			= $r['race-dayof'];
		$this->r_prereg			= $r['race-prereg'];
		$this->r_early			= $r['race-early'];
		$this->r_early_date		= $r['race-early-date'];
	}

	private function earlyDateErrCheck() {
		if($this->r_early_date === '')
			$this->addErr('Early price\'s end date is required with the price');
		else if(!$this->r_early_date)
			$this->addErr('Invalid early end date.');
		else if($this->r_early_date >= $this->e_date)
			$this->addErr('Early price must be before event date');
	}

	protected function addErr($err) {
		$this->err[] = 'Race '. $this->id .': '. $err;
	}
}
?>
