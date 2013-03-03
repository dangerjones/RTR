<?php

require_once 'event.php';
require_once 'race.php';

class EventRegistration extends Person {
	private $answers;
	private $agree;
	private $save_as; // 0=me, 1=friend, 2=no save
	private $save_to;

	private $event;
	private $race;
	private $coupon;
	private $errors;

	function  __construct($post) {
		global $util;
		$e_id = (int)$post['register_event'];
		$r_id = (int)$post['register_race'];
		$this->errors = array();

		if($util->eventExists($e_id)) {
			$this->event = new Event($e_id);
			if(empty($r_id))
				$this->addError('Please select a race');
			else if($this->event->hasRace($r_id))
				$this->race = new Race($r_id);
			else
				$this->addError('Invalid race');
		}
		else
			$this->addError('Invalid event');
		
		$this->setVariables($post);
		$this->errorCheck();
	}

	/* get methods */
	function getErrors() {
		return $this->errors;
	}
	function getfErrors() {
		if(!$this->hasErrors())
			return '';

		$out = '<ul>';
		foreach($this->errors as $e) {
			$out .= '<li>'. $e .'</li>';
		}
		$out .= '</ul>';

		return $out;
	}
	function getQAString() {
		$string = '';
		$q = $this->event->getQuestions();
		$q_num = $this->event->numQuestions();

		for($i = 0; $i < $q_num; $i++) {
			$string .= $q[$i][0] . DELIMITER . $this->answers[$i];
			if($i < $q_num-1)
				$string .= DELIMITER;
		}

		return $string;
	}

	function hasErrors() {
		return count($this->errors) > 0;
	}

	function addToDb() {
		$this->prepareForDB();
		// save personal info as self or friend
		if($this->save_as < 2) {
			if($this->save_as == 0) {
				if(!$this->savePersonalInfo())
					return false;
			} else if($this->save_to === false) {
				if(!$this->makeNewFriend())
					return false;
			} else {
				if(!$this->overwriteFriend())
					return false;
			}
		}

		// add registrant to db
		if(!$this->addRegistrant())
			return false;
		
		return true;
	}

	private function addRegistrant() {
		global $sql;
		$answers = $sql->safeString($this->getQAString());
		$shirt_size = $this->shirt_size == 'none' ? '':$this->shirt_size;
		$coupon_id = $this->event->getCouponId($this->coupon);

		$query = "INSERT INTO ". TABLE_E_REGISTRANTS ." (user_id, event_id, race_id,
			email, fname, lname, address_1, address_2, city, state, zip, phone, birthday,
			gender, shirt_size, adult_size, answers, coupon_id)
			VALUES ($this->id, ". $this->event->getId() .", ". $this->race->getId() .",
			'$this->email', '$this->fname', '$this->lname', '$this->address_1',
			'$this->address_2', '$this->city', '$this->state', '$this->zip', '$this->phone',
			'$this->bday', '$this->gender', '$shirt_size', '$this->adult_shirt', '$answers', '$coupon_id')";
		
		return $sql->q($query);
	}

	private function savePersonalInfo($ow_friend = false) {
		global $sql;
		$table = TABLE_PERSONAL;

		if($ow_friend) {
			$ow = ' AND id='. $this->save_to;
			$table = TABLE_FRIENDS_FAMILY;
		}
		if($this->shirt_size != 'none') {
			$shirt_size = ", shirt_size='$this->shirt_size', adult_size='$this->adult_shirt'";
		}

		$query = "UPDATE ". $table ." SET
			email='$this->email', fname='$this->fname', lname='$this->lname', 
			address_1='$this->address_1', address_2='$this->address_2', city='$this->city',
			state='$this->state', zip='$this->zip', phone='$this->phone', birthday='$this->bday',
			gender='$this->gender'$shirt_size
			WHERE user_id=$this->id" . $ow;

		return $sql->q($query);
	}

	private function makeNewFriend() {
		global $sql;

		$query = "INSERT INTO ". TABLE_FRIENDS_FAMILY ." (user_id, email, fname, lname,
			address_1, address_2, city, state, zip, phone, birthday, gender, shirt_size, adult_size)
			VALUES ($this->id, '$this->email', '$this->fname', '$this->lname', '$this->address_1',
			'$this->address_2', '$this->city', '$this->state', '$this->zip', '$this->phone',
			'$this->bday', '$this->gender', '$this->shirt_size', '$this->adult_shirt')";

		return $sql->q($query);
	}

	private function overwriteFriend() {
		return $this->savePersonalInfo(true);
	}

	private function setVariables($post) {
		global $user, $util, $adult_shirt_sizes, $youth_shirt_sizes;

		$this->id =			$user->getId();
		$this->save_as =	isset($post['register_save']) ? (int)$post['register_save']:false;
		$this->save_to =	$post['register_save_ow'] == 'true' ?
							(int)$post['register_friend']:false;

		$this->fname =		$util->cleanUp($post['register_fname'], 255);
		$this->lname =		$util->cleanUp($post['register_lname'], 255);
		$this->email =		$util->cleanUp($post['register_email'], 255);
		$this->address_1 =	$util->cleanUp($post['register_addr'], 255);
		$this->address_2 =	$util->cleanUp($post['register_addr2'], 255);
		$this->city =		$util->cleanUp($post['register_city'], 255);
		$this->state =		$util->cleanUp($post['register_state'], 2);
		$this->zip =		$util->cleanUp($post['register_zip'], 5);
		$this->bday =		$util->cleanUp($post['register_bday']);
		$this->phone =		$util->cleanUp($post['register_phone'].
							$post['register_phone2'].$post['register_phone3'], 10);

		if(isset($post['register_shirt'])) {
			if($post['register_shirt'] == 'none')
				$this->shirt_size = 'none';
			else {
				$shirt = explode('-', $post['register_shirt']);

				$this->adult_shirt=	$shirt[0] == 'a';

				$shirt_sizes = $this->adult_shirt ? $adult_shirt_sizes:$youth_shirt_sizes;

				$this->shirt_size =	$shirt_sizes[(int)$shirt[1]];
			}
		}
		
		if(isset($post['register_gender']))
			$this->gender =		(int)$post['register_gender'] == 0 ? 'm':'f';

		$this->answers = array();
		$idx = 0;
		while(isset($post['register_q'.++$idx])) {
			$this->answers[] = $util->cleanUp($post['register_q'.$idx]);
		}

		$this->coupon =		$util->cleanUp($post['register_coupon'], 30);

		$this->agree =		$post['register_agree'] == 'true';
	}

	private function errorCheck() {
		if($this->save_as === false)
			$this->addError('In order to save this information for future use, choose a save option');

		if(empty($this->fname))
			$this->addError('First name is required');

		if(empty($this->lname))
			$this->addError('Last name is required');

		if(empty($this->email))
			$this->addError('Email is required');
		else if(!filter_var($this->email, FILTER_VALIDATE_EMAIL))
			$this->addError('Invalid email');

		if(empty($this->address_1))
			$this->addError('Address is required');

		if(empty($this->city))
			$this->addError('City is required');

		if(empty($this->state))
			$this->addError('State is required');

		if(empty($this->zip))
			$this->addError('Zip code is required');
		else if(!is_numeric($this->zip) || strlen($this->zip) != 5)
			$this->addError('Invalid zip code');

		if(empty($this->bday))
			$this->addError('Birthday is required');
		else if(!$this->isValidFBday())
			$this->addError('Invalid birthday');
		else if(mktime(0, 0, 0, date('m'), date('d'), date('Y')) <= strtotime($this->bday))
			$this->addError('Invalid birthday. You were not born in the future.');

		if(empty($this->phone))
			$this->addError('Phone number is required');
		else if(!is_numeric($this->phone) || strlen($this->phone) != 10)
			$this->addError('Invalid phone number');

		if($this->event->hasShirts() && empty($this->shirt_size))
			$this->addError('Shirt size is required');

		if(empty($this->gender))
			$this->addError('Select participant\'s gender');

		if(count($this->answers) < $this->event->numQuestions())
			$this->addError('All of the race director\'s questions are required' );

		if(!$this->agree)
			$this->addError('In order to register, the participant must agree to the event disclosure');
	}

	private function addError($e) {
		$this->errors[] = $e;
	}

}

?>
