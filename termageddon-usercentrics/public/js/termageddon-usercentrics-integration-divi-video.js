window.addEventListener("load", function () {
	document.querySelectorAll("div.et_pb_video_overlay_hover").forEach(function (el) {
		el.addEventListener("click", function (e) {
			var overlay = this.closest("div.et_pb_video_overlay");
			if (overlay) overlay.style.display = "none";
		});
		var playLink = el.querySelector("a.et_pb_video_play");
		if (playLink) playLink.setAttribute("href", "javascript:void(0)");
	});
});
