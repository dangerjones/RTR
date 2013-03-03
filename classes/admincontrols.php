<?php
require_once 'event.php';

class AdminControls {

	function formattedEventsByStatus($status) {
		$ids = $this->getEventsByStatus($status);
		$num = count($ids);
		if($num > 0) {
			$out = '<ul class="event-list">';
			foreach($ids as $id) {
				$event = new Event($id);
				$out .= $event->formattedAsList();
			}
			$out .= '</ul>';
			return $out;
		} else
			return '';
	}

	private function getEventsByStatus($status) {
		global $sql;
		$query = 'SELECT id FROM '. TABLE_EVENTS .' WHERE status='. $status;

		return $sql->getOneColumn($query);
	}
}
?>
