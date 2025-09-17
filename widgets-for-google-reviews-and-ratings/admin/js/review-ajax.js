jQuery(document).ready(function ($) {
    console.log('Nonce :', rep_admin_ajax.nonce); // Debug nonce before sending
    $('.repocean-action-button').on('click', function (e) {
        e.preventDefault();

        const action = $(this).data('action');
        const reviewUrl = "https://wordpress.org/support/plugin/widgets-for-google-reviews-and-ratings/reviews/#new-post";

        console.log('Nonce being sent:', rep_admin_ajax.nonce); // Debug nonce before sending

        if (action === 'reviewed') {
            window.open(reviewUrl, '_blank');
        }

        $.post(rep_admin_ajax.ajax_url, {
            action: 'repocean_handle_review_action',
            review_action: action,
            nonce: rep_admin_ajax.nonce // Ensure nonce is sent
        }, function (response) {
            console.log('Server response:', response); // Debug response
            if (response.success) {
                $('.repocean-review-notice').fadeOut();
                $('.repocean-review-notice-parent').hide();
            } else {
                console.error(response.data);
            }
        });
    });
});
