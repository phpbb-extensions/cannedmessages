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
