if (!uc)
	console.error(
		"[termageddon-usercentrics] Advanced config: Usercentrics not initialized"
	);
const config = window.termageddon_usercentrics_advanced_config;
if (!config)
	console.error(
		"[termageddon-usercentrics] Advanced config: No config found"
	);

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
