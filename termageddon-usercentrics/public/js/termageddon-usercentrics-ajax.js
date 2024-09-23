const tuCookieHideName = "tu-geoip-hide";
const tuDebug = termageddon_usercentrics_obj.debug === "true";
const tuPSLHide = termageddon_usercentrics_obj["psl_hide"] === "true";

if (tuDebug) console.log("UC: AJAX script initialized");

window.addEventListener("UC_UI_INITIALIZED", function () {
	const getCookie = (name) => {
		const value = `; ${document.cookie}`;
		const parts = value.split(`; ${name}=`);
		if (parts.length === 2) return parts.pop().split(";").shift();
	};

	const getQueryParams = (param) => {
		const params = new Proxy(new URLSearchParams(window.location.search), {
			get: (searchParams, prop) => searchParams.get(prop),
		});

		return params[param];
	};

	const setCookie = (name, value, days) => {
		var expires = "";
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
			expires = "; expires=" + date.toUTCString();
		}
		document.cookie = name + "=" + (value || "") + expires + "; path=/";
	};

	const updateCookieConsent = (hide) => {
		if (!hide) {
			if (tuDebug) console.log("UC: Showing consent widget");
			//Show Consent Options
			if (tuPSLHide)
				jQuery("#usercentrics-psl, .usercentrics-psl").show();

			jQuery("div#usercentrics-root").show();

			if (!UC_UI.isConsentRequired()) return UC_UI.closeCMP();
			return UC_UI.showFirstLayer();
		} else {
			if (tuDebug) console.log("UC: Hiding consent widget");
			//Hide Consent Options
			if (tuPSLHide)
				jQuery("#usercentrics-psl, .usercentrics-psl").hide();

			jQuery("div#usercentrics-root").hide();

			//Check for already acceptance.
			if (UC_UI.areAllConsentsAccepted()) return;
		}

		UC_UI.acceptAllConsents().then(() => {
			if (tuDebug) console.log("UC: All consents have been accepted.");

			UC_UI.closeCMP().then(() => {
				if (tuDebug) console.log("UC: CMP Widget has been closed.");
			});
		});
	};

	// Check for Usercentrics Integration
	if (typeof UC_UI === "undefined")
		return console.error("Usercentrics not loaded");

	//Check query variable from browser
	const query_hide =
		getQueryParams("enable-usercentrics") === "" ? true : false;

	//Check for local cookie to use instead of calling.
	const cookie_hide = getCookie(tuCookieHideName);
	if (cookie_hide != null && !tuDebug) {
		if (tuDebug)
			console.log(
				"UC: Cookie found.",
				(cookie_hide ? "Showing" : "Hiding") + " Usercentrics"
			);
		updateCookieConsent(cookie_hide === "true");
	} else {
		if (tuDebug) console.log("UC: Making AJAX Call");

		// Build Ajax Query.
		var data = {
			action: "uc_geolocation_lookup",
			nonce: termageddon_usercentrics_obj.nonce, // We pass php values differently!
		};

		if (typeof termageddon_usercentrics_obj.location !== "undefined")
			data["location"] = termageddon_usercentrics_obj.location;

		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery
			.post(termageddon_usercentrics_obj.ajax_url, data)
			.done(function (response) {
				if (!response.success)
					return console.error(
						"Unable to lookup location.",
						response.message || ""
					);

				if (!response.data)
					return console.error(
						"Location data was not provided.",
						response.data
					);

				const data = response.data;

				// Output debug message to console.
				if (tuDebug) {
					console.log(
						"TERMAGEDDON USERCENTRICS (AJAX)" +
							"\n" +
							"IP Address: " +
							data.ipAddress +
							"\n" +
							"City: " +
							(data.city || "Unknown") +
							"\n" +
							"State: " +
							(data.state || "Unknown") +
							"\n" +
							"Country: " +
							(data.country || "Unknown") +
							"\n" +
							"--" +
							"\n" +
							"Located in EU?: " +
							(data.inEU ? "Yes" : "No") +
							"\n" +
							"Located in UK?: " +
							(data.inUK ? "Yes" : "No") +
							"\n" +
							"Located in Canada?: " +
							(data.inCanada ? "Yes" : "No") +
							"\n" +
							"Located in California?: " +
							(data.inCalifornia ? "Yes" : "No") +
							"\n" +
							"Located in Virginia?: " +
							(data.inVirginia ? "Yes" : "No")
					);
				}

				if (query_hide) {
					if (tuDebug)
						console.log(
							"UC: Enabling due to query parameter override.",
							"Showing Usercentrics"
						);
					return updateCookieConsent(false);
				}

				//If you are not supposed to be hiding, show the CMP.
				setCookie(tuCookieHideName, data.hide ? "true" : "false");

				updateCookieConsent(data.hide);
			})
			.fail(function (response) {
				console.error(
					"Usercentrics: Invalid response returned. Showing widget as a default.",
					response
				);

				updateCookieConsent(false);
			});
	}
});
