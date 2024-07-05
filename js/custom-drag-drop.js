/**
 * Custom Drag and Drop Script
 * 
 * This script enables drag-and-drop sorting for the list of posts and sends the updated order
 * to the WordPress backend via an AJAX request.
 *
 * Dependencies: jQuery, jQuery UI Sortable
 * 
 * @package WP_Post_Order
 */

jQuery(document).ready(function($) {
    /**
     * Initialize sortable functionality on each posts list
     */
    $("ul[id^='sortable-']").sortable({
        update: function(event, ui) {
            // Get the new order of the posts
            var postOrder = $(this).sortable('toArray').toString();

            // Send the new order to the backend via AJAX
            $.ajax({
                url: fpo_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'save_post_order',
                    order: postOrder,
                    nonce: fpo_ajax_object.nonce
                },
                success: function(response) {
                    if (response === 'success') {
                        console.log('Order saved successfully!');
                    } else {
                        console.log('Order saving failed!');
                    }
                }
            });
        }
    });
});
