<?php
require_once 'include/critical.php';
$util->forceSSL();
require_once ROOT .'classes/event.php';

$e_year				= (int)$_GET['event_year'];
$cleanPermalink		= $util->cleanPermalink($_GET['event_name']);
$event				= EventHandler::cacheAndGetEventByPermalink($cleanPermalink, $e_year);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title>Run That Race - Event Registration</title>
	<?php
	include ROOT .'include/core/head_tag.php';
	?>
    <script type="text/javascript" src="/include/register.js"></script>
    <script type="text/javascript" src="/include/base.js"></script>
</head>
<body><?php
	include ROOT .'include/core/navigation.php';
	?>
	<div id="wrapper">
		<?php
		include ROOT .'include/core/head.php';
		?>
		<div id="content">
			<?php
			if($user->loggedIn()) {
				include ROOT .'include/pages/race-registration.php';
			} else {
				include ROOT .'include/event-registration-unauthorized.php';
				include ROOT .'include/accountform.php';
			}
			?>
		</div>
		<div id="footer">
			<?php include ROOT .'include/core/footer.php'; ?>
		</div>
	</div>
</body>
</html>
