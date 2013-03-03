<?php
require_once 'include/critical.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title>Run That Race - Utah Elite</title>
	<?php
	include ROOT .'include/core/head_tag.php';
	?>
	<script src="/include/base.js"></script>
</head>
<body><?php
	include ROOT .'include/core/navigation.php';
	?>
	<div id="wrapper">
		<?php
		include ROOT .'include/core/head.php';
		?>
		<div id="content">
			<?php include ROOT .'racing-team/rightbar.php'; ?>
			<div id="content-main">
				<h2>Utah Elite</h2>
				<img src="/racing-team/images/racing-team-front.jpg" alt="Runners" class="float-l" />
				<p>
					Welcome to the Utah Elite homepage!  Use the menu at the right to access information about the team and our sponsors.
				</p>
				<p>
					We are currently accepting applications for the inaugural men's and women's teams.
				</p>
			</div>
		</div>
		<div id="footer">
			<?php include ROOT .'include/core/footer.php'; ?>
		</div>
	</div>
</body>
</html>
