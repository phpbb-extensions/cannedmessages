form_name = 'cannedmessage_add_edit';
text_name = 'cannedmessage_content';

(function($) { // Avoid conflicts with other libraries
    'use strict';
    $(function() {

        $('#is_cat1').on('change', function() {
           toggleContent($(this).is(':checked'));
        });

        $('#is_cat0').on('change', function() {
            toggleContent(!$(this).is(':checked'));
        });

        function toggleContent(isChecked) {
            if (isChecked) {
                $('#cannedmessage_content_section').hide();
            }
            else {
                $('#cannedmessage_content_section').show();
            }
        }
    });
})(jQuery); // Avoid conflicts with other libraries