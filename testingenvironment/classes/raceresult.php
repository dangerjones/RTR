<?php

class RaceResult {
	private $id;
	private $event_id;
	private $race_id;
	private $name;
	private $file;

	function  __construct($id) {
		$id = (int)$id;
		$this->id = $id;
		$this->setProperties();
	}

	function getId() {
		return $this->id;
	}
	function getEventId() {
		return $this->event_id;
	}
	function getRaceId() {
		return $this->race_id;
	}
	function getName() {
		return $this->name;
	}
	function getFile() {
		return $this->file;
	}
	function getPathToFile() {
		return UPLOAD_FOLDER . $this->file;
	}

	private function setProperties() {
		global $sql;
		$query = 'SELECT event_id, race_id, name, file FROM '. TABLE_RACE_RESULTS .' WHERE id='. $this->id;

		$properties = $sql->getAssocRow($query);

		$this->event_id		= (int)$properties['event_id'];
		$this->race_id		= (int)$properties['race_id'];
		$this->name			= $properties['name'];
		$this->file			= $properties['file'];
	}
}

?>
