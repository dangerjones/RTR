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
    <title>Run That Race - Edit <?php echo $title; ?></title>
	<?php
	include ROOT .'include/core/head_tag.php';
	?>
	<!-- Uploadify plugin -->
	<script type="text/javascript" src="/include/core/plugins/uploadify/swfobject.js"></script>
	<script type="text/javascript" src="/include/core/plugins/uploadify/jquery.uploadify.v2.1.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/include/core/plugins/uploadify/uploadify.css" />

	<!-- Edit Events -->
    <script type="text/javascript" src="/include/edit-events.js"></script>
    <script type="text/javascript" src="/include/base.js"></script>

    <link rel="stylesheet" type="text/css" href="/include/core/css/form.css" />
</head>
<body><?php
	include ROOT .'include/core/navigation.php';
	?>
	<div id="wrapper">
		<?php
		include ROOT .'include/core/head.php';
		?>
		<div id="content">
			<h2 class="float-l">Edit <span id="edit-event-page-title"><?php echo $title; ?></span></h2>
			<?php
			if(!$user->loggedIn())
				include ROOT .'include/unauthorized.php';
			else if(!$exists)
				echo '<h4>Invalid event</h4>';
			else if(!$can_edit)
				echo '<h4>Permissions denied</h4>';
			else {
				include ROOT .'include/pages/edit-event.php';
			}
			?>
		</div>
		<div id="footer">
			<?php include ROOT .'include/core/footer.php'; ?>
		</div>
	</div>
</body>
</html>
