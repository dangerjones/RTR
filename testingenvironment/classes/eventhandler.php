<?php
require_once 'event.php';

class EventHandler {
	private static $event_days = array(); // Structure: array(year -> array(month-day -> array(id -> Event obj), month-day -> array()), year -> array())
	private static $all_events = array(); // Structure: array(id -> Event obj, id -> Event obj)
	private static $event_months = array(); // Structure: array(year -> array(month -> array(id -> Event obj, id -> Event obj))


    /*
    * printAllEvents()
    * Output all events into HTML
    */
    function printYearEvents($year, $month) {
        if($year == 0)
            $year = date('Y');
        if($month == 0)
            $month = date('m');
        for($i = 1; $i < 13; $i++) {
            $this->printMonthEvents($i, $year, $month);
        }
    }

    /*
    * printMonthEvents()
    * Output an entire month's events into HTML as unordered list
    * Structure: div > h2, ul > li
    */
    function printMonthEvents($month, $year, $cMonth) {
        $events			= self::getEvents($month, $year);
        $dateName		= date('F', mktime(0, 0, 0, $month, 1, $year));
		
        // Adds class to hide non-current-month events
        $hideEvent = ' class="event-wrap-';
        $hideEvent .= ($month == $cMonth) ? 'on"' : 'off"';

        echo '<div id="event-wrap-'. $month .'-'. $year .'"'. $hideEvent .'>'. "\n";
        echo '<h2>'. $dateName .' '. $year .'</h2>';
        if(!empty($events)) {
            echo '<ul class="event-list">'. "\n";
			foreach($events as $event) {
                echo $event->formattedAsList();
            }
            echo '</ul>'. "\n";
        } else {
            echo '
            <ul class="event-list">
                <li><strong>No events currently listed</strong></li>
            </ul>
            ';
        }
        echo '</div>'. "\n";
    }

    /*
    * printQuickLinks()
    * Output quicklinks for calendar navigation
    */
    function printQuickLinks($year, $cMonth) {
        if($year == 0)
            $year = date('Y');

        $prev = $year-1;
        $next = $year+1;
        echo '<a id="'. $prev .'-change-year" class="change-year" href ="?month='. $cMonth .'&year='. $prev .'">Prev Year</a>';
        for($i = 1; $i < 13; $i++) {
            echo '<a class="'. $i .'-'. $year .'" id="quickmonth-'. $i .'-'. $year .'" '.
					'href ="?month='. $i .'&year='. $year .'">'.
            date('M', mktime(0, 0, 0, $i, 1, $year)) .'</a>';
        }
        echo '<a id="'. $next .'-change-year" class="change-year" href ="?month='. $cMonth .'&year='. $next .'">Next Year</a>';
    }

    /*
    * isEvent()
    * Returns boolean true if specified day has an event
    */
    function isEvent($month, $day, $year) {
        global $sql;
		static $called = false;
		$month		= (int)$month;
		$day		= (int)$day;
		$year		= (int)$year;
		$hash		= $month . $day;

		if(!$called) {
			self::cacheEvents('e.status='. ESTATUS_OK .' AND year='. $year);
			$called = true;
		}

		return isset(self::$event_days[$year][$hash]);
    }

	/*
	 * Static Functions
	 */

    /*
    * getEvents()
    * Returns an array of Event objects for the specified month/year
    */
	static function getEvents($month, $year) {
		$month	= (int)$month;
		$year	= (int)$year;

		$events =& self::$event_months[$year][$month];

		return $events;
	}

	static function hasEvent($e_id) {
		return is_object(self::$all_events[$e_id]);
	}

	static function getEvent($e_id) {
		if(!is_object(self::$all_events[$e_id]))
			self::cacheEvents('e.id="'. $e_id .'"');

		return self::$all_events[$e_id];
	}

	static function cacheAndGetEventByPermalink($permalink, $year) {
		global $sql;
		$safePermalink = $sql->safeString($permalink);
		$year = (int)$year;

		$events = self::cacheEvents('e.year='. $year .' AND e.permalink="'. $safePermalink .'"');

		return $events[0];
	}

	private static function cacheEvents($where, $limit = null) {
		global $sql;

		if($limit !== null)
			$limit = 'LIMIT '. $limit;

		$query = '
			SELECT
				e.*,
				r.id race_id,
				r.event_id,
				r.name race_name,
				r.distance,
				r.cost_early,
				r.early_date,
				r.cost_pre_reg,
				r.cost_day_of,
				r.start_time,
				r.location,
				(SELECT COUNT(id) FROM '. TABLE_RACE_RESULTS .' rr WHERE e.id = rr.event_id) num_race_results
			FROM
				'. TABLE_EVENTS .' e LEFT JOIN '. TABLE_RACES .' r
			ON
				e.id = r.event_id
			WHERE
				'. $where .'
			ORDER BY
				e.year, e.month, e.day
			'. $limit;

		$events = $sql->getAssoc($query);
		$cached_events = array();

		/*
		 * Load from events/races table
		 */
		foreach($events as $e) {
			if(self::hasEvent($e['id'])) {
				$event_obj	= self::getEvent($e['id'])	;
				$event_obj->setProperties($e);
				continue;

			} else
				$event_obj	= new Event($e);

			$year			= $e['year'];
			$days_hash		= $e['month'] . $e['day'];
			$months_hash	= $e['month'];

			// Organize events into different arrays for easy access

			self::$event_days[$year][$days_hash][$e['id']]		= $event_obj;
			self::$all_events[$e['id']]							= $event_obj;
			self::$event_months[$year][$months_hash][$e['id']]	= $event_obj;

			$cached_events[] = $event_obj;
		}

		self::addCoupons($where);
		self::addRegMethods($where);
		self::addQuestions($where);
		self::addRegistrants($where);

		return $cached_events;
	}

	private static function addRegistrants($where) {
		global $sql;
		$query = '
			SELECT
				r.*
			FROM
				'. TABLE_EVENTS .' e,
				'. TABLE_E_REGISTRANTS .' r
			WHERE
				e.id = r.event_id AND '. $where;

		$registrants = $sql->getAssoc($query);

		// Set registrants for events
		foreach($registrants as $reg) {
			$event = self::getEvent($reg['event_id']);
			$event->addRegistrant($reg);
		}
		
	}

	private static function addCoupons($where) {
		global $sql;
		$query = '
			SELECT
				c.id,
				c.event_id,
				c.code,
				c.amount,
				c.disabled
			FROM
				'. TABLE_EVENTS .' e,
				'. TABLE_E_COUPONS .' c
			WHERE
				e.id = c.event_id AND '. $where;

		$coupons = $sql->getAssoc($query);

		// Set coupons for events
		foreach($coupons as $c) {
			$event = self::getEvent($c['event_id']);
			$event->addCoupon($c);
		}
	}

	private static function addQuestions($where) {
		global $sql;
		$query = '
			SELECT
				q.event_id, 
				q.question, 
				q.answers
			FROM
				'. TABLE_EVENTS .' e,
				'. TABLE_E_QUESTIONS .' q
			WHERE
				e.id = q.event_id AND '. $where;

		$q_and_a = $sql->getAssoc($query);

		// Set questions and answers for events
		foreach($q_and_a as $q_a) {
			$event = self::getEvent($q_a['event_id']);
			$event->addQuestion($q_a);
		}
	}

	private static function addRegMethods($where) {
		global $sql;
		$query = '
			SELECT
				reg.event_id,
				reg.type, 
				reg.info
			FROM
				'. TABLE_EVENTS .' e,
				'. TABLE_REG_METHODS .' reg
			WHERE
				e.id = reg.event_id AND '. $where .' AND reg.info != ""';

		$reg_methods = $sql->getAssoc($query);

		// Set reg methods for events
		foreach($reg_methods as $method) {
			$event = self::getEvent($method['event_id']);
			$event->addRegMethod($method);
		}
	}

}
?>