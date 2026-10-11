<?php
/**
 * Registers the admin menu page and enqueues assets.
 *
 * @package Image_Alt_Text_Auditor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IATA_Admin {

    /** The page slug used throughout. */
    const PAGE_SLUG = 'image-alt-text-auditor';

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    /**
     * Add the plugin page under the Media menu.
     */
    public static function register_menu(): void {
        add_media_page(
            __( 'Image Alt Text Auditor', 'image-alt-text-auditor' ),
            __( 'Alt Text Audit', 'image-alt-text-auditor' ),
            'upload_files',
            self::PAGE_SLUG,
            [ __CLASS__, 'render_page' ]
        );
    }

    /**
     * Enqueue CSS and JS only on our admin page.
     */
    public static function enqueue_assets( string $hook ): void {
        if ( 'media_page_' . self::PAGE_SLUG !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'iata-admin',
            IATA_PLUGIN_URL . 'assets/css/admin.css',
            [],
            IATA_VERSION
        );

        wp_enqueue_script(
            'iata-admin',
            IATA_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            IATA_VERSION,
            true
        );

        // Pass data to JS.
        wp_localize_script( 'iata-admin', 'iataData', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'iata_save_alt' ),
            'i18n'    => [
                'saving'  => __( 'Saving…', 'image-alt-text-auditor' ),
                'saved'   => __( 'Saved!', 'image-alt-text-auditor' ),
                'error'   => __( 'Error saving. Please try again.', 'image-alt-text-auditor' ),
                'confirm' => __( 'Alt text updated.', 'image-alt-text-auditor' ),
            ],
        ] );
    }

    /**
     * Render the full admin audit page.
     */
    public static function render_page(): void {
        if ( ! current_user_can( 'upload_files' ) ) {
            wp_die( esc_html__( 'You do not have permission to view this page.', 'image-alt-text-auditor' ) );
        }

        $paged     = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
        $per_page  = 20;
        $query     = IATA_Query::get_images_missing_alt( $per_page, $paged );
        $total     = $query->found_posts;
        $images    = $query->posts;
        $num_pages = $query->max_num_pages;
        ?>
        <div class="wrap iata-wrap">
            <h1 class="wp-heading-inline">
                <?php esc_html_e( 'Image Alt Text Auditor', 'image-alt-text-auditor' ); ?>
            </h1>

            <p class="iata-subtitle">
                <?php
                printf(
                    // translators: %d: number of images missing alt text.
                    esc_html( _n(
                        '%d image is missing alt text.',
                        '%d images are missing alt text.',
                        $total,
                        'image-alt-text-auditor'
                    ) ),
                    (int) $total
                );
                ?>
            </p>

            <?php if ( empty( $images ) ) : ?>
                <div class="iata-all-clear">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <p><?php esc_html_e( 'All images in your media library have alt text. Great work!', 'image-alt-text-auditor' ); ?></p>
                </div>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped iata-table">
                    <thead>
                        <tr>
                            <th class="iata-col-thumb"><?php esc_html_e( 'Thumbnail', 'image-alt-text-auditor' ); ?></th>
                            <th class="iata-col-file"><?php esc_html_e( 'File', 'image-alt-text-auditor' ); ?></th>
                            <th class="iata-col-date"><?php esc_html_e( 'Uploaded', 'image-alt-text-auditor' ); ?></th>
                            <th class="iata-col-alt"><?php esc_html_e( 'Alt Text', 'image-alt-text-auditor' ); ?></th>
                            <th class="iata-col-action"><?php esc_html_e( 'Action', 'image-alt-text-auditor' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $images as $image ) :
                            $thumb     = wp_get_attachment_image( $image->ID, [ 80, 80 ] );
                            $filename  = basename( get_attached_file( $image->ID ) );
                            $edit_link = get_edit_post_link( $image->ID );
                            $date      = get_the_date( 'M j, Y', $image );
                        ?>
                        <tr id="iata-row-<?php echo esc_attr( $image->ID ); ?>" data-id="<?php echo esc_attr( $image->ID ); ?>">
                            <td class="iata-col-thumb">
                                <?php if ( $thumb ) : ?>
                                    <a href="<?php echo esc_url( $edit_link ); ?>" target="_blank">
                                        <?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                                    </a>
                                <?php else : ?>
                                    <span class="dashicons dashicons-format-image iata-no-thumb"></span>
                                <?php endif; ?>
                            </td>
                            <td class="iata-col-file">
                                <a href="<?php echo esc_url( $edit_link ); ?>" target="_blank">
                                    <?php echo esc_html( $filename ); ?>
                                </a>
                            </td>
                            <td class="iata-col-date"><?php echo esc_html( $date ); ?></td>
                            <td class="iata-col-alt">
                                <input
                                    type="text"
                                    class="iata-alt-input widefat"
                                    placeholder="<?php esc_attr_e( 'Enter alt text…', 'image-alt-text-auditor' ); ?>"
                                    data-id="<?php echo esc_attr( $image->ID ); ?>"
                                    value=""
                                    aria-label="<?php
                                        printf(
                                            /* translators: %s: image filename */
                                            esc_attr__( 'Alt text for %s', 'image-alt-text-auditor' ),
                                            esc_attr( $filename )
                                        );
                                    ?>"
                                />
                                <span class="iata-status" aria-live="polite"></span>
                            </td>
                            <td class="iata-col-action">
                                <button
                                    type="button"
                                    class="button button-primary iata-save-btn"
                                    data-id="<?php echo esc_attr( $image->ID ); ?>"
                                >
                                    <?php esc_html_e( 'Save', 'image-alt-text-auditor' ); ?>
                                </button>
                                <a
                                    href="<?php echo esc_url( $edit_link ); ?>"
                                    class="button iata-edit-btn"
                                    target="_blank"
                                >
                                    <?php esc_html_e( 'Edit in Media Library', 'image-alt-text-auditor' ); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ( $num_pages > 1 ) :
                    $base = add_query_arg( 'paged', '%#%' );
                    echo '<div class="iata-pagination tablenav-pages">';
                    echo paginate_links( [
                        'base'      => $base,
                        'format'    => '',
                        'current'   => $paged,
                        'total'     => $num_pages,
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                    ] );
                    echo '</div>';
                endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
