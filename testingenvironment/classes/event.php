<?php
require_once 'eventhandler.php';
require_once 'race.php';
require_once 'registrant.php';
require_once 'coupon.php';

/*
 * Holds all the information for a single event and all its races
 */
class Event {
    private $id;
    private $user_id;

    private $name, $month, $day, $year, $type, $desc, $contact_info, $youth_sizes;
	private $adult_sizes, $allow_no_shirt, $no_shirt_disc, $reg_here, $grant_access;
	private $user_pays_fee, $banner, $entry_form, $course_map, $status, $races = array();

	private $reg_options = array();
	private $questions = array(), $coupons = array(), $permalink;

	private $all_registrants = array(), $registrants_by_race = array(), $registrants_by_race_paid = array(),
			$registrants_by_race_unpaid = array(), $registrant_adult_shirts = array(), $registrant_youth_shirts = array();

	private $event_details_set = false;

    function __construct($id) {
		if(is_array($id)) {
			$event_info =& $id;
			$this->setProperties($event_info);

		} else {
			$this->id = (int)$id;
			$this->setVariables();

			$this->retrieveRaces();

			$this->setRegistrants();
		}
    }

	/* get methods */
	function getId() {
		return $this->id;
	}
	function getUserId() {
		return $this->user_id;
	}
	function getName() {
		return $this->name;
	}
	function getDecodedName() {
		return html_entity_decode($this->name, ENT_QUOTES);
	}
	function getPermalink($name_only = false) {
		if($name_only)
			return $this->permalink;
		return $this->year .'/'. $this->permalink;
	}
	function getPermalinkURL() {
		return BASEURL .'events/'.  $this->getPermalink();
	}
	function getfDesc() {
		$add_paragraphs = str_replace(array('&#13;&#10;&#13;&#10;', '&#10;&#10;'), '</p><p class="event-description">', $this->desc);
		$add_breaks = str_replace(array('&#13;&#10;', '&#10;'), '<br />', $add_paragraphs);
		return $add_breaks;
	}
	function getType() {
		return $this->type;
	}
	function getYouthShirts() {
		return $this->youth_sizes;
	}
	function getAdultShirts() {
		return $this->adult_sizes;
	}
	function getRaces() {
		return $this->races;
	}
	function getRacesWithRegs($type = 'all', $user = false, $filter_passed = false) {
		$reg_type = $this->getRegType($type);

		$races = array();
		foreach($this->races as $race) {
			$regs = $reg_type[$race->getId()];
			if($regs != null) {
				foreach($regs as $reg) {
					if(($user === false || $reg->getUserId() == $user) && !in_array($race, $races)) {
						if(!$filter_passed)
							$races[] = $race;
						else if(!$reg->getEvent()->hasPassed())
							$races[] = $race;
					}
				}
			}
		}
		return $races;
	}
	function getRace($r_id) {
		foreach($this->races as $r) {
			if($r->getId() == $r_id)
				return $r;
		}
		return null;
	}
	function getDate($format = null) {
		if($format == null)
			return mktime(0, 0, 0, $this->month, $this->day, $this->year);
		return date($format, mktime(0, 0, 0, $this->month, $this->day, $this->year));
	}
	function getMonth() {
		return $this->month;
	}
	function getDay() {
		return $this->day;
	}
	function getYear() {
		return $this->year;
	}
	function getDescription() {
		return $this->desc;
	}
	function getContactInfo() {
		return $this->contact_info;
	}
	function getQuestions() {
		return $this->questions;
	}
	function getQuestionIds() {
		global $sql;
		$query = 'SELECT id FROM '. TABLE_E_QUESTIONS .' WHERE event_id='. $this->id;

		return $sql->getOneColumn($query);
	}
	function getStatus() {
		return $this->status;
	}
	function getPaidRegistrants() {
		$paid = array();
		foreach($this->all_registrants as $r) {
			if($r->hasPaid())
				$paid[] = $r;
		}
		return $paid;
	}
	function getRegistrants($type = 'all', $user = false, $filter_passed = false) {
		$reg_type = $this->getRegType($type);

		if($user === false)
			return $reg_type;

		$user_regs = array();
		foreach($reg_type as $race) {
			foreach($race as $reg) {
				if($reg->getUserId() == $user) {
					if(!$filter_passed)
						$user_regs[] = $reg;
					else if(!$reg->getEvent()->hasPassed())
						$user_regs[] = $reg;
				}
			}
		}
		return $user_regs;
	}
	function getRegistrantsByRace($race_id, $type = 'all', $user = false, $filter_passed = false) {
		$reg_type = $this->getRegType($type);

		if($user === false)
			return $reg_type[$race_id];
		else {
			$user_regs = array();
			foreach($reg_type[$race_id] as $reg) {
				if($reg->getUserId() == $user) {
					if(!$filter_passed)
						$user_regs[] = $reg;
					else if(!$reg->getEvent()->hasPassed())
						$user_regs[] = $reg;
				}
			}
			return $user_regs;
		}
	}
	function getRegistrantShirts($adult_or_youth = 'a') {
		if($adult_or_youth == 'a')
			return $this->registrant_adult_shirts;
		else
			return $this->registrant_youth_shirts;
	}
	function getRegMethod($method) {
		foreach($this->reg_options as $option) {
			if($option['type'] == $method)
				return $option['info'];
		}
		return null;
	}
	function getNoShirtDiscount() {
		return $this->no_shirt_disc;
	}
	function getBanner() {
		return $this->banner;
	}
	function getEntryForm() {
		return $this->entry_form;
	}
	function getCourseMap() {
		return $this->course_map;
	}
	function getBannerFileName() {
		$pieces = explode('/', $this->banner);
		return $pieces[1];
	}
	function getEntryFormFileName() {
		$pieces = explode('/', $this->entry_form);
		return $pieces[1];
	}
	function getCourseMapFileName() {
		$pieces = explode('/', $this->course_map);
		return $pieces[1];
	}
	function getBannerAbsolutePath() {
		return UPLOADS_ABSOLUTE . $this->banner;
	}
	function getEntryFormAbsolutePath() {
		return UPLOADS_ABSOLUTE . $this->entry_form;
	}
	function getCourseMapAbsolutePath() {
		return UPLOADS_ABSOLUTE . $this->course_map;
	}
	function getBannerPath() {
		return UPLOAD_FOLDER . $this->banner;
	}
	function getEntryFormPath() {
		return UPLOAD_FOLDER . $this->entry_form;
	}
	function getCourseMapPath() {
		return UPLOAD_FOLDER . $this->course_map;
	}
	function getUserIDsWithAccess() {
		return $this->grant_access;
	}
	function getCouponId($codename, $return_disabled = false) {
		foreach($this->coupons as $c) {
			if($c->getName() == strtoupper($codename)) {
				if($return_disabled)
					return $c->getId();
				else if(!$c->isDisabled())
					return $c->getId();
				else
					break;
			}
		}
		return null;
	}
	function getCoupons() {
		return $this->coupons;
	}
	function getCoupon($codename_or_id) {
		if(is_numeric($codename_or_id))
			$is_id = true;
		else
			$is_id = false;

		foreach($this->coupons as $c) {
			if($is_id && $c->getId() == $codename_or_id)
				return $c;
			if(!$is_id && $c->getName() == strtoupper($codename_or_id))
				return $c;
		}
		return null;
	}
	function getFacebookShareLink() {
		return '<a href="http://facebook.com/share.php?u='. urlencode(BASEURL .'events/'. $this->getPermalink()) .'" target="_blank">'.
				'<img src="/img/share-facebook-small.png" alt="Share on Facebook" title="Share on Facebook" /></a>';
	}
		// end get methods



	/*
	 * Public methods
	 */
	function addRace($race_info) {
		$race = new Race($race_info);
		$this->races[] = $race;
		Race::addRaceToCache($race);
	}
	function numAdultShirts() {
		return count($this->adult_sizes);
	}
	function numYouthShirts() {
		return count($this->youth_sizes);
	}
	function numCoupons() {
		return count($this->coupons);
	}
	function numPaidRegistrants() {
		return count($this->getPaidRegistrants());
	}
	function isOwner($user_id) {
		return $user_id == $this->user_id;
	}
	function registerHere() {
		return $this->reg_here;
	}
	function hasRaceResults() {
		foreach($this->races as $r) {
			if($r->hasResults())
				return true;
		}
		return false;
	}
	function hasBanner() {
		return !empty($this->banner);
	}
	function hasContactInfo() {
		return !empty($this->contact_info);
	}
	function hasNoShirtDiscount() {
		return $this->no_shirt_disc > 0;
	}
	function hasRegOptions() {
		return count($this->reg_options) > 0;
	}
	function hasCourseMap() {
		return !empty($this->course_map);
	}
	function hasEntryForm() {
		return !empty($this->entry_form);
	}
	function hasRegistrantWithShirt($size = '', $adult_or_youth = 'a') {
		global $youth_shirt_sizes, $adult_shirt_sizes;

		foreach($this->all_registrants as $reg) {
			$shirt = $reg->getShirtSize();
			if(empty($shirt))
				continue;
			// With no parameters given, looks for a registrant with any shirt size
			if($size == '' && (in_array($shirt, $youth_shirt_sizes) || in_array($shirt, $adult_shirt_sizes)))
				return true;
			else if($adult_or_youth == 'a' && $shirt == $size && $reg->isAdultSize())
				return true;
			else if($adult_or_youth == 'y' && $shirt == $size && !$reg->isAdultSize())
				return true;
		}
		return false;
	}

	function hasRegistrantWithNoShirt() {
		foreach($this->all_registrants as $reg) {
			if($reg->getShirtSize() == '')
				return true;
		}
		return false;
	}

	function anyRegistrantHasCoupon($coupon_id) {
		foreach($this->all_registrants as $reg) {
			if($reg->hasCoupon() && $reg->getCoupon()->getId() == $coupon_id)
				return true;
		}
		return false;
	}

	function hasAccess($user_id) {
		$user = User::getUser((int)$user_id);

		if($user->getLvl() >= LVL_EMPLOYEE)
			return true;

		if($user_id == $this->user_id)
			return true;

		if(in_array($user_id, $this->grant_access))
			return true;

		return false;
	}

	function allowNoShirt() {
		return $this->allow_no_shirt;
	}

	function hasRace($r_id) {
		foreach($this->races as $r) {
			if($r->getId() == $r_id)
				return true;
		}
		return false;
	}

	function hasShirts() {
		return count($this->youth_sizes)+count($this->adult_sizes) > 0;
	}

	function hasShirt($size, $adult_or_youth = 'a') {
		if($adult_or_youth == 'a')
			$shirts = $this->adult_sizes;
		else
			$shirts =  $this->youth_sizes;

		$size = strtolower($size);
		return in_array($size, $shirts);
	}

	function hasCoupon($codename_or_id) {
		if(is_numeric($codename_or_id))
			$is_id = true;
		else
			$is_id = false;

		foreach($this->coupons as $c) {
			if($is_id && $c->getId() == $codename_or_id)
				return true;
			if(!$is_id && $c->getName() == strtoupper($codename_or_id))
				return true;
		}
		return false;
	}

	function numRaces() {
		return count($this->races);
	}

	function directorPays() {
		return $this->user_pays_fee;
	}

	function hasRegMethod($method) {
		foreach($this->reg_options as $option) {
			if($option['type'] == $method)
				return true;
		}
		return false;
	}

	function hasQuestions() {
		return $this->numQuestions() > 0;
	}

	function hasQuestion($q_id) {
		$q_ids = $this->getQuestionIds();
		return in_array($q_id, $q_ids);
	}

	function numQuestions() {
		return count($this->questions);
	}

	function hasPassed() {
		$now = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		$end = strtotime($this->getDate('M-d-Y'));

		return $now > $end;
	}

	function hasRegistrants($type = 'all', $user = false, $filter_passed = false) {
		$reg_type = $this->getRegType($type);

		if($user === false) {
			$num = 0;
			foreach($reg_type as $r) {
				$num += count($r);
			}
			return $num > 0;
		} else {
			foreach($reg_type as $race) {
				foreach($race as $reg) {
					if($reg->getUserId() == $user) {
					if(!$filter_passed)
						return true;
					else if(!$reg->getEvent()->hasPassed())
						return true;
					}
				}
			}
		}
		return false;
	}

	function hasRegistrantsInRace($race_id, $type = 'all', $user = false) {
		$reg_type = $this->getRegType($type);

		if($user === false)
			return count($reg_type[$race_id]) > 0;
		else if(!isset($reg_type[$race_id]))
			return false;
		else {
			foreach($reg_type[$race_id] as $reg) {
				if($reg->getUserId() == $user)
					return true;
			}
			return false;
		}
	}

	function registrationAllowed() {
		return $this->reg_here == 1;
	}

	function canRegister() {
		$expires = mktime(-7, 0, 0, $this->month, $this->day, $this->year);
		
		return time() < $expires;
	}

    function retrieveRaces() {
        global $sql;

        $query = "SELECT id FROM ". TABLE_RACES ." WHERE event_id='$this->id' ORDER BY id";
        $races = $sql->getAssoc($query);

        foreach($races as $race) {
            $this->races[] = new Race($race['id']);
        }
    }

    function formattedAsList() {
        $dayId = $this->month .'-'. $this->day;
		if($this->status == ESTATUS_WAITING)
			$format = 'n/j/y';
        else
			$format = 'D j';
		$dateF = date($format, mktime(0, 0, 0, $this->month, $this->day, $this->year));

        $output =	'<li class="'. $dayId .' event"><span class="e-date">' .$dateF. '</span>';
		$output .=	'<span class="e-title">'. $this->name .'</span>';
		if($this->reg_here || $this->hasRaceResults()) {
			$output .= '<span class="extra">';

			if($this->reg_here && !$this->hasPassed())
				$output .= '<a href="/register/'. $this->getPermalink() .'"><img src="/img/document-icon.png" alt="Register online" title="Register online" /></a>';
			if($this->hasRaceResults())
				$output .= $this->raceResultsImage();

			$output .= '</span>';
		}
			
        $output .=	'<div class="content" id="eventid-'. $this->id .'">';
        $output .=	$this->formattedContent();
        $output .=	'</div>';
		$output .=	$this->listExtras();
        $output .=	'</li>'. "\n";

        return $output;
    }

    function formattedContent() {
		global $user;
        $fDate = date('l, M jS', mktime(0, 0, 0, $this->month, $this->day, $this->year));

        // Event info
        $output = '';
        if(!empty($this->banner)) {
			$output .= '<div class="banner-wrap">';
			$output .= '<img src="/uploads/image.php?image='. 
				urlencode($this->getBannerPath()) .'&width='.
				BANNER_MAX_WIDTH .'&height='. BANNER_MAX_HEIGHT .'" />';
			$output .= '</div>';
		}
        $output .= '<p class="event-date">' . $fDate .' ('. $this->type .')';

		if($this->hasRaceResults())
			$output .= $this->raceResultsImage();

		$output .= '</p>';
        $output .= '<p class="event-description">'. $this->getfDesc() .'</p>';

        if(!empty($this->contact_info))
            $output .= '<p>Contact Info: '. $this->contact_info .'</p>'; 

		// Shirt sizes
		if($this->hasShirts()) {
			$output .= '<p>Available shirt sizes: ';
			if(count($this->youth_sizes) > 0)
					$output .= '<strong>Youth - </strong> ('. strtoupper(implode(', ', $this->youth_sizes)) .') ';
			if(count($this->adult_sizes) > 0)
					$output .= '<strong>Adult - </strong> ('. strtoupper(implode(', ', $this->adult_sizes)) .')';
			$output .= '</p>';
		} else {
			$output .= '<p>No shirts available</p>';
		}

		if($this->no_shirt_disc > 0)
			$output .= '<p>No-shirt discount: $'. $this->no_shirt_disc .'</p>';

        //Race info
        $output .= '<div class="event-races">';
        foreach($this->races as $race) {
            $output .= $race->formattedContent();
        }
        $output .= '</div>';

		// Registration options
		if(count($this->reg_options) > 0) {
			$output .= '<p>Registration options:</p>';
			$output .= $this->regOptionsToString();
		}

		// Bottom buttons
		$output .=	'<div class="event-buttons">';

		if(!empty($this->course_map))
			$output .= '<a href="'. $this->getCourseMapPath() .'" '.
				'class="course-map" title="Course Map">View course map</a> ';

		if(!empty($this->entry_form))
			$output .= '<a href="'. $this->getEntryFormPath() .'" target="_blank" '.
				'>Downloadable Entry Form</a> ';

		if($this->status == ESTATUS_OK) {
			if($this->reg_here && !$this->hasPassed())
				$output .= '<a class="register-button" href="/register/'. $this->getPermalink() .'">Register online</a> ';
		}
		
		$output .= '<a class="close-dialog">Close</a>';
		
		// Administrative controls
		if($this->hasAccess($user->getId())) {
			$output .= '<h4>Administrative controls</h4>';
			$output .= '<a href="/edit-event/'. $this->getPermalink() .'">Edit event</a> ';
			$output .= '<a href="/registrants/'. $this->getPermalink() .'">';
			$output .= 'View registrants ('. $this->numPaidRegistrants() .')</a> ';
		}
		$output .=	'</div>';
		
		$output .= '<div id="share-link">Share: ';
		$output .= $this->getFacebookShareLink();
		$output .= ' <a href="'. $this->getPermalinkURL() .'" title="Event\'s page">';
		$output .= $this->getPermalinkURL() .'</a>';
		$output .= '</div>';

        return $output;
    }

	function regOptionsToString() {
		$output = '<ul class="list-info">';

		foreach($this->reg_options as $o) {
			switch($o['type']) {
				case EREG_METHOD_WEB:
					$output .= '<li><span class="list-title">Website:</span><a href="'. $o['info'] .'" target="_blank">'.
								$o['info'] .'</a></li>';
					break;
				case EREG_METHOD_ADDR1:
					$output .= '<li><span class="list-title">Address:</span>'. $o['info'] .'</li>';
					break;
				case EREG_METHOD_ADDR2:
					$output .= '<li><span class="list-title">Address 2:</span>'. $o['info'] .'</li>';
					break;
				default:
			}
		}

		$output .= '</ul>';

		return $output;
	}

	function raceResultsImage() {
		return '<img src="/img/checkered-flag-icon.png" class="event-race-results point" alt="Race results" title="View race results" rel="'. $this->id .'" />';
	}  // end public methods



	/*
	 * Private methods
	 */

	private function getRegType($type) {
		if($type == 'paid')
			return $this->registrants_by_race_paid;
		else if($type == 'unpaid')
			return $this->registrants_by_race_unpaid;
		else
			return $this->registrants_by_race;
	}

	private function listExtras() {
		global $util;
		$output = '';

		if($this->status == ESTATUS_WAITING || $this->status == ESTATUS_DENY) {
			$output =	'<span class="extra">';
			$output .= '(User: '. $util->userEmail($this->user_id) .')';
			$output .= '</span></li><li class="admin-control">'.
						$this->adminForm('Approve', '<input type="hidden" name="e_id" value="'. $this->id .'" />');
			if($this->status != ESTATUS_DENY)
				$output .= $this->adminForm('Deny', '<input type="hidden" name="e_id" value="'. $this->id .'" />');
			else
				$output .= $this->adminForm('Delete', '<input type="hidden" name="e_id" value="'. $this->id .'" />');

		$output .=	'</li>';
		}

		return $output;
	}

	private function adminForm($submit, $form = '') {
		$output = '<form action="/admin-panel/adminProcesses.php" method="post">';
		$output .= $form .'<input type="submit" name="submit" value="'. $submit .'" /></form>';
		return $output;
	}

	private function setRegistrants() {
		global $sql;

		$query = 'SELECT id,race_id FROM '. TABLE_E_REGISTRANTS .' WHERE event_id='. $this->id;
		$regs = $sql->getAssoc($query);
		$this->all_registrants = array();
		$this->registrant_adult_shirts = array();
		$this->registrant_youth_shirts = array();
		$this->registrants_by_race = array();
		$this->registrants_by_race_paid = array();
		$this->registrants_by_race_unpaid = array();

		foreach($regs as $r) {
			$reg = new Registrant($r['id'], $this);
			$this->registrants_by_race[$r['race_id']][] = $reg;
			if($reg->hasPaid())
				$this->registrants_by_race_paid[$r['race_id']][] = $reg;
			else
				$this->registrants_by_race_unpaid[$r['race_id']][] = $reg;

			$this->all_registrants[] = $reg;

			// Save unique shirt sizes that appears amongst the registrants
			$size = $reg->getShirtSize();
			if(!empty($size)) {
				if(!in_array($size, $this->registrant_adult_shirts) && $reg->isAdultSize())
					$this->registrant_adult_shirts[] = $size;
				else if(!in_array($size, $this->registrant_youth_shirts) && !$reg->isAdultSize())
					$this->registrant_youth_shirts[] = $size;
			}
		}
	}

	private function setVariables() {
		global $sql;

		$query = 'SELECT * FROM '. TABLE_EVENTS .' WHERE id='. $this->id;
		$eventInfo = $sql->getAssocRow($query);

        $this->user_id			= $eventInfo['user_id'];
        $this->name				= $eventInfo['name'];
        $this->month			= (int)$eventInfo['month'];
        $this->day				= (int)$eventInfo['day'];
        $this->year				= (int)$eventInfo['year'];
		$this->permalink		= $eventInfo['permalink'];
        $this->type				= $eventInfo['type'];
        $this->desc				= $eventInfo['description'];
        $this->contact_info		= $eventInfo['contact_info'];

		$this->youth_sizes		= !empty($eventInfo['youth_shirt_sizes']) ?
				explode(',', $eventInfo['youth_shirt_sizes']):array();

		$this->adult_sizes		= !empty($eventInfo['adult_shirt_sizes']) ?
				explode(',', $eventInfo['adult_shirt_sizes']):array();

		$this->allow_no_shirt	= $eventInfo['allow_no_shirt'] == 1;
		$this->no_shirt_disc	= $eventInfo['no_shirt_discount'];
        $this->reg_here			= $eventInfo['reg_here'];
        $this->status			= $eventInfo['status'];

		$this->user_pays_fee	= $eventInfo['user_pays_fee'] == 1;
		$this->banner			= $eventInfo['banner'];
		$this->entry_form		= $eventInfo['entry_form'];
		$this->course_map		= $eventInfo['course_map'];

		$this->grant_access		= empty($eventInfo['grant_access']) ? array():
									explode(';', $eventInfo['grant_access']);

		$query = 'SELECT type, info FROM '. TABLE_REG_METHODS .'
			WHERE info!="" AND event_id='. $this->id;

		$this->reg_options = $sql->getAssoc($query);

		$query = 'SELECT question,answers FROM '. TABLE_E_QUESTIONS .' WHERE event_id='. $this->id;
		$results = $sql->getAssoc($query);
		$this->questions = array();
		foreach($results as $q) {
			$single = array($q['question']);
			foreach(explode(DELIMITER, $q['answers']) as $a) {
				$single[] = $a;
			}

			$this->questions[] = $single;
		}

		$query = 'SELECT id FROM '. TABLE_E_COUPONS .' WHERE event_id='. $this->id;
		$results = $sql->getOneColumn($query);
		$this->coupons = array();
		foreach($results as $c_id) {
			$this->coupons[] = new Coupon($c_id);
		}
	}

	/*
	 * First call sets the event details and the first race. If there are
	 * any more calls (meaning more race info), then the extra race data is used
	 * and set to make more races. Event details set once only.
	 */
	function setProperties($event_info) {

		if(!$this->event_details_set) {
			$this->setEventDetails($event_info);
			$this->event_details_set = true;
		}

		$race_info = $this->makeRaceArray($event_info);
		
		$this->addRace($race_info);
	}

	function addRegMethod($method_details) {
		unset($method_details['event_id']);
		
		$this->reg_options[] = $method_details;
	}

	function addQuestion($question_details) {
		$entry = array($question_details['question']);
		foreach(explode(DELIMITER, $question_details['answers']) as $a) {
			$entry[] = $a;
		}

		$this->questions[] = $entry;
	}

	function addCoupon($coupon_details) {
		$this->coupons[] = new Coupon($coupon_details);
	}

	function addRegistrant($reg_details) {
		$reg = new Registrant($reg_details);
		$this->registrants_by_race[$reg_details['race_id']][] = $reg;
		if($reg->hasPaid())
			$this->registrants_by_race_paid[$reg_details['race_id']][] = $reg;
		else
			$this->registrants_by_race_unpaid[$reg_details['race_id']][] = $reg;

		$this->all_registrants[] = $reg;

		// Save unique shirt sizes that appears amongst the registrants
		$size = $reg->getShirtSize();
		if(!empty($size)) {
			if(!in_array($size, $this->registrant_adult_shirts) && $reg->isAdultSize())
				$this->registrant_adult_shirts[] = $size;
			else if(!in_array($size, $this->registrant_youth_shirts) && !$reg->isAdultSize())
				$this->registrant_youth_shirts[] = $size;
		}

		Registrant::addRegistrant($reg);
	}

	private function setEventDetails($event_info) {
			$this->id				= (int)$event_info['id'];
			$this->user_id			= (int)$event_info['user_id'];
			$this->name				= $event_info['name'];
			$this->month			= (int)$event_info['month'];
			$this->day				= (int)$event_info['day'];
			$this->year				= (int)$event_info['year'];
			$this->permalink		= $event_info['permalink'];
			$this->type				= $event_info['type'];
			$this->desc				= $event_info['description'];
			$this->contact_info		= $event_info['contact_info'];

			$this->youth_sizes		= !empty($event_info['youth_shirt_sizes']) ?
					explode(',', $event_info['youth_shirt_sizes']):array();

			$this->adult_sizes		= !empty($event_info['adult_shirt_sizes']) ?
					explode(',', $event_info['adult_shirt_sizes']):array();

			$this->allow_no_shirt	= $event_info['allow_no_shirt'] == 1;
			$this->no_shirt_disc	= $event_info['no_shirt_discount'];
			$this->reg_here			= $event_info['reg_here'];
			$this->status			= $event_info['status'];

			$this->user_pays_fee	= $event_info['user_pays_fee'] == 1;
			$this->banner			= $event_info['banner'];
			$this->entry_form		= $event_info['entry_form'];
			$this->course_map		= $event_info['course_map'];

			$this->grant_access		= empty($event_info['grant_access']) ? array():
										explode(';', $event_info['grant_access']);
	}

	private function makeRaceArray(&$all_details) {
		$race_data = array();

		$race_data['id']				= $all_details['race_id'];
		$race_data['event_id']			= $all_details['event_id'];
		$race_data['name']				= $all_details['race_name'];
		$race_data['distance']			= $all_details['distance'];
		$race_data['cost_early']		= $all_details['cost_early'];
		$race_data['early_date']		= $all_details['early_date'];
		$race_data['cost_pre_reg']		= $all_details['cost_pre_reg'];
		$race_data['cost_day_of']		= $all_details['cost_day_of'];
		$race_data['start_time']		= $all_details['start_time'];
		$race_data['location']			= $all_details['location'];
		$race_data['num_race_results']	= $all_details['num_race_results'];

		return $race_data;
	}

}
?>