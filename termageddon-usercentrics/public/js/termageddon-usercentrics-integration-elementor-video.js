window.addEventListener("load", function () {
	document.querySelectorAll(".pp-media-overlay").forEach(function (el) {
		el.addEventListener("click", function (e) {
			this.style.display = "none";
		});
	});
});
