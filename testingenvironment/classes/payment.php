<?php
require_once 'registrant.php';
/**
 * Payments made
 *
 * @author Sterling
 */
class Payment {
	private $id, $user_id, $transaction, $amt, $regs, $date, $time;

	function  __construct($id) {
		if(is_array(($id))) {
			$pay_details =& $id;
			$this->setProperties($pay_details);
		} else {
			$this->id = (int)$id;
			$this->setVars();
		}
	}

	/* get methods */
	function getId() {
		return $this->id;
	}
	function getAmt() {
		return $this->amt;
	}
	function getTransaction() {
		return $this->transaction;
	}
	function getDate() {
		return $this->date;
	}
	function getTime() {
		return $this->time;
	}
	function getDateTime() {
		return $this->date .' '. $this->time;
	}
	function getRegistrants() {
		return $this->regs;
	}
		// end get methods


	/*
	 * Public methods
	 */
	function isRecent($within_seconds) {
		$timestamp = strtotime($this->date .' '. $this->time);

		return time() - $timestamp <= $within_seconds;
	}


	
	private function setVars() {
		global $sql, $user;

		$query = 'SELECT * FROM '. TABLE_RECEVIED_PAYMENTS .' WHERE id='. $this->id;
		$info = $sql->getAssocRow($query);

		$this->user_id		= $info['user_id'];
		$this->transaction	= $info['transaction'];
		$this->amt			= $info['amount'];

		$this->regs			= array();
		$reg_ids				= explode(';', $info['registrants']);
		foreach($reg_ids as $r_id) {
			$this->regs[] = new Registrant($r_id);
		}

		$this->date			= $info['date'];
		$this->time			= $info['time'];
	}

	private function setProperties($pay_details) {
		$this->id			= $pay_details['id'];
		$this->user_id		= $pay_details['user_id'];
		$this->transaction	= $pay_details['transaction'];
		$this->amt			= $pay_details['amount'];

		$this->regs			= array();
		$reg_ids				= explode(';', $pay_details['registrants']);
		foreach($reg_ids as $r_id) {
			$this->regs[] = Registrant::getRegistrant($r_id);
		}

		$this->date			= $pay_details['date'];
		$this->time			= $pay_details['time'];
	}
}
?>
