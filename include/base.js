/*global window:false, document:false, $:false */
/*jslint indent:2 */
(function () {
	'use strict';

	var RTR = {};
	RTR.Util = {};
	RTR.Util.goHome = function () {
		window.location.href = "/";
	};
	$(document).ready(function () {
		var home = $("#home"),
				lh = $("#left-header");

		if (lh) {
			$("#left-header").click(RTR.Util.goHome);
		}

		if (home) {
			home.click(RTR.Util.goHome);
		}

	});
}());
