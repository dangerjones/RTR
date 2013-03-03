<?php
require_once 'newevent.php';

class EditEvent extends NewEvent {
	private $id;
	private $event;

	function  __construct($post, $type) {
		$this->id = (int)$post['e_id'];
		$this->event = new Event($this->id);

		switch($type) {
			case 'details':
				$this->setEventDetailProperties($post);
				break;
			case 'new_coupon':
				$this->setCouponProperties($post);
				break;
			case 'questions':

				break;
			case 'uploads':

				break;
			default:
		}
	}

	/* get methods */
	function getId() {
		return $this->id;
	}

	/*
	 * Public methods
	 */
	function eventDetailsErrorCheck($event) {
		global $event_types;

		if(empty($this->e_name))
			$this->addErr('Name is required');

		$this->e_type = (int)$this->e_type;
		if(!array_key_exists($this->e_type, $event_types))
			$this->addErr('Invalid event type');

		$this->e_date = strtotime($this->e_date);
		if(!$this->e_date)
			$this->addErr('Invalid event date');
		else if($this->e_date <= time())
			$this->addErr('Date must be in the future');

		if(empty($this->e_desc))
			$this->addErr('Description is required');

		if($this->e_date)
			$this->permalinkErrCheck($event);

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

	}

	// Update DB
	function updateEventDetails() {
		global $util, $sql;
		$this->makeSQLsafe();
		$type			= $util->getEventType($this->e_type);
		$month			= date('m', $this->e_date);
		$day			= date('d', $this->e_date);
		$year			= date('Y', $this->e_date);

		// Keep registrant shirt sizes
		$this->e_youth	= $this->shirtSizesToArray('y');
		$this->e_adult	= $this->shirtSizesToArray('a');
		$youth_sizes	= array_diff($this->event->getRegistrantShirts('y'), $this->e_youth);
		$adult_sizes	= array_diff($this->event->getRegistrantShirts('a'), $this->e_adult);
		$this->e_youth	= implode(',', $util->sortShirtSizes(array_merge($this->e_youth, $youth_sizes), 'y'));
		$this->e_adult	= implode(',', $util->sortShirtSizes(array_merge($this->e_adult, $adult_sizes), 'a'));

		if($this->event->hasRegistrantWithNoShirt())
			$this->e_no_shirt = true;
		$this->e_no_shirt = $this->e_no_shirt ? 1:0;
		if($this->e_no_shirt == 0)
			$this->e_no_shirt_disc = 0;

		$here = $this->e_reg_here ? 1:0;
		$director_pays = $this->user_pays_fee ? 1:0;

		$query = "UPDATE ". TABLE_EVENTS ." SET name='$this->e_name', type='$type',
			month=$month, day=$day, year=$year, permalink='$this->permalink', 
			description='$this->e_desc', contact_info='$this->e_contact',
			youth_shirt_sizes='$this->e_youth', adult_shirt_sizes='$this->e_adult',
			no_shirt_discount='$this->e_no_shirt_disc', allow_no_shirt=$this->e_no_shirt,
			reg_here=$here, user_pays_fee=$director_pays
			WHERE id=". $this->id;
		
		if(!$sql->q($query))
			return false;
		
		if(!$this->updateRegMethods())
			return false;
		
		return true;
	}



	/*
	 * Private methods
	 */
	private function permalinkErrCheck($event) {
		global $util;
		
		if($event->getPermalink(true) != $this->permalink
				|| date('Y', $event->getDate()) != date('Y', $this->e_date)) {
			if(empty($this->permalink))
				$this->addErr('Permalink is required');
			else if($this->e_date && !$util->permalinkAvailable($this->permalink, date('Y', $this->e_date)))
				$this->addErr('Permalink is already in use by another event');
			else if($util->cleanPermalink($this->permalink) != $this->permalink)
				$this->addErr('Only alphanumeric characters and hyphens allowed in permalinks. Hyphens not allowed at the beginning or end.');
		}
	}

	private function setCouponProperties($post) {
		if(!empty($post['coupon_codename']))
			$this->coupon_codes		= array($post['coupon_codename']);
		else
			$this->coupon_codes		= array();

		if(!empty($post['coupon_amount']))
			$this->coupon_code_amts = array($post['coupon_amount']);
		else
			$this->coupon_code_amts = array();
	}

	private function updateRegMethods() {
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

		if(!$this->updateRegMethod(EREG_METHOD_WEB, $this->e_other_web) ||
				!$this->updateRegMethod(EREG_METHOD_ADDR1, $this->e_addr1) ||
				!$this->updateRegMethod(EREG_METHOD_ADDR2, $this->e_addr2))
					return false;

		return true;
	}
	
	private function findRegMethodId($method) {
		global $sql;
		$query = 'SELECT id FROM '. TABLE_REG_METHODS .'
			WHERE event_id='. $this->id .' AND type='. $method;

		return $sql->getOneFieldEntry($query, 'id');
	}

	private function updateRegMethod($method, $info) {
		global $sql;

		$method_id = $this->findRegMethodId($method);
		if(!$method_id) {
			$query = 'INSERT INTO '. TABLE_REG_METHODS ."
				(`event_id`, `type`, `info`)
				VALUES ('$this->id', '$method', '$info')";
		} else {
			$query = 'UPDATE '. TABLE_REG_METHODS ." SET info='$info'
				WHERE id=$method_id";
		}
		
		return $sql->q($query);
	}

	private function makeSQLsafe() {
		global $sql;
		$this->e_name		= $sql->safeString($this->e_name);
		$this->e_desc		= $sql->safeString($this->e_desc);

		$this->e_addr1		= $sql->safeString($this->e_addr1);
		$this->e_addr2		= $sql->safeString($this->e_addr2);
		$this->e_other_web	= $sql->safeString($this->e_other_web);
	}

	private function setEventDetailProperties($post) {
		$this->e_name			= $post['event_name'];
		$this->e_type			= $post['event_type'];
		$this->e_date			= $post['event_date'];
		$this->e_desc			= $post['event_description'];
		$this->e_contact		= $post['event_contact'];
		$this->permalink		= strtolower($post['event_permalink']);
		$this->e_shirt_avail	= $post['event_shirts_available'] == 'true';

		$youth = $post['event_youth_shirt'];
		$adult = $post['event_adult_shirt'];
		if($this->e_shirt_avail) {
			$this->e_youth			= empty($youth) ? array():$youth;
			$this->e_adult			= empty($adult) ? array():$adult;
			$this->e_no_shirt		= $post['event_no_shirt'] == 'true';
		} else {
			$this->e_youth			= array();
			$this->e_adult			= array();
			$this->e_no_shirt		= false;
		}

		if($this->e_no_shirt)
			$this->e_no_shirt_disc	= $post['event_shirt_discount'];
		else
			$this->e_no_shirt_disc	= 0;

		$this->e_reg_here		= $post['event_reg_here'] == 'true';
		$this->user_pays_fee	= $post['event_reg_fee'] != 'true';

		$this->reg_methods		= isset($post['event_reg_methods']) ?
									$post['event_reg_methods']:array();
		$this->e_addr1			= $post['event_address1'];
		$this->e_addr2			= $post['event_address2'];
		$this->e_other_web		= $post['event_website'] == 'http://' ? '':
									$post['event_website'];
	}
}

?>
