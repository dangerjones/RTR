<?php
/**
 * A coupon code as part of an event
 *
 * @author Sterling
 */
class Coupon {
	private $id, $event_id, $name, $amt, $disabled;

	private static $all_coupons = array(); // Structure: array(id -> Coupon obj, id -> Coupon obj)

	function  __construct($id) {
		if(!is_array($id)) {
			$this->id = (int)$id;
			$this->setVars();
		} else {
			$coupon_details =& $id;
			$this->setProperties($coupon_details);
			self::addToCache($this);
		}
	}

	/* get methods */
	function getId() {
		return $this->id;
	}
	function getEventId() {
		return $this->event_id;
	}
	function getName() {
		return $this->name;
	}
	function getAmount() {
		return $this->amt;
	}
		// end get methods


	/*
	 * Public methods
	 */
	function isDisabled() {
		return $this->disabled;
	}

	function toggleDisabledState() {
		global $sql;
		$state = $this->isDisabled() ? 0:1;
		$query = 'UPDATE '. TABLE_E_COUPONS .' SET disabled='. $state .'
			WHERE id='. $this->id .' AND event_id='. $this->event_id;

		return $sql->q($query);
	}

	/*
	 * Private methods
	 */
	private function setVars() {
		global $sql;
		$query = 'SELECT * FROM '. TABLE_E_COUPONS .' WHERE id='. $this->id;
		$result = $sql->getAssocRow($query);

		$this->event_id		= $result['event_id'];
		$this->name			= $result['code'];
		$this->amt			= $result['amount'];
		$this->disabled		= $result['disabled'] == 1;
	}

	private function setProperties($coupon_details) {
		$this->id			= $coupon_details['id'];
		$this->event_id		= $coupon_details['event_id'];
		$this->name			= $coupon_details['code'];
		$this->amt			= $coupon_details['amount'];
		$this->disabled		= $coupon_details['disabled'] == 1;
	}

	/*
	 * Static functions
	 */

	static private function addToCache($coupon_obj) {
		self::$all_coupons[$coupon_obj->getId()] = $coupon_obj;
	}

	static function hasCoupon($c_id) {
		return is_object(self::$all_coupons[$c_id]);
	}

	static function getCoupon($c_id) {
		$coupon =& self::$all_coupons[$c_id];

		if(!is_object($coupon))
			self::cacheCoupon('id="'. $c_id .'"');

		return $coupon;
	}

	static function cacheCoupon($where) {
		global $sql;

		if(strlen($where) > 0)
			$where = ' WHERE '. $where;

		$query = 'SELECT * FROM '. TABLE_E_COUPONS . $where;

		$coupons = $sql->getAssoc($query);

		foreach($coupons as $coupon_details) {
			new Coupon($coupon_details);
		}
	}

	/*
	 * Override
	 */
	function  __toString() {
		global $util;
		return $this->name .' ('. $util->money($this->amt) .')';
	}
}
?>
