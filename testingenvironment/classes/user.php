<?php
require_once 'person.php';
require_once 'friend.php';
require_once 'event.php';
require_once 'payment.php';
/*
 * Class for individual users
 */
class User extends Person {
    /*
     * User variables
     */
	private $account_email;
    private $lvl;
    private $ip;
	private $prev_ip;
    private $lastlog;
	private $friends;
	private $unpaid_event_ids;

	private static $all_users = array(); // Structure: array(id -> User obj, id -> User obj)

    function __construct($user_id, $current_user = true) {
		$this->id = (int)$user_id;
		$this->setIp();
		
		if(is_array($user_id)) {
			$this->setProperties($user_id);
		} else if($this->id > 0) {
			$this->setPersonalInfo(true);
			$this->setEmail();
			$this->setLvl();
            $this->setLastlog();
		} else {
			$this->lvl	= LVL_USER;
		}
    }

	/* get methods */
	function getLvl() {
		return (int)$this->lvl;
	}
	function getPass() {
		return $this->getAccountInfoFromDB('pass');
	}
	function getEmail() {
		return $this->account_email;
	}
	function getContactEmail() {
		if(empty($this->email))
			return $this->account_email;
		
		return $this->email;
	}
	function getFriends() {
		global $sql;
		$this->friends = array();
		
		$query = 'SELECT * FROM '. TABLE_FRIENDS_FAMILY .' WHERE user_id='. $this->id;

		$friends = $sql->getAssoc($query);

		foreach($friends as $friend_details) {
			$this->friends[] = new Friend($friend_details);
		}
		return $this->friends;
	}
	function getUnpaidEvents($filter_passed = false) {
		global $sql;
		$query = 'SELECT DISTINCT event_id FROM '. TABLE_E_REGISTRANTS .' WHERE user_id='. $this->id;
		$events_with_regs = $sql->getOneColumn($query);
		$ids = array();

		foreach($events_with_regs as $e_id) {
			$event = EventHandler::getEvent($e_id);
			if($event->hasRegistrants('unpaid', $this->id)) {
				$ids[] = $e_id;
			}
		}

		$this->unpaid_event_ids = $ids;


		if(!$filter_passed)
			return $this->unpaid_event_ids;

		$filtered = array();
		foreach($this->unpaid_event_ids as $e_id) {
			$event = EventHandler::getEvent($e_id);
			if(!$event->hasPassed())
				$filtered[] = $event->getId();
		}
		return $filtered;
	}
	function getUploadFolder() {
		if(!$this->loggedIn())
			return '';
		return UPLOADS_ABSOLUTE . $this->id .'/';
	}
	function getPayments() {
		global $sql;
		$query = 'SELECT * FROM '. TABLE_RECEVIED_PAYMENTS .' WHERE user_id='. $this->id .' ORDER BY id DESC';
		$payment_details = $sql->getAssoc($query);

		$payments = array();
		$idx = 0;
		foreach($payment_details as $pay) {
			$payment = new Payment($pay);

			if($idx == 0)
				$payments[0] = $payment; // Most recent payment at first index

			$payments[$pay['id']] = $payment;
			$idx++;
		}

		return $payments;
	}
	function makeUploadedFilePath($file) {
		if(strlen($file) == 0)
			return '';

		return $this->id .'/'. $file;
	}
	function uploadedFileExists($file_to_check) {
		$handle = opendir(UPLOADS_ABSOLUTE . $this->id .'/');
		if ($handle) {
			while (false !== ($file = readdir($handle))) {
				if(strtolower($file_to_check) == strtolower($file))
					return true;
			}
		}
		return false;
	}

    /*
     * Returns boolean: whether or not is authentically logged in
     */
    function loggedIn() {
        if(empty($this->id))
            return false;

        if($this->prev_ip != $this->ip) {
            $this->logout();
            return false;
        } else {
            return true;
        }
    }
	
    /*
     * Logout the user
     */
    function logout() {
        $_SESSION = array();
        session_destroy();
		
        $this->id = null;
    }

	/*
	 * Checks if user is an admin
	 */
	function isAdmin() {
		return $this->lvl == LVL_ADMIN;
	}

    /*
     * Calculate user's ip address
     */
    function findIp() {
        if(getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        else if(getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        else if(getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        }
        else if(getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        }
        else if(getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        }
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

	function updatePass($new_pass) {
		global $sql;

		$new_pass = md5($new_pass . SALT);
		$query = 'UPDATE '. TABLE_USER .' SET pass="'. $new_pass .'" WHERE id='. $this->id;

		return $sql->q($query);
	}

	function updateLvl($new_lvl) {
		global $sql;
		$new_lvl = (int)$new_lvl;

		$query = 'UPDATE '. TABLE_USER .' SET lvl='. $new_lvl .' WHERE id='. $this->id;

		return $sql->q($query);
	}

	
    /*
     * Pulls info from the db and sets class variables
     */
    private function setEmail() {
        $this->account_email = $this->getAccountInfoFromDB('email');
    }
    private function setLvl() {
        $this->lvl = $this->getAccountInfoFromDB('lvl');
    }
    private function setLastlog() {
        $this->lastlog = $this->getAccountInfoFromDB('last_login');
    }
    private function setIp() {
        $this->ip = $this->findIp();
    }

    /*
     * Returns a field entry for this user
     */
    private function getAccountInfoFromDB($field) {
        global $sql;

        $query = "SELECT $field FROM ". TABLE_USER ." WHERE id='$this->id'";
        return $sql->getOneFieldEntry($query, $field);
    }

	private function getFriendIds() {
		global $sql;

		$query = 'SELECT id FROM '. TABLE_FRIENDS_FAMILY .' WHERE user_id='. $this->id;
		return $sql->getOneColumn($query);
	}

	private function setProperties($user_details) {
		$this->id				= $user_details['id'];
		$this->account_email	= $user_details['email'];
		$this->lvl				= $user_details['lvl'];
		$this->lastlog			= $user_details['last_login'];
		$this->prev_ip			= $user_details['ip'];

		$this->setPersonalProperties($user_details);
	}

	/*
	 * Static functions
	 */
	static function cacheUsers($where) {
		global $sql;
		if(strlen($where) > 0)
			$where = 'WHERE '. $where;
		$query = '
			SELECT
				u.id,
				u.email,
				u.lvl,
				u.reg_date,
				u.last_login,
				u.ip,
				u.status,
				p.email contact_email,
				p.fname,
				p.lname,
				p.address_1,
				p.address_2,
				p.city,
				p.state,
				p.zip,
				p.phone,
				p.birthday,
				p.gender,
				p.shirt_size,
				p.adult_size
			FROM
				'. TABLE_USER .' u LEFT JOIN '. TABLE_PERSONAL .' p
				ON
					u.id = p.user_id
				'. $where;

		$users = $sql->getAssoc($query);

		self::addArrayToCache($users);

	}
	
	static private function addArrayToCache($array_of_user_details) {
		foreach($array_of_user_details as $u) {
			$user = new User($u);
			self::$all_users[$u['id']] = $user;
		}
		
		if(empty($user))
			self::$all_users[0] = new User(null);
	}
	/*
	 * Cache all the users that are tied to a registrant (a user that has registered
	 * someone for a race).
	 */
	static function cacheRegistrantsUsers($event_id) {
		global $sql;
		$query = '
			SELECT DISTINCT
				u.id,
				u.email,
				u.lvl,
				u.reg_date,
				u.last_login,
				u.ip,
				u.status,
				p.email contact_email,
				p.fname,
				p.lname,
				p.address_1,
				p.address_2,
				p.city,
				p.state,
				p.zip,
				p.phone,
				p.birthday,
				p.gender,
				p.shirt_size,
				p.adult_size
			FROM
				('. TABLE_USER .' u LEFT JOIN '. TABLE_PERSONAL .' p
				ON
					u.id = p.user_id), '. TABLE_E_REGISTRANTS .' r
			WHERE
				r.user_id = u.id AND r.event_id="'. ((int)$event_id) .'"';
		
		$users = $sql->getAssoc($query);

		self::addArrayToCache($users);
	}

	static function getUser($u_id) {
		$user =& self::$all_users[$u_id];
		if(!is_object($user))
			self::cacheUsers('id="'. $u_id .'"');

		return $user;
	}
}


?>
