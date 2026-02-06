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
				.replace(/<script>uc.setCustomTranslations.*<\/script>/g, "")
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

	// ============================================ //
	// ======== Script Snippets Repeater ========== //
	// ============================================ //

	// Constants
	const SNIPPET_CONSTANTS = {
		ANIMATION_DURATION: 300,
		CARET_ROTATION: {
			COLLAPSED: "rotate(0deg)",
			EXPANDED: "rotate(180deg)",
		},
		SELECT2_OPTIONS: {
			width: "100%",
			placeholder: "Select a service...",
			allowClear: true,
		},
		DEFAULT_SERVICE_TEXT: "New Script Snippet",
	};

	const snippetsContainer = $("#termageddon-script-snippets-container");
	const addSnippetButton = $(".termageddon-add-snippet");
	const template = $("#termageddon-script-snippet-template");

	/**
	 * Count script tags in textarea
	 * @param {HTMLElement} textarea - The textarea element
	 * @returns {number} Number of script tags found
	 */
	function countScriptTags(textarea) {
		const content = $(textarea).val() || "";
		const matches = content.match(/<script/gi);
		return matches ? matches.length : 0;
	}

	/**
	 * Update badge with script count
	 * @param {jQuery} $row - The row element
	 */
	function updateScriptBadge($row) {
		const $textarea = $row.find(".termageddon-script-textarea");
		const $badge = $row.find(".termageddon-snippet-badge");
		const count = countScriptTags($textarea[0]);

		if (count > 0) {
			$badge.text(`${count} script${count !== 1 ? "s" : ""} identified`);
			$badge.addClass("termageddon-snippet-badge-success");
		} else {
			$badge.text("0 scripts identified");
			$badge.removeClass("termageddon-snippet-badge-success");
		}
	}

	/**
	 * Update service name in header
	 * @param {jQuery} $row - The row element
	 */
	function updateServiceName($row) {
		const $select = $row.find(".termageddon-singleselect");
		const $serviceName = $row.find(".termageddon-snippet-service-name");
		const selectedText = $select.find("option:selected").text();

		$serviceName.text(
			$select.val() && selectedText
				? selectedText
				: SNIPPET_CONSTANTS.DEFAULT_SERVICE_TEXT
		);
	}

	/**
	 * Toggle accordion open/closed state
	 * @param {jQuery} $header - The accordion header element
	 */
	function toggleAccordion($header) {
		const $content = $header.siblings(
			".termageddon-snippet-accordion-content"
		);
		const $caret = $header.find(".termageddon-snippet-caret");

		if ($content.is(":visible")) {
			// Collapsing
			$content.slideUp(SNIPPET_CONSTANTS.ANIMATION_DURATION, function () {
				$(this).addClass("collapsed");
			});
			$caret.css("transform", SNIPPET_CONSTANTS.CARET_ROTATION.COLLAPSED);
		} else {
			// Expanding
			$content
				.removeClass("collapsed")
				.slideDown(SNIPPET_CONSTANTS.ANIMATION_DURATION);
			$caret.css("transform", SNIPPET_CONSTANTS.CARET_ROTATION.EXPANDED);
		}
	}

	/**
	 * Set accordion state without animation
	 * @param {jQuery} $content - The accordion content element
	 * @param {jQuery} $caret - The caret icon element
	 * @param {boolean} isCollapsed - Whether the accordion should be collapsed
	 */
	function setAccordionState($content, $caret, isCollapsed) {
		if (isCollapsed) {
			$content.hide();
			$caret.css("transform", SNIPPET_CONSTANTS.CARET_ROTATION.COLLAPSED);
		} else {
			$content.show();
			$caret.css("transform", SNIPPET_CONSTANTS.CARET_ROTATION.EXPANDED);
		}
	}

	/**
	 * Initialize accordion for a row
	 * @param {jQuery} $row - The row element
	 */
	function initAccordionRow($row) {
		// Update badge and service name on load
		updateScriptBadge($row);
		updateServiceName($row);

		// Set initial caret rotation based on collapsed state
		const $content = $row.find(".termageddon-snippet-accordion-content");
		const $caret = $row.find(".termageddon-snippet-caret");
		setAccordionState($content, $caret, $content.hasClass("collapsed"));
	}

	/**
	 * Initialize Select2 for single-select fields
	 * @param {jQuery} $element - Optional specific element to initialize, or all if not provided
	 */
	function initSelect2($element) {
		const $targets = $element || $(".termageddon-singleselect");

		$targets.each(function () {
			if (!$(this).hasClass("select2-hidden-accessible")) {
				$(this).select2(SNIPPET_CONSTANTS.SELECT2_OPTIONS);
			}
		});
	}

	// Initialize on page load
	if (snippetsContainer.length) {
		initSelect2();
		snippetsContainer
			.find(".termageddon-script-snippet-row")
			.each(function () {
				initAccordionRow($(this));
			});
	}

	// Accordion header click handler
	$(document).on(
		"click.tu",
		".termageddon-snippet-accordion-header",
		function (e) {
			// Don't toggle if clicking on remove button
			if ($(e.target).closest(".termageddon-remove-snippet").length) {
				return;
			}
			toggleAccordion($(this));
		}
	);

	// Update service name when dropdown changes (handles both regular and Select2)
	$(document).on(
		"change.tu select2:select.tu select2:clear.tu",
		".termageddon-singleselect",
		function () {
			const $row = $(this).closest(".termageddon-script-snippet-row");
			updateServiceName($row);
		}
	);

	// Update badge when textarea changes
	$(document).on(
		"input.tu change.tu",
		".termageddon-script-textarea",
		function () {
			const $row = $(this).closest(".termageddon-script-snippet-row");
			updateScriptBadge($row);
		}
	);

	/**
	 * Get the next available index for a new snippet
	 * @returns {number} The next index to use
	 */
	function getNextSnippetIndex() {
		let maxIndex = -1;
		snippetsContainer
			.find(".termageddon-script-snippet-row")
			.each(function () {
				const index = parseInt($(this).attr("data-index"), 10);
				if (!isNaN(index) && index > maxIndex) {
					maxIndex = index;
				}
			});
		return maxIndex + 1;
	}

	// Add new snippet
	if (addSnippetButton.length) {
		addSnippetButton.off("click.tu").on("click.tu", function (e) {
			e.preventDefault();

			const newIndex = getNextSnippetIndex();

			// Get template HTML
			let templateHtml = template.html();
			if (!templateHtml) {
				console.error("Template not found");
				return;
			}

			// Replace template placeholders
			templateHtml = templateHtml
				.replace(/\{\{INDEX\}\}/g, newIndex)
				.replace(/\{\{INDEX_PLUS_ONE\}\}/g, newIndex + 1);

			// Append new row
			const $newRow = $(templateHtml);
			snippetsContainer.append($newRow);

			// Initialize Select2 for the new select field
			const $newSelect = $newRow.find(".termageddon-singleselect");
			initSelect2($newSelect);

			// Update badge and service name
			updateScriptBadge($newRow);
			updateServiceName($newRow);

			// Set to expanded state (no collapsed class, ensure visible)
			const $content = $newRow.find(
				".termageddon-snippet-accordion-content"
			);
			const $caret = $newRow.find(".termageddon-snippet-caret");
			setAccordionState($content, $caret, false);
		});
	}

	/**
	 * Update an attribute by replacing old index with new index
	 * @param {jQuery} $elem - The element to update
	 * @param {string} attrName - The attribute name (id, name, for)
	 * @param {number} oldIndex - The old index to replace
	 * @param {number} newIndex - The new index to use
	 * @param {string} pattern - The pattern to match ('_' for ids, '[' for names)
	 */
	function updateIndexInAttribute(
		$elem,
		attrName,
		oldIndex,
		newIndex,
		pattern
	) {
		const attrValue = $elem.attr(attrName);
		if (!attrValue) return;

		let searchStr, replaceStr;
		if (pattern === "_") {
			searchStr = `_${oldIndex}_`;
			replaceStr = `_${newIndex}_`;
		} else if (pattern === "[") {
			searchStr = `[${oldIndex}]`;
			replaceStr = `[${newIndex}]`;
		}

		if (attrValue.includes(searchStr)) {
			$elem.attr(attrName, attrValue.replace(searchStr, replaceStr));
			return attrValue; // Return old value for label updates
		}
		return null;
	}

	/**
	 * Re-index all snippet rows after removal
	 */
	function reindexSnippets() {
		snippetsContainer
			.find(".termageddon-script-snippet-row")
			.each(function (index) {
				const $currentRow = $(this);
				const oldIndex = parseInt($currentRow.attr("data-index"), 10);

				if (isNaN(oldIndex) || oldIndex === index) {
					return; // Skip if already correct
				}

				// Update data-index
				$currentRow.attr("data-index", index);

				// Update IDs, names, and labels
				$currentRow.find("select, textarea, label").each(function () {
					const $elem = $(this);

					// Update ID and corresponding labels
					const oldId = updateIndexInAttribute(
						$elem,
						"id",
						oldIndex,
						index,
						"_"
					);
					if (oldId) {
						const $label = $currentRow.find(
							`label[for="${oldId}"]`
						);
						if ($label.length) {
							const newId = $elem.attr("id");
							$label.attr("for", newId);
						}
					}

					// Update name attribute
					updateIndexInAttribute($elem, "name", oldIndex, index, "[");

					// Update for attribute
					updateIndexInAttribute($elem, "for", oldIndex, index, "_");
				});

				// Re-initialize accordion after re-indexing
				initAccordionRow($currentRow);
			});
	}

	// Remove snippet
	$(document).on("click.tu", ".termageddon-remove-snippet", function (e) {
		e.preventDefault();
		e.stopPropagation(); // Prevent accordion from toggling

		if (
			confirm(
				"Are you sure you want to remove this script snippet? This action cannot be undone."
			)
		) {
			const $row = $(this).closest(".termageddon-script-snippet-row");
			$row.remove();
			reindexSnippets();
		}
	});
});
