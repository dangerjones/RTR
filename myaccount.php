<?php
require_once 'include/critical.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title>Run That Race - My Account</title>
	<?php
	include ROOT .'include/core/head_tag.php';
	?>
    <link rel="stylesheet" type="text/css" href="/include/core/css/form.css" />
    <script type="text/javascript" src="/include/myaccount.js"></script>
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
			<h2>My Account</h2>
			<?php
			if(!$user->loggedIn())
				include ROOT .'include/unauthorized.php';
			else
				require ROOT .'include/pages/myaccount.php';
			?>
		</div>
		<div id="footer">
			<?php include ROOT .'include/core/footer.php'; ?>
		</div>
	</div>
</body>
</html>
