function uc_integration_setup(iID, service) {
	uc.blockElements({
		[iID]: "figure.presto-block-video.presto-provider-" + service,
	});
	uc.reloadOnOptIn(iID);
	uc.reloadOnOptOut(iID);
}
uc_integration_setup("BJz7qNsdj-7", "youtube"); // Youtube
uc_integration_setup("HyEX5Nidi-m", "vimeo"); // Vimeo
