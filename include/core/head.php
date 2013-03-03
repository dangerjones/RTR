<?php
if(!defined('ROOT'))
	die();
?>
<div id="header">
	<div id="left-header"></div>
	<div id="right-header">
		<?php include ROOT .'include/core/login.php'; ?>
	</div>
	<input type="button" id="home" class="ui-button ui-widget ui-state-default" role="button" value="Home" style="left: 40px; top: -17px; border-radius: 4px 4px 0 0"/>
	<div id="bottom-header">
		<img src="/img/loaderbar.gif" alt="Loading..." id="main-loader" class="invisible" />
	</div>
</div>
<noscript>
	<div id="noscript">
		<div class="ui-state-highlight">
			You currently have javascript disabled or your browser does not support it.
			If possible, please enable javascript so that you may use our site without
			problems. Otherwise, many features will not be usable.
		</div>
	</div>
</noscript>
