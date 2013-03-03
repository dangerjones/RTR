<?php
require_once 'registrantstatistics.php';

/**
 * Simliar to that of RegistrantStatistics, but on a race level
 *
 * @author Sterling
 */
class RaceStatistics extends RegistrantStatistics {
	private $race_id;

	function  __construct($e_id, $r_id) {
		parent::__construct($e_id);
		$this->race_id = (int)$r_id;
	}

	function addRegistrant($new_regs) {
		if(!is_array($new_regs))
			$new_regs = array($new_regs);

		foreach($new_regs as $new_reg) {
			$this->total_registrants++;

			if($new_reg->isMale())
				$this->male++;

			$this->addMoneyStats($new_reg);
			$this->addAgeStats($new_reg);
		}
	}
}
?>
