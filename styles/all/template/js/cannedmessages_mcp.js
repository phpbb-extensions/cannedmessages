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

        $('#is_cat1').on('change', function() {
           toggleContent($(this).is(':checked'));
        });

        $('#is_cat0').on('change', function() {
            toggleContent(!$(this).is(':checked'));
        });

        $('#preview').on('click', function() {
            var action = $('#cannedmessage_add_edit').prop('action');

            $('#cannedmessage_add_edit').prop('action', action + '#cannedmessage_preview');
        });

        $('#cancel').on('click', function() {
            $('#cannedmessage_add_edit').prop('action', $('#action_cancel').val());
        });

        function toggleContent(isChecked) {
            if (isChecked) {
                $('#cannedmessage_content_section').hide();
                $('#preview').hide();
                $('#cannedmessage_preview').hide();
            }
            else {
                $('#cannedmessage_content_section').show();
                $('#preview').show();
                $('#cannedmessage_preview').show();
            }
        }
    });
})(jQuery); // Avoid conflicts with other libraries
