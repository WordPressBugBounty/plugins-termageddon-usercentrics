window.addEventListener("load", function () {
	jQuery("div.et_pb_video_overlay_hover")
		.on("click", function (e) {
			jQuery(this).closest("div.et_pb_video_overlay").hide();
		})
		.find("a.et_pb_video_play")
		.attr("href", "javascript:void(0)");
});
