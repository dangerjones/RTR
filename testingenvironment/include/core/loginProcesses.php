<?php
require_once '../critical.php';
require_once ROOT .'classes/registration.php';
require_once ROOT .'classes/loggingin.php';

/*
 * Handles all the processing required to things
 * related to logging in, registering, etc.
 *
 * Then outputs login-related information
 */
$email		= isset($_POST['email']) ? $_POST['email'] : '';
$pass		= isset($_POST['pass']) ? $_POST['pass'] : '';
$retype		= isset($_POST['retype_pass']) ? $_POST['retype_pass'] : '';
$fname		= isset($_POST['fname']) ? $_POST['fname'] : '';
$lname		= isset($_POST['lname']) ? $_POST['lname'] : '';
$bot		= isset($_POST['b_name']) ? $_POST['b_name'] : '';
$submit		= isset($_GET['f_submit']) ? $_GET['f_submit'] : $_POST['f_submit'];
$refer		= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$isAjax		= $util->isAjax();

/*
 * Bot control. Doesn't process form if these values are changed.
 * If they are changed, it's likely done by a bot.
 */
if(!empty($bot))
    die(CRITICAL_ERROR);

/*
 * According to the form submitted, do different processes
 */
switch($submit) {
    case 'login':
        login($email, $pass);
        break;
    case 'register':
        register($email, $pass, $retype, $fname, $lname);
        break;
    case 'logout':
        $user->logout();
        break;
    default:

}
/*
 * After registration processes, redirect to initial page
 */
redir($refer);


/*
 * Form handling functions
 */
function login($email, $pass) {
    $log = new LoggingIn($email, $pass);
    $log->login();

    if($log->numErr() > 0) {
        errToSession($log->getErr(), 'log');
		redir('?login=0');
    }
}

function register($email, $pass, $retype, $fname, $lname) {
    $reg = new Registration($email, $pass, $retype, $fname, $lname);
    $reg->addUser();
    if($reg->numErr() > 0)
        errToSession($reg->getErr(), 'reg');
    else 
        login($email, $pass);
}

function redir($s) {
	global $isAjax;
	if(!$isAjax)
		header("Location: ". $s);
    die();
}

function errToSession($errors, $errType) {
	global $isAjax, $util;

    $_SESSION['err'][$errType] = array();

	if(count($errors) == 1) {
		$output .= $errors[0];
	} else {
		$output .= '<ul>';
		foreach($errors as $e) {
			$output .= '<li>'. $e .'</li>';
		}
		$output .= '</ul>';
	}

	$output = $util->errorFormat($output);

	if(!$isAjax)
	    $_SESSION['err'][$errType] = $output;
	else
		echo $output;
}

?>