<?php
/**
 * Registers a Dashboard widget showing the missing alt text count.
 *
 * @package Image_Alt_Text_Auditor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IATA_Dashboard_Widget {

    public static function init(): void {
        add_action( 'wp_dashboard_setup', [ __CLASS__, 'register_widget' ] );
    }

    public static function register_widget(): void {
        wp_add_dashboard_widget(
            'iata_dashboard_widget',
            __( 'Image Alt Text Auditor', 'image-alt-text-auditor' ),
            [ __CLASS__, 'render_widget' ]
        );
    }

    public static function render_widget(): void {
        $count    = IATA_Query::count_images_missing_alt();
        $audit_url = admin_url( 'upload.php?page=image-alt-text-auditor' );
        ?>
        <div class="iata-widget">
            <?php if ( 0 === $count ) : ?>
                <p class="iata-widget-good">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e( 'All images have alt text!', 'image-alt-text-auditor' ); ?>
                </p>
            <?php else : ?>
                <p class="iata-widget-warn">
                    <span class="dashicons dashicons-warning"></span>
                    <?php
                    printf(
                        // translators: %d: number of images missing alt text.
                        esc_html( _n(
                            '%d image is missing alt text.',
                            '%d images are missing alt text.',
                            $count,
                            'image-alt-text-auditor'
                        ) ),
                        (int) $count
                    );
                    ?>
                </p>
                <p>
                    <a href="<?php echo esc_url( $audit_url ); ?>" class="button button-primary">
                        <?php esc_html_e( 'Run Audit', 'image-alt-text-auditor' ); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }
}
