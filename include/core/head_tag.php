	<!-- Layout styles -->
	<link rel="stylesheet" type="text/css" href="/include/core/css/structure.css" />
	<link rel="stylesheet" type="text/css" href="/include/core/css/main.css" />
	<link rel="stylesheet" type="text/css" href="/include/core/plugins/css/black-tie/jquery-ui-1.8rc3.custom.css" />
	<link rel="stylesheet" type="text/css" href="/include/core/plugins/fancybox/jquery.fancybox-1.3.1.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/include/core/plugins/jquery-tooltip/jquery.tooltip.css" />
	<!--[if lte IE 7]>
	<link rel="stylesheet" type="text/css" href="/include/core/css/ie7.css" />
	<![endif]-->
	<!--[if IE 6]>
	<link rel="stylesheet" type="text/css" href="/include/core/css/ie6.css" />
	<![endif]-->

	<!-- Plugins -->
	<script type="text/javascript" src="/include/core/plugins/js/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="/include/core/plugins/js/jquery-ui-1.8.1.custom.min.js"></script>
	<script type="text/javascript" src="/include/core/plugins/form-submit.js"></script>
	<script type="text/javascript" src="/include/core/plugins/highlightFade.js"></script>
	<script type="text/javascript" src="/include/core/plugins/fancybox/jquery.fancybox-1.3.1.pack.js"></script>
	<script type="text/javascript" src="/include/core/plugins/jquery.livequery.js"></script>
	<script type="text/javascript" src="/include/core/plugins/jquery-tooltip/jquery.tooltip.pack.js"></script>

	<!-- Dropdown Menu -->
	<link rel="stylesheet" type="text/css" href="/include/core/plugins/sfmenu/superfish.css" media="screen">
	<script type="text/javascript" src="/include/core/plugins/sfmenu/superfish.js"></script>

	<!-- Main scripts -->
	<script type="text/javascript" src="/include/core/core.js"></script>
<?php
	if(TESTING) {
		echo
		'
		<script type="text/javascript">
		$(document).ready(function() {
			document.getElementById("left-header").style.backgroundImage = "url(/include/core/css/images/header_left_test.png)";
		});
		</script>
		';
	}s
?>