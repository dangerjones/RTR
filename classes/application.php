<?php

class Application extends Person {
	public $pr_mile, $pr_3000, $pr_3000st, $pr_5k, $pr_8k, $pr_10k, $pr_half_marathon, $pr_marathon;

	public $accomplishments, $benefit, $contribution, $comments, $sendtome;

	private $errors;

	function  __construct($post) {
		$this->setVariables($post);
		$this->errorCheck();
	}

	function hasErrors() {
		return $this->numErrors() > 0;
	}

	function numErrors() {
		return count($this->errors);
	}

	function getfErrors() {
		$out = '<ul>';
		foreach($this->errors as $e) {
			$out .= '<li>'. $e .'</li>';
		}
		$out .= '</ul>';

		return $out;
	}

	private function errorCheck() {
		$this->errors = array();

		if(empty($this->fname))
			$this->addError('First name is required');

		if(empty($this->lname))
			$this->addError('Last name is required');

		if(empty($this->address_1))
			$this->addError('Address is required');

		if(empty($this->city))
			$this->addError('City is required');

		if(empty($this->state))
			$this->addError('State is required');

		if(empty($this->zip))
			$this->addError('Zip code is required');
		else if(!is_numeric($this->zip) || strlen($this->zip) < 5)
			$this->addError('Invalid zip code');

		if(empty($this->phone))
			$this->addError('Phone number is required');
		else if(!is_numeric($this->phone) || strlen($this->phone) < 10)
			$this->addError('Invalid phone number');

		if(empty($this->bday))
			$this->addError('Birthday is required');
		else {
			$btime = strtotime($this->bday);
			if(!$btime)
				$this->addError('Invalid birthday');
			else if($btime > time())
				$this->addError('Invalid birthday. You were not born in the future.');
		}

		if(empty($this->email))
			$this->addError('Email address is required');
		else if(!filter_var($this->email, FILTER_VALIDATE_EMAIL))
			$this->addError('Invalid email address');

		if(empty($this->accomplishments))
			$this->addError('Please fill in your notable accomplishments.');

		if(empty($this->benefit))
			$this->addError('Please fill in how a Utah Elite sponsorship will benefit you.');

		if(empty($this->contribution))
			$this->addError('Please fill in how you will contribute to Utah Elite');
		
	}

	private function addError($e) {
		$this->errors[] = $e;
	}

	private function setVariables($post) {
		global $user;

		$this->fname			= $post->get('register_fname', 50);
		$this->lname			= $post->get('register_lname', 50);
		$this->address_1		= $post->get('register_addr', 100);
		$this->address_2		= $post->get('register_addr2', 100);
		$this->city				= $post->get('register_city', 50);
		$this->state			= $post->get('register_state', 'strict', 2);
		$this->zip				= $post->get('register_zip', 5);
		$this->phone			= $post->get('register_phone', 3) .
									$post->get('register_phone2', 3) .
									$post->get('register_phone3', 4);
		$this->bday				= $post->get('register_bday', 10);
		$this->email			= $post->get('register_email', 100);

		$this->pr_mile			= $post->get('time_mile', 10);
		$this->pr_3000			= $post->get('time_3000', 10);
		$this->pr_3000st		= $post->get('time_3000st', 10);
		$this->pr_5k			= $post->get('time_5k', 10);
		$this->pr_8k			= $post->get('time_8k', 10);
		$this->pr_10k			= $post->get('time_10k', 10);
		$this->pr_half_marathon	= $post->get('time_halfmarathon', 10);
		$this->pr_marathon		= $post->get('time_marathon', 10);

		$this->accomplishments	= $post->get('notable_acc');
		$this->benefit			= $post->get('benefit_from');
		$this->contribution		= $post->get('how_contribute');
		$this->comments			= $post->get('comments');

		if($user->loggedIn())
			$this->sendtome		= $post->getBoolean('reg_sendtome');
		else
			$this->sendtome		= false;
	}
}

?>