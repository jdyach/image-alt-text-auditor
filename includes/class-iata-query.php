<?php
/**
 * Handles all WP_Query calls for fetching images missing alt text.
 *
 * @package Image_Alt_Text_Auditor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IATA_Query {

    /**
     * Returns attachment posts where _wp_attachment_image_alt is empty or missing.
     *
     * @param int $per_page Number of results per page. -1 for all.
     * @param int $paged    Current page number.
     * @return WP_Query
     */
    public static function get_images_missing_alt( int $per_page = 20, int $paged = 1 ): WP_Query {
        return new WP_Query( [
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => 'date',
            'order'          => 'DESC',
            // Fetch images where the alt meta key is missing OR its value is an empty string.
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => '_wp_attachment_image_alt',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => '_wp_attachment_image_alt',
                    'value'   => '',
                    'compare' => '=',
                ],
            ],
        ] );
    }

    /**
     * Returns a simple count of images missing alt text.
     *
     * @return int
     */
    public static function count_images_missing_alt(): int {
        $query = self::get_images_missing_alt( -1 );
        return (int) $query->found_posts;
    }
}
