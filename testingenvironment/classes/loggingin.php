<?php
/*
 * Handles logging in
 */
class LoggingIn {
    private $email;
    private $pass;
    private $id;
    private $err;

    function __construct($email, $pass) {
        $this->setEmail(substr($email, 0, MAX_EMAIL_LEN));
        $this->setPass($pass);
        
        $this->err = array();
    }

    /*
     * Get/set methods
     */
    function setEmail($e) {
        $this->email = $e;
    }
    function setPass($p) {
        $this->pass = $p;
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

    private function isCredAuthenticate() {
        global $sql;

        $email = $sql->safeString($this->email);
        $query = "SELECT id FROM ". TABLE_USER ." WHERE email='$email' AND ".
            "pass='" . md5($this->pass . SALT) ."'";

        $row = $sql->getAssocRow($query);
        if(isset($row['id'])) {
            $this->id = $row['id'];
            return true;
        } else {
            return false;
        }
    }

    function login() {
        global $user, $sql;
		
        if($this->isCredAuthenticate()) {
            $_SESSION['user_id'] = $this->id;
            $ip = $user->findIp();
			$date = $sql->dateTime();

            $query = "UPDATE ". TABLE_USER ." SET ip='$ip', last_login='$date' WHERE id='$this->id'";
            if(!$sql->q($query)) {
                $this->addError('Login failed');
            }
        } else {
            $this->addError('Authentication failed');
        }
    }

}
?>
