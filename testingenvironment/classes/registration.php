<?php
/*
 * Class that handles the registration
 */

class Registration {
    private $email;
    private $pass;
    private $passRepeat;
	private $fname;
	private $lname;

    private $err;

    function  __construct($email = null, $pass = null, $retype = null, $fname = null, $lname = null) {
		if($email != null) {
			$this->setEmail(substr($email, 0, MAX_EMAIL_LEN));
			$this->setPass(substr($pass, 0, 255));
			$this->setRetype(substr($retype, 0, 255));
			$this->fname = substr($fname, 0, 255);
			$this->lname = substr($lname, 0, 255);
		}
		
		$this->err = array();
    }

    /*
     * Get/Set methods
     */
    function setEmail($e) {
        $this->email = $e;
    }
    function setPass($p) {
        $this->pass = $p;
    }
    function setRetype($p) {
        $this->passRepeat = $p;
    }
    function getErr() {
        return $this->err;
    }

    /*
     * Add an error to the array
     */
    function addError($e) {
        $this->err[] = $e;
    }

    /*
     * Return the number of errors found in registration
     */
    function numErr() {
        return count($this->err);
    }
	function getErrList() {
		if(count($this->err) <= 1)
			return $this->err[0];

		$out = '<ul>';
		foreach($this->err as $e) {
			$out .= '<li>'. $e .'</li>';
		}
		$out .= '</ul>';

		return $out;
	}

    /*
     * Returns boolean for whether or not the registration
     * has any errors
     */
    private function isValid($invite = false) {
		global $util;

        if(!$this->isValidEmail($this->email))
            $this->addError('Invalid email address');
		else if($util->getUser($this->email) > 0)
			$this->addError('This email address has already been registered');

        if(strlen($this->pass) < MIN_PASS_LEN)
            $this->addError('Minimum password length: '. MIN_PASS_LEN .' characters');

        if(!$invite && $this->pass != $this->passRepeat)
            $this->addError('Passwords do not match');

		if(!$invite && empty($this->fname))
			$this->addError('First name is required');

		if(!$invite && empty($this->lname))
			$this->addError('Last name is required');

        return ($this->numErr() == 0);
    }

    private function isValidEmail($email) {
        $pattern = '/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+'.
            '(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a'.
            '-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}'.
            '\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0'.
            '-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i';

        return (preg_match($pattern, $email) != 0);
    }

    function addUser($email = null, $pass = null) {
        global $sql, $mail, $user;

		if($email == null && $pass == null) {
			$e = $sql->safeString($this->email);
			$fn = $sql->safeString(ucwords(strtolower($this->fname)));
			$ln = $sql->safeString(ucwords(strtolower($this->lname)));
		} else {
			$this->email = $email;
			$this->pass = $pass;
			$e = $sql->safeString($email);
			$fn = '';
			$ln = '';
		}

		$invite = $email != null && $pass != null;

        if($this->isValid($invite)) {
            $query = "INSERT INTO ". TABLE_USER ." (email, pass, lvl, reg_date) ".
                "VALUES ('$e', '". md5($this->pass . SALT) ."', ". LVL_USER .", '". $sql->dateTime() ."')";

            if(!$sql->q($query))
                $this->addError('Email already in use');
            else {
                $id = $sql->id();
                $query = "INSERT INTO ". TABLE_PERSONAL ." (user_id, email, fname, lname) VALUES ('$id', '$e', '$fn', '$ln')";
                if(!$sql->q($query))
                    $this->addError(CRITICAL_ERROR);

				$name = ucfirst($user->getIdentifier());
				$subject = $name .' invited you to join '. COMPANY_NAME .'\'s website!';
				$message = 'Dear User,'. "\r\n".
					"\t" . $name .' from '. BASEURL .' has just invited you to join! To make '.
					'the process as easy as possible, we\'ve already set up an account for you to use. Use this email '.
					'address and the following password to login:'. "\r\n\r\n".
					$this->pass ."\r\n\r\n".
					'Once you have logged in, please change your password as soon as possible to something you can remember. '.
					'To change your password go to '. BASEURL .'my-account/ after logging in. ' .
					'If you wish not to join our website, you may ignore this email.'. "\r\n\r\n".
					'Thank you!';

				$mail->sendAdmin($email, $subject, $message);

				return $id;
            }

        }
    }

}

?>
