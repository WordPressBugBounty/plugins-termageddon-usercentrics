jQuery(function ($) {
	let animationSpeed = 0;
	let initialLoad = false;
	// ======================================= //
	// ======== Logged In User Toggle ======== //
	// ======================================= //
	let loggedIn = $(
		".tu-tab-settings .tu-toggle-section input#termageddon_usercentrics_disable_logged_in"
	);
	let editor = $(
		".tu-tab-settings .tu-toggle-section input#termageddon_usercentrics_disable_editor"
	);
	let admin = $(
		".tu-tab-settings .tu-toggle-section input#termageddon_usercentrics_disable_admin"
	);

	const check = (elem, checked = true) => {
		console.log(elem, checked);
		elem.prop("checked", checked)
			.attr("readonly", checked)
			.trigger("change");
	};

	if (loggedIn.length === 1)
		loggedIn
			.off("change.tu")
			.on("change.tu", function () {
				//Check them based on the value of the logged in checkbox.
				if ($(this).is(":checked") || initialLoad) {
					check(editor, $(this).is(":checked"));
					check(admin, $(this).is(":checked"));
				}
			})
			.trigger("change");

	// ============================================ //
	// ======== Geolocation Enabled Toggle ======== //
	// ============================================ //

	let geolocationToggle = $(
		"input#termageddon_usercentrics_toggle_geolocation"
	);

	let policyToggles = $(".tu-tab-geolocation .tu-toggle-section input");

	if (geolocationToggle.length === 1) {
		geolocationToggle
			.off("change.tu")
			.on("change.tu", function () {
				if ($(this).is(":checked")) {
					//Show Sections
					$(
						".tu-tab-geolocation .tu-section-settings > div"
					).slideDown(animationSpeed);
				} else {
					//Hide Sections
					$(".tu-tab-geolocation .tu-section-settings > div").slideUp(
						animationSpeed
					);
				}
			})
			.trigger("change");

		policyToggles
			.off("change.tu")
			.on("change.tu", function () {
				//Update the master toggle to match the state of all of the toggles.
				if (policyToggles.is(":checked")) {
					jQuery(
						"#no-geolocation-locations-selected,#no-geolocation-locations-selected-top"
					).slideUp(animationSpeed);
				} else {
					jQuery("#no-geolocation-locations-selected").slideDown(
						animationSpeed
					);
				}
			})
			.trigger("change");
	}

	animationSpeed = 300;
	initialLoad = true;
});
