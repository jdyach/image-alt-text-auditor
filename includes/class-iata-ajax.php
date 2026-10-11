<?php
/**
 * Handles the AJAX request for saving alt text inline.
 *
 * @package Image_Alt_Text_Auditor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IATA_Ajax {

    public static function init(): void {
        add_action( 'wp_ajax_iata_save_alt', [ __CLASS__, 'save_alt' ] );
    }

    /**
     * Saves the alt text for a single attachment via AJAX.
     * Responds with JSON.
     */
    public static function save_alt(): void {
        // Verify nonce.
        check_ajax_referer( 'iata_save_alt', 'nonce' );

        // Check capability.
        if ( ! current_user_can( 'upload_files' ) ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.', 'image-alt-text-auditor' ) ], 403 );
        }

        $attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
        $alt_text      = isset( $_POST['alt_text'] ) ? sanitize_text_field( wp_unslash( $_POST['alt_text'] ) ) : '';

        if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid attachment.', 'image-alt-text-auditor' ) ], 400 );
        }

        update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );

        wp_send_json_success( [
            'message'       => __( 'Alt text saved.', 'image-alt-text-auditor' ),
            'attachment_id' => $attachment_id,
            'alt_text'      => $alt_text,
        ] );
    }
}
