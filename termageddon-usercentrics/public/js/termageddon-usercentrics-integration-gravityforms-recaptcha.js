if (typeof uc !== 'undefined') {
	uc.blockElements({
		'Hko_qNsui-Q': '.gfield--type-captcha',
	});
	uc.reloadOnOptIn('Hko_qNsui-Q');

	const style = document.createElement('style');
	style.id = 'termageddon-usercentrics-integration-gravityforms-recaptcha-style';
	style.textContent = `
.gform_wrapper .uc-embedding-container {
  all: revert !important;
  display: revert !important;
  grid-column: 1/-1 !important;
}
.gform_wrapper .uc-embedding-container .uc-embedding-wrapper {
  all: revert !important;
  display: revert !important;
  width: 372px !important;
  max-width: calc(100% - 70px) !important;
  max-height: calc(100% - 35px) !important;
  background: #FFF !important;
  border-radius: 8px !important;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.5) !important;
  position: absolute !important;
  padding: 12px 24px !important;
  top: 50% !important;
  left: 50% !important;
  text-align: center !important;
  font-size: 14px !important;
  line-height: 1.5 !important;
  transform: translateX(-50%) translateY(-50%) !important;
  display: -webkit-box !important;
  display: -ms-flexbox !important;
  display: flex !important;
  flex-direction: column !important;
  overflow: auto !important;
  font-family: BlinkMacSystemFont,-apple-system,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Fira Sans,Droid Sans,Helvetica Neue,Helvetica,Arial,sans-serif !important;
}
.gform_wrapper .uc-embedding-container .uc-embedding-buttons {
  gap: 6px !important;
  display: flex !important;
  justify-content: center !important;
  margin: 8px !important;
}
.gform_wrapper .uc-embedding-container button.uc-embedding-more-info {
  background-color: #F5F5F5 !important;
  color: black !important;
}
.gform_wrapper .uc-embedding-container p.not-existing-service {
  display: none !important;
}`;
	if (!document.getElementById(style.id)) {
		document.head.appendChild(style);
	}
}
