<?php
require_once 'include/critical.php';
require_once ROOT .'classes/event.php';

$e_year				= (int)$_GET['event_year'];
$cleanPermalink		= $util->cleanPermalink($_GET['event_name']);
$event				= EventHandler::cacheAndGetEventByPermalink($cleanPermalink, $e_year);
$exists				= $event !== null;
$can_edit			= $exists ? $event->hasAccess($user->getId()):false;
$title				= $exists && $can_edit ? '"'. $event->getName() .'"':'Event';
$e_id				= $exists ? $event->getId():0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title>Run That Race - View Registrants: <?php echo $title; ?></title>
	<?php
	include ROOT .'include/core/head_tag.php';
	?>
	<!-- View Registrants-->
    <script type="text/javascript" src="/include/view-registrants.js"></script>
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
			<h2 class="float-l"><?php echo $title; ?> Registrants</h2>
			<?php
			if(!$user->loggedIn())
				include ROOT .'include/unauthorized.php';
			else if(!$exists)
				echo '<h4>Invalid event</h4>';
			else if(!$can_edit)
				echo '<h4>Permissions denied</h4>';
			else {
				include ROOT .'include/pages/view-registrants.php';
			}
			?>
		</div>
		<div id="footer">
			<?php include ROOT .'include/core/footer.php'; ?>
		</div>
	</div>
</body>
</html>
