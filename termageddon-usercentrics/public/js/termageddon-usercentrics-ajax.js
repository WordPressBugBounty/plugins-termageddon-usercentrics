const tuCookieHideName = "tu-geoip-hide";
const tuCookieLocationName = "tu-geoip-location";
const tuDebug = termageddon_usercentrics_obj.debug === "true";
const tuPSLHide = termageddon_usercentrics_obj["psl_hide"] === "true";
const tuUseGeoApi = termageddon_usercentrics_obj.use_geo_api === "true";
const tuToggle = "div#usercentrics-root,aside#usercentrics-cmp-ui";

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

	const showElements = (selector) => {
		document.querySelectorAll(selector).forEach((el) => el.style.display = "");
	};

	const hideElements = (selector) => {
		document.querySelectorAll(selector).forEach((el) => el.style.display = "none");
	};

	const updateCookieConsent = (hide) => {
		if (!hide) {
			if (tuDebug) console.log("UC: Showing consent widget");
			//Show Consent Options
			if (tuPSLHide)
				showElements("#usercentrics-psl, .usercentrics-psl");

			showElements(tuToggle);

			if (!UC_UI.isConsentRequired()) return UC_UI.closeCMP();
			return UC_UI.showFirstLayer();
		} else {
			if (tuDebug) console.log("UC: Hiding consent widget");
			//Hide Consent Options
			if (tuPSLHide)
				hideElements("#usercentrics-psl, .usercentrics-psl");

			hideElements(tuToggle);

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
		return;
	}

	// =========================================================================
	// New hosted geolocation service (browser-side fetch + client-side decision).
	// Bypasses admin-ajax entirely.
	// =========================================================================
	if (tuUseGeoApi) {
		const finishWithGeoData = (geoData, persistLocationCookie) => {
			if (persistLocationCookie) {
				setCookie(tuCookieLocationName, JSON.stringify(geoData), 365);
			}

			if (query_hide) {
				if (tuDebug)
					console.log(
						"UC: Enabling due to query parameter override.",
						"Showing Usercentrics"
					);
				return updateCookieConsent(false);
			}

			const decision = shouldHideForGeo(
				geoData,
				termageddon_usercentrics_obj.geo_locations || {}
			);

			if (tuDebug) {
				console.log("UC: Decision computed", {
					hide: decision.hide,
					reason: decision.reason,
					geo: geoData,
					matches: decision.matches,
				});
			}

			setCookie(tuCookieHideName, decision.hide ? "true" : "false");
			updateCookieConsent(decision.hide);
		};

		// 1. Debug override always wins, and never persists.
		const debugOverride = termageddon_usercentrics_obj.geo_debug_override;
		if (debugOverride && typeof debugOverride === "object") {
			if (tuDebug) console.log("UC: Using geo debug override", debugOverride);
			return finishWithGeoData(debugOverride, false);
		}

		// 2. Cached location cookie? Use it (unless debug mode forces a refresh).
		const locationCookie = getCookie(tuCookieLocationName);
		if (locationCookie && !tuDebug) {
			try {
				const cached = JSON.parse(decodeURIComponent(locationCookie));
				if (cached && typeof cached === "object" && cached.country) {
					if (tuDebug) console.log("UC: Using cached geo cookie", cached);
					return finishWithGeoData(cached, false);
				}
			} catch (e) {
				if (tuDebug)
					console.log("UC: Failed to parse geo cookie, refetching", e);
			}
		}

		// 3. Fetch from the hosted geolocation API.
		if (tuDebug) console.log("UC: Fetching geolocation from hosted API");
		fetch(termageddon_usercentrics_obj.geo_api_url, {
			method: "GET",
			headers: { "X-Tm-Key": termageddon_usercentrics_obj.geo_api_key },
		})
			.then((res) => {
				if (!res.ok) throw new Error("HTTP " + res.status);
				return res.json();
			})
			.then((data) => {
				if (!data || typeof data !== "object" || !data.country) {
					throw new Error("Invalid geo response");
				}
				const geoData = {
					country: data.country,
					region_code: data.region_code || null,
					city: data.city || null,
				};
				finishWithGeoData(geoData, true);
			})
			.catch((err) => {
				console.error(
					"Usercentrics: Geolocation lookup failed. Showing widget as a default.",
					err
				);
				updateCookieConsent(false);
			});
		return;
	}

	// =========================================================================
	// Legacy MaxMind path: POST to admin-ajax, server decides hide/show.
	// =========================================================================
	if (tuDebug) console.log("UC: Making AJAX Call");

	// Build form data for POST request.
	var formData = new FormData();
	formData.append("action", "uc_geolocation_lookup");
	formData.append("nonce", termageddon_usercentrics_obj.nonce);

	if (typeof termageddon_usercentrics_obj.location !== "undefined")
		formData.append("location", termageddon_usercentrics_obj.location);

	fetch(termageddon_usercentrics_obj.ajax_url, {
		method: "POST",
		credentials: "same-origin",
		body: formData,
	})
		.then(function (res) {
			if (!res.ok) throw new Error("HTTP " + res.status);
			return res.json();
		})
		.then(function (response) {
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
						"Locations: ",
					data.locations
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
		.catch(function (err) {
			console.error(
				"Usercentrics: Invalid response returned. Showing widget as a default.",
				err
			);

			updateCookieConsent(false);
		});
});

/**
 * Decide whether to hide the consent widget given geo data + per-location rules.
 *
 * Mirrors PHP `Termageddon_Usercentrics::should_hide_due_to_location()`:
 *   - If the visitor is in *any* configured location AND it is disabled → HIDE.
 *   - If the visitor is in *no* configured location → HIDE.
 *   - Otherwise (in an enabled location) → SHOW.
 *
 * Returns the decision plus a per-location match breakdown and a human-readable
 * reason, so the debug console can show *why* a particular outcome was chosen.
 *
 * @param {{country:string, region_code:?string}} geo
 * @param {Object<string, {type:string, enabled:boolean, code?:string, codes?:string[], region_code?:string}>} locations
 * @returns {{hide:boolean, reason:string, matches:Object<string,{located_in:boolean,enabled:boolean}>}}
 */
function shouldHideForGeo(geo, locations) {
	const result = {
		hide: true,
		reason: "Invalid or missing geolocation data",
		matches: {},
	};

	if (!geo || !geo.country) return result;

	let firstDisabledMatch = null;
	let firstEnabledMatch = null;

	for (const key of Object.keys(locations)) {
		const rule = locations[key];
		if (!rule) {
			result.matches[key] = { located_in: false, enabled: false };
			continue;
		}

		let inLocation = false;
		switch (rule.type) {
			case "country_in_list":
				inLocation =
					Array.isArray(rule.codes) && rule.codes.indexOf(geo.country) !== -1;
				break;
			case "country":
				inLocation = rule.code === geo.country;
				break;
			case "us_state":
				inLocation =
					geo.country === "US" &&
					!!geo.region_code &&
					geo.region_code === rule.region_code;
				break;
			default:
				inLocation = false;
		}

		result.matches[key] = {
			located_in: inLocation,
			enabled: !!rule.enabled,
		};

		if (inLocation) {
			if (!rule.enabled && !firstDisabledMatch) firstDisabledMatch = key;
			if (rule.enabled && !firstEnabledMatch) firstEnabledMatch = key;
		}
	}

	if (firstDisabledMatch) {
		result.hide = true;
		result.reason =
			'Located in "' +
			firstDisabledMatch +
			'" but this region is disabled in settings';
	} else if (firstEnabledMatch) {
		result.hide = false;
		result.reason =
			'Located in "' + firstEnabledMatch + '" which is enabled';
	} else {
		result.hide = true;
		result.reason = "Not located in any configured region";
	}

	return result;
}
