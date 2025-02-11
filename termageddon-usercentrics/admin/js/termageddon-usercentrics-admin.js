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

	// ============================================ //
	// ======== Conversion from Embed Code ======== //
	// ============================================ //

	let migrationButton = $("#run_settings_migration");

	if (migrationButton.length === 1) {
		migrationButton.off("click.tu").on("click.tu", function () {
			// Get the current embed code
			const embedCode = document.querySelector(
				"#termageddon_usercentrics_embed_code"
			).value;

			// Try to extract settings ID using regex
			const settingsIdMatch = embedCode.match(
				/data-settings-id="([^"]+)"/
			);

			if (!settingsIdMatch || !settingsIdMatch[1]) {
				alert(
					"Unable to find settings ID in embed code. Please ensure your embed code section contains a data-settings-id attribute. Please contact our support team if would like assistance."
				);
				return;
			}

			const settingsId = settingsIdMatch[1];

			// Update the settings ID field
			document.querySelector(
				"#termageddon_usercentrics_settings_id"
			).value = settingsId;

			// Remove items from the embed code field:
			// <link rel="preconnect" href="//privacy-proxy.usercentrics.eu">
			// <link rel="preload" href="//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js" as="script">
			// <script type="application/javascript" src="https://privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js"></script>
			// <script id="usercentrics-cmp" src="https://web.cmp.usercentrics.eu/ui/loader.js"   async></script>
			// <script>uc.setCustomTranslations('https://termageddon.ams3.cdn.digitaloceanspaces.com/translations/');</script>

			// Remove the items from the embed code field
			document.querySelector(
				"#termageddon_usercentrics_embed_code"
			).value = embedCode
				.replace(
					'<link rel="preconnect" href="//privacy-proxy.usercentrics.eu">',
					""
				)
				.replace(
					'<link rel="preload" href="//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js" as="script">',
					""
				)
				.replace(
					'<script type="application/javascript" src="https://privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js"></script>',
					""
				)
				.replace(
					/<script.*id="usercentrics-cmp".*async>.*<\/script>/g,
					""
				)
				.replace(
					"<script>uc.setCustomTranslations('https://termageddon.ams3.cdn.digitaloceanspaces.com/translations/');</script>",
					""
				)
				.trim();

			// Update the migration message
			const migrationMessage = $(".migration-message");

			migrationMessage
				.removeClass("tu-alert-error")
				.addClass("tu-alert-success");
			migrationMessage.find("strong").text("Conversion Complete");
			migrationMessage.find("p.alert-description").html(
				`The embed code has been converted to a settings ID. All custom scripts outside of the original embed code have been maintained.<br/></br>
					<strong><em>The changes have been submitted.</em></strong>`
			);

			// Submit the form to save changes
			$(".tab-content form input.button").click();
		});
	}
});
