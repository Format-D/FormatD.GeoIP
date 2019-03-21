
export default function locationBasedDimensionSwitchPrompt() {

	$(document).ready(function() {
		let prompt = $('#lbds-prompt'),
			promptCloseButton = $('#lbds-button-close');

		if (!prompt.length) {
			return;
		}

		if(document.cookie.indexOf('lbds_status=1') == -1) {
			prompt.show();
		}

		promptCloseButton.on('click', function () {
			document.cookie = 'lbds_status=1;path=/';
			prompt.fadeOut();
		});
	});

}
