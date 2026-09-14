(function () {
	function initialize() {
		if (typeof uc === 'undefined') {
			return false;
		}
		if (window.termageddonUsercentricsGravityFormsRecaptcha) {
			return true;
		}
		window.termageddonUsercentricsGravityFormsRecaptcha = true;

		const hostClass = 'termageddon-recaptcha-v3-consent';
		const targetClass = 'termageddon-recaptcha-v3-target';

		function addPlaceholders() {
			document.querySelectorAll('.gform_wrapper form').forEach(function (form) {
				if (!form.querySelector('.ginput_recaptchav3') || form.querySelector('.' + hostClass)) {
					return;
				}
				const submit = form.querySelector('[id^="gform_submit_button_"]');
				if (!submit) {
					return;
				}

				// Keep a stable host: Usercentrics replaces the target with its overlay.
				// Put it before the footer so flex layouts cannot place it beside Submit.
				const host = document.createElement('div');
				host.className = hostClass;
				const target = document.createElement('div');
				target.className = targetClass;
				host.appendChild(target);
				const footer = submit.closest('.gform_footer, .gform-footer, .gform_page_footer');
				const anchor = footer || submit;
				anchor.parentNode.insertBefore(host, anchor);
			});
		}

		uc.blockElements({
			'Hko_qNsui-Q': '.gfield--type-captcha, .gfield--type-recaptcha_checkbox, .' + targetClass,
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
}
.gform_wrapper .termageddon-recaptcha-v3-consent {
  width: 100%;
  grid-column: 1/-1;
}
.gform_wrapper .termageddon-recaptcha-v3-consent .uc-embedding-container {
  display: block !important;
  position: relative !important;
  width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
}
.gform_wrapper .termageddon-recaptcha-v3-consent .uc-embedding-wrapper {
  position: relative !important;
  top: auto !important;
  left: auto !important;
  transform: none !important;
  box-sizing: border-box !important;
  max-width: 100% !important;
  max-height: none !important;
  margin: 12px 0 !important;
}
`;
		if (!document.getElementById(style.id)) {
			document.head.appendChild(style);
		}

		function watchForms() {
			addPlaceholders();
			// Covers AJAX/multipage renders, including forms inserted after page load.
			new MutationObserver(addPlaceholders).observe(document.body, { childList: true, subtree: true });
		}
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', watchForms, { once: true });
		} else {
			watchForms();
		}
		return true;
	}

	if (!initialize()) {
		window.addEventListener('UC_UI_INITIALIZED', initialize, { once: true });
	}
})();
