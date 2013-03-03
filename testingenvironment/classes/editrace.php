<?php
require_once ROOT .'classes/event.php';

class EditRace extends NewRace {
	private $event_id;

	function  __construct($post) {
		$this->id = (int)$post['race_id'];
		$this->event_id = (int)$post['e_id'];

		$this->setProperties($post);
	}

	/* get methods */
	function getId() {
		return $this->id;
	}
	function getEventId() {
		return $this->event_id;
	}
		// end get methods

	/*
	 * Public methods
	 */
	function getErrList() {
		$out = '<ul>';

		foreach($this->err as $e) {
			$error = explode(': ', $e);
			$out .= '<li>'. $error[count($error)-1] .'</li>';
		}

		$out .= '</ul>';
		return $out;
	}

	function updateDB() {
		global $sql, $race_distance_measurements;
		
		$this->prepareForDB();
		$distance = $this->r_dist .' '. $race_distance_measurements[$this->r_dist_meas];
		$early_date = empty($this->r_early_date) ? '':date('c', $this->r_early_date);
		$start_time = strftime('%T', $this->r_time + ($this->r_time_isam ? 0:(3600*12)));

		$query = 'UPDATE '. TABLE_RACES ." SET name='$this->r_name', distance='$distance',
			cost_early='$this->r_early', early_date='$early_date', cost_pre_reg='$this->r_prereg',
			cost_day_of='$this->r_dayof', start_time='$start_time', location='$this->r_location'
			WHERE id=". $this->id;
		
		return $sql->q($query);
	}

	function deleteSelf() {
		global $sql;
		$query = 'DELETE FROM '. TABLE_RACES .' WHERE id='. $this->id;

		return $sql->q($query);
	}

	function prepareForDB() {
		global $sql;
		$this->r_name			= $sql->safeString($this->r_name);
		$this->r_dist			= (int)$this->r_dist;
		$this->r_location		= $sql->safeString($this->r_location);
	}


	/*
	 * Private methods
	 */

	private function setProperties($post) {
		$event = new Event($this->event_id);

		$this->e_date			= $event->getDate();
		$this->r_name			= $post['edit_race_name'];
		$this->r_dist			= $post['edit_race_distance'];
		$this->r_dist_meas		= (int)$post['edit_race_measure'];
		$this->r_time			= strtotime($post['edit_race_time']);
		$this->r_time_isam		= $post['edit_race_isam'] == 'true';
		$this->r_location		= $post['edit_race_location'];
		$this->is_free_race		= $post['edit_race_free'] == 'true';

		if(!$this->is_free_race) {
			$this->r_dayof			= $post['edit_race_raceday'];
			$this->r_prereg			= $post['edit_race_prereg'];
			$this->r_early			= $post['edit_race_early'];
			$this->r_early_date		= !empty($post['edit_race_early_date']) ?
						strtotime($post['edit_race_early_date']) : $post['edit_race_early_date'];
		}
	}
}
?>
