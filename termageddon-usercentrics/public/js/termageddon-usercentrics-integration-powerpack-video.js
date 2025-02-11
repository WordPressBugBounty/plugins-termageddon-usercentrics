window.addEventListener("uc_window", function (e) {
	if (e.detail && e.detail.event == "consent_status") {
		if (e.detail["YouTube Video"] === true) {
			document
				.querySelectorAll("iframe.pp-video-iframe[data-src]")
				.forEach((iframe) => {
					iframe.src = iframe.dataset.src;
				});
		}
	}
});
