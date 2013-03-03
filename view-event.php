<?php
require_once 'include/critical.php';
require_once ROOT .'classes/event.php';

$e_year				= (int)$_GET['event_year'];
$cleanPermalink		= $util->cleanPermalink($_GET['event_name']);
$event				= EventHandler::cacheAndGetEventByPermalink($cleanPermalink, $e_year);
$exists				= $event !== null;
$title				= $exists ? $event->getName():'Event';
$e_id				= $exists ? $event->getId():0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title>Run That Race - <?php echo $title; ?></title>
	<?php
	include ROOT .'include/core/head_tag.php';
	?>
    <script type="text/javascript" src="/include/view-event.js"></script>
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
			<h2 class="float-l"><?php echo $title; ?></h2>
			<?php
			if(!$exists)
				echo '<h4>Invalid event</h4>';
			else {
				$e_status = $event->getStatus();
				if($e_status == ESTATUS_OK || $user->isAdmin())
					include ROOT .'include/pages/view-event.php';
				else if($e_status == ESTATUS_DENY)
					echo '<h4>Event has been denied.</h4>';
				else if($e_status == ESTATUS_WAITING)
					echo '<h4>Event is still waiting for approval</h4>';
			}
			?>
		</div>
		<div id="footer">
			<?php include ROOT .'include/core/footer.php'; ?>
		</div>
	</div>
</body>
</html>
