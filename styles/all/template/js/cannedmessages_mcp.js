form_name = 'cannedmessage_add_edit';
text_name = 'cannedmessage_content';

(function($) { // Avoid conflicts with other libraries
	'use strict';
	phpbb.addAjaxCallback('row_down', function(res) {
		if (typeof res.success === 'undefined' || !res.success) {
			return;
		}

		var $firstLi = $(this).parents('li'),
			$secondLi = $firstLi.next();

		$firstLi.insertAfter($secondLi);
	});

	phpbb.addAjaxCallback('row_up', function(res) {
		if (typeof res.success === 'undefined' || !res.success) {
			return;
		}

		var $secondLi = $(this).parents('li'),
			$firstLi = $secondLi.prev();

		$secondLi.insertBefore($firstLi);
	});

	// This removes the parent row of the link or form that fired the callback.
	phpbb.addAjaxCallback('row_delete', function() {
		$(this).parents('li').remove();
	});

	// Remove emoji and other non-standard characters from title
	document.getElementById('cannedmessage_name').addEventListener('input', function(e) {
		const noEmoji = e.target.value.replace(/[\u{1F300}-\u{1F9FF}]|[\u{2700}-\u{27BF}]|[\u{2600}-\u{26FF}]|[\u{2300}-\u{23FF}]|[\u{1F000}-\u{1F6FF}]|[\u{2B00}-\u{2BFF}]/gu, '');

		if (e.target.value !== noEmoji) {
			e.target.value = noEmoji;
		}
	});

	$(function() {
		var $add_edit = $('#cannedmessage_add_edit'),
			$content = $('#cannedmessage_content_section'),
			$preview = $('#cannedmessage_preview'),
			$preview_btn = $('#preview'),
			$cancel_btn = $('#cancel'),
			$action_cancel = $('#action_cancel');

		$('#is_cat1, #is_cat0').on('change', function() {
			$content.add($preview).add($preview_btn).toggle($(this).is(':checked') && $(this).attr('id') === 'is_cat0');
		});

		$preview_btn.on('click', function() {
			$add_edit.prop('action', $add_edit.prop('action') + '#cannedmessage_preview');
		});

		$cancel_btn.on('click', function() {
			$add_edit.prop('action', $action_cancel.val());
		});
	});
})(jQuery); // Avoid conflicts with other libraries
