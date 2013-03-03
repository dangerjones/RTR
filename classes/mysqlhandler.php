<?php
class MysqlHandler {
	private $username	= MYSQL_USER;
	private $password	= MYSQL_PASS;
	private $database	= MYSQL_DB;
	private $host		= MYSQL_HOST;

	private $log_queries	= true;
	private $print_stack	= false;
	private $max_entries	= 10000;
	private $do_only		= 0;
	private $query_log		= QUERY_LOG;

	function  __construct() {
		$this->connect();
	}
	
	private function connect() {
		mysql_connect($this->host, $this->username, $this->password)
			or die("Cannot connect to db");
		mysql_select_db($this->database) 
			or die("Cannot select db");
	}
	/*
	 * Returns a date suitable for mysql DATE insertion
	 */
	function date($timestamp = null) {
		if($timestamp == null)
			$timestamp = time();

		return date('Y-m-d', $timestamp);
	}

	/*
	 * Returns a time suitable for mysql TIME insertion
	 */
	function time($timestamp = null) {
		if($timestamp == null)
			$timestamp = time();

		return date('H:i:s', $timestamp);
	}

	/*
	 * Returns date-time for mysql
	 */
	function dateTime($timestamp = null) {
		if($timestamp == null)
			$timestamp = time();

		return $this->date($timestamp) .' '. $this->time($timestamp);
	}

	/*
	 * Returns an array of assoc arrays (rows)
	 */
	function getAssoc($query) {
		$sqlresult = $this->q($query);
		$results = array();

		if($sqlresult) {
			while($row = mysql_fetch_assoc($sqlresult))
				$results[] = $row;
			return $results;
		} else {
			return false;
		}
	}

	/*
	 * Returns an assoc array (one row)
	 */
	function getAssocRow($query) {
		$sqlresult = $this->q($query);
			if($sqlresult)
				return mysql_fetch_assoc($sqlresult);
			else
				return false;
	}

	/*
	 * Get the result of one field in a single row
	 */
	function getOneFieldEntry($query, $field) {
		$result = $this->getAssocRow($query);

		if(isset($result[$field]))
			return $result[$field];
		else
			return false;
	}

	/*
	 * Get several rows of one column
	 */
	function getOneColumn($query) {
		$sqlresult = $this->q($query);
		$results = array();

		if($sqlresult) {
			while($row = mysql_fetch_array($sqlresult))
				$results[] = $row[0];
			return $results;
		} else
			return false;
	}

	/*
	 * Returns number of rows from query
	 */
	function rows($query) {
		$result = $this->q($query);
		if($result)
			return mysql_num_rows($result);
		else
			return 0;
	}

	/*
	 * Does a query
	 */
	function q($query) {
		if(!is_array($query)) {
			if(!$this->log_queries)
				return mysql_query($query);

			$start		= microtime(true);
			$result		= mysql_query($query);
			$end		= microtime(true);

			$this->recordQueryInfo($start, $end, $query);

			return $result;
		}

		if(!$this->log_queries) {
			foreach($query as $q) {
				if(!mysql_query($q))
					return false;
			}

			return true;
		} else {
			foreach($query as $q) {
				$start		= microtime(true);
				$result		= mysql_query($q);
				$end		= microtime(true);

				$this->recordQueryInfo($start, $end, $query);

				if(!$result)
					return false;
			}
			
			return true;
		}
	}

	/*
	 * Return a date (mm/dd/yyy) formatted for sql (yyyy-mm-dd)
	 */
	function fdateToSQLDate($fdate) {
		$sql_date = explode('/', $fdate);
		return $sql_date[2] .'-'. $sql_date[0] .'-'. $sql_date[1];
	}

	/*
	 * Returns the mysql error
	 */
	function e() {
		return mysql_error();
	}

	/*
	 * Returns mysql_real_escape_string
	 */
	function safeString($s) {
		return mysql_real_escape_string($s);
	}

	/*
	 * Returns id of last id generated
	 */
	function id() {
		return mysql_insert_id();
	}

	/*
	 * Records information on ever query performed
	 */
	private function recordQueryInfo($start, $end, $query) {
		static $write_times = 1;
		static $accumulated_time = 0;

		$elapsed = $end - $start;
		$accumulated_time += $elapsed;

		if($this->max_entries > 0 && $write_times >= $this->max_entries)
			return false;
		else if($this->do_only == 0 || $write_times == $this->do_only) {

			#if(strpos($query, '=\'0\'') === false)
				#return false;

			$handler = fopen($this->query_log, 'a');

			$total_sec = number_format($elapsed, 10);

			$input = '#'. $write_times .' - '. date('[m-d-Y h:i:s a]') .' Execution time: '. $total_sec .' sec :: Elapsed: '. $accumulated_time . "\n\t".
				$query ."\n". ($this->print_stack ? print_r(debug_backtrace(false), true):'') ."\n\n";

			fwrite($handler, $input);
		}

		$write_times++;
	}
}
?>