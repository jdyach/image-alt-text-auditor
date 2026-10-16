<?php
/**
 * Plugin Name:       Image Alt Text Auditor
 * Plugin URI:        https://github.com/jdyach/image-alt-text-auditor
 * Description:       Audits your media library for images missing alt text and provides an admin page and dashboard widget to review and fix them inline.
 * Version:           1.0.0
 * Author:            Jacqui Dyach
 * Author URI:        https://jacquid.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       image-alt-text-auditor
 * Requires at least: 6.0
 * Requires PHP:      8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'IATA_VERSION', '1.0.0' );
define( 'IATA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IATA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once IATA_PLUGIN_DIR . 'includes/class-iata-query.php';
require_once IATA_PLUGIN_DIR . 'includes/class-iata-admin.php';
require_once IATA_PLUGIN_DIR . 'includes/class-iata-dashboard-widget.php';
require_once IATA_PLUGIN_DIR . 'includes/class-iata-ajax.php';

/**
 * Bootstrap all plugin components.
 */
function iata_init(): void {
    IATA_Admin::init();
    IATA_Dashboard_Widget::init();
    IATA_Ajax::init();
}
add_action( 'plugins_loaded', 'iata_init' );
