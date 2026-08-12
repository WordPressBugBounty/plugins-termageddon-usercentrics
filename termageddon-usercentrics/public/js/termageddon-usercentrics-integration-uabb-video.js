(function () {
	function initialize() {
		if (window.termageddonUsercentricsUabbVideoIntegration) {
			return true;
		}

		if (typeof uc === "undefined") {
			return false;
		}

		window.termageddonUsercentricsUabbVideoIntegration = true;

		var markerClass = "termageddon-usercentrics-uabb-youtube-modal";
		var waitingForPopup = false;

		document.addEventListener(
		"click",
		function (event) {
			if (!event.target || typeof event.target.closest !== "function") {
				return;
			}

			var trigger = event.target.closest("a.uabb-video-gallery-fancybox");

			if (!trigger) {
				return;
			}

			var videoUrl =
				trigger.getAttribute("data-url") ||
				trigger.getAttribute("href") ||
				"";
			var isYouTube = /(?:youtube(?:-nocookie)?\.com|youtu\.be)\//i.test(
				videoUrl,
			);

			document.documentElement.classList.toggle(markerClass, isYouTube);
			waitingForPopup = isYouTube;
		},
		true,
	);

		new MutationObserver(function () {
		var popup = document.querySelector(".mfp-wrap .mfp-iframe-scaler");

		if (waitingForPopup && popup) {
			waitingForPopup = false;
		} else if (!waitingForPopup && !popup) {
			document.documentElement.classList.remove(markerClass);
		}
		}).observe(document.documentElement, { childList: true, subtree: true });

		uc.blockElements({
		"BJz7qNsdj-7":
			"html.termageddon-usercentrics-uabb-youtube-modal .mfp-iframe-scaler",
		});

		if (!document.getElementById("termageddon-usercentrics-integration-uabb-video-style")) {
			var style = document.createElement("style");
			style.id = "termageddon-usercentrics-integration-uabb-video-style";
			style.textContent = `
.uabb-video__outer-wrap:before {
    z-index: 0 !important;
}
`;
			document.head.appendChild(style);
		}

		return true;
	}

	if (!initialize()) {
		window.addEventListener("UC_UI_INITIALIZED", initialize, { once: true });
	}
})();
