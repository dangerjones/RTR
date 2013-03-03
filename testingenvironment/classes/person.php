<?php

class Person {
	protected $id;
	protected $user_id;
	protected $isSelf;

	protected $email;
    protected $fname;
    protected $lname;
    protected $address_1;
    protected $address_2;
    protected $city;
    protected $state;
    protected $zip;
	protected $phone;
	protected $bday;
    protected $age;
    protected $gender;
    protected $shirt_size;
	protected $adult_shirt;

    /*
     * Get/set methods
     */
	function getId() {
		return $this->id;
	}
	function getUserId() {
		return $this->user_id;
	}
    function getLastlog() {
        return $this->lastlog;
    }
    function getEmail() {
        return $this->email;
    }
    function getFName() {
        return $this->fname;
    }
    function getLName() {
        return $this->lname;
    }
    function getFullName() {
		if(!empty($this->fname) && !empty($this->lname))
			return $this->fname ." ". $this->lname;
		else
			return '';
    }
	function getAddressingIdentifier() {
		if($this->hasPersonalInfo())
			return $this->getFName();
		else
			return 'User';
	}
	function getIdentifier() {
		if($this->hasPersonalInfo())
			return $this->getFullName();
		else
			return 'someone ('. $this->email .')';
	}
	function getAddr() {
		return $this->address_1;
	}
	function getAddr2() {
		return $this->address_2;
	}
	function getCity() {
		return $this->city;
	}
	function getState() {
		return $this->state;
	}
	function getZip() {
		if(!empty($this->zip))
			return $this->zip;
		else
			return '';
	}
	function getShirtSize() {
		return $this->shirt_size;
	}
	function isAdultSize() {
		return $this->adult_shirt;
	}
	function getAge() {
		return $this->age;
	}
	function getfBirthday($format) {
		return date($format, strtotime($this->bday));
	}
	function getBirthday() {
		return $this->bday;
	}
	function getfBday() {
		$bday = explode('-', $this->bday);
		if($bday[0] == 0)
			return '';
		return $bday ? $bday[1] .'/'. $bday[2] .'/'. $bday[0] : '';
	}
	function getfPhone() {
		return $this->getPhone('(p1) p2-p3');
	}
	// Uses the values p1-p3 to insert each section of the phone number into the format
	function getPhone($format = null) {
		if($format == null)
			return $this->phone;
		else {
			$p1 = substr($this->phone, 0, 3);
			$p2 = substr($this->phone, 3, 3);
			$p3 = substr($this->phone, 6, 4);

			return str_replace(array('p1', 'p2', 'p3'), array($p1, $p2, $p3), $format);
		}
	}
	function getPhoneRange($start, $length) {
		return substr($this->phone, $start, $length);
	}
	function getGender($get_char = false) {
		if($get_char)
			return $this->gender;

		return $this->gender == 'm' ? 'Male':'Female';
	}
	function isMale() {
		return $this->gender == 'm';
	}
	function isFemale() {
		return $this->gender == 'f';
	}

	function hasPersonalInfo() {
		return !empty($this->fname);
	}

	function hasAddr2() {
		return !empty($this->address_2);
	}

	function hasShirt() {
		return !empty($this->shirt_size);
	}

	function isValidFBday() {
		$bday = explode('/', $this->bday);
		return checkDate($bday[0], $bday[1], $bday[2]);
	}
	
    protected function setAge($bday) {
        $cYear = date('Y', time());
		$cDay = date('j', time());
		$cMonth = date('n', time());
		$today_timestamp = mktime(0, 0, 0, $cMonth, $cDay, $cYear);

		$birthday = explode('-', $bday);
		$birth_year = $birthday[0];
		$birth_month = $birthday[1];
		$birth_day = $birthday[2];
		$birthday_timestamp = mktime(0, 0, 0, $birth_month, $birth_day, $cYear);

		$offset = $today_timestamp >= $birthday_timestamp ? 0:1;

		$this->age = $cYear - $birth_year - $offset;
    }

	protected function prepareForDB() {
		global $sql;

		$this->email = $sql->safeString($this->email);
		$this->fname = $sql->safeString(ucwords($this->fname));
		$this->lname = $sql->safeString(ucwords($this->lname));
		$this->address_1 = $sql->safeString(ucwords($this->address_1));
		$this->address_2 = $sql->safeString(ucwords($this->address_2));
		$this->city = $sql->safeString(ucwords($this->city));
		$this->state = $sql->safeString(strtoupper($this->state));
		$this->bday = $sql->fdateToSQLDate($this->bday);
	}

    protected function setPersonalInfo($isSelf) {
		$this->isSelf = $isSelf;
		
		$this->setEmail();

        $this->setFName();
        $this->setLName();
        $this->setAddress_1();
        $this->setAddress_2();
        $this->setCity();
        $this->setState();
        $this->setZip();
		$this->setPhone();
		$this->setBday();
        $this->setAge($this->bday);
        $this->setGender();
        $this->setShirtSize();
		$this->setAdultSize();
    }

	private function setEmail() {
		$this->email = $this->getPersonalInfoFromDB('email');
	}
    private function setFName() {
        $this->fname = $this->getPersonalInfoFromDB('fname');
    }
    private function setLName() {
        $this->lname = $this->getPersonalInfoFromDB('lname');
    }
    private function setAddress_1() {
        $this->address_1 = $this->getPersonalInfoFromDB('address_1');
    }
    private function setAddress_2() {
        $this->address_2 = $this->getPersonalInfoFromDB('address_2');
    }
    private function setCity() {
        $this->city = $this->getPersonalInfoFromDB('city');
    }
    private function setState() {
        $this->state = $this->getPersonalInfoFromDB('state');
    }
    private function setZip() {
        $this->zip = $this->getPersonalInfoFromDB('zip');
    }
    private function setPhone() {
        $this->phone = $this->getPersonalInfoFromDB('phone');
    }
    private function setBday() {
        $this->bday = $this->getPersonalInfoFromDB('birthday');
    }
    private function setGender() {
        $this->gender = $this->getPersonalInfoFromDB('gender');
    }
    private function setShirtSize() {
        $this->shirt_size = $this->getPersonalInfoFromDB('shirt_size');
    }
    private function setAdultSize() {
        $this->adult_shirt = $this->getPersonalInfoFromDB('adult_size') == 1;
    }
	
    private function getPersonalInfoFromDB($field) {
        global $sql;

		if($this->isSelf)
			$query = "SELECT $field FROM ". TABLE_PERSONAL ." WHERE user_id='$this->id'";
		else
			$query = "SELECT $field FROM ". TABLE_FRIENDS_FAMILY ." WHERE id='$this->id' AND user_id='$this->user_id'";
        $result = $sql->getOneFieldEntry($query, $field);

        if($result)
            return $result;
        else
            return '';
    }

	protected function setPersonalProperties($personal_details) {
		$this->fname			= $personal_details['fname'];
		$this->lname			= $personal_details['lname'];
		$this->address_1		= $personal_details['address_1'];
		$this->address_2		= $personal_details['address_2'];
		$this->city				= $personal_details['city'];
		$this->state			= $personal_details['state'];
		$this->zip				= $personal_details['zip'];
		$this->phone			= $personal_details['phone'];
		$this->bday				= $personal_details['birthday'];
		$this->gender			= $personal_details['gender'];
		$this->shirt_size		= $personal_details['shirt_size'];
		$this->adult_shirt		= $personal_details['adult_size'] == 1;
	}
}
?>
