(function($, u_cannedmessage_selected) {
	'use strict';
	$(function() {
		$('#cannedmessages').on('change', function() {
			var cannedmessage_id = $(this).val();
			if (cannedmessage_id !== '') {
				$.get(u_cannedmessage_selected.replace(/0$/, cannedmessage_id), function(message_contents) {
					if (message_contents !== '') {
						insert_text(message_contents, false);
					}
				});
			}
		});
	});
})(jQuery, u_cannedmessage_selected);
