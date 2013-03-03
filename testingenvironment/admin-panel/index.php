<?php
require_once '../include/critical.php';
require_once ROOT .'classes/event.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title><?php echo TITLE_PREFIX; ?>Events</title>
	<?php
	include ROOT .'include/core/head_tag.php';
	?>
	<!-- Events -->
    <link rel="stylesheet" type="text/css" href="/include/events/events.css" />
    <script type="text/javascript" src="/include/events/events.js"></script>
    <script type="text/javascript" src="/include/events/addevent.js"></script>

	<!-- Admin -->
    <script type="text/javascript" src="/admin-panel/admin.js"></script>
    <link rel="stylesheet" type="text/css" href="/admin-panel/admin.css" />
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
			include 'admin_controls.php';
			?>
		</div>
		<div id="footer">
			<?php include ROOT .'include/core/footer.php'; ?>
		</div>
	</div>
</body>
</html>