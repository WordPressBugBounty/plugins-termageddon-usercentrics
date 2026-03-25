if (typeof uc === "undefined") {
	// Usercentrics not loaded (e.g., visitor outside all geo-location regions).
	// Nothing to configure — exit silently.
} else {
	const config = window.termageddon_usercentrics_advanced_config;
	if (!config) {
		console.error(
			"[termageddon-usercentrics] Advanced config: No config found"
		);
	} else {
		// Get the configured providers
		const disabledBlockingProviders = config.disabledBlockingProviders || [];
		const autoRefreshProviders = config.autoRefreshProviders || [];

		// Handle deactivateBlocking
		if (disabledBlockingProviders.length > 0)
			uc.deactivateBlocking(disabledBlockingProviders);

		// Handle reloadOnOptIn
		if (autoRefreshProviders.length > 0)
			autoRefreshProviders.forEach((provider) => {
				uc.reloadOnOptIn(provider);
			});
	}
}
