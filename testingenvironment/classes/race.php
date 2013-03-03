<?php
/*
 * An event's race
 */

class Race {
    private $id;
    private $event_id;
    private $name;
    private $distance;
    private $cost_early;
    private $early_date;
    private $cost_pre_reg;
    private $cost_day_of;
    private $start_time;
    private $location;
	private $has_race_results;

	static private $all_races = array(); // Structure: array(id -> Race obj, id -> Race obj)

    function  __construct($race_id) {
		if(!is_array($race_id)) {
			$this->id = (int)$race_id;
			$this->setRaceInfo();
		} else {
			$race_info =& $race_id;
			$this->setProperties($race_info);
		}
    }

	/* get methods */
	function getName() {
		return $this->name;
	}
	function getDecodedName() {
		return html_entity_decode($this->name, ENT_QUOTES);
	}
	function getId() {
		return $this->id;
	}
	function getEventId() {
		return $this->event_id;
	}
	function getPrice($type) {
		if($type == 'early')
			return $this->cost_early;
		else if($type == 'prereg')
			return $this->cost_pre_reg;
		else if($type == 'dayof')
			return $this->cost_day_of;
		else
			return null;
	}
	function getPriceByTimestamp($timestamp = 0) {
		if($timestamp == 0)
			$timestamp = time();

		$early_timestamp = strtotime($this->early_date);
		$now = mktime(0, 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp));

		if($early_timestamp > 0 && $early_timestamp >= $now)
			return $this->cost_early;
		else
			return $this->cost_pre_reg;
	}
	function getEarlyDate($format = null) {
		$timestamp = strtotime($this->early_date);
		if($timestamp < 0)
			return null;
		if($format == null)
			return $this->early_date;

		return date($format, $timestamp);
	}
	function getLocation() {
		return $this->location;
	}
	function getStartTime($format = null) {
		if($format == null)
			return $this->start_time;
		else
			return date($format, strtotime($this->start_time));
	}
	function getDistance() {
		return $this->distance;
	}
	function getDistanceInt() {
		$measure = explode(' ', $this->distance);
		return (int)$measure[0];
	}
	function getDistanceMeasure() {
		$measure = explode(' ', $this->distance);
		return $measure[count($measure)-1];
	}
	function getResults() {
		global $sql;
		$query = 'SELECT id FROM '. TABLE_RACE_RESULTS .' WHERE race_id='. $this->id;

		return $sql->getOneColumn($query);
	}
		// end get methods

	/*
	 * Public methods
	 */

	 function isAm() {
		 return $this->getStartTime('H') < 12;
	 }
	function hasResults() {
		return $this->has_race_results;
	}

		// end public methods


    private function setRaceInfo() {
        $this->setEventID();
        $this->setName();
        $this->setDistance();
        $this->setEarlyCost();
        $this->setEarlyDate();
        $this->setPreRegCost();
        $this->setDayOfCost();
        $this->setStartTime();
        $this->setLocation();
    }

    /*
     * Pull from the DB
     */
    private function setEventID() {
        $this->event_id = $this->getRaceInfoFromDB('event_id');
    }
    private function setName() {
        $this->name = $this->getRaceInfoFromDB('name');
    }
    private function setDistance() {
        $this->distance = $this->getRaceInfoFromDB('distance');
    }
    private function setEarlyCost() {
        $this->cost_early = $this->getRaceInfoFromDB('cost_early');
    }
    private function setEarlyDate() {
        $this->early_date = $this->getRaceInfoFromDB('early_date');
    }
    private function setPreRegCost() {
        $this->cost_pre_reg = $this->getRaceInfoFromDB('cost_pre_reg');
    }
    private function setDayOfCost() {
        $this->cost_day_of = $this->getRaceInfoFromDB('cost_day_of');
    }
    private function setStartTime() {
        $this->start_time = $this->getRaceInfoFromDB('start_time');
    }
    private function setLocation() {
        $this->location = $this->getRaceInfoFromDB('location');
    }

    private function getRaceInfoFromDB($field) {
        global $sql;

        $query = "SELECT $field FROM ". TABLE_RACES ." WHERE id='$this->id'";
        return $sql->getOneFieldEntry($query, $field);
    }

    function formattedContent() {
        $start = date('g:i a', strtotime($this->start_time));
        $earlyDate = date('M jS', strtotime($this->early_date));

        $output =	"<h4>". $this->name ."</h4>";
        $output .=	'<ul>';
		$output .=	'<li><span style="width: 15%;">Distance:</span>'. $this->distance;
		$output .=	'<span style="width: 20%;padding-left: 15%;">Starting time:</span>'. $start .'</li>';

		$output .=	$this->early_date == 0 ? '':
			'<li><span>Early price:</span>$'. $this->cost_early .' <em>(until '. $earlyDate .')</em></li>';

		$output .=	'<li><span>Pre registration:</span>$'. $this->cost_pre_reg .'</li>';

		$output .=	$this->cost_day_of == 0 ? '':
			'<li><span>Race day price:</span>$'. $this->cost_day_of .'</li>';

		$output .=	'<li><strong>Location:</strong> '. $this->location .'</li>';
        $output .=	'</ul>';

        return $output;
    }

	private function setProperties($race_info) {
		$this->id				= $race_info['id'];
		$this->event_id			= $race_info['event_id'];
		$this->name				= $race_info['name'];
		$this->distance			= $race_info['distance'];
		$this->cost_early		= $race_info['cost_early'];
		$this->early_date		= $race_info['early_date'];
		$this->cost_pre_reg		= $race_info['cost_pre_reg'];
		$this->cost_day_of		= $race_info['cost_day_of'];
		$this->start_time		= $race_info['start_time'];
		$this->location			= $race_info['location'];
		$this->has_race_results = $race_info['num_race_results'] > 0;
	}

	/*
	 * Static functions
	 */
	static function cacheRace($where) {
		global $sql;
		if(strlen($where) > 0)
			$where = ' WHERE '. $where;
		$query = '
			SELECT
				r.*,
				(SELECT COUNT(id) FROM '. TABLE_RACE_RESULTS .' rr WHERE r.id = rr.race_id) num_race_results
			FROM
				'. TABLE_RACES .' r '
			. $where;

		$races = $sql->getAssoc($query);

		foreach($races as $race_details) {
			self::addRaceToCache(new Race($race_details));
		}
	}

	static function addRaceToCache($race_obj) {
		self::$all_races[$race_obj->getId()] = $race_obj;
	}

	static function getRace($r_id) {
		$race =& self::$all_races[$r_id];
		
		if(!is_object($race))
			self::cacheRace('r.id="'. $r_id .'"');

		return $race;
	}
}
?>
