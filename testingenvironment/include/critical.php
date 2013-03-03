<?php
$page_load_testing_started = microtime(true);

/*
 * Don't allow access through biz61.inmotionhosting.com. Otherwise errors will occur
 */
if($_SERVER['HTTP_HOST'] == 'biz61.inmotionhosting.com') {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: http://www.runthatrace.com/");
	die();
}

/*
 * All the files critical to every page and required to run
 */
session_set_cookie_params(86400);
session_start();

date_default_timezone_set('America/Denver');

require_once 'core/constants.php';

require_once ROOT .'classes/mysqlhandler.php';
$sql = new MysqlHandler();

require_once ROOT .'classes/utility.php';
$util = new Utility();

require_once ROOT .'classes/mail.php';
$mail = new Mail();

require_once ROOT .'classes/user.php';
User::cacheUsers('id="'. ((int)$_SESSION['user_id']) .'"');
$user = User::getUser((int)$_SESSION['user_id']);

require_once ROOT .'classes/eventhandler.php';


function writeFile($file, $data) {
	$fh = fopen($file, 'a') or die("can't open file");
	fwrite($fh, $data);
	fclose($fh);
}

?>
