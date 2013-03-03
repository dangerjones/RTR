<?php

class CustomQuestion {
	private $id,
			$event_id,
			$q,
			$answers;

	function  __construct($q_id) {
		$this->id = (int)$q_id;
		$this->setProperties();
	}

	function getId() {
		return $this->id;
	}
	function getEventId() {
		return $this->event_id;
	}
	function getQuestion() {
		return $this->q;
	}
	function getAnswers() {
		return $this->answers;
	}

	private function setProperties() {
		global $sql;
		$query = 'SELECT event_id, question, answers FROM '. TABLE_E_QUESTIONS .' WHERE id='. $this->id;
		$properties = $sql->getAssocRow($query);
		
		$this->event_id		= $properties['event_id'];
		$this->q			= $properties['question'];
		$this->answers		= explode(DELIMITER, $properties['answers']);
	}
}
?>
