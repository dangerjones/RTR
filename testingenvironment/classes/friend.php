<?php
require_once 'person.php';

class Friend extends Person {

	function  __construct($friend_details) {
		$this->id		= $friend_details['id'];
		$this->user_id	= $friend_details['user_id'];
		$this->email	= $friend_details['email'];

		$this->setPersonalProperties($friend_details);
	}
}
?>
